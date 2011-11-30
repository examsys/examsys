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
* Add a note to a students file
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  if (isset($_POST['submit'])) {
    $result = $mysqli->prepare("INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)");
    $result->bind_param('isis', $_POST['tmp_userID'], $_POST['note'], $_POST['paper'], $userID);
    $result->execute();  
    $result->close();
  ?>
  <html>
  <head><title>Note</title>
  <?php
    if ($_POST['calling'] == 'class_totals') {
  ?>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location.reload();
      window.close();
    }
  </script></head>
  <body onload="window.opener.location.reload(); closeWindow();">
  <?php
    } else {
  ?>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location = "details.php?username=<?php echo $_POST['username']; ?>&tab=notes";
      window.close();
    }
  </script></head>
  <body onload="closeWindow();">
  <?php
    }
  ?>
  <form>
    <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
?>
<html>
<head>
<title>Note</title>

<style>
body {background-color:#FFFFCC; color:black; margin:0px; font-size:90%; font-family:Arial,sans-serif}
</style>
</head>

<body onload="document.myform.note.focus();">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="myform">
<?php
  if (isset($_GET['paperID'])) {
    echo "<input type=\"hidden\" name=\"paper\" value=\"" . $_GET['paperID'] . "\" />\n";
    echo $string['studentname'] . " " . $_GET['display_name'] . "</br />\n";
  } else {
    echo $string['papername'] . " <select name=\"paper\">\n<option value=\"\"></option>\n";
    $result = $mysqli->prepare("SELECT DISTINCT property_id, paper_title FROM properties WHERE paper_type='2' AND deleted IS NULL ORDER BY paper_title");
    $result->execute();
    $result->bind_result($property_id, $paper_title);
    while ($result->fetch()) {
      echo "<option value=\"$property_id\">$paper_title</option>\n";
    }
    echo "</select>\n<br />\n";
    $result->close();
  }
  
  echo "<br />" . $string['note'] . "<br />\n";
  echo "<textarea name=\"note\" cols=\"60\" rows=\"15\" style=\"font-family:Arial,sans-serif; font-size:110%; background-color:#FFFFCC; width:100%\"></textarea><br />\n";
?>
<br />
<div style="text-align:center"><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save']; ?>" />&nbsp;<input style="width:100px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="tmp_userID" value="<?php echo $_GET['userID']; ?>" />
<input type="hidden" name="calling" value="<?php if (isset($_GET['calling'])) echo $_GET['calling']; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>