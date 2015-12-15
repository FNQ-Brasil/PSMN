var appraiserUserModule = (function() {
    functionName = function($param) {
        return $param;
    }

    return {
        init: function() {
            $('.appraiser_status').click(function(event){
                var $target = $(event.currentTarget);
                var user_id = $target.attr('name').match(/\d+/)[0];
                var status = $target.val();
                var url = '/management/appraiser-user/change-status/user_id/'+user_id+'/status/'+status;

                $.post(url).success(function(response){
                    if(response.status < 0) alert(response.message);
                    window.location.reload();
                });
            });

            return this;
        }
    };

}());

$(function() {
    try {
        appraiserUserModule.init();
    } catch (e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            //document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }
        throw e;
    }
});