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
// Date copy js
//
// @author ?
// @copyright Copyright (c) 2018 The University of Nottingham
//
define(['jquery', 'jqueryui'], function($) {
  return function() {
    /**
     * Copy date from 'available from' into 'available to' date/time dropdowns.
     * Useful for summative and offline papers what have to be taken within a day.
     * @param object el element
     */
    this.dateCopy = function(el) {
      var highlight = '';
      switch ($(el).attr('id')) {
        case "fday":
          $("#tday").val($("#fday").val());
          highlight = 'tday';
          break;
        case "fmonth":
          $("#tmonth").val($("#fmonth").val());
          highlight = 'tmonth';
          break;
        case "fyear":
          $("#tyear").val($("#fyear").val());
          highlight = 'tyear';
          break;
        case "fhour":
          if ($("#fhour").val() > $("#thour").val()) {
            $("#thour").val($("#fhour").val());
            highlight = 'thour';
            if ($("#fminute").val() > $("#tminute").val()) {
              $("#tminute").val($("#fminute").val());
            }
          }
          break;
        case "fminute":
          if ($("#fminute").val() > $("#tminute").val() && $("#fhour").val() >= $("#thour").val()) {
            $("#tminute").val($("#fminute").val());
            highlight = 'tminute';
          }
          break;
        case "tday":
          $("#fday").val($("#tday").val());
          highlight = 'fday';
          break;
        case "tmonth":
          $("#fmonth").val($("#tmonth").val());
          highlight = 'fmonth';
          break;
        case "tyear":
          $("#fyear").val($("#tyear").val());
          highlight = 'fyear';
          break;
        case "thour":
          if ($("#thour").val() < $("#fhour").val()) {
            $("#fhour").val($("#thour").val());
            highlight = 'fhour';
          }
          break;
        case "tminute":
          if ($("#tminute").val() < $("#fminute").val() && $("#fhour").val() >= $("#thour").val()) {
            $("#fminute").val($("#tminute").val());
            highlight = 'fminute';
          }
          break;
      }
      if (highlight != '') {
        $('#' + highlight).effect("highlight", {}, 1500);
      }
    };
  }
});
