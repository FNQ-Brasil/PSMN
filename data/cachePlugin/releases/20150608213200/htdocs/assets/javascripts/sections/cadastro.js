define(
  [
    'libraries/validate/jquery.validate',
    'libraries/inputmask/inputmask',
    'libraries/mpe_modal/mpe_modal',
  ],
  function() {
    return {
      init: function(mod, params) {

      $.ajaxSetup({
        cache: false
      });

        var that = this;
        that.validate();
        that.masks();
        that.checkbox();
        that.closeMessage();
        that.cepChange();
        that.stateChange();
        that.cityChange();
        that.SearchCnae();
        that.changePassword();
        that.radioPoints();
        that.checkRadio();
        that.newsletter();


      },

      cepData: {
        CityId : false,
        NeighborhoodId : false
      },

      newsletter: function(){
        $('[name="newsletter"]').on('change', function(){
          var opt = $(this).val();
          if( opt == 1 ){
            $('.NewsletterS *').show()
          } else{
            $('.NewsletterS *').hide()
          }
        }).trigger('change')
      },

      checkRadio: function(){
        (function ($) {
          $.fn.checkedPolyfill = function (options) {
            function checkValue ($elem) {
              var $label = $('label[for="' + $elem.attr('id') + '"]');
              // TODO: also find labels wrapped around the input
              if ($elem.prop('checked')) {
                $elem.addClass('checked');
                $label.addClass('checked');
              } else {
                $elem.removeClass('checked');
                $label.removeClass('checked');
              }
              // We modify the label as well as the input because authors may want to style the labels based on the state of the chebkox, and IE7 and IE8 don't fully support sibling selectors.
              // For more info: http://www.quirksmode.org/css/contents.html
              return $elem;
            }

            return this.each(function () {
              var $self = $(this);
              if ($self.prop('type') === 'radio') {
                $('input[name="' + $self.prop('name') + '"]').change(function() {
                  checkValue($self);
                });
              } else if ($self.prop('type') === 'checkbox') {
                $self.change(function() {
                  checkValue($self);
                });
              }
              checkValue($self); // Check value when plugin is first called, in case a value has already been set.
            });

          };
        })(jQuery);
        $('input:radio').checkedPolyfill();
      },

      radioPoints: function(){
        var pointsmessages = {
          0 : 'A empresa se preocupa com muito poucos aspectos da gestão, o que a coloca em risco permanente. <br /> Ao aplicar o modelo de gestão do Prêmio MPE Brasil, encontrará inúmeras e importantes oportunidades para melhorar e aumentar suas chances de sucesso.',
          1 : 'A empresa se preocupa com muito poucos aspectos da gestão, o que a coloca em risco permanente. <br /> Ao aplicar o modelo de gestão do Prêmio MPE Brasil, encontrará inúmeras e importantes oportunidades para melhorar e aumentar suas chances de sucesso.',
          2 : 'A empresa se preocupa com alguns aspectos da gestão e encontrará importantes oportunidades para melhorar ao aplicar o modelo de gestão do Prêmio MPE Brasil, aumentando as suas chances de sucesso.',
          3 : 'A empresa já se preocupa com aspectos importantes da gestão e ao aplicar o modelo de gestão do Prêmio MPE Brasil encontrará muitas oportunidades para melhorar, aumentando as suas chances de sucesso.',
          4 : 'A empresa já se preocupa com aspectos importantes da gestão e ao aplicar o modelo de gestão do Prêmio MPE Brasil encontrará muitas oportunidades para melhorar, aumentando as suas chances de sucesso.',
        }
        if( $('[name="enterprise[fantasy_name]"]').val() == ""){
          $('#form-cadastro').find(':radio').removeAttr('checked')
        }
        var  $radioPointsMessage = $('#radio-points-message')
        $('.radioPoints').on('change', function(){
          if( $('.radioPoints:checked').size() === 4 ){
            var points = $('.radioPoints[value="1"]:checked').size()
            $radioPointsMessage.empty().html( pointsmessages[points] )
          }
        }).trigger('change')
      },

      SearchCnae: function(){
        $("a[rel*=mpe_modal]").mpeModal({
            //top : 200,
            overlay : 0.4,
            closeButton: ".modal_close"
        });
        require([
          "libraries/validate/messages_pt_BR",
          "libraries/validate/additional-methods",
          ],
          function(util) {
          $('#form-search-cnae').validate({
            submitHandler: function(form) {
              var data = $('#form-search-cnae').serialize()
              $('#cnae-list-container').load('/questionnaire/register/cnae?' + data +' #cnae-list');
              return false;
            }
          })
          }
        );
        $('#cnaeNumber, #cnaeDesc').on('click', function(){
            $('#bt-search-cnae').trigger('click')
        })
        $('#cnae-list-container').delegate('li.item', 'click', function(){
          var $this = $(this);
          $('#cnaeNumber').val( $this.find('.num').text() )
          $('#cnaeDesc').val( $this.find('.desc').text() )
          $('#mpe_modal_overlay').click()
        })
      },

      enableScreen: function(enable){
        if(enable){
          $('#enableScreen').remove();
        } else {
          $('body').append('<div id="enableScreen" />');
        }
      },

      cepChange: function(){
        var that = this;
        $('[name*="[cep]"]').on('change, keyup', function(){
          var $el = $(this);
          if( $el.inputmask('unmaskedvalue').length == 8 ){
            var cepVal = $el.val();
            var userType = $el.attr('name').replace('[cep]', '');
            $.post('/default/address/index/format/json', { cep: cepVal }, function(data){
              if(data.itemSuccess){
                that.cepData.CityId = data.address.CityId;
                that.cepData.NeighborhoodId = data.address.NeighborhoodId;
                $('[name="' + userType + '[state_id]"]').val( data.address.UfCode ).trigger('change');
                $('[name="' + userType + '[name_full_log]"]').val( data.address.NameFullLog ).trigger('change').focus();
              }
            });
          }
        });
      },

      stateChange: function(){
        var that = this;
        $('[name*="[state_id]"]').on('change', function(){
          var $el = $(this);
          var stateId = $el.val();
            var userType = $el.attr('name').replace('[state_id]', '');
          $.post('/default/city/index/state_id/'+stateId+'/format/json', function(data){
            if(data.itemSuccess){
              var options = '';
              $.each( data.cities, function(i, e){
                options += '<option value="' + e.Id + '">' + e.Name + '</option>'
              });
            }
            var userType = $el.attr('name').replace('[state_id]', '');
            $('[name="' + userType + '[city_id]"]').html(options);
            if( that.cepData.CityId  ){
              $('[name="' + userType + '[city_id]"]').val( that.cepData.CityId );
              that.cepData.CityId = false;
            }
            $('[name="' + userType + '[city_id]"]').trigger('change');
          })

        });
      },

      cityChange: function(){
        var that = this;
        $('[name*="[city_id]"]').on('change', function(){
          var $el = $(this);
          var cityId = $el.val();
          $.post('/default/neighborhood/index/city_id/'+cityId+'/format/json', function(data){
            if(data.itemSuccess){
              var options = '';
              $.each( data.neighborhoods, function(i, e){
                options += '<option value="' + e.I + '">' + e.N + '</option>'
              });
            }
            var userType = $el.attr('name').replace('[city_id]', '');
            $('[name="' + userType + '[neighborhood_id]"]').html(options);
              if( that.cepData.NeighborhoodId  ){
                $('[name="' + userType + '[neighborhood_id]"]').val( that.cepData.NeighborhoodId );
                that.cepData.NeighborhoodId = false;
              }
            $('[name="' + userType + '[neighborhood_id]"]').trigger('change');
          })

        });
      },

      checkbox: function(){
        $(':checkbox')
          .on('change', function(){
            var $label = $(this).siblings('span');
            if( $(this).is(':checked') ){
              $label.addClass('checked');
            }else{
              $label.removeClass('checked');
            }
          })
          .on('focus', function(){
            $(this).siblings('span').addClass('focus');
          })
          .on('blur', function(){
            $(this).siblings('span').removeClass('focus');
          }).trigger('change');
      }, // checkbox

      changePassword: function(){
        var $password = $('[name="user[keypass]"], [name="user[keypass_confirm]"]');
        $('[name="user[change_password]"]').on('change', function(){
            if( $(this).is(':checked') ){
              $password.removeAttr('disabled')
            }else{
              $password.attr('disabled', 'disabled')
            }
          })
      },

      validate: function(){
        $(':input').each(function(i, e){
          if( $(this).attr('required') ){
            $(this).siblings('span').append('<i title="Campo obrigatório">*</i>')
          }
        })

        var that = this;
        require([
          "libraries/validate/messages_pt_BR",
          "libraries/validate/additional-methods",
          ],
          function(util) {
            $('#form-cadastro').validate({
              submitHandler: function(form) {
                that.save($(form));
                return false;
              },
              errorPlacement: function(error, element) {
                $(element).parent('label').append(error);
               }
            })
          }
        );
      },

      save: function($form){
        var that = this
        that.enableScreen(false)
        jQuery.post( $form.attr('action') , $form.serialize() , function(data, textStatus, jqXHR){
          that.enableScreen(true);
          if( data.itemSuccess  ){
            if( data.itemEdit  ){
              that.showMessage('success', 'Dados atualizados com sucesso.')
              $form.find('fieldset, .tip-form, button').slideUp();
              
              var urlredirect = (typeof data.enterpriseIdKey !== 'undefined')?
                BASE_URL + '/management/enterprise/edit/id_key/' + data.enterpriseIdKey + '/menu/false'
                : BASE_URL  + '/questionnaire/index/list-qsts/successItem/1/?444';
              
              $form.append('<a  class="link-continue" href="' + urlredirect  + '">Continuar</a>');
              $('.section-nav a').eq(2).removeClass('inactive')
                .attr('href', urlredirect);
            } else {
              that.showMessage('success', 'Cadastro efetuado com sucesso.')
              var urlredirect = (typeof data.urlRedirect !== 'undefined')?
                data.urlRedirect : BASE_URL + '/management/enterprise/edit/id_key/' + data.enterpriseIdKey + '/menu/false';
              $form.after('\
                <div class="sucess-message-form">\
                <h3>Bem vindo ao Prêmio MPE Brasil de Competitividade.</h3>\
                 <p>Além de concorrer ao título de melhor micro e pequena empresa do ano, em oito categorias, as empresas participantes recebem gratuitamente um diagnóstico da gestão empresarial.</p>\
                  <p><a href="' + urlredirect  + '">Clique aqui para continuar</a>.</p>\
                  </div>\
                ')

              $form.find('fieldset, .tip-form').slideUp();
              $('.section-nav a').eq(1).removeClass('inactive').attr('href', urlredirect);
            }
          } else {
            that.showMessage('error', data.messageError);
          }
          $('body').animate({scrollTop : 0},'slow');

        });
      },

      showMessage: function(type, message){
        $('#form-cadastro').prepend('<div class="form-message ' + type + '">' + message + '</div>');

        $('.form-message', '#form-cadastro').on('click', function(){
          $(this).slideUp('fast', function(){
            $(this).remove()
          })
        })

      },

      closeMessage: function(){
        $('.form-message').on('click', function(){
          $(this).slideUp('fast', function(){
            $(this).remove()
          })
        })
      },

      masks: function(){
        $('[name="user[cpf]"]').inputmask({ 'mask' : '999.999.999-99'});
        $('[name="enterprise[cnpj]"]').inputmask({ 'mask' : '99.999.999/9999-99'});
        //

      }, // masks


    }
  }
);


