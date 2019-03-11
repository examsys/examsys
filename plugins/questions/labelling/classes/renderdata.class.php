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

namespace plugins\questions\labelling;

/**
 *
 * Class for labelling rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * User response
   * @var string
   */
  public $useranswer;

  /**
   * Temp correct answer
   * @var string
   */
  public $tmpcorrect;

  /**
   * Marks correct
   * @var float
   */
  public $markscorrect;

  /**
   * marks incorrect
   * @var flost
   */
  public $marksincorrect;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'labelling';
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
    $marks = $this->marks;
    $tmp_labels = 0;
    $max_col1 = 0;
    $max_col2 = 0;
    $tmp_first_split = explode(';', $option['correct']);
    $tmp_second_split = explode('|', $tmp_first_split[11]);
    $label_width = $tmp_first_split[5];
    $label_height = $tmp_first_split[6];
    $hyphen = false;
    foreach ($tmp_second_split as $ind_label) {
      $label_parts = explode('$', $ind_label);
      if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
        if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
        $tmp_labels++;
        if ($label_parts[2] > 219) $marks += $option['markscorrect'];
        if ($label_parts[0] < 10) {
          $max_col1 = $label_parts[0];
        } else {
          $max_col2 = $label_parts[0];
        }
      }
    }
    $max_col2-=10;
    $max_label = max($max_col1, $max_col2);

    if ($this->scoremethod == 'Mark per Question') {
      $marks = $option['markscorrect'];
    }
    if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
      $computed_height = round(($label_height + 6) * ceil($tmp_labels / 2)) + 10;
      $tmp_height = max($this->mediaheight, $computed_height);
    } else {                    // Single column
      $computed_height = round(($label_height + 6) * $tmp_labels) + 10;
      $tmp_height = max($this->mediaheight, $computed_height);
    }

    if ($useranswer == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
      $this->unanswered = true;
    } else {
      $this->unanswered = false;
    }
    $tmp_correct = trim($option['correct']);
    $tmp_correct = str_replace("'", "&#039;", $tmp_correct);

    $this->mediaheight = $tmp_height;
    $this->tmpcorrect = $tmp_correct;
    $this->marks = $marks;
    $this->useranswer = trim($useranswer);
    $this->markscorrect = $option['markscorrect'];
    $this->marksincorrect = $option['marksincorrect'];
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