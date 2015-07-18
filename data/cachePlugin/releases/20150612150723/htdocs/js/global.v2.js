$(function() {
    try {
        $("#menu a").click(function() {
            if ($(this).closest('li').hasClass('link')) {
                return true;
            }
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