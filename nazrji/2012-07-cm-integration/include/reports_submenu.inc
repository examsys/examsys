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
?>
<script language="JavaScript">
<?php
  $module_list = explode(',', $module);

  if ($paper_type == '3') {
?>
  function go(url, evt) {
    document.getElementById('stats_menu').style.display='none';
    if (document.getElementById('completerpt').checked) {
      completerpt = 1;
    } else {
      completerpt = 0;
    }
    window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repcourse=" + document.stats_menu.repcourse.value + "&repyear=" + document.stats_menu.repyear.value + "&sortby=surname&module=<?php echo $module_list[0]; ?>&folder=<?php echo $folder; ?>&complete=" + completerpt;
    evt.cancelBubble = true;
    return false;
  }
<?php
  } elseif ($paper_type == '6') {
?>
  function go(url, evt) {
    var extra_data = '';

    for (i=1; i<document.getElementById('meta_no').value; i++) {
      meta_ref = 'meta' + i;
      extra_data += '&meta' + i + '=' + document.getElementById(meta_ref).value;
    }

    document.getElementById('stats_menu').style.display='none';
    window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repmodule=" + document.stats_menu.repmodule.value + "&repcourse=" + document.stats_menu.repcourse.value + extra_data;
  }
  <?php
  } else {
?>
  function go(url, evt) {
    var extra_data = '';

    for (i=1; i<document.getElementById('meta_no').value; i++) {
      meta_ref = 'meta' + i;
      extra_data += '&meta' + i + '=' + document.getElementById(meta_ref).value;
    }
    
    document.getElementById('stats_menu').style.display='none';
    if (document.stats_menu.absent.checked == true) {
      absent = '&absent=1';
    } else {
      absent = '&absent=0';
    }
    if (document.stats_menu.studentsonly.checked == true) {
      studentsonly = '&studentsonly=1';
    } else {
      studentsonly = '&studentsonly=0';
    }
    window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repmodule=" + document.stats_menu.repmodule.value + "&repcourse=" + document.stats_menu.repcourse.value + "&sortby=name&module=<?php echo $module_list[0]; ?>&folder=<?php echo $folder; ?>&percent=" + document.stats_menu.percent.value + absent + studentsonly + "&direction=" + document.stats_menu.direction.value + extra_data;
    
    evt.cancelBubble = true;
    return false;
  }
<?php
  }
?>
  function checkAll() {
    box = document.forms[0].direction;
    if (box.selectedIndex == 0) {
      document.forms[0].percent.selectedIndex = 6;    
    }
  }
</script>

<div class="popup" style="z-index:20; display:none; position:fixed; left:190px; top:60px" id="stats_menu" onclick="showAssStatsMenu(true, event);">
<table border="0" cellpadding="2" cellspacing="0" style="width:530px; border:0px solid #C0C0C0; font-size:100%">
<tr>
<td style="background-color:#F2B100; height:5px; padding:0px"></td>
</tr>
<tr>
<td align="left">

<form name="stats_menu">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%">
<tr>

<?php  
  $now_time = date("YmdHis");
  if ($start_date > $now_time) {
    $target_start_date = $created;
  } else {
    $target_start_date = $start_date;
  }
  if ($end_date > $now_time) {
    $target_end_date = $now_time;
  } else {
    $target_end_date = $end_date;
  }

  $start_year = substr($target_start_date,0,4);
  $start_month = substr($target_start_date,4,2);
  $start_day = substr($target_start_date,6,2);
  $start_time = substr($target_start_date,8,2) . '0000';

  $end_year = substr($target_end_date,0,4);
  $end_month = substr($target_end_date,4,2);
  $end_day = substr($target_end_date,6,2);
  $end_hour = substr($target_end_date,8,2);
  $end_minute = substr($target_end_date,10,2);
  if ($end_hour < 23) {
    $end_hour++;
    if ($end_hour < 10) $end_hour = '0' . $end_hour;
  }
  $end_time = $end_hour . $end_minute . '00';

  if ($end_year > date('Y') or ($end_year == date('Y') and $end_month > date('m'))) {
    $end_day = date('d');
    $end_month = date('m');
  }

  $month_names = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
  $month_nos = array('01','02','03','04','05','06','07','08','09','10','11','12');

  $date_array = getdate();

  echo "<tr><td>" . $string['dates'] . "</td><td>";
  // Start Day
  echo "<select name=\"start_day\">\n";
  for ($i=1; $i<=31; $i++) {
    echo "<option value=\"";
    if ($i < 10) {
      echo "0";
    }
    if ($i == intval($start_day)) {
      echo "$i\" selected>";
    } else {
      echo "$i\">";
    }   
    if ($i < 10) echo '0';
    echo "$i</option>\n";
  }
  echo "</select>";
  // Start Month
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
  
  echo "<select name=\"start_month\">\n";

  for ($i=0; $i<12; $i++) {
    if ($i == (intval($start_month) - 1)) {
      echo "<option value=\"" . $month_nos[$i] . "\" selected>" . mb_substr($string[$month_names[$i]],0,3,'UTF-8') . "</option>\n";
    } else {
      echo "<option value=\"" . $month_nos[$i] . "\">" . mb_substr($string[$month_names[$i]],0,3,'UTF-8') . "</option>\n";
    }
  }
  echo "</select>";
  // Start Year
  echo "<select name=\"start_year\">\n";
  for ($i=2001; $i<=($date_array['year'] + 1); $i++) {
    if ($i == intval($start_year)) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select>";
  // Start Time
  echo "<select name=\"start_time\">\n";
  $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
  foreach ($times as $key => $value) {
    if ($key == intval($start_time)) {
      echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
    } else {
      echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
    }
  }
  echo "</select>\n";

  // ---------------------------------------------------------------------------------
  echo " to ";
  // End Day
  echo "<select name=\"end_day\">\n";
  for ($i=1; $i<=31; $i++) {
    echo "<option value=\"";
    if ($i < 10) {
      echo "0";
    }
    if ($i == intval($end_day)) {
      echo "$i\" selected>";
    } else {
      echo "$i\">";
    }   
    if ($i < 10) echo '0';
    echo "$i</option>\n";
  }
  echo "</select>";
  // End Month
  if ($end_year > $date_array['year']) {
    $end_year = $date_array['year'];
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
    $target_hours = $date_array['hours'];
    if ($target_hours < 23) $target_hours++;
    if ($target_hours < 10) {
      $end_time = '0' . $target_hours . '0000';
    } else {
      $end_time = $target_hours . '0000';
    }
  } elseif ($end_month > $date_array['mon'] and $end_year == $date_array['year']) {
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
    $target_hours = $date_array['hours'];
    if ($target_hours < 23) $target_hours++;
    if ($target_hours < 10) {
      $end_time = '0' . $target_hours . '0000';
    } else {
      $end_time = $target_hours . '0000';
    }
  } elseif ($end_day > $date_array['mday'] and $end_month == $date_array['mon'] and $end_year == $date_array['year']) {
    $end_day = $date_array['mday'];
    $target_hours = $date_array['hours'];
    if ($target_hours < 23) $target_hours++;
    if ($target_hours < 10) {
      $end_time = '0' . $target_hours . '0000';
    } else {
      $end_time = $target_hours . '0000';
    }
  }
  echo "<select name=\"end_month\">\n";
  for ($i=0; $i<12; $i++) {
    if ($i == (intval($end_month) - 1)) {
      echo "<option value=\"" . $month_nos[$i] . "\" selected>" . mb_substr($string[$month_names[$i]],0,3,'UTF-8') . "</option>\n";
    } else {
      echo "<option value=\"" . $month_nos[$i] . "\">" . mb_substr($string[$month_names[$i]],0,3,'UTF-8') . "</option>\n";
    }
  }
  echo "</select>";
  // End Year
  echo "<select name=\"end_year\">\n";
  for ($i=2001; $i<=($date_array['year']); $i++) {
    if ($i == intval($end_year)) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select>";
  // End Time
  echo "<select name=\"end_time\">\n";
  $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
  foreach ($times as $key => $value) {
    if ($key == intval($end_time)) {
      echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
    } else {
      echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
    }
  }
  echo "</select>\n</td></tr>\n";
  
  echo "<tr><td>" . $string['course'] . "</td><td><select name=\"repcourse\" style=\"width:375px\">\n";
  echo "<option value=\"%\">" . $string['anycourse'] . "</option>\n";
  $result = $mysqli->prepare("SELECT DISTINCT student_grade, description FROM log_metadata, courses WHERE log_metadata.student_grade=courses.name AND paperID=? ORDER BY student_grade");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($student_grade, $description);
  while ($result->fetch()) {
    if (strlen($student_grade) == 4) echo "<option value=\"$student_grade\">$student_grade: $description</option>\n";
  }
  $result->close();
  echo "</select></td></tr>\n";
  
  //get the paper modules (also rubric for use if peer review paper)
  $result = $mysqli->prepare("SELECT moduleID, rubric FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($moduleID, $rubric);
  $result->fetch();
  $result->close();
  $moduleIDs = explode(',', $moduleID);
  
  //module drop down
  if ($paper_type != '3') {
    echo "<tr><td>" . $string['module'] . "</td><td><select name=\"repmodule\" style=\"width:150px\">\n";
    echo "<option value=\"\">" . $string['anymodule'] . "</option>\n";
    foreach ($moduleIDs as $mod) {
      echo "<option value=\"" . $mod . "\">" . $mod . "</option>\n";
    }
    echo "</select></td></tr>\n";
    
    echo "<tr><td>" . $string['cohort'] . "</td><td><select name=\"direction\" onchange=\"checkAll();\">\n";
    echo "<option value=\"asc\">" . $string['allcandidates'] . "</option>\n";
    echo "<option value=\"desc\">" . $string['top'] . "</option>\n";
    echo "<option value=\"asc\">" . $string['bottom'] . "</option>\n";
    echo "</select>\n";
    echo "<select name=\"percent\">\n";
    echo "<option value=\"1\">1%</option>\n";
    echo "<option value=\"5\">5%</option>\n";
    echo "<option value=\"10\">10%</option>\n";
    echo "<option value=\"25\">25%</option>\n";
    echo "<option value=\"27\">27%</option>\n";
    echo "<option value=\"33.3\">33.3%</option>\n";
    echo "<option value=\"50\">50%</option>\n";
    echo "<option value=\"100\" selected>100%</option>\n";
    echo "</select>\n";
    echo "</td></tr>\n";
  }
  
  echo "<tr><td colspan=\"2\">" . $string['incabsentcandidates'] . "&nbsp;<input type=\"checkbox\" name=\"absent\" value=\"1\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $string['studentsonly'] . "&nbsp;<input type=\"checkbox\" name=\"studentsonly\" value=\"1\" checked=\"checked\" /></td></tr>\n";

  if ($paper_type == '3') {
    echo "<tr><td>" . $string['year'] . "</td><td><select name=\"repyear\">\n";
    $years = array('%'=>'Any Year','1'=>'Year 1','2'=>'Year 2','3'=>'Year 3','4'=>'Year 4','5'=>'Year 5');
    foreach ($years as $key => $value) {
      echo "<option value=\"$key\">$value</option>\n";
    }
    echo "</select>\n</td></tr>\n";
    echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"completerpt\" name=\"completerpt\" value\"1\" />&nbsp;" . $string['completedatasets'] . "</td></tr>\n";
  }
  
  // Get any metadata fields
  $metadata = array();
  if (count($moduleIDs) > 0) {
    $result = $mysqli->prepare("SELECT DISTINCT type, value FROM users_metadata, modules WHERE users_metadata.moduleID=modules.id AND modules.moduleid IN ('" . implode("','",$moduleIDs) . "') ORDER BY type, value");
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
    if ($paper_type != '6' or $rubric == $meta_type) {
      echo "<tr><td>$meta_type</td><td><select name=\"meta$meta_no\" id=\"meta$meta_no\">";
      echo "<option value=\"$meta_type=%\">&lt;". $string['all']."&gt;</option>";
      foreach ($value_array as $meta_value) {
        echo "<option value=\"$meta_type=$meta_value\">$meta_value</option>\n";
      }
      echo "</select></td></tr>\n";
      $meta_no++;
    }
  }
  
  echo "</table><input type=\"hidden\" name=\"meta_no\" id=\"meta_no\" value=\"$meta_no\" /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />";

  echo "<table style=\"width:100%; padding-bottom:5px\"><tr><td style=\"vertical-align:top; width:42%\">\n";
  
  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2' or $paper_type == '5') {
    // display Assessment stats options

    echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['reviews'] . "</div>\n";
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_comments.php?type=internal&',event)\">" . $string['internalpeerreview'] . "</a></div>\n";
    
    $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='sct'");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($sct_no);
    $result->fetch();
    $result->close();
    if ($sct_no > 0) {
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_sct_answers.php?type=external&',event)\">" . $string['sctresponses'] . "</a></div>\n";
    }
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_comments.php?type=external&',event)\">" . $string['externalexaminers'] . "</a></div>\n";
    echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['cohortreports'] . "</div>\n";
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals.php?',event)\">" . $string['classtotals'] . "</a></div>\n";
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals_xml.php?',event)\">" . $string['classtotalsexcel2003'] . "</a></div>\n";
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals_csv.php?',event)\">" . $string['classtotalscsv'] . "</a></div>\n";
    
    if ($paper_type != '5') {
      $result = $mysqli->prepare("SELECT COUNT(q_id) AS textbox_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='textbox'");
      $result->bind_param('i', $_GET['paperID']);
      $result->execute();
      $result->bind_result($textbox_no);
      $result->fetch();
      $result->close();
      if ($textbox_no > 0) {
        echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['textboxmarking'] . "</div>\n";
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?ws=1&action=mark&phase=1&',event)\">" . $string['primarymarkbyquestion'] . "</a></div>\n";
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_remark.php?',event)\">" . $string['selectpapersforremarking'] . "</a></div>\n";
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?ws=1&action=mark&phase=2&',event)\">" . $string['secondmarkbyquestion'] . "</a></div>\n";
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?action=finalise&',event)\">" . $string['finalisemarks'] . "</a></div>\n";
      }
    }

    echo "</td><td style=\"vertical-align:top\">";
    echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['itemanalysis'] . "</div>\n";

    if ($paper_type != '5') {
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/frequency_discrimination_analysis.php?',event)\">" . $string['frequencyanalysis'] . "</a></div>\n";
    }

    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/cohort_obj_perform.php?',event)\">" . $string['learningobjectiveanalysis'] . "</a></div>\n";
    echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['exports'] . "</div>\n";
    if ($paper_type != '5') {
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/assessment_data.php?',event)\">" . $string['exportresponsescsvnum'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/assessment_data.php?mode=text&',event)\">" . $string['exportresponsescsvtext'] . "</a></div>\n";
    }
    echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/assessment_marks.php?',event)\">" . $string['exportmarkscsv'] . "</a></div>\n";
  }
  
  switch ($paper_type) {
    case '3':     // Stats menu
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['quantitativereports'] . "</div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/quantitative.php?',event)\">" . $string['xhtml'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/quantitative_xml.php?',event)\">" . $string['word2003format'] . "</a></div>\n";
      
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['qualitativeanalysis'] . "</div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/qualitative.php?',event)\">" . $string['xhtml'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/qualitative_xml.php?',event)\">" . $string['word2003format'] . "</a></div>\n";
      
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['exports'] . "</div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/survey_xml_data.php?',event)\">" . $string['rawdataxml'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/survey_csv_data.php?',event)\">" . $string['rawdatacsv'] . "</a></div>\n";
      break;
    case '4':     // OSCE menu
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['cohortreports'] . "</div>\n";
      echo "<div style=\"line-height:150%\">inc. absent candidates&nbsp;<input type=\"checkbox\" name=\"absent\" value=\"1\" /></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals.php?',event)\">" . $string['classtotals'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals_xml.php?',event)\">" . $string['classtotalsexcel2003'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals_csv.php?',event)\">" . $string['classtotalscsv'] . "</a></div>\n";
      
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['itemanalysis'] . "</div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/frequency_analysis.php?',event)\">" . $string['frequencyanalysis'] . "</a></div>\n";
      
      echo "<div style=\"line-height:150%; margin-top:10px; font-weight:bold\">" . $string['exports'] . "</div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/portfolio_sheets.php?',event)\">" . $string['individualportfoliosheets'] . "</a></div>\n";
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/export_ratings.php?',event)\">" . $string['exportratingscsv'] . "</a></div>\n";
      break;
    case '6':
      echo "<br />\n<div style=\"line-height:150%; font-weight:bold\">Reports</div>\n";
      if ($display_question_mark == '1') {
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../peer_review/summary_report.php?percent=0&',event)\">Review Summary - means (HTML)</a></div>\n";
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../peer_review/summary_report.php?percent=1&',event)\">Review Summary - percents (HTML)</a></div>\n";
      } else {
        echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../peer_review/summary_report.php?',event)\">Review Summary</a></div>\n";
      }
      echo "<div style=\"line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../peer_review/summary_report_csv.php?',event)\">Review Summary - means (CSV)</a></div>\n";
      break;
  }

?>
</td></tr></table>
</form>
</td>
</tr>
</table>

</div>
