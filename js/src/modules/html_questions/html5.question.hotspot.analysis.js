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
 * Base html5 hotspot question in analysis mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['log', 'hotspot', 'lang', 'html5_chk', 'answer_hotspot', 'jquery'], function(Log, Hotspot, Lang, Menu_checkbox, Answer_hotspot, $) {
  /**
   * Constructor for the hotspot question in analysis mode.
   * @returns {hotspot_analysis}
   */
  function hotspot_analysis() {
    // Extend the hotspot prototype.
    Hotspot.call(this);
    this.set_mode('analysis');
  }

  /**
   * Extend the hotspot prototype.
   * @type {Object}
   */
  hotspot_analysis.prototype = Object.create(Hotspot.prototype);

  /**
   * Builds the menu for the analysis mode questions.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.build_menu = function () {
    this.editmenu.add_item(new Menu_checkbox(this.menu_item_id_from_name('hotspots'), Lang.get_string('hotspots', 'html5')));
    this.editmenu.add_item(new Menu_checkbox(this.menu_item_id_from_name('correct'), Lang.get_string('correctanswers', 'html5')));
    this.editmenu.add_item(new Menu_checkbox(this.menu_item_id_from_name('incorrect'), Lang.get_string('incorrectanswers', 'html5')));
  };

  /**
   * Turn the display of all layers on.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.hotspots_on = function () {
    this.display_inactive = true;
    this.redraw();
  };

  /**
   * Turn the display of all layers off.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.hotspots_off = function () {
    this.display_inactive = false;
    this.redraw();
  };


  /**
   * Turn the display of correct answers on.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.correct_on = function () {
    this.display_correct = true;
    this.redraw();
  };

  /**
   * Turn the display of correct answers off.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.correct_off = function () {
    this.display_correct = false;
    this.redraw();
  };

  /**
   * Turn the display of incorrect answers on.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.incorrect_on = function () {
    this.display_incorrect = true;
    this.redraw();
  };

  /**
   * Turn the display of incorrect answers off.
   *
   * @returns {void}
   */
  hotspot_analysis.prototype.incorrect_off = function () {
    this.display_incorrect = false;
    this.redraw();
  };

  /**
   * Sets up the object for use.
   *
   * @param {HTMLElement} parent
   * @returns {void}
   */
  hotspot_analysis.prototype.setup = function (parent) {
    Hotspot.prototype.setup.call(this, parent);
    var question = $(parent);
    this.setup_answers(question.data('answers'));
    this.build_menu();
    parent.appendChild(this.editmenu.create(this.identifier));
    parent.appendChild(this.create_main());
  };

  /**
   * Attaches the user answers to layers.
   *
   * @param {String} answer_config
   * @returns {void}
   */
  hotspot_analysis.prototype.setup_answers = function (answer_config) {
    this.answer = answer_config;
    if (!answer_config || answer_config === 'u') {
      // The question is unanswered so we don't need to do more setup.
      return;
    }
    // Answers for each layer should be separated by a pipe character.
    // If any answer is set, then the number of answer parts should
    // equal the number of layers.
    var layer_answers = this.answer.split('|');
    if (layer_answers.length !== this.layers.length) {
      Log('Mismatch between the number of answers and layers.', 'warn');
    }
    for (var i in layer_answers) {
      if (this.layers[i]) {
        this.setup_layer_analysis(this.layers[i], layer_answers[i]);
      } else {
        // The answer does not have a coresponding layer.
        Log('Answer for layer that does not exist', 'warn');
      }
    }
  };

  /**
   * Attaches a set of user answers to a layer.
   *
   * @param {hotspot_layer} layer
   * @param {String} answers
   * @returns {void}
   */
  hotspot_analysis.prototype.setup_layer_analysis = function (layer, answers) {
    // Individual answers will be sepatated by a semi-colon.
    var individual_answers = answers.split(';');
    for (var i in individual_answers) {
      // Each answer should consit of if it is correct and an x and y coordinate separated by a comma.
      var answer = individual_answers[i].split(',');
      if (answer.length !== 3) {
        Log('Answer invalid (layer: ' + layer.index + ')', 'error');
      } else {
        layer.add_analysis_answer(new Answer_hotspot(answer[1], answer[2], answer[0]));
      }
    }
  };

  return hotspot_analysis;
});
