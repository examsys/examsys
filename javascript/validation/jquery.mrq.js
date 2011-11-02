document.write('<script type="text/javascript" src="../../javascript/validation/jquery.mcq.js"></script>');

$(function () {
  $('#edit_form').submit(function () {
    var checked = 0;
    $('.mrq-correct').each(function () {
      if ($(this).is(':checked')) {
        checked++;
      }
    });
    if (checked == 1 && confirm("There is only one correct answer, this would be better as a MCQ question type.\r\nDo you wish to convert this question to MCQ?")) {
      $('#mcqconvert').val('1');
    }
  });
});