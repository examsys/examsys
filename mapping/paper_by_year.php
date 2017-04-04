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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/question_types.php';
require '../include/mapping.inc';
require '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

function getPaper($paperID) {
  global  $mysqli;

  $temp_array = array();

  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  $result = $mysqli->prepare("SELECT paper_title, calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id = ? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper_title,  $session, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
     $temp_array['paper_title'] = $paper_title;
     $temp_array['session'] = $session;
  }
  $result->close();

 // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($result->fetch()) {
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
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['mappingbyyear'] . " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/mapping.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(147);
?>
<div id="content">
<?php
  if (!isset($_GET['ordering'])) {
    $ordering = 'screen';
    $direction = 'asc';
  }

  $result = $mysqli->prepare("SELECT paper_title,  calendar_year, start_date, end_date, paper_type FROM properties WHERE property_id = ? LIMIT 1");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_title, $session, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    echo "<div class=\"head_title\">\n";
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if (isset($_GET['folder']) and $_GET['folder'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      $modules = explode(',', $_GET['module']);
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $modules[0] . '">' . module_utils::get_moduleid_from_id($modules[0], $mysqli) . '</a>';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
    echo "<div class=\"page_title\">" . $string['mappedobjectives'] . "</div>\n</div>\n";
  }
  $result->close();

?>
<table class="header">
<tr><th style="padding-top:1px">
  <table cellpadding="0" cellspacing="0" border="0" style="font-size:90%; width:378px">
  <td class="taboff" onclick="window.location.href='paper_by_session.php?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['bysession']; ?></td>
  <td class="taboff" onclick="window.location.href='paper_by_question.php?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'"><?php echo $string['byquestion']; ?></td>
  <td class="tabon"><?php echo $string['longitudinal']; ?></td>
  </table>
</th><th style="width:100%; text-align:right">&nbsp;</th>
</tr>
<tr><td colspan="4" style="background-color:#1E3C7B">&nbsp;</td></tr>
</table><br/>
<?php

// Look for other papers.
$papers[$paperID] =  getPaper($paperID);
$moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
$moduleIDs_in = "'" . implode("','", array_keys($moduleIDs)) . "'";
$sql = "SELECT properties.property_id from properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND idMod IN ($moduleIDs_in) AND properties.property_id != ? AND paper_type = '2' AND paper_title NOT like '%resit%' AND paper_title NOT like '%supplementary%' AND paper_title NOT like '%test%' AND deleted IS NULL AND start_date < ? ORDER BY start_date DESC LIMIT 3";
$papersRes = $mysqli->prepare($sql);
$papersRes->bind_param('is', $paperID, $start_date);
$papersRes->execute();
$papersRes->bind_result($property_id);
while ($papersRes->fetch()) {
  $papers_tmp[] =  $property_id;
}

$papersRes->close();

if (isset($papers_tmp)) {
  $i = 0;
  foreach ($papers_tmp as $p_id) {
    $papers[$p_id] = getPaper($p_id);
  }
}

$objsBySession = array();
foreach ($papers as $p_id => $paper) {
  $moduleIDs = Paper_utils::get_modules($paperID,$mysqli);
  $objsBySession[$p_id] = getObjectives($moduleIDs, $paper['session'], $p_id,$paper['questionID'], $mysqli);
}

$n = 0;
$allsession     = array();
$id_guid_map    = array();
$guid_id_map    = array();
$obs_canonical  = array();

foreach ($objsBySession as $p_id => $module) {
  if ($module !== 'error') {
    foreach ($module as $moduleID => $sessions) {
      foreach ($sessions as $id => $session) {
        if (isset($session['GUID']) and $session['GUID'] != '') {
          $guid = $session['GUID'];
        } elseif (isset($id_guid_map[$id])){
          $guid = $id_guid_map[$id];
        } else {
          $guid = $id;
        }
        $id_guid_map[$id] = $guid;
        $guid_id_map[$guid][$p_id] = $id;

        if (isset($session['objectives'])) {
          $objbuffer = $session['objectives'];
          if (!isset($allsession[$moduleID][$guid])) {
            $allsession[$moduleID][$guid] = $session;
            unset($allsession[$moduleID][$guid]['objectives']);
          }

          foreach ($objbuffer as $obj) {
            if (isset($obs_canonical[md5($obj['content'])])) {
              $tmp_obj_id = $obs_canonical[md5($obj['content'])];
            } else {
              $tmp_obj_id = $obj['id'];
              $obs_canonical[md5($obj['content'])] = $tmp_obj_id;
            }
            if (isset($allsession[$moduleID][$guid]['objectives'][$tmp_obj_id])) {
              $allsession[$moduleID][$guid]['objectives'][$tmp_obj_id]['id_by_paper'][$p_id] = $obj['id'];
            } else {
              $obj['id_by_paper'][$p_id] = $obj['id'];
              $allsession[$moduleID][$guid]['objectives'][$tmp_obj_id] = $obj;
            }
            $allsession[$moduleID][$guid]['objectives'][$tmp_obj_id]['session'] = $papers[$p_id]['session'];
            $n++;
          }
        }
      }
    }
  }
}

//display
echo "<table style=\"border-collapse:collapse\">\n";
//table heading
echo "<tr><th colspan=\"2\"></th>";
$pcount = 0;
foreach ($papers as $p) {
  $pcount++;
}
foreach ($papers as $p) {
  echo "<th style=\"width:" . round(50/$pcount,0) . "%\">" . $p['paper_title'] . "</th>";
}
echo "</tr>";
foreach ($allsession as $moduleID => $module) {
  foreach ($module as $guid => $session) {
    echo '<tr><td colspan="' . ($pcount+2) . '" class="divider">';
    if ($session['class_code'] != '') {
      echo $session['class_code'] . ': ';
    }
    echo $session['title'] . '&nbsp;<a target="_blank" href="' . $session['source_url'] . '"><img src="../artwork/small_link.png" width="11" height="11" /></a></td></tr>';

    foreach ($session['objectives'] as $objID => $obj) {
      echo "<tr>\n\t<td style=\"width:2%\">&nbsp;</td><td style=\"width:48%\" class=\"obj\"><li>" . strip_tags($obj['content'], '<b><i><strong><em><sub><sup>') . "</li></td>\n";
      foreach ($objsBySession as $p_id => $s) {
        $identifier = isset($guid_id_map[$guid][$p_id]) ? $guid_id_map[$guid][$p_id] : -1;

        if (isset($s[$moduleID]) and array_key_exists($identifier, $s[$moduleID])) {
          $mapped = false;
          if (isset($s[$moduleID][$identifier]['objectives'])) {
            $objID = isset($obj['id_by_paper'][$p_id]) ? $obj['id_by_paper'][$p_id] : -1;
            foreach ($s[$moduleID][$identifier]['objectives'] as $tmpObj) {
              if ($tmpObj['id'] == $objID) {
                if (is_array($tmpObj['mapped'])) {
                  echo "\t<td class=\"o_s\">";
                  foreach($tmpObj['mapped'] as $qid) {
                    echo "<span style=\"cursor:pointer\" title=\"" . $papers[$p_id]['questions'][$qid]['leadin'] . "\"><a class=\"q_prev\" href=\"../question/view_question.php?q_id=" . $papers[$p_id]['questions'][$qid]['q_id'] . "&qNo=" . $papers[$p_id]['questions'][$qid]['qnumber'] . "\" target=\"_blank\">Q" . $papers[$p_id]['questions'][$qid]['qnumber'] . "</a></span> ";
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