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
 * A menu_group holds a set of menu_items
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['html5_menuitem'], function(Menu_item) {
  /**
   * Creates a group of items in a menu.
   *
   * @param {String} id The unique identifier for the menu item.
   * @returns {ROGO.html5.menu_group}
   */
  function menu_group(id) {
    // Extend the hotspot prototype.
    Menu_item.call(this, '', '', id);
    /**
     * @see menu_item.class
     * @private
     */
    this.class = 'menuseparator';
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
  menu_group.prototype = Object.create(Menu_item.prototype);

  menu_group.prototype.add = function (item) {
    // TODO: check that a group is not being added. We will not support nested groups.
    this.items.push(item);
  };

  /**
   * Creates all the elements related to the group.
   * @returns {Array}
   */
  menu_group.prototype.create = function () {
    var separator = document.createElement('div');
    separator.className = this.class;
    // Get the elements of the children.
    var elements = [];
    for (var i in this.items) {
      elements.push(this.items[i].create());
    }
    elements.push(separator);
    return elements;
  };

  /**
   * Find an item in the group that has thie name.
   *
   * @param {String} name
   * @returns {Boolean}
   */
  menu_group.prototype.find = function (name) {
    for (var i in this.items) {
      var result = this.items[i].find(name);
      if (result !== false) {
        // We found the item we want, stop searching.
        return result;
      }
    }
    // It wasn't here.
    return false;
  };

  /**
   * Sets the menu item to be innactive and returns the original state.
   *
   * @returns {Boolean}
   */
  menu_group.prototype.set_inactive = function () {
    var changed_item = false;
    for (var i in this.items) {
      var result = this.items[i].set_inactive();
      if (result !== false) {
        changed_item = result;
      }
    }
    return changed_item;
  };

  return menu_group;
});