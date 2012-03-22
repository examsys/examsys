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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/question_types.inc';
require '../include/mapping.inc';
require '../include/errors.inc';
require '../include/dynamic_papers.inc.php';

check_var('module', 'REQUEST', true, false);

$module = $_REQUEST['module'];

$result = $mysqli->prepare("SELECT fullname FROM modules WHERE moduleid=? LIMIT 1");
$result->bind_param('i', $module);
$result->execute();
$result->bind_result($module_name);
$result->fetch();
$result->close();

$years = get_years($module, $mysqli, 'all');
$session = (isset($_REQUEST['session'])) ? $_REQUEST['session'] : $years[0];

$old_p_id = 0;
$row_no = 0;
$info_count = 0;
$temp_array = array();
$questionID_list = '';

$result = $mysqli->prepare("SELECT q_group, q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited FROM questions q INNER JOIN relationships r ON q.q_id=r.question_id WHERE r.module_id=? AND r.paper_id IS NULL AND r.calendar_year=?");
$result->bind_param('ss', $module, $session);
$result->execute();
$result->bind_result($q_group, $q_id, $q_type, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited);
while ($result->fetch()) {
  $temp_array[$q_id]['q_type'] = $q_type;
  $temp_array[$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
  $temp_array[$q_id]['q_id'] = $q_id;
  $temp_array[$q_id]['display_last_edited'] = $display_last_edited;
  $temp_array[$q_id]['q_media'] = $q_media;
  $temp_array[$q_id]['q_media_width'] = $q_media_width;
  $temp_array[$q_id]['q_media_height'] = $q_media_height;

  if($q_type == 'info') $info_count++;

  $temp_array[$q_id]['q_group'] = $q_group;
  $questionID_list .= $q_id . ',';
}
$result->close();
$questionID_list = rtrim($questionID_list, ',');

//$objsBySession = getObjectives($module, $session, null, $questionID_list, $mysqli);
$objsBySession = getObjectives($module, $session, null, $questionID_list, $mysqli);
unset($objsBySession['none_of_the_above']);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['dynamicpapers'] . ' - ' . $string['mappingbysession'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    h1 {font-size:160%; font-weight:bold; color:#316AC5; margin-left:15px; padding-top:10px}
    img {border:none;}
    td {font-size:100%}
    .q_no {text-align:right; vertical-align:top; cursor:pointer}
    .divider {font-family:Arial,sans-serif; font-size:90%; font-weight:bold; padding-left:30px}
    .mapping {font-size:90%;color:#FF6300;font-weight:normal}
    a.q_excluded {color:red; font-weight:normal; text-decoration:line-through}
    a.q_ok {color:#FF6300; font-weight:normal}
    .unmapped {color:#C0C0C0}
    ul {margin-top:0px; margin-bottom:0px}
    li {padding-left:8px}

    .tab {
      width:126px;
      height:21px;
      text-align:center;
      color:white;
      font-weight:bold;
      font-size:110%;
      background-image:url(../artwork/tab_off.gif)
    }
    .tab a {
      display: block;
      width: 100%;
      color:white;
      text-decoration: none;
    }
    .tab.on {
      background-image:url(../artwork/tab_on.gif)
    }
    table.map-session {
      border: 0;
      padding: 6px 0 2px 0;
      width:100%;
      color:#1E3287
    }
    table.map-session td {
      white-space: nowrap;
    }
    hr.head-line {
      border:0;
      height:1px;
      color:#E5E5E5;
      background-color:#E5E5E5;
      width:100%
    }
    .map-objectives {
      list-style: none;
      padding: 0;
    }
    .map-objectives ul {
      list-style: disc;
    }
    a.unmap {
      color: #f00;
    }
    .map-objective {
      position: absolute;
      top: -9999px;
      left: -9999px;
    }
    .objective {
      padding: 2px 0 2px 16px;
      display: block;
      width: 100%;
    }
    .objective.selected {
      background-color: #B3C8E8;
      color: black;
    }
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/jquery.dynamicpapers.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/jquery.rquerystring.js"></script>
<?php
echo $cfg_js_root;
$langstrings = array('mustselectobjectives', 'ajaxerror', 'ajaxconfirm');
echo LangUtils::render_JS_strings($langstrings, $string);
?>
</head>

<body>
<?php
require '../include/dynamic_paper_options.inc';
?>

  <div id="content" class="content">
    <table class="header" style="font-size:80%">
      <tr>
        <th>
          <div class="breadcrumb">
            <a href="../staff/index.php"><?php echo $string['home'] ?></a>
<?php
if ($module != '') { ?>
            <img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $module . '"><?php echo $module . ' - ' . $module_name ?></a>
<?php
}
?>
          </div>
          <div style="font-size:220%; font-weight:bold; margin-left:10px"><?php echo $string['dynamicpapers'] . ' - ' . $string['mappedobjectives'] ?></div>
        </th>
        <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(147); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
      </tr>
    </table>

    <table class="header" style="font-size:80%">
    <tr>
      <th style="padding-top:1px">
        <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:252px">
          <tr>
            <td class="tab on"><?php echo $string['bysession']; ?></td>
            <td class="tab"><a href="dynamic_papers_by_question.php?module=<?php echo $module; ?>"><?php echo $string['byquestion']; ?></a></td>
          </tr>
        </table>
      </th>
      <th style="width:100%; text-align:right"><?php echo render_year_dropdown($years, $module, $string, $session) ?></th>
    </tr>
    <tr><td colspan="2" style="background-color:#1E3C7B">&nbsp;</td></tr>
    <tr>
      <td colspan="2">
<?php

if (count($objsBySession[$module]) > 0) {
?>
        <form action="./" method="post">
<?php
  $ul_start = false;

  foreach($objsBySession as $module => $sessions ) {
    if (count($objsBySession) > 1) {
      echo "<h1>$module " . $string['objectives'] . "</h1>";
    }
    foreach($sessions as $identifier => $sessionData) {
      if ($ul_start) {
        echo '</ul>';
      }
      echo "<table class=\"map-session\"><tr><td>";
      if ($sessionData['class_code'] != '') {
        echo $sessionData['class_code'] . ': ';
      }
      echo $sessionData['title'] . ' <a href="' . urlencode($sessionData['source_url']) . '"><img src="../artwork/small_link.png" width="12" height="12" alt="" /></a> ';

      echo "</td><td style=\"width:98%\"><hr class=\"head-line\" /></td></tr></table>\n";
      if (isset($sessionData["objectives"]) and is_array($sessionData["objectives"])) {
        echo '<ul class="map-objectives">';
        foreach ($sessionData["objectives"] as $id => $objectives) {
          $mapped = is_array($objectives['mapped']);
          $map_class = ($mapped) ? 'mapped' : 'unmapped';
          echo '<li class="' . $map_class . '"><input type="checkbox" id="obj-mapped' . $objectives['id'] . '" name="obj-mapped" value="' . $objectives['id'] . '" class="map-objective" /> <label for="obj-mapped' . $objectives['id'] . '" class="objective">' . htmlentities($objectives['content']) . '</label>';
          if ($mapped) {
            echo ' <ul>';
            foreach ($objectives['mapped'] as $q_id) {
              echo "<li><a href=\"../question/view_question.php?q_id=" . $q_id . "\" target=\"_blank\">" . $temp_array[$q_id]['leadin'] . "</a> <a href=\"#\" rel=\"{$objectives['id']}_{$q_id}\" class=\"unmap\">Unmap</a></li>";
            }
            echo'</ul>';
          }
          echo '</li>';
        }
        echo '</ul>';
      }
    }
  }
  if ($ul_start) {
    echo '</ul>';
  }
} else {
?>
     <p><?php printf($string['nosessions'], $session) ?></p>
<?php
}
$mysqli->close();
?>
        <input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
        <input type="hidden" name="session" id="session" value="<?php echo $session ?>" />
        </form>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
