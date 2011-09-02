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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root . 'touchstone/config/config.inc';
require_once $cfg_web_root . 'touchstone/classes/formutils.class.php';
require_once $cfg_web_root . 'touchstone/classes/passwordutils.class.php';

$mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);

$password = $password_confirm = $email = '';
$message = '';
$critical_errors = array();
$errors = array();
$token = '';
$form_util = new FormUtils();

// Check if we've been passed a token
$token = (isset($_GET['token']) and $_GET['token'] != '') ? $_GET['token'] : ((!empty($_POST['token'])) ? $_POST['token'] : '');
if($token == '') {
  $critical_errors[] = 'No token supplied';
} else {
  // Check if the token exists and has not expired
  $stmt = $mysqli->prepare("SELECT id, user_id FROM password_tokens WHERE token=? AND time > DATE_ADD(NOW(), INTERVAL -1 DAY) ORDER BY id DESC LIMIT 1");
  $stmt->bind_param('s', $token);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($id, $user_id);
  $stmt->fetch();
  if ($stmt->num_rows == 0) {
    $critical_errors[] = 'Invalid token';
  }
  $stmt->close();
}

if (count($critical_errors) == 0 and isset($_POST['token']) and $_POST['token'] != '') {
  // Process form submission
  $errors = $form_util->check_required(array('email' => 'Email address', 'password' => 'Password', 'password_confirm' => 'Password confirmation'));
  if(!$form_util->is_email($_POST['email'])) {
    $email = $_POST['email'];
    $errors[] = 'Please supply a valid email address';
  }
  if($_POST['password'] != $_POST['password_confirm']) $errors[] = "Passwords do not match";  
  
  if(count($errors) == 0) {    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Check if email address matches that of the user in the token record
    $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $existing_email);
    $stmt->fetch();
    if ($stmt->num_rows == 0) {
      $critical_errors[] = 'User not found';
    } else {
      if($email != $existing_email) {
        $errors[] = 'Incorrect email address supplied';
      } else {
        // Update user's password
        $new_pw = PasswordUtils::encpw($username, $password);
        $update = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param('si', $new_pw, $user_id);
        if(!$update->execute()) {
          $errors[] = 'Database error updating password';
        } else {
          // Delete password token entry for this user
          $delete = $mysqli->prepare("DELETE FROM password_tokens WHERE user_id=?");
          $delete->bind_param('i', $user_id);
          $delete->execute();
          $delete->close();
          
          $message = 'Password updated. <a href="' . $protocol . $_SERVER['HTTP_HOST'] . '/touchstone/">Log in</a>.';
        }
        $update->close();
      }
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>Reset Password<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" href="../css/screen.css" type="text/css" />
<style>
body {background-color:white; color:black; font-family:Arial,sans-serif; font-size:90%}
.field {padding-top:4px; padding-left:6px; font-weight:bold}
</style>
<script type="text/javascript" src="../javascript/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../javascript/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
  $('#forgotten_pw').validate({
		rules: {
		  password_confirm: {
				required: true,
				equalTo: "#password"
			}
		},
		messages: {
			email: 'Please enter a valid email address',
			password: 'Please enter a password',
			password_confirm: {
				required: "Please confirm your password",
				equalTo: "Passwords do not match"
			}
		}
  });
});
</script>
</head>

<body>
<form id="forgotten_pw" name="forgotten_pw" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	<br />
	<div align="center">
  	<table cellpadding="0" cellspacing="0" style="width:500px; border:1px #C8C8C8 solid">
    	<tr style="height:70px; width:100%; background-image:url(../artwork/grey_bar.png); background-repeat:repeat-x; font-size:150%; font-weight:bold; padding-left:6px"><td style="text-align:right; width:135px"><img src="../artwork/key_48.png" width="48" height="48" alt="modules" /></td><td style="text-align:left">&nbsp;&nbsp;Reset Password</td></tr>
<?php
if($message == '') {
?>
    	<tr><td colspan="2" style="padding-top:4px; padding-left:6px;">Enter a new password.</td></tr>
<?php
  if(count($critical_errors) > 0) {
?>
    	<tr>
    		<td colspan="2" style="padding-top:4px; padding-left:6px;">
    			<ul>
<?php
    foreach($critical_errors as $error) {
?>
						<li class="error"><?php echo $error ?></li>
<?php
    }
?>
    			</ul>
				</td>
			</tr>
<?php
  } else {
    if(count($errors) > 0) {
?>
    	<tr>
    		<td colspan="2" style="padding-top:4px; padding-left:6px;">
    			<ul>
<?php
      foreach($errors as $error) {
?>
						<li class="error"><?php echo $error ?></li>
<?php
      }
?>
    			</ul>
				</td>
			</tr>
<?php
  }
?>
    	<tr>
    		<td colspan="2">
    			<table border="0" style="width:100%; text-align:left">
    				<tr>
    					<td class="field" style="width: 180px"><label for="email">Email address</label></td>
    					<td>
    						<input type="text" id="email" name="email" value="<?php echo $email; ?>" style="width: 280px" class="required email" />
    					</td>
    				</tr>
    				<tr>
    					<td class="field"><label for="email">Password</label></td>
    					<td>
    						<input type="password" id="password" name="password" value="<?php echo $password; ?>" style="width: 280px" class="required" />
    					</td>
    				</tr>
    				<tr>
    					<td class="field"><label for="email">Confirm password</label></td>
    					<td>
    						<input type="password" id="password_confirm" name="password_confirm" value="<?php echo $password_confirm; ?>" style="width: 280px" class="required" />
    						<input type="hidden" name="token" value="<?php echo $token ?>" />
    					</td>
    				</tr>
    				<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="Reset"  style="width:100px" /></td></tr>
    				<tr><td colspan="2">&nbsp;</td></tr>
    			</table>
    		</td>
    	</tr>
<?php
  }
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