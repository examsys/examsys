$(function() {
  $('#edit_form').submit(function () { 
    tinyMCE.triggerSave();
  })
  $('#edit_form').validate({
    rules: {
      leadin: 'required'
    },
    messages: {
      leadin: lang['enterleadin'],
      //niko leadin: 'Please enter a leadin for the question'
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
      //niko alert('There were problems with your submission. Please review the form and re-try');
      alert(lang['validationerror']);
    }
  });
})