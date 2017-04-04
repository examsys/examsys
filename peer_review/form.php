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
* This is the peer review form that students use.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';
require_once '../include/demo_replace.inc';


check_var('id', 'GET', true, false, false);

$userObject = UserObject::get_instance();

function display_question($qID, $details, $member_userID, &$row_no, $columns, $marking, $saved_results) {
  if ($details['q_type'] == 'likert') {
    echo "<tr><td>" . $details['leadin'] . "</td>";
    for ($i=(0 + $marking); $i<($columns + $marking); $i++) {
      if (isset($saved_results[$member_userID][$qID]['rating']) and $saved_results[$member_userID][$qID]['rating'] === $i) {
        echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"$i\" checked=\"checked\" /></td>";
      } else {
        echo "<td class=\"col\"><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"$i\" /></td>";
      }
    }
    echo "</tr>";
  } elseif ($details['q_type'] == 'mcq') {
    echo "<tr><td><p>" . $details['leadin'] . "</p><blockquote><table>";
    $i = 1;
    foreach ($details['options'] as $option) {
      if (isset($saved_results[$member_userID][$qID]['rating']) and $saved_results[$member_userID][$qID]['rating'] === $i) {
        echo "<tr><td><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"$i\" checked=\"checked\" /><td><td>$option</td></tr>\n";
      } else {
        echo "<tr><td><input type=\"radio\" name=\"" . $member_userID . "_" . $row_no . "\" value=\"$i\" /><td><td>$option</td></tr>\n";
      }
      $i++;
    }
    echo '</table><blockquote></td></tr>';
  }

  $row_no++;
}

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$property_id		= $propertyObj->get_property_id();
$calendar_year	= $propertyObj->get_calendar_year();
$paper_title		= $propertyObj->get_paper_title();
$paper_type			= $propertyObj->get_paper_type();
$start_date			= $propertyObj->get_start_date();
$end_date				= $propertyObj->get_end_date();
$marking				= $propertyObj->get_marking();
$password				= $propertyObj->get_password();
$paper_prologue	= $propertyObj->get_paper_prologue();
// TODO: remove nasty oveloaded database fields
$display_photos = $propertyObj->get_display_correct_answer();
$review					= $propertyObj->get_display_question_mark();
$type						= $propertyObj->get_rubric();
$demo = is_demo($userObject);
/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $paper_fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$modules = Paper_utils::get_modules($property_id, $mysqli);

if ($calendar_year == '') {
  display_error($string['Error'], $string['NoAcademicSession'], false, true);
}

if ($type == '') {   // What metadata field to use.
  display_error($string['Error'], $string['NoFieldMetadata'], false, true);
}

// Get lab info
$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)){
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}

if ($userObject->has_role('Student')) {
  // Check time security
  check_datetime($start_date, $end_date, $string, $mysqli);

  // Check room security
  $paper_type = '6';
  $low_bandwidth = check_labs(  $paper_type,
                                $propertyObj->get_labs(),
                                $current_address,
                                $propertyObj->get_password(),
                                $string,
                                $mysqli
                              );

  // Check for additional password on the paper
  check_paper_password($propertyObj->get_property_id(), $password, $string, $mysqli, true);
}

// Get questions on the paper
$questions = array();
$old_options = array();
$old_questionID = 0;

$result = $mysqli->prepare("SELECT question, scenario, leadin, display_method, q_type, option_text FROM (papers, questions, options) WHERE papers.question=questions.q_id AND paper = ? AND questions.q_id = options.o_id ORDER BY display_pos");
$result->bind_param('i', $property_id);
$result->execute();
$result->bind_result($questionID, $scenario, $leadin, $display_method, $q_type, $option_text);
while ($result->fetch()) {
  if ($old_questionID != $questionID and $old_questionID != 0) {
    $questions[$old_questionID]['scenario'] = $old_scenario;
    $questions[$old_questionID]['leadin'] = $old_leadin;
    $questions[$old_questionID]['display_method'] = $old_display_method;
    $questions[$old_questionID]['q_type'] = $old_q_type;
    $questions[$old_questionID]['options'] = $old_options;
    $old_options = array();
  }
  $old_questionID = $questionID;
  $old_scenario = $scenario;
  $old_leadin = $leadin;
  $old_display_method = $display_method;
  $old_q_type = $q_type;
  $old_options[] = $option_text;
}
$questions[$old_questionID]['scenario'] = $old_scenario;
$questions[$old_questionID]['leadin'] = $old_leadin;
$questions[$old_questionID]['display_method'] = $old_display_method;
$questions[$old_questionID]['q_type'] = $old_q_type;
$questions[$old_questionID]['options'] = $old_options;

$result->close();

// Work out the scale.
$parts = explode('|', $display_method);
$columns = count($parts) - 1;

// Get the group of the current user.
if ($userObject->has_role('Student')) {
  $result = $mysqli->prepare("SELECT value FROM users_metadata WHERE idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year = ? AND type = ? AND userID = ? LIMIT 1");
  $result->bind_param('ssi', $calendar_year, $type, $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($group);
  $result->fetch();
  $result->close();
} else {                                           // Staff user
  if (isset($_GET['group'])) {
    $group = $_GET['group'];
  } else {
    $result = $mysqli->prepare("SELECT value FROM users_metadata WHERE idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year = ? AND type = ? LIMIT 1");
    $result->bind_param('ss', $calendar_year, $type);
    $result->execute();
    $result->bind_result($group);
    $result->fetch();
    $result->close();
  }
}

if ($group == '') {
  display_error($string['Error'], $string['NoGroup'], true, true);
}

if (isset($_POST['submit'] )) {
  // Check for any previously saved records.
  $result = $mysqli->prepare("SELECT id, peerID, q_id, rating FROM log6 WHERE reviewerID = ? AND paperID = ?");
  $result->bind_param('ii', $userObject->get_user_ID(), $property_id);
  $result->execute();
  $result->bind_result($id, $peerID, $q_id, $rating);
  while ($result->fetch()) {
    $saved_results[$peerID][$q_id]['id'] = $id;
  }
  $result->close();

  $insert_sql = '';
  $variables = array();
  $params = '';

  $current_time = date("YmdHis");

  if ($review == '1') {
    // Get the other users in the same group.
    $result = $mysqli->prepare("SELECT username, title, surname, first_names, users_metadata.userID FROM (users_metadata, users) WHERE users_metadata.userID = users.id AND users_metadata.idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year = ? AND type = ? AND value = ?");
    $result->bind_param('sss', $calendar_year, $type, $group);
    $result->execute();
    $result->store_result();
    $result->bind_result($member_username, $member_title, $member_surname, $member_first_names, $member_userID);
    while ($result->fetch()) {
      if ($member_userID != $userObject->get_user_ID()) {   // Make sure current user cannot peer review themself.
        $row_no = 0;

        foreach ($questions as $questionID=>$details) {
          if (isset($_POST[$member_userID . "_" . $row_no])) {
            $rating = $_POST[$member_userID . "_" . $row_no];
          } else {
            $rating = NULL;
          }

          if (isset($saved_results[$member_userID][$questionID]['id'])) {
            $result2 = $mysqli->prepare("UPDATE log6 SET started = ?, rating = ? WHERE id = ?");
            $result2->bind_param('sii', $current_time, $rating, $saved_results[$member_userID][$questionID]['id']);
            $result2->execute();
            $result2->close();
          } else {
            $result2 = $mysqli->prepare("INSERT INTO log6 VALUES (NULL, ?, ?, ?, ?, ?, ?)");
            $result2->bind_param('iiisii', $property_id, $userObject->get_user_ID(), $member_userID, $current_time, $questionID, $rating);
            $result2->execute();
            $result2->close();
          }
          $row_no++;
        }
      }
    }
    $result->close();
  } else {
    $member_userID = 0;
    // Get the other users in the same group.
    $row_no = 0;

    foreach ($questions as $questionID=>$details) {
      if (isset($_POST[$member_userID . "_" . $row_no])) {
        $rating = $_POST[$member_userID . "_" . $row_no];
      } else {
        $rating = NULL;
      }

      if (isset($saved_results[$member_userID][$questionID]['id'])) {
        $result2 = $mysqli->prepare("UPDATE log6 SET started = NOW(), rating = ? WHERE id = ?");
        $result2->bind_param('ii', $rating, $saved_results[$member_userID][$questionID]['id']);
        $result2->execute();
        $result2->close();
      } else {
        $result2 = $mysqli->prepare("INSERT INTO log6 VALUES (NULL, ?, ?, ?, ?, ?, ?)");
        $result2->bind_param('iiisii', $property_id, $userObject->get_user_ID(), $member_userID, $current_time, $questionID, $rating);
        $result2->execute();
        $result2->close();
      }
      $row_no++;
    }
  }

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['peerreview']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:<?php echo $textsize; ?>%; font-family:<?php echo $font; ?>,sans-serif; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
    td p {margin:0; padding:0}
    p {margin:0; padding:0}
    .paper {padding-left:5px; font-size:150%; color:white; font-weight:bold}
    .group {padding-left:5px; color:white}
    .title {font-size:130%; font-weight:bold; color:<?php echo $themecolor; ?>; border-top:1px solid #C0C0C0}
    .col {text-align:center; color:<?php echo $labelcolor; ?>}
    .phototd {vertical-align:top; border-top:1px solid #C0C0C0}
    .photo {background-color:white; border-left: 1px solid #F1F1F1; border-top: 1px solid #F1F1F1; box-shadow: 2px 2px 4px #808080; padding:10px; margin-right:10px}
    .thankyou {margin-left: 10px; font-size: 350%; font-weight: bold; line-height: 140%}
  </style>

  <script>
    function changeGroup() {
      window.location = "form.php?id=<?php echo $_GET['id']; ?>&group=" + document.getElementById('group').value;
    }
  </script>
</head>
<body>
  <?php
  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td>';
  echo '<div style="float:right; padding-right:10px; position: relative; top: 10px"><a href="../logout.php"><img src="../artwork/student_logout.png" width="24" height="24" /></a></div>';
  echo '<div class="paper">' . $paper_title . '</div><div class="group"><strong>' . $string['Reviewer'] . ':</strong> ' . $userObject->get_title() . ' ' . demo_replace($userObject->get_surname(), $demo) . '<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $string['Group'] . ':</strong> ' . $group . '</strong></div></td></tr></table>';
  
  echo '<p class="thankyou">' . $string['Thank You'] . '</p>';

  echo '<p style="margin-left:10px">' . $string['The ratings saved'] . '</p>';
  echo '<br/><p style="margin-left:10px"><a href="../students/index.php">' . $string['homepagelink'] . '</a></p>';
} else {
  // Get existing values.
  $saved_results = array();
  $result = $mysqli->prepare("SELECT id, peerID, q_id, rating FROM log6 WHERE reviewerID = ? AND paperID = ?");
  $result->bind_param('ii', $userObject->get_user_ID(), $property_id);
  $result->execute();
  $result->bind_result($id, $peerID, $q_id, $rating);
  while ($result->fetch()) {
    $saved_results[$peerID][$q_id]['rating'] = $rating;
  }
  $result->close();

  ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['peerreview']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
  body {font-size:<?php echo $textsize; ?>%; font-family:<?php echo $font; ?>,sans-serif; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
  table {font-size:100%}
  td p {margin:0}
  .paper {padding-left:5px; font-size:150%; color:white; font-weight:bold}
  .group {padding-left:5px; color:white}
  .title {font-size:130%; font-weight:bold; color:<?php echo $themecolor; ?>; border-top:1px solid #C0C0C0}
  .col {text-align:center; color:<?php echo $labelcolor; ?>}
  .phototd {vertical-align:top; border-top:1px solid #C0C0C0}
  .photo {background-color:white; border-left: 1px solid #F1F1F1; border-top: 1px solid #F1F1F1; box-shadow: 2px 2px 4px #808080; padding:10px; margin-right:10px}
  </style>

  <script>
    function changeGroup() {
      window.location = "form.php?id=<?php echo $_GET['id']; ?>&group=" + document.getElementById('group').value;
    }
  </script>
</head>
<body>

  <?php
  echo "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "\" autocomplete=\"off\">\n";

  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div><div class="group"><strong>'.$string['Reviewer'].':</strong> ' . $userObject->get_title() . ' ' . demo_replace($userObject->get_surname(), $demo) . '<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$string['Group'].':</strong> ';
  if ($userObject->has_role('Student')) {
    echo $group;
  } else {
    echo "<select name=\"group\" id=\"group\" onchange=\"changeGroup();\">\n";
    $result = $mysqli->prepare("SELECT DISTINCT value FROM users_metadata WHERE idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year = ? AND type = ? ORDER BY value");
    $result->bind_param('ss', $calendar_year, $type);
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
  echo '</div></td><td width="160"><img src="../config/logo.png" width="160" height="67" alt="Logo" /></td></tr>';
  echo '</table>';

  echo "<br />\n<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"margin-left:auto; margin-right:auto\">\n";

  if (trim($paper_prologue) != '') {
    echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">" . $paper_prologue . "</td></tr>\n";
    echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
  }

  if ($review == '1') {
    // Get the other users in the same group.
    $result = $mysqli->prepare("SELECT username, title, surname, first_names, users_metadata.userID FROM (users_metadata, users) WHERE users_metadata.userID=users.id AND idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year=? AND type=? AND value=? ORDER BY surname, initials");
    $result->bind_param('sss', $calendar_year, $type, $group);
    $result->execute();
    $result->bind_result($member_username, $member_title, $member_surname, $member_first_names, $member_userID);
    $photodirectory = rogo_directory::get_directory('user_photo');
    while ($result->fetch()) {
      if ($member_userID != $userObject->get_user_ID()) {   // Make sure current user cannot peer review themself.
        $row_no = 0;
        echo "<tr><td class=\"phototd\" rowspan=\"" . (count($questions) + 2) . "\">";
        $photoname = UserUtils::student_photo_exist($member_username);
        if ($photoname and $display_photos == '1') {
          echo "<img class=\"photo\" src=\"" . $photodirectory->url($photoname) . "\" width=\"90\" height=\"135\" border=\"0\" />";
        }
        $first_names = explode(' ', $member_first_names);
        echo "</td><td class=\"title\" colspan=\"" . ($columns + 1) . "\">$member_title " . demo_replace($first_names[0], $demo) . " " . demo_replace($member_surname, $demo) . "</td></tr>\n";

        echo "<tr><td></td>";
        for ($i=0; $i<$columns; $i++) {
          echo "<td class=\"col\">" . $parts[$i] . "</td>";
        }
        echo "</tr>\n";

        foreach ($questions as $questionID=>$details) {
          display_question($questionID, $details, $member_userID, $row_no, $columns, $marking, $saved_results);
        }

        echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
      }
    }
    $result->close();
  } else {
    $row_no = 0;
    $member_userID = 0;
    echo "<tr><td></td>";
    for ($i=0; $i<$columns; $i++) {
      echo "<td class=\"col\">" . $parts[$i] . "</td>";
    }
    echo "</tr>\n";

    foreach ($questions as $questionID=>$details) {
      display_question($questionID, $details, $member_userID, $row_no, $columns, $marking, $saved_results);
    }

    echo "<tr><td colspan=\"" . (count($questions) + 2) . "\">&nbsp;</td></tr>\n";
  }

  echo "</table>\n";

  echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"width:100%\"><tr><td style=\"background-color:#5590CF; text-align:center\">";
  if ($userObject->has_role('Student')) {
    echo "<input type=\"submit\" name=\"submit\" value=\"" . $string['save'] . "\" class=\"ok\" />";
  } else {
    echo "<input type=\"button\" name=\"close\" value=\"" . $string['close'] . "\" style=\"width:140px\" onclick=\"window.close();\" />";
  }
  echo "</td></tr>\n";
  echo "</table>\n</form>\n";

  ?>
  </html>
  </body>
<?php
}
?>