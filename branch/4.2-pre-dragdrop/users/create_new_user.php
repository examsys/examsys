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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';
  require '../include/mb_string.inc.php';

  function my_ucwords($s) {
    $s = preg_replace_callback("/(?:^|-|\pZ|')([\pL]+)/su", 'fixcase_callback', $s);
    return $s;         
  } 
    
  function fixcase_callback($word) { 
    $word = $word[1]; 
    $word = mb_strtolower($word, 'UTF-8');
        
    if ($word == "de") return $word; 
    
    $word = mb_ucasefirst($word);
       
    if (mb_substr($word, 1, 1, 'UTF-8') == "'") {
      if (mb_substr($word, 0, 1, 'UTF-8') == "D") {
        $word = mb_strtolower($word, 'UTF-8');
      } 
      $next = mb_substr($word, 2, 1, 'UTF-8');
      $next = mb_strtoupper($next, 'UTF-8');
      $word = mb_substr_replace($word, $next, 2, 1, 'UTF-8');
    }
    return $word; 
  } 

  $unique_username = true;
  if (isset($_POST['submit'])) {
    // Check for unique username
    $result = $mysqli->prepare("SELECT username FROM users WHERE username=?");
    $result->bind_param('s', $_POST['new_username']);
    $result->execute();
    $result->store_result();
    $result->bind_result($tmp_username);
    $result->fetch();
    if ($result->num_rows > 0) $unique_username = false;
    $result->free_result();
    $result->close();
  }
  
  if (isset($_POST['submit']) and $unique_username == true) {
    switch($_POST['new_grade']) {
      case 'University Lecturer':
      case 'University Admin':
      case 'Technical Staff':
      case 'NHS Lecturer':
      case 'NHS Admin':
        $tmp_roles = 'Staff';
        break;
      case 'Invigilator':
        $tmp_roles = 'Invigilator';
        break;
      case 'Staff External Examiner':
        $tmp_roles = 'External Examiner';
        break;
      default:
        $tmp_roles = 'Student';
        break;
    }
  
    $initials = '';
    $first_names_array = explode(' ',$_POST['new_first_names']);
    foreach ($first_names_array as $individual_name) {
      $initials .= trim(substr($individual_name,0,1));
    }
    $initials = strtoupper($initials);
  
    //TODO this function has been moved to passwordutils.class.php
    $new_password = encpw($_POST['new_username'],trim($_POST['new_password']));
    $new_surname = my_ucwords(trim($_POST['new_surname']));
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);
    $new_first_names = my_ucwords(trim($_POST['new_first_names']));
  
    $result = $mysqli->prepare("INSERT INTO users VALUES (?,?,?,?,?,?,?,?,NULL,?,?,NULL,0,?)");
    $result->bind_param('ssssssssssi', $new_password, $_POST['new_grade'], $new_surname , $initials, $_POST['new_users_title'], $new_username, $new_email, $tmp_roles, $new_first_names, $_POST['new_gender'], $_POST['new_year']);
    $result->execute();  
    $result->close();
    $userid = $mysqli->insert_id;
    $to = trim($_POST['new_email']);
    
    if ($_POST['new_sid'] != '') {
      $result = $mysqli->prepare("INSERT INTO sid VALUES (?,?)");
      $result->bind_param('ss', $_POST['new_sid'], $userid);
      $result->execute();  
      $result->close();
    }
    
    // Send out email welcome.
    if (isset($_POST['new_welcome']) and $_POST['new_welcome'] != '') {
      $result = $mysqli->query("SELECT email FROM users WHERE username='" . $_SERVER['PHP_AUTH_USER'] . "'");
      $row = $result->fetch_assoc();
      $result->close();
      $subject = "New Rogo account";
      $headers = "From: " . $row['email'] . "\n";
      $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
      $headers .= "bcc: " . $row['email'] . "\n";
      $sname = ucwords($_POST['new_surname']);
      $message = <<< MESSAGE
<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>{$string['rogoaccount']}</title>
<style>
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:90%}
h1 {font-size:140%}
h2 {font-size:120%}
</style>
</head>
<body>
<p>Dear {$_POST['new_users_title']} {$sname},</p>
<p>{$string['email1']}</p>
<p>{$string['username']}: {$_POST['new_username']}<br />
{$string['password']}: {$_POST['new_password']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">{$string['casesensitive']}</span></p>"
MESSAGE;

      if (strpos($tmp_roles,'Staff') !== false) {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
      } elseif (strpos($tmp_roles,'Student') !== false) {
      } else {
        $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
        $message .= "<p>" . $string['email3'] . "</p>";
      }
      $message .= "</body>\n</html>";
      mail ($to, $subject, $message, $headers) or print "<p>" . $string['couldnotsend'] . " <strong>" . $_POST['new_email'] . "</strong>.</p>";
    }
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rogō: <?php echo "{$string['createnewuser']} $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
</head>
<body>
<?php
  include '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<p><?php echo $string['newaccountcreated'] . ' ' . $_POST['new_users_title'] . ' ' . $_POST['new_surname']; ?>.</p>
</div>
    <?php
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Rogō: <?php echo "{$string['createnewuser']} $cfg_install_type"; ?></title>

<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
.title {font-size:160%; font-weight:bold}
.field {font-weight:bold}
</style>

<script language="JavaScript">
function checkForm() {
  if (document.newUser.new_first_names.value == "") {
    alert("<?php echo $string['reqfirstname'] ?>");
    return false;
  }
  if (document.newUser.new_surname.value == "") {
    alert("<?php echo $string['reqsurname'] ?>");
    return false;
  }
  if (document.newUser.new_email.value == "" || document.newUser.new_email.value == "@nottingham.ac.uk") {
    alert("<?php echo $string['reqemail'] ?>");
    return false;
  }
  if (document.newUser.new_grade.options[document.newUser.new_grade.selectedIndex].value == "") {
    alert("<?php echo $string['reqcourse'] ?>");
    return false;
  }
  if (document.newUser.new_username.value == "") {
    alert("<?php echo $string['requsername'] ?>");
    return false;
  } else {
    username = document.newUser.new_username.value;
    for (a=0; a<username.length; a++) {
      char = username.substr(a,1);
      if (char == '_') {
        alert('<?php echo $string['usernamechars'] ?>');
        return false;
      }
    }
  }
  if (document.newUser.new_password.value == "") {
    alert("<?php echo $string['reqpassword'] ?>");
    return false;
  }
}

function ldaplookup() {
  notice=window.open("ldaplookup.php","ldap","width=650,height=250,left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  notice.moveTo(screen.width/2-325,screen.height/2-125);
  if (window.focus) {
    notice.focus();
  }
}
</script>
</head>

<body>
<?php
  require '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<br />
<form method="post" name="newUser" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div align="center">
<table border="0" cellspacing="1" cellpadding="0" style="background-color:#95AEC8; text-align:left">
<tr><td>
<table border="0" cellspacing="6" cellpadding="0" width="100%" style="background-color:white">
<tr><td width="32"><img src="../artwork/user_female_32.png" width="32" height="32" alt="User Icon" /></td><td class="title"><?php echo $string['createnewuser']; ?></td></tr>
</table>
</td></tr>
<tr><td>
<table border="0" cellspacing="6" cellpadding="0" style="background-color:#F1F5FB">
<?php
  if ($cfg_use_ldap == true) {
    echo '<tr><td colspan=\"4\"><input type="button" name="lookup" value="' . $string['getldapdetails'] . '" onclick="ldaplookup();" /><td></tr>';
  }
?>
<tr><td align="right"><span class="field"><?php echo $string['title']; ?></span></td><td>
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
<tr><td align="right"><span class="field"><?php echo $string['lastname']; ?></span></td><td><input type="text" id="new_surname" name="new_surname" size="40" value="<?php if (isset($_POST['surname'])) echo $_POST['surname']; ?>" /></td></tr>
<tr><td align="right"><span class="field"><?php echo $string['firstnames']; ?></span></td><td><input type="text" id="new_first_names" name="new_first_names" size="40" value="<?php if (isset($_POST['first_names'])) echo $_POST['first_names']; ?>" /></td></tr>
<tr><td align="right"><span class="field"><?php echo $string['email']; ?></span></td><td><input type="text" id="new_email" name="new_email" size="40" value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } else { echo '@nottingham.ac.uk'; } ?>" /></td></tr>
<tr><td align="right"><span class="field"><?php echo $string['username']; ?></span></td><td><input type="text" id="new_username" name="new_username" size="12" <?php if (isset($_POST['username']) and $unique_username != true) echo ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['username'] . '"'; ?>/>
&nbsp;&nbsp;&nbsp;<span class="field"><?php echo $string['password']; ?></span> <input type="text" id="new_password" name="new_password" value="<?php
  if (isset($_POST['password'])) {
    echo $_POST['password'];
  } else {
    echo gen_password();
  }
?>" size="12" /></td></tr>
<tr><td align="right"><span class="field"><?php echo $string['yearofstudy']; ?></span></td><td>
<select name="new_year">
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
<tr><td align="right"><span class="field"><?php echo $string['typecourse']; ?></span></td><td>
<select name="new_grade" size="1" style="width:350px">
<option value=""></option>
<optgroup label="<?php echo $string['universitystaff']; ?>">
<option value="University Lecturer"><?php echo $string['academiclecturer']; ?></option>
<option value="University Admin"><?php echo $string['administrator']; ?></option>
<option value="Technical Staff"><?php echo $string['ittechnical']; ?></option>
</optgroup>
<optgroup label="<?php echo $string['externalstaff']; ?>">
<?php
if (strpos($_SERVER['HTTP_HOST'],'.uk') !== false) {
  echo "<option value=\"NHS Lecturer\">" . $string['nhslecturer'] . "</option>\n";
  echo "<option value=\"NHS Admin\">" . $string['nhsadmin'] . "</option>\n";
}
?>
<option value="Staff External Examiner"><?php echo $string['externalexaminer']; ?></option>
<option value="Invigilator"><?php echo $string['invigilator']; ?></option>
<?php
  $old_school = '';
  $result = $mysqli->prepare("SELECT DISTINCT name, description, school FROM courses WHERE school NOT IN ('university','NHS','N/A') ORDER BY school, name");
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
<td align="right"><span class="field"><?php echo $string['gender']; ?></span></td><td>
<select id="new_gender" name="new_gender" size="1">
<option value=""></option>
<option value="Male"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Male') echo ' selected'; ?>><?php echo $string['male']; ?></option>
<option value="Female"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Female') echo ' selected'; ?>><?php echo $string['female']; ?></option>
</select>
</td>
</tr>
<tr><td align="right"><span class="field"><?php echo $string['studentid']; ?></span></td><td><input type="text" size="15" name="new_sid" /></td></tr>
<tr><td align="right">&nbsp;</td><td style="color:#808080"><?php echo $string['onlyifstudent']; ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="new_welcome" value="1" />&nbsp;<?php echo $string['sendwelcomeemail']; ?></td></tr>
<tr><td colspan="2" align="center">
<input type="submit" name="submit" value="<?php echo $string['createaccount']; ?>" /></td></tr>
</table>
</td></tr>
</table>
</div>
</form>

<?php
  }
  $mysqli->close();
  
  if ($unique_username != true) {
    echo '<script language="JavaScript">alert("' . sprintf($string['usernameinuse'],$_POST['new_username']) . '")</script>';
  }
?>
</div>

</body>
</html>
