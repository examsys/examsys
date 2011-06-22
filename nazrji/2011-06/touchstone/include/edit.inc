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

require_once 'add_edit.inc';

function displayEditTab($created, $modified, $locked) {
  $html = "<div id=\"editortab\" style=\"background-color:#F1F5FB; display:block; font-size:100%\">\n";
  $html .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"editortab\" style=\"font-size:90%; background-color:#DFECFF\">\n";
  $html .= "<tr><td>\n";
  $html .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:504px; font-size:90%\">\n<tr>\n";
  $html .= "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_on.gif)\">Editor</td>\n";
  $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_off.gif)\" onclick=\"showTab('changestab');\">Changes</td>\n";
  $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_off.gif)\" onclick=\"showTab('commentstab');\">Comments</td>\n";
  if (!isset($_GET['paperID']) or strpos($_SERVER['PHP_SELF'],'info.php') !== false or strpos($_SERVER['PHP_SELF'],'likert.php') !== false) {  // Disabled the mapping tab.
    $html .= "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_disabled.gif)\">Mapping</td>\n";
  } elseif (substr($_GET['paperID'],0,5) == 'find:') {
    $html .= "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_disabled.gif)\">Mapping</td>\n";
  } else {
    $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(/touchstone/artwork/tab_off.gif)\" onclick=\"showTab('mappingtab');\">Mapping</td>\n";
  }
  $html .= "</tr></table>\n";
  $html .= "<td style=\"width:100%; background-color:#DFECFF; text-align:right\"><strong>Created:</strong>&nbsp;$created&nbsp;&nbsp;&nbsp;&nbsp;<strong>Modified:</strong>&nbsp;$modified&nbsp;";
  if ($locked != '') $html .= '&nbsp;&nbsp;&nbsp;&nbsp;<strong>Locked:</strong>&nbsp;' . $locked . '&nbsp;';
  $html .= "</td></td></tr>\n<tr><td colspan=\"5\" style=\"background-color:#1E3C7B\">&nbsp;</td></tr>\n</table>";
  return $html;
}

function check_fullSave($q_id, $mysqli) {
  $result = $mysqli->prepare("SELECT locked FROM questions WHERE q_id=?");
  $result->bind_param('i',$q_id);
  $result->execute();  
  $result->bind_result($locked);
  $result->fetch();
  $result->close();

  if ($locked == '') {
    return true;
  } else {
    return false;
  }
}

function do_limitedSave($q_id, $mysqli, $userID) {
  $changes = false;
  
  $part_names = array('bloom','status');
  foreach($part_names as $section_name) {
    if ($section_name != 'keywords') $$section_name = stripslashes($_POST["$section_name"]);
    $old_section_name = 'old_' . $section_name;
    $$old_section_name = stripslashes($_POST["$old_section_name"]);
    record_trackChanges('Edit Question', $q_id, $$old_section_name, $$section_name, $section_name, $userID, $changes);
  }
  
  saveKeywords($q_id, $userID, $changes, true, $mysqli);
    
  $question_teams = getTeams();
  record_trackChanges('Edit Question', $q_id, $_POST['old_teams'], $question_teams, 'teams', $userID, $changes);

  if ($changes == true) {
    $result = $mysqli->prepare("UPDATE questions SET bloom=?, q_group=?, status=?, last_edited=NOW() WHERE q_id=?");
    $result->bind_param('sssi', $bloom, $question_teams, $status, $q_id);
    $result->execute();  
    $result->close();
  }
  
  save_external_responses($mysqli);
}

function redirect() {
  global $protocol, $mysqli, $q_id, $userID;
  // Release the lock.
  if ($_POST['checkout_author'] == $userID) {
    $result = $mysqli->prepare("UPDATE questions SET checkout_time=NULL, checkout_authorID=NULL WHERE q_id=?");
    $result->bind_param('i', $q_id);
    $result->execute();  
    $result->close();
  }

  if (substr($_POST['paperID'],0,5) == 'list:') {
    $type = substr($_POST['paperID'],5);
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/question/list.php?keyword=" . stripslashes($_POST['keyword']) . "&team=" . $_POST['team']);
  } elseif ($_POST['calling'] == 'find') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/question/search.php");
  } elseif ($_POST['calling'] == 'paper') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/paper/details.php?paperID=" . $_POST['paperID'] . "&module=" . $_POST['module'] . "&folder=" . $_POST['folder'] . "&scrOfY=" . $_POST['scrOfY']);
  } elseif ($_POST['calling'] == 'external_comments') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/reports/review_comments.php?type=external&paperID=" . $_POST['paperID'] . "&module=" . $_POST['module'] . "&folder=" . $_POST['folder'] . "&scrOfY=" . $_POST['scrOfY']);
  } elseif ($_POST['calling'] == 'internal_comments') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/reports/review_comments.php?type=internal&paperID=" . $_POST['paperID'] . "&module=" . $_POST['module'] . "&folder=" . $_POST['folder'] . "&scrOfY=" . $_POST['scrOfY']);
  } else {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/");
  }
}

function save_external_responses($dblink) {
  // Record responses to external examiner comments.
  for ($i=0; $i<$_POST['comment_no']; $i++) {
    $tmp_response = stripslashes($_POST["response$i"]);
  
    $result = $dblink->prepare("UPDATE review_comments SET action=?, response=? WHERE id=?");
    $result->bind_param('ssi', $_POST["action$i"], $tmp_response, $_POST["commentid$i"]);
    $result->execute();  
    $result->close();
  }
}

function check_edit_rights($owner, $dblink) {

  echo "<div style=\"font-size:90%; color:#808080; text-align:center\"><span class=\"mandatory\">*</span> Indicates a <strong>mandatory</strong> field that must be completed.</div>\n";
  $disabled = '';

  return $disabled;
}

function check_lock_status($checkout_author, $checkout_time, &$disabled, $dblink, $questionID) {
  global $userID;
  
  // Check for lockout.
  $current_time = time();
  if ($disabled == '' and $userID != $checkout_author) {
    if ($checkout_time != '' and $current_time - $checkout_time < 3600) {
      if ($editor = $dblink->prepare("SELECT title, initials, surname FROM users WHERE id=?")) {
        $editor->bind_param('s', $checkout_author);
        $editor->execute();
        $editor->bind_result($title, $initials, $surname);
        $editor->fetch();
        $editor->close();
      } else {
        display_error("Lock Select Error", $editor->error);
      }
      echo "<script language=\"JavaScript\">\n";
      echo "  alert('This question is currently locked for editing by $title $initials $surname. It is now in read only mode.')";
      echo "</script>\n";
      $disabled = ' disabled';
    } else {
      // Set the lock to the current time/author.
      $editLock = $dblink->prepare("UPDATE questions SET checkout_time=NOW(), checkout_authorID=? WHERE q_id=?");
      $editLock->bind_param('ii', $userID, $questionID);
      $editLock->execute();  
      $editLock->close();
      $checkout_author = $userID;
    }
  } elseif ($disabled == '' and $userID == $checkout_author) {
    // Set the lock to the current time/author.
    $editLock = $dblink->prepare("UPDATE questions SET checkout_time=NOW(), checkout_authorID=? WHERE q_id=?");
    $editLock->bind_param('ii', $userID, $questionID);
    $editLock->execute();  
    $editLock->close();
    $checkout_author = $userID;
  }
  
  return $checkout_author;
}

function record_trackChanges($type, $questionID, $old_value, $new_value, $part, $current_userID, &$changes) {
  global $mysqli;
  
  if ($old_value != $new_value) {
    $changes = true;
    //echo "INSERT INTO track_changes VALUES (NULL,$type,$questionID,$current_userID,$old_value,$new_value,NOW(),$part)<br />";
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,?,?,$current_userID,?,?,NOW(),?)");
    $result->bind_param('sisss', $type, $questionID, $old_value, $new_value, $part);
    $result->execute();
    $result->close();
  }
}

function hidden_edit_fields() {
  global $checkout_author;
  $html = '';

  if (isset($_GET['calling'])) {
    $html .= '<input type="hidden" name="calling" value="' . $_GET['calling'] . '" />';
  } else {
    $html .= '<input type="hidden" name="calling" value="" />';
  }
  
  if (isset($_GET['paperID'])) {
    $html .= '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . '" />';
  } else {
    $html .= '<input type="hidden" name="paperID" value="" />';
  }
 
  if (isset($_GET['module'])) {
    $html .= '<input type="hidden" name="module" value="' . $_GET['module'] . '" />';
  } else {
    $html .= '<input type="hidden" name="module" value="" />';
  }
 
  if (isset($_GET['folder'])) {
    $html .= '<input type="hidden" name="folder" value="' . $_GET['folder'] . '" />';
  } else {
    $html .= '<input type="hidden" name="folder" value="" />';
  }
 
  if (isset($_GET['scrOfY'])) {
    $html .= '<input type="hidden" name="scrOfY" value="' . $_GET['scrOfY'] . '" />';
  } else {
    $html .= '<input type="hidden" name="scrOfY" value="" />';
  }
 
  if (isset($_GET['keyword'])) {
    $html .= '<input type="hidden" name="keyword" value="' . str_replace('"','&quot;',stripslashes($_GET['keyword'])) . '" />';
  } else {
    $html .= '<input type="hidden" name="keyword" value="" />';
  }
  
  if (isset($_GET['team'])) {
    $html .= '<input type="hidden" name="team" value="' . $_GET['team'] . '" />';
  } else {
    $html .= '<input type="hidden" name="team" value="" />';
  }
  
  if (isset($checkout_author)) {
    $html .= '<input type="hidden" name="checkout_author" value="' . $checkout_author . '" />';
  } else {
    $html .= '<input type="hidden" name="checkout_author" value="" />';
  }
  
  return $html;
}

function save_buttons($disabled, $locked, $userID, $checkout_author) {
  $html = '';
  
  if ($disabled == '') {
    $html .= '<input type="submit" name="submit" value="Save Changes" />&nbsp;';
  } elseif ($checkout_author == '') {
    $html .= '<input type="submit" name="submit" value="Limited Save" />&nbsp;';
  } elseif ($checkout_author != $userID) {
    $html .= '<input type="submit" name="bogus" value="Save Changes" disabled />&nbsp;';
  }
  if ($locked != '' and $_GET['paperID'] != '') {
    $html .= '<input style="width:120px" type="submit" name="submit" value="Correct" />&nbsp;';
  }
  $html .= '<input style="width:120px" type="submit" name="submit" value="Cancel" onclick="formCancel();" />';  
  
  return $html;
}
?>