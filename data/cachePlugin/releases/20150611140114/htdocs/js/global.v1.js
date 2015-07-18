$(function() {
    try {
        $("#menu a").click(function() {
            liClosest = $(this).closest('li');
            if (liClosest.hasClass('openned')) {
                liClosest.removeClass('openned');
                liClosest.find('ul:eq(0)').hide();
            } else {
                liClosest.addClass('openned');
                liClosest.find('ul:eq(0)').show();
            }
            //console.log(liClosest);
            return false;
        });
        
        $('#menu ul.ulTabQuestionsNumbers a').die().click(function(){
            $('#menu ul.ulTabQuestionsNumbers li.current').removeClass('current');
            $(this).closest('li').addClass('current');
            return false;
        });

        c = $(this).scrollTop();
        if (c > 181) {
            $('#nextQ').css('top', c-131 + "px");
        }
        if (c > 273) {
            $('#divisor').css('top', c-155 + "px");//43
        }
        $(window).scroll(function() {
            c = $(this).scrollTop();
            $('#nextQ').css('top', (c > 181)? (c-131) : 27 + "px");
            $('#divisor').css('top', (c > 273)? (c-155) : 112 + "px");
        });
        
        $('#respostas :radio').focus(updateSelectedStyle);
        $('#respostas :radio').blur(updateSelectedStyle);
        $('#respostas :radio').change(updateSelectedStyle);
    $('a.tip').click(function(e) {	
        $('#tip').fadeIn('500');
        $('#tip').fadeTo('10',0.9);
        $('#tip').css('top', $(this).position().top - 20 ).css('left', 50);
         return false;
    });
    $('#ajuda').click(function(e) {	
        $('#tipAjuda').fadeIn('500');
        $('#tipAjuda').fadeTo('10',0.9);
        $('#tipAjuda').css('top', e.pageY - 118 ).css('left', 20);
         return false;
    });
    
    $('input.openDis').click(function(e) {	
        $('#tipDis').fadeIn('500');
        $('#tipDis').fadeTo('10',0.9);
        $('#tipDis').css('top', e.pageY - 600 ).css('left', 20);
         return false;
    });

    $('body').click(function(){
        if ($('div.modal').is(':visible')) {
            $('div.modal').hide();
        }
    });
    
    $('#nextQ').click(function(){
        $('form#respostas input[name=backQuestion]').val(1);
        $('form#respostas').submit();
        return false;
    });
    
    $('.ulTabQuestionsNumbers a').click(function(){
        $('form#respostas input[name=setQuestion]').val($(this).text());
        $('form#respostas').submit();
        return false;
    });

    } catch(err){
        return;
    }
});


    function updateSelectedStyle() {
        $('#respostas :radio').removeClass('focused').next().removeClass('focused');
        $('#respostas :radio:checked').addClass('focused').next().addClass('focused');
    }



/*
$(document).ready(function() {
        var el = $('#sidebar-1'),
            top_offset = $('.container').offset().top;

        $(window).scroll(function() {
          var scroll_top = $(window).scrollTop();

          if (scroll_top > top_offset) {
            el.css('top', scroll_top - top_offset);
          }
          else {
            el.css('top', '');
          }
        });$(document).ready(function() {
        var el = $('#sidebar-1'),
            top_offset = $('.container').offset().top;

        $(window).scroll(function() {
          var scroll_top = $(window).scrollTop();

          if (scroll_top > top_offset) {
            el.css('top', scroll_top - top_offset);
          }
          else {
            el.css('top', '');
          }
        });*/