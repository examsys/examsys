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

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  require '../include/media.inc';
  require '../include/mapping.inc';

  // Check to see if that paper name has already been taken.
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE paper_title=?");
  $result->bind_param('s', $_POST['new_paper']);
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_title);
  if ($result->num_rows > 0) {
    ?>
    <table border="0" width="100%" height="100%">
    <tr><td valign="middle">
    <div align="center">

    <table border="0" cellpadding="4" cellspacing="1" style="background-color:#FF0000">
    <tr>
    <td valign="middle" style="background-color: white"><img src="../artwork/access_denied.png" width="48" height="48" alt="Warning" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:150%; font-weight:bold; color:#C00000">Title Warning</span></td>
    </tr>
    <tr>
    <td style="background-color:#FFC0C0">
    <p style="font-family:Arial,sans-serif; font-size:90%">The name '<strong><?php echo $_POST['new_paper']; ?></strong>' has already been used.<br />Please select a different paper name which is unique.</p>

    <div align="center"><input style="width:120px" type="button" value="&lt; Back" name="back" onclick="javascript: window.history.go(-1);"></div>
    </td>
    </tr>
    </table>

    </div>
    </td></tr>
    </table>
    </body>
    </html>
    <?php
    exit;
  }
  $result->free_result();
  $result->close();
  
  function checkSession($session) {
    $updated_session = $session;
    if (preg_match( '/\d\d\d\d.\d\d\d\d/' , $_POST['new_paper'], $matches) == 1) {
      $updated_session = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
    } elseif (preg_match( '/\d\d\d\d.\d\d/' , $_POST['new_paper'], $matches) == 1) {
      $updated_session = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
    } elseif (preg_match( '/\d\d.\d\d/' , $_POST['new_paper'], $matches) == 1) {
      $updated_session = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
    }

    return $updated_session;
  }
  
  $calendar_year = $new_calendar_year = '';
  $moduleID = NULL;
  $error = array();
  if ($_POST['copytype'] == 'paperonly') {        // Copy the paper only!
    // Copy the properties (properties table)
    $new_paper_id = copyProperties($userID, $mysqli, $calendar_year, $new_calendar_year, $moduleID);

    // Copy the question pointers (papers table)
    $result = $mysqli->prepare("SELECT question, screen, display_pos FROM papers WHERE paper=?");
    $result->bind_param('i', $_POST['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($question, $screen, $display_pos);
    $qids = array();
    while ($row = $result->fetch()) {
      $qids[] = $question;
      $addPaper = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
      $addPaper->bind_param('iiii', $new_paper_id, $question, $screen, $display_pos);
      $addPaper->execute();
      $addPaper->close();
    }
    $result->close();

    //if we are copying in the same session we can copy the objctives
    if ($new_calendar_year == $calendar_year) {
      $qids = implode(',',$qids);
      $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, module_id, $new_paper_id as paper_id, question_id, obj_id, calendar_year FROM relationships WHERE question_id IN ($qids) AND paper_id = ?)"); 
      $result->bind_param('i', $_POST['paperID']);
      $result->execute();
      $result->close();
    }
  } else {    // Copy the paper and the questions.
    // Copy the properties (properties table)
    $new_paper_id = copyProperties($userID, $mysqli, $calendar_year, $new_calendar_year, $moduleID);
  	
    // Copy the question and option data (questions and options tables)
    $result = $mysqli->prepare("SELECT question, screen, display_pos FROM papers WHERE paper=? ORDER BY display_pos");
    $result->bind_param('i', $_POST['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($question, $screen, $display_pos);
    $old_qids = array();
    $new_qids = array();
    $q_no = 0;   
    while ($result->fetch()) {
      $line = 0;
      $qData = $mysqli->prepare("SELECT * FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
      $qData->bind_param('i', $question);
      $qData->execute();
      $qData->store_result();
      $qData->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $owner, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $q_group, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks);
      while ($qData->fetch()) {
        $old_qids[$question] = $question; 
        // Question data
        if ($line == 0) {
          if ($q_type != 'info') $q_no++;
          if (trim($q_media) != '') {
            $media_array = array();
            $media_array = explode('|',$q_media);
            $new_q_media = '';
            $image_part = 0;
            foreach ($media_array as $individual_media) {
              if ($line == 0) {
                $new_media_name = '';
                if (trim($individual_media) != '' and trim($individual_media) != 'NULL') {
                	$new_media_name = unique_filename($individual_media,FALSE);
                  if (!copy("../media/$individual_media","../media/$new_media_name")) {
                    $error[] = "Question Number $q_no) Copy Error (Question) File <strong>'$individual_media'</strong> could not be copied.";
                    //if the image is missing dont put the file name in the new question
                    $new_media_name = '';
                  }
                }
                if ($image_part == 0) {
                  $new_q_media = $new_media_name;
                } else {
                  $new_q_media .= '|' . $new_media_name;
                }
              }
              $image_part++;
            }
          } else {
            $new_q_media = '';
          }
        }

        // Option data
        if (trim($o_media) != '') {
          $media_array = array();
          $media_array = explode('|',$o_media);
          $new_o_media = '';
          foreach ($media_array as $individual_media) {
            if (trim($individual_media) != '' and trim($individual_media) != 'NULL') {
							$new_media_name = unique_filename($individual_media,FALSE);
              if (!copy("../media/$individual_media","../media/$new_media_name")) {
                $error[] = "Question Number $q_no) Copy Error (Options) File <strong>'$individual_media'</strong> could not be copied.";
                //if the image is missing dont put the file name in the new question
                $new_media_name = '';
              }
              if ($new_o_media == '') {
                $new_o_media = $new_media_name;
              } else {
                $new_o_media .= '|' . $new_media_name;
              }
            }
          }
        } else {
          $new_o_media = '';
        }
        if ($marks == '') $marks = 1;
        if ($line == 0) {  // First record - write out the question, all the rest are options.
        	$bloom = (empty($bloom)) ? NULL : $bloom;
          $addQuestion = $mysqli->prepare("INSERT INTO questions VALUES(NULL,?,?,?,?,?,?,?,?," . $userID . ",?,?,?,NOW(),NOW(),?,'',?,?,NULL,NULL,NULL,NULL,?,'Normal',?)");
          $addQuestion->bind_param('ssssssssssssssss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $new_q_media, $q_media_width, $q_media_height, $bloom, $scenario_plain, $leadin_plain, $std, $q_option_order);
          $addQuestion->execute();
          $new_qids[] = $question_id = $mysqli->insert_id;
          $addQuestion->close();

          // Add in a record to the papers table.
          $addNewPaper = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
          $addNewPaper->bind_param('iiii', $new_paper_id, $question_id, $screen, $display_pos);
          $addNewPaper->execute();
          $addNewPaper->close();

          // Create a track changes record to say where question was copied from.
          $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Copied Question',?," . $userID . ",?,?,NOW(),'Copied Question')");
          $trackChange->bind_param('iss', $question_id, $question, $question_id);
          $trackChange->execute();
          $trackChange->close();

          // Create a track changes record to say new question added to paper.
          $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Alter Paper',?," . $userID . ",'',?,NOW(),'Add Question')");
          $trackChange->bind_param('is', $new_paper_id, $question_id);
          $trackChange->execute();
          $trackChange->close();
          
          // Lookup and copy the keywords
          $keyword_result = $mysqli->prepare("SELECT keywordID FROM keywords_question WHERE q_id=?");
          $keyword_result->bind_param('i', $question);
          $keyword_result->execute();
          $keyword_result->store_result();
          $keyword_result->bind_result($keywordID);
          while ($keyword_result->fetch()){
            $addKeyword = $mysqli->prepare("INSERT INTO keywords_question VALUES (?,?)");
            $addKeyword->bind_param('ii', $question_id, $keywordID);
            $addKeyword->execute();
            $addKeyword->close();
          }
          $keyword_result->close();
        }
      
        $addOption = $mysqli->prepare("INSERT INTO options VALUES(?,?,?,?,?,?,?,?,NULL,?)");
        $addOption->bind_param('isssssssi', $question_id, $option_text, $new_o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks);
        $addOption->execute();
        $addOption->close();
        $line++;
      }
      $qData->free_result();
      $qData->close();
    }
    $result->free_result();
    $result->close();

    //if we are copying in the same session we can copy the objctives
    if($new_calendar_year == $calendar_year) {
      $i = 0;
      foreach ($old_qids as $old_id) {
        $new_question_id = $new_qids[$i];
        $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, module_id, '$new_paper_id', '$new_question_id', obj_id, calendar_year FROM relationships WHERE question_id = $old_id AND paper_id = ?)"); 
        $result->bind_param('i', $_POST['paperID']);
        $result->execute();
        $result->close();
        $i++;
      }
    } else {
      //we are copying between sessions we need to check for changed sessions/objectives
      $old_course = getObjectives($moduleID,$calendar_year,$_POST['paperID'],'',$mysqli);
      $new_course = getObjectives($moduleID,$new_calendar_year,$_POST['paperID'],'',$mysqli);
      foreach ($old_course as $module=>&$sessions) {
        foreach ($sessions as $identifier=>&$session) {
          foreach ($session['objectives'] as &$obj) {
            $old_objID = $obj['id'];
              foreach($new_course[$module][$identifier]['objectives'] as $new_obj) {
              if ($new_obj['id'] == $old_objID AND $new_obj['content'] == $obj['content']) {
                //build a list of objectives that are still in both sessions
                $mappings_copy_objID[$old_objID] = $old_objID;
                break;
              }
            }
          }
        }
      }
      $mappings_copy_objID = implode(',',$mappings_copy_objID);
      
      //copy the objectives for each session where the objective still exists
      $i = 0;
      foreach ($old_qids as $old_id) {
        $new_question_id = $new_qids[$i];
        $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, module_id, '$new_paper_id', '$new_question_id', obj_id, '$new_calendar_year' FROM relationships WHERE question_id = $old_id AND paper_id = ? AND obj_id IN ($mappings_copy_objID))"); 
        $result->bind_param('i', $_POST['paperID']);
        $result->execute();
        $result->close();
        $i++;
      }
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Copy Paper<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../submenu.css" />
</head>
<?php
  if (count($error) == 0) {
  	echo "<body onload=\"javascript:window.location='" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/paper/details.php?paperID=$new_paper_id&module=" . $_POST['module'] . "&folder=" . $_POST['folder'] . "';\">";
  } else {
?>
  <body onclick="hideMenus()">
  <div id="content" class="content">
  <br />
  <br />
  <br />
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr><td valign="middle">
    <div align="center">

    <table border="0" cellpadding="4" cellspacing="1" style="background-color:#C0C0C0; text-align:left">
    <tr>
    <td valign="middle" style="background-color:white"><img src="/touchstone/artwork/orange_alert_32.png" width="32" height="32" alt="Critical" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:150%; font-weight:bold; color:#C00000">File Copy Warning</span></td>
   </tr>
   <tr>
   <td style="background-color:#EAEAEA"><ul>
   <?php
    echo "<li style=\"font-family:Arial,sans-serif; font-size:90%\">Your paper and questions have been copied but the following image have not been copied.</li>\n";
    foreach ($error as $msg) {
      echo "<li style=\"font-family:Arial,sans-serif; font-size:90%\">$msg</li>\n";
    }
   ?>
    </ul>
    <div style="text-align:center"><input type="button" name="OK" value="OK" onclick="javascript:window.location='<?php echo $protocol . $_SERVER['HTTP_HOST'] . '/touchstone/paper/details.php?paperID=' . $new_paper_id . '&module=' . $_POST['module'] . '&folder=' . $_POST['folder']; ?>'" style="width:100px" /></div>
    <br />
    </td>
    </tr>
    </table>
    </div>
    </td></tr>
    </table>
  </div>
<?php
  }
  $mysqli->close();
  
  function copyProperties($userID, $mysqli, &$calendar_year, &$new_calendar_year, &$moduleID)
  {
    $result = $mysqli->prepare("SELECT * FROM properties WHERE property_id=?");
    $result->bind_param('i', $_POST['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($property_id, $paper_title, $start_date, $end_date, $timezone, $paper_type, $paper_prologue, $paper_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $paper_owner, $folder, $labs, $rubric, $calculator, $externals, $exam_duration, $deleted, $created, $random_mark, $total_mark, $display_correct_answer, $display_question_mark, $display_students_response, $display_feedback, $hide_if_unanswered, $moduleID, $calendar_year, $internal_reviewers, $external_review_deadline, $internal_review_deadline, $sound_demo, $latex_needed, $password);
    while ($row = $result->fetch()) {
			$tmp_exam_duration = $exam_duration;
    	
      if ($paper_type == 2) {
        $tmp_start_date = '20200505090000';
        $tmp_end_date = '20200505100000';
      } else {
        $tmp_start_date = $start_date;
        $tmp_end_date = $end_date;
      }
      $tmp_random_mark = $random_mark;
      if ($tmp_random_mark == '') $tmp_random_mark = NULL;
      $tmp_total_mark = $total_mark;
      if ($tmp_total_mark == '') $tmp_total_mark = NULL;

      $tmp_external_review_deadline = $external_review_deadline;
      if ($tmp_external_review_deadline == '') $tmp_external_review_deadline = NULL;

      $tmp_internal_review_deadline = $internal_review_deadline;
      if ($tmp_internal_review_deadline == '') $tmp_internal_review_deadline = NULL;

      $new_calendar_year = checkSession($calendar_year);
      
      $addPaper = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $addPaper->bind_param('ssssssssssssisiiisssisisdisssssssssssis', $_POST['new_paper'], $tmp_start_date, $tmp_end_date, $timezone, $paper_type, $paper_prologue, $paper_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $userID, $folder, $labs, $rubric, $calculator, $externals, $tmp_exam_duration, $created, $tmp_random_mark, $tmp_total_mark, $display_correct_answer, $display_question_mark, $display_students_response, $display_feedback, $hide_if_unanswered, $moduleID, $new_calendar_year, $internal_reviewers, $tmp_external_review_deadline, $tmp_internal_review_deadline, $sound_demo, $latex_needed, $password);
      $addPaper->execute();
      $new_paper_id = $mysqli->insert_id;
      $addPaper->close();
    }
    $result->free_result();
    $result->close();
    
    return $new_paper_id;
  }
?>
</body>
</html>
