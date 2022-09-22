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
 * HTML5 hotspot question editing mode functions
*
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['hotspot_correction', 'lang', 'html5_button', 'jquery'], function(Hotspot_correction, Lang, Menu_button, $) {
  /**
   * Constructor
   *
   * @returns {hotspot_edit}
   */
  function hotspot_edit(questions) {
    // Extend the hotspot prototype.
    Hotspot_correction.call(this, questions);
    this.set_mode('edit');
  }

  /**
   * Extend the hotspot prototype.
   * @type Object
   */
  hotspot_edit.prototype = Object.create(Hotspot_correction.prototype);

  /**
   * Builds the menu for edit mode questions.
   *
   * @returns {void}
   */
  hotspot_edit.prototype.build_menu = function () {
    Hotspot_correction.prototype.build_menu.call(this);
    var add_layer = new Menu_button('add_layer', Lang.get_string('addlayer', 'html5'), this.menu_item_id_from_name('add_layer'));
    add_layer.togglable = false;
    this.layerzone.add(add_layer);
    var remove_layer = new Menu_button('remove_layer', Lang.get_string('removelayer', 'html5'), this.menu_item_id_from_name('remove_layer'));
    remove_layer.togglable = false;
    this.layerzone.add(remove_layer);
  };

  /**
   * Create a layermenu item.
   *
   * @param {hotspot_layer} layer
   * @returns {HTMLElement}
   */
  hotspot_edit.prototype.create_layermenu_item = function (layer) {
    var menu_item = Hotspot_correction.prototype.create_layermenu_item.call(this, layer);
    // Make the active layers text area editable.
    if (this.active_layer === layer.index) {
      $('.textarea', menu_item).attr('contenteditable', 'true');
    }
    return menu_item;
  };

  /**
   * Changess the active layer of the hotspot question.
   *
   * @param {Number} index The index of the new active layer.
   * @returns {Boolean}
   */
  hotspot_edit.prototype.set_active_layer = function (index) {
    var current_active = this.active_layer,
        parent_success = Hotspot_correction.prototype.set_active_layer.call(this, index);
    // Swap the editable text area to the new active layer.
    if (parent_success) {
      $('#' + this.identifier + '-layer-' + this.layers[current_active].index + ' .textarea').removeAttr('contenteditable');
      $('#' + this.identifier + '-layer-' + this.layers[this.active_layer].index + ' .textarea').attr('contenteditable', 'true');
    }
    return parent_success;
  };

  // Menu action handlers.

  /**
   * The add layer menu item was activated.
   *
   * @returns {void}
   */
  hotspot_edit.prototype.add_layer_off = function () {
    this.add_layer();
  };

  /**
   * The delete layer menu item was activated.
   *
   * @returns {void}
   */
  hotspot_edit.prototype.remove_layer_off = function () {
    if (confirm(Lang.get_string('removelayerconfirm', 'html5'))) {
      this.delete_layer();
    }
  };

  // End of Menu action handlers.

  return hotspot_edit;
});
