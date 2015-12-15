(function() {
	if(window.location.protocol === 'file:') {
		window.alert("Samples in BoilerplateJS might not work when opened directly from the file system. Please deploy in to a web server and access with 'http://' url");
	}

	if($.browser.msie && parseInt($.browser.version, 10) < 8) {
		window.alert("Your browser version is not supported. Please update to Internet Explorer 8 or onwards, or use an HTML5 compatible browser such as Chrome or Firefox.", "Samples may not work");
	}
})();
