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

require '../include/staff_auth.inc';
require '../include/errors.inc';

$duplicate = false;
if (isset($_POST['submit'])) {
  // Check for existing name

  $result = $mysqli->prepare("SELECT id FROM faculty WHERE name=?");
  $result->bind_param('s', $_POST['new_faculty']);
  $result->execute(); 
  $result->store_result();
  if ($result->num_rows() > 0) {
    $duplicate = true;
  }
  $result->close();

  if (!$duplicate) {
    $result = $mysqli->prepare("UPDATE faculty SET name=? WHERE id=?");
    $result->bind_param('si', $_POST['new_faculty'], $_POST['facultyID']);
    $result->execute();  
    $result->close();
  ?>
<html>
<head>
<title><?php echo $string['editfaculty']; ?></title>
</head>
<body onload="window.opener.location.href='list_faculties.php'; window.close();">
</body>
</html>
  <?php
    exit;
  }
}

$result = $mysqli->prepare("SELECT name FROM faculty WHERE id=?");
$result->bind_param('i', $_GET['facultyID']);
$result->execute();
$result->bind_result($name);
$result->fetch();
$result->close();
?>
<html>
<head>
<title><?php echo $string['editfaculty']; ?></title>
<style>
body {font-family:Arial,sans-serif; font-size:90%; background-color:#EEEEEE; color:black}
h1 {font-size:120%}
</style>
</head>

<body onload="document.myform.new_keyword.focus();">
<h1><?php echo $string['editfaculty']; ?></h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div>
<?php
if ($duplicate) {
  echo '<input type="text" style="width:100%; background-color:#FFC0C0; border:solid 1px #C00000; color:#800000" name="new_faculty" value="' . $_POST['new_faculty'] . '" />';
  echo "<script language=\"JavaScript\">\nalert('" . $string['warning'] . "');\n</script>\n";
} else {
  echo '<input type="text" style="width:100%" name="new_faculty" value="' . $name . '" />';
}
?>
<input type="hidden" name="facultyID" value="<?php echo $_GET['facultyID']; ?>" />
</div>
<div align="right"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" style="width:80px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /></div>
</form>

</body>
</html>
<?php
$mysqli->close();
?>