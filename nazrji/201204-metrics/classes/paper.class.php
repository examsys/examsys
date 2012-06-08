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

/**
 *
 * Main class for core questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

class Paper {
  // Paper types
  const FORMATIVE = 0;
  const PROGRESS = 1;
  const SUMMATIVE = 2;
  const SURVEY = 3;
  const OSCE = 4;
  const OFFLINE = 5;
  const PEER = 6;

  private $id = -1;
  private $title;
  private $type;
  private $module_raw;
  private $duration;
  private $deleted;
  private $start_date;
  private $end_date;
  private $bidirectional;
  private $pass_mark;
  private $distinction_mark;
  private $owner_id;
  private $modules = array();
  private $session;
  private $internal_reviewers;
  private $external_reviewers;

  private $owner_fullname = '';
  private $owner_username = '';
  private $owner_email = '';

  private $_mysqli;
  private $_user_id;
  private $_lang_strings;

  private $q_labels = array(
    'calculation' => 'Calculation',
    'dichotomous' => 'Dichotomous',
    'extmatch' => 'Extended Matching',
    'blank' => 'Fill-in-the-Blank',
    'flash' => 'Flash Interface',
    'hotspot' => 'Image Hotspot',
    'info' => 'Information Block',
    'labelling' => 'Labelling',
    'likert' => 'Likert Scale',
    'matrix' => 'Matrix',
    'mcq' => 'Multiple Choice',
    'mrq' => 'Multiple Response',
    'rank' => 'Ranking',
    'sct' => 'Script Concordance',
    'textbox' => 'Text Box',
    'true_false' => 'True / False'
  );

  protected $_fields = array('id', 'title', 'start_date', 'end_date', 'type', 'bidirectional', 'pass_mark', 'distinction_mark', 'owner_id', 'duration', 'deleted', 'module_raw', 'session', 'internal_reviewers', 'external_reviewers');
  protected $_data = array();

  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    // Store the database connection reference and current user
    $this->_mysqli = $mysqli;
    $this->_user_id = $user_id;
    $this->_lang_strings = $lang_strings;

    // Array of references to the fields.  Allows succinct use of call_user_func_array for saving
    foreach($this->_fields as $field) {
      $this->_data[] = &$this->$field;
    }

    // TODO: error messages language specific
    // Check the type of $data
    if (is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif (ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      if (!$this->get_paper()) {
        throw new DatabaseException('Could not connect to database.');
      }
    } elseif ($data !== null) {
      throw new DataTypeException('Invalid data');
    }

    $this->set_friendly_type();
  }


  /**
   * Get the paper details from the database given an ID
   *
   * @return bool
   */
  private function get_paper() {
    $success = false;

    $p_query = <<< QUERY
SELECT property_id, paper_title, start_date, end_date, paper_type, bidirectional, pass_mark, distinction_mark, paper_ownerID, exam_duration, deleted, moduleID, calendar_year, internal_reviewers, externals
FROM properties
WHERE property_id = ?
QUERY;
    $result = $this->_mysqli->prepare($p_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    if ($result->fetch()) {
      $success = true;
      if ($result->num_rows == 0) {
        throw new RecordNotFoundException('Paper not found');
      }
    }
    $result->close();

    return ($success !== false);
  }

  /**
   * @return mixed
   */
  public function get_bidirectional() {
    return ($this->bidirectional == 1) ? 'Yes' : 'No';
  }

  /**
   * @return mixed
   */
  public function get_duration() {
    return $this->duration;
  }

  /**
   * @return mixed
   */
  public function get_deleted() {
    return $this->deleted;
  }

  /**
   * @return mixed
   */
  public function get_distinction_mark() {
    return $this->distinction_mark;
  }

  /**
   * @return mixed
   */
  public function get_end_date($format='') {
    $date = ($format == '') ? $this->end_date : date($format, strtotime($this->end_date));
    return $date;
  }

  /**
   * @return int|null
   */
  public function get_id() {
    return $this->id;
  }

  /**
   * @return array
   */
  public function get_modules() {
    if (count($this->modules) == 0 and $this->module_raw != '') {
      $this->modules = explode(',', $this->module_raw);
    }
    return $this->modules;
  }

  /**
   * @return mixed
   */
  public function get_owner_id() {
    return $this->owner_id;
  }

  /**
   * @return mixed
   */
  public function get_pass_mark() {
    return $this->pass_mark;
  }

  /**
   * @return mixed
   */
  public function get_start_date($format='') {
    $date = ($format == '') ? $this->start_date : date($format, strtotime($this->start_date));
    return $date;
  }

  /**
   * @return mixed
   */
  public function get_title() {
    return $this->title;
  }

  /**
   * @return mixed
   */
  public function get_type() {
    return $this->type;
  }

  /**
   * @return array
   */
  public function get_external_reviewers() {
    return ($this->external_reviewers == '') ? array() : explode(',', $this->external_reviewers);
  }

  /**
   * @return array
   */
  public function get_internal_reviewers() {
    return ($this->internal_reviewers == '') ? array() : explode(',', $this->internal_reviewers);
  }

  /**
   * Get the full details of the owner of the paper. Query database and cache result.
   * @return array
   */
  public function get_owner_details() {
    if ($this->owner_fullname == '') {
      $u_query = <<< QUERY
SELECT surname, initials, title, username, email
FROM users
WHERE id = ?
QUERY;
      $result = $this->_mysqli->prepare($u_query);
      $result->bind_param('i', $this->owner_id);
      $result->execute();
      $result->store_result();
      $result->bind_result($surname, $initials, $title, $username, $email);
      if ($result->fetch()) {
        $this->owner_fullname = $surname . ', ' . $initials . '. ' . $title;
        $this->owner_username = $username;
        $this->owner_email = $email;
      }
      $result->close();
    }
    return array('fullname' => $this->owner_fullname, 'username' => $this->owner_username, 'email' => $this->owner_email);
  }

  public function get_question_breakdown() {
    $questions = array('total' => 0, 'marks_total' => 0, 'screen' => array(), 'type' => array(), 'bloom' => array());

    $q_query = <<< QUERY
SELECT q.q_id, p.screen, q.q_type, q.bloom, count(o.id_num) as num_opts, max(o.option_text) as option_text, max(o.correct) as correct, group_concat(o.correct) as correct_all, max(o.marks_correct) as marks_correct, q.score_method, qe.parts AS exclusions
FROM papers p INNER JOIN questions q ON p.question=q.q_id
  LEFT JOIN options o ON q.q_id=o.o_id
  LEFT JOIN question_exclude qe ON qe.q_id=q.q_id AND qe.q_paper=p.paper
WHERE p.paper=?
GROUP BY o.o_id
ORDER BY p.screen;
QUERY;
    $result = $this->_mysqli->prepare($q_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $screen, $qtype, $bloom, $num_opts, $option_text, $correct, $correct_all, $marks_correct, $score_method, $exclusions);
    while ($result->fetch()) {
      $questions['total']++;
      $questions['marks'][$screen] = (isset($questions['marks'])) ? $questions['marks'] : 0;

      $questions['screen'][$screen] = (isset($questions['screen'][$screen])) ? $questions['screen'][$screen] + 1 : 1;
      $questions['screen_marks'][$screen] = (isset($questions['screen_marks'][$screen])) ? $questions['screen_marks'][$screen] : 0;

      $friendlytype = $this->q_labels[$qtype];
      $questions['type'][$friendlytype] = (isset($questions['type'][$friendlytype])) ? $questions['type'][$friendlytype] + 1 : 1;

      $marks = 0;
      $excluded_parts = substr_count($exclusions, '1');
      if ($score_method == 'Mark per Question') {
        if ($excluded_parts == 0) {
          $marks= $marks_correct;
        }
      } else {
        $add_mark = true;
        switch ($qtype) {
          case 'blank':
            $marks = (substr_count(strtolower($option_text), '[/blank]') - $excluded_parts) * $marks_correct;
            break;
          case 'calculation':
          case 'flash':
          case 'mcq':
          case 'sct':
          case 'textbox':
            if ($excluded_parts == 0) {
              $marks = $marks_correct;
            }
            break;
          case 'dichotomous':
          case 'true_false':
            $marks = ($num_opts - $excluded_parts) * $marks_correct;
            break;
          case 'hotspot':
            $marks = (substr_count(strtolower($correct), '|') + 1 - $excluded_parts) * $marks_correct;
            break;
          case 'labelling':
            $labels = 0;
            $tmp_first_split = explode(';', $correct);
            $tmp_second_split = explode('$', $tmp_first_split[11]);
            for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
              if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
                $labels++;
              }
            }
            $marks = ($labels - $excluded_parts) * $marks_correct;
            break;
          case 'likert':
          case 'info':
          case 'random':
          case 'keyword_based':
            $add_mark = false;
            break;
          case 'extmatch':
          case 'matrix':
            $matches = array();
            $marks = (preg_match_all('/\d{1,2}($\d{1,2})*/', $correct, $matches) - $excluded_parts) * $marks_correct;
            break;
          case 'mrq':
            $marks = (substr_count(strtolower($correct_all), 'y') - $excluded_parts) * $marks_correct;
            break;
          case 'rank':
            if ($excluded_parts == 0) {
              switch ($score_method) {
                case 'Mark per Option':
                  $marks = $num_opts * $marks_correct;
                  break;
                case 'Allow partial Marks':
                  $marks = preg_match_all('/[1-9]\d{0,1}/', $correct_all, $matches) * $marks_correct;
                  break;
                case 'Bonus Mark':
                  $marks = preg_match_all('/[1-9]\d{0,1}/', $correct_all, $matches) * $marks_correct + $marks_correct;
                  break;
              }
            }
            break;
        }
      }

      $questions['screen_marks'][$screen] += $marks;
      if ($add_mark) {
        $questions['marks_total'] += $marks;
        $questions['type_marks'][$friendlytype] = (isset($questions['type_marks'][$friendlytype])) ? $questions['type_marks'][$friendlytype] : 0;
        $questions['type_marks'][$friendlytype] += $marks;
      }

      if ($bloom != '') {
        $questions['bloom'][$bloom] = (isset($questions['bloom'][$bloom])) ? $questions['bloom'][$bloom] + 1 : 1;
        $questions['bloom_marks'][$bloom] = (isset($questions['bloom_marks'][$bloom])) ? $questions['bloom_marks'][$bloom] + $marks : $marks;
      }
    }
    $result->close();

    return $questions;
  }

  /**
   * Check if the paper title contains a date. If so check that this matches the Session for the paper
   * @return bool
   */
  public function title_matches_session() {
    $year_in_title = false;
    $tmp_match = '';
    $title = $this->get_title();
    if (preg_match( '/\d\d\d\d[\/-]\d\d\d\d/', $title, $matches) == 1) {
      $year_in_title = true;
      $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
    } elseif (preg_match( '/\d\d\d\d[\/-]\d\d/' , $title , $matches) == 1) {
      $year_in_title = true;
      $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
    } elseif (preg_match( '/\d\d[\/-]\d\d/' , $title , $matches) == 1) {
      $year_in_title = true;
      $tmp_match = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
    }

    return !($year_in_title == true and $tmp_match != $this->session);
  }

  /**
   * Get the number of completed reviews for the paper
   * @param $type 'internal' or 'external'
   * @return array containing two values 'complete' and 'total'
   */
  public function get_review_count($type) {
    $reviewers = ($type == 'internal') ? $this->internal_reviewers : $this->external_reviewers;
    $reviewer_count = substr_count($reviewers, ',') + 1;

    $reviews_complete = array();
    if (count($reviewers) > 0) {
      $result = $this->_mysqli->prepare("SELECT count(id), reviewer FROM review_comments WHERE q_paper=? AND review_type=? AND reviewer IN ({$reviewers}) GROUP BY reviewer, reviewed");
      $result->bind_param('is', $this->id, $type);
      $result->execute();
      $result->bind_result($review_records, $reviewer);
      while ($result->fetch()) {
        if (!in_array($reviewer, $reviews_complete)) {
          $reviews_complete[] = $reviewer;
        }
      }
      $result->close();
    }

    return array('complete' => count($reviews_complete), 'total' => $reviewer_count);
  }
  
  public function get_std_set_status($qn_count) {
    $standards_set = 0;

    $result = $this->_mysqli->prepare("SELECT COUNT(id), setterID FROM standards_setting WHERE paperID=? GROUP BY setterID");
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->bind_result($set_set_records, $setterID);
    while ($row = $result->fetch()) {
      if ($set_set_records >= $qn_count and $standards_set == 0) {
        $standards_set = 1;
      } elseif ($set_set_records < $qn_count and ($standards_set == 0 or $standards_set == 1)) {
        $standards_set = 0.5;
      }
    }
    $result->close();

    return $standards_set;
  } 


  private function set_friendly_type() {
    switch ($this->type) {
      case self::FORMATIVE:
        $this->type = 'Formative Self-Assessment';
        break;
      case self::PROGRESS:
        $this->type = 'Progress Test';
        break;
      case self::SUMMATIVE:
        $this->type = 'Summative Exam';
        break;
      case self::SURVEY:
        $this->type = 'Survey';
        break;
      case self::OSCE:
        $this->type = 'OSCE Station';
        break;
      case self::OFFLINE:
        $this->type = 'Offline Paper';
        break;
      case self::PEER:
        $this->type = 'Peer Review';
        break;
    }
  }
}