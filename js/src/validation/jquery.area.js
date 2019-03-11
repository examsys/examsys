$(function() {
  $('#edit_form').submit(function () {
    triggerSave();
  })
  $('#edit_form').validate({
    ignore: '',
    rules: {
      leadin: 'required',
      option_correct: 'required'
    },
    messages: {
      leadin: lang['enterleadin'],
      option_correct: lang['selectarea']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'leadin') {
        error.insertAfter('#leadin_parent');
        $('#leadin_tbl').css({'border-color' : '#C00000'});
        $('#leadin_tbl').css({'box-shadow' : '0 0 6px rgba(200, 0, 0, 0.85)'});
      } else if (element.attr('name') == 'option_correct') {
        error.insertBefore($('#externalinterfaceoption_correct_1'));
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});