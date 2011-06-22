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
* Creates a new user (staff or student).
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';

  function my_ucwords($s) { 
    $s = preg_replace_callback("/(\b[\w|']+\b)/s", 'fixcase_callback', $s); 
    return $s;         
  } 
    
  function fixcase_callback($word) { 
    $word = $word[1]; 
    $word = strtolower($word); 
        
    if ($word == "de") return $word; 
    
    $word = ucfirst($word); 
       
    if (substr($word,1,1) == "'") { 
      if (substr($word,0,1) == "D") { 
        $word = strtolower($word); 
      } 
      $next = substr($word,2,1); 
      $next = strtoupper($next); 
      $word = substr_replace($word, $next, 2, 1); 
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
  
    $new_password = encpw($_POST['new_username'],trim($_POST['new_password']));
    $new_surname = my_ucwords(trim($_POST['new_surname']));
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);
    $new_first_names = my_ucwords(trim($_POST['new_first_names']));
  
    $result = $mysqli->prepare("INSERT INTO users VALUES (?,?,?,?,?,?,?,?,NULL,?,?,?,NULL,0,?)");
    $result->bind_param('sssssssssssi', $new_password, $_POST['new_grade'], $new_surname , $initials, $_POST['new_users_title'], $new_username, $new_email, $tmp_roles, $_POST['new_faculty'], $new_first_names, $_POST['new_gender'], $_POST['new_year']);
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
    if (isset($_POST['welcome']) and $_POST['welcome'] != '') {
      $result = $mysqli->query("SELECT email FROM users WHERE username='" . $_SERVER['PHP_AUTH_USER'] . "'");
      $row = $result->fetch_assoc();
      $result->close();
      $subject = "New TouchStone account";
      $headers = "From: " . $row['email'] . "\n";
      $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
      $headers .= "bcc: " . $row['email'] . "\n";
      $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>TouchStone Account</title>
<style>
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:90%}
h1 {font-size:140%}
h2 {font-size:120%}
</style>
</head>
<body>
<p>Dear " . $_POST['users_title'] . " " . ucwords($_POST['surname']) . ",</p>
<p>A new account has been created to access the online assessment and survey system TouchStone. Your personal authentication details are:</p>
<p>Username: " . $_POST['username'] . "<br />
Password: " . $_POST['password'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">(case-sensitive)</span></p>";

      if (strpos($tmp_roles,'Staff') !== false) {
        $message .= "<p>To log into the system goto: <a href=\"https://touchstone.nottingham.ac.uk/touchstone/\">https://touchstone.nottingham.ac.uk/touchstone/</a></p>";
      } elseif (strpos($tmp_roles,'Student') !== false) {
      } else {
        $message .= "<p>To log into the system goto: <a href=\"https://touchstone.nottingham.ac.uk/touchstone/\">https://touchstone.nottingham.ac.uk/touchstone/</a></p>";
        $message .= "<p>When you log in you will be taken to a personal screen listing all the papers that require your attention for review.</p>";
      }
      $message .= "</body>\n</html>";
      mail ($to, $subject, $message, $headers) or print "<p>Could not send mail to <strong>" . $_POST['email'] . "</strong>.</p>";
    }
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Create User<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
</head>
<body>
<?php
  include '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<p>New account created for <?php echo $_POST['new_users_title'] . ' ' . $_POST['new_surname']; ?>.</p>
</div>
    <?php
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>TouchStone: Create User<?php echo " $cfg_install_type"; ?></title>

<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
.title {font-size:160%; font-weight:bold}
.field {font-weight:bold}
</style>

<script language="JavaScript">
function checkForm() {
  if (document.newUser.new_first_names.value == "") {
    alert("Please enter the user's First names.");
    return false;
  }
  if (document.newUser.new_surname.value == "") {
    alert("Please enter the user's Surname.");
    return false;
  }
  if (document.newUser.new_email.value == "" || document.newUser.new_email.value == "@nottingham.ac.uk") {
    alert("Please enter the user's Email Address.");
    return false;
  }
  if (document.newUser.new_grade.options[document.newUser.new_grade.selectedIndex].value == "") {
    alert("Please enter a Type/Course for the user.");
    return false;
  }
  if (document.newUser.new_username.value == "") {
    alert("Please enter a Username for the user.");
    return false;
  } else {
    username = document.newUser.new_username.value;
    for (a=0; a<username.length; a++) {
      char = username.substr(a,1);
      if (char == '_') {
        alert('A username cannot contain an underscore character.');
        return false;
      }
    }
  }
  if (document.newUser.new_password.value == "") {
    alert("Please enter a default Password for the user.");
    return false;
  }
  if (document.newUser.new_faculty.options[document.newUser.new_faculty.selectedIndex].value == "") {
    alert("Please select a Faculty for the user.");
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
<tr><td width="32"><img src="../artwork/user_female_32.png" width="32" height="32" alt="User Icon" /></td><td class="title">Create new User</td></tr>
</table>
</td></tr>
<tr><td>
<table border="0" cellspacing="6" cellpadding="0" style="background-color:#F1F5FB">
<?php
  if ($cfg_use_ldap == true) {
    echo '<tr><td colspan=\"4\"><input type="button" name="lookup" value="Get LDAP details" onclick="ldaplookup();" /><td></tr>';
  }
?>
<tr><td align="right"><span class="field">Title</span></td><td>
<select id="new_users_title" name="new_users_title" size="1">
<option value="Dr">Dr</option>
<option value="Mr" selected>Mr</option>
<option value="Mrs">Mrs</option>
<option value="Miss">Miss</option>
<option value="Ms">Ms</option>
<option value="Professor">Professor</option>
</select></td></tr>
<tr><td align="right"><span class="field">Last Name</span></td><td><input type="text" id="new_surname" name="new_surname" size="40" value="<?php if (isset($_POST['surname'])) echo $_POST['surname']; ?>" /></td></tr>
<tr><td align="right"><span class="field">First Name(s)</span></td><td><input type="text" id="new_first_names" name="new_first_names" size="40" value="<?php if (isset($_POST['first_names'])) echo $_POST['first_names']; ?>" /></td></tr>
<tr><td align="right"><span class="field">Email</span></td><td><input type="text" id="new_email" name="new_email" size="40" value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } else { echo '@nottingham.ac.uk'; } ?>" /></td></tr>
<tr><td align="right"><span class="field">Username</span></td><td><input type="text" id="new_username" name="new_username" size="12" <?php if (isset($_POST['username']) and $unique_username != true) echo ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['username'] . '"'; ?>/>
&nbsp;&nbsp;&nbsp;<span class="field">Password</span> <input type="text" id="new_password" name="new_password" value="<?php
  if (isset($_POST['password'])) {
    echo $_POST['password'];
  } else {
    echo gen_password();
  }
?>" size="12" /></td></tr>
<tr><td align="right"><span class="field">Year of Study</span></td><td>
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
<tr><td align="right"><span class="field">Type/Course</span></td><td>
<select name="new_grade" size="1" style="width:350px">
<option value=""></option>
<optgroup label="University Staff">
<option value="University Lecturer">Academic Lecturer</option>
<option value="University Admin">Administrator</option>
<option value="Technical Staff">IT/Technical</option>
</optgroup>
<optgroup label="External Staff">
<option value="NHS Lecturer">NHS Lecturer/Consultant</option>
<option value="NHS Admin">NHS Admin</option>
<option value="Staff External Examiner">External Examiner</option>
<option value="Invigilator">Invigilator</option>
<?php
  $old_school = '';
  if (strpos($userroles,'SysAdmin') !== false) {
    $degree_details = $mysqli->query("SELECT DISTINCT degree, description, school FROM degrees WHERE school NOT IN ('university','NHS','N/A') ORDER BY school, degree");
  } else {
    $degree_details = $mysqli->query("SELECT DISTINCT degree, description, degrees.school FROM degrees, schools WHERE degrees.school=schools.school AND degrees.school NOT IN ('university','NHS','N/A') AND faculty='$faculty' ORDER BY school, degree");
  }  
  while ($degree_row = $degree_details->fetch_assoc()) {
    if ($old_school != $degree_row['school']) {
      echo "</optgroup>\n<optgroup label=\"Students - " . $degree_row['school'] . "\">\n";    
    }
    echo "<option value=\"" . $degree_row['degree'] . "\">" . $degree_row['degree'] . ": " . $degree_row['description'] . "</option>\n";
    $old_school = $degree_row['school'];
  }
  $degree_details->close();
?>
</optgroup>
</select>
</td></tr>

<tr>
<td align="right"><span class="field">Faculty</span></td><td>
<select id="new_faculty" name="new_faculty" size="1">
<?php
  if (strpos($userroles,'SysAdmin') !== false) {
    echo "<option value=\"\"></option>\n";
    $faculty_details = $mysqli->query("SELECT name FROM faculty ORDER BY name");
    while ($faculty_row = $faculty_details->fetch_assoc()) {
      if (isset($_POST['faculty']) and $faculty_row['name'] == $_POST['faculty']) {
        echo "<option value=\"" . $faculty_row['name'] . "\" selected>" . $faculty_row['name'] . "/option>\n";
      } else {
        echo "<option value=\"" . $faculty_row['name'] . "\">" . $faculty_row['name'] . "</option>\n";
      }
    }
    $faculty_details->close();
  } else {
    echo "<option value=\"$faculty\" selected>$faculty</option>\n";
  }
?>
</select>
</td>
</tr>

<tr>
<td align="right"><span class="field">Gender</span></td><td>
<select id="new_gender" name="new_gender" size="1">
<option value=""></option>
<option value="Male"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Male') echo ' selected'; ?>>Male</option>
<option value="Female"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Female') echo ' selected'; ?>>Female</option>
</select>
</td>
</tr>
<tr><td align="right"><span class="field">Student ID</span></td><td><input type="text" size="15" name="new_sid" /></td></tr>
<tr><td align="right">&nbsp;</td><td style="color:#808080">(only if student)</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>&nbsp;</td><td><input type="checkbox" name="new_welcome" value="1" />&nbsp;Send welcome email to user (inc. password)</td></tr>
<tr><td colspan="2" align="center">
<input type="submit" name="submit" value="Create Account" /></td></tr>
</table>
</td></tr>
</table>
</div>
</form>

<?php
  }
  $mysqli->close();
  
  if ($unique_username != true) {
    echo '<script language="JavaScript">alert("The username \'' . $_POST['new_username'] . '\' is already in use. Please enter a different one.")</script>';
  }
?>
</div>

</body>
</html>
