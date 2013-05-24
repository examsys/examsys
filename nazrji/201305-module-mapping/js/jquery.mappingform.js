$(function () {
  $('.objectives li a').click(function () {
    $(this).nextAll('ul').slideToggle('fast');
    $(this).parent('li').toggleClass('open');
    return false;
  });

  openMappedTabs();
});

function openMappedTabs() {
  $('ul.objectives li.top').each(function () {
    var mappedObjs = $(this).find(':checkbox:checked').length;
    if (mappedObjs > 0) {
      $(this).addClass('open');
      $(this).children('ul').show();
    }
  });
}
