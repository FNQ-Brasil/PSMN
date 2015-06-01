var regionalModule = (function () {

    var $form, $divError, $divErrorMsg, $uf;

    listCities = function(stateId){
        $cityId = $('#citys');
        capital = searchCities(stateId);

        $cityId.find('option').remove();
        $cityId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $cityId.trigger("liszt:updated")
        
        $.ajax({
            url: BASE_URL +'/default/city/index/format/json', // state_id/'+stateId+'
            type: 'post',
            dataType: 'json',
            data: {
                state_id : stateId,
                serviceArea : SERVICE_AREA
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load cities.');
                }
                
                $cityId.find('option').remove();
                //$cityId.append($("<option></option>") .attr("value",capital.capitalID).text(capital.capitalNAME));
                
                for (var c in json.cities) {
                    if (json.cities[c].Id == capital.capitalID){
                        $cityId.append($("<option></option>") .attr("value",capital.capitalID).text(capital.capitalNAME));
                    }
                }
                
                for (var i in json.cities) {
                    if (json.cities[i].Id != capital.capitalID){
                        $cityId.append($("<option></option>") .attr("value",json.cities[i].Id).text(json.cities[i].Name));
                    }
                }

                $cityId.trigger("liszt:updated");
                $('#citys_chzn').find('input:eq(0)').focus();
               // listNeighborhoods(capital.capitalID);
                
            }
        });
    }

    listNeighborhoods = function(cityId){
        $neighborhoodId = $('#neights');

        $neighborhoodId.find('option').remove();
        $neighborhoodId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $neighborhoodId.trigger("liszt:updated");
        
        $.ajax({
            url: BASE_URL +'/default/neighborhood/index/format/json',
            type: 'post',
            dataType: 'json',
            data: { 
                city_id : cityId,
                serviceArea:SERVICE_AREA
            },
            success: function(json, $statusText) {
                var $newNeighborhoods;
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load neighborhoods');
                }
                $neighborhoodId.find('option').remove();
                for (var i in json.neighborhoods) {
                    //console.log(i)
                    //$newNeighborhoods = (i != 0)? $("<option></option>") : $("<option selected></option>"); 
                    $newNeighborhoods = $("<option></option>");
                    $neighborhoodId.append( $newNeighborhoods.attr("value",json.neighborhoods[i].Id).text(json.neighborhoods[i].Name));
                }
                $neighborhoodId.trigger("liszt:updated")
                $('#neights_chzn').find('input:eq(0)').focus();
            }
        });
    }

     return {
         
        init: function() {
            
            var $now = new Date();
            
            $divError = $('div.error');
            $divErrorMsg = $divError.find('b');
            
            $uf = $('#Uf');
            $city = $('#CityId');
            $allCities = $('#allCities'); 
            $neighborhood = $('#NeighborhoodId');
            
            $form = $('#frmRegional');
            $form.data('submited', false);
    
           // $('select.fancy').chosen();
            
            $('#allUfs').on('change', function(){
                var $this = $(this),
                $isSelectUfs = ($this.val() == 's');
                $('#areaSelectUfs').toggle($isSelectUfs);
                if (!$isSelectUfs) {
                    $('#areaSelectCities, #areaSelectNeights').hide();
                    $('#ufs, #citys, #allCities, #neights, #allNeights').val('').change().trigger("liszt:updated");
                } else {
                    $('#ufs_chzn').find('input:eq(0)').focus();
                }
            })//.chosen();
            
            $('#ufs').on('change', function(){
                $('#areaSelectCities, #areaSelectNeights').hide();
                $('#citys, #allCities, #neights, #allNeights').val('').change().trigger("liszt:updated");
            })//.chosen();

            $('#allCities').on('change', function(){
                var $this = $(this),
                    $areaSelectCities = $('#areaSelectCities');
                    $isSelectCities = ($this.val() == 's'),
                    $ufs = $('#ufs');

                if ($isSelectCities && $ufs.find('option:selected').length !== 1) {
                    $this.val('').change().trigger("liszt:updated");;
                    alert('Para seleção de cidades é necessário selecionar somente um Estado.')
                    return false;
                } else if (!$isSelectCities) {
                    $areaSelectCities.hide();
                    $('#neights, #allNeights').val('').change().trigger("liszt:updated");
                    return true;
                }

                $areaSelectCities.show();
                listCities($ufs.val()[0]);
                return true;
            })//.chosen();
            
            $('#citys').on('change', function(){
                $('#areaSelectNeights').hide();
                $('#neights, #allNeights').val('').change().trigger("liszt:updated");
            })//.chosen();
            
            $('#allNeights').on('change', function(){
                var $this = $(this),
                    $areaSelectNeights = $('#areaSelectNeights');
                    $isSelectNeights = ($this.val() == 's'),
                    $cities = $('#citys');

                if ($isSelectNeights && $cities.find('option:selected').length !== 1) {
                    $this.val('').change().trigger("liszt:updated");;
                    alert('Para seleção de bairros é necessário selecionar somente uma Cidade.')
                    return false;
                } else if (!$isSelectNeights) {
                    $areaSelectNeights.hide();
                    $('#neights').val('').change().trigger("liszt:updated");
                    return true;
                }

                $areaSelectNeights.show();
                listNeighborhoods($cities.val()[0]);
                return true;
            })//.chosen();

 
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
                            if (json.redirectUrlRegional) {
                                window.location = json.redirectUrlRegional;
                                return;
                            }
                            if (json.loadUrlRegional) {
                                $('#content').load(json.loadUrlRegional);
                                return;
                            }
                            
                        }
                    });
                    return false;
                }
            });
            $('select').chosen();
            $('#msgError').hide();
            return this;
        }
    };

}());

$(function() {
    try {
        regionalModule.init();
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
