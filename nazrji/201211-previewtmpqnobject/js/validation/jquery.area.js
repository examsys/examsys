$(function() {
  $('#edit_form').submit(function () {
    tinyMCE.triggerSave();
  })
  $('#edit_form').validate({
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
        tinyMCE.getInstanceById('leadin').getWin().document.body.style.backgroundColor='#ffd6d6';
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