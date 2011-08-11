  <?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Main class for core questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';
require_once 'touchstone_object.class.php';
require_once 'option.class.php';
require_once 'logger.class.php';

Class Question extends TouchStoneObject {

  public $id = -1;
  private $type = null;
  private $theme = '';
  private $scenario = '';
  private $scenario_plain = '';
  private $leadin = '';
  private $leadin_plain = '';
  private $notes = '';
  private $correct_fback = '';
  private $incorrect_fback = '';
  private $score_method = '';
  private $option_order = null;
  private $standards_setting = '';
  private $bloom = null;
  private $owner_id = null;
  private $media = '';
  private $media_width = 0;
  private $media_height = 0;
  private $teams = '';
  private $checkout_time = null;
  private $checkout_author_id = '';
  private $created = null;
  private $last_edited = null;
  private $locked = null;
  private $deleted = null;
  private $status = null;
  public $options = array();
  public $max_options = 20;
  
  // Imploded DB version of teams
  private $group = '';
  
  private $_user_id;
  private $_fields = array('type', 'theme', 'scenario', 'scenario_plain', 'leadin', 'leadin_plain', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'option_order', 'standards_setting', 'bloom', 'owner_id', 'media', 'media_width', 'media_height', 'group', 'checkout_time', 'checkout_author_id', 'created', 'last_edited', 'locked', 'deleted', 'status');
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'option_order', 'bloom', 'status');
  private $_required_fields = array('type', 'leadin', 'score_method', 'option_order', 'owner_id', 'status');
  private $_mysqli = null;
  private $_logger = null;
  private $_data = array();
  
  // These properties will be lazily loaded
  private $keywords = null;
  private $changes = null;
  
  // Facilitate tracking of changes to unified fields in the options
  private $_unified_field_modifications = array();
  
  // Map our 'nice' property names to the database fields and 'parts' in track changes
  private $_field_map = array('type' => 'q_type', 'option_order' => 'q_option_order', 'standards_setting' => 'std', 'owner_id' => 'ownerID', 'media' => 'q_media', 'media_width' => 'q_media_width', 'media_height' => 'q_media_height', 'group' => 'q_group', 'checkout_author_id' => 'checkout_authorID', 'created' => 'creation_date');
  private $_change_field_map = array('group' => 'teams');
  private $_pretty_names = array('type' => 'Type', 'leadin' => 'Lead-in', 'score_method' => 'Scoring Method', 'option_order' => 'Option Order', 'owner_id' => 'Owner', 'status' => 'Status');
  public static $types = array('blank' => 'Fill in the Blank', 'calculation' => 'calculation', 'dichotomous' => 'Dichotomous', 'extmatch' => 'Extended Matching', 'flash' => 'Flash', 'hotspot' => 'Image Hotspot', 'info' => 'Information Block', 'keyword_based' => 'Keyword Based', 'labelling' => 'Labelling', 'likert' => 'Likert Scale', 'matrix' => 'Matrix', 'mcq' => 'Multiple Choice', 'mrq' => 'Multiple Response', 'random' => 'Random', 'rank' => 'Ranking', 'sct' => 'Script COncordance', 'textbox' => 'Text Box', 'timedate' => 'Time / Date');
  
  /**
   * Create a new question object by either loading an existing question from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $user_id, $data = null) {
    // Store the database connection reference and current user
    $this->_mysqli = $mysqli;
    $this->_user_id = $user_id;
    
    // Array of references to the fields.  Allows succinct use of call_user_func_array for saving
    foreach($this->_fields as $field) {
      $this->_data[] = &$this->$field;
    }
    
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
      if (!$this->get_question()) {
        throw new DatabaseException('Error loading question data.');
      }
    } elseif ($data !== null) {
      throw new DataTypeException('Invalid question ID.');
    }
  }
  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save($clear_checkout = true) {
    $success = false;
    if ($this->_logger == null ) $this->_logger =  new Logger($this->_mysqli);
    
    $valid = $this->validate();
    
    if ($valid === true) {
      // Clear any existing checkout
      if ($clear_checkout) {
        $this->checkout_author_id = null;
        $this->checkout_time = null;
      }
      
      // Make sure plain versions of scenario and leadin are up to date
      $this->get_scenario_plain();
      $this->get_leadin_plain();
      
      
      // If $id is -1 we're inserting a new record
      if ($this->id == -1) {
        $params = array_merge(array('sssssssssssisisiississsss'), $this->_data);
        $query = <<< QUERY
INSERT INTO questions(q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method, 
q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, q_group, checkout_time, checkout_authorID, creation_date, 
last_edited, locked, deleted, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('sssssssssssisisiississsssi'), $this->_data, array(&$this->id));
        $this->last_edited = date("Y-m-d H:i:s");
        $query = <<< QUERY
UPDATE questions
SET q_type = ?, theme = ?, scenario = ?, scenario_plain = ?, leadin = ?, leadin_plain = ?, notes = ?, correct_fback = ?, incorrect_fback = ?, 
score_method = ?, q_option_order = ?, std = ?, bloom = ?, ownerID = ?, q_media = ?, q_media_width = ?, q_media_height = ?, q_group = ?, 
checkout_time = ?, checkout_authorID = ?, creation_date = ?, last_edited = ?, locked = ?, deleted = ?, status = ?
WHERE q_id = ?
QUERY;
      }
      $result = $this->_mysqli->prepare($query);
      call_user_func_array (array($result,'bind_param'), $params);
      $result->execute();
      $success = ($result->affected_rows > 0);
      
      if ($success) {
        if ($this->id == -1) {
          $this->id = $this->_mysqli->insert_id;
          $logger->track_change('New Question', $this->question_id, $this->_user_id, $this->text, '', '');
        } else {
          // Log any changes
          foreach($this->_modified_fields as $field => $value) {
            $db_field = (in_array($field, array_keys($this->_field_map))) ? $this->_field_map[$field] : $field;
            $change_field = (in_array($field, array_keys($this->_change_field_map))) ? $this->_change_field_map[$field] : $field;
            $this->_logger->track_change('Edit Question', $this->id, $this->_user_id, $value, $this->$field, $change_field);
          }
        }
      }
      $result->close();
            
      if ($success) {
        $success = $this->save_options();
      }
      
      $this->_modified_fields = array();
    } else {
      throw new ValidationException($valid);
    }
    
    return $success;
  }
  
  /**
   * Check out the question for editing
   * @param int $user_id ID of the user who is currently editing the question
   * @return boolean Success or failure of the checkout operation
   */
  public function checkout($user_id) {
    $success = false;
    
    $this->checkout_author_id = $user_id;
    $this->checkout_time = date ("Y-m-d H:i:s");
    
    $u_query = <<< QUERY
UPDATE questions
SET checkout_time = ?, checkout_authorID = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($u_query);
    $result->bind_param('sii', $this->checkout_time, $this->checkout_author_id, $this->id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
  
  /**
   * Lock the question, e.g. when a summative paper has started
   * @return boolean Success or failure of the lock operation
   */
  public function lock() {
    $success = false;
    
    $this->locked = date ("Y-m-d H:i:s");
    
    $u_query = <<< QUERY
UPDATE questions
SET locked = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($u_query);
    $result->bind_param('si', $this->locked, $this->id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
  
  /**
   * Add a change to a unified field. This is a field that is the same across all options and so changes are logged at the question level 
   * @param unknown_type $label
   * @param unknown_type $old_value
   * @param unknown_type $new_value
   */
  public function add_unified_field_modification($field, $label, $old_value, $new_value) {
    if (!in_array($field, $this->_unified_field_modifications)) {
      $this->_unified_field_modifications[$field] = array($label, $old_value, $new_value);
    }
  }
  
  // ACCESSORS
  
  /**
   * Get the question type
   * @return string
   */
  public function get_type() {
    return $this->type;
  }
  
  /**
   * Set the question type
   * @param string $value
   */
  public function set_type($value) {
    $this->type = $value;
  }
  
  /**
   * Get the question theme
   * @return string
   */
  public function get_theme() {
    return $this->theme;
  }
  
  /**
   * Set the question theme
   * @param string $value
   */
  public function set_theme($value) {
    if ($value != $this->theme) {
      $this->set_modified_field('theme', $this->theme);
      $this->theme = $value;
    }
  }
  
  /**
   * Get the question scenario
   * @return string
   */
  public function get_scenario() {
    return $this->scenario;
  }
  
  /**
   * Set the question scenario
   * @param string $value
   */
  public function set_scenario($value) {
    $scenario = (trim(strip_tags($value)) == '') ? '' : $value;
    if ($scenario != $this->scenario) {
      $this->set_modified_field('scenario', $this->scenario);
      $this->scenario = $value;
    }
  }
  
	/**
   * Get the 'plain' version of the scenario, i.e. stripped of HTML and special characters
   * @return string
   */
  public function get_scenario_plain() {
    $this->scenario_plain = trim(strip_tags($this->scenario));
    return $this->scenario_plain;
  }
  
  /**
   * Get the question leadin
   * @return string
   */
  public function get_leadin() {
    return $this->leadin;
  }
  
  /**
   * Set the question leadin
   * @param string $value
   */
  public function set_leadin($value) {
    if ($value != $this->leadin) {
      $this->set_modified_field('leadin', $this->leadin);
      $this->leadin = $value;
    }
  }
  
  /**
   * Get the 'plain' version of the leadin, i.e. stripped of HTML and special characters
   * @return string
   */
  public function get_leadin_plain() {
    $this->leadin_plain = trim(strip_tags($this->leadin));
    return $this->leadin_plain;
  }
  
  /**
   * Get the question notes
   * @return string
   */
  public function get_notes() {
    return $this->notes;
  }
  
  /**
   * Set the question notes
   * @param string $value
   */
  public function set_notes($value) {
    if ($value != $this->notes) {
      $this->set_modified_field('notes', $this->notes);
      $this->notes = $value;
    }
  }
  
  /**
   * Get the question correct feedback
   * @return string
   */
  public function get_correct_fback() {
    return $this->correct_fback;
  }
  
  /**
   * Set the question correct feedback
   * @param string $value
   */
  public function set_correct_fback($value) {
    if ($value != $this->correct_fback) {
      $this->set_modified_field('correct_fback', $this->correct_fback);
      $this->correct_fback = $value;
    }
  }
  
  /**
   * Get the question incorrect feedback
   * @return string
   */
  public function get_incorrect_fback() {
    return $this->incorrect_fback;
  }
  
  /**
   * Set the question incorrect feedback
   * @param string $value
   */
  public function set_incorrect_fback($value) {
    if ($value != $this->incorrect_fback) {
      $this->set_modified_field('incorrect_fback', $this->incorrect_fback);
      $this->incorrect_fback = $value;
    }
  }
  
  /**
   * Get the question score method
   * @return string
   */
  public function get_score_method() {
    return $this->score_method;
  }
  
  /**
   * Set the question score method
   * @param string $value
   */
  public function set_score_method($value) {
    if ($value != $this->score_method) {
      $this->set_modified_field('score_method', $this->score_method);
      $this->score_method = $value;
    }
  }
  
  /**
   * Get the question option order
   * @return string
   */
  public function get_option_order() {
    return $this->option_order;
  }
  
  /**
   * Set the question option order
   * @param string $value
   */
  public function set_option_order($value) {
    if ($value != $this->option_order) {
      $this->set_modified_field('option_order', $this->option_order);
      $this->option_order = $value;
    }
  }
  
  /**
   * Get the question standards setting mark
   * @return float
   */
  public function get_standards_setting() {
    return $this->standards_setting;
  }
  
  /**
   * Set the question standards setting mark
   * @param integer $value
   */
  public function set_standards_setting($value) {
    if ($value != $this->standards_setting) {
      $this->set_modified_field('standards_setting', $this->standards_setting);
      $this->standards_setting = $value;
    }
  }
  
  /**
   * Get the question Bloom's Taxonomy setting
   * @return string
   */
  public function get_bloom() {
    return $this->bloom;
  }
  
  /**
   * Set the question Bloom's Taxonomy setting
   * @param string $value
   */
  public function set_bloom($value) {
    if ($value != $this->bloom) {
      $this->set_modified_field('bloom', $this->bloom);
      $this->bloom = $value;
    }
  }
  
  /**
   * Get the question owner ID
   * @return integer
   */
  public function get_owner_id() {
    return $this->owner_id;
  }
  
  /**
   * Set the question owner ID
   * @param integer $value
   */
  public function set_owner_id($value) {
    if ($value != $this->owner_id) {
      $this->set_modified_field('owner_id', $this->owner_id);
      $this->owner_id = $value;
    }
  }
  
  /**
   * Get the question media as an array containing filename, width and height
   * @return array
   */
  public function get_media() {
    return array('filename' => $this->media, 'width' => $this->media_width, 'height' => $this->media_height);
  }
  
  /**
   * Set the question media as an array containing filename, width and height
   * @param mixed $value Array containing filename, width and height
   */
  public function set_media($value) {
    if ($value != $this->media) {
      $this->set_modified_field('media', $this->media);
      $this->media = $value['filename'];
      $this->media_width = (empty($value['width'])) ? 0 : $value['width'];
      $this->media_height = (empty($value['height'])) ? 0 : $value['height'];
    }
  }
  
  /**
   * Get the teams to which the question belongs
   * @return array
   */
  public function get_teams() {
    if (!is_array($this->teams)) {
      $this->teams = ($this->group != '') ? explode(';', $this->group) : array();
    }
    
    return $this->teams;
  }
  
  /**
   * Set the group to which the question belongs
   * @param string $value
   */
  public function set_teams($value) {
    $this->get_teams();
    
    if (count(array_diff($this->teams, $value)) > 0) {
      $this->set_modified_field('group', $this->group);
      sort($value);
      $this->group = implode(';', $value);
      $this->teams = $value;
    }
  }
  
  /**
   * Get the question checkout time
   * @return datetime
   */
  public function get_checkout_time() {
    return $this->checkout_time;
  }
  
  /**
   * Set the question checkout time
   * @param datetime $value
   */
  public function set_checkout_time($value) {
    $this->checkout_time = $value;
  }
  
  /**
   * Get the user to whom the question is checked out
   * @return integer
   */
  public function get_checkout_author_id() {
    return $this->checkout_author_id;
  }
  
  /**
   * Set the user to whom the question is checked out
   * @param integer $value
   */
  public function set_checkout_author_id($value) {
    $this->checkout_author_id = $value;
  }
  
  /**
   * Get the time at which the question was created
   * @return datetime
   */
  public function get_created() {
    return $this->created;
  }
  
  /**
   * Get the time at which the question was last edited
   * @return datetime
   */
  public function get_last_edited() {
    return $this->last_edited;
  }
  
  /**
   * Get the time at which the question was locked, if set
   * @return datetime
   */
  public function get_locked() {
    return $this->locked;
  }
  
  /**
   * Get whether the question is set as deleted
   * @return boolean
   */
  public function get_deleted() {
    return $this->deleted;
  }
  
  /**
   * Get the status of the question
   * @return string
   */
  public function get_status() {
    return $this->status;
  }
  
  /**
   * Set the status of the question
   * @param string $value
   */
  public function set_status($value) {
    if ($value != $this->status) {
      $this->set_modified_field('status', $this->status);
      $this->status = $value;
    }
  }
  
  /**
   * Get the change history of the question 
   * @return array Associative array containing date, section, old value, new value and user for the change
   */
  public function get_changes() {
    if(!is_array($this->changes)) {
      $this->changes = array();
      // Load the changes into an array
      $result = $this->_mysqli->prepare("SELECT part, old, new, DATE_FORMAT(changed, '%d/%m/%Y') AS display_changed, title, initials, surname FROM (track_changes, users) WHERE track_changes.editor=users.id AND typeID=? ORDER BY changed DESC, users.id LIMIT 200");
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->bind_result($part, $old, $new, $display_changed, $title, $initials, $surname);
      while ($result->fetch()) {
        $this->changes[] = array('date' => $display_changed, 'section' => $part, 'old' => $old, 'new' => $new, 'user' => $title . ' ' . $initials . ' ' . $surname);
      }
    }
    
    return $this->changes;
  }
  
  public function get_keywords() {
    if(!is_array($this->keywords)) {
      $this->keywords = array();
      
      // Load the keywords into an array
      $result = $this->_mysqli->prepare("SELECT keywordID FROM keywords_question WHERE q_id=?");
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->bind_result($keyword_id);
      while ($result->fetch()) {
        $this->keywords[] = $keyword_id;
      }
    }
    
    return $this->keywords;
  }

  /**
   * Set the keywords for the question
   * @param unknown_type $value
   */
  public function set_keywords($value) {
    // Question class is not currently handling the persisting of keywords to the database
    $this->keywords = $value;
  }

  
  // STATIC METHODS
  
  /**
   * Get a list of questions for the given paper
   * @param int $paper_id
   * @return multitype: an array of question objects
   */
  public static function get_questions($paper_id) {
    //TODO: Get questions
    $questions = array();
    
    return $questions;
  }
  
  /**
   * Delete the question with the given ID. Will not actually delete the question from the database, just mark 
   * it as deleted
   * @param int $id
   * @return bool True or false depending on success or failure of the delete operation
   */
  public static function delete($id) {
    // TODO: Track changes
    $success = false;
    
    return Question::update_deletion_status($id, date ("Y-m-d H:i:s"));
  }
  
  /**
   * Restore a previously deleted question
   * @param int $id
   * @return bool True or false depending on success or failure of the restore operation
   */
  public static function restore($id) {
    $success = false;
    
    return Question::update_deletion_status($id, null);
  }
  
  /**
   * Build an array of question objects with options already in place. This will allow a bunch questions to be 
   * built up from a single query rather than requiring queries for each of the questions and their options
   * @param array $questions An array of question IDs to build
   * @return array An array of complete question objects 
   */
  public static function build($questions) {
    $qn_arr = array();
    
    return $qn_arr;
  }
  
  // PRIVATE METHODS
  
  /**
   * Get the actual data for the question and its options
   */
  private function get_question() {
    // Get the question
    $found = 0;
    $success = false;
    
    $q_query = <<< QUERY
SELECT q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method, q_option_order,
 std, bloom, ownerID, q_media, q_media_width, q_media_height, q_group, checkout_time, checkout_authorID, creation_date, last_edited,
 locked, deleted, status
FROM questions
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($q_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    if ($result->fetch()) {
      $success = true;
      $found = $result->num_rows;
    }
    $result->close();
    
    if ($found > 0) {
      // Build array of references to option data for use in call_user_func_array
      $opt_fields = Option::get_field_array();
      $opt_data = array();
      $params = array();
      $params[] = &$opt_data['id'];
      foreach($opt_fields as $field) {
        $params[] = &$opt_data[$field];
      }
      
      // Get the options
      $o_query = <<< QUERY
  SELECT id_num, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks
  FROM options
  WHERE o_id = ?
QUERY;
      $result = $this->_mysqli->prepare($o_query);
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->store_result();
      call_user_func_array(array($result, "bind_result"), $opt_data);
      // TODO: handle 'correctness' more nicely
      while($success == true and $success = $result->fetch()) {
        $this->options[$opt_data['id']] = new Option($this->_mysqli, $this->_user_id, $opt_data);
      }
      $x = 3;
    } else {
      throw new RecordNotFoundException('Question with ID ' . $this->id . ' not found.');
    }
    
    return ($success !== false);
  }
  
  /**
   * Validate the question object before saveing
   * @return Ambigous <boolean, string>
   */
  private function validate() {
    $rval = true;
    
    // If there are errors return an appropriate message
    $missing_fields = '';
    foreach($this->_required_fields as $req) {
      if (empty($this->$req)) $missing_fields .= $this->_pretty_names[$req] . ', ';
    }
    if ($missing_fields != '') {
      $rval = 'The following required fields have not been supplied: ' . rtrim($missing_fields, ', ');
    }
    
    return $rval;
  }
  
  /**
   * Save the options for this question, deleting any that are empty
   * @return boolean
   */
  private function save_options() {
    $success = true;
    
    // Call save() on the options too if successful
    $i = 1;
    foreach($this->options as $oid => $option)
    {
      $media = $option->get_media();
      if ($option->get_text() == '' and $media['filename'] == '') {
        $success = Option::delete($this->_mysqli, $this->_user_id, $oid, $i, $this->id);
        if ($success) {
          unset($this->options[$oid]);
        }
      } else {
        $option->save($i);
      }
      
      if (!$success) break;
      
      $i++;
    }
    
    if ($success) $this->log_unified_field_modifications();
    
    return $success;
  }
  
  private function log_unified_field_modifications() {
    foreach ($this->_unified_field_modifications as $mod) {
      $this->_logger->track_change('Edit Question', $this->id, $this->_user_id, $mod[1], $mod[2], $mod[0]);
    }
    $this->_unified_field_modifications = array();
  }

  /**
   * Perform delete or restore operation
   * @param int $id
   * @return bool True or false depending on success or failure of the operation
   */
  private static function update_deletion_status($id, $status) {
    $success = false;
    
    $d_query = <<< QUERY
UPDATE questions
SET deleted = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($d_query);
    $result->bind_param('i', $status, $id);
    $success = $result->execute();
    $result->close();
    
    return $success;
  }
}

?>