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
* Import student module registrations form SMS export
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/sidebar_menu.inc';
require_once '../include/errors.inc';
require_once '../classes/dateutils.class.php';
require_once '../classes/moduleutils.class.php';

check_var('module', 'GET', true, false);
set_time_limit(0);
ob_start();


// Folder security checks
$folder = '';
$idMod = $_GET['module'];

// Get the moduleid
$module = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);

if ($module === false) {
  display_error($string['modulenotfound'], $string['modulenotfoundmsg'], false, true);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rogō: <?php echo $string['importmetadata'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="../js/sidebar.js" type="text/javascript"></script>
  <script type="text/javascript">
    function changeMsg() {
      document.getElementById('msg').innerHTML = 'Finished';
    }
  </script>
  <style type="text/css">
    p {margin:0px; padding:0px}
    h1 {font-size:120%; font-weight:bold}
  </style>
</head>


<?php
  if (isset($_POST['submit'])) {
    echo "<body onclick=\"hideMenus()\" onload=\"changeMsg()\">\n";
  } else {
    echo "<body onclick=\"hideMenus()\">\n";
  }
  require '../include/folder_options.inc';
?>
<div id="content" class="content" style="font-size:90%; padding-left:10px">
<br />

<?php

  if (isset($_POST['submit'])) {
    echo '<div id="msg">' . $string['loadingdata'] . '</div>';
    ob_flush();
    flush();

    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_import_metadata.csv"))  {
        echo 'Problem - ';
        if ($_FILES['csvfile']['error'] == "0") {
          echo("Value 0: No problem, the file is uploaded.");
        } elseif ($_FILES['csvfile']['error'] == "1") {
          echo("Value 1: The uploaded file was bigger then  upload_max_filesize in php.ini.");
        } elseif ($_FILES['csvfile']['error'] == "2") {
          echo("Value 2: The uploaded file was bigger then MAX_FILE_SIZE in html-form.");
        } elseif ($_FILES['csvfile']['error'] == "3") {
          echo("Value 3: File partialy uploaded.");
        } elseif ($_FILES['csvfile']['error'] == "4") {
          echo("Value 4: No file was uploaded.");
        } else {
          echo("Other problem: " . $_FILES['csvfile']['error']);
        }
        exit;
      } else {
        // Load the IDs for all students in the module
        $student_id_array = array();
        $stmt = $mysqli->prepare("SELECT users.id, username, student_id FROM (users, modules_student, modules) LEFT JOIN sid ON users.id=sid.userID WHERE users.id=modules_student.userID AND modules_student.idMod = modules.id AND idMod=? AND calendar_year=? ORDER BY username");
        $stmt->bind_param('ss', $_GET['module'], $_POST['session']);
        $stmt->execute();
        $stmt->bind_result($id, $username, $student_id);
        while ($stmt->fetch()) {
          $student_id_array[$username] = $id;    // Reference by Username
          $student_id_array[$student_id] = $id;  // Reference by Student ID
        }
        $stmt->close();

        $lines = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_import_metadata.csv');
        $type = '';
        $value = '';

        $line_no = 0;
        $col_no = 0;
        $unknown_users = array();
        $headings = array();
        $stmt = $mysqli->prepare("REPLACE INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisss', $student_id, $idMod, $type, $value, $_POST['session']);
        foreach ($lines as $separate_line) {
          $cols = explode(',', $separate_line);
          if ($line_no == 0) {  // Read the header row
            $heading = $cols;
            $col_no = count($cols);
          } else {
            // 'username' can be either the real username or sid
            $username = trim($cols[0]);

            // Check see if user was found
            echo "checking $username<br />";
            if (!isset($student_id_array[$username])) {
              $unknown_users[] = $username;
            } else {
              $student_id = $student_id_array[$username];
              for ($i=1; $i<$col_no; $i++) {
                $type = trim($heading[$i]);
                $value = trim($cols[$i]);
                $stmt->execute();
              }
            }
          }
          $line_no++;
        }
        $stmt->close();
      }
    }

    echo "<br />\n<div>" . (count($lines) - count($unknown_users) - 1) . " " . $string['uploadedcorrectly'] . "<br />\n";
    if (count($unknown_users) > 0) {
      echo count($unknown_users) . " " . $string['notrecognised'] . "\n<ul>\n";
      foreach ($unknown_users as $unknown) {
        echo "<li>$unknown</li>\n";
      }
      echo "</ul>\n";
    }
    echo "<br />\n<input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" style=\"width:100px\" onclick=\"window.location='../folder/details.php?module=" . $_GET['module'] . "';\" /></div>";

    unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_import_metadata.csv");

    $mysqli->close();
    exit;
  } else {
?>
<br />
<table border="0" cellpadding="4" cellspacing="0" class="dialog_border" style="width:700px">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/user_metadata_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header"><?php echo $string['importmetadata']; ?></span></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<p style="text-align:justify; padding:4px"><?php echo $string['msg']; ?></p>
<br />
<div style="text-align:center">
<img src="../artwork/user_metadata_sheet.png" width="350" height="165" style="border:1px solid black" alt="" />
<br />
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?module=' . $_GET['module']; ?>" enctype="multipart/form-data">
<table style="margin-left:auto; margin-right:auto; text-align:left">
<tr><td><?php echo $string['year']; ?></td><td><select name="session">
<?php
  $current_year = date_utils::get_current_academic_year();

  $parts = explode('/', $current_year);
  echo "<option value=\"" . ($parts[0]-1) . "/" . ($parts[1]-1) . "\">" . ($parts[0]-1) . "/" . ($parts[1]-1) . "</option>\n";
  echo "<option value=\"$current_year\" selected>$current_year</option>\n";
  echo "<option value=\"" . ($parts[0]+1) . "/" . ($parts[1]+1) . "\">" . ($parts[0]+1) . "/" . ($parts[1]+1) . "</option>\n";

?>
</select></td></tr>
<tr><td><?php echo $string['file']; ?></td><td><input type="file" size="50" name="csvfile" /></td></tr>
</table>
<br />
<p style="padding-bottom:10px"><input type="submit" style="width:100px" value="<?php echo $string['import']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

<?php
  $mysqli->close();
  ob_end_flush();
}
?>
</body>
</html>