/* Javascript OO Module Pattern */

var userAdmModule = (function () {
    var $frm;
    
    var $sortableParams = {
        distance: 3
    };
    
    var initSortable = function(elemen) {
    },
    frmSubmit = function() {
        $('#overlay1').show();
        $.prettyLoader.show();
        return true;
    },
    changeQstn = function(){

        window.location = BASE_URL + '/management/role/index/questionnaire_id/' + this.value;

        return true;
    };

    return {
        init: function() {
            $frm = $('#frm');
            $frm.find('select.fnc').chosen();
            $frm.on('submit', frmSubmit);
            $('#Cpf').inputmask("mask", {"mask": "999.999.999-99", "clearIncomplete": true});

            $('#QuestionnaireId')
                .on('change', changeQstn);
                //.chosen();
                
            
            return this;
        }
    };
}());

$(function() {
    try {
        userAdmModule.init();
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