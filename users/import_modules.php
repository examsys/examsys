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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/admin_auth.inc';

ini_set("auto_detect_line_endings", true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title><?php echo $string['impmodtitle'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    p {margin:0; padding:0}
    label.error {display:block; color:#f00}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
</head>

  <body>
<?php
  require '../include/user_search_options.php';
?>
<div id="content">
<br />
<br />
<br />

<?php
  $file_problem = false;

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
        <td class="dialog_header"><img src="../artwork/modules_icon.png" width="48" height="48" alt="Icon" />&nbsp;&nbsp;<?php echo $string['importmodules'] ?></td>
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
        $lines = file($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_cohort_update.csv");

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

            $result = $mysqli->prepare("SELECT moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND  userID = ? AND calendar_year = ?");
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
                  $success = UserUtils::add_student_to_module($students[$sid]['dbID'], $module_list[$module], $attempt, $session, $mysqli);
                  if ($success) {
                    $modulesAdded++;
                  }
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
      unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_cohort_update.csv");

      echo "<table>\n";
			echo "<tr><td>" . $string['enrolementsperformed'] . "</td><td>$modulesAdded</td></tr>\n";
      echo "<tr><td>" . $string['missingusers'] . "</td><td><div>" . count($missing_users) . "<div>\n";
      if (count($missing_users) > 0) echo '<ul>';
			foreach ($missing_users as $sid => $module) {
        echo "<li>$sid<br />";
        foreach ($module['module'] as $moduleid) {
          echo "$moduleid<br />";
        }
				echo "</li>";
      }
      if (count($missing_users) > 0) echo '</ul>';
			echo "</td></tr>\n";
			
      echo "<tr><td>" . $string['missingmodules'] . "</td><td><div>" . count($unknow_ModuleID) . "</div>\n<ul>";
      if (count($unknow_ModuleID) > 0) echo '<ul>';
      foreach ($unknow_ModuleID as $moduleID) {
        echo "<li>$moduleID</li>";
      }
      if (count($unknow_ModuleID) > 0) echo '</ul>';
			echo "</td></tr>\n";
			echo "</table>\n";
      ?>
      </div>
      </td>
      </tr>
      </table>
      </div>
      </td></tr>
      </table>
      <?php
      $mysqli->close();
      exit();
    } else {
      $file_problem = true;
    }
  }
?>
<table style="width:730px" class="dialog_border">
<tr>
  <td style="width:56px; background-color:white"><img src="../artwork/modules_import.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header midblue_header" style="width:90%"><?php echo $string['importmodules']; ?></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<br />
<div style="text-align:center"><img src="../artwork/module_import_headings.png" width="281" height="59" alt="Headings" style="border:1px solid #808080" /></div>
<br />
<div><?php echo $string['msg2']; ?></div>
<br />
<div align="center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
<?php
if ($file_problem) {
  echo '<div style="color:#C00000"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!" />&nbsp;Please specify a file for upload.</div>';
  echo '<p style="color:#C00000; font-weight:bold">' . $string['csvfile'] . ' <input type="file" size="50" name="csvfile" required /></p>';
} else {
  echo '<p style="font-weight:bold">' . $string['csvfile'] . ' <input type="file" size="50" name="csvfile" required /></p>';
}
?>
<br />
<p><input type="submit" class="ok" value="<?php echo $string['import']; ?>" name="submit" /><input class="cancel" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
<br />
</form>
</div>
</td>
</tr>
</table>

<?php
  $mysqli->close();
?>
</body>
</html>