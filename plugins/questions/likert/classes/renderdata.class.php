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

namespace plugins\questions\likert;

/**
 *
 * Class for Likert rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * Na option state
   * @var integer 
   */
  public $displayna;

  /**
   * Note column span length
   * @var integer 
   */
  public $likertnotescolspan;

  /**
   * Scenario column span length
   * @var integer 
   */
  public $likertscenariocolspan;

  /**
   * List of scale labels.
   * @var array
   */
  public $scale;

  /**
   * List of scale options.
   * @var array
   */
  public $scaleopt;

  /**
   * Id of scale
   * @var integer
   */
  public $id;

  /**
   * Number of options in the scale.
   * @var integer
   */
  var $scale_size;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'likert';
    $this->displayna = false;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
    $this->displaydefault = false;
  }

  /**
   * Question level settings for template rendering
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   */
  public function set_question($screen_pre_submitted, $useranswer, $userdismissed) {
    $likert_display = explode('|',$this->displaymethod);
    $this->scale_size = substr_count($this->displaymethod,'|');
    if ($likert_display[$this->scale_size] == 'true') {
      $this->displayna = true;
    }
    if ($this->notes != '') {
      $this->displaylikertnotes = true;
      $this->likertnotescolspan = $this->scale_size + 1;
    } else {
      $this->displaylikertnotes = true;
      $this->displaylikertnotes = false;
    }
    if ($this->scenario != '') {
      $this->displaylikertscenario = true;
      $this->likertscenariocolspan = $this->scale_size + 2;
    } else {
      $this->displaylikertscenario = false;
    }
    $disp[0] = $likert_display[0];
    $temp_end = $this->scale_size - 1;
    for ($i=1; $i<=$temp_end; $i++) {
      $disp[$i] = $likert_display[$i];
    }
    $this->scale = $disp;
  }

  /**
   * Option level settings for template rendering
   * @param integer $part_id part loop id
   * @param mixed $useranswer user answer
   * @param string $userdismissed list of enable/disable flag for options the user has dismissed
   * @param boolean $screen_pre_submitted has the user submitted and answer previously
   */
  public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted) {
    if ($useranswer == 'u' and $screen_pre_submitted == 1) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    $this->id = $this->questionno . "_" . $part_id;
    $scale = array();
    if ($this->displayna) {
      // If n/a enabled set if selected.
      if ($useranswer == 'n/a') {
        $scale['n/a'] = true;
      } else {
        $scale['n/a'] = false;
      }
    }
    // Loop through scale and set if selected.
    for ($i = 1; $i <= $this->scale_size; $i++) {
      if ($i == $useranswer) {
        $scale[$i] = true;
      } else {
        $scale[$i] = false;
      }
    }
    $this->scaleopt = $scale;
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