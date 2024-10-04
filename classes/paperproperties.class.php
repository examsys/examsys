<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Class to load/save and manipulate paper properties
 *
 * @author Anthony Brown (re-factored from Ben Parishs code)
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class PaperProperties
{
    /** @var mysqli The ExamSys database connection. */
    private $db;

    /** @var null @var ConfigObject The ExamSys configuration. */
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
    /** @var int The reference to the year that the paper is on. */
    private $calendar_year;
    private $internal_reviewers;
    private $external_review_deadline;
    private $internal_review_deadline;
    private $sound_demo;
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
    private $unmarked_textbox;
    private $unmarked_student_textbox;
    private $enhancedcalc_questions;
    private $_date_timezone = null;

    /** @var bool Stores if the paper is graded. */
    protected $graded;

    /**
     * A static cache of LogLabEndTime objects indexed by the id of the lab.
     *
     * We use this to reduce database calls when using them in different places in code.
     *
     * @var array
     */
    protected $lab_end_cache = [];

    /**
     * @var PaperSettings paper settings
     */
    private $papersettings;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;

        // We should not keep any cached objects.
        $this->lab_end_cache = [];
    }

    public function __construct($db)
    {
        $this->db = $db;
        $this->configObject = Config::get_instance();
    }


    public function error_handling($context = null)
    {
        return error_handling($this);
    }

    /*
    * Load the paper properties by property_id
    * @param int $p_id                      - The ID of the paper to load.
    * @param object $db                     - Link to MySQL db.
    * @param array $string              - Language translations
    * @param bool $exit_on_false    - If true then exist if the paper does not exist.
    * @return PaperProperties|bool object
    */
    public static function get_paper_properties_by_id($p_id, $db, $string, $exit_on_false = true)
    {
        $configObj = Config::get_instance();
        $notice = UserNotices::get_instance();
        $contactemail = support::get_email();

        $paper_property = new PaperProperties($db);
        $paper_property->set_property_id($p_id);
        if ($paper_property->load() !== false) {
            return $paper_property;
        } else if ($exit_on_false) {
            $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
            $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
        }
        return false;
    }

    /*
    * Load the paper properties by its crypt_name.
    * @param string $crypt_name - The crypt_name of the paper.
    * @param object $db                 - Link to MySQL db.
    * @param array $string              - Language translations
    * @param bool $exit_on_false    - If true then exist if the paper does not exist.
    * @return PaperProperties|bool object
    */
    public static function get_paper_properties_by_crypt_name($crypt_name, $db, $string, $exit_on_false = true)
    {
        $configObj = Config::get_instance();
        $notice = UserNotices::get_instance();
        $contactemail = support::get_email();

        $paper_property = new PaperProperties($db);
        $paper_property->set_crypt_name($crypt_name);
        if ($paper_property->load() !== false) {
            return $paper_property;
        } else if ($exit_on_false) {
            $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
            $notice->display_notice_and_exit($db, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
        }
        return false;
    }

    /**
     * Get summative paper propeties helper function
     * @param mysqli $db database object
     * @param boolean $remote flag to indicate if remote summatives being queried
     * @param mixed $lab_object - Lab object or null
     * @return array List of PaperProperties
     */
    private static function getSummativeProperties($db, bool $remote = false, $lab_object = null): array
    {
        // SELECT statement.
        $select = 'properties.property_id,
                paper_title,
                start_date AS start_date,
                end_date AS end_date,
                exam_duration,
                calendar_year,
                password,
                timezone,
                rubric';
        // FROM statement.
        $from = 'properties';
        if ($remote) {
            $from .= ', paper_settings';
        }
        // WHERE statement.
        $now = time();
        if ($remote) {
            $where = "properties.property_id = paper_settings.paperid AND
                properties.paper_type = '2' AND
                paper_settings.setting = 'remote_summative' AND
                paper_settings.value = 1 AND
                properties.start_date IS NOT NULL AND
                properties.end_date > $now AND
                properties.deleted IS NULL
                ORDER BY properties.property_id";
        } else {
            $where = "paper_type = '2' AND
                labs REGEXP ? AND
                start_date < $now + 1800 AND
                end_date > $now AND
                deleted IS NULL";
        }
        $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where;

        $paper_results = $db->prepare($sql);
        if (!$remote) {
            // If an ip address is on many labs we only use with the first we come across
            $lab_regexp = '(^|,)(' . $lab_object->get_id() . ')(,|$)';
            $paper_results->bind_param('s', $lab_regexp);
        }
        $paper_results->execute();
        $paper_results->store_result();
        $paper_results->bind_result(
            $property_id,
            $paper_title,
            $start_date,
            $end_date,
            $exam_duration,
            $calendar_year,
            $password,
            $timezone,
            $rubric
        );

        if ($paper_results->num_rows <= 0) {
            $paper_results->close();
            return [];
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

    /**
     * Load the paper properties for active remote summative exams
     * used in the invigilator screens
     * @param object $db - Link to MySQL db.
     * @return array List of PaperProperties
     */
    public static function getRemoteSummativePaperProperties($db): array
    {
        return self::getSummativeProperties($db, true);
    }

    /**
    * Load the paper properties by lab ID
    * used in the invigilator screens. previously called (get_invigilator_properties)
    * @param object $lab_object - Lab object.
    * @param object $db                 - Link to MySQL db.
    * @return array List of PaperProperties
    */
    public static function get_paper_properties_by_lab($lab_object, $db): array
    {
        return self::getSummativeProperties($db, false, $lab_object);
    }

    /*
    * Loads the properties of a paper into the paper property object.
    */
    public function load()
    {
        $property_id = $this->get_property_id();
        $crypt_name = $this->get_crypt_name();
        $sql = "SELECT
                  property_id,
                  paper_title,
                  start_date,
                  end_date,
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
                  created,
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
            throw new Exception('property_id or crypt_name must be set to load the properties record from the DB.');
        }

        $paper_results->execute();
        $paper_results->store_result();
        if ($paper_results->num_rows == 0) {
            $paper_results->close();
            return false;
        }

        $paper_results->bind_result(
            $this->property_id,
            $this->paper_title,
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
            $this->password,
            $this->retired,
            $this->crypt_name,
            $this->recache_marks,
            $this->externalid,
            $this->externalsys
        );
        $paper_results->fetch();
        $paper_results->close();

        $this->setRogoFormatStartDate();
        $this->setRogoFormatEndDate();
        $this->set_display_start_date();
        $this->set_display_start_time();
        $this->set_display_end_date();
        $this->set_display_end_time();

        $this->changes = array();

        $this->load_summative_lock();

        $this->papersettings = new PaperSettings($this->get_property_id(), $this->get_paper_type());
        return true;
    }

    /*
    * Function to save the current properties back to the database.
    * The fields that can be saved depends on whether the paper
    * is locked or not and the roles of the current user.
    */
    public function save()
    {
        $configObject = Config::get_instance();
        $userObject   = UserObject::get_instance();
        $graded = $this->isGraded();
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
        } elseif ($configObject->get_setting('core', 'cfg_summative_mgmt') and $this->paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
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
            $params['deleted'] = array('i', $this->deleted);
            if (!$graded) {
                $params['start_date'] = array('i', $this->start_date);
                $params['end_date'] = array('i', $this->end_date);
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
    public function is_live()
    {
        $now = time();
        if ($this->start_date !== null and $now >= $this->start_date and $this->end_date !== null and $now <= $this->end_date) {
            return true;
        } else {
            return false;
        }
    }

    /*
    * Returns true/false depending if the current paper is a) summative, and b) locked (e.g. paper start time is in the past).
    * @return bool - True = the paper is locked, False = the paper is not locked.
    */
    private function load_summative_lock()
    {
        if ($this->start_date !== null and time() >= $this->start_date and $this->paper_type == '2') {
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
    private function load_question_no()
    {
        $item_no = 0;
        $question_no = 0;
        $max_screen = 0;
        $max_display_pos = 0;

        $paper_results = $this->db->prepare('SELECT q_type, screen, display_pos FROM papers, questions WHERE papers.question = questions.q_id AND paper = ?');
        $property_id = $this->get_property_id();
        $paper_results->bind_param('i', $property_id);
        $paper_results->execute();
        $paper_results->bind_result($q_type, $screen, $display_pos);
        while ($paper_results->fetch()) {
            $item_no++;
            if ($q_type != 'info') {
                $question_no++;
            }
            if ($screen > $max_screen) {
                $max_screen = $screen;
            }
            if ($display_pos > $max_display_pos) {
                $max_display_pos = $display_pos;
            }
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
    private function load_questions()
    {
        $q_no = 0;

        $paper_results = $this->db->prepare('SELECT q_id, q_type, screen FROM papers, questions WHERE papers.question = questions.q_id AND paper = ? ORDER BY screen, display_pos');
        $property_id = $this->get_property_id();
        $paper_results->bind_param('i', $property_id);
        $paper_results->execute();
        $paper_results->bind_result($q_id, $q_type, $screen);
        while ($paper_results->fetch()) {
            if ($q_type != 'info') {
                $q_no++;
            }
            $this->questions[] = array('q_id' => $q_id, 'q_no' => $q_no, 'type' => $q_type, 'screen' => $screen);
        }
        $paper_results->close();
    }

    /*
    * Return the list of questions used on the paper.
    * @return - array of questions on the paper.
    */
    public function get_questions()
    {
        if (!isset($this->questions)) {
            $this->load_questions();
        }

        return $this->questions;
    }

    private function load_changes()
    {
        $paper_results = $this->db->prepare('SELECT q_type, screen, display_pos FROM papers, questions WHERE papers.question = questions.q_id AND paper = ?');
        $property_id = $this->get_property_id();
        $paper_results->bind_param('i', $property_id);
        $paper_results->execute();
        $paper_results->bind_result($q_type, $screen, $display_pos);
        while ($paper_results->fetch()) {
            $item_no++;
            if ($q_type != 'info') {
                $question_no++;
            }
            if ($screen > $max_screen) {
                $max_screen = $screen;
            }
            if ($display_pos > $max_display_pos) {
                $max_display_pos = $display_pos;
            }
        }
        $paper_results->close();

        $this->item_no = $item_no;
        $this->question_no = $question_no;
        $this->max_screen = $max_screen;
        $this->max_display_pos = $max_display_pos;
    }

    private function load_externals()
    {
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

    private function load_internals()
    {
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

    public function get_summative_lock()
    {
        if (!isset($this->summative_lock)) {
            $this->load_summative_lock();
        }

        return $this->summative_lock;
    }

    public function is_objective_fb_released()
    {
        if (!isset($this->objective_fb_released)) {
            $this->load_objective_fb_released();
        }

        return $this->objective_fb_released;
    }

    public function is_question_fb_released()
    {
        if (!isset($this->question_fb_released)) {
            $this->load_question_fb_released();
        }

        return $this->question_fb_released;
    }

    private function load_objective_fb_released()
    {
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

    private function load_question_fb_released()
    {
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

    public function get_item_no()
    {
        if (!isset($this->item_no)) {
            $this->load_question_no();
        }

        return $this->item_no;
    }

    public function get_question_no()
    {
        if (!isset($this->question_no)) {
            $this->load_question_no();
        }

        return $this->question_no;
    }

    public function get_max_screen()
    {
        if (!isset($this->max_screen)) {
            $this->load_question_no();
        }

        return $this->max_screen;
    }

    public function get_max_display_pos()
    {
        if (!isset($this->max_display_pos)) {
            $this->load_question_no();
        }

        return $this->max_display_pos;
    }

    /**
     * @return string $property_id
     */
    public function get_property_id()
    {
        return $this->property_id;
    }

    /**
     * @param string $property_id
     */
    public function set_property_id($property_id)
    {
        $this->property_id = $property_id;
    }

    /**
     * @return string $paper_title
     */
    public function get_paper_title()
    {
        return $this->paper_title;
    }

    /**
     * @param string $paper_title
     * @return void
     */
    public function set_paper_title($paper_title)
    {
        if ($paper_title == '') {
            return;
        }

        $old_paper_title = $this->paper_title;

        $this->paper_title = $paper_title;

        if ($old_paper_title != $paper_title) {
            $this->changes[] = array('old' => $old_paper_title, 'new' => $paper_title, 'part' => 'name');
        }
    }

    /**
     * Gets the paper start date.
     *
     * @return string|null $start_date Unix timestamp or null if no start date is set.
     */
    public function get_start_date()
    {
        return $this->start_date;
    }

    /**
     * Get the ExamSys formatted start date
     * Used by log tables until they are migrated to bigint
     * @return string $start_date
     */
    public function getRogoFormatStartDate()
    {
        return $this->rogo_format_start_date;
    }

    /**
     * Set the ExamSys formatted start date
     * Used by log tables until they are migrated to bigint
     */
    public function setRogoFormatStartDate()
    {
        $this->rogo_format_start_date = date('YmdHis', $this->start_date);
    }

    /**
     * @param string $start_date
     */
    public function set_start_date($start_date)
    {
        $old_start_date = $this->start_date;

        $this->start_date = $start_date;

        if ($old_start_date != $start_date) {
            $this->changes[] = array('old' => $old_start_date, 'new' => $start_date, 'part' => 'startdate');
        }
    }

    /**
     * @return string $display_start_date
     */
    public function get_display_start_date()
    {
        return $this->display_start_date;
    }

    /**
     * @return string $display_start_time
     */
    public function get_display_start_time()
    {
        return $this->display_start_time;
    }

    /**
     * @param string $display_start_date
     */
    public function set_display_start_date($display_start_date = '')
    {
        if ($display_start_date == '') {
            // Summative papers may have no start date until scheduled
            if ($this->start_date != '') {
                $start_datetime = DateTime::createFromFormat('U', $this->start_date);
                $start_datetime->setTimezone($this->get_date_time_zone());
                $this->display_start_date = $start_datetime->format($this->configObject->get('cfg_long_datetime_php'));
            }
        } else {
            $this->display_start_date = $display_start_date;
        }
    }

    /**
     * @param string $display_start_date
     */
    public function set_display_start_time($display_start_time = '')
    {
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
     * Get the ExamSys formatted end date
     * Used by log tables until they are migrated to bigint
     * @return string $end_date
     */
    public function getRogoFormatEndDate()
    {
        return $this->rogo_format_end_date;
    }

    /**
     * Set the ExamSys formatted end date
     * Used by log tables until they are migrated to bigint
     */
    public function setRogoFormatEndDate()
    {
        $this->rogo_format_end_date = date('YmdHis', $this->end_date);
    }

    /**
     * Get the end date of the paper.
     *
     * @return string|null $end_date Unix timestamp or null if no date has been set.
     */
    public function get_end_date()
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     */
    public function set_end_date($end_date)
    {
        $old_end_date = $this->end_date;

        $this->end_date = $end_date;

        if ($old_end_date != $end_date) {
            $this->changes[] = array('old' => $old_end_date, 'new' => $end_date, 'part' => 'enddate');
        }
    }

    /**
     * @return string $end_date
     */
    public function get_display_end_date()
    {
        return $this->display_end_date;
    }

    /**
     * @return string $end_date
     */
    public function get_display_end_time()
    {
        return $this->display_end_time;
    }

    /**
     * @param string $end_date
     */
    public function set_display_end_date($display_end_date = '')
    {
        if ($display_end_date == '') {
            // Summative papers may have no end date until scheduled
            if ($this->end_date != '') {
                $end_datetime = DateTime::createFromFormat('U', $this->end_date);
                $end_datetime->setTimezone($this->get_date_time_zone());
                $this->display_end_date = $end_datetime->format($this->configObject->get('cfg_long_datetime_php'));
            }
        } else {
            $this->display_end_date = $display_end_date;
        }
    }

    /**
     * @param string $end_date
     */
    public function set_display_end_time($display_end_time = '')
    {
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
    public function get_timezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $time_zone
     */
    public function set_timezone($timezone)
    {
        $old_timezone = $this->timezone;

        $this->timezone = $timezone;

        if ($old_timezone != $timezone) {
            $this->changes[] = array('old' => $old_timezone, 'new' => $timezone, 'part' => 'timezone');
        }
    }

    /**
     * @return string $paper_type
     */
    public function get_paper_type()
    {
        return $this->paper_type;
    }

    /**
     * @param string $paper_type
     */
    public function set_paper_type($paper_type)
    {
        $old_paper_type = $this->paper_type;

        $this->paper_type = $paper_type;

        if ($old_paper_type != $paper_type) {
            $this->changes[] = array('old' => $old_paper_type, 'new' => $paper_type, 'part' => 'papertype');
        }
    }

    /**
     * @return string $paper_prologue
     */
    public function get_paper_prologue()
    {
        return $this->paper_prologue;
    }

    /**
     * @param string $paper_prologue
     */
    public function set_paper_prologue($paper_prologue)
    {
        $old_paper_prologue = $this->paper_prologue;

        $this->paper_prologue = $paper_prologue;

        if ($old_paper_prologue != $paper_prologue) {
            $this->changes[] = array('old' => $old_paper_prologue, 'new' => $paper_prologue, 'part' => 'prologue');
        }
    }

    /**
     * @return string $paper_postscript
     */
    public function get_paper_postscript()
    {
        return $this->paper_postscript ?? ''; // Avoid return null
    }

    /**
     * @param string $paper_postscript
     */
    public function set_paper_postscript($paper_postscript)
    {
        $old_paper_postscript = $this->paper_postscript;

        $this->paper_postscript = $paper_postscript;

        if ($old_paper_postscript != $paper_postscript) {
            $this->changes[] = array('old' => $old_paper_postscript, 'new' => $paper_postscript, 'part' => 'postscript');
        }
    }

    /**
     * @return string $bgcolor
     */
    public function get_bgcolor()
    {
        return $this->bgcolor;
    }

    /**
     * @param string $bgcolor
     */
    public function set_bgcolor($bgcolor)
    {
        $old_bgcolor = $this->bgcolor;

        $this->bgcolor = $bgcolor;

        if ($old_bgcolor != $bgcolor) {
            $this->changes[] = array('old' => $old_bgcolor, 'new' => $bgcolor, 'part' => 'background');
        }
    }

    /**
     * @return string $fgcolor
     */
    public function get_fgcolor()
    {
        return $this->fgcolor;
    }

    /**
     * @param string $fgcolor
     */
    public function set_fgcolor($fgcolor)
    {
        $old_fgcolor = $this->fgcolor;

        $this->fgcolor = $fgcolor;

        if ($old_fgcolor != $fgcolor) {
            $this->changes[] = array('old' => $old_fgcolor, 'new' => $fgcolor, 'part' => 'foreground');
        }
    }

    /**
     * @return string $thememecolor
     */
    public function get_themecolor()
    {
        return $this->themecolor;
    }

    /**
     * @param string $themecolor
     */
    public function set_themecolor($themecolor)
    {
        $old_themecolor = $this->themecolor;

        $this->themecolor = $themecolor;

        if ($old_themecolor != $themecolor) {
            $this->changes[] = array('old' => $old_themecolor, 'new' => $themecolor, 'part' => 'theme');
        }
    }

    /**
     * @return string $labelcolor
     */
    public function get_labelcolor()
    {
        return $this->labelcolor;
    }

    /**
     * @param string $labelcolor
     */
    public function set_labelcolor($labelcolor)
    {
        $old_labelcolor = $this->labelcolor;

        $this->labelcolor = $labelcolor;

        if ($old_labelcolor != $labelcolor) {
            $this->changes[] = array('old' => $old_labelcolor, 'new' => $labelcolor, 'part' => 'labelsnotes');
        }
    }

    /**
     * @return string $fullscreen
     */
    public function get_fullscreen()
    {
        if ($this->fullscreen == '') {        // Fix old incorrect data.
            $this->fullscreen = '1';
        }
        return $this->fullscreen;
    }

    /**
     * @param string $fullscreen
     */
    public function set_fullscreen($fullscreen)
    {
        $old_fullscreen = $this->fullscreen;

        $this->fullscreen = $fullscreen;

        if ($old_fullscreen != $fullscreen) {
            $this->changes[] = array('old' => $old_fullscreen, 'new' => $fullscreen, 'part' => 'display');
        }
    }

    /**
     * @return string $marking
     */
    public function get_marking()
    {
        return $this->marking;
    }

    /**
     * @param string $marking
     */
    public function set_marking($marking)
    {
        $old_marking = $this->marking;

        $this->marking = $marking;

        if ($old_marking != $marking) {
            $this->changes[] = array('old' => $old_marking, 'new' => $marking, 'part' => 'marking');
        }
    }

    /**
     * @return string $bidirectional
     */
    public function get_bidirectional()
    {
        return $this->bidirectional;
    }

    /**
     * @param string $bidirectional
     */
    public function set_bidirectional($bidirectional)
    {
        $old_bidirectional = $this->bidirectional;

        $this->bidirectional = $bidirectional;

        if ($old_bidirectional != $bidirectional) {
            $this->changes[] = array('old' => $old_bidirectional, 'new' => $bidirectional, 'part' => 'navigation');
        }
    }

    /**
     * @return int $pass_mark
     */
    public function get_pass_mark()
    {
        return $this->pass_mark;
    }

    /**
     * Check if marking has started for the OSCE station.
     * @param int $paperID
     * @param stdClass $mysqli
     * @return boolean
     */
    public function get_osce_started_status($paperID, $mysqli)
    {
        if ($this->paper_type <> 4) {
            return false;
        }
        $result = $mysqli->prepare('SELECT 1 as count FROM (modules_student, users) JOIN log4_overall ON users.id = log4_overall.userID AND q_paper = ? WHERE modules_student.userID = users.id LIMIT 1');
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
    public function set_pass_mark($pass_mark)
    {
        $old_pass_mark = $this->pass_mark;

        $this->pass_mark = $pass_mark;

        if ($old_pass_mark != $pass_mark) {
            $this->changes[] = array('old' => $old_pass_mark, 'new' => $pass_mark, 'part' => 'passmark');
        }
    }

    /**
     * @return int $distinction_mark
     */
    public function get_distinction_mark()
    {
        return $this->distinction_mark;
    }

    /**
     * @param int $distinction_mark
     */
    public function set_distinction_mark($distinction_mark)
    {
        $old_distinction_mark = $this->distinction_mark;

        $this->distinction_mark = $distinction_mark;

        if ($old_distinction_mark != $distinction_mark) {
            $this->changes[] = array('old' => $old_distinction_mark, 'new' => $distinction_mark, 'part' => 'distinction');
        }
    }

    /**
     * @return int $paper_ownerid
     */
    public function get_paper_ownerid()
    {
        return $this->paper_ownerID;
    }

    /**
     * @param int $paper_ownerid
     */
    public function set_paper_ownerid($paper_ownerid)
    {
        $this->paper_ownerID = $paper_ownerid;
    }

    /**
     * @return string $folder
     */
    public function get_folder()
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     */
    public function set_folder($folder)
    {
        $old_folder = $this->folder;

        $this->folder = $folder;

        if ($old_folder != $folder) {
            $this->changes[] = array('old' => $old_folder, 'new' => $folder, 'part' => 'folder');
        }
    }

    /**
     * Gets the labs that the paper is restricted to
     *
     * If labs are set then students must be in one of the labs to take the paper.
     *
     * @return string A comma separated list of lab ids, if an empty string then no restrictions are in place.
     */
    public function get_labs()
    {
        // It seems that historically null was allowed as the value of the labs,
        // this does not appear to tbe the case in modern ExamSys. Later code also
        // always assumes it iis a string.
        return $this->labs ?? '';
    }

    /**
     * Set the labs the paper is restricted to
     *
     * If labs are set then students must be in one of the labs to take the paper.
     *
     * @param string $labs A comma separated list of lab ids, if an empty string then no restrictions are in place.
     */
    public function set_labs($labs)
    {
        $old_labs = $this->labs;

        $this->labs = $labs ?? '';

        if ($old_labs != $labs) {
            $this->changes[] = array('old' => $old_labs, 'new' => $labs, 'part' => 'labs');
        }
    }

    /**
     * @return string $rubric
     */
    public function get_rubric()
    {
        return $this->rubric;
    }

    /**
     * @param string $rubric
     */
    public function set_rubric($rubric)
    {
        $old_rubric = $this->rubric;

        $this->rubric = $rubric;

        if ($old_rubric != $rubric) {
            $this->changes[] = array('old' => $old_rubric, 'new' => $rubric, 'part' => 'rubric');
        }
    }

    /**
     * @return int $calculator
     */
    public function get_calculator()
    {
        return $this->calculator;
    }

    /**
     * @param int $calculator
     */
    public function set_calculator($calculator)
    {
        $old_calculator = $this->calculator;

        $this->calculator = $calculator;

        if ($old_calculator != $calculator) {
            $this->changes[] = array('old' => $old_calculator, 'new' => $calculator, 'part' => 'displaycalculator');
        }
    }

    /**
     * @return string[] $externals
     */
    public function get_externals()
    {
        if (!isset($this->externals)) {
            $this->load_externals();
        }

        return $this->externals;
    }

    /**
     * @param string $externals
     */
    public function set_externals($externals)
    {
        $this->externals = $externals;
    }

    /**
     * @return int $exam_duration
     */
    public function get_exam_duration()
    {
        if ($this->exam_duration == 0) {
            return null;
        } else {
            return $this->exam_duration;
        }
    }

    /**
     * @return int $exam_duration in seconds
     */
    public function get_exam_duration_sec()
    {
        return $this->exam_duration * 60;
    }

    /**
     * @param int $exam_duration
     */
    public function set_exam_duration($exam_duration)
    {
        $old_exam_duration = $this->exam_duration;

        if ($exam_duration == 0) {
            $exam_duration = null;
        }
        $this->exam_duration = $exam_duration;

        if ($old_exam_duration != $exam_duration) {
            $this->changes[] = array('old' => $old_exam_duration, 'new' => $exam_duration, 'part' => 'duration');
        }
    }

    /**
     * @return string $deleted
     */
    public function get_deleted()
    {
        return $this->deleted;
    }

    /**
     * @param string $deleted
     */
    public function set_deleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return string $created
     */
    public function get_created()
    {
        return $this->created;
    }

    /**
     * @param string $created
     */
    public function set_created($created)
    {
        $this->created = $created;
    }

    /**
     * Get the random mark for the paper.
     *
     * The random mark is statistically the mark a user will gain from randomly guessing answers.
     *
     * @return float $random_mark
     */
    public function get_random_mark()
    {
        return $this->random_mark ?? 0.0;
    }

    /**
     * Set the random mark for the paper.
     *
     * The random mark is statistically the mark a user will gain from randomly guessing answers.
     *
     * @param float $random_mark
     */
    public function set_random_mark($random_mark)
    {
        $this->random_mark = $random_mark ?? 0.0;
    }

    /**
     * Get the total marks awarded by questions on the paper.
     *
     * @return int $total_mark
     */
    public function get_total_mark()
    {
        return $this->total_mark;
    }

    /**
     * Set the total marks available on the paper.
     *
     * @param int $total_mark
     */
    public function set_total_mark($total_mark)
    {
        $this->total_mark = $total_mark;
    }

    /**
     * @return string $display_correct_answer
     */
    public function get_display_correct_answer()
    {
        return $this->display_correct_answer;
    }

    /**
     * @param string $display_correct_answer
     */
    public function set_display_correct_answer($display_correct_answer)
    {
        $old_display_correct_answer = $this->display_correct_answer;

        $this->display_correct_answer = $display_correct_answer;

        if ($old_display_correct_answer != $display_correct_answer) {
            if ($this->get_paper_type() == '6') {
                $this->changes[] = array('old' => $old_display_correct_answer, 'new' => $display_correct_answer, 'part' => 'photos');
            } else {
                $this->changes[] = array('old' => $old_display_correct_answer, 'new' => $display_correct_answer, 'part' => 'correctanswerhighlight');
            }
        }
    }

    /**
     * @return string $display_question_mark
     */
    public function get_display_question_mark()
    {
        return $this->display_question_mark;
    }

    /**
     * @param string $display_question_mark
     */
    public function set_display_question_mark($display_question_mark)
    {
        $old_display_question_mark = $this->display_question_mark;

        $this->display_question_mark = $display_question_mark;

        if ($old_display_question_mark != $display_question_mark) {
            $this->changes[] = array('old' => $old_display_question_mark, 'new' => $display_question_mark, 'part' => 'review');
        }
    }

    /**
     * @return string $display_students_response
     */
    public function get_display_students_response()
    {
        return $this->display_students_response;
    }

    /**
     * @param string $display_students_response
     */
    public function set_display_students_response($display_students_response)
    {
        $old_display_students_response = $this->display_students_response;

        $this->display_students_response = $display_students_response;

        if ($old_display_students_response != $display_students_response) {
            $this->changes[] = array('old' => $old_display_students_response, 'new' => $display_students_response, 'part' => 'ticks_crosses');
        }
    }

    /**
     * @return string $display_feedback
     */
    public function get_display_feedback()
    {
        return $this->display_feedback;
    }

    /**
     * @param string $display_feedback
     */
    public function set_display_feedback($display_feedback)
    {
        $old_display_feedback = $this->display_feedback;

        $this->display_feedback = $display_feedback;

        if ($display_feedback != $old_display_feedback) {
            $this->changes[] = array('old' => $old_display_feedback, 'new' => $display_feedback, 'part' => 'textfeedback');
        }
    }

    /**
     * @return string $hide_if_unanswered
     */
    public function get_hide_if_unanswered()
    {
        return $this->hide_if_unanswered;
    }

    /**
     * @param string $hide_if_unanswered
     */
    public function set_hide_if_unanswered($hide_if_unanswered)
    {
        $old_hide_if_unanswered = $this->hide_if_unanswered;

        $this->hide_if_unanswered = $hide_if_unanswered;

        if ($old_hide_if_unanswered != $hide_if_unanswered) {
            $this->changes[] = array('old' => $old_hide_if_unanswered, 'new' => $hide_if_unanswered, 'part' => 'hideallfeedback');
        }
    }

    /**
     * @return int $calendar_year
     */
    public function get_calendar_year()
    {
        return $this->calendar_year;
    }

    /**
     * @param int $calendar_year
     */
    public function set_calendar_year($calendar_year)
    {
        $old_calendar_year = $this->calendar_year;

        $this->calendar_year = $calendar_year;

        if ($old_calendar_year != $calendar_year) {
            $this->changes[] = array('old' => $old_calendar_year, 'new' => $calendar_year, 'part' => 'session');
        }
    }

    /**
     * @return string[] $internal_reviewers
     */
    public function get_internal_reviewers()
    {
        if (!isset($this->internal_reviewers)) {
            $this->load_internals();
        }

        return $this->internal_reviewers;
    }

    /**
     * @param string $internal_reviewers
     */
    public function set_internal_reviewers($internal_reviewers)
    {
        $this->internal_reviewers = $internal_reviewers;
    }

    /**
     * @return string $external_review_deadline
     */
    public function get_external_review_deadline()
    {
        return $this->external_review_deadline;
    }

    /**
     * @param string $external_review_deadline
     */
    public function set_external_review_deadline($external_review_deadline)
    {
        $old_external_review_deadline = $this->external_review_deadline;

        $this->external_review_deadline = $external_review_deadline;

        if ($old_external_review_deadline != $external_review_deadline) {
            $this->changes[] = array('old' => $old_external_review_deadline, 'new' => $external_review_deadline, 'part' => 'externalreviewdeadline');
        }
    }

    /**
     * @return string $internal_review_deadline
     */
    public function get_internal_review_deadline()
    {
        return $this->internal_review_deadline;
    }

    /**
     * @param string $internal_review_deadline
     */
    public function set_internal_review_deadline($internal_review_deadline)
    {
        $old_internal_review_deadline = $this->internal_review_deadline;

        $this->internal_review_deadline = $internal_review_deadline;

        if ($old_internal_review_deadline != $internal_review_deadline) {
            $this->changes[] = array('old' => $old_internal_review_deadline, 'new' => $internal_review_deadline, 'part' => 'internalreviewdeadline');
        }
    }

    /**
     * @return string $sound_demo
     */
    public function get_sound_demo()
    {
        return $this->sound_demo;
    }

    /**
     * @param string $sound_demo
     */
    public function set_sound_demo($sound_demo)
    {
        $old_sound_demo = $this->sound_demo;

        $this->sound_demo = $sound_demo;

        if ($old_sound_demo != $sound_demo) {
            $this->changes[] = array('old' => $old_sound_demo, 'new' => $sound_demo, 'part' => 'demosoundclip');
        }
    }

    /**
     * Return the encrypted password for a paper.
     *
     * @return string $password
     */
    public function get_password()
    {
        return $this->password;
    }

    /**
     * Return the password for a paper.
     *
     * @return string $password
     */
    public function get_decrypted_password()
    {
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
    public function set_password($password)
    {
        $paperID = $this->get_property_id();
        $old_password = $this->get_decrypted_password();

        if ($password != '') {
            $this->password = $this->encrypt_password($paperID . $password);
        } else {
            $this->password = '';
        }

        if ($old_password != $password) {
            $this->changes[] = array('old' => $old_password, 'new' => $password, 'part' => 'password');
        }
    }

    /**
     * Encrypt a password that can be de-crypted.
     *
     * @param $string $password
     * @return $string encrypted passsword
     */
    public function encrypt_password($password)
    {
        return \encryp::openssl_encrypt_decrypt('encrypt', $password);
    }

    /**
     * Decrypt the password.
     *
     * @param string $enc_password encrypted passsword
     * @return string decrypted passsword
     */
    public function decrypt_password($encpassword)
    {
        return \encryp::openssl_encrypt_decrypt('decrypt', $encpassword);
    }

    /**
     * @param int recache_marks
     */
    public function get_recache_marks()
    {
        return $this->recache_marks;
    }

    /**
     * @param int recache_marks
     */
    public function set_recache_marks($recache_marks)
    {
        $this->recache_marks = $recache_marks;
    }

    /**
     * @return string $retired
     */
    public function get_retired()
    {
        return $this->retired;
    }

    /**
     * @param string $retired
     */
    public function set_retired($retired)
    {
        $this->retired = $retired;
    }

    /**
     * @return string $crypt_name
     */
    public function get_crypt_name()
    {
        return $this->crypt_name;
    }

    /**
     * @param string $crypt_name
     */
    public function set_crypt_name($crypt_name)
    {
        $this->crypt_name = $crypt_name;
    }

    /**
     * @return string $externals
     */
    public function get_modules($force_recache = false)
    {
        if (!isset($this->modules) or $force_recache) {
            $this->load_modules();
        }

        return $this->modules;
    }

    private function load_modules()
    {
        $paperID = $this->get_property_id();
        $this->modules = array();

        $result = $this->db->prepare('SELECT idMod, moduleid FROM (modules, properties_modules) WHERE idMod = id AND property_id = ?');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->bind_result($idMod, $moduleid);
        $result->store_result();
        while ($result->fetch()) {
            $this->modules[$idMod] = $moduleid;
        }
        $result->close();
    }

    private function get_date_time_zone()
    {
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
    public function unmarked_enhancedcalc($studentsonly = 0)
    {
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
     * @return void
     */
    private function load_unmarked_enhancedcalc($studentsonly = 0)
    {
        if (!$this->paper_stores_user_answers()) {
            return;
        }
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
                            foreach (QuestionUtils::get_random_question($question['q_id'], 'enhancedcalc') as $possible) {
                                $enhancedcalc_ids[] = $possible;
                            }
                            break;
                        case 'keyword_based':
                            foreach (QuestionUtils::get_keyword_question($question['q_id'], 'enhancedcalc') as $possible) {
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
            $this->enhancedcalc_questions = array();

            // Some error states are fatal we should skip over these to avoid an infitie loop trying to mark them,
            // Affected questions will be flagged to the staff member marking.
            $skiperrorstates = array(-5);

            if ($studentsonly) {
                $rolesql = \log::get_student_only();
            } else {
                $rolesql = '';
            }
            $result = $this->db->prepare("SELECT distinct log$paperType.q_id FROM log$paperType, log_metadata, users "
                . "$rolesql WHERE log$paperType.metadataID = log_metadata.id "
                . 'AND users.id = log_metadata.userID AND q_id IN (' . implode(',', $enhancedcalc_ids) . ') AND paperID = ? AND mark IS NULL and errorstate not in ('
                . implode(',', $skiperrorstates) . ') ORDER BY 1');
            $result->bind_param('i', $paperID);
            $result->execute();
            $result->store_result();
            $result->bind_result($qid);
            if ($result->num_rows > 0) {
                while ($result->fetch()) {
                    $this->enhancedcalc_questions[] = $qid;
                }
                if ($studentsonly) {
                    $this->unmarked_student_enhancedcalc = true;
                } else {
                    $this->unmarked_enhancedcalc = true;
                }
            }
            $result->close();
        }
    }

    /**
     * List of all calculation questions on paper
     * @param int $studentsonly only check students in cohort
     * @return array
     */
    public function get_enhancedcalc_questions($studentsonly = 0)
    {
        if ($studentsonly) {
            // Do we have student only questions?
            $check = $this->unmarked_student_enhancedcalc;
            // Force reload if we have non studnent questions.
            if ($this->unmarked_enhancedcalc === true) {
                $check = null;
            }
        } else {
            // Do we have non student questions?
            $check = $this->unmarked_enhancedcalc;
            // Force reload if we have studnent questions.
            if ($this->unmarked_student_enhancedcalc === true) {
                $check = null;
            }
        }
        if ($check === null) {
            $this->load_unmarked_enhancedcalc($studentsonly);
        }
        return $this->enhancedcalc_questions;
    }

    /**
     * Check state of unmarked textbox questions
     * @param int $studentsonly only check students in cohort
     * @return bool are there unmarked questions?
     */
    public function unmarked_textbox($studentsonly = 0)
    {
        if ($studentsonly) {
            $check = $this->unmarked_student_textbox;
        } else {
            $check = $this->unmarked_textbox;
        }

        if ($check === null) {
            $this->load_unmarked_textbox($studentsonly);
        }

        if ($studentsonly) {
            return $this->unmarked_student_textbox;
        } else {
            return $this->unmarked_textbox;
        }
    }

    /**
     * Check if textbox answers have been marked
     * @param int $studentsonly only check students in cohort
     * @return void
     */
    private function load_unmarked_textbox($studentsonly = 0)
    {
        if (!$this->paper_stores_user_answers()) {
            return;
        }
        if ($studentsonly) {
            $this->unmarked_student_textbox = false;
        } else {
            $this->unmarked_textbox = false;
        }

        if (!isset($this->questions)) {
            $this->load_questions();
        }

        $textbox_ids = array();

        $paperID = $this->get_property_id();
        $paperType = $this->get_paper_type();
        $excluded = new Exclusion($paperID, $this->db);
        $excluded->load();

        if (is_array($this->questions) and count($this->questions) > 0) {
            // Textbox questions may be hidden in random blocks of keyword baed questions so we have to check all possibilities.
            foreach ($this->questions as $question) {
                // Skip excluded questions.
                if (!$excluded->is_question_excluded($question['q_id'])) {
                    switch ($question['type']) {
                        case 'random':
                            foreach (QuestionUtils::get_random_question($question['q_id'], 'textbox') as $possible) {
                                $textbox_ids[] = $possible;
                            }
                            break;
                        case 'keyword_based':
                            foreach (QuestionUtils::get_keyword_question($question['q_id'], 'textbox') as $possible) {
                                $textbox_ids[] = $possible;
                            }
                            break;
                        case 'textbox':
                            $textbox_ids[] = $question['q_id'];
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        // Find unmarked questions.
        if (count($textbox_ids) > 0) {
            if ($studentsonly) {
                $rolesql = \log::get_student_only();
            } else {
                $rolesql = '';
            }
            $result = $this->db->prepare("SELECT log$paperType.id FROM log$paperType, log_metadata, users "
                . "$rolesql WHERE log$paperType.metadataID = log_metadata.id "
                . 'AND users.id = log_metadata.userID AND q_id IN (' . implode(',', $textbox_ids) . ') AND paperID = ? AND mark IS NULL LIMIT 1');
            $result->bind_param('i', $paperID);
            $result->execute();
            $result->store_result();
            $result->bind_result($id);
            if ($result->num_rows > 0) {
                if ($studentsonly) {
                    $this->unmarked_student_textbox = true;
                } else {
                    $this->unmarked_textbox = true;
                }
            }
            $result->close();
        }
    }

    public function q_type_exist($type)
    {
            $paperID = $this->get_property_id();

            $result = $this->db->prepare('SELECT COUNT(q_id) AS q_no FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type = ?');
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

    public function is_active()
    {
        $now = time();
        if ($now > $this->start_date and $now < $this->end_date) {
              return true;
        } else {
            return false;
        }
    }

    /**
     * Get papers external id
     * @return string
     */
    public function get_externalid()
    {
        return $this->externalid;
    }

    /**
     * Set externalid id of paper
     * @param string $externalid
     */
    public function set_externalid($externalid)
    {
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
    public function get_externalsys()
    {
        return $this->externalsys;
    }

    /**
     * Set externalid system name of paper
     * @param string $externalsys
     */
    public function set_externalsys($externalsys)
    {
        $old_externalsys = $this->externalsys;
        if ($old_externalsys != $externalsys) {
            $this->externalsys = $externalsys;
            $this->changes[] = array('old' => $old_externalsys, 'new' => $externalsys, 'part' => 'externalsys');
        }
    }

    /**
     * Returns an array of screen information - used for the numbers at the top
     * of the screen.
     * @param bool $is_question_preview_mode  - Are we previewing a single question
     * @param integer $get_qid the id of the question we are previewing
     * @return array - Returns an array of screens then question ID and question type.
     *
     */
    public function get_screens($is_question_preview_mode, $get_qid = null)
    {
        $paperID = $this->get_property_id();
        // Get how many screens make up the question paper.
        $screen_data = array();
        if ($is_question_preview_mode) {
            $stmt = $this->db->prepare('SELECT 1, q_type, q_id
                                    FROM
                                      questions
                                    WHERE
                                      questions.q_id = ?
                                    ');
            $stmt->bind_param('i', $get_qid);
        } else {
            $stmt = $this->db->prepare('SELECT
                                      screen, q_type, question
                                    FROM
                                      (papers, questions)
                                    WHERE
                                      papers.paper = ? AND
                                      papers.question = questions.q_id
                                    ORDER BY
                                      screen, display_pos');
            $stmt->bind_param('i', $paperID);
        }
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($screen, $q_type, $q_id);

        while ($stmt->fetch()) {
            if ($q_type != 'info') {    // Do not count information blocks.
                $screen_data[$screen][] = array($q_type, $q_id);
            }
        }
        $stmt->free_result();
        $stmt->close();

        return $screen_data;
    }

    /*
     * Load any Reference Material into an array.
     * @return array - Array of all reference material relevant to the current paper and the maximum reference width.
     */
    public function load_reference_materials()
    {
        $paperID = $this->get_property_id();
        $reference_materials = array();
        $ref_no = 0;
        $max_ref_width = 0;
        $stmt = $this->db->prepare('SELECT title, content, width FROM (reference_material, reference_papers) WHERE reference_material.id = reference_papers.refID AND paperID = ?');
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $stmt->bind_result($reference_title, $reference_material, $reference_width);
        while ($stmt->fetch()) {
            $reference_materials[$ref_no]['title'] = $reference_title;
            $reference_materials[$ref_no]['material'] = $reference_material;
            $reference_materials[$ref_no]['width'] = $reference_width;
            if ($ref_no == 0) {
                $max_ref_width = $reference_width;
            } elseif ($reference_width > $reference_materials[$ref_no - 1]['width']) {
                $max_ref_width = $reference_width;
            }
            $reference_materials[$ref_no]['num'] = $ref_no;
            $ref_no++;
        }
        $stmt->close();
        return array($reference_materials, $max_ref_width);
    }

    /**
     * Gets all the question on the paper
     *
     * This should have the same field in each question as {@see self::getReplacementQuestion()}
     *
     * @param boolean $is_question_preview_mode is the paper being previewed
     * @param integer $get_qid question id
     * @param integer $q_number question number on preview screen
     * @param bool $hide_notes hide question notes
     * @return array
     */
    public function build_paper($is_question_preview_mode, $get_qid, $q_number, $hide_notes = false)
    {
        $paperID = $this->get_property_id();
        $q_select = 'q_type,
                     q_id,
                     score_method,
                     display_method,
                     settings,
                     theme,
                     scenario,
                     leadin,
                     correct_fback,
                     notes,
                     display_pos,
                     q_option_order';

        $o_select = "o_id,
                     id_num,
                     marks_correct,
                     marks_incorrect,
                     marks_partial,
                     correct,
                     REPLACE(option_text,'\t','') AS option_text,
                     source,
                     width,
                     height,
                     alt";

        $q_from = 'papers JOIN questions ON papers.question = questions.q_id';
        $o_from = 'options LEFT JOIN (options_media JOIN media m ON options_media.mediaid = m.id)
            ON options.id_num = options_media.oid';
        $q_orderby = 'display_pos';
        if ($is_question_preview_mode) {
            $where = 'paper = ? AND q_id = ?';
            $q_sql = "SELECT 1, $q_select FROM $q_from WHERE $where ORDER BY $q_orderby";
            $question_data = $this->db->prepare($q_sql);
            $question_data->bind_param('ii', $paperID, $get_qid);

            $o_where = "o_id IN (SELECT q_id FROM $q_from WHERE $where) ";
            $o_sql = "SELECT $o_select FROM $o_from WHERE $o_where";
            $option_data = $this->db->prepare($o_sql);
            $option_data->bind_param('ii', $paperID, $get_qid);
        } else {
            $where = 'paper = ?';
            $q_sql = "SELECT screen, $q_select FROM $q_from WHERE $where ORDER BY $q_orderby";
            $question_data = $this->db->prepare($q_sql);
            $tmp_pid = $paperID;
            $question_data->bind_param('i', $tmp_pid);

            $o_where = "o_id IN (SELECT q_id FROM $q_from WHERE $where) ";
            $o_sql = "SELECT $o_select FROM $o_from WHERE $o_where";
            $option_data = $this->db->prepare($o_sql);
            $option_data->bind_param('i', $tmp_pid);
        }

        // Get the data for each question.
        $question_data->execute();
        $question_data->store_result();
        $question_data->bind_result(
            $screen,
            $q_type,
            $q_id,
            $score_method,
            $display_method,
            $settings,
            $theme,
            $scenario,
            $leadin,
            $correct_fback,
            $notes,
            $display_pos,
            $q_option_order
        );

        $q_no = 0;
        $assigned_number = 0;
        $no_on_screen = 0;
        $old_screen = 0;
        // Build the questions_array
        $tmp_questions_array = array();
        while ($question_data->fetch()) {
            $q_no++;
            if ($screen != $old_screen) {
                $no_on_screen = 0;
            }
            if ($q_type != 'info') {
                $assigned_number++;
                $no_on_screen++;
            }
            if (!is_null($q_number)) {
                $tmp_questions_array[$q_id]['assigned_number'] = $q_number;   // Preview mode, use the number that is passed in.
            } else {
                $tmp_questions_array[$q_id]['assigned_number'] = $assigned_number;
            }
            $tmp_questions_array[$q_id]['no_on_screen'] = $no_on_screen;
            $tmp_questions_array[$q_id]['screen'] = $screen;
            $tmp_questions_array[$q_id]['theme'] = trim($theme ?? '');
            $tmp_questions_array[$q_id]['scenario'] = trim($scenario ?? '');
            $tmp_questions_array[$q_id]['leadin'] = trim($leadin);
            $tmp_questions_array[$q_id]['notes'] = $hide_notes ? '' : trim($notes ?? '');
            $tmp_questions_array[$q_id]['q_type'] = $q_type;
            $tmp_questions_array[$q_id]['q_id'] = $q_id;
            $tmp_questions_array[$q_id]['display_pos'] = $display_pos;
            $tmp_questions_array[$q_id]['score_method'] = $score_method;
            $tmp_questions_array[$q_id]['display_method'] = $display_method;
            $tmp_questions_array[$q_id]['settings'] = $settings;
            $tmp_questions_array[$q_id]['q_option_order'] = $q_option_order;
            $tmp_questions_array[$q_id]['dismiss'] = '';
            $tmp_questions_array[$q_id]['correct_fback'] = $correct_fback;
            $tmp_questions_array[$q_id]['options'] = [];
            $used_questions[$q_id] = 1;

            // Get question media.
            $media = QuestionUtils::getMediaAsString($q_id);
            $tmp_questions_array[$q_id]['q_media'] = $media['source'];
            $tmp_questions_array[$q_id]['q_media_width'] = $media['width'];
            $tmp_questions_array[$q_id]['q_media_height'] = $media['height'];
            $tmp_questions_array[$q_id]['q_media_alt'] = $media['alt'];
            $tmp_questions_array[$q_id]['q_media_num'] = $media['num'];

            $old_screen = $screen;
        }
        $question_data->free_result();
        $question_data->close();

        // Add in the option data.
        $option_data->execute();
        $option_data->store_result();
        $option_data->bind_result(
            $q_id,
            $id_num,
            $marks_correct,
            $marks_incorrect,
            $marks_partial,
            $correct,
            $option_text,
            $option_media,
            $option_media_width,
            $option_media_height,
            $option_media_alt
        );

        while ($option_data->fetch()) {
            $tmp_questions_array[$q_id]['options'][$id_num] = array(
                'correct' => $correct,
                'option_text' => $option_text,
                'o_media' => $option_media,
                'o_media_width' => $option_media_width,
                'o_media_height' => $option_media_height,
                'o_media_alt' => $option_media_alt,
                'marks_correct' => $marks_correct,
                'marks_incorrect' => $marks_incorrect,
                'marks_partial' => $marks_partial
            );
        }
        $option_data->free_result();
        $option_data->close();

        // Now renumber the array so that the keys start at 1, and order the options by id_num.
        $return_array = [];
        $q_no = 0;
        foreach ($tmp_questions_array as $qid => $question) {
            $q_no++;
            $options = $question['options'];
            ksort($options);
            if (empty($options)) {
                // We must always have at least one set of options.
                $options[] = array(
                    'correct' => '',
                    'option_text' => '',
                    'o_media' => '',
                    'o_media_width' => '',
                    'o_media_height' => '',
                    'o_media_alt' => '',
                    'marks_correct' => '',
                    'marks_incorrect' => '',
                    'marks_partial' => '',
                );
            }
            $question['options'] = array_values($options);
            $return_array[$q_no] = $question;
        }
        return $return_array;
    }

    /**
     * Gets the details of a question to be displayed.
     *
     * This should include all the fields that are present in questions in {@see self::build_paper()}
     *
     * @param int $qid The database id of the question
     * @param int $position The position the question is in on the paper
     * @param int $screen The screen of the paper the question is on
     * @param int $screen_position The position of the question on the screen
     * @param bool $hide_notes If notes should be displayed
     * @return array The question data, or an empty array if the question could not be found.
     */
    protected function getReplacementQuestion(int $qid, int $position, int $screen, int $screen_position, bool $hide_notes): array
    {
        $q_select = 'q_type,
                     q_id,
                     score_method,
                     display_method,
                     settings,
                     theme,
                     scenario,
                     leadin,
                     correct_fback,
                     notes,
                     q_option_order';

        $o_select = "o_id,
                     id_num,
                     marks_correct,
                     marks_incorrect,
                     marks_partial,
                     correct,
                     REPLACE(option_text,'\t','') AS option_text,
                     source,
                     width,
                     height,
                     alt";

        $q_from = 'questions';
        $o_from = 'options LEFT JOIN (options_media JOIN media m ON options_media.mediaid = m.id)
            ON options.id_num = options_media.oid';

        $where = 'q_id = ?';
        $q_sql = "SELECT $q_select FROM $q_from WHERE $where";
        $question_data = $this->db->prepare($q_sql);
        $question_data->bind_param('i', $qid);

        $o_where = "o_id IN (SELECT q_id FROM $q_from WHERE $where) ";
        $o_sql = "SELECT $o_select FROM $o_from WHERE $o_where ORDER BY id_num";
        $option_data = $this->db->prepare($o_sql);
        $option_data->bind_param('i', $qid);

        // Get the data for each question.
        $question_data->execute();
        $question_data->store_result();
        $question_data->bind_result(
            $q_type,
            $q_id,
            $score_method,
            $display_method,
            $settings,
            $theme,
            $scenario,
            $leadin,
            $correct_fback,
            $notes,
            $q_option_order
        );

        // Build the question.
        $question = [];
        while ($question_data->fetch()) {
            $question['assigned_number'] = $position;
            $question['no_on_screen'] = $screen_position;
            $question['screen'] = $screen;
            $question['theme'] = trim($theme ?? '');
            $question['scenario'] = trim($scenario ?? '');
            $question['leadin'] = trim($leadin);
            $question['notes'] = $hide_notes ? '' : trim($notes ?? '');
            $question['q_type'] = $q_type;
            $question['q_id'] = $qid;
            $question['display_pos'] = $position;
            $question['score_method'] = $score_method;
            $question['display_method'] = $display_method;
            $question['settings'] = $settings;
            $question['q_option_order'] = $q_option_order;
            $question['dismiss'] = '';
            $question['correct_fback'] = $correct_fback;
            $question['options'] = [];

            // Get question media.
            $media = QuestionUtils::getMediaAsString($q_id);
            $question['q_media'] = $media['source'];
            $question['q_media_width'] = $media['width'];
            $question['q_media_height'] = $media['height'];
            $question['q_media_alt'] = $media['alt'];
            $question['q_media_num'] = $media['num'];
        }
        $question_data->free_result();
        $question_data->close();

        if (empty($question)) {
            // The question was not found for some reason, return early.
            return $question;
        }

        // Add in the option data.
        $option_data->execute();
        $option_data->store_result();
        $option_data->bind_result(
            $q_id,
            $id_num,
            $marks_correct,
            $marks_incorrect,
            $marks_partial,
            $correct,
            $option_text,
            $option_media,
            $option_media_width,
            $option_media_height,
            $option_media_alt
        );

        while ($option_data->fetch()) {
            $question['options'][$id_num] = [
                'correct' => $correct,
                'option_text' => $option_text,
                'o_media' => $option_media,
                'o_media_width' => $option_media_width,
                'o_media_height' => $option_media_height,
                'o_media_alt' => $option_media_alt,
                'marks_correct' => $marks_correct,
                'marks_incorrect' => $marks_incorrect,
                'marks_partial' => $marks_partial
            ];
        }
        $option_data->free_result();
        $option_data->close();

        if (empty($question['options'])) {
            // No options were found for the question.
            $question['options'][] = [
                'correct' => '',
                'option_text' => '',
                'o_media' => '',
                'o_media_width' => '',
                'o_media_height' => '',
                'o_media_alt' => '',
                'marks_correct' => '',
                'marks_incorrect' => '',
                'marks_partial' => '',
            ];
        }
        return $question;
    }

    /**
     * Looks up the source question in a random question block.
     * @param array $random_q_data  - Holds question information about the parent random question.
     * @param array $user_answers   - Holds a list of user answers by question ID.
     * @param array $screen_data        - Holds a list of question types and IDs used on all screens in the paper.
     * @param array $used_questions - Array of question IDs already used on the paper.
     * @param array $string             - Contains language translations.
     * @param bool $hide_notes hide question notes
     * @return array
     */
    public function randomQOverwrite($random_q_data, $user_answers, &$screen_data, &$used_questions, $string, $hide_notes = false)
    {
        $selected_q_id = '';
        $current_screen = $random_q_data['screen'];
        $q_no = $random_q_data['no_on_screen'];

        if (isset($user_answers[$current_screen])) {
            // Match user's answers with random question ID.
            $question_on_screen = array_keys($user_answers[$current_screen]);
            // This value will not exist if the paper has been edited so that the question is no longer on the
            // same screen or this method could not allocate a question when the page was last viewed.
            $selected_q_id = $question_on_screen[$q_no - 1] ?? '';
        }

        if ($selected_q_id == '') {
            $try = 0;
            $unique = false;
            while ($unique == false and $try < 9999) {
                $selected_q_id = random_utils::generate_random_qid_from_block($random_q_data['q_id'], $this->db);
                if ($selected_q_id === false) {
                    $unique = false;
                    break;
                }
                if (!isset($used_questions[$selected_q_id])) {
                    $unique = true;
                }
                $try++;
            }
            $used_questions[$selected_q_id] = 1;
        } else {
            $unique = true;
        }

        $error = false;

        if ($unique) {
            $question = $this->getReplacementQuestion(
                $selected_q_id,
                $random_q_data['assigned_number'],
                $random_q_data['screen'],
                $random_q_data['no_on_screen'],
                $hide_notes
            );

            if (!empty($question)) {
                // Overwrite the screen data.
                $screen_no = count($screen_data);
                for ($i = 1; $i <= $screen_no; $i++) {
                    if (isset($screen_data[$i])) {
                        $q_no = count($screen_data[$i]);
                    } else {
                        $q_no = 0;
                    }
                    for ($a = 0; $a < $q_no; $a++) {
                        if ($screen_data[$i][$a][1] == $random_q_data['q_id']) {
                            $screen_data[$i][$a][0] = $question['q_type'];
                            $screen_data[$i][$a][1] = $selected_q_id;
                        }
                    }
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) {
            $question = [
                'assigned_number' => $random_q_data['assigned_number'],
                'no_on_screen' => $random_q_data['no_on_screen'],
                'screen' => $random_q_data['screen'],
                'theme' => '',
                'scenario' => '',
                'leadin' => '<span class = "randomerror">' . $string['error_random'] . '</span>',
                'notes' => '',
                'q_type' => 'random',
                'q_id' => -1,
                'display_pos' => $random_q_data['assigned_number'],
                'score_method' => '',
                'display_method' => '',
                'settings' => '',
                'q_option_order' => '',
                'dismiss' => '',
                'correct_fback' => '',
                'options' => [],
                'q_media' => '',
                'q_media_width' => '',
                'q_media_height' => '',
                'q_media_alt' => '',
                'q_media_num' => '',
            ];
        }

        return $question;
    }

    /**
     * Looks up the source question in a keyword question block.
     * @param array $random_q_data  - Holds question information about the parent random question.
     * @param array $user_answers   - Holds a list of user answers by question ID.
     * @param array $screen_data        - Holds a list of question types and IDs used on all screens in the paper.
     * @param array $used_questions - Array of question IDs already used on the paper.
     * @param array $string             - Contains language translations.
     * @param bool $hide_notes hide question notes
     * @return array
     */
    public function keywordQOverwrite($random_q_data, $user_answers, &$screen_data, &$used_questions, $string, $hide_notes = false)
    {
        $selected_q_id = '';
        $unique = true;
        $current_screen = $random_q_data['screen'];
        $q_no = $random_q_data['no_on_screen'];

        if (isset($user_answers[$current_screen])) {
            // Match user's answers with random question ID.
            $question_on_screen = array_keys($user_answers[$current_screen]);
            // This value will not exist if the paper has been edited so that the question is no longer on the
            // same screen or this method could not allocate a question when the page was last viewed.
            $selected_q_id = $question_on_screen[$q_no - 1] ?? '';
        }

        if ($selected_q_id == '') {
            // Get the keyword id.
            $keyword_id = keyword_utils::get_keywordid_for_question($random_q_data['q_id'], $this->db);
            // Generate a random question ID from keywords.
            $question_ids = array();
            $question_data = $this->db->prepare('SELECT DISTINCT k.q_id FROM keywords_question k, questions q WHERE k.q_id = q.q_id AND'
                . ' k.keywordID = ? AND q.deleted is NULL');
            $question_data->bind_param('i', $keyword_id);
            $question_data->execute();
            $question_data->bind_result($q_id);
            while ($question_data->fetch()) {
                $question_ids[] = $q_id;
            }
            $question_data->close();
            shuffle($question_ids);

            $try = 0;
            $unique = false;
            while ($unique == false and $try < count($question_ids)) {
                $selected_q_id = $question_ids[$try];
                if (!isset($used_questions[$selected_q_id])) {
                    $unique = true;
                }
                $try++;
            }
            $used_questions[$selected_q_id] = 1;
        }

        $error = false;

        if ($unique) {
            $question = $this->getReplacementQuestion(
                $selected_q_id,
                $random_q_data['assigned_number'],
                $random_q_data['screen'],
                $random_q_data['no_on_screen'],
                $hide_notes
            );

            if (!empty($question)) {
                // Overwrite the screen data.
                $screen_no = count($screen_data);
                for ($i = 1; $i <= $screen_no; $i++) {
                    if (isset($screen_data[$i])) {
                        $q_no = count($screen_data[$i]);
                    } else {
                        $q_no = 0;
                    }
                    for ($a = 0; $a < $q_no; $a++) {
                        if ($screen_data[$i][$a][1] == $random_q_data['q_id']) {
                            $screen_data[$i][$a][0] = $question['q_type'];
                            $screen_data[$i][$a][1] = $selected_q_id;
                        }
                    }
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error == true) {
            $question = [
                'assigned_number' => $random_q_data['assigned_number'],
                'no_on_screen' => $random_q_data['no_on_screen'],
                'screen' => $random_q_data['screen'],
                'theme' => '',
                'scenario' => '',
                'leadin' => '<span class = "keyworderror">' . $string['error_keywords'] . '</span>',
                'notes' => '',
                'q_type' => 'keyword_based',
                'q_id' => -1,
                'display_pos' => $random_q_data['assigned_number'],
                'score_method' => '',
                'display_method' => '',
                'settings' => '',
                'q_option_order' => '',
                'dismiss' => '',
                'correct_fback' => '',
                'options' => [],
                'q_media' => '',
                'q_media_width' => '',
                'q_media_height' => '',
                'q_media_alt' => '',
                'q_media_num' => '',
            ];
        }

        return $question;
    }

    /**
     * Check if paper should display a timer
     * @return boolean
     */
    public function display_timer()
    {
        if (is_null($this->get_exam_duration())) {
            // A timer cannot be used on papers with no duration.
            return false;
        }

        if (
            $this->paper_type == '0' or
            $this->paper_type == '1' or
            ($this->paper_type ==  '2' and $this->getSetting('remote_summative'))
        ) {
            // Foramtive, Progressive or REMOTE summative papers that have a duration set should use the timer.
            return true;
        } elseif ($this->paper_type == '2') {
            // Summative exams only allow timing if ALL the modules of the paper allow it.
            return module_utils::modules_allow_timing(array_keys(Paper_utils::get_modules($this->property_id, $this->db)), $this->db);
        }
        return false;
    }

    /**
     * Get list of users that have taken the paper.
     * @param string $startdate start of datetime range
     * @param string $enddate end of datetime range
     * @param float $percentile range of percentile
     * @param boolean $studentonly do we only want student users
     * @param string $modules the modules we are interested in
     * @return array
     */
    public function get_user_list($startdate, $enddate, $percentile, $studentonly = true, $modules = '')
    {
        $student_list = array();
        $userlist = null;
        if ($modules !== '') {
            $moduleusers = array();
            $calendar_year = $this->get_calendar_year();
            $modules = explode(',', $modules);
            foreach ($modules as $module) {
                $members = module_utils::get_student_members($calendar_year, $module, $this->db);
                foreach ($members as $member) {
                    $moduleusers[] = $member['userID'];
                }
            }
            $moduleusers = array_unique($moduleusers);
            $userlist = $moduleusers;
        }
        $log = log::get_paperlog($this->get_paper_type());
        $users = $log->get_log_users($this->property_id, $startdate, $enddate, $userlist, $studentonly);
        $user_no = round((count($users) / 100) * $percentile);
        for ($student_no = 0; $student_no < $user_no; $student_no++) {
            $student_list[] = $users[$student_no]['userid'];
        }
        return $student_list;
    }

    /**
     * Tests if the paper has access restricted by metadata.
     *
     * @return bool
     */
    public function has_metadata(): bool
    {
        $sql = 'SELECT NULL FROM paper_metadata_security WHERE paperID = ? LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->bind_param('i', $pid);
        $pid = $this->get_property_id();
        $query->execute();
        $query->store_result();
        return ($query->num_rows > 0);
    }

    /**
     * Get a list of users who can take the paper.
     *
     * @return \users\UserList
     */
    public function get_users(): \users\UserList
    {
        $users = new \users\UserList();
        $pid = $this->get_property_id();
        $year = $this->get_calendar_year();
        if ($this->has_metadata()) {
            $sql = "SELECT u.id, u.first_names, u.grade, u.surname, GROUP_CONCAT(r.name  SEPARATOR ','), u.title, u.username, 
                           u.yearofstudy, s.student_id
              FROM users u
              JOIN user_roles ur ON u.id = ur.userid
              JOIN roles r ON r.id = ur.roleid
              JOIN modules_student ms ON ms.userID = u.id
              JOIN properties_modules pm ON pm.idMod = ms.idMod
              JOIN users_metadata um ON um.userID = u.id AND um.idMod = ms.idMod AND um.idMod = pm.idMod AND um.calendar_year = ms.calendar_year
              JOIN paper_metadata_security ps ON ps.name = um.type AND ps.value = um.value
              LEFT JOIN sid s ON s.userID = u.id
              WHERE pm.property_id = ? AND ms.calendar_year = ? AND ps.paperID = ?
              GROUP BY u.id, u.first_names, u.grade, u.surname, u.title, u.username, u.yearofstudy, s.student_id
              ORDER BY u.surname, u.first_names, s.student_id";
            $params = ['iii', &$pid, &$year, &$pid];
        } else {
            $sql = "SELECT u.id, u.first_names, u.grade, u.surname, GROUP_CONCAT(r.name  SEPARATOR ','), u.title, u.username,
                           u.yearofstudy, s.student_id
              FROM users u
              JOIN user_roles ur ON u.id = ur.userid
              JOIN roles r ON r.id = ur.roleid
              JOIN modules_student ms ON ms.userID = u.id
              JOIN properties_modules pm ON pm.idMod = ms.idMod
              LEFT JOIN sid s ON s.userID = u.id
              WHERE pm.property_id = ? AND ms.calendar_year = ?
              GROUP BY u.id, u.first_names, u.grade, u.surname, u.title, u.username, u.yearofstudy, s.student_id
              ORDER BY u.surname, u.first_names, s.student_id";
            $params = ['ii', &$pid, &$year];
        }
        $query = $this->db->prepare($sql);
        if ($query === false) {
            throw new \coding_exception($this->db->error);
        }
        call_user_func_array([$query, 'bind_param'], $params);
        $query->execute();
        $query->bind_result($id, $firstname, $grade, $lastname, $role, $title, $username, $studentyear, $sid);
        while ($query->fetch()) {
            $user = new \users\User();
            $user->firstname = $firstname;
            $user->grade = $grade;
            $user->id = $id;
            $user->lastname = $lastname;
            $user->role = $role;
            $user->studentid = $sid;
            $user->title = $title;
            $user->username = $username;
            $user->year = $studentyear;
            $users->add($user);
        }
        $query->close();
        return $users;
    }

    /**
     * Get assessment data for paper
     * @param string $course course to filter by
     * @param string $startdate start of datetime range
     * @param string $enddate end of datetime range
     * @param string $user_list comma seperated list of users to filter by
     * @param boolean $studentonly do we only want student users
     * @param boolean $demo obfusticate data if in demo mode
     * @return array
     */
    public function get_paper_assessment_data($course, $startdate, $enddate, $user_list, $studentonly, $demo)
    {
        $log_array = array();
        $rowID = 0;
        // Capture the log data.
        $log = log::get_paperlog($this->get_paper_type());
        $assessment = $log->get_assessment_data($this->property_id, $startdate, $enddate, $user_list, $course, $studentonly);
        $old_username = '';
        $old_started = '';
        $users = array();
        foreach ($assessment as $log) {
            if ($old_username != $log['username'] or $old_started != $log['started']) {
                $rowID++;
            }
            $log_array[$rowID][$log['screen']][$log['question_ID']] = $log['user_answer'];
            $log_array[$rowID]['userID'] = $log['uID'];
            $users[$log['uID']][] = $rowID;
            $log_array[$rowID]['username'] = $log['username'];
            $log_array[$rowID]['course'] = $log['grade'];
            $log_array[$rowID]['year'] = $log['year'];
            $log_array[$rowID]['started'] = $log['started'];
            $log_array[$rowID]['title'] = $log['title'];
            $log_array[$rowID]['surname'] = \demo::demo_replace($log['surname'], $demo);
            $log_array[$rowID]['first_names'] = \demo::demo_replace($log['first_names'], $demo);
            $log_array[$rowID]['name'] = str_replace("'", '', $log['surname']) . ',' . $log['first_names'];
            $log_array[$rowID]['gender'] = $log['gender'];

            $old_username = $log['username'];
            $old_started = $log['started'];
        }

        // Get student ids.
        if (count($users) > 0) {
            $users_list = implode(',', array_keys($users));
            $result = $this->db->prepare("SELECT student_id, userID FROM sid WHERE userID IN ($users_list)");
            $result->execute();
            $result->bind_result($sid, $userid);
            while ($result->fetch()) {
                foreach ($users[$userid] as $row) {
                    $log_array[$row]['student_id'] = \demo::demo_replace_number($sid, $demo);
                }
            }
            for ($rowID = 1; $rowID < count($log_array); $rowID++) {
                if (!isset($log_array[$rowID]['student_id'])) {
                    $log_array[$rowID]['student_id'] = null;
                }
            }
            $result->close();
        }
        $sortby = 'name';
        $ordering = 'asc';
        return \sort::array_csort($log_array, $sortby, $ordering);
    }

    /**
     * Get the paper details
     * @return array
     */
    public function get_paper_questions()
    {
        $paper_buffer = array();
        $configObject = \Config::get_instance();
        $db = $configObject->db;
        $question_no = -1;
        $old_q_id = -1;
        $result = $db->prepare("SELECT q_id, q_type, screen, correct, option_text, score_method, settings, id_num FROM papers, questions LEFT JOIN options ON questions.q_id = options.o_id WHERE papers.question = questions.q_id AND papers.paper = ? AND q_type != 'info' ORDER BY screen, display_pos, id_num");
        $result->bind_param('i', $this->property_id);
        $result->execute();
        $result->bind_result($q_id, $q_type, $screen, $correct, $option_text, $score_method, $settings, $option_id);
        while ($result->fetch()) {
            if ($old_q_id != $q_id) {
                $question_no++;
                $paper_buffer[$question_no]['ID'] = $q_id;
                $paper_buffer[$question_no]['type'] = $q_type;
                $paper_buffer[$question_no]['screen'] = $screen;
                $old_correct = $paper_buffer[$question_no]['correct'] = QuestionUtils::fix_correct($q_type, $correct, '', $option_text);
                $paper_buffer[$question_no]['correct_text'] = "\t" . $option_text;
                $paper_buffer[$question_no]['score_method'] = $score_method;
                $paper_buffer[$question_no]['settings'] = $settings;
                $paper_buffer[$question_no]['option_id'] = $option_id; // Used to pull option metadata from questions that store their answers in a single option
            } else {
                // A seperate option for the same question as the last loop.
                $old_correct = $paper_buffer[$question_no]['correct'] = QuestionUtils::fix_correct($q_type, $correct, $old_correct, $option_text);
                $paper_buffer[$question_no]['correct_text'] .= "\t" . $option_text;
            }
            $old_q_id = $q_id;
        }
        $result->close();
        // Get random ids.
        $i = 0;
        foreach ($paper_buffer as $question) {
            if ($question['type'] == 'random') {
                $paper_buffer[$i]['rand_ids'] = random_utils::get_random_qids_for_question($question['ID'], $db);
            }
            $i++;
        }
        return $paper_buffer;
    }

    /**
     * Checks if the paper stores answers given by a student taking the paper.
     *
     * Peer review papers are excluded from this because although students are
     * giving answers they are acting as markers.
     *
     * @return boolean
     */
    protected function paper_stores_user_answers()
    {
        $stores_user_answers = array(
            '0' => '0', // Formative.
            '1' => '1', // Progress test.
            '2' => '2', // Summative.
            '3' => '3', // Survey.
        );
        return isset($stores_user_answers[$this->get_paper_type()]);
    }

    /**
     * Check if paper type is enabled.
     * @return bool
     */
    public function isEnabled(): bool
    {
        $type = array(
            assessment::TYPE_FORMATIVE => 'formative',
            assessment::TYPE_PROGRESS => 'progress',
            assessment::TYPE_SUMMATIVE => 'summative',
            assessment::TYPE_SURVEY => 'survey',
            assessment::TYPE_OSCE => 'osce',
            assessment::TYPE_OFFLINE => 'offline',
            assessment::TYPE_PEERREVIEW => 'peer_review'
        );

        if (!array_key_exists($this->get_paper_type(), $type)) {
            return false;
        }
        $checktype = $type[$this->get_paper_type()];
        $config = Config::get_instance();
        $settings = $config->get_setting('core', 'paper_types');
        if (isset($settings[$checktype])) {
            return $settings[$checktype];
        }
        return false;
    }

    /**
     * Get a setting for a paper
     * This is a wrapper funrion to PaperSettings->getSetting
     * @param string $setting the setting
     * @throws coding_exception
     * @return mixed
     */
    public function getSetting(string $setting)
    {
        return $this->papersettings->getSetting($setting);
    }

    /**
     * Update a setting
     * This is a wrapper funrion to PaperSettings->updateSetting
     * @param string $setting The name of the setting
     * @param string|array $value
     * @param integer $paper The paper to which this setting belongs
     * @throws coding_exception
     */
    public function updateSetting(string $setting, string $value, int $paper): void
    {
        $this->papersettings->updateSetting($setting, $value, $paper);
    }

    /**
     * Render paper settings by category
     * This is a wrapper funrion to PaperSettings->renderSettings
     * @param string $category the setting category
     */
    public function renderSettings(string $category = ''): void
    {
        $this->papersettings->renderSettings($category);
    }

    /**
     * Render paper settings by category
     * This is a wrapper funrion to PaperSettings->renderNewSettings
     * @param array $strings language strings
     * @param string $papertype the paper type
     * @param string $category the setting category
     */
    public static function renderNewSettings(array $strings, string $papertype, string $category = ''): void
    {
        PaperSettings::renderNewSettings($strings, $papertype, $category);
    }

    /**
     * Get the minimum time a paper should be available based on student accomodations.
     * @param int $duration exam duartion in minutes
     * @return int
     */
    public function getMinAvailability(int $duration = -1): int
    {
        $minduration = array();
        $calendar_year = $this->get_calendar_year();
        $modules = array_keys($this->get_modules());
        // Get the stored exam duartion if non provided.
        if ($duration === -1) {
            $duration = $this->get_exam_duration();
        }
        foreach ($modules as $module) {
            $members = module_utils::get_student_members($calendar_year, $module, $this->db);
            foreach ($members as $member) {
                $extra = UserUtils::getExtraTime($member['userID'], $this->db);
                if ($extra['extratime'] > 0) {
                    $extra['extratime'] = 1 + ($extra['extratime'] / 100);
                } else {
                    $extra['extratime'] = 1;
                }
                $totalexamtime = $duration * $extra['extratime'];
                if ($this->configObject->get_setting('core', 'paper_breaktime_mins')) {
                    $minduration[] =  $totalexamtime + (ceil($totalexamtime / 60) * $extra['breaktime']);
                } else {
                    if ($extra['breaktime'] > 0) {
                        $extra['breaktime'] = 1 + ($extra['breaktime'] / 100);
                    } else {
                        $extra['breaktime'] = 1;
                    }
                    $minduration[] = $totalexamtime * $extra['breaktime'];
                }
            }
        }
        if (empty($minduration)) {
            if (is_null($duration)) {
                $duration = 0;
            }
            $minavail = $duration;
        } else {
            $minavail = round(max($minduration));
        }
        return $minavail;
    }

    /**
     * Checks if results submitted now should be added to the late log.
     *
     * @param int|null $lab_id The id of the lab the exam is in.
     * @param \LogMetadata $log The metadata record for the user taking the exam.
     * @return bool
     */
    public function shouldLogLate(?int $lab_id, LogMetadata $log): bool
    {
        $log_late = false;

        switch ($this->get_paper_type()) {
            case assessment::TYPE_PROGRESS:
                $log_late = $this->shouldProgressLogLate();
                break;
            case assessment::TYPE_SUMMATIVE:
                $log_late = $this->shouldSummativeLogLate($lab_id, $log);
                break;
        }

        return $log_late;
    }

    /**
     * Tests if a progress test should log it's results as late.
     *
     * @return bool
     */
    protected function shouldProgressLogLate(): bool
    {
        $end_date = $this->get_end_date();
        if (is_null($end_date)) {
            return false;
        }
        return (time() > $end_date);
    }

    /**
     * Checks if summative exam answers should be logged late.
     *
     * @param int|null $lab_id The id of the lab that the user is in, or null if they are in no labs.
     * @param \LogMetadata $metadata
     * @return bool
     */
    protected function shouldSummativeLogLate(?int $lab_id, LogMetadata $metadata): bool
    {
        if (is_null($this->get_start_date())) {
            // Never log late if there is no start date set.
            return false;
        }

        // We want to give users on timed exams a grace period after the end of the exam before
        // answers are sent to the late log, so that it is not filled with people who have had
        // the timer force submit their results.
        $grace_period = 60;

        $timed = $this->display_timer();

        // Assume the paper end date.
        $end_date = $this->get_end_date();

        // Assume remaining time.
        $remaining = true;

        if ($timed) {
            $remaining_time = $this->calculateTimeRemaining($lab_id, $metadata, false, true);
            $remaining_time += $grace_period;
        }

        if ($timed and $this->getSetting('remote_summative')) {
            if (!is_null($remaining_time)) {
                $remaining = ($remaining_time > 0);
            }
        } elseif ($timed) {
            // Find out if the lab has a timer set on it.
            $log_lab_end_time = $this->getLogLabEndTime($lab_id);
            $lab_end_date = $log_lab_end_time->get_session_end_date_datetime();

            if ($lab_end_date !== false) {
                // Get the amount of special needs time.
                /* @var \UserObject $user */
                $user = UserObject::get_instance();
                $special_needs_time = ($this->get_exam_duration_sec() / 100) * $user->get_special_needs_percentage();

                // Get the invigilator assigned extra time.
                $log_extra_time = new LogExtraTime($log_lab_end_time, ['user_ID' => $user->get_user_ID()], $this->db);
                $extra_time_secs = $log_extra_time->get_extra_time_secs();

                $end_date = $lab_end_date->getTimestamp() + $extra_time_secs + $special_needs_time + $grace_period;
            } elseif (!is_null($remaining_time)) {
                $remaining = ($remaining_time > 0);
            }
        }

        $end_passed = time() > $end_date;

        return ($end_passed or !$remaining);
    }

    /**
     * Get the end time object for this paper in a specific lab.
     *
     * @param int|null $lab_id
     * @return \LogLabEndTime
     */
    public function getLogLabEndTime(?int $lab_id): LogLabEndTime
    {
        $labkey = $lab_id ?? 0;

        if (!isset($this->lab_end_cache[$labkey])) {
            $this->lab_end_cache[$labkey] = new LogLabEndTime($lab_id, $this, $this->db);
        }

        return $this->lab_end_cache[$labkey];
    }

    /**
     * Calculates the time remaining on a paper to the current user.
     *
     * @param int|null $lab_id The id of the lab that the user is in, or null if they are in no labs.
     * @param \LogMetadata $log
     * @param bool $preview True if the paper is being viewed in preview mode.
     * @param bool $allow_negative If false the minimum value is zero (default: false)
     * @return int|null The time in seconds, or null if it has no end time yet.
     * @throws \coding_exception
     */
    public function calculateTimeRemaining(?int $lab_id, LogMetadata $log, bool $preview, bool $allow_negative = false): ?int
    {
        $remaining_time = null;
        /* @var UserObject $user */
        $user = UserObject::get_instance();
        $special_needs_percentage = $user->get_special_needs_percentage();
        $is_remote = $this->getSetting('remote_summative');
        $is_summative = $this->get_paper_type() == assessment::TYPE_SUMMATIVE;
        $uses_labs = !empty($this->get_labs());

        if (!$is_remote and $is_summative and $uses_labs and !$preview) {
            // This timer path requires that labs are used and
            // that invigilators are responsible for setting the start and end time of exams.
            $log_lab_end_time = $this->getLogLabEndTime($lab_id);

            // Has the student been allotted extra time by an invigilator?
            $student_object['user_ID'] = $user->get_user_ID();
            $student_object['special_needs_percentage'] = $special_needs_percentage;
            $log_extra_time = new LogExtraTime($log_lab_end_time, $student_object, $this->db);

            // Do not time the exam if the invigilator has not clicked on the 'Start' button
            if ($log_lab_end_time->get_session_end_date_datetime() !== false) {
                $summative_timer = new SummativeTimer($log_extra_time);
                $remaining_time = $summative_timer->calculate_remaining_time_secs($allow_negative);
            }
        } else {
            if ($is_remote) {
                $timer = new RemoteSummativeTimer($log, $this->get_exam_duration(), $special_needs_percentage);
            } else {
                // Used for all other types of exams including summative exams that do not have
                // any labs assigned to them.
                $timer = new Timer($log, $this->get_exam_duration(), $special_needs_percentage);
            }

            if (!$timer->is_started()) {
                $timer->start();
            }

            $remaining_time = $timer->calculate_remaining_time($allow_negative);
        }

        return $remaining_time;
    }

    /**
     * Calculate the amount of break time available to the current user on the paper during a remote summative exam.
     *
     * @return int|null
     */
    public function calculateBreakTime(): ?int
    {
        $break_time = null;

        /* @var UserObject $user */
        $user = UserObject::get_instance();
        $userbreaks = $user->getRequiresBreaks();
        $remote = $this->getSetting('remote_summative');

        if ($remote and !empty($userbreaks) and $this->configObject->get_setting('core', 'paper_pause_exam')) {
            $special_needs_percentage = $user->get_special_needs_percentage();
            // Get available break time from database or calculate if none already used.
            $durationsecs = LogBreakTime::getBreak($user->get_user_ID(), $this->get_property_id());

            if ($durationsecs === -1) {
                $durationsecs = LogBreakTime::calculateBreakTime(
                    $this->get_exam_duration_sec(),
                    $special_needs_percentage,
                    $userbreaks
                );
            }
            $break_time = round($durationsecs);
        }

        return $break_time;
    }

    /**
     * Generate root paper css
     *
     * @param UserObject $userObject the user
     * @param string $bgcolor the paper back ground colour
     * @param string $fgcolor the paper font colour
     * @param string $textsize the paper text size
     * @param string $marks_color the question marks font colour
     * @param string $themecolor the paper theme section font colour
     * @param string $labelcolor the question labels font colour
     * @param string $font the paper font family
     * @param string $unanswered_color the unanswered question colour
     * @param string $dismiss_color the dimissed answer colour
     * @param string $paper_global_themecolour the system theme colour
     * @param string $paper_global_themefont_colour the system theme font colour
     * @param string $highlight_bgcolour the questioh highlight colour
     * @return string
     */
    public static function paperCss(
        UserObject $userObject,
        string $bgcolor = UserObject::BGCOLOUR,
        string $fgcolor = UserObject::FGCOLOUR,
        string $textsize = UserObject::TEXTSIZE,
        string $marks_color = UserObject::MARKSCOLOUR,
        string $themecolor = UserObject::THEMECOLOUR,
        string $labelcolor = UserObject::LABELCOLOUR,
        string $font = UserObject::FONT,
        string $unanswered_color = UserObject::UNANSWEREDCOLOUR,
        string $dismiss_color = UserObject::DISMISSCOLOUR,
        string $paper_global_themecolour = UserObject::GLOBALTHEMECOLOUR,
        string $paper_global_themefont_colour = UserObject::GLOBALTHEMEFONTCOLOUR,
        string $highlight_bgcolour = UserObject::HIGHLIGHTCOLOUR
    ): string {
        $bgcolor = $userObject->get_bgcolor($bgcolor);
        $fgcolor = $userObject->get_fgcolor($fgcolor);
        $textsize = $userObject->get_textsize($textsize);
        $marks_color = $userObject->get_marks_color($marks_color);
        $themecolor = $userObject->get_themecolor($themecolor);
        $labelcolor = $userObject->get_labelcolor($labelcolor);
        $font = $userObject->get_font($font);
        $unanswered_color = $userObject->get_unanswered_color($unanswered_color);
        $dismiss_color = $userObject->get_dismiss_color($dismiss_color);
        $paper_global_themecolour = $userObject->getPaperGlobalThemeColour($paper_global_themecolour);
        $paper_global_themefont_colour = $userObject->getPaperGlobalThemeFontcolour($paper_global_themefont_colour);
        $highlight_bgcolour = $userObject->getHighlightBackgroundColour($highlight_bgcolour);

        return '<style type="text/css">:root {--paper-global-themecolor: ' . $paper_global_themecolour
            . '; --paper-global-themefont-color: ' . $paper_global_themefont_colour
            . '; --paper-backgroundcolour: ' . $bgcolor
            . '; --paper-foregroundcolour: ' . $fgcolor
            . '; --paper-notecolour: ' . $labelcolor
            . '; --paper-markscolour: ' . $marks_color
            . '; --paper-themecolour: ' . $themecolor
            . '; --paper-dismisscolour: ' . $dismiss_color
            . '; --paper-unanswered: ' . $unanswered_color
            . '; --paper-highlightbgcolour: ' . $highlight_bgcolour
            . '; --paper-fontsize: ' . $textsize . '%'
            . '; --paper-font: ' . $font . ',sans-sarif;}</style>';
    }

    /**
     * Checks if the current user can edit the security settings of the paper.
     *
     * When summative management is enabled the Security settings on papers can only be edited by
     * 1. SysAdmins
     * 2. Admins, but only before the paper has been locked.
     *
     * @return bool
     */
    public function canEditSecurity(): bool
    {
        if ($this->get_paper_type() != assessment::TYPE_SUMMATIVE) {
            // Security can be edited by all users who can access that page on non-summative papers.
            return true;
        }

        $user = \UserObject::get_instance();

        if ($user->has_role(['SysAdmin'])) {
            // A SysAdmin can always edit the Security settings.
            return true;
        }

        if ($this->get_summative_lock()) {
            // User's can no longer change the security settings when a paper is locked.
            return false;
        }

        if ($user->has_role(['Admin'])) {
            // Admins can edit Security of summative papers when they are not locked.
            return true;
        }

        if (\Config::get_instance()->get_setting('core', 'cfg_summative_mgmt')) {
            // General users who can access the properties page cannot edit security settings
            // when summative management is enabled.
            return false;
        }

        return true;
    }

    /**
     * Check is the paper has had its grades finalised.
     *
     * @return bool
     */
    public function isGraded(): bool
    {
        if (!isset($this->graded)) {
            // Cache if the paper is graded, so we do not need to go back to the database multiple times.
            $gradebook = new gradebook($this->db);
            $this->graded = $gradebook->paper_graded($this->property_id);
        }

        return $this->graded;
    }
}
