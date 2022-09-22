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
 * Base html5 question class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['jquery'], function($) {
  /**
   * Constructor for the base html5_question class.
   *
   * @returns {html5_question}
   */
  function question() {
    /**
     * The type of question.
     * @type {String}
     * @private
     */
    this.type;
    /**
     * The number of the question.
     *
     * @type {Number}
     * @private
     */
    this.number;
    /**
     * id of the html element that contains the question.
     *
     * @type {String}
     * @private
     */
    this.identifier;
    /**
     * The mode that the question is in.
     *
     * @type {String}
     * @private
     */
    this.mode;
    /**
     * The configuration for the question.
     *
     * @type {String}
     * @private
     */
    this.config;
    /**
     * The url to the image to be loaded by the hotspot.
     * @type {String}
     * @private
     */
    this.image_url;
    /**
     * @type {Integer}
     * @private
     */
    this.image_width;
    /**
     * @type {Integer}
     * @private
     */
    this.image_height;
    /**
     * HTML image object.
     * @type {Element}
     * @private
     */
    this.image;
  }

  /**
   * Gets the type of question.
   *
   * @returns {String}
   */
  question.prototype.get_type = function () {
    return this.type;
  };

  /**
   * Sets the type of the question.
   *
   * @param {String} type
   * @returns {void}
   * @private
   */
  question.prototype.set_type = function (type) {
    this.type = type;
  };

  /**
   * Gets the mode of the question.
   *
   * @returns {String}
   */
  question.prototype.get_mode = function () {
    return this.mode;
  };

  /**
   * Sets the mode of the question.
   *
   * @param {String} mode
   * @returns {void}
   * @private
   */
  question.prototype.set_mode = function (mode) {
    this.mode = mode;
  };

  /**
   * Generic setup method to ensure that it should always be present.
   *
   * @param {Element} parent
   * @returns {void}
   */
  question.prototype.setup = function (parent) {
    var question = $(parent);
    this.identifier = parent.id;
    this.config = question.data('setup');
    this.number = parseInt(question.data('number'));
  };

  /**
   * Sets up the main image for the question.
   *
   * @param {String} src
   * @returns {void}
   * @private
   */
  question.prototype.set_main_image = function (src, width, height) {
    this.image_url = src;
    this.image_width = width || 300;
    this.image_height = height || 200;
    this.image = this.create_image(src);
  };

  /**
   * Create an image object.
   *
   * @param {String} src
   * @returns {Image}
   */
  question.prototype.create_image = function (src) {
    var image = new Image();
    image.src = src;
    // Check if a redraw method exists, if it does do a redraw when the image has loaded.
    if (this.redraw) {
      image.onload = this.redraw.bind(this);
    }
    return image;
  };

  /**
   * All questions should implement the menthod that returns data to the ExamSys page,
   * that loaded the question.
   *
   * @returns {void}
   */
  question.prototype.update_page = function () {
  };

  return question;
});
