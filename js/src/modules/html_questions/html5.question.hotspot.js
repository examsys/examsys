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
 * Base HTML5 hotspot question class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['helplauncher', 'log', 'html5_question', 'html5_menu', 'hotspot_layer', 'jquery'], function(Helplauncher, Log, Question, Menu, Hotspot_layer, $) {
  /**
   * Constructor for the base hotspot prototype.
   *
   * @returns {hotspot}
   */
  function hotspot() {
    // Extend the html5_question prototype.
    Question.call(this);
    this.set_type('hotspot');
    /**
     * The canvas object for the view menu.
     *
     * @type {CanvasRenderingContext2D}
     * @private
     */
    this.view_context;
    /**
     * An array of hotspot layers configured for the question.
     * @type {Array}
     * @private
     */
    this.layers = [];
    /**
     * The layer that is currently active.
     * @type {Number}
     * @private
     */
    this.active_layer = 0;
    /**
     * The object containing the editing for the question.
     * @type {html5_menu}
     * @private
     */
    this.editmenu = new Menu();
    /**
     * Defines if the inactive layers should display using their layer colour or a neutral colour.
     *
     * @type {Boolean}
     */
    this.inactive_coloured = false;
    /**
     * Defines if correct analysis answers should be displayed.
     *
     * @type {Boolean}
     */
    this.display_correct = true;
    /**
     * Defines if incorrect analysis answers should be displayed.
     *
     * @type {Boolean}
     */
    this.display_incorrect = true;
    /**
     * Defines if the shapes for inactive layers should be displayed.
     *
     * @type {Boolean}
     */
    this.display_inactive = true;
    /**
     * Defines if the layer names should be user editable.
     *
     * @type {Boolean}
     */
    this.layer_names_editable = false;
  }

  /**
   * Extend the question prototype.
   * @type Object
   */
  hotspot.prototype = Object.create(Question.prototype);

  /**
   * An array of the layer objects for the question.
   *
   * @returns {Array}
   */
  hotspot.prototype.get_layers = function () {
    return this.layers;
  };

  /**
   * Defines if shape control points should be displayed.
   *
   * @returns {Boolean}
   */
  hotspot.prototype.display_control_points = function () {
    return false;
  };

  /**
   * The index of the layer that is active.
   *
   * @returns {Number}
   */
  hotspot.prototype.get_active_layer = function () {
    return this.active_layer;
  };

  /**
   * Stores global settings for hotspot questions.
   *
   * @type {Object}
   * @static
   */
  hotspot.prototype.settings = {
    // The id for hotspot staff help.
    helpid: 17,
    // The maximum number of layers we support, not reccomended to go over 26.
    maximium_layers: 26,
    // The minimum number of standard size layers we support being displayed at one time.
    miniumum_viewable_layers: 8
  };

  /**
   * Stores important classes that are used as selectors.
   *
   * They are defined here so that there is only one place we need to
   * change in Javascript if we decide they need to be modified.
   *
   * @type {Object}
   * @static
   */
  hotspot.prototype.html_classes = {
    // Denotes that something is currently active.
    active: 'active',
    // The colour selector element in a layer menu item.
    colourpicker: 'colourselect',
    // The container for the edit menu.
    editmenu: 'editmenu',
    // An item in the edit menu.
    editmenuitem: 'menuitem',
    // The layer has been excluded.
    excluded: 'excluded',
    // The coloured bar in a layer menu item.
    layercolourbar: 'colourbar',
    // The area containing the layer label in the layer menu items.
    layerlabel: 'label',
    // The container for the label, text area, ect. of a layer item.
    layermain: 'mainarea',
    // The layer menu container.
    layermenu: 'layermenu',
    // The selector for a single layer.
    layermenuitem: 'layer',
    // The place the layer name is displayed.
    layername: 'textarea',
    // The container for the layer menu and view area.
    mainarea: 'main',
    // The area that displays the image.
    viewarea: 'viewarea'
  };

  /**
   * Gets the class name that is used for a type of element.
   *
   * @param {String} name
   * @param {Boolean} as_selector If true the name will have a dot prepended, so it can be used as a selector.
   * @returns {String}
   */
  hotspot.get_class = function (name, as_selector) {
    var selector;
    if (as_selector) {
      selector = '.';
    } else {
      selector = '';
    }
    if (this.prototype.html_classes[name]) {
      return selector + this.prototype.html_classes[name];
    }
    // We only get here if a bad selector name was passed.
    Log('Invalid class', 'error');
    return selector + 'undefined';
  };

  /**
   * Setup a hotspot question.
   *
   * @param {HTMLElement} parent
   * @returns {void}
   */
  hotspot.prototype.setup = function (parent) {
    Question.prototype.setup.call(this, parent);
    var q = $(parent);
    this.set_main_image(q.data('image'), q.data('imageWidth'), q.data('imageHeight'));

    if (this.config === '') {
      // Force a blank unconfigured hotspot.
      this.config = '~~';
    }

    // Get the information on layers from the config, upto the maximum number supported.
    var hotspotconfigs = this.config.split('|');
    for (var i = 0; (i < hotspotconfigs.length && i < this.settings.maximium_layers); i++) {
      this.layers.push(new Hotspot_layer(i, hotspotconfigs[i], this));
    }
  };

  /**
   * Creates the html that will be used to display the edit menu.
   *
   * @returns {HTMLElement}
   * @private
   */
  hotspot.prototype.create_editmenu = function () {
    return this.editmenu.create(this.identifier);
  };

  /**
   * Creates the html that will be used to display the layer menu.
   *
   * @returns {HTMLElement}
   * @private
   */
  hotspot.prototype.create_layermenu = function () {
    var menu_item;
    var layer_menu = document.createElement('div');
    layer_menu.id = this.identifier + '-layermenu';
    layer_menu.className = this.html_classes.layermenu;
    layer_menu.setAttribute('tabindex', 0);
    // Enable jQuery on the layer menu.
    var menu = $(layer_menu);
    menu.css('height', this.image_height);
    for (var i in this.layers) {
      menu_item = this.create_layermenu_item(this.layers[i]);
      menu.append(menu_item);
    }
    return layer_menu;
  };

  /**
   * Generate the id to be used by a layer menu item given it's index.
   *
   * @param {String} index
   * @returns {String}
   */
  hotspot.prototype.generate_layermenu_item_id = function (index) {
    return this.identifier + '-layer-' + index;
  };

  /**
   * Tests if the question is in a drawing mode.
   *
   * @returns {Boolean}
   */
  hotspot.prototype.in_draw_mode = function () {
    return false;
  };

  /**
   * Tests if the question is in a highlighting mode.
   *
   * @returns {Boolean}
   */
  hotspot.prototype.in_highlight_mode = function () {
    return false;
  };

  /**
   * Create a layermenu item.
   *
   * @param {hotspot_layer} layer
   * @returns {HTMLElement}
   * @private
   */
  hotspot.prototype.create_layermenu_item = function (layer) {
    // The main layer menu item.
    var layer_item = document.createElement('div');
    layer_item.id = this.generate_layermenu_item_id(layer.index);
    var item = $(layer_item);
    item.addClass(this.html_classes.layermenuitem);
    if (this.active_layer === layer.index) {
      item.addClass(this.html_classes.active);
    }
    if (layer.excluded) {
      item.addClass(this.html_classes.excluded);
    }
    item.attr('role', 'button');
    item.attr('tabindex', -1);

    // Create the colour bar.
    var colour_bar = document.createElement('div');
    colour_bar.className = this.html_classes.layercolourbar;
    $(colour_bar).css('backgroundColor', 'rgb(' + layer.colour + ')');
    item.append(colour_bar);

    // The flex container for the rest of the menu item content.
    var main_area = document.createElement('div');
    main_area.className = this.html_classes.layermain;
    var main = $(main_area);

    var label = document.createElement('div');
    label.className = this.html_classes.layerlabel;
    $(label).append(layer.get_label());
    main.append(label);

    var text_area = document.createElement('div');
    text_area.className = this.html_classes.layername;
    text_area.id = 'hotspot_label_' + layer.index;
    $(text_area).append(layer.text);
    $(text_area).attr('tabindex', -1);

    // If unanswered, add 'unans' class
    if (layer.hasOwnProperty('unanswered') && layer.unanswered == true) {
      $(text_area).addClass('unans');
    }

    main.append(text_area);

    item.append(main_area);

    return layer_item;
  };

  /**
   * Changes the active layer of the hotspot question.
   *
   * @param {Number} index The index of the new active layer.
   * @returns {Boolean}
   */
  hotspot.prototype.set_active_layer = function (index) {
    for (var i in this.layers) {
      var identifier = this.generate_layermenu_item_id(this.layers[i].index);
      if (identifier === index) {
        if (parseInt(i) === this.active_layer) {
          // Already active.
          return false;
        }
        // Make the current active layer inactive.
        var current_active = '#' + this.generate_layermenu_item_id(this.layers[this.active_layer].index);
        var scope = this;
        $(current_active).removeClass(scope.html_classes.active);
        // Make the new layer active.
        var new_active = '#' + this.generate_layermenu_item_id(this.layers[i].index);
        $(new_active).addClass(scope.html_classes.active);
        if (this.layer_names_editable) {
          // Make the active layer editable.
          $(current_active + ' .' + scope.html_classes.layername).removeAttr('contenteditable');
          $(new_active + ' .' + scope.html_classes.layername).attr('contenteditable', true);
        }
        this.active_layer = parseInt(i);
        // Redraw the canvas.
        this.redraw();
        return true;
      }
    }
    Log('Layer ' + index + ' not found', 'info');
    // The layer does not exist.
    return false;
  };

  /**
   * Find a layer by the id of a html element for it.
   *
   * @param {String} id
   * @returns {hotspot_layer|Boolean}
   */
  hotspot.prototype.find_layer_by_id = function (id) {
    for (var i in this.layers) {
      var identifier = this.generate_layermenu_item_id(this.layers[i].index);
      if (identifier === id) {
        return this.layers[i];
      }
    }
    // Does not exist.
    Log('Layer (' + id + ') not found in ' + this.identifier, 'warn');
    return false;
  };

  /**
   * Converts the hotspot into a string representation.
   *
   * @returns {String}
   */
  hotspot.prototype.toString = function () {
    var data = [];
    for (var i in this.layers) {
      data.push(this.layers[i].toString());
    }
    return data.join('|');
  };

  /**
   * Gets the answers for the question.
   *
   * @returns {String}
   */
  hotspot.prototype.answer_string = function () {
    var data = [];
    for (var i in this.layers) {
      data.push(this.layers[i].answer_string());
    }
    return data.join('|');
  };

  /**
   * Creates the html used to display the main view area of the hotspot question.
   *
   * @returns {HTMLElement}
   * @private
   */
  hotspot.prototype.create_view = function () {
    var canvas = document.createElement('canvas');
    canvas.id = this.identifier + '-viewarea';
    canvas.className = this.html_classes.viewarea;
    canvas.setAttribute('width', this.image_width);
    canvas.setAttribute('height', this.image_height);
    canvas.setAttribute('tabindex', 0);
    this.view_context = canvas.getContext("2d");
    this.draw_view();
    return canvas;
  };

  /**
   * Creates the container html for the layer menu and view area.
   *
   * @returns {HTMLElement}
   */
  hotspot.prototype.create_main = function () {
    var main = document.createElement('div');
    main.className = this.html_classes.mainarea;
    main.appendChild(this.create_layermenu());
    main.appendChild(this.create_view());
    return main;
  };

  /**
   * Draw the contents of the view area.
   *
   * @returns {void}
   * @private
   */
  hotspot.prototype.draw_view = function () {
    if (!this.view_context) {
      return;
    }
    this.view_context.fillStyle = 'rgba(0, 0, 255, 0.05)';
    this.view_context.rect(0, 0, this.image_width, this.image_height);
    this.view_context.fill();
    this.view_context.drawImage(this.image, 0, 0, this.image_width, this.image_height);
  };

  /**
   * Draws the layers that are part of the hotspot.
   *
   * @returns {void}
   * @private
   */
  hotspot.prototype.draw_layers = function () {
    if (!this.view_context) {
      return;
    }
    var active_layer = null;
    for (var i in this.layers) {
      if (this.layers[i].index === this.active_layer) {
        active_layer = i;
      } else {
        this.layers[i].draw_shapes(this.view_context);
      }
    }
    // Draw the active layer last so it will always be visible over the top of any other layers.
    if (active_layer !== null) {
      this.layers[active_layer].draw_shapes(this.view_context);
    }
    // Draw the answers on top of the shapes.
    for (var j in this.layers) {
      if (this.layers[j].index !== this.active_layer) {
        this.layers[j].draw_answers(this.view_context);
      }
    }
    if (active_layer !== null) {
      this.layers[active_layer].draw_answers(this.view_context);
    }
  };

  /**
   * Redraws the hotspot question in its current state.
   *
   * @returns {void}
   */
  hotspot.prototype.redraw = function () {
    this.draw_view();
    this.draw_layers();
  };

  /**
   * The prefix for all menu item identifiers.
   *
   * @returns {String}
   */
  hotspot.prototype.menu_item_prefix = function () {
    return this.identifier + '-';
  };

  /**
   * Generates a unique identifier for a menu item based on it's name.
   *
   * @param {String} name
   * @returns {String}
   */
  hotspot.prototype.menu_item_id_from_name = function (name) {
    return this.menu_item_prefix() + name;
  };

  /**
   * Get the menu item name from the identifier.
   *
   * @param {String} id
   * @returns {undefined}
   */
  hotspot.prototype.menu_item_name_from_id = function (id) {
    var prefix = this.menu_item_prefix();
    return id.substr(prefix.length);
  };

  /**
   * Activates a menu item.
   *
   * Classes that include this prototype and define an edit menu should define what each button does.
   *
   * @param {string} name The name of the menu item.
   * @returns {void}
   */
  hotspot.prototype.activate_edit_menu_item = function (name) {
    var current_active = this.editmenu.get_active();
    if (this.editmenu.set_active(name)) {
      if (current_active !== null && current_active.id !== name) {
        // There was a different button selected, take away the active class.
        $('#' + current_active.id).removeClass(this.html_classes.active);
        // Check if there a handler for deactivating this functionality.
        if (this[this.menu_item_name_from_id(current_active.id) + '_off']) {
          this[this.menu_item_name_from_id(current_active.id) + '_off'].call(this);
        } else {
          Log(this.menu_item_name_from_id(current_active.id) + ' deactivation handler not implemented', 'warn');
        }
      }
      // The button clicked was toggled on make it active.
      $('#' + name).addClass(this.html_classes.active);
      // Check if there a handler for activating this functionality.
      if (this[this.menu_item_name_from_id(name) + '_on']) {
        this[this.menu_item_name_from_id(name) + '_on'].call(this);
      } else {
        Log(this.menu_item_name_from_id(name) + ' activateion handler not implemented', 'warn');
      }
    } else {
      // The existing active button was toggled off, or is not togglable.
      $('#' + name).removeClass(this.html_classes.active);
      // Check if there a handler for deactivating this functionality.
      if (this[this.menu_item_name_from_id(name) + '_off']) {
        this[this.menu_item_name_from_id(name) + '_off'].call(this);
      } else {
        Log(this.menu_item_name_from_id(name) + ' deactivation handler not implemented', 'warn');
      }
    }
    this.redraw();
  };

  /**
   * Handles a user toggling the state of a menu checkbox.
   *
   * @param {String} name
   * @returns {undefined}
   */
  hotspot.prototype.menu_item_toggled = function (name) {
    var toggled_item = this.editmenu.find(name);
    if (toggled_item === false) {
      // It wasn't found'
      return;
    }
    if (toggled_item.toggle()) {
      // Turned on.
      if (this[this.menu_item_name_from_id(name) + '_on']) {
        this[this.menu_item_name_from_id(name) + '_on'].call(this);
      } else {
        Log(this.menu_item_name_from_id(name) + ' activation handler not implemented', 'warn');
      }
    } else {
      // Turned off.
      if (this[this.menu_item_name_from_id(name) + '_off']) {
        this[this.menu_item_name_from_id(name) + '_off'].call(this);
      } else {
        Log(this.menu_item_name_from_id(name) + ' deactivation handler not implemented', 'warn');
      }
    }
  };

  // Menu action handlers.

  /**
   * The help button was activated.
   *
   * @returns {void}
   */
  hotspot.prototype.help_off = function () {
    Helplauncher.launchHelp(this.settings.helpid, 'staff');
  };

  // End of Menu action handlers.

  // Layer menu event handlers.

  /**
   * Changes the name text of the layer identified by the id.
   *
   * @param {String} id
   * @param {String} name
   * @returns {void}
   */
  hotspot.prototype.change_layer_name = function (id, name) {
    this.find_layer_by_id(id).text = name;
    this.update_page();
  };

  /**
   * Appends a new layer.
   *
   * @returns {void}
   */
  hotspot.prototype.add_layer = function () {
    if (this.layers.length >= this.settings.maximium_layers) {
      // We already have the maximium number of layers.
      return;
    }
    var index = this.layers.length,
        layer = new Hotspot_layer(index, '', this);
    this.layers.push(layer);
    // Create the html item and attach it to the page.
    var layeritem = this.create_layermenu_item(layer);
    $('#' + this.identifier + '-layermenu').append(layeritem);
    this.set_active_layer(this.generate_layermenu_item_id(index));
  };

  /**
   * Deletes the active layer, or the last layer if none-are acive.
   *
   * @returns {void}
   */
  hotspot.prototype.delete_layer = function () {
    if (this.layers.length === 0) {
      return;
    }
    var layer_index = this.layers.length - 1;
    if (this.layers[this.active_layer].index === this.active_layer) {
      // We have found the active layer.
      layer_index = this.active_layer;
    }
    // Set a new active layer.
    if (layer_index === 0 && this.layers.length > 1) {
      this.set_active_layer(this.generate_layermenu_item_id(this.layers[1].index));
    } else if (this.layers.length > 1) {
      this.set_active_layer(this.generate_layermenu_item_id(this.layers[layer_index - 1].index));
    }
    // Remove the layer, but temporarily store it in layer.
    var layer = this.layers.splice(layer_index, 1);
    $('#' + this.generate_layermenu_item_id(layer[0].index)).remove();
    // Update the the remaining layers.
    for (var j = layer_index; j < this.layers.length; j++) {
      var element = $('#' + this.generate_layermenu_item_id(this.layers[j].index));
      // Update the index.
      this.layers[j].index = parseInt(j);
      // Update the id.
      element.attr('id', this.generate_layermenu_item_id(j));
      // Update the label.
      $('#' + this.generate_layermenu_item_id(j) + ' .' + this.html_classes.layerlabel).text(this.layers[j].get_label());
    }
    this.redraw();
  };

  // End of Layer menu event handlers.

  return hotspot;
});
