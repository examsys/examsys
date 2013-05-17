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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/sidebar_menu.inc';
require_once '../include/errors.inc';
require_once '../classes/dateutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/userutils.class.php';

$module = check_var('module', 'GET', true, false, true);
set_time_limit(0);
ob_start();

// Folder security checks
$folder = '';

$module_details = module_utils::get_full_details_by_ID($_GET['module'], $mysqli);
if (!$module_details) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rogō: <?php echo $string['importmetadata'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="../js/sidebar.js" type="text/javascript"></script>
  
  <style type="text/css">
    p {margin:0px; padding:0px}
    h1 {font-size:120%; font-weight:bold}
    .failed {color:#C00000}
  </style>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/folder_options.inc';
?>
<div id="content" class="content" style="font-size:90%; padding-left:10px">
<br />

<?php
  $file_problem = false;
      
  if (isset($_POST['submit'])) {
  ?>
<br />
<table border="0" cellpadding="4" cellspacing="0" class="dialog_border" style="width:700px">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/user_metadata_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header"><?php echo $string['importmetadata']; ?></span></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<br />

<?php
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
        $student_data     = array();
        
        $stmt = $mysqli->prepare("SELECT users.id, username, student_id, users.title, surname, first_names FROM (users, modules_student, modules) LEFT JOIN sid ON users.id = sid.userID WHERE users.id = modules_student.userID AND modules_student.idMod = modules.id AND idMod = ? AND calendar_year = ? ORDER BY username");
        $stmt->bind_param('ss', $_GET['module'], $_POST['session']);
        $stmt->execute();
        $stmt->bind_result($id, $username, $student_id, $title, $surname, $first_names);
        while ($stmt->fetch()) {
          $student_id_array[$username]    = $id;    // Reference by Username
          $student_id_array[$student_id]  = $id;    // Reference by Student ID
          
          $student_data[$id]['title']       = $title;
          $student_data[$id]['surname']     = $surname;
          $student_data[$id]['first_names'] = $first_names;
        }
        $stmt->close();

        $lines = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_import_metadata.csv');
        $type  = '';
        $value = '';

        $line_no = 0;
        $col_no  = 0;
        $headings = array();
       
        echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"font-size:90%\">\n";
       
        $stmt = $mysqli->prepare("REPLACE INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisss', $student_id, $module, $type, $value, $_POST['session']);
        foreach ($lines as $separate_line) {
          $cols = explode(',', $separate_line);
          if ($line_no == 0) {  // Read the header row
            $heading = $cols;
            $col_no = count($cols);
            
            echo "<tr><th></th><th>Username</th><th colspan=\"3\">Student Name</th>";
            for ($i=1; $i<$col_no; $i++) {
              echo "<th>" . trim($heading[$i]) . "</th>";
            }
            echo "</tr>\n";   
          } else {
            // 'username' can be either the real username or sid
            $username = trim($cols[0]);

            // Check see if user was found
            if (!isset($student_id_array[$username])) {
              if (UserUtils::userid_exists($username, $mysqli) or UserUtils::username_exists($username, $mysqli)) {
                echo "<tr><td><img src=\"../artwork/red_cross_16.png\" wodth=\"16\" height=\"16\" alt=\"Failed\" /></td><td class=\"failed\">$username</td><td colspan=\"" . (3 + $col_no) . "\" class=\"failed\" style=\"text-align:center\">&lt;user not registered on " . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . "&gt;</td>";
              } else {
                echo "<tr><td><img src=\"../artwork/red_cross_16.png\" wodth=\"16\" height=\"16\" alt=\"Failed\" /></td><td class=\"failed\">$username</td><td colspan=\"" . (3 + $col_no) . "\" class=\"failed\" style=\"text-align:center\">&lt;unknown user&gt;</td>";
              }
            } else {
              $student_id = $student_id_array[$username];
              echo "<tr><td><img src=\"../artwork/green_plus_16.png\" wodth=\"16\" height=\"16\" alt=\"Add\" /></td><td>$username</td><td>" . $student_data[$student_id]['title'] . "</td><td>" . $student_data[$student_id]['surname'] . "</td><td>" . $student_data[$student_id]['first_names'] . "</td>";
              for ($i=1; $i<$col_no; $i++) {
                $type = trim($heading[$i]);
                $value = trim($cols[$i]);
                echo "<td>$value</td>";
                $stmt->execute();
              }
              echo "</tr>\n";
            }
          }
          $line_no++;
        }
        $stmt->close();
      }
      
      echo "</table>\n";
      
      echo "<br />\n<div style=\"text-align:center\"><input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" style=\"width:100px\" onclick=\"window.location='../folder/details.php?module=" . $_GET['module'] . "';\" /></div>\n<br />\n</td></tr></table>\n</body>\n</html>\n";

      unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_import_metadata.csv");

      $mysqli->close();
      exit;
    } else {
      $file_problem = true;
    }
  }
?>
<br />
<table border="0" cellpadding="4" cellspacing="0" class="dialog_border" style="width:700px">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/user_metadata_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header"><?php echo $string['importmetadata']; ?></span></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

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
<tr><?php
if ($file_problem) {
  echo '<td></td><td style="color:#C00000"><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="!" />&nbsp;Please specify a file for upload.</td></tr><tr>';
  echo '<td style="color:#C00000; font-weight:bold">' . $string['file'] . '</td><td><input type="file" size="50" name="csvfile" />';
} else {
  echo '<td>' . $string['file'] . '</td><td><input type="file" size="50" name="csvfile" />';
}
?></td></tr>
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
?>
</body>
</html>