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
*  Wrapper for question functionality to make it unit testable
*   
*/
class QuestionWrapper {
  
  /**
  *  Wrapper for the qMarks function in calculate_marks.inc which calculates the 
  *  number of available marks for a question 
  */
  public function qMarks($question_type, $tmp_exclude, $question_marks, 
                         $option_text, $correct, $display_method, 
                         $score_method) {
     
     return qMarks($question_type, $tmp_exclude, 
                   $question_marks, $option_text, 
                   $correct, $display_method, $score_method);
  }

  /**
  *  Wrapper for the qRandomMarks function in calculate_marks.inc which 
  *  calculates the "Monky Mark/Random Mark" for a question 
  */
  public function qRandomMarks($question_type, $tmp_exclude, $marks_correct, 
                               $option_text, $correct, $display_method, 
                               $score_method, $old_q_media_width, 
                               $old_q_media_height) {
     
     return qRandomMarks($question_type, $tmp_exclude, $marks_correct, 
                         $option_text, $correct, $display_method, $score_method, 
                         $old_q_media_width, $old_q_media_height);
  }
}
?>