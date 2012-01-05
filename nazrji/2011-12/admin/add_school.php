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

require '../include/sysadmin_auth.inc';

if (isset($_POST['submit'])) {
  $school = trim($_POST['school']);
  $facultyID = trim($_POST['facultyID']);
  
  $result = $mysqli->prepare("INSERT INTO schools VALUES (NULL, ?, ?)");
  $result->bind_param('si', $school, $facultyID);
  $result->execute();
  $result->close();

  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/admin/list_schools.php");
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title><?php echo $string['addschools'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    body {font-family:Arial,sans-serif; color:black; background-color:white; margin:0px}
    td {text-align:left}
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script language="JavaScript">
    function checkForm() {
      if (document.getElementById('school').value == "" || document.getElementById('school').value == "<?php echo $string['prompt']; ?>") {
        alert ("<?php echo $string['enternameofschool']; ?>");
        return false;
      }
    }
  </script>
  </head>
<body>
<?php
  require '../include/school_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
  
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="list_schools.php"><?php echo $string['schools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['addschools']; ?></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>  
  
  <br />
  <div align="center">
  <form name="add_school" method="post" onsubmit="return checkForm();" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0">
    <tr><td class="field"><?php echo $string['school']; ?></td><td><input type="text" size="70" name="school" id="school" value="<?php echo $string['prompt']; ?>" /></td></tr>
    <tr><td class="field"><?php echo $string['faculty']; ?></td><td><select name="facultyID">
    <option value=""></option>
    <?php
      $result = $mysqli->prepare("SELECT id, name FROM faculty ORDER BY name");
      $result->execute();
      $result->bind_result($facultyID, $name);
      while ($result->fetch()) {
        echo "<option value=\"$facultyID\">$name</option>\n";
      }
      $result->close();
    ?>
    </select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['add']; ?>" />&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
<?php
}
?>
</div>
</body>
</html>