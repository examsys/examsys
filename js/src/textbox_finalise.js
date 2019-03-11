// Check select all button
function selectall() {
    var total = 0;
    var count = 0;
    $(".primarychk").each(function() {
        if ($(this).is(':checked')) {
            count++;
            total++;
        } else {
            total++;
        }
    });
    if (count === total) {
        $("#selectallprimary").prop("checked", true);
    }
}
$(function() {
    // Check select all button if all primary mark radio buttons selected on load.
    $(document).ready(function(){
        selectall();
    });
    $("input:radio").click(function() {
        str = $(this).attr('id');

        dropdownID = str.replace('mark', 'override');
        $("#" + dropdownID).val('');
    });

    $("select").click(function() {
        str = $(this).attr('id');

        radioID = str.replace('override', 'mark');


        $('input:radio[name=' + radioID + ']').removeAttr('checked');
    });
    // Select all primary marks radio buttons.
    $("#selectallprimary").change(function() {
        if ($("#selectallprimary").is(':checked')) {
            $(".primarychk").each(function() {
                $(this).prop("checked", true);
            });
        } else {
            $(".primarychk").each(function() {
                $(this).prop("checked", false);
            });
        }
    });
    // Check select all button if all primary mark radio buttons selected.
    $(".primarychk").click(function() {
        selectall();
    });
    // Uncheck select all button if a secondary mark has been selected.
    $(".secondarychk").click(function() {
        $("#selectallprimary").prop("checked", false);
    });
})