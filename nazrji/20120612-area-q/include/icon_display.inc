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

function displayIcon($paper_type, $title, $initials, $surname, $shared, $locked) {
  global $string;
  switch ($paper_type) {
    case 0:
      $html = "<img src=\"../artwork/formative" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['formative self-assessment'] ."&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 1:
      $html = "<img src=\"../artwork/progress" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['progress test'] . "&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 2:
      $html = "<img src=\"../artwork/summative" . $shared . $locked . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['summative exam'] . "&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 3:
      $html = "<img src=\"../artwork/survey" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['survey'] . "&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 4:
      $html = "<img src=\"../artwork/osce" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['osce station'] . "&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 5:
      $html = "<img src=\"../artwork/offline" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": " . $string['offline paper'] . "&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
    case 6:
      $html = "<img src=\"../artwork/peer_review" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"" . $string['type'] . ": Peer Review&#013;" . $string['author'] . ": $title $initials $surname\" border=\"0\" />";
      break;
  }
  return $html;
}

function displayPaperIcon2($property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname) {
  global $folder, $string;
  
  echo '<div class="file">';
  echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px" align="center">';
  $icon_type = $paper_type;
  if (date("YmdHis", time()) >= $start_date) {
    $locked = '_locked';
  } else {
    $locked = '';
  }
  echo "<a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=\">" . displayIcon($icon_type, $title, $initials, $surname, '', $locked) . "</a></td>\n";
  echo "</td><td><a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=\" class=\"blacklink\">" . $paper_title . '</a><br />';
  echo '  <span style="color:#808080">';
  if ($screens == NULL) {
    echo '0 ' . $string['screens'] . ', ';
  } elseif ($screens == 1) {
    echo $screens . ' ' . $string['screen'];
  } else {
    echo $screens . ' ' . $string['screens'];
  }
  echo ', <span style="color:red">' . $string['nomodulesset'] . '</span>';
  echo '<br />';
  echo '  ' . $display_start_date;
  if ($icon_type == 2) {
    echo ', ' . $exam_duration . $string['mins'];
  } else {
    echo ' to ' . $display_end_date;
  }
  echo "</td></tr></table></div>\n";
}
  
function displayPaperIcon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $moduleID) {
  global $userroles, $type, $folder, $module, $mysqli, $userID, $teams, $string;
  
  if ($retired != '') {
    echo '<div class="file retired">';
  } else {
    echo '<div class="file">';
  }
  echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px" align="center">';

  $icon_type = $paper_type;
  if (date("YmdHis", time()) >= $start_date) {
    $locked = '_locked';
  } else {
    $locked = '';
  }
  if ($retired == '') {
    $title_class = 'blacklink';
  } else {
    $title_class = 'greylink';
  }
  if ($paper_ownerID == $userID or strpos($userroles,'Admin') !== false or strpos($userroles,'SysAdmin') !== false or ($icon_type != '2' and $icon_type != '4')) {
    echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\">" . displayIcon($icon_type, $title, $initials, $surname, '', $locked, $retired) . "</a></td>\n";
    echo "<td><a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\" class=\"$title_class\">" . $paper_title . '</a><br />';
  } else {
    $access = false;
    $paper_modules = explode(',', $moduleID);
    foreach ($paper_modules as $individual_module) {
      if (in_array($individual_module, $teams)) $access = true;
    }
  
    if ($access == true) {
      echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\">" . displayIcon(2, $title, $initials, $surname, '', $locked, $retired) . "</a></td>\n";
      echo "<td><a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\" class=\"blacklink\">" . $paper_title . '</a><br />';
    } else {
      if ($icon_type == '2') {
        echo "<img src=\"../artwork/noentry_question_icon_48.png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam (Restricted Access)&#013;Author: " . $title . ' ' . $initials . ' ' . $surname . "\" border=\"0\" />\n";
      } else {
        echo "<img src=\"../artwork/noentry_osce.png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station (Restricted Access)&#013;Author: " . $title . ' ' . $initials . ' ' . $surname . "\" border=\"0\" />\n";
      }
      echo '</td><td>' . $paper_title . '<br />';
    }
  }
  if ($retired == '') {
    echo '  <span style="color:#808080">';
  } else {
    echo '  <span style="color:#C0C0C0">';
  }
  if ($screens == NULL) {
    echo '0 ' . $string['screens'] . ', ';
  } elseif ($screens == 1) {
    echo $screens . ' ' . $string['screen'];
  } else {
    echo $screens . ' ' . $string['screens'];
  }
  if ($moduleID == '') {
    echo ', <span style="color:red">No modules set</span>';
  } else {
    if (isset($_GET['module']) AND $_GET['module'] == '') echo ', ' . str_replace(',',', ',$moduleID);
  }
  echo '<br />';
  echo '  ' . $display_start_date;
  if ($icon_type == 2) {
    if ($exam_duration != '') echo ', ' . $exam_duration . $string['mins'];
  } else {
    echo ' to ' . $display_end_date;
  }
  echo "</span></td></tr></table></div>\n";
}
?>