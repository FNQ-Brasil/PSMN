/* Javascript OO Module Pattern */

var qstnModule = (function () {
    var $qstn, $blockDefault, $criterionDefault, $questionDefault;
    
    var $sortableParams = {
        distance: 3,
        delay: 20,
        opacity: 0.8,
        cursor: 'move',
        forceHelperSize: true,
        axis: 'y',
        update: function() {            },
        start: function(e, ui) {
            $('a.move:visible, a.delete:visible, a.expand:visible').not($(ui.item).find('form a.move:eq(0)'))
                .addClass('vHidden');
        },
        stop: function(e, ui) {
             $('a.vHidden').removeClass('vHidden');
        }
    };
    
    var initSortable = function(elemen) {
        elemen.sortable("destroy");
        elemen.sortable($sortableParams);
        elemen.sortable('option', 'handle', '.move:eq(0)');
    },
    
    expand = function() {
        var $btExpand = $(this),
            $btExpandIco = $btExpand.find('span'),
            $item = $btExpand.closest('li'),
            $itemList = $item.closest('ul'),
            $itemParentList = $item.closest('ul').parent().first(),
            $brothersItem = $item.siblings('li').not('.systemDefault'),
            $btDelete = $item.find('a.delete:eq(0)'),
            $btMove = $item.find('a.move:eq(0)'),
            $btNewItem = $item.children('a.add:eq(0)'),
            $form = $item.find('form:eq(0)'),
            $adicionalFields = $item.find('fieldset.adicionalFields:eq(0)')
            $bool = $adicionalFields.not(':visible');

            if (($bool.size() == 0 && $form.hasClass('changed') && confirm('Existem dados alterados não salvos, deseja realmente sair?'))
                || ($bool.size() == 0 && !$form.hasClass('changed') && $item.hasClass('newItem'))) {
                $form.removeClass("changed");
                if ($item.hasClass('newItem')) {
                    $item.remove();
                };
            } else if ($bool.size() == 0 && $form.hasClass('changed'))  { //não quis sair.
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

            //reseta o conteúdo dos textarea
            $item.find('textarea').trigger('toReset');
            $form.find('input.title, textarea.title').readOnly(!$bool.size());

            initSortable($item.find('ul:eq(0)'));

            if ($bool.size()) {
                $form.find('textarea, input[type=text]')
                    .bind('keyup keydown keypress change blur', detectFormChange);
                return false;
            } else {
                if ($form.hasClass('recentSaved')) {
                    $form.removeClass('recentSaved');
                } else {
                    $form.trigger('reset');
                }
            }

            $form.removeClass('changed').find('textarea, input[type=text]').each(function() {                    
                jQuery.removeData(this, 'lastvalue');
            });

        return false;
    },

    detectFormChange = function() {

        var $this = $(this);
        
        if (typeof(jQuery.data(this, 'lastvalue')) == 'undefined') {
            jQuery.data(this, 'lastvalue', $this.val());
        } else {
            $fieldData = jQuery.data(this, 'lastvalue');

            if ($this.val() != $fieldData) {
                $this.closest('form').addClass('changed');
            }
            jQuery.data(this, 'lastvalue', $this.val());
        }
      },

    newBlock = function() {
        var $block = $blockDefault.clone(true).removeClass('systemDefault');
        $block.find('ul li').remove();
        $qstn.append($block.addClass('newItem'));
        $block.find('a.expand:eq(0)').trigger('click');
        $block.find('input:eq(0), textarea:eq(0)').first().focus();
        return false;
    },

    newCriterion = function() {
        var $block = $(this).closest('li.block');
        if ($block.hasClass('newItem')) {
            alert('Para criar um critério é necessário salvar os dados do novo bloco.')
            return false;
        }

        var $criterionsBlock = $block.find('ul.criterions'),
            $criterion = $criterionDefault.clone(true);

        $criterion.find('ul li').remove();
        $criterionsBlock.append($criterion.addClass('newItem').show());
        $criterion.find('a.expand:eq(0)').trigger('click');
        $criterion.find('input:eq(0), textarea:eq(0)').first().focus();
        return false;
    },
    
    newQuestion = function() {
        var $criterion =  $(this).closest('li.criterion'),
            $questionsCriterion = $criterion.find('ul.questions')
            $question = $questionDefault.clone(true);
        $questionsCriterion.append($question.addClass('newItem').show());
        $question.find('a.expand:eq(0)').trigger('click');
        $question.find('input:eq(0), textarea:eq(0)').first().focus();
        return false;
    },

    textAreaResize = function (e) {
        $hiddenTextarea = 'hidden' + $(this).attr('id');
        $target = $(e.target);

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
        $hiddenDiv.html($textareaContent + '<br class="lbr">');  
        $h = $hiddenDiv.height();

        $target.css('height', ($h>=$target.attr('data-minheight'))? $h+12 : $h);
    },

    submitForm = function(e) {
        
         $(this).ajaxSubmit({
            dataType: 'json',
            beforeSubmit: function() {
                $form.find('.error:eq(0), .success:eq(0)').hide();
                return true;
            },
            success: submitFormSuccess
        }); 

        return false;
    },

    submitFormSuccess = function(json, statusText, xhr, $form)  {
    /*    if (statusText != 'success' || json.messageError) {
            $form.find('.error:eq(0)').show('blind').find('b').text(json.messageError);
            return;
        }
*/
        var $item = $form.closest('li').removeClass('newItem');

        $form.find('.success:eq(0)').show('blind');

        $form.addClass('recentSaved').removeClass('changed').find('textarea, input[type=text]').each(function() {                    
            jQuery.removeData(this, 'lastvalue');
        });
    },

    disableContentForm = function($i, item) {

//console.log($(item));
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
            $criterionDefault = $blockDefault.find('ul.criterions li.criterion');
            $questionDefault = $criterionDefault.find('ul.questions li.question');

            $buttons = {
                'move': $qstn.find('a.move').click(function(e){
                    return false;
                }),
                'expand': $qstn.find('a.expand, button.btReset').click(expand),
                'newBlock': $('#content a.newBlock').click(newBlock),
                'newCriterion': $qstn.find('a.newCriterion').click(newCriterion),
                'newQuestion': $qstn.find('a.newQuestion').click(newQuestion),
                'textareaAuto': $qstn.find('textarea:not(.secondTextarea)')
            };

            $forms = $qstn.find('form').trigger('reset').bind('submit', submitForm);

            $forms.find('textarea, button, input').prop('disabled', false );

            $qstn.children('li').find('input.title, textarea.title').readOnly(true);

            $buttons['textareaAuto'].bind({
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

            $buttons['textareaAuto'].bind('blur', textAreaResize);

            initSortable($('ul.qstn'));

            return this;
        }
    };

}());

$(function() {
    try {
        qstnModule.init();
    } catch(e) {
        /*if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }*/
        throw e;
    }
});