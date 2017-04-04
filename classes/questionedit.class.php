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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
Class QuestionEdit extends RogoObject {

  public $id = -1;
  protected $type = null;
  protected $theme = '';
  protected $scenario = '';
  protected $scenario_plain = '';
  protected $leadin = '';
  protected $leadin_plain = '';
  protected $notes = '';
  protected $correct_fback = '';
  protected $incorrect_fback = '';
  protected $score_method = 'Mark per Option';
  protected $display_method = '';
  protected $option_order = 'display order';
  protected $standards_setting = '';
  protected $bloom = null;
  protected $owner_id = null;
  protected $media = '';
  protected $media_width = 0;
  protected $media_height = 0;
  protected $teams = array();
  protected $checkout_time = null;
  protected $checkout_author_id = '';
  protected $created = null;
  protected $last_edited = null;
  protected $locked = null;
  protected $deleted = null;
  protected $status = -1;
  protected $settings = '';
  protected $guid = null;
  public $options = array();

  public $max_options = 20;
  protected $min_options = 1;
  public $max_stems = 0;
  protected $_answer_positive = 'y';
  protected $_answer_negative = 'n';

  protected $_allow_change_marking_method = true;
  protected $_allow_negative_marks = null;
  protected $_requires_media = false;
  protected $_requires_correction_intermediate = false;
  protected $_requires_html5 = false;
  protected $_allow_mapping = true;
  protected $_allow_correction = true;
  protected $_allow_new_options = false;
  protected $_use_bloom = true;

  protected $_user_id;
  protected $_fields = array('type', 'theme', 'scenario', 'scenario_plain', 'leadin', 'leadin_plain', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'display_method', 'option_order', 'standards_setting', 'bloom', 'owner_id', 'media', 'media_width', 'media_height', 'checkout_time', 'checkout_author_id', 'created', 'last_edited', 'locked', 'deleted', 'status', 'settings','guid');
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'display_method', 'option_order', 'bloom', 'status');
  protected $_fields_required = array('type', 'leadin', 'score_method', 'option_order', 'owner_id', 'status');
  protected $_fields_settings = array();
//  protected $_score_methods = array('Mark per Question', 'Mark per Option', 'Allow partial Marks', 'Bonus Mark');
  protected $_score_methods;
  protected $_display_methods = array();
  protected $_option_orders;
  protected $_mysqli = null;
  protected $_logger = null;
  protected $_data = array();

  // These properties will be lazily loaded
  protected $_keywords = null;
  protected $_changes = null;
  protected $_comments = null;
  protected $_allow_partial_marks = false;

  // Use with extreme caution
  // In most cases the interface will ignore this and not let you edit optons that may have been displayed to the user
  protected $_allow_option_edit = false;

  // These fields will be forced to the negative answer value. Useful for checkboxes that won't have a value posted if unset
  protected $_fields_force = array();

  // 'Unified' fields are set to the same value for all options
  protected $_fields_unified;
  protected $_unified_field_modifications = array();

  // These are the fields that are relevant for post-exam corrections
  protected $_fields_change = array('option_correct', 'option_marks_correct', 'option_marks_incorrect', 'option_marks_partial', 'correct_fback');

  // Map our 'nice' property names to the database fields and 'parts' in track changes
  protected $_field_map = array('type' => 'q_type', 'option_order' => 'q_option_order', 'standards_setting' => 'std', 'owner_id' => 'ownerID', 'media' => 'q_media', 'media_width' => 'q_media_width', 'media_height' => 'q_media_height', 'checkout_author_id' => 'checkout_authorID', 'created' => 'creation_date');
  protected $_change_field_map;
  protected $_pretty_names;
  public static $types = array('blank', 'calculation', 'dichotomous', 'extmatch', 'flash', 'hotspot', 'info', 'keyword_based', 'labelling', 'likert', 'matrix', 'mcq', 'mrq', 'random', 'rank', 'sct', 'textbox', 'true_false', 'area', 'enhancedcalc');

  // Always store English values in the database so need to look up score method against English version
  protected $_score_methods_db;

  // Refrence to array of localised language strings
  protected $_lang_strings = null;

  // A list of correction behaviours that will be called sequentially for the Correct operation
  protected $_correctors = array();

  /** @var string Language component name. */
  protected $langcomponent = 'classes/questionedit';
  /** @var array language strings */
  protected $langstrings; 

  /**
   * Create a new question object by either loading an existing question from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    // Store the database connection reference and current user
    $this->_mysqli = $mysqli;
    $this->_user_id = $userObj->get_user_ID();
    $this->_userObj = $userObj;
    $this->_lang_strings = $lang_strings;

    // Initialise language specific elements
    $this->_score_methods = array($this->_lang_strings['markperquestion'], $this->_lang_strings['markperoption']);
    $this->_option_orders = array('display order' => $this->_lang_strings['displayorder'], 'alphabetic' => $this->_lang_strings['alphabetic'], 'random' => $this->_lang_strings['random']);
    $this->_fields_unified = array('correct' => $this->_lang_strings['correctanswer'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
    $this->_change_field_map = array('scenario_plain' => 'scenario', 'leadin_plain' => 'leadin', 'correct' => $this->_lang_strings['correctanswer']);
    // TODO: check if some question types need 'Display Method' instead of 'Presentation'
    $this->_pretty_names = array('type' => $this->_lang_strings['type'], 'leadin' => $this->_lang_strings['leadin'], 'score_method' => $this->_lang_strings['markingmethod'], 'display_method' => $this->_lang_strings['presentation'], 'option_order' => $this->_lang_strings['optionorder'], 'owner_id' => $this->_lang_strings['owner'], 'status' => $this->_lang_strings['status']);

    $this->_score_methods_db = array($this->_lang_strings['markperquestion'] => 'Mark per Question', $this->_lang_strings['markperoption'] => 'Mark per Option', $this->_lang_strings['allowpartial'] => 'Allow partial Marks', $this->_lang_strings['bonusmark'] => 'Bonus Mark');
    $this->_blooms_db = array('' => '', $this->_lang_strings['knowledge'] => 'Knowledge', $this->_lang_strings['comprehension'] => 'Comprehension', $this->_lang_strings['application'] => 'Application', $this->_lang_strings['analysis'] => 'Analysis', $this->_lang_strings['synthesis'] => 'Synthesis', $this->_lang_strings['evaluation'] => 'Evaluation');

    $langpack = new \langpack();
    $this->langstrings = $langpack->get_all_strings($this->langcomponent);
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
        throw new DatabaseException($this->_lang_strings['questionloaderror']);
      }
    } elseif ($data !== null) {
      throw new DataTypeException($this->_lang_strings['questioninvalid']);
    }
  }

  /**
   * Populate the 'standard' fields for this question
   * @param array $fields list of fields to populate
   * @param array $data source from which to extract field data, normally the $_POST array
   * @param array $exclude a list of fields to exclude from the population process
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   */
  public function populate($fields, $data, $exclude=array(), $prefix='') {
    foreach($fields as $section_name) {
      if (count($this->_fields_force) > 0 and !isset($data[$section_name]) and in_array($section_name, $this->_fields_force)) {
        $data[$section_name] = $this->_answer_negative;
      }

      if(!in_array($section_name, $exclude) and isset($data[$section_name])) {
        $value = $data[$section_name];


        // TODO: what does this do in light of marking changes?
        if ($section_name == 'score_method' and isset($data['other']) and $data['other'] == 1) $value = 'other';

        $method = "set_$section_name";
        $this->$method($value);
      }
    }
  }

  /**
   * Populate media for this question
   * @param string $field name of the field to use in $media_data array
   * @param array $media_data the data source for the media information, normally the $_FILES array
   * @param array $deletion_data the data source for flagging media to be deleted, normally the $_POST array
   */
  public function populate_media($field, $media_data, $deletion_data) {
    if (is_array($media_data) and count($media_data) > 0) {
      $old_media = $this->get_media();
      if ($media_data[$field]['name'] != $old_media['filename'] and ($media_data[$field]['name'] != 'none' and $media_data[$field]['name'] != '')) {
        if ($old_media['filename'] != '') {
          deleteMedia($old_media['filename']);
        }
        $this->set_media(uploadFile($field));
      } else {
        // Delete existing media if asked
        if (isset($deletion_data['delete_media0']) and $deletion_data['delete_media0'] == 'on') {
          deleteMedia($old_media['filename']);
          $this->set_media(array('filename' => '', 'width' => 0, 'height' => 0));
        }
      }
    }
  }

  /**
   * Populate the 'compound' fields for this question. These fields are a concatenated version of number of form fields
   * @param array $fields list of fields to populate
   * @param array $data source from which to extract field data, normally the $_POST array
   * @param array $exclude a list of fields to exclude from the population process
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   */
  public function populate_compound($fields, $data, $exclude=array(), $prefix='') {
    foreach ($fields as $section_name) {
      if (!in_array($section_name, $exclude)) {
        $get_method = "get_all_{$section_name}s";
        $original_vals = $this->$get_method();
        for ($i = 1; $i <= $this->max_stems; $i++) {
          $old_val = (isset($original_vals[$i - 1])) ? $original_vals[$i - 1] : '';
          if (isset($data["{$prefix}{$section_name}{$i}"]) and $data["{$prefix}{$section_name}{$i}"] != '') {
            ${$section_name}[] = $data["{$prefix}{$section_name}{$i}"];
            if (!isset($old_val) or $data["{$prefix}{$section_name}{$i}"] != $old_val) {
              $this->add_unified_field_modification($section_name . $i, $section_name . $i, $old_val, $data["{$prefix}{$section_name}{$i}"], $this->_lang_strings['editscenario']);
            }
          } else {
            if (isset($old_val) and $old_val != '') {
              $this->add_unified_field_modification($section_name . $i, $section_name . $i, $old_val, '', $this->_lang_strings['editscenario']);
            }
            ${$section_name}[] = '';
          }
        }
        $method = "set_all_{$section_name}s";
        $this->$method($$section_name);
      }
    }
  }

  /**
   * Populate 'compound' media for this question. These fields are a concatenated version of number of form fields.
   * Assumes the the first item in the compound field will be the general question media
   * @param array $media_data the data source for the media information, normally the $_FILES array
   * @param array $deletion_data the data source for flagging media to be deleted, normally the $_POST array
   * @param string $general_field name of the field to use for the general question details media
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   */
  public function populate_compound_media($media_data, $deletion_data, $general_field='q_media', $prefix='question_media') {
    $old_media = $this->get_all_media();
    $media_change = false;
    for ($i = 0; $i <= $this->max_stems; $i++) {
      $post_field = ($i == 0) ? $general_field : "{$prefix}$i";

      $media_name = (isset($old_media['filenames'][$i])) ? $old_media['filenames'][$i] : '';
      if ($media_data[$post_field]['name'] != $media_name and ($media_data[$post_field]['name'] != 'none' and $media_data[$post_field]['name'] != '')) {
        if ($media_name != '') {
          deleteMedia($media_name);
        }
        $new_media = uploadFile($post_field);
        $old_media['filenames'][$i] = $new_media['filename'];
        $old_media['widths'][$i] = $new_media['width'];
        $old_media['heights'][$i] = $new_media['height'];
        $this->add_unified_field_modification('q_media' . $i, 'q_media' . $i, $old_media['filenames'][$i], $new_media['filename'], $this->_lang_strings['editscenario']);
      } else {
        // Delete existing media if asked
        if (isset($deletion_data["delete_media$i"]) AND $deletion_data["delete_media$i"] == 'on') {
          deleteMedia($media_name);
          $this->add_unified_field_modification('q_media' . $i, 'q_media' . $i, $media_name, '', $this->_lang_strings['mediadeleted']);
          $old_media['filenames'][$i] = '';
          $old_media['widths'][$i] = 0;
          $old_media['heights'][$i] = 0;
        }
      }
    }
    $this->set_all_media($old_media);
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

      $this->serialize_settings();

      if ($this->bloom == '') {
        $this->bloom = null;
      }
			
			// If $id is -1 we're inserting a new record
      if ($this->id == -1) {
        $this->created = date ('Y-m-d H:i:s');
        $this->last_edited = date ('Y-m-d H:i:s');
        $server_ipaddress = str_replace('.', '', NetworkUtils::get_server_address());
        $this->guid = $server_ipaddress . uniqid('', true);
        $params = array_merge(array('ssssssssssssssissssisssssss'), $this->_data);
        $query = <<< QUERY
INSERT INTO questions (q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method,
display_method, q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, checkout_time, checkout_authorID,
creation_date, last_edited, locked, deleted, status, settings, guid)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('ssssssssssssssissssisssssssi'), $this->_data, array(&$this->id));
        $this->last_edited = date('Y-m-d H:i:s');
        $query = <<< QUERY
UPDATE questions
SET q_type = ?, theme = ?, scenario = ?, scenario_plain = ?, leadin = ?, leadin_plain = ?, notes = ?, correct_fback = ?, incorrect_fback = ?,
score_method = ?, display_method = ?, q_option_order = ?, std = ?, bloom = ?, ownerID = ?, q_media = ?, q_media_width = ?, q_media_height = ?,
checkout_time = ?, checkout_authorID = ?, creation_date = ?, last_edited = ?, locked = ?, deleted = ?, status = ?, settings = ?, guid = ?
WHERE q_id = ?
QUERY;
      }
      $result = $this->_mysqli->prepare($query);
      call_user_func_array (array($result,'bind_param'), $params);
      $result->execute();
      $success = ($result->affected_rows > -1);
      if ($this->_mysqli->error) {
        echo $this->langstrings['showerror'] . "<br >";
      }
      if ($success) {
        if ($this->id == -1) {
          $this->id = $this->_mysqli->insert_id;
          $this->_logger->track_change('New Question', $this->id, $this->_user_id, $this->get_leadin(), '', '');
        } else {
          // Log any changes
          foreach ($this->_modified_fields as $key => $value) {
            $db_field = (in_array($key, array_keys($this->_field_map))) ? $this->_field_map[$key] : $key;
            $change_field = (in_array($db_field, array_keys($this->_change_field_map))) ? $this->_change_field_map[$db_field] : $db_field;
            // Exception for media as it returns an array. Need better solution if other properties do the same in the future
            $get_method = 'get_' . $key . (($key == 'media') ? '_filename' : '');
            if ($value['message'] == '') {
              $this->_logger->track_change($this->_lang_strings['editquestion'], $this->id, $this->_user_id, $value['value'], $this->$get_method(), $change_field);
            } else {
              $this->_logger->track_change($value['message'], $this->id, $this->_user_id, $value['value'], $this->$get_method(), $change_field);
            }
          }
        }
      }
      $result->close();

      if ($success) {
        // Updates the teams/question modules
        QuestionUtils::update_modules($this->teams, $this->id, $this->_mysqli, $this->_userObj);
      }

      if ($success) {
        $success = $this->save_options();
      }

      $this->_modified_fields = array();
    } else {
      throw new ValidationException($valid);
    }

    return $success;
  }

  public function clear_checkout() {
    $success = false;

    $this->checkout_author_id = null;
    $this->checkout_time = null;

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
   * Check out the question for editing
   * @param int $user_id	- ID of the user who is currently editing the question
   * @return boolean  		- Success or failure of the checkout operation
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
   * @param string $label
   * @param string $old_value
   * @param string $new_value
   * @param string $category
   */
  public function add_unified_field_modification($field, $label, $old_value, $new_value, $category = null) {
    $category = ($category == null) ? $this->_lang_strings['editquestion'] : $category;

    if (!in_array($field, $this->_unified_field_modifications)) {
      $this->_unified_field_modifications[$field] = array($category, $label, $old_value, $new_value);
    }
  }

  /**
   * Does this question type use Bloom's Taxonomy?
   * @return boolean
   */
  public function use_bloom() {
    return $this->_use_bloom;
  }

  /**
   * Does this question type allow changes to the correct answer after it is locked?
   * @return boolean
   */
  public function allow_correction() {
    return $this->_allow_correction;
  }

  /**
   * Does this question type allow for the addition of new oiptions when locked?
   * @return boolean
   */
  public function allow_new_options() {
    return $this->_allow_new_options;
  }

  /**
   * Add the default correction behaviour based on the type of question
   * @throws ClassNotFoundException
   */
  public function add_default_correction_behaviours($cfg_web_root) {
    $file_base = 'behaviours/corrections/';
    $classdetails = array();

    if ($this->allow_correction()) {
      $classdetails[] = array('file' => $file_base . 'MARKSCorrector.class.php', 'name' => 'MARKSCorrector');
      $type = strtoupper($this->get_type());
      $classdetails[] = array('file' => $file_base . $type . 'Corrector.class.php', 'name' => $type . 'Corrector');
    } else {
      $classdetails[] = array('file' => $file_base . 'NullCorrector.class.php', 'name' => 'NullCorrector');
    }
    foreach ($classdetails as $class) {
      $classname = '';
      try {
        include $class['file'];
        $classname = $class['name'];
        $correction_object = new $classname($this->_mysqli, $this->_lang_strings, $this);
        $this->add_corrector($correction_object);
      } catch (Exception $ex) {
        throw new ClassNotFoundException(sprintf($this->lang_strings['noclasserror'], $classname));
      }
    }
  }

  /**
   * Add a new correction behaviour to the list
   * @param $correction_object
   */
  public function add_corrector($correction_object) {
    $this->_correctors[] = $correction_object;
  }

  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    if ($paper_id == -1) {  // No valid Paper ID, we can't remark anything.
      return;
    }
        
    $paper_type = $this->get_paper_type($paper_id);
    if ($paper_type == -1) $paper_type = 2;

    $errors = array();
    $changes = false;

    foreach ($this->_correctors as $corrector) {
      $tmp_errors = $corrector->execute($new_correct, $paper_id, $changes, $paper_type);
      if (count($tmp_errors) > 0) {
        array_merge($errors, $tmp_errors);
      }
    }

    return $errors;
  }

  /**
   * Does this question type require a media upload?
   * @return boolean
   */
  public function requires_media() {
    return $this->_requires_media;
  }

  /**
   * Does this question type require an intermediate screen when making corrections?
   * @return boolean
   */
  public function requires_correction_intermediate() {
    return $this->_requires_correction_intermediate;
  }

  /**
   * Does this question type require the html5 JavaScript includes?
   * @return boolean
   */
  public function requires_html5() {
    return $this->_requires_html5;
  }

  /**
   * Does this question type allow the marking method to be changed?
   * @return boolean
   */
  public function allow_change_marking_method() {
    return $this->_allow_change_marking_method;
  }

  /**
   * Does this question type allow partial parking?
   * @return boolean
   */
  public function allow_partial_marks() {
    return $this->_allow_partial_marks;
  }

  /**
   * Does this question type allow options to be edited after questions are locked
   * Hint: This should almost NEVER happen
   * @return boolean
   */
  public function allow_option_edit() {
    return $this->_allow_option_edit;
  }

  /**
   * Does this question type allow negative marking?  Check all the modules that the question is on
   * @return boolean
   */
  public function allow_negative_marks() {

    // Check all the modules that the question is on
    $moduleIds = implode(',',array_keys($this->teams));
    if ($moduleIds != '') {
      $result = $this->_mysqli->prepare("SELECT neg_marking FROM modules WHERE id IN (" . $moduleIds . ") AND neg_marking = 0");
      $result->execute();
      $result->store_result();
      if ($result->num_rows > 0) {
        $this->_allow_negative_marks = false;
      } else {
        $this->_allow_negative_marks = true;
      }
      $result->close();
    }
    
    return $this->_allow_negative_marks;
  }

  /**
   * Does this question type allow mapping to learning outcomes?
   * @return boolean
   */
  public function allow_mapping() {
    return $this->_allow_mapping;
  }

  public function get_question_number($paper_id) {
    $number = '';

    if (ctype_digit($paper_id)) {
      $pos = 0;

      $pos_query = <<< QUERY
SELECT p.display_pos FROM papers p WHERE p.question = ? AND p.paper = ? ORDER BY p.display_pos ASC;
QUERY;
      $result = $this->_mysqli->prepare($pos_query);
      $result->bind_param('ii', $this->id, $paper_id);
      $result->execute();
      $result->store_result();
      $result->bind_result($pos);
      $result->fetch();
      $result->close();

      if ($pos > 0) {
        $info_query = <<< QUERY
SELECT count(p.p_id) AS info FROM papers p INNER JOIN questions q ON p.question = q.q_id WHERE p.paper = ? AND p.display_pos < ? AND q.q_type = 'info';
QUERY;
        $result = $this->_mysqli->prepare($info_query);
        $result->bind_param('ii', $paper_id, $pos);
        $result->execute();
        $result->store_result();
        $result->bind_result($info);
        $result->fetch();
        $result->close();

        $number = $pos - $info;
      } else {
        $num_query = <<< QUERY
SELECT count(p.p_id) AS pos FROM papers p INNER JOIN questions q ON p.question = q.q_id WHERE p.paper = ? AND q.q_type <> 'info';
QUERY;
        $result = $this->_mysqli->prepare($num_query);
        $result->bind_param('i', $paper_id);
        $result->execute();
        $result->store_result();
        $result->bind_result($pos);
        $result->fetch();
        $result->close();

        $number = $pos + 1;
      }
    }

    return $number;
  }

  /**
   * How many summative papers, apart from the current paper, is this question on?
   * @param $paper_id ID of current paper, if any
   * @return int
   */
  public function get_other_summative_count($paper_id) {
    $count_query = <<< QUERY
SELECT COUNT(pr.property_id) FROM papers pa INNER JOIN properties pr ON pa.paper = pr.property_id WHERE pr.paper_type = '2' AND pa.question = ? AND pr.property_id <> ? GROUP BY pa.question ORDER BY count(pr.property_id) DESC;
QUERY;
    $result = $this->_mysqli->prepare($count_query);
    $result->bind_param('ii', $this->id, $paper_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($p_count);
    $result->fetch();
    $result->close();

    $p_count = (isset($p_count)) ? $p_count : 0;

    return $p_count;
  }

  // ACCESSORS

  /**
   * The the array of fields (properties) for this class
   * @return multitype:string
   */
  public function get_editable_fields() {
    return $this->_fields_editable;
  }

  /**
   * The the array of unified fields (properties) for this class
   * @return multitype:string
   */
  public function get_unified_fields() {
    return $this->_fields_unified;
  }

  /**
   * The the array of fields (properties) that are relevant for post-exam corrections for this class
   * @return multitype:string
   */
  public function get_change_fields() {
    return $this->_fields_change;
  }

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
    $this->theme = $this->replace_mee_div($this->theme);
    return $this->theme;
  }

  /**
   * Set the question theme
   * @param string $value
   */
  public function set_theme($value) {
    $value = $this->replace_tex($value);
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
    $scenario = (trim(strip_tags($value, '<img>')) == '') ? '' : $value;
    $tmp_scenario = trim($this->scenario);
    if ($scenario != $tmp_scenario) {
      $this->set_modified_field('scenario_plain', $this->get_scenario_plain());
      $this->scenario = $scenario;
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
      $this->set_modified_field('leadin_plain', $this->get_leadin_plain());
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
    $this->notes = $this->replace_mee_div($this->notes);
    return $this->notes;
  }

  /**
   * Set the question notes
   * @param string $value
   */
  public function set_notes($value) {
    $value = $this->replace_tex($value);
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
    $this->correct_fback = $this->replace_mee_div($this->correct_fback);
    return $this->correct_fback;
  }

  /**
   * Set the question correct feedback
   * @param string $value
   */
  public function set_correct_fback($value) {
    $value = $this->replace_tex($value);
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
    $this->incorrect_fback = $this->replace_mee_div($this->incorrect_fback);
    return $this->incorrect_fback;
  }

  /**
   * Set the question incorrect feedback
   * @param string $value
   */
  public function set_incorrect_fback($value) {
    $value = $this->replace_tex($value);
    if ($value != $this->incorrect_fback) {
      $this->set_modified_field('incorrect_fback', $this->incorrect_fback);
      $this->incorrect_fback = $value;
    }
  }

  /**
   * Get the question score method as an integer
   * @return string
   */
  public function get_score_method($style='string') {
    if ($style != 'string') {
      return array_search(array_search($this->score_method, $this->_score_methods_db), $this->_score_methods);
    } else {
      return array_search($this->score_method, $this->_score_methods_db);
    }
  }

  /**
   * Set the question score method
   * @param string $value
   */
  public function set_score_method($value) {
    $value_en = $this->_score_methods_db[$this->_score_methods[$value]];
    if ($value_en != $this->score_method) {
      $this->set_modified_field('score_method', array_search($this->score_method, $this->_score_methods_db));
      $this->score_method = $value_en;
    }
  }

  /**
   * Get the question display method
   * @return string
   */
  public function get_display_method() {
    return $this->display_method;
  }

  /**
   * Set the question display method
   * @param string $value
   */
  public function set_display_method($value) {
    if ($value != $this->display_method) {
      $this->set_modified_field('display_method', $this->display_method);
      $this->display_method = $value;
    }
  }

  /**
   * Return the scoring methods questions. The array may be overridden in sub-classes that do not support certain marking styles
   * @return array array of scoring method strings
   */
  public function get_score_methods() {
    return $this->_score_methods;
  }

  /**
   * Return the display methods of this question. The array is expected to be overridden in sub-classes
   * @return array array of display method key => value strings
   */
  public function get_display_methods() {
    return $this->_display_methods;
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
   * Return the option orders available for this question
   * @return array array of scoring method key => value strings
   */
  public function get_option_orders() {
    return $this->_option_orders;
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
    return array_search($this->bloom, $this->_blooms_db);
  }

  /**
   * Set the question Bloom's Taxonomy setting
   * @param string $value
   */
  public function set_bloom($value) {
    $value_en = $this->_blooms_db[$value];
    if ($value != $this->bloom) {
      $this->set_modified_field('bloom', array_search($this->bloom, $this->_blooms_db));
      $this->bloom = $value_en;
    }
		
		if ($this->bloom == '') {
			$this->bloom = NULL;
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
   * Get the question media filename only
   * @return array
   */
  public function get_media_filename() {
    return $this->media;
  }

  /**
   * Set the question media as an array containing filename, width and height
   * @param mixed $value Array containing filename, width and height
   */
  public function set_media($value) {
    if ($value['filename'] != $this->media) {
      $this->set_modified_field('media', $this->media);
      $this->media = $value['filename'];
      $this->media_width = (empty($value['width'])) ? 0 : $value['width'];
      $this->media_height = (empty($value['height'])) ? 0 : $value['height'];
    }
  }

  /**
   * Get the groups (imploded version of teams) to which the question belongs
   * @return array
   */
  protected function get_group() {
    return implode(';',$this->get_teams());
  }

  /**
   * Get the teams to which the question belongs
   * @return array
   */
  public function get_teams() {
    return $this->teams;
  }

  /**
   * Set the modules/teams to which the question belongs
   * @param array $value
   */
  public function set_teams($value) {

    // Sort the arrays so that we can compare directly. Should have few members so overhead will be small
    asort($this->teams);
    asort($value);

    if (count($this->teams) != count($value) or $this->teams != $value) {
      $this->set_modified_field('teams', $this->teams);
      $this->teams = $value;
    }
  }

  /**
   * Get the question checkout time
   * @return datetime
   */
  public function get_checkout_time($format = 'string') {
    if ($format == 'timestamp') {
      return strtotime($this->checkout_time);
    } else {
      return $this->checkout_time;
    }
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
   * Get the name of the user to whom the question is checked out
   * @return integer
   */
  public function get_checkout_author_name() {
    $name = '<unknown>';

    if ($editor = $this->_mysqli->prepare("SELECT title, initials, surname FROM users WHERE id=?")) {
      $editor->bind_param('s', $this->checkout_author_id);
      $editor->execute();
      $editor->bind_result($title, $initials, $surname);
      $editor->store_result();
      $editor->fetch();
      if($editor->num_rows !== 0) {
        $name = $title . ' ' . $initials . ' ' . $surname;
      }
      $editor->close();
    }

    return $name;
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
  public function get_created($format = 'string') {
    if ($format == 'timestamp') {
      return strtotime($this->created);
    } else {
      return $this->created;
    }
  }

  /**
   * Get the time at which the question was last edited
   * @return datetime
   */
  public function get_last_edited($format = 'string') {
    if ($format == 'timestamp') {
      return strtotime($this->last_edited);
    } else {
      return $this->last_edited;
    }
  }

  /**
   * Get the time at which the question was locked, if set
   * @return datetime
   */
  public function get_locked($format = 'string') {
    if ($format == 'timestamp') {
      return strtotime($this->locked);
    } else {
      return $this->locked;
    }
  }

  /**
   * Get whether the question is set as deleted
   * @return boolean
   */
  public function get_deleted($format = 'string') {
    if ($format == 'timestamp') {
      return strtotime($this->deleted);
    } else {
      return $this->deleted;
    }
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
   * Get the positive answer for this question
   * @return string
   */
  public function get_answer_positive() {
    return $this->_answer_positive;
  }

  /**
   * Get the negative answer for this question
   * @return string
   */
  public function get_answer_negative() {
    return $this->_answer_negative;
  }

  /**
   * Get the change history of the question, lazily loaded
   * @return array Associative array containing date, section, old value, new value and user for the change
   */
  public function get_changes() {
    if ($this->id == -1) {
      return array();
    }

    if (!is_array($this->_changes)) {
      $this->_changes = array();
      // Load the changes into an array
      $result = $this->_mysqli->prepare("SELECT type, part, old, new, DATE_FORMAT(changed, '%d/%m/%Y') AS display_changed, title, initials, surname FROM (track_changes, users) WHERE track_changes.editor=users.id AND typeID=? ORDER BY changed DESC, users.id LIMIT 200");
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->bind_result($type, $part, $old, $new, $display_changed, $title, $initials, $surname);
      while ($result->fetch()) {
        $this->_changes[] = array('date' => $display_changed, 'action' => $type, 'section' => $part, 'old' => $old, 'new' => $new, 'user' => $title . ' ' . $initials . ' ' . $surname);
      }
      $result->close();
    }

    return $this->_changes;
  }

  /**
   * Get the keywords for the question, lazily loaded
   * @return array Array of keyword IDs
   */
  public function get_keywords() {
    if (!is_array($this->_keywords)) {
      $this->_keywords = array();

      // Load the keywords into an array
      $result = $this->_mysqli->prepare("SELECT keywordID FROM keywords_question WHERE q_id=?");
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->bind_result($keyword_id);
      while ($result->fetch()) {
        $this->_keywords[] = $keyword_id;
      }
      $result->close();
    }

    return $this->_keywords;
  }

  /**
   * Set the keywords for the question
   * @param unknown_type $value
   */
  public function set_keywords($value) {
    // Question class is not currently handling the persisting of keywords to the database
    $this->_keywords = $value;
  }

  /**
   * Get external examiner comments on the question. Lazily loaded.
   * @param unknown_type $paper_id
   * @return array Array of comments indexed by comment ID and containing paper ID, category,  comment text, date, reviewer name, action, response and type
   */
  public function get_comments($paper_id = -1) {
    if ($this->id == -1) {
      return array();
    }

    if(!is_array($this->_comments)) {
      $this->_comments = array();

      if ($paper_id != -1) {
        $query = <<< QUERY
SELECT paper_title, review_comments.id, category, comment, started, title, initials, surname, action, response, review_type
FROM (review_metadata, review_comments, users) LEFT JOIN properties ON review_metadata.paperID = properties.property_id
WHERE review_metadata.id = review_comments.metadataID AND reviewerID = users.id AND q_id = ? AND paperID = ?
ORDER BY surname
QUERY;
        $result = $this->_mysqli->prepare($query);
        $result->bind_param('ii', $this->id, $paper_id);
      } else {
        $query = <<< QUERY
SELECT paper_title, review_comments.id, category, comment, started, title, initials, surname, action, response, review_type
FROM (review_metadata, review_comments, users) LEFT JOIN properties ON review_metadata.paperID = properties.property_id
WHERE review_metadata.id = review_comments.metadataID AND reviewerID = users.id AND q_id = ?
ORDER BY paperID, surname
QUERY;
        $result = $this->_mysqli->prepare($query);
        $result->bind_param('i', $this->id);
      }
      $result->execute();
      $result->bind_result($paper_title, $id, $category, $comment, $reviewed, $title, $initials, $surname, $action, $response, $review_type);
      while ($result->fetch()) {
        $this->_comments[$id] = array('paper' => $paper_title, 'category' => $category, 'comment' => $comment, 'date' => $reviewed, 'name' => $title . ' ' . $initials . ' ' . $surname, 'action' => $action, 'response' => $response, 'type' => $review_type);
      }
      $result->close();
    }

    return $this->_comments;
  }

  /**
   * Set the comments list for the question
   * @param unknown_type $value
   */
  public function set_comments($value) {
    // Question class is not currently handling the persisting of comments to the database
    $this->_comments = $value;
  }

  /**
   * Get the source of marks data for this question, usually the first option
   * @return mixed The source of marks or false if none has yet been defined
   */
  public function get_marks_source() {
    if (count($this->options) > 0) {
      return reset($this->options);
    } else {
      return false;
    }
  }

  // STATIC METHODS

  /**
   * Delete the question with the given ID. Will not actually delete the question from the database, just mark
   * it as deleted
   * @param int $id
   * @return bool True or false depending on success or failure of the delete operation
   */
  public static function delete($id, $mysqli) {
    // TODO: Track changes
    $success = false;

    return QuestionEdit::update_deletion_status($id, date ("Y-m-d H:i:s"), $mysqli);
  }

  /**
   * Restore a previously deleted question
   * @param int $id
   * @return bool True or false depending on success or failure of the restore operation
   */
  public static function restore($id) {
    $success = false;

    return QuestionEdit::update_deletion_status($id, null, $mysqli);
  }

  /**
   * Return a question object of the correct type
   * @param object $mysqli database link
   * @param int $user_id
   * @param mixed $data either ID of an existing question or the type if a new question is to be created
   * @throws ClassNotFoundException
   * @return object a question object of the correct type
   */
  public static function question_factory($mysqli, $user_id, &$lang_strings, $data) {
    $object = null;
    if(ctype_digit($data)) {
      // In some versions of PHP, bind_param may change the type of $data to int, so use a copy and
      // keep original for future use in ctype_digit() in question constructor
      $tmp_data = $data;

      // Extra DB query here but easiest way to return a question of correct type for now
      $q_query = <<< QUERY
SELECT q_type
FROM questions
WHERE q_id = ?
QUERY;
      $result = $mysqli->prepare($q_query);
      $result->bind_param('i', $tmp_data);
      $result->execute();
      $result->bind_result($type);

      if ($result->fetch()) {
        $result->close();
        $classname = 'Question' . strtoupper($type);
        $classfile = 'questions/question_' . strtolower($type) . '.class.php';

        try {
          include $classfile;
          $object = new $classname($mysqli, $user_id, $lang_strings, $data);
        } catch (Exception $ex) {
          throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
        }

      } else {
        $result->close();
        throw new RecordNotFoundException(sprintf($lang_strings['norecorderror'], $data));
      }
    } else {
      $classname = 'Question' . strtoupper($data);
      $classfile = 'questions/question_' . strtolower($data) . '.class.php';
      try {
        include $classfile;
        $object = new $classname($mysqli, $user_id, $lang_strings);
      } catch (Exception $ex) {
        throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
      }
    }
    return $object;
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
SELECT q_type, theme, scenario, scenario_plain, leadin, leadin_plain, notes, correct_fback, incorrect_fback, score_method, display_method,
 q_option_order, std, bloom, ownerID, q_media, q_media_width, q_media_height, checkout_time, checkout_authorID, creation_date,
 last_edited, locked, deleted, status, settings, guid
FROM questions
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($q_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, 'bind_result'), $this->_data);
    if ($result->fetch()) {
      $success = true;
      $found = $result->num_rows;
    }
    $result->close();

    $this->unserialize_settings();

    if ($found > 0) {

      //get the question modules
$t_query = <<< QUERY
  SELECT idMod, moduleId
  FROM questions_modules, modules
  WHERE q_id = ?  AND questions_modules.idMod = modules.id
QUERY;
      $result = $this->_mysqli->prepare($t_query);
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->store_result();
      $result->bind_result($idMod, $moduleId);
      while ($success = $result->fetch()) {
        $this->teams[$idMod] = $moduleId;
      }
      $result->close();

      // Build array of references to option data for use in call_user_func_array
      $opt_fields = OptionEdit::get_field_array();
      $opt_data = array();
      $params = array();
      $params[] = &$opt_data['id'];
      foreach($opt_fields as $field) {
        $params[] = &$opt_data[$field];
      }

      // Get the options
      $o_query = <<< QUERY
  SELECT id_num, o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial
  FROM options
  WHERE o_id = ?
  ORDER BY id_num ASC
QUERY;
      $result = $this->_mysqli->prepare($o_query);
      $result->bind_param('i', $this->id);
      $result->execute();
      $result->store_result();
      call_user_func_array(array($result, 'bind_result'), $opt_data);
      // TODO: handle 'correctness' more nicely
      $i = 1;
      while ($success = $result->fetch()) {
        $this->options[$opt_data['id']] = OptionEdit::option_factory($this->_mysqli, $this->_user_id, $this, $i, $this->_lang_strings, $opt_data);
        $i++;
      }
      $result->close();
    } else {
      throw new RecordNotFoundException(sprintf($this->_lang_strings['norecorderror'], $this->id));
    }

    return ($success !== false);
  }

  /**
   * Validate the question object before saving
   * @return Mixed <boolean, string>
   */
  protected function validate() {
    $rval = true;

    // If there are errors return an appropriate message

    // Required fields
    $missing_fields = '';
    foreach ($this->_fields_required as $req) {
      if (empty($this->$req)) $missing_fields .= $this->_pretty_names[$req] . ', ';
    }
    if ($missing_fields != '') {
      $rval = $this->_lang_strings['missingfieldserror'] . ' ' . rtrim($missing_fields, ', ');
    }

    // Number of options
    $opt_error = false;
    if (count($this->options) < $this->min_options) {
      $opt_error = true;
    } else {
      $valid_opts = 0;
      foreach ($this->options as $option) {
        if (!$option->is_blank()) {
          $valid_opts++;
        }
      }
      if ($valid_opts < $this->min_options) {
        $opt_error = true;
      }
    }

    if ($opt_error) {
      $messg = sprintf($this->_lang_strings['validanswers'], $this->min_options);
      if ($rval == true) {
        $rval = $messg;
      } else {
        $rval .= '<br />' . $messg;
      }
    }

    return $rval;
  }

  /**
   * Put all the extra data fields into an array and encode as JSON
   * @return string JSON encoded string containing extra data fields
   */
  protected function serialize_settings() {
    $extra = array();

    foreach ($this->_fields_settings as $field) {
      if (isset($this->$field)) {
        $extra[$field] = $this->$field;
      }
    }

    $this->settings = json_encode($extra);
  }

  /**
   * Unpack JSON string containing extra data into local fields
   */
  protected function unserialize_settings() {
    $extra = json_decode($this->settings, true);

    if (is_array($extra)) {
      foreach ($extra as $field => $value) {
        $this->$field = $value;
      }
    }
  }

  /**
   * Save the options for this question, deleting any that are empty
   * @return boolean
   */
  private function save_options() {
    $success = true;

    // Call save() on the options too if successful
    $i = 1;
    foreach ($this->options as $oid => $option) {
      $media = $option->get_media();
      if ($option->is_blank()) {
        $success = $option->delete();
        if ($success) {
          unset($this->options[$oid]);
        }
      } else {
        $option->set_question_id($this->id);
        $success = $option->save($i);
        if ($success and $option->id != $oid) {
          // Unset temporary option index
          $this->options[$option->id] = $this->options[$oid];
          unset($this->options[$oid]);
        }
      }

      if (!$success) break;

      $i++;
    }

    if ($success) $this->log_unified_field_modifications();

    return $success;
  }

  private function log_unified_field_modifications() {
    foreach ($this->_unified_field_modifications as $mod) {
      $this->_logger->track_change($mod[0], $this->id, $this->_user_id, $mod[2], $mod[3], $mod[1]);
    }
    $this->_unified_field_modifications = array();
  }

  private function get_paper_type($paper_id) {
    $type = -1;

    $p_query = <<< QUERY
SELECT paper_type
FROM properties
WHERE property_id = ?
QUERY;
    $result = $this->_mysqli->prepare($p_query);
    $result->bind_param('i', $paper_id);
    $result->execute();
    $result->bind_result($type);
    $result->fetch();
    $result->close();

    return $type;
  }

  /**
   * Perform delete or restore operation
   * @param int $id
   * @return bool True or false depending on success or failure of the operation
   */
  private static function update_deletion_status($id, $status, $mysqli) {
    $success = false;

    $d_query = <<< QUERY
UPDATE questions
SET deleted = ?
WHERE q_id = ?
QUERY;
    $result = $this->_mysqli->prepare($d_query);
    $result->bind_param('si', $status, $id);
    $success = $result->execute();
    $result->close();

    return $success;
  }

  /**
   * Convert PHP value of 'on' for a checked chekbox to a boolean
   * @param  string $value Value from checkbox submission
   * @return bool          True if the checkbox was checked
   */
  protected function get_checkbox_bool($value) {
    return $value == 'on';
  }
}

?>
