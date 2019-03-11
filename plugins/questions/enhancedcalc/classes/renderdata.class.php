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

namespace plugins\questions\enhancedcalc;

/**
 *
 * Class for enhancedcalc rendering
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'enhancedcalc';
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->displaydefault = true;
    if ($this->notes != '') {
      $this->displaynotes = true;
    }
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   */
  public function set_question($screen_pre_submitted, $useranswer, $userdismissed) {
    $question = $this->question;
    if (!is_null($useranswer)) {
      $d = array();
      $d['useranswer'] = $useranswer;
      $question['object']->load($d);
    }
    $useranswers = $this->useranswers;
    $question['object']->load_all_user_answers($useranswers);
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    $marks = $this->marks;
    $question = $this->question;
    $useranswers = $this->useranswers;
    $question['object']->load_all_user_answers($useranswers);
    $marks += $question['object']->calculate_question_mark();
    $this->marks = $marks;
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    // Nothing to do.
  }

  /**
   * Render the question to screen
   * Enhanced calculation question override the generic method
   * @param object $render twig rendering object
   * @param array $string language strings
   * @return void
   */
  public function render_question($render, $string) {
    $question = $this->question;
    // no options for enhanced calc now stored in settings
    $extra = array(
      'num_on_screen' => $this->questionno,
      'current_question' => $question,
      'assignednumber' => $this->assignednumber,
      'mediaid' => $this->mediaid,
      'mediatype' => $this->mediatype,
      'mediawidth' => $this->mediawidth,
      'mediaheight' => $this->mediaheight,
      'mediaborder' => $this->mediaborder,
      'mediabordercolour' => $this->mediabordercolour,
      'mediaurl' => $this->mediaurl,
      'mediafile' => $this->mediafile
    );
    $question['object']->render_paper($extra);
  }
}