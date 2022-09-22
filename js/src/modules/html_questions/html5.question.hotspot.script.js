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
 * Base html5 hotspot question in script mode.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['hotspot', 'hotspot_standardset', 'answer_hotspot', 'log', 'jquery'], function(Hotspot, Hotspot_standardset, Answer_hotspot, Log, $) {
  /**
   * Constructor
   *
   * @returns {hotspot_script}
   */
  function hotspot_script() {
    // Extend the standard setting hotspot prototype.
    Hotspot_standardset.call(this);
    this.set_mode('script');
    /**
     * The user's answer to the question.
     *
     * @type {String}
     * @private
     */
    this.answer;
  }

  /**
   * Extend the hotspot standard setting prototype.
   * @type Object
   */
  hotspot_script.prototype = Object.create(Hotspot_standardset.prototype);

  /**
   * Sets up the object for use.
   * @param {Element} parent
   * @returns {void}
   */
  hotspot_script.prototype.setup = function (parent) {
    Hotspot.prototype.setup.call(this, parent);
    this.build_menu();
    parent.appendChild(this.editmenu.create(this.identifier));
    var question = $(parent);
    this.setup_answers(question.data('answer'));
    this.setup_exclusions(question.data('exclusions'));
    parent.appendChild(this.create_main());
  };

  /**
   * Sets the answer the user has provided for each layer.
   *
   * @param {String} answer_config
   * @returns {void}
   */
  hotspot_script.prototype.setup_answers = function (answer_config) {
    this.answer = answer_config;
    if (!answer_config || answer_config === 'u') {
      // The question is unanswered so we don't need to do more setup.
      return;
    }
    // Answers for each layer should be separated by a pipe character.
    // If any answer is set, then the number of answer parts should
    // equal the number of layers.
    var answers = this.answer.split('|');
    if (answers.length !== this.layers.length) {
      Log('Mismatch between the number of answers and layers.', 'warn');
    }
    for (var i in answers) {
      // Each answer should consit of an x and y coordinate separated by a comma.
      var answer = answers[i].split(',');
      if (answer.length !== 3) {
        //
        Log('Answer invalid (index: ' + i + ')', 'error');
      } else if (this.layers[i]) {
        this.layers[i].set_answer(new Answer_hotspot(answer[1], answer[2], answer[0]));
      } else {
        // The answer does not have a coresponding layer.
        Log('Answer for layer that does not exist', 'warn');
      }
    }
  };

  /**
   * Create a layermenu item.
   *
   * @param {hotspot_layer} layer
   * @returns {HTMLElement}
   */
  hotspot_script.prototype.create_layermenu_item = function (layer) {
    var menu_item = Hotspot.prototype.create_layermenu_item.call(this, layer),
        main_area = $('.mainarea', menu_item);

    var answer = document.createElement('div');
    if (layer.user_answer && layer.user_answer.correct === true) {
      answer.className = 'answer correct';
    } else {
      answer.className = 'answer incorrect';
    }
    main_area.prepend(answer);

    return menu_item;
  };

  return hotspot_script;
});