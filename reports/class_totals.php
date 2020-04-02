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
 * Class Totals report.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

set_time_limit(0);

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/feedback.inc';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
if (isset($_GET['recache']) and $_GET['recache'] == '1') {
    $report->compile_report(true);  // Force a re-cache
} else {
    $report->compile_report(false);
}

$user_results = $report->get_user_results();
$paper_buffer = $report->get_paper_buffer();
$cohort_size  = $report->get_cohort_size();
$stats        = $report->get_stats();
$ss_pass      = $report->get_ss_pass();
$ss_hon       = $report->get_ss_hon();
$question_no  = $report->get_question_no();
$log_late     = $report->get_log_late();
$user_no      = $report->get_user_no();

// Check for unmarked calculation questions against the cohort subset selected by the user.
$unmarked = $propertyObj->unmarked_enhancedcalc($studentsonly);
$nonsummative_papertypes = array ('0', '1');
// If a summative exam (2) only mark if exam is not active. If Formative or Progressive (0,1) always mark.
if (($paper_type == '2' and $unmarked and !$propertyObj->is_active()) or (in_array($paper_type, $nonsummative_papertypes) and $unmarked)) {
    ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
    <title><?php echo page::title($string['classtotals']); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
    <link rel="stylesheet" type="text/css" href="../css/warnings.css" />

    <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src='../js/classtotalsinit.min.js'></script>
  </head>
  <body>
    <?php
    require '../include/toprightmenu.inc';

    echo draw_toprightmenu(30);

    echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:80%\">\n";
    echo '<tr><th class="h">';

    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';

    if (isset($_GET['folder']) and $_GET['folder'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

    echo '<span style="margin-left:10px; font-size:200%; color:black"><strong>' . $string['classtotals'] . '</strong> - ' . $string['markingcalcquestions'] . "</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></th></tr>\n";

    echo '</table>';

    echo "<div class=\"marking\"><img src=\"../artwork/large_spin.gif\" widht=\"32\" height=\"32\" style=\"float:left; padding-right:10px\" />\n";
    echo '<div id="msg">' . $string['marking'] . "</div>\n";
    echo "</div>\n";
    ?>

  <input type="hidden" id="paperID" value="<?php echo $paperID ?>" />
  <input type="hidden" id="startdate" value="<?php echo $startdate ?>" />
  <input type="hidden" id="enddate" value="<?php echo $enddate; ?>" />
  <input type="hidden" id="markall" value="1" />
    <?php
    // JS utils dataset.
    $render = new render($configObject);
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    ?>
  </body>
  </html>
    <?php
    exit();
}

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['classtotals']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/popup_menu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />

  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/classtotalsinit.min.js'></script>

</head>


<body>
<div id="noteDiv" class="studentnote">
  <div class="popup_close"><img src="../artwork/close_note.png" class="popupclose" alt="Close" /></div>
  <div id="noteMsg"></div>
</div>

<div id="accessDiv" class="studentaccess">
  <div class="popup_close"><img src="../artwork/close_note.png" class="popupclose" alt="Close" /></div>
  <div id="accessMsg"></div>
</div>

<div id="toiletDiv" class="toiletbreak">
  <div class="popup_close"><img src="../artwork/close_note.png" class="popupclose" alt="Close" /></div>
  <div id="toiletMsg"></div>
</div>

<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu(30);
?>
<div id="menudiv" class="popupmenu">
  <div class="popup_row" id="item1">
    <div class="popup_icon"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['examscript'] ?></div>
  </div>

  <div class="popup_row" id="item2">
    <div class="popup_icon"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['feedback']; ?></div>
  </div>

  <div class="popup_divider_row">
    <div class="popup_icon"></div>
    <div class="popup_title"><img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></div>
  </div>

  <div class="popup_row" id="item3">
    <div class="popup_icon"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['studentprofile'] ?></div>
  </div>

  <div class="popup_row" id="item4">
    <div class="popup_icon"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['newnote'] ?></div>
  </div>

  <div class="popup_divider_row">
    <div class="popup_icon"></div>
    <div class="popup_title"><img src="../artwork/popup_divider.png" width="100%" height="3" alt="-" /></div>
  </div>

  <div class="popup_row" id="item5">
    <div class="popup_icon"><img src="../artwork/guest_account_16.png" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['reassigntouser']; ?></div>
  </div>
  <?php
    // Only allow reset of timer for Progress tests and Remote Summative exams.
    if ($paper_type == '1' or ($paper_type == '2' and $configObject->get_setting('core', 'summative_remote'))) {
        $class = 'popup_row';
    } else {
        $class = 'popup_row_disabled';
    }
    ?>
  <div class="<?php echo $class ?>">
      <div class="popup_icon"><img src="../artwork/clock_16.png" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item6"><?php echo $string['resettimer']; ?></div>
  </div>

  <div class="popup_row" id="item7">
    <div class="popup_icon"><img id="log_late_icon" src="../artwork/log_late_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['latesubmissions']; ?></div>
  </div>
</div>
<?php
for (
    $i = -100; $i <= 100; $i++
) {
    $distribution[$i] = 0;
}

$notes = PaperNotes::get_all_notes_by_paper($paperID, $mysqli);

$toilet_breaks = ToiletBreaks::get_all_breaks_by_paper($paperID, $mysqli);

if ($marking == '0') {
    $marking_label = $string['%'];
    $marking_key = 'percent';
} else {
    $marking_label = $string['adjusted%'];
    $marking_key = 'adj_percent';
}

// Output table heading
$table_order = array('', 'Title', $string['surname'], $string['firstnames'], $string['studentid'], $string['course'], $string['mark'], $marking_label, $string['classification'], $string['rank'], $string['decile'], $string['starttime'], $string['duration']);
if ($configObject->get_setting('core', 'system_hostname_lookup')) {
    $table_order[] = $string['hostnames'];
} else {
    $table_order[] = $string['ipaddress'];
}
if ($paper_type == '2') {
    $table_order[] = $string['room'];
}

$metadata_cols = array();
if (isset($user_results[0])) {
    foreach ($user_results[0] as $key => $val) {
        if (strrpos($key, 'meta_') !== false) {
            $table_order[] = ucfirst(str_replace('meta_', '', $key));
            $metadata_cols[$key] = $key;
        }
    }
}

$cols = count($table_order);

echo "<div style=\"font-size:80%\">\n";
echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';

if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
} elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

$report_title = $string['classtotals'];
if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title .= ' <span style="font-weight: normal">(' . module_utils::get_moduleid_from_id($_GET['repmodule'], $mysqli) . ' ' . $string['studentsonly'] . ')</span>';
} elseif (isset($_GET['percent']) and $_GET['percent'] < 100) {
    if ($ordering == 'desc') {
        $report_title .= ' <span style="font-weight: normal">(' . $string['top'] . ' ' . $_GET['percent'] . '%)</span>';
    } else {
        $report_title .= ' <span style="font-weight: normal">(' . $string['bottom'] . ' ' . $_GET['percent'] . '%)</span>';
    }
}

echo "<div class=\"page_title\">$report_title</div>";
echo "</div>\n";

// Warning display banners
$report->check_late_submission_warnings();
$report->check_unmarked_textbox_warnings();
$report->check_unmarked_enhancedcalc_warnings();
$report->check_temp_account_warnings();

// Output table header
echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:110%; width:100%\">\n";
echo "<thead>\n";
if (isset($user_results[0])) {
    echo "<tr>\n";
    foreach ($table_order as $col_title) {
        echo "<th class=\"vert_div\">$col_title</th>\n";
    }
    echo "</tr>\n";
}
echo "</thead>\n";

if ($sortby == 'classification') {
    $sortby = 'mark';
}

$percent_decimals = $configObject->get_setting('core', 'rpt_percent_decimals');
$absent_no = 0;
$scatter_data = '';

echo "<tbody>\n";
$guestusers = false;
for ($i = 0; $i < $user_no; $i++) {
    extract($user_results[$i]);

    if ($user_results[$i]['visible'] == 1) {
        if (strpos($user_results[$i]['username'], 'user') !== 0) {
            $reassign = 'n';
        } else {
            $reassign = 'y';
        }

        if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
            $role_css = 'staff';
        } else {
            $role_css = '';
        }

        if ($user_results[$i]['display_started'] == '') {
            // Setup the row for an absent user.
            $bg_color = '#FFC0C0';
            $late_submissions = '';
            $class = '';
            $user_results[$i]['attempt'] = 0;
            ?>
<tr class="nonattend" id="res<?php echo $i + 1 ?>" data-metadataid="" data-paperid="<?php echo $paperID ?>" data-cryptname="<?php echo $propertyObj->get_crypt_name() ?>" data-userid="<?php echo $userID; ?>" data-papertype="<?php echo $paper_type; ?>" data-reassign="<?php echo $reassign ?>" data-late="<?php echo $late_submissions ?>" data-percent="<?php echo $percent; ?>"><td>&nbsp;</td>
            <?php
        } else {
            // Setup the row for a user who took the exam.
            if (isset($log_late[$user_results[$i]['metadataID']])) {
                $late_submissions = 'y';
            } else {
                $late_submissions = 'n';
            }
            echo '<tr id="res' . ($i + 1) . '"';
            if ($user_results[$i]['questions'] < $question_no) {
                $scatter_data .= "0\n0\n";
                $class = 'redln';
            } else {
                if (strpos($user_results[$i]['username'], 'user') === 0) {
                    $class = 'guestln';
                } else {
                    $class = 'greyln';
                }
                $temp_location = round($user_results[$i]['percent']);
                if (isset($distribution[$temp_location])) {
                    $distribution[$temp_location]++;
                } else {
                    $distribution[$temp_location] = 1;
                }
                $scatter_data .= $temp_location . "\n" . $user_results[$i]['duration'] . "\n";
            }
            if (isset($log_late[$user_results[$i]['metadataID']])) {
                $icon = 'log_late_16.gif';
                $alt = $string['displayexamscript'];
            } elseif ($user_results[$i]['questions'] < $question_no) {
                $icon = 'incomplete_paper_icon.gif';
                $alt = $string['notcompleted'];
            } elseif ($user_results[$i]['paper_type'] == '0') {
                $icon = 'formative_16.gif';
                $alt = $string['formative'];
            } elseif ($user_results[$i]['paper_type'] == '1') {
                $icon = 'progress_16.gif';
                $alt = $string['progress'];
            } elseif ($user_results[$i]['paper_type'] == '2') {
                $icon = 'summative_16.gif';
                $alt = $string['summative'];
            } elseif ($user_results[$i]['paper_type'] == '3') {
                $icon = 'survey_16.gif';
                $alt = $string['displaysurvey'];
            } elseif ($user_results[$i]['paper_type'] == '5') {
                $icon = 'offline_16.gif';
                $alt = $string['displaypaper'];
            }
            echo ' style="cursor:default" data-metadataid="' . $user_results[$i]['metadataID'] . '" data-paperid="' . $paperID . '" data-cryptname="' . $propertyObj->get_crypt_name() . '" data-userid="' . $user_results[$i]['userID'] . '" data-papertype="' . $user_results[$i]['paper_type'] . '" data-reassign="' . $reassign . '" data-late="' . $late_submissions . '" data-percent="' . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . '"';
            echo "><td class=\"$class $role_css\"><img src=\"../artwork/$icon\" title=\"$alt\" alt=\"$alt\" class=\"picon\" /></td>";
        }

        // Display the student names and any relevant note and icons.
        if (strpos($user_results[$i]['username'], 'user') === 0) {
            echo "<td class=\"$class tmpacc $role_css\">Mr</td>";
            echo "<td class=\"$class tmpacc $role_css\">Guest</td>";
            echo "<td class=\"$class tmpacc $role_css\">" . str_replace('User', 'Account #', $user_results[$i]['surname']);
            $guestusers = true;
        } else {
            echo "<td class=\"$class $role_css\">" . $user_results[$i]['title'] . '</td>';
            echo "<td class=\"$class $role_css\">" . $user_results[$i]['surname'] . '</td>';
            echo "<td class=\"$class $role_css\">" . $user_results[$i]['first_names'];
        }
        if ($report->has_special_need($user_results[$i]['userID']) or $user_results[$i]['attempt'] > 1 or isset($toilet_breaks[$user_results[$i]['userID']])) {
            echo '&nbsp;&nbsp;';
        }
        if ($report->has_special_need($user_results[$i]['userID'])) {
            echo '<img src="../artwork/accessibility_16.png" class="accessibility" data-id="' . $user_results[$i]['userID'] . '" alt="' . $string['alternativearrangements'] . '" title="' . $string['viewaccessibility'] . '" />';
        }
        $student_id = $user_results[$i]['username'];
        if (!empty($user_results[$i]['attempt']) and $user_results[$i]['attempt'] > 1) {
            echo '&nbsp;<img src="../artwork/resit.png" width="16" height="16" alt="Resit" title="' . $string['resitcandidate'] . '" />';
        }
        if (isset($notes[$user_results[$i]['userID']]) and $notes[$user_results[$i]['userID']] == 'y') {
            echo '<img src="../artwork/notes_icon.gif" alt="Notes" data-paperid="' . $paperID . '" data-id="' . $user_results[$i]['userID'] . '" class="note" title="' . $string['viewstudentnote'] . '" />';
        }
        if (isset($toilet_breaks[$user_results[$i]['userID']])) {
            foreach ($toilet_breaks[$user_results[$i]['userID']] as $toilet_break) {
                echo '<img src="../artwork/wc.png" alt="Toilet" class="icon16_active" data-id="' . $toilet_break . '"/>';
            }
        }
        echo '</td>';

        if ($user_results[$i]['student_id'] == '') {
            if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
                echo "<td class=\"grey $class $role_css\">&nbsp;</td>";
            } else {
                echo "<td class=\"grey $class $role_css\">" . $string['unknown'] . '</td>';
            }
        } else {
            echo "<td class=\"$class $role_css\">" . $user_results[$i]['student_id'] . '</td>';
        }
        echo "<td class=\"$class $role_css\">" . $user_results[$i]['student_grade'] . '</td>';

        // Display result information.
        if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
            echo '<td colspan="' . (9 + count($metadata_cols)) . '" style="text-align:center">&lt;' . $string['noattendance'] . "&gt;</td></tr>\n";
            $absent_no++;
        } else {
            //$user_results[$i]['mark'] += 1;   // Use for testing the Class Totals/Exam Script checking script.

            if ($user_results[$i]['classification'] == 'Fail') {
                echo "<td class=\"mk $class fail r $role_css\">";
                if ($user_results[$i]['marking_complete'] == '0') {
                    echo '<img src="../artwork/small_yellow_warning_icon.gif"  width="12" height="11" title="' . $string['markingnotcomplete'] . '" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
                }
                echo $user_results[$i]['mark'] . '</td>';
                echo "<td class=\"$class fail r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class fail $role_css\">&nbsp;" . $string['fail'] . '</td>';
            } else {
                if ($user_results[$i]['classification'] == 'Distinction') {
                    echo "<td class=\"mk $class dist r $role_css\">";
                    if ($user_results[$i]['marking_complete'] == '0') {
                        echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" "' . $string['markingnotcomplete'] . '" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
                    }
                    echo $user_results[$i]['mark'] . '</td>';
                    echo "<td class=\"dist $class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class dist $role_css\">&nbsp;" . $string['distinction'] . '</td>';
                } else {
                    echo "<td class=\"mk $class r $role_css\">";
                    if ($user_results[$i]['marking_complete'] == '0') {
                        echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" "' . $string['markingnotcomplete'] . '" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
                    }
                    echo $user_results[$i]['mark'] . '</td>';
                    echo "<td class=\"$class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class $role_css\">&nbsp;" . $string['pass'] . '</td>';
                }
            }
            // Rank column
                echo "<td class=\"$class r $role_css\">" . $user_results[$i]['rank'] . '</td>';
            // Decile column
                echo "<td class=\"$class r $role_css\">" . $user_results[$i]['decile'] . '</td>';
            // Start Time column
                echo "<td class=\"$class $role_css\">" . $user_results[$i]['display_started'] . '</td>';
            // Duration column
                echo "<td class=\"$class $role_css\">" . $report->formatsec($user_results[$i]['duration']);
            if ($late_submissions == 'y') {
                echo '&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" title="' . $string['markingnotcomplete'] . '" alt="' . $string['markingnotcomplete'] . '"  width="12" height="11" />';
            }
                echo '</td>';

                echo "<td class=\"$class $role_css\">" . $user_results[$i]['ipaddress'] . '</td>';
            if ($paper_type == 2) {
                echo "<td class=\"$class $role_css\">" . $user_results[$i]['room'] . '</td>';
            }

            // Display any associated metadata
            if (count($metadata_cols) > 0) {
                foreach ($metadata_cols as $type) {
                    if (isset($user_results[$i][$type])) {
                              echo "<td class=\"$class $role_css\">&nbsp;" . $user_results[$i][$type] . '</td>';
                    } else {
                        echo "<td class=\"$class $role_css\">&nbsp;</td>";
                    }
                }
            }
                echo "</tr>\n";
        }
    }
}
  echo "<tbody>\n</table>\n";

  // Summary information after the cohort listing.
  // ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  $scatter_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_scatter.dat', 'w');              // Scatter plot data
  fwrite($scatter_file, $scatter_data . "\n");
  fclose($scatter_file);

  $distribution_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat', 'w');   // Distribution data
  fwrite($distribution_file, serialize($distribution) . "\n");
  fclose($distribution_file);

if ($user_no > 0) {
    //Check for any paper notes
    echo '<table border="0" class="subheading"><tr><td><nobr>' . $string['papernotes'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'" . $configObject->get('cfg_long_date_time') . "'), note_workstation FROM paper_notes WHERE paper_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($note, $note_date, $note_workstation);
    while ($result->fetch()) {
        $lab_name = '';
        $result2 = $mysqli->prepare('SELECT name FROM labs, client_identifiers WHERE labs.id = client_identifiers.lab AND address = ?');
        $result2->bind_param('s', $note_workstation);
        $result2->execute();
        $result2->bind_result($lab_name);
        $result2->fetch();
        $result2->close();
        echo "<div class=\"papernote\"><strong>$note_date</strong><p>$note</p><span style=\"font-size:80%\">$note_workstation";
        if ($lab_name != '') {
            echo " ($lab_name)";
        }
        echo "</span></div>\n";
    }
    $result->close();

    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    $exam_announcements = $exam_announcementObj->get_announcements();
    echo '<table border="0" class="subheading"><tr><td><nobr>' . $string['midexamclarifications'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo '<tr><td colspan="' . $cols . "\" height=\"9\"><table cellspacing=\"0\" cellpadding=\"2\">\n";
    foreach ($exam_announcements as $exam_announcement) {
        $msg = $exam_announcement['msg'];
        if (substr_count($msg, '<p>')) {
            $msg = str_replace('<p>', '', $msg);
            $msg = str_replace('</p>', '', $msg);
        }

        echo '<tr><td class="q_no">Q' . $exam_announcement['q_number'] . '</td><td class="q_msg">(' . $exam_announcement['created'] . ')' . $msg . "</td></tr>\n";
    }
    echo "</table>\n";

    echo '<table border="0" class="subheading"><tr><td><nobr>' . $string['distributionchart'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

    echo '<div class="graph"><img src="draw_distribution_chart.php?adjust=' . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark&q1=" . $stats['q1'] . '&q2=' . $stats['q2'] . '&q3=' . $stats['q3'] . "\" width=\"830\" height=\"300\" alt=\"Distribution Chart\" /></div>\n";

    echo '<table border="0" class="subheading"><tr><td><nobr>' . $string['scatterplot'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo '<div class="graph"><img src="draw_scatter_plot.php?adjust=' . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></div>\n";


    // Display summary -------------------------------------------------------------------------------------
    echo '<table class="graph" border="0" style="width:100%">';
    echo '<tr><td class="subheading" style="width:50px">' . $string['summary'] . '</td><td style="width:48%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td><td>&nbsp;&nbsp;</td><td class="subheading" style="width:40px">' . $string['deciles'] . '</td><td style="width:30%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td><td>&nbsp;&nbsp;</td><td class="subheading" style="width:40px">' . $string['quartiles'] . "</td><td style=\"width:100%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr>\n";
    echo '<tr><td colspan="2" style="width:33%">';

    echo "<table border=\"0\" style=\"font-size:110%\">\n";
    echo '<tr><td class="field" style="width:170px">' . $string['paper'] . "</td><td colspan=\"3\">$paper</td></tr>\n";
    echo '<tr><td class="field">' . $string['cohortsize'];
    if ($_GET['percent'] < 100) {
        if ($ordering == 'desc') {
            echo ' (' . $string['top'] . ' ' . $_GET['percent'] . '%)';
        } else {
            echo ' (' . $string['bottom'] . ' ' . $_GET['percent'] . '%)';
        }
    }

    $size_msg = ($cohort_size < $user_no) ? $cohort_size . $string['of'] . $user_no : $user_no;
    echo "</td><td class=\"r\" style=\"width:60px\">$size_msg</td>";
    if (($stats['completed_no'] + $stats['out_of_range']) < $user_no) {
        echo '<td>(' . ($user_no - $stats['completed_no'] - $stats['out_of_range']) . ' ' . $string['candidatenotcomplete'] . ')</td>';
    } else {
        echo '<td>';
        if ($absent_no == 1) {
            echo "<span style=\"color:#C00000\">($absent_no " . $string['candidateabsent'] . ')</span>';
        } elseif ($absent_no > 1) {
            echo "<span style=\"color:#C00000\">($absent_no " . $string['candidatesabsent'] . ')</span>';
        }
        echo '</td><td>&nbsp;</td>';
    }
    echo "</tr>\n";

    if ($cohort_size > 0) {
        $percent_failures = round(($stats['failures'] / $cohort_size) * 100);
        $percent_passes = round(($stats['passes'] / $cohort_size) * 100);
        $percent_honours = round(($stats['honours'] / $cohort_size) * 100);
    } else {
        $percent_failures = 0;
        $percent_passes = 0;
        $percent_honours = 0;
    }

    echo '<tr><td class="field">' . $string['failureno'] . '</td><td class="r">' . $stats['failures'] . '</td><td>(' . $percent_failures . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo '<tr><td class="field">' . $string['passno'] . '</td><td class="r">' . $stats['passes'] . '</td><td>(' . $percent_passes . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo '<tr><td class="field">' . $string['distinctionno'] . '</td><td class="r"> ' . $stats['honours'] . '</td><td>(' . $percent_honours . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";

    echo '<tr><td class="field">' . $string['totalmarks'] . '</td><td class="r">';
    if ($report->get_total_marks() < $report->get_orig_total_marks()) {
        echo '<span class="exclude">' . $report->get_orig_total_marks() . '</span>&nbsp;&nbsp;';
    }
    echo $report->get_total_marks() . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo '<tr><td class="field">' . $string['passmark'] . "</td><td class=\"r\">$pass_mark%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    if ($marking == '1') {
        echo '<tr><td class="field">' . $string['randommark'] . '</td><td class="r">' . number_format($report->get_total_random_mark(), 2, '.', ',') . "</td><td>&nbsp;</td></tr>\n";
        if ($stats['completed_no'] > 0) {
            if ($report->get_total_marks() > 0) {
                echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="r">' . round($stats['mean_mark'], 1) . '</td><td>(' . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
            } else {
                echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="grey r">' . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
            }
        } else {
            echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="grey r">' . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
    } elseif ($marking == '0') {
        if ($stats['completed_no'] > 0) {
            echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="r">' . round($stats['mean_mark'], 1) . '</td><td>(' . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
        } else {
            echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="grey r">' . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
    } else {
        echo '<tr><td class="field">' . $string['ss'] .  '</td><td class="r">' . round($ss_pass, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        if ($ss_hon > 0) {
            echo '<tr><td class="field">' . $string['ssdistinction'] . '</td><td class="r">' . MathsUtils::formatNumber($ss_hon, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
        if ($stats['completed_no'] > 0) {
            echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="r">' . round($stats['mean_mark'], 1) . '</td><td>(' . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
        } else {
            echo '<tr><td class="field">' . $string['meanmark'] . '</td><td class="grey r">' . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
    }
    $mid_point = round($cohort_size / 2) - 1;
    echo '<tr><td class="field">' . $string['medianmark'] . '</td><td class="r">' . round($stats['median_mark'], 1) . '</td><td>(' . MathsUtils::formatNumber($stats['median_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
    if ($stats['completed_no'] == 0) {
        echo '<tr><td class="field">' . $string['stdevmark'] . '</td><td class="grey r">' . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
        echo '<tr><td class="field">' . $string['stdevmark'] . '</td><td class="r">' . number_format($stats['stddev_mark'], 2, '.', ',') . '</td><td>(' . MathsUtils::formatNumber($stats['stddev_percent'], 2) . "%)</td><td>&nbsp;</td></tr>\n";
    }
    echo '<tr><td class="field">' . $string['maxmark'] . '</td><td class="r">' . $stats['max_mark'] . '</td><td>(' . number_format($stats['max_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo '<tr><td class="field">' . $string['minmark'] . '</td><td class="r">' . $stats['min_mark'] . '</td><td>(' . number_format($stats['min_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo '<tr><td class="field">' . $string['range'] . '</td><td class="r">' . $stats['range'] . '</td><td>(' . number_format($stats['range_percent']) . "%)</td><td>&nbsp;</td></tr>\n";

    if ($stats['completed_no'] <= 1) {
        echo '<tr><td class="field">' . $string['averagetime'] . '</td><td class="grey r">' . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
        echo '<tr><td class="field">' . $string['averagetime'] . '</td><td class="r">' . $report->formatsec(round($stats['total_time'] / $stats['completed_no'], 0)) . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    }
    if ($report->get_display_excluded() != '') {
        echo '<tr><td class="field">' . $string['excludedquestions'] . '</td><td colspan="3">' . $report->get_display_excluded() . "</td></tr>\n";
    }
    if ($report->get_display_experimental() != '') {
        echo '<tr><td class="field">' . $string['skippedquestions'] . '</td><td colspan="3">' . $report->get_display_experimental() . "</td></tr>\n";
    }
    echo "</table></td>\n";

    echo '<td></td>';

    // Deciles
    $suffix = array('', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th' ,'th');
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table border=\"0\" style=\"font-size:110%\">\n";
    for ($i = 1; $i < 10; $i++) {
        echo '<tr><td style="width:40px">' . $i;
        echo ($language == 'en') ? $suffix[$i] : '.';
        echo '</td><td>' . MathsUtils::formatNumber($stats["decile$i"], 1) . "%</td></tr>\n";
    }
    echo "</table></td>\n";

    echo '<td></td>';

    // Quartiles
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table border=\"0\" style=\"font-size:110%\">\n";
    echo '<tr><td style="width:40px">Q1</td><td>' . MathsUtils::formatNumber($stats['q1'], 1) . "%</td></tr>\n";
    echo '<tr><td style="width:40px">Q2</td><td>' . MathsUtils::formatNumber($stats['q2'], 1) . "%</td></tr>\n";
    echo '<tr><td style="width:40px">Q3</td><td>' . MathsUtils::formatNumber($stats['q3'], 1) . "%</td></tr>\n";

    echo "</table></td>\n";

    echo "</tr></table>\n";

    $emailtemplatedir = rogo_directory::get_directory('email_templates');
    // Email Class -----------------------------------------------------------------------------------------
    if ($paper_type < 2 and isset($_POST['emailclass']) and $_POST['emailclass'] == 'yes') {
        // Save the latest template to disk.
        $file = fopen($emailtemplatedir->fullpath($userObject->get_user_ID() . '.txt'), 'w');
        fwrite($file, $userObject->get_email() . "\n");
        fwrite($file, $_POST['ccaddress'] . "\n");
        fwrite($file, $_POST['bccaddress'] . "\n");
        fwrite($file, $_POST['subject'] . "\n");
        fwrite($file, $_POST['emailtemplate'] . "\n");
        fclose($file);

        for ($i = 0; $i < $user_no; $i++) {
            switch ($i) {
                case 25:
                case 50:
                case 75:
                case 100:
                case 125:
                case 150:
                case 175:
                case 200:
                case 225:
                case 250:
                case 275:
                case 300:
                case 325:
                case 350:
                case 375:
                case 400:
                case 425:
                case 450:
                case 475:
                case 500:
                case 525:
                case 550:
                case 575:
                case 600:
                    echo "<tr><td>&nbsp;</td><td colspan=\"8\" height=\"9\">$i sent</td></tr>\n";
                    flush();
                    ob_flush();
            }

            // Perform replacement.
            $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">\n<html><head>\n<title>$paper</title>\n<style type=\"text/css\">\nbody {font-family: Arial,sans-serif; background-color: white; color:black}</style>\n</head>\n<body>";
            $message .= $_POST['emailtemplate'];
            $message = str_replace('{student-title}', $user_results[$i]['title'], $message);
            $message = str_replace('{student-last-name}', $user_results[$i]['surname'], $message);
            $message = str_replace('{student-mark}', $user_results[$i]['mark'], $message);
            $message = str_replace('{student-percent}', number_format($user_results[$i]['percent'], 2, '.', ',') . '%', $message);
            $message = str_replace('{total-paper-mark}', $report->get_total_marks(), $message);
            $message = str_replace('{student-time}', formatsec($user_results[$i]['duration']), $message);
            $message = str_replace('{class-mean-mark}', $stats['mean_mark'], $message);
            $message = str_replace('{class-mean-percent}', $stats['mean_percent'], $message);
            if ($stats['completed_no'] - 1 == 0) {
                $message = str_replace('{class-stdev}', 0, $message);
            } else {
                $message = str_replace('{class-stdev}', number_format($stats['stddev_mark'], 2, '.', ','), $message);
            }
            $message = str_replace('{class-max-mark}', $stats['max_mark'], $message);
            $message = str_replace('{class-min-mark}', $stats['min_mark'], $message);
            if ($stats['completed_no'] == 0) {
                $mean = 0;
            } else {
                $mean = round($stats['total_time'] / $stats['completed_no'], 0);
            }
            $message = str_replace('{class-mean-time}', formatsec($mean), $message);
            $message = str_replace('{random-mark}', number_format($report->get_total_random_mark(), 1, '.', ','), $message);
            $message = str_replace('{paper-title}', $paper, $message);

            $to = $user_results[$i]['email'];

            $subject = $_POST['subject'];
            $subject = str_replace('{total-paper-mark}', $report->get_total_marks(), $subject);
            if ($stats['completed_no'] == 0) {
                $mean = 0;
            } else {
                $mean = round($report->get_total_marks() / $stats['completed_no'], 1);
            }
            $subject = str_replace('{class-mean-mark}', $mean, $subject);
            $subject = str_replace('{class-mean-percent}', $stats['mean_percent'], $subject);
            $subject = str_replace('{class-max-mark}', $stats['max_mark'], $subject);
            $subject = str_replace('{class-min-mark}', $stats['min_mark'], $subject);
            if ($stats['completed_no'] == 0) {
                $mean = 0;
            } else {
                $mean = round($stats['total_time'] / $stats['completed_no'], 0);
            }
            $subject = str_replace('{class-mean-time}', formatsec($mean), $subject);
            $subject = str_replace('{random-mark}', number_format($report->get_total_random_mark(), 1, '.', ','), $subject);
            $subject = str_replace('{paper-title}', $paper, $subject);

            $headers = 'From: ' . $userObject->get_email() . "\n";
            $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=utf8\n";
            if ($_POST['ccaddress'] != '') {
                $headers .= 'cc: ' . $_POST['ccaddress'] . "\n";
            }
            if ($_POST['bccaddress'] != '') {
                $headers .= 'bcc: ' . $_POST['bccaddress'] . "\n";
            }
            $message .= "</body>\n</html>\n";
            mail($to, $subject, $message, $headers) or print '<div>' . $string['couldnotsend'] . " <strong>$to</strong>.</div>";
        }
        echo '<p>' . $string['emailssent'] . '</p>';
    } else {
        if ($paper_type < 2) {
            echo "<div class=\"graph\">\n";
            echo "<form id=\"theform\" name=\"theform\" method=\"post\" autocomplete=\"off\">\n";
            echo '<input id="emailmarks" type="button" value="' . $string['emailclassmarks'] . "\" style=\"margin:10px; width:160px\" />\n";
            echo '<input type="hidden" id="emailclass" name="emailclass" value="" />';
            echo '<input type="hidden" id="emailtemplate" name="emailtemplate" value="" />';
            echo '<input type="hidden" id="ccaddress" name="ccaddress" value="" />';
            echo '<input type="hidden" id="bccaddress" name="bccaddress" value="" />';
            echo '<input type="hidden" id="subject" name="subject" value="" />';
            echo "</form>\n</div>\n";
        }
    }

    $unmarked_questions = false;
    $summative = false;
    $late_answers = false;
    if ($report->unmarked_textbox() or $report->unmarked_enhancedcalc()) {
        $unmarked_questions = true;
    }
    if ($paper_type == 2 or $paper_type == 4) {
        $summative = true;
    }
    if (count($log_late) > 0) {
        $late_answers = true;
    }
    $finished = false;
    if ($propertyObj->get_end_date() <= time()) {
        $finished = true;
    }
    if ($configObject->get_setting('core', 'cfg_gradebook_enabled') and !$guestusers and $finished and !$unmarked_questions and $summative and !$late_answers and !$report->paper_graded()) {
        if (isset($_POST['publishmarks']) and $_POST['publishmarks'] == 'yes') {
            $report->create_gradebook();
            $report->store_grades();
            echo '<p>' . $string['gradepublish'] . '</p>';
        } else {
            echo "<div>\n";
            echo "<form id=\"publishform\" name=\"publishform\" method=\"post\" autocomplete=\"off\">\n";
            echo '<input id="publishmarksbutton" type="button" value="' . $string['publishmarks'] . "\" style=\"margin:10px; width:160px\" />\n";
            echo '<input type="hidden" id="publishmarks" name="publishmarks" value="" />';
            echo "</form>\n</div>\n";
        }
    }
    echo "</table>\n";
} else {
    $msg = sprintf($string['noattempts'], $report->nicedate($startdate), $report->nicedate($enddate));
    echo $notice->info_strip($msg, 100) . "\n</div>\n";
    // JS utils dataset.
    $render = new render($configObject);
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    echo "</body>\n</html>";
    exit;
}
  $mysqli->close();
?>
  <input type="hidden" id="datetime" value="<?php echo $configObject->get('cfg_tablesorter_date_time'); ?>" />
  <input type="hidden" id="markall" value="0" />
  </div>
<?php
  // JS utils dataset.
  $render = new render($configObject);
  $jsdataset['name'] = 'jsutils';
  $jsdataset['attributes']['xls'] = json_encode($string);
  $render->render($jsdataset, array(), 'dataset.html');
  $dataset['name'] = 'dataset';
  $dataset['attributes']['remotesummative'] = $configObject->get_setting('core', 'summative_remote');
  $render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
