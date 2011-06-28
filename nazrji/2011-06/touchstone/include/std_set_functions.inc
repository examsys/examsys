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

require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../include/std_set_shared_functions.inc');

$stdID = 0;

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

function display_options($options,$q_id, $current_theme, $current_scenario, $current_leadin, $current_notes, $q_type, $std_method, $tmp_reviews, $exclude, $is_group) {
  global $marking, $question_no, $question_offset, $part_id, $labelcolor, $old_likert_label, $A, $B, $C, $D, $E, $F, $G, $H, $li_set, $bgcolor, $total_marks, $old_correct_fback, $stdID;
  
  $display_inline = array('textbox', 'dichotomous', 'mrq', 'rank', 'timedate', 'blank', 'flash');
  
  if (array_key_exists($q_id,$exclude)) {
    $tmp_exclude = $exclude[$q_id];
  } else {
    $tmp_exclude = '0000000000000000000000000000000000000000';
  }

  $option_no = count($options);
  $total_rank_no = 0;
  $require_na = false;
  $part_id = 0;
  $marks = 0;
  if ($options[0]['q_type'] != 'likert') $old_likert_label = '';

  if ($options[0]['q_type'] == 'dichotomous') {
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
    if ($marking < 2) {
      if (($options[0]['score_method'] == 'TF_NegativeAbstain') or ($options[0]['score_method'] == 'TF_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'TF_Negative') or ($options[0]['score_method'] == 'TF_Positive')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } elseif (($options[0]['score_method'] == 'YN_NegativeAbstain') or ($options[0]['score_method'] == 'YN_PostiveAbstain')) {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"40\" align=\"center\" style=\"color: $labelcolor; font-size: 90%\">Abstain</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      if ($options[0]['score_method'] == 'TF_Positive' or $options[0]['score_method'] == 'TF_Negative' or $options[0]['score_method'] == 'TF_NegativeAbstain') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">False</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">No</td><td width=\"45%\">&nbsp;</td><td>&nbsp;</td></tr>\n";
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

    for ($i=0; $i<$option_no; $i++) {
      if ($options[$i]['correct'] != 0) {
        $total_rank_no++;
      }
      if ($options[$i]['correct'] == 0) $require_na = true;
    }
  } elseif ($options[0]['q_type'] == 'extmatch' or $options[0]['q_type'] == 'matrix') {
    $matching_scenarios = explode('|', $options[0]['scenario']);
    $matching_media = explode('|', $options[0]['q_media']);
    $matching_media_width = explode('|', $options[0]['q_media_width']);
    $matching_media_height = explode('|', $options[0]['q_media_height']);
    $matching_options = array();
    $matching_users_answers = explode('|', $options[0]['correct']);
  }

  if($options[0]['q_type'] != 'info') {
    $i = 0;
    foreach ($options as $display_option) {
      $part_id++;
      $review_val = (in_array($options[0]['q_type'], $display_inline)) ? get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group) : 0;
      // MRQs and MCQs have multiple parts but are excluded as a whole
      $exclude_this = ($options[0]['q_type'] != 'mcq' and $options[0]['q_type'] != 'mrq') ? substr($tmp_exclude,($part_id-1),1) : substr($tmp_exclude,0,1);
      display_option($options[0], $q_id, $question_no, $part_id, $display_option, $std_method, $is_group, $marks, $exclude_this, $review_val, $matching_options, 0, $tmp_reviews, $tmp_exclude, $option_no, $total_rank_no, $require_na, $bgcolor);
      $i++;
    }                    // End foreach loop
  }

  switch($options[0]['q_type']) {
    case 'mcq':
      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      $scale_html = reviewScale($question_no, '', $std_method, (isset($exclude[$q_id])), $review_val, $stdID, $is_group);
      if ($options[0]['score_method'] == 'vertical' or $options[0]['score_method'] == 'vertical_other') {
        if ($options[0]['score_method'] == 'vertical_other') {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
        }
        echo '</table></blockquote>';
        echo "<div>";
        echo $scale_html;
        echo "</div>\n";
      } elseif ($options[0]['score_method'] == 'dropdown') {
        echo '</select>&nbsp;';
        echo $scale_html;
        echo "</div>\n";
      }
      break;
    case 'calculation':
      $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
      $score_array = explode(',',$options[0]['score_method']);
      if ($li_set == 1) {
        echo '<tr><td></td><td>';
      } else {
        echo '<tr><td class="question_no">' . (($question_no + $question_offset) - 1) . '.&nbsp;</td><td>';
      }
      //$new_leadin = "echo \"<p>$current_leadin</p>\"";
      //eval("$new_leadin;");
      $search = array('$A','$B','$C','$D','$E','$F','$G','$H');
      $replace = array($A,$B,$C,$D,$E,$F,$G,$H);
      echo "<p";
      if(isset($exclude[$q_id])) echo ' style="color:red; text-decoration:line-through"';
      echo ">" . str_ireplace($search,$replace,$current_leadin) . "</p>\n";
      
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" />" . $score_array[2] .  "</p>\n";
      echo "<div>";
      echo reviewScale($question_no, '', $std_method, (isset($exclude[$q_id])), $review_val, $stdID, $is_group);
      echo "</div>\n";
      $marks = 1;
      break;
    case 'dichotomous':
    case 'mrq':
    case 'rank':
      if ($options[0]['score_method'] == 'other') {
        $part_id++;
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
        echo "<td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
      }
      echo '</table>';
      if ($options[0]['q_type'] == 'mrq') {
        echo "</blockquote>\n";
        if ($options[0]['score_method'] == 'AllItemsCorrect') {
          $part_id++;
          $review_val = get_review($options[0]['q_type'], $std_method, $q_id, 1, $tmp_reviews, $is_group);
          echo "<div>";
          echo reviewScale($question_no, 1, $std_method, substr($tmp_exclude,1,1), $review_val, $stdID, $is_group);
          echo "</div>\n";
        }
      } elseif ($options[0]['q_type'] == 'rank') {
        echo "<input type=\"hidden\" id=\"rank" . $question_no . "_options\" name=\"rank" . $question_no . "_options\" value=\"$part_id\" />\n";
        if ($options[0]['score_method'] == 'BonusMark') {
          $review_val = get_review($options[0]['q_type'], $std_method, $q_id, ($part_id+1), $tmp_reviews, $is_group);
          echo "<br />\n<div>All items correct: ";
          echo reviewScale($question_no, ($part_id+1), $std_method, substr($tmp_exclude,0,1), $review_val, $stdID, $is_group);
          echo "</div>\n";
          echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"". ($marks-1) . "\" />\n";
        } elseif ($options[0]['score_method'] == 'AllItemsCorrect') {
          echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$part_id\" />\n";
        } else {
          echo "<input type=\"hidden\" id=\"rank" . $question_no . "_correct_no\" name=\"rank" . $question_no . "_correct_no\" value=\"$marks\" />\n";
        }
      }
      break;
    case 'extmatch':
      $matching_answers = explode('|', $options[0]['correct']);
  
      if ($matching_media[0] != '') {
        echo '<p align="center">' . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . '</p>';
      }
      $text_scenarios = 0;
      for ($part_id=0; $part_id<10; $part_id++) {
        if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') $text_scenarios++;
      }
      $media_scenarios = 0;
      for ($part_id=1; $part_id<10; $part_id++) {
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
      }
      $scenario_no = max($text_scenarios, $media_scenarios);
      echo '<ol type="i">';
      $rating_part = 0;
      for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
        $marks += substr_count($matching_answers[$part_id - 1],'$') + 1;
  
        echo '<li>';
        if ($matching_scenarios[$part_id - 1] != '') {
          echo "<div";
          if (substr($tmp_exclude,$part_id-1,1) == '1') echo ' style="color:red; text-decoration:line-through"';
          echo ">" . $matching_scenarios[$part_id - 1] . "</div>";
        }
        if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
          echo "<p>" . display_media($matching_media[$part_id],$matching_media_width[$part_id],$matching_media_height[$part_id]) . "</p>\n";
        }
        $sub_answers = explode('$',$matching_answers[$part_id - 1]);
        $list_size = 10;
        if (count($matching_options) < 10) $list_size = count($matching_options);
        if ($answer_no == 1) {
          echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
          echo "<option value=\"u\"></option>\n";
        } else {
          echo "<br />\n";
          echo "<div class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
        }
  
        $answer_count = 0;
        $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
        $tmp_option_no = 1;
        foreach ($matching_options as $single_option) {
          $tmp_answer_match = false;
          foreach ($multi_answers as $separate_tmp_answer) {
            if ($separate_tmp_answer == $tmp_option_no) {
              $tmp_answer_match = true;
              $answer_count++;
            }
          }
          if ($tmp_answer_match == true) {
            echo "<option value=\"$tmp_option_no\" selected>$single_option</option>\n";
          } else {
            echo "<option value=\"$tmp_option_no\">$single_option</option>\n";
          }
          $tmp_option_no++;
        }
        echo '</select>';
        if ($answer_no == 1) {
          echo '&nbsp;';
          echo reviewScale($question_no, $part_id, $std_method, substr($tmp_exclude,($part_id - 1),1), $review_val, $stdID, $is_group);
          echo '</div></li>';
          $rating_part++;
        } else {
          echo '<div class="no_marks">(Hold down &lt;CTRL&gt; key, then click mouse to toggle options on/off)</div>';
          echo '</div><br /></li>';
          for ($i=1; $i<=$answer_count; $i++) {
            echo '<div>';
            echo reviewScale($question_no, $part_id, $std_method, (isset($exclude[$q_id])), $review_val, $stdID, $is_group);
            echo '</div>';
            $rating_part++;
          }
        }
        echo '<br />';
      }
      echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" /><input type="hidden" name="multimatching' . $question_no . '_correct_no" value="' . $marks . '" />';
      break;
    case 'labelling':
      $part_id = 1;
      echo "<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
      foreach ($matching_options as $single_option) {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        echo "<tr><td>" . chr($part_id + 64) . ".</td><td>" . $single_option . "</td><td>&nbsp;</td><td>";
        echo reviewScale($question_no, $part_id, $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $is_group);
        echo "</td></tr>\n";
        $part_id++;
        $marks++;
      }
      echo "</table>\n</blockquote>\n";
      break;
    case 'hotspot':
      $part_id = 1;
      echo "<table summary=\"Options for Hotspot question\">\n";
      $excl_string = '';
      if (isset($exclude[$q_id])) {
        $excl_string = ' ;color:red; text-decoration:line-through';
      }
      foreach ($matching_options as $single_option) {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        echo "<tbody><tr><td style=\"padding-right: 12px$excl_string\">" .  chr($part_id+64) . ')&nbsp;' . $single_option . "</td><td>&nbsp;</td><td>";
        echo reviewScale($question_no, $part_id, $std_method, (isset($exclude[$q_id])), $review_val, $stdID, $is_group);
        echo "</td></tr></tbody>\n";
        $part_id++;
      }
      echo '</table>';
      break;
    case 'matrix':
      $part_id = 1;
      if ($matching_media[0] != '') {
        echo "<p align=\"center\">" . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . "</p>\n";
      }
      echo '<table cellpadding="2" cellspacing="0" border="1">';
      echo "<tr><td colspan=\"2\">&nbsp;</td>\n";
      foreach ($matching_options as $single_option) {
        echo '<td>' . $single_option . '</td>';
      }
      echo "<td>&nbsp;</td><tr>\n";
      $numerals = array('0','i','ii','iii','iv','v','vi','vii','viii','ix','x');
      foreach ($matching_scenarios as $single_scenario) {
        $review_val = get_review($options[0]['q_type'], $std_method, $q_id, $part_id, $tmp_reviews, $is_group);
        if (trim($single_scenario) != '') {
          echo '<tr>';
          echo '<td align="right">' . $numerals[$part_id] . '.</td><td';
          if (substr($tmp_exclude,$part_id-1,1) == '1') echo ' style="color:red; text-decoration:line-through"';
          echo '>' . $single_scenario . '</td>';
          $answer_no = 1;
          foreach ($matching_options as $single_option) {
            if ($matching_users_answers[$part_id - 1] == $answer_no) {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" checked /></div></td>';
            } else {
              echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" /></div></td>';
            }
            $answer_no++;
          }
          echo '<td>';
          echo reviewScale($question_no, $part_id, $std_method, substr($tmp_exclude,$part_id - 1,1), $review_val, $stdID, $is_group);
          echo '</td>';
          echo "</tr>\n";
          $part_id++;
        }
      }
      echo '</table>';
      $marks = $part_id - 1;
      break;
  }

  // Display possible marks for question (if not Survey)
  if ($options[0]['q_type'] != 'hotspot' and $options[0]['q_type'] != 'likert' and $options[0]['q_type'] != 'mcq' and $options[0]['q_type'] != 'mrq' and $options[0]['q_type'] != 'calculation') echo '</blockquote>';
  if ($q_type < 3) {
    if ($marks == 1) {
      echo "<div style=\"color: #808080; font-size:80%\">(1 mark)</div>\n<br /></td></tr>\n";
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
    $total_marks += $marks;
  }
}
?>
