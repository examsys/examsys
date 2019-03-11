$(function() {
  $('#edit_form').submit(function () { 
    triggerSave();
  });

  jQuery.validator.addMethod("calcvariable", function(value, element) {
    // Variable defintion for calculation questions.
    // Can be a link to another variable i.e. $A,
    // a floating point or integer number i.e. 10.1,
    // a link to another questions answer or variable i.e. ans10 or var$A99,
    // a simple formula using [+,-,*,/] i.e. $A/$B
    return this.optional( element ) || /^((\$[A-Z][0-9]*|var\$[A-Z][0-9]*|ans[0-9]*|[-]?[0-9]*[.]?[0-9]+)([+-/*]?))+$/.test( value );
  }, lang['entervalidvariable']);

  $('#edit_form').validate({
    ignore: '',
    rules: {
      leadin: 'required',
      option_min1: 'required calcvariable',
      option_max1: 'calcvariable',
      option_min2: 'calcvariable',
      option_max2: 'calcvariable',
      option_min3: 'calcvariable',
      option_max3: 'calcvariable',
      option_min4: 'calcvariable',
      option_max4: 'calcvariable',
      option_min5: 'calcvariable',
      option_max5: 'calcvariable',
      option_min6: 'calcvariable',
      option_max6: 'calcvariable',
      option_min7: 'calcvariable',
      option_max7: 'calcvariable',
      option_min8: 'calcvariable',
      option_max8: 'calcvariable',
      option_min9: 'calcvariable',
      option_max9: 'calcvariable',
      option_min10: 'calcvariable',
      option_max10: 'calcvariable',
      option_formula1: {
        required: function () {
          var haveFormula = true;
          $('.formula').each(function () {
            if ($(this).val() != '') {
              haveFormula = false;
            }
          });
          return haveFormula;
        }
      },
      option_increment1: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(1);
          }
        }
      },
      option_increment2: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(2);
          }
        }
      },
      option_increment3: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(3);
          }
        }
      },
      option_increment4: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(4);
          }
        }
      },
      option_increment5: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(5);
          }
        }
      },
      option_increment6: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(6);
          }
        }
      },
      option_increment7: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(7);
          }
        }
      },
      option_increment8: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(8);
          }
        }
      },
      option_increment9: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(9);
          }
        }
      },
      option_increment10: {
        number: true,
        required: {
          depends: function (element) {
            return requiresIncrement(10);
          }
        }
      }
    },
    messages: {
      leadin: lang['enterleadin'],
      option_formula1: lang['enterformula'],
      option_increment1: '<br />' + lang['entervaliddecimal'],
      option_increment2: '<br />' + lang['entervaliddecimal'],
      option_increment3: '<br />' + lang['entervaliddecimal'],
      option_increment4: '<br />' + lang['entervaliddecimal'],
      option_increment5: '<br />' + lang['entervaliddecimal'],
      option_increment6: '<br />' + lang['entervaliddecimal'],
      option_increment7: '<br />' + lang['entervaliddecimal'],
      option_increment8: '<br />' + lang['entervaliddecimal'],
      option_increment9: '<br />' + lang['entervaliddecimal'],
      option_increment10: '<br />' + lang['entervaliddecimal']
    },
    errorPlacement: function(error, element) {
      if (element.attr('name') == 'leadin') {
        error.insertAfter('#leadin_parent');
        $('#leadin_tbl').css({'border-color' : '#C00000'});
        $('#leadin_tbl').css({'box-shadow' : '0 0 6px rgba(200, 0, 0, 0.85)'});
      } else if (element.attr('name') == 'option_formula1') {
        error.insertBefore('#option_formula1');
      } else {
        error.insertAfter(element);
      }
    },
    invalidHandler: function() {
      alert(lang['validationerror']);
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