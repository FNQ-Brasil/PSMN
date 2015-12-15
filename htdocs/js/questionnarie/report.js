var reportModule = (function () {

    var $form, $divError, $divErrorMsg;

     return {
         
        init: function() {
            
            $divError = $('div.error');
            $divErrorMsg = $divError.find('b');
            
            $form = $('#frmReport');
            $form.data('submited', false);

            $('#Title').focus();
            

            jQuery.validator.messages.required = "";

            $form.validate({
                invalidHandler: function(e, validator) {
                    //console.log(e, validator);
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var message = errors == 1
                            ? 'Você esqueceu de 1 campo. Ele está destacado.'
                            : 'Você esqueceu de ' + errors + ' campos.  Eles estão destacados.';
                        $divErrorMsg.html(message);

                        $.scrollTo(
                            $(validator.errorList[0].element), 200,
                            {
                                offset: -35,
                                onAfter: function() {
                                    setTimeout(function(){$divError.show().effect('bounce');}, 200);
                                }
                            }
                        );
                        //.focus();
                    } else {
                        $divError.hide();
                    }
                },
                onkeyup: false,
                submitHandler: function() {
                    $form.ajaxSubmit({
                        dataType: 'json',
                        beforeSubmit: function(formData, jqForm, options) { 
                            if (!$form.valid() || $form.data('submited')) {
                                //console.log('valid false');
                                return false;
                            }
                            $form.data('submited', true);
                            $divError.hide();
                            return true;
                        },
                        success: function(json, $statusText, xhr, $form)  {
                            if (!($statusText == 'success') || !json.itemSuccess) {
                                 $divErrorMsg.html(json.messageError);
                                 $divError.show('bounce');
                                 $form.data('submited', false);
                                setTimeout(function(){escroll();}, 250);
                                return;
                            }
                            
                            if (json.redirectUrlReport) {
                                window.location = json.redirectUrlReport;
                                return;
                            }
                            
                            if (json.loadUrlReport) {
                                $('div.content').load(json.loadUrlReport);
                                return;
                            }
                            
                        },
                        
                        error:function(x,e){
                            $form.data('submited', false);
                            setTimeout(function(){escroll();}, 250);
                        }
                    });
                    return false;
                }
            });
           
            $('#msgError').hide();
        return this;
        }
    };

}());

$(function() {
    try {
        reportModule.init();
    } catch(e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            document.write ("Outer catch caught <br/>");
            Psmn.error(e.message);
            return;
        }
        throw e;
    }
});



var input = $('[name="title"]'),
  textarea = $('.description'),
  charCount = $('#charCount'),
  wordCount = $('#wordCount'),
  charLimit = 7140;

$({}).add(
  input
).add(
  textarea
).keyup(function() {

  var charStr = {
    input: $.trim(input[0].value).length,
    textarea: $.trim(textarea[0].value).length
  };

  var words = {
    input: $.trim(input[0].value).replace(/{.*?}/g, '').split(' ').length,
    textarea: $.trim(textarea[0].value).replace(/{.*?}/g, '').split(' ').length
  };

  //if ( charStr > charLimit ) {
 //     this.value = this.value.substr(0, this.value.length + charLimit - charStr);
   //   charStr = 7140;
  //}
  charCount.text( (charStr.input + charStr.textarea) );
  wordCount.text( (words.input + words.textarea) );

}).triggerHandler('keyup');


(function($){
  $.fn.textareaCounter = function(options) {
    // setting the defaults
    // $("textarea").textareaCounter({ limit: 100 });
    var defaults = {
      limit: 100
    };  
    var options = $.extend(defaults, options);
 
    // and the plugin begins
    return this.each(function() {
      var obj, text, wordcount, limited;
      obj = $(this);
      var contaPalavras = obj.val().split(' ').length;
      $('#wordCount').html(contaPalavras);

      obj.keyup(function() {
          text = obj.val();
          if(text === "") {
            wordcount = 0;
          } else {
            wordcount = $.trim(text).split(" ").length;
        }
         /* if(wordcount > options.limit) {
              $("#wordCount").html('0');
          limited = $.trim(text).split(" ", options.limit);
          limited = limited.join(" ");
          $(this).val(limited);
          } else {*/
              $("#wordCount").html((wordcount));
         // } 
      });
    });
  };


  //$("textarea").textareaCounter({ limit: 1200 });
})(jQuery);