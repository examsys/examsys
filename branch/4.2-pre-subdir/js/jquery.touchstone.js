$(function () {
  addHelpLinks();
});

function addHelpLinks() {
  $('.help-link').each(function () {
    var rel = 0;
    if ($(this).attr('rel') != undefined) {
      rel = $(this).attr('rel');
    }
    $(this).click(function () { return launchHelp(rel); });
  });
}
