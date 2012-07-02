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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';
  require '../include/errors.inc';

  check_var('labID', 'GET', true, false);
  
  $result = $mysqli->prepare("SELECT name FROM labs WHERE id=?");
  $result->bind_param('i',  $_GET['labID']);
  $result->execute();
  $result->bind_result($lab_name);
  $result->fetch();
  $result->close();

  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['deletelab']; ?></title>

  <style type="text/css">
    body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed}
  </style>
</head>

<body>
<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg1']; ?></p>
<p><strong><?php echo $string['msg2']; ?> '<?php echo $lab_name; ?>'?</strong><p>
<br />

<div style="text-align:right">
<form action="do_delete_lab.php" method="post">
<input type="hidden" name="labID" value="<?php echo $_GET['labID']; ?>" />
<input style="width:140px" type="submit" name="submit" value="<?php echo $string['delete']; ?>" />&nbsp;
<input style="width:80px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>