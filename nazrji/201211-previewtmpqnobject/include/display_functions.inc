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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/mathsutils.class.php';

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
        if($pre_qid == $find_qid) {
          $pre_user_answers_array = explode('|',$ans);
          $variables = explode(',', $pre_user_answers_array[2]);
          $pre_var_val = $variables[array_search($find_var,$tmp_vars)-1];
          break 2;
        }
      }
    }
    if($pre_var_val == '') return 'ERROR';
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

function display_question(&$question, $paper_type, $current_screen, $old_q_type, &$question_no, &$question_offset, $user_answers, &$unanswered) {
  global $cfg_editor_javascript, $labelcolor, $old_likert_label, $old_likert_cols, $screen_pre_submitted, $A, $B, $C, $D, $E, $F, $G, $H, $I, $J, $li_set, $bgcolor, $li_set, $calculator, $used_questions, $user_dismiss, $user_order, $string, $language, $cfg_root_path, $cfg_js_root;

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
    echo "</table>\n<br />\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
    echo "<col width=\"40\"><col>\n";
  } elseif ($old_q_type != 'likert' and $question['q_type'] == 'likert') {
    echo "</table>\n<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
  }

  if ($question['theme'] != '') echo '<tr><td colspan="2" class="theme">' . $question['theme'] . '</td></tr>';

  if ($question['q_type'] != 'info' and $question['q_type'] != 'sct') {
    if ($question['scenario'] != '' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'matrix' and $question['q_type'] != 'likert') {
      echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
      if ($calculator == 1) echo '<br /><img onclick="openCalc2();" style="cursor:pointer" src="../artwork/calc.gif" width="18" height="25" alt="' . $string['calculator'] . '" />';
      echo "</td><td>";
      if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '' and $question['q_type'] != 'hotspot' and $question['q_type'] != 'labelling' and $question['q_type'] != 'flash' and $question['q_type'] != 'extmatch' and $question['q_type'] != 'area') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img onclick="openCalc2();" style="cursor:pointer" src="../artwork/calc.gif" width="18" height="25" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      echo '<p align="center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
      $li_set = 1;
    }
    if ($question['q_type'] != 'likert') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
        if ($calculator == 1) echo '<br /><img onclick="openCalc2();" style="cursor:pointer" src="../artwork/calc.gif" width="18" height="25" alt="' . $string['calculator'] . '" />';
        echo '</td><td>';
      }
      $li_set = 1;
      if (($question['notes'] != '' and $question['scenario'] == '') or ($question['notes'] != '' and $question['q_type'] == 'extmatch')) echo '<p class="note"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
      if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'calculation') echo $question['leadin'];
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
    case 'calculation':
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
          echo "<tr><td></td><td colspan=\"" . ($likert_col_no + 1) . "\" class=\"note\"><img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;<strong>" . $string['note'] . ":</strong>&nbsp;" . $question['notes'] . "</td></tr>\n";
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
        echo "<tr><td class=\"question_no\">" . ($question_no + $question_offset) . ".&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">" . $string['clinicalvignette'] . "</td></tr>\n";
        echo '<tr><td style="vertical-align:top; text-align:right">';
        if ($calculator == 1) echo '<br /><img onclick="openCalc2();" style="cursor:pointer" src="../artwork/calc.gif" width="18" height="25" alt="' . $string['calculator'] . '" />';
        echo "</td><td>";
        if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . ':</strong>&nbsp;' . $question['notes'] . '</p>';
        echo $question['scenario'] . "<br />\n<br />";
        $li_set = 1;
      }
      if ($question['q_media'] != '') {
        if ($li_set == 0) {
          echo '<tr><td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;';
          if ($calculator == 1) echo '<br /><img onclick="openCalc2();" style="cursor:pointer" src="../artwork/calc.gif" width="18" height="25" alt="' . $string['calculator'] . '" />';
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
        if (isset($user_answers[$current_screen][$q_id]) and $tmp_user_answer == '' and  $screen_pre_submitted == 1) {
          echo "<div align=\"left\" class=\"unans\" style=\"padding:2px\">";
          $unanswered = true;
          $tmp_bgcolor = '#FFC0C0';
        } else {
          echo "<div align=\"left\">";
          $tmp_bgcolor = $bgcolor;
        }
        ?>

        <script language="javascript">
          write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="<?php echo ($question['q_media_width'] + 2); ?>" height="<?php echo ($question['q_media_height'] + 24); ?>" id="externalinterfaceq<?php echo $question_no; ?>_1" align="top">');
          write_string('<param name="movie" value="<?php echo $cfg_root_path; ?>/question/edit/area.swf" />');
          write_string('<param name="quality" value="high" />');
          write_string('<param name="bgcolor" value="#ffffff" />');
          write_string('<param name="play" value="true" />');
          write_string('<param name="loop" value="true" />');
          write_string('<param name="wmode" value="opaque" />');
          write_string('<param name="scale" value="showall" />');
          write_string('<param name="menu" value="true" />');
          write_string('<param name="devicefont" value="false" />');
          write_string('<param name="salign" value="top" />');
          write_string('<param name="allowScriptAccess" value="sameDomain" />');
          write_string('<!--[if !IE]>-->');
          write_string('<object type="application/x-shockwave-flash" data="<?php echo $cfg_root_path ?>/question/edit/area.swf" id="externalinterfaceq<?php echo $question_no; ?>_2" width="<?php echo ($question['q_media_width'] + 2); ?>" height="<?php echo ($question['q_media_height'] + 24); ?>">');
          write_string('<param name="movie" value="<?php echo $cfg_root_path; ?>/question/edit/area.swf" />');
          write_string('<param name="quality" value="high" />');
          write_string('<param name="bgcolor" value="#ffffff" />');
          write_string('<param name="play" value="true" />');
          write_string('<param name="loop" value="true" />');
          write_string('<param name="wmode" value="opaque" />');
          write_string('<param name="scale" value="showall" />');
          write_string('<param name="menu" value="true" />');
          write_string('<param name="devicefont" value="false" />');
          write_string('<param name="salign" value="top" />');
          write_string('<param name="allowScriptAccess" value="sameDomain" />');
          write_string('<!--<![endif]-->');
          write_string('<a href="https://www.adobe.com/go/getflash"> <img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>');
          write_string('<!--[if !IE]>-->');
          write_string('</object>');
          write_string('<!--<![endif]-->');
          write_string('</object>');
          
          sendTextToAS3('<?php echo $language; ?>','q<?php echo $question_no; ?>', 1, '<?php echo $cfg_root_path . '/media/' . $question['q_media']; ?>', '<?php echo $display_option['correct']; ?>', '<?php echo $tmp_user_answer; ?>');
          
        </script>
        </div>
        <input type="hidden" name="q<?php echo $question_no; ?>" id="q<?php echo $question_no; ?>" />
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

        if ($tmp_user_answer == 'u' and $screen_pre_submitted == 1) {
          echo '<tr class="unans"><td align="center">';
          $unanswered = true;
        } else {
          echo '<tr><td align="center">';
        }
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if ($tmp_user_answer == 't') {
          echo '<input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="t" checked /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        } elseif ($tmp_user_answer == 'f') {
          echo '<input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" checked /></td>';
        } elseif ($tmp_user_answer == 'a') {
          echo '<input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        } else {
          echo '<input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="t" /></td><td align="center"><input type="radio" name="q' . $question_no . '_' . $tmp_part_id . '" value="f" /></td>';
        }
        if ($abstain == true) {
          if ($tmp_user_answer == 'a') {
            echo "<td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"a\" checked /></td>";
          } else {
            echo "<td align=\"center\"><input type=\"radio\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"a\" /></td>";
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
      case 'calculation':
        $vars = array(1=>'A', 2=>'B', 3=>'C', 4=>'D', 5=>'E', 6=>'F', 7=>'G', 8=>'H', 9=>'I', 10=>'J');
        $var = $vars[$part_id];
        if (isset($user_answers[$current_screen][$q_id])) {
          $tmp_user_answers = explode('|',$user_answers[$current_screen][$q_id]);
        } else {
          $tmp_user_answers = array();
        }

        $figures = explode(',',$display_option['option_text']);

        $min = calcPreParse($figures[0],$user_answers,$vars);
        $max = (isset($figures[1]) and $figures[1] != '') ? calcPreParse($figures[1],$user_answers,$vars) : $min;

        if (!isset($user_answers[$current_screen][$q_id])) {
          //its a normal range and user has not seen the question genarate value
          $$var = MathsUtils::gen_random_no(checkVariables($min), checkVariables($max), $figures[2], $figures[3]);
        } else {
          //user has seen this question before use values already presented to the student
          $variables = explode(',', $tmp_user_answers[2]);
          if (substr($figures[0],0,3) == 'ans' or substr($figures[1],0,3) == 'ans' or substr($figures[0],0,3) == 'var' or substr($figures[1],0,3) == 'var') {
            $$var = MathsUtils::gen_random_no(checkVariables($min), checkVariables($max), $figures[2], $figures[3]);
          } else {
            $$var = $variables[$part_id-1];
          }
        }

        break;
      case 'likert':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and $screen_pre_submitted == 1) {
          echo '<tr class="unans">';
          $unanswered = true;
        } else {
          echo '<tr>';
        }
        echo '<td class="q_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>' . $question['leadin'] . '</td>';
        $scale_size = substr_count($question['display_method'],'|');
        if ($likert_display[$scale_size] == 'true') {
          echo "<td style=\"text-align:center; width:40px; vertical-align:top\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"n/a\"";
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'n/a') echo ' checked';
          echo "/></td>";
        }
        for ($i=1; $i<=$scale_size; $i++) {
          if (isset($user_answers[$current_screen][$q_id]) and $i == $user_answers[$current_screen][$q_id]) {
            echo "<td class=\"likert_button\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"$i\" checked /></td>";
          } else {
            echo "<td class=\"likert_button\"><input type=\"radio\" name=\"q" . $question_no . "_" . $part_id . "\" value=\"$i\" /></td>";
          }
        }
        echo "</tr>\n";
        break;
      case 'mcq':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if ($question['display_method'] == 'vertical' or $question['display_method'] == 'vertical_other') {
          if (isset($user_answers[$current_screen][$q_id]) AND $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
          }
          if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
            $class_type = 'inact';
          } else {
            $class_type = 'act';
          }
          if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" onclick=\"onoff($question_no,$tmp_part_id)\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . '</span>';
          if ($display_option['o_media'] != '') {
            if ($display_option['option_text'] != '') echo '<br />';
            echo display_media($display_option['o_media'],$display_option['o_media_width'],$display_option['o_media_height'], '');
          }
          echo "</td></tr>\n";
        } elseif ($question['display_method'] == 'horizontal') {
          if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $tmp_part_id . "\" checked />";
          } else {
            echo "<input type=\"radio\" name=\"q" . $question_no . "\" value=\"" . $tmp_part_id . "\" />";
          }
          if ($display_option['option_text'] != '') echo '&nbsp;' . $display_option['option_text'];
          if ($display_option['o_media'] != '') echo '&nbsp;' . display_media($display_option['o_media'], $display_option['o_media_width'], $display_option['o_media_height'], '');
          echo '&nbsp;&nbsp;';
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
        if ($paper_type == 3) {
          if (isset($user_answers[$current_screen][$q_id]) AND substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"y\" /></td><td>";
          }
        } else {
          if (isset($user_answers[$current_screen][$q_id]) and substr($user_answers[$current_screen][$q_id],$question['option_order'][$part_id-1],1) == 'y') {
            echo "<tr><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$tmp_part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"y\" checked /></td><td>";
          } else {
            echo "<tr><td><input type=\"checkbox\" onclick=\"MRQ($question_no,$tmp_part_id,$option_no,$mrq_correct);\" name=\"q" . $question_no . "_" . $tmp_part_id . "\" id=\"q" . $question_no . "_" . $tmp_part_id . "\" value=\"y\" /></td><td>";
          }
        }

        if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$part_id-1,1) == '1') {
          $class_type = 'inact';
        } else {
          $class_type = 'act';
        }

        if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" onclick=\"onoff($question_no,$part_id)\" id=\"$question_no" . "_" . "$part_id\">" . $display_option['option_text'] . '</span>';
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
        for ($i=1; $i<count($rank_answers); $i++) {
          if ($rank_answers[$i] != 'u' and $rank_answers[$i] != 0 and $rank_answers[$i] != 'u') {
            $tmp_user_answers++;
          }
        }

        echo '<tr';
        if (isset($rank_answers[$tmp_part_id - 1]) and $rank_answers[$tmp_part_id - 1] == 'u' and $screen_pre_submitted == 1 and $tmp_user_answers < $total_rank_no) {
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
        echo "<td><span class=\"$class_type\" onclick=\"onoff($question_no,$tmp_part_id)\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . "</span></td>\n</tr>\n";
        if ($display_option['correct'] != 0) {
          $marks += $display_option['marks_correct'];
        } elseif ($question['score_method'] == 'Mark per Option') {
          $marks += $display_option['marks_correct'];
        }
        break;
      case 'sct':
        $tmp_part_id = $question['option_order'][$part_id-1] + 1;
        if (isset($user_answers[$current_screen][$q_id]) and $tmp_part_id == $user_answers[$current_screen][$q_id]) {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" checked /></td><td>";
        } else {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$tmp_part_id\" /></td><td>";
        }
        if (isset($user_dismiss[$current_screen][$q_id]) and substr($user_dismiss[$current_screen][$q_id],$tmp_part_id-1,1) == '1') {
          $class_type = 'inact';
        } else {
          $class_type = 'act';
        }
        if ($display_option['option_text'] != '') echo "<span class=\"$class_type\" onclick=\"onoff($question_no,$tmp_part_id)\" id=\"$question_no" . "_" . "$tmp_part_id\">" . $display_option['option_text'] . '</span>';
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
        }
        
        echo '</blockquote>';
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
          $blank_user_answers = explode('|', $user_answers[$current_screen][$q_id]);
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

            if ( (isset($blank_user_answers[$blank_count]) and $blank_user_answers[$blank_count] == 'u') and (isset($screen_pre_submitted) and $screen_pre_submitted == 1) ) {
              $screen_output[$blank_count] = preg_replace("|\[blank.*\].*\[/blank\]|","<input type=\"text\" name=\"q" . $question_no . "_" . $blank_count . "\" class=\"unans\" size=\"$blank_size[$blank_count]\" />",$blank_details[$blank_count]);
              $unanswered = true;
            } else {
              if (isset($blank_user_answers[$blank_count])) {
                $ans = $blank_user_answers[$blank_count];
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
            if (isset($blank_user_answers[$part_id]) and $blank_user_answers[$part_id] == 'u' and $screen_pre_submitted == 1) {
              echo "<select name=\"q" . $question_no . "_" . $part_id . "\" class=\"unans\">\n";
              $unanswered = true;
            } else {
              echo "<select name=\"q" . $question_no . "_" . $part_id . "\">\n";
            }
            echo "<option value=\"u\"></option>\n";
            shuffle($answer_list);            // Shuffle the answers up.
            for ($i=1; $i<=count($answer_list); $i++) {
              if (isset($answer_list[$i-1]) and isset($blank_user_answers[$blank_count]) and html_entity_decode(trim($answer_list[$i-1])) == html_entity_decode(trim($blank_user_answers[$blank_count]))) {
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
        $textbox_size = explode('x',$question['display_method']);
        if ($display_option['option_text'] == 'plain') {
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
            echo "<blockquote><textarea class=\"unans\" name=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\">" . $user_answers[$current_screen][$q_id] . "</textarea></blockquote>\n";
            $unanswered = true;
          } else {
            $ans = '';
            if(isset($user_answers[$current_screen][$q_id])) {
              $ans = $user_answers[$current_screen][$q_id];
            }
            echo "<blockquote><textarea name=\"q" . $question_no . "\" cols=\"" . $textbox_size[0] . "\" rows=\"" . $textbox_size[1] . "\">" . $ans . "</textarea></blockquote>\n";
          }
        } else {
          include_once('wysiwyg_editor.inc');
          $ans = '';
          if (isset($user_answers[$current_screen][$q_id])) {
            $ans = $user_answers[$current_screen][$q_id];
          }
          if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '' and $screen_pre_submitted == 1) {
            echo "<textarea class=\"mceEditor\" style=\"background-color:red\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" style=\"width:" . (40 + ($textbox_size[0] * 8)) . "px; height:" . ($textbox_size[1] * 28) . "px\">$ans</textarea>\n";
          } else {
            echo "<textarea class=\"mceEditor\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" style=\"width:" . (40 + ($textbox_size[0] * 8)) . "px; height:" . ($textbox_size[1] * 28) . "px\">$ans</textarea>\n";
          }
        }
        $marks += $display_option['marks_correct'];
        break;
      case 'hotspot':
        if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == 'u' and  $screen_pre_submitted == 1) {
          echo "<div align=\"left\" class=\"unans\">";
          $unanswered = true;
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
        ?>
        <script language="JavaScript">
          function swfLoaded<?php echo $question_no; ?>(message) {
            var num = message.substring(5,message.length);

            setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $question['q_media']; ?>', '<?php echo $tmp_correct; ?>', '<?php if(isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]); ?>','<?php echo $screen_pre_submitted; ?>');
          }
          write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($question['q_media_width'] + 335); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
          write_string('<param name="allowScriptAccess" value="always" />');
          write_string('<param name="movie" value="../paper/hotspot_question.swf" />');
          write_string('<param name="quality" value="high" />');
          write_string('<param name="bgcolor" value="<?php echo $tmp_bgcolor; ?>" />');
          write_string('<embed src="../paper/hotspot_question.swf" quality="high" bgcolor="<?php echo $tmp_bgcolor; ?>" width="<?php echo ($question['q_media_width'] + 335); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
          write_string('</object>');
        </script>
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
          $unanswered = true;
          $tmp_bgcolor = '#FFC0C0';
        } else {
          echo "<div align=\"center\">\n";
          $tmp_bgcolor = $bgcolor;
        }
        $tmp_correct = trim($question['options'][0]['correct']);
        $tmp_correct = str_replace("'", "&#039;", $tmp_correct);
  ?>
    <script language="javascript">
      function swfLoaded<?php echo $question_no; ?>(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $question['q_media']; ?>', '<?php echo $tmp_correct; ?>', '<?php if(isset($user_answers[$current_screen][$q_id])) echo trim($user_answers[$current_screen][$q_id]); ?>', '<?php echo $display_option['marks_correct'] . '~' . $display_option['marks_incorrect'] . '~' . $question['score_method']; ?>');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($question['q_media_width'] + 220); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="../paper/label_question.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="<?php echo $tmp_bgcolor; ?>" />');
      write_string('<embed src="../paper/label_question.swf" quality="high" bgcolor="<?php echo $tmp_bgcolor; ?>" width="<?php echo ($question['q_media_width'] + 220); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
 		</script>

          </div>
        <?php
        if (!isset($user_answers[$current_screen][$q_id]) OR $user_answers[$current_screen][$q_id] == '') {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"0$" . $marks . ";\" />\n";
        } else {
          echo "<input type=\"hidden\" name=\"q" . $question_no . "\" id=\"q" . $question_no . "\" value=\"" . $user_answers[$current_screen][$q_id] . "\" />\n";
        }
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
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" checked /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($user_answers[$current_screen][$q_id],6) . "\" /></td></tr>\n";
        } else {
          echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"other\" /></td><td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "[" . $part_id . "].checked=true\" name=\"q" . $question_no . "_other\" /></td></tr>\n";
        }
      }
      echo '</table>';
    } elseif ($question['display_method'] == 'dropdown') {
      echo '</select></div>';
    }
  } elseif ($question['q_type'] == 'calculation') {
    $score_array = explode(',', $question['display_method']);
    $new_leadin = '';
    $search = array('$A', '$B', '$C', '$D', '$E', '$F', '$G', '$H', '$I', '$J');
    $replace = array($A, $B, $C, $D, $E, $F, $G, $H, $I, $J);
    $question['leadin'] = str_ireplace($search, $replace, $question['leadin']);
    if(strpos($question['leadin'],'class="mee"') !== false) {
      $question['leadin'] = str_replace('ERROR', $string['unanswered'] , $question['leadin']);
    } else {
      $question['leadin'] = str_replace('ERROR', '<span style="color:red; font-weight:bold">' . $string['unanswered'] . '</span>', $question['leadin']);
    }
    echo $question['leadin'];
    if (in_array('ERROR', $replace, true)) {
      echo "<p><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" value=\"\" disabled />" . $score_array[3] .  "</p>\n";
    } else {
      if (isset($user_answers[$current_screen][$q_id]) and $user_answers[$current_screen][$q_id] == '') {
        echo "<div><input type=\"text\" style=\"text-align:right\" name=\"q" . $question_no . "\" size=\"10\" class=\"calc-answer\" />" . $score_array[3] .  "</div>\n";
      } else {
        if ((isset($tmp_user_answers[0]) and $tmp_user_answers[0] != '') or $screen_pre_submitted == 0) {
          $ans = '';
          if(isset($tmp_user_answers[0])){
            $ans = $tmp_user_answers[0];
          }
          echo "<div><input type=\"text\" style=\"text-align:right\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" size=\"10\" value=\"" . $ans . "\" class=\"calc-answer\" />" . $score_array[3] .  "</div>\n";
        } else {
          echo "<div><input type=\"text\" style=\"text-align:right\" class=\"unans calc-answer\" id=\"q" . $question_no . "\" name=\"q" . $question_no . "\" size=\"10\" value=\"\" />" . $score_array[3] .  "</div>\n";
          $unanswered = true;
        }
      }
    }
    $answer = "echo \"" . $question['options'][0]['correct'] . "\"";
    echo '<input type="hidden" name="q' . $question_no . '_answer" value="';
    if (isset($A)) echo $A;
    if (isset($B)) echo ",$B";
    if (isset($C)) echo ",$C";
    if (isset($D)) echo ",$D";
    if (isset($E)) echo ",$E";
    if (isset($F)) echo ",$F";
    if (isset($G)) echo ",$G";
    if (isset($H)) echo ",$H";
    if (isset($I)) echo ",$I";
    if (isset($J)) echo ",$J";
    echo '" />';
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
      echo "<td>Other <input type=\"text\" onkeypress=\"document.questions.q" . $question_no . "_" . $part_id . ".checked=true\" name=\"q" . $question_no . "_other\" value=\"" . substr($answer,($part_id+1)) . "\" /></td></tr>\n";
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

      echo '<li>';
      if (isset($matching_scenarios[$part_id - 1]) and $matching_scenarios[$part_id - 1] != '') echo '<div>' . $matching_scenarios[$part_id - 1] . '</div>';
      if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') {
        echo "<div>" . display_media($matching_media[$part_id], $matching_media_width[$part_id], $matching_media_height[$part_id], '') . "</div>\n<br />";
      }
      $sub_answers = explode('$', $matching_answers[$part_id - 1]);
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
  if ($question['q_type'] != 'hotspot' and $question['q_type'] != 'likert' and $question['q_type'] != 'info' and $question['q_type'] != 'calculation' and $question['q_type'] != 'matrix') echo '</blockquote>';
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
    echo "</td></tr>\n";
  }
  if ($question['q_type'] != 'info') echo "<input type=\"hidden\" name=\"order$question_no\" value=\"" . implode(',', $question['option_order']) . "\" />\n";
  $used_questions[$q_id] = $q_id;
}
?>