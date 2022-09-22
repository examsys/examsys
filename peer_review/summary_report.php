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
 * Displays a 'Class Totals' like report for a peer review paper type.
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);
// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_title        = $propertyObj->get_paper_title();
$calendar_year  = $propertyObj->get_calendar_year();
$type                       = $propertyObj->get_rubric();
$marking                = $propertyObj->get_marking();
$review_type        = $propertyObj->get_display_question_mark();
$question_no        = $propertyObj->get_question_no();
require_once 'summary_report.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['reviewsummary']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/popup_menu.css" />
  <style type="text/css">
    td {font-size:110%}
    .fn {color:#808080}
    .num {padding-top:1px; padding-bottom:1px; padding-left:15px; text-align:right; border-bottom:solid #EEEEEE 1px}
    .errnum {color:#C00000; padding-top:1px; padding-bottom:1px; padding-left:15px; text-align:right; border-bottom:solid #EEEEEE 1px}
    .title {padding-left:10px}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/summaryreportinit.min.js"></script>

</head>

<body>
<?php
  require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
?>
<div id="menudiv" class="popupmenu">
  <div class="popup_row" id="item1">
    <div class="popup_icon"><img src="../artwork/peer_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['Review Form'] ?></div>
  </div>

  <div class="popup_row" id="item2">
    <div class="popup_icon"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['Student Profile']; ?></div>
  </div>
</div>

<?php
  echo "<div style=\"font-size:80%\">\n";
echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
if (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
} elseif (isset($_GET['folder'])) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
}
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
echo '<div class="page_title">' . $string['reviewsummary'] . '</div>';
echo "</div>\n";
?>
<?php

  // Work out ordring
if (isset($_GET['ordering']) and $_GET['ordering'] == 'asc') {
    $ordering = 'desc';
    $ordering_img = '<img src="../artwork/desc.gif" width="9" height="7" style="padding-left:5px" />';
} else {
    $ordering = 'asc';
    $ordering_img = '<img src="../artwork/asc.gif" width="9" height="7" style="padding-left:5px" />';
}
if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
} else {
    $sortby = 'surname';
}
if (isset($_GET['percent'])) {
    $percent =  $_GET['percent'];
} else {
    $percent = 100;
}

if (isset($_GET['meta1'])) {
    $meta1 = $_GET['meta1'];
} else {
    $meta1 = '';
}

  // Write out headings
  $query_string = "percent=$percent&paperID=$paperID&startdate=$startdate&enddate=$enddate&repmodule=" . $_GET['repmodule'] . '&repcourse=' . $_GET['repcourse'] . "&meta1=$meta1";
$heading = array('title' => $string['title'], 'surname' => $string['surname'], 'first_names' => $string['firstnames'], 'student_id' => $string['studentid'], 'have_review' => $string['reviewed'], 'group' => $type);
if ($review_type == 1) {
    $heading['review_no'] = $string['reviews'];
}
  $i = 1;
foreach ($questions as $questionID => $tmp_data) {
    $heading[$questionID] = $string['q'] . $i;
    $i++;
}
  $heading['overall'] = $string['overall'];
if (count($user_data) == 0) {
    echo $notice->info_strip('No students found', 80) . "</body>\n</html>\n";
    exit;
}
?>
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0">
  <thead>
<?php
  echo '<tr><th></th>';
foreach ($heading as $k => $h) {
    echo '<th class="' . $k . '">&nbsp;' . $h . '</th>';
}
  echo "<th class=\"num\">&nbsp;</th></tr>\n";
?>
  </thead>

  <tbody>
<?php
  // Take the arrays and form one master array which can be sorted for on-screen display.
  $master_array = array();
$user_number = 0;
foreach ($user_data as $student_userID => $student) {
    if ($student_userID > 0) {
        $master_array[$user_number]['icon'] = ($user_data[$student_userID]['have_review']) ? 'peer_16.gif' : 'peer_review_retired_16.png';
        $mean_total = 0;
        $master_array[$user_number]['userid'] = $student_userID;
        $master_array[$user_number]['student_id'] = $student['student_id'];
        $master_array[$user_number]['title'] = $student['title'];
        $master_array[$user_number]['surname'] = $student['surname'];
        $master_array[$user_number]['first_names'] = $student['first_names'];
        if ($user_data[$student_userID]['have_review']) {
            $master_array[$user_number]['have_review'] = 'Complete';
        } else {
            $master_array[$user_number]['have_review'] = 'Missing';
        }
        $master_array[$user_number]['group'] = $student['group'];
        if ($review_type == 1) {
            if (isset($student['review_no'])) {
                $master_array[$user_number]['review_no'] = $student['review_no'];
                $master_array[$user_number]['group_no'] = (count($groups[$student['group']]) - 1);
                if ($student['review_no'] < (count($groups[$student['group']]) - 1)) {
                    $master_array[$user_number]['reviews'] = $student['review_no'] . '/' . (count($groups[$student['group']]) - 1);
                } else {
                    $master_array[$user_number]['reviews'] = $student['review_no'] . '/' . (count($groups[$student['group']]) - 1);
                }
            } else {
                $master_array[$user_number]['reviews'] = '0';
            }
            $q_no = 1;
            foreach ($questions as $questionID => $tmp_data) {
                if (isset($student['means'][$questionID])) {
                    if ($_GET['percent'] == '1') {
                        $master_array[$user_number]["q$q_no"] = round($student['percent'][$questionID], 0) . '%';
                    } else {
                        $master_array[$user_number]["q$q_no"] = padDecimals($student['means'][$questionID], 1);
                    }
                    $mean_total += $student['means'][$questionID];
                } else {
                    $master_array[$user_number]["q$q_no"] = '';
                }
                $q_no++;
            }
            if ($_GET['percent'] == '1') {
                if (array_key_exists('total_percent', $student)) {
                    $master_array[$user_number]['overall'] = round($student['total_percent'][$questionID], 0);
                } else {
                    $master_array[$user_number]['overall'] = '';
                }
            } else {
                $master_array[$user_number]['overall'] = padDecimals($mean_total / $question_no, 2);
            }
        } else {
            $q_no = 1;
            foreach ($questions as $questionID => $tmp_data) {
                if (isset($user_data[0]['data'][$questionID][$student_userID])) {
                    $master_array[$user_number]["q$q_no"] = $user_data[0]['data'][$questionID][$student_userID];
                } else {
                    $master_array[$user_number]["q$q_no"] = '';
                }
                $q_no++;
            }
        }

        $user_number++;
    }
}

  // Sort the data.
  $master_array = \sort::array_csort($master_array, $sortby, $ordering);

for (
    $i = 0; $i < $user_number; $i++
) {
    if ($master_array[$i]['student_id'] != '') {
        echo '<tr style="cursor:default" id="res' . $i . '" data-paperid="' . $paperID . '" data-userid="' . $master_array[$i]['userid'] . '">';
        echo '<td class="greyln col"><img src="../artwork/' . $master_array[$i]['icon'] . '" width="16" height="16" alt="" /></td>';
        echo '<td class="greyln col">' . $master_array[$i]['title'] . '</span></td>';
        echo '<td class="greyln col">' . $master_array[$i]['surname'] . '</span></td>';
        echo '<td class="greyln col">' . $master_array[$i]['first_names'] . '</td>';
        echo '<td class="greyln col">' . $master_array[$i]['student_id'] . '</td>';
        if ($master_array[$i]['have_review'] == 'Complete') {
            echo '<td class="greyln col">' . $string['Complete'] . '</td>';
        } else {
            echo '<td class="greyln col" style="color:#C00000">' . $string['Missing'] . '</td>';
        }
        echo '<td class="greyln col">' . $master_array[$i]['group'] . '</td>';
        if ($review_type == 1) {
            if (isset($master_array[$i]['review_no'])) {
                if ($master_array[$i]['review_no'] < $master_array[$i]['group_no']) {
                    echo '<td class="errnum">' . $master_array[$i]['reviews'] . '</td>';
                } else {
                    echo '<td class="num">' . $master_array[$i]['reviews'] . '</td>';
                }
            } else {
                echo '<td class="errnum">0</td>';
            }
            $q_no = 1;
            foreach ($questions as $questionID => $tmp_data) {
                echo '<td class="num">' . $master_array[$i]["q$q_no"] . '</td>';
                $q_no++;
            }
            if ($_GET['percent'] == '1') {
                echo '<td class="num">' . $master_array[$i]['overall'] . "%</td>\n";
            } else {
                echo '<td class="num">' . $master_array[$i]['overall'] . '</td>';
            }
        } else {
            $q_no = 1;
            foreach ($questions as $questionID => $tmp_data) {
                echo '<td class="num">' . $master_array[$i]["q$q_no"] . '</td>';
                $q_no++;
            }
            echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
        }
        echo "<td class=\"num\">&nbsp;</td></tr>\n";
    }
}
?>
  </tbody>
</table>
</div>
</body>
</html>
