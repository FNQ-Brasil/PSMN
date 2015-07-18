var roleQuestionnaireModule = (function () {

    var $form, $divError, $divErrorMsg;

    functionParam = function(param){
        $param = param;
        return;
    }

     return {
         
        init: function() {
            
            var $now = new Date();
            
            $divError = $('div.error');
            $divErrorMsg = $divError.find('b');
            
            $("input.datepicker").inputmask("dd/mm/yyyy", {placeholder:"dd/mm/aaaa", "clearIncomplete": true});

            $("#StartDate").datepicker({
                changeMonth: true,
                numberOfMonths: 2,
                showButtonPanel: true,
                beforeShow: function(){
                    $(this).datepicker('option', 'maxDate', $( "#EndDate" ).val());
                },
                onSelect: defaultModule.datepickeOnComplete
            });
            $("#EndDate").datepicker({
                defaultDate: "+4w",
                changeMonth: true,
                numberOfMonths: 2,
                showButtonPanel: true,
                beforeShow: function(){
                    $(this).datepicker('option', 'minDate', $( "#StartDate" ).val());
                },
                onSelect: defaultModule.datepickeOnComplete
            });
            
            $('input.datepicker').on('keydown', function(event) {
                if (event.which >= 48 && event.which <= 57 || event.which >= 96 && event.which <= 105) {
                    $(this).datepicker('hide');
                }
            });
        
            $form = $('#frmRoleQuestionnaire');
            $form.data('submited', false);
    
            $form.find('input:eq(0)').focus();
            
            $('select.fancy').chosen();

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
                            if (json.itemSuccess) {
                                alert('Período selecionado com sucesso!');
                                
                            }
                            if (json.redirectUrlroleQuestionnaire) {
                                window.location = json.redirectUrlroleQuestionnaire;
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

$(function() {
    try {
        roleQuestionnaireModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }
        throw e;
    }
});