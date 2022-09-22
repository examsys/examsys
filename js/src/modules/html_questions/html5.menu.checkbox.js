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
 * A menu_item displayed as a check box to the user
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['html5_menuitem', 'jquery'], function(Menu_item, $) {
  /**
   * A menu item that displays a check box that the user can toggle on and off.
   *
   * @param {String}
   * @returns {menu_group}
   */
  function menu_checkbox(id, text) {
    // Extend the hotspot prototype.
    Menu_item.call(this, '', '', id);
    /**
     * @see menu_item.class
     * @private
     */
    this.class = 'menucontainer';
    /**
     * The text for the checkbox.
     *
     * @type {String}
     * @private
     */
    this.text = text;
    /**
     * Checkbox elements default to active.
     *
     * @type {Boolean}
     * @private
     */
    this.active = true;
  }

  /**
   * Extend the menu_item prototype.
   * @type Object
   */
  menu_checkbox.prototype = Object.create(Menu_item.prototype);

  /**
   * Generates the html for the menu item.
   *
   * @returns {HTMLElement}
   */
  menu_checkbox.prototype.create = function () {
    // Create the menu item.
    var menu_item = document.createElement('div');
    menu_item.className = this.class;
    var item = $(menu_item);
    // Create a checkbox.
    var checkbox = document.createElement('input');
    checkbox.id = this.id;
    $(checkbox).attr('type', 'checkbox');
    if (this.active === true) {
      $(checkbox).attr('checked', 'checked');
    }
    $(checkbox).attr('tabindex', -1);
    // Create the lable for the checkbox.
    var text = document.createElement('label');
    var textitem = $(text);
    textitem.attr('for', this.id);
    textitem.append(this.text);
    // Add the label and checkbox to the menu item.
    item.append(checkbox);
    item.append(text);
    return menu_item;
  };

  return menu_checkbox;
});
