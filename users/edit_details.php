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
* Shows information on the currently selected user: name, username, email, etc
* plus the details of any taken assessment or survey. SysAdmin users also have the ability
* to edit personal details such as name, username, password, etc.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/sysadmin_auth.inc';
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';

$userID = check_var('userID', 'GET', true, false, true);

$errors = false;

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['submit']) and $_POST['username'] != $_POST['prev_username']) {
  // Check new username is not already used. Overwriting usernames could screw up other accounts.
  if (UserUtils::username_exists($_POST['username'], $mysqli)) {
    $errors = 'Username exists';
  }
}  


if (isset($_POST['submit']) and !$errors) {
  $cfg_web_root = $configObject->get('cfg_web_root');

  if (!empty($_FILES['photofile']['name'])) {
    $photodirectory = rogo_directory::get_directory('user_photo');
    // First check if the user has an image already, if they do delete it.
    // This stops an image type of a different type blocking the display of the uploaded image.
    $student_photo = UserUtils::student_photo_exist($_POST['username']);
    if ($student_photo !== false) {
      unlink($photodirectory->fullpath($student_photo));
    }
    // Add the file.
    $filename = $_FILES['photofile']['name'];
    $explode = explode('.', $filename);
    $count = count($explode) - 1;
    // Ensure the file extenstion is lower case or it will not load on some Operating systems.
    $file_ext = strtolower($explode[$count]);

    if (!move_uploaded_file($_FILES['photofile']['tmp_name'],  $photodirectory->fullpath($_POST['username'] . '.' . $file_ext))) {
      log_error($userObject->get_user_ID(), 'Edit User', 'Application Error', 'Error uploading user photo - error: ' . $_FILES['photofile']['error'], $_SERVER['PHP_SELF'], 49, '', null, null, null);
    }
  }
  
  $initials = '';
  $first_names_array = explode(' ', $_POST['first_names']);
  foreach ($first_names_array as $individual_name) {
    $initials .= trim(substr($individual_name,0,1));
  }
  // Update 'users' table.
  $tmp_roles = $_POST['roles'];
  
	$gender = $_POST['gender'];
	if ($gender == '') $gender = NULL;

  $result = $mysqli->prepare("UPDATE users SET roles = ?, title = ?, initials = ?, surname = ?, grade = ?, yearofstudy = ?, username = ?, email = ?, first_names = ?, gender = ? WHERE id = ?");
  $result->bind_param('sssssissssi', $tmp_roles, $_POST['title'], $initials, $_POST['surname'], $_POST['grade'], $_POST['year'], $_POST['username'], $_POST['email'], $_POST['first_names'], $gender, $userID);
  $result->execute();
  $result->close();
  
  // Remove from teams if 'left'.
  if (strtolower($tmp_roles) == 'left') {
    UserUtils::clear_staff_modules_by_userID($userID, $mysqli);
  }

  // Remove from admin access if role changed from Admin
  if ($userObject->has_role('SysAdmin')) {
    if ($tmp_roles != $_POST['prev_roles'] and $_POST['prev_roles'] == 'Staff,Admin') {
      UserUtils::clear_admin_access($userID, $mysqli);
    }
  }

  // Update 'sid' table;
  $result = $mysqli->prepare("DELETE FROM sid WHERE userID = ?");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->close();

  if (isset($_POST['sid']) and $_POST['sid'] != '' and $_POST['sid'] != $string['unknown']) {
    $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
    $result->bind_param('si', $_POST['sid'], $userID);
    $result->execute();
    $result->close();
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $string['usermanagement'] ?></title>
    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script>
      $(function () {
        window.opener.location = window.opener.parent.location.href.replace('&tab=admin', '');
        window.opener.parent.location.reload(true);
        window.close();
      });
    </script>
  </head>
  <body>
    
  </body>
</html>
<?php
}

if ($user_details['gender'] == 'Male') {
  $generic_icon = '../artwork/user_male_48.png';
} elseif ($user_details['gender'] == 'Female') {
  $generic_icon = '../artwork/user_female_48.png';
} else {
  $generic_icon = '../artwork/user_mx_48.png';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['usermanagement'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#EEF4FF}
    .form-error {border: 2px solid #C00000 !important}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
</head>

<body>
  <table cellspacing="0" cellpadding="5" border="0" style="width:100%; background-color:white">
    <tr><td style="border-bottom:1px solid #CCD9EA; width:50px"><img src="<?php echo $generic_icon ?>" width="48" height="48" /></td><td class="dkblue_header" style="background-color:white; font-size:160%; border-bottom:1px solid #CCD9EA; font-weight:bold"><?php echo $string['edituserdetails'] ?></td></tr>
  </table>
  <br />
  
  <form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>?userID=<?php echo $userID ?>" method="post" enctype="multipart/form-data" autocomplete="off">
  <table cellspacing="0" cellpadding="2" border="0" style="width:100%; border:12px solid #EEF4FF">
<?php
  echo "<tr><td>" . $string['name'] . "</td><td>";
  $title_array = explode(',', $string['title_types']);
  echo '<select name="title">';
  foreach ($title_array as $individual_title) {
    if ($individual_title == $user_details['title']) {
      echo '<option value="' . $individual_title . '" selected>' . $individual_title . '</option>';
    } else {
      echo '<option value="' . $individual_title . '">' . $individual_title . '</option>';
    }
  }
  echo "</select> <input type=\"text\" value=\"" . $user_details['first_names'] . "\" name=\"first_names\" required /> <input type=\"text\" value=\"" . $user_details['surname'] . "\" name=\"surname\" required /></td></tr>\n";
  echo "<tr><td>" . $string['studentid'] . "</td><td><input type=\"text\" value=\"" . $user_details['student_id'] . "\" name=\"sid\" /></td></tr>\n";
  if ($errors == 'Username exists') {
    echo "<tr><td>" . $string['username'] . "</td><td><input type=\"text\" value=\"" . $_POST['username'] . "\" name=\"username\" class=\"form-error\" required />&nbsp;&nbsp;<span style=\"color:#C00000\">" . $string['usernameexists'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td>" . $string['username'] . "</td><td><input type=\"text\" value=\"" . $user_details['username'] . "\" name=\"username\" required /></td></tr>\n";
  }
  echo "<tr><td>" . $string['email'] . "</td><td><input type=\"text\" value=\"" . $user_details['email'] . "\" name=\"email\" style=\"width:340px\" required /></td></tr>\n";
  echo "<tr><td>" . $string['course'] . "</td><td><select name=\"grade\" style=\"width:300px\">";
  $found = 0;
  $course_details = $mysqli->prepare("SELECT DISTINCT name, description FROM courses ORDER BY name");
  $course_details->execute();
  $course_details->bind_result($name, $description);
  while ($course_details->fetch()) {
    if ($name == $user_details['grade']) {
      $found = 1;
      echo "<option value=\"$name\" selected>$name: $description</option>\n";
    } else {
      echo "<option value=\"$name\">$name: $description</option>\n";
    }
  }
  if ($found == 0) echo "<option value=\"" . $user_details['grade'] . "\" selected>" . $user_details['grade'] . ": " . $string['unknown'] . "</option>\n";
  $course_details->close();
  
  echo "</select></td></tr>\n";
  echo "<tr><td>" . $string['yearofstudy'] . "</td><td><select name=\"year\">";
  for ($i=1; $i<=6; $i++) {
    if ($i == $user_details['yearofstudy']) {
      echo "<option value=\"$i\" selected>$i</option>";
    } else {
      echo "<option value=\"$i\">$i</option>";
    }
  }
  echo "</select></td></tr>";
  
  echo "<tr><td>" . $string['gender'] . "</td><td><select name=\"gender\">";
  if ($user_details['gender'] == 'Male') {
    echo "<option value=\"Male\" selected>" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . "</option>";
  } elseif ($user_details['gender'] == 'Female') {
    echo "<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\" selected>" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . "</option>";
  } elseif ($user_details['gender'] == 'Other') {
    echo "<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\" selected>" . $string['other'] . "</option>";
  } else {
    echo "<option value=\"\"></option>\n<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . "</option>\n";
  }  
  echo "</select></td></tr>";

  echo "<tr><td>" . $string['status'] . "</td><td><select name=\"roles\">";
  $old_optgroup = '';

  $roles_array = array('#Staff', 'Staff');
  if ($userObject->has_role('SysAdmin')) {
    $roles_array[] = 'Staff,Admin';
    $roles_array[] = 'Staff,SysAdmin';
  } elseif ($userObject->has_role('Admin')) {
    $roles_array[] = 'Staff,Admin';
  }
  $roles_array[] = 'Staff,Student';
  $roles_array[] = 'External Examiner';
  $roles_array[] = 'Internal Reviewer';
  $roles_array[] = 'Staff,Standards Setter';
  $roles_array[] = 'Invigilator';
  $roles_array[] = 'Inactive Staff';
  $roles_array[] = '#Students';
  $roles_array[] = 'Student';
  $roles_array[] = 'Graduate';
  $roles_array[] = 'Left';
  $roles_array[] = 'Suspended';
  $roles_array[] = 'Locked';

  foreach ($roles_array as $value) {
    if (substr($value,0,1) == '#') {
      if ($old_optgroup != '') echo "</optgroup>\n";
      echo "<optgroup label=\"" . $string[substr($value,1)] . "\">\n";
      $old_optgroup = $value;
    } else {
      $display_val = str_replace(' ', '', $value);
      $display_val = str_replace(',', '', $display_val);
      $display_val = $string[strtolower($display_val)];
      if (strtolower($value) == strtolower($user_details['roles'])) {
        echo "<option value=\"$value\" selected>$display_val</option>";
      } else {
        echo "<option value=\"$value\">$display_val</option>";
      }
    }
  }
  echo "</optgroup>\n</select>\n";
  echo "<input type=\"hidden\" name=\"prev_roles\" value=\"" . $user_details['roles'] . "\" /></td></tr>\n";
  echo "<input type=\"hidden\" name=\"prev_username\" value=\"" . $user_details['username'] . "\" /></td></tr>\n";
  echo "<tr><td>" . $string['photo'] . "</td><td><input type=\"file\" name=\"photofile\" /></td></tr>";
  ?>
  </table>

    <div style="margin-top:24px; text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="window.close()" /></div>
  </form>
</body>
</html>
