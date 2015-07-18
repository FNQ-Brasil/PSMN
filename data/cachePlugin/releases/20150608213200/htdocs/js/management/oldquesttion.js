
var Qstn = {

    
    init: function() {

    


   
        
        $('#content a.newBlock').click(Qstn.newBlock);
        $('ul.qstn a.newCriterion').click(Qstn.newCriterion);      
        $('ul.qstn a.newQuestion').click(Qstn.newQuestion);

       $('ul.qstn input.title, ul.qstn textarea:not(.secondTextarea)').readOnly(true);
        
        $('ul.qstn textarea:not(.secondTextarea)').on({
            'keyup': Qstn.textAreaResize,
            'reset': Qstn.textAreaResize,
            'blur': function(e) {
                textareaContent = $(e.target).val();
                textareaContent = textareaContent.replace(/\n\n?/g, "\n");
                textareaContent = textareaContent.replace(/\n/g, "\n").replace(/\n\n+/g, "\n");
                $(e.target).val(textareaContent);
            }
        });
        
        $('ul.qstn textarea').on('blur', Qstn.textAreaResize);
    },
    

    
    newBlock: function() {

        var block = $('ul.qstn li.block.systemDefault:eq(0)').clone(true).removeClass('systemDefault');
        $('ul.qstn').append(block.show());

        block.find('a.expand:eq(0)').trigger('click');

        return false;
    },

    newCriterion: function() {
        var block = $(this).closest('li.block'),
            criterionsBlock = block.find('ul.criterions');
            newCriterionObj = $('ul.qstn li.block.systemDefault ul.criterions li.criterion:eq(0)')
                .clone(true).removeClass('systemDefault');
                console.log(Qstn.criterionDefault);
        criterionsBlock.show().append(newCriterionObj.show());
        return false;
    },
    
    newQuestion: function() {
        $(this).closest('li.criterion').find('ul.questions:eq(0)').append(
            $('ul.qstn li.block.systemDefault ul.questions li.question:eq(0)')
                .clone(true).removeClass('systemDefault').show()
         );
        return false;
    },
    
    textAreaResize: function (e) {
        hiddenTextarea = 'hidden' + $(this).attr('id');

        if ($('#' + hiddenTextarea).size() == 0) {
            hiddenDiv = $('<div id="' + hiddenTextarea +'"></div>');
            hiddenDiv.addClass('hiddendiv');
            if ($(e.target).hasClass('secondTextarea')) {
                hiddenDiv.addClass('secondTextarea');
            }
            $(this).closest('form').after(hiddenDiv);
        }

        hiddenDiv = $('#' + hiddenTextarea);
        textareaContent = $(e.target).val();
        textareaContent = textareaContent.replace(/\n/g, '<br>');
        hiddenDiv.html(textareaContent + '<br class="lbr">');  
        h = hiddenDiv.height();

        $(e.target).css('height', (h>=$(e.target).attr('data-minheight'))? h+12 : h);
    },
    
    expand: function(e) {
        var icosChild = $(this).children('span.ico'),
            parent = $(this).closest('li'),
            formChild = parent.children('form:eq(0)'),
            show = icosChild.hasClass('plus');
            
        parent.children('ul').toggle(show);
        parent.children('.add').toggleClass('hide');
        //reseta o conteúdo dos textarea
        parent.find('textarea').trigger('reset');

        //mostra tudo do formulario
        formChild.find('*').readOnly(!show).toggle(show);
        //mostra botão mover
        formChild.children('.move:eq(0)').toggleClass('hidden');

        //formChild.children('.itemContent').children('textarea.autohide, label.apoio, button').toggle();

        icosChild.toggleClass('plus').toggleClass('minus');
        return false;
    }
};