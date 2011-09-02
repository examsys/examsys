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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/errors.inc';

  check_var('questionID', 'POST', true, false);

  $tmp_questionID = $_POST['questionID'];
  $tmp_paperID = $_POST['paperID'];
  
  if ($result = $mysqli->prepare("DELETE FROM papers WHERE p_id=?")) {
    $result->bind_param('i', $tmp_questionID);
    $result->execute();
    $result->close();

    // Create a track changes record to say new question added.
    $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Alter Paper',?," . $userID . ",?,'',NULL,'Delete Question')");
    $trackChange->bind_param('is', $tmp_paperID, $tmp_questionID);
    $trackChange->execute();
    $trackChange->close();

  } else {
    display_error('Papers Delete Error',$mysqli->error);
  }

  if ($_POST['paperID'] != '') {
    if ($result = $mysqli->prepare("UPDATE properties SET random_mark=NULL, total_mark=NULL WHERE property_id=?")) {
      $result->bind_param('i', $tmp_paperID);
      $result->execute();
      $result->close();
    } else {
      display_error('Properties Update Error',$result->error);
    }
  }
  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Question Deleted</title>
<script language="javascript">
  function closeWindow() {
    window.opener.location.href='../paper/details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $_POST['module']; ?>&folder=<?php echo $_POST['folder']; ?>&scrOfY=<?php echo $_POST['scrOfY']; ?>';
    self.close();
  }
</script>
</head>

<body onload="closeWindow();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="Recycle Bin" /></td>

<td><p>Question pointer successfully deleted from question paper.<p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="    OK    " onclick="javascript:window.opener.location.reload();window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>