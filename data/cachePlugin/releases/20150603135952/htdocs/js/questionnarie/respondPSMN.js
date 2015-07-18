/* Javascript OO Module Pattern */
var respondModule = (function () {
    var $main, $contentQuestion ,$localTextQuestions, $questionDefault, $listsQuestions,
        $totalQuestionsGeneral = 0, $totalQuestionsOkGeneral = 0;
    var $showFinishCriterion = false, $showFinishBlock = false, $actualCriterion, $actualBlock;
    var $menu, $allLiQuestionsNumbers, $frmResposta, $respostasRadio, $overlay;
    var $showQstnFinal = false;
    var $sortableParams = {};

    var menuClick = function() {
        var $liClosest = $(this).closest('li');
        if ($liClosest.hasClass('link')) {
            return true;
        }

        if ($liClosest.hasClass('uniqueCriterion')) {
            return false;
        } else if ($liClosest.hasClass('openned')) {
            $liClosest.removeClass('openned');
            $liClosest.find('ul:eq(0)').hide();
        } else {
            $liClosest.addClass('openned');
            $liClosest.find('ul:eq(0)').show();
        }

        return false;
    }

    var initializeModals = function(){
        var fancyboxCommonOptions = {
            wrapCSS    : 'fancybox-custom',
            closeClick : true,

            openEffect : 'elastic',
            openSpeed  : 150,

            closeEffect : 'elastic',
            closeSpeed  : 150,

            prevEffect : 'elastic',
            nextEffect : 'elastic',

            arrows    : false,
            nextClick : false,

            helpers : {
                title : {
                    type : 'inside'
                },
                overlay : {
                    css : {
                        'background' : 'rgba(0,0,0,0.85)'
                    }
                }
            }
        };

        initializeQuestionDetails(fancyboxCommonOptions);
        initializeQuetionnaireIntroduction(fancyboxCommonOptions);
    }

    var initializeQuestionDetails = function(fancyboxCommonOptions){
        $('[data-question-id] a.details').each(function(index,details_link){
            var $details_link = $(details_link);
            var content = $details_link.parents('[data-question-id]').first().find('div.details').html();
            var details_fancybox_options = jQuery.extend(true, {}, fancyboxCommonOptions);
            details_fancybox_options.content = content;
            details_fancybox_options.maxWidth = 800;
            $details_link.fancybox(details_fancybox_options);
        });

        $('body').on('click', '.fb-content a.close', function(){
            $.fancybox.close();
        });
    }

    var initializeQuetionnaireIntroduction = function(fancyboxCommonOptions){
        var content = $('#questionnaire-intro').html();
        var introduction_fancybox_options = jQuery.extend(
            true,
            { content: content, maxWidth: 800, closeClick: false },
            fancyboxCommonOptions
        );

        $helpButton = $('.btn-submit.help');
        $helpButton.fancybox(introduction_fancybox_options);

        if($.isEmptyObject(window.QUESTION_ANSWERED_ITEMS)) $helpButton.click();
    }

    var goToNextQuestion = function(data){
        data._.questions[(data._.questions.isLast ? 'todo' : 'answered')][(data._.questions.isLast ? 'first' : 'next')]().find('a').trigger('click', true);
    }

    updateProgress = function() {
        var $totalPercent = ($totalQuestionsOkGeneral*97)/$totalQuestionsGeneral;
        $("#percent").animate({width: $totalPercent + '%'}, 700);
    },
    generateDevolutive = function(e) {
        e.preventDefault();
        $('#msgDownloadDevolutiva').html('Aguarde...');
        $.ajax({
            url: BASE_URL + '/questionnaire/devolutive/index/format/json/qstn/' + QSTN_ID + '/enterprise-user/' + ENTERPRISE_USER,
            type: 'get',
            cache: false,
            dataType: 'json'
        }).done(function(json, $statusText, jqXHR) {
            if (!($statusText == 'success') || !json.itemSuccess) {
                alert(json.messageError);
                return;
            }
            $('#msgDownloadDevolutiva')
                .html('<a href="' + json.devolutive + '" target="_blank">Devolutiva gerada com sucesso. Clique aqui para fazer o download dela.</a>');
        });
    },
    submitRespond = function() {
        var $this = $(this),
            $question = $this.closest('li.questionItem'),
            $dataPost = {
                'format': 'json',
                'question_id' :  $question.data('question-id'),
                'alternative_id': $this[0].value,
                'start_time': startDate,
                'enterprise-id-key': ENTERPRISE_ID_KEY
            };
            
        var $allRadios = $main.find('input.radio').not($this)
            .prop('disabled', true);

        $.ajax({
            url: BASE_URL + '/questionnaire/respond/answer/',
            type: 'get',
            cache: false,
            dataType: 'json',
            data: $dataPost
        }).done(function(json, $statusText, jqXHR) {
            if(window.formcomplete == false){
                if( $('.questionTypeSubmitRadioChange').find(':radio:checked').size() == 30 ){
                $url = (PAPEL_EMPRESA)?
                    BASE_URL+'/questionnaire/respond/index/block/'+CURRENT_BLOCK_ID:
                    BASE_URL+'/management/questionnaire/not-coop-responding/block/'+CURRENT_BLOCK_ID+'/enterprise-id-key/'+ENTERPRISE_ID_KEY;
            
                window.location.href = $url;
                // BASE_URL + '/questionnaire/respond/index/geraDevolutiva/1/enterprise-id-key/'+ENTERPRISE_ID_KEY;
                }
            }
            $allRadios.prop('disabled', false);
            if (!($statusText == 'success') || !json.itemSuccess) {
                alert(json.messageError);
                return;
            }
        });
    };

    var $menuItems = $('.quizz a[href^=#tab]');

    return {
        quiz: {
            change: function (element, triggered) {
                var $index,
                $form,
                $answers,
                $checked,
                $id,
                $confirm = [
                    'Você gostaria de avançar de questão sem salvar a reposta atual?',
                    'As informações contidas nesse formulário serão perdidas.'
                ],
                $question,
                $answer,
                $complement,
                $warnings,
                $status = false,
                $return = true;
                triggered = ((triggered !== undefined) ? triggered : false);
                if ($menuItems.length) {
                    $index = {
                        current: parseInt($menuItems.filter(function () {
                            return $(this).hasClass('current');
                        }).parent().index(), 10),
                        target: parseInt(element.parent().index(), 10)
                    };
                    $form = {
                        current: $('form').eq($index.current),
                        target: $('form').eq($index.target)
                    };
                    $answers = {
                        current: $form.current.find('.answer').find(':radio'),
                        target: $form.target.find('.answer').find(':radio')
                    };
                    $checked = {
                        current: $answers.current.filter(function () {
                            return $(this).is(':checked');
                        }),
                        target: $answers.target.filter(function () {
                            return $(this).is(':checked');
                        })
                    };
                    if ($index.current !== $index.target) {
                        if ($checked.current.length) {
                            $id = parseInt($form.current.data('question-id'), 10);
                            $question = $form.current.find('.label');
                            $answer = $checked.current.next(['label[for="', $checked.current.attr('id'), '"]'].join(''));
                            $answer_id = $checked.current.attr('id').match(/\d+/g);
                            $complement = $form.current.find(['#FdbkQuestion', $form.current.data('question-id')].join(''));
                            $warnings = $form.current.find('.complement-label, .complement-field, .error-required');
                            if (_.contains(QUESTION_ANSWERED, $id) && (QUESTION_ANSWERED_ITEMS.hasOwnProperty($id) && (QUESTION_ANSWERED_ITEMS[$id].hasOwnProperty('id') && (QUESTION_ANSWERED_ITEMS[$id].id.toString() === $answer_id.toString())) && (QUESTION_ANSWERED_ITEMS[$id].hasOwnProperty('complement') && (QUESTION_ANSWERED_ITEMS[$id].complement.toString() === $.trim($complement.val()).toString())))) {
                                $return = true;
                            } else {
                                $status = (!triggered ? confirm($confirm.join('\n')) : triggered);
                                if ($status) {
                                    if (!triggered) {
                                        $form.current.find('.answer').find(':radio').removeAttr('checked').end().removeClass('checked');
                                        if (_.contains(QUESTION_ANSWERED, $id)) {
                                            $checked.original = $answers.current.filter(function () {
                                                return $.trim($(this).val()).toString() === QUESTION_ANSWERED_ITEMS[$id].id.toString();
                                            }).attr('checked', 'checked');
                                            $answer = $checked.original.next(['label[for="', $checked.original.attr('id'), '"]'].join(''));
                                            $complement.val(QUESTION_ANSWERED_ITEMS[$id].complement.toString());
                                            $answer.parent().addClass('checked');
                                            if ($checked.original.parents('.answer').index() < 3) {
                                                $warnings.stop().animate(
                                                    {
                                                        opacity: 0
                                                    },
                                                    250
                                                );
                                            }
                                        } else {
                                            $complement.val('');
                                            $warnings.stop().animate(
                                                {
                                                    opacity: 0
                                                },
                                                250
                                            );
                                        }
                                        $warnings.attr('tabindex', -1);
                                    }
                                    $return = true;
                                } else {
                                    $return = false;
                                }
                            }
                        }
                    }
                    $form.current.find('a, button, input, select').attr('tabindex', -1);
                    $form.target.find('a, button, input, select').removeAttr('tabindex');
                } else {
                    $return = false;
                }
                return {
                    status: $return,
                    element: ($checked.target.length ? $checked.target : $answers.target.first())
                };
            },
            select: function (element) {
                var form = element.parents('form'),
                item = element.parents('.answer'),
                complement = form.find('.complement-label, .complement-field, .error-required').stop().animate(
                    {
                        opacity: ((item.index() >= 3) ? 0 : 0)
                    },
                    250
                ).filter(function () {
                    return $(this).is('textarea');
                })[((item.index() >= 3) ? 'removeAttr' : 'attr')]('tabindex', -1)[((item.index() >= 3) ? 'focus' : 'blur')]()[((item.index() >= 3) ? 'select' : 'blur')]();
                return this;
            },
            submit: function (element) {
                var
                    $this = $(element),
                    $isChrome = /chrome/.test(navigator.userAgent.toLowerCase()),
                    $question = {
                        id: parseInt(($this.data('question-id') || 0), 10),
                        index: $this.parents('.tab-item').index()
                    },
                    $answers = {
                        checked: {
                            element: $this.find(':radio:checked')
                        },
                        complement: {
                            error: {
                                classname: '.error-required',
                                content: '<div class="error-required">Complemente sua resposta, com um texto no campo abaixo.</div>'
                            },
                            wrapper: $this.find('.complement'),
                            field: $this.find('textarea')
                        }
                    },
                    $scroller = $($isChrome ? 'body' : 'html'),
                    $status = true;
                if ($answers.checked.element.length) {
                    $answers.checked.parent = $answers.checked.element.parents('.answer');

                    $answers.complement.field.val('');
                    $answers.complement.wrapper.find($answers.complement.error.classname).fadeOut().remove();

                    if ($status) {
                        $.ajax(
                            {
                                url: [BASE_URL, '/questionnaire/respond/answer/'].join(''),
                                type: 'get',
                                cache: false,
                                dataType: 'json',
                                data: {
                                    'format': 'json',
                                    'question_id':  $question.id,
                                    'answer_value':  $.trim($answers.complement.field.val()),
                                    'alternative_id': $.trim($answers.checked.element.val()),
                                    'start_time': startDate,
                                    'enterprise-id-key': ENTERPRISE_ID_KEY
                                },
                                success: function (data) {
                                    data = (data || {});
                                    data._ = (data._ || {
                                        questions: {
                                            all: $menuItems.parent()
                                        }
                                    });
                                    if (!_.contains(QUESTION_ANSWERED, $question.id)) {
                                        QUESTION_ANSWERED.push($question.id);
                                    }
                                    QUESTION_ANSWERED_ITEMS[$question.id] = {
                                        id: $.trim($answers.checked.element.val()),
                                        complement: $.trim($answers.complement.field.val())
                                    };
                                    data._.questions.answered = data._.questions.all.eq($question.index).addClass('ui-state-active');
                                    data._.questions.done = data._.questions.all.filter(function () {
                                        return $(this).hasClass('ui-state-active');
                                    });
                                    data._.questions.todo = data._.questions.all.not(data._.questions.done);
                                    data._.questions.isLast = (data._.questions.answered.index() === (data._.questions.all.length - 1));

                                    if(data.updateDevolutive){
                                        var url = [
                                            '/questionnaire/respond/index/geraDevolutiva/1/regerar/1',
                                            '/enterprise-id-key/', ENTERPRISE_ID_KEY
                                        ].join('');

                                        $.post(url).success(
                                            function(){
                                                goToNextQuestion(data);
                                            }
                                        ).fail(
                                            function(){
                                                var message = [
                                                    'Prezada candidata,',
                                                    'Sua devolutiva já foi gerada, mas uma das suas respostas no questionário '+
                                                    'foi modificada, portanto é necessário atualizar a devolutiva.',
                                                    'Por favor, aguarde o redirecionamento para a atualização.'
                                                ].join('\n');

                                                alert(message);

                                                window.location = url;
                                            }
                                        );
                                    } else if (data._.questions.todo.length === 0){
                                        var url;

                                        if(PAPEL_EMPRESA){
                                            url = '/questionnaire/report/';
                                        } else {
                                            url = [
                                                '/management/report/index/enterprise-id-key/',
                                                ,
                                                '/programa_id/',
                                                PROGRAMA_ID
                                            ].join('');
                                        }

                                        window.location = url;
                                    } else {
                                        goToNextQuestion(data);
                                    }
                                }
                            }
                        );
                    }
                }
            },
            init: function () {
                return this;
            }
        },
        loadQuestion: function($questionId, $isPost, $dataPost) { //publico
            loadQuestion($questionId, $isPost, $dataPost);
            return this;
        },
        init: function() {
            var $this = this;
            this.quiz.init();

             $('ol').find('[type="radio"]').click(function() {
              $(this).parents('.questionItem').find('.checkedRadio').removeClass('checkedRadio')
              $(this).next('.label-inline').eq(0).find('span.radio-button').addClass('checkedRadio')
                
            }).each(function () {
                if($(this).is(':checked')){
                    $(this).parents('.questionItem').find('.checkedRadio').removeClass('checkedRadio')
                    $(this).next('.label-inline').eq(0).find('span.radio-button').addClass('checkedRadio')
                }
            });

            $overlay = $('#overlay1').show();
            $main = $('#content');
            var
                $questionsTypeSubmitButton = $main.find('.questionTypeSubmitButton'),
                $questionTypeSubmitRadioChange = $main.find('.questionTypeSubmitRadioChange :radio');
            $questionTypeSubmitRadioChange.on('change', submitRespond);
            $questionsTypeSubmitButton.on('change', 'input.radio', submitRespond);
            $('#geraDevolutiva').on('click', generateDevolutive);

            $overlay.hide();



            var $act;

            
            var $listquestions = $('.inner-content ol');
            var questionsNr = $listquestions.find('li').size();
            var questionsperPage = 3;
            var questionHeight = 97;
            var questionPage = 0;
            var answerEmptyPage = 3-$('#content').find('.questionTypeSubmitRadioChange :radio:checked').slice( questionPage*questionsperPage , questionPage*questionsperPage+3 ).size();
            
            var formComplete = questionsNr - $listquestions.find('li :radio:checked').size() == 0 ? true : false;

            function checkAnswers(){
                var answereds = $listquestions.find('li :radio:checked').size();
                $listquestions.find('li').each(function(i, e){
                    if($(this).find(':radio:checked').size() == 0){
                        $(this).addClass('sem-resposta')
                    }
                })
                if( questionsNr > answereds ){
                    var remainQuestions = questionsNr-answereds;
                    $listquestions.before('<div class="info-answered-questions">Ainda falta' + (remainQuestions > 1 ? 'm' : '') + ' responder ' + remainQuestions + ' quest' + (remainQuestions > 1 ? 'ões' : 'ão') + '</div>')
                }else{
                    if( formComplete == false){
                        window.location.href = '/questionnaire/respond/index/geraDevolutiva/1';
                    }
                    $('.info-answered-questions').fadeOut().remove()
                }
            }

            if( $('.questionTypeSubmitRadioChange').find(':radio:checked').size() == 30 ){
                window.formcomplete = true;
                var urlRedirect = BASE_URL + '/questionnaire/respond/index/block/'+CURRENT_BLOCK_ID;
                if (!PAPEL_EMPRESA) {
                    urlRedirect = BASE_URL + '/management/questionnaire/not-coop-responding/enterprise-id-key/' + ENTERPRISE_ID_KEY;
                }
                $('.inner-content ol')
                    .after('<div id="message-quest"><a href="' + urlRedirect + '">Ir para o Questionário de Negócios.</a></div>')
            } else{
                window.formcomplete = false;
            }
            
              /*
            $listquestions
              .css({'height': questionsperPage*questionHeight , 'overflow': 'hidden'})
              .before('<button class="bt-nav-quiz-vert next" type="button">Anteriores</button>')
              .after('<button class="bt-nav-quiz-vert prev"  type="button">Próximas</button>');

            $('.bt-nav-quiz-vert').on('click', function(){
              if( $(this).hasClass('prev')){
                questionPage++;
                if( questionPage >= Math.ceil(questionsNr/questionsperPage)  ){
                    questionPage = Math.ceil(questionsNr/questionsperPage);
                    checkAnswers()
                    return false
                }
              }else{
                questionPage--;
                $('.info-answered-questions').fadeOut().remove()
                if( questionPage < 0  ){
                    questionPage = 0;
                    return false
                }
              }
              //answerEmptyPage = 3- $('#content').find('.questionTypeSubmitRadioChange :radio:checked').slice( questionPage*questionsperPage , questionPage*questionsperPage+3 ).size()
              desloc = questionsperPage*questionHeight*questionPage;
              $('.inner-content ol li').eq(0).animate({ marginTop: -desloc})
            });
    */  


      

            $status = $('.status');
            $status.width(0);
            $('[class*="answer"] input')
                .on('focus', function(){
                    $('.radio-focus').removeClass('radio-focus')
                    $(this).next('label').addClass('radio-focus');
                })
                .on('change', function(){
                    $this.quiz.select($(this));
                    var percent = Math.ceil(($('form :radio:checked').size() * 100) / $('form li').size())
                    $('#percent-tooltip').html(percent+'<span>%</span>');
                    $status.animate({
                        width: percent+'%'
                    });
                })//.eq(0).trigger('change')
                
                //$('[class*="answer"] input:checked').eq('0').trigger('change')
                 var percent = Math.ceil(($('form :radio:checked').size() * 100) / $('form li').size())
                $('#percent-tooltip').html(percent+'<span>%</span>');
                $status.animate({
                    width: percent+'%'
                });

             $questionTypeSubmitRadioChange.on('click', function (e) {
                $(this).parents('li').removeClass('sem-resposta')
                 if(answerEmptyPage > 0){
                     var answeredPageNr = 0;
                     $('#content').find('.questionTypeSubmitRadioChange').slice( (questionPage)*questionsperPage , questionPage*questionsperPage+3 ).each(function(i, e){
                        if( $(this).find(':radio:checked').size() > 0 ){
                            answeredPageNr++;
                        }
                     })

                     if(answeredPageNr == questionsperPage ){
                         $('.bt-nav-quiz-vert.prev').trigger('click')
                     }
                 }
             });


            $menuItems.click(function(e, triggered){ 
                e.preventDefault();
                var $button = $(this);
                var $id = $button.attr('href').replace('#tab-', '');
                var $change = $this.quiz.change($button, triggered);
                if ($change.status) {
                    $('#tabs-list').animate(
                        { marginLeft: -832*($id-1) },
                        function () {
                            $menuItems.not($button).removeClass('current');
                            $button.addClass('current');
                            $change.element.focus();
                        }
                    );
                }
            });

             var itemBlank1 = false;
             $('#tabs-list .tab-item').each(function (i, e) {
                if( $(e).find(':radio:checked').size() ){
                    $('#marker-'+(i+1)).addClass('ui-state-active');
                } else{
                    if( !itemBlank1){
                        itemBlank1 = true;
                        $('#marker-'+(i+1)+' a').addClass('current').trigger('click');
                    }
                }
             })

             var quanQuestions = $('.tab-item').size()


             var form1size =  $('.answer :radio').size()/5;
            var form1responded =  $('.answer :radio:checked').size();
            var form1complete = form1size == form1responded ? true : false;

            $('.formsubmitfull').on(
                'submit',
                function (event) {
                    event.preventDefault();
                    $this.quiz.submit(this);
                }
            );
            $('.formsubmitfull').find(':radio:checked').parents('.answer').addClass('checked');
            $('.formsubmitfull').find(':radio').on('change', function(){
                $(this).parents('form').find('.checked').removeClass('checked');
                $(this).parents('.answer').addClass('checked');

            });

            initializeModals();

            /*
            $('.formsubmitfull').find('.btSaveQuestionWithFeedback').on('click', function(){
                var $this = $(this),
                $act = $this,
                $form = $this.parents('form'),
                $question = $this.closest('li.questionItem'),
                $form = $this.parents('form'),
                $dataPost,
                $isChrome = /chrome/.test(navigator.userAgent.toLowerCase());
                if( $form.find(':radio:checked').parents('.answer').index() >= 3  ){
                    if( $form.find('textarea').val() == '' ){
                        $form.find('.complement').prepend('<div class="error-required">Complemente sua resposta, com um texto no campo abaixo.</div>')
                        $($isChrome ? 'body' : 'html').stop().animate(
                            {
                                scrollTop: $form.find('.error-required').offset().top
                            },
                            1000
                        );
                        return false;
                    }
                }else{
                    $form.find('textarea').val('');
                    $form.find('.error-required').fadeOut().remove()
                }

                $dataPost = {
                    'format': 'json',
                    'question_id' :  $form.data('question-id'),
                    'answer_value' :  $form.find('textarea').val(),
                    'alternative_id': $form.find(':radio:checked').val(),
                    'start_time': startDate
                };

                $.ajax({
                    url: BASE_URL + '/questionnaire/respond/answer/',
                    type: 'get',
                    cache: false,
                    dataType: 'json',
                    data: $dataPost, 
                    success: function(data){
                        if (!_.contains(QUESTION_ANSWERED, parseInt($dataPost.question_id, 10))) {
                            QUESTION_ANSWERED.push(parseInt($dataPost.question_id, 10));
                        }
                        $this.find('.responseretu').text('Salvo com sucesso')
                        var id = $this.parents('.tab-item').index()+1;
                        if( $this.parents('.tab-item').find(':radio:checked').size() > 0 ){
                            $('#marker-' + id).addClass('ui-state-active');
                        }
                        if( id == quanQuestions ){
                            var $naoRespondidas = $('.quizz li').not('.ui-state-active');
                            if( $naoRespondidas.size() > 0 ){
                                id = $naoRespondidas.eq(0).find('a').index()+1;
                            }else{
                                if(form1complete == false){
                                    window.location.href = '/questionnaire/respond/index/block/60';
                                    return false;
                                }
                                id = 0;

                            }
                        }
                        $('#tabs-list').animate({ marginLeft: -832*id });
                    }
                });
            })
            */
            return;
        }
    }

}());

$.fn.ready(function() {
    try {
        respondModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            //document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }
        throw e;
    }
});