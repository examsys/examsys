$(function() {
  $('#edit_form').submit(function () {
    tinyMCE.triggerSave();
  });
  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      option_text1: 'required',
      option_text2: 'required',
      option_text3: 'required'
    },
    messages: {
      leadin: lang['enterleadin'],
      option_text1: '<br />' + lang['enteroptionshort'],
      option_text2: '<br />' + lang['enteroptionshort'],
      option_text3: '<br />' + lang['enteroptionshort']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'leadin') {
        error.insertAfter('#leadin_parent');
        tinyMCE.getInstanceById('leadin').getWin().document.body.style.backgroundColor='#ffd6d6';
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
});