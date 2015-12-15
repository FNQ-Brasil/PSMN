    var qstnEditModule = (function () {
    var $form;
     return {
        init: function() {

        $("input.datepicker").inputmask("dd/mm/yyyy", {placeholder:"dd/mm/aaaa", "clearIncomplete": true});

        $("#operation_beginning").datepicker({
			changeMonth: true,
			numberOfMonths: 2,
            showButtonPanel: true,
            beforeShow: function(){
                $(this).datepicker('option', 'maxDate', $( "#operation_ending" ).val());
            },
            onSelect: defaultModule.datepickeOnComplete
		});
		$( "#operation_ending" ).datepicker({
			defaultDate: "+4w",
			changeMonth: true,
			numberOfMonths: 2,
            showButtonPanel: true,
            beforeShow: function(){
                $(this).datepicker('option', 'minDate', $( "#operation_beginning" ).val());
            },
            onSelect: defaultModule.datepickeOnComplete
		});

        $form = $('form.normal');
        $form.find('select.fnc').chosen();
        jQuery.validator.messages.required = "";
        $form.validate({
            invalidHandler: function(e, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                        ? 'Você esqueceu de 1 campo. Ele está destacado em vermelho.'
                        : 'Você esqueceu de ' + errors + ' campos.  Eles estão destacados em vermelho.';
                    $("div.error b").html(message);
                    $("div.error").show();
                } else {
                    $("div.error").hide();
                }
            },
            onkeyup: true/*,
            submitHandler: function() {
                $("div.error").hide();
            }*/
        });
        return this;
        }
    };

}());

$(function() {
    try {
        qstnEditModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Mpe.error(e.message);
            return;
        }
        throw e;
    }
});