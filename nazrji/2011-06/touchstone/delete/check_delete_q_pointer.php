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
  
  check_var('questionID', 'GET', true, false);

  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Confirm Pointer Delete</title>

<style>
body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed}
</style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="Recycle Bin" /></td>

<td><p>Deleting the pointer to this question will only delete it from the question paper.<p>
<p>It does <strong>not</strong> delete the actual question in the question bank.</p>

<div style="text-align: right">
<form action="do_delete_q_pointer.php" method="post">
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<input type="hidden" name="scrOfY" value="<?php echo $_GET['scrOfY']; ?>" />
<input type="hidden" name="questionID" value="<?php echo $_GET['questionID']; ?>" />
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input style="width:140px" type="submit" name="submit" value="Delete Pointer" />&nbsp;
<input style="width:80px" type="button" name="cancel" value=" Cancel " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>