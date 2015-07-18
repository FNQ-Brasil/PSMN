define(
  [
    'libraries/validate/jquery.validate',
    //'libraries/validate/additional-methods',
    //'libraries/validate/messages_pt_BR',
    'libraries/inputmask/inputmask',
    'libraries/placeholder/placeholder',
  ],
  function() {
    return {
      init: function(mod, params) {
        this.customSelect();
        this.masks();
        this.placeholders();
        return false;
      },

      customSelect: function(){
        require([
          'libraries/custom-select/custom-select'
          ], function(util){
            $('select').not('.original, .fancy').customSelect();
        });
      },

      validate: function(){
        $('.form-default').validate();
      },

      placeholders: function(){
        $('input, textarea').placeholder();
      },

      masks: function(){
        $(".dateITA").inputmask("99/99/9999");
        $(".tel").inputmask("9999-9999[9]");
        $(".tel-ddd").inputmask("(99) 9999-9999[9]");
        $(".cpf").inputmask("999.999.999-99");
        $(".cnpj").inputmask("99.999.999/9999-99");
        $(".cep").inputmask("99999-999");
        $(".number").inputmask("integer");
      }

    }
  }
);
