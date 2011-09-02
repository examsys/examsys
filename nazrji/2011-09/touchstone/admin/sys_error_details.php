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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('errorID', 'GET', true, false);

$result = $mysqli->prepare("SELECT title, initials, surname, DATE_FORMAT(occurred,'%d/%m/%y&nbsp;%H:%i:%s'), userID, errtype, errstr, errfile, errline, php_self, query_string, request_method, DATE_FORMAT(fixed,'%d/%m/%y&nbsp;%H:%i:%s') FROM sys_errors, users WHERE sys_errors.userID=users.id AND sys_errors.id=?");
$result->bind_param('i', $_GET['errorID']);
$result->execute();
$result->store_result();
$result->bind_result($title, $initials, $surname, $occurred, $userID, $errtype, $errstr, $errfile, $errline, $php_self, $query_string, $request_method, $fixed);
$result->fetch();
$result->close();

if (isset($_POST['submit'])) {
  $result = $mysqli->prepare("UPDATE sys_errors SET fixed=NOW() WHERE errstr=? AND errfile=? AND errline=?");
  $result->bind_param('ssi', $errstr, $errfile, $errline);
  $result->execute();
  $result->close();
  
  echo "<html>\n<head><title>Error Details</title></head>\n<body onload=\"window.opener.location='sys_error_list.php'; window.close();\"></body>\n<html>\n";
  exit;
} else {
  $result = $mysqli->prepare("SELECT id FROM sys_errors WHERE errstr=? AND errfile=? AND errline=?");
  $result->bind_param('ssi', $errstr, $errfile, $errline);
  $result->execute();
  $result->store_result();
  $result->bind_result($id);
  $similar_errors = $result->num_rows();
  $result->close();
}
$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Error Details</title>

<style>
body {color:black; background-color:#EEEEEE; font-family:Arial; font-size:90%}
table {border: 1px solid #7F9DB9; font-size:100%; border-collapse:collapse; background-color:white}
td {border: 1px solid #7F9DB9; padding:2px}
</style>
</head>

<body>

<table>
<tr><td style="width:250px">Date</td><td><?php echo $occurred; ?></td></tr>
<tr><td>Staff</td><td><?php echo $title . ' ' . $initials . ' ' . $surname; ?></td></tr>
<tr><td>Type</td><td><?php echo $errtype; ?></td></tr>
<tr><td>Description</td><td><?php echo $errstr; ?></td></tr>
<tr><td>File</td><td><?php echo $errfile . ' (line ' . $errline . ')'; ?></td></tr>
<tr><td>$_SERVER['QUERY_STRING']</td><td><?php echo $query_string; ?></td></tr>
<tr><td>$_SERVER['PHP_SELF']</td><td><?php echo $php_self; ?></td></tr>
<tr><td>$_SERVER['REQUEST_METHOD']</td><td><?php echo $request_method; ?></td></tr>
<tr><td>Occurrance of error</td><td><?php echo $similar_errors; ?></td></tr>
<tr><td>Date fixed</td><td><?php echo ($fixed == '' ? 'n/a' : $fixed); ?></td></tr>
</table>

<br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?errorID=<?php echo $_GET['errorID']; ?>" method="post" name="myform">
<div style="text-align:center"><input type="button" name="close" value="Close" style="width:100px" onclick="javascript:window.close();" />&nbsp;&nbsp;
<?php
if ($fixed == '') {
  echo '<input type="submit" name="submit" value="Fixed" style="width:100px" />';
} else {
  echo '<input type="submit" name="submit" value="Fixed" style="width:100px" disabled />';
}
?>
</div>
</form>
<?php

?>

</body>
</html>