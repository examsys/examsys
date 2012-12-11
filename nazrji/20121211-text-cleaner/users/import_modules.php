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
* Bulk student module enrolement
*
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../include/admin_auth.inc';
require_once '../classes/userutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['impmodtitle'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    p {margin:0px; padding:0px}
    h1 {font-size:120%; font-weight:bold}
    label.error {display:block; color:#f00}
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript">
    $(function () { $('#import_form').validate(); });
  </script>
</head>

  <body>
<?php
  require '../include/user_search_options.inc';
?>
<div id="content" class="content">
<br />
<br />
<br />

<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_cohort_update.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        ?>
        <br /><br /><br />
        <table class="dialog_border" style="width:600px">
        <tr>
        <td class="dialog_header"><img src="../artwork/modules_icon.png" width="48" height="48" alt="Icon" />&nbsp;&nbsp;<?php echo $string['addingmodules']; ?> (<?php echo $_FILES['csvfile']['name'] ;?>)</td>
        </tr>
        <tr>
        <td class="dialog_body">

        <?php
        // Get a list of modules held by Rogo.
        $module_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT id, moduleid FROM modules");
        $result->execute();
        $result->bind_result($idMod, $moduleid);
        while ($result->fetch()) {
          $module_list[$moduleid] = $idMod;
        }
        $result->close();

        $modulesAdded = 0;
        $missing_users = array();
        $unknow_ModuleID = array();
        $lines = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_cohort_update.csv");

        // Build an array of unique student names.
        $students = array();
        foreach ($lines as $separate_line) {
          if (trim($separate_line) != '') {
            $fields = explode(',', $separate_line);
            
            $sid = trim($fields[0]);
            $session = trim($fields[2]);
            // Modules will be added later.
            
            $students[$sid]['sid'] = $sid;
            $students[$sid]['session'] = $session;
            $students[$sid]['modules'] = array();
          }
        }

        // Query the modules for each student
        foreach ($students as $student) {
          $student_databaseID = UserUtils::studentid_exists($student['sid'], $mysqli);
          
          if ($student_databaseID !== false) {
            $students[$student['sid']]['dbID'] = $student_databaseID;

            $result = $mysqli->prepare("SELECT moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND  userID=? AND calendar_year=?");
            $result->bind_param('is', $student_databaseID, $student['session']);
            $result->execute();
            $result->store_result();
            $result->bind_result($moduleid, $attempt);
            while ($result->fetch()) {
              if (isset($module_list[$moduleid])) {
                $students[$student['sid']]['modules'][$moduleid][] = $attempt;
              }
            }
            $result->close();
          }
        }

        foreach ($lines as $separate_line) {
          $fields = explode(',', $separate_line);
          if (!stristr($fields[0], "ID") and !stristr($fields[0], "Student ID")) {
            $sid = trim($fields[0]);
            $module = trim($fields[1]);
            $session = trim($fields[2]);
            if (isset($fields[3])) {
              $attempt = trim($fields[3]);
            } else {
              $attempt = 1;
            }
            
            if (isset($module_list[$module])) {
              $require_insert = true;
              if (isset($students[$sid]['modules'][$module])) {
                foreach ($students[$sid]['modules'][$module] as $individual_attempt) {
                  if ($individual_attempt == $attempt) {
                    $require_insert = false;
                  }
                }
              }
              if ($require_insert) {
                if (isset($students[$sid]['dbID'])) {
                  UserUtils::add_student_to_module($students[$sid]['dbID'], $module_list[$module], $attempt, $session, $mysqli);
                  $modulesAdded++;
                } else {
                  $missing_users[$sid]['module'][] = $module;
                }
              }
            } else {
              $unknow_ModuleID[] = $module;
            }
          }
        }
      }
    }
    unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_cohort_update.csv");

    echo "<h2>$modulesAdded " . $string['enrolementsperformed'] . "</h2>";
    echo "<p>" . count($missing_users) . " " . $string['missingusers'] . "</p>";
    foreach ($missing_users as $sid => $module) {
      echo "$sid<br />";
      foreach ($module['module'] as $moduleid) {
        echo "<p style=\"margin-left:10px\">$moduleid</p>";
      }
    }
    echo "<p>" . count($unknow_ModuleID) . " " . $string['missingmodules'] . "</p>";
    foreach($unknow_ModuleID as $moduleID) {
       echo "<p style=\"margin-left:10px\">$moduleID</p>";
    }
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
<table class="dialog_border" style="width:730px">
<tr>
<td style="width:56px; background-color:white"><img src="../artwork/modules_icon.png" width="48" height="48" alt="Icon" /></td><td style="text-align:left; font-size:160%; font-weight:bold; width:90%" class="midblue_header"><?php echo $string['importmodules']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#F1F5FB" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<br />
<div style="text-align:center"><img src="../artwork/module_import_headings.png" width="290" height="60" alt="Headings" style="border:1px solid black" /></div>
<br />
<div><?php echo $string['msg2']; ?></div>
<br />
<div align="center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" class="required" /></p>
<br />
<p><input type="submit" style="width:100px" value="<?php echo $string['import']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
<br />
</form>
</div>
</td>
</tr>
</table>

<?php
  }
?>
</body>
</html>