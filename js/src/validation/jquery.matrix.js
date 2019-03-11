$(function() {
    $('#edit_form').submit(function () {
        triggerSave();
    });

    jQuery.validator.addMethod("matrixlabels", function(value, element) {
        // Check valid matrix labels  setup.
        start = element.id.search('_text');
        y = element.id.substr(start + 5);
        // Check for empty strings.
        if (value != "" && value.trim() == "") {
            return false;
        }
        // Check the label of a selected radio button is not blank
        // Ignore if stem is also blank.
        if (value == "") {
            for (x = 1; x <= 10; x++) {
                id = 'option_correct' + x + '_' + y;
                if ($('#' + id).is(':checked') && $('#question_stem' + x).val() != "") {
                    return false;
                }
            }
            // Do not allow empty labels, apart from those not in use at the end of the list.
            emptylabel = false;
            for (x = (parseInt(y) + 1); x <= 10; x++) {
                id = 'option_text' + x;
                if ($('#' + id).val() != "") {
                    emptylabel = true;
                    break;
                }
            }
            if (emptylabel) {
                return false;
            }
        }
        return this.optional( element ) || true;
    }, lang['entervalidvariable']);

    jQuery.validator.addMethod("matrixstems", function(value, element) {
        // Check valid matrix stem setup.
        start = element.id.search('_stem');
        x = element.id.substr(start + 5);
        // Check for empty strings.
        if (value != "" && value.trim() == "") {
            return false;
        }
        // Check the stem of a selected radio button is not blank
        // Ignore if label is also blank.
        if (value == "") {
            for (y = 1; y <= 10; y++) {
                id = 'option_correct' + x + '_' + y;
                if ($('#' + id).is(':checked') && $('#option_text' + y).val() != "") {
                    return false;
                }
            }
            // Do not allow empty stems, apart from those not in use at the end of the list.
            emptystem = false;
            for (y = (parseInt(x) + 1); y <= 10; y++) {
                id = 'question_stem' + y;
                if ($('#' + id).val() != "") {
                    emptystem = true;
                    break;
                }
            }
            if (emptystem) {
                return false;
            }
        }
        return this.optional( element ) || true;
    }, lang['entervalidvariable']);

    $('#edit_form').validate({
        rules: {
            leadin: 'required',
            option_text1: 'matrixlabels',
            question_stem1: 'matrixstems',
            option_text2: 'matrixlabels',
            question_stem2: 'matrixstems',
            option_text3: 'matrixlabels',
            question_stem3: 'matrixstems',
            option_text4: 'matrixlabels',
            question_stem4: 'matrixstems',
            option_text5: 'matrixlabels',
            question_stem5: 'matrixstems',
            option_text6: 'matrixlabels',
            question_stem6: 'matrixstems',
            option_text7: 'matrixlabels',
            question_stem7: 'matrixstems',
            option_text8: 'matrixlabels',
            question_stem8: 'matrixstems',
            option_text9: 'matrixlabels',
            question_stem9: 'matrixstems',
            option_text10: 'matrixlabels',
            question_stem10: 'matrixstems',
        },
        messages: {
            leadin: lang['enterleadin'],
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
            alert(lang['validationerror']);
        }
    });
});
