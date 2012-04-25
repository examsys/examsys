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

require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('folderID', 'POST', true, false);

$result = $mysqli->prepare("SELECT name FROM folders WHERE id=?");
$result->bind_param('i', $_POST['folderID']);
$result->execute();
$result->bind_result($name);
$result->fetch();
$result->close();

$directories = explode(';',$name);
$parent = '';
if (count($directories) > 1) {
  for ($i=1; $i<count($directories); $i++) {
    if ($parent == '') {
      $parent = $directories[$i-1];
    } else {
      $parent .= ';' . $directories[$i-1];
    }
  }
}

if ($parent != '') {
  $result = $mysqli->prepare("SELECT id FROM folders WHERE name=?");
  $result->bind_param('s', $parent);
  $result->execute();
  $result->bind_result($parentID);
  $result->fetch();
  $result->close();
}
if (strpos($userroles,'SysAdmin') !== false) {
  $result = $mysqli->prepare("UPDATE folders SET deleted=NOW(), name=CONCAT(name,' [deleted ',DATE_FORMAT(NOW(),'%d/%m/%Y'),']') WHERE id=?");
} else {
  $result = $mysqli->prepare("UPDATE folders SET deleted=NOW(), name=CONCAT(name,' [deleted ',DATE_FORMAT(NOW(),'%d/%m/%Y'),']') WHERE id=? AND ownerID=$userID");
}
$result->bind_param('i', $_POST['folderID']);
$result->execute();
$result->close();

$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['folderdeleted']; ?></title>
  <script type="text/javascript">
    function closeWindow() {
      <?php
      if ($parent == '') {
        echo "window.opener.location.href = '../staff/index.php'\n";
      } else {
        echo "window.opener.location.href = '../folder.php?folder=$parentID'\n";
      }
      ?>
      self.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get">
<?php
if ($parent == '') {
  echo "<input type=\"button\" name=\"cancel\" value=\"    " . $string['ok'] . "    \" onclick=\"javascript:self.opener.location.href='../index.php';window.close();\" />\n";
} else {
  echo "<input type=\"button\" name=\"cancel\" value=\"    " . $string['ok'] . "    \" onclick=\"javascript:self.opener.location.href='../folder.php?folder=$parentID';window.close();\" />\n";
}
?>
</form>
</div>
</td></tr>
</table>

</body>
</html>