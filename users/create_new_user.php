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
* @author Simon Wilkinson / Richard Whitefoot (UEA)
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/admin_auth.inc';
require_once '../include/mb_string.inc.php';
require '../include/toprightmenu.inc';

$render = new render($configObject);
$lang['title'] = $string['createnewuser'];
$additionaljs = "<script type=\"text/javascript\" src=\"../js/jquery.validate.min.js\"></script>";
$additionaljs .= "<script type=\"text/javascript\" src=\"../js/jquery.user.js\"></script>";
$additionaljs .= "<script type=\"text/javascript\" src=\"../js/jquery.create_new_user.js\"></script>";

$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/dialog.css\" />
        <link rel=\"stylesheet\" type=\"text/css\" href=\"/css/list.css\" />
        <style type=\"text/css\">
          .dialog_table {background-color:#F1F5FB; border: 1px solid #95AEC8; margin-top:40px; margin-left:auto; margin-right:auto}
          .field {text-align:right; padding-right:6px; width:120px}
        </style>";

$breadcrumb = array($string['home'] => "../index.php");
$action = $_SERVER['PHP_SELF'];
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
include '../include/user_search_options.php';
$render->render_admin_content($breadcrumb, $lang);
echo '<body>';
echo draw_toprightmenu();
$submit = (bool) param::optional('submit', null, param::TEXT, param::FETCH_POST);
$unique_username = false;

if ($submit) {

  $new_password = trim(check_var('new_password', 'POST', true, false, true, param::TEXT));
  $new_surname = UserUtils::my_ucwords(trim(check_var('new_surname', 'POST', true, false, true, param::TEXT)));
  $new_username = trim(check_var('new_username', 'POST', true, false, true, param::TEXT));
  $new_email = trim(check_var('new_email', 'POST', true, false, true, param::EMAIL));
  $new_first_names = UserUtils::my_ucwords(trim(check_var('new_first_names', 'POST', true, false, true, param::TEXT)));
  $new_grade = check_var('new_grade', 'POST', false, false, true, param::TEXT);
  $new_year = check_var('new_year', 'POST', true, false, true, param::INT);
  $new_roles = check_var('new_roles', 'POST', false, false, true, param::TEXT);
  $new_sid = check_var('new_sid', 'POST', false, false, true, param::ALPHANUM);
  $new_users_title = check_var('new_users_title', 'POST', true, false, true, param::ALPHANUM);
  $new_gender = check_var('new_gender', 'POST', false, false, true, param::ALPHANUM);
  $new_welcome = check_var('new_welcome', 'POST', false, false, true, param::BOOLEAN);

  // Check for valid and unique username
  $unique_username = UserUtils::username_is_valid($new_username) && !UserUtils::username_exists($new_username, $mysqli);
}

if ($submit and $unique_username) {
  if ($new_username == '' or strpos($new_username, '_') !== false or $new_surname == '' or $new_email == '' or $new_first_names == '' or $new_roles == '' or $new_grade == '') {
    $problem = true;
  } else {
    $new_userID = UserUtils::create_user($new_username, $new_password, $new_users_title, $new_first_names, $new_surname, $new_email, $new_grade, $new_gender, $new_year, $new_roles, $new_sid, $mysqli);

    // Send out email welcome.
    if (isset($new_welcome) and $new_welcome != '') {
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
      $sname = ucwords($new_surname);
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
<p>{$string['dear']} {$new_users_title} {$sname},</p>
<p>{$string['email1']}</p>
<p>{$string['username']}: {$new_username}<br />
{$string['password']}: {$new_password}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">{$string['casesensitive']}</span></p>
MESSAGE;

      if (strpos($new_roles,'Staff') !== false) {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/staff/</a></p>";
      } elseif (strpos($new_roles,'Student') !== false) {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/students/</a></p>";
      } else {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
        $message .= "<p>" . $string['email3'] . "</p>";
      }
      $message .= "</body>\n</html>";
      mail ($new_email, $subject, $message, $headers) or print "<p>" . $string['couldnotsend'] . " <strong>" . $new_email . "</strong>.</p>";
    }
?>
<p>&nbsp;<?php echo $string['newaccountcreated'] . ' ' . $new_users_title . ' ' . $new_surname ?>.</p>
<div>&nbsp;<input type="button" name="gotouser" value="View Account" class="ok" onclick="window.location='details.php?userID=<?php echo $new_userID ?>'" /></div>
<?php
    }
  }
  if (!$submit or !$unique_username) {
?>

<form method="post" id="theform" name="newUser" action="<?php echo $action; ?>" autocomplete="off">
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
<select id="new_users_title" name="new_users_title" size="1" required>

<?php
if ($language != 'en') {
  echo "<option label=\"\"></option>\n";
}
$titles = explode(',', $string['title_types']);
foreach ($titles as $tmp_title) {
  echo "<option value=\"$tmp_title\">$tmp_title</option>";
}
?>
</select></td></tr>
<tr><td class="field"><?php echo $string['firstnames'] ?></td><td><input<?php if ($submit and (!isset($new_first_names) or $new_first_names == '')) echo ' class="required"'; ?> type="text" id="new_first_names" name="new_first_names" size="40" maxlength="60" value="<?php if (isset($new_first_names)) echo $new_first_names; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['lastname'] ?></td><td><input<?php if (isset($new_surname) and $new_surname == '') echo ' class="required"'; ?> type="text" id="new_surname" name="new_surname" size="40" maxlength="35" value="<?php if (isset($new_surname)) echo $new_surname; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['studentid'] ?></td><td><input id="new_studentid" type="text" size="15" name="new_sid" /><span style="color:#808080"><?php echo $string['onlyifstudent']; ?></span></td></tr>
<tr><td class="field"><?php echo $string['email'] ?></td><td><input<?php if (isset($new_email) and $new_email == '') echo ' class="required"'; ?> type="email" id="new_email" name="new_email" size="40" maxlength="65" value="<?php if (isset($new_email)) echo $new_email; ?>" required /></td></tr>
<tr><td class="field"><?php echo $string['username'] ?></td><td><input<?php if (isset($new_username) and ($new_username == '' or strpos($new_username, '_') !== false or !$unique_username)) echo ' class="required"'; ?> type="text" id="new_username" name="new_username" size="12" maxlength="15" value="<?php if (isset($new_username)) echo $new_username; ?>" autocomplete="off" required />
&nbsp;&nbsp;&nbsp;<?php echo $string['password'] ?> <input type="text" id="new_password" name="new_password" value="<?php
  if (isset($new_password)) {
    echo $new_password;
  } else {
    $enc = new encryp();
    $generated_password = $enc->gen_password(true);
    echo $generated_password['password'];
  }
?>" size="12" autocomplete="off" required /></td></tr>
<tr><td class="field"><?php echo $string['yearofstudy'] ?></td><td>
<select id="new_yos" name="new_year" required>
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
<tr>
<td class="field"><?php echo $string['gender'] ?></td><td>
<select id="new_gender" name="new_gender" size="1">
<option label=" "></option>
<option value="Male"<?php if (isset($new_gender) and $new_gender == 'Male') echo ' selected' ?>><?php echo $string['male'] ?></option>
<option value="Female"<?php if (isset($new_gender) and $new_gender == 'Female') echo ' selected' ?>><?php echo $string['female'] ?></option>
<option value="Other"<?php if (isset($new_gender) and $new_gender == 'Other') echo ' selected' ?>><?php echo $string['other'] ?></option>
</select>
</td>
</tr>
<tr><td class="field"><?php echo $string['status'] ?></td><td>
<?php
  echo "<select name=\"new_roles\" id=\"new_roles\" class=\"required\" required>";
  echo "<option label=\"\" value=\"\"> </option>";

  $old_optgroup = '';

  $roles_array = array('#Staff', 'Staff');
  if ($userObject->has_role('SysAdmin')) {
    $roles_array[] = 'Staff,Admin';
    $roles_array[] = 'Staff,SysAdmin';
  } elseif ($userObject->has_role('Admin')) {
    $roles_array[] = 'Staff,Admin';
  }
  $roles_array[] = 'External Examiner';
  $roles_array[] = 'Internal Reviewer';
  $roles_array[] = 'Staff,Standards Setter';
  $roles_array[] = 'Invigilator';
  $roles_array[] = '#Students';
  $roles_array[] = 'Student';
  $roles_array[] = 'Staff,Student';

  foreach ($roles_array as $value) {
    if (substr($value,0,1) == '#') {
      $parentRole = substr($value,1);
      echo "<optgroup label=\"" . $string[$parentRole] . "\">\n";
    } else {
      $display_val = str_replace(' ', '', $value);
      $display_val = str_replace(',', '', $display_val);
      $display_val = $string[strtolower($display_val)];
      echo "<option value=\"$value\" data-parent=\"$parentRole\">$display_val</option>";
    }

    if (substr($value,0,1) == '#') {
      $old_optgroup = $value;
      if($old_optgroup != $value) {
        echo "</optgroup>\n";
      }
    }

  }
  echo "</optgroup>\n</select>\n";
?>
</td></tr>
<tr><td class="field" id="typecourse"><?php echo $string['typecourse']; ?></td><td>
<select name="new_grade" id="new_grade" size="1" style="width:350px" data-prev-parent="" required>
<?php  
  echo "<option label=\"\" value=\"\"> </option>";
  
  $old_school = '';
  $result = $mysqli->prepare("SELECT DISTINCT c.name, c.description, s.school FROM courses c INNER JOIN schools s ON c.schoolid=s.id WHERE s.school NOT IN ('university','NHS','N/A') ORDER BY s.school, c.name");
  $result->execute();
  $result->bind_result($name, $description, $school);
  while ($result->fetch()) {
    if ($old_school != $school) {
      echo "<optgroup data-role=\"Students\" label=\"$school\">\n";
    }
    
    echo "<option value=\"$name\">$name: $description</option>\n";
    
    $old_school = $school;
    
    if ($old_school != $school) {
      echo "</optgroup>";
    }
  }
  $result->close();
  
  echo "\n";
?>
<optgroup data-role="Staff" label="<?php echo $string['universitystaff']; ?>">
<option value="University Lecturer"><?php echo $string['academiclecturer'] ?></option>
<option value="University Admin"><?php echo $string['administrator'] ?></option>
<option value="Technical Staff"><?php echo $string['ittechnical'] ?></option>
<option value="Standards Setter"><?php echo $string['standardssetter'] ?></option>
<option value="Staff Internal Reviewer"><?php echo $string['internalreviewer'] ?></option>
</optgroup>
<optgroup data-role="Staff" label="<?php echo $string['externalstaff'] ?>">
<?php
if (strpos($_SERVER['HTTP_HOST'],'.uk') !== false) {
  echo "<option value=\"NHS Lecturer\">" . $string['nhslecturer'] . "</option>\n";
  echo "<option value=\"NHS Admin\">" . $string['nhsadmin'] . "</option>\n";
}
?>
<option value="Staff External Examiner"><?php echo $string['externalexaminer'] ?></option>
<option value="Invigilator"><?php echo $string['invigilator'] ?></option>
</optgroup>
</select></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="new_welcome" value="1" /><?php echo $string['sendwelcomeemail'] ?></td></tr>
<tr><td colspan="2" style="text-align:center; padding-bottom:12px">
<input type="submit" name="submit" value="<?php echo $string['createaccount'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="history.back();" /></td></tr>
</table>
</form>
<?php
  }
$mysqli->close();

if ($submit and !$unique_username) {
  echo '<script>alert("' . sprintf($string['usernameinuse'], $new_username) . '")</script>';
}

$render->render_admin_footer();
?>