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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/mapping.inc';
  require '../include/feedback.inc';
  $paperID = $_GET['paperID'];
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Rogō: <?php echo $string['learningobjectiveanalysis'] . ' ' . $cfg_install_type; ?></title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
h1 {margin-left:15px; font-size:18pt}
p {margin-left:15px; margin-right:15px}
.h {background-color:#F1F5FB; color:black}
.q_no {text-align:right; vertical-align:top}
.grey {color:#808080}
img {border: none;}
li {list-style:none; padding-bottom:5px}
</style>
<link rel="stylesheet" type="text/css" href="../css/breadcrumb.css" />
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
  // Get some paper properties
  getPaperProperties($mysqli, $paperID);
  
  if ($_GET['percent'] != 100 AND $_GET['percent'] != '') {
    $percent = $_GET['percent'];
  } else {
    $percent = 100;
  }
  
  $student_no = 0;
  $user_total = 0;
  $question_data = getCohortData($mysqli, $moduleID, $startdate, $enddate, $_GET['repdegree'], $_GET['repmodule'], '%', $paperID, $paper_type, $_GET['direction'], $student_no, $user_total, $percent);
  
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $paper_title = $paper_title . ' - ' . $_GET['repmodule'] . ' students only';
  } else {
    $paper_title = $paper_title;
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
  
  echo '<tr><td class="h">';
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
  
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">" . $string['learningobjectiveanalysis'] . "</span></td><td class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"2\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";

  if ($student_no == 0) {
    echo "</table>\n<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;This paper has not been attempted by anyone.</td></tr></table>\n<div>\n</body>\n</html>";
    exit;
  }
  echo '</table>';
  
  $qid_list = substr($qid_list,0,-1); 
  $objByModule = getObjectivesByMapping($moduleID, $session, $paperID, $qid_list, $mysqli);
  unset($objByModule['none_of_the_above']);  
  if (count($objByModule) == 0) {
    echo "</table>\n<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;This paper has not been mapped to any learning objectives.</td></tr></table>\n";
  } else {
    foreach ($objByModule as $module => $mappings) {
      foreach ($mappings as $id => $mappingData) {
        if ($mappingData['session']['class_code'] != '') {
          $sessiontitle = $mappingData['session']['class_code'];
        } else {
          $sessiontitle = $mappingData['session']['title'];
        }
        $objectives[$id] = $mappingData;
        foreach($mappingData['mapped'] as $q_id) {
          if (isset($objectives[$id]['totalpos_sum'])) {
            $objectives[$id]['totalpos_sum'] += $question_data[$q_id]['totalpos'];
          } else {
            $objectives[$id]['totalpos_sum'] = $question_data[$q_id]['totalpos'];
          }
          if (isset($objectives[$id]['mark_sum'])) {
            $objectives[$id]['mark_sum'] += $question_data[$q_id]['mark'];
          } else {
            $objectives[$id]['mark_sum'] = $question_data[$q_id]['mark'];
          }
          $objectives[$id]['q_ids'][] = $q_id;
          $objectives[$id]['session']['sessiontitle'] = $sessiontitle;
        }
        $objectives[$id]['ratio'] = $objectives[$id]['mark_sum']/$objectives[$id]['totalpos_sum'] * 100;
      }
    }
    $sortby = 'ratio';
    $ordering = 'desc';
    $objectives = array_csort($objectives,$sortby,$ordering);

    //Display the feedback
    ?>
    <br /><div align="center"><table cellpadding="4" cellspacing="0" border="0" width="95%" style="background-color:#E4EEFC; border:1px solid #B5C4DF">
    <tr><td style="text-align:left"><table cellpadding="2" cellspacing="0" border="0">
    <?php
      echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['totalcandidate'] . '</td><td>' . number_format($user_total) . '</td></tr>';
      if ($_GET['percent'] != 100 AND $_GET['percent'] != '') {
        if ($_GET['direction'] == 'desc') {
          echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['uppersize'] . '</td><td>' . $_GET['percent'] . '% (' . $student_no . ' ' . $string['candidates'] . ')</td></tr>';
        } else {
          echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['lowersize'] . '</td><td>' . $_GET['percent'] . '% (' . $student_no . ' ' . $string['candidates'] . ')</td></tr>';
        }
      }
    ?>
    <tr><td style="margin:0px; font-weight:bold; text-align:right"><img src="../artwork/ok_comment.png" width="16" height="16" alt="<?php echo $string['completely']; ?>" /></td><td><?php echo $string['key1']; ?></td></tr>
    <tr><td style="margin:0px; font-weight:bold; text-align:right"><img src="../artwork/minor_comment.png" width="16" height="16" alt="<?php echo $string['partically']; ?>" /></td><td><?php echo $string['key2']; ?></td></tr>
    <tr><td style="margin:0px; font-weight:bold; text-align:right"><img src="../artwork/major_comment.png" width="16" height="16" alt="<?php echo $string['mostly']; ?>" /></td><td><?php echo $string['key3']; ?></td></tr>
    <tr><td style="margin:0px; font-weight:bold; text-align:right"><img src="../artwork/small_link.png" width="12" height="12" alt="<?php echo $string['shortcut']; ?>" /></td><td><?php echo $string['key4']; ?></td></tr>
    </table></td></tr>
    </table></div>
    <h1><?php echo $string['learningobjectives']; ?></h1>
    <p><?php printf($string['msg'], count($objectives)); ?></p>
    <?php
    echo "<blockquote><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
    foreach($objectives as $id => $obj_data) {
      if($obj_data['ratio'] > 79.9) {
       $img_src = '../artwork/ok_comment.png';
       $session_string = '';
      } else if ($obj_data['ratio'] > 40.9) {
       $img_src = '../artwork/minor_comment.png';
      } else {
       $img_src = '../artwork/major_comment.png';    
      }
      if (isset($obj_data['session']['identifier'])) {
        $tmp_identifier = $obj_data['session']['identifier'];
      } else {
        $tmp_identifier = '';
      }
      if (isset($obj_data['session']['specificguide'])) {
        $session_string = "&nbsp;&nbsp;<a target=\"_blank\" href=\"http://www.nle.nottingham.ac.uk/displayMediGuide.php?module=" . $module . "&session=" . $session . "&specificguide=" . $obj_data['session']['specificguide'] . "&mk=" . $tmp_identifier . "\"><img src=\"../artwork/small_link.png\" width=\"12\" height=\"12\" /></a>&nbsp;<a target=\"_blank\" href=\"http://www.nle.nottingham.ac.uk/displayMediGuide.php?module=" . $module . "&session=" . $session . "&specificguide=" . $obj_data['session']['specificguide'] . "&mk=" . $tmp_identifier . "\">" . $obj_data['session']['sessiontitle'] . "</a>";
      }
      echo "<tr><td><img src=\"$img_src\" alt=\"" . $obj_data['mark_sum'] . ' out of ' . $obj_data['totalpos_sum'] . " objectives acquired\" width=\"16\" height=\"16\" /></td><td>" . floor(($obj_data['mark_sum']/$obj_data['totalpos_sum'])*100) . "%</td><td>" . $obj_data['content'] . " $session_string</td></tr>\n";
    }
    echo "</table></blockquote>\n";
  }
  ?>
<br />
</body>
</html>
