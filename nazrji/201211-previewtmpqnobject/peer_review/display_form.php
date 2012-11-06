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

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require '../include/paper_security.inc';

check_var('paperID', 'GET', true, false);

// Get some properties of the paper.
$result = $mysqli->prepare("SELECT property_id, paper_title, modules.id, properties.moduleID, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), calendar_year, bgcolor, fgcolor, themecolor, labelcolor, rubric, paper_prologue AS type, marking, display_correct_answer AS display_photos, labs, crypt_name, display_question_mark FROM (properties, modules) WHERE properties.moduleID=modules.moduleid AND property_id=? LIMIT 1");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($property_id, $paper_title, $moduleID, $moduleID_text, $start_date, $end_date, $calendar_year, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $type, $paper_prologue, $marking, $display_photos, $labs, $crypt_name, $review_type);
$result->fetch();
$result->close();

if ($calendar_year == '') {
  display_error('Error', 'No Academic Session is set.', false, true);
}
if ($type == '') {   // What metadata field to use.
  display_error('Error', 'No field in the metadata set for groups.', false, true);
}

// If set overwrite the default colours with the current users' special settings
if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';

// Get questions on the paper
$questions = array();

$result = $mysqli->prepare("SELECT question, q_type, leadin, display_method FROM (papers, questions) WHERE papers.question=questions.q_id AND paper=? ORDER BY display_pos");
$result->bind_param('i', $property_id);
$result->execute();
$result->bind_result($questionID, $q_type, $leadin, $display_method);
while ($result->fetch()) {
  $questions[$questionID]['leadin'] = $leadin;
  $questions[$questionID]['scale'] = $display_method;
}
$result->close();

// Work out the scale.
if ($q_type == 'likert') {
$parts = explode('|', $display_method);
$columns = count($parts) - 1;
} elseif ($q_type == 'mcq') {
  $parts = array();
  $result = $mysqli->prepare("SELECT option_text FROM options WHERE o_id=?");
  $result->bind_param('i', $questionID);
  $result->execute();
  $result->bind_result($o_text);
  while($result->fetch()) {
    $parts[] = $o_text;
  }
  $result->close();
  $columns = count($parts);
} else {
  $columns = 0;
}
// Get the group of the current user.
$result = $mysqli->prepare("SELECT value FROM users_metadata WHERE moduleID=? AND calendar_year=? AND type=? AND userID=? LIMIT 1");
$result->bind_param('issi', $moduleID, $calendar_year, $type, $_GET['userID']);
$result->execute();
$result->bind_result($group);
$result->fetch();
$result->close();

if ($group == '') {
  display_error('Error', 'No Group can be found for the current user.', true, true);
}

// Get the name of the current user.
$result = $mysqli->prepare("SELECT username, surname, first_names, title FROM users WHERE id=? LIMIT 1");
$result->bind_param('i', $_GET['userID']);
$result->execute();
$result->bind_result($student_username, $student_surname, $student_first_names, $student_title);
$result->fetch();
$result->close();


// Get existing values.
$saved_results = array();
if ($review_type == '1') {
  $result = $mysqli->prepare("SELECT id, reviewerID, q_id, rating FROM log6 WHERE peerID=? AND paperID=?");
} else {
  $result = $mysqli->prepare("SELECT id, reviewerID, q_id, rating FROM log6 WHERE reviewerID=? AND paperID=?");
}
$result->bind_param('ii', $_GET['userID'], $property_id);
$result->execute();
$result->bind_result($id, $reviwerID, $q_id, $rating);
while ($result->fetch()) {
  $saved_results[$reviwerID][$q_id]['id'] = $id;
  $saved_results[$reviwerID][$q_id]['rating'] = $rating;
}
$result->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Form</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:<?php echo $textsize; ?>%; font-family:<?php echo $font; ?>; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
    td p {margin:0px}
    .paper {padding-left:5px; font-size:150%; color:white; font-weight:bold}
    .group {padding-left:5px; color:white}
    .title {font-size:130%; font-weight:bold; color:<?php echo $themecolor; ?>; border-top:1px solid #C0C0C0}
    .col {text-align:center; color:<?php echo $labelcolor; ?>}
    .phototd {vertical-align:top; border-top:1px solid #C0C0C0}
    .photo {background-color:white; border-left: 1px solid #F1F1F1; border-top: 1px solid #F1F1F1; box-shadow: 2px 2px 4px #808080; padding:10px; margin-right:10px}
    .mcq td {vertical-align:top; padding: 3px 0}
    .mcq td.radio {width: 36px}
    .indented {margin-left: 36px; width:100%; border: 0; border-collapse: collapse}
  </style>

  <script type="text/javascript">
    function changeGroup() {
      window.location = "form.php?id=<?php echo $crypt_name; ?>&group=" + document.getElementById('group').value;
    }
  </script>
</head>
<body>

<?php
echo "<form>\n";

echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%;border-bottom:1px solid #164994;background-color:#2765AB;background-image:url(\'../artwork/title_gradient.png\');background-repeat:repeat-y;background-position:center">';
echo '<tr><td><div class="paper">' . $paper_title . '</div><div class="group"><strong>Student:</strong> ' . $student_title . ' ' . $student_surname . ', ' . $student_first_names . '<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Group:</strong> ' . $group . '</div></td><td width="160"><img src="../artwork/uni_logo.png" width="160" height="67" alt="Logo" /></td></tr>';
echo '</table>';

echo "<br />\n<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"margin-left:auto; margin-right:auto\">\n";

if (trim($paper_prologue) != '') {
  echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">" . $paper_prologue . "</td></tr>\n";
  echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
}
  
// Get the other users in the same group.
if ($review_type == '1') {
  $result = $mysqli->prepare("SELECT username, title, surname, first_names, users_metadata.userID FROM (users_metadata, users) WHERE users_metadata.userID=users.id AND moduleID=? AND calendar_year=? AND type=? AND value=? AND userID!=? ORDER BY surname, initials");
  $result->bind_param('isssi', $moduleID, $calendar_year, $type, $group, $_GET['userID']);
  $result->execute();
  $result->bind_result($member_username, $member_title, $member_surname, $member_first_names, $member_userID);
  while ($result->fetch()) {
    if ($member_userID != $userID) {   // Make sure current user cannot peer review themself.
      display_user($review_type, $q_type, $questions, $saved_results, $cfg_web_root, $member_userID, $member_username, $member_title, $member_first_names, $member_surname, $display_photos, $columns, $parts, $marking);
    }
  }
  $result->close();
} else {
  display_user($review_type, $q_type, $questions, $saved_results, $cfg_web_root, $_GET['userID'], $student_username, $student_title, $student_first_names, $student_surname, $display_photos, $columns, $parts, $marking);
}
echo "</table>\n";

echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td style=\"border-top:1px solid #164994;background-color:#2765AB;background-image:url('../artwork/title_gradient.png');background-repeat:repeat-y;background-position:center; text-align:center\">";
echo "<input type=\"button\" name=\"close\" value=\"Close\" style=\"width:100px\" onclick=\"window.close();\" />";
echo "</td></tr>\n";

echo "</table>\n</form>\n";
  
?>
</html>
</body>
<?php
function display_user($review_type, $q_type, $questions, $saved_results, $cfg_web_root, $member_userID, $member_username, $member_title, $member_first_names, $member_surname, $display_photos, $columns, $parts, $marking) {
  $row_no = 0;
  $rowspan = ($review_type == '1') ? count($questions) + 2 : (count($questions) * 2) + 2;
  echo "<tr><td class=\"phototd\" rowspan=\"$rowspan\">";
  $peer_photo = $cfg_web_root . 'users/photos/' . $member_username . '.jpg';
  if (file_exists($peer_photo) and $display_photos == '1') {
    echo "<img class=\"photo\" src=\"../users/photos/" . $member_username . ".jpg\" width=\"90\" height=\"135\" border=\"0\" />";
  }
  $first_names = explode(' ', $member_first_names);
  echo "</td><td class=\"title\" colspan=\"" . ($columns + 1) . "\">$member_title " . $first_names[0] . " $member_surname</td></tr>\n";

  echo "<tr><td></td>";
  if ($q_type == 'likert') {
    for ($i=0; $i<$columns; $i++) {
      echo "<td class=\"col\">" . $parts[$i] . "</td>";
    }
  }
  echo "</tr>\n";


  foreach($questions as $questionID=>$details) {
    $rating = (isset($saved_results[$member_userID][$questionID]['rating'])) ? $saved_results[$member_userID][$questionID]['rating'] : -99;
    if ($q_type == 'mcq') {
      render_mcq($details, $parts, $marking, $columns, $member_userID, $rating, $row_no);
    } else {
      render_likert($details, $marking, $columns, $member_userID, $rating, $row_no);
    }
    $row_no++;
  }

  echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
}

function render_likert($details, $marking, $columns, $member_userID, $rating, $row_no) {
  echo "<tr><td>" . $details['leadin']. "</td>";
  for ($i=(0 + $marking); $i<($columns + $marking); $i++) {
    if ($rating === $i) {
      echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" checked /></td>";
    } else {
      echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" /></td>";
    }
  }
  echo "</tr>\n";
}

function render_mcq($details, $parts, $marking, $columns, $member_userID, $rating, $row_no) {
  echo "<tr><td style=\"padding-top: 12px;\">{$details['leadin']}</td></tr>\n";
  echo "<tr><td><table class=\"indented\">\n";
  $index = 0;
  for ($i=(0 + $marking); $i<($columns + $marking); $i++) {
    echo "<tr class=\"mcq\">";
    if ($rating === $i) {
      echo "\t<td class=\"radio\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" checked /></td>\n";
    } else {
      echo "\t<td class=\"radio\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"" . ($i + $marking) . "\" /></td>\n";
    }
    echo "<td>{$parts[count($parts) - $index - 1]}</td></tr>\n";
    $index++;
  }
  echo '</table></td></tr>';
}
?>