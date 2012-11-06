$(function () {
  if ($('#option_text1').size() == 0) {
    $('#addbank').attr('disabled', 'disabled');
    $('#addpaper').attr('disabled', 'disabled');
  }

  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      option_text1: 'required'
    },
    messages: {
      leadin: '<br />'+lang['enterleadin'],
      option_text1: '<br />'+lang['enteroption_kw']
    }
  });
});
