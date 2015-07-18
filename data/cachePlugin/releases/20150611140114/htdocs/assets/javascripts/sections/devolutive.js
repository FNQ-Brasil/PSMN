define(
    [],
    function() {
        return {
            init: function(mod, params) {
                $.ajaxSetup({
                    cache: false
                });
                var that = this;

                $main = $('div.content');
                $('body').append('<div id="enableScreen" />');

                $.ajax({
                    url: BASE_URL + '/questionnaire/devolutive/index/format/json/',
                    type: 'post',
                    cache: false,
                    dataType: 'json',
                    data: {'qstn': QSTN_ID, 'enterprise-user': ENTERPRISE_USER}
                }).done(function(json, $statusText, jqXHR) {
                    $('#enableScreen').remove();
                    if (!($statusText == 'success') || !json.itemSuccess) {
                        $('#tituloDevolutiva').html('Devolutiva');
                        $('#msgDownloadDevolutiva').html(json.messageError);
                        return;
                    }
                    $('#tituloDevolutiva').html('');
                    $('#msgDownloadDevolutiva')
                        .html('<a href="' + json.devolutive + '" target="_blank" style="color: #6894BC">Devolutiva gerada com sucesso. <b style="font-weight:bold"> Fazer download. </b></a>');
                
                });

            }
        }
    }
);


