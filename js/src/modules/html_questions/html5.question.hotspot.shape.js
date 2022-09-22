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
 * Defines a shape that is in a HTML5 hotspot question layer.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['log', 'html5helper'], function(Log, Helper) {
  /**
   * Constructor for the layer class.
   *
   * @param {String} shape
   * @param {String} coordiantes
   * @param {Integer} id
   * @param {hotspot_layer} parent The parent html5_layer of the shape.
   * @returns {hotspot_shape}
   */
  function hotspot_shape(shape, coordiantes, id, parent) {
    /**
     * Stores the type of shape.
     * i.e. ellipse, polygon, rectangle
     *
     * @type {String}
     * @public
     */
    this.shape = shape;

    /**
     * A comma seperated string of coordinates.
     *
     * @type {String}
     * @public
     */
    this.coordinates = this.validateCoordinates(coordiantes);

    /**
     * An id for the shape.
     *
     * @type {Integer}
     * @public
     */
    this.id = parseInt(id);

    /**
     * The parent layer of the shape.
     *
     * @type {hotspot_layer}
     * @private
     */
    this.layer = parent;
  }

  /**
   * Settings for all shape objects.
   *
   * @type {Object}
   * @static
   */
  hotspot_shape.prototype.settings = {
    active_border_transparancy: 0.75,
    active_fill_transparancy: 0.5,
    // The amount to change the transparancy by if the shape is highlighted.
    highlight_tansparancy_mod: 0.25,
    inactive_border_colour: '255, 255, 255',
    inactive_border_transparancy: 0.5,
    inactive_fill_colour: '255, 255, 255',
    inactive_fill_transparancy: 0.25,
    mid_border_colour: 'rgba(0, 0, 0, 1)',
    mid_fill_colour: 'rgba(255, 255, 255, 1)',
    // The distance in pixels that the user must click
    // from a point for it to be considered to be a 'hit'.
    point_range: 4,
    vertex_border_colour: 'rgba(255, 255, 255, 1)',
    vertex_fill_colour: 'rgba(0, 0, 0, 1)'
  };

  /**
   * Returns an array of coordinate parts for the shape.
   *
   * @returns {Array}
   */
  hotspot_shape.prototype.getCoordinates = function () {
    var coordinates = this.coordinates.split(',');
    for (var i in coordinates) {
      coordinates[i] = parseInt(coordinates[i], 16);
    }
    return coordinates;
  };

  /**
   * Validate loaded coordinates.
   *
   * @param {String} coords
   * @returns {String}
   */
  hotspot_shape.prototype.validateCoordinates = function (coords) {
    var coordinates = coords.split(','),
        boundary = {
          minx: 0,
          miny: 0
        };
    for (var i in coordinates) {
      coordinates[i] = parseInt(coordinates[i], 16);
    }
    // Test if the coordinates have gone outside the boundary.
    for (var j = 0; j < coordinates.length; j += 2) {
      if (coordinates[j] < boundary.minx) {
        // To the left of the boundary.
        coordinates[j] = boundary.minx;
      }
      if (coordinates[j + 1] < boundary.miny) {
        // Above the boundary.
        coordinates[j + 1] = boundary.miny;
      }
    }
    // Convert coordinates back to hexidecimal.
    coordinates.forEach(this.decimal_to_hex);
    return coordinates.join(',');
  };

  /**
   * Move the vertext of the bounding box.
   * This method reroutes the call to the correct method for the type of shape.
   *
   * @param {Number} x The current x position of the pointer.
   * @param {Number} y The current y position of the pointer.
   * @param {Number} oldx The new x position of the pointer.
   * @param {Number} oldy The new y position of the pointer.
   * @param {Number} index The index of the vertex that is being moved.
   * @returns {Boolean}
   */
  hotspot_shape.prototype.change_point = function (x, y, oldx, oldy, index) {
    if (this['change_point_' + this.shape]) {
      return this['change_point_' + this.shape].call(this, x, y, oldx, oldy, index);
    }
    return false;
  };

  /**
   * Move a vertex of a polygon.
   *
   * @param {Number} x The current x position of the pointer.
   * @param {Number} y The current y position of the pointer.
   * @param {Number} oldx The new x position of the pointer.
   * @param {Number} oldy The new y position of the pointer.
   * @param {Number} index The index of the vertex that is being moved.
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.change_point_polygon = function (x, y, oldx, oldy, index) {
    var control_point = this.control_point_contains(oldx, oldy);
    if (control_point === false || control_point.index !== index || control_point.type !== 'vertex') {
      // Wrong control point.
      return false;
    }
    var coordinates = this.getCoordinates(),
        // Work out the new coordinates of the vertex.
        newx = control_point.x + (x - oldx),
        newy = control_point.y + (y - oldy);
    // Replace the existing point with the new one.
    coordinates.splice(index * 2, 2, newx, newy);
    // Convert coordinates back to hexidecimal.
    coordinates.forEach(this.decimal_to_hex);
    this.coordinates = coordinates.join(',');
    return true;
  };

  /**
   * Move a vertex of a rectangle.
   *
   * @param {Number} x The current x position of the pointer.
   * @param {Number} y The current y position of the pointer.
   * @param {Number} oldx The new x position of the pointer.
   * @param {Number} oldy The new y position of the pointer.
   * @param {Number} index The index of the vertex that is being moved.
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.change_point_rectangle = function (x, y, oldx, oldy, index) {
    var control_point = this.control_point_contains(oldx, oldy);
    if (control_point === false || control_point.index !== index || control_point.type !== 'vertex') {
      // Wrong control point.
      return false;
    }
    Log([control_point, index, x, y, oldx, oldy], 'info');
    var coordinates = this.getCoordinates(),
        // Work out the new coordinates of the vertex.
        newx = control_point.x + (x - oldx),
        newy = control_point.y + (y - oldy);
    // The index number determines which x and which y are affected.
    // The variables are named assuming that the bounding box
    // was drawn left to right, top to bottom.
    switch (index) {
      case 0:
        // Top left.
        coordinates[0] = newx;
        coordinates[1] = newy;
        break;
      case 1:
        // Top right.
        coordinates[2] = newx;
        coordinates[1] = newy;
        break;
      case 2:
        // Bottom right.
        coordinates[2] = newx;
        coordinates[3] = newy;
        break;
      case 3:
        // Bottom left.
        coordinates[0] = newx;
        coordinates[3] = newy;
        break;
      default:
        Log('Invalid control point index', 'error');
        return false;
    }
    coordinates.forEach(this.decimal_to_hex);
    this.coordinates = coordinates.join(',');
    return true;
  };

  /**
   * Move a vertex of an ellipse (they have a rectangle bounding box).
   *
   * @param {Number} x The current x position of the pointer.
   * @param {Number} y The current y position of the pointer.
   * @param {Number} oldx The new x position of the pointer.
   * @param {Number} oldy The new y position of the pointer.
   * @param {Number} index The index of the vertex that is being moved.
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.change_point_ellipse = function (x, y, oldx, oldy, index) {
    return this.change_point_rectangle(x, y, oldx, oldy, index);
  };

  /**
   * Change the last point of the shape.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {void}
   */
  hotspot_shape.prototype.change_end_point = function (x, y) {
    var coordinates = this.getCoordinates();
    coordinates.splice(coordinates.length - 2, 2, x, y);
    // Convert coordinates back to hexidecimal.
    coordinates.forEach(this.decimal_to_hex);
    this.coordinates = coordinates.join(',');
  };

  /**
   * Callback for changing the values in an array from decimal to hexidecimal.
   *
   * @param {Number} value
   * @param {Number} index
   * @param {Array} array
   * @returns {void}
   */
  hotspot_shape.prototype.decimal_to_hex = function (value, index, array) {
    array[index] = value.toString(16);
  };

  /**
   * Add a new coordinate to the shape.
   *
   * @param {Number} x
   * @param {Number} y
   * @param {Number} index The position that this should be inserted into.
   * @returns {Boolean} true if the shape was closed.
   */
  hotspot_shape.prototype.add_point = function (x, y, index) {
    if (this.shape !== 'polygon') {
      return;
    }
    var coordinates = this.getCoordinates(),
        returnval,
        // Find the distance the new point is from the start point.
        distance = {
          x: Math.abs(coordinates[0] - x),
          y: Math.abs(coordinates[1] - y)
        };
    // If no index is specified set it to be the end of the coordinates array.
    index = index || (coordinates.length / 2);
    if (distance.x > this.settings.point_range || distance.y > this.settings.point_range) {
      // Insert the point at the index.
      coordinates.splice(index * 2, 0, x, y);
      returnval = false;
    } else {
      // The user clicked on the start point, so we do not need to add more points,
      // but we do need to remove the last temporary point.
      coordinates.splice(coordinates.length - 2);
      returnval = true;
    }
    // Convert coordinates back to hexidecimal.
    coordinates.forEach(this.decimal_to_hex);
    this.coordinates = coordinates.join(',');
    return returnval;
  };

  /**
   * Moves the shape by the number of pixels described in the arguments.
   *
   * We should however not allow the shape to move off the edge of the image itself.
   *
   * @param {Number} xdiff
   * @param {Number} ydiff
   * @param {Boolean} enforce_boundary If true the shape will be kept inside the boundaty of the image.
   * @returns {void}
   */
  hotspot_shape.prototype.move_by = function (xdiff, ydiff, enforce_boundary) {
    var coordinates = this.getCoordinates(),
        boundary = {
          minx: 0,
          miny: 0,
          maxx: this.layer.question.view_context.canvas.width,
          maxy: this.layer.question.view_context.canvas.height
        },
        // Adjusts the movement if the shape goes over the boundary.
        adjustx = 0,
        adjusty = 0;
    // Change all the coordinates.
    for (var i = 0; i < coordinates.length; i += 2) {
      var x = coordinates[i],
          y = coordinates[i + 1],
          newx = x + xdiff,
          newy = y + ydiff,
          temp_adjustx,
          temp_adjusty;
      // Set the new coordinates.
      coordinates[i] = newx;
      coordinates[i + 1] = newy;
      // Test if the coordinate has gone outside the boundary.
      if (newx < boundary.minx) {
        // To the left of the boundary.
        temp_adjustx = boundary.minx - newx;
      } else if (newx > boundary.maxx) {
        // To the right of the boundary.
        temp_adjustx = boundary.maxx - newx;
      }
      if (Math.abs(temp_adjustx) > Math.abs(adjustx)) {
        // We want to store the largest change.
        adjustx = temp_adjustx;
      }
      if (newy < boundary.miny) {
        // Above or below the boundaries.
        temp_adjusty = boundary.miny - newy;
      } else if (newy > boundary.maxy) {
        temp_adjusty = boundary.maxy - newy;
      }
      if (Math.abs(temp_adjusty) > Math.abs(adjusty)) {
        // We want to store the largest change.
        adjusty = temp_adjusty;
      }
    }
    // Convert coordinates back to hexidecimal.
    coordinates.forEach(this.decimal_to_hex);
    this.coordinates = coordinates.join(',');
    // Test if we need to adjust the shape so it doesn't move off the edge of the image.
    if (enforce_boundary && (adjustx !== 0 || adjusty !== 0)) {
      this.move_by(adjustx, adjusty, false);
    }
  };

  /**
   * Draw the shape.
   *
   * @param {CanvasRenderingContext2D} context
   * @returns {void}
   */
  hotspot_shape.prototype.draw = function (context) {
    var border,
        border_colour,
        border_transparancy,
        coordinates = this.getCoordinates(),
        fill,
        fill_colour,
        fill_transparancy,
        // Check if the shape is in the current active area.
        in_active_layer = this.in_active_layer(),
        active_shape = this.is_active();

    // Work out the colour
    if (in_active_layer || this.layer.question.inactive_coloured || this.layer.question.display_inactive) {
      border_colour = this.layer.colour;
      fill_colour = this.layer.colour;
    } else {
      // White.
      border_colour = this.settings.inactive_border_colour;
      fill_colour = this.settings.inactive_fill_colour;
    }

    // Work out the transarency that should be used.
    if (in_active_layer && (active_shape || this.layer.question.inactive_coloured)) {
      // The active shape should be highlighted, as should all shapes in a layer
      // of a question type that allows inactive layers to use their colour.
      fill_transparancy = this.settings.active_fill_transparancy;
      border_transparancy = this.settings.active_border_transparancy;
    } else {
      // Standard transparency.
      fill_transparancy = this.settings.inactive_fill_transparancy;
      border_transparancy = this.settings.inactive_border_transparancy;
    }

    if (this.is_highlighted()) {
      // The shape is highlighted, i.e. by being moused over.
      fill_transparancy += this.settings.highlight_tansparancy_mod;
      border_transparancy += this.settings.highlight_tansparancy_mod;
    }

    // Set up the colour definitions.
    border = 'rgba(' + border_colour + ', ' + border_transparancy + ')';
    fill = 'rgba(' + fill_colour + ', ' + fill_transparancy + ')';

    // Check if we have a drawing method for the shape.

    if (Helper['draw_' + this.shape]) {
      Helper['draw_' + this.shape].call(this, context, fill, border, coordinates);
    }

    if (this.show_control_points()) {
      // Draw control points for the shape.
      var cp = this.get_control_points(coordinates);
      // Draw any mid points.
      for (var mid in cp.midpoints) {
        Helper.draw_rectangle(context, this.settings.mid_fill_colour, this.settings.mid_border_colour, cp.midpoints[mid]);
      }
      // Draw any vertexes.
      for (var vertex in cp.vertexes) {
        Helper.draw_ellipse(context, this.settings.vertex_fill_colour, this.settings.vertex_border_colour, cp.vertexes[vertex]);
      }
    }

    if (this.show_polygon_start_point()) {
      // Draw a shape that shows where the user can click to close a polygon.
      var startpoint = [
        coordinates[0] - this.settings.point_range,
        coordinates[1] - this.settings.point_range,
        coordinates[0] + this.settings.point_range,
        coordinates[1] + this.settings.point_range
      ];
      Helper.draw_ellipse(context, border, border, startpoint);
  }
  };

  /**
   * Routes the coordinates to the appropriate method to get the control points for the shape type.
   * If one does not exist it sends back an empty return value.
   *
   * The shape specific methods must accept a single parameter that is an array of coordianates
   * from the getCoordinates method.
   *
   * They must return an object with two properties:
   * - vertexes: containing an array of points sutiable for the ellipse draw method.
   * - midpoints: containing an array of points suitiable for the rectangle draw method.
   *
   * @param {Array} coordinates
   * @returns {Object}
   */
  hotspot_shape.prototype.get_control_points = function (coordinates) {
    if (this['get_' + this.shape + '_controlpoints']) {
      return this['get_' + this.shape + '_controlpoints'].call(this, coordinates);
    } else {
      return {
        vertexes: [],
        midpoints: []
      };
    }
  };

  /**
   * Get the control points for a polygon.
   * Polygons have two types of control point, at the vertixes to move it,
   * and at the mid point of each line so that a new point can be created.
   *
   * @param {Array} coordinates
   * @returns {Object}
   */
  hotspot_shape.prototype.get_polygon_controlpoints = function (coordinates) {
    var control_points = {
      vertexes: [],
      midpoints: []
    };
    for (var i = 0; i < coordinates.length; i += 2) {
      // Draw the vertex.
      var vertex = [
        coordinates[i] - this.settings.point_range,
        coordinates[i + 1] - this.settings.point_range,
        coordinates[i] + this.settings.point_range,
        coordinates[i + 1] + this.settings.point_range
      ];
      control_points.vertexes.push(vertex);
      // Draw a point at the middle of the line.
      var mid_point = {
        x: (coordinates[i] + coordinates[(i + 2) % coordinates.length]) / 2,
        y: (coordinates[i + 1] + coordinates[(i + 3) % coordinates.length]) / 2
      };
      var mid = [
        mid_point.x - this.settings.point_range,
        mid_point.y - this.settings.point_range,
        mid_point.x + this.settings.point_range,
        mid_point.y + this.settings.point_range
      ];
      control_points.midpoints.push(mid);
    }
    return control_points;
  };

  /**
   * Get the control points for a rectangle.
   * They are at the vertexes of the rectangle,
   * each point can be used to resize the shape.
   *
   * @param {Array} coordinates
   * @returns {Object}
   */
  hotspot_shape.prototype.get_rectangle_controlpoints = function (coordinates) {
    var control_points = {
      vertexes: [],
      midpoints: []
    };
    // The variable names assume that the bounding box was drawn left to right, top to bottom.
    var top_left = [
      coordinates[0] - this.settings.point_range,
      coordinates[1] - this.settings.point_range,
      coordinates[0] + this.settings.point_range,
      coordinates[1] + this.settings.point_range
    ];
    var top_right = [
      coordinates[2] - this.settings.point_range,
      coordinates[1] - this.settings.point_range,
      coordinates[2] + this.settings.point_range,
      coordinates[1] + this.settings.point_range
    ];
    var bottom_right = [
      coordinates[2] - this.settings.point_range,
      coordinates[3] - this.settings.point_range,
      coordinates[2] + this.settings.point_range,
      coordinates[3] + this.settings.point_range
    ];
    var bottom_left = [
      coordinates[0] - this.settings.point_range,
      coordinates[3] - this.settings.point_range,
      coordinates[0] + this.settings.point_range,
      coordinates[3] + this.settings.point_range
    ];
    control_points.vertexes.push(top_left);
    control_points.vertexes.push(top_right);
    control_points.vertexes.push(bottom_right);
    control_points.vertexes.push(bottom_left);
    return control_points;
  };

  /**
   * Get the control points for an ellipse.
   * They are at the vertexes of the rectangle bounding box.
   *
   * @param {Array} coordinates
   * @returns {Object}
   */
  hotspot_shape.prototype.get_ellipse_controlpoints = function (coordinates) {
    return this.get_rectangle_controlpoints(coordinates);
  };

  /**
   * Tests if the start point of a polygon should be drawn.
   *
   * @returns {Boolean}
   */
  hotspot_shape.prototype.show_polygon_start_point = function () {
    // It must by a polygon.
    var test_result = this.shape === 'polygon';
    // Draw mode must be on.
    test_result = test_result && this.layer.question.in_draw_mode();
    // This must be the active shape.
    test_result = test_result && this.is_active();
    // In the active layer.
    test_result = test_result && this.in_active_layer();
    return test_result;
  };

  /**
   * Tests if control points should be drawn for the shape.
   *
   * @returns {Boolean}
   */
  hotspot_shape.prototype.show_control_points = function () {
    var test_results = this.layer.question.display_control_points();
    test_results = test_results && this.in_active_layer();
    test_results = test_results && this.is_active();
    return test_results;
  };

  /**
   * Test if a control point is at the coordinate. If a mid point is clicked,
   * then it sghould be converted into a vertex.
   *
   * Returns the index of a vertex or false if no control points were clicked.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Number|Boolean}
   */
  hotspot_shape.prototype.activate_control_point_at = function (x, y) {
    if (!this.show_control_points()) {
      // Control points are not dsiplayed so they cannot be clicked on.
      return false;
    }
    var control_point = this.control_point_contains(x, y),
        control_point_found = false;
    if (control_point.type === 'mid') {
      // A mid point was clicked on we need to insert a vertex in it's place.
      this.add_point(control_point.x, control_point.y, control_point.index + 1)
      control_point_found = control_point.index + 1;
    } else if (control_point.type === 'vertex') {
      // A vertex was clicked on, nothing needs to be done.
      control_point_found = control_point.index;
    }
    return control_point_found;
  };

  /**
   * Test if the shape is in the active layer.
   *
   * @returns {Boolean}
   */
  hotspot_shape.prototype.in_active_layer = function () {
    return this.layer.index === this.layer.question.get_active_layer();
  };

  /**
   * Test if this is the active shape in the layer.
   *
   * @returns {Boolean}
   */
  hotspot_shape.prototype.is_active = function () {
    return this.layer.active_shape === this.id;
  };

  /**
   * Test if this shape should be highlighted.
   *
   * @returns {Boolean}
   */
  hotspot_shape.prototype.is_highlighted = function () {
    var test_result = this.layer.question.in_highlight_mode();
    test_result = test_result && this.layer.question.highlight;
    if (test_result) {
      var highlight = this.layer.question.highlight;
      test_result = highlight.shape === this.id && highlight.layer === this.layer.index;
    }
    return test_result;
  };

  /**
   * Test if the a coordinate is contained by the shape.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Boolean}
   */
  hotspot_shape.prototype.contains = function (x, y) {
    // Start by assuming the shape has not been clicked on.
    var clicked_on = false;

    if (this['detect_' + this.shape]) {
      // Check if the shape itself has been clicked on.
      clicked_on = this['detect_' + this.shape].call(this, x, y);
    }

    if (!clicked_on && this.show_control_points() && this.control_point_contains(x, y) !== false) {
      // We did not click in the shape, but a visible control point outside the shape was clicked.
      clicked_on = true;
    }
    // No detection handler so assume it is not clicked on.
    return clicked_on;
  };

  /**
   * Test if a control point for the shape contains the coordinate passed.
   * It returns details necesary to identify the point, or false if the
   * coordinate is not in any of the control points.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {hotspot_shape.prototype.control_point_contains.selected|Boolean}
   */
  hotspot_shape.prototype.control_point_contains = function (x, y) {
    var selected = {
      // The index of the control point (This will equal the
      // index of the start point of the line it is for)
      index: null,
      // The type of control point.
      type: null,
      // The coordinates of the control point.
      x: null,
      y: null
    };
    var left, right, top, bottom;
    var control_points = this.get_control_points(this.getCoordinates());
    // Check the Vertexes.
    for (var vertex in control_points.vertexes) {
      left = control_points.vertexes[vertex][0];
      right = control_points.vertexes[vertex][2];
      top = control_points.vertexes[vertex][1];
      bottom = control_points.vertexes[vertex][3];
      if (x > left && x < right && y > top && y < bottom) {
        // The point is in a Vertex contol point.
        selected.index = parseInt(vertex);
        selected.type = 'vertex';
        selected.x = (left + right) / 2;
        selected.y = (top + bottom) / 2;
        return selected;
      }
    }
    // Check the mid points.
    for (var mid in control_points.midpoints) {
      left = control_points.midpoints[mid][0];
      right = control_points.midpoints[mid][2];
      top = control_points.midpoints[mid][1];
      bottom = control_points.midpoints[mid][3];
      if (x > left && x < right && y > top && y < bottom) {
        // The point is in a Mid contol point.
        selected.index = parseInt(mid);
        selected.type = 'mid';
        selected.x = (left + right) / 2;
        selected.y = (top + bottom) / 2;
        return selected;
      }
    }
    return false;
  };

  /**
   * Detects if the point is in a rectangle.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.detect_rectangle = function (x, y) {
    var coordinates = this.getCoordinates(),
        contains_x = false,
        contains_y = false;
    if (coordinates[0] < coordinates[2] && coordinates[0] <= x && coordinates[2] >= x) {
      // The rectangle was draw left to right.
      contains_x = true;
    } else if (coordinates[0] > coordinates[2] && coordinates[0] >= x && coordinates[2] <= x) {
      // The rectangle was drawn right to left.
      contains_x = true;
    }
    if (coordinates[1] < coordinates[3] && coordinates[1] <= y && coordinates[3] >= y) {
      // The rectangle was drawn top to bottom.
      contains_y = true;
    } else if (coordinates[1] > coordinates[3] && coordinates[1] >= y && coordinates[3] <= y) {
      // The rectangle was drawn bottom to top.
      contains_y = true;
    }
    return contains_x && contains_y;
  };

  /**
   * Detects if the point is in an ellipse.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.detect_ellipse = function (x, y) {
    var coordinates = this.getCoordinates(),
        centre_x = (coordinates[0] + coordinates[2]) / 2,
        centre_y = (coordinates[1] + coordinates[3]) / 2,
        x_radius = Math.abs(coordinates[0] - coordinates[2]) / 2,
        y_radius = Math.abs(coordinates[1] - coordinates[3]) / 2,
        // The equation for determining is a point is in an ellipse is:
        // ((x - centre_x)^2/(x_radius)^2) - ((y - centre_y)^2/(y_radius)^2) <= 1
        check_x = Math.pow(x - centre_x, 2) / Math.pow(x_radius, 2),
        check_y = Math.pow(y - centre_y, 2) / Math.pow(y_radius, 2),
        check = check_x + check_y;
    return (check <= 1);
  };

  /**
   * Detects if the point is within the polygon.
   *
   * It uses the winding number method:
   * http://geomalgorithms.com/a03-_inclusion.html
   *
   * Copyright 2000 softSurfer, 2012 Dan Sunday
   * This code may be freely used and modified for any purpose
   * providing that this copyright notice is included with it.
   * SoftSurfer makes no warranty for this code, and cannot be held
   * liable for any real or imagined damage resulting from its use.
   * Users of this code must verify correctness for their application.
   *
   * @param {Number} x
   * @param {Number} y
   * @returns {Boolean}
   * @private
   */
  hotspot_shape.prototype.detect_polygon = function (x, y) {
    var coordinates = this.getCoordinates(),
        edges = (coordinates.length / 2),
        winding_counter = 0;

    for (var edge = 0; edge < edges; edge++) {
      var start_point_x = coordinates[2 * edge],
          start_point_y = coordinates[(2 * edge) + 1],
          // The last coordinate will be the same as the first one, so we need to wrap round the coordinate array.
          index2 = (2 * (edge + 1)) % coordinates.length,
          end_point_x = coordinates[index2],
          end_point_y = coordinates[index2 + 1],
          // Tests if the point is to the left, right or on the edge (assuming it is infinite in length line)
          // when positive then it is on the left
          // when negative on the right
          // when 0 it intersects the line.
          intersect = ((start_point_x - x) * (end_point_y - y)) - ((end_point_x - x) * (start_point_y - y));
      if (start_point_y <= y) {
        if (end_point_y > y) {
          // An upwards crossing.
          if (intersect > 0) {
            // The point is to the left of the edge.
            winding_counter++;
          }
        }
      } else {
        if (end_point_y <= y) {
          // A downwards crossing.
          if (intersect < 0) {
            // The point is to the right of the edge.
            winding_counter--;
          }
        }
      }
    }
    // If the counter is equal to 0 the point is outside of the polygon.
    return (winding_counter !== 0);
  };

  /**
   * Changes the shape data into a string.
   *
   * @returns {String}
   */
  hotspot_shape.prototype.toString = function () {
    return [this.shape, this.coordinates, this.id].join('~');
  };

  return hotspot_shape;
});
