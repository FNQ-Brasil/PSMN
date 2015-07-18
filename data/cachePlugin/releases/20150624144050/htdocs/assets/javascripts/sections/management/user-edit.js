define(
  [
    'libraries/validate/jquery.validate',
    'libraries/inputmask/inputmask',
    'libraries/mpe_modal/mpe_modal',
    'libraries/chosen/chosen.min',
    'libraries/form/jquery.form.min',
    'libraries/capital',
  ],
  function() {
    return {
      init: function(mod, params) {

        $.ajaxSetup({cache: false});

        var that = this;
        userModule.init();
      }
    }
  }
);
var userModule = (function () {

    var $form, $divError, $divErrorMsg;
    
     return {
         
        init: function() {
            
            var $now = new Date();
            
            $divError = $('div.error');
            $divErrorMsg = $divError.find('b');

            $('#Cpf').inputmask("mask", {"mask": "999.999.999-99", "clearIncomplete": true}); /*dd*/
             //$('#Login').inputmask("mask", {"mask": "aaaaaaaaaaaaa", "clearIncomplete": false});
            $("#Login").inputmask({ "mask": "a", "repeat": 13, "greedy": false })
            $("input.datepicker").inputmask("dd/mm/yyyy", {placeholder:"dd/mm/aaaa", "clearIncomplete": true});
/*
            $( "#BornDate" ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                changeYear: true,
                yearRange: '1900:+0',
                onSelect: defaultModule.datepickeOnComplete
            });
               */
            
            $('input.datepicker').on('keydown', function(event) {
                if (event.which >= 48 && event.which <= 57 || event.which >= 96 && event.which <= 105) {
                    $(this).datepicker('hide');
                }
            });
            
            $form = $('#frmUser');
            $form.data('submited', false);
            $form.find('input:eq(0)').focus();
            $form.find('input.checkbox').bind({
                'change' : function() {
                    var $fieldKeypass = $('#Keypass');
                    var $fieldKeypassConfirm = $('#KeypassConfirm');
                    var $this = $(this);
                    if ($this.is(':checked')) {
                        $fieldKeypass.attr('disabled', false).removeClass('disabled');
                        $fieldKeypassConfirm.attr('disabled', false).removeClass('disabled');
                        $fieldKeypass.focus();
                    } else {
                        $fieldKeypass.attr('disabled', true).addClass('disabled').val('');
                        $fieldKeypassConfirm.attr('disabled', true).addClass('disabled').val('');
                    }
                }
            });
    
          //  $('select.fancy').chosen();

            jQuery.validator.messages.required = "";
            $form.validate({
                invalidHandler: function(e, validator) {
                    //console.log(e, validator);
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var message = errors == 1
                            ? 'Você esqueceu de 1 campo. Ele está destacado em vermelho.'
                            : 'Você esqueceu de ' + errors + ' campos.  Eles estão destacados em vermelho.';
                        $divErrorMsg.html(message);

                        $.scrollTo(
                            $(validator.errorList[0].element), 200,
                            {
                                offset: -35,
                                onAfter: function() {
                                    setTimeout(function(){
                                        $divError.show().effect('bounce');
                                    }, 200);
                                }
                            }
                        );
                        //.focus();
                    } else {
                        $divError.hide();
                    }
                },
                onkeyup: false,
                submitHandler: function() {
                    $form.ajaxSubmit({
                        dataType: 'json',
                        beforeSubmit: function(formData, jqForm, options) { 
                            if (!$form.valid() || $form.data('submited')) {
                                //console.log('valid false');
                                return false;
                            }
                            $form.data('submited', true);
                            $divError.hide();
                            return true;
                        },
                        success: function(json, $statusText, xhr, $form)  {
                            if (!($statusText == 'success') || !json.itemSuccess) {
                                $divErrorMsg.html(json.messageError);
                                $divError.show('bounce');
                                $form.data('submited', false);
                                return;
                            }
                            if (json.redirectUrlUser) {
                                window.location = json.redirectUrlUser;
                                return;
                            }
                            if (json.loadUrlUser) {
                                $('#content').load(json.loadUrlUser);
                                return;
                            }
                            if (json.itemSuccess) {
                                $('#content').load(json.loadUrlUser);
                                return;
                            }                            
                        }
                    });
                    return false;
                }
            });
           
            $('#msgError').hide();
        return this;
        }
        
        
        
        
        
    };

}());