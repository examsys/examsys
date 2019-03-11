$(function() {
  $('#edit_form').submit(function () { 
    triggerSave();
  })
  $('#edit_form').validate({
    ignore: '',
    rules: {
      scenario: 'required'
    },
    messages: {
      scenario: lang['entervignette']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'scenario') {
        error.insertAfter('#scenario_parent');
        $('#scenario_tbl').css({'border-color' : '#C00000'});
        $('#scenario_tbl').css({'box-shadow' : '0 0 6px rgba(200, 0, 0, 0.85)'});
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});