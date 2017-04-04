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

$configObject          = Config::get_instance();

function get_unanswered_screens($screen_no, $screen_data, $user_answers, $questions, $paperID, $db) {
  $incomplete_screens = array_fill(1, $screen_no, 0);  // Set all screens to be initinally complete

  for ($i=1; $i<=$screen_no; $i++) {
    if (!isset($user_answers[$i])) {                            // No user answers exist for that screen
      $incomplete_screens[$i] = 1;
    } elseif (count($user_answers[$i]) < count($screen_data[$i]) ) {   // Fewer answers than questions on that screen
      $incomplete_screens[$i] = 1;
    } else {                                                    // Right number of records but need to check if any are unanswered
      $configObject = Config::get_instance();
      $tmp_answer_no = count($screen_data[$i]);

      $answers = array();
      $a = 1;
      foreach ($user_answers[$i] as $q_id=>$individual_answer) {
        $answers[$q_id] = $individual_answer;
        $a++;
      }

      for ($a=1; $a<=$tmp_answer_no; $a++) {
        $current_q_id = $screen_data[$i][$a-1][1];
        if (isset($answers[$current_q_id])) {
          $current_answer = $answers[$current_q_id];
        } else {
          $current_answer = '';
        }
        // Get the ID of the question array
        for ($x=0; $x<count($questions); $x++) {
          if ($questions[$x]['q_id'] == $current_q_id) {
            $questionID = $x;
          }
        }
        switch ($screen_data[$i][$a-1][0]) {
          case 'area':
            $parts = explode(';', $current_answer);
            if (!isset($parts[1]) or (isset($parts[1]) and $parts[1] == '')) {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'blank':
            $parts = json_decode($current_answer);
            foreach ($parts as $part) {
              if ($part == 'u') {
                $incomplete_screens[$i] = 1;
              }
            }
            break;
          case 'enhancedcalc':
            require_once dirname(__DIR__) . '/plugins/questions/enhancedcalc/enhancedcalc.class.php';
            $enhancedcalc = new EnhancedCalc($configObject);
            $enhancedcalc->set_useranswer($current_answer);
            if ($enhancedcalc->get_user_answer_raw() == '') {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'dichotomous':
            if (strpos($current_answer, 'u') !== false) {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'extmatch':
            $parts = explode('|', $current_answer);
            foreach ($parts as $part) {
              if ($part == '' or $part == 'u') {
                $incomplete_screens[$i] = 1;
              }
            }
            break;
          case 'hotspot':
            if (strpos($current_answer, 'u') !== false or strpos($current_answer, 'false') !== false) {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'labelling':
            $tmp_first_split = explode(';', $questions[$questionID]['options'][0]['correct']);
            $tmp_second_split = explode('$', $tmp_first_split[11]);
						
            $placeholders = 0;
            $count_tmp_second_split = count($tmp_second_split);
            for ($label_no = 4; $label_no <= $count_tmp_second_split; $label_no += 4) {
              if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
                $x = $tmp_second_split[$label_no-2];
                $y = $tmp_second_split[$label_no-1] - 25;
                $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no], 0, strpos($tmp_second_split[$label_no],'|'));
                $placeholders++;
              }
            }

            $user_parts = explode(';', $current_answer);
            $user_parts2 = explode('$', $user_parts[1]);
            $user_answer_no = (count($user_parts2) - 1) / 4;

            if ($user_answer_no < $placeholders) {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'likert':
            if (strpos($current_answer, 'u') !== false) {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'matrix':
            $parts = explode('|', $current_answer);
            foreach ($parts as $part) {
              if ($part == '') {
                $incomplete_screens[$i] = 1;
              }
            }
            break;
          case 'mcq':
            if ($current_answer == '0') {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'mrq':
            if (strpos($current_answer, 'y') === false and $current_answer != 'a') {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'rank':
            if ($questions[$questionID]['score_method'] == 'Mark per Option') {
              if (strpos($current_answer, 'u') !== false) {
                $incomplete_screens[$i] = 1;
              }
            } else {
              $no_correct = 0;
              foreach ($questions[$questionID]['options'] as $option) {
                if ($option['correct'] > 0) {
                  $no_correct++;
                }
              }

              $parts = explode(',', $current_answer);
              $user_answer_no = 0;

              foreach ($parts as $part) {
                if ($part != '' and $part != 'u' and $part != '0') {
                  $user_answer_no++;
                }
              }

              if ($user_answer_no < $no_correct) {
                $incomplete_screens[$i] = 1;
              }
            }
            break;
          case 'sct':
            if ($current_answer == '0') {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'textbox':
            if ($current_answer == '') {
              $incomplete_screens[$i] = 1;
            }
            break;
          case 'true_false':
            if (strpos($current_answer, 'u') !== false) {
              $incomplete_screens[$i] = 1;
            }
            break;
        }
      }
    }
  }

  return $incomplete_screens;
}

function calcPreParse($inputVal, $user_answers, $tmp_vars) {
  global $A, $B, $C, $D, $E, $F, $G, $H, $I, $J;
  if (substr($inputVal,0,3) == 'ans') {
    //its a question refrance get previous user answer
    $find_qid = intval(substr($inputVal,3));
    $pre_user_answers = '';
    foreach ($user_answers as $screen => $answers) {
      foreach ($answers as $pre_qid => $ans) {
        if ($pre_qid == $find_qid) {
          list($pre_user_answers) = explode('|',$ans);
          break 2;
        }
      }
    }
    if ($pre_user_answers == '') return 'ERROR';
    $pre_user_answers = preg_replace('/[^0-9\.\-]/','',$pre_user_answers);
    $inputVal = str_replace('ans' . $find_qid, $pre_user_answers, $inputVal);
  } elseif (substr($inputVal,0,3) == 'var') {
    //its a var refrance from a previous question
    $find_var = substr($inputVal,3,1);
    $find_qid = intval(substr($inputVal,4));
    $pre_var_val = '';
    foreach ($user_answers as $screen => $answers) {
      foreach ($answers as $pre_qid => $ans) {
        if ($pre_qid == $find_qid) {
          $pre_user_answers_array = explode('|',$ans);
          $variables = explode(',', $pre_user_answers_array[2]);
          $pre_var_val = $variables[array_search($find_var,$tmp_vars)-1];
          break 2;
        }
      }
    }
    if ($pre_var_val == '') return 'ERROR';
    $inputVal = str_replace('var' . substr($inputVal,3,1) . $find_qid, $pre_var_val, $inputVal);
  }
  eval("\$inputVal = $inputVal;");
  return $inputVal;
}

function checkVariables($number) {
  global $A, $B, $C, $D, $E, $F, $G, $H, $I, $J;
  eval("\$new_number = \"$number\";");
  return $new_number;
}

/**
* Replace error string from reference to blank user answer to previous question with presentation text in a way that is safe for strings containing equations
 * @param  string $haystack    The string within which the replacement will be made
 * @param  string $needle      String to search for
 * @param  string $replacement Replacement string
 * @return string              The original string with occurrences of $needle replaced with $replacement with HTML for highlighting when not part of an equation
 */
function calc_replace_error_safe($haystack, $needle, $replacement) {
  $matches = array();
  preg_match_all('/<(div|span) *class="mee">.*<\/\1>/U', $haystack, $matches);

  if (is_array($matches) and count($matches) > 0) {
    $match_count = 1;
    foreach ($matches[0] as $match) {
      $haystack = str_replace($match, str_replace($needle, $replacement, $match), $haystack);
      $match_count++;
    }
  }

  return str_replace($needle, '<span style="color:red; font-weight:bold">' . $replacement . '</span>', $haystack);
}

function display_question($configObject, &$question, $paper_type, $calculator, $current_screen, $old_q_type, &$question_no, $user_answers, &$unanswered) {
  global $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $li_set, $bgcolor, $li_set, $used_questions, $user_dismiss, $user_order, $string, $language, $configObject, $unanswered_color;

  // Get the media directory object.
  $mediadirectory = rogo_directory::get_directory('media');

  $q_id = $question['q_id'];
  $option_no = count($question['options']);
	
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
          $tmp_order_array[$i] = strtolower($question['options'][$i]['option_text']);
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
  $textboxes_seen = array();

  $li_set = 0;
  if ($old_q_type == 'likert' and $question['q_type'] != 'likert') {
    echo "</table>\n<br />\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
    echo "<col width=\"40\"><col>\n";
  } elseif ($old_q_type != 'likert' and $question['q_type'] == 'likert') {
    echo "</table>\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
  }

  if ($question['theme'] != '') echo '<tr><td colspan="2" class="theme">' . $question['theme'] . '</td></tr>';
	
  if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
    if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert' and $question['q_type'] != 'enhancedcalc') {
      echo '<tr><td class="q_no">' . $question['assigned_number'] . '.&nbsp;';
      if ($calculator == 1) echo '<br /><img class="calc" src="../artwork/calc.png" alt="' . $string['calculator'] . '" />';
      echo "</td><td>";
      if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'area' and $question['q_type'] != 'enhancedcalc') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . $question['assigned_number'] . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img class="calc" src="../artwork/calc.png" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      echo '<p align="center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
      $li_set = 1;
    }
    if ($question['q_type'] != 'likert') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . $question['assigned_number'] . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img class="calc" src="../artwork/calc.png" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      $li_set = 1;
      if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and in_array($question['q_type'], array('extmatch', 'matrix', 'enhancedcalc')))) echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'enhancedcalc') echo $question['leadin'];
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

  $part_id = 0;
  $marks = 0;
  if ($question['q_type'] != 'likert') $old_likert_label = '';

  // Pre-question processing
  switch ($question['q_type']) {
    case 'enhancedcalc':
      if (isset($user_answers[$current_screen][$q_id])) {
        $d = array();
        $d['useranswer'] = $user_answers[$current_screen][$q_id];
        $question['object']->load($d);
      }
      $question['object']->load_all_user_answers($user_answers);
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
      if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0' and $screen_pre_submitted == 1) {
        echo '<blockquote class="unans">';
        $unanswered = true;
      } else {
        echo '<blockquote>';
      }
      if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
        echo '<table cellpadding="2" cellspacing="0" border="0">';
      } elseif ($question['display_method'] == 'dropdown') {
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0') {
          echo '<div class="option"><select class="unans" name="q' . $question_no . '" size="1">';
          $unanswered = true;
        } else {
          echo '<div class="option"><select name="q' . $question_no . '" size="1">';
        }
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
      if (isset($user_answers[$current_screen][$q_id])) {
        $answer_parts = explode(':', $user_answers[$current_screen][$q_id]);
        $len_answer = strlen($answer_parts[0]);
      } else {
        $len_answer = 0;
      }
      if (isset($answer_parts) and $answer_parts[0] == str_repeat('n', $len_answer) and $screen_pre_submitted == 1) {
        echo '<blockquote class="unans"><table cellpadding="2" cellspacing="0" border="0">';
        $unanswered = true;
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
        if ($likert_col_no != $old_likert_cols) echo '</table><table cellpadding="4" cellspacing="0" border="0"><col width="40"><col>';
        if ($likert_display[$likert_col_no] == 'true') {
          $likert_col_no++;
          $na = true;
        }
        if ($question['notes'] != '') {
          echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"note\"><img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"Note\" />&nbsp;<strong>" . $string['note'] . ":</strong>&nbsp;" . $question['notes'] . "</td></tr>\n";
        }
        if ($question['scenario'] != '') {
          echo "<tr><td colspan=\"" . ($likert_col_no + 2) . "\">" . $question['scenario'] . "</td></tr>\n";
        }
        echo '<tr><td></td><td></td>';
        if ($na == true) {
          echo "<td style=\"vertical-align:bottom; text-align:center; color:$labelcolor; font-size:80%\">" . $string['na'] . "</td>";
        }
        echo "<td style=\"vertical-align:bottom; text-align:center; color:$labelcolor; font-size:80%\">$likert_display[0]</td>";
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
        echo "<tr><td class=\"question_no\">" . $question['assigned_number'] . ".&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">" . $string['clinicalvignette'] . "</td></tr>\n";
        echo '<tr><td style="vertical-align:top; text-align:right">';
        if ($calculator == 1) echo '<br /><img class="calc" src="../artwork/calc.png" alt="' . $string['calculator'] . '" />';
        echo "</td><td>";
        if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
        echo $question['scenario'] . "<br />\n<br />";
        $li_set = 1;
      }
      if ($question['q_media'] != '') {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . $question['assigned_number'] . '.&nbsp;';
          if ($calculator == 1) echo '<br /><img class="calc" src="../artwork/calc.png" alt="' . $string['calculator'] . '" />';
          echo '</td><td>';
        }
        echo '<p align="center">' . display_media($question['q_media'],$question['q_media_width'],$question['q_media_height'], '') . "</p>\n";
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

      if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0' and $screen_pre_submitted == 1) {
        echo '<blockquote class="unans"><table cellpadding="2" cellspacing="0" border="0">';
        $unanswered = true;
      } else {
        echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      }
      $marks = 1;
      break;
  }

  // Processing for each stem.
  foreach ($question['options'] as $display_option) {
    $part_id++;
    switch ($question['q_type']) {
      case 'area':
        
        $default_ans  = '100,0,0,0,0,0';
        
        if (isset($user_answers[$current_screen][$q_id])) {
          $tmp_user_answer = $user_answers[$current_screen][$q_id];
        } else {
          $tmp_user_answer = $default_ans;
        }
        
        $answer_parts = explode(';', $tmp_user_answer);
        if (isset($answer_parts[1])) {
          $tmp_user_answer = substr($answer_parts[1], 0, -2);
          $full_user_ans = $user_answers[$current_screen][$q_id];
        } else {
          $tmp_user_answer = '';
          $full_user_ans = $default_ans;
        }
        ?>
        <br />
        <?php
        if (isset($user_answers[$current_screen][$q_id]) and $tmp_user_answer == $default_ans and  $screen_pre_submitted == 1) {
          echo "<div align=\"left\" class=\"unans\" style=\"padding:2px\">";
          $unanswered = true;
          $tmp_bgcolor = '#FFC0C0';
        } else {
          echo "<div align=\"left\">";
          $tmp_bgcolor = $bgcolor;
        }

		//<!-- ======================== HTML5 part include disp ================= -->
		echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 2) . "' height='" . ($question['q_media_height'] + 27) . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script>\n";
		echo "setUpQuestion(" . $question_no . ", 'q" . $question_no . "','" . $language . "', '" . $question['q_media'] . "', '" . $display_option['correct'] . "', '" . $tmp_user_answer . "',1,'#FFC0C0','area','answer');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->
?>
        </div>
        <input type="hidden" name="q<?php echo $question_no; ?>" id="q<?php echo $question_no; ?>" value="<?php echo $full_user_ans; ?>"/>
        <?php
        $marks += $display_option['marks_correct'];
        break;
      case 'dichotomous':
        if (isset($user_answers[$current_screen][$q_id])) {
          $tmp_user_answer = substr($user_answers[$current_screen][$q_id], $question['option_order'][$part_id-1], 1);
        } else {
          $tmp_user_answer = '';
        }
        if (($question['display_method'] == 'TF_NegativeAbstain') or ($question['display_method'] == 'TF_NegativeAbstainHalf') or ($question['display_method'] == 'TF_PostiveAbstain') or ($question['display_method'] == "YN_NegativeAbstain")) {
          $abstain = true;
        } else {
          $abstain = false;
        }

        $class_mod = '';
        if ($tmp_user_answer == 'u' and $screen_pre_submitted == 1) {
          $class_mod = ' class="unans"';
          $unanswered = true;
        }

        echo '<tr id="q' . $question_no . '_opt' . $part_id . '"' . $class_mod . '><td align="center">';

        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        $optionID = 'q' . $question_no . '_' . $tmp_part_id;
        if ($tmp_user_answer == 't') {
          echo '<input type="radio" name="' . $optionID . '" value="t" checked="checked" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        } elseif ($tmp_user_answer == 'f') {
          echo '<input type="radio" name="' . $optionID . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" checked="checked" /></td>';
        } elseif ($tmp_user_answer == 'a') {
          echo '<input type="radio" name="' . $optionID . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        } else {
          echo '<input type="radio" name="' . $optionID . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        }
        if ($abstain == true) {
          if ($tmp_user_answer == 'a') {
            echo "<td align=\"center\"><input type=\"radio\" name=\"$optionID\" value=\"a\" checked=\"checked\" /></td>";
          } else {
            echo "<td align=\"center\"><input type=\"radio\" name=\"$optionID\" value=\"a\" /></td>";
          }
        }
        echo '<td>';
        if ($display_option['option_text'] != '') echo $display_option['option_text'];
        if ($display_option['o_media'] != '') {
          if ($display_option['option_text'] != '') echo '<br />';
          echo display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
        }
        echo "</td></tr>\n";
        $marks += $display_option['marks_correct'];
        break;
      case 'enhancedcalc':
        // no options for enhanced calc now stored in settings

        $extra = array('num_on_screen' => $question_no);

        $question['object']->render_paper($extra);
        $question['object']->load_all_user_answers($user_answers);

        $marks += $question['object']->calculate_question_mark();

        break;
      case 'likert':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
          echo '<tr class="unans">';
          $unanswered = true;
        } else {
          echo '<tr>';
        }
        echo '<td class="q_no">' . $question['assigned_number'] . '.&nbsp;</td><td>' . $question['leadin'] . '</td>';
        $scale_size = substr_count($question['display_method'],'|');
        if ($likert_display[$scale_size] == 'true') {
          echo "<td style=\"text-align:center; width:40px; vertical-align:top\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"n/a\"";
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'n/a') echo ' checked="checked"';
          echo "/></td>";
        }
        for ($i=1; $i<=$scale_size; $i++) {
          $optionID = 'q' . $question_no . '_' . $part_id;
          if (isset($user_answers[$current_screen][$q_id]) and $i == $user_answers[$current_screen][$q_id]) {
            echo "<td class=\"likert_button\"><input type=\"radio\" name=\"$optionID\" value=\"$i\" checked=\"checked\" /></td>";
          } else {
            echo "<td class=\"likert_button\"><input type=\"radio\" name=\"$optionID\" value=\"$i\" /></td>";
          }
        }
        echo "</tr>\n";
        break;
      case 'mcq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" checked=\"checked\" /></td><td>";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $class_type = 'inact';
          } else {
            $class_type = 'act';
          }
          if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . '</span>';
          if ($display_option['o_media'] != '') {
            if ($display_option['option_text'] != '') echo '<br />';
            echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'], '');
          }
          echo "</td></tr>\n";
        } elseif ($question['display_method'] == 'horizontal') {
          echo "<div style=\"float:left; margin-right:15px; margin-bottom:15px\">";
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $tmp_part_id . "\" checked=\"checked\" />";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $tmp_part_id . "\" />";
          }
          if ($display_option['option_text'] != '') echo '&nbsp;' . $display_option['option_text'];
          if ($display_option['o_media'] != '') echo '&nbsp;' . display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          echo "</div>";
        } else {
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            echo "<option value=\"" . $tmp_part_id . "\" selected>" . $display_option['option_text'] . "</option>\n";
          } else {
            echo "<option value=\"" . $tmp_part_id . "\">" . $display_option['option_text'] . "</option>\n";
          }
        }
        if ($tmp_part_id == $display_option['correct']) $marks = $display_option['marks_correct'];
        break;
      case 'mrq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        $optionID = 'q' . $question_no . '_' . $tmp_part_id;
        if ($paper_type == 3) {
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            echo "<tr><td><input type=\"checkbox\" name=\"$optionID\" id=\"$optionID\" value=\"y\" checked=\"checked\" /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"$optionID\" id=\"$optionID\" value=\"y\" /></td><td>";
          }
        } else {
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            echo "<tr><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$tmp_part_id,$option_no,$mrq_correct);\" name=\"$optionID\" id=\"$optionID\" value=\"y\" checked=\"checked\" /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$tmp_part_id,$option_no,$mrq_correct);\" name=\"$optionID\" id=\"$optionID\" value=\"y\" /></td><td>";
          }
        }

        if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$part_id-1,1) == '1') {
          $class_type = 'inact';
        } else {
          $class_type = 'act';
        }

        if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
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

        if ($rank_answers != '') {
          for ($i=0; $i<count($rank_answers); $i++) {
            if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
              $tmp_user_answers++;
            }
          }
        }

        if ($question['score_method'] == 'Mark per Option') {
          $answers_needed = $option_no;
        } else {
          $answers_needed = $total_rank_no;
        }

        echo '<tr';
        if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == 'u' and $screen_pre_submitted == 1 and $tmp_user_answers < $answers_needed) {
          echo ' class="unans"';
          $unanswered = true;
        }
        echo "><td class=\"option\"><select name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $tmp_part_id . "\" class=\"rankselect q" . $question_no . "\">\n";

        echo "<option value=\"u\"></option>\n";
        if ($require_na) {
          if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == '0') {
            echo "<option value=\"0\" selected>" . $string['na'] . "</option>\n";
          } else {
            echo "<option value=\"0\">" . $string['na'] . "</option>\n";
          }
        }
        for ($i=1; $i<=$total_rank_no; $i++) {
          if (isset($rank_answers[$tmp_part_id - 1]) and $i == $rank_answers[$tmp_part_id - 1]) {
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
        if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
          $class_type = 'inact';
        } else {
          $class_type = 'act';
        }
        echo "<td><span class=\"$class_type\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
        if ($display_option['correct'] != 0) {
          $marks += $display_option['marks_correct'];
        } elseif ($question['score_method'] == 'Mark per Option') {
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'sct':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" checked=\"checked\" /></td><td>";
        } else {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
        }
        if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
          $class_type = 'inact';
        } else {
          $class_type = 'act';
        }
        if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . '</span>';
        echo "</td></tr>\n";
        break;
      case 'true_false':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
          echo '<blockquote style="line-height:175%" class="unans">';
          $unanswered = true;
        } else {
          echo '<blockquote style="line-height:175%">';
        }

        $true_checked = '';
        $false_checked = '';
        $abstain_checked = '';

        if ($question['display_method'] == 'dropdown') {
          echo "<select name=\"q" . $question_no . "\">\n";
          echo "<option value=\"\"></option>\n";
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') {
            echo "<option value=\"t\" selected>" . $string['true'] . "</option>\n";
          } else {
            echo "<option value=\"t\">" . $string['true'] . "</option>\n";
          }
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') {
            echo "<option value=\"f\" selected>" . $string['false'] . "</option>\n";
          } else {
            echo "<option value=\"f\">" . $string['false'] . "</option>\n";
          }
          echo "</select>\n";
        } else {
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 't') $true_checked = ' checked="checked"';
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'f') $false_checked = ' checked="checked"';

          echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"t\"$true_checked />&nbsp;" . $string['true'];
          if ($question['display_method'] == 'horizontal') {
            echo '&nbsp;&nbsp;&nbsp;';
          } elseif ($question['display_method'] == 'vertical') {
            echo '<br />';
          }
          echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"f\"$false_checked />&nbsp;" . $string['false'];
          
          if ($neg_marking) {
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
              $abstain_checked = ' checked="checked"';
            }
            if ($question['display_method'] == 'horizontal') {
              echo '&nbsp;&nbsp;&nbsp;';
            } elseif ($question['display_method'] == 'vertical') {
              echo '<br />';
            }
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"a\"$abstain_checked />&nbsp;<span style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</span>";         
          }
        }

        echo '</blockquote>';

        $marks = $display_option['marks_correct'];
        
        break;
      case 'blank':
        $ans = '';
        $blank_mark = array();
        $display_option['option_text'] = str_replace('&nbsp;',' ',$display_option['option_text']);
        $blank_details = explode('[blank',$display_option['option_text']);
        if (isset($user_answers[$current_screen][$q_id])) {
          $user_answers[$current_screen][$q_id] = str_replace('&nbsp;',' ',$user_answers[$current_screen][$q_id]);
        }

        if (isset($user_answers[$current_screen][$q_id])) {
          $blank_user_answers = json_decode($user_answers[$current_screen][$q_id]);
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

            if ( (isset($blank_user_answers[$blank_count - 1]) and $blank_user_answers[$blank_count - 1] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1) ) {
              $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" class=\"unans\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
              $unanswered = true;
            } else {
              if (isset($blank_user_answers[$blank_count - 1])) {
                $ans = $blank_user_answers[$blank_count - 1];
              }
              $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" value=\"" . htmlentities($ans). "\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
            }
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
            if (isset($blank_user_answers[$part_id - 1]) and $blank_user_answers[$part_id - 1] == 'u' and $screen_pre_submitted == 1) {
              echo "<select name=\"q" . $question_no . "_" . $part_id . "\" class=\"unans\">\n";
              $unanswered = true;
            } else {
              echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            }
            echo "<option value=\"u\"></option>\n";
            shuffle($answer_list);            // Shuffle the answers up.
            for ($i=1; $i<=count($answer_list); $i++) {
              if (isset($answer_list[$i-1]) and isset($blank_user_answers[$blank_count - 1]) and html_entity_decode(trim($answer_list[$i-1])) == html_entity_decode(trim($blank_user_answers[$blank_count - 1]))) {
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
        if (stripos($question['leadin'], '<p>') === false and stripos($question['leadin'], '<div>') == false) {
          echo '<br />';
        }
        if (!in_array($question_no, $textboxes_seen)) {
          $textboxes_seen[] = $question_no;
          $settings = json_decode($question['settings'], true);
          if (!isset($settings['editor']) or $settings['editor'] == 'plain') {
            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
              echo "<textarea class=\"unans\" name=\"q" . $question_no . "\" cols=\"" . $settings['columns'] . "\" rows=\"" . $settings['rows'] . "\">" . $user_answers[$current_screen][$q_id] . "</textarea>\n";
              $unanswered = true;
            } else {
              $ans = '';
              if (isset($user_answers[$current_screen][$q_id])) {
                $ans = $user_answers[$current_screen][$q_id];
              }
              echo "<textarea name=\"q" . $question_no . "\" cols=\"" . $settings['columns'] . "\" rows=\"" . $settings['rows'] . "\">" . $ans . "</textarea>\n";
            }
          } else {
            include_once('wysiwyg_editor.inc');
            $ans = '';
            if (isset($user_answers[$current_screen][$q_id])) {
              $ans = $user_answers[$current_screen][$q_id];
            }

            $textbox_width  = ( 40 + ( $settings['columns'] * 8 ) );
            $textbox_height = ( $settings['rows'] * 28 );

            $background_colour = '';

            if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
              $background_colour = 'background-color:red;';
            }
            ?>
            <textarea class="mceEditor" id="q<?php echo $question_no ?>" name="q<?php echo $question_no ?>" style="<?php echo $background_colour; ?>width:<?php echo $textbox_width ?>px; height:<?php echo $textbox_height ?>px"><?php echo $ans ?></textarea><?php echo "\n"; ?>
            <?php
          }
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'hotspot':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and  $screen_pre_submitted == 1) {
          $unanswered = true;
        }
        $hotspot_no = substr_count($question['options'][0]['correct'],'|') + 1;
        $tmp_height = $question['q_media_height'] + 30;
        if ($tmp_height < (($hotspot_no * 36) + 25)) $tmp_height = (($hotspot_no * 36) + 25);

        $tmp_correct = str_replace("'", "\'", trim($question['options'][0]['correct']));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);
        ?>
        <div align="left">
				<?php
					//<!-- ======================== HTML5 part include disp ================= -->
					echo "<canvas id='canvas" . $question_no . "' width='" . ($question['q_media_width'] + 300) . "' height='" . ($tmp_height-29) . "'></canvas>\n";
					echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
					echo "<script>\n";
					echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . $tmp_correct . "', '";
					if (isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]);
					echo "', '" . $screen_pre_submitted . "','#FFC0C0','hotspot','answer');\n";
					echo "</script>\n";
					//<!-- ==================================================== -->
				?>
        </div>
        <?php
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
        $tmp_first_split = explode(';', $question['options'][0]['correct']);
        $tmp_second_split = explode('|', $tmp_first_split[11]);
        $label_width = $tmp_first_split[5];
        $label_height = $tmp_first_split[6];
        $hyphen = false;
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            if (mb_strstr($label_parts[4], '-') !== false) $hyphen = true;
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
        if (($label_width < 80 and $hyphen) or ($label_width < 104 and !$hyphen)) {    // Two columns
          $computed_height = round(($label_height + 6) * ceil($tmp_labels / 2)) + 10;
          $tmp_height = max($question['q_media_height'], $computed_height);
        } else {                    // Single column
          $computed_height = round(($label_height + 6) * $tmp_labels) + 10;
          $tmp_height = max($question['q_media_height'], $computed_height);
        }

        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '0$' . $marks . ';' and  $screen_pre_submitted == 1) {
          $unanswered = true;
        }
        $tmp_correct = trim($question['options'][0]['correct']);
        $tmp_correct = str_replace("'", "&#039;", $tmp_correct);
  ?>
			<div align="left">
			<?php
				//<!-- ======================== HTML5 part include disp ================= -->
				echo "<canvas class=\"labelling\" id=\"canvas" . $question_no . "\" width=\"" . ($question['q_media_width'] + 220) . "\" height=\"" . $tmp_height . "\"></canvas>\n";
				echo "<br /><div id=\"canvasbox\"></div>\n";
				echo "<script>\n";
				echo "setUpQuestion(" . $question_no . ", 'flash" . $question_no . "', '" . $language . "', '" . $question['q_media'] . "', '" . $tmp_correct . "', '";
				if (isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]);
				echo "', '" . $display_option['marks_correct'] . "~" . $display_option['marks_incorrect'] . "~" . $question['score_method'] . "','#FFC0C0','labelling','answer');\n";
				echo "</script>\n";
				//<!-- ==================================================== -->
			?>
          </div>
        <?php
        if (!isset($user_answers[$current_screen][$q_id]) or $user_answers[$current_screen][$q_id] == '') {
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
    write_string('<param name="wmode" value="opaque" />');
    write_string('<param name="allowScriptAccess" value="sameDomain" />');
    write_string('<param name="movie" value="<?php echo $mediadirectory->url($question['q_media']); ?>" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    <?php
      if ($question['scenario'] != '') {
        echo 'write_string(\'<param name="FlashVars" value="' . $question['scenario'] . '">\')';
      }
      echo 'write_string(\'<embed style="z-index:0;" src="' . $mediadirectory->url($question['q_media']) . '"';
      if ($question['scenario'] != '') {
        echo ' FlashVars="' . $question['scenario'] . '"';
      }
      echo ' wmode="opaque" quality="high" bgcolor="' . $bgcolor . '" width="' . $question['q_media_width'] . '" height="' . $question['q_media_height'] . '" swLiveConnect=true id="flash' . $question_no . '" name="flash' . $question_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />\');';
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
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" checked=\"checked\" /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($user_answers[$current_screen][$q_id],6) . "\" /></td></tr>\n";
        } else {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
        }
      }
			if ($display_option['marks_incorrect'] < 0) {
				// Include an abstain option if negative marking is used.
			  if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
					echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"a\" checked=\"checked\" /></td><td style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</td></tr>\n";
				} else {
					echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"a\" /></td><td style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</td></tr>\n";
				}
			}
      echo '</table>';
    } elseif ($question['display_method'] == 'horizontal') {
		  if ($display_option['marks_incorrect'] < 0) {
				if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'a') {
					echo '&nbsp;&nbsp;<input type="radio" name="q' . $question_no . '" value="a" checked="checked" /><span style="color:' . $labelcolor . '">&lt;' . $string['abstain'] . '&gt;</span>';
				} else {
					echo '&nbsp;&nbsp;<input type="radio" name="q' . $question_no . '" value="a" /><span style="color:' . $labelcolor . '">&lt;' . $string['abstain'] . '&gt;</span>';
				}
			}
		} elseif ($question['display_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($question['q_type'] == 'dichotomous' or $question['q_type'] == 'mrq' or $question['q_type'] == 'rank') {
    $answer = (isset($user_answers[$current_screen][$q_id])) ? $user_answers[$current_screen][$q_id] : '';
    if ($question['display_method'] == 'other') {
      $part_id++;
      if ($answer != '' and substr($answer,($part_id - 1),1) == 'y') {
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" checked=\"checked\" /></td>";
      } else {
        echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"y\" /></td>";
      }
      echo "<td>" . $string['other'] . " <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($answer, $part_id) . "\" /></td></tr>\n";
    } elseif ($question['q_type'] == 'mrq' and $display_option['marks_incorrect'] < 0) {
		  // Include an abstain option if negative marking is used.
      if ($answer != '' and $answer == 'a') {
				echo "<tr><td><input type=\"checkbox\" onclick=\"MRQabstain($question_no,$option_no);\" name=\"q" . $question_no . "_abstain\" id=\"q" . $question_no . "_abstain\" value=\"y\" checked=\"checked\" /></td><td><span style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</span></td></tr>";
			} else {
				echo "<tr><td><input type=\"checkbox\" onclick=\"MRQabstain($question_no,$option_no);\" name=\"q" . $question_no . "_abstain\" id=\"q" . $question_no . "_abstain\" value=\"y\" /></td><td><span style=\"color:$labelcolor\">&lt;" . $string['abstain'] . "&gt;</span></td></tr>";
			}
		}
    echo '</table>';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'extmatch') {
    $matching_answers = explode('|', $question['options'][0]['correct']);

    if ($matching_media[0] != '') {
      echo '<p align="center">' . display_media($matching_media[0],$matching_media_width[0],$matching_media_height[0], '') . '</p>';
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
    for ($part_id=1; $part_id<=$scenario_no; $part_id++) {
      if(isset($matching_answers[$part_id-1])) {
        $answer_no = substr_count($matching_answers[$part_id-1],'$') + 1;
        $marks += (substr_count($matching_answers[$part_id-1],'$') + 1) * $display_option['marks_correct'];
      } else {
        $answer_no = 0;
      }
      echo '<li>';
      if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') echo '<div>' . $matching_scenarios[$part_id] . '</div>';
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<div>" . display_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '') . "</div>\n<br />";
      }
      if(isset($matching_answers[$part_id-1])) {
        $sub_answers = explode('$', $matching_answers[$part_id - 1]);
      } else {
        $sub_answers = array();
      }
      $list_size = 10;
      if (count($matching_options) < 10) $list_size = count($matching_options);
      if ($answer_no == 1) {
        if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == 'u' and $screen_pre_submitted == 1) {
          echo "<select class=\"unans\" name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
          $unanswered = true;
        } else {
          echo "<select name=\"q" . $question_no . "_" . $part_id . "\" size=\"1\">\n";
        }
        echo "<option value=\"u\"></option>\n";
      } else {
        echo "<br />\n";
        if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
          echo "<div class=\"option\"><select class=\"unans\" onchange=\"multimatchingCheck('q" . $question_no . "_" . $part_id . "'," . count($matching_options) . "," . count($sub_answers) . ");\" name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
          $unanswered = true;
        } else {
          echo "<div class=\"option\"><select onchange=\"multimatchingCheck('q" . $question_no . "_" . $part_id . "'," . count($matching_options) . "," . count($sub_answers) . ");\" name=\"q" . $question_no . "_" . $part_id . "[]\" id=\"q" . $question_no . "_" . $part_id . "\" multiple=\"multiple\" size=\"$list_size\">\n";
        }
      }

      $multi_answers = array();
      if (isset($matching_users_answers[$part_id - 1])) {
        $multi_answers = explode('$', $matching_users_answers[$part_id - 1]);
      }

      $tmp_option_no = 0;
      for ($option_no=0; $option_no<count($matching_options); $option_no++) {
        $tmp_answer_match = false;
        foreach ($multi_answers as $separate_tmp_answer) {
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
      if ($answer_no > 1) echo '<div class="mk">' . $string['holddownctrlkey'] . '</div>';
      echo '</div></li>';
    }
    echo '</ol><input type="hidden" name="multimatching' . $question_no . '_options" value="' . count($matching_options) . '" />';
    if ($question['score_method'] == 'Mark per Question') {
      $marks = $display_option['marks_correct'];
    }
  } elseif ($question['q_type'] == 'matrix') {
    $part_id = 1;
    echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
    echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
    foreach ($matching_options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }
    echo "</tr>\n";
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
          echo "<tr class=\"unans\">\n";
          $unanswered = true;
        } else {
          echo "<tr>\n";
        }
        echo '<td align="right">' . chr(64 + $part_id) . '.</td><td>' . $single_scenario . '</td>';
        $answer_no = 1;
        foreach ($matching_options as $single_option) {
          $tmp_part_id = $question['option_order'][$answer_no-1] + 1;
          if (isset($matching_users_answers[$part_id-1]) and $matching_users_answers[$part_id-1] == $tmp_part_id) {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_part_id . '" checked="checked" /></div></td>';
          } else {
            echo '<td><div align="center"><input type="radio" name="q' . $question_no . '_' . $part_id . '" value="' . $tmp_part_id . '" /></div></td>';
          }
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

  // Write out the hidden field for the dismiss facility.
  if ($question['q_type'] == 'mcq' or $question['q_type'] == 'mrq' or $question['q_type'] == 'rank') {
    if (isset($user_dismiss[$current_screen][$q_id]) and $user_dismiss[$current_screen][$q_id] != '') {
      echo "\n<div><input type=\"hidden\" name=\"dismiss$question_no\" id=\"dismiss$question_no\" value=\"" . $user_dismiss[$current_screen][$q_id] . "\" /></div>\n";
    } else {
      echo "\n<div><input type=\"hidden\" name=\"dismiss$question_no\" id=\"dismiss$question_no\" value=\"" . str_repeat('0', count($question['options'])) . "\" /></div>\n";
    }
  }

  // Display possible marks for question (if not Survey)
  if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'likert' and $question['q_type'] != 'info' and $question['q_type'] != 'enhancedcalc' and $question['q_type'] != 'matrix') echo '</blockquote>';
  if ($paper_type < 3) {
    if ($marks == 0) {
      echo "</td></tr>\n<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    } else {
      echo "<div id=\"q{$question_no}_mk\" class=\"mk\">($marks ";
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
    echo "</td></tr>\n";
  }
  if ($question['q_type'] != 'info') echo "<input type=\"hidden\" name=\"order$question_no\" value=\"" . implode(',', $question['option_order']) . "\" />\n";
  $used_questions[$q_id] = $q_id;
}
?>