<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/question_types.inc';
  require '../include/mapping.inc';
  require '../include/errors.inc';
  
  check_var('paperID', 'GET', true, false);
 
  $paperID = $_GET['paperID'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: <?php echo $string['mappingbysession'] . " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />

<style style="text/css">
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
<script src="../javascript/staff_help.js" type="text/javascript"></script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/paper_options.inc';
?>

<div id="content" class="content">
<?php
  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }
    
  $result = $mysqli->prepare("SELECT paper_title, moduleID, calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $moduleID, $session, $start_date, $end_date, $paper_type);
  while ($row = $result->fetch()) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:80%\">\n";
    echo '<tr><td style="background-color:#F1F5FB">';
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if ($folder != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
    }
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
    echo "<div style=\"font-size:220%; font-weight:bold; margin-left:10px\">" . $string['mappedobjectives'] . "</div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(147); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n</table>\n";
  }
  $result->close();

  //build excluded array
  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($row = $result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();

  $old_p_id = 0;
  $row_no = 0;
  $info_count = 0;
  $temp_array = array();
  $questionID_list = '';

  $result = $mysqli->prepare("SELECT random_mark, total_mark, q_group, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('ii', $paperID, $paperID);
  $result->execute();
  $result->bind_result($random_mark, $total_mark, $q_group, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
  while ($row = $result->fetch()) {
    $row_no++;
    $temp_array[$q_id]['screen'] = $screen;
    $temp_array[$q_id]['q_type'] = $q_type;
    $temp_array[$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
    if (strlen($temp_array[$q_id]['leadin']) > 160) $temp_array[$row_no]['leadin'] = substr($temp_array[$q_id]['leadin'],0,160) . "...";
    $temp_array[$q_id]['p_id'] = $p_id;
    $temp_array[$q_id]['q_id'] = $q_id;
    $temp_array[$q_id]['display_last_edited'] = $display_last_edited;
    $temp_array[$q_id]['q_media'] = $q_media;
    $temp_array[$q_id]['q_media_width'] = $q_media_width;
    $temp_array[$q_id]['q_media_height'] = $q_media_height;
    $temp_array[$q_id]['display_pos'] = $display_pos;

    $temp_array[$q_id]['qnumber'] = $display_pos - $info_count;

    if($q_type == 'info') $info_count++;

    $temp_array[$q_id]['q_group'] = $q_group;
    $total_random_mark = $random_mark;
    $total_marks = $total_mark;
    $temp_total_marks = $total_mark;
    $questionID_list .= $q_id . ',';
  }
  $result->close();

  $questionID_list = substr($questionID_list,0,-1);
  $total_random_mark = 0;
  $total_marks = 0;
  if ($row_no > 0) {
    ?>
    <table cellpadding="0" cellspacing="0" border="0" style="font-size:80%; background-color:white; width:100%">
    <tr><td style="background-color:#F1F5FB">
    <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:378px; background-color:#F1F5FB">
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_on.gif)"><?php echo $string['bysession']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_question.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['byquestion']; ?></td>
    <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_year.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['longitudinal']; ?></td>
    </table>
    </td><td style="width:100%; background-color:#F1F5FB; text-align:right">&nbsp;</td>
    </tr>
    <tr><td colspan="4" style="background-color:#1E3C7B">&nbsp;</td></tr>
    <?php
      $year_in_title = false;
      $tmp_match = '';
      if (preg_match( '/\d\d\d\d.\d\d\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;
        $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
      } elseif (preg_match( '/\d\d\d\d.\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;
        $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
      } elseif (preg_match( '/\d\d.\d\d/' , $paper_title , $matches) == 1) {
        $year_in_title = true;      
        $tmp_match = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
      }
      if ($year_in_title == true) {
        if ($tmp_match != $session) {
          echo "<tr><td colspan=\"4\"><table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" style=\"width:100%; font-size:100%\">\n";
          echo "<tr><td style=\"width:40px; height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/non_owner_icon.png\" width=\"25\" height=\"30\" alt=\"Warning\" />&nbsp;&nbsp;</td><td colspan=\"7\" style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><strong>" . $string['warning'] . "</strong>&nbsp;&nbsp;&nbsp;";
          printf($string['nomatchsession'], $tmp_match, $session);
          echo "</td></tr>\n</table>\n</td></tr>\n";
        }
      }
    ?>
    <tr>
    <td colspan="5" style="padding:0px">
    <?php
    $ul_start = false;
    
    $objsBySession = getObjectives($moduleID, $session, $paperID, $questionID_list, $mysqli);
    unset($objsBySession['none_of_the_above']);
    foreach($objsBySession as $module => $sessions ) {
      if (count($objsBySession) > 1) {
        echo "<tr><td colspan=\"3\"><h1>$module Objectives</h1></td></tr>";
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
