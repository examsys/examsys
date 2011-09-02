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

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H, $I, $J;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

function display_question(&$question, $paper_type, $current_screen, $old_q_type, &$question_no, &$question_offset) {
  global $external_review_deadline, $reviews_array, $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $li_set, $bgcolor, $li_set, $calculator, $used_questions, $user_dismiss;

  $q_id = $question['q_id'];
  $option_no = count($question['options']);

  // Determine if negative marking is used.
  $neg_marking = false;
  foreach ($question['options'] as $tmp_option) {
    if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
  }
  
  // Process the order
  $question['option_order'] = array(); 
  if (isset($question['q_option_order']) and ($question['q_option_order'] == 'random' or $question['q_option_order'] == 'alphabetic')) {
    if (!isset($user_order[$current_screen][$q_id]) or $user_order[$current_screen][$q_id] == '') {
      if ($question['q_option_order'] == 'random') {
        for ($i=0; $i<$option_no; $i++) {
          $question['option_order'][$i] = $i;
        }
        shuffle($question['option_order']);
      } elseif ($question['q_option_order'] == 'alphabetic') {
        $tmp_order_array = array();
        for ($i=0; $i<$option_no; $i++) {
          $tmp_order_array[$i] = $question['options'][$i]['option_text'];
        }
        asort($tmp_order_array);
        foreach ($tmp_order_array as $key => $value) {
          $question['option_order'][]= $key;
        }
      } else {
        // Make up the order array in the existing order
        for ($i=0; $i<$option_no; $i++) {
          $question['option_order'][$i] = $i;
        }
      }
    } else {
      // Set the order array to what is stored in the users log record
      $question['option_order'] = explode(',',$user_order[$current_screen][$q_id]);
    }
    
    // Re-arrange the options array
    $new_options = array();
    for ($i=0; $i<$option_no; $i++) {
      $new_options[$i] = $question['options'][$question['option_order'][$i]];
    }
    $question['options'] = $new_options;
  } else {
    // Make up the order array in the existing order
    for ($i=0; $i<$option_no; $i++) {
      $question['option_order'][$i] = $i;
    }
  }
  
  $question_no++;
  $li_set = 0;
  if ($old_q_type == 'likert' and $question['q_type'] != 'likert') {
    echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
    echo "<col width=\"40\"><col>\n";
  } elseif ($old_q_type != 'likert' and $question['q_type'] == 'likert') {
    echo "</table>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
  }

  if ($question['theme'] != '') echo '<tr><td colspan="2"><p class="theme">' . $question['theme'] . '</p></td></tr>';

  if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
    if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert') {
      echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
      if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
      echo "</td><td>";
      if ($question['notes'] != '') echo '<p class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
        echo '</td><td>';
      }
      echo '<p align="center">' . display_media($question['q_media'],$question['q_media_width'],$question['q_media_height']) . "</p>\n";
      $li_set = 1;
    }
    if ($question['q_type'] != 'likert' and $question['q_type'] != 'calculation') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
        echo '</td><td>';
      }
      $li_set = 1;
      if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and $question['q_type'] == 'extmatch')) echo '<p class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $question['notes'] . '</p>';
      if ($question['q_type'] != 'hotspot') echo $question['leadin'];
    }
  }
  if ($question['q_type'] == 'info') {
    // Special processing of Information Blocks.
    if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:10px; padding-right:10px">';
    if ($question['q_media'] != '') {
      echo '<p align="center">' . display_media($question['q_media'],$question['q_media_width'],$question['q_media_height']) . "</p>\n";
    }
    echo $question['leadin'];
    $li_set = 1;
    $question_no--;
  }

  $option_no = count($question['options']);
  $part_id = 0;
  $marks = 0;
  if ($question['q_type'] != 'likert') $old_likert_label = '';

  // Pre-question processing
  switch ($question['q_type']) {
    case 'dichotomous':
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      if (substr($question['score_method'],0,2) == 'TF') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">True</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">False</td>";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">Yes</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">No</td>";
      }
      if (strpos($question['score_method'],'Abstain') !== false) echo "<td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">Abstain</td><td>&nbsp;</td>";
      echo "</tr>\n";
      break;
    case 'mcq':
      echo '<blockquote>';
      if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
        echo '<table cellpadding="2" cellspacing="0" border="0">';
      } elseif ($question['display_method'] == 'dropdown') {
        echo '<div class="option"><select name="q' . $question_no . '" size="1">';
        echo '<option value=""></option>';
      }
      break;
    case 'mrq':
      $mrq_correct = 0;
      if ($question['score_method'] == 'AllItemsCorrect') {
        $mrq_correct = $option_no;
      } else {
        for ($i=0; $i<$option_no; $i++) {
          if ($question['options'][$i]['correct'] == 'y') $mrq_correct++;      
        }
      }
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      break;
    case 'rank':
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      break;
    case 'likert':
      $str = $question['score_method'];
      $likert_display = explode('|',$str);
      $likert_col_no = substr_count($question['score_method'],'|');
      if (strtolower($old_likert_label) != strtolower($str) or $current_theme != '') {
        if ($likert_col_no != $old_likert_cols) echo '</table><table cellpadding="4" cellspacing="0" border="0"><col width="40"><col>';
        if ($likert_display[$likert_col_no] == 'true') {
          $likert_col_no++;
          $na = true;
        }
        if ($question['notes'] != '') {
          echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"notes\"><img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;<strong>NOTE:</strong>&nbsp;" . $question['notes'] . "</td></tr>\n";
        }
        if ($question['scenario'] != '') {
          echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
        }
        echo '<tr><td></td><td></td>';
        if ($na == true) {
          echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">N/A</td>";
        }
        echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[0]</td>";
        $temp_end = substr_count($question['score_method'],'|') - 1;
        for ($i=1; $i<=$temp_end; $i++) {
          echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[$i]</td>\n";
        }
        echo "</tr>\n";
      } else {
        if ($question['scenario'] != '') echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
      }
      $old_likert_label = $str;
      $old_likert_cols = $likert_col_no;
      break;
    case 'extmatch':
    case 'matrix':
      $matching_scenarios = explode('|', $question['scenario']);
      $matching_media = explode('|', $question['q_media']);
      $matching_media_width = explode('|', $question['q_media_width']);
      $matching_media_height = explode('|', $question['q_media_height']);
      $matching_options = array();
      $matching_answers = explode('|', $question['options'][0]['correct']);
      break;
    case 'sct':
      if ($question['scenario'] != '') {
        echo "<tr><td class=\"q_no\">" . ($question_no + $question_offset) . ".&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">Clinical Vignette</td></tr>\n";
        echo '<tr><td style="vertical-align:top; text-align:right">';
        if ($calculator == 1) echo '<a href="#" onclick="openCalculator(); return false;"><img src="/touchstone/artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
        echo "</td><td>";
        if ($question['notes'] != '') echo '<p class="notes"><img src="/touchstone/artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $question['notes'] . '</p>';
        echo $question['scenario'] . "<br />\n<br />";
        $li_set = 1;
      }
      if ($question['q_media'] != '') {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
          if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="/touchstone/artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
          echo '</td><td>';
        }
        echo '<p align="center">' . display_media($question['q_media'],$question['q_media_width'],$question['q_media_height']) . "</p>\n";
        $li_set = 1;
      }
    
      $sct_parts = explode('~',$question['leadin']);
      echo '<table cellpadding="2" cellspacing="0" border="0">';
      echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">Hypothesis</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">New Information</td></tr>\n";
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were thinking of the following diagnosis</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And then you find:</span><br />" . $sct_parts[1] . "</td></tr>\n";
      echo '</table>';
      
      if ($question['score_method'] == 1) {
        echo "\n<p>Then this hypothesis becomes:</p>\n";
      } elseif ($question['score_method'] == 2) {
        echo "\n<p>Then this investigation becomes:</p>\n";
      } elseif ($question['score_method'] == 3) {
        echo "\n<p>Then this treatment becomes:</p>\n";
      }
      
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      break;
  }
  
  // Processing for each stem.
  foreach ($question['options'] as $display_option) {
    $part_id++;
    switch ($question['q_type']) {
      case 'dichotomous':
        if (($question['score_method'] == 'TF_NegativeAbstain') or ($question['score_method'] == 'TF_NegativeAbstainHalf') or ($question['score_method'] == 'TF_PostiveAbstain') or ($question['score_method'] == "YN_NegativeAbstain")) {
          $abstain = true;
        } else {
          $abstain = false;
        }
        echo '<tr><td align="center">';
        if ($question['options'][$part_id-1]['correct'] == 't') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" checked /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
        } elseif ($question['options'][$part_id-1]['correct'] == 'f') {
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" checked /></td>';
        } else {
          // No correct answer set
          echo '<input type="radio" name="q' . $question_no . '_' . $part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="f" /></td>';
        }
        if ($abstain == true) {
          echo "<td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"a\" /></td>";
        }
        echo '<td>';
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
        }
        echo "</td></tr>\n";
        $marks++;
        break;
      case 'calculation':
        $figures = explode(',',$display_option['option_text']);
        switch ($part_id) {
          case 1:
            $A = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $A_min = $figures[0];
            $A_max = $figures[1];
            break;
          case 2:
            $B = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $B_min = $figures[0];
            $B_max = $figures[1];
            break;
          case 3:
            $C = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $C_min = $figures[0];
            $C_max = $figures[1];
            break;
          case 4:
            $D = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $D_min = $figures[0];
            $D_max = $figures[1];
            break;
          case 5:
            $E = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $E_min = $figures[0];
            $E_max = $figures[1];
            break;
          case 6:
            $F = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $F_min = $figures[0];
            $F_max = $figures[1];
            break;
          case 7:
            $G = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $G_min = $figures[0];
            $G_max = $figures[1];
            break;
          case 8:
            $H = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $H_min = $figures[0];
            $H_max = $figures[1];
            break;
          case 9:
            $I = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $I_min = $figures[0];
            $I_max = $figures[1];
            break;
          case 10:
            $J = MathsUtils::gen_random_no(checkVariables($figures[0]), checkVariables($figures[1]), $figures[2], $figures[3]);
            $J_min = $figures[0];
            $J_max = $figures[1];
            break;
        }
        break;
      case 'likert':
        echo '<tr>';
        echo '<td class="q_no">' . (($question_no + $question_offset) - 1) . '.&nbsp;</td><td>' . $question['leadin'] . '</td>';
        $scale_size = substr_count($question['score_method'],'|');
        if ($likert_display[$scale_size] == 'true') echo "<td style=\"background-color:#FFC0C0; text-align:center; width:40px; vertical-align:top\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"n/a\" /></td>";
        for ($i=1; $i<=$scale_size; $i++) {
          echo "<td class=\"likert_button\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"$i\" /></td>";
        }
        echo "</tr>\n";
        break;
      case 'mcq':
        if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
          if ($part_id == $question['options'][0]['correct']) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>";
          }
          if ($display_option['option_text'] != '') echo "<span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
          if ($display_option['o_media'] != '') {
            if ($display_option['option_text'] != '') echo '<br />';
            echo display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height']);
          }          
          echo "</td></tr>\n";
        } elseif ($question['display_method'] == 'horizontal') {
          if ($part_id == $question['options'][0]['correct']) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />";
          }
          if ($display_option['option_text'] != '') echo '&nbsp;' . $display_option['option_text'];
          if ($display_option['o_media'] != '') echo '&nbsp;' . display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
          echo '&nbsp;&nbsp;';
        } else {
          if ($part_id == $question['options'][0]['correct']) {
            echo "<option value=\"" . $part_id . "\" selected>" . $display_option['option_text'] . "</option>\n";
          } else {
            echo "<option value=\"" . $part_id . "\">" . $display_option['option_text'] . "</option>\n";
          }
        }
        if ($part_id == $display_option['correct']) $marks = $display_option['marks_correct'];
        break;
      case 'mrq':
        if ($paper_type == 3) {
          if ($question['options'][$part_id-1]['correct'] == 'y') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td>";
          }
        } else {
          if ($question['options'][$part_id-1]['correct'] == 'y') {
            echo "<tr><td><input type=\"checkbox\" onclick=\"checkMRQ($question_no,$part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" onclick=\"checkMRQ($question_no,$part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td>";
          }
        }
        if ($display_option['option_text'] != '') echo "<span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height']);
        }
        echo "</td></tr>\n";
        if ($question['score_method'] == 'Mark per Option') {
          if ($display_option['correct'] == 'y') $marks += $display_option['marks_correct'];  // Mark for correct options only
        } elseif ($question['score_method'] == 'Mark per Question') {
          if ($part_id == 1) $marks += $display_option['marks_correct'];
        } else {
          $marks += $display_option['marks_correct'];  // Mark for each and every item
        }
        break;
      case 'extmatch':
      case 'matrix':
        $matching_options[] = $display_option['option_text'];
        break;
      case 'rank':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        $total_rank_no = 0;
        $require_na = false;
        for ($i=0; $i<$option_no; $i++) {
          if ($question['options'][$i]['correct'] < 9990) {
            $total_rank_no++;
          }
          if ($question['options'][$i]['correct'] == 9990) $require_na = true;         
        }
           
        if ($part_id == 1 and $question['score_method'] == 'BonusMark') {
          $marks++;  // Add one mark at start in case all options are answered correctly
        }
        
        echo "<tr><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" onchange=\"rankCheck(" . $question_no . "," . $part_id . ",$option_no,$total_rank_no);\">\n";
        echo $question . "<option value=\"9999\"></option>\n";
        if ($require_na) {
          echo $question . "<option value=\"9990\">N/A</option>\n";
        }
        for ($i=1; $i<=$total_rank_no; $i++) {
          if ($i == $question['options'][$part_id-1]['correct']) {
            echo "<option value=\"" . $i . "\" selected>" . $i;
          } else {
            echo "<option value=\"" . $i . "\">" . $i;
          }
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
        if ($question['score_method'] == 'BonusMark') {
          echo "<td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
        } else {
          echo "<td>" . $display_option['option_text'] . "</td>\n</tr>\n";
        }
        if ($question['score_method'] == 'Mark per Question') {
          if ($tmp_part_id == 1) {
            $marks += $display_option['marks_correct'];
          }
        } elseif ($display_option['correct'] != 0) {
          $marks += $display_option['marks_correct'];
        } elseif ($question['score_method'] == 'Mark per Option') {
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'sct':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
        if (substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
          $class_type = 'inactive';
        } else {
          $class_type = 'active';
        }
        if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" onclick=\"dismissItem($question_no,$tmp_part_id)\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . '</span>';
        echo "</td></tr>\n";
        if ($tmp_part_id == $display_option['correct']) $marks = $display_option['marks'];
        break;
      case 'timedate':
        $date_details = explode('|',$question['score_method']);
        $answer_array = explode('/',$display_option['correct']);
        $answer_day = $answer_array[0];
        $answer_month = $answer_array[1];
        $answer_year = $answer_array[2];
        echo '<p>';
        if ($date_details[0] == 4) {
          $answer_day = $answer_array[1];
          $answer_month = $answer_array[0];
          echo '<select name="q' . $question_no . '_month">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
            if ($tmp_month < 10) {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"0$tmp_month\" selected>0$tmp_month</option>\n";
              } else {
                echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
              }
            } else {
              if (intval($answer_month) == $tmp_month) {
                echo "<option value=\"$tmp_month\" selected>$tmp_month</option>\n";
              } else {
                echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
              }
            }
          }
          echo '</select> / ';
        }
        if ($date_details[0] == 1 or $date_details[0] == 2 or $date_details[0] == 3 or $date_details[0] == 4 or $date_details[0] == 5) {
          echo '<select name="q' . $question_no . '_day">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_day=1; $tmp_day<=31; $tmp_day++) {
            if ($tmp_day < 10) {
              if (intval($answer_day) == $tmp_day) {
                echo "<option value=\"0$tmp_day\" selected>0$tmp_day</option>\n";
              } else {
                echo "<option value=\"0$tmp_day\">0$tmp_day</option>\n";
              }
            } else {
              if (intval($answer_day) == $tmp_day) {
                echo "<option value=\"$tmp_day\" selected>$tmp_day</option>\n";
              } else {
                echo "<option value=\"$tmp_day\">$tmp_day</option>\n";
              }
            }
          }
          echo '</select>';
          if ($date_details[0] != 4 and $date_details[0] != 5) {
            echo ' / <select name="q' . $question_no . '_month">';
            echo "\n<option value=\"\"></option>\n";
            for ($tmp_month=1; $tmp_month<=12; $tmp_month++) {
              if ($tmp_month < 10) {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"0$tmp_month\" selected>0$tmp_month</option>\n";
                } else {
                  echo "<option value=\"0$tmp_month\">0$tmp_month</option>\n";
                }
              } else {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"$tmp_month\" selected>$tmp_month</option>\n";
                } else {
                  echo "<option value=\"$tmp_month\">$tmp_month</option>\n";
                }
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
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"0$tmp_month\" selected>$individual_month</option>\n";
                } else {
                  echo "<option value=\"0$tmp_month\">$individual_month</option>\n";
                }
              } else {
                if (intval($answer_month) == $tmp_month) {
                  echo "<option value=\"$tmp_month\" selected>$individual_month</option>\n";
                } else {
                  echo "<option value=\"$tmp_month\">$individual_month</option>\n";
                }
              }
              $tmp_month++;
            }
            echo '</select>';
          }
          echo ' / <select name="q' . $question_no . '_year">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_year=$date_details[1]; $tmp_year<=$date_details[2]; $tmp_year++) {
            if (intval($answer_year) == $tmp_year) {
              echo "<option value=\"$tmp_year\" selected>$tmp_year</option>\n";
            } else {
              echo "<option value=\"$tmp_year\">$tmp_year</option>\n";
            }
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
          for ($tmp_min=0; $tmp_min<60; $tmp_min++) {
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
        if ($date_details[0] == 8) {
          echo '<select name="q' . $question_no . '_hour">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_hour=0; $tmp_hour<=23; $tmp_hour++) {
            echo "<option value=\"$tmp_hour\">$tmp_hour</option>\n";
          }
          echo "</select>\n";
          echo ' : <select name="q' . $question_no . '_minute">';
          echo "\n<option value=\"\"></option>\n";
          for ($tmp_min=0; $tmp_min<60; $tmp_min+=15) {
            if ($tmp_min < 10) {
              echo "<option value=\"0$tmp_min\">0$tmp_min</option>\n";
            } else {
              echo "<option value=\"$tmp_min\">$tmp_min</option>\n";
            }
          }
          echo "</select>\n";
        }
        echo "</p>\n";
        $marks++;
        break;
      case 'blank':
        $blank_mark = array();
        $blank_details = explode("[blank",$display_option['option_text']);
        if ($question['score_method'] == 'textboxes') {
          $array_size = count($blank_details);
          $blank_count = 1;
          while ($blank_count < $array_size) {
            $blank_details[$blank_count] = '[blank' . $blank_details[$blank_count];
            $answer_options = explode('[/blank]',$blank_details[$blank_count]);
            $answer_list = explode(',',str_replace('[blank]', '', $answer_options[0]));
            
            $results = array();
            $not_used = preg_match("|size=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);         
            if (isset($results[1]) and $results[1] != '') {
              $blank_size[$blank_count] = $results[1];
            } else {
              $blank_size[$blank_count] = 15;
            }
            $results=array();
            $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
            if (isset($results[1]) and $results[1] != '') {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" value=\"" . $answer_list[0] . "\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
            $blank_count++;
          }
          $screen_output_all = '';
          if (count($screen_output) > 0) {     // Check there are actually some blanks
            foreach ($screen_output as $value) {
              $screen_output_all .= $value;
            }
          }
          echo "<p class=\"stem\">" . $blank_details[0] . $screen_output_all . "</p>\n";
        } else {
          // Dropdown lists.
          $no_blanks = count($blank_details);
          echo '<p>' . $blank_details[0];
          $blank_count = 1;
          while ($blank_count < $no_blanks) {
            $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
            if (isset($results[1]) and $results[1]) {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = 1;
            }
            $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
            $answer_options = explode("[/blank]",$blank_details[$blank_count]);
            $answer_list = explode(",",$answer_options[0]);
            $correct_answer = $answer_list[0];
            echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            echo "<option value=\"u\"></option>\n";
            shuffle($answer_list);            // Shuffle the answers up.
            for ($i=1; $i<=count($answer_list); $i++) {
              if (html_entity_decode(trim($answer_list[$i-1])) == trim($correct_answer)) {
                echo "<option value=\"" . html_entity_decode(trim($answer_list[$i-1])) . "\" selected>" . trim($answer_list[$i-1]) . "</option>\n";
              } else {
                echo "<option value=\"" . html_entity_decode(trim($answer_list[$i-1])) . "\">" . trim($answer_list[$i-1]) . "</option>\n";
              }
            }
            echo '</select>';
            echo $answer_options[1]; // Bit after the closing [/blank] tag.
            $blank_count++;
            $part_id++;
          }
          echo '</p>';
        }
        if (count($blank_mark) > 0) {
          foreach ($blank_mark as $individual_mark) $marks += $individual_mark;
        }
        break;
      case 'textbox':
        $textbox_size = explode('x',$question['score_method']);
        if ($display_option['option_text'] == 'plain') {
          if ($textbox_size[1] == 1) {
            echo "<blockquote><input style=\"font-family:Arial,sans-serif\" type=\"text\" name=\"q" . $question_no . "\" size=\"" . $textbox_size[0] . "\" value=\"\" />";
          } else {
            echo "<blockquote><textarea style=\"font-family:Arial,sans-serif\" name=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\"></textarea>";
          }
        } else {
          include_once('wysiwyg_editor.inc');
          echo "<p><textarea style=\"font-family:Arial,sans-serif\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\"></textarea></p>\n";
          echo "<script>\n";
          echo "var oEdit$question_no = new InnovaEditor(\"oEdit$question_no\");\n";
          echo "oEdit$question_no.mode=\"XHTMLBody\";\n";
          echo "oEdit$question_no.useTagSelector=false;\n";
          echo "oEdit$question_no.useBR=false;\n";
          echo "oEdit$question_no.width=\"" . ($textbox_size[0] * 7). "px\";\n";
          echo "oEdit$question_no.height=\"" . ($textbox_size[1] * 28) . "px\";\n";
          echo "oEdit$question_no.features=[\"StyleAndFormatting\",\"TextFormatting\",\"ListFormatting\",\"BoxFormatting\",\"ParagraphFormatting\",\"CssText\",\"|\",\"Cut\",\"Copy\",\"PasteText\",\"|\",\"Undo\",\"|\",\"Bold\",\"Italic\",\"Underline\",\"|\",\"Superscript\",\"Subscript\",\"|\",\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"|\",\"Numbering\",\"Bullets\",\"|\",\"Table\",\"Characters\"];\n";
          echo "oEdit$question_no.arrStyle = [[\"BODY\",false,\"\",\"background:white; margin:2px; color:black; font-size:90%; font-family:Arial\"]];\n";
          echo "oEdit$question_no.btnStyles = true;\n";
          echo "oEdit$question_no.REPLACE(\"q$question_no\");\n</script>\n";
        }
        
        echo "</blockquote>\n";
        echo "<pre style=\"font-family:Arial,sans-serif; font-style:italic; color:$labelcolor\"><strong>Model Answer:</strong><br />" . $question['correct_fback'] . "</pre>\n";
        $marks += $display_option['marks'];
        break;
      case 'hotspot':
        echo "<div align=\"left\">";
        $tmp_bgcolor = $bgcolor;
        $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
        $tmp_height = $question['q_media_height'] + 30;
        if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);
        $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        ?>
        <script language="JavaScript">
          function swfLoaded<?php echo $question_no; ?>(message) {
            var num = message.substring(5,message.length);
            setUpFlash(num, message, '<?php echo $question['q_media']; ?>', '<?php echo $tmp_correct; ?>', '', '1,1,0000000000000000000000000000000000000000');
          }
          write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($question['q_media_width'] + 300); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
          write_string('<param name="allowScriptAccess" value="always" />');
          write_string('<param name="movie" value="/touchstone/paper/hotspot_answer.swf" />');
          write_string('<param name="quality" value="high" />');
          write_string('<param name="bgcolor" value="<?php echo $tmp_bgcolor; ?>" />');
          write_string('<embed src="/touchstone/paper/hotspot_answer.swf" quality="high" bgcolor="<?php echo $tmp_bgcolor; ?>" width="<?php echo ($question['q_media_width'] + 300); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
          write_string('</object>');
        </script>
        <br /><span class="mk">(Select a tab firstly then single click with the mouse to record your answer. Single click again if you need to change that answer.)</span>
        </div><br />
        <?php
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"u\" />\n";
        
        if ($question['score_method'] == 'Mark per Question') {
          $marks = $display_option['marks_correct'];
        } else {
          $marks = (substr_count($question['options'][0]['correct'],'|') + 1) * $display_option['marks_correct'];
        }
        break;
      case 'labelling':
        $tmp_labels = 0;
        $max_col1 = 0;
        $max_col2 = 0;
        $tmp_first_split = explode(";", $question['options'][0]['correct']);
        $tmp_second_split = explode('|', $tmp_first_split[8]);
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            $tmp_labels++;
            if ($label_parts[2] > 219) $marks += $display_option['marks_correct'];
            if ($label_parts[0] < 10) {
              $max_col1 = $label_parts[0];
            } else {
              $max_col2 = $label_parts[0];
            }
          }
        }
        $max_col2-=10;
        $max_label = max($max_col1, $max_col2);

        if ($question['score_method'] == 'Mark per Question') {
          $marks = $display_option['marks_correct'];
        }

        $tmp_height = $question['q_media_height'];
        if ($tmp_height < ($tmp_labels * 55)) $tmp_height = ($tmp_labels * 55);
    echo "<div align=\"center\">\n";
    $tmp_bgcolor = $bgcolor;
?>
  <script language="JavaScript">
    function swfLoaded<?php echo $question_no; ?>(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $question['q_media']; ?>', '<?php echo trim($question['options'][0]['correct']); ?>', '','');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($question['q_media_width'] + 220); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="/touchstone/paper/label_answer.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<embed src="/touchstone/paper/label_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($question['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>

  </div>
<?php
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
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
  <script language="JavaScript">
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo $question['q_media_width']; ?>" height="<?php echo $question['q_media_height']; ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="sameDomain" />');
    write_string('<param name="movie" value="../media/<?php echo $question['q_media']; ?>" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    <?php
      if ($question['scenario'] != '') {
        echo 'write_string(\'<param name="FlashVars" value="' . $question['scenario'] . '">\')';
      }
      echo 'write_string(\'<embed src="../media/' . $question['q_media'] . '"';
      if ($display_option['scenario'] != '') {
        echo ' FlashVars="' . $question['scenario'] . '"';
      }
      echo ' quality="high" bgcolor="' . $bgcolor . '" width="' . $question['q_media_width'] . '" height="' . $question['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />\');';
    ?>
    write_string('</object>');
  </script>
  </div>
<?php        
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
        $marks += $display_option['marks'];
        break;
    }                  // End switch
  }                    // End foreach loop

  if ($question['q_type'] == 'mcq') {
    if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
      if ($question['display_method'] == 'vertical_other') {
        echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
      }
      echo '</table>';
    } elseif ($question['display_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($question['q_type'] == 'calculation') {
    $score_array = explode(',',$question['display_method']);
    $answer_equation = $question['options'][0]['correct'];
    if ($li_set == 1) {
      echo '<tr><td></td><td>';
    } else {
      echo "<tr><td></td><td>";
      echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"padding:10px; border: 1px solid #C0C000; background-color:#FFFFC0; width:700px\">\n";
      echo "<tr><td colspan=\"4\"><strong>Information:</strong> (not visible to candidates)</td></tr>";
      echo "<tr><td colspan=\"4\" style=\"text-align:justify\">This is a Calculation type question. Variables are calculated at run-time and will vary for different candidates. The answer, however, is based on a single formula. Candidates will not see <strong>\$A</strong>, etc they will only see the randomly generated figures.</td></tr>";
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>";
      echo "<tr style=\"font-weight:bold\"><td style=\"width:80px; border-bottom: 1px solid #C0C000\">Variable</td><td style=\"width:80px; border-bottom: 1px solid #C0C000\">Generated</td><td style=\"width:80px; border-bottom: 1px solid #C0C000\">Min</td><td style=\"width:460px; border-bottom: 1px solid #C0C000\">Max</td></tr>\n";
      if (isset($A)) echo "<tr><td>\$A</td><td>$A</td><td>$A_min</td><td>$A_max</td></tr>\n";
      if (isset($B)) echo "<tr><td>\$B</td><td>$B</td><td>$B_min</td><td>$B_max</td></tr>\n";
      if (isset($C)) echo "<tr><td>\$C</td><td>$C</td><td>$C_min</td><td>$C_max</td></tr>\n";
      if (isset($D)) echo "<tr><td>\$D</td><td>$D</td><td>$D_min</td><td>$D_max</td></tr>\n";
      if (isset($E)) echo "<tr><td>\$E</td><td>$E</td><td>$E_min</td><td>$E_max</td></tr>\n";
      if (isset($F)) echo "<tr><td>\$F</td><td>$F</td><td>$F_min</td><td>$F_max</td></tr>\n";
      if (isset($G)) echo "<tr><td>\$G</td><td>$G</td><td>$G_min</td><td>$G_max</td></tr>\n";
      if (isset($H)) echo "<tr><td>\$H</td><td>$H</td><td>$H_min</td><td>$H_max</td></tr>\n";
      if (isset($I)) echo "<tr><td>\$I</td><td>$I</td><td>$I_min</td><td>$I_max</td></tr>\n";
      if (isset($J)) echo "<tr><td>\$J</td><td>$J</td><td>$J_min</td><td>$J_max</td></tr>\n";
      echo "<tr><td>Answer</td><td colspan=\"3\">$answer_equation</td></tr>\n";
      echo "</table>\n";
      echo "</td></tr>\n";
    
      echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
      if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
      echo '</td><td>';
    }
    
    $question['leadin'] = str_replace('$A','<span style="background-color:#FFFF80">&nbsp;<strong>$A</strong>&nbsp;</span>' . $A,$question['leadin']);
    $question['leadin'] = str_replace('$B','<span style="background-color:#FFFF80">&nbsp;<strong>$B</strong>&nbsp;</span>' . $B,$question['leadin']);
    $question['leadin'] = str_replace('$C','<span style="background-color:#FFFF80">&nbsp;<strong>$C</strong>&nbsp;</span>' . $C,$question['leadin']);
    $question['leadin'] = str_replace('$D','<span style="background-color:#FFFF80">&nbsp;<strong>$D</strong>&nbsp;</span>' . $D,$question['leadin']);
    $question['leadin'] = str_replace('$E','<span style="background-color:#FFFF80">&nbsp;<strong>$E</strong>&nbsp;</span>' . $E,$question['leadin']);
    $question['leadin'] = str_replace('$F','<span style="background-color:#FFFF80">&nbsp;<strong>$F</strong>&nbsp;</span>' . $F,$question['leadin']);
    $question['leadin'] = str_replace('$G','<span style="background-color:#FFFF80">&nbsp;<strong>$G</strong>&nbsp;</span>' . $G,$question['leadin']);
    $question['leadin'] = str_replace('$H','<span style="background-color:#FFFF80">&nbsp;<strong>$H</strong>&nbsp;</span>' . $H,$question['leadin']);
    $question['leadin'] = str_replace('$I','<span style="background-color:#FFFF80">&nbsp;<strong>$G</strong>&nbsp;</span>' . $I,$question['leadin']);
    $question['leadin'] = str_replace('$J','<span style="background-color:#FFFF80">&nbsp;<strong>$H</strong>&nbsp;</span>' . $J,$question['leadin']);
    
    echo "<p>" . $question['leadin'] . "</p>";
    
    eval ("\$answer = $answer_equation;");
    $answer = round($answer, $score_array[0]);
    
    echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" value=\"$answer\" />" . $score_array[2] . "</p>\n";
    $marks = 1;
  } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'mrq' or $question['q_type'] == 'rank') {
    if ($question['score_method'] == 'other') {
      $part_id++;
      echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      echo "<td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
    }
    echo '</table>';
  } elseif ($question['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $question['options'][0]['correct']);

    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0], $matching_media_width[0], $matching_media_height[0]) . '</p>';
    }
    $text_scenarios = 0;
    for ($part_id=0; $part_id<10; $part_id++) {
      if (isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id])) != '') $text_scenarios++;
    }
    $media_scenarios = 0;
    for ($part_id=1; $part_id<=10; $part_id++) {
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
    }
    $scenario_no = max($text_scenarios, $media_scenarios);
    echo '<ol class="extmatch">';
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
      $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];
      
      echo '<li>';
      if (isset($matching_scenarios[$part_id - 1]) and $matching_scenarios[$part_id - 1] != '') echo '<div>' . $matching_scenarios[$part_id - 1] . '</div>';
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<p>" . display_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id]) . "</p>\n";
      }
      $sub_answers = explode('$',$matching_answers[$part_id - 1]);
      $list_size = 10;
      if (count($matching_options) < 10) $list_size = count($matching_options);
      if ($answer_no == 1) {
        echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
        echo "<option value=\"u\"></option>\n";
      } else {
        echo "<br />\n";
        echo "<div class=\"option\"><select onchange=\"multimatchingCheck('q" . $question_no . "_" . $part_id . "'," . count($matching_options) . "," . count($sub_answers) . ");\" name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
      }
      
      $tmp_option_no = 1;
      foreach ($matching_options as $single_option) {
        $tmp_answer_match = false;
        foreach ($sub_answers as $separate_tmp_answer) {
          if ($separate_tmp_answer == $tmp_option_no) {
            $tmp_answer_match = true;
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
      if ($answer_no > 1) echo '<div class="mk">(Hold down &lt;CTRL&gt; key, then click mouse to toggle options on/off)</div>';      
      echo '</div><br /></li>';
    }
    echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" />';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'matrix') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo "<p align=\"center\">" . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0]) . "</p>\n";
    }
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="1" class="matrix">';
    //echo '<table cellpadding="2" cellspacing="0" border="1">'; 
    echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "<tr>\n";
    $numerals = array('i','ii','iii','iv','v','vi','vii','viii','ix','x','xi','xii');
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo "<tr>\n";
        echo '<td align="right">' . $numerals[$part_id - 1] . '.</td><td>' . $single_scenario . '</td>';
        $answer_no = 1;
        foreach ($matching_options as $single_option) {
          if ($matching_answers[$part_id-1] == $answer_no) {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" checked /></div></td>';
          } else {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $answer_no . '" /></div></td>';
          }
          $answer_no++;
        }
        echo "</tr>\n";
        $part_id++;
      }
    }    
    echo '</table>';
    $marks = $part_id - 1;
  } elseif ($question['q_type'] == 'sct') {
    echo '</table>';
  }
  
  // Display possible marks for question (if not Survey)
  if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'likert' and $question['q_type'] != 'info' and $question['q_type'] != 'calculation') echo '</blockquote>';
  if ($paper_type < 3) {
    if ($marks == 0) {
      echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    } else {
      echo "<div class=\"mk\">($marks mark";
      if ($marks <> 1) echo 's';
      if ($neg_marking) echo ', negative marking';
      if ($question['score_method'] == 'Bonus Mark') echo ' for correct options, plus 1 bonus mark for fully correct order';  // Used on ranking questions
      echo ")</div>\n<br /></td></tr>\n";
    }
  } else {
    echo "</td></tr>\n";
  }

  if ($question['q_type'] != 'info') {
    echo "<tr><td></td><td>";
      
    echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:900px; font-size:100%\">";
    echo "<tr><td style=\"width:360px; font-weight:bold\">Q" . ($question_no + $question_offset) . " Comments</td>";
    if (isset($reviews_array[$q_id]['category'])) {
      if ($reviews_array[$q_id]['category'] == 1) {
        echo "<td style=\"background-color:#C0FFC0; color:#004000; background-image:url('../artwork/ok_background.png'); background-repeat:repeat-x; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"1\" checked />&nbsp;Question OK</td><td style=\"background-color:#FFFFC0; background-image:url('../artwork/minor_background.png'); background-repeat:repeat-x; color:#404000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"2\" />&nbsp;Minor/some problems</td><td style=\"background-color:#FFC0C0; background-image:url('../artwork/major_background.png'); background-repeat:repeat-x; color:#800000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"3\" />&nbsp;Major/many problems</td></tr>\n";
      } elseif ($reviews_array[$q_id]['category'] == 2) {
        echo "<td style=\"background-color:#C0FFC0; color:#004000; background-image:url('../artwork/ok_background.png'); background-repeat:repeat-x; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"1\" />&nbsp;Question OK</td><td style=\"background-color:#FFFFC0; background-image:url('../artwork/minor_background.png'); background-repeat:repeat-x; color:#404000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"2\" checked />&nbsp;Minor/some problems</td><td style=\"background-color:#FFC0C0; background-image:url('../artwork/major_background.png'); background-repeat:repeat-x; color:#800000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"3\" />&nbsp;Major/many problems</td></tr>\n";
      } else {
        echo "<td style=\"background-color:#C0FFC0; color:#004000; background-image:url('../artwork/ok_background.png'); background-repeat:repeat-x; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"1\" />&nbsp;Question OK</td><td style=\"background-color:#FFFFC0; background-image:url('../artwork/minor_background.png'); background-repeat:repeat-x; color:#404000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"2\" />&nbsp;Minor/some problems</td><td style=\"background-color:#FFC0C0; background-image:url('../artwork/major_background.png'); background-repeat:repeat-x; color:#800000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"3\" checked />&nbsp;Major/many problems</td></tr>\n";
      }
    } else {
      echo "<td style=\"background-color:#C0FFC0; color:#004000; background-image:url('../artwork/ok_background.png'); background-repeat:repeat-x; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"1\" checked />&nbsp;Question OK</td><td style=\"background-color:#FFFFC0; background-image:url('../artwork/minor_background.png'); background-repeat:repeat-x; color:#404000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"2\" />&nbsp;Minor/some problems</td><td style=\"background-color:#FFC0C0; background-image:url('../artwork/major_background.png'); background-repeat:repeat-x; color:#800000; text-align:center; width:180px\"><input type=\"radio\" name=\"exttype$question_no\" value=\"3\" />&nbsp;Major/many problems</td></tr>\n";
    }
    echo "<tr><td colspan=\"4\"><textarea cols=\"80\" rows=\"3\" style=\"font-family:Arial,sans-serif; width:898px\" name=\"extcomments$question_no\">";
    if (isset($reviews_array[$q_id]['comment'])) echo $reviews_array[$q_id]['comment']; 
    echo "</textarea></td></tr>";
    if (date("Ymd") > $external_review_deadline and isset($reviews_array[$q_id]['action']) and $reviews_array[$q_id]['action'] != 'Not actioned') {
      echo "<tr><td colspan=\"4\"><strong>UoN Reponse: ";
      if ($reviews_array[$q_id]['action'] == 'Read - actioned') {
        echo '<span style="color:#008000">';
      } else {
        echo '<span style="color:#C00000">';
      }
      echo $reviews_array[$q_id]['action'] . "</span></strong><br /><textarea cols=\"80\" rows=\"3\" style=\"font-family:Arial,sans-serif; width:898px\" name=\"extreponse$question_no\">" . $reviews_array[$q_id]['response'] . "</textarea></td></tr>";
    }    
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n</table>\n";
      
    echo "</td></tr>\n";
  }
}
?>