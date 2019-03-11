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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

function display_question(&$question, $paper_type, $current_screen, $old_q_type, &$question_no, &$question_offset, $user_answers) {
  global $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $li_set, $bgcolor, $li_set, $used_questions, $user_dismiss, $user_order, $string, $language;

  $mediadirectory = rogo_directory::get_directory('media');
  $q_id = $question['q_id'];
  $option_no = count($question['options']);
  $configObj = Config::get_instance();
  $questiondata = \questiondata::get_datastore($question['q_type']);
  $render = new render($configObj);
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
      $question['option_order'] = explode(',', $user_order[$current_screen][$q_id]);
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
  if ($old_q_type != '') {
    if ($old_q_type == 'likert' and $question['q_type'] != 'likert') {
      echo "</table>\n<br />\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
      echo "<col width=\"40\"><col>\n";
    } elseif ($old_q_type != 'likert' and $question['q_type'] == 'likert') {
      echo "</table>\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
    }
  }

  if ($question['theme'] != '') echo '<tr><td colspan="2" class="theme">' . $question['theme'] . '</td></tr>';

  if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
    if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert') {
      echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
      if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'enhancedcalc' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
      }
      echo '<div class="mediadiv">';
      $questiondata->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');
      $render->render($questiondata, $string, 'paper/media.html');
      echo "</div>\n";
      $li_set = 1;
    }
    if ($question['q_type'] != 'likert' and $question['q_type'] != 'enhancedcalc') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
      }
      $li_set = 1;
      if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and $question['q_type'] == 'extmatch')) echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      if ($question['q_type'] != 'hotspot') echo $question['leadin'];
    }
  }
  if ($question['q_type'] == 'info') {
    // Special processing of Information Blocks.
    if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:10px; padding-right:10px">';
    if ($question['q_media'] != '') {
      echo '<div class="mediadiv">';
      $questiondata->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');
      $render->render($questiondata, $string, 'paper/media.html');
      echo "</div>\n";
    }
    echo $question['leadin'];
    $li_set = 1;
    $question_no--;
  }

  $part_id = 0;
  $marks = 0;
  if ($question['q_type'] != 'likert') $old_likert_label = '';

  // Pre-question processing
  switch ($question['q_type']) {
    case 'enhancedcalc':
      $A = $B = $C = $D = $E = $F = $G = $H = $I = $J = '';    // Reset all variables initially
      break;
    case 'dichotomous':
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      if (substr($question['display_method'],0,2) == 'TF') {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">" . $string['false'] . "</td>";
      } else {
        echo "<tr><td align=\"center\" width=\"40\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td width=\"40\" align=\"center\" style=\"color:$labelcolor;font-size:90%\">" . $string['no'] . "</td>";
      }
      if (strpos($question['display_method'],'Abstain') !== false) echo "<td width=\"40\" align=\"center\" style=\"color:$labelcolor; font-size:90%\">" . $string['abstain'] . "</td><td>&nbsp;</td>";
      echo "</tr>\n";
      break;
    case 'mcq':
      echo '<blockquote>';
      echo '<table cellpadding="2" cellspacing="0" border="0">';
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
      if (isset($user_answers[$current_screen][$q_id])) {
        $answer_parts = explode(':', $user_answers[$current_screen][$q_id]);
        $len_answer = strlen($answer_parts[0]);
      } else {
        $len_answer = 0;
      }
      if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
        echo '<blockquote class="unans"><table cellpadding="2" cellspacing="0" border="0">';
      } else {
        echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      }
      break;
    case 'rank':
      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      break;
    case 'likert':
      $na = false;
      $likert_display = explode('|',$question['display_method']);
      $likert_col_no = substr_count($question['display_method'],'|');

      if (strtolower($old_likert_label) != strtolower($question['display_method']) or $question['theme'] != '') {
        if ($likert_col_no != $old_likert_cols) {
          echo '</table><table cellpadding="4" cellspacing="0" border="0"><col width="40"><col>';
        }
        if ($likert_display[$likert_col_no] == 'true') {
          $likert_col_no++;
          $na = true;
        }
        if ($question['notes'] != '') {
          echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"note\"><img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"Note\" />&nbsp;<strong>NOTE:</strong>&nbsp;" . $question['notes'] . "</td></tr>\n";
        }
        if ($question['scenario'] != '') {
          echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
        }
        echo '<tr><td></td><td></td>';
        if ($na == true) {
          echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">" . $string['na'] . "</td>";
        }
        echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[0]</td>";
        $temp_end = substr_count($question['display_method'],'|') - 1;
        for ($i=1; $i<=$temp_end; $i++) {
          echo "<td valign=\"bottom\" style=\"text-align:center; color:$labelcolor; font-size:80%\">$likert_display[$i]</td>\n";
        }
        echo "</tr>\n";
      } else {
        if ($question['scenario'] != '') echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
      }

      $old_likert_label = $question['display_method'];
      $old_likert_cols = $likert_col_no;
      break;
    case 'extmatch':
    case 'matrix':
      $matching_scenarios = explode('|', $question['scenario']);
      $matching_media = explode('|', $question['q_media']);
      $matching_media_width = explode('|', $question['q_media_width']);
      $matching_media_height = explode('|', $question['q_media_height']);
      $matching_options = array();
      if (isset($user_answers[$current_screen][$q_id])) {
        $matching_users_answers = explode('|', $user_answers[$current_screen][$q_id]);
      } else {
        $matching_users_answers = array();
      }
      $matching_answers = explode('|', $question['options'][0]['correct']);
      break;
    case 'sct':
      if ($question['scenario'] != '') {
        echo "<tr><td class=\"question_no\">" . ($question_no + $question_offset) . ".&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">" . $string['clinicalvignette'] . "</td></tr>\n";
        echo '<tr><td style="vertical-align:top; text-align:right"></td><td>';
        if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
        echo $question['scenario'] . "<br />\n<br />";
        $li_set = 1;
      }
      if ($question['q_media'] != '') {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
        }
        echo '<div class="mediadiv">';
        $questiondata->set_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '');
        $render->render($questiondata, $string, 'paper/media.html');
        echo "</div>\n";
        $li_set = 1;
      }

      $sct_parts = explode('~',$question['leadin']);
      echo '<table cellpadding="2" cellspacing="0" border="0" style="width:100%">';
      $sct_titles = array(1=>$string['hypothesis'], 2=>$string['investigation'], 3=>$string['prescription'], 4=>$string['intervention'], 5=>$string['treatment']);
      echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_titles[$question['display_method']] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string['newinformation'] . "</td></tr>\n";
      echo "<tr><td style=\"width:49%; vertical-align:top\">" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\">" . $sct_parts[1] . "</td></tr>\n";
      echo "</table>\n";

      echo '<p><strong>';
      echo $string['thenthis'] . " " . mb_strtolower($sct_titles[$question['display_method']], 'UTF-8') . " " . $string['is'] . ":";
      echo '</strong></p>';

      echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';

      $marks = 1;
      break;
    case 'true_false':
      echo '<blockquote style="line-height:175%"><table>';
      echo '<td align="center"><img src="../artwork/print_box_circle.png" width="24" height="24" alt="" />&nbsp;True';
      if ($question['display_method'] == 'horizontal') {
        echo '&nbsp;&nbsp;&nbsp;';
      } else {
        echo '<br />';
      }
      echo '<td align="center"><img src="../artwork/print_box_circle.png" width="24" height="24" alt="" />&nbsp;False';

      echo '</table></blockquote>';
      break;
  }

  // Processing for each stem.
  foreach ($question['options'] as $display_option) {
    $part_id++;
    switch ($question['q_type']) {
      case 'dichotomous':
        if (isset($user_answers[$current_screen][$q_id])) {
          $tmp_user_answer = substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1);
        } else {
          $tmp_user_answer = '';
        }
        if (($question['display_method'] == 'TF_NegativeAbstain') or ($question['display_method'] == 'TF_NegativeAbstainHalf') or ($question['display_method'] == 'TF_PostiveAbstain') or ($question['display_method'] == "YN_NegativeAbstain")) {
          $abstain = true;
        } else {
          $abstain = false;
        }

        echo '<tr><td align="center">';
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        echo '<img src="../artwork/print_box_circle.png" width="24" height="24" alt="" /></td><td align="center"><img src="../artwork/print_box_circle.png" width="24" height="24" alt="" /></td>';
        if ($abstain == true) {
          echo '<td align=\"center\"><img src="../artwork/print_box_circle.png" width="24" height="24" alt="" /></td>';
        }
        echo '<td>';
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          $render->render($questiondata, $string, 'paper/media.html');
        }
        echo "</td></tr>\n";
        $marks += $display_option['marks_correct'];
        break;
      case 'enhancedcalc':
        break;
      case 'likert':
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>' . $question['leadin'] . '</td>';
        $scale_size = substr_count($question['display_method'],'|');
        if ($likert_display[$scale_size] == 'true') {
          echo '<td style="text-align:center; width:40px; vertical-align:top"><img src="../artwork/print_box_tick.png" width="23" height="23" alt="" /></td>';
        }
        for ($i=1; $i<=$scale_size; $i++) {
          echo '<td class=\"likert_button\"><img src="../artwork/print_box_tick.png" width="23" height="23" alt="" /></td>';
        }
        echo "</tr>\n";
        break;
      case 'mcq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
				echo '<tr><td><img src="../artwork/print_box_circle.png" width="24" height="24" alt="" /></td><td>';
				if ($display_option['option_text'] != '') echo $display_option['option_text'];
				if ($display_option['o_media'] != '') {
					if ($display_option['option_text'] != '') echo '<br />';
					$questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
					$render->render($questiondata, $string, 'paper/media.html');
				}
				echo "</td></tr>\n";
        if ($tmp_part_id == $display_option['correct']) $marks = $display_option['marks_correct'];
        break;
      case 'mrq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        echo '<tr><td><img src="../artwork/print_box_tick.png" width="23" height="23" alt="" /></td><td>';
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          $questiondata->set_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          $render->render($questiondata, $string, 'paper/media.html');
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
        if (isset($user_answers[$current_screen][$q_id])) {
          $rank_answers = explode(',', $user_answers[$current_screen][$q_id]);
        } else {
          $rank_answers = '';
        }
        $total_rank_no = 0;
        $require_na = false;
        for ($i=0; $i<$option_no; $i++) {
          if ($question['options'][$i]['correct'] != 0 or $paper_type == '3') {
            $total_rank_no++;
          }
          if ($question['options'][$i]['correct'] == 0) $require_na = true;
        }
        $tmp_user_answers = 0;
        if (is_array($rank_answers)) {
          for ($i = 1; $i < count($rank_answers); $i++) {
            if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
              $tmp_user_answers++;
            }
          }
        }

        echo '<tr><td class="option"><img src="../artwork/print_box_1.png" width="30" height="30" /></td>';

        echo "<td>" . $display_option['option_text'] . "</td>\n</tr>\n";
        if ($display_option['correct'] != 0) {
          $marks += $display_option['marks_correct'];
        } elseif ($question['score_method'] == 'Mark per Option') {
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'sct':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        echo "</td></tr>\n";
        break;
      case 'blank':
        if ($question['display_method'] == 'dropdown') {
          $blank_details = explode('[blank',$display_option['option_text']);
          echo '<table class="extmatch_opt_table"><tr>';

          $no_blanks = count($blank_details);
          $blank_count = 1;
          while ($blank_count < $no_blanks) {
            echo '<td style="vertical-align:top"><strong>' . $blank_count . '.</strong><ul type="A">';
            $not_used = preg_match("|mark=\"([0-9]{1,3})\"|",$blank_details[$blank_count],$results);
            if (isset($results[1]) and $results[1] != '') {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = $display_option['marks_correct'];
            }
            $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
            $answer_options = explode('[/blank]',$blank_details[$blank_count]);
            $answer_list = explode(',',$answer_options[0]);
            $answer_list = param::clean_array($answer_list, param::TEXT);
            shuffle($answer_list);            // Shuffle the answers up.
            for ($i=1; $i<=count($answer_list); $i++) {
              echo '<li>' . htmlentities(trim($answer_list[$i-1])) . '</li>';
            }
            $blank_count++;
            echo '</ul></td>';
          }
          echo '</tr></table>';
        }

        $blank_details = explode('[blank',$display_option['option_text']);
        $ans = '';
        $blank_mark = array();
        $display_option['option_text'] = str_replace('&nbsp;',' ',$display_option['option_text']);

        if (isset($user_answers[$current_screen][$q_id])) {
          $user_answers[$current_screen][$q_id] = str_replace('&nbsp;',' ',$user_answers[$current_screen][$q_id]);
        }

        if (isset($user_answers[$current_screen][$q_id])) {
          $blank_user_answers = json_decode($user_answers[$current_screen][$q_id]);
          $blank_user_answers = param::clean_array($blank_user_answers, param::TEXT);
        } else {
          $blank_user_answers = array();
        }

        if ($question['display_method'] == 'textboxes') {
          $screen_output = array();
          $array_size = count($blank_details);
          $blank_count = 1;
          while ($blank_count < $array_size) {
            $blank_details[$blank_count] = '[blank]' . $blank_details[$blank_count];
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
              $blank_mark[$blank_count] = $display_option['marks_correct'];
            }

            $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" style=\"border:1px solid black\" name=\"q" . $question_no . "_" . $blank_count . "\" value=\"\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);

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
            if (isset($results[1]) and $results[1] != '') {
              $blank_mark[$blank_count] = $results[1];
            } else {
              $blank_mark[$blank_count] = $display_option['marks_correct'];
            }
            $blank_details[$blank_count] = substr($blank_details[$blank_count], (strpos($blank_details[$blank_count],']') + 1));
            $answer_options = explode('[/blank]',$blank_details[$blank_count]);
            $answer_list = explode(',',$answer_options[0]);
            $answer_list = param::clean_array($answer_list, param::TEXT);
            echo '<strong>' . $blank_count . '.</strong>&nbsp;<img src="../artwork/print_box_a.png" width="23" height="23" alt="" /> ';

            echo $answer_options[1]; // Bit after the closing [/blank] tag.
            $blank_count++;
            $part_id++;
          }
          echo '</p>';
        }
        if ($question['score_method'] == 'Mark per Option') {
          if (count($blank_mark) > 0) {
            foreach ($blank_mark as $individual_mark) $marks += $individual_mark;
          }
        } else {
          $marks = $display_option['marks_correct'];
        }
        break;
      case 'textbox':
        $settings = json_decode($question['settings'], true);

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
          echo "<blockquote><textarea class=\"unans\" name=\"q" . $question_no . "\" cols=\"" . $settings['columns'] . "\" rows=\"" . $settings['rows'] . "\">" . $user_answers[$current_screen][$q_id] . "</textarea></blockquote>\n";
        } else {
          $ans = '';
          if (isset($user_answers[$current_screen][$q_id])) {
            $ans = $user_answers[$current_screen][$q_id];
          }
          echo "<blockquote><textarea style=\"border:1px solid black; overflow:hidden; width:100%\" name=\"q" . $question_no . "\" cols=\"" . $settings['columns'] . "\" rows=\"" . ($settings['rows'] * 2) . "\"></textarea></blockquote>\n";
        }
        $marks += $display_option['marks_correct'];
        break;
      case 'hotspot':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and  $screen_pre_submitted == 1) {
          echo "<div align=\"left\" class=\"unans\">";
          $tmp_bgcolor = '#FFC0C0';
        } else {
          echo "<div align=\"left\">";
          $tmp_bgcolor = $bgcolor;
        }
        $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
        $tmp_height = $question['q_media_height'] + 30;
        if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);

        $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

		//<!-- ======================== HTML5 part include disp ================= -->
		echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 300) . "' height='" . ($tmp_height-29) . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script language='JavaScript' type='text/javascript'>\n";
		echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . $tmp_correct . "', '";
		if (isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]);
		echo "', '" . $screen_pre_submitted . "','#FFC0C0','hotspot','answer');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->
		echo "</div>\n";

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] != '') {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"" . $user_answers[$current_screen][$q_id] . "\" />\n";
        } else {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"u\" />\n";
        }
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
        $tmp_second_split = explode('|', $tmp_first_split[11]);
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
        if ($tmp_height < ($max_label * 55)) $tmp_height = ($max_label * 55);

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
          echo "<div align=\"center\" class=\"unans\">\n";
          $tmp_bgcolor = '#FFC0C0';
        } else {
          echo "<div align=\"center\">\n";
          $tmp_bgcolor = $bgcolor;
        }
        $tmp_correct = trim($question['options'][0]['correct']);
        $tmp_correct = str_replace("'", "&#039;", $tmp_correct);

		//<!-- ======================== HTML5 part include disp ================= -->
		echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 220) . "' height='" . $tmp_height . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script language='JavaScript' type='text/javascript'>\n";
		echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . $tmp_correct . "', '";
		if (isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]);
		echo "', '" . $display_option['marks_correct'] . "~" . $display_option['marks_incorrect'] . "~" . $question['score_method'] . "','#FFC0C0','labelling','answer');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->

        if (!isset($user_answers[$current_screen][$q_id]) OR $user_answers[$current_screen][$q_id] == '') {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
        } else {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"" . $user_answers[$current_screen][$q_id] . "\" />\n";
        }
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
      if ($question['scenario'] != '') {
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
      if ($question['display_method'] == 'vertical_other' and $paper_type == '3') {
        if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],0,5) == 'other') {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" checked /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($user_answers[$current_screen][$q_id],6) . "\" /></td></tr>\n";
        } else {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
        }
      }
    }
    echo '</table>';
  } elseif ($question['q_type'] == 'enhancedcalc') {
    if(!isset($question['object'])) {
      require_once('../plugins/questions/enhancedcalc/enhancedcalc.class.php');
      $question['object'] = new EnhancedCalc($configObj);
      $question['object']->load($question);
    }
    $settings = json_decode($question['settings'], true);
    if ($li_set == 1) {
      echo '<tr><td></td><td>';
    } else {
      echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
    }
    $extra = array('num_on_screen' => $question_no,'printhardcopy' => true);

    $question['object']->render_paper($extra);


    $marks = $display_option['marks_correct'];
  } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'mrq' or $question['q_type'] == 'rank') {
    if ($question['display_method'] == 'other') {
      $answer = (isset($user_answers[$current_screen][$q_id])) ? $user_answers[$current_screen][$q_id] : '';
      $part_id++;
      if ($answer != '' and substr($answer,($part_id - 1),1) == 'y') {
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked /></td>";
      } else {
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      }
      echo "<td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($answer,($part_id+1)) . "\" /></td></tr>\n";
    }
    echo '</table>';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $question['options'][0]['correct']);

    if ($matching_media[0] != '') {
      echo '<div class="mediadiv">';
      $questiondata->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
      $render->render($questiondata, $string, 'paper/media.html');
      echo '</div>';
    }
    $text_scenarios = 0;
    for ($part_id=0; $part_id<count($matching_scenarios); $part_id++) {
      if (trim(strip_tags($matching_scenarios[$part_id],'<img>')) != '') $text_scenarios++;
    }
    $media_scenarios = 0;
    for ($part_id=1; $part_id<count($matching_media); $part_id++) {
      if ($matching_media[$part_id] != '') $media_scenarios++;
    }
    $scenario_no = max($text_scenarios, $media_scenarios);

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
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
      $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];

      $sub_answers = explode('$', $matching_answers[$part_id - 1]);

      echo '<li>';
      if (isset($matching_scenarios[$part_id - 1]) and $matching_scenarios[$part_id - 1] != '') echo '<div>' . $matching_scenarios[$part_id - 1] . '</div>';
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<div>";
        $questiondata->set_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '');
        $render->render($questiondata, $string, 'paper/media.html');
        echo "</div>\n";
      }

      for ($i=0; $i<count($sub_answers); $i++) {
        echo '<img src="../artwork/print_box_a.png" width="30" height="30" />&nbsp;';
      }

      echo '</li>';
    }
    echo '</ol>';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'matrix') {
    $part_id = 1;
    if ($matching_media[0] != '') {
      echo "<div class=\"mediadiv\">";
      $questiondata->set_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '');
      $render->render($questiondata, $string, 'paper/media.html');
      echo "</div>\n";
    }
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="1" class="matrix">';
    echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "</tr>\n";
    $numerals = array('i','ii','iii','iv','v','vi','vii','viii','ix','x','xi','xii');
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo '<tr><td align="right">' . $numerals[$part_id - 1] . '.</td><td>' . $single_scenario . '</td>';
        $answer_no = 1;
        foreach ($matching_options as $single_option) {
          $tmp_part_id = $question['option_order'][$answer_no-1] + 1;
          echo '<td><div align="center"><img src="../artwork/print_box_tick.png" width="23" height="23" alt="" /></div></td>';
          $answer_no++;
        }
        echo "</tr>\n";
        $part_id++;
      }
    }
    echo '</table>';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    } else {
      $marks = $part_id - 1;
    }
  } elseif ($question['q_type'] == 'sct') {
    echo '</table>';
  }

  // Display possible marks for question (if not Survey)
  if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'likert' and $question['q_type'] != 'info' and $question['q_type'] != 'enhancedcalc') echo '</blockquote>';
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
      if ($question['score_method'] == 'Bonus Mark') {
        $plural = ($display_option['marks_correct'] == 1) ?  $string['mark'] : $string['marks'];
        echo ' ' . sprintf($string['bonusmark'], $display_option['marks_correct'], $plural);  // Used on ranking questions
      }
      if ($neg_marking) echo ', ' . $string['negmarking'];
      echo ")</div>\n<br /></td></tr>\n";
    }
  } else {
    //echo "</td></tr>\n";
  }
  if ($question['q_type'] != 'info') echo "<input type=\"hidden\" name=\"order$question_no\" value=\"" . implode(',', $question['option_order']) . "\" />\n";
  $used_questions[$q_id] = $q_id;

  //echo "</table>\n";
}
?>
