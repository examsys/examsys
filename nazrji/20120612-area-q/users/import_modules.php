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

  require '../include/admin_auth.inc';
  require '../classes/userutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['impmodtitle'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    body {font-family:Arial,sans-serif; background-color:white; colour:black}
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
<div id="content" class="content" style="font-size:80%">
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], $cfg_tmpdir . $userID . "_cohort_update.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        ?>
        <br /><br /><br />
        <div align="center">
        <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; font-size:120%">
        <tr>
        <td valign="middle" align="left" style="background-color:white"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:140%; font-weight:bold; color:#5582D2"><?php echo $string['addingmodules']; ?> (<?php echo $_FILES['csvfile']['name'] ;?>)</span></td>
        </tr>
        <tr>
        <td align="left" style="background-color:#F1F5FB">

        <?php
        // Get a list of modules held by Rogo.
        $module_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT moduleid FROM modules");
        $result->execute();
        $result->bind_result($moduleid);
        while ($result->fetch()) {
          $module_list[] = $moduleid;
        }
        $result->close();

        $modulesAdded = 0;
        $missing_users = array();
        $lines = file($cfg_tmpdir . $userID . "_cohort_update.csv");

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
          $student_databaseID = UserUtils::studentidExists($student['sid'], $mysqli);
          
          if ($student_databaseID !== false) {
            $students[$student['sid']]['dbID'] = $student_databaseID;

            $result = $mysqli->prepare("SELECT moduleid, attempt FROM student_modules WHERE userID=? AND calendar_year=?");
            $result->bind_param('is', $student_databaseID, $student['session']);
            $result->execute();
            $result->store_result();
            $result->bind_result($moduleid, $attempt);
            while ($result->fetch()) {
              if (in_array($moduleid, $module_list)) {
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
            
            if (in_array($module, $module_list)) {
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
                  UserUtils::addUserToModule($students[$sid]['dbID'], $module, $attempt, $session, $mysqli);
                  $modulesAdded++;
                } else {
                  $missing_users[$sid]['module'][] = $module;
                }
              }
            }
          }
        }
      }
    }
    unlink($cfg_tmpdir . $userID . "_cohort_update.csv");

    echo "<h2>$modulesAdded " . $string['enrolementsperformed'] . "</h2>";
    echo "<p>" . count($missing_users) . " " . $string['missingusers'] . "</p>";
    foreach ($missing_users as $sid => $module) {
      echo "$sid<br />";
      foreach ($module['module'] as $moduleid) {
        echo "<p style=\"margin-left:10px\">$moduleid</p>";
      }
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
<table border="0" cellpadding="4" cellspacing="0" style="width:650px; border:1px solid #95AEC8; margin-left:auto; margin-right:auto">
<tr>
<td style="width:56px; background-color:white"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" /></td><td style="text-align:left; font-size:150%; font-weight:bold; color:#5582D2; width:90%"><?php echo $string['importmodules']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#F1F5FB" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<br />
<div style="text-align:center"><img src="../artwork/module_import_headings.png" width="290" height="60" alt="Headings" border="1" /></div>
<br />
<div><?php echo $string['msg2']; ?></div>
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