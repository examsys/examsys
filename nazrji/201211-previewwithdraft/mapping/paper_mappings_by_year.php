<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/question_types.inc';
require '../include/mapping.inc';
require_once '../classes/paperutils.class.php';

$paperID = $_GET['paperID'];

function getPaper($paperID) {
  global  $mysqli;

  $temp_array = array();

  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  $result = $mysqli->prepare("SELECT paper_title, calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title,  $session, $start_date, $end_date, $paper_type);
  while ($row = $result->fetch()) {
     $temp_array['paper_title'] = $paper_title;
     $temp_array['session'] = $session;
  }
  $result->close();
 
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
  $result = $mysqli->prepare("SELECT random_mark, total_mark, p_id, q_id, q_type, screen, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_last_edited, display_pos FROM (properties, papers, questions) WHERE property_id=? AND paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('ii', $paperID, $paperID);
  $result->execute();
  $result->bind_result($random_mark, $total_mark, $p_id, $q_id, $q_type, $screen, $leadin, $q_media, $q_media_width, $q_media_height, $display_last_edited, $display_pos);
  $temp_array['questionID'] = '';
  while ($result->fetch()) {
    $row_no++;
    $temp_array['questions'][$q_id]['screen'] = $screen;
    $temp_array['questions'][$q_id]['q_type'] = $q_type;
    $temp_array['questions'][$q_id]['leadin'] = trim(str_replace('&nbsp;',' ',(strip_tags($leadin))));
    if (strlen($temp_array['questions'][$q_id]['leadin']) > 160) $temp_array['questions'][$q_id]['leadin'] = substr($temp_array['questions'][$q_id]['leadin'],0,160) . "...";
    $temp_array['questions'][$q_id]['p_id'] = $p_id;
    $temp_array['questions'][$q_id]['q_id'] = $q_id;
    $temp_array['questions'][$q_id]['display_last_edited'] = $display_last_edited;
    $temp_array['questions'][$q_id]['q_media'] = $q_media;
    $temp_array['questions'][$q_id]['q_media_width'] = $q_media_width;
    $temp_array['questions'][$q_id]['q_media_height'] = $q_media_height;
    $temp_array['questions'][$q_id]['display_pos'] = $display_pos;

    $temp_array['questions'][$q_id]['qnumber'] = $display_pos - $info_count;

    if($q_type == 'info') $info_count++;

    $temp_array['total_random_mark'] = $random_mark;
    $temp_array['total_marks'] = $total_mark;
    $temp_array['temp_total_marks'] = $total_mark;
    $temp_array['questionID'] .= $q_id . ',';
  }
  $result->close();
  $temp_array['questionID'] = substr($temp_array['questionID'],0,-1);
  return $temp_array;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Rogō: <?php echo $string['mappingbyyear'] . " $cfg_install_type"; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .q_no {text-align:right; vertical-align:top; cursor:pointer}
    .divider {font-weight:normal; color:#1E3287; padding-left:6px}
    .mapping {font-size:90%; color:#FF6300; font-weight:normal}
    .mapping_exclueded {color:red;font-weight:normal;text-decoration:line-through}
    .unmapped {color:#C0C0C0}
    li {padding-left:32px; text-indent:-16px;}
    a {text-decoration: none}
    .m_s {background-image:url('../artwork/red_hash_background.png'); background-repeat:repeat}
    .o_s {background-color:#99FF99}
    .nm_s {background-color:white}
    td.m_s {border:1px solid #c0c0c0}
    td.o_s {border:1px solid #c0c0c0}
    td.nm_s {border:1px solid #c0c0c0}
    td.obj {border:1px solid #c0c0c0}
    table {border-collapse:collapse}
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
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
    
  $result = $mysqli->prepare("SELECT paper_title,  calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title, $session, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    echo "<table class=\"header\">\n";
    echo '<tr><th>';
    echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
    if ($folder != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      $modules = explode(',', $_GET['module']);
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $modules[0] . '">' . $modules[0] . '</a>';
    }
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
    echo "<div style=\"font-size:220%; font-weight:bold; margin-left:10px\">" . $string['mappedobjectives'] . "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(147); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n</table>\n";
  }
  $result->close();
  
?>
<table class="header">
<tr><th style="padding-top:1px">
  <table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; width:378px">
  <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_session.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['bysession']; ?></td>
  <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)" onclick="window.location.href='paper_mappings_by_question.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['byquestion']; ?></td>
  <td style="cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_on.gif)"><?php echo $string['longitudinal']; ?></td>
  </table>
</th><th style="width:100%; text-align:right">&nbsp;</th>
</tr>
<tr><td colspan="4" style="background-color:#1E3C7B">&nbsp;</td></tr>
</table><br/>
<?php

//look for other papers
$papers[$_GET['paperID']] =  getPaper($_GET['paperID']);
$moduleIDs = Paper_utils::get_modules($_GET['paperID'],$mysqli);
$moduleIDs_in = "'" . implode("','",array_keys($moduleIDs)) . "'";
$sql = "SELECT properties.property_id from properties,properties_modules WHERE properties.property_id = properties_modules.property_id AND  idMod IN ($moduleIDs_in) AND properties.property_id != ? AND paper_type = 3 AND paper_title NOT like '%resit%' AND paper_title NOT like '%supplementary%' AND paper_title NOT like '%test%' AND deleted IS NULL AND labs IS NOT NULL AND start_date < ? order by start_date DESC LIMIT 3";
$papersRes = $mysqli->prepare($sql);
$papersRes->bind_param('is', $_GET['paperID'],$start_date);
$papersRes->execute();
$papersRes->bind_result($property_id);
while ($papersRes->fetch()) {
  $papers_tmp[] =  $property_id;
}

$papersRes->close();

if(isset($papers_tmp)) {
  //$papers_tmp = array_reverse($papers_tmp);
  $i = 0;
  foreach ($papers_tmp as $p_id) {
    $papers[$p_id] = getPaper($p_id);
  }
}

foreach ($papers as $p_id => $paper) {
  $moduleIDs = Paper_utils::get_modules($paperID,$mysqli);
  $objsBySession[$p_id] = getObjectives($moduleIDs, $paper['session'], $p_id,$paper['questionID'], $mysqli);
}

$allsession = array();
$n = 0;
foreach ($objsBySession as $p_id => $module) {
  foreach ($module as $moduleID => $sessions) {
    foreach ($sessions as $id => $session) {
      if(isset($session['objectives'])) {
        $objbuffer = $session['objectives'];
        if (!isset($allsession[$moduleID][$id])) {
          $allsession[$moduleID][$id] = $session;
          unset($allsession[$moduleID][$id]['objectives']);
        }
        
        foreach($objbuffer as $obj) {
          $allsession[$moduleID][$id]['objectives'][$obj['id']] = $obj;
          $allsession[$moduleID][$id]['objectives'][$obj['id']]['session'] = $papers[$p_id]['session'];
          $n++;
        }
      }
    }
  }
}

//display
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
//table heading
echo "<tr><th colspan=\"2\"></th>";
$pcount = 0;
foreach($papers as $p) {
  $pcount++;
}
foreach($papers as $p) {
  echo "<th style=\"width:" . round(50/$pcount,0) . "%\">" . $p['paper_title'] . "</th>";
}
echo "</tr>";
foreach($allsession as $moduleID => $module) {
  foreach($module as $identifier => $session) {
    echo '<tr><td colspan="' . ($pcount+2) . '" class="divider">';
    if ($session['class_code'] != '') {
      echo $session['class_code'] . ': ';
    }
    echo $session['title'] . '&nbsp;<a target="_blank" href="' . $session['source_url'] . '"><img src="../artwork/small_link.png" width="12" height="12" /></a></td></tr>';

    foreach ($session['objectives'] as $objID => $obj) {
      echo "<tr>\n\t<td style=\"width:2%\">&nbsp;</td><td style=\"width:48%\" class=\"obj\"><li>" . strip_tags($obj['content'], '<b><i><strong><em><sub><sup>') . "</li></td>\n";
      $objID = $obj['id'];
      foreach($objsBySession as $p_id => $s) {
        if (isset($s[$moduleID]) and array_key_exists($identifier,$s[$moduleID])) {
          $mapped = false;
          if(isset($s[$moduleID][$identifier]['objectives'])) {
            foreach ($s[$moduleID][$identifier]['objectives'] as $tmpObj) {
              if ($tmpObj['id'] == $objID) {
                if (is_array($tmpObj['mapped'])) {
                  echo "\t<td class=\"o_s\">";
                  foreach($tmpObj['mapped'] as $qid) {
                    echo "<span class=\"\" style=\"cursor:pointer\" title=\"" . $papers[$p_id]['questions'][$qid]['leadin'] . "\"><a href=\"../question/view_question.php?q_id=" . $papers[$p_id]['questions'][$qid]['q_id'] . "&qNo=" . $papers[$p_id]['questions'][$qid]['qnumber'] . "\" target=\"_blank\">Q" . $papers[$p_id]['questions'][$qid]['qnumber'] . "</a></span> ";
                  }
                } else {
                  echo "\t<td class=\"nm_s\">";
                }
                echo "&nbsp;</td>\n";
                $mapped = true;
                break;
              }
            }
          }
          if ($mapped == false) {
            echo "\t<td class=\"m_s\">&nbsp;</td>\n";
          }
        } else {
          echo "\t<td class=\"m_s\">&nbsp;</td>\n";
        }
      }
    }
    echo "</tr>";
    echo "<tr><td colspan=\"" . ($pcount+2) . "\">&nbsp;</td></tr>\n";
  }
}
echo "</table>\n";
?>
</div>
</body>
</html>