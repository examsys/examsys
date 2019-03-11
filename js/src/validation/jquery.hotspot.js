$(function() {
  $('#edit_form').submit(function () {
    triggerSave();
  })
  $('#edit_form').validate({
    ignore: '',
    rules: {
      points1: 'required'
    },
    messages: {
      points1: lang['selectarea']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'points1') {
        error.insertBefore($('#hs_holder'));
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});