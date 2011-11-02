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
      leadin: 'Please enter a leadin for the question',
      option_correct: 'Please enter a formula'
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
      alert('There were problems with your submission. Please review the form and re-try');
    }
  });
})