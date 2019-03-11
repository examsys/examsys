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
?>
<script>
<?php
  $module_list = explode(',', $module);

  if ($properties->get_paper_type() == '3') {
?>
  function go(url, evt) {
    $('#stats_menu').hide();
    if ($('#completerpt').is(':checked')) {
      completerpt = 1;
    } else {
      completerpt = 0;
    }
    window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repcourse=" + $('#repcourse').val() + "&repyear=" + $('#repyear').val() + "&sortby=surname&module=<?php echo $module_list[0]; ?>&folder=<?php echo $folder; ?>&complete=" + completerpt;
    evt.cancelBubble = true;
    return false;
  }
<?php
  } elseif ($properties->get_paper_type() == '6') {
?>
  function go(url, evt) {
    var extra_data = '';

    for (i=1; i<$('#meta_no').val(); i++) {
      meta_ref = 'meta' + i;
      extra_data += '&meta' + i + '=' +$('#' + meta_ref).val();
    }

    $('#stats_menu').hide();
    window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repmodule=" + $('#repmodule').val() + "&repcourse=" + $('#repcourse').val() + extra_data + "&module=<?php echo $module_list[0]; ?>";
  }
  <?php
  } else {
?>
  function go(url, evt) {
    var extra_data = '';

    for (i=1; i<$('#meta_no').val(); i++) {
      meta_ref = 'meta' + i;
      extra_data += '&meta' + i + '=' + $('#' + meta_ref).val();
    }
    
    $('#stats_menu').hide();
    if ($('#absent').is(':checked')) {
      absent = '&absent=1';
    } else {
      absent = '&absent=0';
    }
    if ($('#studentsonly').is(':checked')) {
      studentsonly = '&studentsonly=1';
    } else {
      studentsonly = '&studentsonly=0';
    }
    window.location.href = url + "paperID=" + $('#paperID').val() + "&startdate=" + $('#start_year').val() + $('#start_month').val() + $('#start_day').val() + $('#start_hour').val() + $('#start_minute').val() + "00" + "&enddate=" + $('#end_year').val() + $('#end_month').val() + $('#end_day').val() + $('#end_hour').val() + $('#end_minute').val() + "00" + "&repmodule=" + $('#repmodule').val() + "&repcourse=" + $('#repcourse').val() + "&sortby=name&module=<?php echo $module_list[0]; ?>&folder=<?php echo $folder; ?>&percent=" + $('#percent').val() + absent + studentsonly + "&ordering=" + $('#direction').val() + extra_data;
    
    evt.cancelBubble = true;
    return false;
  }
<?php
  }
?>
  function checkAll() {
    if ($('#direction')[0].selectedIndex == 0) {
      $("#percent").prop("selectedIndex", 7);
    }
  }
</script>

<div class="popup" style="top:60px" id="stats_menu" onclick="showAssStatsMenu(true, event);">
<form name="stats_menu" style="width:640px; border:0 solid #C0C0C0; font-size:110%" autocomplete="off">

<div style="background-color:#F2B100; height:6px"></div>

<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; margin:10px">
<tr>

<?php
  $month_names = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
  $month_nos = array('01','02','03','04','05','06','07','08','09','10','11','12');

  $date_array = getdate();
  
  $now_data = date('Y') . '-' . date('m') . '-' . date('d') . ' ' . date('H') . ':' . date('i') . ':00';
  $now_time = date('U', strtotime($now_data));
  if ($properties->get_start_date() > $now_time) {
    $target_start_date = $properties->get_created();
  } else {
    $target_start_date = $properties->get_start_date();
  }
  if ($properties->get_end_date() > $now_time) {
    $target_end_date = $now_time;
  } else {
    $target_end_date = $properties->get_end_date();
  }

  $start_year = date("Y", $target_start_date);
  $start_month = date("m", $target_start_date);
  $start_day = date("d", $target_start_date);
  $start_hour = date("H", $target_start_date);
  $start_minute = date("i", $target_start_date);
	
  $end_year = date("Y", $target_end_date);
  $end_month = date("m", $target_end_date);
  $end_day = date("d", $target_end_date);
  $end_hour = date("H", $target_end_date);
  $end_minute = date("i", $target_end_date);
	
  if ($end_year > date('Y') or ($end_year == date('Y') and $end_month > date('m'))) {
    $end_day = date('d');
    $end_month = date('m');
  }
  
  if ($start_year > $date_array['year']) {
    $start_year = $date_array['year'];
    $start_month = $date_array['mon'];
    $start_day = $date_array['mday'];
    $start_hour = $date_array['hours'];
    $end_min = $date_array['minutes'];
  } elseif ($start_year == $date_array['year'] and intval($start_month) > $date_array['mon']) {
    $start_month = $date_array['mon'];
    $start_day = $date_array['mday'];
    $start_hour = $date_array['hours'];
    $start_min = $date_array['minutes'];
  } elseif ($start_year == $date_array['year'] and intval($start_month) == $date_array['mon'] and intval($start_day) > $date_array['mday']) {
    $start_day = $date_array['mday'];
    $start_hour = $date_array['hours'];
    $start_min = $date_array['minutes'];
  }
  
  echo "<tr><td>" . $string['dates'] . "</td><td>";
  
  $default_start = $start_year . $start_month . $start_day . $start_hour . $start_minute . '00';
  $start_year = 2001;
  $end_year = ($date_array['year'] + 1);
  echo date_utils::timedate_select('start_', date($default_start), true, $start_year, $end_year, $string);
  
  // ---------------------------------------------------------------------------------
  echo ' ' . $string['to'] . ' ';
  // End Day
  if ($end_year > $date_array['year']) {
    $end_year = $date_array['year'];
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
  } elseif ($end_month > $date_array['mon'] and $end_year == $date_array['year']) {
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
  } elseif ($end_day > $date_array['mday'] and $end_month == $date_array['mon'] and $end_year == $date_array['year']) {
    $end_day = $date_array['mday'];
  }
  if ($end_month < 10) $end_month = '0' . $end_month;
  if ($end_day < 10)   $end_day   = '0' . $end_day;
  
  $default_end = $end_year . $end_month . $end_day . $end_hour . $end_minute . '00';
  $start_year = 2001;
  $end_year = ($date_array['year'] + 1);
  echo date_utils::timedate_select('end_', date($default_end), true, $start_year, $end_year, $string);
  
  echo "&nbsp;<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_daterange'] . "\" />\n</td></tr>\n";
  
  echo "<tr><td>" . $string['course'] . "</td><td><select name=\"repcourse\" id=\"repcourse\" style=\"width:375px\">\n";
  echo "<option value=\"%\">" . $string['anycourse'] . "</option>\n";
  $result = $mysqli->prepare("SELECT DISTINCT student_grade, description FROM log_metadata, courses WHERE log_metadata.student_grade = courses.name AND paperID = ? ORDER BY student_grade");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($student_grade, $description);
  while ($result->fetch()) {
    echo "<option value=\"$student_grade\">$student_grade: $description</option>\n";
  }
  $result->close();
  echo "</select></td></tr>\n";

  $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);

  $checklist = '';
  if (count($moduleIDs) > 0) {
    $ids = array_keys($moduleIDs);
    $stmt = $mysqli->prepare("SELECT checklist FROM modules WHERE id IN (" . implode(',', $ids) . ")");
    $stmt->execute();
    $stmt->bind_result($tmp_checklist);
    $check = array();
    while ($stmt->fetch()) {
      if ($tmp_checklist != '') {
        $tmp = explode(',', $tmp_checklist);
        foreach ($tmp as $c => $type) {
          $check[] = $type;
        }
      }
    }
    $checklist = implode(',', $check);
    $stmt->close();
  }
  
  // Module drop down
  if ($properties->get_paper_type() != '3') {
    echo "<tr><td>" . $string['module'] . "</td><td><select name=\"repmodule\" id=\"repmodule\" style=\"width:150px\">\n";
    echo "<option value=\"\">" . $string['anymodule'] . "</option>\n";
    foreach ($moduleIDs as $modID=>$modCode) {
      echo "<option value=\"" . $modID . "\">" . $modCode . "</option>\n";
    }
    echo "</select></td></tr>\n";
    
    echo "<tr><td>" . $string['cohort'] . "</td><td><select name=\"direction\" id=\"direction\" onchange=\"checkAll();\">\n";
    echo "<option value=\"asc\">" . $string['allcandidates'] . "</option>\n";
    echo "<option value=\"desc\">" . $string['top'] . "</option>\n";
    echo "<option value=\"asc\">" . $string['bottom'] . "</option>\n";
    echo "</select>\n";
    echo "<select name=\"percent\" id=\"percent\">\n";
    echo "<option value=\"1\">1%</option>\n";
    echo "<option value=\"5\">5%</option>\n";
    echo "<option value=\"10\">10%</option>\n";
    echo "<option value=\"15\">15%</option>\n";
    echo "<option value=\"25\">25%</option>\n";
    echo "<option value=\"27\">27%</option>\n";
    echo "<option value=\"33.3\">33.3%</option>\n";
    echo "<option value=\"50\">50%</option>\n";
    echo "<option value=\"100\" selected>100%</option>\n";
    echo "</select>\n";
    echo "</td></tr>\n";
  }
  
  echo "<tr><td colspan=\"2\"><label for=\"absent\">" . $string['incabsentcandidates'] . "</label>&nbsp;<input type=\"checkbox\" name=\"absent\" id=\"absent\" value=\"1\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for=\"studentsonly\">" . $string['studentsonly'] . "</label>&nbsp;<input type=\"checkbox\" name=\"studentsonly\" id=\"studentsonly\" value=\"1\" checked=\"checked\" /> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_studentattempts'] . "\" /></td></tr>\n";

  if ($properties->get_paper_type() == '3') {
    echo "<tr><td>" . $string['year'] . "</td><td><select name=\"repyear\" id=\"repyear\">\n";
    $years = array('%'=>$string['anyyear'],'1'=>$string['year'].' 1','2'=>$string['year'].' 2','3'=>$string['year'].' 3','4'=>$string['year'].' 4','5'=>$string['year'].' 5');
    foreach ($years as $key => $value) {
      echo "<option value=\"$key\">$value</option>\n";
    }
    echo "</select>\n</td></tr>\n";
    echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"completerpt\" name=\"completerpt\" value\"1\" />&nbsp;" . $string['completedatasets'] . "</td></tr>\n";
  }
  
  // Get any metadata fields
  $metadata = array();
  if (count($moduleIDs) > 0) {
    $result = $mysqli->prepare("SELECT DISTINCT type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.moduleid IN ('" . implode("','",$moduleIDs) . "') ORDER BY type, value");
    $result->execute();
    $result->bind_result($meta_type, $meta_value);
    while ($result->fetch()) {
      $metadata[$meta_type][] = $meta_value;
    }
    $result->close();
  }
  
  $meta_no = 1;

  if (count($metadata) > 0) {
    echo "<tr><td colspan=\"2\"><hr style=\"height:1px; color:#C0C0C0; background-color:#C0C0C0; border:0px\" /></td></tr>\n";
  }
  foreach ($metadata as $meta_type=>$value_array) {
    if ($properties->get_paper_type() != '6' or $properties->get_rubric() == $meta_type) {
      echo "<tr><td>$meta_type</td><td><select name=\"meta$meta_no\" id=\"meta$meta_no\">";
      echo "<option value=\"$meta_type=%\">&lt;". $string['all']."&gt;</option>";
      foreach ($value_array as $meta_value) {
        echo "<option value=\"$meta_type=$meta_value\">$meta_value</option>\n";
      }
      echo "</select></td></tr>\n";
      $meta_no++;
    }
  }
  
  echo "</table><input type=\"hidden\" name=\"meta_no\" id=\"meta_no\" value=\"$meta_no\" /><input type=\"hidden\" name=\"paperID\" id=\"paperID\" value=\"" . $_GET['paperID'] . "\" />";

  echo "<table style=\"width:100%; padding: 0px 10px 10px 10px\"><tr><td style=\"vertical-align:top; width:45%; padding-right:6px\">\n";
  
  if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2' or $properties->get_paper_type() == '5') {
    // display Assessment stats options

    echo "<div class=\"reports_header\">" . $string['reviews'] . "</div>\n";
    echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/review_comments.php?type=internal&scrOfY=0&',event)\">" . $string['internalpeerreview'] . "</a></div>\n";

		if ($properties->q_type_exist('sct')) {
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/review_sct_answers.php?type=external&',event)\">" . $string['sctresponses'] . "</a></div>\n";
    }
    echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/review_comments.php?type=external&',event)\">" . $string['externalexaminers'] . "</a></div>\n";
    echo "<div class=\"reports_header\">" . $string['cohortreports'] . "</div>\n";
    echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/class_totals.php?',event)\">" . $string['classtotals'] . "</a></div>\n";
    if ($properties->unmarked_enhancedcalc(1) or $properties->unmarked_textbox(1)) {
      echo "<div class=\"greybullet\">" . $string['classtotalsexcel2003'] . "</div>\n";
      echo "<div class=\"greybullet\">" . $string['classtotalscsv'] . "</div>\n";
    } else {
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/class_totals_xml.php?',event)\">" . $string['classtotalsexcel2003'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/class_totals_csv.php?',event)\">" . $string['classtotalscsv'] . "</a></div>\n";
    }
    if ($properties->get_paper_type() != '5') {
			if ($properties->q_type_exist('textbox')) {
        echo "<div class=\"reports_header\">" . $string['textboxmarking'] . "</div>\n";
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?action=mark&phase=1&',event)\">" . $string['primarymarkbyquestion'] . "</a></div>\n";
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/textbox_remark.php?',event)\">" . $string['selectpapersforremarking'] . "</a></div>\n";
        $remark_array = textbox_marking_utils::get_remark_users($paperID, $mysqli);
        if (count($remark_array) > 0) {
          echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?action=mark&phase=2&',event)\">" . $string['secondmarkbyquestion'] . "</a></div>\n";
        } else {
          echo "<div class=\"greybullet\">" . $string['secondmarkbyquestion'] . "</div>\n";          
        }
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?action=finalise&',event)\">" . $string['finalisemarks'] . "</a></div>\n";
      }
    }

    echo "</td><td style=\"vertical-align:top\">";
    echo "<div class=\"reports_header\">" . $string['itemanalysis'] . "</div>\n";

    if ($properties->get_paper_type() != '5') {
		  echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/frequency_discrimination_analysis.php?',event)\">" . $string['frequencyanalysis'] . "</a></div>\n";
		}

	  if ($properties->unmarked_enhancedcalc()) {
			echo "<div class=\"greybullet\">" . $string['learningobjectiveanalysis'] . "</div>\n";
    } else {
			echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/cohort_obj_perform.php?',event)\">" . $string['learningobjectiveanalysis'] . "</a></div>\n";
    }
		echo "<div class=\"reports_header\">" . $string['exports'] . "</div>\n";
    if ($properties->get_paper_type() != '5') {
			echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/assessment_data.php?',event)\">" . $string['exportresponsescsvnum'] . "</a></div>\n";
			echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/assessment_data.php?mode=text&',event)\">" . $string['exportresponsescsvtext'] . "</a></div>\n";
			echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/assessment_boolean.php?',event)\">" . $string['exportbooleancsv'] . "</a></div>\n";
    }
		if ($properties->unmarked_enhancedcalc()) {
			echo "<div class=\"greybullet\">" . $string['exportmarkscsv'] . "</div>\n";
		} else {
			echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/assessment_marks.php?',event)\">" . $string['exportmarkscsv'] . "</a></div>\n";
		}
	}
  
  switch ($properties->get_paper_type()) {
    case '3':     // Stats menu
      echo "<div class=\"reports_header\">" . $string['quantitativereports'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/quantitative.php?',event)\">" . $string['xhtml'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/quantitative_xml.php?',event)\">" . $string['word2003format'] . "</a></div>\n";
      
      echo "<div class=\"reports_header\">" . $string['qualitativeanalysis'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/qualitative.php?',event)\">" . $string['xhtml'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/qualitative_xml.php?',event)\">" . $string['word2003format'] . "</a></div>\n";
      
      echo "<div class=\"reports_header\">" . $string['exports'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/survey_xml_data.php?',event)\">" . $string['rawdataxml'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../export/survey_csv_data.php?',event)\">" . $string['rawdatacsv'] . "</a></div>\n";
      break;
    case '4':     // OSCE menu
      echo "<div class=\"reports_header\">" . $string['cohortreports'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/class_totals.php?',event)\">" . $string['classtotals'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/class_totals_xml.php?',event)\">" . $string['classtotalsexcel2003'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/class_totals_csv.php?',event)\">" . $string['classtotalscsv'] . "</a></div>\n";
      
      echo "<div class=\"reports_header\">" . $string['itemanalysis'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/frequency_analysis.php?',event)\">" . $string['frequencyanalysis'] . "</a></div>\n";
      
      echo "<div class=\"reports_header\">" . $string['exports'] . "</div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/portfolio_sheets.php?',event)\">" . $string['individualportfoliosheets'] . "</a></div>\n";
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../osce/export_ratings.php?',event)\">" . $string['exportratingscsv'] . "</a></div>\n";
      break;
    case '6':
      echo "<br />\n<div class=\"reports_header\">Reports</div>\n";
      if ($properties->get_display_question_mark() == '1') {
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../peer_review/summary_report.php?percent=0&',event)\">" . $string['ReviewSummary1'] . "</a></div>\n";
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../peer_review/summary_report.php?percent=1&',event)\">" . $string['ReviewSummary2'] . "</a></div>\n";
      } else {
        echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../peer_review/summary_report.php?',event)\">" . $string['ReviewSummary3'] . "</a></div>\n";
      }
      echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../peer_review/summary_report_csv.php?',event)\">" . $string['ReviewSummary4'] . "</a></div>\n";
      break;
  }
    
  if(($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2') && (strpos($checklist, 'stdset') !== false)) {
    echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/standards_setting_csv.php?',event)\">" . $string['standardssettingcsv'] . "</a></div>\n";
    echo "<div class=\"bullet\"><a href=\"#\" onclick=\"go('../reports/standards_setting_full_csv.php?',event)\">" . $string['standardssettingfullcsv'] . "</a></div>\n";
  }

?>
</td></tr></table>
</form>

</div>
