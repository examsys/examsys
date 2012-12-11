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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['faculties'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/list.js" type="text/javascript"></script>
</head>

<body>
<?php
  require '../include/faculty_options.inc';
?>
<div id="content" class="content">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['faculties']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<th><div class="col10"><?php echo $string['name']; ?>&nbsp;</div></th><th class="vert_div">&nbsp;<?php echo $string['schoolno']; ?></th><th style="width:50%" class="vert_div"></th></tr>
<tr><th colspan="3" class="bevel"></th></tr>
<?php
$old_faculty = '';
$id = 0;

$result = $mysqli->prepare("SELECT faculty.id, name, COUNT(school) FROM faculty LEFT JOIN schools ON schools.facultyID=faculty.id WHERE faculty.deleted IS NULL GROUP BY name ORDER BY name");
$result->execute();
$result->bind_result($id, $name, $school_no);
while ($result->fetch()) {
  echo "<tr id=\"$id\" onclick=\"selLine($id, event)\" ondblclick=\"editFaculty()\" class=\"l\"><td><div class=\"col10\">$name</div></td><td class=\"col10\" style=\"text-align:right\">$school_no</td><td></td></tr>\n";
  $id++;
}
$result->close();
$mysqli->close();
?>
</table>
</div>

</body>
</html>