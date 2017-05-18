(function ($) {

  $.fn.loadMore = function (selector, options) {
    var $this = $(this);
    //LoadMore: Default Settings
    $.fn.loadMore.defaults = {
      parent: ".read-more-wrapper",
      item: ".load-more-item",
      list: ".more-list",
      animationSpeed: "300",
      mobileDisplayNum: 3
    };
    $this.options = $.extend({}, $.fn.loadMore.defaults, options);

    // Private LoadMore methods
    $this.methods = {
      init: function () {
        $this.addClass("read-more-effect-processed");
        $this.list_wrapper = $this.parent($this.options.parent).prev($this.options.list);
        $this.load_items = $this.list_wrapper.find($this.options.item);
        var maxHeight = Math.max.apply(null, $this.load_items.map(function ()
        {
          return $(this).height();
        }).get());
        $this.load_items.css('height', maxHeight);
        $this.items_per_row = $this.methods.calculateItemsInRow($this.load_items);
        $this.loaded_items = $this.items_per_row;
        $this.itemHight = $this.load_items.outerHeight(true);
        $this.hight = $this.itemHight;
        $this.loaded_times = 1;
        $this.load_times = Math.ceil($this.load_items.length / $this.items_per_row);
        $this.list_wrapper.height($this.methods.calculateHeight(false));
        $this.methods.viewElements();


        $this.on("click.loadMore", function (e) {
          e.preventDefault();
          e.stopPropagation();
          $this.methods.viewNext();
        }).trigger("click.$");
        $(window).resize(function () {
          if (!$this.methods.isFinish()) {
            $this.itemHight = $this.load_items.outerHeight(true);
            $this.list_wrapper.animate({"height": $this.methods.calculateHeight(false)}, $this.options.animationSpeed);
          }
        });
      },
      calculateItemsInRow: function () {
        var items_per_row = 0;
        $this.load_items.each(function () {
          if ($(this).prev().length > 0) {
            if ($(this).position().top != $(this).prev().position().top)
              return false;
            items_per_row++;
          }
          else {
            items_per_row++;
          }
        });
        return items_per_row;
      },
      isFinish: function () {
        if ($this.load_times <= 0) {
          return true;
        }
        return false;
      },
      calculateHeight: function ($increase) {
        if (!$this.methods.isFinish()) {
          $this.calculated_loaded_times = ($this.loaded_times - 1) ? $this.loaded_times - 1 : 1;
          $this.hight = ($increase) ?
                  $this.hight + $this.itemHight :
                  $this.itemHight * $this.calculated_loaded_times;
          if (window.innerWidth < 768) {
            $this.hight = ($increase) ?
                    $this.hight + $this.itemHight * $this.options.mobileDisplayNum - $this.itemHight :
                    $this.itemHight * $this.calculated_loaded_times * $this.options.mobileDisplayNum;

            var remain_elements = $this.list_wrapper.find($this.options.item + ":not(.loaded-item)").length;

            if (remain_elements < $this.options.mobileDisplayNum)
            {
              $this.hight = $this.hight - $this.itemHight * ($this.options.mobileDisplayNum - remain_elements);
            }

          }
        }
        return $this.hight;
      },
      hideLink: function () {
        $this.animate({"opacity": 0}, $this.options.animationSpeed);
      },
      showLink: function () {
        $this.animate({"opacity": 1}, $this.options.animationSpeed);
      },
      distroy: function () {
        $this.load_times = 0;
        $this.removeAttr("style").addClass("distroyed");
        $this.unbind("click.loadMore");
      },
      switchLink: function () {
        $this.find("span").toggleClass("hidden");
      },
      viewElements: function () {
        var calculated_items_per_row = $this.items_per_row;
        if (window.innerWidth < 768) {
          calculated_items_per_row = $this.options.mobileDisplayNum;
        }
        var from = ($this.loaded_times - 1) * calculated_items_per_row;
        $this.load_items.slice(from, from + calculated_items_per_row).animate({"opacity": 1}, $this.options.animationSpeed).addClass("loaded-item");
        if (window.innerWidth < 768) {
          $this.load_times = $this.load_times - ($this.options.mobileDisplayNum - 1);
        }
        $this.loaded_times++;
        $this.load_times--;
      },
      viewNext: function () {
        $this.methods.hideLink();
        if (!$this.methods.isFinish()) {
          $this.list_wrapper.animate({"height": $this.methods.calculateHeight(true)}, $this.options.animationSpeed, function () {
            $this.methods.viewElements();
            $this.methods.showLink();
            if ($this.methods.isFinish()) {
              $this.list_wrapper.removeAttr("style");
              if ($this.hasClass("hidden-after-more")) {
                $this.methods.hideLink();
              } else {
                $this.methods.switchLink();
                $this.methods.showLink();
              }
              $this.methods.distroy();
            }
          });
        }
      },
    };
    $this.methods.init();
  };
})(jQuery);