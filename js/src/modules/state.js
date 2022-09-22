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
// User State
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['rogoconfig', 'jquery'], function(config, $) {
  return function() {
    /**
     * Initialise state saving for checkboxes on screen.
     */
    this.init = function() {
      var scope = this;
      $('.chk').click(function() {
        var state_name = $(this).attr('id');
        var content = $(this).is(':checked');
        scope.updateState(state_name, content);
      });
    };

    /**
     * Save the current state of the screen.
     * @param string state_name state identifier
     * @param string content state value
     */
    this.updateState = function (state_name, content) {
      $.post(config.cfgrootpath + '/include/set_state.php', {state_name: state_name, content: content, page: document.URL}, function() {}, "html");
    };

    /**
     * Update the sate of a dropdown box.
     * @param string mySel dropdown box selector
     * @param string NameOfState state identifier
     */
    this.updateDropdownState = function(mySel, NameOfState) {
      var setting = mySel.options[mySel.selectedIndex].value;
      this.updateState(NameOfState, setting);
    };
  }
});
