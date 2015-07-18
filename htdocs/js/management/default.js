/* Javascript OO Module Pattern */

var enterpriseAdmModule = (function () {
    var $qstn, $blockDefault, $criterionDefault, $questionDefault;
    
    var $sortableParams = {
        distance: 3,
        delay: 20,
        opacity: 0.8,
        cursor: 'move',
        forceHelperSize: true,
        axis: 'y',
        update: function() {            },
        start: function(e, ui) {
            $('a.move:visible, a.delete:visible, a.expand:visible').not($(ui.item).find('form a.move:eq(0)'))
                .addClass('vHidden');
        },
        stop: function(e, ui) {
             $('a.vHidden').removeClass('vHidden');
        }
    };
    
    var initSortable = function(elemen) {

    },
    
    expand = function() {
 

        return false;
    };
  
    return {
        init: function() {
  

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