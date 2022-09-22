<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

  require '../include/sysadmin_auth.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['schools']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/schoolinit.min.js"></script>
</head>

<body>
<?php
  $result = $mysqli->prepare('SELECT schools.id, schools.code, schools.school, faculty.name, faculty.code, (COUNT(modules.id) - COUNT(modules.mod_deleted)) FROM (schools, faculty)
    LEFT JOIN modules ON schools.id = modules.schoolid WHERE schools.facultyID = faculty.id AND schools.deleted IS NULL GROUP BY faculty.code, faculty.name, schools.code, schools.school, schools.id');
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $code, $school, $faculty, $faculty_code, $module_no);
  $faculties = $result->num_rows;
  require '../include/school_options.inc';
  require '../include/toprightmenu.inc';
  echo draw_toprightmenu();
    ?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['schools'] ?> (<?php echo $faculties ?>)</div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col10" style="width:10%"><?php echo $string['code'] ?></th>
  <th class="col" style="width:40%"><?php echo $string['name'] ?></th>
  <th class="col" style="width:40%"><?php echo $string['faculty'] ?></th>
  <th class="col" style="width:10%"><?php echo $string['modules'] ?></th>
</tr>
</thead>

<tbody>
<?php
if ($faculties > 0) {
    while ($result->fetch()) {
        echo "<tr id=\"$id\" class=\"l\"><td>$code</td><td>$school</td><td>$faculty_code $faculty</td><td class=\"no\">" . number_format($module_no) . "</td></tr>\n";
    }
    $result->close();
} else {
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"4\">{$string['musthavefaculty']}</td></tr>\n";
}

$mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>
