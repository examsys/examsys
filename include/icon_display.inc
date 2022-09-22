<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

function display_paper_icon($paper_ownerID, $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObj)
{
    global $folder, $module, $string;

    if ($retired != '') {
        echo '<div class="file retired">';
    } else {
        echo '<div class="file">';
    }
    echo '<div class="file_icon">';

    $icon_type = $paper_type;
    if (date('YmdHis', time()) >= $start_date and $start_date != '') {
        $locked = '_locked';
    } else {
        $locked = '';
    }
    if ($retired == '') {
        $title_class = 'blacklink';
    } else {
        $title_class = 'greylink';
    }

    // Take Standards Setter straight to Standards Setter section
    if ($userObj->has_role('Standards Setter')) {
        $href = '../std_setting/index.php?paperID=' . $property_id . "&folder=$folder&module=$module";
    } else {
        $href = '../paper/details.php?paperID=' . $property_id . "&folder=$folder&module=$module";
    }


    if ($paper_ownerID == $userObj->get_user_ID() or $userObj->has_role(array('Admin', 'SysAdmin')) or ($icon_type != '2' and $icon_type != '4')) {
        echo '<a class="blacklink" href="' . $href . '">' . Paper_utils::displayIcon($icon_type, $title, $initials, $surname, $locked, $retired) . "</a></div>\n";
        echo '<div class="file_details"><a href="' . $href . "\" class=\"$title_class\">" . $paper_title . '</a>';
        if ($password != '') {
            echo ' <img src="../artwork/key.png" width="16" height="16" alt="Key" />';
        }
        echo '<br />';
    } else {
        echo '<a class="blacklink" href="' . $href . '">' . Paper_utils::displayIcon(2, $title, $initials, $surname, $locked, $retired) . "</a></div>\n";
        echo '<div class="file_details"><a href="' . $href . '" class="blacklink">' . $paper_title . '</a><br />';
    }
    if ($retired == '') {
        echo '  <span class="grey">';
    } else {
        echo '  <span style="color:#C0C0C0">';
    }
    if ($screens == null) {
        echo '0 ' . $string['screens'] . ', ';
    } elseif ($screens == 1) {
        echo $screens . ' ' . $string['screen'];
    } else {
        echo $screens . ' ' . $string['screens'];
    }
    echo '<br />';
    if ($display_start_date == '') {
        echo ' &lt;unscheduled&gt;';
    } else {
        echo '  ' . $display_start_date;
    }
    if ($icon_type == 2) {
        if ($exam_duration != '') {
            echo ', ' . $exam_duration . $string['mins'];
        }
    } else {
        echo ' to ' . $display_end_date;
    }
    echo "</span></div></div>\n";
}
