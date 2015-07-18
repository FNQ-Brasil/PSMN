define(
  [
    'libraries/validate/jquery.validate',
    'libraries/inputmask/inputmask'
  ],
  function() {
    return {
      init: function(mod, params) {
        var that = this;
        that.fillForm();

      },

      fillForm: function(){
        $('#application-env').append('<button id="fillForm">Preencher formulário</button>');
        $('#fillForm').on('click', function(){
          $('form :input').each(function(i,e){
            console.dir(e)
            if( $(e).hasClass('tel-ddd') ){ $(e).val( '111234-5678'); }
            if( $(e).hasClass('dateITA') ){ $(e).val( '01012011'); }
            if( $(e).hasClass('number') ){ $(e).val( '123456789'); }
            if( $(e).hasClass('cep') ){ $(e).val( '31015340'); }
            if( $(e).hasClass('hasCustomSelect') ){ $(e).find('option').eq(1).trigger('click change') }
            if( $(e).hasClass('cep') ){ $(e).val( '31015340'); }
            else{
              $(e).val( 'valor ' + i);
            }
          });
        });
      }


    }
  }
);
