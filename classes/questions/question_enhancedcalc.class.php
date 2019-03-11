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
 * Class for Area questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require_once __DIR__ . '/../options/option_enhancedcalc.class.php';

Class QuestionENHANCEDCALC extends QuestionEdit {

  protected $units = '';
  protected $dp = null;
  protected $sf = null;
  protected $strictdisplay = false;
  protected $strictzeros = false;
  protected $tolerance_full = 0;
  protected $tolerance_partial = 0;
  protected $fulltoltyp = '#';
  protected $parttoltyp = '#';
  protected $vars = array();
  protected $answers = array();
  protected $score_method = 'Allow partial Marks';
  protected $show_units = true;
  protected $marks_correct = 1;
  protected $marks_incorrect = 0;
  protected $marks_partial = 0;
  protected $marks_unit = 'N/A';
  public $max_options = 10;
  public $max_stems = 10;
  protected $variable_labels = array();
  protected $_allow_partial_marks = true;
  protected $_allow_change_marking_method = false;
  protected $_allow_new_options = true;

  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'units', 'answer_precision', 'show_units', 'marks_correct', 'marks_incorrect', 'marks_partial', 'marks_unit', 'tolerance_full', 'tolerance_partial', 'bloom', 'status');
  protected $_fields_change = array('option_formula', 'option_units', 'option_marks_correct', 'option_marks_incorrect', 'option_marks_partial', 'answer_precision', 'marks_unit', 'tolerance_full', 'tolerance_partial');
  protected $_fields_settings = array('sf', 'strictdisplay', 'strictzeros', 'dp', 'tolerance_full', 'fulltoltyp', 'tolerance_partial', 'parttoltyp', 'marks_partial', 'marks_incorrect', 'marks_correct', 'marks_unit', 'show_units', 'answers', 'vars');
  protected $_fields_force = array('show_units');

  protected $_answer_negative = false;

  private $_variable_map = array();

  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);
    $this->_score_methods = array($this->_lang_strings['allowpartial']);
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);

    // Convert the max number of options into a list of variables
    $this->variable_labels = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';
  }

  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save($clear_checkout = true) {
    // Extract options into arrays for JSON encoding
    $this->extract_answers();
    $this->extract_vars();
    $this->extract_marks();

    try {
      $status = parent::save($clear_checkout);
    } catch (ValidationException $ex) {
      $this->unserialize_settings();

      throw new ValidationException($ex->getMessage());
    }

    return $status;
  }

  // ACCESSORS

  /**
   * Get the variables for the question
   * @return integer
   */
  public function get_variables() {
    return $this->vars;
  }

  /**
   * Get the answers for the question
   * @return integer
   */
  public function get_answers() {
    return $this->answers;
  }

  /**
   * Get the units for the question
   * @return integer
   */
  public function get_units() {
    return $this->units;
  }

  /**
   * Set the units for the question
   * @param unknown_type $value
   */
  public function set_units($value) {
    if ($value != $this->units) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
    }
  }

  /**
   * Get the number of decimal places or significant figures for the question
   * @return integer
   */
  public function get_answer_precision() {
		if ($this->id == -1) {
		  return '0 dp';  // Set up the default if a new question
		}
		
    // If not enforced return blank
    if (!$this->strictdisplay) {
      return '';
    }

    $rval = 0;
    $rtype = 'dp';
    $rzeros = '';

    if (isset($this->dp)) {
      $rval = $this->dp;
    } elseif (isset($this->sf)) {
      $rval = $this->sf;
      $rtype = 'sf';
    }

    if ($this->strictzeros) {
      $rzeros = ' zero';
    }

    return $rval . ' ' . $rtype . $rzeros;
  }

  /**
   * Set the number of decimal places for the question
   * @param string $value
   */
  public function set_answer_precision($value) {
    if ($value == '') {
      if ($this->strictdisplay) {
        $this->set_modified_field('answer_precision', $this->get_answer_precision());

        $this->strictdisplay = false;
        $this->strictzeros = false;
      }
    } else {
      $cur_parts = explode(' ', $this->get_answer_precision());
      $cur_val = $cur_parts[0];
      $cur_type = (isset($cur_parts[1])) ? $cur_parts[1] : '';
      $cur_zeros = (isset($cur_parts[2])) ? $cur_parts[2] : '';

      $new_parts = explode(' ', $value);
      $val = $new_parts[0];
      $type = $new_parts[1];
      $zeros = (isset($new_parts[2])) ? $new_parts[2] : '';

      $changed = ($val != $cur_val or $type != $cur_type or $zeros != $cur_zeros);

      if ($type == 'sf') {
        $dpval = null;
        $sfval = $val;
      } else {
        $dpval = $val;
        $sfval = null;
      }

      if ($changed) {
        $this->set_modified_field('answer_precision', $this->get_answer_precision());
      }

      $this->dp = $dpval;
      $this->sf = $sfval;
      $this->strictdisplay = true;
      $this->strictzeros = ($zeros !== '');
    }
  }

  /**
   * Get whether the question requires answers to stricly match the display precision
   * @return boolean
   */
  public function get_strict_display() {
    return $this->strictdisplay;
  }

  /**
   * Get whether trailing zeros should be taken into account when calculating the display precision
   * @return boolean
   */
  public function get_strict_zeros() {
    return $this->strictzeros;
  }

  /**
   * Get the possible labels for variables
   * @return arar List of variable labels
   */
  public function get_variable_labels() {
    return $this->variable_labels;
  }

  /**
   * Get whether to display units for the question
   * @return integer
   */
  public function get_show_units() {
    return $this->show_units;
  }

  /**
   * Set whether to display units for the question
   * @param boolean $value
   */
  public function set_show_units($value) {
    $value = $this->get_checkbox_bool($value);
    if ($value != $this->show_units) {
      $this->set_modified_field('show_units', $this->show_units);
      $this->show_units = $value;
    }
  }

  /**
   * Get the marks adjustment for units for the question
   * @return integer
   */
  public function get_marks_unit() {
    return $this->marks_unit;
  }

  /**
   * Set the marks adjustment for units for the question
   * @param mixed $value
   */
  public function set_marks_unit($value) {
    if ($value != $this->marks_unit) {
      $this->set_modified_field('marks_unit', $this->marks_unit);
      $this->marks_unit = $value;
    }
  }

  /**
   * Get the question marks for correct answers
   * @return string
   */
  public function get_marks_correct() {
    return $this->marks_correct;
  }

  /**
   * Set the question marks for correct answers
   * @param string $value
   */
  public function set_marks_correct($value, $log_change=true) {
    if($log_change and $value != $this->marks_correct) {
      $this->set_modified_field('marks_correct', $this->marks_correct);
    }
    $this->marks_correct = $value;
  }

  /**
   * Get the question marks for incorrect answers
   * @return string
   */
  public function get_marks_incorrect() {
    return $this->marks_incorrect;
  }

  /**
   * Set the question marks for incorrect answers
   * @param string $value
   */
  public function set_marks_incorrect($value, $log_change=true) {
    if($log_change and $value != $this->marks_incorrect) {
      $this->set_modified_field('marks_incorrect', $this->marks_incorrect);
    }
    $this->marks_incorrect = $value;
  }

    /**
   * Get the question marks for partially correct answers
   * @return string
   */
  public function get_marks_partial() {
    return $this->marks_partial;
  }

  /**
   * Set the question marks for partially correct answers
   * @param string $value
   */
  public function set_marks_partial($value) {
    if($value != $this->marks_partial) {
      $this->set_modified_field('marks_partial', $this->marks_partial);
    }
    $this->marks_partial = $value;
  }

  /**
   * Get the full marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_full() {
    if ($this->fulltoltyp == '%') {
      return $this->tolerance_full . $this->fulltoltyp;
    }
    return $this->tolerance_full;
  }

  /**
   * Set the full marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_full($value) {
    $this->set_tolerance_value($value, 'tolerance_full', $this->tolerance_full, $this->fulltoltyp);
  }

  /**
   * Get the partial marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_partial() {
    if ($this->parttoltyp == '%') {
      return $this->tolerance_partial . $this->parttoltyp;
    }
    return $this->tolerance_partial;
  }

  /**
   * Set the partial marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_partial($value) {
    $this->set_tolerance_value($value, 'tolerance_partial', $this->tolerance_partial, $this->parttoltyp);
  }

  /**
   * Get the source of marks data for this question, usually the first option
   * @return mixed The source of marks or false if none has yet been defined
   */
  public function get_marks_source() {
    // For this question type this object will provide the marks data
    if ($this->id != -1) {
      return $this;
    } else {
      return false;
    }
  }

  function get_settings() {
    // Extracting answers temporarily populates answers and vars with the option data
    $this->extract_answers();
    $this->extract_vars();
    $this->extract_marks();

    // Serialise it into the setting var then extract again to reset answers and vars to option indices
    $this->serialize_settings();
    $this->unserialize_settings();

    return $this->settings;
  }

  /**
   * Unpack JSON string containing extra data into local fields
   */
  protected function unserialize_settings() {
    $extra = json_decode($this->settings, true);

    if (is_array($extra)) {
      foreach ($extra as $field => $value) {
        if (is_array($value)) {
          $func = "unserialize_$field";
          $this->$func($value);
        } else {
          $this->$field = $value;
        }
      }
    }

    $this->unserialize_marks();
  }

  /**
   * Parse the data for the answers
   * @param  array $data Data describing the answers in the form of a formula and associated units
   */
  private function unserialize_answers($data) {
    $i = 1;
    $this->answers = array();

    foreach ($data as $fields) {
      if (!isset($this->options[$i])) {
        $opt = new OptionENHANCEDCALC($this->_mysqli, $this->_user_id, $this, $i, $this->_lang_strings, array('formula' => $fields['formula'], 'units' => $fields['units']));
        $this->options[$i] = $opt;
      } else {
        $old_opt = $this->options[$i];
        $opt = new OptionENHANCEDCALC($this->_mysqli, $this->_user_id, $this, $i, $this->_lang_strings, array('formula' => $fields['formula'], 'units' =>  $fields['units'], 'min' => $old_opt->get_min(), 'max' => $old_opt->get_max(), 'decimals' => $old_opt->get_decimals(), 'increment' => $old_opt->get_increment()));
        $opt->set_variable($old_opt->get_variable());
        $this->options[$i] = $opt;
      }
      $opt->id = $i;
      $this->answers[] = $i;
      $i++;
    }
  }

  /**
   * Parse the data for the variables
   * @param  array $data Data describing the variables indexed by the variable label
   */
  private function unserialize_vars($data) {
    $i = 1;
    $this->vars[] = array();

    foreach ($data as $label => $fields) {
      if (!isset($this->options[$i])) {
        $opt = new OptionENHANCEDCALC($this->_mysqli, $this->_user_id, $this, $i, $this->_lang_strings, array('min' => $fields['min'], 'max' => $fields['max'], 'decimals' => $fields['dec'], 'increment' => $fields['inc']));
        $opt->set_variable($label);
        $this->options[$i] = $opt;
      } else {
        $old_opt = $this->options[$i];
        $opt = new OptionENHANCEDCALC($this->_mysqli, $this->_user_id, $this, $i, $this->_lang_strings, array('formula' => $old_opt->get_formula(), 'units' => $old_opt->get_units(), 'min' => $fields['min'], 'max' => $fields['max'], 'decimals' => $fields['dec'], 'increment' => $fields['inc']));
        $opt->set_variable($label);
        $this->options[$i] = $opt;
      }
      $opt->id = $i;
      $this->vars[] = $i;
      $this->_variable_map[$label] = $i;
      $i++;
    }
  }

  private function unserialize_marks() {
    foreach ($this->options as $opt) {
      $opt->set_marks_correct($this->marks_correct, false);
      $opt->set_marks_partial($this->marks_partial, false);
      $opt->set_marks_incorrect($this->marks_incorrect, false);
    }
  }

  private function extract_answers() {
    $this->answers = array();
    
    $all_ans = array_filter ($this->options, function ($var) { return ($var->get_formula() != ''); } );
    
    foreach ($all_ans as $option) {
      $formula = $option->get_formula();
      $units = $option->get_units();

      if ($formula != '') {
        $this->answers[] = array('formula' => $formula, 'units' => $units);
      }
    }
  }

  private function extract_vars() {
    $this->vars = array();

    $index = 1;
    foreach ($this->options as $dummy => $option) {
      $label = '$' . chr(64 + $index);
      $min = $option->get_min();
      $max = $option->get_max();
      $decimals = $option->get_decimals();
      $increment = $option->get_increment();

      if ($min != '') {
        $this->vars[$label] = array('min' => $min, 'max' => $max, 'inc' => $increment, 'dec' => $decimals);
      }
      $index++;
    }
  }

  private function extract_marks() {
    if (count($this->options) > 0) {
      $first = reset($this->options);
      $this->marks_correct = $first->get_marks_correct();
      $this->marks_partial = $first->get_marks_partial();
      $this->marks_incorrect = $first->get_marks_incorrect();
    }
  }

  /**
   * Set the full marks tolerance for the question
   * @param unknown_type $value
   */
  private function set_tolerance_value($value, $type_string, &$val_target, &$type_target) {
    if (StringUtils::ends_with($value, '%')) {
      $val = rtrim($value, '%');
      $type = '%';
    } else {
      $val = $value;
      $type = '#';
    }

    if ($val != $val_target or $type != $type_target) {
      $old_type = ($type_target == '#') ? '' : $type_target;
      $type_target = $type;
      $this->set_modified_field($type_string, $val_target . $old_type);
      $val_target = $val;
    }
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

    // Number of answers
    $have_formula = false;
    foreach ($this->options as $option) {
      if ($option->get_formula() != '') {
        $have_formula = true;
        break;
      }
    }
    if ($have_formula == false) {
      if ($rval !== true) $rval .= '<br />';
      $rval .= $this->_lang_strings['enterformula'];
    }

    return $rval;
  }

}
