define(
  [
    'libraries/inputmask/inputmask',
    'libraries/mpe_modal/mpe_modal',
    'libraries/chosen/chosen.min',
    'libraries/capital',
  ],
  function() {
    return {
      init: function(mod, params) {

        $.ajaxSetup({cache: false});

        var that = this;
        enterpriseAdmModule.init();
      }
    }
  }
);

var enterpriseAdmModule = (function () {
    
    var $qstn, $blockDefault, $criterionDefault, $questionDefault, $frm;
    
    openIframeCompany = function(e) {
        e.preventDefault();
        var $this = $(this);
        $('#main').hide();
        $('#areaIframe').html('<iframe src="' + $this.attr('href') + '" width="100%" height="200" frameborder="0" scrolling="no" id="frame"></iframe>')
        var height = document.documentElement.clientHeight;
        height -= document.getElementById('frame').offsetTop;
        document.getElementById('frame').style.height = height +"px";
    }
    
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
                    $neighborhoodId.append($("<option></option>") .attr("value",json.neighborhoods[i].I).text(json.neighborhoods[i].N));
                }
                $neighborhoodId.trigger("liszt:updated");
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

        });
    };

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
    changeOrder = function() {
        $('#orderByValue')[0].value = this.value;
        $frm.submit();
        return true;
    };
    return {
        init: function() {
            $uf = $('#Uf');
            $city = $('#CityId');

            $frm = $('#frm');
            $frm.on('submit', frmSubmit);

            /*
            $('ul.rpp li.rppSort')
                .on('change', 'select', changeOrder)
                .find('label')
                .each(function() {
                    var $this = $(this);
                    $this.prev('span').show();
                    $this.replaceWith($frm.find('select.sortBy').clone().show());
                    
                });
            */

            $('#tbCoops td.chk').on('change', 'input.checkElegible', checkElegible);

            $uf.on('change', function() {
                listCities($uf.val());
            });

            $city.on('change', function() {
                 listNeighborhoods($city.val());
            });

            $frm.find('select.fnc').chosen();
            
            $('#cnpj').inputmask("mask", {"mask": "99.999.999/9999-99", "clearIncomplete": true});
             
            var $tableData = $('#table-details');
            //$tableData.find('a[rel=openIframe]').on('click', openIframeCompany);
            
            $tableData.find('a.geraNovaSenha').on('click', function(e){
                e.preventDefault();
                var $this = $(this), linhaempresa = $this.closest('tr'),
                nomeempresa = linhaempresa.find('td.td-empresa span:eq(0)').text(),
                enterpriseKey = linhaempresa.data('idkey');
                
                if (confirm('Deseja gerar um nova senha para a empresa [' + $.trim(nomeempresa) + ']?')) {
                    $.ajax({
                        url: BASE_URL + '/management/enterprise/regenerate-password/format/json',
                        type: 'post',
                        cache: false,
                        dataType: 'json',
                        data: { 'id-key' : enterpriseKey  }
                    }).done(function(json, $statusText, jqXHR) {
                        if (!($statusText === 'success') || !json.itemSuccess) {
                            alert(json.messageError);
                            return;
                        }
                        alert('Senha gerada, a empresa receberá no e-mail cadastrado.');
                    });
                }
            });

            $('.unverified').click(function(){
                var enterpriseIdKey = $(this).data('enterprise-id-key');
                submitRespond(enterpriseIdKey);
            });
            
            return this;
        }
    };
}());