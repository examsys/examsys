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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/errors.inc';
require '../include/paper_security.inc';

check_var('id', 'GET', true, false);

// Get some properties of the paper.
$result = $mysqli->prepare("SELECT property_id, paper_title, modules.id, properties.moduleID, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), calendar_year, bgcolor, fgcolor, themecolor, labelcolor, rubric, paper_prologue AS type, marking, display_correct_answer AS display_photos, labs FROM (properties, modules) WHERE properties.moduleID=modules.moduleid AND crypt_name=? LIMIT 1");
$result->bind_param('s', $_GET['id']);
$result->execute();
$result->bind_result($property_id, $paper_title, $moduleID, $moduleID_text, $start_date, $end_date, $calendar_year, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $type, $paper_prologue, $marking, $display_photos, $labs);
$result->fetch();
$result->close();

if ($calendar_year == '') {
  display_error('Error', 'No Academic Session is set.', false, true);
}
if ($type == '') {   // What metadata field to use.
  display_error('Error', 'No field in the metadata set for groups.', false, true);
}

// Get special settings for the Student.
if ($special_needs == 1) {
  $stmt = $mysqli->prepare("SELECT background, foreground, textsize, themecolor, labelcolor, font FROM special_needs WHERE userid=?");
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $themecolor, $labelcolor, $font);
  $stmt->fetch();
  $stmt->close();
}

// If set overwrite the default colours with the current users' special settings
if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';

if (stripos($userroles,'Student') !== false) {
  // Check time security
  check_datetime($start_date, $end_date);
  
  // Check room security
  $paper_type = '6';
  $low_bandwidth = check_labs($paper_type, $labs, $mysqli);
}

// Get questions on the paper
$questions = array();

$result = $mysqli->prepare("SELECT question, leadin, display_method FROM (papers, questions) WHERE papers.question=questions.q_id AND paper=? ORDER BY display_pos");
$result->bind_param('i', $property_id);
$result->execute();
$result->bind_result($questionID, $leadin, $display_method);
while ($result->fetch()) {
  $questions[$questionID]['leadin'] = $leadin;
  $questions[$questionID]['scale'] = $display_method;
}
$result->close();

// Work out the scale.
$parts = explode('|', $display_method);
$columns = count($parts) - 1;

// Get the group of the current user.
if (stripos($userroles,'Student') !== false) {     // Student user
  $result = $mysqli->prepare("SELECT value FROM users_metadata WHERE moduleID=? AND calendar_year=? AND type=? AND userID=? LIMIT 1");
  $result->bind_param('issi', $moduleID, $calendar_year, $type, $userID);
  $result->execute();
  $result->bind_result($group);
  $result->fetch();
  $result->close();
} else {                                           // Staff user
  if (isset($_GET['group'])) {
    $group = $_GET['group'];
  } else {
    $result = $mysqli->prepare("SELECT value FROM users_metadata WHERE moduleID=? AND calendar_year=? AND type=? LIMIT 1");
    $result->bind_param('iss', $moduleID, $calendar_year, $type);
    $result->execute();
    $result->bind_result($group);
    $result->fetch();
    $result->close();
  }
}

if ($group == '') {
  display_error('Error', 'No Group can be found for the current user.', true, true);
}

if (isset($_POST['submit'] )) {
  // Get the other users in the same group.
  $result = $mysqli->prepare("SELECT username, title, surname, first_names, users_metadata.userID FROM (users_metadata, users) WHERE users_metadata.userID=users.id AND moduleID=? AND calendar_year=? AND type=? AND value=?");
  $result->bind_param('isss', $moduleID, $calendar_year, $type, $group);
  $result->execute();
  $result->store_result();
  $result->bind_result($member_username, $member_title, $member_surname, $member_first_names, $member_userID);
  while ($result->fetch()) {
    if ($member_userID != $userID) {   // Make sure current user cannot peer review themself.
      $row_no = 0;      

      foreach ($questions as $questionID=>$details) {
        if (isset($_POST[$member_userID . "_" . $row_no])) {
          $rating = $_POST[$member_userID . "_" . $row_no];
        } else {
          $rating = NULL;
        }
      
        if (isset($_POST[$member_userID . "_" . $row_no . "_id"]) and $_POST[$member_userID . "_" . $row_no . "_id"] != '') {
          $result2 = $mysqli->prepare("UPDATE log6 SET started=NOW(), rating=? WHERE id=?");
          $result2->bind_param('ii', $rating, $_POST[$member_userID . "_" . $row_no . "_id"]);
        } else {
          $result2 = $mysqli->prepare("INSERT INTO log6 VALUES (NULL, ?, ?, ?, NOW(), ?, ?)");
          $result2->bind_param('iiiii', $property_id, $userID, $member_userID, $questionID, $rating);
        }
        $result2->execute();
        $result2->close();
        
        $row_no++;
      }
    }
  }
  $result->close();
  
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">

  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['peerreview']; ?></title>

  <style>
  body {margin:0px; font-size:<?php echo $textsize; ?>%; font-family:<?php echo $font; ?>,sans-serif; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
  table {font-size:100%}
  td p {margin:0px; padding:0px}
  p {margin:0px; padding:0px}
  .paper {padding-left:5px; font-size:150%; color:white; font-weight:bold}
  .group {padding-left:5px; color:white}
  .title {font-size:130%; font-weight:bold; color:<?php echo $themecolor; ?>; border-top:1px solid #C0C0C0}
  .col {text-align:center; color:<?php echo $labelcolor; ?>}
  .phototd {vertical-align:top; border-top:1px solid #C0C0C0}
  .photo {background-color:white; border-left: 1px solid #F1F1F1; border-top: 1px solid #F1F1F1; box-shadow: 2px 2px 4px #808080; padding:10px; margin-right:10px}
  </style>
  
  <script language="JavaScript">
    function changeGroup() {
      window.location = "form.php?id=<?php echo $_GET['id']; ?>&group=" + document.getElementById('group').value;
    }
  </script>
  </head>
  <body>

  <?php
  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%;border-bottom:1px solid #164994;background-color:#2765AB;background-image:url(\'../artwork/title_gradient.png\');background-repeat:repeat-y;background-position:center">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div><div class="group"><strong>Reviewer:</strong> ' . $title .' ' . $surname . '<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Group:</strong> ' . $group . '</strong></div></td></tr></table>';
  
  if ($language == 'en') {
    echo '<p style="margin-left:10px; font-size:450%; font-family:\'Monotype Corsiva\',Rage,\'Brush Script MT\',\'Lucida Handwriting\',sans-serif">Thank You</p>';
  } else {
    // Do not use fancy fonts for foreign lanuages due to extended character support issues.
    echo '<p style="margin-left:10px; font-size:450%">Thank You</p>';
  }
  echo '<p style="margin-left:10px">The ratings of your peers have been saved.</p>';
} else {
  // Get existing values.
  $saved_results = array();
  $result = $mysqli->prepare("SELECT id, peerID, q_id, rating FROM log6 WHERE reviewerID=? AND paperID=?");
  $result->bind_param('ii', $userID, $property_id);
  $result->execute();
  $result->bind_result($id, $peerID, $q_id, $rating);
  while ($result->fetch()) {
    $saved_results[$peerID][$q_id]['id'] = $id;
    $saved_results[$peerID][$q_id]['rating'] = $rating;
  }
  $result->close();

  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">

  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['peerreview']; ?></title>

  <style>
  body {margin:0px; font-size:<?php echo $textsize; ?>%; font-family:<?php echo $font; ?>,sans-serif; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
  table {font-size:100%}
  td p {margin:0px}
  .paper {padding-left:5px; font-size:150%; color:white; font-weight:bold}
  .group {padding-left:5px; color:white}
  .title {font-size:130%; font-weight:bold; color:<?php echo $themecolor; ?>; border-top:1px solid #C0C0C0}
  .col {text-align:center; color:<?php echo $labelcolor; ?>}
  .phototd {vertical-align:top; border-top:1px solid #C0C0C0}
  .photo {background-color:white; border-left: 1px solid #F1F1F1; border-top: 1px solid #F1F1F1; box-shadow: 2px 2px 4px #808080; padding:10px; margin-right:10px}
  </style>
  
  <script language="JavaScript">
    function changeGroup() {
      window.location = "form.php?id=<?php echo $_GET['id']; ?>&group=" + document.getElementById('group').value;
    }
  </script>
  </head>
  <body>

  <?php
  echo "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "\">\n";

  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%;border-bottom:1px solid #164994;background-color:#2765AB;background-image:url(\'../artwork/title_gradient.png\');background-repeat:repeat-y;background-position:center">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div><div class="group"><strong>Reviewer:</strong> ' . $title .' ' . $surname . '<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Group:</strong> ';
  if (stripos($userroles,'Student') !== false) {
    echo $group;
  } else {
    echo "<select name=\"group\" id=\"group\" onchange=\"changeGroup();\">\n";
    $result = $mysqli->prepare("SELECT DISTINCT value FROM users_metadata WHERE moduleID=? AND calendar_year=? AND type=? ORDER BY value");
    $result->bind_param('iss', $moduleID, $calendar_year, $type);
    $result->execute();
    $result->bind_result($tmp_group);
    while ($result->fetch()) {
      if ($group == $tmp_group) {
        echo "<option value=\"$tmp_group\" selected>$tmp_group</option>\n";
      } else {
        echo "<option value=\"$tmp_group\">$tmp_group</option>\n";
      }
    }
    $result->close();
    
    echo "</select>\n";
  }
  echo '</div></td><td width="160"><img src="../artwork/uni_logo.png" width="160" height="67" alt="Logo" /></td></tr>';
  echo '</table>';

  echo "<br />\n<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"margin-left:auto; margin-right:auto\">\n";
  
  if (trim($paper_prologue) != '') {
    echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">" . $paper_prologue . "</td></tr>\n";
    echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
  }
    
  // Get the other users in the same group.
  $result = $mysqli->prepare("SELECT username, title, surname, first_names, users_metadata.userID FROM (users_metadata, users) WHERE users_metadata.userID=users.id AND moduleID=? AND calendar_year=? AND type=? AND value=?");
  $result->bind_param('isss', $moduleID, $calendar_year, $type, $group);
  $result->execute();
  $result->bind_result($member_username, $member_title, $member_surname, $member_first_names, $member_userID);
  while ($result->fetch()) {
    if ($member_userID != $userID) {   // Make sure current user cannot peer review themself.
      $row_no = 0;
      echo "<tr><td class=\"phototd\" rowspan=\"" . (count($questions) + 2) . "\">";
      $peer_photo = $cfg_web_root . 'users/photos/' . $member_username . '.jpg';
      if (file_exists($peer_photo) and $display_photos == '1') {
        echo "<img class=\"photo\" src=\"/users/photos/" . $member_username . ".jpg\" width=\"90\" height=\"135\" border=\"0\" />";
      }
      $first_names = explode(' ', $member_first_names);
      echo "</td><td class=\"title\" colspan=\"" . ($columns + 1) . "\">$member_title " . $first_names[0] . " $member_surname</td></tr>\n";
      
      echo "<tr><td></td>";
      for ($i=0; $i<$columns; $i++) {
        echo "<td class=\"col\">" . $parts[$i] . "</td>";
      }
      echo "</tr>\n";
      
      
      foreach($questions as $questionID=>$details) {
        $record_id = '';
        if (isset($saved_results[$member_userID][$questionID]['id'])) {
          $record_id = $saved_results[$member_userID][$questionID]['id'];
        }
        echo "<tr><td>" . $details['leadin']. "<input type=\"hidden\" name=\"" . $member_userID . "_" . $row_no . "_id\" value=\"$record_id\" /></td>";
        for ($i=0; $i<$columns; $i++) {
          if (isset($saved_results[$member_userID][$questionID]['rating']) and $saved_results[$member_userID][$questionID]['rating'] === $i) {
            echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" checked /></td>";
          } else {
            echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" /></td>";
          }
        }
        echo "</tr>";
        $row_no++;
      }
      
      echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
    }
  }
  $result->close();
  
  echo "</table>\n";

  echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td style=\"border-top:1px solid #164994;background-color:#2765AB;background-image:url('../artwork/title_gradient.png');background-repeat:repeat-y;background-position:center; text-align:center\">";
  if (stripos($userroles,'Student') !== false) {
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $string['save'] . "\" style=\"width:100px\" />";
  } else {
    echo "<input type=\"button\" name=\"close\" value=\"Close\" style=\"width:100px\" onclick=\"window.close();\" />";
  }
  echo "</td></tr>\n";

  echo "</table>\n</form>\n";
    
  ?>
  </html>
  </body>
<?php
}
?>