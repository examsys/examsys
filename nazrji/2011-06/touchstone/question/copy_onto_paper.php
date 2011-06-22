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

require '../include/staff_auth.inc';
require '../include/errors.inc';
require '../include/media.inc';
require '../classes/dateutils.class.php';

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="margin:0px; width:100%; height:100%;">
<head>
<title>Add new Question</title>
<style>
body {font-family:Arial,sans-serif; margin:0px}
td {font-size:80%}
</style>

<script language="JavaScript">
  function checkForm() {
    checkOption = -1
    for (i=0; i<theForm.property_id.length; i++) {
      if (theForm.property_id[i].checked) {
        checkOption = i;
      }
    }
    if (checkOption == -1) {
      alert("Please select which paper you would like to add the question to.");
      return false;
    }

    paperTitle = theForm.new_paper.value;
    for (a=0; a<paperTitle.length; a++) {
      char = paperTitle.substr(a,1);
      if (char == '&' || char == '#' || char == '@' || char == '?' || char == '^' || char == '~') {
        alert('A paper name cannot contain any of the following characters:\r      &  #  @  ?  ^  ~');
        return false;
      }
    }
  }
</script>
</head>

<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr><td style="background-color:#EBEADB; border-left:solid white 1px; border-right:solid #D8D2BD 1px; border-top:solid white 1px; border-bottom:solid #D8D2BD 1px; font-size:200%; font-weight:bold; color:black\">&nbsp;Select Paper</td></tr>
</table>
<?php
  echo "<form style=\"width:100%; height:100%;\" method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id'] . "\">\n";
?>

  <p style="width:98%; margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_warning_16.png" width="16" height="16" alt="WARNING: Active paper!" border="0" /> = A paper is currently 'active'. The current date lies between its start and end dates. This is a safety feature so active papers cannot be altered.</p>
  <p style="width:98%; margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_padlock.png" width="16" height="16" alt="WARNING: Locked paper!" border="0" /> = A summative paper is locked and cannot be altered.</p>
  <div style="width:100%;">
  <table cellpadding="0" cellspacing="1" border="0" width="95%">
<?php
  $result = $mysqli->prepare("SELECT DISTINCT property_id, paper_title, start_date, end_date, paper_type FROM properties WHERE (paper_ownerID=? OR moduleID IN ('" . implode("','",$teams) . "')) AND deleted IS NULL  ORDER BY paper_title");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($row = $result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and date("Y-m-d H:i:s") > $end_date) {
      //echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" alt=\"WARNING: Locked paper!\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_warning_16.png\" width=\"16\" height=\"16\" alt=\"WARNING: Active paper!\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\">$paper_title</td></tr>\n";
    }
  }
  $result->close();
  echo "<tr><td>&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"-new-assessment-paper-\"><input type=\"text\" size=\"40\" name=\"new_paper\" value=\"New Assessment Paper\" />&nbsp;<select name=\"new_module\" style=\"width:220px\">";
  $result = $mysqli->prepare("SELECT moduleid, fullname FROM teams, modules WHERE teams.name=modules.moduleid AND memberID=?");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($moduleid, $fullname);
  while ($row = $result->fetch()) {
    echo "<option value=\"$moduleid\">$moduleid: $fullname</option>";
  }
  $result->close();
  echo "</select></td></tr>\n</table>\n</div>";
  echo "<div align=\"center\"><input type=\"submit\" style=\"width:120px\" name=\"submit\" value=\"OK\" />&nbsp;&nbsp;<input type=\"button\" style=\"width:120px\" name=\"cancel\" onclick=\"window.close();\" value=\"Cancel\" /></div>\n</form>\n";
} else {
?>
<html>
<head>
<title>Add new Question</title>
</head>
<body style="font-family:Arial,sans-serif; background-color:EEECDC; text-align:center">
<?php
  $property_id = $_POST['property_id'];
  $q_id = $_GET['q_id'];

  //- Handle paper data first ------------------------------------------------------------------------------------------------------------------------------------
  if ($property_id == '-new-assessment-paper-') {
    // Check that paper name is not already in use.
    $result = $mysqli->prepare("SELECT property_id FROM properties WHERE paper_title=? LIMIT 1");
    $np_name = stripslashes($_POST['new_paper']);
    $result->bind_param('s', $np_name);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_id);
    $rows_found = $result->num_rows;
    $result->free_result();
    $result->close();
    if ($rows_found > 0) {
      echo "Sorry <strong>'" . stripslashes($_POST['new_paper']) . "'</strong> is a name already in use.";
      echo "<p><input type=\"button\" value=\"Back\" style=\"width:100px\" onclick=\"history.back();\" /></p>\n</body>\n</html>\n";
      exit;
    }
    $paper_title = $_POST['new_paper'];

    // Calculate what the current academic session is.
		$session = DateUtils::get_current_academic_year();
        
    $tmp_paper_title = stripslashes($_POST['new_paper']);

    // Create the new paper.
    $result = $mysqli->prepare("INSERT INTO properties VALUES (NULL,?,'20030101090000','20250101090000','Europe/London','0','','','white','black','#316AC5','#C00000','0',1,'0',40,70,?,'','','',0,'',NULL,NULL,NOW(),0,0,'1','1','1','1','0',?,?,'',NULL,NULL,'0',0,'')");
    $result->bind_param('siss', $tmp_paper_title, $userID, $_POST['new_module'], $session);
    $result->execute();
    $property_id = $mysqli->insert_id;
    $result->close();

    $display_pos = 1;
    $screen = 1;
  } else {
    // Get the paper name.
    $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($paper_title);
    $result->fetch();
    $result->close();

    // Get the maximum display position for an existing paper.
    $result = $mysqli->prepare("SELECT MAX(display_pos), MAX(screen) FROM papers WHERE paper=?");
    $result->bind_param('i', $property_id);
    $result->execute();
    $result->bind_result($display_pos, $screen);
    $result->fetch();
    $result->close();
    if ($screen == '') $screen = 1;
    $display_pos++;                     // Add one to put new question right at the end.
  }

  //- Copy the question ------------------------------------------------------------------------------------------------------------------------------------------
  $result = $mysqli->prepare("SELECT * FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $result->bind_param('i', $_GET['q_id']);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $owner, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $q_group, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks);
  $line = 0;
  while ($row = $result->fetch()) {
    // Question data
    if ($q_media != '' and $q_media != 'NULL' and $line == 0) {
      $media_array = array();
      $media_array = explode("|",$q_media);
      $new_q_media = '';
      foreach ($media_array as $individual_media) {
        if ($individual_media != '' and $individual_media != 'NULL') {
          $new_media_name = unique_filename($individual_media,FALSE);
          if(file_exists("../media/$individual_media")){
            if (!copy("../media/$individual_media","../media/$new_media_name")) {
              display_error('File Copy Error 1', "File <strong>'$new_media_name'</strong> could not be copied");
            }
          } else {
            display_error('File Copy Error 3', "File <strong>'$new_media_name'</strong> could not be copied.<br />File not found.");
          }
          if ($new_q_media == '') {
            $new_q_media = $new_media_name;
          } else {
            $new_q_media .= '|' . $new_media_name;
          }
        }
      }
    }

    // Option data
    $o_id = $_GET['q_id'];
    if ($o_media != '') {
      $media_array = array();
      $media_array = explode("|",$o_media);
      $new_o_media = '';
      foreach ($media_array as $individual_media) {
        if ($individual_media != '' and $individual_media != 'NULL') {
          $new_media_name = unique_filename($individual_media,FALSE);
          if(file_exists("../media/$individual_media")){
            if (!copy("../media/$individual_media","../media/$new_media_name")) {
              display_error('File Copy Error 2', "File <strong>'$new_media_name'</strong> could not be copied.<br />Original filename was: $individual_media");
            }
          } else {
            display_error('File Copy Error 4', "File <strong>'$new_media_name'</strong> could not be copied.<br />File not found.");
          }
          if ($new_o_media == '') {
            $new_o_media = $new_media_name;
          } else {
            $new_o_media .= '|' . $new_media_name;
          }
        }
      }
    }
    
    if ($line == 0) {  // First record - write out the question, all the rest are options.
      $addQuestion = $mysqli->prepare("INSERT INTO questions VALUES(NULL,?,?,?,?,?,?,?,?," . $userID . ",?,?,?,NOW(),NOW(),?,'',?,?,NULL,NULL,NULL,NULL,?,'Normal',?)");
      $addQuestion->bind_param('ssssssssssssssss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $score_method, $notes, $new_q_media, $q_media_width, $q_media_height, $bloom, $scenario_plain, $leadin_plain, $std, $q_option_order);
      $addQuestion->execute();
      $question_id = $mysqli->insert_id;
      $addQuestion->close();

      // Create a track changes record to say where question came from.
      $question_id = intval($question_id);
      $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Copied Question',?," . $userID . ",?,?,NOW(),'Copied Question')");
      $trackChange->bind_param('iss', $question_id, $_GET['q_id'], $question_id);
      $trackChange->execute();
      $trackChange->close();

      // Lookup and copy the keywords
      $keyword_result = $mysqli->prepare("SELECT keywordID FROM keywords_question WHERE q_id=?");
      $keyword_result->bind_param('i', $_GET['q_id']);
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
  $result->free_result();
  $result->close();

  //- Add the question to the paper ------------------------------------------------------------------------------------------------------------------------------
  $result = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
  $result->bind_param('iiii',$property_id,$question_id,$screen,$display_pos);
  $result->execute();
  $result->close();


  // Create a track changes record to say new question added.
  $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Alter Paper',?," . $userID . ",'',?,NOW(),'Add Question')");
  $trackChange->bind_param('is', $property_id, $question_id);
  $trackChange->execute();
  $trackChange->close();
  $mysqli->close();

  echo "<p>Question copied onto <strong>$paper_title</strong>.</p>\n";
  echo "<p><input type=\"button\" value=\"  OK  \" onclick=\"window.close();\" /></p>\n";
}
?>
</body>
</html>