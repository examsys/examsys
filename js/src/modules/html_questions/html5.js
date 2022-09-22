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
 * Init html5 question.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['log', 'html5images', 'html5listener', 'hotspot_answer', 'hotspot_analysis', 'hotspot_correction', 'hotspot_edit', 'hotspot_script', 'hotspot_standardset', 'jquery'], function(Log, Images, Listener, Hotspot_answer, Hotspot_analysis, Hotspot_correction, Hotspot_edit, Hotspot_script, Hotspot_standardset, $) {
  return function () {
    /**
     * An object containing an entry for each html5 question based objects on the page.
     *
     * @type {Object}
     */
    this.questions = {};

    /**
     * The total tab index of the item on the page.
     *
     * @type {Number}
     * @static
     */
    this.tabindex = 1;

    /**
     * Stores if html5 questions have been initialised already.
     *
     * @type {Boolean}
     */
    this.init_done = false;

    /**
     * The web root config setting for ExamSys.
     *
     * @type {String}
     */
    this.webroot = '';

    /**
     * Finds all html5 question placeholders and initialises them.
     *
     * @param {String} webroot The webroot config setting for ExamSys.
     * @returns {void}
     */
    this.init = function (webroot) {
      if (this.init_done === true) {
        return;
      }
      // Set the web root.
      if (typeof webroot !== 'string') {
        Log('webroot is not a string it is a ' + (typeof webroot), 'error');
        this.webroot = '/';
      } else {
        if (!webroot.endsWith('/')) {
          webroot += '/';
        }
        this.webroot = webroot;
      }
      // Load the combined image.
      Images.load(this.webroot, this.questions);
      // Initialise the questions.
      var scope = this;
      $('.html5').each(function() {
        scope.init_question(this);
      });
      // Add listeners, for question tyes on the page.
      var listen = new Listener();
      listen.init(this.question_types_on_page, this.questions);
      // Stop the initialisation from running more than one time.
      this.init_done = true;
    };

    /**
     * Stores which question types have been found on the page.
     *
     * @type {Object}
     */
    this.question_types_on_page = {
      hotspot: false
    };

    /**
     * Initialise an individual question.
     *
     * @param {Element} element
     * @returns {undefined}
     */
    this.init_question = function (element) {
      var question = $(element);
      var type = question.data('type');
      var mode = question.data('mode');
      var class_name = type + '_' + mode;
      var instance = this.question_from_string(class_name, element);
      if (instance !== null) {
        this.questions[element.id] = instance;
        Log('HTML5 Loaded question: ' + element.id + ' as a ' + class_name, 'info');
        // Store that this type of question has been found.
        this.question_types_on_page[type] = true;
      }
    };

    /**
     * Initialise the question in the correct mode, assuming it exists.
     *
     * @param {String} class_name
     * @param {element} element
     * @returns {Object}
     */
    this.question_from_string = function (class_name, element) {
      var question;
      switch (class_name) {
        case 'hotspot_answer':
          question = new Hotspot_answer;
          break;
        case 'hotspot_analysis':
          question = new Hotspot_analysis;
          break;
        case 'hotspot_correction':
          question = new Hotspot_correction(this.questions);
          break;
        case 'hotspot_edit':
          question = new Hotspot_edit(this.questions);
          break;
        case 'hotspot_script':
          question = new Hotspot_script;
          break;
        case 'hotspot_standardset':
          question = new Hotspot_standardset;
          break;
        default:
          question = null;
          break;
      }
      question.setup(element);
      return question;
    };
  }
});
