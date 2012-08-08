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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/admin_auth.inc';
  require '../include/errors.inc';
  require '../include/import_users.inc';
  
  set_time_limit(0);
  ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['importusers'] . " $cfg_install_type"; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <script type="text/javascript">
    function updateMsg() {
      document.getElementById('msg').innerHTML = '<?php echo $string['finished']; ?>';
    }
  </script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript">
  $(function () { $('#import_form').validate(); });
  </script>
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

  require '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:90%; padding-left:10px">
<br />
<?php
  if (isset($_POST['submit'])) {
    echo "<div id=\"msg\">" . $string['loading'] . "</div>\n<br />\n";
    ob_flush();
    flush();

    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], $cfg_tmpdir . $userID . "_new_cohort.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        $users = add_users_from_file($cfg_tmpdir . $userID . '_new_cohort.csv');
        unlink($cfg_tmpdir . $userID . '_new_cohort.csv');
        if (isset($users['error'])) {
          echo "<p>" . $string['followingerrors'] . "</p><ul>";
          foreach ($users['error'] as $msg) {
            echo $msg;
          }
          echo "</ul>";
        } else {
          echo "<ul>\n";
          if (isset($users['added'])) {
            echo "<li>" . count($users['added']) . " " . $string['usersadded'] . "</li>\n";
          } else {
            echo "<li>0 " . $string['usersadded'] . "</li>\n";
          }
          if (isset($users['updated'])) {
            echo "<li>" . count($users['updated']) . " " . $string['usersupdated'] . "</li>\n";
          } else {
            echo "<li>0 " . $string['usersupdated'] . "</li>\n";
          }
          echo "</ul>\n";
        }
      }
    }
  } else {
    // Display upload form.
?>
<br />
<br />

<table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; width:730px; margin-left:auto; margin-right:auto">
<tr>
<td style="width:56px; background-color:white"><img src="../artwork/multi_ids.png" width="48" height="48" alt="Icon" /></td><td style="text-align:left; font-size:160%; font-weight:bold; width:90%" class="midblue_header"><?php echo $string['importusers']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#F1F5FB" colspan="2">

<p><?php echo $string['msg1']; ?></p>
<blockquote>Type, ID, First Names, Family Name, Title, Course, Year of Study and Email</blockquote>
<p><?php echo $string['msg2']; ?></p> 

<div style="text-align:center"><img src="../artwork/student_import_headings.png" width="749" height="59" alt="Headings" border="1" /></div>
<br />
<br />
<div style="text-align:center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" class="required" /></p>

<div align="center"><input type="checkbox" name="welcome" value="1" />&nbsp;<?php echo $string['sendwelcomeemail']; ?></div>
<p><input type="submit" style="width:100px" value="<?php echo $string['import']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

<?php
  }
  $mysqli->close();
?>
</div>

</body>
</html>
