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
* Bulk course creation from CSV formatted file.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';
  require '../classes/courseutils.class.php';
  require '../classes/schoolutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
  <title><?php echo $string['bulkcourseimport'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    body {font-family:Arial,sans-serif; background-color:white; colour:black}
    p {margin:0px; padding:0px}
    h1 {font-size:120%; font-weight:bold}
    label.error {display:block; color:#f00}
    .existing {color:#808080}
    .added {color:black}
    .failed {color:#C00000}
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript">
    $(function () { $('#import_form').validate(); });
  </script>
  </head>

  <body>
<?php
  require '../include/course_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], "/tmp/" . $userID . "_course_create.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        ?>
        <br /><br /><br />
        <div align="center">
        <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; font-size:120%">
        <tr>
        <td valign="middle" align="left" style="background-color:white"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:140%; font-weight:bold; color:#5582D2"><?php echo $string['bulkcourseimport']; ?></span></td>
        </tr>
        <tr>
        <td align="left" style="background-color:#F1F5FB">
        <ul>

        <?php
        // Get a list of courses held by Rogo.
        $course_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT name FROM courses");
        $result->execute();
        $result->bind_result($course_name);
        while ($result->fetch()) {
          $course_list[] = $course_name;
        }
        $result->close();
        
        // Get a list of schools held by Rogo.
        $unknown_schoolID = 0;
        $school_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT id, school FROM schools");
        $result->execute();
        $result->bind_result($school_id, $school_name);
        while ($result->fetch()) {
          $school_list[$school_name] = $school_id;
          if ($school_name == 'unknown') {
            $unknown_schoolID = $school_id;
          }
        }
        $result->close();

        $coursesAdded = 0;
        $lines = file("/tmp/" . $userID . "_course_create.csv");

        $students = array();
        foreach ($lines as $separate_line) {
          if (trim($separate_line) != '') {
            $fields = explode(',', $separate_line);
            
            if (trim($fields[0]) != 'Course ID' and trim($fields[0]) != 'ID') {  // Ignore header line
              $courseid = trim($fields[0]);
              $description = trim($fields[1]);
              if (isset($school_list[trim($fields[2])])) {
                $schoolID = $school_list[trim($fields[2])];
              } else {
                if ($unknown_schoolID == 0) {
                  $result = $mysqli->prepare("SELECT id FROM faculty WHERE name='Administrative and Support Units' LIMIT 1");
                  $result->execute();
                  $result->bind_result($facultyID);
                  $result->fetch();
                  $result->close();

                  $unknown_schoolID = SchoolUtils::addSchool($facultyID, '', $mysqli);
                }
                $schoolID = $unknown_schoolID;
              }              

              if (in_array($courseid, $course_list)) {
                echo "<li class=\"existing\">$courseid - " . $string['alreadyexists'] . "</li>\n";
              } else {
                $success = CourseUtils::addCourse($schoolID, $courseid, $description, $mysqli);
                if ($success) {
                  echo "<li class=\"added\">$courseid - " . $string['added'] . "</li>\n";
                  $coursesAdded++;
                } else {
                  echo "<li class=\"fail\">$courseid - " . $string['failed'] . "</li>\n";
                }
              }
            }
          }
        }
      }
    }
    unlink("/tmp/" . $userID . "_course_create.csv");

    echo "</ul>";
    echo "<div style=\"text-align:center\"><input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" onclick=\"window.location='list_courses.php'\" style=\"width:100px\" /></div>\n";

    $mysqli->close();
    ?>
    </div>
    </td>
    </tr>
    </table>
    </div>
    </td></tr>
    </table>
    <?php
  } else {
?>
<table border="0" cellpadding="4" cellspacing="0" style="width:650px; border:1px solid #95AEC8; margin-left:auto; margin-right:auto">
<tr>
<td style="width:56px; background-color:white"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" /></td><td style="text-align:left; font-size:150%; font-weight:bold; color:#5582D2; width:90%"><?php echo $string['bulkcourseimport']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#F1F5FB" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<blockquote>Course ID, Name, School</blockquote>
<div style="text-align:center"><img src="../artwork/bulk_course_import_headings.png" width="361" height="60" alt="screenshot" border="1" /></div>
<br />

<div align="center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" class="required" /></p>
<br />
<p><input type="submit" style="width:100px" value="<?php echo $string['import']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>
<?php
  }
?>
</body>
</html>