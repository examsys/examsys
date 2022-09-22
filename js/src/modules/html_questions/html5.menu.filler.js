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

/**
 * A menu item that pushes following items to the right.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['html5_menuitem'], function(Menu_item) {
  /**
   * A menu item that pushes following items to the right.
   *
   * @returns {menu_group}
   */
  function menu_filler() {
    // Extend the hotspot prototype.
    Menu_item.call(this, '');
    /**
     * @see menu_item.class
     * @private
     */
    this.class = 'clear';
    /**
     * The menu items that are part of this group.
     *
     * @type {menu_item}
     * @private
     */
    this.items = [];
  }

  /**
   * Extend the menu_item prototype.
   * @type Object
   */
  menu_filler.prototype = Object.create(Menu_item.prototype);

  /**
   * This will never be a valid return result.
   *
   * @param {String} name (Not used by this method, should be passed by calling code)
   * @returns {Boolean}
   */
  menu_filler.prototype.find = function () {
    return false;
  };

  return menu_filler;
});
