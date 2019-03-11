$(function() {
  $('#edit_form').submit(function () { 
    triggerSave();
  })
  $('#edit_form').validate({
    ignore: '',
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
        $('#leadin_tbl').css({'border-color' : '#C00000'});
        $('#leadin_tbl').css({'box-shadow' : '0 0 6px rgba(200, 0, 0, 0.85)'});
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