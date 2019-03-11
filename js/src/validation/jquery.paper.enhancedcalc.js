$(function() {
    jQuery.validator.addMethod("calcanswer", function(value, element) {
      return this.optional( element ) || /^[+-]?[0-9]+[\.]?[0-9]*(.)*$/.test( value );
    }, lang_string['entervalidcalcanswer']);
    jQuery.validator.addClassRules('ecalc-answer', {
        calcanswer: true
    });
    $('#qForm').validate({
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.insertBefore(element);
        }
    });
});
