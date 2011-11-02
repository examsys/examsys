$(function() {
  $('#edit_form').submit(function () { 
    tinyMCE.triggerSave();
  })
  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      option_text1: {
        required: {
          depends: function (element) {
            return $('#option_media1').val() == '';
          }
        } 
      },
      option_text2: {
        required: {
          depends: function (element) {
            return $('#option_media2').val() == '';
          }
        } 
      }
    },
    messages: {
      leadin: lang['enterleadin'],
      option_text1: '<br />'+lang['enteroption'],
      option_text2: '<br />'+lang['enteroption']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'leadin') {
        error.insertAfter('#leadin_parent');
        tinyMCE.getInstanceById('leadin').getWin().document.body.style.backgroundColor='#ffd6d6';
      } else if(element.attr('name') == 'option_text1') {
        error.insertAfter('#option_media1');
      } else if(element.attr('name') == 'option_text2') {
        error.insertAfter('#option_media2');
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
    }
  });
})