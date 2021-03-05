// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Initialise user details page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['user', 'colourpicker', 'osce', 'classtotals', 'menu', 'rolelist', 'jquery', 'jquerytablesorter'], function (USER, PICKER, OSCE, CLASSTOTALS, MENU, ROLELIST, $) {
    var user = new USER();
    var picker = new PICKER();
    picker.init();

    ROLELIST.init($('#dataset').data('sysadmin'));

    user.updateAccessDemo();

    var userid = $('#dataset').attr('data-userid');
    var datetime = $('#dataset').attr('data-datetime');

    $('#menu2a').hide();
    $('#menu2b').show();

    $('#edit').click(function() {
        user.editDetails();
    });

    $('.paperreview').click(function() {
        var classtotal = new CLASSTOTALS();
        classtotal.cryptname = $(this).attr('data-papername');
        classtotal.userid = userid;
        classtotal.metadataid = $(this).attr('data-metadataid');
        classtotal.papertype = $(this).attr('data-papertype');
        classtotal.viewScript();
    });

    $('.tabon, .taboff').click(function() {
        user.showTab($(this).attr('data-tabid'));
    });

    $('#createname').click(function() {
        user.newStudentNote();
    });

    $('.modules').click(function() {
        user.editModules($(this).attr('data-session'), $(this).attr('data-grade'), $('#dataset').attr('data-sid'), $('#dataset').attr('data-searchusername'), $('#dataset').attr('data-searchsurname'));
    });

    $('#teams').click(function() {
        user.editMultiTeams();
    });

    $('#forcepasswordreset').click(function() {
        user.forceResetPassword();
    });

    $('#emailpasswordreset').click(function() {
        user.resetPassword();
    });

    $('.access').on("change", function() {
        user.updateAccessDemo();
    });

    $('.oscereview').click(function() {
        var osce = new OSCE();
        osce.userid= userid;
        osce.paperid = $(this).attr('data-paperid');
        osce.viewScript();
    });

    if ($("#maindata").find("tr").length > 2) {
        $("#maindata").tablesorter({
            dateFormat: datetime,
            sortList: [[1,0]]
        });
    }

    $('.showpicker').click(function(e) {
        picker.showPicker($(this).attr('data-pickertype'), e);
        switch ($(this).attr('data-pickertype')) {
            case 'background':
                $('#bg_radio_on').attr('checked', true);
                break;
            case 'foreground':
                $('#fg_radio_on').attr('checked', true);
                break;
            case 'marks_color':
                $('#marks_radio_on').attr('checked', true);
                break;
            case 'themecolor':
                $('#theme_radio_on').attr('checked', true);
                break;
            case 'labelcolor':
                $('#labels_radio_on').attr('checked', true);
                break;
            case 'unansweredcolor':
                $('#unanswered_radio_on').attr('checked', true);
                break;
            case 'dismisscolor':
                $('#dismiss_radio_on').attr('checked', true);
                break;
            default:
                break;
        }
    });

    var menu = new MENU();
    $('#advancedmenu').click(function() {
        menu.updateMenu($(this).attr('data-menuid'));
    });
});
