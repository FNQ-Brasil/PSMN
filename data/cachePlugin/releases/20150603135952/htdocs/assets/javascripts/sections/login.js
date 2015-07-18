define(
        [
            'libraries/validate/jquery.validate',
            'libraries/inputmask/inputmask',
            'libraries/placeholder/placeholder',
        ],
        function() {
            return {
                init: function(mod, params) {
                    var that = this;
                    that.validate();
                    that.masks();
                    that.fixFooter();
                    this.placeholders();

                    if (!window.console)
                        console = {log: function() {
                            }};

                },
                placeholders: function() {
                    //$('input, textarea').placeholder();
                },
                fixFooter: function() {
                },
                step: 'verify-username',
                validate: function() {
                    var that = this;
                    require([
                        "libraries/validate/messages_pt_BR",
                        "libraries/validate/additional-methods",
                    ],
                            function(util) {
                                $('#frmLogin').validate({
                                    onkeyup: false,
                                    submitHandler: function(form) {
                                        $('.warn').fadeOut('fast', function() {
                                            $(this).remove()
                                        })
                                        if (that.step === 'verify-username') {
                                            that.verifyUsername(form);
                                        }
                                        if (that.step === 'send-password') {
                                            that.sendLogin(form);
                                        }
                                        if (that.step === 'load-register') {
                                            form.submit();
                                        }
                                    }
                                })
                            }
                    );
                },
                // @todo : mask cnpj && cpf
                masks: function() {
                    var that = this;
                    var $input = $('[name="username"]');
                    $input.inputmask({
                        'mask': '***.***.***-**'
                    });
                    $input.bind('paste', function(e) {
                        var func = function() {
                            $input.trigger('keyup');
                        }
                        _.delay(func, 500);
                    });
                    that.maskUpdate();
                }, // masks

                validateCPF: function(value) {
                    value = $('[name="username"]').inputmask('unmaskedvalue')
                    if (value.length != 11)
                        return false
                    value = jQuery.trim(value);
                    value = value.replace('.', '');
                    value = value.replace('.', '');
                    cpf = value.replace('-', '');
                    while (cpf.length < 11)
                        cpf = "0" + cpf;
                    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
                    var a = [];
                    var b = 0;
                    var c = 11;
                    for (i = 0; i < 11; i++) {
                        a[i] = cpf.charAt(i);
                        if (i < 9)
                            b += (a[i] * --c);
                    }
                    if ((x = b % 11) < 2) {
                        a[9] = 0;
                    } else {
                        a[9] = 11 - x;
                    }
                    b = 0;
                    c = 11;
                    for (y = 0; y < 10; y++)
                        b += (a[y] * c--);
                    if ((x = b % 11) < 2) {
                        a[10] = 0;
                    } else {
                        a[10] = 11 - x;
                    }
                    var retorno = true;
                    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg))
                        retorno = false;
                    return retorno;
                },
                isUsername: false,
                maskUpdate: function() {
                    var that = this;
                    var $input = $('[name="username"]');
                    $input.on('keyup', function(e) {
                        var value = $input.inputmask('unmaskedvalue');
                        //$input.val( value )
                        if (!isNaN(parseFloat(value)) && isFinite(value)) {
                            that.isUsername = false;
                            if (that.validateCPF(value)) {
                                $input.addClass('cpf');
                                var func = function() {
                                    if (that.validateCPF($input.inputmask('unmaskedvalue'))) {
                                        $input.inputmask({
                                            mask: '999.999.999-99',
                                            placeholder: "_",
                                            greedy: true
                                        });
                                        $('#frmLogin').submit();
                                    }
                                };
                                _.delay(func, 3000);
                            }
                        } else {
                            $(this).removeClass('cpf');
                            that.isUsername = true;
                            $input.inputmask({
                                mask: "*",
                                placeholder: "",
                                repeat: 20,
                                greedy: false
                            });
                        }
                    })
                },
                sendLogin: function(form) {
                    var $form = $(form);
                    $.post('/login/index/format/json/', $form.serialize(), function(data) {
                        if (data.itemSuccess == false) {
                            $form.prepend('<div class="warn error">' + data.messageError + '</div>');
                        } else {
                            window.location.assign(data.urlRedirect);
                            window.location.href = data.urlRedirect;
                        }
                    });
                }, // sendLogin

                verifyUsername: function(form) {
                    var $form = $(form);
                    var data = $(form).serialize();
                    var that = this;
                    jQuery.post(
                            $form.attr('action') + 'index/format/json/',
                            $form.serialize(),
                            function(data, textStatus, jqXHR) {
                                if (data.existe == "false") {
                                    if (that.isUsername == false) {
                                        that.postRegister($form, data);
                                    } else {
                                        that.showWarn('Informe um Usuário válido');
                                    }
                                } else {
                                    that.enablePassword(true);
                                }
                            });
                }, //loginSubmit

                showWarn: function(message) {
                    alert(message);
                },
                postRegister: function($form, data) {
                    $form.append('<input type="hidden" name="cpf" value="' + data.cpf + '">');
                    $form.append('<input type="hidden" name="forward" value="' + data.forward + '">');
                    $form.attr('action', data.loadUrlRegister);
                    this.step = 'load-register';
                    $form.submit();

                }, // postRegister

                enablePassword: function(enable) {
                    var that = this;
                    var $password = $('#login-password');
                    var $linkPassword = $('#link-forgot-password');
                    if (enable) {
                        that.step = 'send-password';
                        $password.find('input').removeAttr('disabled').focus();
                        $password.animate({
                            height: 78
                        }, 'fast', function() {
                            $linkPassword.fadeIn();
                        });
                    } else {
                        that.step = 'verify-username';
                        $password.find('input').attr('disabled')
                        $password.animate({
                            height: 0
                        }, 'fast', function() {
                            $linkPassword.fadeOut();
                        });
                    }
                } // enablePassword
            }
        }
);
