/* Javascript OO Module Pattern */

var respondModule = (function () {
    var $contentQuestion ,$localTextQuestions, $questionDefault, $listsQuestions,
        $totalQuestionsGeneral = 0, $totalQuestionsOkGeneral = 0,
        $keyEmbedly = '75e04ed400e54a5b95a5a309348fad58';
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
    },
    updateSelectedStyle = function(x, e) {
        var $respostasFiledSet = $(this).closest('fieldset'),
            $respostasRadio = $respostasFiledSet.find('input.radio');
        $respostasRadio.removeClass('focused').prev().removeClass('focused');
        var $checked = $respostasRadio.filter('input:checked');
        $respostasFiledSet.find('div.divTypeText:visible').hide();

        if ($checked.hasClass('typeText')) {
            $checked.next('div.divTypeText').show(); //.find('textarea').focus();
        }
        $checked.addClass('focused').prev().addClass('focused');
    },

    clickQuestion = function(e) {
        
        e.preventDefault();
        $questionMenuItem = $(this).closest('li');
        $questionId = $questionMenuItem.attr('data-item-id');

        respondModule.loadQuestion($questionId, false);
    },

    respondQuestion = function(e) {

        $formRespond = $(this);

        if ($formRespond.data('submited')) {
            return false;
        }

        $formRespond.data('submited', true);

        $showFinishCriterion = false;
        $showFinishBlock = false;
        $questionId = $formRespond.find('input.respondQuestionId').val();
        $questionMnuItem = $('#questionItem' + $questionId);
        $nextQuestion = $questionMnuItem.next('li.questionItem:eq(0)');
        //console.log('1', $nextQuestion);

        $actualCriterion = $questionMnuItem.closest('li.criterionItem');
        $actualBlock = $actualCriterion.closest('li.blockItem');

        // se nao tem proxima questao no menu, entÃ£o foi a ultima questao do criterio.
        if ($nextQuestion.length === 0) {
            $showFinishCriterion = true;

            //fecha todos os criterios
            $sideMenu.find('li.criterionItem.openned').find('a:eq(0)').click();

            var $nextCriterion = $actualCriterion.next();
            $nextCriterion.find('a:eq(0)').click(); //expand proximo critÃ©rio
            $nextQuestion = $nextCriterion.find('li.questionItem:eq(0)');
            //console.log('2', $nextQuestion);
        }

        //sem proxima questao, vai para o proximo bloco.
        if ($nextQuestion.length === 0) {
            $showFinishBlock = true;
            var $nextBlock = $actualBlock.next();

            if (!$nextBlock.hasClass('openned')) {
                $nextBlock.find('a:eq(0)').click(); //expand proximo bloco
            }

            $sideMenu.find('li.criterionItem.openned').find('a:eq(0)').click();
            $nextCriterion = $nextBlock.find('li.criterionItem:eq(0)');
            $nextCriterion.find('a:eq(0)').click();

            $nextQuestion = $nextCriterion.find('li.questionItem:eq(0)');
            //console.log('3', $nextQuestion);
        }

        //Ãºltima do questionÃ¡rio, volta para a primeira sem responder.
        if ($nextQuestion.length === 0) {
            var $questionsNotOk = $listsQuestions.find('li.questionItem:not(.ok)'),
                $lenQuestionNotOK = $questionsNotOk.length,
                $firstQuestionNotOk = $questionsNotOk.first();

            if (!($firstQuestionNotOk.attr('data-item-id') == $formRespond.find('input.respondQuestionId').val()
                && $formRespond.find('input[name=alternative_id]').fieldValue() != '')
            ) {
                $nextQuestion = $firstQuestionNotOk;
            }
        }

        //console.log('n', $nextQuestion);

        respondModule.loadQuestion(
            $nextQuestion.attr('data-item-id'), true, $formRespond.formSerialize()
        );

        return false;
    },
    showQstnFinal = function($question) {

        $question.show()
        var $form = $question.find('form:eq(0)').hide(),
            $formDivisor = $question.find('div.divisor').hide(),
            $finishMsgText = '';
        $finishMsg = $question.find('div.finishMsg').show();

        //fecha todos os blocos
        $sideMenu.find('li.blockItem.openned').find('a:eq(0)').click();

        $finishMsgText = '<b class="final">Aguarde...</b>'
        $finishMsg.html($finishMsgText);
        $('body').append('<div id="enableScreen" />');
        $('#topQuestionNumber').hide();

        if (COOP_RESPONDING) {
            window.location.href = BASE_URL + '/questionnaire/index/list-qsts/successItem/1';
        } else {
            window.location.href = BASE_URL + '/management/questionnaire/not-coop-responding/enterprise-user/' + ENTERPRISE_USER + '/menu/false/list-all/true';
        }
    },
    showCriterionFinal = function($question) {

        var $finishMsg = $question.find('div.finishMsg').hide(),
            $finishMsgText = '', $faltMsg = '';

        if ($showFinishBlock) {
            $finishMsgText += 'Você concluiu o bloco ' + $actualBlock.find('a:first').text() + '!<br>';
        }
        var $criterionName = $actualCriterion.find('a:first').text();
        if ($criterionName != '') {
            $finishMsgText += 'Você concluiu o critério ' + $actualCriterion.find('a:first').text() + '!<br>';
        }
        var $faultBlock = $sideMenu.find('li.blockItem:not(.ok)').length,
            $faultCriterion = $sideMenu.find('li.criterionItem:not(.ok)').length;

        $faltMsg += 'Falta(m) ';
        if ($faultBlock) {
            $faltMsg += $faultBlock + ' bloco' + ($faultBlock!=1? 's' : '');
        }
        if ($faultCriterion) {
            $faltMsg += ($faultBlock!=0? '/' : '') + $faultCriterion + ' critério' + ($faultCriterion!=1? 's' : '');
        }
        $faltMsg += ' para a conclusão deste questionário.<br>';

        if ($faultBlock || $faultCriterion) {
            $finishMsgText += $faltMsg;
        }

        $showFinishCriterion = false;

        /*if (!$faultBlock && !$faultCriterion) {
            //alert('x');
            showQstnFinal($question);
            return;
        }*/

        $finishMsg.html($finishMsgText)
            .append($sideMenu.find('ul.ulTab1.current').clone(false))
            .append(
                '<button class="large green finishContinue" type="button">Continuar <span class="st">&gt;</span></button>'
            )
            .show();
    },
    loadLastQuestion = function(e) {
        e.preventDefault();
        //$overlay.show();

        $questionMnuItem = $listsQuestions.find('li.questionItem.current');
        $lastQuestion = $questionMnuItem.prev('li.questionItem:eq(0)');

        $actualCriterion = $questionMnuItem.closest('.criterionItem');

        // se nÃ£o tem questÃ£o anterior no menu, entÃ£o Ã© a primeira questÃ£o do critÃ©rio.
        if ($lastQuestion.length === 0) {
            //fecha todos os criterios
            $sideMenu.find('li.criterionItem.openned').find('a:eq(0)').click();
            var $lastCriterion = $actualCriterion.prev();
            $lastCriterion.find('a:eq(0)').click(); //expand critÃ©rio anterior
            $lastQuestion = $lastCriterion.find('li.questionItem:last');
        }

        //sem questÃ£o anterior, vai para o bloco anterior.
        if ($lastQuestion.length === 0) {
            var $actualBlock = $actualCriterion.closest('.blockItem');
            var $lastBlock = $actualBlock.prev();

            if (!$lastBlock.hasClass('openned')) {
                $lastBlock.find('a:eq(0)').click(); //expand bloco anterior
            }

            $sideMenu.find('li.criterionItem.openned').find('a:eq(0)').click(); //fecha todos criterios
            $lastCriterion = $lastBlock.find('li.criterionItem:last');
            $lastCriterion.find('a:eq(0)').click();

            $lastQuestion = $lastCriterion.find('li.questionItem:last');
        }

        if ($lastQuestion.length === 0) {
            alert('sem questao anterior');
            $('#enableScreen').remove();
            return;
        }
        $lastQuestion.find('a:eq(0)').click();

        return;
    },
    goToHtml = function($qsLi, $qsId) {

        var $totalLoadQuestions = $localTextQuestions.find('li:not(.systemDefault):not(.hide)').length;
        $localTextQuestions.css('width',  ($totalLoadQuestions*703) + 'px');

        if ($totalLoadQuestions == 1) {
            $contentQuestion.get(0).scrollLeft = 0;
        }
        $localTextQuestions.find('li.current').removeClass('current').find('div.divisor').css('visibility', 'visible');
        $qsLi.addClass('current');

        setTimeout(function(){
            $("#topQuestionNumber").hide();
            $('#topAjuda').show().css('visibility', 'hidden');
            $qsLi.show();
            $localTextQuestions.animate({"left": -($qsLi.position().left)}, SPEEDY_TRANSITION, function(){
                $listsQuestions.find('li.questionItem.current').removeClass('current');
                $listsQuestions.find('li#questionItem' + $qsId).addClass('current');

                $(document).on('keydown.ans', documentKeys);
                $(this).blur().focus();

                    var $atualQstDivisor = $qsLi.find('div.divisor');
                    $("#topQuestionNumberSpan").text(
                        $atualQstDivisor.find('h3.questionNumber span').text()
                    );
                    $('#topAjuda').show().css('visibility', $atualQstDivisor.find('a.ajuda').is(':visible')? 'visible' : 'hidden');
                    $("#topQuestionNumber").show();
                    $atualQstDivisor.css('visibility', 'hidden');

                var $buttonContinue = $qsLi.find('button.finishContinue:first');
                if ($buttonContinue.length) {
                    $buttonContinue.focus();
                    $("#topQuestionNumber").hide();
                } else {
                    $qsLi.find('input.chk').focus();
                }
            });
        }, 150);

        $('.tp').on('mouseover', function(){
          var $this = $(this);
          var id = $this.attr('title');
          var $tooltip = $('#gloss_'+id)
          $tooltip.addClass('tooltip').fadeIn();
          var offset = $this.position();
          //$('#qstnRespond').append($tooltip)
          $tooltip.css({
            top: offset.top+25,
            left: (offset.left > 450 ? 450 : offset.left ),
            position: 'absolute',
            width: (600 - offset.left  < 200 ? 200 : 600 - offset.left )
          }).stop(true, true).fadeIn()
        })
        .on('mouseleave', function(){
           var $this = $(this);
          var id = $this.attr('title');
          var $tooltip = $('#gloss_'+id)
          $tooltip.stop(true, true).fadeOut()
        })
    },
    documentKeys = function(e){
        switch (e.keyCode) {
            case 65:
                $localTextQuestions.find('li.current form input.radioA').prev('label')
                    .attr('checked', true).trigger('click').focus();
            break;
            case 66:
                $localTextQuestions.find('li.current form input.radioB').prev('label')
                    .attr('checked', true).trigger('click').focus();
            break;
            case 67:
                $localTextQuestions.find('li.current form input.radioC').prev('label')
                    .attr('checked', true).trigger('click').focus();
            break;
            case 68:
                $localTextQuestions.find('li.current form input.radioD').prev('label')
                    .attr('checked', true).trigger('click').focus();
            break;
        }
    },
    loadQuestion = function($questionId, $isPost, $dataPost) {
        $('body').append('<div id="enableScreen" />');
        //console.log($questionId);
        if (typeof $questionId === 'undefined') {
            $questionId = '';
        }

        $(document).off('keydown.ans');

        /* @TODO */
        //$.scrollTo( $sideMenu, 400, {offset: 5} );
//console.log('loadQuestion', $dataPost)
        $.ajax({
            url: BASE_URL + '/questionnaire/respond/answer/Id/' + $questionId + '/format/json',
            type: ($isPost === true)? 'post' : 'get',
            cache: false,
            dataType: 'json',
            data: ($isPost === true)? $dataPost : {}
        }).done(function(json, $statusText, jqXHR) {
            /* @TODO */
            /*
            setTimeout(function(){escroll();}, 500);
            */
           $('#enableScreen').remove();

            if( ! _.isUndefined( json.answerData ) ){
            if( ! _.isUndefined( json.answerData.AdditionalInfo ) ){
              _.each(json.answerData.AdditionalInfo.answerArray, function(value, id){


                $('[name="additional_info[answerArray][' + id + ']"]:not(:radio)', '.additional-question-' + json.question_id ).val( value );

                var $radios = $('input:radio[name="additional_info[answerArray][' + id + ']"]', '.additional-question-' + json.question_id );
                if($radios.is(':checked') === false) {
                  $radios.filter('[value="' + value + '"]').prop('checked', true);
                }

                //$('[name="additional_info[answerArray][' + id + ']"]', '.additional-question-' + json.question_id );
              });
            }
            }

            if (!($statusText == 'success') || !json.itemSuccess) {
                if ($isPost) {
                    $formRespond.data('submited', false);
                }
                $(document).on('keydown.ans', documentKeys);
                alert(json.messageError);
                return;
            }

            $localTextQuestions.find('li.current').find('input.radio, button.green, textarea').prop('disabled', true);

            if ($isPost && json.respondQuestionOk) {

                if (!$questionMnuItem.hasClass('ok')) {
                    $questionMnuItem.addClass('ok');
                    $totalQuestionsOkGeneral += 1;
                    updateProgress();
                }

                var $actualCriterionTotalQuestions = $actualCriterion.attr('total-questions');
                var $actualCriterionTotalQuestionsOk = $actualCriterion.find('li.questionItem.ok').length;
                if ($actualCriterionTotalQuestions == $actualCriterionTotalQuestionsOk) {
                    $actualCriterion.addClass('ok');
                }

                var $actualBlockTotalCriterions = $actualBlock.attr('total-criterions'); //mudei aqui temp
                var $actualBlockTotalCriterionsOk = $actualBlock.find('li.criterionItem.ok').length;
                if ($actualBlockTotalCriterions == $actualBlockTotalCriterionsOk) {
                    $actualBlock.addClass('ok');
                }
            }


            if (typeof json.question === 'undefined'
                || ($isPost && json.question.I === $questionMnuItem.attr('data-item-id'))
            ) {
                var $itemCurrent = $localTextQuestions.find('li.current');
                $questionMnuItem.removeClass('current');
                showQstnFinal($itemCurrent);
                //console.log('showQstnFinal', $itemCurrent);
                return;
            };

            var $question = $questionDefault.clone(true).removeClass('systemDefault'),
                $form = $question.find('form:eq(0)').show(),
                $formDivisor = $question.find('div.divisor').show(),
                $itemId = json.question.I,
                $questionTitle = json.question.V,
                $questionDescription = json.question.S,
                $questionTypeId = json.question.T;

            $question[0].id = 'qst' + $itemId;
            $('#qst' + $itemId).replaceWith($question);




            $form.find('h2.questionTitle').html($questionTitle);
            /* @TODO */
                    /*
                    .tooltip({
                items: "[title]",
                content: function(x) {
                 var element = $( this );
                 if ( element.is( "[title]" ) ) {
                   return $('#gloss_' + element[0].title).html();
                 }
              }
            });*/
            $form.find('p.questionDescribe').html($questionDescription);
            /* @TODO */
                    /*
                    .tooltip({
                items: "[title]",
                content: function() {
                 var element = $( this );
                 if ( element.is( "[title]" ) ) {
                   return $('#gloss_' + element[0].title).html();
                 }
                 return '';
              }
            });*/

            $form.find('input.respondQuestionId').val($itemId);

            $question.find('h3.questionNumber span').text(
                '.' + $('#questionItem' + $itemId).attr('data-question-number')
            );


            var $questionFieldsetArea = $question.find('form fieldset:eq(0)');


            loadAlternatives(
                json.alternatives, $questionFieldsetArea, json.answerData, $questionTypeId
            );
            loadTips(json.tips, $question);
            loadEvaluationResponse(
                $question, $questionFieldsetArea,
                typeof json.evaluation !== 'undefined'? json.evaluation : '',
                typeof json.evaluationImprove !== 'undefined'? json.evaluationImprove : ''
            );

            if (($isPost && json.respondQuestionOk && $showFinishCriterion
                && $actualCriterionTotalQuestions == $actualCriterionTotalQuestionsOk)
            ) {
                $form.hide();
                $formDivisor.hide();
                //console.log($sideMenu.find('li.questionItem.ok').length);
                showCriterionFinal($question);
            }

             // show additional question if exists
            var $alternativeAnswer = $('.additional-question-' + json.question_id);
            if( $alternativeAnswer.size() > 0 ){
              $alternativeAnswer.each(function(i, e){
                var $e = $(e).clone()
                if( $(e).hasClass('additional-question-add') ){
                  $questionFieldsetArea.append( $e )
                }else{
                  $questionFieldsetArea.empty().append( $e )
                }

                var selects = $(e).find("select");
                $(selects).each(function(i) {
                var select = this;
                $e.find("select").eq(i).val($(select).val());
                });

                var textareas = $(e).find("textarea");
                $(textareas).each(function(i) {
                var textarea = this;
                $e.find("textarea").eq(i).val($(textareas).val());
                  $e.find("textarea").eq(i).on('change', function(){
                    var val = $(this).val()
                    textareas.eq(i).val(val)
                  })
                });

                var $radios = $e.find(":radio");
                $($radios).each(function(ind, el){
                  $(el).on('change', function(){
                    $(e).find(":radio").eq(ind).attr('checked', 'true')
                  })
                })

                 var $texts = $e.find('[type="text"]');
                $($texts).each(function(ind, el){
                  $(el).on('change', function(){
                    console.log( $(e).find(":radio").eq(ind) )
                    $(e).find('[type="text"]').eq(ind).val( $(el).val() )
                  })
                })

                  var $texts = $e.find('select');
                $($texts).each(function(ind, el){
                  $(el).on('change', function(){
                    console.log( $(e).find(":radio").eq(ind) )
                    $(e).find('select').eq(ind).val( $(el).val() )
                  })
                })





                $e.show();
                $(".integer").inputmask("integer");
                $(".pencent").inputmask('999999999,99', { numericInput: true, placeholder: "_" })

                var $inputs = $e.find(":input");
                $inputs.on('change', function(){
                  var $input = $(this);
                  $(e).find(':input').eq( $input.eq() ).val( $input.val() );

                })

              });
            }

            goToHtml($question, $itemId);
        });

        return true;
    },
    loadAlternatives = function($alternatives, $fieldset, $answerData, $questionTypeId) {

        var $rowAlternative, len = $alternatives.length, $annual, $annualYears, $lenAnnualYears,
            $answers = '', $answer, $letters = ['A', 'B', 'C', 'D'], $isTypeText;

        if ($questionTypeId == QUESTION_TYPE_YESNO_ID) { //Questões desse tipo tem somente 3 alternativas
            len = 3;
        }

        for (var i = 0; i < len; i++) {
            $rowAlternative = $alternatives[i];
            if ($rowAlternative.Value == '') {
                continue;
            }
            $isTypeText = ($rowAlternative.AlternativeTypeId == ALTERNATIVE_TYPE_TEXT_ID);
            $isTypeResult = ($rowAlternative.AlternativeTypeId == ALTERNATIVE_TYPE_RESULT_ID);

            $answer = '<div ' + 'class="' + (i === 0? 'first' : '') + '">'
                + '<label for="ans' + $rowAlternative.Id + '">'
                    + '<b>.' + $letters[i] + '</b>' + $rowAlternative.Value + '</label>'
                + '<input id="ans' + $rowAlternative.Id + '" type="radio" name="alternative_id" value="' + $rowAlternative.Id + '" '
                    + 'class="radio '
                    + ($isTypeText? 'typeText' : '')
                    + ($isTypeResult? 'typeText ' : '')
                    + ' radio' + $letters[i] + '" />';

            if ($isTypeText) {
                $answer += '<div class="divTypeText hide">'
                    + '<label for="ansTypeText' + $rowAlternative.Id + '">'
                    + $rowAlternative.DialogueDescription + '</label>'
                    + '<textarea id="ansTypeText' + $rowAlternative.Id + '" class="answer_value" name="answer_value' + $rowAlternative.Id + '" placeholder="Resposta escrita">'
                    + ((typeof $answerData.answer_value !== 'undefined'
                        && $answerData.alternative_id == $rowAlternative.Id)?
                            $answerData.answer_value : '')
                    +'</textarea>'
                    + '</div>';
            } else if ($isTypeResult && typeof $rowAlternative.AnnualResult !== 'undefined') {
                $annual = $rowAlternative.AnnualResult;
                $annualYears = $annual.AnnualResultData;
                $lenAnnualYears = $annualYears.length;

                $answer += '<div class="divTypeText divTypeResult hide"><label>' + $annual.Value
                    + ' </label><div class="clear"></div>';
                for (var x = 0; x < $lenAnnualYears; x++) {
                    var $annualYearsRow = $annualYears[x];
                    //$annual.Mask
                    $answer += '<label>' + $annualYearsRow.Year
                        +  '<input class="aaresult_value" type="text" name="aaresult_value' + $rowAlternative.Id + '[]" value="' +

                        ((typeof $answerData.annual_result !== 'undefined'
                            && $answerData.alternative_id == $rowAlternative.Id)?
                                $answerData.annual_result[$annualYearsRow.Year] : '')

                        + '" /></label>';
                }
                $answer += '<div class="clear"></div></div>';
            }
            $answer += '</div>';
            $answers += $answer;
        }

     //   $fieldset.append($answers).find('input.aaresult_value').setMask({mask:'99,999.999.999.99', type:'reverse'});

   //.find(' input.aaresult_value').inputmask("mask", {"mask": "999.999.999,99", placeholder:"_", clearMaskOnLostFocus: true});
//    $fieldset.append($answers).find('input.aaresult_value').css('text-align', 'left').inputmask("decimal", { radixPoint: ",", autoGroup: true, groupSeparator: ".", groupSize: 3, digits: 2 });

    //.inputmask('999999999,99', { numericInput: true, placeholder: "_" })

   //.find('input.aaresult_value').inputmask("mask", {"mask": "999.999.999,99", placeholder:"_", clearMaskOnLostFocus: true});
    $fieldset.append($answers).find('input.aaresult_value').css('text-align', 'left').inputmask('999999999,99', { numericInput: true, placeholder: "_" })

        if (typeof $answerData.alternative_id !== 'undefined') {
            $('#ans' + $answerData.alternative_id).prop('checked', true).change();
        }
        return;
    },
    loadTips = function($tips, $question) {

        var $rowAlternative, $len = $tips.length;
        if (!$len) {
            return;
        }
        var $contentHelper = $question.find('div.contentHelp'),
            $tipsContent = '';
        $question.find('a.ajuda').show();

        $contentHelper.find($len == 1? 'button.fright, button.fleft': 'button.fleft').hide();

        for (var i = 0; i < $len; i++) {
            $rowAlternative = $tips[i];
            if ($rowAlternative.Value == '') {
                continue;
            }
            $tipsContent += '<li class="'
                + $rowAlternative.TipTypeTitle
                + ' ' + (i===0? 'visible':'') +'">'
                + '<a target="_blank" href="' + $rowAlternative.Value + '" style="color: #666; font-weight: bold">'
                + '<img style="margin-top: 20px;" src="' + BASE_URL+ '/img/ajax-loader.gif" />'
                + '<br/>Aguarde, carregando ajuda...</a></li>';
        }
        $contentHelper.find('ul.contentHelperTips').append($tipsContent);
        return;
    },
    loadEvaluationResponse = function($question, $fieldset, $evaluationValue, $evaluationImproveValue) {
        if (PERMISSION_EVALUATION_OF_RESPONSE) {
            var $html = '<div class="evaluationArea" style="display:none">';
            $html += '<label>Pontos fortes <br> <textarea placeholder="Faça um comentário" name="evaluation">' + $evaluationValue + '</textarea></label>';
            $html += '<label>Oportunidades de melhoria <br> <textarea placeholder="Faça um comentário" name="evaluationImprove" >' + $evaluationImproveValue + '</textarea></label>';
            $html += '</div>';
            $fieldset.after($html);
        }
    },
    openFirstQuestionNotRespond = function() {
        //abrir questao inicial, primeira nao respondida
        var $firstQuestionOpenned = $listsQuestions.find('li.questionItem:not(.ok)').first();
        if ($firstQuestionOpenned.length === 0) {
            $firstQuestionOpenned = $listsQuestions.find('li.questionItem').first();
        }
        if ($firstQuestionOpenned.length === 0) {
           if ($('div.error').length) {
                alert($('div.error b').text());
            } else {
                alert('Nenhum questão cadastrada para esse questionário.');
            }

            window.location = BASE_URL + '/';
            return;
        }
        var $firstCriterionOpenned = $firstQuestionOpenned.closest('li.criterionItem');
        var $firstBlockOpenned = $firstCriterionOpenned.closest('li.blockItem');
        $firstBlockOpenned.find('a:eq(0)').click();
        $firstCriterionOpenned.find('a:eq(0)').click();
        $firstQuestionOpenned.find('a:eq(0)').click();
    },
    updateProgress = function() {
        var $totalPercent = ($totalQuestionsOkGeneral*97)/$totalQuestionsGeneral;
        $("#percent").animate({width: $totalPercent + '%'}, 700);
    },
    embedMedia = function($tip) {
        $tip.find('a:eq(1)').remove();
        var $tipLink = $tip.find('a:eq(0)').show();
        $tipLink.before($tipLink.clone().hide());
        try {
            $tipLink.embedly({key: $keyEmbedly, maxWidth: 570, maxHeight: 340});
        } catch(e) {}
    };

    return {
        loadQuestion: function($questionId, $isPost, $dataPost) { //publico
            loadQuestion($questionId, $isPost, $dataPost);
            return this;
        },
        init: function() {

            var $main = $('body');

            $contentQuestion = $('#contentQuestion');
            $sideMenu = $main.find('ul.sideMenu:eq(0)');
            $listsQuestions = $sideMenu.find('ul .ulTabQuestionsNumbers');
            $localTextQuestions = $('#questions');
            var $formFieldsetQstnRespond = $localTextQuestions.find('form.qstnRespond fieldset');
            $questionDefault = $localTextQuestions.find('li.systemDefault');
            $questionDefault.find('form.qstnRespond').data('submited', false);

            $sideMenu.on('click', 'a:not(.questionNumberLink)', menuClick);
            $listsQuestions.on('click', 'a', clickQuestion);

            $localTextQuestions
                .on('submit', 'form.qstnRespond', respondQuestion)
                .on('click', 'button.finishContinue', function(e){
                    e.preventDefault();
                    //Hide tela de mensagem de conclusão de critério/bloco
                    var $this = $(this);
                    //console.log($this.closest('div.finishMsg'))
                    $this.closest('div.finishMsg').hide('blind', function(){
                        $this.closest('li').find('div.divisor, form.qstnRespond').show();
                        $("#topQuestionNumber").show();
                    });
                })
                .on('click', 'ul.ulTab1 a', function(e){
                    e.preventDefault();
                })
                .on('click', 'a.closeContentHelp', function(e){
                    e.preventDefault();
                    var $contentHelper = $(this).closest('div.contentHelp');
                    $contentHelper.fadeOut('500');
                    $contentHelper.find('div.embed').remove();
                })
                .on('click', 'a.ajuda', function(e){
                    e.preventDefault();
                    var $contentHelper = $(this).closest('li').find('div.contentHelp');
                    if ($contentHelper.is(':visible')) {
                        $contentHelper.fadeOut('500');
                        $contentHelper.find('div.embed').remove();
                        return;
                    }
                    $contentHelper.fadeIn('500');
                    var $firstTip = $contentHelper.find('ul.contentHelperTips li.video.visible');
                    embedMedia($firstTip);
                })
                .on('click', 'div.contentHelp button.fright', function(e){
                    e.preventDefault();
                    var $this = $(this),
                        $contentHelper = $this.closest('div.contentHelp'),
                        $tipVisible = $contentHelper.find('li.visible').removeClass('visible'),
                        $nextTip = $tipVisible.next().addClass('visible');
                        $tipVisible.find('div.embed').remove();
                    $contentHelper.find('button.fleft').show();
                    if ($nextTip.next().length === 0) { //ultimo
                        $this.hide();
                    }
                    embedMedia($nextTip);
                })
                .on('click', 'div.contentHelp button.fleft', function(e){
                    e.preventDefault();
                    var $this = $(this),
                        $contentHelper = $this.closest('div.contentHelp'),
                        $tipVisible = $contentHelper.find('li.visible').removeClass('visible');
                    $tipVisible.find('div.embed').remove();
                    $contentHelper.find('button.fright').show();
                    var $tip = $tipVisible.prev().addClass('visible');
                    if ($tipVisible.prev().prev().length === 0) { //ultimo
                        $this.hide();
                    }
                    embedMedia($tip);
                });

            $('#back').on('click', loadLastQuestion);
            $('#topAjuda').on('click', function(e){
                e.preventDefault();
                $localTextQuestions.find('li.current:eq(0) div.divisor a.ajuda').trigger('click');
            });

            var $disableKeysElements = 'div.divTypeText textarea, div.divTypeText input';

            $formFieldsetQstnRespond
                .on('change', 'input.radio', updateSelectedStyle)
                .on('focusin', 'input.radio', function() {
                    $(this).prev().addClass('focus');
                })
                .on('focusout', 'input.radio', function() {
                    $(this).prev().removeClass('focus');
                })
                .on('focusin', $disableKeysElements, function() {
                    $(document).off('keydown.ans');
                })
                .on('focusout', $disableKeysElements, function() {
                    $(document).on('keydown.ans', documentKeys);
                });

              $localTextQuestions
                .on('focusin', 'table :input', function() {
                    $(document).off('keydown.ans');
                })
                .on('focusout', 'table :input', function() {
                    $(document).on('keydown.ans', documentKeys);
                });

            $localTextQuestions
                .on('focusin', 'div.evaluationArea textarea', function() {
                    $(document).off('keydown.ans');
                })
                .on('focusout', 'div.evaluationArea textarea', function() {
                    $(document).on('keydown.ans', documentKeys);
                });

            //OKs ico critÃ©rios
            var $crits = $sideMenu.find('li.criterionItem'), $len = $crits.length,
                $crit, $critTotalQuestions, $criTotalQuestionsOk;
            for (var $i = 0; $i < $len; $i++) {
                $crit = $($crits[$i]);
                $critTotalQuestions = $crit.attr('total-questions');
                $criTotalQuestionsOk = $crit.find('li.questionItem.ok').length;
                if ($critTotalQuestions == $criTotalQuestionsOk) {
                    $crit.addClass('ok');
                }
            }

            //OKs ico blocos
            var $blks = $sideMenu.find('li.blockItem'), $lenB = $blks.length,
                $blk, $blkTotalCriterions, $blkTotalCriterionsOk;
            for (var $c = 0; $c < $lenB; $c++) {
                $blk = $($blks[$c]);
                $blkTotalCriterions = $blk.find('li.questionItem').length;
                $totalQuestionsGeneral += $blkTotalCriterions;
                $blk.attr('total-questions', $blkTotalCriterions)
                    .attr('total-criterions', $blk.find('li.criterionItem').length);
                $blkTotalCriterionsOk = $blk.find('li.questionItem.ok').length;
                $totalQuestionsOkGeneral += $blkTotalCriterionsOk;
                if ($blkTotalCriterions == $blkTotalCriterionsOk) {
                    $blk.addClass('ok');
                }
            }

            $(document).off('keypress').on('keydown.ans', documentKeys);

            openFirstQuestionNotRespond();
            updateProgress();

            $('#qstnTitle').text($sideMenu.find('li.qstnItem a.currentLnk')
                .closest('li').attr('data-item-description'));

            if( $('.sideMenu').size() > 0 ){
              window.setTimeout(function(){
                $('.sideMenu').height($('#main').height());
                //$('#footer-int').css({paddingLeft: 250, border: '1px solid red', clear: 'both', marginTop: -60})//.appendTo('section')
              }, 1000);

              $('body').resize(function() {
                $('.sideMenu').height($('#main').height());
              })

            }

            return;
        }
    }

}());

$.fn.ready(function() {
    respondModule.init();
});
