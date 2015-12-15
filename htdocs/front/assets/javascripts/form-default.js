define(
  [
    'libraries/validate/jquery.validate',
    //'libraries/validate/additional-methods',
    //'libraries/validate/messages_pt_BR',
    'libraries/maskedinput/maskedinput',
  ],
  function() {
    return {
      init: function(mod, params) {
        this.validate();
        this.masks();
      },

      validate: function(){
        $('.form-default').validate();
      },

      masks: function(){
        $(".date").mask("99/99/9999");
        $(".tel").mask("9999-9999");
        $(".cpf").mask("999.999.999-99");
        $(".cnpj").mask("99.999.999/9999-99");
        $(".cep").mask("99.999-999");
      }

    }
  }
);