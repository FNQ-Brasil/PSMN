require.config({
	paths : {
		'jquery' : 'libs/jquery/jquery-min',
		'signals' : 'libs/signals/signals.min',
		'crossroads' : 'libs/crossroads/crossroads.min',
		'underscore' : 'libs/underscore/underscore-1.3.3',
	}
});

define(
	[
		'require',
		'jquery',
		'crossroads',
		'underscore',
	],
	function(require, $, crossroads, underscore){
		//console.dir(require, $, crossroads, underscore)
		function init(){
			
			// Body data-modules dependencies
			var modules = $('body').data('modules') || '';
			if(modules){
				require(modules.split(/\s*,\s*/), function(){});
			}
			
			// elements css class dependencies
			if( $('.need-js').length ){
				require(['folder/script']);
			}

			// Routes

			// fixed routes			
			switch(document.location.pathname){
				case '/ssss':
					require(['app/questionnaire/respond'], initSection);
				break;
				default:
					require(['/assets/javascripts/sections/main.js'], initSection);
			}
			
			// Dinamic routes
			var routeRespond = crossroads.addRoute('/questionnaire/:slug:/:slug:/:slug:/{id}', loadSection);
			/*
			routeRespond.rules = {
				section : ['respond'] //valid sections
			};
			*/

			function initSection(section){
				section.init();
			}
		
			function loadSection(path, rest_params){
				var params = Array.prototype.slice.call(arguments, 1);
				require(['sections/'+ path +'/main'], function(mod){
					mod.init.apply(mod, params);
				});
			}

			crossroads.parse(document.location.pathname);

		}

		$(document).ready(init);
	}
);