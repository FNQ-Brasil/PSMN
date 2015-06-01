var appraiserModule = (function () {

    var $form, $divError, $divErrorMsg;

    init = function(){
         $('ul.devolver').on('click', 'a', function(e){
             e.preventDefault();
             var $this = $(this);
            var $prompt = prompt('Informe o motivo dessa devolução:', '');
            if ($prompt===null) {
                return;
            }

            var etapa = $('body').attr('class').match(/nacional/) ? 'nacional' : 'estadual';

            $.ajax({
                url: BASE_URL + $this[0].href +'/format/json',
                type: 'get',
                dataType: 'json',
                data: {
                    'motivo': $prompt,
                    etapa: etapa
                },
                success: function(json, $statusText) {
                    if (!($statusText === 'success') || !json.itemSuccess) {
                        alert('Error');
                    }
                    $this.empty();
                }
            });
         });
    }

     return {
        init: init
    };

}());

$(function() {
    appraiserModule.init();
});