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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../classes/paperutils.class.php';

function display_paper_icon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObj) { //todo TAKES USEROBJECT AND PARATMETR FROM FIX
  global $folder, $module, $mysqli, $string;
  
  $paper_modules = Paper_utils::get_modules($property_id, $mysqli);

  if ($retired != '') {
    echo '<div class="file retired">';
  } else {
    echo '<div class="file">';
  }
  echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px" align="center">';

  $icon_type = $paper_type;
  if (date("YmdHis", time()) >= $start_date and $start_date != '') {
    $locked = '_locked';
  } else {
    $locked = '';
  }
  if ($retired == '') {
    $title_class = 'blacklink';
  } else {
    $title_class = 'greylink';
  }
  if ($paper_ownerID == $userObj->get_user_ID() or $userObj->has_role(array('Admin', 'SysAdmin')) or ($icon_type != '2' and $icon_type != '4')) {
    echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\">" . Paper_utils::displayIcon($icon_type, $title, $initials, $surname, $locked, $retired) . "</a></td>\n";
    echo "<td><a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\" class=\"$title_class\">" . $paper_title . '</a>';
    if ($password != '') {
      echo ' <img src="../artwork/key.png" width="16" height="16" alt="Key" />';
    }
    echo '<br />';
  } else {
    $access = false;
    foreach ($paper_modules as $idmod => $individual_module) {
      if ($userObj->is_staff_user_on_module($idmod)) {
        $access = true;
        break;
      }
    }
  
    if ($access == true) {
      echo "<a class=\"blacklink\" href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\">" . Paper_utils::displayIcon(2, $title, $initials, $surname, $locked, $retired) . "</a></td>\n";
      echo "<td><a href=\"../paper/details.php?paperID=" . $property_id . "&folder=$folder&module=$module\" class=\"blacklink\">" . $paper_title . '</a><br />';
    } else {
      if ($icon_type == '2') {
        echo "<img src=\"../artwork/noentry_question_icon_48.png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam (Restricted Access)&#013;Author: " . $title . ' ' . $initials . ' ' . $surname . "\" />\n";
      } else {
        echo "<img src=\"../artwork/noentry_osce.png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station (Restricted Access)&#013;Author: " . $title . ' ' . $initials . ' ' . $surname . "\" />\n";
      }
      echo '</td><td>' . $paper_title;
      if ($password != '') {
        echo ' <img src="../artwork/key.png" width="16" height="16" alt="Key" />';
      }
      echo '<br />';
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
  if (count($paper_modules) == 0) {
    echo ', <span style="color:red">No modules set</span>';
  } else {
    if (isset($_GET['module']) and $_GET['module'] == '') echo ', ' . implode(',', $paper_modules);
  }
  echo '<br />';
  if ($display_start_date == '') {
    echo ' &lt;unscheduled&gt;';
  } else {
    echo '  ' . $display_start_date;
  }
  if ($icon_type == 2) {
    if ($exam_duration != '') echo ', ' . $exam_duration . $string['mins'];
  } else {
    echo ' to ' . $display_end_date;
  }
  echo "</span></td></tr></table></div>\n";
}
?>