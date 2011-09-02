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
* TouchStone hompage. Uses ./include/options_menu.inc for the sidebar menu.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
?>
<html>
<head>
<title>Authentication Details</title>
<style>
body {font-family:Arial,sans-serif; background-color:white}
p {font-size:100%; text-align:justify}
</style>
</head>

<body>
<?php
  if (!isset($_POST['email_address'])) {
    exit;
  }

  if(strpos($_POST['email_address'],'nottingham.ac.uk') !== false) {
    //LDAP password used plese update that first
    ?>
    <br />
    <div style="text-align:center">
    <table cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td colspan="2" style="font-size:140%; font-weight:bold">TouchStone 3.5</td>
    </tr>
    <tr>
      <td style="height:120px; width:140px" align="center"><img src="./touchstone/artwork/stamp.png" width="119" height="80" alt="Email Stamp" border="0" /></td>
     <td>
       <div><strong>University of Nottingham Staff Or Student</strong></div>
       <div>All Staff and Student passwords are now managed centrally.<br />Please go to <a href="https://password.nottingham.ac.uk/">https://password.nottingham.ac.uk/</a> to </div>
       <div>retrieve or rest your password.</div>
     </td> 
    </tr>
    </table>
    </div>
    <?php
  } else {
    //local db lookup allowed 
    
    require_once('./touchstone/include/auth.inc');

    $mysqli = new mysqli('localhost', 'notts_nle', '', 'touchstone');
    if ($stmt = $mysqli->prepare("SELECT title, surname, username FROM users WHERE email=? LIMIT 1")) {
      $stmt->bind_param('s', $_POST['email_address']);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($title, $surname, $username);
      $stmt->fetch();
    }

  if ($username != '') {
  
    //create new password
    $password = gen_password();

    //update local DB
    $stmt = $mysqli->prepare("UPDATE users set password=? where username = ? LIMIT 1");
    $stmt->bind_param('ss', encpw($username,$password), $username);
    $stmt->execute();

    //email user !!
    $message = '<html>
<body style="font-family:Calibri,Arial,sans-serif; background-color:white; color:#003366; font-size:90%">
<p style="text-align:center; font-size:140%; font-weight:bold">TouchStone 3.5</p>

<p>Dear ' . $title . ' ' . $surname . ',</p>

<p>This is an automated email from the TouchStone Authentication system
in response to a request for TouchStone login details. Your personal authentication details are as follows:</p>

<p><strong>Username:</strong> <tt>' . $username . '</tt><br />
<strong>Password:</strong> <tt>' . $password . '</tt> <span style="color:#808080">(case-sensitive)</span><br />
</p>

<ul>
<li><p>The TouchStone authentication system is currently independent of other systems (e.g. Novell,Outlook,Portal).
It should be noted that changing your password in any one of these systems will <em>not</em>
automatically change your password in the others.</p></li>

<li><p>Regularly change your TouchStone and Novell passwords to maximise online security. For ultimate
protection use different passwords for each system in case one gets compromised and always use passwords
that cannot easily be guessed and that have both alphabetic and numeric characters in them.</p></li>

<li><p>Do <em>not</em> share your authentication details with anyone (even members of staff).
Other users who have forgotten their details should use the automatic lookup facility or contact
the Medical Education Unit.</p></li>
</ul>
</body>
</html>';
    $to = $_POST['email_address'];
    //$to = 'simon.wilkinson@nottingham.ac.uk';
    $headers = "From: simon.wilkinson@nottingham.ac.uk\r\n";
    $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\r\n";
    $subject = "Your TouchStone details";
    if (!mail ($to, $subject, $message, $headers)) {
      echo "<p><strong>Error:</strong> Email failed to be sent!</p>";
      exit;
    }
    echo '<h1>Email Dispatched</h1>';
    echo '<p>Your TouchStone login details have reset been sent to ' . $_POST['email_address'] . '. Please check your email in the next few minutes.</p>';
  } else {
    echo "<p>Your account was not found using this email address.</p>";
  }
?>
<br />
<div align="center">
<form>
<input type="button" value="    OK    " name="ok" onclick="history.back()" />
</form>
</div>
<?php
  } 
?>
</body>
</html>
