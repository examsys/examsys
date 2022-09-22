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
 * Defines a html5 hotspot question layer.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['hotspot_shape', 'html5images', 'html5helper', 'log'], function(Hotspot_shape, Images, Helper, Log) {
  /**
   * Constructor for the layer class.
   *
   * @param {Integer} index
   * @param {String} config_text
   * @param {hotspot} parent The question that the layer is attached to.
   * @returns {hotspot_layer}
   */
  var hotspot_layer = function(index, config_text, parent) {
    var config = config_text.split("~");
    /**
     * @type {Integer}
     * @public
     */
    this.index = index;
    /**
     * The text for the label.
     *
     * @type {String}
     * @public
     */
    this.text = config.shift();

    var nextval = config[0],
        colourval;
    if (config.length < 1 || nextval === 'polygon' || nextval === 'rectangle' || nextval === 'ellipse') {
      // Some legacy configs, and new layers, do not contain a colour, so we need to get one.
      colourval = this.get_default_colour();
    } else {
      var encoded_colour = config.shift();
      if (encoded_colour === '') {
        // No valid colour is set, this happens on brand new hotspots.
        colourval = this.get_default_colour();
      } else {
        colourval = Helper.decode_colour_rgb(encoded_colour);
      }
    }

    /**
     * The colour of the label in rgb format.
     *
     * @type {String}
     * @public
     */
    this.colour = colourval;

    /**
     * An array of shapes used in the layer.
     *
     * @type {Array}
     * @public
     */
    this.shapes = [];

    // Find all the shapes configured in the layer.
    var shape, coordinates, id;
    while ((config.length / 3) >= 1) {
      // There are at least 3 values left.
      shape = config.shift();
      coordinates = config.shift();
      id = config.shift();
      // Add the shape to the layer.
      this.shapes.push(new Hotspot_shape(shape, coordinates, id, this));
    }

    /**
     * The number of shapes in the layer.
     *
     * @type {Number}
     * @public
     */
    this.length = this.shapes.length;
    /**
     * The user's answer to the question.
     *
     * @type {hotspot_answer}
     */
    this.user_answer;
    /**
     * Contains all user answers for the layer.
     *
     * @type {Array}
     */
    this.analysis = [];
    /**
     * The question that the layer is part of.
     *
     * @type {hotspot}
     */
    this.question = parent;
    /**
     * The id of the shape that is currently active, or null if no active shape is set.
     *
     * @type {Number|null}
     */
    this.active_shape = null;
    /**
     * Stores if the layer is excluded.
     *
     * @type {Boolean}
     */
    this.excluded = false;
  }

  /**
   * Settings used by all layers.
   *
   * @type {Object}
   * @static
   */
  hotspot_layer.prototype.settings = {
    fontweight: 'bold',
    fontsize: 18, // In pixels.
    fontface: 'Arial',
    lightfontcolour: 'rgba(255, 255, 255, 1)',
    darkfontcolour: 'rgba(0, 0, 0, 1)',
    analysisdotsize: 3,
    correctcolour: 'rgba(0, 255, 0, 1)',
    incorrectcolour: 'rgba(255, 0, 0, 1)'
  };

  /**
   * The default colours to be used by layers.
   *
   * The colours are also displayed in the colour selector for layers,
   * because of this the number and order of colours should be considered
   * for how it looks in that.
   *
   * @type Array
   */
  hotspot_layer.prototype.colours = [
    // A selection of distinct colours.
    '255, 0, 0', // #FF0000 - Red
    '255, 255, 0', // #FFFF00 - Yellow
    '0, 176, 80', // #00B050 - Green
    '0, 112, 192', // #0070C0 - Blue
    '112, 48, 160', // #7030A0 - Purple
    '192, 0, 0', // #C00000 - Dark Red
    '255, 192, 0', // #FFC000 - Orange
    '146, 208, 80', // #92D050 - Light Green
    '0, 176, 240', // #00B0F0 - Light Blue
    '0, 32, 96', // #002060 - Dark Blue
    '255, 255, 255', // #FFFFFF - White
    '0, 0, 0', // #000000 - Black
    // Grey Scale.
    '232, 232, 232',
    '216, 216, 216', // #D8D8D8
    '191, 191, 191', // #BFBFBF
    '165, 165, 165', // #A5A5A5
    '127, 127, 127', // #7F7F7F
    '70, 70, 70',
    // Off Grey scale
    '238, 236, 225', // #EEECE1
    '221, 217, 195', // #DDD9C3
    '196, 189, 151', // #C4BD97
    '147, 137, 83', // #938953
    '73, 68, 41', // #494429
    '29, 27, 16', // #1D1B10
    // Reds
    '242, 220, 219', // #F2DCDB
    '229, 185, 183', // #E5B9B7
    '217, 150, 148', // #D99694
    '192, 80, 77', // #C0504D
    '149, 55, 52', // #953734
    '99, 36, 35', // #632423
    // Oranges
    '253, 234, 218', // #FDEADA
    '251, 213, 181', // #FBD5B5
    '250, 192, 143', // #FAC08F
    '247, 150, 70', // #F79646
    '227, 108, 9', // #E36C09
    '151, 72, 6', // #974806
    // Greens
    '235, 241, 221', // #EBF1DD
    '215, 227, 188', // #D7E3BC
    '195, 214, 155', // #C3D69B
    '155, 187, 89', // #9BBB59
    '118, 146, 60', // #76923C
    '79, 97, 40', // #4F6128
    // Purples
    '229, 224, 236', // #E5E0EC
    '204, 193, 217', // #CCC1D9
    '178, 162, 199', // #B2A2C7
    '128, 100, 162', // #8064A2
    '95, 73, 122', // #5F497A
    '63, 49, 81', // #3F3151
    // Dark Blues
    '198, 217, 240', // #C6D9F0
    '141, 179, 226', // #8DB3E2
    '84, 141, 212', // #548DD4
    '31, 73, 125', // #1F497D
    '23, 54, 93', // #17365D
    '15, 36, 62', // #0F243E
    // Light Blues
    '216, 238, 243', // #DBEEF3
    '183, 221, 232', // #B7DDE8
    '146, 205, 220', // #92CDDC
    '75, 172, 198', // #4BACC6
    '49, 133, 155', // #31859B
    '32, 88, 103' // #205867
  ];

  /**
   * Gets the default colour for the layer.
   *
   * The default colour is based on the index.
   *
   * @returns {String}
   */
  hotspot_layer.prototype.get_default_colour = function() {
    // ensure we will never use an index that does not exist.
    var colour_index = (this.index) % this.colours.length;
    return this.colours[colour_index];
  };

  /**
   * Gets the display label for the layer.
   *
   * It is generated based on the index, starting at A for an index of 0.
   *
   * @returns {String}
   */
  hotspot_layer.prototype.get_label = function() {
    return String.fromCharCode(65 + this.index);
  };

  /**
   * Draw the contents of the layer into the canvas.
   *
   * @param {CanvasRenderingContext2D} context
   * @returns {void}
   */
  hotspot_layer.prototype.draw_shapes = function(context) {
    var active_index;
    if (!this.question.display_inactive && this.index !== this.question.get_active_layer()) {
      return;
    }
    // Draw any shapes onto the view area.
    for (var shape in this.shapes) {
      if (this.shapes[shape].id !== this.active_shape) {
        // First draw all the inactive shapes.
        this.shapes[shape].draw(context);
      } else {
        // Save the index of the active shape to draw it last.
        active_index = shape;
      }
    }
    if (active_index) {
      // Draw the active shape last, so it is always on top.
      this.shapes[active_index].draw(context);
    }
  };

  /**
   * Draw the user answers and analysys.
   *
   * @param {CanvasRenderingContext2D} context
   * @returns {void}
   */
  hotspot_layer.prototype.draw_answers = function(context) {
    if (!this.question.display_inactive && this.index !== this.question.get_active_layer()) {
      return;
    }
    if (this.user_answer) {
      // Draw the user answer on the layer.
      this.draw_answer(context);
    }
    if (this.analysis.length > 0) {
      this.draw_analysis(context);
    }
  };

  /**
   * Answers are drawn as a speech balloon onto the Canvas,
   * the origin point of the balloon is set to the answer point.
   *
   * If the balloon would go over the edge of the canvas it will be
   * flipped on the axis that would cross.
   *
   * @param {CanvasRenderingContext2D} context
   * @returns {void}
   */
  hotspot_layer.prototype.draw_answer = function(context) {
    context.save();
    context.font = this.settings.fontweight + ' ' + this.settings.fontsize + 'px ' + this.settings.fontface;
    // The text will be drawn centered on the selected point.
    context.textAlign = 'center';
    context.textBaseline = 'middle';

    var balloon_background = Images.map['toolbar/smoke_b.png'],
      balloon = Images.map['toolbar/smoke.png'],
      // Split the colour into an array containing the Red, Green and Blue colour values.
      background_colour = this.colour.split(/,[ ]*/),
      text_colour,
      label = this.get_label(),
      // Calculate if the answer image will go off the canvas.
      flip_horizontal = ((this.user_answer.x + balloon.width) > context.canvas.width) ? -1 : 1,
      flip_vertical = ((this.user_answer.y - balloon.height) < 0) ? -1 : 1,
      // Point on the image that is the center of the selection area.
      image_x_offset = 5.5,
      image_y_offset = 24,
      // The point to draw the image on.
      image_x = (this.user_answer.x - image_x_offset * flip_horizontal) * flip_horizontal,
      image_y = (this.user_answer.y - image_y_offset * flip_vertical) * flip_vertical,
      // Offset of the centre of the bubble from the centre of the image.
      text_x_offest = 7.5,
      text_y_offset = -2,
      // The position of the baseline of the start of the text label.
      text_x = (image_x + (balloon.width / 2) + text_x_offest) * flip_horizontal,
      text_y = (image_y  + (balloon.height / 2) + text_y_offset) * flip_vertical,
      // The image data methods do not seem to respect the scale settings,
      // so we need to ensure that the co-ordinates reflect that for them the axis will not flip.
      cut_x = image_x * flip_horizontal - (balloon_background.width * (flip_horizontal === -1)),
      cut_y = image_y * flip_vertical - (balloon_background.height * (flip_vertical === -1));
    context.save();
    context.scale(flip_horizontal, flip_vertical);
    // Get the area of the image we will be drawing to.
    var original_area = context.getImageData(cut_x, cut_y, balloon_background.width, balloon_background.height);
    // Clear the area we are going to draw onto.
    context.clearRect(image_x, image_y, balloon_background.width, balloon_background.height);

    // Draw a coloured background.
    context.drawImage(
      Images.image,
      balloon_background.left,
      balloon_background.top,
      balloon_background.width,
      balloon_background.height,
      image_x,
      image_y,
      balloon_background.width,
      balloon_background.height
    );

    // Get the same area with the image drawn on.
    var new_area = context.getImageData(cut_x, cut_y, balloon_background.width, balloon_background.height);
    for (var i = 0; i < new_area.data.length; i += 4) {
      var red_match = new_area.data[i] === 0,
        green_match = new_area.data[i + 1] === 42,
        blue_match = new_area.data[i + 2] === 255,
        transparancy_match = new_area.data[i + 3] === 255;
      if (red_match && green_match && blue_match && transparancy_match) {
        // The image was applied here, it is not transparent and the colour is the one we expect to change.
        new_area.data[i] = background_colour[0];
        new_area.data[i + 1] = background_colour[1];
        new_area.data[i + 2] = background_colour[2];
      } else {
        // Ensure the original pixel is put back.
        new_area.data[i] = original_area.data[i];
        new_area.data[i + 1] = original_area.data[i + 1];
        new_area.data[i + 2] = original_area.data[i + 2];
        new_area.data[i + 3] = original_area.data[i + 3];
      }
    }

    // Draw it back onto the canvas.
    context.putImageData(new_area, cut_x, cut_y);

    // Draw the border.
    context.drawImage(
      Images.image,
      balloon.left,
      balloon.top,
      balloon.width,
      balloon.height,
      image_x,
      image_y,
      balloon.width,
      balloon.height
    );

    context.restore();

    // Draw the layers label.
    var colour_intensity = (parseInt(background_colour[0]) + parseInt(background_colour[1]) + parseInt(background_colour[2])) / 3;
    if (colour_intensity > 127.5) {
      // Light background so make the text dark.
      text_colour = this.settings.darkfontcolour;
    } else {
      // Dark background so make the text light.
      text_colour = this.settings.lightfontcolour;
    }
    context.fillStyle = text_colour;
    context.fillText(label, text_x, text_y);

    // Draw a dot on the user answer point.
    context.strokeStyle = text_colour;
    context.strokeRect(
      this.user_answer.x,
      this.user_answer.y - 0.5,
      1,
      1
    );

    context.restore();
  };

  /**
   * Draws a point for each user answer in the anaysis array for the layer.
   *
   * @param {CanvasRenderingContext2D} context
   * @returns {void}
   */
  hotspot_layer.prototype.draw_analysis = function(context) {
    if (this.question.get_active_layer() !== this.index || (!this.question.display_correct && !this.question.display_incorrect)) {
      // Answers should only ever be drawn for the active layer, or the settings do not allow drawing of points.
      return;
    }
    context.save();
    var i, correct, xposition, yposition, skip;
    var size = this.settings.analysisdotsize,
      offset = size / 2;
    for (i in this.analysis) {
      correct = this.analysis[i].correct;
      xposition = this.analysis[i].x;
      yposition = this.analysis[i].y;

      // Test if the display of this answer should be skipped.
      skip = (!this.question.display_correct && correct) || (!this.question.display_incorrect && !correct);
      if (skip) {
        continue;
      }

      // Set the colour of the point.
      if (correct === true) {
        context.fillStyle = this.settings.correctcolour;
      } else {
        context.fillStyle = this.settings.incorrectcolour;
      }
      // Draw a dot to represent the user answer.
      context.fillRect(xposition - offset, yposition - offset, size, size);
    }
    context.restore();
  };

  /**
   * Sets an answer for the layer.
   *
   * @param {hotspot_answer} answer
   * @returns {void}
   */
  hotspot_layer.prototype.set_answer = function(answer) {
    this.user_answer = answer;
  };

  /**
   * Adds an answer for the analysys view for the layer.
   *
   * @param {hotspot_answer} answer
   * @returns {void}
   */
  hotspot_layer.prototype.add_analysis_answer = function(answer) {
    this.analysis.push(answer);
  };

  /**
   * Returns the answer currently set for this layer.
   *
   * @returns {hotspot_answer}
   */
  hotspot_layer.prototype.get_answer = function() {
    return this.user_answer;
  };

  /**
   * Finds if a shape in the layer contains the coordinates.
   * If it does it will be set as the active shape.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_layer.prototype.select_shape_at = function(x, y) {
    this.active_shape = this.get_shape_at(x, y);
  };

  /**
   * Deletes a shape that contains the coordinates passed.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_layer.prototype.delete_shape_at = function(x, y) {
    var shape = this.get_shape_at(x, y);
    if (shape === null) {
      // No shape clicked.
      return;
    } else if (this.active_shape === shape) {
      // We just deleted the active shape so it cannot be active anymore.
      this.active_shape = null;
    } else if (this.active_shape > shape) {
      // The index of the active shape will have been reduced by one.
      this.active_shape--;
    }
    // Delete the shape.
    this.shapes.splice(shape, 1);
    this.length--;
    // Fix the shape ids.
    for (var i = shape; i < this.shapes.length; i++) {
      this.shapes[i].id = parseInt(i);
    }
  };

  /**
   * Finds and returns the array index of the shape that contains the point.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Number}
   */
  hotspot_layer.prototype.get_shape_at = function(x, y) {
    if (this.active_shape !== null && this.shapes[this.active_shape].contains(x, y)) {
      // The active shape was clicked, it always has priority.
      return this.active_shape;
    }
    for (var shape in this.shapes) {
      if (shape !== this.active_shape && this.shapes[shape].contains(x, y)) {
        // Found a shape at the coordinates.
        return parseInt(shape);
      }
    }
    return null;
  };

  /**
   * Creates a shape based on it's starting point.
   *
   * @param {String} shape The type of shape
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_layer.prototype.add_shape = function(shape, x, y) {
    var index = this.shapes.length,
      coordinates;
    switch (shape) {
      case 'rectangle':
      case 'ellipse':
      case 'polygon':
        coordinates = [x.toString(16), y.toString(16), x.toString(16), y.toString(16)].join(',');
        break;
      default:
       Log('Invalid shape (' + shape + '), cannot add to layer.', 'error');
        return;
    }
    this.shapes.push(new Hotspot_shape(shape, coordinates, index, this));
    this.active_shape = index;
    this.length++;
  };

  /**
   * Changes the last point of the shape.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_layer.prototype.change_end_point = function(x, y) {
    if (this.active_shape === null) {
      return;
    }
    this.shapes[this.active_shape].change_end_point(x, y);
  };

  /**
   * Move the active shape by the number of pixels described in the arguments.
   *
   * @param {Number} xdiff The distance to move on the x-axis.
   * @param {Number} ydiff The distance to move on the y-axis.
   * @returns {void}
   */
  hotspot_layer.prototype.move_active_shape = function(xdiff, ydiff) {
    if (this.active_shape === null) {
      return;
    }
    this.shapes[this.active_shape].move_by(xdiff, ydiff, true);
  };

  /**
   * Adds a point to a polygon.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Boolean} true if the shape was finished, otherwise false.
   */
  hotspot_layer.prototype.add_polygon_point = function(x, y) {
    if (this.active_shape === null) {
      // We are starting a new polygon.
      this.add_shape('polygon', x, y);
      return;
    }
    var closed = this.shapes[this.active_shape].add_point(x, y);
    return closed;
  };

  /**
   * Test if a control point for the active shape is at the coordinates.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Number|Boolean} The index of the control vertex or false if none selected.
   */
  hotspot_layer.prototype.activate_control_point_at = function(x, y) {
    if (this.active_shape === null) {
      return false;
    }
    return this.shapes[this.active_shape].activate_control_point_at(x, y);
  };

  /**
   * Move a control point in the active shape.
   *
   * @param {Number} x The current x position of the pointer.
   * @param {Number} y The current y position of the pointer.
   * @param {Number} oldx The new x position of the pointer.
   * @param {Number} oldy The new y position of the pointer.
   * @param {Number} index The index of the vertex that is being moved.
   * @returns {Boolean}
   */
  hotspot_layer.prototype.change_point = function(x, y, oldx, oldy, index) {
    if (this.active_shape === null) {
      return false;
    }
    return this.shapes[this.active_shape].change_point(x, y, oldx, oldy, index);
  };

  /**
   * Converts the layer to a string.
   *
   * @returns {String}
   */
  hotspot_layer.prototype.toString = function() {
    var data = [this.text, Helper.encode_rgb_colour(this.colour)];
    for (var i in this.shapes) {
      data.push(this.shapes[i].toString());
    }
    return data.join('~') + '~';
  };

  /**
   * Returns the string representation of the users answer.
   *
   * @returns {String}
   */
  hotspot_layer.prototype.answer_string = function() {
    var data = '';
    if (this.user_answer) {
      data = this.user_answer.toString();
    }
    if (this.analysis.length > 0) {
      var answers = [];
      for (var i in this.analysis) {
        answers.push(this.analysis[i].toString());
      }
      data = answers.join(',');
    }
    return data;
  };

  return hotspot_layer;
});
