require.config({
    paths : {
        'jquery': '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min'
    },
    urlArgs: "version=4"
});

define(
    [
        'require',
        'jquery',
        'libraries/underscore/underscore',
    ],
    function(require, $, someSharedModule){

        function init(){

            if( document.documentMode == 7 && window.location.hash != '#7'){
                //window.top.location.href = "http://mpepremio.postbox.com.br/login#7";
                $('body').append('<form id="parentredirect" target="_parent" action="http://mpepremio.postbox.com.br/login#7" method="post"></form>')
                $('#parentredirect').submit()
              //if (parent.location==location) {}
              return false;
            }

            // body data-modules="module1, module2"
            var modules = $('body').data('modules') || '';
            if(modules){
                require(modules.split(/\s*,\s*/), function(){});
            }

            if( $('.form-default').length ){
                require(['form-default'], initSection);
            }
            if( $('#form-forgot-password').length ){
                require(['sections/forgot-password'], initSection);
            }

             if( $('.grid-question').length ){
                require(['form-default'], initSection);
            }

            if( $('#table-details').length ){
                require(['table-details'], initSection);
            }

            if( $('.finish-criterion').length ){
                require(['finish-criterion'], initSection);
            }

            if( $('#qstnRespond').length ){
                require(['libraries/inputmask/inputmask']);
                require(['/js/validation/lib/jquery.form.js']);
                require(['sections/respond']);
            }
            
            if( $('#msgDownloadDevolutiva').length ){
                require(['sections/devolutive'], initSection);
            }

            if ( $('#form-cadastro').length ) {
                require(['sections/cadastro'], initSection);
            }

            if ( $('#page-login').length ) {
                require(['sections/login'], initSection);
            }

            if ( $('#application-env').length ) {
                require(['sections/application-env'], initSection);
            }

            if ( $('#main.listagem-empresa').length ) {
                require(['sections/management/enterprise'], initSection);
            }

            if ( $('#main.regional-edit').length ) {
                require(['sections/management/regional-edit'], initSection);
            }
            
            if ( $('#main.user-edit').length ) {
                require(['sections/management/user-edit'], initSection);
            }
            
            if ( $('#main.digitador').length ) {
                require(['sections/login'], initSection);
            }
            

            $('#back-link-float').on('click', function(e) {
                e.preventDefault();
                window.close();

                /*
                var $doc = $(parent.document);
                console.log($doc)
                console.log($doc.find('#main'));

                $doc.find('#main').show();
                $doc.find('#areaIframe').html(''); */
            });

            //page.init();
            //setTimeout("$('#content-menu').height( $('.box-content').height()+38 )", 2000 )
          }
        switch(document.location.pathname){
            case '/login-page':
                require(['sections/login'], initSection);
                break;

            default:
                //require(['sections/page'], initSection);
        }

        function initSection(section){
            section.init();
        }

        //init app on domready
        $(document).ready(init);
})
