$(function() {
  $.validator.addMethod('havequestions', function () {
    var rval = true;
    if ($('.random-q:checked').length == 0) {
      rval = false;
    }
    return rval;
  }, 'Wrong password');

  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      questioncheck: 'havequestions'
    },
    messages: {
      leadin: lang['enterdescription'],
      questioncheck: lang['randomenterquestion']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'questioncheck') {
        error.insertAfter('#qlist-holder');
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});