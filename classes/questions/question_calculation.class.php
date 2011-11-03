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
 * Class for Multiple Response questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionCALCULATION extends Question {
  
  protected $units = '';
  protected $answer_decimals = 0;
  protected $tolerance_full = 0;
  protected $tolerance_partial = 0;
  protected $score_method = 'Allow partial Marks';
  public $max_options = 10;
  protected $_allow_partial_marks = true;
  protected $_allow_change_marking_method = false;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'score_method', 'units', 'answer_decimals', 'tolerance_full', 'tolerance_partial', 'bloom', 'status');
  protected $_fields_change = array('option_correct', 'answer_decimals', 'tolerance_full', 'tolerance_partial');
  
  private $_variables = null;
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    $this->_score_methods = array($this->_lang_strings['allowpartial']);
    $this->_fields_unified = array('correct' => $this->_lang_strings['correctanswer'], 'marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);

    // Convert the max number of options into a list of variables
    $this->_variables = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';
  }

  /**
   * Ensure that display_method is in correct format before calling parent save() function
   * @return integer
   */
  public function save($clear_checkout = true) {
    $this->set_display_method('dummy');
    return parent::save($clear_checkout);
  }
  
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct array of new correct answers
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    $changes = false;
    
    $first = reset($this->options);
    $old_correct = $first->get_correct();
    $mark_correct = $first->get_marks_correct();
    $mark_incorrect = $first->get_marks_incorrect();
    $mark_partial = $first->get_marks_partial();
    
    if ($new_correct['option_correct'] != $old_correct) {
      foreach ($this->options as $option) {
        $option->set_correct($new_correct['option_correct']);
      }
    
      $this->add_unified_field_modification('correct', 'formula', $old_correct, $new_correct['option_correct'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }
    
    $old_decimals = $this->get_answer_decimals();
    if ($new_correct['answer_decimals'] != $old_decimals) {
      $this->set_answer_decimals($new_correct['answer_decimals']);
    
      $this->add_unified_field_modification('answer_decimals', 'answer_decs ', $old_decimals, $new_correct['answer_decimals'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }
    
    $old_tolerance_full = $this->get_tolerance_full();
    if ($new_correct['tolerance_full'] != $old_tolerance_full) {
      $this->set_tolerance_full($new_correct['tolerance_full']);
    
      $this->add_unified_field_modification('tolerance_full', 'tolerance_full', $old_tolerance_full, $new_correct['tolerance_full'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }
    
    $old_tolerance_partial = $this->get_tolerance_partial();
    if ($new_correct['tolerance_partial'] != $old_tolerance_partial) {
      $this->set_tolerance_partial($new_correct['tolerance_partial']);
    
      $this->add_unified_field_modification('tolerance_partial', 'tolerance_partial', $old_tolerance_partial, $new_correct['tolerance_partial'], $this->_lang_strings['postexamchange']);
      $changes = true;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          $score_method = $this->score_method;
    	    
          // Remark the student's answers in 'log2'.
          $result = $this->_mysqli->prepare("SELECT user_answer, id FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();
          $result->store_result();
          $result->bind_result($user_answer, $id);
          while ($row = $result->fetch()) {
            // Split up the user answer into its constituent parts.
            $answer_parts = explode('|',$user_answer);
            $variable_array = explode(',',$answer_parts[2]);
            $saved_response = $answer_parts[0];
            $var_no = 1;
            foreach($variable_array as $individual_variable) {
              $var = chr(64 + $var_no);
              $$var = $individual_variable;
              $var_no++;
            }
            $mark = 0;
            $answer_equation = $first->get_correct();
            eval ("\$answer = $answer_equation;");
            $answer = round($answer, $this->get_answer_decimals());
            if ($saved_response == $answer) {
              $mark = $mark_correct;
            } elseif (abs($saved_response - $answer) <= $this->get_tolerance_full()) {
              $mark = $mark_correct;
            } elseif ($score_method == 'Allow partial Marks' and abs($saved_response - $answer) <= $this->get_tolerance_partial()) {
              $mark = $mark_partial;
            } else {
              $mark = $mark_incorrect;
            }
            $saved_response .= '|' . $answer . '|' . $answer_parts[2];
          
            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=?, user_answer=? WHERE id=? AND q_paper=?");
            $updateLog->bind_param("dsii", $mark, $saved_response, $id, $paper_id);
            $updateLog->execute();  
            $updateLog->close();
          }
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }
    
    
    return $errors;
  }
  
  // ACCESSORS
  
  /**
   * Get the variables for the question
   * @return integer
   */
  public function get_variables() {
    return $this->_variables;
  }
  
  /**
   * Get the units for the question
   * @return integer
   */
  public function get_units() {
    $this->get_display_method();
    return $this->units;
  }
  
  /**
   * Set the units for the question
   * @param unknown_type $value
   */
  public function set_units($value) {
    if ($value != $this->get_units()) {
      $this->set_modified_field('units', $this->units);
      $this->units = $value;
      $this->set_display_method('dummy');
    }
  }

  /**
   * Get the number of decimal places for the question
   * @return integer
   */
  public function get_answer_decimals() {
    $this->get_display_method();
    return $this->answer_decimals;
  }
  
  /**
   * Set the number of decimal places for the question
   * @param unknown_type $value
   */
  public function set_answer_decimals($value) {
    if ($value != $this->get_answer_decimals()) {
      $this->set_modified_field('answer_decimals', $this->answer_decimals);
      $this->answer_decimals = $value;
      $this->set_display_method('dummy');
    }
  }

  /**
   * Get the full marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_full() {
    $this->get_display_method();
    return $this->tolerance_full;
  }
  
  /**
   * Set the full marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_full($value) {
    if ($value != $this->get_tolerance_full()) {
      $this->set_modified_field('tolerance_full', $this->tolerance_full);
      $this->tolerance_full = $value;
      $this->set_display_method('dummy');
    }
  }

  /**
   * Get the partial marks tolerance for the question
   * @return integer
   */
  public function get_tolerance_partial() {
    $this->get_display_method();
    return $this->tolerance_partial;
  }
  
  /**
   * Set the partial marks tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance_partial($value) {
    if ($value != $this->get_tolerance_partial()) {
      $this->set_modified_field('tolerance_partial', $this->tolerance_partial);
      $this->tolerance_partial = $value;
      $this->set_display_method('dummy');
    }
  }

  /**
   * Get the question display method, populating pseudo-properties as we go
   * @return string
   */
  public function get_display_method() {
    if ($this->display_method != '') {
      $parts = explode(',', $this->display_method);
      $this->answer_decimals = $parts[0];
      $this->tolerance_full = $parts[1];
      $this->tolerance_partial = $parts[2];
      $this->units = $parts[3];
    }
    return $this->display_method;
  }
  
  /**
   * Set the display method for the question - this is a composite of decimals, tolerances and units
   * @param unknown_type $value
   */
  public function set_display_method($value) {
    $this->display_method = $this->answer_decimals . ',' . $this->tolerance_full . ',' . $this->tolerance_partial . ',' . $this->units;
  }
}

