/* Javascript OO Module Pattern */

var qstnModule = (function () {
    var $qstn, $blockDefault, $criterionDefault,
        $questionDefault, $alternativeDefault, $helperDefault;

    var moveItem = function(event, ui) {
        $newOrder = ui.item.index();
        $item = $(ui.item);
        $form = $item.find('form:eq(0)');
       
        if ($form.size() == 0) {
            return;
        }
        
        $qstnId = $form.find('input[name=questionnaire_id]').val();
        $itemId = $form.find('input[name=item_id]').val();
        $itemType = $form.find('input[name=item_type]').val();

        $itemParent = $itemType == 'block'?
            $qstn : $item.parents('li:eq(0)');
        $itemParentId = $itemType == 'block'?
            $qstnId : $itemParent.find('input[name=item_id]:eq(0)').val();

        $newOrder = ($itemType == 'block')? $newOrder : $newOrder+1;

        $.ajax({
            url: BASE_URL + '/management/' + $itemType + '/move/id/' + $itemId + '/format/json',
            type: 'post',
            dataType: 'json',
            data: {
                new_position_designation: $newOrder, parent_id: $itemParentId
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error move');
                }
                
                for(var i in json.getIdDesignation) {
                    var $rowOrder = json.getIdDesignation[i],
                        $form = $itemParent.find('li form input[value=' + $rowOrder.Id +']:eq(0)')
                            .closest('form');
                    $form.find('input[name=designation]').val($rowOrder.Designation);
                }
            }
        });
    };

    var initSortable = function(elemen) {


       if (elemen.hasClass('ui-sortable')) {
           elemen.sortable('destroy');
       }
       
        elemen.sortable({
            distance: 3,
            delay: 20,
            opacity: 0.8,
            cursor: 'move',
            forceHelperSize: true,
            axis: 'y',
            start: function(e, ui) {
                $('a.move:visible, a.delete:visible, a.expand:visible')
                    .not($(ui.item).find('form a.move:eq(0)'))
                    .addClass('vHidden');
            },
            stop: function(e, ui) {
                 $('a.vHidden').removeClass('vHidden');
            },
            update: moveItem
        });
        elemen.sortable('option', 'handle', '.move:eq(0)');
    },
    
    expand = function() {
        var $this = $(this),
            $item = $this.closest('li'),
            $btExpandIco = $item.find('a.expand span'),
            $itemList = $item.closest('ul'),
            $itemParentList = $item.closest('ul').parent().first(),
            $brothersItem = $item.siblings('li').not('.systemDefault'),
            $btDelete = $item.find('a.delete:eq(0)'),
            $btMove = $item.find('a.move:eq(0)'),
            $btNewItem = $item.children('a.add:eq(0)'),
            $form = $item.find('form:eq(0)'),
            $adicionalFields = $item.find('fieldset.adicionalFields:eq(0)');

        $bool = $adicionalFields.not(':visible');
            
        if (($bool.size() == 0 && $form.hasClass('changed') && confirm('Existem dados alterados não salvos, deseja realmente sair?'))
            || ($bool.size() == 0 && !$form.hasClass('changed') && $item.hasClass('newItem'))) {
            $form.removeClass("changed");
            if ($item.hasClass('newItem')) {
                $item.remove();
            };
        } else if ($bool.size() == 0 && $form.hasClass('changed'))  { //nÃ£o quis sair.
            return false;
        }

        $btExpandIco.toggleClass('minus');
        $item.toggleClass('selected');
        $btDelete.toggle();
        $btNewItem.toggleClass('hide', 'slow');

        $adicionalFields.toggle('blind');

        $item.find('ul li:not(.alternative)').toggle();

        $itemList.siblings('a.add').toggleClass('hidden');

        $.merge($brothersItem, $itemParentList).each(disableContentForm); 

        //reseta o conteúdo dos textareas
        $item.find('textarea').trigger('toReset');
        
        $form.find('input.title, textarea.title').readOnly(!$bool.size());

        initSortable($item.find('ul:eq(0)'));

        if ($bool.size()) {
            $form.find('textarea, input[type=text], select')
                .bind('keyup keydown keypress change blur focus', detectFormChange);

            loadChildItems.init($item);
            
            $itemScroll = $item;
            setTimeout(function(){
                $.scrollTo( $itemScroll, 400, {offset: -30} );
            }, 500);

            return false;
        }

        if ($form.hasClass('recentSaved')) {
            //console.log($form);
            $form.find('textarea, input').each(function() {
                var $this = $(this);
                $this.prop('defaultValue', $this.val());
            });
            $form.removeClass('recentSaved');

        } else {
            //console.log($form, 'reset');
            $form.trigger('reset');
        }

        $itemScroll = $itemParentList;
        setTimeout(function(){
            $.scrollTo( $itemScroll, 400, {offset: -30} );
        }, 500);

        $form.removeClass('changed').find('textarea, input[type=text]').each(function() {                    
            jQuery.removeData(this, 'lastvalue');
        });

        return false;
    },

    loadChildItems = {
        init: function($obj) {
            $item = $obj;
            $form = $item.find('form:eq(0)');
            $itemId = $form.find('input[name=item_id]').val(),
            $itemType = $form.find('input[name=item_type]').val();
            
            if ($itemId == '') {
                return;
            }

            switch ($itemType) {
                case 'block':
                    this.loadCriterions();
                break;
                case 'criterion':
                    this.loadQuestions();
                break;
                case 'question':
                    this.loadQuestionComplements();
                break;
            }
        },

        loadCriterions: function() {
            $block = $item;
            $blockId = $itemId;
            $criterionsBlock = $block.find('ul:eq(0)').empty();

            $.ajax({
                url: BASE_URL + '/management/criterion/index/block_id/' + $blockId + '/format/json',
                type: 'get',
                cache: false,
                dataType: 'json',
                success: function(json, $statusText) {
                    if (!$statusText == 'success' || !json.getAllCriterion) {
                        throw 'Error get criterions';
                    }

                    for (var i in json.getAllCriterion) {
                        var $criterion = $criterionDefault.clone(true),
                            $rowData = json.getAllCriterion[i];

                        $criterion.find('ul li').remove();
                        $criterionsBlock.append($criterion.show());
                        $criterionForm = $criterion.find('form:eq(0)').addClass('recentSaved');
                        $criterionId = $rowData.Id;

                        $criterionForm.attr(
                            'action', $criterionForm.attr('action').replace('insert', 'edit') + '/id/' + $criterionId
                        );
                        $criterionForm.find('input[name=block_id]').val($blockId);
                        $criterionForm.find('input[name=value]').val($rowData.Value);
                        $criterionForm.find('input[name=designation]').val($rowData.Designation);
                        $criterionForm.find('input[name=item_id]').val($rowData.Id);
                        $criterionForm.find('textarea[name=long_description]')
                            .val($rowData.LongDescription);
                    }
                }
            });
        },

        loadQuestions: function() {
            $criterion = $item;
            $criterionId = $itemId;
            $questionsBlock = $criterion.find('ul:eq(0)').empty();

            $.ajax({
                url: BASE_URL + '/management/question/index/criterion_id/' + $criterionId + '/format/json',
                type: 'get',
                cache: false,
                dataType: 'json',
                success: function(json, $statusText) {
                    if (!$statusText == 'success' || !json.getAllQuestion) {
                        throw 'Error get questions';
                    }

                    for (var i in json.getAllQuestion) {
                        var $question = $questionDefault.clone(true),
                            $rowData = json.getAllQuestion[i];

                        $question.find('ul li').remove();
                        $questionsBlock.append($question.show());
                        $qstnForm = $question.find('form:eq(0)').addClass('recentSaved');
                        $qstnId = $rowData.Id;
                        
                        $qstnForm.attr(
                            'action', $qstnForm.attr('action').replace('insert', 'edit') + '/id/' + $qstnId
                        );
                        $qstnForm.find('input[name=designation]').val($rowData.Designation);
                        $qstnForm.find('input[name=item_id]').val($qstnId);
                        $qstnForm.find('input[name=parent_id]').val($criterionId);                       
                        $qstnForm.find('input[name=status]').val($rowData.Status);
                        $qstnForm.find('input[name=version]').val($rowData.Version);

                        $qstnForm.find('select[name=question_type_id]').val($rowData.QuestionTypeId);
                            //.trigger('change');
                        
                        $qstnForm.find('textarea[name=value]').val($rowData.Value);
                        $qstnForm.find('textarea[name=supporting_text]').val($rowData.SupportingText);
                        //$criterion.find('.opt').hide();

                        $qstnForm.find('textarea').trigger('toReset');
                        //console.log('1', $qstnForm)
                    }
                }
            });
        },
        
        loadQuestionComplements: function() {
            $question = $item;
            $questionId = $itemId;
            $alternativesBlock = $question.find('ul.alternatives:eq(0)').empty();
            $helpersBlock = $question.find('ul.helpers:eq(0)').empty();

            $.ajax({
                url: BASE_URL + '/management/alternative/index/question_id/' + $questionId + '/format/json',
                type: 'get',
                cache: false,
                dataType: 'json',
                success: function(json, $statusText) {
                    if (!$statusText == 'success' || !json.getAllAlternative) {
                        throw 'Errxor get questions';
                    }

                    for (var i in json.getAllAlternative) {
                        var $alternative = $alternativeDefault.clone(true),
                            $rowData = json.getAllAlternative[i];

                        $alternativesBlock.append($alternative.show());
                        $alternative.find('input.alternativeItem').val($rowData.Value);

                        $alternative.find('select.alternativeType')
                            .val($rowData.AlternativeTypeId).trigger('change');
                        $alternative.find('input.titleAnsWriteVal').val($rowData.DialogueDescription);

                        $alternative.find('textarea.alternativeFeedbackDefault').val($rowData.FeedbackDefault);
                        
                        var $scoreLevel = ($rowData.ScoreLevel.length === 6)?
                            '0'+$rowData.ScoreLevel: $rowData.ScoreLevel;
                        
                        $alternative.find('input.alternativeScoreLevel').val($scoreLevel);
                        
                        if (typeof $rowData.AnnualResult !== 'undefined') {
                            $alternative.find('input.titleAnnualResultVal')
                                .val($rowData.AnnualResult.Value);
                            $alternative.find('select.mascAnnualResult')
                                .val($rowData.AnnualResult.Mask);
                        }

                        $alternative.find('textarea, input[type=text], select')
                            .bind('keyup keydown keypress change blur focus', detectFormChange);
                            
                        $alternative.find('input.pesoItem').setMask('99.9999');
                    }

                    for (var i in json.getAllQuestionTip) {
                        var $rowData2 = json.getAllQuestionTip[i],
                            $helper = $helperDefault[$rowData2.TipTypeTitle].clone(true);

                        $helpersBlock.append($helper.show());
                        $helper.find('input.helperItem').val($rowData2.Value)
                            .bind('keyup keydown keypress change blur focus', detectFormChange);
                    }

                    var $selectQuestionType = $question.find('select.question_type');
                    if (QUESTION_TYPE_YESNO_ID==$selectQuestionType[0].value) {
                        $selectQuestionType.trigger('change');
                    }
                }
            });
        }
    },

    detectFormChange = function(e) {
        var $this = $(this);

        if (typeof(jQuery.data(this, 'lastvalue')) == 'undefined') {
            jQuery.data(this, 'lastvalue', $this.val());
            return;
        }
        if (e.type == 'focus') {
            return;
        }
        $fieldData = jQuery.data(this, 'lastvalue');

        if ($this.val() != $fieldData) {
            $this.closest('form').addClass('changed').find('div.notice').fadeOut();
        }
        jQuery.data(this, 'lastvalue', $this.val());
    },
      
    newItem = {
        newBlock: function() {
            var $block = $blockDefault.clone(true).removeClass('systemDefault');
            $block.find('ul li').remove();
            $qstn.append($block.addClass('newItem'));
            $block.find('a.expand:eq(0)').trigger('click');

            $block.find('input[type=text]:eq(0), textarea:eq(0)').first().focus();
            return false;
        },

        newCriterion: function() {
            var $block = $(this).closest('li.block');
            if ($block.hasClass('newItem')) {
                alert('Para criar um critério é necessário salvar os dados do novo bloco.')
                return false;
            }
            var $blockForm = $block.find('form:eq(0)'),
                $blockId = $blockForm.find('input[name=item_id]').val();
            var $criterionsBlock = $block.find('ul.criterions'),
                $criterion = $criterionDefault.clone(true);

            $criterion.find('ul li').remove();
            $criterionsBlock.append($criterion.addClass('newItem').show());
            $criterion.find('a.expand:eq(0)').trigger('click');
            $criterion.find('input[name=block_id]').val($blockId);

            $criterion.find('input[type=text]:eq(0), textarea:eq(0)').first().focus();
            return false;
        },

        newQuestion: function() {
            var $criterion =  $(this).closest('li.criterion');
            if ($criterion.hasClass('newItem')) {
                alert('Para criar uma questão é necessário salvar os dados do novo critério.')
                return false;
            }
            var $criterionForm = $criterion.find('form:eq(0)'),
                $criterionId = $criterionForm.find('input[name=item_id]').val(),
                $questionsCriterion = $criterion.find('ul.questions')
                $question = $questionDefault.clone(true);

            $question.find('ul.helpers li').remove();
            $questionsCriterion.append($question.addClass('newItem').show());
            $question.find('a.expand:eq(0)').trigger('click');
            $question.find('input[name=parent_id]').val($criterionId);

            $question.find('input[type=text]:eq(0), textarea:eq(0)').first().focus();
            $question.find('input.pesoItem').setMask('99.9999');

            return false;
        }
    },

    textAreaResize = function (e) {
       /* $hiddenTextarea = 'hidden' + $(this).attr('id');
        $target = $(this);
        
        //console.log($target);
        if ($('#' + $hiddenTextarea).size() == 0) {
            $hiddenDiv = $('<div id="' + $hiddenTextarea +'"></div>');
            $hiddenDiv.addClass('hiddendiv');
            if ($target.hasClass('textareaQuestion')) {
                $hiddenDiv.addClass('textareaQuestion');
            }
            $(this).closest('form').after($hiddenDiv);
        }

        $hiddenDiv = $('#' + $hiddenTextarea);
        $textareaContent = $target.val();
        $textareaContent = $textareaContent.replace(/\n/g, '<br>');
        $hiddenDiv.html($textareaContent + '<br class="lbr">');  */
        //$h = $hiddenDiv.height();
           
        /*if ($h != 0) {
            $target.css('height', ($h>=$target.attr('data-minheight'))? $h+12 : $h);
        }*/
    },

    submitForm = function(e) {
         $c = $(this).formSerialize();
       // alert('Questão sendo alterada. Clique OK para continuar e aguarde.');
        e.preventDefault();
        
         $(this).ajaxSubmit({
            dataType: 'json',
            beforeSubmit: function() {
                $form.find('.error:eq(0), .success:eq(0)').hide();
                console.log($form.find('.error:eq(0), .success:eq(0)'))
                return true;
            },
            success: submitFormSuccess
        }); 
    },

    submitFormSuccess = function(json, $statusText, xhr, $form)  {
        var $messageError = $form.find('.error:eq(0)').hide(),
            $messageSuccess = $form.find('.success:eq(0)').hide();
            //$itemType = 'block';

        if (!($statusText == 'success') || !json.itemSuccess) {
            $messageError.show('blind').find('b').text(json.messageError);
            return;
        }

        var $item = $form.closest('li').removeClass('newItem');

        if (json.lastInsertId) {
            $createAction = $form.attr('action');
            $form.attr('action', $createAction.replace('insert', 'edit') + '/id/' + json.lastInsertId);
            $form.find('input[name=item_id]').val(json.lastInsertId);
            $form.find('input[name=designation]').val(json.lastDesignation);
        }

        $messageSuccess.show('blind');

        $form.addClass('recentSaved').removeClass('changed').find('textarea, input[type=text]').each(function() {                    
            jQuery.removeData(this, 'lastvalue');
        });
    },

    deleteItem = {
        init: function() { //var globais sem var
            $this = $(this),
            $form = $this.closest('form');
            $item = $this.closest('li'); //alterado de $form.closest('li');
            $itemId = $form.find('input[name=item_id]').val();
            $itemType = $form.find('input[name=item_type]').val();
            $itemParent = $item.parents('li:eq(0)');

            if ($item.hasClass('alternative') && window.confirm('Deseja realmente excluir?')) {
                $item.remove();
                return false;
            }
            
            if ($itemId == '' || !window.confirm('Deseja realmente excluir?')) {
                return false;
            }

            $.ajax({
                url: BASE_URL + '/management/' + $itemType + '/delete/id/' + $itemId + '/format/json',
                type: 'post',
                dataType: 'json',
                data: {},
                success: deleteItem.deleteSuccess
            });  

            return false;
        },

        deleteSuccess: function(json, $statusText) {
            if (!($statusText == 'success') || !json.itemSuccess) {
                alert(json.messageError);
                return;
            }
            
            $itemDelete = $item;
            $itemDeleteParent = $itemParent;
            $itemDelete.find('a.expand:eq(0)').trigger('click');
            $itemDelete.remove();

            loadChildItems.init($itemDeleteParent);
        }
    },

    disableContentForm = function($i, item) {

        $item = $(item); 
        $item.find('a.expand:eq(0), a.delete:eq(0)').toggleClass('hidden');
        $item.find('a.move:eq(0)').toggle();
        $form = $(item).find('form:eq(0)');
        $form.toggleClass('opacity45');

        $form.find('textarea, button, input').prop("disabled", $bool.size()? true : false);

    };
  
    return {
        init: function() {
            
            $qstn = $('#main ul.qstn');
            $blockDefault = $qstn.find('li.block.systemDefault');
            $criterionDefault = $blockDefault.find('ul.criterions li.criterion:eq(0)');
            $questionDefault = $criterionDefault.find('ul.questions li.question:eq(0)');
            $alternativeDefault = $questionDefault.find('ul.alternatives li.alternative:eq(0)');

            $helperDefault = new Array();           
            $helperDefault['video'] = $questionDefault.find('ul.helpers li.helperVideo:eq(0)'),
            $helperDefault['image'] = $questionDefault.find('ul.helpers li.helperImage:eq(0)'),
            $helperDefault['text'] = $questionDefault.find('ul.helpers li.helperText:eq(0)');
            
            var $blocks = $qstn.find('li.block'),
                $criterions = $blocks.find('ul.criterions li.criterion'),
                $questions = $criterions.find('ul.questions li.question'),
                $helpers = $questions.find('ul.helpers li.helper'),
                $alternatives = $questions.find('ul.alternatives li.alternative');

            $buttons = {
                'move': $qstn.find('a.move').click(function(e){
                    return false;
                }),
                'expand': $qstn.find('a.expand, button.btReset').click(expand),
                'newBlock': $('#content a.newBlock').click(newItem.newBlock),
                'newCriterion': $qstn.find('a.newCriterion').click(newItem.newCriterion),
                'newQuestion': $qstn.find('a.newQuestion').click(newItem.newQuestion),
                'textareaAuto': $qstn.find('textarea:not(.secondTextarea)'),
                'delete': $qstn.find('a.delete').click(deleteItem.init)
            };

            $forms = $qstn.find('form');
            $forms.trigger('reset');
            $forms.on('submit', submitForm);

            $forms.find('textarea, button, input').prop('disabled', false );

            $qstn.children('li').find('input.title, textarea.title').readOnly(true);

            $buttons['textareaAuto'].on({
                'keyup': textAreaResize,
                'toReset': textAreaResize,
                'blur': function(e) {
                    $target = $(e.target);
                    $textareaContent = $target.val();
                    $textareaContent2 = $textareaContent.replace(/\n\n?/g, "\n");
                    $textareaContent2 = $textareaContent2.replace(/\n/g, "\n").replace(/\n\n+/g, "\n");
                    if ($textareaContent2 != $textareaContent) {
                        $target.val($textareaContent);
                    }
                }
            });

            $buttons['textareaAuto'].on('blur', textAreaResize);

            $questions
                .on('change', 'select.question_type', function(e) {
                    e.preventDefault();
                    var $this = $(this),
                        $form = $this.closest('form'),
                        $alternatives = $form.find('ul.alternatives:eq(0), .opt');
                        $alternativesItens = $alternatives.find('input.alternativeItem');
                    if (QUESTION_TYPE_YESNO_ID==$this.val()) {
                        $form.find('div.opt').hide();
                        $alternatives
                            .removeClass('alternativeItemABCD')
                            .addClass('alternativeItemYesNo')
                            .find("select.alternativeType").val('1')
                            .trigger('change').hide();
                        $alternativesItens.readOnly(true);
                        $alternatives.find('a.move').hide();
                        //.prop({readOnly: true});
                        $alternativesItens[0].value = 'Dificilmente acontece';
                        $alternativesItens[1].value = 'Ás vezes acontece';
                        $alternativesItens[2].value = 'Sempre acontece';
                        $alternativesItens[3].value = 'x';
                        $alternativesItens.eq(3).closest('li').hide();
                        //    .find('input, textarea').prop("disabled", true);
                    } else if (QUESTION_TYPE_ABCD_ID==$this.val()) {
                        $form.find('div.opt').show();
                        $alternatives
                            .removeClass('alternativeItemYesNo')
                            .addClass('alternativeItemABCD')
                            .find("select.alternativeType").val('1')
                            .trigger('change').show();
                        $alternatives.find('a.move').show();
                        $alternativesItens.readOnly(false);
                            //prop({readOnly: false});
                        $alternativesItens[0].value = '';
                        $alternativesItens[1].value = '';
                        $alternativesItens[2].value = '';
                        $alternativesItens[3].value = '';
                        $alternativesItens.eq(3).closest('li').show();
                            //    .find('input, textarea').prop("disabled", false);
                    }
                })
                .on('click', 'button.addHelper', function(e) {
                    e.preventDefault();
                    $this = $(this);
                    $form = $this.closest('form');
                    $helpers = $form.find('ul.helpers:eq(0)');

                    $helperItem = $questionDefault.find('li.helper' + $this.attr('data-addHelperType') +':eq(0)').clone(true);
                    $helpers.append($helperItem);

                    initSortable($helpers);

                    $helperItem.find('input:eq(0)').focus();
                    return false;
                });

            $helpers
                .on('click', 'button.small', function(e) {
                    e.preventDefault();
                    $(this).closest('li').find('input:eq(0)').focus();
                    return false;
                });

            $alternatives
                .on('change', 'select.alternativeType', function(e) {
                    e.preventDefault();
                    var $this = $(this),
                        $item = $this.closest('li');
                    $item.find('input.titleAnsWriteVal, a.titleAnsWriteLnk')
                        .toggle(this.value == ALTERNATIVE_TYPE_TEXT_ID);
                    $item.find('input.titleAnnualResultVal, a.titleAnnualResultLnk, select.mascAnnualResult')
                        .toggle(this.value == ALTERNATIVE_TYPE_RESULT_ID);
                })
                .on('click', 'a.titleAnsWriteLnk', function(e) {
                    e.preventDefault();
                    var $titleAns = $(this).closest('li').find('input.titleAnsWriteVal');
                    var $prompt = prompt('Título da resposta escrita:', $titleAns[0].value);
                    if ($prompt!==null) {
                        $titleAns[0].value = $prompt;
                    }
                })
                .on('click', 'a.titleAnnualResultLnk', function(e) {
                    e.preventDefault();
                    var $titleAns = $(this).closest('li').find('input.titleAnnualResultVal');
                    var $prompt = prompt('Título da resposta com resultados:', $titleAns[0].value);
                    if ($prompt!==null) {
                        $titleAns[0].value = $prompt;
                    }
                });

            initSortable($('ul.qstn'));

            //$.prettyLoader();
            
            return this;
        }
    };

}());

$(function() {
    try {
        $('#overlay1').show();
        qstnModule.init();
        $('#overlay1').hide();

    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Mpe.error(e.message);
            return;
        }
        throw e;
    }
});
