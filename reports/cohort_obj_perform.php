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

require '../include/staff_auth.inc';
require '../include/mapping.inc';
require '../include/feedback.inc';
require_once '../include/sort.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate = check_var('enddate', 'GET', true, false, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['learningobjectiveanalysis'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <style type="text/css">
    body {font-size:90%}
    h1 {margin-left:15px; font-size:18pt}
    p {margin-left:15px; margin-right:15px}
  </style>
	
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(30);
?>
<div style="font-size:90%">
<?php
  // Get some paper properties
  $propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

  $paper_title = $propertyObj->get_paper_title();
  $paper_type = $propertyObj->get_paper_type();
  $session = $propertyObj->get_calendar_year();

  $moduleID = Paper_utils::get_modules($paperID, $mysqli);
  
  if ($_GET['percent'] != 100 and $_GET['percent'] != '') {
    $percent = $_GET['percent'];
  } else {
    $percent = 100;
  }
  
  $student_no = 0;
  $user_total = 0;
  $question_data = getCohortData($mysqli, $moduleID, $startdate, $enddate, $_GET['repcourse'], $_GET['repmodule'], '%', $paperID, $paper_type, $_GET['ordering'], $student_no, $user_total, $percent);

  echo '<div class="head_title">';
  echo '<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>';
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
  
  echo "<div class=\"page_title\">" . $string['learningobjectiveanalysis'];
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    echo ' (' . module_utils::get_moduleid_from_id($_GET['repmodule'], $mysqli) . ' ' . $string['studentsonly'] . ')';
  }
  echo "</div>\n";

  if ($student_no == 0) {
		echo $notice->info_strip($string['msg1'], 100) . "</div>\n</body>\n</html>\n";
    exit;
  }
  echo '</div>';
  
  $qid_list = substr($qid_list, 0, -1); 
  $objByModule = getObjectivesByMapping($moduleID, $session, $paperID, $qid_list, $mysqli);
  unset($objByModule['none_of_the_above']);

  if (count($objByModule) == 0) {
		echo "</table>" . $notice->info_strip($string['msg2'], 100);
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
    $objectives = array_csort($objectives, $sortby, $ordering, SORT_NUMERIC);

    //Display the feedback
    ?>
    <br /><div class="key"><table cellpadding="2" cellspacing="0" border="0">
    <?php
      echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['totalcandidate'] . '</td><td>' . number_format($user_total) . '</td></tr>';
      if ($_GET['percent'] != 100 and $_GET['percent'] != '') {
        if ($_GET['ordering'] == 'desc') {
          echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['uppersize'] . '</td><td>' . $_GET['percent'] . '% (' . $student_no . ' ' . $string['candidates'] . ')</td></tr>';
        } else {
          echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['lowersize'] . '</td><td>' . $_GET['percent'] . '% (' . $student_no . ' ' . $string['candidates'] . ')</td></tr>';
        }
      }
    ?>
    <tr><td style="margin:0; font-weight:bold; text-align:right"><img src="../artwork/ok_comment.png" width="16" height="16" alt="<?php echo $string['completely']; ?>" /></td><td><?php echo $string['key1']; ?></td></tr>
    <tr><td style="margin:0; font-weight:bold; text-align:right"><img src="../artwork/minor_comment.png" width="16" height="16" alt="<?php echo $string['partically']; ?>" /></td><td><?php echo $string['key2']; ?></td></tr>
    <tr><td style="margin:0; font-weight:bold; text-align:right"><img src="../artwork/major_comment.png" width="16" height="16" alt="<?php echo $string['mostly']; ?>" /></td><td><?php echo $string['key3']; ?></td></tr>
    <tr><td style="margin:0; font-weight:bold; text-align:right"><img src="../artwork/small_link.png" width="11" height="11" alt="<?php echo $string['shortcut']; ?>" /></td><td><?php echo $string['key4']; ?></td></tr>
    </table></div>
    <h1><?php echo $string['learningobjectives']; ?></h1>
    <p><?php printf($string['msg'], count($objectives)); ?></p>
    <?php
    echo "<blockquote><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
    foreach($objectives as $id => $obj_data) {
      $session_string = '';
      if ($obj_data['ratio'] >= 80) {
        $img_src = '../artwork/ok_comment.png';
      } else if ($obj_data['ratio'] >= 50) {
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
        $session_string = "&nbsp;&nbsp;<a target=\"_blank\" href=\"" . $configObject->get_setting('core', 'cfg_nle_url') .
        "/displayMediGuide.php?module=" . $module . "&session=" . $session . "&specificguide=" . $obj_data['session']['specificguide'] .
        "&mk=" . $tmp_identifier . "\"><img src=\"../artwork/small_link.png\" width=\"11\" height=\"11\" /></a>&nbsp;<a target=\"_blank\" href=\"" .
        $configObject->get_setting('core', 'cfg_nle_url') . "/displayMediGuide.php?module=" . $module . "&session=" . $session . "&specificguide=" .
        $obj_data['session']['specificguide'] . "&mk=" . $tmp_identifier . "\">" . $obj_data['session']['sessiontitle'] . "</a>";
      }
      echo "<tr><td><img src=\"$img_src\" alt=\"" . $obj_data['mark_sum'] . ' out of ' . $obj_data['totalpos_sum'] . " objectives acquired\" width=\"16\" height=\"16\" /></td><td>" . floor(($obj_data['mark_sum']/$obj_data['totalpos_sum'])*100) . "%</td><td>" . $obj_data['content'] . " $session_string</td></tr>\n";
    }
    echo "</table></blockquote>\n";
  }
  ?>
<br />
</div>
</body>
</html>
