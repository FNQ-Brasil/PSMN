define(
  [
    'libraries/validate/jquery.validate',
    'libraries/inputmask/inputmask',
  ],
  function() {
    return {
      init: function(mod, params) {
        this.validate();
        this.masks();
        this.loginSubmit()
      },

      validate: function(){
        require([
          "libraries/validate/messages_pt_BR",
          "libraries/validate/additional-methods",
          ], function(util) {});
        $('#form-home-login').validate({
          submitHandler: function(form) {
            console.log(form)
            return false;
          }
        });
      },

      // @todo : mask cnpj && cpf
      masks: function(){
        /*
        $("#cpf-cnpj").inputmask({
          "mask"    : "9",
          "repeat"  : 14,
          "greedy": false
        });

*/
        $.extend($.inputmask.defaults.definitions, {
          'q': {
            validator: "[A-Za-z\u0410-\u044F\u0401\u04510-9]",
            cardinality: 1,
            casing: "lower"
          }
        });
        var $input =  $('#cpf-cnpj');
        $input.inputmask({
          'mask'            : 'qqq.qqq.qqq-qq',
          onKeyValidation: function (result, inputmask) {
            var value = $input.inputmask('unmaskedvalue');
            console.log(inputmask)
            if( true  || _.isNumber( value ) ){
              $input.inputmask('remove');
              $input.inputmask({
                'mask' : ( String(value).length <= 11 ? '999.999.999-99[9]' : '99.999.999/9999-99' ),
                onKeyValidation: inputmask.onKeyValidation
              })
            } else {
              $input.inputmask({ 'mask' : 'qqqqqqqqq'})
            }
          }
        })
        /*
        $('#cpf-cnpj').on('keyup', function(){
            var numberPattern = /\d+/g;
            var $input = $(this);
            console.log(val)

            $input.val(val)
            $input.inputmask('remove').inputmask({
              'mask'            : ( val.length <= 11 ? '999.999.999-99[9]' : '' ),
              'clearIncomplete': true
            })

        });
*/
      }, // masks

      loginSubmit: function(){
        // envio via ajax
      } //loginSubmit

    }
  }
);
