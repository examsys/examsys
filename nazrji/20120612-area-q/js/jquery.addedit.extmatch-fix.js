// Bit of a hack to get the options section to fit in
$(function () {
  extraWidth = 0;
  img = $('#media0 img:first');
  if (img.length == 1) {
    if (img.width() > 820) {
      extraWidth = img.width() - 820;
    }
  }
  qh = $('#question-holder');
  qh.addClass('wide');
  if (extraWidth > 0) {
    qh.width(qh.width() + extraWidth);
  }
});
