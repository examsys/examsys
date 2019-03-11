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

namespace plugins\questions\sct;

/**
 *
 * Class for SCT rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * SCT title
   * @var string
   */
  public $scttitle;

  /**
   * SCT hypothesis
   * @var string
   */
  public $scthyp;

  /**
   * SCT New information
   * @var string
   */
  public $sctinfo;

  /**
   * SCT title
   * @var string
   */
  public $scttitlelower;

  /**
   * Language pack component.
   */
  public $langcomponent = 'plugins/questions/sct/sct';

  /**
   * Land pack strings.
   * @var string
   */
  private $strings;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'sct';
    $langpack = new \langpack();
    $this->strings = $langpack->get_all_strings($this->langcomponent);
  }

  /**
   * Return the base mark for the question type
   * @return int
   */
  public function get_base_marks() {
    return 1;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    // Nothing to do.
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   */
  public function set_question($screen_pre_submitted, $useranswer, $userdismissed) {
    // SCT stores vignette in scenario so must display. 
    $this->displayscenario = true;
    if ($this->notes != '') {
      $this->displaynotes = true;
    }
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
    $sct_parts = explode('~',$this->leadin);
    $sct_titles = array(1 => $this->strings['hypothesis'],
        2 => $this->strings['investigation'],
        3 => $this->strings['prescription'],
        4 => $this->strings['intervention'],
        5 => $this->strings['treatment']);
    $this->scttitle = $sct_titles[$this->displaymethod];
    $this->scthyp = $sct_parts[0];
    $this->sctinfo = $sct_parts[1];
    $this->scttitlelower = sprintf($this->strings['scttitle'], mb_strtolower($sct_titles[$this->displaymethod], 'UTF-8'));

    if ($useranswer == '0' and $screen_pre_submitted == 1) {
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
    if (substr($userdismissed, $option['position']-1, 1) == '1') {
      $option['inact'] = true;
    } else {
      $option['inact'] = false;
    }
    $option['optiontextdisplay'] = false;
    if ($option['optiontext'] != '') {
      $option['optiontextdisplay'] = true;
    }
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
    // Write out the hidden field for the dismiss facility.
    if ($userdismissed != '') {
       $this->dismiss = $userdismissed;
    } else {
       $this->dismiss = str_repeat('0', $this->optionnumber);
    }
  }
}