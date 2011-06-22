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
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../classes/mathsutils.class.php');

/**
 * Returns the number of marks the question is out of.
 * This number will adapt if the question (or parts) are excluded.
 *
 */
function qMarks($question_type, $tmp_exclude, $question_marks, $option_text, $correct, $score_method) {
  $marks = 0;
  if ($tmp_exclude == '') $tmp_exclude = '0000000000000000000000000000000000000000';

  switch ($question_type) {
    case 'blank':
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
            $marks++;
          }
        }
        $blank_count++;
      }
      break;
    case 'calculation':
    case 'timedate':
    case 'flash':
    case 'textbox':
      if (substr($tmp_exclude,0,1) == '0') {
        $marks = $question_marks;
      }
      break;
    case 'dichotomous':
      $correct_no = 0;
      for ($i=0; $i<count($option_text); $i++) {
        if (substr($tmp_exclude,$i,1) == '0' and $correct[$i] != '') $marks++;
        if ($correct[$i] == 't' or $correct[$i] == 'f') $correct_no++;
      }
      break;
    case 'labelling':
      $tmp_first_split = explode(';', $correct[0]);
      $tmp_second_split = explode('$', $tmp_first_split[8]);
      $label_count = 0;
      $placeholders = 0;
      $i = 0;
      $excluded_no = 0;
      for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
          if (substr($tmp_exclude,$i,1) == '0') {
            $placeholders++;
            $marks++;
          } else {
            $excluded_no++;
          }
          $i++;
        }
      }
      break;
    case 'mcq':
      if (substr($tmp_exclude,0,1) == '0') $marks++;
      break;
    case 'mrq':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] == 'y') $correct_no++;
      }
    
      if (substr($tmp_exclude,0,1) == '0') {
        if ($score_method == 'SelectedPositive') {
          $marks += $correct_no;
        } elseif ($score_method == 'AllItemsCorrect') {
          $marks += 1;
        } else {
          $marks += count($option_text);
        }
      }
      break;
    case 'matrix':
      $correct_no = 0;
      $matching_correct = explode('|', $correct[0]);
      for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
        if ($matching_correct[$part_id] != '' and substr($tmp_exclude,$part_id,1) == '0') $correct_no++;
      }
      $marks += $correct_no;
      break;
    case 'extmatch':
      $correct_array = explode('|',$correct[0]);
      $i = 0;
      $sub_part_no = 0;
      foreach ($correct_array as $individual_correct) {
        if (trim($individual_correct) != '') {
          $sub_array = explode('$',$individual_correct);
          $correct_no = 0;
          foreach ($sub_array as $subpart) {
            if (substr($tmp_exclude,$sub_part_no,1) == '0') {
              $correct_no++;
              $marks++;
            }
            $sub_part_no++;
          }
        }
        $i++;
      }
      break;
    case 'sct':
      if (substr($tmp_exclude,0,1) == '0') $marks = 1;
      break;
    case 'rank':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] != 0) $correct_no++;
      }
      if (substr($tmp_exclude,0,1) == '0') {
        $correct++;
        if ($score_method == 'StrictOrder') {
          $marks += count($option_text);
        } elseif ($score_method == 'AllItemsCorrect') {
          $marks += 1;
        } elseif ($score_method == 'OrderNeighbours') {
          $marks += $correct_no;
        } elseif ($score_method == 'BonusMark' and $correct_no > 0) {
          $marks += $correct_no + 1;
        }
      }
      break;
    case 'hotspot':
      if (substr($tmp_exclude,0,1) == '0' and $correct[0] != '') {
        $marks += (substr_count($correct[0],'|') + 1);
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
function qRandomMarks($question_type, $tmp_exclude, $option_text, $correct, $score_method, $old_q_media_width, $old_q_media_height) {
  $random_mark = 0;
  if ($tmp_exclude == '') $tmp_exclude = '0000000000000000000000000000000000000000';
      
  switch ($question_type) {
    case 'calculation':
    case 'timedate':
    case 'flash':
    case 'textbox':
      $random_mark = 0;
      break;
    case 'dichotomous':
      for ($i=0; $i<count($option_text); $i++) {
        if (substr($tmp_exclude,$i,1) == '0') {
          $random_mark += 0.5;
        }
      }
      break;
    case 'labelling':
      $tmp_first_split = explode(';', $correct[0]);
      $tmp_second_split = explode('$', $tmp_first_split[8]);
      $label_count = 0;
      $placeholders = 0;
      $i = 0;
      $excluded_no = 0;
      for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
        if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
        if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
          if (substr($tmp_exclude,$i,1) == '0') {
            $placeholders++;
          } else {
            $excluded_no++;
          }
          $i++;
        }
      }
      $random_mark += ($placeholders / ($label_count - $excluded_no));
      break;
    case 'mcq':
      if (substr($tmp_exclude,0,1) == '0') {
        $random_mark += 1 / count($option_text);
      }
      break;
    case 'mrq':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] == 'y') $correct_no++;
      }

      if (substr($tmp_exclude,0,1) == '0') {
        if ($score_method == 'SelectedPositive') {
          $random_mark += ($correct_no * $correct_no) / count($option_text);
        } elseif ($score_method == 'AllItemsCorrect') {
          $figure = 1;
          for ($i=count($option_text); $i>(count($option_text)-$correct_no); $i--) {
            $figure *= (1/$i);
          }
          $random_mark += $figure;
        } else {
          $random_mark += (count($option_text) / 2);
        }
      }
      break;
    case 'matrix':
      $correct_no = 0;
      $matching_correct = explode('|', $correct[0]);
      for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
        if ($matching_correct[$part_id] != '' and substr($tmp_exclude,$part_id,1) == '0') $correct_no++;
      }
      $random_mark += (1 / count($option_text)) * $correct_no;
      break;
    case 'extmatch':
      $correct_array = explode('|',$correct[0]);
      $i = 0;
      $sub_part_no = 0;
      foreach ($correct_array as $individual_correct) {
        if (trim($individual_correct) != '') {
          $sub_array = explode('$',$individual_correct);
          $correct_no = 0;
          foreach ($sub_array as $subpart) {
            if (substr($tmp_exclude,$sub_part_no,1) == '0') {
              $correct_no++;
            }
            $sub_part_no++;
          }
          $random_mark += ($correct_no * $correct_no) / count($option_text);
        }
        $i++;
      }
      break;
    case 'sct':
      if (substr($tmp_exclude,0,1) == '0') {
        $random_mark += 1 / count($option_text);
      }
      break;
    case 'rank':
      $correct_no = 0;
      for ($i=0; $i<count($correct); $i++) {
        if ($correct[$i] != 0) $correct_no++;
      }
      if (substr($tmp_exclude,0,1) == '0') {
        $correct++;
        if ($score_method == 'StrictOrder') {
          if ($correct_no == count($option_text)) {
            $random_mark += 1;
          } else {
            $na = count($option_text) - $correct_no;
            $random_mark += ($correct_no / count($option_text)) + ((count($option_text) - $correct_no) / (count($option_text) / $na));
          }
        } elseif ($score_method == 'AllItemsCorrect') {
          $random_mark += 1 / MathsUtils::factorial(count($option_text));
        } elseif ($score_method == 'OrderNeighbours') {
          $random_mark += 1 + ((count($option_text) - 2) / count($option_text)) + (1/count($option_text));
        } elseif ($score_method == 'BonusMark' and $correct_no > 0) {
          $random_mark += (($correct_no * $correct_no) / count($option_text)) + (MathsUtils::factorial(count($option_text)-$correct_no)/MathsUtils::factorial(count($option_text)));
        }
      }
      break;
    case 'blank':
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
            if ($score_method == 'dropdown') $random_mark += (1 / $choice_no) * $results[1];
          } else {
            if ($score_method == 'dropdown') $random_mark += 1 / $choice_no;
          }
        }
        $blank_count++;
      }
      break;
    case 'hotspot':
      if (substr($tmp_exclude,0,1) == '0' and $correct[0] != '') {
        $hotspot_layers = explode('|',$correct[0]);
        $hotspot_image_area = $old_q_media_width * $old_q_media_height;
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
          //echo $master_area_total . '<br />';
          $random_mark += ($master_area_total/$hotspot_image_area);
        }
      }
      break;
  }
  return $random_mark;
}

?>
