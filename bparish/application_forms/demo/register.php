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
 * For the demo Creates a new user (staff & student).
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once '../config/config.inc.php';

require_once '../include/auth.inc';
require_once '../include/mb_string.inc.php';

require_once '../classes/dbutils.class.php';
require_once '../classes/lang.class.php';
require_once '../classes/userutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/dateutils.class.php';

if (strcmp($cfg_install_type, 'demo') != 0) {  // If the installation type is not set to 'demo' then exit.
  header("HTTP/1.0 404 Not Found");
  exit;
}

$userroles='SysAdmin';
//$cfg_db_admin_user, $cfg_db_admin_passwd
$mysqli = DBUtils::get_mysqli_link($cfg_db_host , $cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);

db_change_user($mysqli);

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

function adduser($tmp_roles,$new_username) {
  global $mysqli, $cfg_encrypt_salt;
  $initials = '';
  $first_names_array = explode(' ',$_POST['new_first_names']);
  foreach ($first_names_array as $individual_name) {
    $initials .= trim(substr($individual_name,0,1));
  }
  $initials = strtoupper($initials);

  $new_password = encpw($cfg_encrypt_salt, $_POST['new_username'], trim($_POST['new_password']));
  $new_surname = my_ucwords(trim($_POST['new_surname']));

  $new_email = trim($_POST['new_email']);
  $new_first_names = my_ucwords(trim($_POST['new_first_names']));

  $result = $mysqli->prepare("INSERT INTO users VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL, 0, ?)");
  $result->bind_param('ssssssssssi', $new_password, $_POST['new_grade'], $new_surname , $initials, $_POST['new_users_title'], $new_username, $new_email, $tmp_roles, $new_first_names, $_POST['new_gender'], $_POST['new_year']);
  $result->execute();
  $result->close();
  $userid = $mysqli->insert_id;

  return $userid;
}

$unique_username = true;  
$unique_module = true;
  
if (isset($_POST['submit'])) {
  $new_moduleid = '';
  $result = $mysqli->prepare("SELECT MAX(id) FROM modules");
  $result->execute();
  $result->store_result();
  $result->bind_result($maxmodid);
  $result->fetch();
  for ($a = 0; $a < strlen($_POST['new_grade2']); $a++) {
    $b = substr($_POST['new_grade2'], $a, 1);
    if (ctype_upper($b) or ctype_digit($b)) {
      $new_moduleid = $new_moduleid . $b;
    }
  }
  $new_moduleid = $new_moduleid . $maxmodid;

  // replace with module utils function
  $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE moduleid=?");
  $result->bind_param('s', $_POST['new_moduleid']);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_moduleid);
  $result->fetch();
  if ($result->num_rows > 0) $unique_module = false;
  $result->free_result();
  $result->close();

  // Check for unique username
  $result = $mysqli->prepare("SELECT id, username FROM users WHERE username=? or username=?");
  $newname = $_POST['new_username'] . '-stu';
  $result->bind_param('ss', $_POST['new_username'],$newname);
  $result->execute();
  $result->store_result();
  $result->bind_result($userid, $tmp_username);
  $result->fetch();
  if ($result->num_rows > 0) $unique_username = false;
  $result->free_result();
  $result->close();


  if ($unique_module == true) {
    $new_modid = module_utils::add_modules($new_moduleid, $_POST['new_grade2'], 1, 5, NULL, NULL, true, true, true, false, false, true, false, $mysqli);
  }

  if ($unique_username == true) {
    $tmp_roles = 'Staff';
    
    $new_username = trim($_POST['new_username']);
    $useridstf = adduser('Technical Staff',$new_username);
    $new_username = $new_username . '-stu';
    $_POST['new_grade'] = $new_moduleid;
    $userid=adduser('Student', $new_username);
    $result = $mysqli->prepare("SELECT MAX(id) as a FROM users");
    $result->execute();
    $result->bind_result($max);
    $result->fetch();
    $result->close();

    $_POST['new_sid'] = $max;
    $to = trim($_POST['new_email']);

    if ($_POST['new_sid'] != '') {
      $result = $mysqli->prepare("INSERT INTO sid VALUES (?,?)");
      $result->bind_param('si', $_POST['new_sid'], $userid);
      $result->execute();
      $result->close();
    }
  }

  $session = date_utils::get_current_academic_year();

  UserUtils::add_student_to_module($userid, $new_moduleid, 1, $session, $mysqli);

  $result = $mysqli->prepare("INSERT INTO teams VALUES (NULL, ?, ?, NULL, 'System')");
  $result->bind_param('si', $new_moduleid, $useridstf);
  $result->execute();

  $nm = 'DEMO';
  $result->bind_param('si', $nm, $useridstf);
  $result->execute();
  $result->close();

    //TODO register student on $new_modid and add staff to team of this and onto demo so can see demo questions


  // Send out email welcome.
  if (isset($_POST['new_welcome']) and $_POST['new_welcome'] != '') {
    $result = $mysqli->prepare("SELECT email FROM users WHERE username=?");
    $result->bind_param('s', $_SERVER['PHP_AUTH_USER']);
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<p>{$string['username']}: {$_POST['new_username']} & <p>{$string['username']}: {$_POST['new_username']}-stu <br />
{$string['password']}: {$_POST['new_password']} for all&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">{$string['casesensitive']}</span></p>

MESSAGE;

    $to = $_POST['new_email'];
    $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
    $message .= "</body>\n</html>";
    mail ($to, $subject, $message, $headers) or print "<p>" . $string['couldnotsend'] . " <strong>" . $_POST['new_email'] . "</strong>.</p>";
  }

  ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['register'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
</head>
<body>

<div id="content" class="content" style="font-size:80%">
  <p><?php echo $string['newaccountcreated'] . ' ' . $_POST['new_users_title'] . ' ' . $_POST['new_surname']; ?>.</p>
  <p><input type="button" name="home" value="Staff Homepage" onclick="window.location='<?php echo $cfg_web_root; ?>staff/'" /></p>
</div>
  <?php
} else {
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['register'] . ' ' . $cfg_install_type; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
    .title {font-size:160%; font-weight:bold}
    .field {}
    .h {font-weight:bold; padding-top:10px}
  </style>

  <script type="text/javascript">
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
<div id="content" class="content" style="font-size:80%">
<br />
  <form method="post" name="newUser" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div align="center">
      <table border="0" cellspacing="1" cellpadding="0" style="background-color:#95AEC8; text-align:left">
        <tr><td>
          <table border="0" cellspacing="6" cellpadding="0" width="100%" style="background-color:white">
            <tr><td width="32"><img src="../artwork/user_female_32.png" width="32" height="32" alt="User Icon" /></td><td class="title"><?php echo $string['register']; ?></td></tr>
          </table>
        </td></tr>
        <tr><td>
          <table border="0" cellspacing="6" cellpadding="0" style="background-color:#F1F5FB">
            <tr><td colspan="2" class="h">Your Details</td></tr>
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
            <tr><td align="right"><span class="field"><?php echo $string['firstnames']; ?></span></td><td><input type="text" id="new_first_names" name="new_first_names" size="40" value="<?php if (isset($_POST['first_names'])) echo $_POST['first_names']; ?>" /></td></tr>
            <tr><td align="right"><span class="field"><?php echo $string['lastname']; ?></span></td><td><input type="text" id="new_surname" name="new_surname" size="40" value="<?php if (isset($_POST['surname'])) echo $_POST['surname']; ?>" /></td></tr>
            <tr><td align="right"><span class="field"><?php echo $string['email']; ?></span></td><td><input type="text" id="new_email" name="new_email" size="40" value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } else { echo ''; } ?>" /></td></tr>
            <tr><td align="right"><span class="field"><?php echo $string['username']; ?></span></td><td><input type="text" id="new_username" name="new_username" size="12" <?php if (isset($_POST['username']) and $unique_username != true) echo ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['username'] . '"'; ?>/>
              &nbsp;&nbsp;&nbsp;<span class="field"><?php echo $string['password']; ?></span> <input type="text" id="new_password" name="new_password" value="<?php
                if (isset($_POST['password'])) {
                  echo $_POST['password'];
                } else {
                  echo gen_password();
                }
                ?>" size="12" /></td></tr>

            <input type="hidden" name="new_year" value="1" />

            <tr>
              <td align="right"><span class="field"><?php echo $string['gender']; ?></span></td><td>
              <select id="new_gender" name="new_gender" size="1">
                <option value=""></option>
                <option value="Male"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Male') echo ' selected'; ?>><?php echo $string['male']; ?></option>
                <option value="Female"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Female') echo ' selected'; ?>><?php echo $string['female']; ?></option>
              </select>
            </td>
            </tr>
            
            <tr><td colspan="2" class="h"><?php echo $string['demomodule']; ?></td></tr>

            <tr><td align="right"><span class="field"><?php echo $string['name']; ?></span></td><td>
              <input type="text" id="new_grade2" name="new_grade2" size="40" value="<?php if (isset($_POST['new_grade2'])) echo $_POST['new_grade2']; ?>" /></td></tr>

            <tr><td colspan="2">&nbsp;</td></tr>
            <tr><td>&nbsp;</td><td><input type="hidden" name="new_welcome" value="1" />&nbsp;</td></tr>
            <tr><td colspan="2" align="center">
              <input type="submit" name="submit" value="<?php echo $string['createaccount']; ?>" /></td></tr>
          </table>
        </td></tr>
      </table>
    </div>
    <input type="hidden" size="15" name="new_sid" />
  </form>

  <?php
}
$mysqli->close();

if ($unique_username != true) {
  //echo '<script language="JavaScript">alert("' . sprintf($string['usernameinuse'],$_POST['new_username']) . '")</script>';
}
?>
</div>
</body>
</html>
