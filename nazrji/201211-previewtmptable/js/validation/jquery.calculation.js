$(function() {
  $('#edit_form').submit(function () { 
    tinyMCE.triggerSave();
    checkHasOption();
  });

  $('#edit_form').validate({
    rules: {
      leadin: 'required',
      option_correct: 'required',
      option_increment1: {
        required: {
          depends: function (element) {
            return requiresIncrement(1);
          }
        }
      },
      option_increment2: {
        required: {
          depends: function (element) {
            return requiresIncrement(2);
          }
        }
      },
      option_increment3: {
        required: {
          depends: function (element) {
            return requiresIncrement(3);
          }
        }
      },
      option_increment4: {
        required: {
          depends: function (element) {
            return requiresIncrement(4);
          }
        }
      },
      option_increment5: {
        required: {
          depends: function (element) {
            return requiresIncrement(5);
          }
        }
      },
      option_increment6: {
        required: {
          depends: function (element) {
            return requiresIncrement(6);
          }
        }
      },
      option_increment7: {
        required: {
          depends: function (element) {
            return requiresIncrement(7);
          }
        }
      },
      option_increment8: {
        required: {
          depends: function (element) {
            return requiresIncrement(8);
          }
        }
      },
      option_increment9: {
        required: {
          depends: function (element) {
            return requiresIncrement(9);
          }
        }
      },
      option_increment10: {
        required: {
          depends: function (element) {
            return requiresIncrement(10);
          }
        }
      }
    },
    messages: {
      leadin: lang['enterleadin'],
	    option_correct: lang['enterformula'],
      option_increment1: '<br />' + lang['enteroptionshort'],
      option_increment2: '<br />' + lang['enteroptionshort'],
      option_increment3: '<br />' + lang['enteroptionshort'],
      option_increment4: '<br />' + lang['enteroptionshort'],
      option_increment5: '<br />' + lang['enteroptionshort'],
      option_increment6: '<br />' + lang['enteroptionshort'],
      option_increment7: '<br />' + lang['enteroptionshort'],
      option_increment8: '<br />' + lang['enteroptionshort'],
      option_increment9: '<br />' + lang['enteroptionshort'],
      option_increment10: '<br />' + lang['enteroptionshort']
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
      //niko alert('There were problems with your submission. Please review the form and re-try');
    }
  });
});

function requiresIncrement(index) {
  var rval = false;
  var min_value = $('#option_min' + index).val();
  if (min_value != '' && min_value.substring(0, 3) != 'var' && min_value.substring(0, 3) != 'ans') {
    rval = true;
  }
  return rval;
}

function checkHasOption() {
  var hasVal = false;
  $('.calc-min').each(function () {
    if ($(this).val() != '') hasVal = true;
  });
  if (!hasVal) {
    $('#option_min1').val('0');
    $('#option_increment1').val('1');
  }
}