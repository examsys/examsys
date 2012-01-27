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

$unique_course = true;
if (isset($_POST['submit'])) {
  // Check for unique username
  $tmp_course = trim($_POST['course']);
  
  $result = $mysqli->prepare("SELECT name FROM courses WHERE name=?");
  $result->bind_param('s', $tmp_course);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_course);
  $result->fetch();
  if ($result->num_rows > 0) $unique_course = false;
  $result->free_result();
  $result->close();
}

if (isset($_POST['submit']) and $unique_course == true) {
  $tmp_school = $_POST['school'];
  $tmp_course = trim($_POST['course']);
  $tmp_description = trim($_POST['description']);
  
  $result = $mysqli->prepare("INSERT INTO courses VALUES (NULL, ?, ?, ?, NULL)");
  $result->bind_param('sss', $tmp_school, $tmp_course, $tmp_description);
  $result->execute();  
  $result->close();
  $mysqli->close();
  header("location: list_courses.php");
} else {
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title><?php echo $string['createnewcourse']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style>
    input, textarea {font-family:Arial,sans-serif; color:black}
    .field {font-weight:bold; text-align:right; padding-right:10px}
  </style>

  <script language="JavaScript">
  function checkForm() {
    if (edit_course.course.value == "") {
      alert ("<?php echo $string['codecourse']; ?>");
      return false;
    }
    if (edit_course.description.value == "") {
      alert ("<?php echo $string['titlecourse']; ?>");
      return false;
    }
  }
  </script>
  </head>
  
  <body>
  <?php
    require '../include/course_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['createnewcourse']; ?></div></td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="edit_course" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
    if ($unique_course == false) {
      echo "<tr><td class=\"field\">" . $string['code'] . "</td><td><input type=\"text\" size=\"10\" name=\"course\" style=\"background-color:#FFD9D9; color:#800000; border:1px solid #800000\" value=\"$tmp_course\" /></td></tr>\n";
    } else {
    ?>
      <tr><td class="field"><?php echo $string['code']; ?></td><td><input type="text" size="10" name="course" value="<?php if (isset($_GET['moduleid'])) echo $_GET['moduleid']; ?>" /></td></tr>
    <?php
    }
    ?>
    <tr><td class="field"><?php echo $string['name']; ?></td><td><input type="text" size="70" name="description" value="<?php if (isset($_POST['description'])) echo $_POST['description']; ?>" /></td></tr>
    <tr><td class="field"><?php echo $string['school']; ?></td><td><select name="school">
    <option value=""></option>
    <?php
      $result = $mysqli->prepare("SELECT school, name FROM schools, faculty WHERE schools.facultyID=faculty.id ORDER BY name, school");
      $result->execute();
      $result->bind_result($school, $faculty);
      
      $old_faculty = '';
      while ($result->fetch()) {
        if ($faculty != $old_faculty) {
          if ($old_faculty != '') echo "</optgroup>\n";
          echo "<optgroup label=\"$faculty\">\n";
        }
        if (isset($_POST['school']) and $_POST['school'] == $school) {
          echo "<option value=\"$school\" selected>$school</option>\n";
        } else {
          echo "<option value=\"$school\">$school</option>\n";
        }
        $old_faculty = $faculty;
      }
      echo "</optgroup>\n";
      $result->close();
    ?>
    </select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['add']; ?>">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
<?php
}
$mysqli->close();
?>
</div>

</body>
</html>