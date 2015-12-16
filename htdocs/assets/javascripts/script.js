require.config({
    paths : {
        'jquery' : 'libs/jquery/jquery-min'
    }
});

define(
    [
        'require',
        'jquery',
    ],
    function(require, $, someSharedModule){

        function init(){
            // body data-modules="include1, otherInclude"
            var modules = $('body').data('modules') || '';
            if(modules){
                require(modules.split(/\s*,\s*/), function(){});
            }

            if( $('.class-to-js-include').length ){
                require(['folder/script']);
            }
            
            if ( $('#frmLogin').length ) {
                require(['sections/login'], initSection);
            }

            //something.init();
        }

        switch(document.location.pathname){
            case '/foo':
                require(['sections/foo/main'], initSection);
                break;
            case '/foo/bar':
                require(['sections/foo/main'], initSection);
                break;
            default:
                //let's just assume we have a lot of pages with common features
                require(['sections/simplePage'], initSection);
        }

        function initSection(section){
            section.init();
        }

        //init app on domready
        $(document).ready(init);
    }
);



/*




$(document).ready(function(){

	$(".main-menu li a").each(function(){
		$(this).bind("click", function() {
			
			var clicked = $(this).parent();

			clicked.toggleClass('open');

			var liQnt = $(".main-menu .open ul li").size();
			$(".main-menu .open ul").css('width',''+ liQnt * 125 +'px');		
			return false;	
		})

	});

	$('.input-phone').mask('(99) 99999-9999');
	$('.input-cnpj').mask('99.999.9999/9999-99');
	$('.input-inscricao').mask('999.999.999.999');
	$('.input-date').mask('99 / 99 / 9999');

	$('#tabs').tabs();
	$('.quizz').tabs();

	$('#e1').select2({
	    minimumInputLength: 2
	});
});
*/