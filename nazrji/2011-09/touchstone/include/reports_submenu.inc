<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
?>
<script language="JavaScript">
<?php
  if ($paper_type == '3') {
?>
  function go(url, evt) {
    document.getElementById('stats_menu').style.display='none';
    if (document.getElementById('completerpt').checked) {
      completerpt = 1;
    } else {
      completerpt = 0;
    }
    window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repdegree=" + document.stats_menu.repdegree.value + "&repyear=" + document.stats_menu.repyear.value + "&sortby=surname&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&complete=" + completerpt;
    evt.cancelBubble = true;
    return false;
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
      window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repmodule=" + document.stats_menu.repmodule.value + "&repdegree=" + document.stats_menu.repdegree.value + "&sortby=name&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&percent=" + document.stats_menu.percent.value + "&absent=1&direction=" + document.stats_menu.direction.value + extra_data;
    } else {
      window.location.href = url + "paperID=" + document.stats_menu.paperID.value + "&startdate=" + document.stats_menu.start_year.value + document.stats_menu.start_month.value + document.stats_menu.start_day.value + document.stats_menu.start_time.value + "&enddate=" + document.stats_menu.end_year.value + document.stats_menu.end_month.value + document.stats_menu.end_day.value + document.stats_menu.end_time.value + "&repmodule=" + document.stats_menu.repmodule.value + "&repdegree=" + document.stats_menu.repdegree.value + "&sortby=name&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&percent=" + document.stats_menu.percent.value + "&absent=0&direction=" + document.stats_menu.direction.value + extra_data;
    }
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

<div style="display:none; position:fixed; left:190px; top:60px; filter: progid:DXImageTransform.Microsoft.Shadow(direction=120,color=gray,strength=4)" id="stats_menu" onclick="showAssStatsMenu(event);">
<table border="0" cellpadding="3" cellspacing="1" style="width:490px; border:1px solid #C0C0C0; font-size:80%; background-color:white">
<tr>
<td style="background-color:#F2B100; height:5px"> </td>
</tr>
<tr>
<td align="left">

<form name="stats_menu">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%">
<tr>

<?php
  $start_year = substr($start_date,0,4);
  $start_month = substr($start_date,4,2);
  $start_day = substr($start_date,6,2);
  $start_time = substr($start_date,8,4) . '00';

  $end_year = substr($end_date,0,4);
  $end_month = substr($end_date,4,2);
  $end_day = substr($end_date,6,2);
  $end_time = substr($end_date,8,4) . '00';

  $month_names = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
  $month_nos = array('01','02','03','04','05','06','07','08','09','10','11','12');

  $date_array = getdate();

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
  echo "<tr><td>Start Date</td><td>";
  echo "<select name=\"start_month\">\n";
  for ($i=0; $i<12; $i++) {
    if ($i == (intval($start_month) - 1)) {
      echo "<option value=\"" . $month_nos[$i] . "\" selected>" . $month_names[$i] . "</option>\n";
    } else {
      echo "<option value=\"" . $month_nos[$i] . "\">" . $month_names[$i] . "</option>\n";
    }
  }
  echo "</select>\n";
  // Start Day
  echo "<select name=\"start_day\">\n";
  for ($i=1; $i<=31; $i++) {
    echo "<option value=\"";
    if ($i < 10) {
      echo "0";
    }
    if ($i == intval($start_day)) {
      echo "$i\" selected>$i";
    } else {
      echo "$i\">$i";
    }   
    if ($i==1 or $i==21 or $i==31) {
      echo "st";
    } elseif ($i==2 or $i==22) {
      echo "nd";
    } elseif ($i==3 or $i==23) {
      echo "rd";
    } else {
      echo "th";
    }
    echo "</option>\n";
  }
  echo "</select>\n";
  // Start Year
  echo "<select name=\"start_year\">\n";
  for ($i=2001; $i<=($date_array['year'] + 1); $i++) {
    if ($i == intval($start_year)) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select>\n&nbsp;\n";
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
  echo "</select>\n</td></tr>\n";

  // ---------------------------------------------------------------------------------
  // End Month
  if ($end_year > $date_array['year']) {
    $end_year = $date_array['year'];
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
    if ($date_array['hours'] < 10) {
      $end_time = '0' . $date_array['hours'] . '0000';
    } else {
      $end_time = $date_array['hours'] . '0000';
    }
  } elseif ($end_month > $date_array['mon'] and $end_year == $date_array['year']) {
    $end_month = $date_array['mon'];
    $end_day = $date_array['mday'];
    if ($date_array['hours'] < 10) {
      $end_time = '0' . $date_array['hours'] . '0000';
    } else {
      $end_time = $date_array['hours'] . '0000';
    }
  } elseif ($end_day > $date_array['mday'] and $end_month == $date_array['mon'] and $end_year == $date_array['year']) {
    $end_day = $date_array['mday'];
    if ($date_array['hours'] < 10) {
      $end_time = '0' . $date_array['hours'] . '0000';
    } else {
      $end_time = $date_array['hours'] . '0000';
    }
  }
  echo "<tr><td>End Date</td><td>";
  echo "<select name=\"end_month\">\n";
  for ($i=0; $i<12; $i++) {
    if ($i == (intval($end_month) - 1)) {
      echo "<option value=\"" . $month_nos[$i] . "\" selected>" . $month_names[$i] . "</option>\n";
    } else {
      echo "<option value=\"" . $month_nos[$i] . "\">" . $month_names[$i] . "</option>\n";
    }
  }
  echo "</select>\n";
  // End Day
  echo "<select name=\"end_day\">\n";
  for ($i=1; $i<=31; $i++) {
    echo "<option value=\"";
    if ($i < 10) {
      echo "0";
    }
    if ($i == intval($end_day)) {
      echo "$i\" selected>$i";
    } else {
      echo "$i\">$i";
    }   
    if ($i==1 or $i==21 or $i==31) {
      echo "st";
    } elseif ($i==2 or $i==22) {
      echo "nd";
    } elseif ($i==3 or $i==23) {
      echo "rd";
    } else {
      echo "th";
    }
    echo "</option>\n";
  }
  echo "</select>\n";
  // End Year
  echo "<select name=\"end_year\">\n";
  for ($i=2001; $i<=($date_array['year']); $i++) {
    if ($i == intval($end_year)) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select>\n&nbsp;\n";
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
  
  echo "<tr><td>Course</td><td><select name=\"repdegree\" style=\"width:375px\">\n";
  echo "<option value=\"%\">&lt;Any Course&gt;</option>\n";
  $result = $mysqli->prepare("SELECT DISTINCT student_grade, description FROM log_metadata, degrees WHERE log_metadata.student_grade=degrees.degree AND paperID=? ORDER BY student_grade");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($student_grade, $description);
  while ($result->fetch()) {
    if (strlen($student_grade) == 4) echo "<option value=\"$student_grade\">$student_grade: $description</option>\n";
  }
  $result->close();
  echo "</select></td></tr>\n";
  
  //get the paper modules
  $result = $mysqli->prepare("SELECT moduleID FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($moduleID);
  $result->fetch();
  $result->close();
  $moduleIDs = explode(',',$moduleID);
  
  //module drop down
  if ($paper_type != '3') {
    echo "<tr><td>Module</td><td><select name=\"repmodule\" style=\"width:150px\">\n";
    echo "<option value=\"\">&lt;Any Module&gt;</option>\n";
    foreach ($moduleIDs as $mod) {
      echo "<option value=\"" . $mod . "\">" . $mod . "</option>\n";
    }
    echo "</select></td></tr>\n";
    
    echo "<tr><td>Cohort</td><td><select name=\"direction\" onchange=\"checkAll();\">\n";
    echo "<option value=\"asc\">All candidates</option>\n";
    echo "<option value=\"desc\">Top</option>\n";
    echo "<option value=\"asc\">Bottom</option>\n";
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
  
  if ($paper_type == '3') {
    echo "<tr><td>Year</td><td><select name=\"repyear\">\n";
    $years = array('%'=>'Any Year','1'=>'Year 1','2'=>'Year 2','3'=>'Year 3','4'=>'Year 4','5'=>'Year 5');
    foreach ($years as $key => $value) {
      echo "<option value=\"$key\">$value</option>\n";
    }
    echo "</select>\n</td></tr>\n";
    echo "<tr><td>&nbsp;</td><td><input type=\"checkbox\" id=\"completerpt\" name=\"completerpt\" value\"1\" />&nbsp;Show complete datasets only</td></tr>\n";
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
    echo "<tr><td colspan=\"2\"><hr style=\"height:1px; color:#C0C0C0; background-color:#C0C0C0\" /></td></tr>\n";
  }
  foreach ($metadata as $meta_type=>$value_array) {
    echo "<tr><td>$meta_type</td><td><select name=\"meta$meta_no\" id=\"meta$meta_no\">";
    echo "<option value=\"$meta_type=%\">&lt;all&gt;</option>";
    foreach ($value_array as $meta_value) {
      echo "<option value=\"$meta_type=$meta_value\">$meta_value</option>\n";
    }
    echo "</select></td></tr>\n";
    $meta_no++;
  }
 
  
  echo "</table><input type=\"hidden\" name=\"meta_no\" id=\"meta_no\" value=\"$meta_no\" /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />";

  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2' or $paper_type == '5') {
    // display Assessment stats options

    echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Reviews</div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_comments.php?type=internal&',event)\">Internal Peer Review</a></div>\n";
    
    $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='sct'");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($sct_no);
    $result->fetch();
    $result->close();
    if ($sct_no > 0) {
      echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_sct_answers.php?type=external&',event)\">SCT Responses/Reasons</a></div>\n";
    }
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/review_comments.php?type=external&',event)\">External Examiners</a></div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Cohort Reports</div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%\">inc. absent candidates&nbsp;<input type=\"checkbox\" name=\"absent\" value=\"1\" /></div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals.php?',event)\">Class Totals</a></div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals_xml.php?',event)\">Class Totals (Excel 2003)</a></div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/class_totals_csv.php?',event)\">Class Totals (CSV file)</a></div>\n";
    
    if ($paper_type != '5') {
      $result = $mysqli->prepare("SELECT COUNT(q_id) AS textbox_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='textbox'");
      $result->bind_param('i', $_GET['paperID']);
      $result->execute();
      $result->bind_result($textbox_no);
      $result->fetch();
      $result->close();
      if ($textbox_no > 0) {
        echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Textbox Marking</div>\n";
        echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?ws=1&action=mark&phase=1&',event)\">Primary Mark by Question</a></div>\n";
        echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_remark.php?',event)\">Select Papers for Remarking</a></div>\n";
        echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?ws=1&action=mark&phase=2&',event)\">Second Mark by Question</a></div>\n";
        echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/textbox_select_q.php?action=finalise&',event)\">Finalise Marks</a></div>\n";
      }
    }

    echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Item Analysis</div>\n";

    if ($paper_type != '5') {
      echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/frequency_discrimination_analysis.php?',event)\">Frequency &amp; Discrimination (U-L) Analysis</a></div>\n";
    }

    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/cohort_obj_perform.php?',event)\">Learning Objective Analysis</a></div>\n";
    echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Exports</div>\n";
    if ($paper_type != '5') {
      echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/assessment_data.php?',event)\">Export responses as CSV file</a></div>\n";
    }
    echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/assessment_marks.php?',event)\">Export marks as CSV file</a></div>\n";
  } 

if ($paper_type == '4') {
  //OSCE menu
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Cohort Reports</div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\">inc. absent candidates&nbsp;<input type=\"checkbox\" name=\"absent\" value=\"1\" /></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals.php?',event)\">Class Totals</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals_xml.php?',event)\">Class Totals (Excel 2003)</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/class_totals_csv.php?',event)\">Class Totals (TAB-delimited file)</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Item Analysis</div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/frequency_analysis.php?',event)\">Frequency Analysis</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Exports</div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/portfolio_sheets.php?',event)\">Individual Portfolio Sheets</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../../osce/export_ratings.php?',event)\">Export ratings as CSV file</a></div>\n";
}

if ($paper_type == '3') {
  // display Survey stats options
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Quantitative Reports</div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/quantitative.php?',event)\">XHTML</a></div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/quantitative_xml.php?',event)\">Word 2003 format</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Qualitative Analysis</div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/qualitative.php?',event)\">XHTML (interactive)</a></div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../reports/qualitative_xml.php?',event)\">Word 2003 format</a></div>\n";
  echo "<div style=\"margin-left:25px; line-height:150%; margin-top:10px; font-weight:bold\">Exports</div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/survey_xml_data.php?',event)\">Raw data as XML file</a></div>\n";
  echo "<div style=\"margin-left:25px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"#\" onclick=\"go('../export/survey_csv_data.php?',event)\">Raw data as CSV file</a></div>\n";
}
?>
</form>
</td>
</tr>
</table>

</div>
