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

namespace plugins\questions\mcq;

/**
 *
 * Class for MCQ rendering
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
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'mcq';
    $this->otherselected = false;
    $this->abstainselected = false;
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
    if ($useranswer == '0' and $screen_pre_submitted) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
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
    if ($option['position'] == $useranswer) {
      $option['selected'] = true;
    } else {
      $option['selected'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
    }
    $option['displayoptionmedia'] = false;
    if ($option['omedia'] != '') {
      $option['displayoptionmedia'] = true;
    }
    if ($this->displaymethod === 'vertical' or $this->displaymethod === 'vertical_other') {
      if (substr($userdismissed, $option['position']-1, 1) == '1') {
        $option['inact'] = true;
      } else {
        $option['inact'] = false;
      }
    }
    $this->set_opt($part_id, $option);
    $this->marks = $option['markscorrect'];
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
    if ($option['marksincorrect'] < 0) {
      $this->negativemarking = true;
      if ($useranswer === 'a') {
        $this->abstainselected = true;
      } else {
        $this->abstainselected = false;
      }
    } else {
      $this->negativemarking = false;
    }
    // other textbox not currently supported by non survey papers.
    if ($this->displaymethod === 'vertical_other') {
      if ($this->papertype == 3) {
        if (substr($useranswer,0,5) === 'other') {
          $this->otherselected = true;
          $this->other = substr($useranswer,6);
        }
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