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

check_var('module', 'GET', true, false);

$module = $_GET['module'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>

<body onclick="hideMenus()">
<?php
require '../include/dynamic_paper_options.inc';
?>

<div id="content" class="content">
<?php
if (!isset($_GET['ordering'])) {
  $ordering = 'screen';
  $direction = 'asc';
}

$result = $mysqli->prepare("SELECT fullname FROM modules WHERE moduleid=? LIMIT 1");

$result->bind_param('i', $module);
$result->execute();
$result->bind_result($module_name);
$result->fetch();
$result->close();
?>
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

<?php
  // TODO: How are we handling multiple sessions?  Check the main mapping screen
//$objsBySession = getObjectives($module, $session, null, $questionID_list, $mysqli);
$objsBySession = getObjectives($module, '2011/12', null, '', $mysqli);
unset($objsBySession['none_of_the_above']);


//$old_p_id = 0;
//$row_no = 0;
//$info_count = 0;
//$temp_array = array();
//$questionID_list = '';
//
//$result = $mysqli->prepare("SELECT random_mark, total_mark, q_group, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
//$result->bind_param('ii', $paperID, $paperID);
//$result->execute();
//$result->bind_result($random_mark, $total_mark, $q_group, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
//while ($result->fetch()) {
//  $row_no++;
//  $temp_array[$q_id]['screen'] = $screen;
//  $temp_array[$q_id]['q_type'] = $q_type;
//  $temp_array[$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
//  $temp_array[$q_id]['p_id'] = $p_id;
//  $temp_array[$q_id]['q_id'] = $q_id;
//  $temp_array[$q_id]['display_last_edited'] = $display_last_edited;
//  $temp_array[$q_id]['q_media'] = $q_media;
//  $temp_array[$q_id]['q_media_width'] = $q_media_width;
//  $temp_array[$q_id]['q_media_height'] = $q_media_height;
//  $temp_array[$q_id]['display_pos'] = $display_pos;
//
//  $temp_array[$q_id]['qnumber'] = $display_pos - $info_count;
//
//  if($q_type == 'info') $info_count++;
//
//  $temp_array[$q_id]['q_group'] = $q_group;
//  $total_random_mark = $random_mark;
//  $total_marks = $total_mark;
//  $temp_total_marks = $total_mark;
//  $questionID_list .= $q_id . ',';
//}
//$result->close();
?>
  <table class="header" style="font-size:80%">
  <tr>
    <th style="padding-top:1px">
      <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:252px">
      <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_on.gif)"><?php echo $string['bysession']; ?></td>
      <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_question.php?module=<?php echo $module; ?>'"><?php echo $string['byquestion']; ?></td>
      </table>
    </th>
    <th style="width:100%; text-align:right">&nbsp;</th>
  </tr>
  <tr><td colspan="4" style="background-color:#1E3C7B">&nbsp;</td></tr>
<?php
//$questionID_list = substr($questionID_list,0,-1);
//
//
//$total_random_mark = 0;
//$total_marks = 0;


//if ($row_no > 0) {
if (true) {
 ?>
    <tr>
    <td colspan="5" style="padding:0px">
<?php
  $ul_start = false;

  foreach($objsBySession as $module => $sessions ) {
    if (count($objsBySession) > 1) {
      echo "<tr><td colspan=\"3\"><h1>$module " . $string['objectives'] . "</h1></td></tr>";
    }
    foreach($sessions as $identifier => $sessionData) {
      if ($ul_start) {
        echo '</ul>';
      }
      echo "<tr><td colspan=\"4\" style=\"padding-left:4px\"><table border=\"0\" style=\"padding-top:6px; padding-bottom:2px; width:100%; color:#1E3287\"><tr><td><nobr>";
      if ($sessionData['class_code'] != '') {
        echo $sessionData['class_code'] . ': ';
      }
      echo $sessionData['title'] . ' <a href="' . $sessionData['source_url'] . '"><img src="../artwork/small_link.png" width="12" height="12" alt="" /></a> ';

      echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n</td></tr>\n";
      if (isset($sessionData["objectives"]) and is_array($sessionData["objectives"])) {
        echo '<tr><td colspan="4"><ul>';
        foreach ($sessionData["objectives"] as $id => $objectives) {
          if (is_array($objectives['mapped'])) {
            echo '<li class="mapped">' . $objectives['content'] . ' <span class="mapping">';
            $i = 0;
            foreach ($objectives['mapped'] as $q_id) {
              if (array_key_exists($q_id,$excluded)) {
                $class = 'q_excluded';
              } else {
                $class = 'q_ok';
              }
              if ($i != 0) echo ', ';
              $i++;
              echo "<a class=\"$class\" href=\"../question/view_question.php?q_id=" . $q_id . "&qNo=" . $temp_array[$q_id]['qnumber'] . "\" target=\"_blank\">Q" . $temp_array[$q_id]['qnumber'] . "</a>";
            }
            echo'</span></li>';
          } else {
            //could display unmaped obj here !!
            echo '<li class="unmapped">' . $objectives['content'] . '</li>';
          }
        }
        echo '</ul></td></tr>';
      }
    }

  }
  if ($ul_start) {
    echo '</ul>';
  }
  echo "</td></tr>\n</table>";
}
$mysqli->close();
?>
</table>
</div>
</body>
</html>
