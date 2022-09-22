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
 * Colour selector for hotspot layers.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['lang', 'hotspot_layer', 'jquery'], function(Lang, Hotspot_layer, $) {
  /**
   * Constructor.
   *
   * @param {String} id The identifier for the colour selector.
   * @returns {hotspot_colourselector}
   */
  function hotspot_colourselector(id) {
    /**
     * The identifier for the colour selector
     * @type {String}
     */
    this.identifier = id + '_colourselect';
  }

  hotspot_colourselector.prototype.html_classes = {
    // The cancel button.
    cancel: 'cancel',
    // A colour swatch.
    colour: 'colour',
    // The area that contains all the swatches.
    colourarea: 'selectarea',
    // The heading of the colour selector.
    heading: 'header',
    // A selected colour swatch, there should only be one.
    selected: 'selected',
    // The colour selector itself.
    selector: 'colourselector'
  };

  /**
   * Generate the html for the colour selector.
   *
   * @returns {Element}
   */
  hotspot_colourselector.prototype.create = function () {
    // Generate the html.
    var colourselector = document.createElement('div'),
        selector = $(colourselector);
    selector.attr('id', this.identifier);
    selector.attr('tabindex', -1);
    selector.addClass(this.html_classes.selector);
    selector.append(this.create_heading());
    selector.append(this.create_colours());
    selector.append(this.create_cancel());
    return colourselector;
  };

  /**
   * Generate the heading for the colour selector.
   *
   * @returns {Element}
   */
  hotspot_colourselector.prototype.create_heading = function () {
    var heading = document.createElement('div'),
        element = $(heading);
    element.addClass(this.html_classes.heading);
    element.text(Lang.get_string('colourselect', 'html5'));
    return heading;
  };

  /**
   * Generate the colour swatch.
   *
   * @returns {Element}
   */
  hotspot_colourselector.prototype.create_colours = function () {
    var colourarea = document.createElement('div'),
        element = $(colourarea);
    element.addClass(this.html_classes.colourarea);
    for (var index in Hotspot_layer.prototype.colours) {
      var colour = 'rgb(' + Hotspot_layer.prototype.colours[index] + ')';
      element.append(this.create_colour(colour));
    }
    return colourarea;
  };

  /**
   * Generate the element that displays a specific colour.
   *
   * @param {String} colour
   * @returns {Element}
   */
  hotspot_colourselector.prototype.create_colour = function (colour) {
    var colourelement = document.createElement('div'),
        element = $(colourelement),
        attributes = {
          'aria-pressed': false,
          role: 'button',
          tabindex: -1
        };
    element.addClass(this.html_classes.colour);
    element.css('backgroundColor', colour);
    element.attr(attributes);
    return colourelement;
  };

  /**
   * Generate the cancel button.
   *
   * @returns {Element}
   */
  hotspot_colourselector.prototype.create_cancel = function () {
    var cancel = document.createElement('button'),
        element = $(cancel);
    element.addClass(this.html_classes.cancel);
    element.attr('id', this.identifier + '-' + this.html_classes.cancel);
    element.text(Lang.get_string('cancel', 'html5'));
    element.attr('tabindex', -1);
    return cancel;
  };

  /**
   * Sets the default colour selected.
   *
   * @param {String} colour rgb style colour code, i.e. rgb(127, 0, 255)
   * @returns {void}
   */
  hotspot_colourselector.prototype.set_colour = function (colour) {
    // Set the colour.
    var selector = '#' + this.identifier + ' .' + this.html_classes.colour,
        colourelements = $(selector);
    // Remove any existing slected colours.
    colourelements.removeClass(this.html_classes.selected);
    colourelements.attr('aria-pressed', false);
    // If an element has the colour passed set it to be selected.
    var scope = this;
    colourelements.each(function () {
      var element = $(this);
      if (element.css('backgroundColor') === colour) {
        element.addClass(scope.html_classes.selected);
        element.attr('aria-pressed', 'pressed');
      }
    });
  };

  /**
   * Find the colour that has been selected.
   *
   * @returns {String}
   */
  hotspot_colourselector.prototype.get_colour = function () {
    // Find the colour.
    var selector = '#' + this.identifier + ' .' + this.html_classes.colour + ' .' + this.html_classes.selected,
        colour = $(selector).css('backgroundColor');
    return colour;
  };

  /**
   * Show the colour selector.
   *
   * @returns {void}
   */
  hotspot_colourselector.prototype.show = function () {
    // Make visible.
    var menu = $('#' + this.identifier);
    menu.show();
    menu.focus();
  };

  /**
   * Hide the colour selector.
   *
   * @returns {void}
   */
  hotspot_colourselector.prototype.hide = function () {
    // Make visible invidible.
    $('#' + this.identifier).hide();
  };

  /**
   * Set the position of the top left of the colour selector.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_colourselector.prototype.position = function (x, y) {
    var position = {
      top: y,
      left: x
    };
    $('#' + this.identifier).css(position);
  };

  return hotspot_colourselector;
});
