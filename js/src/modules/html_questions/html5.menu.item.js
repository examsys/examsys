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
 * Base html5 menu item base class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['hotspot', 'jquery'], function(Hotspot, $) {
  /**
   * Constructor.
   *
   * @param {String} image The name of the css class containing the details of the items image.
   * @param {String} help A description of what the menu item does.
   * @param {String} id The unique identifier for the menu item.
   * @returns {menu_item}
   */
  function menu_item(image, help, id) {
    /**
     * The unique identifier for the menu item.
     *
     * @type {String}
     * @private
     */
    this.id = id;
    /**
     * The class of the menu item.
     *
     * @type {String}
     * @private
     */
    this.class = Hotspot.get_class('editmenuitem');
    /**
     * The class of the item image.
     *
     * @type {String}
     * @private
     */
    this.image = image;
    /**
     * Brief description of the function of the menu item
     *
     * @type {String}
     * @private
     */
    this.help = help;
    /**
     * Determines if the menu item can toggle an active state or not.
     *
     * @type {Boolean}
     */
    this.togglable = true;
    /**
     * Stores if the menu option is active.
     *
     * @type {Boolean}
     * @private
     */
    this.active = false;
  }

  /**
   * Creates the html for the menu item.
   *
   * @returns {Element}
   */
  menu_item.prototype.create = function () {
    var menu_item = document.createElement('div');
    if (this.id) {
      menu_item.id = this.id;
    }
    var item = $(menu_item);
    item.addClass(this.class);
    if (this.image) {
      item.addClass(this.image);
    }
    if (this.active) {
      item.addClass('active');
    }
    if (this.help) {
      item.attr('title', this.help);
    }
    return menu_item;
  };

  /**
   * Toggles if the menu item is active and then returns the state the button in now in.
   *
   * @returns {Boolean}
   */
  menu_item.prototype.toggle = function () {
    if (!this.togglable) {
      return this.active;
    }
    if (this.active === true) {
      this.active = false;
    } else {
      this.active = true;
    }
    return this.active;
  };

  /**
   * Sets the menu item to be innactive and returns itself if the was toggles off.
   *
   * @returns {Boolean|menu_item}
   */
  menu_item.prototype.set_inactive = function () {
    var original_state = this.active;
    this.active = false;
    if (original_state === false) {
      return false;
    } else {
      return this;
    }
  };

  /**
   * Returns the name of the the menu item.
   *
   * @returns {String}
   */
  menu_item.prototype.get_name = function () {
    return this.id;
  };

  /**
   * Returns itself if it has the specified name.
   *
   * @param {String} name
   * @returns {String|Boolean}
   */
  menu_item.prototype.find = function (name) {
    if (this.id === name) {
      return this;
    }
    return false;
  };

  return menu_item;
});
