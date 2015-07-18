var appraiserModule = (function () {

    var $form, $divError, $divErrorMsg;

    listAppraiser = function(){
         
    }

     return {
         
        init: function() {
            
            $divError = $('div.error');
            $divErrorMsg = $divError.find('b');
 
            $('select.fancy').chosen();
 
 
 
            $form = $('#frmIndexAppraiser_X');
            $form.data('submited', false);
    
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
                            if (json.redirectUrlAppraiser) {
                                window.location = json.redirectUrlAppraiser;
                                alert(json.redirectUrlAppraiser);
                                return;
                            }
                            if (json.loadUrlAppraiser) {
                                $('#content').load(json.loadUrlAppraiser);
                                //alert(json.loadUrlAppraiser);
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
        appraiserModule.init();
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
