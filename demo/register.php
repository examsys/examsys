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
 * @author Simon Atack, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/load_config.php';
$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

if (strcmp($configObject->get('cfg_install_type'), 'demo') != 0) { // If the installation type is not set to 'demo' then exit.
  header("HTTP/1.0 404 Not Found");
  exit();
}

require_once '../include/auth.inc';
require_once '../include/mb_string.inc.php';

require_once '../include/custom_error_handler.inc';

$notice = UserNotices::get_instance();
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_sysadmin_user'), $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));
$configObject->set_db_object($mysqli);

function adduser($course, $tmp_roles, $new_username, $mysqli) {
  $new_password = trim($_POST['new_password']);
  $new_surname = StringUtils::my_ucwords(trim($_POST['new_surname']));
  $new_title = $_POST['new_users_title'];

  $new_email = trim($_POST['new_email']);
  $new_first_names = StringUtils::my_ucwords(trim($_POST['new_first_names']));
  $new_year = $_POST['new_year'];
  $new_gender = $_POST['new_gender'];
	
  $userid = UserUtils::create_user($new_username, $new_password, $new_title, $new_first_names, $new_surname, $new_email, $course, $new_gender, $new_year, $tmp_roles, '', $mysqli);

  return $userid;
}

$unique_username = true;
$unique_module 	 = true;

if (isset($_POST['submit'])) {
  $new_moduleid = '';
  $result = $mysqli->prepare("SELECT MAX(id) FROM modules");
  $result->execute();
  $result->store_result();
  $result->bind_result($maxmodid);
  $result->fetch();
  $result->close();
	
  for ($a = 0; $a < strlen($_POST['new_grade2']); $a++) {
    $b = substr($_POST['new_grade2'], $a, 1);
    if (ctype_upper($b) or ctype_digit($b)) {
      $new_moduleid = $new_moduleid . $b;
    }
  }
  $new_moduleid = $new_moduleid . $maxmodid;

  // Check for unique username
	if (UserUtils::username_exists($_POST['new_username'], $mysqli) or UserUtils::username_exists($_POST['new_username'] . '-stu', $mysqli)) {
		$unique_username = false;
	} else {
		$unique_username = true;
	}
	
	$schoolID = SchoolUtils::add_school(1, 'School of Practice', $mysqli);   			// Make sure the 'School of Practice' school exists.

	CourseUtils::add_course($schoolID, 'A10DEMO', 'Demonstration BSc', null, null, $mysqli);  // Make sure demo course exists.
	
	$new_modid = module_utils::add_modules($new_moduleid, $_POST['new_grade2'], 1, $schoolID, NULL, NULL, true, true, true, false, false, true, false, $mysqli, 0, 0, 1, 1, '07/01');

  if ($unique_username == true) {
    $_POST['new_grade'] = $new_moduleid;
    $yearutils = new yearutils($mysqli);
    $session = $yearutils->get_current_session();
		
    // Add staff account
		$new_username = trim($_POST['new_username']);
    $useridstf = adduser('Staff', 'Staff', $new_username, $mysqli);
    UserUtils::add_staff_to_module_by_modulecode($useridstf, $new_moduleid, $mysqli);  	// Add staff to the new module
    UserUtils::add_staff_to_module_by_modulecode($useridstf, 'DEMO', $mysqli);         	// Add staff to the general DEMO module
    
		// Add student account
    $max_sid = 0;
    $new_username = $new_username . '-stu';
    $userid = adduser('A10DEMO', 'Student', $new_username, $mysqli);
    $result = $mysqli->prepare("SELECT MAX(id) as a FROM users");
    $result->execute();
    $result->bind_result($max_sid);
    $result->fetch();
    $result->close();

    $max_sid++;

    $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
    $result->bind_param('si', $max_sid, $userid);
    $result->execute();
    $result->close();

    UserUtils::add_student_to_module_by_name($userid, $new_moduleid, 1, $session, $mysqli); // Add student to the new module
    UserUtils::add_student_to_module_by_name($userid, 'A10DEMO', 1, $session, $mysqli); // Add student to the demo module
  }
	
  // Send out email welcome.
  if (isset($_POST['new_welcome']) and $_POST['new_welcome'] != '') {
    $tmp_email = trim($_POST['new_email']);

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
<p>{$string['username']}: {$_POST['new_username']} & <p>{$string['username']}: {$_POST['new_username']}-stu <br />
{$string['password']}: {$_POST['new_password']} for all&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#808080\">{$string['casesensitive']}</span></p>

MESSAGE;

    $to = $tmp_email;
    $message .= "<p>" . $string['email2'] . " <a href=\"https://{$_SERVER['HTTP_HOST']}/\">https://{$_SERVER['HTTP_HOST']}/</a></p>";
    $message .= "</body>\n</html>";
    mail($to, $subject, $message, $headers) or print "<p>" . $string['couldnotsend'] . " <strong>$tmp_email</strong>.</p>";
  }

  ?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333;: <?php echo $string['register'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
</head>
<body>

<div id="content">
  <p><?php echo $string['newaccountcreated'] . ' ' . $_POST['new_users_title'] . ' ' . $_POST['new_surname']; ?>.</p>

  <p><input type="button" name="home" value="Staff Homepage" onclick="window.location='../index.php'" /></p>
</div>
  <?php
} else {
  ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333;: <?php echo $string['register'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <style type="text/css">
            .h {font-weight: bold; padding-top: 10px}
    </style>

    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
    <script>
        $(function () {
          $('#theform').validate({
            errorClass: 'errfield',
            errorPlacement: function(error,element) {
              return true;
            }
          });
          $('form').removeAttr('novalidate');
        });

        function checkForm() {
            username = $('#new_username').val();
            for (a = 0; a < username.length; a++) {
                char = username.substr(a, 1);
                if (char == '_') {
                    alert('<?php echo $string['usernamechars'] ?>');
                    return false;
                }
            }
        }
    </script>
</head>

<body>
<div id="content">
<br />
<form method="post" id="theform" name="newUser" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
    <div align="center">
        <table border="0" cellspacing="1" cellpadding="0" style="background-color:#95AEC8; text-align:left">
            <tr>
                <td>
                    <table border="0" cellspacing="4" cellpadding="0" width="100%" style="background-color:white">
                        <tr>
                            <td style="width:48px"><img src="../artwork/self_enrol.png" width="48" height="48" alt="Key" /></td>
                            <td class="dkblue_header" style="font-size:160%; font-weight:bold"><?php echo $string['register']; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table border="0" cellspacing="6" cellpadding="0" style="background-color:#F1F5FB">
                        <tr>
                            <td colspan="2" class="h">Your Details</td>
                        </tr>
                        <tr>
                            <td align="right"><?php echo $string['title']; ?></td>
                            <td>
                                <select id="new_users_title" name="new_users_title" size="1" required>

                                    <?php
                                    if ($language != 'en') {
                                            echo "<option value=\"\"></option>\n";
                                    }
                                    $titles = explode(',', $string['title_types']);
                                    foreach ($titles as $tmp_title) {
                                            echo "<option value=\"$tmp_title\">$tmp_title</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><?php echo $string['firstnames']; ?></td>
                            <td><input type="text" id="new_first_names" name="new_first_names" size="40" value="<?php if (isset($_POST['first_names'])) echo $_POST['first_names']; ?>" required /></td>
                        </tr>
                        <tr>
                            <td align="right"><?php echo $string['lastname']; ?></td>
                            <td><input type="text" id="new_surname" name="new_surname" size="40" value="<?php if (isset($_POST['surname'])) echo $_POST['surname']; ?>" required /></td>
                        </tr>
                        <tr>
                            <td align="right"><?php echo $string['email']; ?></td>
                            <td><input type="text" id="new_email" name="new_email" size="40" value="<?php if (isset($_POST['email'])) {
                                    echo $_POST['email'];
                                } else {
                                        echo '';
                                } ?>" required /></td>
                        </tr>
                        <tr>
                            <td align="right"><?php echo $string['username']; ?></td>
                            <td><input type="text" id="new_username" name="new_username" id="new_username" size="12" <?php if (isset($_POST['username']) and $unique_username != true) echo ' style="background-color:#FFD9D9; color:#800000; border:1px solid #800000" value="' . $_POST['username'] . '"'; ?> required />
                                &nbsp;&nbsp;&nbsp;<?php echo $string['password']; ?>
                                <input type="text" id="new_password" name="new_password" id="new_username" value="<?php
                                        if (isset($_POST['password'])) {
                                                echo $_POST['password'];
                                        } else {
                                                echo gen_password();
                                        }
                                        ?>" size="12" required /></td>
                        </tr>

                        <input type="hidden" name="new_year" value="1"/>

                        <tr>
                            <td align="right"><?php echo $string['gender']; ?></td>
                            <td>
                                <select id="new_gender" name="new_gender" size="1" required>
                                    <option value=""></option>
                                    <option value="Male"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Male') echo ' selected'; ?>><?php echo $string['male']; ?></option>
                                    <option value="Female"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Female') echo ' selected'; ?>><?php echo $string['female']; ?></option>
                                    <option value="Other"<?php if (isset($_POST['gender']) and $_POST['gender'] == 'Other') echo ' selected'; ?>><?php echo $string['other']; ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2" class="h"><?php echo $string['demomodule']; ?></td>
                        </tr>

                        <tr>
                            <td align="right"><?php echo $string['name']; ?></td>
                            <td><input type="text" id="new_grade2" name="new_grade2" size="40" value="<?php if (isset($_POST['new_grade2'])) echo $_POST['new_grade2']; ?>" required /></td>
                        </tr>

                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="hidden" name="new_welcome" value="1"/>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $string['createaccount']; ?>"/></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <input type="hidden" size="15" name="new_sid"/>
</form>

<?php
}
$mysqli->close();
?>
</div>
</body>
</html>
