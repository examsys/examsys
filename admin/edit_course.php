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
require_once '../include/errors.php';

$courseID = check_var('courseID', 'REQUEST', true, false, true);

if (!CourseUtils::courseid_exists($courseID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$result = $mysqli->prepare('SELECT schoolid, name, description, externalid, externalsys FROM courses WHERE id = ?');
$result->bind_param('i', $courseID);
$result->execute();
$result->bind_result($current_school, $coursename, $description, $current_externalid, $current_externalsys);
$result->fetch();
$result->close();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Edit Course</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/courseinit.min.js"></script>
</head>
<?php

  echo '<body>';
  require '../include/course_options.inc';
  require '../include/toprightmenu.inc';
    
    echo draw_toprightmenu();
?>
  <div id="content">
  <div class="head_title">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" /><a href="list_courses.php"><?php echo $string['courses'] ?></a></div>
    <div class="page_title"><?php echo $string['editcourse']; ?></div>
  </div>
  <br />
  <div align="center">
  <form id="theform" name="edit_course" method="post" action="" autocomplete="off">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
      echo '<tr><td class="field">' . $string['code'] . '</td><td><input type="text" size="10" maxlength="255" id="course" name="course" value="' . $coursename . '" /><input type="hidden" name="old_course" value="' . $coursename . "\" required /></td></tr>\n";
    ?>
    <tr><td class="field"><?php echo $string['name']; ?></td><td><input type="text" size="70" maxlength="255" name="description" value="<?php echo $description; ?>" required /></td></tr>
    <tr><td class="field"><?php echo $string['school']; ?></td><td><select name="school" required>
    <?php
      $result = $mysqli->prepare("SELECT schools.id, schools.code, school, faculty.code, name, faculty.id FROM schools, faculty WHERE schools.facultyID = faculty.id AND school != '' ORDER BY faculty.code, name, school");
      $result->execute();
      $result->bind_result($schoolid, $code, $school, $facultycode, $faculty, $facultyid);
      
      $old_faculty = '';
      $old_facultycode = '';
      $old_facultyid = 0;
    while ($result->fetch()) {
        if ($facultyid != $old_facultyid) {
            if ($old_facultycode . ' ' . $old_faculty != '') {
                echo "</optgroup>\n";
            }
            echo "<optgroup label=\"$facultycode $faculty\">\n";
        }
        if ($current_school == $schoolid) {
            echo "<option value=\"$schoolid\" selected>$code $school</option>\n";
        } else {
            echo "<option value=\"$schoolid\">$code $school</option>\n";
        }
        $old_facultyid = $facultyid;
        $old_faculty = $faculty;
        $old_facultycode = $facultycode;
    }
      echo "</optgroup>\n";
      $result->close();
      
    ?>
    </select></td></tr>
    <tr><td class="field"><?php echo $string['externalid'] ?></td><td><?php echo $current_externalid; ?></td></tr>
    <tr><td class="field"><?php echo $string['externalsys'] ?></td><td><?php echo $current_externalsys; ?></td></tr>
    </table>
    <input type="hidden" name="courseID" value="<?php echo $courseID; ?>" />
    <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input type="button" class="cancel" name="home" id="cancel" value="<?php echo $string['cancel'] ?>" /></p>
  </form>
  </div>
<?php
$mysqli->close();
?>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_edit_course.php';
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
