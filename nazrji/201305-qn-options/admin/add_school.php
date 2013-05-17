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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../classes/schoolutils.class.php';

$school = $string['prompt'];
$faculty = '';

if (isset($_POST['submit'])) {
  $school = trim($_POST['school']);
  $faculty = trim($_POST['facultyID']);

  if (SchoolUtils::school_exists_in_faculty($faculty, $school, $mysqli)) {
    $error = 'duplicate';
  } else {
    $insert_id = SchoolUtils::add_school($faculty, $school, $mysqli);

    header("location: list_schools.php");
    exit;
  }
}

$faculties = 0;
$faculty_list = array();
$result = $mysqli->prepare("SELECT id, name FROM faculty WHERE deleted IS NULL ORDER BY name");
$result->execute();
$result->bind_result($facultyID, $name);
while ($result->fetch()) {
  $faculty_list[] = array($facultyID, $name);
  $faculties++;
}
$result->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['addschools'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    td {text-align:left}
    .field {font-weight:bold; text-align:right; padding-right:10px}
    .form-error {
      width: 468px;
      margin: 18px auto;
      padding: 16px;
      background-color: #FFD9D9;
      color: #800000;
      border: 2px solid #800000
    }
  </style>

  <script language="JavaScript">
    function checkForm() {
      if (document.getElementById('school').value == "" || document.getElementById('school').value == "<?php echo $string['prompt'] ?>") {
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

<table class="header">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="list_schools.php"><?php echo $string['schools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['addschools']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>

  <br />
  <div align="center">
  <form name="add_school" method="post" onsubmit="return checkForm();" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php
  if (isset($error) and $error = 'duplicate') {
?>
    <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
<?php
  }
?>
    <table cellpadding="0" cellspacing="2" border="0">
    <tr><td class="field"><?php echo $string['school']; ?></td><td><input type="text" size="70" name="school" id="school" value="<?php echo $school ?>" /></td></tr>
    <tr><td class="field"><?php echo $string['faculty']; ?></td><td><select name="facultyID">
    <?php
      foreach ($faculty_list as $faculty) {
        $selected = ($faculty[0] == $faculty) ? ' selected="selected"' : '';
        echo "<option value=\"{$faculty[0]}\"$selected>{$faculty[1]}</option>\n";
      }
    ?>
    </select></td></tr>
    </table>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['add']; ?>" />&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
</body>
</html>