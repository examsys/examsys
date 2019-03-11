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

namespace plugins\questions\area;

/**
 *
 * Class for area rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * Area user response
   * @var string
   */
  public $areauseranswer;

  /**
   * Area full user response
   * @var string
   */
  public $areafulluseranswer;

  /**
   * Area display value
   * @var string
   */
  public $areadisplay;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype =  'area';
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    $this->displaydefault = true;
    if ($this->notes != '') {
      $this->displaynotes = true;
    }
    if ($this->scenario != '') {
      $this->displayscenario = true;
    }
    $this->displayleadin = true;
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   */
  public function set_question($screen_pre_submitted, $useranswer, $userdismissed) {
    // Noting to do.
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    $default_ans  = '100,0,0,0,0,0';
    if (!is_null($useranswer)) {
      $tmp_user_answer = $useranswer;
    } else {
      $tmp_user_answer = $default_ans;
    }

    $answer_parts = explode(';', $tmp_user_answer);
    if (isset($answer_parts[1])) {
      $tmp_user_answer = substr($answer_parts[1], 0, -2);
      $full_user_ans = $useranswer;
    } else {
      $tmp_user_answer = '';
      $full_user_ans = $default_ans;
    }

    if ($tmp_user_answer == $default_ans and $screen_pre_submitted == 1) {
      $this->unanswered = true;
    }

    $this->areadisplay = $option['correct'];
    $this->areauseranswer =  $tmp_user_answer;
    $this->areafulluseranswer = $full_user_ans;
    $this->marks += $option['markscorrect'];;
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
}