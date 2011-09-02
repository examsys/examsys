this.tstip = function() {
  this.xOffset = 5; // x distance from top of container element
  this.yOffset = 5;

  $(".tooltip").unbind().hover(
      function(e) {
        this.t = this.title;
        this.title = '';

        var pos = $(this).offset();

        var h_alignment = 'centre'
        if ($(this).hasClass('ttleft')) {
          h_alignment = 'left';
        } else if ($(this).hasClass('ttright')) {
          h_alignment = 'right';
        }

        var v_alignment = 'top'
        if ($(this).hasClass('ttmiddle')) {
          v_alignment = 'middle';
        } else if ($(this).hasClass('ttbottom')) {
          v_alignment = 'bottom';
        }

        // Add a random key to the ID of the created element so it is (maybe)
        // unique
        var rnd = Math.floor(Math.random() * 50);
        $(this).data('key', rnd);

        $(this).append(
            '<span id="tstip' + rnd + '" class="tstip">' + this.t + '</span>');

        var tt = $('span#tstip' + rnd);

        var top = 0;
        switch (v_alignment) {
        case 'top':
          top = pos.top - tt.outerHeight() - yOffset;
          break;
        case 'middle':
          top = pos.top + $(this).outerHeight() - (tt.outerHeight() / 2);
          break;
        case 'bottom':
          top = pos.top + $(this).outerHeight() + yOffset;
          break;
        }

        var left = 0;
        switch (h_alignment) {
        case 'centre':
          left = pos.left + ($(this).outerWidth() / 2) - (tt.outerWidth() / 2);
          break;
        case 'left':
          left = pos.left - tt.outerWidth() - xOffset;
          break;
        case 'right':
          left = pos.left + $(this).outerWidth() + xOffset;
          break;
        }

        tt.css("top", top + "px").css("left", left + "px");
        tt.fadeIn("fast");

        e.preventDefault();
      }, function() {
        var rnd = $(this).data('key');
        this.title = this.t;
        $("span#tstip" + rnd).fadeOut("fast", function() {
          $(this).remove();
        });
      });
};

jQuery(document).ready(function($) {
  tstip();
});
