<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/stringutils.class.php';

function getSpecialSettings($userID, $mysqlidb) {
  global $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font;
  
  $stmt = $mysqlidb->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs, users WHERE users.id=special_needs.userid AND special_needs = 1 AND userid=?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
  $stmt->close();
}

function echo_content($html) {
  echo $html;
  if (strpos($html,'<div>') === false and strpos($html,'<p>') === false and strpos($html,'<br') === false) {
    echo "<br />\n<br />\n";
  }
}

function reset_feedback($hide) {
  // Set all the feedback display options to '0' so that they become hidden.
  global $tmp_display_correct_answer, $tmp_display_students_response, $tmp_display_feedback, $tmp_display_question_mark, $strong_on, $strong_off;

  if ($hide == '1') {
    $tmp_display_correct_answer = '0';
    $tmp_display_students_response = '0';
    $tmp_display_feedback = '0';
    $tmp_display_question_mark = '0';

    $strong_on = '';
    $strong_off = '';
  }
}

function init_feedback(&$strong_on, &$strong_off) {
  // Reset feedback back to defaults.
  global $tmp_display_correct_answer, $tmp_display_students_response, $tmp_display_feedback, $tmp_display_question_mark, $display_correct_answer, $display_students_response, $display_feedback, $display_question_mark;

  $tmp_display_correct_answer = $display_correct_answer;
  $tmp_display_students_response = $display_students_response;
  $tmp_display_feedback = $display_feedback;
  $tmp_display_question_mark = $display_question_mark;

  if ($display_correct_answer == '1') {
    $strong_on = '<strong>';
    $strong_off = '</strong>';
  } else {
    $strong_on = '';
    $strong_off = '';
  }
}

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

function display_feedback($sessionid, $temp_userID, $paperID, $paper_type, $log_type, $paper_title, $paper_postscript, $marking, $userroles, $mysqlidb) {
  global $language, $string, $bottom_html, $hide_if_unanswered, $tmp_display_correct_answer, $tmp_display_students_response, $tmp_display_feedback, $tmp_display_question_mark, $moduleID, $calendar_year, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $pass_mark, $cfg_root_path, $strong_on, $strong_off;
  
  $strong_on = '';
  $strong_off = '';

  // Get any questions to exclude.
  $excluded = array();
  $exclude_query = $mysqlidb->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $exclude_query->bind_param('i', $paperID);
  $exclude_query->execute();
  $exclude_query->bind_result($exclude_q_id, $exclude_parts);
  while ($exclude_query->fetch()) {
    $excluded[$exclude_q_id] = $exclude_parts;
  }
  $exclude_query->close();

  if ($paper_type == '2' and (strpos($userroles,'Staff') !== false or strpos($userroles,'SysAdmin') !== false)) {
    if (!isset($_GET['userid'])) {
      $tmp_string = sprintf($string['thankyoumsg'], $paper_title);
      echo '<blockquote><p style="font-size:450%; font-family:\'Monotype Corsiva\',Rage,\'Brush Script MT\',\'Lucida Handwriting\',sans-serif">' . $string['thankyou'] . '</p><p>' . $tmp_string . '</p>';
      //echo '<blockquote><p><img src="../artwork/thankyou.gif" width="238" height="76" alt="Thank You" /></p><p>' . $tmp_string . '</p><br />';
      if ($paper_postscript != '') echo "<p>$paper_postscript</p>\n";
      echo '</blockquote>';
      
      echo '<div class="studentview">' . $string['studentviewend'] . '</div>';
      echo '<div class="staffview"><div style="padding-left:15px">' . $string['staffviewbelow'] . '</div></div>';
    }
  }
  $q_no = 0;
  $old_q_id = 0;
  
  // Get standards setting data
  if (substr($marking,0,1) == '2') {
    $standards_setting = array();
    $tmp_parts = explode(',',$marking);
    $std_data = $mysqlidb->prepare("SELECT questionID, rating FROM standards_setting WHERE paperID=? AND setterID=? AND std_set=?");
    $std_data->bind_param('iis', $paperID, $tmp_parts[1], $tmp_parts[2]);
    $std_data->execute();
    $std_data->bind_result($questionID, $rating);
    while ($row = $std_data->fetch()) {
      $standards_setting[$questionID] = $rating;
    }
    $std_data->close();
  } else {
    $standards_setting = array();
  }

  // Capture the structure of the paper into an array.
  $paper = array();
  $std_used = false;
  $total_random_mark = 0;
  $total_marks = 0;
  $user_mark = 0;
  
  $answer_data = $mysqlidb->prepare("SELECT screen, questions.q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method, score_method, notes, q_media, q_media_width, q_media_height, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial, display_pos, status FROM (papers, questions, options) WHERE papers.question=questions.q_id AND paper=? AND questions.q_id=options.o_id ORDER BY screen, display_pos, id_num");
  $answer_data->bind_param('i', $paperID);
  $answer_data->execute();
  $answer_data->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $score_method, $notes, $q_media, $q_media_width, $q_media_height, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial, $display_pos, $status);
  while ($answer_data->fetch()) {
    if ($old_q_id != $q_id or $old_display_pos != $display_pos) {  // New question.
     if ($old_q_id != 0) {
        if (isset($excluded[$old_q_id])) {
          $tmp_exclude = $excluded[$old_q_id];
        } else {
          $tmp_exclude = '';
        }
        $paper[$q_no]['totalpos'] = qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $paper[$q_no]['option_text'], $paper[$q_no]['correct'], $old_display_method, $old_score_method);
        $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $paper[$q_no]['option_text'], $paper[$q_no]['correct'], $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      }
      $correct_no = 0;
      $old_marks_correct = 0;
      $q_no++;
      $paper[$q_no]['q_id'] = $q_id;
      $paper[$q_no]['screen'] = $screen;
      $paper[$q_no]['display_pos'] = $display_pos;
      $paper[$q_no]['scenario'] = $scenario;
      $paper[$q_no]['theme'] = $theme;
      $paper[$q_no]['q_type'] = $q_type;
      $paper[$q_no]['leadin'] = $leadin;
      $paper[$q_no]['notes'] = $notes;
      $paper[$q_no]['marks_correct'] = $marks_correct;
      $paper[$q_no]['marks_incorrect'] = $marks_incorrect;
      $paper[$q_no]['marks_partial'] = $marks_partial;
      $paper[$q_no]['q_media'] = $q_media;
      $paper[$q_no]['q_media_height'] = $q_media_height;
      $paper[$q_no]['q_media_width'] = $q_media_width;
      $paper[$q_no]['display_method'] = $display_method;
      $paper[$q_no]['score_method'] = $score_method;
      $paper[$q_no]['correct_fback'] = $correct_fback;
      $paper[$q_no]['incorrect_fback'] = $incorrect_fback;
      $paper[$q_no]['status'] = $status;

      $paper[$q_no]['option_text'] = array();
      $paper[$q_no]['feedback_right'] = array();
      $paper[$q_no]['feedback_wrong'] = array();
      $paper[$q_no]['o_media'] = array();
      $paper[$q_no]['o_media_width'] = array();
      $paper[$q_no]['o_media_height'] = array();
      $paper[$q_no]['correct'] = array();
    }
    $paper[$q_no]['option_text'][] = $option_text;
    $paper[$q_no]['feedback_right'][] = $feedback_right;
    $paper[$q_no]['feedback_wrong'][] = $feedback_wrong;
    $paper[$q_no]['o_media'][] = $o_media;
    $paper[$q_no]['o_media_width'][] = $o_media_width;
    $paper[$q_no]['o_media_height'][] = $o_media_height;
    $paper[$q_no]['correct'][] = $correct;
    
    if (isset($standards_setting[$q_id])) {
      $paper[$q_no]['std'] = explode(',',$standards_setting[$q_id]);
    } else {
      $paper[$q_no]['std'] = '';
    }

    $old_q_id = $q_id;
    $old_display_pos = $display_pos;
    $old_q_type = $q_type;
    $old_display_method = $display_method;
    $old_score_method = $score_method;
    $old_q_media_width = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_status = $status;
    $old_marks_correct = $marks_correct;
  }
  $answer_data->close();
  if (isset($excluded[$old_q_id])) {
    $tmp_excluded = $excluded[$old_q_id];
  } else {
    $tmp_excluded = '';
  }
  $paper[$q_no]['totalpos'] = qMarks($old_q_type, $tmp_excluded, $old_marks_correct, $paper[$q_no]['option_text'], $paper[$q_no]['correct'], $old_display_method, $old_score_method);
  $total_random_mark += qRandomMarks($old_q_type, $tmp_excluded, $old_marks_correct, $paper[$q_no]['option_text'], $paper[$q_no]['correct'], $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
  
  // Parse for random questions.
  for ($i=1; $i<=$q_no; $i++) {
    if ($paper[$i]['q_type'] == 'random' or $paper[$i]['q_type'] == 'keyword_based') {
      if ($paper[$i]['q_type'] == 'keyword_based') {
        $selected_q_id = (isset($_POST['q' . $i . '_randomID'])) ? $_POST['q' . $i . '_randomID'] : 0;
      } else {
        $possible_array = array();
        foreach ($paper[$i]['option_text'] as $checkID) {
          $used = false;
          for ($j = 1; $j <= $q_no; $j++) {
            if ($paper[$j]['q_id'] == $checkID) {
              $used = true;
              break;
            }
          }
          if ($used == false) {
            $possible_array[] = $checkID;
          }
        }

        $possible_questions = implode(',', $possible_array);
        $answer_data = $mysqlidb->prepare("SELECT q_id FROM log$log_type WHERE q_paper=? AND started=? AND userID=? AND screen=? AND q_id IN ($possible_questions) ORDER BY id");
        $answer_data->bind_param('isii', $paperID, $sessionid, $temp_userID, $paper[$i]['screen']);
        $answer_data->execute();
        $answer_data->bind_result($selected_q_id); 
        $answer_data->fetch();
        $answer_data->close();
      }
      
      // Look up selected question and overwrite data.
      if ($selected_q_id > 0) {
        $stems = 0;
        $question_data = $mysqlidb->prepare("SELECT questions.q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method, score_method, notes, q_media, q_media_width, q_media_height, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial, status FROM (questions, options) WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
        $question_data->bind_param('i', $selected_q_id);
        $question_data->execute();
        $question_data->store_result();
        $question_data->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $score_method, $notes, $q_media, $q_media_width, $q_media_height, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial, $status);
        while ($question_data->fetch()) {
          if ($stems == 0) {
            $correct_no = 0;
            $paper[$i]['q_id'] = $q_id;
            $paper[$i]['scenario'] = $scenario;
            $paper[$i]['theme'] = $theme;
            $paper[$i]['q_type'] = $q_type;
            $paper[$i]['leadin'] = $leadin;
            $paper[$i]['notes'] = $notes;
            $paper[$i]['marks_correct'] = $marks_correct;
            $paper[$i]['marks_incorrect'] = $marks_incorrect;
            $paper[$i]['marks_partial'] = $marks_partial;
            $paper[$i]['q_media'] = $q_media;
            $paper[$i]['q_media_height'] = $q_media_height;
            $paper[$i]['q_media_width'] = $q_media_width;
            $paper[$i]['display_method'] = $display_method;
            $paper[$i]['score_method'] = $score_method;
            $paper[$i]['correct_fback'] = $correct_fback;
            $paper[$i]['incorrect_fback'] = $incorrect_fback;
            $paper[$i]['status'] = $status;

            $paper[$i]['option_text'] = array();
            $paper[$i]['feedback_right'] = array();
            $paper[$i]['feedback_wrong'] = array();
            $paper[$i]['o_media'] = array();
            $paper[$i]['o_media_width'] = array();
            $paper[$i]['o_media_height'] = array();
            $paper[$i]['correct'] = array();
          }    
          $paper[$i]['option_text'][] = $option_text;
          $paper[$i]['feedback_right'][] = $feedback_right;
          $paper[$i]['feedback_wrong'][] = $feedback_wrong;
          $paper[$i]['o_media'][] = $o_media;
          $paper[$i]['o_media_width'][] = $o_media_width;
          $paper[$i]['o_media_height'][] = $o_media_height;
          $paper[$i]['correct'][] = $correct;
          if (isset($standards_setting[$q_id])) {
            $paper[$i]['std'] = explode(',',$standards_setting[$q_id]);
          } else {
            $paper[$i]['std'] = '';
          }

          if ($q_type == 'mrq') {
            if ($correct == 'y') $correct_no++;
          } elseif ($q_type == 'rank') {
            if ($correct != 0) $correct_no++;
          }
          $stems++;
        }
        $question_data->close();
      } else {
        $paper[$i]['q_type'] = 'random_failed';
        $paper[$i]['leadin'] = "<span style=\"color: #f00;\">ERROR: unable to find unique question for supplied keywords</span>";
      }
      if(isset($excluded[$old_q_id])) {
        $tmp_excluded = $excluded[$old_q_id];
      } else {
        $tmp_excluded = '';
      }
      $paper[$i]['totalpos'] = qMarks($q_type, $tmp_excluded, $marks_correct, $paper[$i]['option_text'], $paper[$i]['correct'], $display_method, $score_method);
      $total_random_mark += qRandomMarks($q_type, $tmp_excluded, $old_marks_correct, $paper[$i]['option_text'], $paper[$i]['correct'], $display_method, $score_method, $q_media_width, $q_media_height);
    }
  }

  // Add the user's log data into the above array.
  $tmp_q_id = 1;
  if ($paper_type == '5') {
    $answer_data = $mysqlidb->prepare("SELECT 1 AS screen, q_id, NULL AS user_answer, mark, NULL AS option_order FROM log5 WHERE q_paper=? AND started=? AND userID=? ORDER BY id");
  } else {
    $answer_data = $mysqlidb->prepare("SELECT screen, q_id, user_answer, mark, option_order FROM log$log_type WHERE q_paper=? AND started=? AND userID=? ORDER BY screen, id");
  }
  if ($answer_data) {
    $answer_data->bind_param('isi', $paperID, $sessionid, $temp_userID);
    $answer_data->execute();
    $answer_data->store_result();
    $answer_data->bind_result($screen, $q_id, $user_answer, $mark, $option_order);
    while ($answer_data->fetch()) {
      // Skip over missing screens;
      foreach ($paper as $tmp_q_no => $question) {
        if ($question['q_id'] == $q_id and $question['screen'] == $screen) {
          $paper[$tmp_q_no]['user_answer'] = $user_answer;
          $paper[$tmp_q_no]['mark'] = $mark;
          $paper[$tmp_q_no]['option_order'] = $option_order;
          break;
        }
      }
    }
    $answer_data->close();
  } else {
    display_error("User Answer Error", $mysqlidb->error);
  }
  
  ?>
    <br />
    <div align="center">
    <table cellpadding="4" cellspacing="0" border="0" class="box" style="font-size:90%">
    <tr>
    <td style="margin:0px"><div style="font-weight:bold; font-size:120%"><?php echo $string['key']; ?></div>
    <div><img src="../artwork/tick.gif" width="17" height="16" alt="Tick" /> <?php echo $string['correctanswer']; ?><br />
    <img src="../artwork/cross.gif" width="17" height="16" alt="Cross" /> <?php echo $string['incorrectanswer']; ?><br />
    <?php echo $string['boldwords'] ; ?><br />
    <span class="fback"><?php echo $string['feedbackinred']; ?></span><br />
    <?php
    if (substr($marking,0,1) == '2') echo '<span style="background-color:#f27000; color:white; width:35px; text-align:center">&nbsp;EE&nbsp;</span> ' . $string['difficultyofthequestion'] . '.</div></td>';
    ?>
    <td style="text-align:right; vertical-align:top; width:80px"><a href="javascript:window.print()"><img src="../artwork/lrg_print_icon.png" width="64" height="64" border="0" /></a></td>
    </tr>
    </table>
    </div>
    <br />
 <?php

  // Process the array for display.
  $old_screen = 1;
  $display_no = 0;
     
  echo "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"table-layout:fixed\">\n<col width=\"40\"><col>\n";
  for ($question=1; $question<=$q_no; $question++) {
    $display_no++;
    
    init_feedback($strong_on, $strong_off);

    if ($old_screen < $paper[$question]['screen']) {
      echo '</table><br />';
      
      echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $paper[$question]['screen'] . '</span></div>';
      echo "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\" style=\"table-layout:fixed\">\n<col width=\"40\"><col>\n";
      if ($paper[$question]['q_type'] != 'info') {
        if ($paper[$question]['theme'] != '') echo "<tr><td colspan=\"2\" class=\"theme\">" . $paper[$question]['theme'] . "</td></tr>\n";
        echo "<tr><td class=\"q_no\">" . $display_no . ".</td><td>";
      }
    } else {
      if ($paper[$question]['q_type'] != 'info') {
        if ($paper[$question]['theme'] != '') echo "<tr><td colspan=\"2\" class=\"theme\">" . $paper[$question]['theme'] . "</td></tr>\n";
        echo "<tr><td class=\"q_no\">" . $display_no . ".</td><td>";
      }
    }
    if (trim($paper[$question]['notes']) != '') {
      echo "<p class=\"note\"><img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"" . $string['note']  . "\" />&nbsp;<strong>" . $string['note']  . ":</strong>&nbsp;" . $paper[$question]['notes'] . "</p>\n";
    }
    $no_options = count($paper[$question]['option_text']);

    if (array_key_exists($paper[$question]['q_id'],$excluded)) {
      $tmp_exclude = $excluded[$paper[$question]['q_id']];
    } else {
      $tmp_exclude = '0000000000000000000000000000000000000000';      // No exclusions found, enable all options.
    }
    
    // Backwards compatibility - make up a default order if there is none.
    if (!isset($paper[$question]['option_order']) or $paper[$question]['option_order'] == '') {
      for ($i=0; $i<$no_options; $i++) {
        if ($i == 0) {
          $paper[$question]['option_order'] = $i;
        } else {
          $paper[$question]['option_order'] .= ',' . $i;
        }
      }
      
    }
    
    $option_order = explode(',',$paper[$question]['option_order']);
    $question_no = $display_no;

    switch ($paper[$question]['q_type']) {
      case 'area':
        if (isset($paper[$question]['user_answer'])) {
          $tmp_user_answer = $paper[$question]['user_answer'];
          $answer_parts = explode(';', $tmp_user_answer);
          $tmp_user_answer = substr($answer_parts[1], 0, -2);
          
          $percents = explode(',', $answer_parts[0]);
        } else {
          $tmp_user_answer = '';
        }
        
        if ($tmp_user_answer == '') {
          reset_feedback($hide_if_unanswered);
        }
        if ($tmp_display_correct_answer == '0') {
          $paper[$question]['correct'][0] = '';
        }
        ?>
        <script language="javascript">
          write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="<?php echo $paper[$question]['q_media_width']; ?>" height="<?php echo $paper[$question]['q_media_height']; ?>" id="externalinterfaceq<?php echo $question_no; ?>_1" align="top">');
          write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/question/edit/area.swf" />');
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
          write_string('<object type="application/x-shockwave-flash" data="<?php echo $cfg_root_path ?>/question/edit/area.swf" id="externalinterfaceq<?php echo $question_no; ?>_2" width="<?php echo $paper[$question]['q_media_width']; ?>" height="<?php echo $paper[$question]['q_media_height']; ?>">');
          write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/question/edit/area.swf" />');
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
          write_string('<a href="http://www.adobe.com/go/getflash"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>');
          write_string('<!--[if !IE]>-->');
          write_string('</object>');
          write_string('<!--<![endif]-->');
          write_string('</object>');
          <?php
          if ($tmp_display_correct_answer == '1') {
            echo "sendTextToAS3('$language', 'q$question_no', 3, '../media/" . $paper[$question]['q_media'] . "', '" . $paper[$question]['correct'][0] . "', '$tmp_user_answer');\n";
          } else {
            echo "sendTextToAS3('$language', 'q$question_no', 1, '../media/" . $paper[$question]['q_media'] . "', '" . $paper[$question]['correct'][0] . "', '" . $paper[$question]['user_answer'] . "');\n";
          }
          ?>
        </script>
        <br />
        <?php
        if ($paper[$question]['correct_fback'] != '' and $tmp_display_feedback == '1') echo "<div class=\"fback\">" . $paper[$question]['correct_fback'] . "</div>\n";
        if ($tmp_user_answer == '') {
          echo "<div style=\"color:#808080\">{$string['unanswered']}</div>\n";
        } else {
          echo "<table>\n";
          echo "<tr><td>{$string['withinshape']}</td><td style=\"text-align:right\">" . $percents[1] . "%</td></tr>\n";
          echo "<tr><td>{$string['outsideshape']}&nbsp;</td><td style=\"text-align:right\">" . $percents[2] . "%</td></tr>\n";
          echo "</table>\n";
        }
        break;
      case 'calculation':
        $paper[$question]['mark'] = 0;
        if (isset($paper[$question]['user_answer'])) {
          $tmp_answer = explode('|', $paper[$question]['user_answer']);
        } else {
          $tmp_answer = array('','','','','','','','');
        }
        
        if ($tmp_answer[0] == '') {
          reset_feedback($hide_if_unanswered);
        }
        
        $saved_response = $tmp_answer[0];
        $part_id = 1;
        
        $tmp_leadin = $paper[$question]['leadin'];
        $tmp_fback = $paper[$question]['correct_fback'];
        
        $variable_array = explode(',',$tmp_answer[2]);
        $var_no = 1;
        foreach($variable_array as $individual_variable) {
          switch ($var_no) {
            case 1:
              $A = $individual_variable;
              $tmp_leadin = str_replace('$A',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$A',$individual_variable,$tmp_fback);
              break;
            case 2:
              $B = $individual_variable;
              $tmp_leadin = str_replace('$B',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$B',$individual_variable,$tmp_fback);
              break;
            case 3:
              $C = $individual_variable;
              $tmp_leadin = str_replace('$C',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$C',$individual_variable,$tmp_fback);
              break;
            case 4:
              $D = $individual_variable;
              $tmp_leadin = str_replace('$D',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$D',$individual_variable,$tmp_fback);
              break;
            case 5:
              $E = $individual_variable;
              $tmp_leadin = str_replace('$E',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$E',$individual_variable,$tmp_fback);
              break;
            case 6:
              $F = $individual_variable;
              $tmp_leadin = str_replace('$F',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$F',$individual_variable,$tmp_fback);
              break;
            case 7:
              $G = $individual_variable;
              $tmp_leadin = str_replace('$G',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$G',$individual_variable,$tmp_fback);
              break;
            case 8:
              $H = $individual_variable;
              $tmp_leadin = str_replace('$H',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$H',$individual_variable,$tmp_fback);
              break;
            case 9:
              $I = $individual_variable;
              $tmp_leadin = str_replace('$I',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$I',$individual_variable,$tmp_fback);
              break;
            case 10:
              $J = $individual_variable;
              $tmp_leadin = str_replace('$J',$individual_variable,$tmp_leadin);
              $tmp_fback = str_replace('$J',$individual_variable,$tmp_fback);
              break;
          }
          $var_no++;
        }
        
        if ($paper[$question]['scenario'] != '') echo "<p>" . $paper[$question]['scenario'] . "</p>\n";
        if ($paper[$question]['q_media'] != '') echo "<p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
        
        echo_content($tmp_leadin);
        
        $score_array = explode(',', $paper[$question]['display_method']);

        $decimals = ($score_array[0] != '') ? $score_array[0] : 0;

        $tolerance_full = $score_array[1];
        if (StringUtils::ends_with($tolerance_full, '%')) {
          $tolerance_perc = rtrim($tolerance_full, '%');
          $tolerance_full = $tmp_answer[1] * ($tolerance_perc/100);
        }
        $tolerance_partial = $score_array[2];
        if (StringUtils::ends_with($tolerance_partial, '%')) {
          $tolerance_perc = rtrim($tolerance_partial, '%');
          $tolerance_partial = $tmp_answer[1] * ($tolerance_perc/100);
        }

        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\"><tr>";
        if ($tmp_display_correct_answer == '1') {
          echo '<td>';
           if(isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
          echo '</td>';
        } else {
          echo '<td></td>';
        }

        $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);
                  
        if ($tmp_answer[0] == '') {
          echo "<td><img src=\"../artwork/blank_tick_cross.gif\" width=\"17\" height=\"16\" alt=\"\" /><input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q' . $question . '\" size=\"10\" value=\"" . $string['unanswered'] . "\" />" . $score_array[3];
        } else {
          echo '<td>';
          if ($tmp_exclude == '1')  echo '<span class="exclude">';
          
          $difference = round(abs($saved_response_clean - $tmp_answer[1]), 12);
          
          if ($saved_response_clean == $tmp_answer[1]) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } elseif ($difference > 0 and $difference <= $tolerance_full and $tolerance_full > 0) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } elseif ($difference > 0 and $difference <= $tolerance_partial and $tolerance_partial > 0) {
            $paper[$question]['mark'] = $paper[$question]['marks_partial'];
          } else {
            $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
          }
          if (substr($tmp_exclude,$part_id,1) == '1') {
            $paper[$question]['mark'] = 0;
          }
               
          if ($paper[$question]['mark'] == $paper[$question]['marks_correct']) {
            if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
          } elseif ($tolerance_partial > 0 and $paper[$question]['mark'] == $paper[$question]['marks_partial'] and $paper[$question]['marks_partial'] > 0) {
            if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick_half.gif" width="17" height="16" alt="Half Right" />';
          } else {
            if ($tmp_display_students_response == '1') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
          }
          echo '<input type="text" style="text-align:right" name="q' . $question . '" size="10" value="' . $tmp_answer[0] . '" />' . $score_array[3];
        }
        if ($tmp_display_correct_answer == '1') {
          if (is_numeric($tmp_answer[1])) {
            $decimals = ($score_array[0] != '') ? $score_array[0] : 0;
            echo ' <strong>(' . number_format($tmp_answer[1], $decimals, '.', '');
            if ($score_array[3] != '') echo ' ' . $score_array[3];
            echo ')</strong>';
          } else {
            echo ' <strong>(' . $tmp_answer[1] . ')</strong>';
          }
        } else {
          echo ' ';
        }
                  
        if ($saved_response_clean <> $tmp_answer[1] and $paper[$question]['mark'] == $paper[$question]['marks_correct'] and $tolerance_full > 0) {
          echo ' ' . $string['withatoleranceof'] . ' ' . $score_array[1];
          if (StringUtils::ends_with($score_array[1], '%')) echo " (" . (round($tmp_answer[1], $decimals) - $tolerance_full) . " - " . (round($tmp_answer[1], $decimals) + $tolerance_full) . ")";
        }
        if ($saved_response_clean <> $tmp_answer[1] and $paper[$question]['mark'] == $paper[$question]['marks_partial'] and $tolerance_partial > 0) {
          echo ' ' . $string['withatoleranceof'] . ' ' . $score_array[2];
          if (StringUtils::ends_with($score_array[2], '%')) echo " (" . (round($tmp_answer[1], $decimals) - $tolerance_partial) . " - " . (round($tmp_answer[1], $decimals) + $tolerance_partial) . ")";
        }
        
        if ($tmp_exclude == '1')  echo '</span>';
        echo "</td></tr>\n</table>\n";
        if ($tmp_fback != '' and $tmp_display_feedback == '1') echo "<div class=\"fback\" style=\"margin-left:17px\">&nbsp;$tmp_fback</div>\n";
        break;
      case 'dichotomous':
        // Check to see if the user has answered any options.
        $answered = false;
        if (isset($paper[$question]['user_answer'])) {
          for ($i=0; $i<strlen($paper[$question]['user_answer']); $i++) {
            $answer_part = substr($paper[$question]['user_answer'], $i, 1);
            if ($answer_part == 't' or $answer_part == 'f') {
              $answered = true;
            }
          }
        }
        if ($answered  == false) {
          reset_feedback($hide_if_unanswered);
        }
      
        $paper[$question]['mark'] = 0;
        if (!empty($paper[$question]['scenario'])) echo_content($paper[$question]['scenario']);
        if (!empty($paper[$question]['q_media'])) echo "<br /><p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
        echo_content($paper[$question]['leadin']);
        echo '<table cellpadding="0" cellspacing="1" border="0">';
        
        $abstain = false;
        $no_correct = 0;
        $no_incorrect = 0;
        if ($paper[$question]['display_method'] == 'TF_NegativeAbstain' or $paper[$question]['display_method'] == 'TF_NegativeAbstainHalf' or $paper[$question]['display_method'] == 'TF_Positive') {
          echo "<tr><td></td><td align=\"center\" width=\"50\" style=\"color:$labelcolor; font-size:90%\">" . $string['true'] . "</td><td align=\"center\" width=\"50\" style=\"color:$labelcolor; font-size:90%\">" . $string['false'] . "</td>";
          $true_label = 'T';
          $false_label = 'F';
        } else {
          echo "<tr><td></td><td align=\"center\" width=\"50\" style=\"color:$labelcolor; font-size:90%\">" . $string['yes'] . "</td><td align=\"center\" width=\"50\" style=\"color:$labelcolor; font-size:90%\">" . $string['no'] . "</td>";
          $true_label = 'Y';
          $false_label = 'N';
        }
        if ($paper[$question]['display_method'] == 'TF_NegativeAbstain' or $paper[$question]['display_method'] == 'YN_NegativeAbstain' or $paper[$question]['display_method'] == 'TF_NegativeAbstainHalf') {
          echo "<td align=\"center\" width=\"50\" style=\"color:$labelcolor; font-size:90%\">" . $string['abstain'] . "</td>";
          $abstain = true;
        }
        echo "</tr>\n";
        
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          $tmp_part_id = $option_order[$part_id];
          if ($tmp_display_correct_answer == '1') {
            echo '<tr><td>';
            if(isset($paper[$question]['std'][$tmp_part_id])) echo display_std($paper[$question]['std'][$tmp_part_id]);
            echo '</td>';
          } else {
            echo '<tr><td></td>';
          }
    
          $correct_icon = '';
          if (isset($paper[$question]['user_answer']) and substr($paper[$question]['user_answer'], $tmp_part_id, 1) != 'u' and substr($paper[$question]['user_answer'], $tmp_part_id, 1) != 'a') {
            if ($paper[$question]['correct'][$tmp_part_id] == substr($paper[$question]['user_answer'], $tmp_part_id, 1)) {
              if ($tmp_display_students_response == '1') $correct_icon = '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
              if (substr($tmp_exclude,$tmp_part_id,1) == '0') {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                $no_correct++;
              }
            } else {
              if ($tmp_display_students_response == '1') $correct_icon = '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
              if (substr($tmp_exclude,$tmp_part_id,1) == '0') {
                $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                $no_incorrect++;
              }
            }
          }
          
          // Radio buttons
          if (!isset($paper[$question]['user_answer']) or substr($paper[$question]['user_answer'], $tmp_part_id, 1)=='u' or substr($paper[$question]['user_answer'], $tmp_part_id, 1)=='') {
            echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td>";
            if ($abstain == true) echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td>";
          } elseif (isset($paper[$question]['user_answer']) and substr($paper[$question]['user_answer'], $tmp_part_id, 1)=='t') {
            echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" checked />$correct_icon</td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td>";
            if ($abstain == true) echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td>";
          } elseif (isset($paper[$question]['user_answer']) and substr($paper[$question]['user_answer'], $tmp_part_id, 1)=='f') {
            echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" checked />$correct_icon</td>";
            if ($abstain == true) echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td>";
          } elseif (isset($paper[$question]['user_answer']) and substr($paper[$question]['user_answer'], $tmp_part_id, 1)=='a') {
            echo "<td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" /></td><td>&nbsp;&nbsp;<input type=\"radio\" name=\"q" . $question . "_" . $tmp_part_id ."\" checked /></td>";
          }
          
          echo '<td>';
          if ($tmp_display_correct_answer == '1') {
            if ($paper[$question]['correct'][$tmp_part_id] == 't') {
              echo "<strong>$true_label&nbsp;</strong>";
            } elseif ($paper[$question]['correct'][$tmp_part_id] == 'f') {
              echo "<strong>$false_label&nbsp;</strong>";
            }
          }
          echo '</td>';
          
          // Option data
          if ($paper[$question]['o_media'][$tmp_part_id] == '') {
            echo "<td";
            if (substr($tmp_exclude,$tmp_part_id,1) == '1') echo ' class="exclude"';
            echo ">" . $paper[$question]['option_text'][$tmp_part_id] . "</td></tr>\n";
          } else {
            echo "<td";
            if (substr($tmp_exclude,$tmp_part_id,1) == '1') echo ' class="exclude"';
            echo ">";
            if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') echo $paper[$question]['option_text'][$tmp_part_id] . '<br />';
            echo "<img src=\"../media/" . $paper[$question]['o_media'][$tmp_part_id] . "\" width=\"" . $paper[$question]['o_media_width'][$tmp_part_id] . "\" height=\"" . $paper[$question]['o_media_height'][$tmp_part_id] . "\" border=\"0\" /></td></tr>\n";
          }

          // Feedback
          if ($tmp_display_feedback == '1') {
            if (isset($paper[$question]['user_answer']) and $paper[$question]['correct'][$tmp_part_id] == substr($paper[$question]['user_answer'], $tmp_part_id, 1)) {
              if ($paper[$question]['feedback_right'][$tmp_part_id] != '') {
                echo '<tr><td></td><td></td><td></td><td></td>';
                if ($abstain == true) echo '<td></td>';
                echo "<td class=\"fback\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
              }
            } else {
              if ($paper[$question]['feedback_wrong'][$tmp_part_id] != '') {
                echo '<tr><td></td><td></td><td></td><td></td>';
                if ($abstain == true) echo '<td></td>';
                echo "<td class=\"fback\">" . $paper[$question]['feedback_wrong'][$tmp_part_id] . "</td></tr>\n";
              } elseif ($paper[$question]['feedback_right'][$tmp_part_id] != '') {
                echo '<tr><td></td><td></td><td></td><td></td>';
                if ($abstain == true) echo '<td></td>';
                echo "<td class=\"fback\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
              }
            }
          }
        }

        
        if ($paper[$question]['score_method'] == 'Mark per Question') {
          if ($no_correct == 0 and $no_incorrect == 0) {
            $paper[$question]['mark'] = 0;
          } elseif ($no_correct == $no_options) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } else {
            $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
          }
        }
        if ($answered  == false) {
          echo "<tr><td colspan=\"3\" style=\"color:#808080\">" . $string['unanswered'] . "</td></tr>\n";
        }
        echo "</table>\n";
        if ($paper[$question]['correct_fback'] != '' and $tmp_display_feedback == '1') echo "<div class=\"fback\" style=\"margin-left:17px\">&nbsp;{$paper[$question]['correct_fback']}</div>\n";
        break;
      case 'info':
        echo "<tr><td colspan=\"2\" style=\"padding-left:10px; padding-right:10px\">";
        if ($paper[$question]['q_media'] != '' and $paper[$question]['q_media'] != NULL) {
          echo '<p align="center">' . display_media($paper[$question]['q_media'],$paper[$question]['q_media_width'],$paper[$question]['q_media_height'],$question_no) . "</p>\n";
        }
        echo $paper[$question]['leadin'];
        echo "</td></tr>\n";
        $display_no--;
        break;
      case 'mcq':
        if (!isset($paper[$question]['user_answer']) or (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 0)) {
          reset_feedback($hide_if_unanswered);
        }
           
        if ($paper[$question]['scenario'] != '') echo_content($paper[$question]['scenario']);
        if ($paper[$question]['q_media'] != '') echo "<p align=\"center\">" . display_media($paper[$question]['q_media'],$paper[$question]['q_media_width'],$paper[$question]['q_media_height'], '') . "</p>\n";
        echo_content($paper[$question]['leadin']);
        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          $tmp_part_id = $option_order[$part_id];
          echo '<tr>';
          if ($tmp_part_id+1 == $paper[$question]['correct'][0]) {
            if (isset($paper[$question]['user_answer']) and $tmp_part_id+1 == $paper[$question]['user_answer']) {
              if ($tmp_display_correct_answer == '1') {
                echo '<td>';
                if(isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
                echo '</td>';          
              } else {
                echo '<td></td>';          
              }
              if ($tmp_display_students_response == '1') {
                echo '<td><img src="../artwork/tick.gif" width="17" height="16" alt="Tick" /></td>';
              } else {
                echo '<td></td>';
              }
              echo "<td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" checked /></td><td style=\"width:99%\">";
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
                echo $strong_on . $paper[$question]['option_text'][$tmp_part_id] . $strong_off;
                if (substr($tmp_exclude,0,1) == '1') echo '</span>';
                echo '<br />';
              }
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo '&nbsp;<img src="../media/' . $paper[$question]['o_media'][$tmp_part_id] . '" height="' . $paper[$question]['o_media_height'][$tmp_part_id] . '" width="' . $paper[$question]['o_media_width'][$tmp_part_id] . '" border="0" />';
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and $tmp_display_correct_answer == '1') echo ' ' . $strong_on . $string['iscorrect'] . $strong_off;
              }
              echo '</td></tr>';
            } else {
              if ($tmp_display_correct_answer == '1') {
                echo '<td>';
                  if(isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
                echo '</td>';          
              } else {
                echo '<td></td>';          
              }
              echo "<td></td><td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" /></td><td style=\"width:99%\">";
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
                echo $strong_on . $paper[$question]['option_text'][$tmp_part_id] . $strong_off;
                if (substr($tmp_exclude,0,1) == '1') echo '</span>';
                echo '<br />';
              }
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo '&nbsp;<img src="../media/' . $paper[$question]['o_media'][$tmp_part_id] . '" height="' . $paper[$question]['o_media_height'][$tmp_part_id] . '" width="' . $paper[$question]['o_media_width'][$tmp_part_id] . '" border="0" />';
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and $tmp_display_correct_answer == '1') echo ' <strong>'. $string['iscorrect'] . '</strong>';
              }
              echo '</td></tr>';
            }
          } else {
            echo '<td></td>';          
            if (isset($paper[$question]['user_answer']) and $tmp_part_id+1 == $paper[$question]['user_answer']) {
              if ($tmp_display_students_response == '1') {
                echo '<td><img src="../artwork/cross.gif" width="17" height="16" alt="Cross" /></td>';
              } else {
                echo '<td></td>';
              }
              echo "<td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" checked /></td><td style=\"width:99%\">";
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
                echo $paper[$question]['option_text'][$tmp_part_id];
                if (substr($tmp_exclude,0,1) == '1') echo '</span>';
                echo '<br />';
              } 
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo '<img src="../media/' . $paper[$question]['o_media'][$tmp_part_id] . '" height="' . $paper[$question]['o_media_height'][$tmp_part_id] . '" width="' . $paper[$question]['o_media_width'][$tmp_part_id] . '" border="0" />';
              }
              echo "</td></tr>";
            } else {
              echo "<td></td><td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" /></td><td>";
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
                echo $paper[$question]['option_text'][$tmp_part_id];
                if (substr($tmp_exclude,0,1) == '1') echo '</span>';
                echo '<br />';
              }
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo '<img src="../media/' . $paper[$question]['o_media'][$tmp_part_id] . '" height="' . $paper[$question]['o_media_height'][$tmp_part_id] . '" width="' . $paper[$question]['o_media_width'][$tmp_part_id] . '" border="0" />';
              }
              echo '</td></tr>';
            }
          }
          if ($paper[$question]['feedback_right'][$part_id] != '' and $tmp_display_feedback == '1') {
            echo "<tr><td></td><td></td><td></td><td class=\"fback\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
          }
        }
        if (!isset($paper[$question]['user_answer'])) {
          echo "\n<tr><td></td><td></td><td style=\"color:#808080\" colspan=\"2\">" . $string['unanswered'] . "</td></tr>\n";
        } elseif ($paper[$question]['user_answer'] == 0) {
          echo "\n<tr><td></td><td></td><td style=\"color:#808080\" colspan=\"2\">" . $string['unanswered'] . "</td></tr>\n";
        }
        if ($tmp_display_feedback == '1') {
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == $paper[$question]['correct'][0]) {
            if ($paper[$question]['correct_fback'] != '') {
              echo "<tr><td class=\"fback\" colspan=\"4\">&nbsp;</td></tr>\n";
              echo "<tr><td class=\"fback\" colspan=\"4\">" . $paper[$question]['correct_fback'] . "</td></tr>\n";
            }
          } else {
            if ($paper[$question]['incorrect_fback'] != '') {
              echo "<tr><td class=\"fback\" colspan=\"4\">&nbsp;</td></tr>\n";
              echo "<tr><td class=\"fback\" colspan=\"4\">" . $paper[$question]['incorrect_fback'] . "</td></tr>\n";
            } elseif ($paper[$question]['correct_fback'] != '') {
              echo "<tr><td class=\"fback\" colspan=\"4\">&nbsp;</td></tr>\n";
              echo "<tr><td class=\"fback\" colspan=\"4\">" . $paper[$question]['correct_fback'] . "</td></tr>\n";
            }
          }
        }
        echo "</table>\n";
        
        break;
      case 'mrq':
        $std_part = 0;
        if ($paper[$question]['scenario'] != '') echo_content($paper[$question]['scenario']);
        if ($paper[$question]['q_media'] != '') echo "<p align=\"center\">" . display_media($paper[$question]['q_media'],$paper[$question]['q_media_width'],$paper[$question]['q_media_height'], '') . "</p>\n";
        echo_content($paper[$question]['leadin']);
        
        // Check to see if the user has answered any options.
        $answered = false;
        if (isset($paper[$question]['user_answer'])) {
          for ($i=0; $i<strlen($paper[$question]['user_answer']); $i++) {
            if (substr($paper[$question]['user_answer'], $i, 1) == 'y') {
              $answered = true;
            }
          }
        }
        if ($answered  == false) {
          reset_feedback($hide_if_unanswered);
        }

        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          $tmp_part_id = $option_order[$part_id];
          if (isset($paper[$question]['user_answer']) and substr($paper[$question]['user_answer'], $tmp_part_id, 1) == 'y') {
            if ($paper[$question]['correct'][$tmp_part_id] == 'y') {
              if ($tmp_display_correct_answer == '1') {
                echo '<tr><td>';
                  if(isset($paper[$question]['std'][$tmp_part_id])) echo display_std($paper[$question]['std'][$tmp_part_id]);
                echo '</td>';
              } else {
                echo '<tr><td></td>';
              }
              $std_part++;
              if ($tmp_display_students_response == '1') {
                echo '<td valign="top"><img src="../artwork/tick.gif" width="17" height="16" alt="Tick" /></td>';
              } else {
                echo '<td></td>';
              }
              echo "<td valign=\"top\"><input type=\"checkbox\" name=\"q" . $question . "_" . $tmp_part_id . "\" value=\"y\" checked />&nbsp;</td><td>";
              if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                echo $strong_on;
                echo $paper[$question]['option_text'][$tmp_part_id];
                echo "$strong_off<br />\n";
              }
              if (substr($tmp_exclude,0,1) == '1') echo '</span>';
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo display_media($paper[$question]['o_media'][$tmp_part_id], $paper[$question]['o_media_width'][$tmp_part_id] ,$paper[$question]['o_media_height'][$tmp_part_id], '') . "\n";
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and substr($tmp_exclude,0,1) == '1') echo '<span style="color:red">'. $string['isexcluded'] . '</span>';
              }
              echo "</td></tr>\n";
              if ($paper[$question]['feedback_right'][$tmp_part_id] != '' and $tmp_display_feedback == '1') {
                echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
              }
            } else {
              if ($tmp_display_students_response == '1') {
                echo '<tr><td></td><td valign="top"><img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />&nbsp;</td>';
              } else {
                echo '<tr><td></td><td></td>';
              }
              echo "<td valign=\"top\"><input type=\"checkbox\" name=\"q" . $question . "_" . $tmp_part_id . "\" value=\"y\" checked />&nbsp;</td><td>";
              if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                echo $paper[$question]['option_text'][$tmp_part_id] . "<br />\n";
              }
              if (substr($tmp_exclude,0,1) == '1') echo '</span>';
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo display_media($paper[$question]['o_media'][$tmp_part_id], $paper[$question]['o_media_width'][$tmp_part_id], $paper[$question]['o_media_height'][$tmp_part_id], '') . "\n";
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and substr($tmp_exclude,0,1) == '1') echo '<span style="color:red">'. $string['isexcluded'] . '</span>';
              }
              echo "</td></tr>\n";
              if ($tmp_display_feedback == '1') {
                if ($paper[$question]['feedback_wrong'][$tmp_part_id] != '') {
                  echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['feedback_wrong'][$tmp_part_id] . "</td></tr>\n";
                } elseif ($paper[$question]['feedback_right'][$tmp_part_id] != '') {
                  echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
                }
              }
            }
          } else {
            if ($paper[$question]['correct'][$tmp_part_id] == 'y') {
              if ($tmp_display_correct_answer == '1') {
                echo '<tr><td>';
                if(isset($paper[$question]['std'][$tmp_part_id])) echo display_std($paper[$question]['std'][$tmp_part_id]);
                echo '</td>';
              } else {
                echo '<tr><td></td>';
              }
            
              if ($tmp_display_students_response == '1') {
                echo '<td></td>';
              } else {
                echo '<td></td>';
              }
              echo "<td valign=\"top\"><input type=\"checkbox\" name=\"q" . $question . "_" . $tmp_part_id . "\" value=\"y\" />&nbsp;</td><td valign=\"top\">";
              $std_part++;
              if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                echo "$strong_on";
                echo $paper[$question]['option_text'][$tmp_part_id];
                echo "$strong_off</div><br />\n";
              }
              if (substr($tmp_exclude,0,1) == '1') echo '</span>';
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo display_media($paper[$question]['o_media'][$tmp_part_id], $paper[$question]['o_media_width'][$tmp_part_id], $paper[$question]['o_media_height'][$tmp_part_id], '') . "\n";
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and substr($tmp_exclude,0,1) == '1') echo '<span style="color:red">'. $string['isexcluded'] . '</span>';
              }
              if ($tmp_display_feedback == '1') {
                if ($paper[$question]['feedback_wrong'][$tmp_part_id] != '') {
                  echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['feedback_wrong'][$tmp_part_id] . "</td></tr>\n";
                } elseif ($paper[$question]['feedback_right'][$tmp_part_id] != '') {
                  echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
                }
              }
            } else {
              if ($tmp_display_students_response == '1') {
                echo '<tr><td></td><td></td>';
              } else {
                echo '<tr><td></td><td></td>';
              }
              echo "<td valign=\"top\"><input type=\"checkbox\" name=\"q" . $question . "_" . $tmp_part_id . "\" value=\"y\" />&nbsp;</td><td valign=\"top\">";
              if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
              if (trim($paper[$question]['option_text'][$tmp_part_id]) != '') {
                echo $paper[$question]['option_text'][$tmp_part_id] . "<br />\n";
              }
              if (substr($tmp_exclude,0,1) == '1') echo '</span>';
              if ($paper[$question]['o_media'][$tmp_part_id] != '') {
                echo display_media($paper[$question]['o_media'][$tmp_part_id], $paper[$question]['o_media_width'][$tmp_part_id], $paper[$question]['o_media_height'][$tmp_part_id], '') . "\n";
                if (trim($paper[$question]['option_text'][$tmp_part_id]) == '' and substr($tmp_exclude,0,1) == '1') echo '<span style="color:red">'. $string['isexcluded'] . '</span>';
              }
              echo "</td></tr>\n";
              if ($paper[$question]['feedback_right'][$tmp_part_id] != '' and $tmp_display_feedback == '1') {
                echo "<tr><td></td><td></td><td></td><td class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
              }
            }
          }
        }
        echo "</table>\n";
        if (!$answered) echo "<br />\n<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";
        if ($paper[$question]['correct_fback'] != '' and $tmp_display_feedback == '1') {
          echo "<br /><div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $paper[$question]['correct_fback'] . "</div>\n";
        }
        break;
      case 'sct':
        $paper[$question]['mark'] = 0;
        echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td colspan=\"3\" style=\"background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string['clinicalvignette'] . "</td></tr>\n<tr><td colspan=\"2\">" . $paper[$question]['scenario'] . "</td></tr>\n";
        if ($paper[$question]['q_media'] != '') echo "<tr><td colspan=\"3\"><p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p></td></tr>\n";
    
        $sct_parts = explode('~',$paper[$question]['leadin']);
        echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
        $sct_titles = array(1=>$string['hypothesis'],2=>$string['investigation'],3=>$string['prescription'],4=>$string['intervention'],5=>$string['treatment']);
        $sct_intros = array(1=>$string['tdiagnosis'],2=>$string['tinvestigation'],3=>$string['tprescription'],4=>$string['tintervention'],5=>$string['ttreatment']);
        echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_titles[$paper[$question]['display_method']] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string['newinformation'] . "</td></tr>\n";
        echo "<tr><td style=\"width:49%;\">" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%;\">" . $sct_parts[1] . "</td></tr>\n";
        echo '</table>';
    
        echo "\n<br /><p><strong>" . $string['thenthis'] . " " . mb_strtolower($sct_titles[$paper[$question]['display_method']], 'UTF-8') . " " . $string['is'] . ":</strong></p>\n";

        $max = -1;
        $reviewers_total = 0;
        // Loop around to find the max number of experts
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          if ($paper[$question]['correct'][$part_id] > $max) {
            $max = $paper[$question]['correct'][$part_id];
            $correct_answer[$part_id] = $part_id;
          }
          $reviewers_total += $paper[$question]['correct'][$part_id];
        }
        // Loop round again to set correct answer(s)
        $correct_answer = array();
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          if ($max == $paper[$question]['correct'][$part_id]) {
            $correct_answer[$part_id] = $part_id;
          }
        }
        
        if (!isset($paper[$question]['user_answer']) OR $paper[$question]['user_answer'] == 0) {
          reset_feedback($hide_if_unanswered);
        }

        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";
        echo "<tr><td></td><td></td><td></td>";
        if ($tmp_display_correct_answer == 1) {
          echo "<td style=\"color:#808080\">" . $string['experts'] . "</td>";
        }
        echo "<td></td></tr>\n";
        for ($part_id=0; $part_id<$no_options; $part_id++) {
          $tmp_part_id = $option_order[$part_id];
          
          if (isset($correct_answer[$tmp_part_id])) {
            $strong_on = '';
            $strong_off = '';
            
            if (($tmp_display_correct_answer == '1' and $hide_if_unanswered == '0') or ($hide_if_unanswered == '1' and isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] != '0' and $tmp_display_correct_answer == '1')){
              $strong_on = '<strong>';
              $strong_off = '</strong>';
            } else {
              $strong_on = '';
              $strong_off = '';
            }
          } else {
            $strong_on = '';
            $strong_off = '';
          }
          
          if (isset($paper[$question]['user_answer']) and $tmp_part_id+1 == $paper[$question]['user_answer']) {
            if ($tmp_display_correct_answer == '1') {
              echo '<td>';
              if (isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
              echo '</td>';          
            } else {
              echo '<td></td>';          
            }
            if ($tmp_display_students_response == '1') {
              if ($paper[$question]['correct'][$tmp_part_id] == $max) {
                echo '<td><img src="../artwork/tick.gif" width="17" height="16" alt="Tick" /></td>';
                $paper[$question]['mark'] = 1;
              } elseif ($paper[$question]['correct'][$tmp_part_id] > 0) {
                echo '<td><img src="../artwork/tick_half.gif" width="17" height="16" alt="Half Right" /></td>';
                $paper[$question]['mark'] = $paper[$question]['correct'][$tmp_part_id] / $max;
              } else {
                echo '<td><img src="../artwork/cross.gif" width="17" height="16" alt="Cross" /></td>';
                $paper[$question]['mark'] = 0;
              }
            } else {
              echo '<td></td>';
            }
            echo "<td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" checked /></td>";
            if ($tmp_display_correct_answer == 1) {
              if ($paper[$question]['correct'][$tmp_part_id] > 0) {
                echo "<td style=\"text-align:right; color:#808080\">" . $paper[$question]['correct'][$tmp_part_id] . "&nbsp;" . $string['of'] . "&nbsp;$reviewers_total</td>";
              } else {
                echo "<td style=\"text-align:right; color:#808080\">-</td>";
              }
            }
            echo "<td style=\"width:99%; padding-left:5px\">";
            if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
            echo $strong_on . $paper[$question]['option_text'][$tmp_part_id] . $strong_off;
            if (substr($tmp_exclude,0,1) == '1') echo '</span>';
            echo '</td></tr>';
          } else {
            if ($tmp_display_correct_answer == '1') {
              echo '<td>';
              if(isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
              echo '</td>';          
            } else {
              echo '<td></td>';          
            }
            echo "<td></td><td><input type=\"radio\" name=\"q" . $question . "\" value=\"\" /></td>";
            if ($tmp_display_correct_answer == 1) {
              if ($paper[$question]['correct'][$tmp_part_id] > 0) {
                echo "<td style=\"text-align:right; color:#808080\">" . $paper[$question]['correct'][$tmp_part_id] . "&nbsp;" . $string['of'] . "&nbsp;$reviewers_total</td>";
              } else {
                echo "<td style=\"text-align:right; color:#808080\">-</td>";
              }
            }
            echo "<td style=\"width:99%; padding-left:5px\">";
            if (substr($tmp_exclude,0,1) == '1') echo '<span class="exclude">';
            echo $strong_on . $paper[$question]['option_text'][$tmp_part_id] . $strong_off;
            if (substr($tmp_exclude,0,1) == '1') echo '</span>';
            echo '</td></tr>';
          }
          
          if ($paper[$question]['feedback_right'][$tmp_part_id] != '' and $tmp_display_feedback == '1') {
            echo "<tr><td colspan=\"4\"></td><td class=\"fback\">" . $paper[$question]['feedback_right'][$tmp_part_id] . "</td></tr>\n";
          }
        }

        if (!isset($paper[$question]['user_answer']) OR $paper[$question]['user_answer'] == 0) echo "\n<tr><td></td><td></td><td style=\"color:#808080\" colspan=\"3\">" . $string['unanswered'] . "</td></tr>\n";
        if ($tmp_display_feedback == '1') {
          if ($paper[$question]['correct_fback'] != '') {
            echo "<tr><td class=\"fback\" colspan=\"5\">&nbsp;</td></tr>\n";
            echo "<tr><td class=\"fback\" colspan=\"5\">" . $paper[$question]['correct_fback'] . "</td></tr>\n";
          }
        }
        echo "</table>\n";
        
        break;
      case 'true_false':
        if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 'u') {
          reset_feedback($hide_if_unanswered);
        }

        $paper[$question]['mark'] = 0;
        $paper[$question]['totalpos'] = 0;

        if ($paper[$question]['scenario'] != '') echo_content($paper[$question]['scenario']);
        if ($paper[$question]['q_media'] != '') echo "<p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
        echo_content($paper[$question]['leadin']);

        $true_checked = '';
        $false_checked = '';
        
        if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 't') $true_checked = ' checked="checked"';
        if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 'f') $false_checked = ' checked="checked"';
        
        echo '<table cellpadding="2" cellspacing="0" border="0"><tr>';
        echo '<td>';
        if ($paper[$question]['display_method'] == 'dropdown') {
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == $paper[$question]['correct'][0]) {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
          } elseif (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] != 'u') {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
          }
          echo '</td><td>';
          if (substr($tmp_exclude,0,1) == '1') {
            echo "<select name=\"q" . $question_no . "\" class=\"exclude\" style=\"border:1px solid red\">\n";
          } else {
            echo "<select name=\"q" . $question_no . "\">\n";
          }
          if (!isset($paper[$question]['user_answer']) or $paper[$question]['user_answer'] == 'u') {
            echo "<option value=\"\" style=\"color:#808080\">unanswered</option>\n";
          }
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 't') {
            echo "<option value=\"t\" selected>True</option>\n";
          } else {
            echo "<option value=\"t\">True</option>\n";
          }
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 'f') {
            echo "<option value=\"f\" selected>False</option>\n";
          } else {
            echo "<option value=\"f\">False</option>\n";
          }
          echo "</select>\n";
          if ($tmp_display_correct_answer == '1') {
            if ($paper[$question]['correct'][0] == 't') {
              echo ' <strong>True</strong>';
            } else {
              echo ' <strong>False</strong>';
            }
          }
          echo '</td></tr>';
        } else {
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 't' and $paper[$question]['correct'][0] == 't') {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
          } elseif (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 't' and $paper[$question]['correct'][0] == 'f') {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
          }
          echo '</td>';
          echo "<td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"t\"$true_checked /></td>";
          if (substr($tmp_exclude,0,1) == '1') {
            echo '<td class="exclude">';
          } else {
            echo '<td>';
          }
          if ($paper[$question]['correct'][0] == 't') {
            echo $strong_on . 'True' . $strong_off;
          } else {
            echo 'True';
          }
          if ($paper[$question]['display_method'] == 'horizontal') {
            echo '&nbsp;&nbsp;&nbsp;';
          } elseif ($paper[$question]['display_method'] == 'vertical') {
            echo '</tr><tr>';
          }
          echo '<td>';
          if (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 'f' and $paper[$question]['correct'][0] == 'f') {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
          } elseif (isset($paper[$question]['user_answer']) and $paper[$question]['user_answer'] == 'f' and $paper[$question]['correct'][0] == 't') {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
          }
          echo '</td>';
          echo "<td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"f\"$false_checked /></td>";
          if (substr($tmp_exclude,0,1) == '1') {
            echo '<td class="exclude">';
          } else {
            echo '<td>';
          }
          if ($paper[$question]['correct'][0] == 'f') {
            echo $strong_on . 'False' . $strong_off;
          } else {
            echo 'False';
          }
          echo '</td></tr>';
          if (!isset($paper[$question]['user_answer']) or $paper[$question]['user_answer'] == 'u') {
            echo "<td style=\"color:#808080\" colspan=\"3\">" . $string['unanswered'] . "</td></tr>";
          }
        }
        echo '</table>';

        if (!isset($paper[$question]['user_answer']) or $paper[$question]['user_answer'] == 'u') {
          $paper[$question]['mark'] = 0;
        } elseif ($paper[$question]['correct'][0] == $paper[$question]['user_answer']) {
          $paper[$question]['mark'] = $paper[$question]['marks_correct'];
        } else {
          $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
        }
        $paper[$question]['totalpos'] = $paper[$question]['marks_correct'];

        if ($tmp_display_feedback == '1') {
          if ($paper[$question]['mark'] > 0) {
            if ($paper[$question]['correct_fback'] != '') {
              echo "<br /><div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $paper[$question]['correct_fback'] . "</div>\n";
            }
          } else {
            if ($paper[$question]['incorrect_fback'] != '') {
              echo "<br /><div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $paper[$question]['incorrect_fback'] . "</div>\n";
            } elseif ($paper[$question]['correct_fback'] != '') {
              echo "<br /><div class=\"fback\" style=\"margin-left:17px\">&nbsp;" . $paper[$question]['correct_fback'] . "</div>\n";
            }
          }
        }
        if (substr($tmp_exclude,0,1) == '1') {
          $paper[$question]['mark'] = 0;
          $paper[$question]['totalpos'] = 0;        
        }

        break;
      case 'rank':
        $paper[$question]['mark'] = 0;
        $paper[$question]['totalpos'] = 0;
        $all_correct = true;
        
        $correct_no = 0;
        for ($i=0; $i<count($paper[$question]['correct']); $i++) {
          if ($paper[$question]['correct'][$i] != 0) $correct_no++;
        }
        if (substr($tmp_exclude,0,1) == '0') {
          if ($paper[$question]['score_method'] == 'Mark per Option') {
            $paper[$question]['totalpos'] += count($paper[$question]['option_text']) * $paper[$question]['marks_correct'];
          } elseif ($paper[$question]['score_method'] == 'Mark per Question') {
            $paper[$question]['totalpos'] += $paper[$question]['marks_correct'];
          } elseif ($paper[$question]['score_method'] == 'Allow partial Marks') {
            $paper[$question]['totalpos'] += $correct_no * $paper[$question]['marks_correct'];
          } elseif ($paper[$question]['score_method'] == 'Bonus Mark' and $correct_no > 0) {
            $paper[$question]['totalpos'] += ($correct_no + 1) * $paper[$question]['marks_correct'];
          }
        }

        if ($paper[$question]['scenario'] != '') echo_content($paper[$question]['scenario']);
        if ($paper[$question]['q_media'] != '') echo "<p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
        echo_content($paper[$question]['leadin']);

        $na_count = 0;
        foreach ($paper[$question]['correct'] as $correct_option) {
          if ($correct_option == 0) $na_count++;
        }

        if (isset($paper[$question]['user_answer'])) {
          $rank_answers = explode(',',$paper[$question]['user_answer']);
        } else {
          $rank_answers = array_fill(0, 20, 'u');
        }
        
        // Check to see if the user has answered any options.
        $answered = false;
        for ($i=0; $i<count($rank_answers); $i++) {
          if ($rank_answers[$i] != 'u') {
            $answered = true;
          }
        }
        if ($answered  == false) {
          reset_feedback($hide_if_unanswered);
        }
        
        echo "<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";
        for ($i=1; $i<=$no_options; $i++) {
          $tmp_part_id = $option_order[$i-1]+1;
          echo '<tr>';
          if ($tmp_display_correct_answer == '1') {
            if (($paper[$question]['score_method'] == 'Bonus Mark' and $paper[$question]['correct'][$tmp_part_id-1] != 0) or $paper[$question]['score_method'] != 'Bonus Mark') {
              echo '<td>';
              if(isset($paper[$question]['std'][$tmp_part_id-1])) echo display_std($paper[$question]['std'][$tmp_part_id-1]);
              echo '</td>';
            } else {
              echo '<td></td>';
            }
          } else {
            echo '<td></td>';
          }

          echo '<td>';
          if (isset($rank_answers[$tmp_part_id-1]) and ($rank_answers[$tmp_part_id-1] != 'u' or $paper[$question]['score_method'] == 'Mark per Option')) {
            if ($tmp_display_students_response == '1' and substr($tmp_exclude,0,1) == '0') {
              if (($paper[$question]['score_method'] == 'Bonus Mark' or $paper[$question]['score_method'] == 'Allow partial Marks') and $rank_answers[$tmp_part_id-1] == '0' and $paper[$question]['correct'][$tmp_part_id-1] == '0') {
                echo '&nbsp;';
              } else {
                if ($paper[$question]['score_method'] == 'Allow partial Marks') {
                  if ($rank_answers[$tmp_part_id-1] > 0 and $rank_answers[$tmp_part_id-1] == $paper[$question]['correct'][$tmp_part_id-1]) {
                    echo '&nbsp;<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
                    $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                  } elseif ($rank_answers[$tmp_part_id-1] > 0 and $paper[$question]['correct'][$tmp_part_id-1] > 0 and ($rank_answers[$tmp_part_id-1]+1 == $paper[$question]['correct'][$tmp_part_id-1] or $rank_answers[$tmp_part_id-1]-1 == $paper[$question]['correct'][$tmp_part_id-1])) {
                    echo '&nbsp;<img src="../artwork/tick_half.gif" width="17" height="16" alt="Half Right" />';
                    $paper[$question]['mark'] += $paper[$question]['marks_partial'];
                  } else {
                    if ($rank_answers[$tmp_part_id-1] != 'u' and $rank_answers[$tmp_part_id-1] != '0') {
                      echo '&nbsp;<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
                      $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                    }
                    $all_correct = false;
                  }
                } else {
                  if ($rank_answers[$tmp_part_id-1] == $paper[$question]['correct'][$tmp_part_id-1] or ($paper[$question]['score_method'] == 'Bonus Mark' and $rank_answers[$tmp_part_id-1] > 0 and $paper[$question]['correct'][$tmp_part_id-1] > 0)) {
                    echo '&nbsp;<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
                    $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                    // Not all correct if Bonus Mark but not exact match
                    if ($rank_answers[$tmp_part_id-1] != $paper[$question]['correct'][$tmp_part_id-1]) $all_correct = false;
                  } else {
                    if (($rank_answers[$tmp_part_id-1] != 'u' and $rank_answers[$tmp_part_id-1] != '0') or ($paper[$question]['score_method'] == 'Mark per Option' and $rank_answers[$tmp_part_id-1] != 'u')) {
                      echo '&nbsp;<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
                      $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                      $all_correct = false;
                    }
                  }
                }
              }
            }
          } elseif ($paper[$question]['correct'][$tmp_part_id-1] > 0) {
            $all_correct = false;
            if ($paper[$question]['score_method'] == 'Mark per Question' and $rank_answers[$tmp_part_id-1] != 'u') {
              echo '&nbsp;<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
            }
          }
          echo "</td>";

          if (substr($tmp_exclude,0,1) == '1') {
            echo "<td><select name=\"q" . $question . "_" . $tmp_part_id . "\" class=\"exclude\" style=\"border:1px solid red\">\n";
          } else {
            echo "<td><select name=\"q" . $question . "_" . $tmp_part_id . "\">\n";
          }
          if (isset($rank_answers[$tmp_part_id-1]) and $rank_answers[$tmp_part_id-1] == 'u') echo "<option value=\"\" style=\"color:#808080\"></option>\n";
          for ($a=0; $a<=count($paper[$question]['correct']); $a++) {
            if (isset($rank_answers[$tmp_part_id-1]) and $rank_answers[$tmp_part_id-1] != 'u' and $a == $rank_answers[$tmp_part_id-1]) {
              echo "<option value=\"" . $a . "\" selected>" . StringUtils::ordinal_suffix($a, $language) . "</option>\n";
            } else {
              echo "<option value=\"" . $a . "\">" . StringUtils::ordinal_suffix($a, $language) . "</option>\n";;
            }
          }
          echo '</select></td>';
          if ($tmp_display_correct_answer == '1' and $paper[$question]['correct'][$tmp_part_id-1] > 0) {
            echo "<td style=\"font-weight:bold; vertical-align:top\">&nbsp;" . StringUtils::ordinal_suffix($paper[$question]['correct'][$tmp_part_id-1], $language) . "</td>";
          } else {
            echo "<td></td>";
          }
          if (substr($tmp_exclude,0,1) == '1') {
            echo "<td style=\"vertical-align:top\" class=\"exclude\">" . $paper[$question]['option_text'][$tmp_part_id-1] . "</td>";
          } else {
            echo "<td style=\"vertical-align:top\">" . $paper[$question]['option_text'][$tmp_part_id-1] . "</td>";
          }
          echo "</tr>\n";
        }
      
        if ($paper[$question]['score_method'] == 'Bonus Mark' and $tmp_display_students_response == '1') {
          $img_name = 'cross';
          if ($paper[$question]['mark'] == ($paper[$question]['totalpos'] - $paper[$question]['marks_correct']) and $all_correct) {
            $paper[$question]['mark'] += $paper[$question]['marks_correct'];
            $img_name = 'tick';
          }
            
          echo '<tr><td colspan="5">&nbsp;</td></tr>';
          
          $html = '<tr><td>';
          
          if (isset($paper[$question]['std'][$no_options])) {
            $html .= display_std($paper[$question]['std'][$no_options]);
          }
          $html .= '</td>';
          $html .= '<td>&nbsp;<img src="../artwork/' . $img_name . '.gif" width="17" height="16" alt="' . ucwords($img_name) . '" /></td>';
          $html .= '<td colspan="3">' . $string['overallcorrectorder'] . '</td>';
          $html .= '</tr>';
          
          echo $html;
        }
        
        if ($answered  == false) {
          echo "<tr><td colspan=\"3\" style=\"color:#808080\">" . $string['unanswered'] . "</td></tr>\n";
        }
        echo "</table>\n";
        if ($tmp_display_feedback == '1') {
          if (isset($paper[$question]['mark']) and $paper[$question]['mark'] != $paper[$question]['totalpos'] and $paper[$question]['incorrect_fback'] != '') {
            echo "<br /><div class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['incorrect_fback'] . "</div>\n";
          } else {
            if ($paper[$question]['correct_fback'] != '') {
              echo "<br /><div class=\"fback\" style=\"margin-left:17px\">" . $paper[$question]['correct_fback'] . "</div>\n";
            }
          }
        }
        
        if ($paper[$question]['score_method'] == 'Mark per Question') {
          if ($all_correct) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } elseif ($answered) {
            $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
          }
          $paper[$question]['totalpos'] = $paper[$question]['marks_correct'];
        }
        
        if (substr($tmp_exclude,0,1) == '1') $paper[$question]['mark'] = 0;
        
        break;
      case 'extmatch':
        $paper[$question]['mark'] = 0;
        $all_correct = true;
        $have_answer = false;

        echo_content($paper[$question]['leadin']);
        echo '<ol class="extmatch">';
        $matching_scenarios = array();
        $matching_scenarios = explode('|', $paper[$question]['scenario']);
        if (isset($paper[$question]['user_answer'])) {
          $user_answers = explode('|', $paper[$question]['user_answer']);
        } else {
          $user_answers = array('','','','','','','','','','','');
        }
        $correct_answers = explode('|', $paper[$question]['correct'][0]);
        $matching_media = explode('|', $paper[$question]['q_media']);
        $matching_media_width = explode('|', $paper[$question]['q_media_width']);
        $matching_media_height = explode('|', $paper[$question]['q_media_height']);
        $matching_media_correct_fback = explode('|', $paper[$question]['correct_fback']);
        $matching_media_incorrect_fback = explode('|', $paper[$question]['incorrect_fback']);
        
        $text_scenarios = 0;
        for ($part_id=0; $part_id<10; $part_id++) {
          if (isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id])) != '') $text_scenarios++;
        }
        $media_scenarios = 0;
        for ($part_id=1; $part_id<=10; $part_id++) {
          if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
        }
        $total_scenarios = max($text_scenarios, $media_scenarios);
        
        if ($matching_media[0] != '') {
          echo "<p align=\"center\">" . display_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '') . "</p>\n";
        }
        $i = 0;
        $section = 0;
        $std_part = 0;
        $option_answered = 0;
        for ($scenario_no=0; $scenario_no<$total_scenarios; $scenario_no++) {
          if(isset($matching_scenarios[$scenario_no])) {
            $single_scenario = $matching_scenarios[$scenario_no];
          } else {
            $single_scenario = '';
          }
          echo '<li>';
          if ($single_scenario != '') echo "$single_scenario<br />";
          if (isset($matching_media[$i+1]) and $matching_media[$i+1] != '') {
            echo "<p>" . display_media($matching_media[$i+1], $matching_media_width[$i+1], $matching_media_height[$i+1], '') . "</p>\n";
          }
          echo '<br />';
          $separate_answers = array();
          $separate_answers = explode('$', $correct_answers[$i]);
          $separate_user_answers = array();
          $separate_user_answers = explode('$', $user_answers[$i]);
          echo '<table cellpadding="0" cellspacing="1" border="0">';
          $tmp_option_no = 1;
          if (count($separate_answers) == 1) {   // Single answer Extended Matching
            if ($user_answers[$i] == 'u' or $user_answers[$i] == '') {
              reset_feedback($hide_if_unanswered);
            } else {
              init_feedback($strong_on, $strong_off);
            }
            $std_part++;
            if ($tmp_display_correct_answer == '1') {
              echo '<td>';
              if(isset($paper[$question]['std'][$std_part-1])) echo display_std($paper[$question]['std'][$std_part-1]);
              echo '</td>';
            } else {
              echo '<td></td>';
            }
            echo '<td>';
            if ($separate_user_answers[0] == $correct_answers[$i]) {
              if (substr($tmp_exclude,$section,1) == '0') {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
              }
              if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />&nbsp;';
            } else {
              if (substr($tmp_exclude,$section,1) == '0' and $user_answers[$i] != '' and $user_answers[$i] != 'u') {
                $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                $all_correct = false;
              }
              if ($tmp_display_students_response == '1' and $user_answers[$i] != '' and $user_answers[$i] != 'u') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />&nbsp;';
            }
            if (substr($tmp_exclude,$section,1) == '0') {
              echo "<select name=\"q" . $question. "_" . $std_part;
              if ($user_answers[$i] == 'u' or $user_answers[$i] == '') echo "\" style=\"color: #808080";
            } else {
              echo "<select name=\"q" . $question. "_" . $std_part . "\" class=\"exclude\" style=\"border:1px solid red";
              if ($user_answers[$i] == 'u' or $user_answers[$i] == '') echo ";color: #808080";
            }
            echo "\">\n";
            if ($user_answers[$i] == 'u' or $user_answers[$i] == '') {
              echo "<option value=\"\" style=\"color:#808080\">" . $string['unanswered'] . "</option>\n";
            } else {
              $option_answered = 1;
            }
            $tmp_option_no = 1;
            for ($option_no=0; $option_no < count($paper[$question]['option_text']); $option_no++) {
              if ($option_order[$option_no]+1 == $user_answers[$i]) {
                echo "<option value=\"\" selected=\"selected\">" . $paper[$question]['option_text'][$option_order[$option_no]] .  "</option>\n";
              } else {
                echo "<option value=\"\">" . $paper[$question]['option_text'][$option_order[$option_no]] .  "</option>\n";
              }
              $tmp_option_no++;
            }
            echo '</select>';
            if ($tmp_display_correct_answer == '1') {
              echo '&nbsp;&nbsp;<strong>' . $paper[$question]['option_text'][$correct_answers[$i]-1] . '</strong>';
            }
            echo "</td>\n";
          } else {                              // Multiple answer Extended Matching     
            for ($option_no=0; $option_no < count($paper[$question]['option_text']); $option_no++) {              
              $matching_option = 0;
              foreach ($separate_answers as $single_answer) {
                if ($single_answer == $option_order[$tmp_option_no-1]+1) {
                  $matching_option = 1;
                }
              }
      
              if ($matching_option == 1) {
                $std_part++;
                $matching_user_option = 0;
                foreach ($separate_user_answers as $single_user_answer) {
                  if ($single_user_answer != '' and $single_user_answer != 'u') $unanswered = false;
                  if ($single_user_answer == $option_order[$tmp_option_no-1]+1) {
                    $matching_user_option = 1;
                  }
                }
                $option_answered += $matching_user_option;
      
                echo '<tr>';
                if ($tmp_display_correct_answer == '1') {
                  echo '<td>'; 
                  if(isset($paper[$question]['std'][$std_part-1])) echo display_std($paper[$question]['std'][$std_part-1]);
                  echo '</td>';
                } else {
                  echo '<td></td>';
                }
                echo '<td>';
                if ($matching_user_option == 1 and substr($tmp_exclude,$section,1) == '0') {
                  $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                  if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
                } else {
                  $all_correct = false;
                  if ($tmp_display_students_response == '1') echo '<img src="../artwork/blank_tick_cross.gif" width="17" height="16" alt="" />';
                }
                echo '&nbsp;</td><td';
                if (substr($tmp_exclude,$section,1) == '1') echo ' class="exclude"';
                echo ">$strong_on" . $paper[$question]['option_text'][$option_order[$option_no]] . "$strong_off</td></tr>\n";
              } else {
                $matching_user_option = 0;
                foreach ($separate_user_answers as $single_user_answer) {
                  if ($single_user_answer == $option_order[$tmp_option_no-1]+1) {
                    $matching_user_option = 1;
                  }
                }
                $option_answered += $matching_user_option;
                echo '<tr><td></td><td>';
                if ($matching_user_option == 1 and $tmp_display_students_response == '1' and substr($tmp_exclude,$section,1) == '0') {
                  if ($tmp_display_students_response == '1') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
                  $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                  $all_correct = false;
                } else {
                  if ($tmp_display_students_response == '1') echo '<img src="../artwork/blank_tick_cross.gif" width="17" height="16" alt="" />';
                }
                echo '&nbsp;</td><td';
                if (substr($tmp_exclude,$section,1) == '1') echo ' class="exclude"';
                echo ">" . $paper[$question]['option_text'][$option_order[$option_no]] . "</td></tr>\n";
              }
              $tmp_option_no++;
            }
          }
        
          $section = $std_part;
          echo '</table>';
          echo '<br />';
          if (isset($matching_media_correct_fback[$i]) and $matching_media_correct_fback[$i] != '' and $tmp_display_feedback == '1') {
            echo '<div class="fback">' . $matching_media_correct_fback[$i] . '</div>';
          }            
          echo '</li>';
          $i++;
        }
        echo "</ol>\n";

        init_feedback($strong_on, $strong_off);
        if ($option_answered == 0) {
          reset_feedback($hide_if_unanswered);
          echo "<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";
          //$paper[$question]['mark'] = 0;
        } elseif ($paper[$question]['score_method'] == 'Mark per Question') {
          $paper[$question]['mark'] = ($all_correct) ? $paper[$question]['marks_correct'] :$paper[$question]['marks_incorrect']; 
        }
        break;
      case 'matrix':
        $paper[$question]['mark'] = 0;
        $matching_scenarios = explode('|', $paper[$question]['scenario']);
        $matching_media = explode('|', $paper[$question]['q_media']);
        $matching_media_width = explode('|', $paper[$question]['q_media_width']);
        $matching_media_height = explode('|', $paper[$question]['q_media_height']);
        $matching_media_correct_fback = explode('|', $paper[$question]['correct_fback']);
        $matching_media_incorrect_fback = explode('|', $paper[$question]['incorrect_fback']);
        if (isset($paper[$question]['user_answer'])) {
          $user_answers = explode('|', $paper[$question]['user_answer']);
        } else {
          $user_answers = array();
        }
        $correct_answers = explode('|', $paper[$question]['correct'][0]);
        echo_content($paper[$question]['leadin']);
        if ($matching_media[0] != '') {
          echo '<p align="center">' . display_media($matching_media[0], $matching_media_width[0], $matching_media_height[0], '') . '</p>';
        }

        echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
        if (!isset($paper[$question]['std'][0]) or display_std($paper[$question]['std'][0]) == '') {
          echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
        } else {
          echo "<tr>\n<td colspan=\"3\">&nbsp;</td>";
        }
        for ($i=0; $i<count($paper[$question]['option_text']); $i++) {
          echo '<td style="text-align:center">' . $paper[$question]['option_text'][$option_order[$i]] . '</td>';
        }
        echo "</tr>\n";
        $row_no = 0;
        $part_id = 0;
        $answered = 0;
        foreach ($matching_scenarios as $single_scenario) {
          if (trim($single_scenario) != '') {
            echo "<tr>\n";
            echo '<td align="right">' . chr(65 + $part_id) . '.</td>';
            if (isset($paper[$question]['std'][$row_no]) and display_std($paper[$question]['std'][$row_no]) != '') echo '<td>' . display_std($paper[$question]['std'][$row_no]) . '</td>';
            echo '<td';
            if (substr($tmp_exclude,$row_no,1) == '1') echo ' class="exclude"';
            echo '>' . $single_scenario . '</td>';
            $answer_no = 1;
            $col_no = 1;
            foreach ($paper[$question]['option_text'] as $single_option) {
              $tmp_col_no = $option_order[$col_no - 1] + 1;
              if ($correct_answers[$row_no] == $tmp_col_no and $tmp_display_correct_answer == '1' and (!$hide_if_unanswered or !empty($user_answers[$row_no]))) {
                echo '<td style="background-color:#C0FFC0">';
              } else {
                echo '<td>';
              }
              echo '<div align="center"><img src="../artwork/blank_tick_cross.gif" width="17" height="16" alt="" /><input type="radio" name="q' . $question . '_' . $row_no . '" value="' . $answer_no . '"';
              $tmp_answer = (isset($user_answers[$row_no])) ? $user_answers[$row_no] : 'u';
              if ($tmp_answer == $tmp_col_no) echo ' checked';
              echo ' />';
              if (isset($user_answers[$row_no]) and $user_answers[$row_no] != 'u' and $user_answers[$row_no] != '') {
                $answered++;
              }
              if ($correct_answers[$row_no] == $tmp_col_no and $tmp_answer == $tmp_col_no) {
                if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
                if (substr($tmp_exclude,$row_no,1) == '0') $paper[$question]['mark'] += $paper[$question]['marks_correct'];
              } elseif ($correct_answers[$row_no] != $tmp_col_no and $tmp_answer == $tmp_col_no) {
                if ($tmp_display_students_response == '1') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
                if (substr($tmp_exclude,$row_no,1) == '0') $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
              } else {
                if ($tmp_display_students_response == '1') echo '<img src="../artwork/blank_tick_cross.gif" width="17" height="16" alt="" />';       
              }
              echo '</div>';
              echo '</td>';
              $answer_no++;
              $col_no++;
            }
            echo "</tr>\n";
            $part_id++;
            $row_no++;
          }
        }    
        echo '</table>';

        if ($answered == 0) {
          reset_feedback($hide_if_unanswered);
          echo "<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";
        }

        if ($tmp_display_feedback == '1' and trim($paper[$question]['correct_fback']) != '') {
          echo "<p class=\"fback\">" . $paper[$question]['correct_fback'] . "</p>\n";
        }
        
        if ($paper[$question]['score_method'] == 'Mark per Question') {
          if ($paper[$question]['mark'] == $paper[$question]['marks_correct'] * $row_no) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } elseif (isset($paper[$question]['user_answer']) and (strlen($paper[$question]['user_answer']) + 1)  == $row_no) {
            $paper[$question]['mark'] = 0;
          } else {
            if ($answered > 0) {
              $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
            }
          }
        }
        break;
      case 'textbox':
        $textbox_size = explode('x', $paper[$question]['score_method']);
        if ($paper[$question]['scenario'] != '') {
          echo echo_content($paper[$question]['scenario']);
          if ($paper[$question]['q_media'] != '') {
            echo "<p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }
          echo '<p';
          if (substr($tmp_exclude,0,1) == '1') echo ' class="exclude"';
          echo '>' . $paper[$question]['leadin'] . "</p>\n";
        } else {
          if ($paper[$question]['q_media'] != '') {
            echo "<br /><p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }
          echo '<p class="leadin"';
          if (substr($tmp_exclude,0,1) == '1') echo ' class="exclude"';
          echo '>' . $paper[$question]['leadin'] . "</p>\n";
        }
        if (isset($paper[$question]['user_answer'])) {
          $tmp_answer = $paper[$question]['user_answer'];
        } else {
          $tmp_answer = '';
        }
        $correct_answers = explode(';', $paper[$question]['correct'][0]);
        if ($tmp_display_correct_answer == '1') {
          foreach ($correct_answers as $single_answer) {
            $tmp_answer = str_ireplace($single_answer, '<span style="background-color:#FFFF00">' . $single_answer . '</span>', $tmp_answer);
          }
        }
        echo "<div style=\"border:1px solid #164994; padding:12px; text-align:justify; line-height:150%\">" . $tmp_answer . "</div>\n<br />\n";

        if ($tmp_answer == '') {
          reset_feedback($hide_if_unanswered);
        }

        if ($tmp_display_feedback == '1' and $paper[$question]['correct_fback'] != '') {
          echo '<p class="fback" style="margin-left:17px">&nbsp;' . nl2br($paper[$question]['correct_fback']) . "</p>\n";
        }
        break;
      case 'likert':
        if ($paper[$question]['scenario'] != '') {
          echo_content($paper[$question]['scenario']);
          if ($paper[$question]['q_media'] != '') {
            echo "<p align=\"center\">" . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }
          echo_content($paper[$question]['leadin']);
        } else {
          if ($paper[$question]['q_media'] != '') {
            echo '<br /><p align="center">' . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }            
          echo_content($paper[$question]['leadin']);
        }
        break;
      case 'blank':
        $paper[$question]['mark'] = 0;
        $have_answer = false;
        
        if ($paper[$question]['scenario'] != '') {
          echo_content($paper[$question]['scenario']);
          if ($paper[$question]['q_media'] != '') {
            echo '<p align="center">' . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }
          echo_content($paper[$question]['leadin']);
        } else {
          if ($paper[$question]['q_media'] != '') {
            echo '<br /><p align="center">' . display_media($paper[$question]['q_media'], $paper[$question]['q_media_width'], $paper[$question]['q_media_height'], '') . "</p>\n";
          }
          echo_content($paper[$question]['leadin']);
        }

        if (isset($paper[$question]['user_answer'])) {
          $user_choices = explode('|',$paper[$question]['user_answer']);
        } else {
          $user_choices = array('u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u','u');
        }

        $blank_details = array();
        $blank_details = explode("[blank",$paper[$question]['option_text'][0]);
        $no_blanks = count($blank_details);
        echo '<p>' . $blank_details[0];
        $blank_count = 1;
        
        while ($blank_count < $no_blanks) {
          if (!isset($user_choices[$blank_count]) or $user_choices[$blank_count] == 'u') {
            reset_feedback($hide_if_unanswered);
          } else {
            init_feedback($strong_on, $strong_off);
          }

          $answer_options = array();
          $answer_options = explode("[/blank]",substr($blank_details[$blank_count],(strpos($blank_details[$blank_count],']') + 1)));
          $answer_list = array();
          $answer_list = explode(',',$answer_options[0]);
          $exclude_mod = (substr($tmp_exclude,$blank_count-1,1) == '1') ? ' class="exclude"' : '';
          if ($paper[$question]['display_method'] == 'textboxes') {             // Textboxes
            $correct_flag = false;
            foreach ($answer_list as $individual_answer) {
              if (trim(strtolower($individual_answer)) == trim(strtolower($user_choices[$blank_count]))) {
                $correct_flag = true;
              }
            }
            if ($correct_flag == true) {
              if (substr($tmp_exclude,$blank_count-1,1) == '0') {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
              }
              $have_answer = true;
              echo "<input type=\"text\" size=\"20\" name=\"q" . $question . "_" . $blank_count . "\" value=\"" . htmlentities($user_choices[$blank_count]) .  "\"$exclude_mod />";
              if ($tmp_display_students_response == '1') echo '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
            } else {
              if ($user_choices[$blank_count] == 'u') {
                  echo "<input type=\"text\" size=\"20\" name=\"q" . $question . "_" . $blank_count . "\" value=\"" . $string['unanswered'] .  "\" style=\"color: #808080\"$exclude_mod />";
              } else {
                if (substr($tmp_exclude,$blank_count-1,1) == '0') {
                  $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                }
                $have_answer = true;
                echo "<input type=\"text\" size=\"20\" name=\"q" . $question . "_" . $blank_count . "\" value=\"" . htmlentities($user_choices[$blank_count]) .  "\"$exclude_mod />";
                if ($tmp_display_students_response == '1') echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
              }
              if ($tmp_display_correct_answer == '1') {
                echo '<strong>(';
                $answer_string = '';
                foreach ($answer_list as $individual_answer) {
                  $answer_string .= $individual_answer . ', ';
                }
                echo rtrim($answer_string, ', ');
                echo ')</strong>';
              }
            }
          } else {                                                                // Dropdown menus
            //echo '</span>';
            if ($tmp_display_correct_answer == '1') {
              if (isset($paper[$question]['std'][$blank_count-1])) echo display_std($paper[$question]['std'][$blank_count-1],0);
            }
            $colormod = (!isset($user_choices[$blank_count]) or $user_choices[$blank_count] == 'u') ? " style=\"color: #808080\"" : '';
            echo "<select name=\"\"{$colormod}{$exclude_mod}>\n";
            echo "<option value=\"\"></option>";
            if (!isset($user_choices[$blank_count]) or $user_choices[$blank_count] == 'u') {
              echo "<option value=\"u\" selected=\"selected\">{$string['unanswered']}</option>\n";
            }
            foreach ($answer_list as $answer_option) {
              if (isset($user_choices[$blank_count]) and html_entity_decode(trim($answer_option)) == html_entity_decode(trim($user_choices[$blank_count]))) {
                echo "<option value=\"\" selected>$answer_option</option>\n";
              } else {
                echo "<option value=\"\">$answer_option</option>\n";
              }
            }
            echo "</select>\n";

            if (isset($user_choices[$blank_count]) and str_replace('&nbsp;',' ',html_entity_decode(trim($answer_list[0]))) == str_replace('&nbsp;',' ',html_entity_decode(trim($user_choices[$blank_count])))) {
              if (substr($tmp_exclude,$blank_count-1,1) == '0') {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                $have_answer = true;
                if ($tmp_display_students_response == '1') echo  '<img src="../artwork/tick.gif" width="17" height="16" alt="Tick" />';
              }
            } else {
              if (isset($user_choices[$blank_count]) and html_entity_decode(trim($user_choices[$blank_count])) != 'u') {
                if (substr($tmp_exclude,$blank_count-1,1) == '0') $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                $have_answer = true;
              }
              if ($tmp_display_students_response == '1'and substr($tmp_exclude,$blank_count-1,1) == '0' and isset($user_choices[$blank_count]) and html_entity_decode(trim($user_choices[$blank_count])) != 'u') {
                echo '<img src="../artwork/cross.gif" width="17" height="16" alt="Cross" />';
              } else {
                echo '<img src="../artwork/blank_tick_cross.gif" width="17" height="16" alt="" />';
              }
            }
            if ($tmp_display_correct_answer == '1') echo ' <strong>(' . $answer_list[0] . ')</strong>';
          }
          echo $answer_options[1]; // Bit after the closing [/blank] tag.
          $blank_count++;
        }
        echo "</p>\n";

        init_feedback($strong_on, $strong_off);
        if (!$have_answer) {
          reset_feedback($hide_if_unanswered);
        }

        if ($tmp_display_feedback == '1' and $paper[$question]['correct_fback'] != '') {
          echo '<div class="fback">&nbsp;' . $paper[$question]['correct_fback'] . "</div>\n";
        }
        
        if ($paper[$question]['score_method'] == 'Mark per Question' and $have_answer) {
          $paper[$question]['mark'] = ($paper[$question]['mark'] == ($no_blanks - 1) * $paper[$question]['marks_correct']) ? $paper[$question]['marks_correct'] :  $paper[$question]['marks_incorrect'];
        }
        break;
      case 'hotspot':
        $have_answer = false;
        if (!isset($paper[$question]['user_answer'])) {
          reset_feedback($hide_if_unanswered);
        } elseif ($paper[$question]['user_answer'] == 'u') {
          reset_feedback($hide_if_unanswered);
        }
        $paper[$question]['mark'] = 0;
        $paper[$question]['totalpos'] = 0;
        $all_correct = true;
        $parts = explode('|',$paper[$question]['correct'][0]);
        if (isset($paper[$question]['user_answer'])) {
          $ua_parts = explode('|',$paper[$question]['user_answer']);
        }
        for ($i = 0; $i < count($parts); $i++) {
          if (isset($paper[$question]['user_answer'])) {
            if (substr($tmp_exclude, $i, 1) == '0' and $paper[$question]['user_answer'] != 'u') {
              $have_answer = true;
              if (substr($ua_parts[$i], 0, 1) == 1) {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
              } else {
                $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                $all_correct = false;
              }
            }
          }
          if ($paper[$question]['score_method'] == 'Mark per Option' and substr($tmp_exclude, $i, 1) == '0') {
            $paper[$question]['totalpos'] += $paper[$question]['marks_correct'];
          }
        }

        if ($paper[$question]['score_method'] == 'Mark per Question' and substr($tmp_exclude, 0, 1) == '0') {
          if (!$have_answer) {
            $paper[$question]['mark'] = 0;
          } elseif ($all_correct) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } else {
            $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
          }
          $paper[$question]['totalpos'] = $paper[$question]['marks_correct'];
        }
        
        if ($paper[$question]['scenario'] != '') {
          echo_content($paper[$question]['scenario']);
        }
        
        $extra = $tmp_display_students_response . ',' . $tmp_display_correct_answer . ',' . $tmp_exclude;
        
        $tmp_correct = str_replace("'", "\'", trim($paper[$question]['correct'][0]));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct); 
?>
  <div>
  <script language="JavaScript">
    function swfLoaded<?php echo $question_no; ?>(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $paper[$question]['q_media']; ?>', '<?php echo $tmp_correct; ?>', '<?php if (isset($paper[$question]['user_answer'])) echo trim($paper[$question]['user_answer']); ?>', '<?php echo $extra; ?>');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 301); ?>" height="<?php echo ($paper[$question]['q_media_height'] + 30); ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/paper/hotspot_answer.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<embed src="<?php echo $cfg_root_path ?>/paper/hotspot_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 301); ?>" height="<?php echo ($paper[$question]['q_media_height'] + 30); ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>
  </div>
<?php

        if ($tmp_display_correct_answer == '1') {
          echo '<br />';
          if(isset($paper[$question]['std'][0])) echo display_std($paper[$question]['std'][0]);
          echo '<br />';
        }
        
        if (!isset($paper[$question]['user_answer'])){
          echo "<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";
        } elseif ($paper[$question]['user_answer'] == 'u') {
          echo "<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";         
        }
        
        if ($paper[$question]['correct_fback'] != '' and $tmp_display_feedback == '1') {
          echo '<div class="fback" style="margin-left:17px">&nbsp;' . $paper[$question]['correct_fback'] . "</div>\n";
        }
        break;
      case 'labelling':
        $correct_labels = array();
      
        $tmp_first_split = explode(';', $paper[$question]['correct'][0]);
        $tmp_second_split = explode('$', $tmp_first_split[11]);
        $label_count = 0;
        $placeholders = 0;
        $i = 0;
        $excluded_no = 0;
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
            if (substr($tmp_exclude,$i,1) == '0') {
              $x = $tmp_second_split[$label_no-2];
              $y = $tmp_second_split[$label_no-1] - 25;
              $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no], 0, strpos($tmp_second_split[$label_no], '|'));
              $tmp_pos = strpos($correct_labels[$x . 'x' . $y], '~');
              if ($tmp_pos !== false) {
                $correct_labels[$x . 'x' . $y] = substr($correct_labels[$x . 'x' . $y], 0, $tmp_pos);
              }
              $placeholders++;
            } else {
              $excluded_no++;
            }
            $i++;
          }
        }
        
        $paper[$question]['mark'] = 0;
        $all_correct = true;
        $correct_no = 0;
        
        $tmp_labels = 0;
        $max_col1 = 0;
        $max_col2 = 0;
        $tmp_first_split = explode(';', $paper[$question]['correct'][0]);
        $tmp_second_split = explode('|', $tmp_first_split[11]);
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            $tmp_labels++;
            if ($label_parts[0] < 10) {
              $max_col1 = $label_parts[0];
            } else {
              $max_col2 = $label_parts[0];
            }
          }
        }
        $max_col2-=10;
        $max_label = max($max_col1,$max_col2);

        $tmp_height = $paper[$question]['q_media_height'];
        if ($tmp_height < ($max_label * 55)) $tmp_height = ($max_label * 55);

        $answered = false;
        if (isset($paper[$question]['user_answer'])) {
          $user_split1 = explode(';',$paper[$question]['user_answer']);
          if ($user_split1[1] != '') {
            $answered = true;
            $user_split2 = explode('$',$user_split1[1]);

            $i = 0;
            for ($a=0; $a<count($user_split2)-3; $a+=4) {
              $x = $user_split2[$a];
              $y = $user_split2[$a+1];
              if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
                $paper[$question]['mark'] += $paper[$question]['marks_correct'];
                $correct_no++;
              } else {
                $paper[$question]['mark'] += $paper[$question]['marks_incorrect'];
                $all_correct = false;
              }
              $i++;
            }
          }
        }

        if ($paper[$question]['score_method'] == 'Mark per Question') {
          if (substr($tmp_exclude, 0, 1) == 1) $paper[$question]['leadin'] = '<div class="exclude">' . $paper[$question]['leadin'] . '</div>';
          if ($correct_no == count($correct_labels)) {
            $paper[$question]['mark'] = $paper[$question]['marks_correct'];
          } elseif ($correct_no == 0 and $all_correct == true) {
            $paper[$question]['mark'] = 0;
          } else {
            $paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
          }
        }
      
        if ($paper[$question]['scenario'] != '') {
          echo_content($paper[$question]['scenario']);
        }
        echo_content($paper[$question]['leadin']);

        if ($answered == 0) {
          reset_feedback($hide_if_unanswered);
        }

        echo '<div align="center">';
        echo '<script language="JavaScript">';
        $extra = ',' . $tmp_exclude;

    
        if ($tmp_display_correct_answer == '0' or $tmp_display_students_response == '0') {
          if (!empty($paper[$question]['user_answer'])) {
            $paper[$question]['user_answer'] = str_replace('"','&#034;',$paper[$question]['user_answer']);
            $paper[$question]['user_answer'] = str_replace("'",'&#039;',$paper[$question]['user_answer']);
          }
          $paper[$question]['correct'][0] = str_replace('"','&#034;',$paper[$question]['correct'][0]);
          $paper[$question]['correct'][0] = str_replace("'",'&#039;',$paper[$question]['correct'][0]);
?> 
    function swfLoaded<?php echo $question_no; ?>(message) {
      var num = message.substring(5,message.length);
      setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $paper[$question]['q_media']; ?>', '<?php echo trim($paper[$question]['correct'][0]); ?>', '<?php if (isset($paper[$question]['user_answer'])) echo trim($paper[$question]['user_answer']); ?>','<?php echo $extra; ?>');
    }
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="always" />');
    write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/paper/label_question.swf" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<embed src="<?php echo $cfg_root_path ?>/paper/label_question.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
<?php
} else {
  $tmp_std = '';
  if (is_array($paper[$question]['std'])) {
    foreach ($paper[$question]['std'] as $tmp_std_part) {
      if ($tmp_std == '') {
        $tmp_std = $tmp_std_part;
      } else {
        $tmp_std .= '$' . $tmp_std_part;
      }
    }
  }

  if (isset($paper[$question]['user_answer'])) {
    $paper[$question]['user_answer'] = str_replace('"','&#034;',$paper[$question]['user_answer']);
    $paper[$question]['user_answer'] = str_replace("'",'&#039;',$paper[$question]['user_answer']);
  }
  $paper[$question]['correct'][0] = str_replace('"','&#034;',$paper[$question]['correct'][0]);
  $paper[$question]['correct'][0] = str_replace("'",'&#039;',$paper[$question]['correct'][0]);

$extra = $tmp_std . ',' . $tmp_exclude;
?> 
  function swfLoaded<?php echo $question_no; ?>(message) {
    var num = message.substring(5,message.length);
    setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $paper[$question]['q_media']; ?>', '<?php echo trim($paper[$question]['correct'][0]); ?>', '<?php if (isset($paper[$question]['user_answer'])) echo trim($paper[$question]['user_answer']); ?>','<?php echo $extra ; ?>');
  }
  write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question_no; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
  write_string('<param name="allowScriptAccess" value="always" />');
  write_string('<param name="movie" value="<?php echo $cfg_root_path ?>/paper/label_answer.swf" />');
  write_string('<param name="quality" value="high" />');
  write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
  write_string('<embed src="<?php echo $cfg_root_path ?>/paper/label_answer.swf" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo ($paper[$question]['q_media_width'] + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $question_no; ?>" name="flash<?php echo $question_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
  write_string('</object>');
<?php
}
echo '</script>';
  ?>
  <input type="hidden" name="q<?php echo $question_no; ?>" id="q<?php echo $question_no; ?>" value="" />
  </div>
  <br />
<?php
        if ($answered == 0) {
          echo "<div style=\"color:#808080\">" . $string['unanswered'] . "</div>\n";
        } else {
          echo '<div align="center" style="color:#808080">(Move the mouse over incorrect labels to reveal the correct answer)</div>';
        }

        if ($paper[$question]['correct_fback'] != '' and $tmp_display_feedback == '1') {
          echo '<div class="fback" style="margin-left:17px">&nbsp;' . $paper[$question]['correct_fback'] . "</div>\n";
        }
        break;
      case 'flash':
        if ($paper[$question]['scenario'] != '') {
          echo_content($paper[$question]['scenario']);
        }
        echo_content($paper[$question]['leadin']);

        $movie = $paper[$question]['o_media'][0];
        if (!isset($paper[$question]['user_answer'])) {
          reset_feedback($hide_if_unanswered);
          if ($tmp_display_correct_answer == '0') {
            $movie = $paper[$question]['q_media'];
          }
        }
?>
  <div align="center">
  <script language="JavaScript">
    write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $question; ?>" width="<?php echo $paper[$question]['o_media_width'][0]; ?>" height="<?php echo $paper[$question]['o_media_height'][0]; ?>" align="middle">');
    write_string('<param name="allowScriptAccess" value="sameDomain" />');
    write_string('<param name="movie" value="/media/<?php echo $paper[$question]['o_media'][0]; ?>" />');
    write_string('<param name="quality" value="high" />');
    write_string('<param name="bgcolor" value="<?php echo $bgcolor; ?>" />');
    write_string('<param name="FlashVars" value="dataIn=<?php if (isset($paper[$question]['user_answer'])) echo $paper[$question]['user_answer']; ?>" />');
    write_string('<embed src="/media/<?php echo $movie ?>" FlashVars="dataIn=<?php if (isset($paper[$question]['user_answer'])) echo $paper[$question]['user_answer']; ?>" quality="high" bgcolor="<?php echo $bgcolor; ?>" width="<?php echo $paper[$question]['o_media_width'][0]; ?>" height="<?php echo $paper[$question]['o_media_height'][0]; ?>" swLiveConnect=true id="flash<?php echo $question; ?>" name="flash<?php echo $question; ?>" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
    write_string('</object>');
  </script>
  </div>
<?php
        break;
      case 'random_failed':
        echo '<p>' . $paper[$question]['leadin'] . '</p>';
        $paper[$question]['totalpos'] = 0;
        break;

    }
    
    // Display any objectives mapped
    if ($paper[$question]['q_type'] != 'info' and $paper[$question]['q_type'] != 'likert') {
      $objByModule = getObjectivesByMapping($moduleID, $calendar_year, $paperID, $paper[$question]['q_id'], $mysqlidb);
      
      if (count($objByModule) > 0) {
        echo "<br />\n<div class=\"objH\">Learning Objectives</div>\n<ul>\n";
        $module_list = explode(',',$moduleID);
        foreach ($module_list as $thisModuleid) {
          if (isset($objByModule[$thisModuleid])) {
            foreach ($objByModule[$thisModuleid] as $id => $mappingData) {
              echo "<li>" . $mappingData['content'];
              if ($mappingData['session']['source_url'] != '') echo "&nbsp;&nbsp;<a target=\"_blank\" href=\"" . $mappingData['session']['source_url'] . "\"><img src=\"../artwork/small_link.png\" width=\"12\" height=\"12\" border=\"0\" /></a>&nbsp;<a href=\"" . $mappingData['session']['source_url'] . "\" target=\"_blank\">" . $mappingData['session']['title'] . "</a>";
              echo "</li>\n";
            }
          }
        }
        echo "</ul>\n";
      }
    }
    
    // Write out the marks for the question
    if ($tmp_display_question_mark == '1' and $paper[$question]['q_type'] != 'info' and $paper[$question]['q_type'] != 'likert') {
      if (isset($paper[$question]['mark']) and is_numeric($paper[$question]['mark'])) {
        if ($paper[$question]['status'] == 'Experimental') {
          echo '<p class="mkpad"><span style="color:#800000; background-color:#FFC0C0; padding-left:8px; padding-right:8px">&nbsp;0 ' . $string['outof'] . ' 0 - Experimental Question&nbsp;</span></p>';
        } elseif ($paper[$question]['totalpos'] == 0) {
          $paper[$question]['mark'] = 0; 
          echo '<p class="mkpad"><span style="color:#800000; background-color:#FFC0C0; padding-left:8px; padding-right:8px">&nbsp;0 ' . $string['outof'] . ' 0&nbsp;</span></p>';
        } else {
          echo '<p class="mkpad"><span class="usermk">' . round($paper[$question]['mark'],2) . ' ' . $string['outof'] . ' ' . $paper[$question]['totalpos'] . '</span></p>';
        }
      } elseif ($paper[$question]['q_type'] == 'textbox') {
        // Unmarked textbox questions.
        echo '<p class="mkpad"><span class="usermk">&lt;'. $string['unmarked'] .'&gt; ' . $string['outof'] . ' ' . $paper[$question]['totalpos'] . '</span></p>';
      } else {
        // User has skipped over question.
        echo '<p class="mkpad"><span class="usermk">0 ' . $string['outof'] . ' ' . $paper[$question]['totalpos'] . '</span></p>';
      }
    }
    if ($paper[$question]['status'] != 'Experimental') {
      $total_marks += $paper[$question]['totalpos'];
      if (isset($paper[$question]['mark'])) $user_mark += round($paper[$question]['mark'], 2);
    }
    if ($paper[$question]['q_type'] != 'info') echo '</td></tr>';
    echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    $old_screen = $paper[$question]['screen'];
  }
  echo "</table>\n";

  // Marks summary
  if ($total_marks > 0 and $paper_type != 3) {
    echo '<br /><div align="center"><table cellpadding="4" cellspacing="0" border="0" class="box">';
    echo '<tr><td><table cellpadding="2" cellspacing="0" border="0" style="text-align:left">';
    echo '<tr><td colspan="2" style="margin:0px; font-weight:bold; font-size:120%">' . $string['summaryofmarks'] . '</td></tr>';
    if ($marking == 1) {
      echo "<tr><td style=\"width:210px\">" . $string['yourmark'] . "</td><td style=\"text-align:right\">" . $user_mark . " " . $string['outof'] . " $total_marks</td><td></td></tr>\n";
      echo "<tr><td>" . $string['randommark'] . "</td><td style=\"text-align:right\">" . number_format($total_random_mark, 2, '.', ',') . "</td><td><img onclick=\"launchHelp(13);\" src=\"../artwork/small_help_icon.gif\" style=\"cursor:pointer\" width=\"16\" height=\"16\" alt=\"help\" border=\"0\" /></td></tr>\n";
      echo "<tr><td>" . $string['passmark'] . "</td><td style=\"text-align:right\">$pass_mark%</td><td></td></tr>\n";
      echo "<tr><td>" . $string['yourpercentage'] . "</td><td style=\"text-align:right\">";
      if (isset($_GET['percent'])) {
        echo $_GET['percent'];
      } else {
        if ( ($total_marks-$total_random_mark) > 0 and (($user_mark-$total_random_mark)/($total_marks-$total_random_mark))*100 > 0) {
          echo number_format((($user_mark-$total_random_mark)/($total_marks-$total_random_mark))*100, 1, '.', ',');
        } else {
          echo '0';
        }
      }
      echo '%</td><td>' . $string['adjusted'] . '</td></tr>';
    } else {
      echo "<tr><td style=\"width:210px\">" . $string['yourmark'] . "</td><td style=\"text-align:right\">" . round($user_mark,2) . " " . $string['outof'] . " $total_marks</td></tr>\n";
      echo "<tr><td>" . $string['passmark'] . "</td><td style=\"text-align:right\">$pass_mark%</td></tr>\n";
      echo "<tr><td>" . $string['yourpercentage'] . "</td><td style=\"text-align:right\">";
      if (isset($_GET['percent'])) {
        echo $_GET['percent'];
      } else {
        echo number_format(($user_mark/$total_marks)*100, 1, '.', ',');
      }
      echo "%</td></tr>\n";
    }
    echo '</table></td></tr>';
    echo '</table></div>';
  }
  if ($paper_postscript != '') echo "<br />\n<blockquote>$paper_postscript</blockquote>\n";
  echo $bottom_html;
  if ($_SERVER['PHP_SELF'] == '/paper/finish.php') {
    echo '<tr><td align="center"><input type="button" name="close" value="&nbsp;' . $string['closewindow'] . '&nbsp;" onclick="window.close();" /></td></tr>';
  } else {
    echo '<tr><td>&nbsp;</td></tr>';
  }
  echo '</table>';
}
?>
