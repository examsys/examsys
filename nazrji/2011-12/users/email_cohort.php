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
* Sends an email to the current cohort.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  if ($_POST['submit']) {
    $surname = $_POST['surname'];
    $degreeID = $_POST['degreeID'];
    $yearID = $_POST['yearID'];
    
    $result = $mysqli->prepare("SELECT DISTINCT student_id, surname, initials, title, users.username, password, email FROM users LEFT JOIN sid ON users.id=sid.userID WHERE roles='Student' AND year LIKE ? AND grade LIKE ? AND surname LIKE ?");
    $result->bind_param('sss', $yearID, $degreeID, $surname);
    $result->execute();
    $result->bind_result($student_id, $surname, $initials, $title, $username, $password, $email);
    while ($row = $result->fetch()) {
      // Perform replacement.
      $message = $_POST['message'];
      $message = str_replace("{title}", $title, $message);
      $message = str_replace("{initials}", $initials, $message);
      $message = str_replace("{last_name}", $surname, $message);
      $message = str_replace("{username}", $username, $message);
      $message = str_replace("{password}", $password, $message);
     
      //$to = 'simon.wilkinson@nottingham.ac.uk';
      $to = $email;
      $subject = $_POST['subject'];
      $headers = "From: " . $_POST['from'] . "\n";
      $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
      if ($_POST['ccaddress'] != '') $headers .= "cc: " . $_POST['ccaddress'] . "\n";
      if ($_POST['bccaddress'] != '') $headers .= "bcc: " . $_POST['bccaddress'] . "\n";
      mail ($to, $subject, $message, $headers) or print "<p>Could not send mail to <strong>$to</strong>.</p>";
    }
    $result->close();
  } else {
?>
<html>
<head>
<title>Email Cohort</title>

<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:#D6DFF7; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
td {font-size:90%}
.heading {background-color:#EBEADB; border-left:solid white 1px; border-right:solid #D8D2BD 1px; border-top:solid white 1px; border-bottom:solid #D8D2BD 1px; color:black; font-family:Arial,sans-serif}
textarea, input {font-family:Arial,sans-serif}
</style>

<script language=JavaScript src='../editor/scripts/innovaeditor.js'></script>
</head>

<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div align="center">
<table cellpadding="2" cellspacing="0" border="0">
<tr>
<td><strong>From:</strong></td>
<?php
  $stmt = $mysqli->prepare("SELECT email FROM users WHERE username=? AND password=?");
  $stmt->bind_param('ss', $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
  $stmt->execute();
  $stmt->bind_result($from_email);
  $stmt->fetch();
  $stmt->close();
  echo "<td><input type=\"text\" name=\"from\" size=\"60\" value=\"$from_email\" /></td>\n";
?>
<td rowspan="3"><img src="../reports/stamp_icon.gif" width="113" height="69" alt="stamp" /></td>
</tr>
<tr>
<td><strong>CC:</strong></td>
<td><input type="text" name="cc" size="60" /></td>
</tr>
<tr>
<td><strong>BCC:</strong></td>
<td><input type="text" name="bcc" size="60" /></td>
</tr>
<tr>
<td><strong>Subject:</strong></td>
<td colspan="2"><input type="text" name="subject" size="80" /></td>
</tr>
<tr>
<td><strong>Message:</strong></td>
<td colspan="2"><textarea name="message" cols="80" rows="14"></textarea>
  <script>
    var oEdit1 = new InnovaEditor("oEdit1");
    oEdit1.mode="XHTML";
    oEdit1.useTagSelector=false;
    oEdit1.useBR=false;
    oEdit1.width="530px";
    oEdit1.height="320px";
    oEdit1.arrCustomTag=[["Title","{title}"],["Initials","{initials}"],["Last Name","{last_name}"],["Username","{username}"],["Password","{password}"]];
    oEdit1.features=["CustomTag","SpellCheck","|","Cut","Copy","PasteText","|","Undo","|","Bold","Italic","Underline","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","|","Numbering","Bullets","|","Table","Characters","|","XHTMLSource"];
    oEdit1.arrStyle = [["BODY",false,"","background:white; margin: 2px; color:black; font-size: 80%; font-family:Arial,sans-serif"]];
    oEdit1.btnStyles = true;
    oEdit1.REPLACE("message");
  </script>
</td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr><td colspan="3" align="center"><input style="width: 120px" type="submit" name="submit" value="Email Cohort" />&nbsp;<input type="button" name="cancel" style="width: 120px" value="Cancel" onclick="window.close();" /></td></tr>
</table>
</div>

<input type="hidden" name="surname" value="<?php echo $_GET['surname']; ?>" />
<input type="hidden" name="degreeID" value="<?php echo $_GET['degreeID']; ?>" />
<input type="hidden" name="yearID" value="<?php echo $_GET['yearID']; ?>" />
</form>
<?php
  }
  $mysqli->close();
?>
</body>
</html>