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
* Script is used to change the userID from a reservered temp_user account to a real user account.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../classes/dateutils.class.php';

function getModules($userID, $mysqlidb) {
  $modules = array();
  $session = date_utils::get_current_academic_year();

  $result = $mysqlidb->prepare("SELECT moduleid FROM student_modules WHERE calendar_year=? AND userID=?");
  $result->bind_param('si', $session, $userID);
  $result->execute();
  $result->bind_result($moduleid);
  while ($result->fetch()) {
    $modules[] = $moduleid;
  }
  $result->close();
  
  return $modules;
}


// Get all the details from 'temp_users' for given userID.
$result = $mysqli->prepare("SELECT temp_users.id, temp_users.title, temp_users.first_names, temp_users.surname, student_id, assigned_account, username FROM users, temp_users WHERE users.id=? AND users.username=temp_users.assigned_account");
$result->bind_param('i', $_GET['userID']);
$result->execute();
$result->bind_result($temp_account_id, $temp_title, $temp_first_names, $temp_surname, $temp_student_id, $assigned_account, $temp_username);
$result->fetch();
$result->close();

if (isset($_POST['submit'])) {
  $temp_title = $_POST['title'];
  $temp_first_names = $_POST['first_names'];
  $temp_surname = $_POST['surname'];
  $temp_student_id = $_POST['student_id'];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Reassign Script to User</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%}
  </style>

  <script language="JavaScript">
    function doReassign(targetID) {
      window.location = "do_reassign_script.php?temp_userID=<?php echo $_GET['userID']; ?>&userID=" + targetID + "&assigned_account=<?php echo $temp_username; ?>";
    }

    function lon(lineID) {
      document.getElementById(lineID).style.backgroundColor = '#EBF3FD';
      document.getElementById(lineID).style.border = '1px solid #B8D6FB';
    }

    function loff(lineID) {
      document.getElementById(lineID).style.backgroundColor = '';
      document.getElementById(lineID).style.border = '1px solid white';
    }
  </script>
</head>

<body>
<?php
// Check if the exam is still running. Re-assignment mid-exam would upset the data.
$result = $mysqli->prepare("SELECT UNIX_TIMESTAMP(end_date) FROM properties WHERE property_id=?");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($end_date);
$result->fetch();
$result->close();

if (time() < $end_date) {
  echo "<p><strong>Warning</strong><p><p>Exam scripts cannot be reassigned mid exam.<br />Please wait until after the exam has finished</p>\n";
  exit;
}

$target_userID = '';

$target_student = array();

// Look up the temporary information in 'users'.
if ($temp_student_id != '') {
  // Try student number lookup.
  $result = $mysqli->prepare("SELECT id, surname, first_names, title, gender FROM users, sid WHERE users.id=sid.userID AND student_id=?");
  $result->bind_param('i', $temp_student_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($target_userID, $target_surname, $target_first_names, $target_title, $gender);
  while ($result->fetch()) {
    $target_student[$target_userID]['surname'] = $target_surname;
    $target_student[$target_userID]['first_names'] = $target_first_names;
    $target_student[$target_userID]['title'] = $target_title;
    $target_student[$target_userID]['gender'] = $gender;
    $target_student[$target_userID]['student_id'] = $temp_student_id;
    $target_student[$target_userID]['modules'] = getModules($target_userID, $mysqli);
  }
  $result->close();
}
if ($target_userID == '') {
  // If no student number try the other details.
  $first_names = trim($temp_first_names) . '%';
  $temp_surname = trim($temp_surname);
  $temp_title = trim($temp_title);
  $result = $mysqli->prepare("SELECT id, surname, first_names, title, gender, student_id FROM users LEFT JOIN sid ON users.id=sid.userID WHERE surname=? AND first_names LIKE ? AND (roles LIKE '%staff%' OR roles = 'student')");
  $result->bind_param('ss', $temp_surname, $first_names);
  $result->execute();
  $result->store_result();
  $result->bind_result($target_userID, $target_surname, $target_first_names, $target_title, $gender, $student_id);
  while ($result->fetch()) {
    $target_student[$target_userID]['surname'] = $target_surname;
    $target_student[$target_userID]['first_names'] = $target_first_names;
    $target_student[$target_userID]['title'] = $target_title;
    $target_student[$target_userID]['gender'] = $gender;
    $target_student[$target_userID]['student_id'] = $student_id;
    $target_student[$target_userID]['modules'] = getModules($target_userID, $mysqli);
  }
  $result->close();
}

echo "<p style=\"color:#0033BC\">" . str_replace('user','Temporary Account ',$temp_username) . " was reserved with the following details:</p>\n<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=" . $_GET['userID'] . "\">\n<table border=\"0\" style=\"width:100%\">\n";
echo "<tr><th>Title</th><th>Last Name</th><th>First Names</th><th>Student ID</th><th></th></tr>\n";
echo "<tr><td><input type=\"text\" name=\"title\" value=\"$temp_title\" size=\"5\" /></td><td><input type=\"text\" name=\"surname\" value=\"$temp_surname\" size=\"15\" /></td><td><input type=\"text\" name=\"first_names\" value=\"$temp_first_names\" size=\"15\" /></td><td><input type=\"text\" name=\"student_id\" value=\"$temp_student_id\" size=\"6\" /></td><td><input type=\"submit\" name=\"submit\" value=\"Search\" style=\"width:80px\" /></tr>\n";
echo "</table>\n</form>\n";

if (count($target_student) == 0) {
  echo "<div>No user found matching above details.</div>\n";
} else {
  echo "<br /><div style=\"color:#0033BC\">Reassign answers/marks from " . str_replace('user','Temporary Account ',$temp_username) . " to following user:</div>\n<div style=\"height:300px; border:1px solid #7F9DB9; overflow-y:scroll\">\n";
  foreach ($target_student as $individualID=>$individual) {
    if ($individual['title'] == 'Mr') {
      $user_icon = 'user_male_64.png';
    } elseif ($individual['title'] == 'Dr') {
      if ($individual['gender'] == 'female') {
        $user_icon = 'user_female_64.png';
      } else {
        $user_icon = 'user_male_64.png';
      }
    } else {
      $user_icon = 'user_female_64.png';
    }
    echo "<div style=\"border:1px solid white; cursor:hand\" onclick=\"doReassign($individualID)\" onmouseover=\"lon($individualID)\" onmouseout=\"loff($individualID)\" id=\"$individualID\"><table border=\"0\"><tr><td><img src=\"../artwork/$user_icon\" width=\"64\" height=\"64\" alt=\"user\" border=\"0\" /></td><td>" . $individual['title'] . " " . $individual['surname'] . ", <span style=\"color:#808080\">" . $individual['first_names'] . "</span><br />(" . $individual['student_id'] . ")<br />";
    echo implode(', ',$individual['modules']);
    echo "</td></tr></table></div>";
  }
  echo "</div>\n";
}
?>
<br />
<div style="text-align:center"><input type="button" name="cancel" value="Cancel" onclick="window.close()" style="width:100px" /></div>

</body>
</html>