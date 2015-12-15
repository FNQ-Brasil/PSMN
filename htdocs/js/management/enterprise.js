/* Javascript OO Module Pattern */
var enterpriseAdmModule = (function () {
    
    var $qstn, $blockDefault, $criterionDefault, $questionDefault, $frm;
    
    listCities = function(stateId) {
        $cityId = $('#CityId');
        $neighborhoodId = $('#NeighborhoodId');
        $cityId.find('option').remove();
        
            $neighborhoodId.find('option').remove();
            $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Todos'));
            $neighborhoodId.trigger("liszt:updated");
        if (stateId == "") {
            $cityId.append($("<option></option>") .attr("value","").text("Todas"));
            $cityId.trigger("liszt:updated");
            return;
        }
        
        capital = searchCities(stateId);
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
                $cityId.append($("<option></option>") .attr("value","").text("Todas"));
                $cityId.append($("<option></option>") .attr("value",capital.capitalID).text(capital.capitalNAME));
                for(var i in json.cities) {
                    if(json.cities[i].Id != capital.capitalID){
                        $cityId.append($("<option></option>") .attr("value",json.cities[i].Id).text(json.cities[i].Name));
                    }
                }
                $cityId.trigger("liszt:updated");
                //listNeighborhoods(capital.capitalID);
            }
        });
    }

    submitRespond = function(enterpriseIdKey) {
       var dataPost = {
            'format': 'json',
            'enterprise-id-key': enterpriseIdKey
       };

       $.ajax({
          url: BASE_URL + '/management/enterprise/verify/',
          type: 'get',
          cache: false,
          dataType: 'json',
          data: dataPost
       }).done(function(json, $statusText, jqXHR) {
          var selected = $("input[data-enterprise-id-key='"+enterpriseIdKey+"']");
          $(selected).prop('checked','checked');
          $(selected).prop('disabled','disabled');
       });
    };

    listNeighborhoods = function(cityId){
        $neighborhoodId = $('#NeighborhoodId');
        $neighborhoodId.find('option').remove();
        
        if (cityId == "") {
            $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Todos'));
            $neighborhoodId.trigger("liszt:updated");
            return;
        }
        
        $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $neighborhoodId.trigger("liszt:updated");
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
                $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Todos'));
                for(var i in json.neighborhoods) {
                    $neighborhoodId.append($("<option></option>") .attr("value",json.neighborhoods[i].Id).text(json.neighborhoods[i].Name));
                }
                $neighborhoodId.trigger("liszt:updated");
            }
        });
    }

    var $sortableParams = {
        distance: 3
    };
    
    var initSortable = function(elemen) {
    },
    checkElegible = function() {
        var $thisCheck = $(this),
            $enterpriseId = $thisCheck.attr('data-enterprise-id');
            $isChecked = (this.checked === true)? 1 : 0;
        $.ajax({
            url: BASE_URL + '/management/enterprise/do-premio-eligibility/format/json',
            type: 'post',
            dataType: 'json',
            data: {
                id: $enterpriseId, eligibility: $isChecked
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Não foi possível torná-la (in)elegível no momento.');
                    return;
                }
                var $handIco = $thisCheck.closest('td').find('span.icon'),
                    $isElegibility = json.eligibility == '1';
                    $handIco.toggleClass('red', !$isElegibility).toggleClass('green', $isElegibility);
                    $handIco.find('span').text($isElegibility? 'q' : 'r');
                    return;                        
            }
        });
    },
    frmSubmit = function() {
        $('#overlay1').show();
        $.prettyLoader.show();
        return true;
    },
    winning_notification_submit = function(e) {
        e.preventDefault();

        var $form = $(e.currentTarget);
        var state_id = $form.find('[name=state_id]').val();
        var competition_id = $form.find('[name=competition_id]').val();
        var message = $form.find('textarea').val();
        var enterprise_ids = [];
        $form.find('[name=enterprise_ids\\[\\]]').each(function(i,e){ enterprise_ids.push($(e).val()); });

        $.ajax({
            url: BASE_URL + $form.attr('action'),
            type: 'post',
            dataType: 'json',
            data: {enterprise_ids: enterprise_ids, message: message, state_id: state_id, competition_id: competition_id},
            success: function(){
                alert('As empresas foram adicionadas a lista de notificação. O envio dos e-mails será realizado em segundo plano.');
                location.reload();
            },
            error: function(){
                alert('Não foi possível adicionar as empresas a lista de notificação.');
            }
        });

        return false;
    },
    changeOrder = function() {
        $('#orderByValue')[0].value = this.value;
        $frm.submit();
        return true;
    };
    return {
        Appraisers: {
          data: {
            elements: {
              body: $('body'),
              status: $('.change-ranking'),
              options: $('.change-ranking-option'),
              cancel: {
                status: $('.cancel-ranking'),
                items: $('.cancel-ranking-fieldset'),
                notes: $('.cancel-ranking-note'),
                save: $('.cancel-ranking-note-save'),
                edit: $('.cancel-ranking-note-edit')
              }
            }
          },
          lock: function (status) {
            status = ((status === undefined) ? true : status);
            $(status ? '<div id="enableScreen" />' : '#enableScreen')[(status ? 'appendTo' : 'remove')](status ? this.data.elements.body : undefined);
            return this;
          },
          unlock: function () {
            return this.lock(false);
          },
          editCancelNote: function (element, event) {
            event.preventDefault();
            element._ = (element._ || {
              parent: element.parents('tr')
            });
            element._.item = element._.parent.find(this.data.elements.cancel.status).trigger('edit', true);
          },
          saveCancelNote: function (element, event) {
            event.preventDefault();
            element._ = (element._ || {
              parent: element.parents('tr')
            });
            element._.item = element._.parent.find(this.data.elements.cancel.items).data('saved', false);
            element._.note = element._.item.find(this.data.elements.cancel.notes);
            element._.salvable = ($.trim(element._.note.val()) !== '');
            if (element._.salvable) {
              this.lock();
              element._.note.prop('disabled', true);
                var urlPost = $('body').hasClass('pg-management-enterprise-classificadas')?
                    '/management/enterprise/do-desclassificar-verificacao/format/json'
                    : '/management/enterprise/do-desclassificar/format/json';
                if ($('body').hasClass('pg-management-enterprise-finalistas')) { 
                    urlPost = '/management/enterprise/do-desclassificar-finalista/format/json';
                }
                
                var etapaPost = (
                        $('body').hasClass('pg-management-enterprise-candidatas-nacional')
                    )? 'nacional' : 'estadual';
                etapaPost = (
                      $('body').hasClass('pg-management-enterprise-classificadas-nacional')
                    )? 'nacional-fase2' : etapaPost;
                
              $.ajax(
                {
                  url: (BASE_URL + urlPost),
                  type: 'post',
                  dataType: 'json',
                  data: {
                    idKey: element._.parent.data('idkey'),
                    checked: true,
                    justificativa: $.trim(element._.note.val()),
                    etapa: etapaPost
                  },
                  error: function () {
                    enterpriseAdmModule.Appraisers.unlock();
                    element._.note.prop('disabled', false);
                    alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                  },
                  success: function(data) {
                    enterpriseAdmModule.Appraisers.unlock();
                    element._.note.prop('disabled', false);
                    if (data.itemSuccess) {
                      element._.item.removeClass('error').hide().data('saved', true);
                      element._.parent.prev().addClass('editable');
                    } else {
                      alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                    }
                  }
                }
              );
            } else {
              element._.item.addClass('error');
            }
          },
          clear: function (element) {
            element.each(function () {
              this._ = (this._ || {
                dom: $(this)
              });
              this._.parent = this._.dom.parents('tr');
              this._.item = this._.parent.find(enterpriseAdmModule.Appraisers.data.elements.cancel.items);
              this._.note = this._.item.find(enterpriseAdmModule.Appraisers.data.elements.cancel.notes);
             // this._.parent.prev().find(enterpriseAdmModule.Appraisers.data.elements.cancel.status).prop('checked', false);
            });
          },
          modifyCancelNote: function (element) {
            element._ = (element._ || {
              parent: element.parents('tr')
            });
            element._.item = element._.parent.find(this.data.elements.cancel.items).data('saved', false);
            return this;
          },
          confirmToggleCancel: function (element) {
            element._ = (element._ || {
              parent: element.parents('tr')
            });
            element._.item = element._.parent.find(this.data.elements.cancel.items);
            element._.saved = ((element._.item.data('saved') !== undefined) ? element._.item.data('saved') : true);
            element._.visible = element._.item.is(':visible');
            return (!element._.visible ? true : element._.saved);
          },
          toggleCancel: function (element, triggered) {
      //xxx
            element._ = (element._ || {
              parent: element.parents('tr'),
              checked: element.is(':checked'),
              changeable: true
            });
            
            element._.item = element._.parent.next().find(this.data.elements.cancel.items);
            element._.items = this.data.elements.cancel.items.not(element._.item);
            element._.changeable = (element._.checked ? (!(element._.items.filter(function () {
              return !enterpriseAdmModule.Appraisers.confirmToggleCancel($(this));
            }).length === 0) ? confirm('Você está tentando exibir outra justificativa sem salvar a atual.\nInformações podem ser perdidas.\nGostaria de continuar?') : element._.changeable) : element._.changeable);
            
            if (element._.changeable) {
                //console.log( element._.item.show())
                if (element._.checked) {
                    element._.item.removeClass('error').show().focus();
                } else {
                    element._.item.removeClass('error').hide();
                }
              //.toggle();
              if (element._.checked) {
                //console.log(element._.items.hide());
                enterpriseAdmModule.Appraisers.clear(element._.items.hide());
              } else {
                this.lock();
                element.prop('disabled', true);
                var urlPost = $('body').hasClass('pg-management-enterprise-classificadas')?
                    '/management/enterprise/do-desclassificar-verificacao/format/json'
                    : '/management/enterprise/do-desclassificar/format/json';
                    
                if ($('body').hasClass('pg-management-enterprise-finalistas')) { 
                    urlPost = '/management/enterprise/do-desclassificar-finalista/format/json';
                }
                
                var etapaPost = (
                        $('body').hasClass('pg-management-enterprise-candidatas-nacional')
                    )? 'nacional' : 'estadual';
                etapaPost = (
                      $('body').hasClass('pg-management-enterprise-classificadas-nacional')
                    )? 'nacional-fase2' : etapaPost;

                $.ajax(
                  {
                    url: (BASE_URL + urlPost),
                    type: 'post',
                    dataType: 'json',
                    data: {
                      idKey: element._.parent.data('idkey'),
                      checked: false,
                      justificativa: '',
                      etapa: etapaPost
                    },
                    error: function () {
                      enterpriseAdmModule.Appraisers.unlock();
                      element.prop('disabled', false);
                      alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                    },
                    success: function(data) {
                      enterpriseAdmModule.Appraisers.unlock();
                      element.prop('disabled', false);
                      element._.parent.removeClass('editable');
                    }
                  }
                );
              }
            } else {
              if (!triggered) {
                element.prop('checked', false);
              }
            }
          },
          toggleStatus: function (element) {
            element._ = (element._ || {
              parent: element.parents('tr'),
              checked: element.is(':checked')
            });
            element._.label = {
              container: element._.parent.find('.change-ranking-label')
            };
            element._.field = {
              container: element._.parent.find('.change-ranking-field')
            };
            element._.field.items = element._.field.container.find(this.data.elements.options);
            element._.changeable = (element._.checked ? true : !(element._.field.items.filter(function () {
              return (parseInt($(this).val(), 10) !== 0);
            }).length > 0));

            if (!element._.changeable) {

              element.prop('checked', true);
              alert('Não é possível alterar esse campo enquanto houverem avaliadores definidos.\nPor favor, verifique.');
              element._.field.items.first().focus().select();

            } else {

              this.lock();
              element.prop('disabled', true);
                var urlPost = $('body').hasClass('pg-management-enterprise-classificadas')?
                    '/management/enterprise/do-classificar-verificacao/format/json'
                    : '/management/enterprise/do-classificar/format/json';

                if ($('body').hasClass('pg-management-enterprise-finalistas')) { 
                    urlPost = '/management/enterprise/do-classificar-finalista/posicao/' + element.val() +'/format/json';
                }
                
                var etapaPost = (
                        $('body').hasClass('pg-management-enterprise-candidatas-nacional')
                    )? 'nacional' : 'estadual';
                etapaPost = (
                      $('body').hasClass('pg-management-enterprise-classificadas-nacional')
                    )? 'nacional-fase2' : etapaPost;

                /*********************************************\
                 * TRATAMENTO ISOLADO DE FINALISTAS-NACIONAL *
                \*********************************************/

                if($('body').hasClass('pg-management-enterprise-finalistas-nacional')){
                    urlPost = '/management/enterprise/do-classificar-finalista/posicao/'+element.val()+'/format/json';
                    etapaPost = 'nacional';
                }

                /*********************************************\
                 * TRATAMENTO ISOLADO DE FINALISTAS-NACIONAL *
                \*********************************************/

                $.ajax(
                  {
                    url: (BASE_URL + urlPost),
                    type: 'post',
                    dataType: 'json',
                  data: {
                    idKey: element._.parent.data('idkey'),
                    checked: element._.checked,
                    etapa: etapaPost
                  },
                    error: function () {
                      enterpriseAdmModule.Appraisers.unlock();
                      element.prop('disabled', false);
                      alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                    },
                    success: function(data) {
                      enterpriseAdmModule.Appraisers.unlock();
                      element.prop('disabled', false);
                      if (data.itemSuccess) {
                        element._.label.container.toggle(!element._.checked);
                        element._.field.container.toggle(element._.checked);
                      } else {
                        alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                      }
                    }
                  }
                );
            }
            return this;
          },
          changeOption: function (element) {
            element._ = (element._ || {
              parent: element.parents('tr'),
              checked: element.is(':checked')
            });
            element._.label = {
              container: element._.parent.find('.change-ranking-label')
            };
            element._.field = {
              container: element._.parent.find('.change-ranking-field')
            };
            element._.field.items = element._.field.container.find(this.data.elements.options);
            element.prop('disabled', true);
            this.lock();
            var urlPost = $('body').hasClass('pg-management-enterprise-classificadas')?
                '/management/appraiser/set-checker-to-enterprise/format/json'
                : '/management/appraiser/set-appraiser-to-enterprise/format/json';
            $.ajax(
              {
                url: (BASE_URL + urlPost),
                type: 'post',
                dataType: 'json',
                data: {
                  idKey: element._.parent.data('idkey'),
                  tipo: element.data('type'),
                  appraiserId: element.val(),
                    etapa: ($('body').hasClass('pg-management-enterprise-candidatas-nacional'))?
                      'nacional' : 'estadual'
                },
                error: function () {
                  enterpriseAdmModule.Appraisers.unlock();
                  element.prop('disabled', false);
                  alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                },
                success: function(data) {
                  enterpriseAdmModule.Appraisers.unlock();
                  element.prop('disabled', false);
                  if (!data.itemSuccess) {
                    alert('Falha ao efetuar requisição!\nPor favor, tente novamente.')
                  }
                }
              }
            );
            return this;
          },
          bind: function () {
            this.data.elements.status.on(
              'change',
              function () {
                enterpriseAdmModule.Appraisers.toggleStatus($(this));
              }
            );
            this.data.elements.options.on(
              'change',
              function () {
                enterpriseAdmModule.Appraisers.changeOption($(this));
              }
            );
            this.data.elements.cancel.status.on(
              'change edit',
              function (e) {
                e.preventDefault();

                enterpriseAdmModule.Appraisers.toggleCancel($(this));
              }
            );
            this.data.elements.cancel.notes.on(
              'keyup',
              function () {
                enterpriseAdmModule.Appraisers.modifyCancelNote($(this));
              }
            );
            this.data.elements.cancel.save.on(
              'click',
              function (event) {
                enterpriseAdmModule.Appraisers.saveCancelNote($(this), event);
              }
            );
            this.data.elements.cancel.edit.on(
              'click',
              function (event) {
                enterpriseAdmModule.Appraisers.editCancelNote($(this), event);
              }
            );
            return this;
          },
          init: function () {
            return this.bind();
          }
        },
        init: function() {
            
            $uf = $('#Uf');
            $city = $('#CityId');

            $frm = $('#frm');
            $frm.on('submit', frmSubmit);

            $('ul.rpp li.rppSort')
                .on('change', 'select', changeOrder)
                .find('label')
                .each(function() {
                    var $this = $(this);
                    $this.prev('span').show();
                    $this.replaceWith($frm.find('select.sortBy').clone().show());
                    
                });

            $('#tbCoops td.chk').on('change', 'input.checkElegible', checkElegible);

            $uf.on('change', function() {
                listCities($uf.val());
            });

            $city.on('change', function() {
                 listNeighborhoods($city.val());
            });
            
            $('select.fancy').chosen();
            $('#cnpj').inputmask("mask", {"mask": "99.999.999/9999-99", "clearIncomplete": true});
            $('#cpf').inputmask("mask", {"mask": "999.999.999-99", "clearIncomplete": true});

            $('#winning_notification').submit(winning_notification_submit);

            $('.unverified').click(function(){
               var enterpriseIdKey = $(this).data('enterprise-id-key');
               submitRespond(enterpriseIdKey);
            });

            this.Appraisers.init();
            return this;
        }
    };
}());

$(function() {
    try {
        enterpriseAdmModule.init();
    } catch(e) {
        /*if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }*/
        throw e;
    }
});