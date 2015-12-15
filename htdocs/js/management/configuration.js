var configurationModule = (function () {
    
    var $frm; //, $divError, $divErrorMsg; 9,8188-9951
    
    listBlocks = function(questionnaireId,blockName){
        var $blockId = blockName; //$('#blockScoreDiagnostico');
        $blockId.find('option').remove();
        if (questionnaireId == "") {
            $blockId.append($("<option></option>") .attr("value",'') .text('Todos'));
            $blockId.trigger("liszt:updated");
            return;
        }
        $blockId.append($("<option></option>") .attr("value",'') .text('Carregando...'));
        $blockId.trigger("liszt:updated");
        $.ajax({
            async: false,
            url: BASE_URL +'/management/block/index/questionnaire_id/'+questionnaireId+'/format/json',
            type: 'post',
            dataType: 'json',
            data: {
            },
            success: function(json, $statusText) {
                if (!($statusText == 'success') || !json.itemSuccess) {
                    alert('Error load blocks');
                }
                $blockId.find('option').remove();
                for(var i in json.getAllBlock) {
                    $blockId.append($("<option></option>") .attr("value",json.getAllBlock[i].Id).text(json.getAllBlock[i].Value));
                }
                $blockId.trigger("liszt:updated");
            }
        });
    }
    
    var $sortableParams = {
        distance: 3
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
            
            $frm = $('#frmConfiguration');
            $diagnostico = $('#diagnosticoId');
            $autoavaliacao = $('#autoavaliacaoId');
            $frm.on('submit', frmSubmit);
            
            $('#diagnostico_score_value').inputmask("mask", 
                {
                    "mask": "99999999999", 
                    placeholder:" ", 
                    clearMaskOnLostFocus: true, 
                    'autoUnmask' : true
                }
            );
                
            $diagnostico.on('change', function() {
                listBlocks($diagnostico.val(),$('#blockScoreDiagnostico'));
            });
            $autoavaliacao.on('change', function() {
                listBlocks( $autoavaliacao.val(), $('#blockScoreGovAutoavaliacao') );
                listBlocks( $autoavaliacao.val(), $('#blockScoreRadarAutoavaliacao') );
            });
            
            
            $frm.find('select.fnc').chosen();
            
            return this;
        }
    };
}());

$(function() {
    try {
        configurationModule.init();
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