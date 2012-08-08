<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/invigilator_auth.inc';

  if (isset($_POST['submit'])) {
    if ($_POST['note_id'] == '' or $_POST['note_id'] == '0') {
      $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL,?,?,NOW(),?,?)");
      $result->bind_param('isii', $_POST['student_userID'], $_POST['note'], $_POST['paperID'], $userID);
      $result->execute();  
      $result->close();
    } else {
      $result = $mysqli->prepare("UPDATE student_notes SET note=? WHERE note_id=?");
      $result->bind_param('si', $_POST['note'], $_POST['note_id']);
      $result->execute();  
      $result->close();
    }
  ?>
  <html>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <head><title>Note</title>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location.reload(true);
      window.close();
    }
  </script></head>
  <body onload="closeWindow();">
  <form>
    <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
    $result = $mysqli->prepare("SELECT username, surname, first_names, title, student_id FROM users LEFT JOIN sid ON users.id=sid.userID WHERE id=?");
    $result->bind_param('i', $_GET['userID']);
    $result->execute();
    $result->bind_result($student_username, $student_surname, $student_firstnames, $student_title, $student_id);
    $result->fetch();
    $result->close();
    
    $result = $mysqli->prepare("SELECT note_id, note FROM student_notes WHERE paper_id=? AND userID=?");
    $result->bind_param('is', $_GET['paperID'], $_GET['userID']);
    $result->execute();
    $result->bind_result($note_id, $note);
    $result->fetch();
    $result->close();
?>
<html>
<head>
<title>Note</title>

<style type="text/css">
body {background-color:#FFFFCC; color:black; margin:0px; font-size:90%; font-family:Arial,sans-serif}
</style>
</head>

<body onload="document.myform.note.focus();">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="myform">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; font-size:100%">
<tr>
<?php
  if (file_exists($cfg_web_root . 'users/photos/' . $student_username . '.jpg')) {
    echo "<td style=\"border-left:1px solid #5582D2; background-color:white; width:180px; text-align:left; vertical-align:bottom\">&nbsp;<strong>$student_title $student_surname</strong><br />&nbsp;$student_firstnames<br />&nbsp;$student_id<br /><img src=\"../users/photos/$student_username.jpg\" width=\"180\" height=\"270\" border=\"0\" alt=\"Photo\" /></td><td>";
  } else {
    echo "<td><strong>Student Name:</strong> $student_title $student_surname, $student_firstnames";
    if ($student_id != '') echo " ($student_id)";
    echo "<br />";
  }

  echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n";
  echo "<strong>Note:</strong><br />\n";
  echo "<textarea name=\"note\" cols=\"60\" rows=\"17\" style=\"font-family:Arial,sans-serif; font-size:110%; background-color:#FFFFCC; width:100%\">$note</textarea><br />\n";
?>
</td>
</table>
<br />
<div style="text-align:center"><input type="submit" style="width:100px" name="submit" value="Save" />&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="javascript:window.close();" /></div>
<input type="hidden" name="student_userID" value="<?php echo $_GET['userID']; ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_id; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>