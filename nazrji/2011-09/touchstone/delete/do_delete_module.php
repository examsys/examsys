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

require '../include/sysadmin_auth.inc';

  $result = $mysqli->prepare("DELETE FROM modules WHERE moduleid=?");
  $result->bind_param('s', $_POST['moduleID']);
  $result->execute();  
  $result->close();
  $mysqli->close();
?>

<html>
<head>
<title>Module Deleted</title>
<script language="javascript">
  function updateParent() {
    window.location.reload();
    self.close();
  }
</script>
</head>

<body onload="javascript:updateParent();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/assessment_bin.png" width="32" height="32" border="0" alt="Recycle Bin" /></td>

<td><p>Module successfully deleted.<p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="    OK    " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
