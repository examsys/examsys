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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

// Query 'objectives' to get the IDs of the 'relationships' records to delete.
$obj_data = $mysqli->prepare("SELECT obj_id FROM objectives WHERE identifier=? AND moduleID=? AND calendar_year=?");
$obj_data->bind_param('dss', $_POST['identifier'], $_POST['moduleID'], $_POST['session']);
$obj_data->execute();
$obj_data->store_result();
$obj_data->bind_result($obj_id);
while ($row = $obj_data->fetch()) {
  // Delete from 'relationships' table.
  $result = $mysqli->prepare("DELETE FROM relationships WHERE obj_id=? AND module_id=? AND calendar_year=?");
  $result->bind_param('iss', $obj_id, $_POST['moduleID'], $_POST['session']);
  $result->execute();  
  $result->close();
}
$obj_data->close();

// Delete from 'sessions' table.
$result = $mysqli->prepare("DELETE FROM sessions WHERE identifier=? AND moduleID=? AND calendar_year=?");
$result->bind_param('dss', $_POST['identifier'], $_POST['moduleID'], $_POST['session']);
$result->execute();  
$result->close();

// Delete from 'objectives' table.
$result = $mysqli->prepare("DELETE FROM objectives WHERE identifier=? AND moduleID=? AND calendar_year=?");
$result->bind_param('dss', $_POST['identifier'], $_POST['moduleID'], $_POST['session']);
$result->execute();  
$result->close();

$mysqli->close();
?>

<html>
<head>
<title>Session Deleted</title>
<script language="javascript">
  function updateParent() {
    window.opener.location.reload();
    self.close();
  }
</script>
</head>

<body onload="javascript:updateParent();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get">
<input type="button" name="cancel" value="    <?php echo $string['ok']; ?>    " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
