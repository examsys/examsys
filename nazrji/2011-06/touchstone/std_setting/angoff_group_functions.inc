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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../classes/mathsutils.class.php');

function reviewScale($q_type, $tmp_q_id, $q_no, $tmp_part_no, $mean_val, $default_on) {
  $qID = $q_no;
  if ($tmp_part_no != '') $qID .= '_' . $tmp_part_no;
  $html = '';
  $mean_val = round($mean_val / 5) * 5;
  $html = "<select name=\"std$qID\" style=\"background-color:#D6DFF7\">\n<option value=\"\"></option>";
  for ($i=0; $i<=100; $i+=5) {
    if ($i == $mean_val and $default_on == 1) {
      $html .= "<option value=\"$i\" selected>$i%</option>\n";
    } else {
      $html .= "<option value=\"$i\">$i%</option>\n";
    }
  }
  $html .=  "</select></div>\n";

  return $html;
}

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

function display_options($options,$q_id, $current_theme, $current_scenario, $current_leadin, $current_notes, $q_type, $std_method, $setter_username) {
  global $marking, $question_no, $question_offset, $part_id, $labelcolor, $old_likert_label, $screen_pre_submitted, $question_type, $A, $B, $C, $D, $E, $F, $G, $H, $li_set, $bgcolor, $rater_names, $reviews;

  $option_no = count($options);
  $part_id = 0;
  $part_no = 0;
  $marks = 0;
  $rating_part = 0;
  if ($options[0]['q_type'] != 'likert') $old_likert_label = '';

  if ($options[0]['q_type'] == 'dichotomous') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    if ($marking < 2) {
      if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'TF_Negative') or ($options[0]['score_method'] == 'TF_Positive')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'YN_NegativeAbstain') or ($options[0]['score_method'] == 'YN_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">Abstain</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td></tr>\n";
      }
    } else {
      if ($options[0]['score_method'] == 'TF_Positive' or $options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"80\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">Confidence</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"80\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">Confidence</td></tr>\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'mcq') {
    echo '<blockquote>';
    if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
      echo '<table cellpadding="2" cellspacing="0" border="0">';
    }
    if ($options[0]['score_method'] == 'dropdown') {
      echo '<div class="option"><select name="q' . $question_no . '" size="1">';
      echo '<option value=""></option>';
    }
  } elseif ($options[0]['q_type'] == 'mrq') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
  } elseif ($options[0]['q_type'] == 'rank') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
  } elseif ($options[0]['q_type'] == 'likert') {
    $str = $options[0]['score_method'];
    $likert_display = explode('|',$str);
    $likert_col_no = substr_count($options[0]['score_method'],'|');
    if ($old_likert_label != $str or $current_theme != '') {
      echo '</table><table cellpadding="4" cellspacing="0" border="0">';
      if ($likert_display[$likert_col_no] == 'true') {
        $likert_col_no++;
        $na = true;
      }
      if (trim($current_notes) != '') {
        echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"notes\"><img src=\"./artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;<strong>NOTE:</strong>&nbsp;$current_notes</td></tr>\n";
      }
      if (trim($current_scenario) != '') {
        echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">$current_scenario</td></tr>\n";
        echo '</table><br /><table cellpadding="4" cellspacing="0" border="0">';
      }
      echo '<tr><td></td>';
      if ($na == true) {
        echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">N/A</td>";
      }
      echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[0]</td>";
      $temp_end = substr_count($options[0]['score_method'],'|') - 1;
      for ($i=1; $i<=$temp_end; $i++) {
        echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[$i]</td>\n";
      }
    } else {
      if ($current_scenario != '') {
        echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">$current_scenario</td></tr>\n";
      }
    }
    $old_likert_label = $str;
  } elseif ($options[0]['q_type'] == 'extmatch' or $options[0]['q_type'] == 'matrix') {
    $matching_scenarios = explode('|', $options[0]['scenario']);
    $matching_media = explode('|', $options[0]['q_media']);
    $matching_media_width = explode('|', $options[0]['q_media_width']);
    $matching_media_height = explode('|', $options[0]['q_media_height']);
    $matching_options = array();
  }
  $i = 0;
  
  foreach ($options as $display_option) {
    $part_id++;
    switch ($options[0]['q_type']) {
      case 'dichotomous':
        $tmp_user_answer = $display_option['correct'];
        if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain') or ($options[0]['score_method'] == "YN_NegativeAbstain") or ($options[0]['score_method'] == "YN_PostiveAbstain")) $abstain = true;

        echo '<tr><td align="center">';
        if ($tmp_user_answer == 't') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" checked /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td><td>';
        } elseif ($tmp_user_answer == 'f') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" checked /></td><td>';
        } elseif ($tmp_user_answer == 'a') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td><td>';
        } else {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td><td>';
        }
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
        }
        echo "</td></tr>\n";
        if ($marking == 2) $marks += 2;
        $marks++;
        break;
      case 'calculation':
        $figures = explode(',',$display_option['option_text']);
        switch ($part_id) {
          case 1:
            $A = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 2:
            $B = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 3:
            $C = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 4:
            $D = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 5:
            $E = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 6:
            $F = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 7:
            $G = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
          case 8:
            $H = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            break;
        }
        break;
      case 'mcq':
        if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td></tr>\n";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span><br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if ($part_id == $display_option['correct']) {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            } else {
              echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "</td></tr>\n";
            }
          }
        } elseif ($options[0]['score_method'] == 'horizontal') {
          if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;" . $display_option['option_text'] . "&nbsp;&nbsp;";
            } else {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . $display_option['option_text'] . "&nbsp;&nbsp;";
            }
          } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            } else {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . $display_option['option_text'] . "<br />" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            }
          } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and media
            if ($part_id == $display_option['correct']) {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            } else {
              echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />&nbsp;" . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']) . "&nbsp;&nbsp;";
            }
          }
        } else {
          echo "<option value=\"" . $part_id . "\">" . $display_option['option_text'] . "</option>\n";
        }
        if ($part_id == $display_option['correct']) {
          $marks = $display_option['marks'];
        }
        break;
      case 'mrq':
        if ($display_option['option_text'] != '' and $display_option['o_media'] == '') {        // Text and No image
          echo "<tr><td><input type=\"checkbox\" onclick=\"itemClicked($question_no,$part_id);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span></td></tr>\n";
        } elseif ($display_option['option_text'] != '' and $display_option['o_media'] != '') {  // Text and image
          echo "<div><input type=\"checkbox\" onclick=\"itemClicked($question_no,$part_id);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . stripslashes($display_option['option_text']) . "</span><br /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . $display_option['o_media_height'] . "\" border=\"0\"/></div>\n";
        } elseif ($display_option['option_text'] == '' and $display_option['o_media'] != '') {  // No Text and image
          echo "<div><input type=\"checkbox\" onclick=\"itemClicked($question_no,$part_id);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /><img src=\"./media/" . $display_option['o_media'] . "\" width=\"" . $display_option['o_media_width'] . "\" height=\"" . stripslashes($display_option['o_media_height']) . "\" border=\"0\"/></div>\n";
        }
        if ($display_option['score_method'] == 'SelectedPositive') {
          if ($display_option['correct'] == 'y') $marks++;  // 1 mark for correct options only
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          if ($part_id == 1) $marks++;
        } else {
          $marks++;  // 1 mark for each and every item
        }
        break;
      case 'extmatch':
      case 'matrix':
        $matching_options[] = $display_option['option_text'];
        break;
      case 'rank':
        $total_rank_no = 0;
        $require_na = false;
        for ($i=0; $i<$option_no; $i++) {
          if ($options[$i]['correct'] < 9990) {
            $total_rank_no++;
          }
          if ($options[$i]['correct'] == 9990) $require_na = true;         
        }
           
        if ($part_id == 1 and $display_option['score_method'] == 'BonusMark') {
          $marks++;  // Add one mark at start in case all options are answered correctly
        }
        
        echo "<tr><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\" onchange=\"itemSelected($question_no,$part_id);\">\n";
        echo "<option value=\"9999\"></option>\n";
        if ($require_na) {
          echo "<option value=\"9990\">N/A</option>\n";
        }
        for ($i=1; $i<=$total_rank_no; $i++) {
          echo "<option value=\"" . $i . "\">" . $i;
          if ($i == 1) {
            echo 'st';
          } elseif ($i == 2) {
            echo 'nd';
          } elseif ($i == 3) {
            echo 'rd';
          } else {
            echo 'th';
          }
          echo '</option>';
        }
        echo '</select></td>';
        if ($display_option['score_method'] == 'BonusMark') {
          echo "<td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
        } else {
          echo "<td>" . $display_option['option_text'] . "</td>\n</tr>\n";
        }
        if ($display_option['score_method'] == 'AllItemsCorrect') {
          if ($part_id == 1) {
            $marks++;
          }
        } elseif ($display_option['correct'] < 9990) {
                $marks++;
              } elseif ($display_option['score_method'] == 'StrictOrder') {
                $marks++;
              }
        break;
      case 'timedate':
        $date_details = explode('|',$display_option['score_method']);
        echo '<p>';
        if ($date_details[0] == 1 or $date_details[0] == 4) {
          echo '<select name="q' . $question_no . '_month">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
            if ($tmp_month < 10) {
              echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
            } else {
              echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
            }
          }
          echo '</select> / ';
        }
        if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 3 or $date_details[0] == 4 or $date_details[0] == 5) {
          echo '<select name="q' . $question_no . '_day">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_day=1; $tmp_day<=31; $tmp_day++) {
            if ($tmp_day < 10) {
              echo "<option value=\"0$tmp_day\">0$tmp_day</option>\n";
            } else {
              echo "<option value=\"$tmp_day\">$tmp_day</option>\n";
            }
          }
          echo '</select>';
          if ($date_details[0] != 4 and $date_details[0] != 5) {
            echo ' / <select name="q' . $question_no . '_month">';
            echo "\n<option value=\"\"></option>\n";
            for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
              if ($tmp_month < 10) {
                echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
              }
            }
            echo '</select>';
          } elseif ($date_details[0] == 5) {
            $month_array = array('January','February','March','April','May','June','July','August','September','October','November','December');
            $tmp_month = 1;
            echo ' / <select name="q' . $question_no . '_month">';
            echo "\n<option value=\"\"></option>\n";
            foreach ($month_array as $individual_month) {
              if ($tmp_month < 10) {
                echo "<option value=\"0$tmp_month\">$individual_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$individual_month</option>\n";
              }
              $tmp_month++;
            }
            echo '</select>';
          }
          echo ' / <select name="q' . $question_no . '_year">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_year=$date_details[1]; $tmp_year<=$date_details[2]; $tmp_year++) {
            echo "<option value=\"$tmp_year\">$tmp_year</option>\n";
          }
          echo "</select>\n";
        }        
        if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 6 or $date_details[0] == 7) {
          if ($date_details[0] < 6) echo '&nbsp;&nbsp;&nbsp;';
          echo '<select name="q' . $question_no . '_hour">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_hour=0; $tmp_hour<=23; $tmp_hour++) {
            if ($tmp_hour < 10) {
              echo "<option value=\"0$tmp_hour\">0$tmp_hour</option>\n";
            } else {
              echo "<option value=\"$tmp_hour\">$tmp_hour</option>\n";
            }
          }
          echo "</select>\n";
          echo ' : <select name="q' . $question_no . '_minute">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_min=0; $tmp_min<=60; $tmp_min++) {
            if ($tmp_min < 10) {
              echo "<option value=\"0$tmp_min\">0$tmp_min</option>\n";
            } else {
              echo "<option value=\"$tmp_min\">$tmp_min</option>\n";
            }
          }
          echo "</select>\n";
          if ($date_details[0] == 1 or $date_details[0] == 6) {
            echo ' : <select name="q' . $question_no . '_second">';
            echo "\n<option value=\"\"></option>\n";
            for ($tmp_sec=0; $tmp_sec<=60; $tmp_sec++) {
              if ($tmp_sec < 10) {
                echo "<option value=\"0$tmp_sec\">0$tmp_sec</option>\n";
              } else {
                echo "<option value=\"$tmp_sec\">$tmp_sec</option>\n";
              }
            }
            echo "</select>\n";
          }          
        }
        echo "</p>\n";
        $marks++;
        break;
      case 'blank':
        if ($display_option['score_method'] == 'textboxes') {
          $blank_details = explode('[blank',$display_option['option_text']);
          $array_size = count($blank_details);
          $blank_count = 1;
          while ($blank_count < $array_size) {
            $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
            $results = array();
            $not_used = ereg("size=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results);
         
            if ($results[1]) {
              $blank_size[$blank_count] = $results[1];
            } else {
              $blank_size[$blank_count] = 15;
            }
            $results=array();
            $not_used = ereg("mark=\"([0-9]{1,3})\"",$blank_details[$blank_count],$results);
            if ($results[1]) {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $screen_output[$blank_count] = ereg_replace("\[blank.*\].*\[/blank\]","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
            $blank_count++;
          }
          $screen_output_all = '';
          foreach ($screen_output as $value) {
            $screen_output_all .= $value;
          }
          echo "<p class=\"stem\">" . $blank_details[0].$screen_output_all . "</p>\n";
          if (count($blank_mark) > 0) {
            foreach ($blank_mark as $individual_mark) {
              $marks += $individual_mark;
            }
          }
        } else {
          // Dropdown lists.
          $blank_details = explode('[blank',$display_option['option_text']);
          $no_blanks = count($blank_details);
          echo '<p>' . $blank_details[0];
          $blank_count = 1;
          while ($blank_count < $no_blanks) {
            $not_used = preg_match("/mark=\"([0-9]{1,3})\"/",$blank_details[$blank_count],$results);
            if ($not_used > 0) {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
            $answer_options = explode('[/blank]',$blank_details[$blank_count]);
            $answer_list = explode(',',$answer_options[0]);
            echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            echo "<option value=\"u\"></option>\n";
            $no_answers = count($answer_list);
          
            // Shuffle the answers up.
            for ($i=1; $i<26; $i++) {
              $pos1 = rand(0,$no_answers-1);
              $pos2 = rand(0,$no_answers-1);
              if ($pos1 != $pos2) {
                $postemp = $answer_list[$pos2];
                $answer_list[$pos2] = $answer_list[$pos1];
                $answer_list[$pos1] = $postemp;
              }
            }
          
            for ($i=1; $i<=$no_answers; $i++) {
              echo "<option value=\"" . trim($answer_list[$i-1]) . "\">" . trim($answer_list[$i-1]) . "</option>\n";
            }
            echo '</select>';
            echo $answer_options[1]; // Bit after the closing [/blank] tag.
            $blank_count++;
            $part_id++;
          }
          echo '</p>';
          if (count($blank_mark) > 0) {
            foreach ($blank_mark as $individual_mark) {
              $marks += $individual_mark;
            }	   
          }
        }
        break;
      case 'hotspot':
?>        
               
  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function hotspot<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var hotspot<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.hotspot<?php echo $question_no; ?> : document.hotspot<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub hotspot<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call hotspot<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>
 
 <?php
    echo "<div align=\"center\">\n";
    $tmp_bgcolor = $bgcolor;
 ?>
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="hotspot<?php echo $question_no; ?>" width="<?php echo ($options[0]['q_media_width'] + 8); ?>" height="<?php echo ($options[0]['q_media_height'] + 8); ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="hotspot_question.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="<?php echo $tmp_bgcolor; ?>" />
    <param name="FlashVars" value="imageName=<?php echo $options[0]['q_media']; ?>&t&config=<?php echo $options[0]['correct']; ?>&user_answer=" />
    <embed src="hotspot_question.swf" FlashVars="imageName=<?php echo $options[0]['q_media']; ?>&config=<?php echo $options[0]['correct']; ?>&user_answer=" quality="high" bgcolor="<?php echo $tmp_bgcolor; ?>" width="<?php echo ($options[0]['q_media_width'] + 8); ?>" height="<?php echo ($options[0]['q_media_height'] + 8); ?>" swLiveConnect=true id="hotspot<?php echo $question_no; ?>" name="hotspot<?php echo $question_no; ?>" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />
    </object>
    <br /><span style="color:#808080; font-size:80%">(<img src="./artwork/hotspot_cursor.gif" width="15" height="15" alt="cusor" border="0" /> Single click with the mouse to record your answer. Single click again if you need to change that answer.)</span>
  </div>
        
<?php
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"\" />\n";
        $marks = $display_option['marks'];
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $options[0]['correct']);
        $tmp_second_split = explode('\$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 20) $marks++;
        }

        $tmp_height = $options[0]['q_media_height'];
        if ($tmp_height < ($marks * 55)) $tmp_height = ($marks * 55);
?>        
               
  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function label_question<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var label_question<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.label_question<?php echo $question_no; ?> : document.label_question<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub label_question<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call label_question<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>
        
  <?php
    echo "<div align=\"center\">\n";
    $tmp_bgcolor = $bgcolor;
  ?>
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="label_question<?php echo $question_no; ?>" width="<?php echo ($options[0]['q_media_width'] + 220); ?>" height="<?php echo $tmp_height; ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="label_question.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="<?php echo $tmp_bgcolor; ?>" />
    <param name="FlashVars" value="imageName=<?php echo $options[0]['q_media']; ?>&labels=<?php echo str_replace('|','&#124;',$options[0]['correct']); ?>&answers=" />
    <embed src="label_question.swf" FlashVars="imageName=<?php echo $options[0]['q_media']; ?>&labels=<?php echo str_replace('|','&#124;',$options[0]['correct']); ?>&answers=" quality="high" bgcolor="<?php echo $tmp_bgcolor; ?>" width="<?php echo ($options[0]['q_media_width'] + 220); ?>" height="<?php echo $tmp_height; ?>" swLiveConnect=true id="label_question<?php echo $question_no; ?>" name="label_question<?php echo $question_no; ?>" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
    </object>
  </div>
  
<?php
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"\" />\n";
        break;
      case 'flash':
?>        
               
  <script language="JavaScript">
    var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
    function flash<?php echo $question_no; ?>_DoFSCommand(command, args) {
      var flash<?php echo $question_no; ?>Obj = isInternetExplorer ? document.all.flash<?php echo $question_no; ?> : document.flash<?php echo $question_no; ?>;
      document.questions.q<?php echo $question_no; ?>.value = args;
    }
    if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
      document.write('<script language=\"VBScript\"\>\n');
      document.write('On Error Resume Next\n');
      document.write('Sub flash<?php echo $question_no; ?>_FSCommand(ByVal command, ByVal args)\n');
      document.write('	Call flash<?php echo $question_no; ?>_DoFSCommand(command, args)\n');
      document.write('End Sub\n');
      document.write('</script\>\n');
    }
  </script>
        
  <div align="center">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo $display_option['q_media_width']; ?>" height="<?php echo $display_option['q_media_height']; ?>" align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="./media/<?php echo $display_option['q_media']; ?>" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="<?php echo $bgcolor; ?>" />
    <?php
      if ($display_option['scenario'] != '') {
        echo '<param name="FlashVars" value="' . $display_option['scenario'] . '">';
      }
      echo '<embed src="./media/' . $display_option['q_media'] . '"';
      if ($display_option['scenario'] != '') {
        echo ' FlashVars="' . $display_option['scenario'] . '"';
      }
      echo ' quality="high" bgcolor="' . $bgcolor . '" width="' . $display_option['q_media_width'] . '" height="' . $display_option['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />';
    ?>
    </object>
  </div>
      
<?php        
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
        $marks += $display_option['marks'];
        break;
    }                  // End switch
    $i++;
  }                    // End foreach loop

  if ($options[0]['q_type'] == 'mcq') {
    if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
      if ($options[0]['score_method'] == 'vertical_other') {
        echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
      }
      echo '</table></blockquote>';
      $rating_string = '';
      $ratings_array = array();
      $rating_total = 0;
      foreach ($rater_names as $individual_rater) {
        $ratings_array[] = $reviews[$individual_rater][$q_id];
        $rating_total += $reviews[$individual_rater][$q_id];
        $rater_name = $reviews[$individual_rater]['name'];
        if ($rating_string == '') {
          $rating_string = $rater_name . '=' . $reviews[$individual_rater][$q_id] . '%';
        } else {
          $rating_string .= ', ' . $rater_name . '=' . $reviews[$individual_rater][$q_id] . '%';
        }
      }
      
      $review_mean = round($rating_total/count($ratings_array));
      if (isset($reviews[$setter_username][$q_id]) and $reviews[$setter_username][$q_id] != '') {
        $tmp_value = $reviews[$setter_username][$q_id];
        echo "<div>" .  reviewScale('mcq', $q_id, $question_no, $part_no, $tmp_value, 1) . "</div>\n";
      } elseif (max($ratings_array) > (min($ratings_array) + 10) and count($ratings_array) > 0) {
        echo "<div>" .  reviewScale('mcq', $q_id, $question_no, $part_no, $review_mean, 0) . "</div>\n";
      } else {
        echo "<div>" .  reviewScale('mcq', $q_id, $question_no, $part_no, $review_mean, 1) . "</div>\n";
      }
      if (count($ratings_array) > 0) {
        if (max($ratings_array) > (min($ratings_array) + 10)) {
          echo "<div style=\"font-weight:bold\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" />mean=$review_mean%, <span style=\"color:red\">$rating_string</span></div>\n";
        } else {
          echo "<div>mean=$review_mean%, $rating_string</div>\n";
        }
      }
    } elseif ($options[0]['score_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($options[0]['q_type'] == 'calculation') {
    $score_array = explode(',',$options[0]['score_method']);
    echo '<tr><td></td><td>';
    echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" />" . $score_array[2] .  "</p>\n";
    $answer = "echo \"" . $options[0]['correct'] . "\"";
    echo '<input type="hidden" name="q' . $question_no . '_answer" value="';
    echo $A;
    if (isset($B)) echo ",$B";
    if (isset($C)) echo ",$C";
    if (isset($D)) echo ",$D";
    if (isset($E)) echo ",$E";
    if (isset($F)) echo ",$F";
    if (isset($G)) echo ",$G";
    if (isset($H)) echo ",$H";
    echo '" />';
    $marks = 1;
  } elseif ($options[0]['q_type'] == 'dichotomous' or $options[0]['q_type'] == 'mrq' or $options[0]['q_type'] == 'rank') {
    if ($options[0]['score_method'] == 'other') {
      $part_id++;
      echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      echo "<td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
    }
    echo '</table>';
    if ($options[0]['q_type'] == 'mrq') {
      echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_options\" name=\"mrq" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'SelectedPositive' and $q_type < 3) {
        echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_correct_no\" name=\"mrq" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"mrq" . $question_no . "_correct_no\" name=\"mrq" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      }
    } elseif ($options[0]['q_type'] == 'rank') {
      echo "<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n";
      if ($options[0]['score_method'] == 'BonusMark') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n";
      } elseif ($options[0]['score_method'] == 'AllItemsCorrect') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
      } else {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n";
      }
    }
  } elseif ($options[0]['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $options[0]['correct']);

    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . '</p>';
    }
    $text_scenarios = 0;
    for ($part_id=0; $part_id<10; $part_id++) {
      if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') $text_scenarios++;
    }
    $media_scenarios = 0;
    for ($part_id=1; $part_id<=10; $part_id++) {
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
    }
    $scenario_no = max($text_scenarios, $media_scenarios);
    echo '<ol type="i">';
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
      $marks += substr_count($matching_answers[$part_id - 1],'$') + 1;

      echo '<li>';
      if ($matching_scenarios[$part_id - 1] != '') echo "<div>" . $matching_scenarios[$part_id - 1] . "</div>";
      if ($matching_media[$part_id] != '') {
        echo "<p>" . display_media($matching_media[$part_id],$matching_media_width[$part_id],$matching_media_height[$part_id]) . "</p>\n";
      }
      $sub_answers = explode('\$',$matching_answers[$part_id - 1]);
      $list_size = 10;
      if (count($matching_options) < 10) $list_size = count($matching_options);
      if ($answer_no == 1) {
        echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
        echo "<option value=\"u\"></option>\n";
      } else {
        echo "<br />\n";
        echo "<div class=\"option\"><select onchange=\"multimatchingCheck('q" . $question_no . "_" . $part_id . "'," . count($matching_options) . "," . count($sub_answers) . ");\" name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
      }

      $answer_count = 0;
      $tmp_option_no = 1;
      foreach ($matching_options as $single_option) {
        echo "<option value=\"$tmp_option_no\">$single_option</option>\n";
        $tmp_option_no++;
      }
      echo '</select>';
      $rating_string = '';
      $ratings_array = array();
      $rating_total = 0;
      foreach ($rater_names as $individual_rater) {
        $ratings_parts = explode(',',$reviews[$individual_rater][$q_id]);
        $ratings_array[] = $ratings_parts[$part_id - 1];
        $rating_total += $ratings_parts[$part_id - 1];
        $rater_name = $reviews[$individual_rater]['name'];
        if ($rating_string == '') {
          $rating_string = $rater_name . '=' . $ratings_parts[$part_id - 1] . '%';
        } else {
          $rating_string .= ', ' . $rater_name . '=' . $ratings_parts[$part_id - 1] . '%';
        }
      }
      $review_mean = round($rating_total/count($ratings_array));
      
      $rating_part++;
      if ($answer_no == 1) {
        if (isset($reviews[$setter_username][$q_id]) and $reviews[$setter_username][$q_id] != '') {
          $tmp_value = $reviews[$setter_username][$q_id];
          echo '&nbsp;' .  reviewScale('extmatch', $q_id, $question_no, $rating_part, $tmp_value, 1) . '</div></li>';
        } elseif (max($ratings_array) > (min($ratings_array) + 10) and count($ratings_array) > 0) {
          echo '&nbsp;' . reviewScale('extmatch', $q_id, $question_no, $rating_part, $review_mean, 0) . '</div></li>';
        } else {
          echo '&nbsp;' . reviewScale('extmatch', $q_id, $question_no, $rating_part, $review_mean, 1) . '</div></li>';
        }
      } else {
        echo '<div class="no_marks">(Hold down &lt;CTRL&gt; key, then click mouse to toggle options on/off)</div>';      
        echo '</div><br /></li>';
        for ($i=1; $i<=$answer_count; $i++) {
          if (max($ratings_array) > (min($ratings_array) + 10) and count($ratings_array) > 0) {
            echo '<div>' .  reviewScale('extmatch', $q_id, $question_no, $rating_part, $review_mean, 0) . '</div>';
          } else {
            echo '<div>' .  reviewScale('extmatch', $q_id, $question_no, $rating_part, review_mean, 1) . '</div>';
          }
          $rating_part++;
        }
      }
      
      if (count($ratings_array) > 0) {
        if (max($ratings_array) > (min($ratings_array) + 10)) {
          echo "<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" /><span style=\"font-weight:bold\">mean=$review_mean%, </span><span style=\"color:red; font-weight:bold\">$rating_string</span>\n";
        } else {
          echo "mean=$review_mean%, $rating_string\n";
        }
      }
      echo "<br />\n<br />\n";
    }
    echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" /><input type="hidden" name="multimatching' . $question_no . '_correct_no" value="' . $marks . '" />';
  } elseif ($options[0]['q_type'] == 'matrix') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo "<p align=\"center\">" . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . "</p>\n";
    }
    echo '<table cellpadding="2" cellspacing="0" border="1">'; 
    echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "<tr>\n";
    $numerals = array('0','i','ii','iii','iv','v','vi','vii','viii','ix','x');
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo "<tr>\n";
        echo '<td align="right">' . $numerals[$part_id - 1] . '.</td><td>' . $single_scenario . '</td>';
        $answer_no = 1;
        foreach ($matching_options as $single_option) {
          echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" /></div></td>';
          $answer_no++;
        }
        echo "</tr>\n";
        $part_id++;
      }
    }    
    echo '</table>';
    $marks = $part_id - 1;
  }
  
  // Display possible marks for question (if not Survey)
  if ($options[0]['q_type'] != 'hotspot' and $options[0]['q_type'] != 'likert' and $options[0]['q_type'] != 'mcq') echo '</blockquote>';
  if ($q_type < 3) {
    if ($marks == 1) {
      echo "<div style=\"color:#808080; font-size: 80%\">(1 mark)</div>\n<br /></td></tr>\n";
    } elseif ($marks > 1) {
      if ($options[0]['q_type'] == 'dichotomous' and ($options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain' or $options[0]['score_method'] == 'YN_Negative' or $options[0]['score_method'] == 'YN_NegativeAbstain')) {
        echo "<div class=\"no_marks\">($marks marks, negative marking)</div>\n<br /></td></tr>\n";
      } elseif ($options[0]['q_type'] == 'mrq') {
        if ($options[0]['score_method'] == 'SelectedPositive') {
          echo "<div class=\"no_marks\">($marks marks)</div>\n<br /></td></tr>\n";
        } else {
          echo "<div class=\"no_marks\">($marks marks, all options items marked with negative marking)</div>\n<br /></td></tr>\n";
        }
      } elseif ($options[0]['q_type'] == 'rank') {
        if ($options[0]['score_method'] == 'BonusMark') {
          echo "<div class=\"no_marks\">(" . ($marks - 1) . "&nbsp;marks for correct options, plus 1 bonus mark for fully correct order)</div>\n";
        } elseif ($display_option['score_method'] == 'AllItemsCorrect') {
          echo "<div class=\"no_marks\">($marks&nbsp;mark)</div>\n<br /></td></tr>\n";
        } elseif ($options[0]['score_method'] == 'OrderNeighbours') {
          echo "<div class=\"no_marks\">($marks marks, half marks for neighbours)</div>\n<br /></td></tr>\n";
        } else {
          echo "<div class=\"no_marks\">($marks marks, exact order)</div>\n<br /></td></tr>\n";
        }
      } else {
        echo "<div class=\"no_marks\">($marks marks)</div>\n<br /></td></tr>\n";
      }
    }
  }
}
?>
