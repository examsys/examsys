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
 * Class for Image Hotspot questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

// TODO: deletion of layers - requires Flash change?

Class QuestionHOTSPOT extends Question {
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
      
  protected $points1 = '';
  protected $_requires_media = true;
  protected $_requires_correction_intermediate = true;
  protected $_requires_flash = true;
  public $max_options = 1;
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);

    // Convert the max number of options into a list of variables
    $this->option_order = 'display_order';
    $this->_fields_editable[] = 'points1';
    $this->_change_field_map['points1'] = 'points';
    $this->_fields_change = array('option_correct1', 'points1');
    //    $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
  }

  
  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   * @throws ValidationException
   */
  public function save($clear_checkout = true) {
    // Make sure 'correct' value is set for option
    if (count($this->options) > 0) {
      $this->set_points1($this->points1);
    }
    return parent::save($clear_checkout);
  }
  
  /**
   * Change the correct answer after the question has been locked. Update user marks in summative log table
   * @param integer $new_correct new correct answer
   * @param integer $paper_id
   */
  public function update_correct($new_correct, $paper_id) {
    $errors = array();
    $changes = false;
    
    $old_points = $this->get_points1();
    $option = reset($this->options);
    $marks_correct = $option->get_marks_correct();
    $marks_incorrect = $option->get_marks_incorrect();
    
    if ($old_points != $new_correct['points1']) {
      $changes = true;
      
      $option->set_correct($new_correct['points1']);
      $this->add_unified_field_modification('points', 'points', $old_points, $new_correct['points1'], $this->_lang_strings['postexamchange']);
    }
    
    if ($changes) {
      try {
    	  if(!$this->save()) {
    	    $errors[] = $this->_lang_strings['datasaveerror'];
    	  } else {
          $student_records = explode(';', $new_correct['option_correct1']);
          $max_layers = 0;
          
          foreach ($student_records as $student_record) {
            if (strlen($student_record) > 0) {
              $layers = explode('|',$student_record);
              $mark = 0;
              $correct_count = 0;
              $layer_no = 0;
              foreach ($layers as $layer) {
                $sub_parts = explode(',',$layer);
                if ($layer_no == 0) {
                  $database_id = $sub_parts[0];
                  $is_correct = $sub_parts[1];
                } else {
                  $is_correct = $sub_parts[0];
                }
                  
                if ($is_correct == 1) {
                  $mark += $marks_correct;
                  $correct_count++;
                } else {
                  $mark += $marks_incorrect;
                }
                
                $layer_no++;
                $max_layers = ($layer_no > $max_layers) ? $layer_no : $max_layers;
              }
              
              if ($this->score_method == 'Mark per Question') {
                $totalpos = $marks_correct;
                if ($correct_count == $max_layers) {
                  $mark = $marks_correct;
                } else {
                  $mark = $marks_incorrect;
                }
              } else {
                $totalpos = $marks_correct * $max_layers;
              }
              
              $first_comma = strpos($student_record, ',') + 1;
              $tmp_user_answer = substr($student_record,$first_comma);
              
              $result = $this->_mysqli->prepare("UPDATE log2 SET mark=?, totalpos=?, user_answer=? WHERE id=?");
              $result->bind_param('disi', $mark, $totalpos, $tmp_user_answer, $database_id);
              $result->execute();  
              $result->close();
            }
          }
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    }
    
    return $errors;
  }
  
  
  // ACCESSORS
  
  public function get_points1() {
    if (empty($this->points1) and count($this->options) > 0) {
      $option = reset($this->options);
      $this->points1 = $option->get_correct();
    }
    return $this->points1;
  }
  
  public function set_points1($value) {
    if ($value != $this->get_points1()) {
      $this->set_modified_field('points1', $this->points1);
      $this->points1 = $value;
    }
    
    $leadin = '';
    $layers = explode('|',$value);
    $i = 0;
    foreach ($layers as $layer) {
      $parts = explode('~',$layer);
      if ($leadin == '') {
        $leadin = chr(65 + $i) . ') ' . $parts[0];
      } else {
        $leadin .= ', ' . chr(65 + $i) . ') ' . $parts[0];
      }
      $i++;
    }

    $this->set_leadin($leadin);
    if (count($this->options) > 0) {
      $option = reset($this->options);
      $option->set_correct($value);
    }
  }
  
  /**
   * Set the question leadin, stripping any carriage returns
   * @param string $value
   */
  public function set_leadin($value) {
    $value = str_replace("\r\n", ' ', $value);
    if ($value != $this->leadin) {
      $this->set_modified_field('leadin', $this->leadin);
      $this->leadin = $value;
    }
  }
  
}

