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

require_once '../include/demo_replace.inc';
require_once '../include/errors.php';
require_once '../include/sort.inc';
require_once './osce.inc';

$demo = is_demo($userObject);

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

$percent      = (isset($_GET['percent'])) ? $_GET['percent'] : 100;
$ordering     = (isset($_GET['ordering'])) ? $_GET['ordering'] : 'asc';
$absent       = (isset($_GET['absent'])) ? $_GET['absent'] : 0;
$sortby       = (isset($_GET['sortby'])) ? $_GET['sortby'] : 'name';
$studentsonly = (isset($_GET['studentsonly'])) ? $_GET['studentsonly'] : 1;
$repcourse    = (isset($_GET['repcourse'])) ? $_GET['repcourse'] : '%';
$repmodule    = (isset($_GET['repmodule'])) ? $_GET['repmodule'] : '';

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper = $propertyObj->get_paper_title();
$crypt_name = $propertyObj->get_crypt_name();

$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();                                                  // Get any questions to exclude.

$report = new ClassTotals($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $mysqli, $string);
$report->load_answers();
$paper_buffer = $report->get_paper_buffer();
$question_no  = $report->get_question_no();

$user_results = load_osce_results($propertyObj, $demo, $configObject, $question_no, $mysqli);

$report->set_user_results($user_results);
$report->generate_stats();
$user_no = $report->get_user_no();

$q_medians = load_osce_medians($mysqli);

if ($propertyObj->get_pass_mark() == 101) {
  $borderline_method = true;
} else {
  $borderline_method = false;
}

if ($borderline_method) {
  $passmark = getBlinePassmk($user_results, $user_no, $propertyObj);
} elseif ($propertyObj->get_pass_mark() == 102) {
  $passmark = 'N/A';
} else {
  $passmark = $propertyObj->get_pass_mark();
}
$distinction_mark = $propertyObj->get_distinction_mark();

set_classification($propertyObj->get_marking(), $user_results, $passmark, $user_no, $string);
$report->set_user_results($user_results);
$report->sort_results();
$user_results = array_csort($user_results, $sortby, $ordering);

$completed_no = 0;
$total_score = 0;
$classifications = array(''=>'', 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 'ERROR'=>0, 'pass' => 0, 'fail' => 0);

for ($i=0; $i<$user_no; $i++) {
  if ($user_results[$i]['metadataID'] != '') {   // No attendance
    $classifications[$user_results[$i]['rating']]++;
    $total_score += $user_results[$i]['mark'];
    $completed_no++;
  }
}

$stats = $report->get_stats();                        // Generate the main statistics

$results_cache = new ResultsCache($mysqli);
if ($results_cache->should_cache($propertyObj, $percent, $absent)) {
  $results_cache->save_paper_cache($paperID, $stats);                 // Cache general paper stats
  
  $results_cache->save_student_mark_cache($paperID, $user_results);   // Cache student/paper marks
  
  $results_cache->save_median_question_marks($paperID, $q_medians);   // Cache the question/paper medians
}

rating_num_text($user_results, $user_no, $propertyObj, $string);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['classtotals'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/class_totals.css" />
  <link rel="stylesheet" type="text/css" href="../css/popup_menu.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/popup_menu.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>   

    function popupPublishMarks() {
      var winwidth = 785;
      var winheight = 150;
      templatewin = window.open("../reports/publishmarks.php","templatewin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      templatewin.moveTo(screen.width/2-350,screen.height/2-275);
    } 
    
    function setVars(metadataID, currentUserID) {
      $('#metadataID').val(metadataID);
      $('#userID').val(currentUserID);
      
      if (metadataID == '') {
        $('#item1').removeClass('popup_row');
        $('#item1').addClass('popup_row_disabled');
        $('#item2').removeClass('popup_row');
        $('#item2').addClass('popup_row_disabled');
      } else {
        $('#item1').addClass('popup_row');
        $('#item1').removeClass('popup_row_disabled');
        $('#item2').addClass('popup_row');
        $('#item2').removeClass('popup_row_disabled');
      }
    }
    
	<?php
		if (count($user_results) > 0) {
	?>
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          // sort on the first column and third column, order asc 
          dateFormat: '<?php echo $configObject->get('cfg_tablesorter_date_time'); ?>',
          sortList: [[2,0],[3,0]] 
        });
      }
     
      $(document).click(function() {
        $('#menudiv').hide();
        $('#toprightmenu').hide();
      });
      
      // View OSCE Script
      $('#item1').click(function() {
        $('#menudiv').hide();
        if ($('#metadataID').val() != '') {
          var winwidth = 750;
          var winheight = screen.height-80;
          window.open("view_form.php?paperID=<?php echo $paperID; ?>&userID=" + $('#userID').val() + "","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        }
      });
      
      // View Feedback
      $('#item2').click(function() {
        $('#menudiv').hide();
        if ($('#metadataID').val() != '') {
          var winwidth = screen.width-80;
          var winheight = screen.height-80;
          window.open("../students/objectives_feedback.php?id=<?php echo $crypt_name; ?>&userID=" + $('#userID').val() + "&metadataID=" + $('#metadataID').val() + "","feedback","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        }
      });
      
      // View student profile
      $('#item3').click(function() {
        $('#menudiv').hide();
        window.location = '../users/details.php?userID=' + $('#userID').val();
      });
      
    });
	<?php
		}
	?>

  </script>
</head>

<body>
<?php
require '../include/toprightmenu.inc';
	
echo draw_toprightmenu();

?>
<div id="menudiv" class="popupmenu">
  <div class="popup_row" id="item1">
    <div class="popup_icon"><img src="../artwork/osce_16.gif" width="16" height="16" alt="" /></div>
    <div class="popup_title"><?php echo $string['oscemarksheet'] ?></div>
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
</div>

<div style="font-size:90%">
<?php
  //output table heading
  if ($borderline_method) {
    $table_order = array(''=>16, 'Title'=>45, $string['surname']=>170, $string['firstnames']=>270, $string['studentid']=>80, $string['course']=>55, $string['total']=>50, $string['rating']=>'rating', $string['classification']=>80, $string['starttime']=>170, $string['examiner']=>100);
  } else {
    $table_order = array(''=>16, 'Title'=>45, $string['surname']=>170, $string['firstnames']=>270, $string['studentid']=>80, $string['course']=>55, $string['total']=>50, $string['classification']=>80, $string['starttime']=>170, $string['examiner']=>100);
  }
  $metadata_cols = array();
  if (isset($user_results[0])){
    foreach ($user_results[0] as $key => $val) {
      if (strrpos($key,'meta_') !== false) {
        $key_display = ucfirst(str_replace('meta_','',$key));
        $table_order[$key_display] = 150;
        $metadata_cols[$key] = $key;
      }
    }
  }
  
  $column_no = count($table_order) + count($metadata_cols);

  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';
  
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = sprintf($string['classtotalsmodule'], $_GET['repmodule']);
  } else {
    $report_title = $string['classtotals'];
  }
  echo "<div class=\"page_title\">$report_title</div>\n";
  echo "</div>\n";

  // Output table header
  echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
  echo "<thead>\n";
  if (isset($user_results[0])) {
    echo "<tr>\n";
    foreach ($table_order as $display => $col_width) {
      echo "<th style=\"width:" . $col_width . "px\" class=\"vert_div\">$display</th>\n";
    }
    echo "</tr>\n";
  }
  echo "</thead>\n<tbody>";
  
	if ($user_no == 0) {
    $msg = sprintf($string['noattempts'], $report->nicedate($startdate), $report->nicedate($enddate));
		echo "</tbody>\n</table>\n" . $notice->info_strip($msg) . "\n</div>\n</body>\n</html>";
    exit;
	}
 
  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['started'] == '') {   // No attendance
      echo "<tr class=\"nonattend\" onclick=\"popMenu(3, event); setVars('', '" . $user_results[$i]['userID'] . "');\"><td>&nbsp;</td><td>" . $user_results[$i]['title'] . "</td><td>" . $user_results[$i]['surname'] . "</td><td>" . $user_results[$i]['first_names'] . "</td><td>" . $user_results[$i]['student_id'] . "</td><td colspan=\"" . ($column_no - 2) . "\" style=\"text-align:center\">&lt;" . $string['noattendance'] . "&gt;</td></tr>\n";
    } else {
      echo "<tr onclick=\"popMenu(3, event); setVars('" . $user_results[$i]['metadataID'] . "', '" . $user_results[$i]['userID'] . "');\">\n";
      echo "<td class=\"greyln\"><img src=\"../artwork/osce_16.gif\" class=\"picon\" /></td>";
      echo '<td class="greyln col">' . $user_results[$i]['title'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['surname'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['first_names'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['student_id'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['grade'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['mark'] . '</td>';
            
      if ($borderline_method) {
        echo '<td class="greyln col">' . $user_results[$i]['rating'] . '</td>';
      }
      
      echo '<td class="greyln col">' . $user_results[$i]['classification'];
			if ($user_results[$i]['killer_fail'] == $string['fail']) {
        echo '&nbsp;<img src="../artwork/skull_16.png" width=16" height="16" alt="skull" />';
      }
      echo '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['display_started'] . '</td>';
      echo '<td class="greyln col">' . $user_results[$i]['examiner'] . '</td>';
      echo "</tr>\n";
    }
  }
  ?>
</tbody>
</table>

<br />
  <?php
  echo "<table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . $string['summary'] . "</td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"line-height:150%\">\n";
  echo "<tr><td align=\"right\" style=\"width:110px\">" . $string['cohortsize'] . "</td><td style=\"text-align:right; width:40px\">" . $user_no . "</td></tr>\n";
  
  if ($borderline_method) {
    echo "<tr><td align=\"right\">" . $string['passmark'] . "</td><td style=\"text-align:right\">" . round($passmark, 2) . "</td><td>% (" . $string['borderlinemethod'] . ")</td></tr>\n";
  } elseif ($propertyObj->get_pass_mark() != 102) {  // Not the N/A option
    echo "<tr><td align=\"right\">" . $string['passmark'] . "</td><td style=\"text-align:right\">" . $propertyObj->get_pass_mark() . "</td><td>%</td></tr>\n";
  }
  
  $labels = get_labels($propertyObj);
  foreach ($labels as $i => $label) {
    echo "<tr><td align=\"right\">" . $string[strtolower($label)] . "</td><td style=\"text-align:right\">" . $classifications[$i] . "</td></tr>\n";
  }
  echo "</table>\n";
  
  $finished = false;
  if ($propertyObj->get_end_date() <= time()) {
    $finished = true;
  }
  if ($configObject->get_setting('core', 'cfg_gradebook_enabled') and $finished and !$report->paper_graded()) {
    if (isset($_POST['publishmarks']) and $_POST['publishmarks'] == 'yes') {
        $report->create_gradebook();
        $report->store_grades();
        echo '<p>' . $string['gradepublish'] . '</p>';
    } else {
        echo "<div>\n";
        echo "<form name=\"theform\" method=\"post\" autocomplete=\"off\">\n";
        echo "<input type=\"button\" value=\"" . $string['publishmarks'] . "\" onclick=\"popupPublishMarks();\" style=\"margin:10px; width:160px\" />\n";
        echo '<input type="hidden" name="publishmarks" value="" />';
        echo "</form>\n</div>\n";
    }
  }
  
  $mysqli->close();
?>
<input type="hidden" id="userID" value="" />
<input type="hidden" id="metadataID" value="" />
</div>
</body>
</html>
