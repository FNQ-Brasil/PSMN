define(
  [
    //'libraries/validate/additional-methods',
  ],
  function() {
    return {
      init: function(mod, params) {
        this.checkbox();
        //console.log('sui')
      },

      checkbox: function(){
        $(':checkbox')
          .on('change', function(){
            var $label = $(this).siblings('span');
            if( $(this).is(':checked') ){
              $label.addClass('checked');
            }else{
              $label.removeClass('checked');
            }
          })
          .on('focus', function(){
            $(this).siblings('span').addClass('focus');
          })
          .on('blur', function(){
            $(this).siblings('span').removeClass('focus');
          });
      }, // checkbox

    }
  }
);
