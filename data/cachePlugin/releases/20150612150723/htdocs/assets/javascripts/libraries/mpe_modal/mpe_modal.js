(function ($) {
  $.fn.extend({
    mpeModal: function (options) {
      var defaults = {
        top: 100,
        overlay: 0.5,
        closeButton: null
      };
      var overlay = $("<div id='mpe_modal_overlay'></div>");
      $("body").append(overlay);
      options = $.extend(defaults, options);
      return this.each(function () {
        var o = options;
        $(this).click(function (e) {
          var modalId = $(this).attr("href");
          $("#mpe_modal_overlay").click(function () {
            closeModal(modalId)
          });
          $(o.closeButton).click(function () {
            closeModal(modalId)
          });
          var modalHeight = $(modalId).outerHeight();
          var modalWidth = $(modalId).outerWidth();
          $("#mpe_modal_overlay").css({
            display: "block",
            opacity: 0
          });
          $("#mpe_modal_overlay").fadeTo(200, o.overlay);
          $(modalId).css({
            display: "block",
            position: "fixed",
            opacity: 0,
            zIndex: 11000,
            left: 50 + "%",
            marginLeft: -(modalWidth / 2) + "px",
            top: o.top + "px"
          });
          $(modalId).fadeTo(200, 1);
          e.preventDefault()
        })
      });
      function closeModal(modalId) {
        $("#mpe_modal_overlay").fadeOut(200);
        $(modalId).css({
          display: "none"
        })
      }
    }
  })
})(jQuery);
