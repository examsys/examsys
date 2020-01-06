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
require_once '../include/errors.php';

?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['createnewcourse']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .field {font-weight:bold; text-align:right; padding-right:10px}
    .warn {background-color:#FFD9D9; color:#800000; border:1px solid #800000}
  </style>

  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/courseinit.min.js"></script>
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
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" /><a href="list_courses.php"><?php echo $string['courses'] ?></a></div>
    <div class="page_title"><?php echo $string['createnewcourse']; ?></div>
  </div>
  <br />
  <div align="center">
  <form id="theform" name="edit_course" method="post" action="" autocomplete="off">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <?php
      echo "<tr><td class=\"field\">" . $string['code'] . "</td><td><input type=\"text\" size=\"10\" maxlength=\"255\" name=\"course\" value=\"\" /></td></tr>\n";
    ?>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" maxlength="255" name="description" value="<?php if (isset($_POST['description'])) echo $_POST['description']; ?>" required /></td></tr>
    <tr><td class="field"><?php echo $string['school'] ?></td><td><select name="school" required>
    <option value=""></option>
    <?php
      $result = $mysqli->prepare("SELECT schools.id, schools.code, school, faculty.code, name, faculty.id FROM schools, faculty WHERE schools.facultyID = faculty.id AND schools.deleted IS NULL ORDER BY faculty.code, name, school");
      $result->execute();
      $result->bind_result($schoolid, $code, $school, $facultycode, $faculty, $facultyid);

      $old_faculty = '';
      $old_facultycode = '';
      $old_facultyid = 0;
      while ($result->fetch()) {
        if ($facultyid != $old_facultyid) {
          if ($old_facultycode . ' ' . $old_faculty != '') echo "</optgroup>\n";
          echo "<optgroup label=\"$facultycode $faculty\">\n";
        }
        if (isset($_POST['schoolid']) and $_POST['schoolid'] == $school) {
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
    <?php
      $external = new \external_systems();
      $extsys = $external->get_all_externalsystems();
    ?>
    <tr><td class="field"><?php echo $string['externalsys'] ?></td><td><select name="externalsys">
    <?php
      echo "<option value=\"\"></option>\n";
      foreach ($extsys as $i => $s) {
        if (isset($externalsys) and $s == $externalsys) {
          $selected = "selected";
        } else {
          $selected = "";
        }
        echo "<option value=\"$s\" $selected>$s</option>\n";
      }
    ?>
    </select></td></tr>
    <tr><td class="field"><?php echo $string['externalid'] ?></td><td><input type="text" size="30" maxlength="255" name="externalid" value=""></td></tr>
    </table>
    <p><input type="submit" class="ok" name="submit" value="<?php echo $string['add'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
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
$miscdataset['attributes']['posturl'] = "do_add_course.php";
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
