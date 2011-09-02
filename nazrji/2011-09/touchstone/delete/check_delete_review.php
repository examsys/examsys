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

  $mysqli->close();
  
  function niceDate($tmp_date) {
    return substr($tmp_date,6,2) . '/' . substr($tmp_date,4,2) . '/' . substr($tmp_date,0,4) . ' ' . substr($tmp_date,8,2) . ':' . substr($tmp_date,10,2);
  }
?>
<html>
<head>
<title>Confirm Review Delete</title>

<style>
body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed}
</style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/assessment_bin.png" width="32" height="37" border="0" alt="Recycle Bin" /></td>

<td><p>Are you sure you wish to delete this review made on <strong><?php echo niceDate($_GET['dateID']); ?></strong>?</p>

<div style="text-align: right">
<form action="do_delete_review.php" method="post">
<input type="hidden" name="setterID" value="<?php echo $_GET['setterID']; ?>" />
<input type="hidden" name="dateID" value="<?php echo $_GET['dateID']; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="submit" name="submit" value="Delete Review" />&nbsp;
<input type="button" name="cancel" value=" Cancel " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>