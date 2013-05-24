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
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/mathsutils.class.php';

/**
 * Returns the number of marks the question is out of.
 * This number will adapt if the question (or parts) are excluded.
 *
 */
function qMarks($question_type, $tmp_exclude, $question_marks, $option_text, $correct, $display_method, $score_method) {
  $marks = 0;
  if ($tmp_exclude == '') $tmp_exclude = '0000000000000000000000000000000000000000';

  switch ($question_type) {
    case 'blank':
      if ($score_method == 'Mark per Option') {
        $blank_details = explode('[blank',$option_text[0]);
        $array_size = count($blank_details);
        $blank_count = 1;
        while ($blank_count < $array_size) {
          $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
          $closing_blank_pos = strpos($blank_details[$blank_count],'[/blank]');
          $tmp_first_part = substr($blank_details[$blank_count],0,$closing_blank_pos);
          $choice_no = substr_count($tmp_first_part,',') + 1;
          if (substr($tmp_exclude,$blank_count-1,1) == '0') {
            $results = array();
            if (preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results)) {
              $marks += $results[1];
            } else {
              $marks += $question_marks;
            }
          }
          $blank_count++;
        }
      } else {
        if ($tmp_exclude{0} == '0') {
          $marks = $question_marks;
        }
      }
      break;
    case 'area':
    case 'calculation':
    case 'flash':
    case 'textbox':
      if ($tmp_exclude{0} == '0') {
        $marks = $question_marks;
      }
      break;
    case 'dichotomous':
      $correct_no = 0;
      for ($i=0; $i<count($option_text); $i++) {
        if ($tmp_exclude{$i} == '0' and $correct[$i] != '') $marks += $question_marks;
        if ($correct[$i] == 't' or $correct[$i] == 'f') $correct_no++;
      }
      if ($score_method == 'Mark per Question') {
        if ($tmp_exclude{0} == '0') {
          $marks = $question_marks;    // Overwrite the marks if by question
        }
      }
      break;
    case 'labelling':
      if ($score_method == 'Mark per Question') {
        if ($tmp_exclude{0} == '0') {
          $marks += $question_marks;
        }
      } else {
        $tmp_first_split = explode(';', $correct[0]);
        $tmp_second_split = explode('$', $tmp_first_split[11]);
        $label_count = 0;
        $placeholders = 0;
        $i = 0;
        $excluded_no = 0;
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
            if ($tmp_exclude{$i} == '0') {
              $placeholders++;
              $marks += $question_marks;
            } else {
              $excluded_no++;
            }
            $i++;
          }
        }
      }
      break;
    case 'likert':
      $marks = 0;
      if ($display_method == '0|1|2|false' or $display_method == '0|1|2|true') {
        $marks = 2;
      } elseif ($display_method == '0|1|false' or $display_method == '0|1|true') {
        $marks = 1;
      }
      break;
    case 'mcq':
    case 'true_false':
      if ($tmp_exclude{0} == '0') $marks += $question_marks;
      break;
    case 'mrq':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] == 'y') $correct_no++;
      }
    
      if ($tmp_exclude{0} == '0') {
        if ($score_method == 'Mark per Option') {
          $marks += ($correct_no * $question_marks);
        } elseif ($score_method == 'Mark per Question') {
          $marks += $question_marks;
        } else {
          $marks += count($option_text);
        }
      }
      break;
    case 'matrix':
      if ($score_method == 'Mark per Question') {
        if ($tmp_exclude{0} == '0') $marks = $question_marks;
      } else {
        $correct_no = 0;
        $matching_correct = explode('|', $correct[0]);
        for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
          if ($matching_correct[$part_id] != '' and $tmp_exclude{$part_id} == '0') $correct_no++;
        }
        $marks += $correct_no * $question_marks;
      }
      break;
    case 'extmatch':
      if ($score_method == 'Mark per Question') {
        if ($tmp_exclude{0} == '0') $marks = $question_marks;
      } else {
        $correct_array = explode('|', $correct[0]);
        $i = 0;
        $sub_part_no = 0;
        foreach ($correct_array as $individual_correct) {
          if (trim($individual_correct) != '') {
            $sub_array = explode('$', $individual_correct);
            $correct_no = 0;
            foreach ($sub_array as $subpart) {
              if ($tmp_exclude{$sub_part_no} == '0') {
                $correct_no++;
                $marks += $question_marks;
              }
              $sub_part_no++;
            }
          }
          $i++;
        }
      }
      break;
    case 'sct':
      if ($tmp_exclude{0} == '0') $marks = 1;
      break;
    case 'rank':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] != 0) $correct_no++;
      }
      if ($tmp_exclude{0} == '0') {
        $correct++;
        if ($score_method == 'Mark per Option') {
          $marks += count($option_text) * $question_marks;
        } elseif ($score_method == 'Mark per Question') {
          $marks += $question_marks;
        } elseif ($score_method == 'Allow partial Marks') {
          $marks += $correct_no * $question_marks;
        } elseif ($score_method == 'Bonus Mark' and $correct_no > 0) {
          $marks += ($correct_no + 1) * $question_marks;
        }
      }
      break;
    case 'hotspot':
      if ($score_method == 'Mark per Question') {
        if ($tmp_exclude{0} == '0') $marks = $question_marks;
      } else {
        $marks += ((substr_count($correct[0],'|') + 1) - substr_count($tmp_exclude, '1')) * $question_marks;
      }
      break;
  }
  return $marks;
}

/**
 * Returns the random mark for a question, what statisticians call the 'expected' mark.
 * This number will adapt if the question (or parts) are excluded.
 *
 */
function qRandomMarks($question_type, $tmp_exclude, $marks_correct, $option_text, $correct, $display_method, $score_method, $old_q_media_width, $old_q_media_height) {
  $random_mark = 0;
  if ($tmp_exclude == '') $tmp_exclude = '0000000000000000000000000000000000000000';
      
  switch ($question_type) {
    case 'calculation':
    case 'flash':
    case 'textbox':
      $random_mark = 0;
      break;
    case 'dichotomous':
      $correct_no = 0;
      for ($i=0; $i<count($option_text); $i++) {
        if ($tmp_exclude{$i} == '0') {
          $correct_no++;
        }
      }
      if ($score_method == 'Mark per Option') {
        $random_mark = 0.5 * $correct_no * $marks_correct;
      } else {
        $random_mark = ($correct_no == 0) ? 0 : pow(0.5, $correct_no) * $marks_correct;
      }
      break;
    case 'labelling':
      $tmp_first_split = explode(';', $correct[0]);
      $tmp_second_split = explode('$', $tmp_first_split[11]);
      $label_count = 0;
      $placeholders = 0;
      $i = 0;
      $excluded_no = 0;
      for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
          if ($tmp_exclude{$i} == '0') {
            $placeholders++;
          } else {
            $excluded_no++;
          }
          $i++;
        }
      }
      if ($score_method == 'Mark per Option') {
        $random_mark += (($label_count - $excluded_no) == 0) ? 0 : ($placeholders / ($label_count - $excluded_no)) * $marks_correct;
      } else {
        $prob = MathsUtils::factorial($label_count - $placeholders) / MathsUtils::factorial($label_count);
        $random_mark = $prob * $marks_correct;
      }
      break;
    case 'mcq':
      if ($tmp_exclude{0} == '0') {
        $random_mark += $marks_correct / count($option_text);
      }
      break;
    case 'mrq':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] == 'y') $correct_no++;
      }
      $option_no = count($option_text);

      if ($tmp_exclude{0} == '0') {
        if ($score_method == 'Mark per Option') {
          $random_mark += (($correct_no * $correct_no) / $option_no) * $marks_correct;
        } elseif ($score_method == 'Mark per Question') {
          if (isset($_GET['startdate'])) {
            $tmp_date = $_GET['startdate'];
          } else {
            $tmp_date = 20600111100000;
          }
          
          if ($tmp_date > 20111107000000 ) {
            $prob = (MathsUtils::factorial($correct_no) * MathsUtils::factorial($option_no - $correct_no)) / MathsUtils::factorial($option_no);
            $random_mark += $prob * $marks_correct;
          } else {    // Maintain backward compatibility
            $figure = 1;
            for ($i=count($option_text); $i>(count($option_text)-$correct_no); $i--) {
              $figure *= (1/$i);
            }
            $random_mark += $figure;
          }
        } else {
          $random_mark += ($option_no / 2);
        }
      }
      break;
    case 'matrix':
      $correct_no = 0;
      $matching_correct = explode('|', $correct[0]);
      for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
        if ($matching_correct[$part_id] != '' and $tmp_exclude{$part_id} == '0') $correct_no++;
      }
      if ($score_method == 'Mark per Option') {
        $random_mark = (1 / count($option_text)) * $correct_no * $marks_correct;
      } else {
        $random_mark = pow((1 / count($option_text)), $correct_no) * $marks_correct;
      }
      break;
    case 'extmatch':
      $correct_array = explode('|', $correct[0]);
      $i = 0;
      $sub_part_no = 0;
      $option_no = count($option_text);
      $prob = 1;
      
      foreach ($correct_array as $individual_correct) {
        if (trim($individual_correct) != '') {
          $sub_array = explode('$', $individual_correct);
          $correct_no = 0;
          foreach ($sub_array as $subpart) {
            if ($tmp_exclude{$sub_part_no} == '0') {
              $correct_no++;
            }
            $sub_part_no++;
          }
          if ($score_method == 'Mark per Option') {
            $random_mark += ($correct_no * $correct_no) / $option_no * $marks_correct;
          } else {
            $prob *= (MathsUtils::factorial($correct_no) * MathsUtils::factorial($option_no - $correct_no)) / MathsUtils::factorial($option_no);
          }
        }
        $i++;
      }
      if ($score_method == 'Mark per Question') $random_mark = $prob * $marks_correct;
      break;
    case 'sct':
      $max_correct = 0;
      foreach ($correct as $tmp_correct) {
        if ($tmp_correct > $max_correct) $max_correct = $tmp_correct;
      }
      $tmp_total = 0;
      foreach ($correct as $tmp_correct) {
        if ($max_correct > 0) $tmp_total += $tmp_correct / $max_correct;
      }
      $random_mark += $tmp_total / count($correct);
      break;
    case 'true_false':
      if ($tmp_exclude{0} == '0') {
        $random_mark += $marks_correct / 2;
      }
      break;
    case 'rank':
      $correct_no = 0;
      $option_no = count($option_text);
      
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] != 0) $correct_no++;
      }
      if ($tmp_exclude{0} == '0') {
        $correct++;
        if ($score_method == 'Mark per Option') {
          if ($correct_no == $option_no) {
            $random_mark += $marks_correct;
          } else {
            $na = $option_no - $correct_no;
            $random_mark += (($correct_no / $option_no) + (($option_no - $correct_no) / ($option_no / $na))) * $marks_correct;
          }
        } elseif ($score_method == 'Mark per Question') {
          $random_mark += $marks_correct / MathsUtils::factorial($option_no);
        } elseif ($score_method == 'Allow partial Marks') {
          $random_mark += (1 + (($option_no - 2) / $option_no) + (1/$option_no)) * $marks_correct;
        } elseif ($score_method == 'Bonus Mark' and $correct_no > 0) {
          $random_mark += (($correct_no * $correct_no) / $option_no) + (MathsUtils::factorial($option_no-$correct_no)/MathsUtils::factorial($option_no));
        }
      }
      $random_mark *= $marks_correct;
      break;
    case 'blank':
      $blank_details = explode('[blank',$option_text[0]);
      $array_size = count($blank_details);
      $blank_count = 1;
      if ($score_method == 'Mark per Question') $random_mark = 1;
      while ($blank_count < $array_size) {
        $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
        $closing_blank_pos = strpos($blank_details[$blank_count],'[/blank]');
        $tmp_first_part = substr($blank_details[$blank_count],0,$closing_blank_pos);
        $choice_no = substr_count($tmp_first_part,',') + 1;
        if ($display_method == 'dropdown' and substr($tmp_exclude,$blank_count-1,1) == '0') {
          $results = array();
          if ($score_method == 'Mark per Option') {
            if (preg_match("|mark=\"([0-9]{1,3})\"|", $blank_details[$blank_count], $results)) {
              $random_mark += (1 / $choice_no) * $results[1];
            } else {
              $random_mark += (1 / $choice_no) * $marks_correct;
            }
          } else {
            $random_mark *= (1 / $choice_no);
          }
        }
        $blank_count++;
      }
      if ($score_method == 'Mark per Question') {
        $random_mark *= $marks_correct;
      }
      break;
    case 'hotspot':
      if ($tmp_exclude{0} == '0' and $correct[0] != '') {
        $hotspot_layers = explode('|',$correct[0]);
        $hotspot_image_area = $old_q_media_width * $old_q_media_height;
        if ($score_method == 'Mark per Question') $random_mark = 1;
        foreach ($hotspot_layers as $hotspot_layer) {
          $parts = explode('~',$hotspot_layer);
          
          $tmp_layer = '';
          for ($i=2; $i<count($parts); $i++) {
            if ($tmp_layer == '') {
              $tmp_layer = $parts[$i];
            } else {
              $tmp_layer .= ';' . $parts[$i];
            }
          }
          
          $coords_array = explode(';',$tmp_layer);
          $master_area_total = 0;
          for ($area_no = 0; $area_no < (count($coords_array)-1); $area_no += 3) {
            $individual_coords = array();
            $individual_coords = explode(",",$coords_array[$area_no+1]);
            if ($coords_array[$area_no] == 'polygon') {
              $individual_coords[] = $individual_coords[0];
              $individual_coords[] = $individual_coords[1];
              $area_total = 0;
              for ($i = 0; $i < (count($individual_coords) - 2); $i += 2) {
                $first_part = hexdec($individual_coords[$i]) * hexdec($individual_coords[$i+3]);
                $second_part = hexdec($individual_coords[$i+2]) * hexdec($individual_coords[$i+1]);
                $area_total += $first_part - $second_part;
              }
              $master_area_total += abs($area_total) / 2;
            } elseif ($coords_array[$area_no] == 'rectangle') {
              $ellipse_x_radius = abs(hexdec($individual_coords[2]) - hexdec($individual_coords[0]));
              $ellipse_y_radius = abs(hexdec($individual_coords[3]) - hexdec($individual_coords[1]));
              $master_area_total += $ellipse_x_radius * $ellipse_y_radius;
            } elseif ($coords_array[$area_no] == 'ellipse') {
              $ellipse_x_radius = abs((hexdec($individual_coords[2]) - hexdec($individual_coords[0]))) / 2;
              $ellipse_y_radius = abs((hexdec($individual_coords[3]) - hexdec($individual_coords[1]))) / 2;
              $master_area_total += $ellipse_x_radius * $ellipse_y_radius * pi();
            }
          }
          if ($hotspot_image_area > 0) {
            if ($score_method == 'Mark per Option') {
              $random_mark += ($master_area_total/$hotspot_image_area);
            } else {
              $random_mark *= ($master_area_total/$hotspot_image_area);
            }
          }
        }
      }
      $random_mark *= $marks_correct;
      break;
  }
  return $random_mark;
}

?>
