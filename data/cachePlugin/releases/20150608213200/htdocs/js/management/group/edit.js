/* Javascript OO Module Pattern */
var groupModule = (function () {
    
    var $frm;
    
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
    checkChange = function(){
        var $thisCheck = $(this),
            $enterpriseId = $thisCheck.attr('data-enterprise-id');
            $isChecked = (this.checked === true)? 1 : 0;

        $.ajax({
            url: BASE_URL + '/management/group/set-group-to-enterprise/format/json',
            type: 'post',
            dataType: 'json',
            data: {
                enterpriseId: $enterpriseId, groupId: $('#GroupId').val(), checked: $isChecked
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('erro');
                    return;
                }
                $thisCheck.closest('label').find('span').text(
                    $isChecked? ' ' + $('#GroupName').val() : ''
                );
            }
        });
    };
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
            
            $('ul.rpp li.rppSort')
                .on('change', 'select', changeOrder)
                .find('label')
                .each(function() {
                    var $this = $(this);
                    $this.prev('span').show();
                    $this.replaceWith($frm.find('select.sortBy').clone().show());
                });

            $('input.checkbox').on('change', checkChange);

            $uf.on('change', function() {
                listCities($uf.val());
            });

            $city.on('change', function() {
                listNeighborhoods($city.val());
            });
            
            $frm.find('select.fnc').chosen();

            return this;
        }
    };
}());

$(function() {
    try {
        groupModule.init();
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