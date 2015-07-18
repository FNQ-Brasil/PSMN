define(
  [
    'libraries/validate/jquery.validate',
    'libraries/inputmask/inputmask'
  ],
  function() {
    return {
      init: function(mod, params) {
        var that = this;
        that.validate();
        that.masks();

if ( ! window.console ) console = { log: function(){} };


      },

      validate: function(){
        var that = this;
        require([
          "libraries/validate/messages_pt_BR",
          "libraries/validate/additional-methods",
          ],
          function(util) {
            var $form = $('#form-forgot-password');
            $form.validate({
              onkeyup: false,
              submitHandler: function(form) {
                  //form.submit();
                $.post( '/login/lost/format/json', $form.serialize(), function(data){
                   that.showMessage( data.itemSuccess ? 'success' : 'error' , data.messageError);
                   //console.log(data.messageError);
                   if (data.itemSuccess) {
                       $('#submitSenha').hide();
                   }
                });
              }
            })
          }
        );
      },

      masks: function(){
        var that = this;
        var $input =  $('[name="user[login]"]');
        $input.inputmask({
          'mask'            : '**.***.***/****-**',
        });
        $input.bind('paste', function(e) {
            var func = function(){
              $input.trigger('keyup');
            }
            _.delay(func, 500);
        });
        that.maskUpdate();
      }, // masks

      validateCPF: function(value) {
        value =  $('[name="user[login]"]').inputmask('unmaskedvalue')
        if( value.length != 11 ) return false
        value = jQuery.trim(value);
        value = value.replace('.','');
        value = value.replace('.','');
        cpf = value.replace('-','');
        while(cpf.length < 11) cpf = "0"+ cpf;
          var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
          var a = [];
          var b =0;
          var c = 11;
          for (i=0; i<11; i++){
            a[i] = cpf.charAt(i);
            if (i < 9) b += (a[i] * --c);
          }
          if ((x = b % 11) < 2) { a[9] = 0; } else { a[9] = 11-x; }
          b = 0;
          c = 11;
          for (y=0; y<10; y++) b += (a[y] * c--);
          if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }
          var retorno = true;
          if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) retorno = false;
          return retorno;
      },

      isUsername: false,

      maskUpdate: function(){
        var that = this;
        var $input =  $('[name="user[login]"]');
        $input.on('keyup', function(e){
          var value = $input.inputmask('unmaskedvalue');
          if( !isNaN(parseFloat(value)) && isFinite(value)  ){
            that.isUsername = false;
            if( that.validateCPF(value)  ){
              $input.addClass('cpf').removeClass('cnpj')
              var func = function(){
                if( that.validateCPF($input.inputmask('unmaskedvalue'))  ){
                   $input.inputmask({
                      mask            : '999.999.999-99',
                      placeholder     : "_",
                      greedy          : true
                    });
                    $('#form-home-login').submit();
                }
             };
              _.delay(func, 3000);
            } else{
              $input.addClass('cnpj').removeClass('cpf');
              $input.inputmask({
                mask            : '*9.999.999/9999-99',
                placeholder     : "_",
                greedy          : true
              });
              var value = $input.inputmask('unmaskedvalue');
              if(value.length == 14){
                if( $input.valid() ){
                 $('#form-home-login').submit()
                }
              }
            }
          }else{
            $(this).removeClass('cpf').removeClass('cnpj')
            that.isUsername = true;
             $input.inputmask({
                mask            : "*",
                placeholder     : "",
                repeat          : 20,
                greedy          : false
              });
          }
        })
      },

      showWarn: function(message){
        alert(message)
      },

       showMessage: function(type, message){
        $('#form-forgot-password').prepend('<div class="form-message ' + type + '">' + message + '</div>');

        $('.form-message', '#form-forgot-password').on('click', function(){
          $(this).slideUp('fast', function(){
            $(this).remove()
          })
        })

      },


    }
  }
);
