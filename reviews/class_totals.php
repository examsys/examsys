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
* Class Totals (for externals) report.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

set_time_limit(0);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$id = check_var('id', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$paperID = $propertyObj->get_property_id();
if (!ReviewUtils::is_external_on_paper($userObject->get_user_ID(), $paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
}

$paper            = $propertyObj->get_paper_title();
$marking          = $propertyObj->get_marking();
$pass_mark        = $propertyObj->get_pass_mark();
$distinction_mark = $propertyObj->get_distinction_mark();
$paper_type       = $propertyObj->get_paper_type();
$startdate        = $propertyObj->get_raw_start_date();
$enddate          = $propertyObj->get_raw_end_date();

$percent      = 100;
$ordering     = 'asc';
$absent       = 0;
$sortby       = 'name';
$studentsonly = 1;
$repcourse    = '%';
$repmodule    = '';

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->compile_report(false, true);

$user_results = $report->get_user_results();
$paper_buffer = $report->get_paper_buffer();
$cohort_size  = $report->get_cohort_size();
$stats        = $report->get_stats();
$ss_pass      = $report->get_ss_pass();
$ss_hon       = $report->get_ss_hon();
$question_no  = $report->get_question_no();
$log_late     = $report->get_log_late();
$user_no      = $report->get_user_no();

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/popup_menu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/popup_menu.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/class_totals.js"></script>
  <script>
    function setVars(tmpMetadataID, tmpUserID, tmpLogType, tmpReassign, tmpLogLate, tmpPercent, e) {
      $('#metadataID').val(tmpMetadataID);
      $('#userID').val(tmpUserID);
      $('#log_type').val(tmpLogType);
      $('#reassign').val(tmpReassign);
      $('#loglate').val(tmpLogLate);
      $('#percent').val(tmpPercent);

      if (tmpMetadataID == '') {
        $('#item1b').css('color', '#C0C0C0');
        $('#item2b').css('color', '#C0C0C0');
      } else {
        $('#item1b').css('color', '#000000');
        $('#item2b').css('color', '#000000');
      }
    }
    
    var paperID = <?php echo $paperID ?>;
    var crypt_name = '<?php echo $propertyObj->get_crypt_name() ?>';
    
    $(function () {
      $("#maindata").tablesorter({ 
        // sort on the first column and third column, order asc 
        sortList: [[4,0]] 
      });

      $(document).click(function() {
        $('#menudiv').hide();
      });
    });
  </script>
</head>


<body>
<div id="noteDiv" class="studentnote">
<div style="text-align:right; margin-right:5px;"><img onclick="$('#noteDiv').hide();" src="../artwork/close_note.png" class="popupclose" alt="Close" /></div>
<div id="noteMsg"></div>
</div>

<div id="accessDiv" class="studentaccess">
<div style="text-align:right; margin-right:5px;"><img onclick="$('#accessDiv').hide();" src="../artwork/close_note.png" class="popupclose" alt="Close" /></div>
<div id="accessMsg"></div>
</div>

<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu(30);

?>
<div id="menudiv" class="popupmenu">
  <div class="popup_row" onclick="viewScript();">
    <div class="popup_icon"><img src="../artwork/summative_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item1"><?php echo $string['examscript'] ?></div>
  </div>
  
  <div class="popup_row" onclick="viewFeedback();">
    <div class="popup_icon"><img src="../artwork/ok_comment.png" width="16" height="16" alt="" /></div>
    <div class="popup_title" id="item2"><?php echo $string['feedback']; ?></div>
  </div>
</div>
<?php
  for ($i=-100; $i<=100; $i++) $distribution[$i] = 0;

  $notes = PaperNotes::get_all_notes_by_paper($paperID, $mysqli);

  if ($marking == '0') {
    $marking_label = $string['%'];
    $marking_key = 'percent';
  } else {
    $marking_label = $string['adjusted%'];
    $marking_key = 'adj_percent';
  }

  //output table heading
	$table_order = array('', $string['studentid'], $string['course'], $string['mark'], $marking_label, $string['classification'], $string['rank'], $string['decile'], $string['starttime'], $string['duration']);
	if ($configObject->get('cfg_client_lookup') == 'name') {
		$table_order[] = $string['hostnames'];
	} else {
		$table_order[] = $string['ipaddress'];
  }
	if ($paper_type == '2') $table_order[] = $string['room'];
  
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key, 'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = 150;
        $metadata_cols[$key] = $key;
      }
    }
  }

  $cols = count($table_order);
  
  echo "<div style=\"font-size:80%\">\n";
  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo '<div class="breadcrumb"><a href="../reviews/index.php">' . $string['home'] . '</a></div>';
  echo "<div class=\"page_title\">" . $string['classtotals'] . ": <span style=\"font-weight:normal\">" . $paper . "</span></div>";
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
      echo "<th class=\"col\">$col_title</th>\n";
    }
    echo "</tr>\n";
  }
  echo "</thead>\n";
  
  if ($sortby == 'classification') {
    $sortby = 'mark';
  }

  $percent_decimals = $configObject->get('percent_decimals');
  $absent_no = 0;
  $scatter_data = '';

  echo "<tbody>\n";

  for ($i=0; $i<$user_no; $i++) {
    extract($user_results[$i]);

    if ($user_results[$i]['visible'] == 1) {
      if (strpos($user_results[$i]['username'], 'user') !== 0) {
        $reassign = 'n';
      } else {
        $reassign = 'y';
      }

      if ($user_results[$i]['display_started'] == '') {  // Student did not take exam.
        $bg_color = '#FFC0C0';
        $late_submissions = '';
        ?>
        <tr class="nonattend" id="res<?php echo $i+1 ?>" onclick="popMenu(6, event); setVars('', '<?php echo $userID; ?>', '<?php echo $paper_type; ?>', '<?php echo $reassign ?>', '<?php echo $late_submissions ?>', '<?php echo $percent; ?>');"><td>&nbsp;</td>
        <?php
        if ($user_results[$i]['student_id'] == '') {
          echo "<td class=\"grey\">" . $string['unknown'] . "</td>";
        } else {
          echo "<td>" . $user_results[$i]['student_id'] . "</td>";
        }
        echo "<td>" . $user_results[$i]['student_grade'] . "</td><td colspan=\"" . (9 + count($metadata_cols)) . "\" style=\"text-align:center\">&lt;" . $string['noattendance'] . "&gt;</td></tr>\n";
        $absent_no++;
      } else {
        if (isset($log_late[$user_results[$i]['metadataID']])) {
          $late_submissions = 'y';
        } else {
          $late_submissions = 'n';
        }
        echo '<tr id="res' . ($i+1) . '"';
        if ($user_results[$i]['questions'] < $question_no) {
          $scatter_data .= "0\n0\n";
          $class = 'redln';
        } else {
          $class = 'greyln';
          $temp_location = round($user_results[$i]['percent']);
          if (isset($distribution[$temp_location])) {
						$distribution[$temp_location]++;
          } else {
						$distribution[$temp_location] = 1;
					}
					$scatter_data .= $temp_location . "\n" . $user_results[$i]['duration'] . "\n";
        }
        if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
          $role_css = 'staff';
        } else {
          $role_css = '';
        }
        if (isset($log_late[$user_results[$i]['metadataID']])) {
          $icon = 'log_late_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['questions'] < $question_no) {
          $icon = 'incomplete_paper_icon.gif';
          $alt = $string['notcompleted'];
        } elseif ($user_results[$i]['paper_type'] == '0') {
          $icon = 'formative_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '1') {
          $icon = 'progress_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '2') {
          $icon = 'summative_16.gif';
          $alt = $string['displayexamscript'];
        } elseif ($user_results[$i]['paper_type'] == '3') {
          $icon = 'survey_16.gif';
          $alt = $string['displaysurvey'];
        } elseif ($user_results[$i]['paper_type'] == '5') {
          $icon = 'offline_16.gif';
          $alt = $string['displaypaper'];
        }
        echo " style=\"cursor:hand\" onclick=\"popMenu(5, event); setVars('" . $user_results[$i]['metadataID'] . "'," . $user_results[$i]['userID'] . ",'" . $user_results[$i]['paper_type'] . "','$reassign','$late_submissions','" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "');" . "\"";
        echo "><td class=\"$class $role_css\"><img src=\"../artwork/$icon\" class=\"picon\" /></td>";
        $student_id = $user_results[$i]['username'];
        
        if ($user_results[$i]['student_id'] == '') {
          if (strpos($user_results[$i]['roles'], 'Staff') !== false) {
            echo "<td class=\"grey $class $role_css\">&nbsp;";
          } else {
            echo "<td class=\"grey $class $role_css\">" . $string['unknown'];
          }
        } else {
          echo "<td class=\"$class $role_css\">" . $user_results[$i]['student_id'];
        }
        
        if ($report->has_special_need($user_results[$i]['userID']) or isset($notes[$user_results[$i]['userID']]) and $notes[$user_results[$i]['userID']] == 'y') {
          echo '&nbsp;';
        }
        // Add icons
        if (isset($notes[$user_results[$i]['userID']]) and $notes[$user_results[$i]['userID']] == 'y') {
          echo '<img src="../artwork/notes_icon.gif" alt="Notes" class="note" onclick="viewNote(' . $user_results[$i]['userID'] . ', event)" />';
        }
        if ($report->has_special_need($user_results[$i]['userID'])) {
          echo '<img src="../artwork/accessibility_16.png" class="accessibility" onclick="viewAccessibility(' . $user_results[$i]['userID'] . ', event)" alt="' . $string['alternativearrangements'] . '" />';
        }        
        echo '</td>';
        
        echo "<td class=\"$class $role_css\">" . $user_results[$i]['student_grade'] . "</td>";
       			
        if (round($user_results[$i]['percent'], $percent_decimals) < $pass_mark) {
          echo "<td class=\"mk $class fail r $role_css\">";
          if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
          echo $user_results[$i]['mark'] . "</td>";
          echo "<td class=\"$class fail r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class fail $role_css\">&nbsp;" . $string['fail'] . "</td>";
        } else {
          if (round($user_results[$i]['percent'], $percent_decimals) >= $distinction_mark) {
            echo "<td class=\"mk $class dist r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"dist $class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class dist $role_css\">&nbsp;" . $string['distinction'] . "</td>";
          } else {
            echo "<td class=\"mk $class r $role_css\">";
            if ($user_results[$i]['marking_complete'] == '0') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['markingnotcomplete'] . '" />&nbsp;';
            echo $user_results[$i]['mark'] . "</td>";
            echo "<td class=\"$class r $role_css\">" . MathsUtils::formatNumber($user_results[$i]['percent'], $percent_decimals) . "%</td><td class=\"$class $role_css\">&nbsp;" . $string['pass'] . "</td>";
          }
        }
        // Rank column
        echo "<td class=\"$class r $role_css\">" . $user_results[$i]['rank'] . "</td>";
        // Decile column
        echo "<td class=\"$class r $role_css\">" . $user_results[$i]['decile'] . "</td>";
        // Start Time column
        echo "<td class=\"$class $role_css\">" . $user_results[$i]['display_started'] . "</td>";
        // Duration column
        echo "<td class=\"$class $role_css\">" . $report->formatsec($user_results[$i]['duration']);
        if ($late_submissions == 'y') {
          echo '&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" />';
        }
        echo "</td>";

        echo "<td class=\"$class $role_css\">" . $user_results[$i]['ipaddress'] . "</td>";
        if ($paper_type == 2) {
          echo "<td class=\"$class $role_css\">" . $user_results[$i]['room'] . "</td>";
        }

        // Display any associated metadata
        if (count($metadata_cols) > 0) {
          foreach ( $metadata_cols as $type) {
            if (isset($user_results[$i][$type])) {
              echo "<td class=\"$class $role_css\">&nbsp;" . $user_results[$i][$type] . "</td>";
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
  $scatter_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID(). '_scatter.dat', 'w');              // Scatter plot data
  fwrite($scatter_file, $scatter_data . "\n");
  fclose($scatter_file);

  $distribution_file = fopen($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_distribution.dat', 'w');   // Distribution data
  fwrite($distribution_file, serialize($distribution) . "\n");
  fclose($distribution_file);
	
  if ($user_no > 0) {
    //Check for any paper notes
    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['papernotes'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'" . $configObject->get('cfg_long_date_time') . "'), note_workstation FROM paper_notes WHERE paper_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($note, $note_date, $note_workstation);
    while ($result->fetch()) {
      $lab_name = '';
      $result2 = $mysqli->prepare("SELECT name FROM labs, client_identifiers WHERE labs.id = client_identifiers.lab AND address = ?");
      $result2->bind_param('s', $note_workstation);
      $result2->execute();
      $result2->bind_result($lab_name);
      $result2->fetch();
      $result2->close();
      echo "<div class=\"papernote\"><strong>$note_date</strong><p>$note</p><br /><span style=\"font-size:80%\">$note_workstation";
      if ($lab_name != '') echo " ($lab_name)";
      echo "</span></div>\n";
    }
    echo "<br clear=\"all\" />";
    $result->close();

    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    $exam_announcements = $exam_announcementObj->get_announcements();
    echo "<br />\n<table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['midexamclarifications'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo "<tr><td colspan=\"" . $cols . "\" height=\"9\"><table cellspacing=\"0\" cellpadding=\"2\">\n";
    foreach ($exam_announcements as $exam_announcement) {
      $msg = $exam_announcement['msg'];
      if (substr_count($msg, '<p>')) {
        $msg = str_replace('<p>', '', $msg);
        $msg = str_replace('</p>', '', $msg);
      }

      echo "<tr><td class=\"q_no\">Q" . $exam_announcement['q_number'] . "</td><td class=\"q_msg\">(" . $exam_announcement['created'] .")<br />" . $msg . "</td></tr>\n";
    }
    echo "</table>\n";

    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['distributionchart'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

    echo "<div class=\"graph\"><img src=\"../reports/draw_distribution_chart.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark&q1=" . $stats['q1'] . "&q2=" . $stats['q2'] . "&q3=" . $stats['q3'] . "\" width=\"830\" height=\"300\" alt=\"Distribution Chart\" /></div>\n";

    echo "<br /><table border=\"0\" class=\"subheading\"><tr><td><nobr>" . $string['scatterplot'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    echo "<div class=\"graph\"><img src=\"../reports/draw_scatter_plot.php?adjust=" . substr($marking, 0, 1) . "&pmk=$pass_mark&distinction_mark=$distinction_mark\" width=\"830\" height=\"300\" border=\"0\" alt=\"Distribution Chart\" /></div>\n";


    // Display summary -------------------------------------------------------------------------------------
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" style=\"width:100%; font-size:110%\">";
    echo "<tr><td class=\"subheading\" style=\"width:50px\">" . $string['summary'] . "</td><td style=\"width:48%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['deciles'] . "</td><td style=\"width:30%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td><td>&nbsp;&nbsp;</td><td class=\"subheading\" style=\"width:40px\">" . $string['quartiles'] . "</td><td style=\"width:100%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr>\n";
    echo "<tr><td colspan=\"2\" style=\"width:33%\">";

    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td class=\"field\" style=\"width:170px\">" . $string['paper'] . "</td><td colspan=\"3\">$paper</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['cohortsize'];
    
    $size_msg = ($cohort_size < $user_no) ? $cohort_size . $string['of'] . $user_no : $user_no;
    echo "</td><td class=\"r\" style=\"width:60px\">$size_msg</td>";
    if (($stats['completed_no'] + $stats['out_of_range']) < $user_no) {
      echo '<td>(' . ($user_no - $stats['completed_no'] - $stats['out_of_range']). ' ' . $string['candidatenotcomplete'] . ')</td>';
    } else {
      echo '<td>';
      if ($absent_no == 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidateabsent'] . ")</span>";
      } elseif ($absent_no > 1) {
        echo "<span style=\"color:#C00000\">($absent_no " . $string['candidatesabsent'] . ")</span>";
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

    echo "<tr><td class=\"field\">" . $string['failureno'] . "</td><td class=\"r\">" . $stats['failures'] . "</td><td>(" . $percent_failures . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passno'] . "</td><td class=\"r\">" . $stats['passes'] . "</td><td>(" . $percent_passes . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['distinctionno'] . "</td><td class=\"r\"> " . $stats['honours'] . "</td><td>(" . $percent_honours . $string['percentofcohort'] . ")</td><td>&nbsp;</td></tr>\n";

    echo "<tr><td class=\"field\">" . $string['totalmarks'] . "</td><td class=\"r\">";
    if ($report->get_total_marks() < $report->get_orig_total_marks()) echo "<span class=\"exclude\">" . $report->get_orig_total_marks() . "</span>&nbsp;&nbsp;";
    echo $report->get_total_marks() . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['passmark'] . "</td><td class=\"r\">$pass_mark%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    if ($marking == '1') {
      echo "<tr><td class=\"field\">" . $string['randommark'] . "</td><td class=\"r\">" . number_format($report->get_total_random_mark(), 2, '.', ',') . "</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        if ($report->get_total_marks() > 0) {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
        } else {
          echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        }
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } elseif ($marking == '0') {
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    } else {
      echo "<tr><td class=\"field\">" . $string['ss'] .  "</td><td class=\"r\">" . round($ss_pass, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($ss_hon > 0) echo "<tr><td class=\"field\">" . $string['ssdistinction'] . "</td><td class=\"r\">" . MathsUtils::formatNumber($ss_hon, 2) . "%</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ($stats['completed_no'] > 0) {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"r\">" . round($stats['mean_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['mean_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
      } else {
        echo "<tr><td class=\"field\">" . $string['meanmark'] . "</td><td class=\"grey r\">" . $string['nocompletions'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      }
    }
    $mid_point = round($cohort_size / 2) - 1;
    echo "<tr><td class=\"field\">" . $string['medianmark'] . "</td><td class=\"r\">" . round($stats['median_mark'], 1) . "</td><td>(" . MathsUtils::formatNumber($stats['median_percent'], 1) . "%)</td><td>&nbsp;</td></tr>\n";
    if ($stats['completed_no'] == 0) {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['stdevmark'] . "</td><td class=\"r\">" . number_format($stats['stddev_mark'], 2, '.', ',') . "</td><td>(" . MathsUtils::formatNumber($stats['stddev_percent'], 2) . "%)</td><td>&nbsp;</td></tr>\n";
    }
    echo "<tr><td class=\"field\">" . $string['maxmark'] . "</td><td class=\"r\">" . $stats['max_mark'] . "</td><td>(" . number_format($stats['max_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['minmark'] . "</td><td class=\"r\">" . $stats['min_mark'] . "</td><td>(" . number_format($stats['min_percent']) . "%)</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td class=\"field\">" . $string['range'] . "</td><td class=\"r\">" . $stats['range'] . "</td><td>(" . number_format($stats['range_percent']) . "%)</td><td>&nbsp;</td></tr>\n";

    if ($stats['completed_no'] <= 1) {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"grey r\">" . $string['na'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"field\">" . $string['averagetime'] . "</td><td class=\"r\">" . $report->formatsec(round($stats['total_time'] / $stats['completed_no'], 0)) . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    }
    if ($report->get_display_excluded() != '') {
      echo "<tr><td class=\"field\">" . $string['excludedquestions'] . "</td><td colspan=\"3\">" . $report->get_display_excluded() . "</td></tr>\n";
    }
    if ($report->get_display_experimental() != '') {
      echo "<tr><td class=\"field\">" . $string['skippedquestions'] . "</td><td colspan=\"3\">" . $report->get_display_experimental() . "</td></tr>\n";
    }
    echo "</table></td>\n";

    echo "<td></td>";

    // Deciles
    $suffix = array('', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th' ,'th');
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    for ($i=1; $i<10; $i++) {
      echo "<tr><td style=\"width:40px\">" . $i;
			echo ($language == 'en') ? $suffix[$i] : '.';
			echo "</td><td>" . MathsUtils::formatNumber($stats["decile$i"], 1) . "%</td></tr>\n";
    }
    echo "</table></td>\n";

    echo "<td></td>";

    // Quartiles
    echo "<td colspan=\"2\" style=\"width:33%; vertical-align:top\"><table cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td style=\"width:40px\">Q1</td><td>" . MathsUtils::formatNumber($stats['q1'], 1) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q2</td><td>" . MathsUtils::formatNumber($stats['q2'], 1) . "%</td></tr>\n";
    echo "<tr><td style=\"width:40px\">Q3</td><td>" . MathsUtils::formatNumber($stats['q3'], 1) . "%</td></tr>\n";

    echo "</table></td>\n";

    echo "</tr></table>\n<br />";

  } else {
		$msg = sprintf($string['noattempts'], $report->nicedate($startdate), $report->nicedate($enddate));
		echo $notice->info_strip($msg, 100) . "\n</div>\n</body>\n</html>";
    exit;
  }
  $mysqli->close();
?>
<input type="hidden" id="metadataID" value="" /><input type="hidden" id="userID" value="" /><input type="hidden" id="log_type" value="" /><input type="hidden" id="reassign" value="" /><input type="hidden" id="loglate" value="" /><input type="hidden" id="percent" value="" />
</div>
</body>
</html>
