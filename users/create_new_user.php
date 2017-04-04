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
* Creates a new user (staff or student).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/admin_auth.inc';
require_once '../include/mb_string.inc.php';

$unique_username = true;
$problem = false;

if (isset($_POST['submit'])) {
  // Check for unique username
  if (UserUtils::username_exists($_POST['new_username'], $mysqli) !== false) {
    $unique_username = false;
    $problem = true;
  }

  switch($_POST['new_grade']) {
    case 'University Lecturer':
    case 'University Admin':
    case 'Technical Staff':
    case 'NHS Lecturer':
    case 'NHS Admin':
    case 'Standards Setter':
      $tmp_roles = 'Staff';
      break;
    case 'Invigilator':
      $tmp_roles = 'Invigilator';
      break;
    case 'Staff External Examiner':
      $tmp_roles = 'External Examiner';
      break;
    case 'Staff Internal Reviewer':
      $tmp_roles = 'Internal Reviewer';
      break;
    default:
      $tmp_roles = 'Student';
      break;
  }

  $new_password = trim($_POST['new_password']);
  $new_surname = UserUtils::my_ucwords(trim($_POST['new_surname']));
  $new_username = trim($_POST['new_username']);
  $new_email = trim($_POST['new_email']);
  $new_first_names = UserUtils::my_ucwords(trim($_POST['new_first_names']));
  $new_grade = $_POST['new_grade'];
	$new_year = (isset($_POST['new_year']) ? $_POST['new_year'] : 1);
}

if (isset($_POST['submit']) and $unique_username == true) {
  if ($new_username == '' or strpos($new_username, '_') !== false or $new_surname == '' or $new_email == '' or $new_first_names == '' or $new_grade == '') {
    $problem = true;
  } else {
    $new_userID = UserUtils::create_user($new_username, $new_password, $_POST['new_users_title'], $new_first_names, $new_surname, $new_email, $new_grade, $_POST['new_gender'], $new_year, $tmp_roles, $_POST['new_sid'], $mysqli);

    // Send out email welcome.
    if (isset($_POST['new_welcome']) and $_POST['new_welcome'] != '') {
      $result = $mysqli->prepare("SELECT email FROM users WHERE username = ?");
      $result->bind_param('s', $userObject->get_username());
      $result->execute();
      $result->bind_result($tmp_email);
      $result->fetch();
      $result->close();

      $subject = "{$string['newrogoaccount']}";
      $headers = "From: $tmp_email\n";
      $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\n";
      $headers .= "bcc: $tmp_email\n";
      $sname = ucwords($_POST['new_surname']);
      $message = <<< MESSAGE
<!DOCTYPE html>
<html>
<head>
<title>{$string['rogoaccount']}</title>
<style type="text/css">
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:90%}
h1 {font-size:140%}
h2 {font-size:120%}
</style>
</head>
<body>
<p>{$string['dear']} {$_POST['new_users_title']} {$sname},</p>
<p>{$string['email1']}</p>
<p>{$string['username']}: {$_POST['new_username']}<br />
{$string['password']}: {$_POST['new_password']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">{$string['casesensitive']}</span></p>
MESSAGE;

      if (strpos($tmp_roles,'Staff') !== false) {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/staff/</a></p>";
      } elseif (strpos($tmp_roles,'Student') !== false) {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/students/</a></p>";
      } else {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
        $message .= "<p>" . $string['email3'] . "</p>";
      }
      $message .= "</body>\n</html>";
      mail ($new_email, $subject, $message, $headers) or print "<p>" . $string['couldnotsend'] . " <strong>" . $new_email . "</strong>.</p>";
    }
    ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['createnewuser'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';
  include '../include/user_search_options.php';
  
	echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
  <div class="page_title"><?php echo $string['createnewuser'] ?></div>
</div>

<p>&nbsp;<?php echo $string['newaccountcreated'] . ' ' . $_POST['new_users_title'] . ' ' . $_POST['new_surname'] ?>.</p>
<div>&nbsp;<input type="button" name="gotouser" value="View Account" class="ok" onclick="window.location='details.php?userID=<?php echo $new_userID ?>'" /></div>

</div>
      <?php
    }
  }
  if (!isset($_POST['submit']) or $problem) {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo "{$string['createnewuser']} {$configObject->get('cfg_install_type')}" ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style>
    .dialog_table {background-color:#F1F5FB; border: 1px solid #95AEC8; margin-top:40px; margin-left:auto; margin-right:auto}
    .field {text-align:right; padding-right:6px; width:120px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
      
      $('#ldaplookup').click(function() {
        notice = window.open("ldaplookup.php","ldap","width=650,height=300,left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        notice.moveTo(screen.width/2-325, screen.height/2-150);
        if (window.focus) {
          notice.focus();
        }        
      });
    });
  </script>
</head>

<body>
<?php
  require '../include/user_search_options.php';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">

  <div class="head_title">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
    <div class="page_title"><?php echo $string['createnewuser'] ?></div>
  </div>

<form method="post" id="theform" name="newUser" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">

<table border="0" cellspacing="0" cellpadding="3" class="dialog_table">
<tr><td class="dialog_header" style="border-bottom: 1px solid #95AEC8; line-height:170%" colspan="2"><img src="../artwork/user_female_32.png" width="32" height="32" alt="User Icon" style="float:left; padding-right:8px" /><?php echo $string['createnewuser'] ?></td></tr>
<?php
  $authinfo = $authentication->version_info();
  $ldap_enabled = false;
  foreach ($authinfo->plugins as $p) {
    if ($p->name == 'LDAP') {
      $ldap_enabled = true;
      break;
    }
  }
  if ($ldap_enabled == true) {
    echo '<tr><td colspan="2"><input type="button" name="lookup" id="ldaplookup" value="' . $string['getldapdetails'] . '" /></td></tr>';
  }
?>
<tr><td class="field"><?php echo $string['title'] ?></td><td>
<select id="new_users_title" name="new_users_title" size="1">

<?php
if ($language != 'en') {
  echo "<option value=\"\"></option>\n";
}
$titles = explode(',', $string['title_types']);
foreach ($titles as $tmp_title) {
  echo "<option value=\"$tmp_title\">$tmp_title</option>";
}
?>
</select></td></tr>
<tr><td class="field"><?php echo $string['firstnames'] ?></td><td><input<?php if (isset($_POST['submit']) and (!isset($new_first_names) or $new_first_names == '')) echo ' class="required"'; ?> type="text" id="new_first_names" name="new_first_names" size="40" maxlength="60" value="<?php if (isset($new_first_names)) echo $new_first_names; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['lastname'] ?></td><td><input<?php if (isset($new_surname) and $new_surname == '') echo ' class="required"'; ?> type="text" id="new_surname" name="new_surname" size="40" maxlength="35" value="<?php if (isset($new_surname)) echo $new_surname; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['email'] ?></td><td><input<?php if (isset($new_email) and $new_email == '') echo ' class="required"'; ?> type="email" id="new_email" name="new_email" size="40" maxlength="65" value="<?php if (isset($new_email)) echo $new_email; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['username'] ?></td><td><input<?php if (isset($new_username) and ($new_username == '' or strpos($new_username, '_') !== false or !$unique_username)) echo ' class="required"'; ?> type="text" id="new_username" name="new_username" size="12" maxlength="15" value="<?php if (isset($new_username)) echo $new_username; ?>" autocomplete="off" required />
&nbsp;&nbsp;&nbsp;<?php echo $string['password'] ?> <input type="text" id="new_password" name="new_password" value="<?php
  if (isset($_POST['password'])) {
    echo $_POST['password'];
  } else {
    echo gen_password();
  }
?>" size="12" autocomplete="off" required /></td></tr>
<tr><td class="field"><?php echo $string['yearofstudy'] ?></td><td>
<select id="new_yos" name="new_year">
<?php
  for ($tmp_year=1; $tmp_year<=6; $tmp_year++) {
    if ($tmp_year == 1) {
      echo "<option value=\"$tmp_year\" selected>$tmp_year</option>\n";
    } else {
      echo "<option value=\"$tmp_year\">$tmp_year</option>\n";
    }
  }
?>
</select>
</td></tr>
<tr><td class="field"><?php echo $string['typecourse'] ?></td><td>
<select name="new_grade" id="new_grade" size="1" style="width:350px"<?php if (isset($new_grade) and $new_grade == '') echo ' class="required"' ?> required>
<option value=""></option>
<optgroup label="<?php echo $string['universitystaff']; ?>">
<option value="University Lecturer"><?php echo $string['academiclecturer'] ?></option>
<option value="University Admin"><?php echo $string['administrator'] ?></option>
<option value="Technical Staff"><?php echo $string['ittechnical'] ?></option>
<option value="Standards Setter"><?php echo $string['standardssetter'] ?></option>
<option value="Staff Internal Reviewer"><?php echo $string['internalreviewer'] ?></option>
</optgroup>
<optgroup label="<?php echo $string['externalstaff'] ?>">
<?php
if (strpos($_SERVER['HTTP_HOST'],'.uk') !== false) {
  echo "<option value=\"NHS Lecturer\">" . $string['nhslecturer'] . "</option>\n";
  echo "<option value=\"NHS Admin\">" . $string['nhsadmin'] . "</option>\n";
}
?>
<option value="Staff External Examiner"><?php echo $string['externalexaminer'] ?></option>
<option value="Invigilator"><?php echo $string['invigilator'] ?></option>
<?php
  $old_school = '';
  $result = $mysqli->prepare("SELECT DISTINCT c.name, c.description, s.school FROM courses c INNER JOIN schools s ON c.schoolid=s.id WHERE s.school NOT IN ('university','NHS','N/A') ORDER BY s.school, c.name");
  $result->execute();
  $result->bind_result($name, $description, $school);
  while ($result->fetch()) {
    if ($old_school != $school) {
      echo "</optgroup>\n<optgroup label=\"" . $string['students'] . " - $school\">\n";
    }
    echo "<option value=\"$name\">$name: $description</option>\n";
    $old_school = $school;
  }
  $result->close();
?>
</optgroup>
</select>
</td></tr>

<tr>
<td class="field"><?php echo $string['gender'] ?></td><td>
<select id="new_gender" name="new_gender" size="1">
<option value=""></option>
<option value="Male"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Male') echo ' selected' ?>><?php echo $string['male'] ?></option>
<option value="Female"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Female') echo ' selected' ?>><?php echo $string['female'] ?></option>
<option value="Other"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Other') echo ' selected' ?>><?php echo $string['other'] ?></option>
</select>
</td>
</tr>
<tr><td class="field"><?php echo $string['studentid'] ?></td><td><input id="new_studentid" type="text" size="15" name="new_sid" /></td></tr>
<tr><td class="field"&nbsp;</td><td style="color:#808080"><?php echo $string['onlyifstudent'] ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="new_welcome" value="1" /><?php echo $string['sendwelcomeemail'] ?></td></tr>
<tr><td colspan="2" style="text-align:center; padding-bottom:12px">
<input type="submit" name="submit" value="<?php echo $string['createaccount'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="history.back();" /></td></tr>
</table>

</form>

<?php
  }
  $mysqli->close();

  if ($unique_username != true) {
    echo '<script>alert("' . sprintf($string['usernameinuse'], $_POST['new_username']) . '")</script>';
  }
?>
</div>

</body>
</html>
