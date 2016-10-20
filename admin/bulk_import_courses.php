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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/admin_auth.inc';

ini_set("auto_detect_line_endings", true);
?>
<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['bulkcourseimport'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    p {margin:0; padding:0}
    h1 {font-size:120%; font-weight:bold}
    label.error {display:block; color:#f00}
    .existing {color:#808080}
    .added {color:black}
    .failed {color:#C00000}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
      $('#import_form').validate();
      
      $('#cancel').click(function() {
        history.back();
      });
    });
  </script>
  </head>

  <body>
<?php
  require '../include/course_options.inc';
?>
<div id="content">
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_course_create.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        ?>
        <br /><br /><br />
        <div align="center">
        <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; font-size:120%; width:500px">
        <tr>
        <td valign="middle" align="left" style="background-color:white"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" />&nbsp;&nbsp;<span style="font-size:140%; font-weight:bold; color:#5582D2"><?php echo $string['bulkcourseimport']; ?></span></td>
        </tr>
        <tr>
        <td align="left" style="background-color:#F1F5FB">
        <table cellspaing="0" cellpadding="2" border="0" style="margin-top:15px; margin-bottom:15px">

        <?php
        // Get a list of courses held by Rogo.
        $course_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT name FROM courses WHERE deleted IS NULL");
        $result->execute();
        $result->bind_result($course_name);
        while ($result->fetch()) {
          $course_list[] = $course_name;
        }
        $result->close();
        
        // Get a list of schools held by Rogo.
        $unknown_schoolID = 0;
        $school_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT id, school FROM schools WHERE deleted IS NULL");
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
        $lines = file($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_course_create.csv");

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

                  $unknown_schoolID = SchoolUtils::add_school($facultyID, '', $mysqli);
                }
                $schoolID = $unknown_schoolID;
              }              

              if (in_array($courseid, $course_list)) {
                echo "<tr><td></td><td class=\"existing\">$courseid</td><td class=\"existing\">$description</td><td class=\"existing\">". $string['alreadyexists'] . "</td></tr>\n";
              } else {
                $success = CourseUtils::add_course($schoolID, $courseid, $description, null, null, $mysqli);
                if ($success) {
                  echo "<tr><td><img src=\"../artwork/green_plus_16.png\" wodth=\"16\" height=\"16\" alt=\"Add\" /></td><td class=\"added\">$courseid</td><td class=\"added\">$description</td><td class=\"added\">". $string['added'] . "</td></tr>\n";
                  $coursesAdded++;
                } else {
                  echo "<tr><td><img src=\"../artwork/red_cross_16.png\" wodth=\"16\" height=\"16\" alt=\"Failed\" /></td><td class=\"failed\">$courseid</td><td class=\"failed\">$description</td><td class=\"failed\">". $string['failed'] . "</td></tr>\n";
                }
              }
            }
          }
        }
      }
    }
    unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_course_create.csv");

    echo "</table>";
    echo "<div style=\"text-align:center\"><input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" onclick=\"window.location='list_courses.php'\" style=\"width:100px\" /></div>\n<br />\n";

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
<table class="dialog_border">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header" style="width:90%"><?php echo $string['bulkcourseimport']; ?></span></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<blockquote>Course ID, Name, School</blockquote>
<div style="text-align:center"><img src="../artwork/bulk_course_import_headings.png" width="334" height="59" alt="screenshot" style="border:1px solid #909090" /></div>
<br />

<div align="center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" class="required" /></p>
<br />
<p><input type="submit" class="ok" value="<?php echo $string['import']; ?>" name="submit" /><input class="cancel" id="cancel" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" /></p>
<br />
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