// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Initialise faculty page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
requirejs(['form', 'alert', 'jquery'], function (FORM, ALERT, $) {
  $(function () {
    var form = new FORM();
    form.init();

    $('#theform').submit(function(e){
      var alert = new ALERT();
      e.preventDefault();
      $.ajax({
        url: "do_add_faculty.php",
        type: "post",
        data: $('#theform').serialize(),
        dataType: "json",
        success: function (data) {
          switch (data) {
            case 'SUCCESS':
              window.opener.location.reload();
              window.close();
              break;
            case 'DUPLICATE_NAME':
               $('#add_faculty').removeClass('valid');
               $('#add_faculty').addClass('errfield');
               alert.notification('facultywarning');
               break;
            case 'DUPLICATE_CODE':
               $('#code').removeClass('valid');
               $('#code').addClass('errfield');
               alert.notification('facultywarning');
               break;
            default:
               window.close();
               break;
          }
        },
        error: function(xhr, textStatus) {
          alert(textStatus);
        },
      });
    });
  });
});


