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

  require '../include/admin_auth.inc';
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo ' ' . $configObject->get('cfg_install_type') ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    #displaycredits {position:absolute; bottom:22px; text-align:center; width:90%; cursor:pointer; color:#295AAD; font-weight:bold}
	  #content img {width:16px; height:16px; padding-right:6px}
    .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
    .sch {background-image: url('../artwork/folder_16.png'); background-repeat:no-repeat; background-position:30px center; padding-left:50px; color:#295AAD; cursor:pointer; padding-top:2px; padding-bottom:2px}
    .greysch {padding-left:12px; color:#808080}
    .mod {background-image: url('../artwork/folder_16.png'); background-repeat:no-repeat; background-position:60px center; padding-left:80px; color:#295AAD; cursor:pointer; padding-top:2px; padding-bottom:2px}
    .recent {margin-left:-25px; padding-bottom:9px}
    .recent a {color:black}
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script>
    $(function () {
      $('.mod').click(function() {
        window.location = 'index.php?module=' + $(this).attr('id');
      });
      
      $('.sch').click(function() {
        $('#block' + $(this).attr('id')).toggle();
      });
    });
  </script>
    
</head>

<body>
<?php
  require '../include/options_menu.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
  <div class="page_title"><?php echo $string['allmodules'] ?></div>
</div>

<table style="width:100%">
<tr><td style="vertical-align:top; width:50%; border-right:#95AEC8 1px solid">
<?php
  $old_faculty = '';
  $old_facultycode = '';
  $old_facultyid = 0;
  $old_school = '';
  $module_block = false;
  $block_id = 0;
  if ($userObject->has_role('SysAdmin')) {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, faculty.code, faculty.name, faculty.id, schools.school, schools.code, moduleid, fullname FROM (schools, faculty) LEFT JOIN modules ON schools.id = modules.schoolid WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL AND active = 1 AND mod_deleted IS NULL ORDER BY faculty.code, faculty.name, school, moduleid");
  } else {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, faculty.code, faculty.name, faculty.id, schools.school, schools.code, moduleid, fullname FROM (schools, faculty, admin_access, modules) WHERE schools.facultyID = faculty.id AND schools.id = modules.schoolid AND schools.id = admin_access.schools_id AND admin_access.userID = ? AND schools.deleted IS NULL AND active = 1 AND mod_deleted IS NULL ORDER BY faculty.code, faculty.name, school, moduleid");
    $results->bind_param('i', $userObject->get_user_ID());
  }
  $results->execute();
  $results->bind_result($modID, $facultycode, $faculty, $facultyid, $school, $schoolcode, $moduleid, $fullname);
  while ($results->fetch()) {
    if ($old_facultyid != $facultyid or $old_school != $school) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if (is_null($facultycode)) {
      if ($old_faculty != $faculty) {
        echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>$facultycode $faculty</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      }
    } else {
      if ($old_facultycode != $facultycode) {
        echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>$facultycode $faculty</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      }
    }
    if ($old_school != $school) {
      if ($moduleid == '') {
        echo "<div class=\"greysch\"><img src=\"../artwork/folder_16_grey.png\" alt=\"folder\" />$schoolcode $school</div>\n";
      } else {
        echo "<div class=\"sch\" id=\"$block_id\">$schoolcode $school</div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($moduleid != '') {
      echo "<div class=\"mod\" id=\"$modID\">$moduleid: $fullname</div>\n";
    }
    $old_facultyid = $facultyid;
    $old_facultycode = $facultycode;
    $old_faculty = $faculty;
    $old_school = $school;
  }
  $results->close();

  echo "</div>\n";

?>
</td><td style="vertical-align:top; width:50%">
<?php
  echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['bymodulecode'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  $old_faculty = '';
  $old_letter = '';
  $module_block = false;
  if ($userObject->has_role('SysAdmin')) {
    $results = $mysqli->prepare("SELECT DISTINCT id, moduleid, fullname FROM modules WHERE active = 1 AND mod_deleted IS NULL ORDER BY moduleid");
  } else {
    $results = $mysqli->prepare("SELECT DISTINCT modules.id, moduleid, fullname FROM (schools, admin_access, modules) WHERE schools.id = modules.schoolid AND schools.id = admin_access.schools_id AND admin_access.userID = ? AND active = 1 AND mod_deleted IS NULL ORDER BY moduleid");
    $results->bind_param('i', $userObject->get_user_ID());
  }
  $results->execute();
  $results->bind_result($modID, $moduleid, $fullname);
  while ($results->fetch()) {
    if ($old_letter !== mb_substr($moduleid, 0, 1)) {
      if ($module_block == true) {
        echo "</div>\n";
        $module_block = false;
      }
    }
    if ($old_letter !== mb_substr($moduleid,0,1)) {
      if ($moduleid !== '') {
        echo "<div class=\"sch\" id=\"$block_id\">" . mb_substr($moduleid, 0, 1) . "</div>\n";
      }
      if ($module_block == false) {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
        $module_block = true;
        $block_id++;
      }
    }
    if ($moduleid !== '') {
      echo "<div class=\"mod\" id=\"$modID\">$moduleid: $fullname</div>\n";
    }
    $old_letter = mb_substr($moduleid, 0, 1);
  }
  $results->close();

  echo "</div>\n";
?>
</td></tr>
</table>
</div>
<?php

  $mysqli->close();
?>
</body>
</html>