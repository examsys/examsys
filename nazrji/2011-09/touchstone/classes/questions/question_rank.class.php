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
 * Class for Ranking questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionRANK extends Question {
  
  protected $_answer_negative = 0;
  
  protected $_fields_unified = array();
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);
    
    // 'correct' is not a unified field for Rank questions
    $this->_fields_editable[] = 'correct';
  }

  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct array of new correct answers
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    $changes = false;
    
    $i = 0;
    foreach ($this->options as $option) {
      if ($new_correct[$i] != $option->get_correct()) {
        $option->set_correct($new_correct[$i]);
        $changes = true;
        
        $opt_no = $i + 1;
        $this->add_unified_field_modification('correct', "Correct Option $opt_no", $option->get_correct(), $new_correct[$i], 'Post Exam Answer change');
      }
      $i++;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  } else {
          // Remark the student's answers in 'log2'.
          $totalpos = 0;
          $score_method = $this->get_score_method();
          $correct_rank = true;
        
          
    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();  
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = explode(',', $user_answer);
            $mark = 0;
            
            for ($i=0; $i < count($new_correct); $i++) {
              if ($new_correct[$i] != 0 and $new_correct[$i] != '') $totalpos++;
              
              switch ($score_method) {
                case 'OrderNeighbours':
                case 'BonusMark':
                  if($user_answers[$i] != 0 and $user_answers[$i] != 'u') {
                    if ($new_correct[$i] == $user_answers[$i]) $mark++;
                    if ($score_method == 'OrderNeighbours' and abs($new_correct[$i] - $user_answers[$i]) == 1) $mark += 0.5;
                  }
                  break;
                default:
                  if ($new_correct[$i] == $user_answers[$i]) $mark++;
                  break;
              }
            }
            
            // Recalculate total possible marks if 'all correct' or 'bonus mark'.
            if ($score_method == 'AllItemsCorrect') {
              $mark = ($mark == $totalpos) ? 1 : 0;
              $totalpos = 1;
            } elseif ($score_method == 'BonusMark') {
              $totalpos++;
              $mark = ($mark == $totalpos - 1) ? $totalpos : $mark;
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

