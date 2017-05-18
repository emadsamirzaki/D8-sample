(function ($) {
  $(document).ready(function () {
    $(".region-navigation-collapsible ul.menu").addClass("navbar-nav");
    $(".region-navigation-collapsible ul a[href*='#']").removeClass('is-active');
    if (window.innerWidth >= 992) {
      $('.region-navigation-collapsible ul a').removeAttr('data-toggle');
      $(".region-navigation-collapsible ul .dropdown-menu").find('.sub-navbar-menu-wrapper').children().unwrap();
      $(".region-navigation-collapsible ul .dropdown-menu").wrapInner("<div class='container sub-navbar-menu-wrapper flex-display flex-wrap'></div>");
    } else {
      $(".region-navigation-collapsible ul .dropdown-menu").wrapInner("<div class='container sub-navbar-menu-wrapper'></div>");
      $('.region-navigation-collapsible ul a').each(function () {
        var attr = $(this).hasClass("dropdown-toggle");
        if (typeof attr !== typeof undefined && attr !== false) {
          var txt = $(this).clone().children().remove().end().text();
          var children = $(this).clone().children();
          $(this).html("<a href='" + $(this).attr('href') + "' class='inline-block'>" + txt + "</a>").append(children);
          $(this).attr("href", 'javascript:void(0);');
          $(this).find("a").click(function (event) {
            event.stopPropagation();
          });
          $(this).click(function (event) {
            $(this).next(".dropdown-menu").collapse('toggle');
          });
        }
      });
    }
    if (window.location.hash) {
      $("body, html").stop().animate({
        scrollTop: $(window.location.hash).offset().top - $('#navbar').height() - parseFloat($("body").css("padding-top"))
      }, 600);
    }
    $(".jumper, .region-navigation-collapsible ul a[href*='#']").click(function (e) {
      if ($($(this).attr('href').substr($(this).attr('href').indexOf('#'))).length) {
        e.preventDefault();
        e.stopPropagation();
      }
      try {
        if (window.innerWidth <= 991)
        {
          $('#navbar-collapse').collapse("hide");
//                    $('button.navbar-toggle').click();
        }
        $("body, html").stop().animate({
          scrollTop: $($(this).attr('href').substr($(this).attr('href').indexOf('#'))).offset().top - $('#navbar').height() - parseFloat($("body").css("padding-top"))
        }, 600);
      } catch (exception) {
        console.log(exception);
      }

    });

    var sections = $('section.issue-issues');
    var nav = $('.region-navigation-collapsible ul');
    var nav_height = $('#navbar-collapse').outerHeight();

    $(window).on('scroll', function () {
      var cur_pos = $(this).scrollTop();

      sections.each(function () {
        var top = $(this).offset().top - 1 - nav_height - parseFloat($("body").css("padding-top")),
                bottom = top + $(this).outerHeight();

        if (cur_pos >= top && cur_pos <= bottom) {
          nav.find('a[href*="#"]').removeClass('is-active');
          sections.removeClass('is-active');

          $(this).addClass('is-active');
          nav.find('a[href*="#' + $(this).attr('id') + '"]').addClass('is-active');
        }
      });
    });


    $(".form-textarea-wrapper + label").detach().appendTo('.form-textarea-wrapper');
    if (window.innerWidth < 992) {
      $(".navbar-collapse").on("shown.bs.collapse", function () {
        if ($(".sticky-header").hasClass("affix") || $(".sticky-header").hasClass("affix-top")) {
          $(".sticky-header").addClass("scroll").removeClass("affix");
          $("html, body").animate({scrollTop: 0}, 0);
        }
      });
      $(".navbar-collapse").on("hidden.bs.collapse", function () {
        if ($(".sticky-header").hasClass("affix") || $(".sticky-header").hasClass("affix-top")) {
          $(".sticky-header").removeClass("scroll");
        }
      });
    }
    $('.search-form-wrapper').on('shown.bs.collapse', function () {
      $(this).find("input[type='search']").focus();
    })
    $(".block-search-form-block input[type='search']").on("focus", function () {
      $(this).parents("form").addClass("focused");
    });
    $(".block-search-form-block input[type='search']").on("blur", function () {
      $(this).parents("form").removeClass("focused");
    });
    if (window.innerWidth >= 768) {
      $(document).click(function (e)
      {
        var container = $(".block-search-form-block");
        if (!container.is(e.target) // if the target of the click isn't the container...
                && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
          $('.search-form-wrapper').collapse("hide");
        }
      });
    }

    function slide_effect(e, $this) {
      e.preventDefault();
      e.stopPropagation();
      if (window.innerWidth >= 768) {
        var $width = "60%";
        if (window.innerWidth < 992) {
          $width = "80%";
        }
        $this.prev(".form-fields-slide-container").animate({"width": $width}, 400);
      } else {
        $this.prev(".form-fields-slide-container").animate({"height": "100px"}, 300);
      }
      $this.prev(".form-fields-slide-container").addClass("sign-slide-processed");
      $this.removeClass("sign-slide-action");
    }

    $(".subscribr-btn").click(function (e) {
      if ($(this).hasClass("sign-slide-action")) {
        slide_effect(e, $(this));
      } else {
        $(this).unbind("slide_effect");
      }
    });
    $('.read-more-effect').each(function () {
      $(this).loadMore();
    });

    $('#video-popup').on('show.bs.modal', function () {
      $("#videoIframe")[0].src += "&autoplay=1";
    });
    $('#video-popup').on('hide.bs.modal', function (e) {
      var rawVideoURL = $("#videoIframe")[0].src;
      rawVideoURL = rawVideoURL.replace("&autoplay=1", "");
      $("#videoIframe")[0].src = rawVideoURL;
    });
    $(".event-title").hover(function () {
      $(this).parents(".event-data-container").prev("a.event-date-container").toggleClass("hovered");
    });
    $(".roadmap-resource-item a").hover(function () {
      $(this).parents(".borderd-item-before").toggleClass("hovered");
    });

    $(".navbar-nav .dropdown-menu").css("left", function () {
      return $(this).parent(".dropdown").offset().left * -1;
    });
    $(".not-logged").click(function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(".not-logged-message-wrapper").stop(true, true).slideDown("slow");
      if ($(document).scrollTop() + $(window).height() <= $(".not-logged-message-wrapper").offset().top + 50) {
        $("body, html").stop().animate({
          scrollTop: $(document).scrollTop() + 172
        }, 500);
      }
    });

    if ($("#jquery_jplayer_1").length) {
      $(document).ready(function () {
        var $player = $("#jquery_jplayer_1");
        var $extension = $player.data("url").substr(($player.data("url").lastIndexOf('.') + 1));
        var $player_data = {
          title: $player.attr("title")
        };
        $player_data[$extension] = $player.data("url");
        console.log($player_data);
        $("#jquery_jplayer_1").jPlayer({
          ready: function () {
            $(this).jPlayer("setMedia", $player_data);
          },
          cssSelectorAncestor: "#jp_container_1",
          swfPath: "../jplayer/js",
          supplied: $extension,
          useStateClassSkin: true,
          autoBlur: false,
          smoothPlayBar: true,
          keyEnabled: true,
          remainingDuration: true,
          toggleDuration: true
        });
      });
    }

  });

})(jQuery);

(function ($) {
  'use strict';

  Drupal.behaviors.ceres = {
    attach: function (context, settings) {
      $('select.ceres-custom-select').selectpicker({
        style: 'rltv',
        template: {
          caret: '<i class="fa fa-angle-down" aria-hidden="true"></i>'
        }
      });
      if ($(".expert-name", context).length) {
        $(".expert-name", context).once().hover(function () {
          $(this).parents(".expert-content").prev("a.expert-image-wrapper").toggleClass("hovered");
        });
      }
    }
  };

}(jQuery));
