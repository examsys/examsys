$(function() {
  $('#edit_form').submit(function () { 
    tinyMCE.triggerSave();
  })
  $('#edit_form').validate({
    rules: {
      scenario: 'required'
    },
    messages: {
      scenario: lang['entervignette']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'scenario') {
        error.insertAfter('#scenario_parent');
        tinyMCE.getInstanceById('scenario').getWin().document.body.style.backgroundColor='#ffd6d6';
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});