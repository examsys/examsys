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
// Type Course Filter Dropdown
//
// @author Richard Whitefoot (UEA)
//
define(['jquery', 'jqueryui'], function($) {
  return function() {

    /**
     * Default properties
     */
    this.settings = {
      parentField: "#new_roles",
      childField: "#new_grade",
      parentLabel: "#typecourse",
      disableClass: "grey"
    };

    /**
     * Disable the drop-down and grey out the associated field lable
     * @private
     */
    this._disable = function () {
      var scope = this;
      $(this.settings.childField).attr("disabled", "disabled");
      $(this.settings.parentLabel).addClass(scope.settings.disableClass);
    };

    /**
     * Enable the drop-down and remove the grey out class from the associated field lable
     * @private
     */
    this._enable = function () {
      var scope = this;
      $(this.settings.childField).removeAttr("disabled");
      $(this.settings.parentLabel).removeClass(scope.settings.disableClass);
    };

    /**
     * Make changes to options within the Type/Course drop-down based on the Status drop-down
     * @private
     */
    this._setFilter = function () {
      var scope = this;

      $(this.settings.parentField).change(function () {

        var correspondingID = $(this).find(":selected").data("parent");

        if (!correspondingID) {
          correspondingID = "";
        }

        // Reset Type/Course and store previous value
        if ($(scope.settings.childField).data("prev-parent") != correspondingID) {

          $(scope.settings.childField).val("");

          $(scope.settings.childField + " optgroup").hide();

          if (!correspondingID) {
            scope._disable();
          } else {
            $(scope.settings.childField + " optgroup[data-role='" + correspondingID + "']").show();
            scope._enable();
          }
        }

        $(scope.settings.childField).data("prev-parent", correspondingID);

      });
    };

    /**
     # Initialise module
     * @public
     */
    this.init = function (options) {

      // Extend and override default properties
      this.settings = $.extend({}, this.settings, options);

      this._disable();
      this._setFilter();
    };
  }
});