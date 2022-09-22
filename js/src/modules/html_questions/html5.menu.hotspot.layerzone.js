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
 * A special menu group that is as wide as the layer menu in hotspot questions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['html5_group', 'jquery'], function(Menu_group, $) {
  /**
   * A special menu group that is as wide as the layer menu in hotspot questions.
   *
   * @param {String} id The unique identifier for the menu item.
   * @returns {menu_group}
   */
  function menu_hotspot_layerzone() {
    // Extend the hotspot prototype.
    Menu_group.call(this, '');
    /**
     * @see ROGO.html5.menu_item.class
     * @private
     */
    this.layerzoneclass = 'layerzone';
  }

  /**
   * Extend the menu_item prototype.
   * @type Object
   */
  menu_hotspot_layerzone.prototype = Object.create(Menu_group.prototype);

  /**
   * Constructor.
   *
   * @returns {HTMLElement}
   */
  menu_hotspot_layerzone.prototype.create = function () {
    var layerzone = document.createElement('div');
    layerzone.className = this.layerzoneclass;
    $(layerzone).append(Menu_group.prototype.create.call(this));
    return layerzone;
  };

  return menu_hotspot_layerzone;
});
