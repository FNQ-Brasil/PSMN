require.config({
    paths : {
        'jquery' : 'libraries/jquery/jquery'
    }
});

define(
    [
        'require',
        'jquery',
        'libraries/underscore/underscore',
    ],
    function(require, $, someSharedModule){

        function init(){
            // body data-modules="module1, module2"
            var modules = $('body').data('modules') || '';
            if(modules){
                require(modules.split(/\s*,\s*/), function(){});
            }

            if( $('.form-default').length ){
                require(['form-default'], initSection);
            }

            //page.init();
        }

        switch(document.location.pathname){
            case '/':
                require(['sections/home'], initSection);
                break;
            default:
                //require(['sections/page'], initSection);
        }

        function initSection(section){
            section.init();
        }

        //init app on domready
        $(document).ready(init);
    }
);
