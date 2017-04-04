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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class PaperProperties {

  private $db;
  private $configObject;

  private $property_id;
  private $paper_title;
  private $start_date;
  private $display_start_date;
  private $display_start_time;
  private $end_date;
  private $display_end_date;
  private $display_end_time;
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
  private $changes;
  private $recache_marks;
  private $modules;
  private $questions;
  private $unmarked_enhancedcalc;
  private $unmarked_student_enhancedcalc;
  private $externalid;
  private $externalsys;

  private $_date_timezone = null;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($db) {
  	$this->db = $db;
    $this->configObject = Config::get_instance();
  }


  function error_handling($context = null) {
    return error_handling($this);
  }


  /*
  * Load the paper properties by property_id
	* @param int $p_id						- The ID of the paper to load.
	* @param object $db						- Link to MySQL db.
	* @param array $string				- Language translations
	* @param bool $exit_on_false	- If true then exist if the paper does not exist.
  *	@return PaperProperties object
  */
  static function get_paper_properties_by_id($p_id, $db, $string, $exit_on_false = true) {
    $configObj = Config::get_instance();
    $notice = UserNotices::get_instance();

  	$paper_property = new PaperProperties($db);
  	$paper_property->set_property_id($p_id);
  	if ($paper_property->load() !== false) {
  		return $paper_property;
  	} else {
      if ($exit_on_false) {
        $msg = sprintf($string['furtherassistance'], $configObj->get('support_email'), $configObj->get('support_email'));
        $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
      } else {
        return false;
      }
    }
  }

  /*
  * Load the paper properties by its crypt_name.
	* @param string $crypt_name	- The crypt_name of the paper.
	* @param object $db					- Link to MySQL db.
	* @param array $string				- Language translations
	* @param bool $exit_on_false	- If true then exist if the paper does not exist.
  *	@return PaperProperties object
  */
  static function get_paper_properties_by_crypt_name($crypt_name, $db, $string, $exit_on_false = true) {
    $configObj = Config::get_instance();
    $notice = UserNotices::get_instance();

  	$paper_property = new PaperProperties($db);
  	$paper_property->set_crypt_name($crypt_name);
  	if ($paper_property->load() !== false) {
  		return $paper_property;
  	} else {
      if ($exit_on_false) {
        $msg = sprintf($string['furtherassistance'], $configObj->get('support_email'), $configObj->get('support_email'));
        $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
      } else {
        return false;
      }
  	}
  }


  /*
  * Load the paper properties by lab ID
  * used in the invigilator screens. previously called (get_invigilator_properties)
	* @param object $lab_object - Lab object.
	* @param object $db					- Link to MySQL db.
  *	@return array of PaperProperties
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
    			timezone,
          rubric
    		FROM
    			properties
    		WHERE
    			paper_type = '2' AND
    			labs REGEXP ? AND
    			start_date < DATE_ADD( NOW(), interval 30 minute ) AND
    			end_date > NOW() AND
    			deleted IS NULL";

    $paper_results = $db->prepare($sql);
    // TODO get_lab_based_on_client only fetches the first lab that populates $lab_object
    // If an ip address is on many labs we only use with the first we come across
    $lab_regexp = "(^|,)(" . $lab_object->get_id() . ")(,|$)";
    $paper_results->bind_param('s', $lab_regexp);
    $paper_results->execute();
    $paper_results->store_result();
    $paper_results->bind_result($property_id, $paper_title, $start_date, $end_date, $exam_duration, $calendar_year, $password, $timezone, $rubric);

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
      $property_object->password = $password;
      $property_object->set_timezone($timezone);
      $property_object->set_display_start_date();
      $property_object->set_display_start_time();
      $property_object->set_display_end_date();
      $property_object->set_display_end_time();
      $property_object->set_rubric($rubric);
      $properties[] = $property_object;
    }

    $paper_results->close();
    return $properties;
  }

  /*
  * Loads the properties of a paper into the paper property object.
  */
  public function load() {
    $property_id = $this->get_property_id();
    $crypt_name = $this->get_crypt_name();
    $sql = "SELECT
                  property_id,
                  paper_title,
                  DATE_FORMAT(start_date, '%Y%m%d%H%i%s'),
                  DATE_FORMAT(end_date, '%Y%m%d%H%i%s'),
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
                  DATE_FORMAT(external_review_deadline, '%Y-%m-%d'),
                  DATE_FORMAT(internal_review_deadline, '%Y-%m-%d'),
                  sound_demo,
                  latex_needed,
                  password,
                  retired,
                  crypt_name,
                  recache_marks,
                  externalid,
                  externalsys
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
                                  $this->crypt_name,
                                  $this->recache_marks,
                                  $this->externalid,
                                  $this->externalsys
                                );
    $paper_results->fetch();
    $paper_results->close();

    $this->set_display_start_date();
    $this->set_display_start_time();
    $this->set_display_end_date();
    $this->set_display_end_time();

    $this->changes = array();

    $this->load_summative_lock();
  }

  /*
  * Function to save the current properties back to the database.
  *	The fields that can be saved depends on whether the paper
  * is locked or not and the roles of the current user.
  */
  public function save() {
    $configObject = Config::get_instance();
    $userObject   = UserObject::get_instance();
    $gradebook = new gradebook($this->db);
    $graded = $gradebook->paper_graded($this->property_id);
    // Set common updates parameters.
    $params = array();
    $params['display_correct_answer'] = array('s', $this->display_correct_answer);
    $params['display_students_response'] = array('s', $this->display_students_response);
    $params['display_question_mark'] = array('s', $this->display_question_mark);
    $params['display_feedback'] = array('s', $this->display_feedback);
    $params['external_review_deadline'] = array('s', $this->external_review_deadline);
    $params['internal_review_deadline'] = array('s', $this->internal_review_deadline);
    $params['recache_marks'] = array('i', $this->recache_marks);
    
    // Set update parameters.
    if ($this->summative_lock and !$userObject->has_role('SysAdmin')) {  // For SysAdmin drop through to bottom if
      if (!$graded) {
        $params['marking'] = array('s', $this->marking);
        $params['pass_mark'] = array('i', $this->pass_mark);
        $params['distinction_mark'] = array('i', $this->distinction_mark);
      }
    } elseif ($configObject->get('cfg_summative_mgmt') and $this->paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
        // Non admin users cannot delete papers when summative management enabled so stop paper title change when non admin tries to delete a paper.
        if (is_null($this->get_deleted())) {
          $params['paper_title'] = array('s', $this->paper_title);
        }
        $params['paper_prologue'] = array('s', $this->paper_prologue);
        $params['paper_postscript'] = array('s', $this->paper_postscript);
        $params['bgcolor'] = array('s', $this->bgcolor);
        $params['fgcolor'] = array('s', $this->fgcolor);
        $params['themecolor'] = array('s', $this->themecolor);
        $params['labelcolor'] = array('s', $this->labelcolor);
        $params['fullscreen'] = array('s', $this->fullscreen);
        $params['marking'] = array('s', $this->marking);
        $params['bidirectional'] = array('s', $this->bidirectional);
        $params['pass_mark'] = array('i', $this->pass_mark);
        $params['distinction_mark'] = array('i', $this->distinction_mark);
        $params['folder'] = array('s',$this->folder);
        $params['rubric'] = array('s',$this->rubric);
        $params['calculator'] = array('i',$this->calculator);
        $params['hide_if_unanswered'] = array('s',$this->hide_if_unanswered);
        $params['sound_demo'] = array('s',$this->sound_demo);
        $params['password'] = array('s',$this->password);
    } else {
      $params['paper_title'] = array('s', $this->paper_title);
      $params['paper_type'] = array('s', $this->paper_type);
      $params['paper_prologue'] = array('s', $this->paper_prologue);
      $params['paper_postscript'] = array('s', $this->paper_postscript);
      $params['bgcolor'] = array('s', $this->bgcolor);
      $params['fgcolor'] = array('s', $this->fgcolor);
      $params['themecolor'] = array('s', $this->themecolor);
      $params['labelcolor'] = array('s', $this->labelcolor);
      $params['fullscreen'] = array('s', $this->fullscreen);
      $params['bidirectional'] = array('s', $this->bidirectional);
      $params['folder'] = array('s', $this->folder);
      $params['labs'] = array('s', $this->labs);
      $params['rubric'] = array('s', $this->rubric);
      $params['calculator'] = array('i', $this->calculator);
      $params['hide_if_unanswered'] = array('s', $this->hide_if_unanswered);
      $params['sound_demo'] = array('s', $this->sound_demo);
      $params['password'] = array('s', $this->password);
      $params['deleted'] = array('s', $this->deleted);
      if (!$graded) {
        $params['start_date'] = array('s', $this->raw_start_date);
        $params['end_date'] = array('s', $this->raw_end_date);
        $params['timezone'] = array('s', $this->timezone);
        $params['marking'] = array('s', $this->marking);
        $params['pass_mark'] = array('i', $this->pass_mark);
        $params['distinction_mark'] = array('i',  $this->distinction_mark);
        $params['exam_duration'] = array('i', $this->exam_duration);
        $params['calendar_year'] = array('i', $this->calendar_year);
        if ($userObject->has_role('SysAdmin')) {
          $params['externalid'] = array('s', $this->externalid);
          $params['externalsys'] = array('s', $this->externalsys);
        }
      }
    }

    // Udpate assessment properties.
    $assessment = new assessment($this->db, $configObject);
    $assessment->db_update_assessment($this->property_id, $params);

    // Record any changes
   	$logger = new Logger($this->db);

    foreach ($this->changes as $change) {
      $logger->track_change('Paper', $this->property_id, $userObject->get_user_ID(), $change['old'], $change['new'], $change['part']);
    }

  }

  /*
  * Returns true/false depending if the current date is between the start and end date/times.
	* @return bool - True = the paper dates are live, False = the paper is not live.
  */
  public function is_live() {
    if ($this->start_date !== null and date("U", time()) >= $this->start_date and $this->end_date !== null and date("U", time()) <= $this->end_date) {
      return true;
    } else {
      return false;
    }

  }

  /*
  * Returns true/false depending if the current paper is a) summative, and b) locked (e.g. paper start time is in the past).
	* @return bool - True = the paper is locked, False = the paper is not locked.
  */
  private function load_summative_lock() {
    if ($this->start_date !== null and date("U", time()) >= $this->start_date and $this->paper_type == '2') {
      $this->summative_lock = true;
    } else {
      $this->summative_lock = false;
    }
  }

  /*
  * Load how many questions there are on the current paper.
	* $item_no includes information blocks.
	* $question_no does not include information blocks
  */
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

  /*
  * Load the questions from the current paper into an array.
	*/
	private function load_questions() {
	  $q_no = 0;

    $paper_results = $this->db->prepare("SELECT q_id, q_type, screen FROM papers, questions WHERE papers.question = questions.q_id AND paper = ? ORDER BY screen, display_pos");
    $property_id = $this->get_property_id();
    $paper_results->bind_param('i', $property_id);
    $paper_results->execute();
    $paper_results->bind_result($q_id, $q_type, $screen);
    while ($paper_results->fetch()) {
		  if ($q_type != 'info') {
			  $q_no++;
			}
		  $this->questions[] = array('q_id'=>$q_id, 'q_no'=>$q_no, 'type'=>$q_type, 'screen'=>$screen);
		}
    $paper_results->close();
	}

  /*
  * Return the list of questions used on the paper.
	* @return - array of questions on the paper.
	*/
	public function get_questions() {
	  if (!isset($this->questions)) {
		  $this->load_questions();
		}

		return $this->questions;
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

    $result = $this->db->prepare("SELECT reviewerID, title, initials, surname FROM properties_reviewers, users WHERE properties_reviewers.reviewerID = users.id AND paperID = ? AND type = 'external'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($reviewerID, $title, $initials, $surname);
    while ($result->fetch()) {
      $external_list[$reviewerID] = "$title $initials $surname";
    }
    $result->close();

    $this->externals = $external_list;
  }

  private function load_internals() {
    $internal_list = array();

    $result = $this->db->prepare("SELECT reviewerID, title, initials, surname FROM properties_reviewers, users WHERE properties_reviewers.reviewerID = users.id AND paperID = ? AND type = 'internal'");
    $property_id = $this->get_property_id();
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($reviewerID, $title, $initials, $surname);
    while ($result->fetch()) {
      $internal_list[$reviewerID] = "$title $initials $surname";
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
  public function set_paper_colour_scheme($userObject, &$bgcolor, &$fgcolor, &$textsize, &$marks_color, &$themecolor, &$labelcolor, &$font, &$unanswered_color, &$dismiss_color) {
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
		$dismiss_color = '#A5A5A5';

    // If set overwrite the default colours with the current users' special settings
    if ($userObject->is_special_needs()) {
      $bgcolor					= $userObject->get_bgcolor($bgcolor);
      $fgcolor					= $userObject->get_fgcolor($fgcolor);
      $textsize					= $userObject->get_textsize($textsize);
      $marks_color			= $userObject->get_marks_color($marks_color);
      $themecolor				= $userObject->get_themecolor($themecolor);
      $labelcolor				= $userObject->get_labelcolor($labelcolor);
      $font							= $userObject->get_font($font);
      $unanswered_color = $userObject->get_unanswered_color($unanswered_color);
      $dismiss_color		= $userObject->get_dismiss_color($dismiss_color);
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
    if ($paper_title == '') {
      return false;
    }

    $old_paper_title = $this->paper_title;

    $this->paper_title = $paper_title;

    if ($old_paper_title != $paper_title) {
      $this->changes[] = array('old'=>$old_paper_title, 'new'=>$paper_title, 'part'=>'name');
    }
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

  public function set_raw_start_date($raw_start_date) {
    $this->raw_start_date = $raw_start_date;
  }

  /**
   * @param string $start_date
   */
  public function set_start_date($start_date) {
    $old_start_date = $this->start_date;

    $this->start_date = $start_date;

    if ($old_start_date != $start_date) {
      $this->changes[] = array('old'=>$old_start_date, 'new'=>$start_date, 'part'=>'startdate');
    }
  }

  /**
   * @return string $display_start_date
   */
  public function get_display_start_date() {
    return $this->display_start_date;
  }

  /**
   * @return string $display_start_time
   */
  public function get_display_start_time() {
    return $this->display_start_time;
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
   * @param string $display_start_date
   */
  public function set_display_start_time($display_start_time = '') {
    if ($display_start_time == '') {
      // Summative papers may have no start date until scheduled
      if ($this->start_date != '') {
        $start_datetime = DateTime::createFromFormat('U', $this->start_date);
        $start_datetime->setTimezone($this->get_date_time_zone());
        $this->display_start_time = $start_datetime->format($this->configObject->get('cfg_long_time_php'));
      }
    } else {
      $this->display_start_time = $display_start_time;
    }
  }

  /**
   * @return string $end_date
   */
  public function get_raw_end_date() {
    return $this->raw_end_date;
  }

  public function set_raw_end_date($raw_end_date) {
    $this->raw_end_date = $raw_end_date;
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
    $old_end_date = $this->end_date;

    $this->end_date = $end_date;

    if ($old_end_date != $end_date) {
      $this->changes[] = array('old'=>$old_end_date, 'new'=>$end_date, 'part'=>'enddate');
    }
  }

  /**
   * @return string $end_date
   */
  public function get_display_end_date() {
    return $this->display_end_date;
  }

  /**
   * @return string $end_date
   */
  public function get_display_end_time() {
    return $this->display_end_time;
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
   * @param string $end_date
   */
  public function set_display_end_time($display_end_time = '') {
    if ($display_end_time == '') {
      // Summative papers may have no end date until scheduled
      if ($this->end_date != '') {
        $end_datetime = DateTime::createFromFormat('U', $this->end_date);
        $end_datetime->setTimezone($this->get_date_time_zone());
        $this->display_end_time = $end_datetime->format($this->configObject->get('cfg_long_time_php'));
      }
    } else {
      $this->display_end_time = $display_end_time;
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
    $old_timezone = $this->timezone;

    $this->timezone = $timezone;

    if ($old_timezone != $timezone) {
      $this->changes[] = array('old'=>$old_timezone, 'new'=>$timezone, 'part'=>'timezone');
    }
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
    $old_paper_type = $this->paper_type;

    $this->paper_type = $paper_type;

    if ($old_paper_type != $paper_type) {
      $this->changes[] = array('old'=>$old_paper_type, 'new'=>$paper_type, 'part'=>'papertype');
    }
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
    $old_paper_prologue = $this->paper_prologue;

    $this->paper_prologue = $paper_prologue;

    if ($old_paper_prologue != $paper_prologue) {
      $this->changes[] = array('old'=>$old_paper_prologue, 'new'=>$paper_prologue, 'part'=>'prologue');
    }
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
    $old_paper_postscript = $this->paper_postscript;

    $this->paper_postscript = $paper_postscript;

    if ($old_paper_postscript != $paper_postscript) {
      $this->changes[] = array('old'=>$old_paper_postscript, 'new'=>$paper_postscript, 'part'=>'postscript');
    }
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
    $old_bgcolor = $this->bgcolor;

    $this->bgcolor = $bgcolor;

    if ($old_bgcolor != $bgcolor) {
      $this->changes[] = array('old'=>$old_bgcolor, 'new'=>$bgcolor, 'part'=>'background');
    }
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
    $old_fgcolor = $this->fgcolor;

    $this->fgcolor = $fgcolor;

    if ($old_fgcolor != $fgcolor) {
      $this->changes[] = array('old'=>$old_fgcolor, 'new'=>$fgcolor, 'part'=>'foreground');
    }
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
  public function set_themecolor($themecolor) {
    $old_themecolor = $this->themecolor;

    $this->themecolor = $themecolor;

    if ($old_themecolor != $themecolor) {
      $this->changes[] = array('old'=>$old_themecolor, 'new'=>$themecolor, 'part'=>'theme');
    }
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
    $old_labelcolor = $this->labelcolor;

    $this->labelcolor = $labelcolor;

    if ($old_labelcolor != $labelcolor) {
      $this->changes[] = array('old'=>$old_labelcolor, 'new'=>$labelcolor, 'part'=>'labelsnotes');
    }
  }

  /**
   * @return string $fullscreen
   */
  public function get_fullscreen() {
	  if ($this->fullscreen == '') {		// Fix old incorrect data.
			$this->fullscreen = '1';
		}
    return $this->fullscreen;
  }

  /**
   * @param string $fullscreen
   */
  public function set_fullscreen($fullscreen) {
    $old_fullscreen = $this->fullscreen;

    $this->fullscreen = $fullscreen;

    if ($old_fullscreen != $fullscreen) {
      $this->changes[] = array('old'=>$old_fullscreen, 'new'=>$fullscreen, 'part'=>'display');
    }
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
    $old_marking = $this->marking;

    $this->marking = $marking;

    if ($old_marking != $marking) {
      $this->changes[] = array('old'=>$old_marking, 'new'=>$marking, 'part'=>'marking');
    }
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
    $old_bidirectional = $this->bidirectional;

    $this->bidirectional = $bidirectional;

    if ($old_bidirectional != $bidirectional) {
      $this->changes[] = array('old'=>$old_bidirectional, 'new'=>$bidirectional, 'part'=>'navigation');
    }
  }

  /**
   * @return int $pass_mark
   */
  public function get_pass_mark() {
    return $this->pass_mark;
  }

  /**
   * Check if marking has started for the OSCE station.
   * @param int $paperID
   * @param stdClass $mysqli
   * @return boolean
   */
  public function get_osce_started_status($paperID, $mysqli) {
    if ($this->paper_type <> 4) {
      return false;
    }
    $result = $mysqli->prepare("SELECT 1 as count FROM (modules_student, users) JOIN log4_overall ON users.id = log4_overall.userID AND q_paper = ? WHERE modules_student.userID = users.id LIMIT 1");
    $result->bind_param('s', $paperID);
    $result->execute();
    $result->store_result();
    if ($result->num_rows == 1) {
      return true;
    }
    return false;
  }

  /**
   * @param int $pass_mark
   */
  public function set_pass_mark($pass_mark) {
    $old_pass_mark = $this->pass_mark;

    $this->pass_mark = $pass_mark;

    if ($old_pass_mark != $pass_mark) {
      $this->changes[] = array('old'=>$old_pass_mark, 'new'=>$pass_mark, 'part'=>'passmark');
    }
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
    $old_distinction_mark = $this->distinction_mark;

    $this->distinction_mark = $distinction_mark;

    if ($old_distinction_mark != $distinction_mark) {
      $this->changes[] = array('old'=>$old_distinction_mark, 'new'=>$distinction_mark, 'part'=>'distinction');
    }
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
    $old_folder = $this->folder;

    $this->folder = $folder;

    if ($old_folder != $folder) {
      $this->changes[] = array('old'=>$old_folder, 'new'=>$folder, 'part'=>'folder');
    }
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
    $old_labs = $this->labs;

    $this->labs = $labs;

    if ($old_labs != $labs) {
      $this->changes[] = array('old'=>$old_labs, 'new'=>$labs, 'part'=>'labs');
    }
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
    $old_rubric = $this->rubric;

    $this->rubric = $rubric;

    if ($old_rubric != $rubric) {
      $this->changes[] = array('old'=>$old_rubric, 'new'=>$rubric, 'part'=>'rubric');
    }
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
    $old_calculator = $this->calculator;

    $this->calculator = $calculator;

    if ($old_calculator != $calculator) {
      $this->changes[] = array('old'=>$old_calculator, 'new'=>$calculator, 'part'=>'displaycalculator');
    }
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
    if ($this->exam_duration == 0) {
		  return null;
		} else {
			return $this->exam_duration;
		}
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
    $old_exam_duration = $this->exam_duration;

    if ($exam_duration == 0) $exam_duration = null;
		$this->exam_duration = $exam_duration;

    if ($old_exam_duration != $exam_duration) {
      $this->changes[] = array('old'=>$old_exam_duration, 'new'=>$exam_duration, 'part'=>'duration');
    }
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
    $old_display_correct_answer = $this->display_correct_answer;

    $this->display_correct_answer = $display_correct_answer;

    if ($old_display_correct_answer != $display_correct_answer) {
      if ($this->get_paper_type() == '6') {
        $this->changes[] = array('old'=>$old_display_correct_answer, 'new'=>$display_correct_answer, 'part'=>'photos');
      } else {
        $this->changes[] = array('old'=>$old_display_correct_answer, 'new'=>$display_correct_answer, 'part'=>'correctanswerhighlight');
      }
    }
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
    $old_display_question_mark = $this->display_question_mark;

    $this->display_question_mark = $display_question_mark;

    if ($old_display_question_mark != $display_question_mark) {
      $this->changes[] = array('old'=>$old_display_question_mark, 'new'=>$display_question_mark, 'part'=>'review');
    }
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
    $old_display_students_response = $this->display_students_response;

    $this->display_students_response = $display_students_response;

    if ($old_display_students_response != $display_students_response) {
      $this->changes[] = array('old'=>$old_display_students_response, 'new'=>$display_students_response, 'part'=>'ticks_crosses');
    }
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
    $old_display_feedback = $this->display_feedback;

    $this->display_feedback = $display_feedback;

    if ($old_display_feedback != $old_display_feedback) {
      $this->changes[] = array('old'=>$old_display_feedback, 'new'=>$display_feedback, 'part'=>'textfeedback');
    }
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
    $old_hide_if_unanswered = $this->hide_if_unanswered;

    $this->hide_if_unanswered = $hide_if_unanswered;

    if ($old_hide_if_unanswered != $hide_if_unanswered) {
      $this->changes[] = array('old'=>$old_hide_if_unanswered, 'new'=>$hide_if_unanswered, 'part'=>'hideallfeedback');
    }
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
    $old_calendar_year = $this->calendar_year;

    $this->calendar_year = $calendar_year;

    if ($old_calendar_year != $calendar_year) {
      $this->changes[] = array('old'=>$old_calendar_year, 'new'=>$calendar_year, 'part'=>'session');
    }
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
    $old_external_review_deadline = $this->external_review_deadline;

    $this->external_review_deadline = $external_review_deadline;

    if ($old_external_review_deadline != $external_review_deadline) {
      $this->changes[] = array('old'=>$old_external_review_deadline, 'new'=>$external_review_deadline, 'part'=>'externalreviewdeadline');
    }
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
    $old_internal_review_deadline = $this->internal_review_deadline;

    $this->internal_review_deadline = $internal_review_deadline;

    if ($old_internal_review_deadline != $internal_review_deadline) {
      $this->changes[] = array('old'=>$old_internal_review_deadline, 'new'=>$internal_review_deadline, 'part'=>'internalreviewdeadline');
    }
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
    $old_sound_demo = $this->sound_demo;

    $this->sound_demo = $sound_demo;

    if ($old_sound_demo != $sound_demo) {
      $this->changes[] = array('old'=>$old_sound_demo, 'new'=>$sound_demo, 'part'=>'demosoundclip');
    }
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
   * Return the encrypted password for a paper.
   * 
   * @return string $password
   */
  public function get_password() {
    return $this->password;
  }
  
  /**
   * Return the password for a paper.
   * 
   * @return string $password
   */
  public function get_decrypted_password() {
    $paperID = $this->get_property_id();
    if ($this->password != '') {
        $password = $this->decrypt_password($this->password);
        // Strip of the paper id before returning the password.
        return preg_replace("/^$paperID/", '', $password);
    } else {
        return $this->password;
    }
  }
  
  /**
   * Save password to database.
   * 
   * @param string $password
   * @param bool $encypt if true we encypt the password
   */
  public function set_password($password) {
    $paperID = $this->get_property_id();
    $old_password = $this->get_decrypted_password();

    if ($password != '') {
        $this->password = $this->encrypt_password($paperID . $password);
    } else {
        $this->password = '';
    }
    
    if ($old_password != $password) {
      $this->changes[] = array('old'=>$old_password, 'new'=>$password, 'part'=>'password');
    }
  }
  
  /**
   * Encrypt a password that can be de-crypted.
   * 
   * @param $string $password 
   * @return $string encrypted passsword
   */
  public function encrypt_password($password) {
    $encryp = new encryp();
    return $encryp->mcrypt_password($password);
  }
  /**
   * Decrypt the password.
   * 
   * @param string $enc_password encrypted passsword
   * @return string decrypted passsword
   */
  public function decrypt_password($encpassword) {
    $encryp = new encryp();
    return $encryp->mdecrypt_password($encpassword);
  }

  /**
   * @param int recache_marks
   */
  public function get_recache_marks() {
    return $this->recache_marks;
  }
  /**
   * @param int recache_marks
   */
  public function set_recache_marks($recache_marks) {
    $this->recache_marks = $recache_marks;
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

  /**
   * @return string $externals
   */
  public function get_modules($force_recache = false) {
    if (!isset($this->modules) or $force_recache) {
      $this->load_modules();
    }

    return $this->modules;
  }

	private function load_modules() {
    $paperID = $this->get_property_id();
		$this->modules = array();

    $result = $this->db->prepare("SELECT idMod, moduleid FROM (modules, properties_modules) WHERE idMod = id AND property_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($idMod, $moduleid);
    $result->store_result();
    while ($result->fetch()) {
      $this->modules[$idMod] = $moduleid;
    }
    $result->close();
	}

  private function get_date_time_zone() {
    if ($this->_date_timezone === null) {
      $this->_date_timezone = new DateTimeZone($this->timezone);
    }
    return $this->_date_timezone;
  }

  /**
   * Check state of unmarked calculation questions
   * @param int $studentsonly only check students in cohort
   * @return bool are there unmarked questions?
   */
    public function unmarked_enhancedcalc($studentsonly = 0) {
        if ($studentsonly) {
            $check = $this->unmarked_student_enhancedcalc;
        } else {
            $check = $this->unmarked_enhancedcalc;
        }

        if ($check === null) {
            $this->load_unmarked_enhancedcalc($studentsonly);
        }

        if ($studentsonly) {
            return $this->unmarked_student_enhancedcalc;
        } else {
            return $this->unmarked_enhancedcalc;
        }
    }

    /**
     * Check if calculation answers have been marked
     * @param int $studentsonly only check students in cohort
     */
    private function load_unmarked_enhancedcalc($studentsonly = 0) {
        if ($studentsonly) {
            $this->unmarked_student_enhancedcalc = false;
        } else {
            $this->unmarked_enhancedcalc = false;
        }
        
        if (!isset($this->questions)) {
            $this->load_questions();
        }

        $enhancedcalc_ids = array();

        $paperID = $this->get_property_id();
        $paperType = $this->get_paper_type();
        $excluded = new Exclusion($paperID, $this->db);
        $excluded->load();

        if (is_array($this->questions) and count($this->questions) > 0) {
            // Calculation questions may be hidden in random blocks of keyword baed questions so we have to check all possibilities.
            foreach ($this->questions as $question) {
                // Skip excluded questions.
                if (!$excluded->is_question_excluded($question['q_id'])) {
                    switch ($question['type']) {
                        case 'random':
                            foreach (QuestionUtils::get_random_calc_question($question['q_id'], $this->db) as $possible) {
                                $enhancedcalc_ids[] = $possible;
                            }
                            break;
                        case 'keyword_based':
                            foreach (QuestionUtils::get_keyword_calc_question($question['q_id'], $this->db) as $possible) {
                                $enhancedcalc_ids[] = $possible;
                            }
                            break;
                        case 'enhancedcalc':
                            $enhancedcalc_ids[] = $question['q_id'];
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        // Find unmarked questions.
        if (count($enhancedcalc_ids) > 0) {

            // Some error states are fatal we should skip over these to avoid an infitie loop trying to mark them,
            // Affected questions will be flagged to the staff member marking.
            $skiperrorstates = array(-5);

            if ($studentsonly) {
                $rolesql = "AND (users.roles = 'Student' OR users.roles = 'graduate')";
            } else {
                $rolesql = '';
            }
            $result = $this->db->prepare("SELECT log$paperType.id FROM log$paperType, log_metadata, users WHERE log$paperType.metadataID = log_metadata.id "
              . "AND users.id = log_metadata.userID AND q_id IN (" . implode(',', $enhancedcalc_ids) . ") AND paperID = ? AND mark IS NULL and errorstate not in ("
              . implode(',', $skiperrorstates) . ") $rolesql LIMIT 1");
            $result->bind_param('i', $paperID);
            $result->execute();
            $result->store_result();
            $result->bind_result($id);
            if ($result->num_rows > 0) {
                if ($studentsonly) {
                    $this->unmarked_student_enhancedcalc = true;
                } else {
                    $this->unmarked_enhancedcalc = true;
                }
            }
            $result->close();
        }
    }

    public function q_type_exist($type) {
            $paperID = $this->get_property_id();

            $result = $this->db->prepare("SELECT COUNT(q_id) AS q_no FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type = ?");
            $result->bind_param('is', $paperID, $type);
            $result->execute();
            $result->bind_result($q_no);
            $result->fetch();
            $result->close();

            if ($q_no > 0) {
              return true;
            } else {
              return false;
            }
    }

    public function is_active() {
      if (date('U') > $this->start_date and date('U') < $this->end_date) {
              return true;
            } else {
              return false;
            }
    }

    /**
     * Get papers external id
     * @return string
     */
    public function get_externalid() {
        return $this->externalid;
    }
    
    /**
     * Set externalid id of paper
     * @param string $externalid
     */
    public function set_externalid($externalid) {
        $old_externalid = $this->externalid;
        if ($old_externalid != $externalid) {
          $this->externalid = $externalid;
          $this->changes[] = array('old' => $old_externalid, 'new' => $externalid, 'part' => 'externalid');
        }
    }
  
    /**
     * Get papers external system name
     * @return string
     */
    public function get_externalsys() {
        return $this->externalsys;
    }
    
    /**
     * Set externalid system name of paper
     * @param string $externalsys
     */
    public function set_externalsys($externalsys) {
        $old_externalsys = $this->externalsys;
        if ($old_externalsys != $externalsys) {
          $this->externalsys = $externalsys;
          $this->changes[] = array('old' => $old_externalsys, 'new' => $externalsys, 'part' => 'externalsys');
        }
    }
}
