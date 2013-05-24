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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('questionID', 'GET', true, false, false);
check_var('pID', 'GET', true, false, false);
check_var('paperID', 'GET', true, false, false);

$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['confirmdelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<table>
<tr>
<td class="icon"><img src="../artwork/delete_warning.png" width="48" height="48" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?></p>

<div style="text-align: right">
<form action="do_delete_q_pointer.php" method="post">
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<input type="hidden" name="scrOfY" value="<?php echo $_GET['scrOfY']; ?>" />
<input type="hidden" name="questionID" value="<?php echo $_GET['questionID']; ?>" />
<input type="hidden" name="pID" value="<?php echo $_GET['pID']; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />

<?php
if (substr_count($_GET['pID'], ',')  > 1) {
  echo '<input style="width:140px" type="submit" name="submit" value="' . $string['deletes'] . '" />';
} else {
  echo '<input style="width:140px" type="submit" name="submit" value="' . $string['delete'] . '" />';
}
?>
&nbsp;
<input style="width:90px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>