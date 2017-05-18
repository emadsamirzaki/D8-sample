(function ($) {
  $(document).ready(function () {
    $("a[href='']:not([role='button'])").click(function (e) {
      e.preventDefault();
      e.stopPropagation();
    });

    $('nav ul.menu li').each(function () {
      $(this).find('span:first').wrap('<a href="javascript:void(0);"></a>');
    });

    fix_badge_height();
    $(window).resize(function () {
      fix_badge_height();
    });

  });

  function fix_badge_height() {
    var $height = 0;
    $(".fix-badge-height .network-title").each(function () {
      if ($(this).height() > $height) {
        $height = $(this).height();
      }
    });
    $(".fix-badge-height .network-title").height($height);
  }

})(jQuery);