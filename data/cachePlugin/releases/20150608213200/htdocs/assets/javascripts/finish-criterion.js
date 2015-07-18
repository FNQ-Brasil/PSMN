define(
  [
    //'libraries/validate/additional-methods',
  ],
  function() {
    return {
      init: function(mod, params) {
        this.progressbar();
        this.accordion();
      },

      progressbar: function(){
        var activeCount = $('li.active').length;

        switch(activeCount) {

          case(activeCount = 1):
            $('.fc-progressbar-active').addClass('none');
            break;

          case(activeCount = 2):
            $('.fc-progressbar-active').addClass('fc-progressbar-active-23')
            break;

          case(activeCount = 3):
            $('.fc-progressbar-active').addClass('fc-progressbar-active-42')
            break;

          case(activeCount = 4):
            $('.fc-progressbar-active').addClass('fc-progressbar-active-61')
            break;

          case(activeCount = 5):
            $('.fc-progressbar-active').addClass('fc-progressbar-active-80')
            break;

          case(activeCount = 6):
            $('.fc-progressbar-active').addClass('fc-progressbar-active-99')
            break;

          default:
            $('.fc-progressbar-active').addClass('none');
        }
      }, // progressbar

      accordion: function() {
        $('.fc-accordion-title').on('click', function(){
          $(this).next().slideToggle('slow', function(){
            $(this).parent().toggleClass('open');
          });
        })
      },

    }
  }
);
