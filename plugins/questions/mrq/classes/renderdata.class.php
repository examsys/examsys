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

namespace plugins\questions\mrq;

/**
 *
 * Class for MRQ rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \mpqgetmarks;
  /**
   * Question 'other' option selected state
   * @var boolean
   */
  public $otherselected;

  /**
   * Question 'abstain' option selected state
   * @var boolean
   */
  public $abstainselected;

  /**
   * Question options dismissed
   * @var string
   */
  public $dismiss;

  /**
   * Number of allowed responses to the question
   * @var integer 
   */
  public $allowedresponses;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'mrq';
    $this->otherselected = false;
    $this->abstainselected = false;
  }

  /**
   * Return the allowed number of responses
   * @return int
   */
  private function get_allowed_responses() {
    $mrq_correct = 0;
    if ($this->scoremethod == 'Mark per Question') {
      $mrq_correct = $this->optionnumber;
    } else {
      $question = $this->question;
      for ($i = 0; $i < $this->optionnumber; $i++) {
        if ($question['options'][$i]['correct'] == 'y') {
          $mrq_correct++;
        }
      }
    }
    return $mrq_correct;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    if ($this->scenario != '') {
      $this->displayscenario = true;
    }
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
    $this->displaydefault = true;
    if ($this->notes != ''){
      $this->displaynotes = true;
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
    $allowed_responses = $this->get_allowed_responses();
    if (!is_null($useranswer)) {
      $answer_parts = explode(':', $useranswer);
      $len_answer = strlen($answer_parts[0]);
    } else {
      $len_answer = 0;
    }
    if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    $this->allowedresponses = $allowed_responses;
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
    if (substr($useranswer, $option['position']-1, 1) === 'y') {
      $option['selected'] = true;
    } else {
      $option['selected'] = false;
    }
    if (substr($userdismissed,$option['position']-1,1) === '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
    }
    $option['displayoptionmedia'] = false;
    if ($option['omedia'] != '') {
      $option['displayoptionmedia'] = true;
    }
    $marks = $this->marks;
    if ($this->scoremethod === 'Mark per Option') {
      if ($option['correct'] === 'y') {
        $marks += $option['markscorrect'];  // Mark for correct options only
      }
    } elseif ($this->scoremethod === 'Mark per Question') {
      if ($part_id == 1) {
        $marks += $option['markscorrect'];
      }
    } else {
      $marks += $option['markscorrect'];  // Mark for each and every item
    }
    $this->marks = $marks;
    $this->set_opt($part_id, $option);
  }

  /**
   * Additional option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    $option = $this->get_opt($part_id);
    if ($this->displaymethod === 'other') {
      $part_id = $this->partid + 1;
      $this->partid = $part_id ;
      if (!is_null($useranswer) and substr($useranswer,($part_id - 1),1) == 'y') {
        $this->otherselected = true;
      }
      $this->other = substr($useranswer, $part_id);
    }
    if ($option['marksincorrect'] < 0) {
      $this->negativemarking = true;
      if ($useranswer === 'a') {
        $this->abstainselected = true;
      }
    }
    // Write out the hidden field for the dismiss facility.
    if ($userdismissed != '') {
       $this->dismiss = $userdismissed;
    } else {
       $this->dismiss = str_repeat('0', $this->optionnumber);
    }
  }
}