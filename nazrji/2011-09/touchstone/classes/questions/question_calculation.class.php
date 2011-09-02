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
  protected $tolerance = 0;
  public $max_options = 10;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'units', 'answer_decimals', 'tolerance', 'bloom', 'status');
  protected $_fields_unified = array('correct' => 'Correct Answer', 'marks' => 'Marks');
  protected $_fields_change = array('option_correct', 'answer_decimals', 'tolerance');
  
  private $_variables = null;
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);

    // Convert the max number of options into a list of variables
    $this->_variables = range('A', chr(64 + $this->max_options));
    $this->option_order = 'display order';
  }

  /**
   * Ensure that display_method is in correct format before calling parent save() function
   * @return integer
   */
  public function save($clear_checkout = true) {
    $this->set_display_method();
    return parent::save($clear_checkout);
  }
  
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct array of new correct answers
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    
    $first = reset($this->options);
    $old_correct = $first->get_correct();
        
    if ($new_correct['option_correct'] != $old_correct) {
      foreach ($this->options as $option) {
        $option->set_correct($new_correct['option_correct']);
      }
    
      $this->add_unified_field_modification('correct', 'formula', $old_correct, $new_correct['option_correct'], 'Post Exam Answer change');
      $changes = true;
    }
    
    $old_decimals = $this->get_answer_decimals();
    if ($new_correct['answer_decimals'] != $old_decimals) {
      $this->set_tolerance($new_correct['answer_decimals']);
    
      $this->add_unified_field_modification('answer_decimals', 'answer_decs ', $old_decimals, $new_correct['answer_decimals'], 'Post Exam Answer change');
      $changes = true;
    }
    
    $old_tolerance = $this->get_tolerance();
    if ($new_correct['tolerance'] != $old_tolerance) {
      $this->set_tolerance($new_correct['tolerance']);
    
      $this->add_unified_field_modification('tolerance', 'tolerance', $old_tolerance, $new_correct['tolerance'], 'Post Exam Answer change');
      $changes = true;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  } else {
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
              $mark = 1;
            } elseif (abs($saved_response - $answer) <= $this->get_tolerance()) {
              $mark = 1;
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
      $this->set_display_method();
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
      $this->set_display_method();
    }
  }

  /**
   * Get the tolerance for the question
   * @return integer
   */
  public function get_tolerance() {
    $this->get_display_method();
    return $this->tolerance;
  }
  
  /**
   * Set the tolerance for the question
   * @param unknown_type $value
   */
  public function set_tolerance($value) {
    if ($value != $this->get_tolerance()) {
      $this->set_modified_field('tolerance', $this->tolerance);
      $this->tolerance = $value;
      $this->set_display_method();
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
      $this->tolerance = $parts[1];
      $this->units = $parts[2];
    }
    return $this->display_method;
  }
  
  /**
   * Set the display method for the question - this is a composite of decimals, tolerance and units
   * @param unknown_type $value
   */
  public function set_display_method($value=-1) {
    $this->display_method = $this->answer_decimals . ',' . $this->tolerance . ',' . $this->units;
  }
}

