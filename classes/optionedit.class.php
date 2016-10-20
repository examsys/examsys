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
 * Main class for core question options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require_once dirname(__DIR__) . '/include/path_functions.inc.php';

Class OptionEdit extends RogoObject {

  public $id = -1;
  protected $question_id = null;
  protected $text = '';
  protected $media = '';
  protected $media_width = '';
  protected $media_height = '';
  protected $correct_fback = '';
  protected $incorrect_fback = '';
  protected $correct = '';
  protected $marks_correct = 1;
  protected $marks_incorrect = 0;
  protected $marks_partial = 0;

  protected static $_fields = array('question_id', 'text', 'media', 'media_width', 'media_height', 'correct_fback', 'incorrect_fback', 'correct', 'marks_correct', 'marks_incorrect', 'marks_partial');
  // 'media' should not appear in the list below as they are handled separately
  protected $_fields_editable = array('text', 'correct_fback', 'incorrect_fback', 'correct', 'marks_correct', 'marks_incorrect', 'marks_partial');
  protected $_fields_required = array('question_id', 'marks_correct');

  protected $_question = null;
  protected $_number = -1;
  protected $_mysqli = null;
  protected $_user_id;
  protected $_data = array();

  // Map our 'nice' property names to the database fields
  protected $_field_map = array('question_id' => 'o_id', 'text' => 'option_text', 'media' => 'o_media', 'media_width' => 'o_media_width', 'media_height' => 'o_media_height', 'correct_fback' => 'feedback_right', 'incorrect_fback' => 'feedback_wrong');
  protected $_pretty_names = array('question_id' => 'Question ID', 'text' => '', 'correct_fback' => 'Correct Feedback', 'incorrect_fback' => 'Incorrect Feedback', 'correct' => 'Correct Value', 'marks_correct' => 'Marks (correct)', 'marks_incorrect' => 'Marks (incorrect)', 'marks_partial' => 'Marks (partial)');

  // Refrence to array of localised language strings
  protected $_lang_strings = null;


  /**
   * Create a new option object by either loading an existing option from the database or populating
   * properties from an associative array
   * @param mixed $data
   */
  function __construct($mysqli, $user_id, $question, $number, $lang_strings, $data = null) {
    // Store the database connection reference
    $this->_mysqli = $mysqli;
    $this->_user_id = $user_id;
    $this->_question = $question;
    $this->question_id = $question->id;
    $this->_number = $number;
    $this->_lang_strings = $lang_strings;

    // Array of references to the fields.  Allows succinct use of call_user_func_array
    foreach(self::$_fields as $field) {
      $this->_data[] = &$this->$field;
    }

    // Check the type of $data
    if (is_array($data)) {
      // If it is an array, assume an associative array of fields for creating a new object (but not
      // saving it to the database)
      foreach($data as $field => $val) {
        $this->$field = $val;
      }
    } elseif(ctype_digit($data)) {
      // If it is an int use it as an ID for the database lookup
      $this->id = $data;
      if (!$this->get_option()) {
        throw new DatabaseException($this->_lang_strings['optionloaderror']);
      }
    } elseif ($data !== null) {
      throw new DataTypeException($this->_lang_strings['optioninvalid']);
    }
  }

  /**
   * Populate the 'standard' fields for this option
   * @param array $fields list of fields to populate
   * @param integer $index index into list of options
   * @param array $data source from which to extract field data, normally the $_POST array
   * @param array $exclude a list of fields to exclude from the population process
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   */
  public function populate($fields, $index, $data, $exclude=array(), $prefix='') {
    foreach ($fields as $section_name) {
      if (!in_array($section_name, $exclude, true)) {
        $field = $prefix . $section_name . $index;

        // If 'correct' is not a unified field then its value if not in POST is negative
        if ($section_name == 'correct' and !isset($data[$field])) $data[$field] = $this->_question->get_answer_negative();

        $value = (isset($data[$field])) ? $data[$field] : '';
        $method = "set_$section_name";
        $this->$method($value);
      }
    }
  }

  /**
   * Populate the 'unified' fields for this option, which will come from data fields without a numeric index
   * @param array $fields list of fields to populate
   * @param array $data source from which to extract field data, normally the $_POST array
   * @param array $exclude a list of fields to exclude from the population process
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   * @param boolean $save_changes should we save changes?  We don't want to do this for new options
   */
  public function populate_unified($fields, $data, $exclude=array(), $prefix='', $save_changes=true) {
    foreach ($fields as $section_name => $section_label) {
      if (!in_array($section_name, $exclude, true)) {
        $field = $prefix . $section_name;
        $get_method = "get_$section_name";
        $old_value = $this->$get_method();
        if (isset($data[$field]) and $data[$field] != $old_value) {
          $set_method = "set_$section_name";
          $this->$set_method($data[$field]);
          if ($save_changes) $this->_question->add_unified_field_modification($section_name, $section_label, $old_value, $data[$field]);
        }
      }
    }
  }

  /**
   * Populate the 'compound' fields for this option, which will come from data fields without a numeric index
   * Assumes that compound fields are unified so will only actually calculate the value for the first option
   * @param array $fields list of fields to populate
   * @param array $data source from which to extract field data, normally the $_POST array
   * @param array $existing_values an array of values that will be calculated and populated for the first option and then re-used
   * @param string $prefix a prefix to apply to field names when used as keys into data array
   */
  public function populate_compound($fields, $data, &$existing_values, $prefix='', $message = '') {
    $message = ($message == '') ? $this->_lang_strings['editscenario'] : $message;

    foreach ($fields as $section_name) {
      if (!isset($existing_values[$section_name])) {
        $get_method = "get_all_{$section_name}s";
        $original_vals = $this->$get_method();
        for ($i = 1; $i <= $this->_question->max_stems; $i++) {
          $old_val = (isset($original_vals[$i - 1])) ? $original_vals[$i - 1] : '';
          if (isset($_POST["{$prefix}{$section_name}{$i}"]) and $data["{$prefix}{$section_name}{$i}"] != '') {
            ${$section_name}[] = $data["{$prefix}{$section_name}{$i}"];
            if (!isset($old_val) or $data["{$prefix}{$section_name}{$i}"] != $old_val) {
              $this->log_compound_field_change($section_name, $section_name, $i, $old_val, $data["{$prefix}{$section_name}{$i}"], $message);
            }
          } else {
            if (isset($old_val) and $old_val != '') {
              $this->log_compound_field_change($section_name, $section_name, $i, $old_val, '', $message);
            }
            ${$section_name}[] = '';
          }
        }
        $existing_values[$section_name] = $$section_name;
      }
      $method = "set_all_{$section_name}s";
      $this->$method($existing_values[$section_name]);
    }
  }

  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   */
  public function save($option_number = 0) {
    $success = false;
    $logger = new Logger($this->_mysqli);

    $valid = $this->validate();

    if ($valid === true) {
      // If $id is -1 we're inserting a new record
      if ($this->id == -1) {
        $params = array_merge(array('issiisssddd'), $this->_data);
        $query = <<< QUERY
INSERT INTO options(o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('issiisssdddi'), $this->_data, array(&$this->id));
        $query = <<< QUERY
UPDATE options
SET o_id = ?, option_text = ?, o_media = ?, o_media_width = ?, o_media_height = ?, feedback_right = ?, feedback_wrong = ?, correct = ?, marks_correct = ?, marks_incorrect = ?, marks_partial = ?
WHERE id_num = ?
QUERY;
      }
      $result = $this->_mysqli->prepare($query);
      call_user_func_array (array($result,'bind_param'), $params);
      $result->execute();
      $success = ($result->affected_rows > -1);

      if ($success) {
        if ($this->id == -1) {
          $this->id = $this->_mysqli->insert_id;
          $this->track_new($logger, $option_number);
        } else {
          // Log any changes
          $this->save_changes($logger, $option_number);
        }
      }
      $result->close();

      $this->_modified_fields = array();
    } else {
      throw new ValidationException($valid);
    }

    return $success;
  }

  protected function save_changes($logger, $option_number) {
    foreach ($this->_modified_fields as $key => $value) {
      $db_field = (in_array($key, array_keys($this->_field_map))) ? $this->_field_map[$key] : $key;
      if ($value['message'] == '') {
        $this->track_change($logger, $option_number, $value['value'], $this->$key, $db_field);
      } else {
        $logger->track_change($this->_lang_strings['editquestion'], $this->question_id, $this->_user_id, $value['value'], $this->$key, $value['message']);
      }
    }
  }

  /**
   * Delete this option
   * @return bool True of false depending on success or failure of the delete operation
   */
  public function delete() {
    $query = <<< QUERY
DELETE FROM options WHERE id_num = ?
QUERY;
    $result = $this->_mysqli->prepare($query);
    $result->bind_param('i', $this->id);
    $result->execute();

    $success = ($result->affected_rows > -1);

    if($success) {
      $logger = new Logger($this->_mysqli);
      $this->track_delete($logger, $this->_number);
    }

    return $success;
  }


  /**
   * Is this option blank?
   * @return boolean
   */
  public function is_blank() {
    return ($this->text == '' and $this->media == '');
  }

  /**
   * Check that the minimum set of fields exist in the given data to create a new option
   * @param array $data
   * @param array $files expects PHP FILES array
   * @param integer $index option number
   * @return boolean
   */
  public function minimum_fields_exist($data, $files, $index) {
    return ((isset($data["option_text$index"]) and $data["option_text$index"] != '') or (isset($files["option_media$index"]) and ($files["option_media$index"]['name'] != 'none' and $files["option_media$index"]['name'] != '')));
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
   * Get the ID of the question to which this option relates
   * @return string
   */
  public function get_question_id() {
    return $this->question_id;
  }

  /**
   * Get the ID of the question to which this option relates
   * @return string
   */
  public function set_question_id($value) {
    $this->question_id = $value;
  }

  /**
   * Get the option text
   * @return string
   */
  public function get_text() {
    $this->text = $this->replace_mee_div($this->text);
    return $this->text;
  }

  /**
   * Set the option text
   * @param string $value
   */
  public function set_text($value) {

    $value = $this->replace_tex($value);

    if ($value != $this->text and !in_array('text', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('text', $this->text, sprintf($this->_lang_strings['optiontext'], $this->_number));
    }
    $this->text = $value;
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
    if($value != $this->media) {
      $this->set_modified_field('media', $this->media, sprintf($this->_lang_strings['optionmedia'], $this->_number));
      $this->media = $value['filename'];
      $this->media_width = (empty($value['width'])) ? 0 : $value['width'];
      $this->media_height = (empty($value['height'])) ? 0 : $value['height'];
    }
  }

  /**
   * Get the option correct feedback
   * @return string
   */
  public function get_correct_fback() {
    $this->correct_fback = $this->replace_mee_div($this->correct_fback);
    return $this->correct_fback;
  }

  /**
   * Set the option correct feedback
   * @param string $value
   */
  public function set_correct_fback($value) {
    $value = $this->replace_tex($value);
    if($value != $this->correct_fback) {
      $this->set_modified_field('correct_fback', $this->correct_fback, sprintf($this->_lang_strings['optionfbcorrect'], $this->_number));
      $this->correct_fback = $value;
    }
  }

  /**
   * Get the option incorrect feedback
   * @return string
   */
  public function get_incorrect_fback() {
    $this->incorrect_fback = $this->replace_mee_div($this->incorrect_fback);
    return $this->incorrect_fback;
  }

  /**
   * Set the option incorrect feedback
   * @param string $value
   */
  public function set_incorrect_fback($value) {
    $value = $this->replace_tex($value);
    if($value != $this->incorrect_fback) {
      $this->set_modified_field('incorrect_fback', $this->incorrect_fback, sprintf($this->_lang_strings['optionfbincorrect'], $this->_number));
      $this->incorrect_fback = $value;
    }
  }

  /**
   * Get the option correct answer
   * @return string
   */
  public function get_correct() {
    return $this->correct;
  }

  /**
   * Set the option correct answer
   * @param string $value
   */
  public function set_correct($value) {
    if($value != $this->correct and !in_array('correct', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('correct', $this->correct, sprintf($this->_lang_strings['optionanswer'], $this->_number));
    }
    $this->correct = $value;
  }

  /**
   * Get the option marks for correct answers
   * @return string
   */
  public function get_marks_correct() {
    return $this->marks_correct;
  }

  /**
   * Set the option marks for correct answers
   * @param string $value
   */
  public function set_marks_correct($value, $log_change=true) {
    if($log_change and $value != $this->marks_correct and !in_array('marks_correct', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('marks_correct', $this->marks_correct);
    }
    $this->marks_correct = $value;
  }

  /**
   * Get the option marks for incorrect answers
   * @return string
   */
  public function get_marks_incorrect() {
    return $this->marks_incorrect;
  }

  /**
   * Set the option marks for incorrect answers
   * @param string $value
   */
  public function set_marks_incorrect($value, $log_change=true) {
    if($log_change and $value != $this->marks_incorrect and !in_array('marks_incorrect', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('marks_incorrect', $this->marks_incorrect);
    }
    $this->marks_incorrect = $value;
  }

    /**
   * Get the option marks for partially correct answers
   * @return string
   */
  public function get_marks_partial() {
    return $this->marks_partial;
  }

  /**
   * Set the option marks for partially correct answers
   * @param string $value
   */
  public function set_marks_partial($value, $log_change=true) {
    if($log_change and $value != $this->marks_partial and !in_array('marks_partial', array_keys($this->_question->get_unified_fields()))) {
      $this->set_modified_field('marks_partial', $this->marks_partial);
    }
    $this->marks_partial = $value;
  }

  // STATIC METHODS

  /**
   * Get an array with the names of the properties of this class
   * @return array Array of property names
   */
  public static function get_field_array() {
    return self::$_fields;
  }

  /**
   * Get a list of options for the given question
   * @param int $question_id
   * @return multitype: an array of option objects
   */
  public static function get_options($question_id) {
    $options = array();

    return $options;
  }

  public static function option_factory($mysqli, $user_id, $question, $number, &$lang_strings, $data=-1) {
    $object = null;
    $root = get_root_path();
    $root .= '/classes/';

    $question_type = $question->get_type();
    $classname = 'Option' . strtoupper($question_type);
    $classfile = 'options/option_' . strtolower($question_type) . '.class.php';
    if (file_exists($root . $classfile)) {
      include_once $classfile;
    } else {
      $classname = 'OptionEdit';
    }

    if($data != -1 and ctype_digit($data)) {
        try {
          $object = new $classname($mysqli, $user_id, $question, $number, $lang_strings, $data);
        } catch (Exception $ex) {
          throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
        }
    } else {
      try {
        if (is_array($data)) {
          $object = new $classname($mysqli, $user_id, $question, $number, $lang_strings, $data);
        } else {
          $object = new $classname($mysqli, $user_id, $question, $number, $lang_strings);
        }
      } catch (Exception $ex) {
        throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
      }
    }

    return $object;
  }


  // PRIVATE METHODS

  /**
   * Get the actual data for the option from the database
   */
  private function get_option() {
    $o_query = <<< QUERY
SELECT o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial
FROM options
WHERE id_num = ?
QUERY;
    $result = $this->_mysqli->prepare($o_query);
    $result->bind_param('i', $this->id);
    $result->execute();
    $result->store_result();
    call_user_func_array(array($result, "bind_result"), $this->_data);
    $result->fetch();
  }

  protected function validate() {
    $rval = true;
    // If there are errors return an appropriate message
    $missing_fields = '';
    foreach($this->_fields_required as $req) {
      if($this->$req === '' or $this->$req === null) $missing_fields .= $this->_pretty_names[$req] . ', ';
    }
    if($missing_fields != '') {
      $rval = 'The following required fields have not been supplied: ' . rtrim($missing_fields, ', ');
    }

    return $rval;
  }

  /**
   * Track the addition of a new option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_new($logger, $option_number) {
    $log_text = ($this->text != '') ? $this->text : $this->media;
    $logger->track_change($this->_lang_strings['newoption'], $this->question_id, $this->_user_id, '', $this->text, sprintf($this->_lang_strings['optionno'], $option_number));
  }

  /**
   * Track the change of an option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $option_number
   * @param integer $option_number
   * @param mixed $old
   * @param mixed $new
   * @param string $field
   */
  protected function track_change($logger, $option_number, $old, $new, $field) {
    $logger->track_change($this->_lang_strings['editquestion'], $this->question_id, $this->_user_id, $old, $new, $field);
  }

  /**
   * Track the deletion of an option.  The message may be different in other question types so allow this method to be overridden
   * @param Logger $option_number
   * @param integer $option_number
   */
  protected function track_delete($logger, $option_number) {
    $old_val = '';
    if ($this->text != '') {
      $old_val = $this->text;
    } elseif (isset($this->_modified_fields['text'])) {
      $old_val = $this->_modified_fields['text']['value'];
    }

    $logger->track_change($this->_lang_strings['deletedoption'], $this->question_id, $this->_user_id, $old_val, '', sprintf($this->_lang_strings['optionno'], $option_number));
  }

  /**
   * Log a change to a compound field. The actual value logged will depend on the conversion type defined in $_fields_compound.
   * Also be aware that the field may be an array o must be converted to a string
   * @param string $field name of field for which to log a change
   * @param string $label the label to use when logging the change
   * @param integer $index index value that will be added to the log to identify the option that has been changed
   * @param mixed $old_value the old value to log
   * @param mixed $new_value the new value to log
   * @param string $category category label to use in the log
   */
  protected function log_compound_field_change($field, $label, $index, $old_value, $new_value, $category='') {
    if ($category == '') $category = $this->_lang_strings['editquestion'];

    $log_value_old = $log_value_new = '';

    if (is_array($old_value)) {
      foreach ($old_value as $value) {
        $log_value_old .= $this->convert_compound_field_value($value, $this->_fields_compound[$field]) . ',';
      }
      $log_value_old = rtrim($log_value_old, ',');
    } else {
      $log_value_old .= $this->convert_compound_field_value($old_value, $this->_fields_compound[$field]);
    }
    if (is_array($new_value)) {
      foreach ($new_value as $value) {
        $log_value_new .= $this->convert_compound_field_value($value, $this->_fields_compound[$field]) . ',';
      }
      $log_value_new = rtrim($log_value_new, ',');
    } else {
      $log_value_new .= $this->convert_compound_field_value($new_value, $this->_fields_compound[$field]);
    }

    if ($log_value_old != '' or $log_value_new != '') {
      $this->_question->add_unified_field_modification($field . $index, $field . $index, $log_value_old, $log_value_new, $category);
    }
  }

  protected function convert_compound_field_value($value, $type) {
    $converted = '';
    if ($value != '') {
      switch ($type) {
        case 'ucalpha':
          $converted .= chr(64 + $value);
          break;
        default:
          $converted = $value;
          break;
      }
    }
    return $converted;
  }
}

?>
