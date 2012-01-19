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
  require_once '../classes/networkutils.class.php';
  
  if (isset($_POST['submit'])) {
    if ($_POST['note_id'] == '' or $_POST['note_id'] == '0') {
      $note = $_POST['note'];
      $current_ipaddress = NetworkUtils::get_ipaddress();
    
      $result = $mysqli->prepare("INSERT INTO paper_notes VALUES (NULL,?,NOW(),?,?,?)");
      $result->bind_param('siis', $note, $_POST['paperID'], $userID, $current_ipaddress);
      $result->execute();  
      $result->close();
    } else {
      $result = $mysqli->prepare("UPDATE paper_notes SET note=? WHERE note_id=?");
      $result->bind_param('si', $_POST['note'], $_POST['note_id']);
      $result->execute();  
      $result->close();
    }
  ?>
  <html>
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
    $current_ipaddress = NetworkUtils::get_ipaddress();
  
    $result = $mysqli->prepare("SELECT note_id, note FROM paper_notes WHERE paper_id=? AND note_workstation=?");
    $result->bind_param('is', $_GET['paperID'], $current_ipaddress);
    $result->execute();
    $result->bind_result($note_id, $note);
    $result->fetch();
    $result->close();
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
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; font-size:100%">
<tr>
<td>
<?php
  echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n";
  echo "<strong>Note:</strong><br />\n";
  echo "<textarea name=\"note\" cols=\"60\" rows=\"17\" style=\"font-family:Arial,sans-serif; font-size:110%; background-color:#FFFFCC; width:100%\">$note</textarea><br />\n";
?>
</td>
</table>
<br />
<div style="text-align:center"><input type="submit" style="width:100px" name="submit" value="Save" />&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="javascript:window.close();" /></div>
<input type="hidden" name="note_id" value="<?php echo $note_id; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>