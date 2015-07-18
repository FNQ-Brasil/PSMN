define(
  [
    'libs/mask/jquery.maskinput'
  ],
  function(mask) {
    return {
      init: function() {

        $(".main-menu li a").each(function(){
          $(this).bind("mouseover mouseout", function() {

          var clicked = $(this).parent();

          clicked.toggleClass('open');

          var liQnt = $(".main-menu .open ul li").size();
          $(".main-menu .open ul").css('width',''+ liQnt * 125 +'px');    
            return false; 
          })

        });

        $('.input-phone').mask('(99) 99999-9999');
        $('.input-cnpj').mask('99.999.9999/9999-99');
        $('.input-inscricao').mask('999.999.999.999');
        $('.input-date').mask('99 / 99 / 9999');

        //$('#tabs').tabs();
        //$('.quizz').tabs();
/*
        $('#e1').select2({
          minimumInputLength: 2
        });

*/
      }
    }
  }
);