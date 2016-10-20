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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sort.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['courses'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/list.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function edit(courseID) {
      document.location.href='./edit_course.php?courseID=' + courseID;
    }
    
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          sortList: [[0,0]] 
        });
      }

      $(".l").click(function(event) {
        event.stopPropagation();
        selLine($(this).attr('id'),event);
      });

      $(".l").dblclick(function() {
        edit($(this).attr('id'));
      });
      
    });
  </script>
</head>

<body>
<?php
  require '../include/course_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['courses']; ?></div>
</div>
  
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col" style="width:15%"><?php echo $string['code'] ?></th>
  <th class="col" style="width:50%"><?php echo $string['name'] ?></th>
  <th class="col" style="width:25%"><?php echo $string['school'] ?></th>
</tr>
</thead>

<tbody>
<?php
$course_no = 0;
$courses = array();

$result = $mysqli->prepare("SELECT courses.id, schools.code, school, name, description FROM courses LEFT JOIN schools ON courses.schoolid = schools.id WHERE name != 'left' AND name != 'none' AND courses.deleted IS NULL");
$result->execute();
$result->bind_result($id, $schoolcode, $school, $name, $description);
while ($result->fetch()) {
  if ($school == '') $school = '<span style="color:#808080">unknown</span>';
  $courses[$course_no]['id'] = $id;
  $courses[$course_no]['code'] = $name;
  $courses[$course_no]['name'] = $description;
  $courses[$course_no]['schoolcode'] = $schoolcode;
  $courses[$course_no]['school'] = $school;
  $course_no++;
}
$result->close();
$mysqli->close();

for ($i=0; $i<$course_no; $i++) {
  $id = $courses[$i]['id'];

  echo "<tr id=\"$id\" class=\"l\"><td class=\"col\">" . $courses[$i]['code'] . "</td><td>" . $courses[$i]['name'] . "</td><td>" . $courses[$i]['schoolcode'] . ' ' . $courses[$i]['school'] . "</td></tr>\n";
}

?>
</tbody>
</table>
</div>

</body>
</html>