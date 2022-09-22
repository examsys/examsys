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
 * Base html5 hotspot question in answer mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(function() {
  /**
   * Stores a user answer.
   *
   * @param {Number} x The x co-ordinate of the answer
   * @param {Number} y The y-coordinate of the answer
   * @param {String|Boolean|null} correct Boolean is marked, or null if unmarked.
   * @returns {hotspot_answer}
   */
  function hotspot_answer(x, y, correct) {
    /**
     * The x coordinate of the answer
     * @type {Number}
     */
    this.x = parseInt(x);
    /**
     * The y coordinate of the answer
     * @type {Number}
     */
    this.y = parseInt(y);

    // Ensure that correct value is translaed into a boolean.
    if (correct === '1') {
      correct = true;
    } else if (correct === '0') {
      correct = false;
    }
    if (correct !== true && correct !== false) {
      correct = null;
    }
    /**
     * If the answer has been marked correct.
     *
     * @type {Boolean|null}
     */
    this.correct = correct;
  }

  /**
   * Convert answer to a string.
   * In the form of the x and y coordinates separated by a comma.
   *
   * @returns {String}
   */
  hotspot_answer.prototype.toString = function () {
    return [this.x, this.y].join(',');
  };

  return hotspot_answer;
});
