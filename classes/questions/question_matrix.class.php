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
 * Class for Extended Matching questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionMATRIX extends Question {
  
  protected $stems = array();  
  protected $_answer_negative = array();
  public $max_options = 10;
  public $max_stems = 10;
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
  protected $_fields_editable = array('theme', 'leadin', 'notes', 'score_method', 'option_order', 'bloom', 'status');
  protected $_fields_compound = array('stem');
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    // 'correct' is not a unified field for Matrix because it is compound
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
  }

  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_fields array of corrected fields
   * @param integer $paper_id
   */
  public function update_correct($new_fields, $paper_id) {
    $new_correct = $new_fields['option_correct'];
    $errors = array();
    
    $changes = false;
    
    $first = reset($this->options);
    $old_correct = $first->get_all_corrects();
    $mark_correct = $first->get_marks_correct();
    $mark_incorrect = $first->get_marks_incorrect();
    $correct_count = 0;
    $data = array();

    for ($i = 0; $i < $this->max_stems; $i++) {
      $data['option_correct' . strval($i + 1)] = $new_correct[$i];
      $correct_count += count($new_correct[$i]);
      if ($new_correct[$i] != $old_correct[$i]) {
        $changes = true;
      }
    }
    
    if ($changes) {
      $opt_ids = array_keys($this->options);
      $existing = array();
      for ($option_no = 1; $option_no <= count($this->options); $option_no++) {
        $option = $this->options[$opt_ids[$option_no - 1]];
        $option->populate_compound(array('correct'), $data, $existing, 'option_', $this->_lang_strings['postexamchange']);
      }
    }
    
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log2'.
          $totalpos = 0;
          $score_method = $this->score_method;
        
          $totalpos = ($score_method == 'Mark per Question') ? $mark_correct : $mark_correct * $correct_count;
          
    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();  
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $big_user_parts = explode('|',$user_answer);
            $mark = 0;
            $all_correct = true;
            
            for ($i=0; $i < $correct_count; $i++) {
              if (isset($big_user_parts[$i]) and $big_user_parts[$i] != '' and $big_user_parts[$i] != 'u') {
                if ($score_method == 'Mark per Option') {
                  $mark += ($new_correct[$i] == $big_user_parts[$i]) ? $mark_correct : $mark_incorrect;
                } elseif ($new_correct[$i] != $big_user_parts[$i]) {
                  $all_correct = false;
                }
              } else {
                $all_correct = false;
              }
            }
            
            if ($score_method == 'Mark per Question') {
              if ($all_correct) {
                $mark = $mark_correct;
              } else {
                $mark = $mark_incorrect;
              }
            }

            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=?, totalpos=? WHERE user_answer=? AND q_id=? AND q_paper=?");
            $updateLog->bind_param('disii', $mark, $totalpos, $user_answer, $this->id, $paper_id);
            $updateLog->execute();
            $updateLog->close();
          }
          $result->free_result();
          $result->close();
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }
    
    return $errors;
  }
  
  // ACCESSORS
  
  /**
   * Get an array of stems for the compounded scenarios
   * @return multitype:
   */
  public function get_all_stems() {
    $this->get_scenario();
    return $this->stems;
  }
  
  /**
   * Compound the stems into a single string and set as the scenario
   * @return multitype:
   */
  public function set_all_stems($value) {
    $this->stems = $value;
    $this->set_scenario('dummy');
  }
  
  /**
   * Get the question scenario
   * @return string
   */
  public function get_scenario() {
    if ($this->scenario != '') {
      $this->stems = explode('|', $this->scenario);
    }
    return $this->scenario;
  }

  /**
   * Set the question scenario
   * @param string $value
   */
  public function set_scenario($value) {
    $this->scenario = implode('|', $this->stems);
  }
}

