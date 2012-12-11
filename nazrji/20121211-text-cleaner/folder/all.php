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

  require '../include/admin_auth.inc';
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rogō<?php echo " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
    .sch {padding-left:32px; text-indent:-20px}
    .greysch {padding-left:12px; color:#808080}
    .mod {padding-left:60px; text-indent:-20px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="../js/sidebar.js" type="text/javascript"></script>
  <script language="JavaScript">
    function displayCredits(){
      notice=window.open("../credits/index.php","credits","width=696,height=500,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
      notice.moveTo(screen.width/2-350,screen.height/2-250)
      if (window.focus) {
        notice.focus();
      }
    }

    function showHide(sectionID) {
      sectionID = 'block' + sectionID;
      current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
      document.getElementById(sectionID).style.display = current;
    }
  </script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/options_menu.inc';
?>

<div id="content" class="content">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a></div><div style="font-size:220%; font-weight:bold; margin-left:10px"><?php echo $string['allmodules']; ?></div></td><td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>

<table style="width:100%">
<tr><td style="vertical-align:top; width:50%; border-right:#95AEC8 1px solid">
<?php
  $old_faculty = '';
  $old_school = '';
  $module_block = false;
  $block_id=0;
  if ($userObject->has_role('SysAdmin')) {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, faculty.name as faculty, schools.school, moduleid, fullname FROM (schools, faculty) LEFT JOIN modules ON schools.id=modules.schoolid WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL ORDER BY faculty.name, school, moduleid");
  } else {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, faculty.name as faculty, schools.school, moduleid, fullname FROM (schools, faculty, admin_access, modules) WHERE schools.facultyID=faculty.id AND schools.id=modules.schoolid AND schools.id=admin_access.schools_id AND admin_access.userID=? AND schools.deleted IS NULL ORDER BY faculty.name, school, moduleid");
    $results->bind_param('i',$userObject->get_user_ID());
  }
  $results->execute();
  $results->bind_result($modID, $faculty, $school, $moduleid, $fullname);
  while ($results->fetch()) {
    if ($old_faculty != $faculty or $old_school != $school) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if ($old_faculty != $faculty) {
      echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>$faculty</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
    if ($old_school != $school) {
      if ($moduleid == '') {
        echo "<div class=\"greysch\"><img src=\"../artwork/folder_16_grey.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" />&nbsp;$school</div>\n";
      } else {
        echo "<div class=\"sch\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" onclick=\"showHide($block_id)\" />&nbsp;<a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">$school</a></div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($moduleid != '') {
      echo "<div class=\"mod\"><a href=\"details.php?module=$modID\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" /></a>&nbsp;<a href=\"details.php?module=$modID\">$moduleid: $fullname</a></div>\n";
    }
    $old_faculty = $faculty;
    $old_school = $school;
  }
  $results->close();

  echo "</div>\n";      // -- End of 'content' div ------------------

?>
</td><td style="vertical-align:top; width:50%">
<?php
  echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['bymodulecode'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  $old_faculty = '';
  $old_letter = '';
  $module_block = false;
  if ($userObject->has_role('SysAdmin')) {
    $results = $mysqli->prepare("SELECT DISTINCT id, moduleid, fullname FROM modules ORDER BY moduleid");
  } else {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, moduleid, fullname FROM (schools, admin_access, modules) WHERE schools.id=modules.schoolid AND schools.id=admin_access.schools_id AND admin_access.userID=? ORDER BY moduleid");
    $results->bind_param('i',$userObject->get_user_ID());
  }
  $results->execute();
  $results->bind_result($modID, $moduleid, $fullname);
  while ($results->fetch()) {
    if ($old_letter !== substr($moduleid,0,1)) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if ($old_letter !== substr($moduleid,0,1)) {
      if ($moduleid !== '') {
        echo "<div class=\"sch\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" onclick=\"showHide($block_id)\" />&nbsp;<a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">" . substr($moduleid,0,1) . "</a></div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($moduleid !== '') {
      echo "<div class=\"mod\"><a href=\"details.php?module=$modID\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" /></a>&nbsp;<a href=\"details.php?module=$modID\">$moduleid: $fullname</a></div>\n";
    }
    $old_letter = substr($moduleid,0,1);
  }
  $results->close();

  echo "</div>\n";      // -- End of 'content' div ------------------
?>
</td></tr>
</table>
</div>
</div>
<?php

  $mysqli->close();
?>
</body>
</html>