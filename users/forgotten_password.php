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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/load_config.php';
$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

$notice = UserNotices::get_instance();

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));
$configObject->set_db_object($mysqli);

$email = (isset($_GET['email'])) ? $_GET['email'] : '';
$message = '';
$errors = array();
$form_util = new FormUtils();

if (isset($_POST['submit']) and $_POST['submit'] == $string['send']) {
  $email = $_POST['email'];

  // Process the form submission
  $errors = $form_util->check_required(array('email' => $string['emailaddress']));

  if(count($errors) == 0) {
  // Check if the supplied value is an email address (avoid an unnecessary DB call)
    if(!$form_util->is_email($email)) {
      $errors[] = $string['emailaddressinvalid'];
    } else if ($form_util->is_email_in_cfg_institutional_domains($email)) {
      $errors[] = $string['emailaddressininstitutionaldomains'];
    } else {
      // If it is, look for the user in the database
      $stmt = $mysqli->prepare("SELECT id, title, surname FROM users WHERE email = ? ORDER BY id DESC LIMIT 1");
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($user_id, $title, $surname);
      $stmt->fetch();
      if ($stmt->num_rows == 0) {
        $errors[] = $string['emailaddressnotfound'];
      } else {
        // If they do exist, create a token and send it to them in an email
        $token = substr(md5(rand(10000000,99999999)), 0, 15);

        // Check if there is already a token for the user and update reather than continually adding new ones
        // if they refresh the browser
        $stmt = $mysqli->prepare("SELECT id FROM password_tokens WHERE user_id=? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($token_id);
        $stmt->fetch();
        if ($stmt->num_rows == 0) {
          $addtoken = $mysqli->prepare("INSERT INTO password_tokens(user_id, token, time) VALUES(?, ?, NOW())");
          $addtoken->bind_param('is', $user_id, $token);
          $addtoken->execute();
          $addtoken->close();
        } else {
          $updatetoken = $mysqli->prepare("UPDATE password_tokens SET token=?, time=NOW() WHERE id=?");
          $updatetoken->bind_param('si', $token, $token_id);
          $updatetoken->execute();
          $updatetoken->close();
        }

        $email_body = <<< EMAIL
<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>Rogō {$string['passwordreset']}</title>
<style type="text/css">
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:90%}
h1 {font-size:140%}
h2 {font-size:120%}
</style>
</head>
<body>
EMAIL;
        $contactemail = support::get_primary_email();
        $email_body .= sprintf($string['emailhtml'], $title, $surname, $_SERVER['HTTP_HOST'], $token, $contactemail);

        $email_body .= <<< EMAIL
</body>
</html>
EMAIL;

        $mail_to = $email;
        $subject = "Rogo {$string['passwordreset']}";
        $headers = "From: " . $contactemail . "\n";
        $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
        if(!@mail ($mail_to, $subject, $email_body, $headers)) {
          $errors[] = sprintf($string['couldntsendemail'], $email);
        } else {
          $message = sprintf($string['emailsentmsg'], $email);
        }
      }
      $stmt->close();
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['forgottenpassword']); ?></title>

  <link rel="stylesheet" href="../css/body.css" type="text/css" />
  <link rel="stylesheet" href="../css/screen.css" type="text/css" />
  <style type="text/css">
  body {font-size:90%}
  .field {padding-top:4px; padding-left:6px; font-weight:bold}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
  $(function() {
    $('#forgotten_pw').validate({
      messages: {
        email: '<?php echo $string['emailaddressinvalid'] ?>'
      }
    });
  });
  </script>
</head>

<body>
<form id="forgotten_pw" name="forgotten_pw" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
	<br />
	<div align="center">
  	<table cellpadding="0" cellspacing="0" style="width:500px; border:1px #C8C8C8 solid">
    	<tr style="height:70px; width:100%; background-color:#EAEAEA; font-size:150%; font-weight:bold; padding-left:6px"><td style="text-align:right; width:135px"><img src="../artwork/fingerprint_48.png" width="48" height="48" alt="fingerprint" /></td><td style="text-align:left">&nbsp;&nbsp;<?php echo $string['forgottenpassword'] ?></td></tr>
<?php
if ($message == '') {
?>
    	<tr><td colspan="2" style="padding:6px"><?php echo $string['intromsg'] ?></td></tr>
    	<tr>
    		<td colspan="2" style="padding:6px">
<?php
  if (count($errors) > 0) {
?>
    			<ul>
<?php
    foreach ($errors as $error) {
?>
						<li class="error"><?php echo $error ?></li>
<?php
    }
?>
    			</ul>
<?php
  }
?>
        </td>
      </tr>
    	<tr>
    		<td colspan="2">
    			<table border="0" style="width:100%; text-align:left">
    				<tr>
    					<td class="field" style="width: 180px"><label for="email"><?php echo $string['emailaddress'] ?></label></td>
    					<td>
    						<input type="text" id="email" name="email" value="<?php echo $email; ?>" style="width: 280px" class="required email" />
    					</td>
    				</tr>
    				<tr><td colspan="2">&nbsp;</td></tr>
    				<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['send'] ?>" class="ok" /></td></tr>
    				<tr><td colspan="2">&nbsp;</td></tr>
    			</table>
    		</td>
    	</tr>
<?php
} else {
?>
    	<tr><td colspan="2" style="padding-top:4px; padding-left:6px;"><?php echo $message ?></td></tr>
			<tr><td colspan="2">&nbsp;</td></tr>
<?php
}
?>
    </table>
  </div>
<?php
//  $mysqli->close();
?>
</form>

</body>
</html>