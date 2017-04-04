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
* Load new users from CSV file.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/admin_auth.inc';
require '../include/errors.php';
require '../include/import_users.inc';
require_once '../include/demo_replace.inc';

set_time_limit(0);
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title><?php echo $string['importusers'] . " " . $configObject->get('cfg_install_type') ?></title>
	
	<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      $('#cancel').click(function() {
        history.back();
      });
    });  
  </script>
  
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    label.error {display:block; color:#f00}
  </style>
</head>

<?php
  if (isset($_POST['submit'])) {
    echo "<body onload=\"updateMsg()\">\n";
  } else {
    echo "<body>\n";
  }

  require '../include/user_search_options.php';
?>
<div id="content" class="content" style="padding-left:10px">
<?php
  $file_problem = false;

  if (isset($_POST['submit'])) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_new_cohort.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {

        $users = add_users_from_file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_new_cohort.csv');
        unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_new_cohort.csv');
        if (isset($users['error'])) {
          echo "<p>" . $string['followingerrors'] . "</p><ul>";
          foreach ($users['error'] as $msg) {
            echo $msg;
          }
          echo "</ul>";
        } else {
          echo $users['html'];
        }
      }
      
      $mysqli->close();
      exit;
    } else {
      $file_problem = true;
    }
  }
  // Display upload form.
?>
<br />
<br />

<table style="width:780px" class="dialog_border">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/multi_ids.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header midblue_header" style="width:724px"><?php echo $string['importusers']; ?></span></td>
</tr>
<tr>
<td align="left" class="dialog_body" colspan="2">

<p><?php echo $string['msg1']; ?></p>
<blockquote>Type, ID, First Names, Family Name, Title, Course, Year of Study, Email</blockquote>
<p><?php echo $string['msg2']; ?></p>

<div style="text-align:center"><img src="../artwork/student_import_headings.png" width="743" height="59" alt="Headings" style="border:1px solid #808080" /></div>
<br />
<br />
<div style="text-align:center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
<?php
if ($file_problem) {
  echo '<div style="color:#C00000"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="!" />&nbsp;' . $string['specifyfile'] . '</div>';
  echo '<p style="color:#C00000; font-weight:bold">' . $string['csvfile'] . ' <input type="file" size="50" name="csvfile" required /></p>';
} else {
  echo '<p style="font-weight:bold">' . $string['csvfile'] . ' <input type="file" size="50" name="csvfile" required /></p>';
}
?>

<div align="center"><input type="checkbox" name="welcome" value="1" />&nbsp;<?php echo $string['sendwelcomeemail']; ?></div>
<p><input type="submit" class="ok" value="<?php echo $string['import'] ?>" name="submit" /><input class="cancel" type="button" value="<?php echo $string['cancel'] ?>" name="cancel" id="cancel" /></p>
</form>
</div>
</td>
</tr>
</table>

<?php
  $mysqli->close();
?>
</div>

</body>
</html>
