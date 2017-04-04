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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

$configObject          = Config::get_instance();

function display_std($std_value, $inline=1) {
  $html = '';
  if ($std_value != '') {
    $html .= '<span class="std"';
    if ($inline == 0) $html .= ' style="display:inline"';
    if (is_numeric($std_value)) {
      $html .= '>&nbsp;' . $std_value . '%&nbsp;</span>';
    } elseif ($std_value != '') {
      $std_value = str_replace('exclude_','',$std_value);

      $titles = array('EE'=>'Easy/Essential','EI'=>'Easy/Important','EN'=>'Easy/Nice to know','ME'=>'Medium/Essential','MI'=>'Medium/Important','MN'=>'Medium/Nice to know','HE'=>'Hard/Essential','HI'=>'Hard/Important','HN'=>'Hard/Nice to know');
      $html .= ' title="' . $titles[$std_value] . '">&nbsp;' . $std_value . '&nbsp;</span>';
    }
  }

  return $html;
}

function display_question ($configObject, &$question, $paper_type, $calculator, $current_screen, $old_q_type, &$question_no, &$question_offset, $start_of_day_ts) {
  global $external_review_deadline, $review, $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $A_min, $B_min, $C_min, $D_min, $E_min, $F_min, $G_min, $H_min, $I_min, $J_min, $A_max, $B_max, $C_max, $D_max, $E_max, $F_max, $G_max, $H_max, $I_max, $J_max, $li_set, $bgcolor, $li_set, $used_questions, $user_dismiss, $string, $language, $configObject;
  
  $q_id = $question['q_id'];
  $option_no = count($question['options']);
  $mediadirectory = rogo_directory::get_directory('media');

  // Determine if negative marking is used.
  $neg_marking = false;
  if (isset($question['object']) and method_exists($question['object'], 'is_negative_marked')) {
    $neg_marking = $question['object']->is_negative_marked();
  } else {
    foreach ($question['options'] as $tmp_option) {
      if ($tmp_option['marks_incorrect'] < 0) $neg_marking = true;
    }
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
      if ($calculator == 1) echo '<br /><img src="../artwork/calc.png" class="calc" alt="' . $string['calculator'] . '" />';
      echo "</td><td>";
      if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'area') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img src="../artwork/calc.png" class="calc" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      echo '<p align="center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
      $li_set = 1;
    }
    if ($question['q_type'] != 'likert' and $question['q_type'] != 'enhancedcalc') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img src="../artwork/calc.png" class="calc" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      $li_set = 1;
      if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and in_array($question['q_type'], array('extmatch', 'matrix')))) echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $question['notes'] . '</p>';
      if ($question['q_type'] != 'hotspot') echo $question['leadin'];
    }
  }
  if ($question['q_type'] == 'info') {
    // Special processing of Information Blocks.
    if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:10px; padding-right:10px">';
    if ($question['q_media'] != '') {
      echo '<p align="center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
    }
    echo $question['leadin'];
    $li_set = 1;
    $question_no--;
  }

  $option_no = count($question['options']);
  $part_id = 0;
  $marks = 0;
  $calc_original_vars = array();
  if ($question['q_type'] != 'likert') $old_likert_label = '';

  $std_parts= '' ;
  if (isset($question['std'])) $std_parts = explode(',',$question['std']);
  foreach($question['option_order'] as $key => $value) {
    $qst_order[$value] = $key;
  }

  // Pre-question processing
  switch ($question['q_type']) {
    case 'dichotomous':
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      if (substr($question['display_method'],0,2) == 'TF') {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">" . $string['false'] . "</td>";
      } else {
        echo "<tr><td></td><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">" . $string['no'] . "</td>";
      }
      if (strpos($question['score_method'],'Abstain') !== false) echo "<td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['abstain'] . "</td><td>&nbsp;</td>";
      echo "</tr>\n";
      break;
    case 'mcq':
      echo '<blockquote>';
      if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
        echo '<table cellpadding="2" cellspacing="0" border="0">';
      } elseif ($question['display_method'] == 'dropdown') {
        echo '<div class="option">';
        if (isset($std_parts[0])) echo display_std($std_parts[0],0).' ';
        echo '<select name="q' . $question_no . '" size="1">';
        echo '<option value=""></option>';
      }
      break;
    case 'mrq':
      $mrq_correct = 0;
      if ($question['score_method'] == 'Mark per Question') {
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
        } else {
          $na = false;
        }
        if ($question['notes'] != '') {
          echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"note\"><img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['note'] . "\" />&nbsp;<strong>" . $string['note'] . "</strong>&nbsp;" . $question['notes'] . "</td></tr>\n";
        }
        if ($question['scenario'] != '') {
          echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
        }
        echo '<tr><td></td><td></td>';
        if ($na == true) {
          echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">" . $string['na'] . "</td>";
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
        echo "<tr><td class=\"q_no\">" . ($question_no + $question_offset) . ".&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">" . $string['clinicalvignette'] . "</td></tr>\n";
        echo '<tr><td style="vertical-align:top; text-align:right">';
        if ($calculator == 1) echo '<img src="' . $configObject->get('cfg_root_path') . '/artwork/calc.png" class="calc" alt="' . $string['calculator'] . '" />';
        echo "</td><td>";
        if ($question['notes'] != '') echo '<p class="note"><img src="' . $configObject->get('cfg_root_path') . '/artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $question['notes'] . '</p>';
        echo $question['scenario'] . "<br />\n<br />";
        $li_set = 1;
      }
      if ($question['q_media'] != '') {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
          if ($calculator == 1) echo '<br /><img src="' . $configObject->get('cfg_root_path') . '/artwork/calc.png" class="calc" alt="' . $string['calculator'] . '" />';
          echo '</td><td>';
        }
        echo '<p align="center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
        $li_set = 1;
      }

      $sct_parts = explode('~',$question['leadin']);
      $sct_titles = array(1=>$string['hypothesis'], 2=>$string['investigation'], 3=>$string['prescription'], 4=>$string['intervention'], 5=>$string['treatment']);
      echo '<table cellpadding="2" cellspacing="0" border="0">';
      echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_parts[1] . "</td></tr>\n";
      echo '</table>';

      echo '<p><strong>';
      echo $string['thenthis'] . " " . mb_strtolower($sct_titles[$question['display_method']], 'UTF-8') . " " . $string['is'] . ":";
      echo '</strong></p>';

      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      break;
    case 'true_false':
      if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
        echo '<blockquote style="line-height:175%" class="unans">';
      } else {
        echo '<blockquote style="line-height:175%">';
      }

      $true_checked = '';
      $false_checked = '';
      echo '<table cellpadding="2" cellspacing="0" border="0"><tr><td>';
      if (isset($std_parts[0])) echo display_std($std_parts[0]);
      echo '</td><td>';
      if ($question['display_method'] == 'dropdown') {
        echo "<select name=\"q" . $question_no . "\">\n";
        echo "<option value=\"\"></option>\n";
        if ($question['options'][0]['correct'] == 't') {
          echo "<option value=\"t\" selected>" . $string['true'] . "</option>\n";
        } else {
          echo "<option value=\"t\">" . $string['true'] . "</option>\n";
        }
        if ($question['options'][0]['correct'] == 'f') {
          echo "<option value=\"f\" selected>" . $string['false'] . "</option>\n";
        } else {
          echo "<option value=\"f\">" . $string['false'] . "</option>\n";
        }
        echo "</select>\n";
      } else {
        if ($question['options'][0]['correct'] == 't') $true_checked = ' checked="checked"';
        if ($question['options'][0]['correct'] == 'f') $false_checked = ' checked="checked"';

        echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"t\"$true_checked />&nbsp;True";
        if ($question['display_method'] == 'horizontal') {
          echo '&nbsp;&nbsp;&nbsp;';
        } elseif ($question['display_method'] == 'vertical') {
          echo '<br />';
        }
        echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"f\"$false_checked />&nbsp;False";
      }
      echo '</td><tr></table>';
      echo '</blockquote>';
      break;
  }

  $textboxes_seen = array();
  // Processing for each stem.
  foreach ($question['options'] as $display_option) {
    $part_id++;
    switch ($question['q_type']) {
     case 'area':
        if (isset($user_answers[$current_screen][$q_id])) {
          $tmp_user_answer = $user_answers[$current_screen][$q_id];
          $answer_parts = explode(';', $tmp_user_answer);
          $tmp_user_answer = substr($answer_parts[1], 0, -2);
        } else {
          $tmp_user_answer = '';
        }

        ?>
        <br />
		<?php
			//<!-- ======================== HTML5 part rev disp ================= -->
			echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 2) . "' height='" . ($question['q_media_height'] + 1) . "'></canvas>\n";
			echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
			echo "<script>\n";
			echo "setUpQuestion(" . $question_no . ", 'q" . $question_no . "','" . $language . "', '" . $question['q_media'] . "', '" . $display_option['correct'] . "', '" . $display_option['correct'] . "',1,'#FFC0C0','area','review');\n";
			echo "</script>\n";
			//<!-- ==================================================== -->
		?>
        <input type="hidden" name="q<?php echo $question_no; ?>" id="q<?php echo $question_no; ?>" />
        <?php
        if (isset($std_parts[$part_id-1])) echo "<br /><br />" . display_std($std_parts[$part_id-1]);
        break;
      case 'dichotomous':
        if (($question['display_method'] == 'TF_NegativeAbstain') or ($question['display_method'] == 'TF_NegativeAbstainHalf') or ($question['display_method'] == 'TF_PostiveAbstain') or ($question['display_method'] == "YN_NegativeAbstain")) {
          $abstain = true;
        } else {
          $abstain = false;
        }
        echo '<tr><td>';
        if (isset($std_parts[$question['option_order'][$part_id-1]])) echo display_std($std_parts[$question['option_order'][$part_id-1]]);
        echo '</td><td align="center">';
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
          echo display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
        }
        echo "</td></tr>\n";
        if ($question['score_method'] == 'Mark per Question') {
          $marks = $display_option['marks_correct'];
        } else {
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'enhancedcalc':
        // Load old question data from the session (NB reviewer screens).
        $question['object']->load_all_user_answers($_SESSION['allans']);

        $extra = array('num_on_screen' => $question_no, 'reviewers' => true);
      
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
        $question['object']->render_paper($extra);
        echo '</td>';
        
        $marks += $question['object']->calculate_question_mark();
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
          if ($question['option_order'][$part_id - 1] == $question['options'][0]['correct'] - 1) {
            echo "<tr><td>";
            if (isset($std_parts[0])) echo display_std($std_parts[0]);
            echo "</td><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td>";
          } else {
            echo "<tr><td>";
            echo "</td><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>";
          }
          if ($display_option['option_text'] != '')
            echo "<span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
          if ($display_option['o_media'] != '') {
            if ($display_option['option_text'] != '') echo '<br />';
            echo display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          }
          echo "</td></tr>\n";
        } elseif ($question['display_method'] == 'horizontal') {
          if ($question['option_order'][$part_id - 1] == $question['options'][0]['correct'] - 1) {
            if (isset($std_parts[0])) echo display_std($std_parts[0],0);
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" checked />";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $part_id . "\" />";
          }
          if ($display_option['option_text'] != '') echo '&nbsp;' . $display_option['option_text'];
          if ($display_option['o_media'] != '') echo '&nbsp;' . display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          echo '&nbsp;&nbsp;';
        } else {
          if ($question['option_order'][$part_id - 1] == $question['options'][0]['correct'] - 1) {
            echo "<option value=\"" . $part_id . "\" selected>" . $display_option['option_text'] . "</option>\n";
          } else {
            echo "<option value=\"" . $part_id . "\">" . $display_option['option_text'] . "</option>\n";
          }
        }
        if ($question['option_order'][$part_id - 1] == $display_option['correct'] - 1) $marks = $display_option['marks_correct'];
        break;
      case 'mrq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if ($paper_type == 3) {
          if ($question['options'][$part_id-1]['correct'] == 'y') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td>";
          }
        } else {
          if ($question['options'][$part_id-1]['correct'] == 'y') {
            echo "<tr><td>";
            if (isset($std_parts[$question['option_order'][$part_id-1]])) echo display_std($std_parts[$question['option_order'][$part_id-1]]);
            echo "</td><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td>";
            if (isset($std_parts[$question['option_order'][$part_id-1]])) echo display_std($std_parts[$question['option_order'][$part_id-1]]);
            echo "</td><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td><td>";
          }
        }
        if ($display_option['option_text'] != '') echo "<span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
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

        if ($part_id == 1 and $question['score_method'] == 'Bonus Mark') {
          $marks += $display_option['marks_correct'];  // Add one mark at start in case all options are answered correctly
        }

        echo "<tr><td>";
        if ($question['score_method'] == 'Mark per Question' && $part_id==1 && isset($std_parts[0])) echo display_std($std_parts[0]);
        if ($question['score_method'] != 'Mark per Question' && isset($std_parts[$question['option_order'][$part_id-1]])) echo display_std($std_parts[$question['option_order'][$part_id-1]]);
        echo "</td><td class=\"option\"><select name=\"q" . $question_no . "_" . $part_id . "\" id=\"q" . $question_no . "_" . $part_id . "\" onchange=\"rankCheck(" . $question_no . "," . $part_id . ",$option_no,$total_rank_no);\">\n";
        echo  "<option value=\"9999\"></option>\n";
        if ($require_na) {
          echo  "<option value=\"9990\">" . $string['na'] . "</option>\n";
        }
        for ($i=1; $i<=$total_rank_no; $i++) {
          if ($i == $question['options'][$part_id-1]['correct']) {
            echo "<option value=\"" . $i . "\" selected>" . $i;
          } else {
            echo "<option value=\"" . $i . "\">" . $i;
          }
          if ($language == 'en') {
            if ($i == 1) {
              echo 'st';
            } elseif ($i == 2) {
              echo 'nd';
            } elseif ($i == 3) {
              echo 'rd';
            } else {
              echo 'th';
            }
          }
          echo '</option>';
        }
        echo '</select></td>';
        if ($question['score_method'] == 'Bonus Mark') {
          echo "<td><span class=\"active\" onclick=\"dismissItem('$question_no" . "_" . "$part_id')\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
          if ($part_id == count($question['options']) && isset($std_parts[$part_id])) echo "<tr><td>" . display_std($std_parts[$part_id])."</td><td  colspan=\"2\">" . $string['AllItemsCorrect'] . "</td></tr>";


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
            $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" value=\"" . htmlentities($answer_list[0]) . "\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
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
          echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
          echo '<tr><td>';
          if (($question['score_method'] == 'Mark per Question') && (isset($std_parts[$part_id-1]))) echo display_std($std_parts[$part_id-1]);
          echo '</td><td>';
          echo $blank_details[0];
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
            if (($question['score_method'] == 'Mark per Option') && (isset($std_parts[$part_id-1]))) echo display_std($std_parts[$part_id-1],0)."&nbsp;";
            echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            echo "<option value=\"u\"></option>\n";
            shuffle($answer_list);            // Shuffle the answers up.
            for ($i=1; $i<=count($answer_list); $i++) {
              if (html_entity_decode(trim($answer_list[$i-1])) == trim($correct_answer)) {
                echo "<option value=\"" . htmlentities(trim($answer_list[$i-1]), ENT_COMPAT, "UTF-8") . "\" selected>" . trim($answer_list[$i-1]) . "</option>\n";
              } else {
                echo "<option value=\"" . htmlentities(trim($answer_list[$i-1]), ENT_COMPAT, "UTF-8") . "\">" . trim($answer_list[$i-1]) . "</option>\n";
              }
            }
            echo '</select>';
            echo $answer_options[1]; // Bit after the closing [/blank] tag.
            $blank_count++;
            $part_id++;
          }
          echo "</td></tr></table>";

        }
        if (count($blank_mark) > 0) {
          if ($question['score_method'] == 'Mark per Question') {
            $marks = $display_option['marks_correct'];
          } else {
            foreach ($blank_mark as $individual_mark) {
              $marks += $display_option['marks_correct'];
            }
          }
        }
        break;
      case 'textbox':
        if (!in_array($question_no, $textboxes_seen)) {
          $textboxes_seen[] = $question_no;
          $settings = json_decode($question['settings'], true);
          if (!isset($settings['editor']) or $settings['editor'] == 'plain') {
            if ($settings['rows'] == 1) {
              echo "<blockquote><input type=\"text\" name=\"q" . $question_no . "\" size=\"" . $settings['columns'] . "\" value=\"" . $question['correct_fback'] . "\" />";
            } else {
              echo "<blockquote><textarea name=\"q" . $question_no . "\" cols=\"" . $settings['columns'] . "\" rows=\"" . $settings['rows'] . "\">" . $question['correct_fback'] . "</textarea>";
            }
          } else {
            include_once('../include/wysiwyg_editor.inc');
            echo "<textarea class=\"mceEditor\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" style=\"width:" . (40 + ($settings['columns'] * 8)) . "px; height:" . ($settings['rows'] * 28) . "px\">" . $question['correct_fback'] . "</textarea>\n";
          }

          echo "</blockquote>\n";
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'hotspot':
        echo "<div align=\"left\">";
        $tmp_bgcolor = $bgcolor;
        $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
        $tmp_height = $question['q_media_height'] + 30;
        if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);
        $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);
		//<!-- ======================== HTML5 part rev disp ================= -->
		echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 300) . "' height='" . $tmp_height . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script>\n";
		echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . $tmp_correct . "', '', '1,1,0000000000000000000000000000000000000000','#FFC0C0','hotspot','review');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->
		?>
        </div><br />
        <?php
        $part_std_offset = 0;
        if ($hotspot_no>1) {
          if (isset($std_parts[$part_id-1])) {
            echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
            for ($part_sub=0;$part_sub<$hotspot_no;$part_sub++) {
              echo "<tr><td>" . chr(65+$part_sub) . ".</td>";
              echo "<td>";
              if (isset($std_parts[$part_id-1+$part_sub])) echo display_std($std_parts[$part_id-1+$part_sub]);
              echo "</td>";
              echo "</tr>\n";
              $part_std_offset++;
            }
            $part_std_offset--;
            echo "</table>";
          }
        } else {
          if (isset($std_parts[$part_id-1+$part_std_offset])) echo display_std($std_parts[$part_id-1+$part_std_offset]);
        }

        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"u\" />\n";

        if ($question['score_method'] == 'Mark per Question') {
          $marks = $display_option['marks_correct'];
        } else {
          $marks = (substr_count($question['options'][0]['correct'],'|') + 1) * $display_option['marks_correct'];
        }
        break;
      case 'labelling':
        $tmp_answer = '';
        $labels_correct = 0;
        $labels_correct_array = array();
        $tmp_labels = 0;
        $max_col1 = 0;
        $max_col2 = 0;
        $tmp_first_split = explode(";", $question['options'][0]['correct']);
        $tmp_second_split = explode('|', $tmp_first_split[11]);
        $label_width = $tmp_first_split[5];
        $label_height = $tmp_first_split[6];
        $hyphen = false;
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
            $tmp_labels++;
            if ($label_parts[2] > 219) {
              $marks += $display_option['marks_correct'];
              $tmp_answer .= $label_parts[2] . '$' . ($label_parts[3] - 25) . '$' . $label_parts[4] . '$t$';
              $labels_correct_array[$labels_correct] = $label_parts[4];
              $labels_correct++;
            }
            if ($label_parts[0] < 10) {
              $max_col1 = $label_parts[0];
            } else {
              $max_col2 = $label_parts[0];
            }
          }
        }
        $max_col2-=10;
        $max_label = max($max_col1, $max_col2);
        $tmp_answer = $labels_correct . '$' . $labels_correct . ';' . $tmp_answer;

        if ($question['score_method'] == 'Mark per Question') {
          $marks = $display_option['marks_correct'];
        }

        if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
          $computed_height = round(($label_height + 5) * ceil($tmp_labels / 2)) + 10;
          $tmp_height = max($question['q_media_height'], $computed_height);
        } else {                    // Single column
          $computed_height = round(($label_height + 5) * $tmp_labels) + 10;
          $tmp_height = max($question['q_media_height'], $computed_height);
        }
    echo "<div align=\"center\">\n";
    $tmp_bgcolor = $bgcolor;
?>
<?php
    echo "<canvas class=\"labelling\" id=\"canvas" . $question_no . "\" width=\"" . ($question['q_media_width'] + 220) . "\" height=\"" . $tmp_height . "\"></canvas>\n";
	echo "<br /><div id=\"canvasbox\"></div>\n";
	echo "<script>\n";
	echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . trim($question['options'][0]['correct']) . "', '" . $tmp_answer . "','";
	if (isset($question['std'])) echo $question['std'];
	echo "','#FFC0C0','labelling','review');\n";
	echo "</script>\n";
?>
  </div>
<?php
        $part_std_offset = 0;
          if (isset($std_parts[$part_id-1])) {
            echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
            for ($part_sub=0;$part_sub<$labels_correct;$part_sub++) {
              echo "<tr><td>" . chr(65+$part_sub) . ".</td>";
              echo "<td>";
              if (isset($std_parts[$part_id-1+$part_sub])) echo display_std($std_parts[$part_id-1+$part_sub]);
              echo  "</td><td>" . $labels_correct_array[$part_sub];
              echo "</td>";
              echo "</tr>\n";
              $part_std_offset++;
            }
            $part_std_offset--;
            echo "</table>";
          }

        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
        break;
      case 'flash':
?>
  <script>
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
  <script>
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" id="flash<?php echo $question_no; ?>" width="<?php echo $question['q_media_width']; ?>" height="<?php echo $question['q_media_height']; ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="sameDomain" />');
    write_string('<param name="movie" value="<?php echo $mediadirectory->url($question['q_media']); ?>" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    <?php
      if ($question['scenario'] != '') {
        echo 'write_string(\'<param name="FlashVars" value="' . $question['scenario'] . '">\')';
      }
      echo 'write_string(\'<embed src="' . $mediadirectory->url($question['q_media']) . '"';
      if (isset($display_option['scenario']) and $display_option['scenario'] != '') {
        echo ' FlashVars="' . $question['scenario'] . '"';
      }
      echo ' quality="high" bgcolor="' . $bgcolor . '" width="' . $question['q_media_width'] . '" height="' . $question['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />\');';
    ?>
    write_string('</object>');
  </script>
  </div>
<?php
        echo "<input type=\"hidden\" name=\"q" . $question_no . "\" value=\"0,0\" />\n";
        $marks += $display_option['marks_correct'];
        break;
    }                  // End switch
  }                    // End foreach loop

  if ($question['q_type'] == 'mcq') {
    if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
      if ($question['display_method'] == 'vertical_other') {
        echo "<tr><td></td><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
      }
			if ($display_option['marks_incorrect'] < 0) {
				// Include an abstain option if negative marking is used.
			  if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
					echo "<tr><td></td><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"a\" checked=\"checked\" /></td><td style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</td></tr>\n";
				} else {
					echo "<tr><td></td><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"a\" /></td><td style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</td></tr>\n";
				}
			}
      echo '</table>';
    } elseif ($question['display_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($question['q_type'] == 'enhancedcalc') {
    $settings = json_decode($question['settings'], true);
    $answer_equation = $question['options'][0]['correct'];
    // Put the calculated answer in the session for use in linked calculation questions (N.B only for reviewers screen!!)
    $_SESSION['allans'][$current_screen][$question['q_id']] = $question['object']->useranswer;
    
    echo '<tr><td></td><td>';
    
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

    $answer = '';

    $_SESSION['ans' . $q_id] = $answer;
    $_SESSION['q' . $q_id] = ($question_no + $question_offset);
    echo "<table  cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n<tr><td>";
    if (isset($std_parts[0])) echo "<p>" . display_std($std_parts[0]) . "</p>";
    echo "</td>\n<td></td></tr></table>";
  } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'mrq' or $question['q_type'] == 'rank') {
    if ($question['score_method'] == 'other') {
      $part_id++;
      echo "<tr><td></td><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      echo "<td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"\" /></td></tr>\n";
    } elseif ($question['q_type'] == 'mrq' and $display_option['marks_incorrect'] < 0) {
		  // Include an abstain option if negative marking is used.
			echo "<tr><td></td><td><input type=\"checkbox\" onclick=\"MRQabstain($question_no,$option_no);\" name=\"q" . $question_no . "_abstain\" id=\"q" . $question_no . "_abstain\" value=\"y\" /></td><td><span style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</span></td></tr>";
		}
    echo '</table>';
  } elseif ($question['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $question['options'][0]['correct']);

    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '') . '</p>';
    }

    array_unshift($matching_scenarios, '');
    $max_scenarios = max(count($matching_scenarios), count($matching_media));
    $scenario_no = 0;
    for ($part_id = 1; $part_id < $max_scenarios; $part_id++) {
      if ((isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id],'<img>')) != '')
              or (isset($matching_media[$part_id]) and $matching_media[$part_id] != '')) {
        $scenario_no++;
      }
    }

    $col1_no = ceil(count($matching_options) / 2);
    echo "<table class=\"extmatch_opt_table\">\n<tr><td><ol>\n";
    for ($i=0; $i<count($matching_options); $i++) {

      echo "<li>" . $matching_options[$i] . "</li>";
      if ($i == ($col1_no-1)) {
        echo "</ol></td><td style=\"vertical-align:top\"><ol start=\"" . ($i+2) . "\">";
      }
    }
    echo "</ol></td></tr>\n</table>\n";

    echo '<ol class="extmatch">';
    $part_std_offset = 0;
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
      $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];

      echo '<li>';
      if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') echo '<div>' . $matching_scenarios[$part_id] . '</div>';
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<p>" . display_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '') . "</p>\n";
      }
      $sub_answers = explode('$',$matching_answers[$part_id - 1]);
      $list_size = 10;
      if (count($matching_options) < 10) $list_size = count($matching_options);
        echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
      if (count($sub_answers)>1) {
        echo "<td valign='top'>";
        if (isset($std_parts[$part_id-1+$part_std_offset])) {
          echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
          for ($part_sub=0;$part_sub<count($sub_answers);$part_sub++) {
            echo "<tr><td>" . chr(65+$qst_order[$sub_answers[$part_sub]-1]) . ".</td>";
            echo "<td>" . display_std($std_parts[$part_id-1+$part_std_offset]) . "</td>";
            echo "</tr>\n";
            $part_std_offset++;
          }
          $part_std_offset--;
          echo "</table>";
        }
      } else {
        echo "<td>";
        if (isset($std_parts[$part_id-1+$part_std_offset])) echo display_std($std_parts[$part_id-1+$part_std_offset]);
      }
      echo "</td>\n<td>";
      if ($answer_no == 1) {
        echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
        echo "<option value=\"u\"></option>\n";
      } else {
        echo "<div class=\"option\"><select onchange=\"multimatchingCheck('q" . $question_no . "_" . $part_id . "'," . count($matching_options) . "," . count($sub_answers) . ");\" name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
      }

      $tmp_option_no = 0;
      for ($option_no=0; $option_no<count($matching_options); $option_no++) {
        $tmp_answer_match = false;
        foreach ($sub_answers as $separate_tmp_answer) {
          if ($separate_tmp_answer == ($question['option_order'][$tmp_option_no]+1)) {
            $tmp_answer_match = true;
          }
        }
        if ($tmp_answer_match == true) {
          echo "<option value=\"" . ($question['option_order'][$option_no]+1) . "\" selected>" . chr($option_no+65) . ". " . $matching_options[$option_no] . "</option>\n";
        } else {
          echo "<option value=\"" . ($question['option_order'][$option_no]+1) . "\">" . chr($option_no+65) . ". " . $matching_options[$option_no] . "</option>\n";
        }
        $tmp_option_no++;
      }
      echo '</select>';
      echo "</td></tr></table>";
      if ($answer_no > 1) echo '<div class="mk">' . $string['holddownctrlkey'] . '</div>';
      echo '</div><br /></li>';
    }
    echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" />';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'matrix') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo "<p align=\"center\">" . display_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '') . "</p>\n";
    }
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="1" class="matrix">';
    echo "<tr>\n<td colspan=\"3\">";
    if (isset($std_parts[$part_id-1]) && ($question['score_method'] == 'Mark per Question')) echo display_std($std_parts[$part_id-1],0);
    echo "&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "<tr>\n";
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo "<tr>\n";
        echo '<td align="right">' . chr(64 + $part_id) . '.</td>';
        if (isset($std_parts[$part_id-1]) && ($question['score_method'] == 'Mark per Option')) {
          echo '<td>'.display_std($std_parts[$part_id-1]).'</td><td>';
        } else {
          echo "<td colspan=\"2\">";
        }
        echo $single_scenario . '</td>';
        $col_no = 1;
        foreach ($matching_options as $single_option) {
          $tmp_col_no = $question['option_order'][$col_no - 1] + 1;
          if (isset($matching_answers[$part_id-1]) and $matching_answers[$part_id-1] == $tmp_col_no) {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_col_no . '" checked /></div></td>';
          } else {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_col_no . '" /></div></td>';
          }
          $col_no++;
        }
        echo "</tr>\n";
        $part_id++;
      }
    }
    echo '</table>';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    } else {
      $marks = ($part_id - 1) * $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'sct') {
    echo '</table>';
  }

  // Display possible marks for question (if not Survey)
  if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'likert' and $question['q_type'] != 'info' and $question['q_type'] != 'calculation') echo '</blockquote>';
  if ($paper_type < 3) {
    if ($marks == 0) {
      echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    } else {
      echo "<div class=\"mk\">($marks ";
      if ($marks == 1) {
        echo $string['mark'];
      } else {
        echo $string['marks'];
      }
      if ($neg_marking) echo ', ' . $string['negmarking'];
      if ($question['score_method'] == 'Bonus Mark') {
        $plural = ($display_option['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
        echo ' ' . sprintf($string['bonusmark'], $display_option['marks_correct'], $plural);  // Used on ranking questions
      }
      echo ")</div>\n<br /></td></tr>\n";
    }
  } else {
    echo "</td></tr>\n";
  }

  if ($question['q_type'] != 'info') {
    echo "<tr><td></td><td>";

    echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:700px; font-size:100%\">";
    echo "<tr><td style=\"width:300px; font-weight:bold\">" . sprintf($string['qcomments'], ($question_no + $question_offset)) . "</td>";
    
    $categories = array('ok_comment'=>$string['questionok'], 'minor_comment'=>$string['minorproblems'], 'major_comment'=>$string['majorproblems'], 'cannot_comment'=>$string['cannotcomment']);

    $value_no = 0;
    foreach ($categories as $class => $text) {
      $value_no++;
      if ($review->get_category($q_id) == $value_no) {
        echo "<td class=\"$class\"><input type=\"radio\" name=\"exttype$question_no\" value=\"$value_no\" checked=\"checked\" />" . $text . "</td>";
      } else {
        echo "<td class=\"$class\"><input type=\"radio\" name=\"exttype$question_no\" value=\"$value_no\" />" . $text . "</td>";
      }
    }

    echo "<tr><td colspan=\"5\"><textarea cols=\"80\" rows=\"4\" name=\"extcomments$question_no\" id=\"extcomments$question_no\" class=\"commentsbox\">" . $review->get_comment($q_id) . "</textarea></td></tr>";

    if ($start_of_day_ts > $external_review_deadline and ($review->get_action($q_id) != 'Not actioned' and $review->get_action($q_id) !== null)) {
      echo "<tr><td colspan=\"5\"><strong>" . $configObject->get('cfg_company');
      if ($review->get_action($q_id) == 'Read - actioned') {
        echo '&nbsp;&nbsp;<span style="color:#008000">';
      } else {
        echo '&nbsp;&nbsp;<span style="color:#C00000">';
      }
      echo $review->get_action($q_id) . "</span></strong><br /><textarea cols=\"80\" rows=\"3\" style=\"font-family:Arial,sans-serif; width:100%\" name=\"extreponse$question_no\">" . $review->get_response($q_id) . "</textarea></td></tr>";
    }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n</table>\n";

    echo "</td></tr>\n";
  }
}
?>