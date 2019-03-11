<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* Question Render package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

/**
 * Question rendering helper class.
 */
class questionrender {

  /**
   * Config object
   * @var object
   */
  protected $config;

  /**
   * Question data class
   * @var class
   */
  public $questiondata;

  /**
   * Constructor
   * @param string $qtype question type
   */
  function __construct($qtype) {
    $this->config = Config::get_instance();
    $this->questiondata = questiondata::get_datastore($qtype);
  }

  /**
   * Render question
   * @global array $used_questions user log data for questions
   * @global array $user_dismiss user dismiss data for questions
   * @global array $user_order the order the user gets the question options
   * @global string $language system language
   * @param boolean $screen_pre_submitted has the user been on this screen before
   * @param integer $q_displayed loop id of question
   * @param string $string language strings
   * @param array $question question data
   * @param integer $pid paper id
   * @param integer $current_screen current screen id
   * @param integer $question_no current question number
   * @param array $user_answers users answers
   */
  public function display_question($screen_pre_submitted, $q_displayed, $string, &$question, $pid, $current_screen, &$question_no, $user_answers) {

    $texteditorplugin = \plugins\plugins_texteditor::get_editor();
    $renderpath = $texteditorplugin->get_render_paths();
    $strings = $texteditorplugin->get_strings();
    $string = array_merge($string, $strings);
    $renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'questions' . DIRECTORY_SEPARATOR . $question['q_type'] . DIRECTORY_SEPARATOR . 'templates';
    $renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
    $render = new render($this->config, $renderpath);

    $this->questiondata->setup_question_data($screen_pre_submitted, $q_displayed, $string, $question, $pid, $current_screen, $question_no, $user_answers);
    $render->render($this->questiondata, $string, 'paper/question_header.html');
    // Plugin question use there own templating for question body.
    $this->questiondata->render_question($render, $string);
  }

}