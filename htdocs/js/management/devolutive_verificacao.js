/* Javascript O
 * O Module Pattern */


    
  function generateDevolutive(QSTN_ID, ENTERPRISE_ID_KEY) {
      
        //e.preventDefault();
        $.ajax({
            url: BASE_URL + '/questionnaire/devolutive/verificacao/format/json/',
            type: 'post',
            cache: false,
            dataType: 'json',
            //data: {'qstn': QSTN_ID, 'enterprise-id-key': ENTERPRISE_ID_KEY}
            data: {'enterprise-id-key': ENTERPRISE_ID_KEY}
        }).done(function(json, $statusText, jqXHR) {
            if (!($statusText == 'success') || !json.itemSuccess) {
                $('#tituloDevolutiva').html('Devolutiva');
                $('#msgDownloadDevolutiva').html(json.messageError);
                return;
            }
            var textoDevolutiva = '';
            if (json.protocoloId != '') {
                    textoDevolutiva +='Devolutiva gerada às "'+json.protocoloCreateAt+'" por "'+json.userLogadoGerouDevolutiva+'"';
                    textoDevolutiva += '<br><br>Protocolo "'+json.protocolo+'"<br> ';
                    textoDevolutiva += '<br><a href="' + json.devolutive + '" target="_blank" style="color: #6894BC">';
                    textoDevolutiva += 'Download Devolutiva</b></a> <br><br> ';
                    if (ADMIN_MENU) {
                        textoDevolutiva += '<a href="'+json.regerar_devolutive+'/menu-admin/1">Regerar Devolutiva</a>';
                    } else {
                        textoDevolutiva += '<a href="'+json.regerar_devolutive+'">Regerar Devolutiva</a>';
                    }
            } else {
                    
                    textoDevolutiva += '<br><a href="' + json.devolutive + '" target="_blank" style="color: #6894BC">';
                    textoDevolutiva += 'Download Devolutiva</b></a> <br><br> ';
                    if (ADMIN_MENU) {
                        textoDevolutiva += '<a href="'+json.regerar_devolutive+'/menu-admin/1">Regerar Devolutiva</a>';
                    } else {
                        textoDevolutiva += '<a href="'+json.regerar_devolutive+'">Regerar Devolutiva</a>';
                    }
            }
            if (json.permissaoCadastrar) {
                textoDevolutiva += '<br><br><a href="'+BASE_URL+'/management/enterprise/cadastro">Cadastrar nova empresa</a>';
            }
            
            $('#tituloDevolutiva').html('Devolutiva<br><br>');
            $('#msgDownloadDevolutiva')
               .html(textoDevolutiva);
               //.html('<a href="' + json.devolutive + '" target="_blank">Clique aqui para fazer o download!</a>');

        });
    }

$.fn.ready(function() {
    try {
        devolutiveModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);Sescoop.error(e.message);return;
        }
        throw e;
    }
});