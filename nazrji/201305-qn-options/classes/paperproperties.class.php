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
 *  Class to load/save and manipulate paper properties
 *
 * @author Anthony Brown (re-factored from Ben Parishs code)
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class PaperProperties {

  private $db;
  private $configObject;

  private $property_id;
  private $paper_title;
  private $start_date;
  private $display_start_date;
  private $end_date;
  private $display_end_date;
  private $timezone;
  private $paper_type;
  private $paper_prologue;
  private $paper_postscript;
  private $bgcolor;
  private $fgcolor;
  private $themecolor;
  private $labelcolor;
  private $fullscreen;
  private $marking;
  private $bidirectional;
  private $pass_mark;
  private $distinction_mark;
  private $paper_ownerID;
  private $folder;
  private $labs;
  private $rubric;
  private $calculator;
  private $externals;
  private $exam_duration;
  private $deleted;
  private $created;
  private $random_mark;
  private $total_mark;
  private $display_correct_answer;
  private $display_question_mark;
  private $display_students_response;
  private $display_feedback;
  private $hide_if_unanswered;
  private $calendar_year;
  private $internal_reviewers;
  private $external_review_deadline;
  private $internal_review_deadline;
  private $sound_demo;
  private $latex_needed;
  private $password;
  private $retired;
  private $crypt_name;
  private $summative_lock;
  private $item_no;
  private $question_no;
  private $max_screen;
  private $max_display_pos;
  private $objective_fb_released;
  private $question_fb_released;

  private $_date_timezone = null;

  public function __construct($db) {
  	$this->db = $db;
    $this->configObject = Config::get_instance();
  }

  /*
  * static helper function to load the paper properties by property_id
  *	return @PaperProperties
  */
  static function get_paper_properties_by_id($p_id, $db) {
  	$paper_property = new PaperProperties($db);
  	$paper_property->set_property_id($p_id);
  	if ($paper_property->load() !== false) {
  		return $paper_property;
  	} else {
  		return false;
  	}
  }

  /*
  * static helper function to load the paper properties by crypt_name
  *	return @PaperProperties
  */
  static function get_paper_properties_by_crypt_name($crypt_name, $db) {
  	$paper_property = new PaperProperties($db);
  	$paper_property->set_crypt_name($crypt_name);
  	if ($paper_property->load() !== false) {
  		return $paper_property;
  	} else {
  		return false;
  	}
  }


  /*
  * static helper function to load the paper properties by lab id
  * used in the invigilator screens. previously called (get_invigilator_properties)
  *	return @array of PaperProperties
  */
  static function get_paper_properties_by_lab($lab_object, $db) {

    $sql = "SELECT
    			properties.property_id,
    			paper_title,
    			UNIX_TIMESTAMP(start_date) AS start_date,
          UNIX_TIMESTAMP(end_date) AS end_date,
    			exam_duration,
    			calendar_year,
    			password,
    			timezone
    		FROM
    			properties
    		WHERE
    			paper_type = '2' AND
    			labs LIKE ? AND
    			start_date < DATE_ADD( NOW(), interval 30 minute ) AND
    			end_date > NOW() AND
    			deleted IS NULL";

    $paper_results = $db->prepare($sql);
    $lab_like = '%' . $lab_object->get_id() . '%'; //TODO this is how the old code work !! concatenated field not sure if this always works if a room is on many labs
    $paper_results->bind_param('s', $lab_like);
    $paper_results->execute();
    $paper_results->store_result();
    $paper_results->bind_result($property_id, $paper_title, $start_date, $end_date, $exam_duration, $calendar_year, $password, $timezone);

    if ($paper_results->num_rows <= 0) {
      $paper_results->close();
      return false;
    }

    $properties = array();
    while ($paper_results->fetch()) {
      $property_object = new PaperProperties($db);
      $property_object->set_property_id($property_id);
      $property_object->set_paper_title($paper_title);
      $property_object->set_start_date($start_date);
      $property_object->set_end_date($end_date);
      $property_object->set_exam_duration($exam_duration);
      $property_object->set_calendar_year($calendar_year);
      $property_object->set_calendar_year($calendar_year);
      $property_object->set_timezone($timezone);
      $property_object->set_display_start_date();
      $property_object->set_display_end_date();
      $properties[] = $property_object;
    }

    $paper_results->close();
    return $properties;
  }

  public function load() {
    $property_id = $this->get_property_id();
    $crypt_name = $this->get_crypt_name();
    $sql = "SELECT
                  property_id,
                  paper_title,
                  start_date AS raw_start_date,
                  end_date AS raw_end_date,
                  UNIX_TIMESTAMP(start_date) AS start_date,
                  UNIX_TIMESTAMP(end_date) AS end_date,
                  timezone,
                  paper_type,
                  paper_prologue,
                  paper_postscript,
                  bgcolor,
                  fgcolor,
                  themecolor,
                  labelcolor,
                  fullscreen,
                  marking,
                  bidirectional,
                  pass_mark,
                  distinction_mark,
                  paper_ownerID,
                  folder,
                  labs,
                  rubric,
                  calculator,
                  exam_duration,
                  deleted,
                  UNIX_TIMESTAMP(created),
                  random_mark,
                  total_mark,
                  display_correct_answer,
                  display_question_mark,
                  display_students_response,
                  display_feedback,
                  hide_if_unanswered,
                  calendar_year,
                  UNIX_TIMESTAMP(external_review_deadline) AS external_review_deadline,
                  UNIX_TIMESTAMP(internal_review_deadline) AS internal_review_deadline,
                  sound_demo,
                  latex_needed,
                  password,
                  retired,
                  crypt_name
              FROM
                  properties";

    if (isset($property_id)) {
      $sql .= ' WHERE property_id = ?';
      $paper_results = $this->db->prepare($sql);
      $property_id = $this->get_property_id();
      $paper_results->bind_param('i', $property_id);
    } elseif (isset($crypt_name)) {
      $sql .= ' WHERE crypt_name = ?';
      $paper_results = $this->db->prepare($sql);
      $property_id = $this->get_property_id();
      $paper_results->bind_param('s', $crypt_name);
    } else {
      throw new Exception("property_id or crypt_name must be set to load the properties record from the DB.");
    }

    $paper_results->execute();
    $paper_results->store_result();
    if ($paper_results->num_rows == 0) {
      $paper_results->close();
      return false;
    }

    $paper_results->bind_result(  $this->property_id,
                                  $this->paper_title,
                                  $this->raw_start_date,
                                  $this->raw_end_date,
                                  $this->start_date,
                                  $this->end_date,
                                  $this->timezone,
                                  $this->paper_type,
                                  $this->paper_prologue,
                                  $this->paper_postscript,
                                  $this->bgcolor,
                                  $this->fgcolor,
                                  $this->themecolor,
                                  $this->labelcolor,
                                  $this->fullscreen,
                                  $this->marking,
                                  $this->bidirectional,
                                  $this->pass_mark,
                                  $this->distinction_mark,
                                  $this->paper_ownerID,
                                  $this->folder,
                                  $this->labs,
                                  $this->rubric,
                                  $this->calculator,
                                  $this->exam_duration,
                                  $this->deleted,
                                  $this->created,
                                  $this->random_mark,
                                  $this->total_mark,
                                  $this->display_correct_answer,
                                  $this->display_question_mark,
                                  $this->display_students_response,
                                  $this->display_feedback,
                                  $this->hide_if_unanswered,
                                  $this->calendar_year,
                                  $this->external_review_deadline,
                                  $this->internal_review_deadline,
                                  $this->sound_demo,
                                  $this->latex_needed,
                                  $this->password,
                                  $this->retired,
                                  $this->crypt_name
                                );
    $paper_results->fetch();
    $paper_results->close();

    $this->set_display_start_date();
    $this->set_display_end_date();
  }

  private function load_summative_lock() {
    if ($this->start_date !== null and date("U", time()) >= $this->start_date and $this->paper_type == '2') {
      $this->summative_lock = true;
    } else {
      $this->summative_lock = false;
    }
  }

  private function load_question_no() {
    $item_no = 0;
    $question_no = 0;
    $max_screen = 0;
    $max_display_pos = 0;

    $paper_results = $this->db->prepare("SELECT q_type, screen, display_pos FROM papers, questions WHERE papers.question = questions.q_id AND paper = ?");
    $property_id = $this->get_property_id();
    $paper_results->bind_param('i', $property_id);
    $paper_results->execute();
    $paper_results->bind_result($q_type, $screen, $display_pos);
    while ($paper_results->fetch()) {
      $item_no++;
      if ($q_type != 'info') $question_no++;
      if ($screen > $max_screen) $max_screen = $screen;
      if ($display_pos > $max_display_pos) $max_display_pos = $display_pos;
    }
    $paper_results->close();

    $this->item_no = $item_no;
    $this->question_no = $question_no;
    $this->max_screen = $max_screen;
    $this->max_display_pos = $max_display_pos;
  }

  private function load_changes() {
    $paper_results = $this->db->prepare("SELECT q_type, screen, display_pos FROM papers, questions WHERE papers.question = questions.q_id AND paper = ?");
    $property_id = $this->get_property_id();
    $paper_results->bind_param('i', $property_id);
    $paper_results->execute();
    $paper_results->bind_result($q_type, $screen, $display_pos);
    while ($paper_results->fetch()) {
      $item_no++;
      if ($q_type != 'info') $question_no++;
      if ($screen > $max_screen) $max_screen = $screen;
      if ($display_pos > $max_display_pos) $max_display_pos = $display_pos;
    }
    $paper_results->close();

    $this->item_no = $item_no;
    $this->question_no = $question_no;
    $this->max_screen = $max_screen;
    $this->max_display_pos = $max_display_pos;
  }
  
  private function load_externals() {
    $external_list = array();
  
    $result = $this->db->prepare("SELECT reviewerID FROM properties_reviewers WHERE paperID = ? AND type = 'external'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($reviewerID);
    while ($result->fetch()) {
      $external_list[] = $reviewerID;
    }
    $result->close();
    
    $this->externals = $external_list;
  }

  private function load_internals() {
    $internal_list = array();
  
    $result = $this->db->prepare("SELECT reviewerID FROM properties_reviewers WHERE paperID = ? AND type = 'internal'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($reviewerID);
    while ($result->fetch()) {
      $internal_list[] = $reviewerID;
    }
    $result->close();
    
    $this->internal_reviewers = $internal_list;
  }

  public function get_summative_lock() {
    if (!isset($this->summative_lock)) {
      $this->load_summative_lock();
    }

    return $this->summative_lock;
  }
  
  public function is_objective_fb_released() {
    if (!isset($this->objective_fb_released)) {
      $this->load_objective_fb_released();
    }

    return $this->objective_fb_released;
  }
  
  public function is_question_fb_released() {
    if (!isset($this->question_fb_released)) {
      $this->load_question_fb_released();
    }

    return $this->question_fb_released;
  }
  
  private function load_objective_fb_released() {
    $row_no = 0;
  
    $result = $this->db->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id = ? AND type = 'objectives'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($idfeedback_release);
    $result->store_result();
    $row_no = $result->num_rows;
    $result->close();
    
    $this->objective_fb_released = $row_no > 0;
  }
  
  private function load_question_fb_released() {
    $row_no = 0;
  
    $result = $this->db->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id = ? AND type = 'questions'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($idfeedback_release);
    $result->store_result();
    $row_no = $result->num_rows;
    $result->close();
    
    $this->question_fb_released = $row_no > 0;
  }

  public function get_item_no() {
    if (!isset($this->item_no)) {
      $this->load_question_no();
    }

    return $this->item_no;
  }

  public function get_question_no() {
    if (!isset($this->question_no)) {
      $this->load_question_no();
    }

    return $this->question_no;
  }

  public function get_max_screen() {
    if (!isset($this->max_screen)) {
      $this->load_question_no();
    }

    return $this->max_screen;
  }

  public function get_max_display_pos() {
    if (!isset($this->max_display_pos)) {
      $this->load_question_no();
    }

    return $this->max_display_pos;
  }

  /**
   * Set the default colour scheme for this paper and allow current users' special settings to override
   *
   * $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
   *
   */
  public function set_paper_colour_scheme($userObject, &$bgcolor, &$fgcolor, &$textsize, &$marks_color, &$themecolor, &$labelcolor, &$font, &$unanswered_color) {
    /*
    *  DEFAULT colour scheme
    */
    $bgcolor = $this->get_bgcolor();
    $fgcolor = $this->get_fgcolor();
    $textsize = 90;
    $marks_color = '#808080';
    $themecolor = $this->get_themecolor();
    $labelcolor = $this->get_labelcolor();
    $font = 'Arial';
    $unanswered_color = '#FFC0C0';

    // If set overwrite the default colours with the current users' special settings
    if ($userObject->is_special_needs()) {
      $bgcolor = $userObject->get_bgcolor($bgcolor);
      $fgcolor = $userObject->get_fgcolor($fgcolor);
      $textsize = $userObject->get_textsize($textsize);
      $marks_color = $userObject->get_marks_color($marks_color);
      $themecolor = $userObject->get_themecolor($themecolor);
      $labelcolor = $userObject->get_labelcolor($labelcolor);
      $font = $userObject->get_font($font);
      $unanswered_color = $userObject->get_unanswered_color($unanswered_color);
    }
  }

  /**
   * @return string $property_id
   */
  public function get_property_id() {
      return $this->property_id;
  }

  /**
   * @param string $property_id
   */
  public function set_property_id($property_id) {
      $this->property_id = $property_id;
  }

  /**
   * @return string $paper_title
   */
  public function get_paper_title() {
      return $this->paper_title;
  }

  /**
   * @param string $paper_title
   */
  public function set_paper_title($paper_title) {
      $this->paper_title = $paper_title;
  }

  /**
   * @return string $start_date
   */
  public function get_start_date() {
      return $this->start_date;
  }

  /**
   * @return string $start_date
   */
  public function get_raw_start_date() {
      return $this->raw_start_date;
  }

  /**
   * @param string $start_date
   */
  public function set_start_date($start_date) {
      $this->start_date = $start_date;
  }

  /**
   * @return string $display_start_date
   */
  public function get_display_start_date() {
    return $this->display_start_date;
  }

  /**
   * @param string $display_start_date
   */
  public function set_display_start_date($display_start_date = '') {
    if ($display_start_date == '') {
      // Summative papers may have no start date until scheduled
      if ($this->start_date != '') {
        $start_datetime = DateTime::createFromFormat('U', $this->start_date);
        $start_datetime->setTimezone($this->get_date_time_zone());
        $this->display_start_date = $start_datetime->format($this->configObject->get('cfg_long_date_php') . ' ' . $this->configObject->get('cfg_long_time_php'));
      }
    } else {
      $this->display_start_date = $display_start_date;
    }
  }

  /**
   * @return string $end_date
   */
  public function get_raw_end_date() {
      return $this->raw_end_date;
  }

  /**
   * @return string $end_date
   */
  public function get_end_date() {
      return $this->end_date;
  }

  /**
   * @param string $end_date
   */
  public function set_end_date($end_date) {
      $this->end_date = $end_date;
  }

  /**
   * @return string $end_date
   */
  public function get_display_end_date() {
      return $this->display_end_date;
  }

  /**
   * @param string $end_date
   */
  public function set_display_end_date($display_end_date = '') {
    if ($display_end_date == '') {
      // Summative papers may have no end date until scheduled
      if ($this->end_date != '') {
        $end_datetime = DateTime::createFromFormat('U', $this->end_date);
        $end_datetime->setTimezone($this->get_date_time_zone());
        $this->display_end_date = $end_datetime->format($this->configObject->get('cfg_long_date_php') . ' ' . $this->configObject->get('cfg_long_time_php'));
      }
    } else {
      $this->display_end_date = $display_end_date;
    }
  }

  /**
   * @return string $time_zone
   */
  public function get_timezone() {
      return $this->timezone;
  }

  /**
   * @param string $time_zone
   */
  public function set_timezone($timezone) {
      $this->timezone = $timezone;
  }

  /**
   * @return string $paper_type
   */
  public function get_paper_type() {
      return $this->paper_type;
  }

  /**
   * @param string $paper_type
   */
  public function set_paper_type($paper_type) {
      $this->paper_type = $paper_type;
  }

  /**
   * @return string $paper_prologue
   */
  public function get_paper_prologue() {
      return $this->paper_prologue;
  }

  /**
   * @param string $paper_prologue
   */
  public function set_paper_prologue($paper_prologue) {
      $this->paper_prologue = $paper_prologue;
  }

  /**
   * @return string $paper_postscript
   */
  public function get_paper_postscript() {
      return $this->paper_postscript;
  }

  /**
   * @param string $paper_postscript
   */
  public function set_paper_postscript($paper_postscript) {
      $this->paper_postscript = $paper_postscript;
  }

  /**
   * @return string $bgcolor
   */
  public function get_bgcolor() {
      return $this->bgcolor;
  }

  /**
   * @param string $bgcolor
   */
  public function set_bgcolor($bgcolor) {
      $this->bgcolor = $bgcolor;
  }

  /**
   * @return string $fgcolor
   */
  public function get_fgcolor() {
      return $this->fgcolor;
  }

  /**
   * @param string $fgcolor
   */
  public function set_fgcolor($fgcolor) {
      $this->fgcolor = $fgcolor;
  }

  /**
   * @return string $thememecolor
   */
  public function get_themecolor() {
      return $this->themecolor;
  }

  /**
   * @param string $themecolor
   */
  public function set_themecolor($stringmecolor) {
      $this->stringmecolor = $stringmecolor;
  }

  /**
   * @return string $labelcolor
   */
  public function get_labelcolor() {
      return $this->labelcolor;
  }

  /**
   * @param string $labelcolor
   */
  public function set_labelcolor($labelcolor) {
      $this->labelcolor = $labelcolor;
  }

  /**
   * @return string $fullscreen
   */
  public function get_fullscreen() {
      return $this->fullscreen;
  }

  /**
   * @param string $fullscreen
   */
  public function set_fullscreen($fullscreen) {
      $this->fullscreen = $fullscreen;
  }

  /**
   * @return string $marking
   */
  public function get_marking() {
      return $this->marking;
  }

  /**
   * @param string $marking
   */
  public function set_marking($marking) {
      $this->marking = $marking;
  }

  /**
   * @return string $bidirectional
   */
  public function get_bidirectional() {
      return $this->bidirectional;
  }

  /**
   * @param string $bidirectional
   */
  public function set_bidirectional($bidirectional) {
      $this->bidirectional = $bidirectional;
  }

  /**
   * @return int $pass_mark
   */
  public function get_pass_mark() {
      return $this->pass_mark;
  }

  /**
   * @param int $pass_mark
   */
  public function set_pass_mark($pass_mark) {
      $this->pass_mark = $pass_mark;
  }

  /**
   * @return int $distinction_mark
   */
  public function get_distinction_mark() {
      return $this->distinction_mark;
  }

  /**
   * @param int $distinction_mark
   */
  public function set_distinction_mark($distinction_mark) {
      $this->distinction_mark = $distinction_mark;
  }

  /**
   * @return int $paper_ownerid
   */
  public function get_paper_ownerid() {
      return $this->paper_ownerID;
  }

  /**
   * @param int $paper_ownerid
   */
  public function set_paper_ownerid($paper_ownerid) {
      $this->paper_ownerID = $paper_ownerid;
  }

  /**
   * @return string $folder
   */
  public function get_folder() {
      return $this->folder;
  }

  /**
   * @param string $folder
   */
  public function set_folder($folder) {
      $this->folder = $folder;
  }

  /**
   * @return string $labs
   */
  public function get_labs() {
      return $this->labs;
  }

  /**
   * @param string $labs
   */
  public function set_labs($labs) {
      $this->labs = $labs;
  }

  /**
   * @return string $rubric
   */
  public function get_rubric() {
      return $this->rubric;
  }

  /**
   * @param string $rubric
   */
  public function set_rubric($rubric) {
      $this->rubric = $rubric;
  }

  /**
   * @return int $calculator
   */
  public function get_calculator() {
      return $this->calculator;
  }

  /**
   * @param int $calculator
   */
  public function set_calculator($calculator) {
      $this->calculator = $calculator;
  }

  /**
   * @return string $externals
   */
  public function get_externals() {
    if (!isset($this->externals)) {
      $this->load_externals();
    }
    
    return $this->externals;
  }

  /**
   * @param string $externals
   */
  public function set_externals($externals) {
    $this->externals = $externals;
  }

  /**
   * @return int $exam_duration
   */
  public function get_exam_duration() {
    return $this->exam_duration;
  }

  /**
   * @return int $exam_duration in seconds
   */
  public function get_exam_duration_sec() {
    return $this->exam_duration * 60;
  }

  /**
   * @param int $exam_duration
   */
  public function set_exam_duration($exam_duration) {
    $this->exam_duration = $exam_duration;
  }

  /**
   * @return string $deleted
   */
  public function get_deleted() {
    return $this->deleted;
  }

  /**
   * @param string $deleted
   */
  public function set_deleted($deleted) {
    $this->deleted = $deleted;
  }

  /**
   * @return string $created
   */
  public function get_created() {
    return $this->created;
  }

  /**
   * @param string $created
   */
  public function set_created($created) {
    $this->created = $created;
  }

  /**
   * @return float $random_mark
   */
  public function get_random_mark() {
    return $this->random_mark;
  }

  /**
   * @param float $random_mark
   */
  public function set_random_mark($random_mark) {
    $this->random_mark = $random_mark;
  }

  /**
   * @return int $total_mark
   */
  public function get_total_mark() {
    return $this->total_mark;
  }

  /**
   * @param int $total_mark
   */
  public function set_total_mark($total_mark) {
    $this->total_mark = $total_mark;
  }

  /**
   * @return string $display_correct_answer
   */
  public function get_display_correct_answer() {
    return $this->display_correct_answer;
  }

  /**
   * @param string $display_correct_answer
   */
  public function set_display_correct_answer($display_correct_answer) {
    $this->display_correct_answer = $display_correct_answer;
  }

  /**
   * @return string $display_question_mark
   */
  public function get_display_question_mark() {
    return $this->display_question_mark;
  }

  /**
   * @param string $display_question_mark
   */
  public function set_display_question_mark($display_question_mark) {
    $this->display_question_mark = $display_question_mark;
  }

  /**
   * @return string $display_students_response
   */
  public function get_display_students_response() {
    return $this->display_students_response;
  }

  /**
   * @param string $display_students_response
   */
  public function set_display_students_response($display_students_response) {
    $this->display_students_response = $display_students_response;
  }

  /**
   * @return string $display_feedback
   */
  public function get_display_feedback() {
    return $this->display_feedback;
  }

  /**
   * @param string $display_feedback
   */
  public function set_display_feedback($display_feedback) {
    $this->display_feedback = $display_feedback;
  }

  /**
   * @return string $hide_if_unanswered
   */
  public function get_hide_if_unanswered() {
    return $this->hide_if_unanswered;
  }

  /**
   * @param string $hide_if_unanswered
   */
  public function set_hide_if_unanswered($hide_if_unanswered) {
    $this->hide_if_unanswered = $hide_if_unanswered;
  }

  /**
   * @return string $calendar_year
   */
  public function get_calendar_year() {
    return $this->calendar_year;
  }

  /**
   * @param string $calendar_year
   */
  public function set_calendar_year($calendar_year) {
    $this->calendar_year = $calendar_year;
  }

  /**
   * @return string $internal_reviewers
   */
  public function get_internal_reviewers() {
    if (!isset($this->internal_reviewers)) {
      $this->load_internals();
    }

    return $this->internal_reviewers;
  }

  /**
   * @param string $internal_reviewers
   */
  public function set_internal_reviewers($internal_reviewers) {
    $this->internal_reviewers = $internal_reviewers;
  }

  /**
   * @return string $external_review_deadline
   */
  public function get_external_review_deadline() {
    return $this->external_review_deadline;
  }

  /**
   * @param string $external_review_deadline
   */
  public function set_external_review_deadline($external_review_deadline) {
    $this->external_review_deadline = $external_review_deadline;
  }

  /**
   * @return string $internal_review_deadline
   */
  public function get_internal_review_deadline() {
    return $this->internal_review_deadline;
  }

  /**
   * @param string $internal_review_deadline
   */
  public function set_internal_review_deadline($internal_review_deadline) {
    $this->internal_review_deadline = $internal_review_deadline;
  }

  /**
   * @return string $sound_demo
   */
  public function get_sound_demo() {
    return $this->sound_demo;
  }

  /**
   * @param string $sound_demo
   */
  public function set_sound_demo($sound_demo) {
    $this->sound_demo = $sound_demo;
  }

  /**
   * @return int $latex_needed
   */
  public function get_latex_needed() {
    return $this->latex_needed;
  }

  /**
   * @param int $latex_needed
   */
  public function set_latex_needed($latex_needed) {
    $this->latex_needed = $latex_needed;
  }

  /**
   * @return string $password
   */
  public function get_password() {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function set_password($password) {
    $this->password = $password;
  }

  /**
   * @return string $retired
   */
  public function get_retired() {
    return $this->retired;
  }

  /**
   * @param string $retired
   */
  public function set_retired($retired) {
    $this->retired = $retired;
  }

  /**
   * @return string $crypt_name
   */
  public function get_crypt_name() {
    return $this->crypt_name;
  }

  /**
   * @param string $crypt_name
   */
  public function set_crypt_name($crypt_name) {
    $this->crypt_name = $crypt_name;
  }

  private function get_date_time_zone() {
    if ($this->_date_timezone === null) {
      $this->_date_timezone = new DateTimeZone($this->timezone);
    }
    return $this->_date_timezone;
  }
}