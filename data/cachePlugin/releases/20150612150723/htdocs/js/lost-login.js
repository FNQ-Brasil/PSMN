var lostLoginModule = (function () {

    var $form, $divError, $divErrorMsg;

    return {
         
        init: function() {
            
            var $now = new Date();
            
            $cpf = $('#Cpf');
            $btPasswordHint = $("#bt-PasswordHint");
            
            $btPasswordHint.on( "click", function() {
                passwordHint($cpf.val());
              //alert( $( this ).text() + $cpf.val() );
            });
            
            $divError = $('div.error');
            
            $divErrorMsg = $divError.find('b');

            $cpf.inputmask("mask", {"mask": "999.999.999-99", "clearIncomplete": true});

            $form = $('#frmLost');
            $form.data('submited', false);
            $form.find('input:eq(0)').focus();
            
            
            var that = this;
            var $input =  $('[name="user[login]"]');
            $input.on('keyup', function(e){
              var value = $input.inputmask('unmaskedvalue');
              //$input.val( value )
              if( !isNaN(parseFloat(value)) && isFinite(value)  ){
                that.isUsername = false;
                if( validateCPF(value)  ){
                  $input.addClass('cpf');
                  var func = function(){
                    if( validateCPF($input.inputmask('unmaskedvalue'))  ){
                       $input.inputmask({
                          mask            : '999.999.999-99',
                          placeholder     : "_",
                          greedy          : true
                        });
                        //$('#frmLogin').submit();
                    }
                 };
                  //_.delay(func, 3000);
                }
              } else {
                $(this).removeClass('cpf');
                that.isUsername = true;
                 $input.inputmask({
                    mask            : "*",
                    placeholder     : "",
                    repeat          : 20,
                    greedy          : false
                  });
              }
            });
            
            validateCPF = function(value) {
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
              };
            

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
                        return;
                    } 
                    $divError.hide();
                },
                onkeyup: false,
                submitHandler: function() {
                    $form.ajaxSubmit({
                        dataType: 'json',
                        beforeSubmit: function(formData, jqForm, options) { 
                            if (!$form.valid() || $form.data('submited')) {
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
                                setTimeout(function(){escroll();}, 250);
                                return;
                            }
                            alert('Uma nova senha foi gerada e enviada para seu e-mail, aguarde recebimento.')
                        },
                        error:function(x,e){
                            $form.data('submited', false);
                            setTimeout(function(){escroll();}, 250);
                        }
                    });
                    return false;
                }
            });
            
            passwordHint = function(cpf) {
                    var $cpf = cpf;
                    $PasswordHint = $('#PasswordHint');
                    console.log($cpf);
                    $.ajax({
                        url: BASE_URL + '/default/login/password-hint/format/json',
                        type: 'post',
                        dataType: 'json',
                        data: {cpf: $cpf},
                        
                        success: function(json, $statusText) {
                            if (!json.itemSuccess) {
                                alert(1);
                                alert(json.messageError);
                                return;
                            }
                            //alert(json.messageSuccess);
                            //console.log($passwordHint,json.messageSuccess,json);
                            //console.log($passwordHint,json.messageSuccess,json);
                            $('#PasswordHint').show();
                            $('#PasswordHint').val(json.messageSuccess);
                            $btPasswordHint.hide();
                            return;                        
                        }
                    });
            };
            $('#msgError').hide();
        return this;
        }
    };

}());

$(function() {
    try {
        lostLoginModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            Sescoop.error(e.message);
            return;
        }
        throw e;
    }
});