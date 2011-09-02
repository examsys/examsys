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
 * Class for Dichotomous questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionDICHOTOMOUS extends Question {
  
  public $max_options = 15;
  protected $_answer_positive = 't';
  protected $_answer_negative = 'f';
  
  protected $_fields_unified = array();
  protected $_display_methods = array('TF_NegativeAbstain' => 'True/False/Abstain (Negative Marking -1)', 'TF_NegativeAbstainHalf' => 'True/False/Abstain (Negative Marking -0.5)', 'TF_Positive' => 'True/False', 'YN_NegativeAbstain' => 'Yes/No/Abstain (Negative Marking -1)', 'YN_Positive' => 'Yes/No');
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);
    
    // 'correct' is not a unified field for Dichotomous questions
    $this->_fields_editable[] = 'correct';
  }

  /**
   * Get the labels for true/false options. These change depending on the score method
   */
  public function get_tf_labels() {
    if (substr($this->get_score_method(), 0, 2) == 'YN') {
      $labels = array('true' => 'Y', 'false' => 'N');
    } else {
      $labels = array('true' => 'T', 'false' => 'F');
    }
    
    return $labels;
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
        $this->add_unified_field_modification('correct', "Correct Option $opt_no", $old_correct, $new_correct, 'Post Exam Answer change');
      }
      $i++;
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  } else {
          // Remark the student's answers in 'log2'.
          $score_method = $this->get_score_method();
          switch ($score_method) {
            case 'TF_NegativeAbstain':
            case 'YN_NegativeAbstain':
              $negative = 1;
              break;
            case 'TF_NegativeAbstainHalf':
              $negative = 0.5;
              break;
            default:
              $negative = 0;
              break;
          }
        
    	    $result = $this->_mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
          $result->bind_param('ii', $this->id, $paper_id);
          $result->execute();  
          $result->store_result();
          $result->bind_result($user_answer);
          while ($row = $result->fetch()) {
            $user_answers = str_split($user_answer);
            $mark = 0;
            
            for ($i=0; $i < count($new_correct); $i++) {
              if ($new_correct[$i] == $user_answers[$i]) {
                $mark++;
              } elseif ($user_answers[$i] == $this->get_answer_positive() or $user_answers[$i] == $this->get_answer_negative()) {
                // Don't subtract for unanswered/abstain
                $mark -= $negative;
              }
            }
            
            $updateLog = $this->_mysqli->prepare("UPDATE log2 SET mark=? WHERE user_answer=? AND q_id=? AND q_paper=?");
            $updateLog->bind_param('dsii', $mark, $user_answer, $this->id, $paper_id);
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
}

