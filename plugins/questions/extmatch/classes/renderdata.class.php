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

namespace plugins\questions\extmatch;

/**
 *
 * Class for extact match rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */

class renderdata extends \questiondata {
  use \mpqgetmarks;
  /**
   * Media state of extmatch question
   * @var boolean
   */
  public $extmatchdisplaymedia;

  /**
   * Matching scenarios
   * @var array
   */
  public $scenarios;

  /**
   * Matching media
   * @var array
   */
  public $media;

  /**
   * Matching media width
   * @var array
   */
  public $mediawidth;

  /**
   * Matching media height
   * @var array
   */
  public $mediaheight;

  /**
   * Matching user answers
   * @var array
   */
  public $usersanswers;

  /**
   * Matching options
   * @var array 
   */
  public $matchoptions;

  /**
   * Number of matching options
   * @var integer 
   */
  public $matchoptionsno;

  /**
   * Display split position
   * @var integer 
   */
  public $split;

  /**
   * Match stems
   * @var integer 
   */
  public $matchstem;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
    $this->questiontype = 'extmatch';
    $this->extmatchdisplaymedia = false;
  }

  /**
   * Disable/Enable display of question header sections for template rendering
   */
  public function set_question_head() {
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
    $this->scenarios = explode('|', $this->scenario);
    $this->media = explode('|', $this->qmedia);
    $this->mediawidth = explode('|', $this->qmediawidth);
    $this->mediaheight = explode('|', $this->qmediaheight);
    if (!is_null($useranswer)) {
      $this->usersanswers = explode('|', $useranswer);
    } else {
      $this->usersanswers = array();
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
    $matching_options = $this->matchoptions;
    $this->matchoptions[] = $option['optiontext'];
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
    $matching_answers = explode('|', $option['correct']);
    $matching_media = $this->media;
    $matching_media_width = $this->mediawidth;
    $matching_media_height = $this->mediaheight;
    $matching_scenarios = $this->scenarios;
    $matching_options = $this->matchoptions;
    $matching_users_answers = $this->usersanswers;
    $option_order = explode(',', $this->optionorder);
    if ($matching_media[0] != '') {
      $this->extmatchdisplaymedia =  true;
      $this->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
    }

    array_unshift($matching_scenarios, '');
    $max_scenarios = max(count($matching_scenarios), count($matching_media));
    $scenario_no = 0;
    for ($id = 1; $id < $max_scenarios; $id++) {
      if ((isset($matching_scenarios[$id]) and trim(strip_tags($matching_scenarios[$id],'<img>')) != '')
        or (isset($matching_media[$id]) and $matching_media[$id] != '')) {
        $scenario_no++;
      }
    }

    $col1_no = ceil(count($matching_options) / 2);
    $this->split = $col1_no-1;
    $this->matchoptionsno = count($matching_options);
    $matchstem = array();
    $marks = $this->marks;
    for ($id=1; $id<=$scenario_no; $id++) {
      if(isset($matching_answers[$id-1])) {
        $answer_no = substr_count($matching_answers[$id-1],'$') + 1;
        $marks += (substr_count($matching_answers[$id-1],'$') + 1) * $option['markscorrect'];
      } else {
        $answer_no = 0;
      }
      $matchstem[$id-1]['answerno'] = $answer_no;
      if (isset($matching_scenarios[$id]) and $matching_scenarios[$id] != '') {
        $matchstem[$id-1]['scenario'] = $matching_scenarios[$id];
      }
      $matchstem[$id-1]['display'] = false;
      if (isset($matching_media[$id]) and $matching_media[$id] != '') {
        $matchstem[$id-1]['display'] = true;
        $this->set_media($matching_media[$id], $matching_media_width[$id], $matching_media_height[$id], '', false, -1, false, $id);
        $mediaoption = $this->get_opt($id);
        $matchstem[$id-1]['media'] = $mediaoption['optionmedia'];
      }
      if(isset($matching_answers[$id-1])) {
        $sub_answers = explode('$', $matching_answers[$id - 1]);
      } else {
        $sub_answers = array();
      }
      $list_size = 10;
      if (count($matching_options) < 10) {
        $list_size = count($matching_options);
      }
      if ($answer_no == 1) {
        if (isset($matching_users_answers[$id - 1]) and $matching_users_answers[$id - 1] == 'u' and $screen_pre_submitted == 1) {
          $matchstem[$id-1]['unanswered'] = true;
          $this->unanswered = true;
        } else {
          $matchstem[$id-1]['unanswered'] = false;
        }
      } else {
        $matchstem[$id-1]['listsize'] = $list_size;
        $matchstem[$id-1]['subanswers'] = count($sub_answers);
        $matchstem[$id-1]['matchingoptions'] = count($matching_options);
        if (isset($matching_users_answers[$id - 1]) and $matching_users_answers[$id - 1] == '' and $screen_pre_submitted == 1) {
          $matchstem[$id-1]['unanswered'] = true;
          $this->unanswered = true;
        } else {
          $matchstem[$id-1]['unanswered'] = false;
        }
      }

      $multi_answers = array();
      if (isset($matching_users_answers[$id - 1])) {
        $multi_answers = explode('$', $matching_users_answers[$id - 1]);
      }

      $tmp_option_no = 0;
      for ($option_no=0; $option_no<count($matching_options); $option_no++) {
        $tmp_answer_match = false;
        foreach ($multi_answers as $separate_tmp_answer) {
          if ($separate_tmp_answer == $option_order[$tmp_option_no]+1) {
            $tmp_answer_match = true;
          }
        }
        if ($tmp_answer_match == true) {
          $matchstem[$id-1]['matchingoption'][$option_no]['selected'] = true;
        } else {
          $matchstem[$id-1]['matchingoption'][$option_no]['selected'] = false;
        }
        $matchstem[$id-1]['matchingoption'][$option_no]['value'] = $option_order[$option_no]+1;
        $matchstem[$id-1]['matchingoption'][$option_no]['option'] = chr($option_no+65) . '. ' . $matching_options[$option_no];
        $tmp_option_no++;
      }
    }
    $this->marks = $marks;
    $this->matchstem = $matchstem;
  }
}