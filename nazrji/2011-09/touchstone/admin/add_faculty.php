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
  
$duplicate = false;
if (isset($_POST['ok']) or (isset($_POST['returnhit']) and $_POST['returnhit'] == '1')) {
  $add_faculty = trim($_POST['add_faculty']);
  if ($add_faculty != '') {
    // Check for existing name

    $result = $mysqli->prepare("SELECT id FROM faculty WHERE name=?");
    $result->bind_param('s', $add_faculty);
    $result->execute(); 
    $result->store_result();
    if ($result->num_rows() > 0) {
      $duplicate = true;
    }
    $result->close();

    if (!$duplicate) {
      $result = $mysqli->prepare("INSERT INTO faculty VALUES (NULL, ?)");
      $result->bind_param('s', $add_faculty);
      $result->execute();  
      $result->close();
    }
  }
  if (!$duplicate) {
?>
<html>
<head>
<title><?php echo $string['addfaculty']; ?></title>
</head>
<?php
  if ($add_faculty != '') {
    echo "<body onload=\"window.opener.location.href='list_faculties.php'; window.close();\">\n";
  } else {
    echo "<body onload=\"window.close();\">\n";
  }
?>
</body>
</html>
<?php
    exit;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['addfaculty']; ?></title>
<style>
body {font-family:Arial,sans-serif; font-size:90%; background-color:#EEEEEE; color:black}
textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
h1 {font-size:120%}
</style>
</head>

<body onload="document.myform.new_faculty.focus();">
<h1><?php echo $string['addfaculty']; ?></h1>
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div><?php
if ($duplicate) {
  echo '<input type="text" style="width:100%; background-color:#FFC0C0; border:solid 1px #C00000; color:#800000" name="add_faculty" value="' . $_POST['add_faculty'] . '" />';
  echo "<script language=\"JavaScript\">\nalert('" . $string['warning'] . "');\n</script>\n";
} else {
  echo '<input type="text" style="width:100%" name="add_faculty" />';
}
?>
</div>
<div align="right"><input type="submit" name="ok" value="<?php echo $string['ok']; ?>" style="width:80px" />&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:80px" onclick="window.close();" /><input type="hidden" name="returnhit" value="" /><input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" /></div>
</form>

</body>
</html>
<?php
$mysqli->close();
?>