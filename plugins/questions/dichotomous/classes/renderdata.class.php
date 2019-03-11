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

namespace plugins\questions\dichotomous;

/**
 *
 * Class for dichotomous rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \mpqgetmarks;
  /**
   * Display true/false options if enabled. Y/N by default.
   * @var boolean 
   */
  var $displayTF;
  /**
   * Display Abstain options if enabled.
   * @var boolean 
   */
  var $displayAbstain;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'dichotomous';
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
    // Nothing to do.
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
    $option['useranswer'] = substr($useranswer, $option['position']-1, 1);
    if ($option['useranswer'] == 'u' and $screen_pre_submitted == 1) {
      $this->unanswered = true;
      $option['unanswered'] = true;
    }
    $option['displayoptionmedia'] = false;
    if ($option['omedia'] != '') {
      $option['displayoptionmedia'] = true;
    }
    $option['abstain'] = false;
    if ($this->displaymethod === 'TF_NegativeAbstain' or $this->displaymethod === 'YN_NegativeAbstain') {
        $option['abstain'] = true;
        $this->displayAbstain = true;
    } else {
      $this->displayAbstain = false;
    }
    if ($this->displaymethod === 'TF_Positive' or $this->displaymethod === 'TF_NegativeAbstain') {
      $this->displayTF = true;
    } else {
      $this->displayTF = false;
    }
    $marks = $this->marks;
    $marks += $option['markscorrect'];
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
    if ($option['marksincorrect'] < 0) {
      $this->negativemarking = true;
    } else {
      $this->negativemarking = false;
    }
  }
}