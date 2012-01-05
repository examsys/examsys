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
 * Class for Ranking questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

Class QuestionRANK extends Question {
  
  protected $_answer_negative = 0;
  protected $_allow_partial_marks = true;
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);
    
    $this->_score_methods = array($this->_lang_strings['markperquestion'], $this->_lang_strings['markperoption'], $this->_lang_strings['allowpartial'], $this->_lang_strings['bonusmark']);
    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect'], 'marks_partial' => $this->_lang_strings['markspartial']);
    
    // 'correct' is not a unified field for Rank questions
    $this->_fields_editable[] = 'correct';
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
    
    $i = 0;
    foreach ($this->options as $option) {
      if ($i == 0) {
        $mark_correct = $option->get_marks_correct();
        $mark_incorrect = $option->get_marks_incorrect();
        $mark_partial = $option->get_marks_partial();
      }
      if ($new_correct[$i] != $option->get_correct()) {
        $option->set_correct($new_correct[$i]);
        $changes = true;
        
        $opt_no = $i + 1;
//        $this->add_unified_field_modification('correct', "Correct Option $opt_no", $option->get_correct(), $new_correct[$i], $this->_lang_strings['postexamchange']);
      }
      $i++;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          // Remark the student's answers in 'log2'.
          $score_method = $this->score_method;
          $correct_rank = true;
        
          
    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();  
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = explode(',', $user_answer);
            $totalpos = 0;
            $mark = 0;
            $order_correct = true;
            
            for ($i=0; $i < count($new_correct); $i++) {
              if (!$this->is_answer_blank($new_correct[$i]) or ($score_method != 'Bonus Mark' and $score_method != 'Allow partial Marks')) $totalpos += $mark_correct;
              
              if ($user_answers[$i] != 'u') {
                if (!$this->is_answer_blank($user_answers[$i]) and !$this->is_answer_blank($new_correct[$i])) {
                  if ($new_correct[$i] == $user_answers[$i]) {
                    $mark += $mark_correct;
                  } elseif ($score_method == 'Bonus Mark') {
                    $mark += $mark_correct;
                    $order_correct = false;
                  } elseif ($score_method == 'Allow partial Marks' and abs($new_correct[$i] - $user_answers[$i]) == 1) {
                    $mark += $mark_partial;
                  } else {
                    $mark += $mark_incorrect;
                  }
                } elseif ($this->is_answer_blank($user_answers[$i]) and $this->is_answer_blank($new_correct[$i])) {
                  if ($score_method != 'Bonus Mark' and $score_method != 'Allow partial Marks') {
                    $mark += $mark_correct;
                  }
                } else {
                  $mark += $mark_incorrect;
                  $order_correct = false;
                }
              } else {
                $order_correct = false;
              }
            }
            
            // Recalculate total possible marks if 'all correct' or 'bonus mark'.
            if ($score_method == 'Mark per Question') {
              $mark = ($mark == $totalpos) ? $mark_correct : $mark_incorrect;
              $totalpos = $mark_correct;
            } elseif ($score_method == 'Bonus Mark') {
              $totalpos += $mark_correct;
              $mark = ($order_correct) ? $totalpos : $mark;
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
  
  private function is_answer_blank($value) {
    return ($value == 0 or $value == '');
  }
  
  public function convert_to_mcq($correct_answer) {
    // TODO: update question and get new MCQ object based on it
    $this->set_type('mcq');
    $this->set_option_order('vertical');

    foreach ($this->options as $option) {
      $option->set_correct($correct_answer);
    }
    
    $this->save();
    
    return new QuestionMCQ($this->_mysqli, $this->_user_id, $this->id);
  }
}

