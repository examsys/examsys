// This file is part of Rogō
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

/**
 * Javascript to initialise the user search page.
 * 
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package LTi
 */

/**
 * Opens the unlink user dialogue.
 *
 * @param {string} url
 * @returns {void}
 */
function unlinkuser(url) {
  var url = url + '&id=' + $('#lineID').val();
  var properties = "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable";
  var notice = window.open(url, "LTiUserUnlink", properties);
  notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
  if (window.focus) {
    notice.focus();
  }
}

/**
 * Sets up the sortable table.
 *
 * @returns {void}
 */
$(function() {
  if ($("#maindata").find("tr").size() > 1) {
    $("#maindata").tablesorter({
      sortList: [[0,0]]
    });
  }

  $(".l").click(function(event) {
    event.stopPropagation();
    selLine($(this).attr('id'),event);
  });
});
