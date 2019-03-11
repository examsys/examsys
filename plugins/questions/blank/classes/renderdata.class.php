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

namespace plugins\questions\blank;

/**
 *
 * Class for fill in the blank rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \defaultgetmarks;
  /**
   * Blank options
   * @var array
   */
  public $blankoptions;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'blank';
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
    if ($this->qmedia != '') {
      $this->displaymedia = true;
    }
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
    $ans = '';
    $blank_mark = array();
    // Get possible answers.
    $option['optiontext'] = str_replace('&nbsp;',' ',$option['optiontext']);
    // Create an array of all blurbs.
    $blurbs = preg_split("/\[blank(\|)*(size=\"([0-9]{1,3})\"\|)*(mark=\"([0-9]{1,3})\"\|)*\](.*?)\[\/blank\]/", $option['optiontext']);
    // Create an array of all blanks.
    preg_match_all("/\[blank(\|)*(size=\"([0-9]{1,3})\"\|)*(mark=\"([0-9]{1,3})\"\|)*\](.*?)\[\/blank\]/", $option['optiontext'], $blankmatch);
    $blanks = $blankmatch[6];
    $sizes = $blankmatch[3];
    $marks = $blankmatch[5];
    // Decode user answer.
    if (!is_null($useranswer)) {
      $useranswer = str_replace('&nbsp;', ' ', $useranswer);
      $blank_user_answers = json_decode($useranswer);
    } else {
      $blank_user_answers = array();
    }
    // Create options, assign preivous user answers etc.
    $count = 0;
    $itemcount = 1;
    $j = 0;
    $blankoption = array();
    foreach ($blurbs as $blurb) {
      $count++;
      $blankoption[$count]['itemtype'] = 'blurb';
      $blankoption[$count]['itemvalue'] = $blurb;
      if (!empty($blanks[$j])) {
        $count++;
        $blankoption[$count]['itemtype'] = 'blank';
        $blankoption[$count]['itemcount'] = $itemcount;
        if ($this->displaymethod === 'textboxes') {
          // Resize textboxes to fit text.
          if (!empty($sizes[$j])) {
            $blank_size[$count] = $sizes[$j];
          } else {
            $blank_size[$count] = 15;
          }
          $blankoption[$count]['size'] = $blank_size[$count];
          // Set question as unanswered if not attempted.
          if ((isset($blank_user_answers[$itemcount - 1]) and $blank_user_answers[$itemcount - 1] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1)) {
            $this->unanswered = true;
            $blankoption[$count]['unans'] = true;
          } else {
            $blankoption[$count]['unans'] = false;
            if (isset($blank_user_answers[$itemcount - 1])) {
              $ans = $blank_user_answers[$itemcount - 1];
            }
            // Encoded user answer for display.
            $encoded_ans = htmlentities($ans, ENT_COMPAT | ENT_HTML5, \Config::get_instance()->get('cfg_page_charset'), false);
            $blankoption[$count]['encoded_ans'] = $encoded_ans;
          }
        // Drop Down display.
        } else {
          // Get list of possible answers.
          $answer_list = explode(',', $blanks[$j]);
          // Ensure that the correct answer is filtered in the same way as the user's answer.
          $answer_list = \param::clean_array($answer_list, \param::TEXT);
          // Shuffle the answers up.
          shuffle($answer_list);
          // If question previsouly answered auto select option.
          for ($i=0; $i<count($answer_list); $i++) {
            if (isset($answer_list[$i]) and isset($blank_user_answers[$itemcount - 1]) and html_entity_decode(trim($answer_list[$i])) == html_entity_decode(trim($blank_user_answers[$itemcount - 1]))) {
              $blankoption[$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => true);
            } else {
              $blankoption[$count]['itemvalue'][] = array('answer' => htmlentities(trim($answer_list[$i]), ENT_COMPAT, "UTF-8"), 'selected' => false);
            }
          }
          // Set question as unanswered if not attempted.
          if (isset($blank_user_answers[$itemcount - 1]) and $blank_user_answers[$itemcount - 1] == 'u' and $screen_pre_submitted == 1) {
            $blankoption[$count]['unans'] = true;
            $this->unanswered = true;
          } else {
            $blankoption[$count]['unans'] = false;
          }
        }
        // Mark per blank option.
        if (!empty($marks[$j])) {
          $blank_mark[$j] = $marks[$j];
        } else {
          $blank_mark[$j] = $option['markscorrect'];
        }
        $itemcount++;
      }
      $j++;
    }
    $this->blankoptions = $blankoption;

    // Calculate total marks.
    if ($this->scoremethod == 'Mark per Option') {
      if (count($blank_mark) > 0) {
        foreach ($blank_mark as $individual_mark) {
          $this->marks += $individual_mark;
        }
      }
    } else {
      $this->marks = $option['markscorrect'];
    }
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