var registerModule = (function () {

    var $form, $divError, $divErrorMsg;

    var isEmpty = function (str) {
        return (!str || 0 === str.length);
    }
        
    var isset = function (str) {
        return typeof str !== 'undefined';
    } 

    getAddressByCep = function(president) {
        $nameFullLog = $('#NameFullLog'+president);
        cepData = $('#Cep'+president).val();
        $.ajax({
            url: BASE_URL +'/address/index/format/json',
            type: 'post',
            dataType: 'json',
            data: {
                'cep': cepData
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    //alert('Error load address');
                    return;
                } 
                $nameFullLog.val(json.address.NameFullLog);
                stateId = json.address.UfCode;
                cityId = json.address.CityId;
                neighborhoodId = json.address.NeighborhoodId;
                listStates(stateId,cityId,neighborhoodId,president);
                $nameFullLog.trigger("liszt:updated");
            }
        });
    }

    listStates = function(stateId,cityId,neighborhoodId,president) {
        $uf = $('#Uf'+president);
        $uf.find('option').remove();
        $uf.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $uf.trigger("liszt:updated");
        $.ajax({
            url: BASE_URL +'/default/state/index/format/json',
            type: 'post',
            dataType: 'json',
            data: {
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load states');
                }
                $uf.find('option').remove();
                for(var i in json.states) {
                    if ( (json.states[i].Id == stateId) ){
                        $uf.append($("<option></option>").attr("value",json.states[i].Id)
                            .attr("selected",'selected').text(json.states[i].Name));
                            stateId = json.states[i].Id;
                    }
                    $uf.append($("<option></option>")
                        .attr("value",json.states[i].Id).text(json.states[i].Name));
                }
                $uf.trigger("liszt:updated");
                listCities(stateId,cityId,neighborhoodId,president);
            }
        });
    }

    listCities = function(stateId,cityId,neighborhoodId,president){
        
        $cityId = $('#CityId'+president);
        capital = searchCities(stateId);

        $cityId.find('option').remove();
        $cityId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $cityId.trigger("liszt:updated");

        $.ajax({
            url: BASE_URL +'/default/city/index/state_id/'+stateId+'/format/json',
            type: 'post',
            dataType: 'json',
            data: {
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load cities');
                }
                
                $cityId.find('option').remove();
                $cityId.append($("<option></option>") .attr("value","").text("Escolha a Cidade"));
                
                if (isset(cityId)) {
                    $cityId.find('option').remove();
                    if (cityId != capital.capitalID) {
                        for(var c in json.cities) {
                            if ( json.cities[c].Id == cityId ) {
                                $cityId.append($("<option></option>").attr("value",json.cities[c].Id)
                                    .text(json.cities[c].Name).attr("selected",'selected'));
                            }
                        }
                    }
                }
                
                $cityId.append($("<option></option>") .attr("value",capital.capitalID).text(capital.capitalNAME));
                
                for(var i in json.cities) {
                    if ( (json.cities[i].Id != capital.capitalID) && (json.cities[i].Id != isset(cityId)) ){
                        $cityId.append($("<option></option>") .attr("value",json.cities[i].Id).text(json.cities[i].Name));
                    }
                }
                
                $cityId.trigger("liszt:updated");
                city = (cityId != null)?cityId:capital.capitalID;
                listNeighborhoods(city,neighborhoodId,president);
            }
        });
    }

    listNeighborhoods = function(cityId,neighborhoodId,president){
        $neighborhoodId = $('#NeighborhoodId'+president);
        $neighborhoodId.find('option').remove();
        $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $neighborhoodId.trigger("liszt:updated")
        $.ajax({
            url: BASE_URL +'/default/neighborhood/index/city_id/'+cityId+'/format/json',
            type: 'post',
            dataType: 'json',
            data: {
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load neighborhoods');
                }
                $neighborhoodId.find('option').remove();
                $neighborhoodId.append($("<option></option>") .attr("value","").text("Escolha o Bairro"));
                $neighSelected = (neighborhoodId != null)?neighborhoodId:null;
                for (var i in json.neighborhoods) {
                    if ( json.neighborhoods[i].Id == $neighSelected ) {
                        $neighborhoodId.append($("<option></option>")
                        .attr("value",json.neighborhoods[i].Id)
                        .attr("selected",'selected')
                        .text(json.neighborhoods[i].Name));
                    } else {
                        $neighborhoodId.append($("<option></option>")
                        .attr("value",json.neighborhoods[i].Id)
                        .text(json.neighborhoods[i].Name));
                }
                }
                $neighborhoodId.trigger("liszt:updated");
                setTimeout(function(){escroll();}, 500);
            }
        });
    },

    addOrRemoveRequiredFromEmailFields = function(elClass){
        var inputs = $('.emailField').find('input');
        $(inputs).each(function(){
            $(this).prop('class', elClass);
        })
    }

    hideOrShowEmailFields = function(el){
        if($(el).is(':checked')){
            addOrRemoveRequiredFromEmailFields('valid');
            $('.emailField').hide();
            $('#EmailDefault').val('');
            $('#Email').val('');
            $('[name="enterprise[hasnt_email]"]').prop('checked',true);
        }
        else{
            var elClass = "w300 {required:true, email:true, messages:{email:'Entre com endereço de e-mail válido.'}}  input list-table-100";
            addOrRemoveRequiredFromEmailFields(elClass);
            $('.emailField').show();
            $('[name="enterprise[hasnt_email]"]').prop('checked',false);
        }

    }

    var isRuralProducer = function(){
        return $('#CategoryAwardId2').is(':checked');
    }

    var filledAtLeastOneRuralProducerInfo = function(){
        var $ruralProducerFields = $('#Cnpj, #RegisterMinistryFisher, #StateRegistration, #Dap, #Nirf');
        var emptyFields = 0;

        $ruralProducerFields.each(function(index,element){
            if($(element).val() == '')
                emptyFields++;
        });

        return emptyFields < $ruralProducerFields.length;
    }

    var validateFarmSize = function(){
        var $farmSize = $('#FarmSize');
        var clean_value = $farmSize.val().replace(/\./,'').replace(/,/,'.');
        var valid = !isNaN(clean_value);

        if(!valid) $farmSize.addClass('error');

        return valid;
    }

    var showSector = function(){
        var option = $('#CategorySectorId').find("option[selected]");
        if(option){
          $('#CategorySectorId_chzn').find('a').find('span').text(option.text());
          $('#CategorySectorId').val(option.val());
        }
    }

    var pushGAPageview = function(url){
        if(window._gaq) _gaq.push(['_trackPageview', url]);
    }

    var trackGARegisterPresidentConversion = function(){
        getGAConversionScript(function(){
            pushGAConversion({
                google_conversion_id: 965670021,
                google_conversion_language: "en",
                google_conversion_format: "3",
                google_conversion_color: "ffffff",
                google_conversion_label: "oOTaCK_slVwQhem7zAM",
                google_remarketing_only: false
            });
        });
    }

    var trackGARegisterSuccessConversion = function(){
        getGAConversionScript(function(){
            pushGAConversion({
                google_conversion_id: 965670021,
                google_conversion_language: "en",
                google_conversion_format: "3",
                google_conversion_color: "ffffff",
                google_conversion_label: "LkbKCKib71YQhem7zAM",
                google_remarketing_only: false
            });
        });
    }

    var getGAConversionScript = function(callback){
        if(window.google_trackConversion) {
            callback();
        } else {
            $.getScript('//www.googleadservices.com/pagead/conversion_async.js').success(callback);
        }
    }

    var pushGAConversion = function(data){
        window.google_trackConversion(data);
    }

    var setFormStepChangeTriggers = function(){
        $('a[href=\\#tabs-1]').on('click',function(){
            pushGAPageview('/questionnaire/register#enterprise');
        });

        var at_enterprise_form = $('a[href=\\#tabs-1]').length > 0;
        if(at_enterprise_form) pushGAPageview('/questionnaire/register#enterprise');

        $('#ui-id-2').on('click',function(){
            window._fbq.push(['track', '6025441638765', {'value':'0.00','currency':'BRL'}]);
            pushGAPageview('/questionnaire/register#president');
            trackGARegisterPresidentConversion();
        });
    }

     return {
        pushGAPageview: pushGAPageview,
        trackGARegisterSuccessConversion: trackGARegisterSuccessConversion,

        init: function() {
          $uf = $('#Uf');
          $city = $('#CityId');
          $cepSearch = $('#CepSearch');

          $ufPresident = $('#UfPresident');
          $cityPresident = $('#CityIdPresident');
          $cepSearchPresident = $('#CepSearchPresident');
            
          $categorySectorId = $('#CategorySectorId');

          $("#Cep").blur(function(e){
            if($.trim( $("#Cep").val() ) != ""){
                getAddressByCep('');
            }
          });

          $("#CepPresident").blur(function(e){
            if($.trim($("#CepPresident").val()) != ""){
                getAddressByCep('President')
            }
          });



            var $now = new Date();
            $(".radio").click(function() {
              $(this).parents('.line-radios').find('.checkedRadio').removeClass('checkedRadio');
              $(this).next('.label-inline').eq(0).find('span.radio-button').addClass('checkedRadio');
            });
            
            $(".radio").click(function() {
              $(".radio.selected").removeClass("selected");
              $(this).addClass("selected");
              
              //Produtor Rural
              if($('#CategoryAwardId2').hasClass('selected')){
                  
                  var AgronegocioValue = 1;
                    $("#Cnae").removeClass("{required:true}");
                  
                  $categorySectorId.val(AgronegocioValue).attr('disabled', true).trigger("liszt:updated");                 
                  
                  //alert( $('#CategorySectorId').html() ) ;
                  //alert( $('#CategorySectorId').val() ) ;
                  
                  //$("p.intro").hide("slow",callbackEver);
                
                $('.other-data').css('display','block');
              } else {
                $('.other-data').css('display','none');
              }
              if($('#CategoryAwardId1').hasClass('selected') || $('#CategoryAwardId3').hasClass('selected')) {
                $('#Cnpj').addClass('{required:true}');
                $("#Cnae").addClass("{required:true}");
                $('#Cnpj').siblings().text('CNPJ: *');
                
                $categorySectorId.val('').attr('disabled', false).trigger("liszt:updated");
              }else {
                $('#Cnpj').removeClass('{required:true}');
                $('#Cnpj').siblings().text('CNPJ:');
              }
            });
            //.trigger('click');

            $('[name="enterprise[hasnt_email]"]').on('change', function(){
                hideOrShowEmailFields(this);
            });

            $('[name="enterprise[hasnt_email]"]').each(function(){
                hideOrShowEmailFields(this);
            })
            
            $('input:radio[name="newsletter"]').on('change', function(){
                console.log('mudou')
                if ($(this).is(':checked') && $(this).val() == '1') {
                    //console.log('mudou 1')
                    $('#newsTypes').removeClass('disabled').find(':input').removeAttr('disabled')
                }else{
                    //console.log('mudou 2')
                    $('#newsTypes').addClass('disabled').find(':input').attr('disabled', 'disabled')
                }
            });


            $('#tabs').tabs({
                show: function(event, ui){
                     if(ui.index == 0) {
                        $('#CategoryAwardId1').focus()
                    }
                    if(ui.index == 1) {
                        $('#Name').focus()
                    }
                },
                select: function(event, ui) {
                    var $inputs = $('#tabs-1').find(":input:visible, .chzn-done");
                    var valid = true;
                    window.location.hash = '';
                    $inputs.each(function(i, e) {
                        if( $(e).hasClass('chzn-done') && !$(e).valid() ){
                            var id = $(e).attr('id');
                            $('#' + id + '_chzn' ).addClass('chzn-error')
                        }
                        if (!validator.element(this) && valid) {
                            valid = false;
                        }
                    });

                    var invalidRuralProducer = false;

                    if(isRuralProducer()){
                        invalidRuralProducer = !(validateFarmSize() && filledAtLeastOneRuralProducerInfo());
                        if(valid) valid = !invalidRuralProducer;
                    }

                    if(valid){
                    } else {
                        event.preventDefault();
                        //xgh
                        $(':input.error').eq(0).focus()
                        var errors = validator.numberOfInvalids();
                    if (errors || invalidRuralProducer) {
                        var message = '';

                        if(errors){
                            message = (
                                errors == 1
                                ? 'Você esqueceu de 1 campo. Ele está destacado'
                                : 'Você esqueceu de ' + errors + ' campos.  Eles estão destacados'
                            );
                            message = "<p>- "+message+"</p>";
                        }

                        if(isRuralProducer() && !validateFarmSize()){
                            message += "<p>- Tamanho da propriedade inválido (ex: 12345678,09)</p>";
                        }

                        if(isRuralProducer() && !filledAtLeastOneRuralProducerInfo()){
                            var phraseJoin = errors ? 'Além disso, p' : 'P';
                            message += "<p>- "+phraseJoin+"elo menos uma das informações deve ser preenchida:</p>";
                            message += "<p> CNPJ, Registro no Ministério da Pesca, DAP, NIRF ou Inscrição Estadual</p>";
                        }

                        var $divError = $('#errors-tab-1');
                        $divError.html(message);
                        $.scrollTo(
                            $('#tabs'), 200,
                            {
                                offset: -35,
                                onAfter: function() {
                                    setTimeout(function(){
                                        $divError.eq(0).show().effect('bounce');
                                    }, 200);
                                }
                            }
                        );
                    } else {
                        $divError.hide();
                    }
                    }
                        $.scrollTo('#tabs', 200)
                }   
            }).find( 'ul.ui-tabs-nav a' ).bind( 'click', function(e){
                  e.preventDefault();
            });
            $('.ui-tabs-anchor').each(function(i, e){
                $(this).attr('tabindex', i+1)
            })

            $('.form-button-tab-nav.btn-submit').bind( 'click', function(e){
                  e.preventDefault();
            });
            $('.error-box').on('click', function(){
                $(this).fadeOut();
            });

            $('.form-button-tab-nav').on('click', function(){
                $('[href="'+this.hash+'"].ui-tabs-anchor').trigger('click');
            });
            
            $divError = $('div.error-box');
            $divErrorMsg = $divError.find('b');
                     
            $('#Cnpj, #EleCnpj').inputmask("mask", {"mask": "99.999.999/9999-99", "clearIncomplete": true});
            $('#Cep').inputmask("mask", {"mask": "99999-999", "clearIncomplete": true});
            $('#CepPresident').inputmask("mask", {"mask": "99999-999", "clearIncomplete": true});
            $('#Cpf').inputmask("mask", {"mask": "999.999.999-99", "clearIncomplete": true});
            
            $('#StreetNumber').inputmask("mask", {"mask": "99999999999", placeholder:" ", clearMaskOnLostFocus: true, 'autoUnmask' : true})
            //$('#Cnae').inputmask("mask", {"mask": "9999999999", placeholder:" ", clearMaskOnLostFocus: true, 'autoUnmask' : true})
            $('#CooperatedQuantity').inputmask("mask", {"mask": "99999999999", placeholder:" ", clearMaskOnLostFocus: true, 'autoUnmask' : true})
            $('#EmployeesQuantity').inputmask("mask", {"mask": "99999999999", placeholder:" ", clearMaskOnLostFocus: true, 'autoUnmask' : true});
            
            $('#Phone').inputmask("mask", {"mask": "(99) 999999999", placeholder:" ", clearMaskOnLostFocus: true});
            $('#presidentPhone').inputmask("mask", {"mask": "(99) 999999999", placeholder:" ", clearMaskOnLostFocus: true});
            $('#presidentCellPhone').inputmask("mask", {"mask": "(99) 999999999", placeholder:" ", clearMaskOnLostFocus: true});

            $("input.datepicker").inputmask(
                "dd/mm/yyyy",
                {
                    placeholder:"dd/mm/aaaa", "clearIncomplete": true
                }
            );

            $( "#CreationDate" ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                showButtonPanel: false,
                changeYear: true,
                yearRange: '1900:+0',//"c-150:c",
                onSelect: defaultModule.datepickeOnComplete
            });
            $( "#BornDate" ).datepicker({
                changeMonth: true,
                numberOfMonths: 1,
                changeYear: true,
                yearRange: '1900:+0',
                onSelect: defaultModule.datepickeOnComplete
            });

            $form = $('#frmRegister');
            $form.data('submited', false);

            $form.find('input.datepicker').on('keydown', function(event) {
                if (event.which >= 48 && event.which <= 57 || event.which >= 96 && event.which <= 105) {
                    $(this).datepicker('hide');
                }
            });
        
            //$form.find('input:eq(0)').focus();

            $form.find('input.radioYesNo').on(
                'change', function() {
                    var $this = $(this),
                        $fieldQual = $this.find('~ label.qual:eq(0), ~ input.qual:eq(0)');
                    if ($this.is(':checked') && $this.val()=='1') {
                        $fieldQual.attr('disabled', false).removeClass('disabled');
                        $fieldQual.addClass('{required:true}');
                        $fieldQual.filter(':eq(1)').focus();
                    } else if ($this.is(':checked') && $this.val()=='0') {
                        $fieldQual.removeClass('{required:true}');
                        $fieldQual.attr('disabled', true).addClass('disabled');
                        $fieldQual.filter(':eq(1)').val('');
                    }
                }
            );

            $form.find('input.changePassword').on(
                'change', function() {
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
            );

            $('select.fancy').chosen();

            $cepSearch.on('click', function() {
                getAddressByCep('');
            });
            
            $cepSearchPresident.on('click', function() {
                getAddressByCep('President');
            });
           
            $uf.on('change', function() {
                listCities($uf.val(),null,null,'');
                $('#CityId_chzn').removeClass('chzn-error');
            }).chosen();

            $city.on('change', function() {
                listNeighborhoods($city.val(),null,'');
            }).chosen();

            $ufPresident.on('change', function() {
                listCities($ufPresident.val(),null,null,'President');
                $('#CityIdPresident_chzn').removeClass('chzn-error');
            }).chosen();

            $cityPresident.on('change', function() {
                listNeighborhoods($cityPresident.val(),null,'President');
            }).chosen();

            $('select').on('change', function() {
                var id = $(this).attr('id');
                $('#' + id + '_chzn' ).removeClass('chzn-error');
            })

            

            $.validator.addMethod(
                "chosen",
                function(value, element) {
                    console.log('chosen validate');
                    return (value == null ? false : (value.length == 0 ? false : true))
                },
                "Selecione uma opcao"
            );

            jQuery.validator.messages.required = "";
            jQuery.validator.focusInvalid = true;
            validator = $form.validate({
                ignore: ':hidden:not(.chzn-done)',
                //focusInvalid: true,
                focusCleanup: true,
                rules: {
                    fancy: {
                        chosen: true
                    },
                    fanc2y: {
                        chosen: true
                    }
                },
                errorPlacement: function(error, element) {  
                    //$(element).attr({"title": error.append()});
                },
                invalidHandler: function(e, validator) {
                    e.preventDefault()
                    $(':input.error').eq(0).focus();
                    var errors = validator.numberOfInvalids();

                    var $divError = $('#errors-tab-2');
                    $('.chzn-error').removeClass('chzn-error')
                    if (errors) {
                        var message = errors == 1
                            ? 'Você esqueceu de 1 campo. Ele está destacado.'
                            : 'Você esqueceu de ' + errors + ' campos.  Eles estão destacados.';

                        message = "<p>" + message + "</p>";

                        $divError.html(message);

                        $.scrollTo(
                            $('#tabs'),200,
                            {
                                offset: -35,
                                onAfter: function() {
                                    setTimeout(function(){
                                        $divError.eq(0).show().effect('bounce');
                                    }, 200);
                                }
                            }
                        );

                        var $inputs = $('#tabs-2').find(":input:visible, .chzn-done");
                        $inputs.each(function(i, e) {

                            if($(e).hasClass('chzn-done') && $(e).hasClass('error') ){
                                var id = $(e).attr('id');
                                $('#' + id + '_chzn' ).addClass('chzn-error')
                            }

                        });

                    } else {
                        $divError.hide();
                    }
                },
                onkeyup: false,
                submitHandler: function() {
                    
                    $categorySectorId.attr('disabled', false);
                    
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
                                $('.error-box').html(json.messageError);
                                $(document).scrollTo('.error-box');
                                 $divError.show('bounce');
                                 $form.data('submited', false);
                                setTimeout(function(){escroll();}, 250);
                                return;
                            }
                            if (json.redirectUrlRegister) {
                                window.location = json.redirectUrlRegister;
                                return;
                            }
                            if (json.loadUrlRegister) {
                                $('div.content').load(json.loadUrlRegister);
                                return;
                            }
                        },
                        error:function(x,e){
                            $form.data('submited', false);
                            setTimeout(function(){escroll();}, 250);
                        }
                    });
                    return false;
                }
            });

            //modal CNAE POG
            (function ($) {
              $.fn.extend({
                mpeModal: function (options) {
                  var defaults = {
                    top: 100,
                    overlay: 0.5,
                    closeButton: null
                  };
                  var overlay = $("<div id='psmn_modal_overlay'></div>");
                  $("body").append(overlay);
                  options = $.extend(defaults, options);
                  return this.each(function () {
                    var o = options;
                    $(this).click(function (e) {
                      var modalId = $(this).attr("href");
                      $("#psmn_modal_overlay").click(function () {
                        closeModal(modalId)
                      });
                      $(o.closeButton).click(function () {
                        closeModal(modalId)
                      });
                      var modalHeight = $(modalId).outerHeight();
                      var modalWidth = $(modalId).outerWidth();
                      $("#psmn_modal_overlay").css({
                        display: "block",
                        opacity: 0
                      });
                      $("#psmn_modal_overlay").fadeTo(200, o.overlay);
                      $(modalId).css({
                        display: "block",
                        position: "fixed",
                        opacity: 0,
                        zIndex: 11000,
                        left: 50 + "%",
                        marginLeft: -(modalWidth / 2) + "px",
                        top: o.top + "px"
                      });
                      $(modalId).fadeTo(200, 1, function(){
                        var elementfocus = $('#form-search-cnae input[type=text]');
                        if (elementfocus.length) {
                            elementfocus.focus();
                        }
                      });
                      e.preventDefault()
                    })
                  });
                  function closeModal(modalId) {
                    $("#psmn_modal_overlay").fadeOut(200);
                    $(modalId).css({
                      display: "none"
                    });
                    $('#busca-cnae').val('');
                    $('#cnae-list-container').empty();
                  }
                }
              })
            })(jQuery);

            $("a[rel*=psmn_modal],button[rel*=psmn_modal]").mpeModal({
                //top : 200,
                overlay : 0.4,
                closeButton: ".modal_close"
            });
            
            $('#form-search-cnae').validate({
                submitHandler: function(form) {
                  var data = $('#form-search-cnae').serialize()
                  $('#cnae-list-container').load('/questionnaire/register/cnae?' + data +' #cnae-list', function() {
                    $('#cnae-list').find('input:eq(0)').focus();
                  });
                  return false;
                }
            });
            $('#Cnae').on('click', function(){
                $('#bt-search-cnae').trigger('click')
            });

            $('.category').each(function(){
               var el = $(this);
               if(el.prop('checked'))
                 el.trigger('click');
            });

            showSector();

            $('#cnae-list-container').delegate('li.item', 'click', function(){
              var $this = $(this);
              var value = $this.find('.num').text() + ' | ' + $this.find('.desc').text();
              $('#Cnae').val( value )
              $('#psmn_modal_overlay').click()
              $('#CompanyHistory').focus()
            });

            setFormStepChangeTriggers();
        return this;
        }
    };

}());

$(function() {
    try {
        registerModule.init();
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
