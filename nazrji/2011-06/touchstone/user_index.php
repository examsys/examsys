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
* Displays a summary of a particular paper. Initial screen called by a VLE and is used to launch start.php.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require './include/staff_student_auth.inc';

function display_duration($normal,$extra) {
  $mins = $normal;
  if ($extra != NULL) $mins .= ' + ' . ($normal/100)*$extra;
  return $mins;
}

function displayPrevTake($markTotal,$adjPercent,$totalRandomMark,$marking_style,$disDate,$type) {
  global $rerun_date,$total_marks,$low_bandwidth;

  if ($low_bandwidth == 0) {
    echo "<tr><td><img src=\"./artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<a href=\"\" onclick=\"reviewPaper('$rerun_date',$type); return false;\">$disDate</a></td><td style=\"text-align:right\" width=\"70\">";
  } else {
    echo "<tr><td><a href=\"\" onclick=\"reviewPaper('$rerun_date',$type); return false;\">$disDate</a></td><td style=\"text-align:right\" width=\"70\">";
  }
  if ($total_marks > 0) {
    if ($markTotal > 0) {
      if ($marking_style == 1) {
        $adjPercent = number_format((($markTotal-$totalRandomMark)/($total_marks-$totalRandomMark))*100, 1, '.', ',');
        if ($adjPercent < 0) $adjPercent = 0;
        echo $adjPercent . '%';
      } else {
        echo number_format(($markTotal/$total_marks)*100, 1, '.', ',') . '%';
      }
    } else {
      echo '0%';
    }
  }
  echo '</td></tr>';
}

if ($special_needs == 1) {
  //look up special_needs data
  if ($stmt = $mysqli->prepare("SELECT extra_time, textsize, font FROM special_needs WHERE userid=?")) {
    $stmt->bind_param('i',$userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($extra_time, $textsize, $font);
    $stmt->fetch();
  }
  $stmt->close();
  if ($textsize != '') {
    $textsize = $textsize + 5;
  }
} else {
  $extra_time = 0;
  $textsize = '';
  $font = '';
}
  
//if blank reset to defaults
if ($textsize == '') $textsize = 95;
if ($font == '') $font = 'Arial';
  
$paper = urldecode(stripslashes($_GET['paper']));
$person = $title . ' ' . $surname;
$total_random_mark = 0;
$total_marks = 0;

//get paper info
$paper_info = $mysqli->prepare("SELECT DISTINCT property_id, random_mark, total_mark, bidirectional, screen, paper_type, UNIX_TIMESTAMP(start_date) AS start_date, start_date AS display_start_date, UNIX_TIMESTAMP(end_date) AS end_date, end_date AS display_end_date, timezone, moduleID, fullscreen, marking, labs, rubric, exam_duration, calendar_year, sound_demo, password FROM (properties, papers) WHERE properties.paper_title=? AND properties.property_id=papers.paper ORDER BY screen DESC LIMIT 1");
$paper_info->bind_param('s',$paper);
$paper_info->execute();
$paper_info->bind_result($property_id, $total_random_mark, $total_marks, $navigation, $paper_screens, $test_type, $paper_start, $display_start_date, $paper_end, $display_end_date, $timezone, $moduleID, $fullscreen, $marking, $labs, $rubric, $exam_duration, $calendar_year, $sound_demo, $password);
$paper_info->store_result();
$paper_info->fetch();

if ($paper_info->num_rows == 0) {
  echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:$font,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#C00000;font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute;left:10px;top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:60px\">Page cannot be found</h1>\n";
  echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">The requested paper <strong>" . $_GET['paper'] . "</strong> cannot be found</p>\n</body>\n</html>";
  $paper_info->free_result();
  $paper_info->close();
  $mysqli->close();
  exit;
}
$paper_info->free_result();
$paper_info->close();

// Adjust for timezones.
$UK_time = new DateTimeZone("Europe/London");
$target_timezone = new DateTimeZone($timezone);
$display_start_date = new dateTime($display_start_date, $UK_time);
$display_end_date = new dateTime($display_end_date, $UK_time);

$display_start_date->setTimezone($target_timezone);
$display_end_date->setTimezone($target_timezone);

$display_start_date = $display_start_date->format("d/m/Y H:i");
$display_end_date = $display_end_date->format("d/m/Y H:i");

$previously_submitted = 0;

// Check for additional password on the paper
if ($password != '') {
  if (!isset($_COOKIE['paperpwd']) or $password != $_COOKIE['paperpwd']) {      
    echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:$font,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#4465A2;font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute;left:10px;top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">There is a specific password assigned to this paper.</p>\n<p style=\"margin-left:60px\"v><form><input type=\"button\" value=\"&lt Back\" style=\"width:100px\" name=\"ok\" onclick=\"history.back();\"></form></p>\n</body>\n</html>";
    $mysqli->close();
    exit;
  }
}

$low_bandwidth = 0;
//Check this PC is registered for this exam
if ($labs != '' and stripos($userroles,'Student') !== false) {
  $lab_info = $mysqli->prepare("SELECT address, low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($labs)");
  $lab_info->bind_param('s',$_SERVER['REMOTE_ADDR']);
  $lab_info->execute();
  $lab_info->bind_result($address, $low_bandwidth);
  $lab_info->store_result();
  $lab_info->fetch();
  if ($lab_info->num_rows == 0) {
    echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:$font,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#4465A2;font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute; left:10px; top:10px\"><img src=\"./artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Access to this paper is not permitted from your current location.</p>\n</body>\n</html>";
    exit;
  }
  $lab_info->free_result();
  $lab_info->close();
}

//get modules if the user is a student and the paper is not formative
if (stripos($userroles,'Student') !== false AND stripos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
  if($moduleID != '') {
    $cal_year_sql = '';
    if ($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";
    $module_info = $mysqli->query("SELECT moduleid FROM student_modules WHERE userID=$userID AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') $cal_year_sql");
    if ($module_info->num_rows == 0) {
      echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
      echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">$title $surname ($username) is not registered on <strong>$moduleID</strong> in <strong>$calendar_year</strong>.</p>\n</body>\n</html>";
      exit;
    }
    $module_info->close();
  } else {
    echo "<html>\n<head>\n<title>Access Denied - Year</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">This paper is not on any module.</p>\n</body>\n</html>";
    exit;
  }
}

$paper = stripslashes(urldecode($_GET['paper']));
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Start Screen</title>
<style>
body {font-family:<?php echo $font; ?>,sans-serif;color:black;font-size:<?php echo $textsize; ?>%}
input {font-family:<?php echo $font; ?>,sans-serif;font-size:90%}
td {text-align:left}
.f {font-weight:bold; text-align:right;line-height:180%;padding-right:6px}
.w {font-size:90%;color:#C00000;font-weight:bold}
</style>
<script language="JavaScript">
function startPaper() {
  exam=window.open("./paper/start.php?paperID=<?php echo $property_id; ?>","paper","fullscreen=<?php echo $fullscreen; ?>,width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,menubar=no,titlebar=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes");
  if (window.focus) {
    exam.focus();
  }
  document.getElementById('start').value = 'Re-Start >>';
}
function reviewPaper(started,type) {
  exam=window.open("./paper/finish.php?paperID=<?php echo $property_id; ?>&previous="+started+"&log_type="+type+"","paper","fullscreen=<?php echo $fullscreen; ?>,width="+(screen.width-80)+",height="+(screen.height-80)+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
  if (window.focus) {
    exam.focus();
  }
}
function launchHelp() {
  help=window.open("./help/student/index.php","help","width="+(screen.width-30)+",height="+(screen.height-100)+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
  help.moveTo(10,10);
  if (window.focus) {
    help.focus();
  }
}
</script>
</head>
<body>
<form name="theform">
<br />
<div align="center">
<?php
if ($textsize > 120) {
  $table_width = 90;
  $button_width = 150;
} else {
  $table_width = 80;
  $button_width = 115;
}
?>
<table cellpadding="3" cellspacing="0" border="0" style="font-size:100%;border-top:1px solid #5582D2;border-left:1px solid #5582D2;border-right:1px solid #5582D2;background-color:white;width:<?php echo $table_width; ?>%">
<tr>
<?php
  $icon_types = array('formative.png','progress.png','summative.png','survey.png');
  echo '<td colspan="2"><table cellspacing="4" cellpadding="0" border="0"><tr><td style="vertical-align:top; width:54px">&nbsp;<img src="./artwork/' . $icon_types[$test_type] . '" width="48" height="48" alt="Icon" />';
  echo "</td><td><span style=\"font-size:8pt\">TouchStone $ts_version</span><br />\n";
  echo "<span style=\"font-size:18pt; font-family:'Arial Black',sans-serif\">$paper</span></td>\n</tr></table></td></tr>";
  echo "<tr>\n</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border:1px solid #5582D2;background-color:#DFE8FF\" width=\"$table_width%\">\n";
  echo '<tr><td colspan="4">&nbsp;</td>';
  if ($test_type == 2) {
    if (file_exists($cfg_web_root . 'touchstone/users/photos/' . $_SERVER['PHP_AUTH_USER'] . '.jpg')) {
      echo '<td rowspan="';
      if ($sound_demo == '1') {
        echo '8';
      } else {
        echo '7';
      }
      echo '" style="border-left:1px solid #5582D2;background-color:white;width:180px;text-align:center;vertical-align:bottom"><img src="./users/photos/' . $_SERVER['PHP_AUTH_USER'] . '.jpg" width="180" height="270" border="0" alt="Photo" /></td>';
    }
  }
  echo '</tr>';
  if ($rubric != '') echo '<tr><td class="f">Rubric</td><td colspan="3">' . $rubric . "</td></tr>\n";
  if ($test_type != 2) echo '<tr><td class="f">Availability</td><td colspan="3">' . $display_start_date . ' to '. $display_end_date;
  if ($timezone != 'Europe/London') echo ' (' . str_replace('_',' ',$timezone) . ')';
  echo '<input type="hidden" name="startdate" value="$display_start_date" /><input type="hidden" name="testtype" value="' . $test_type . "\" /></td></tr>\n";
  echo "<tr><td class=\"f\">Candidates</td><td colspan=\"3\">" . str_replace(',',', ',$moduleID);
  echo '</td></tr><tr><td class="f">Screens</td><td>' . $paper_screens . '</td>';
  echo '<td class="f">Navigation</td><td>';
  if ($navigation == 1) {
    echo 'Bidirectional';
  } else {
    echo 'Unidirectional';
  }
  echo '</td></tr>';
  if ($test_type < 3) {
    echo '<tr><td class="f">Marks</td>';
    echo '<td colspan="3">' . $total_marks;
    if ($marking == 1) echo ' (Adjusted using random mark of ' . number_format($total_random_mark, 2, '.', ',') . ')';
    echo '</td></tr>';
  }
  echo "<tr><td class=\"f\">&nbsp;Current&nbsp;User</td><td>$person</td>";
  if ($test_type == 2 and $exam_duration) {
    echo '<td class="f">Duration</td><td>' . display_duration($exam_duration,$extra_time) . ' minutes</td>';
  } else {
    echo '<td></td><td></td>';
  }
  
  if ($sound_demo == '1') {
    echo "<tr><td colspan=\"4\" style=\"text-align:center\"><span style=\"color:#D27800;font-size:90%;font-weight:bold\">Test Clip</span>&nbsp;&nbsp;<object type=\"application/x-shockwave-flash\" data=\"./paper/player_mp3_maxi.swf\" width=\"200\" height=\"20\">\n";
    echo "<param name=\"wmode\" value=\"transparent\" />\n";
    echo "<param name=\"movie\" value=\"./paper/player_mp3_maxi.swf\" />\n";
    echo "<param name=\"FlashVars\" value=\"mp3=/touchstone/paper/sound_demo.mp3&amp;showstop=1&amp;showvolume=1&amp;bgcolor1=ffa50b&amp;bgcolor2=d07600\" />\n";
    echo "</object></td></tr>\n";  
  }
  
  if ($test_type == 0) {
    $log_info = $mysqli->query("SELECT screen, SUM(mark) AS mark, DATE_FORMAT(started,\"%Y%m%d%H%i%s\") AS started, 0 AS paper_type, DATE_FORMAT(started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log0 WHERE q_paper=$property_id AND userID=$userID GROUP BY started DESC, screen UNION SELECT screen, SUM(mark) AS mark, DATE_FORMAT(started,\"%Y%m%d%H%i%s\") AS started, 1 AS paper_type, DATE_FORMAT(started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log1 WHERE q_paper=$property_id AND userID=$userID GROUP BY started DESC, screen");
  } else {
    $log_info = $mysqli->query("SELECT MAX(screen) AS screen, SUM(mark) AS mark, DATE_FORMAT(started,\"%Y%m%d%H%i%s\") AS started, $test_type AS paper_type, DATE_FORMAT(started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log$test_type WHERE q_paper=$property_id AND userID=$userID GROUP BY started DESC");
  }

  echo '<tr><td style="text-align:center" colspan="4"><br />';
  if ($test_type == 2) echo "<div style=\"color:#C00000;font-size:90%\">Do <strong>not</strong> click 'Start &gt;&gt;' until instructed to by an invigilator.</div>\n";
  echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"Help\" name=\"help\" onclick=\"launchHelp();\" onkeypress=\"launchHelp();\" />\n";
  if ($test_type == 2) {
    $switch_info = $mysqli->prepare("SELECT property_id FROM properties WHERE paper_type IN('1','2') AND start_date > DATE_SUB(NOW(), INTERVAL 4 HOUR) AND start_date < DATE_ADD(NOW(), INTERVAL 3 HOUR) AND end_date < DATE_ADD(NOW(), INTERVAL 6 HOUR) AND property_id != ?");
    $switch_info->bind_param('i',$property_id);
    $switch_info->execute();
    $switch_info->bind_result($tmp_property_id);
    $switch_info->store_result();
    $switch_info->fetch();
    if ($switch_info->num_rows > 0) echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"Switch Papers\" name=\"switch\" onclick=\"window.location='../index.php'\" />&nbsp;&nbsp;&nbsp;&nbsp;\n";
    $switch_info->close();
  }
  if (strpos($userroles,'Staff') !== false or strpos($userroles,'QABME') !== false or strpos($userroles,'SysAdmin') !== false) {
    if (time() < $paper_start or time() > $paper_end) {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"Start &gt;&gt;\" name=\"start\" onclick=\"\" disabled />\n";
      echo '<br /><div style="font-size:90%;color:#C00000"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;Paper not available at this time.</div>';
    } else {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"Start &gt;&gt;\" name=\"start\" id=\"start\" onclick=\"startPaper();\" onkeypress=\"startPaper();\" />\n";
    }
  } else {
    if ($test_type > 0 and $log_info->num_rows > 0) {
      $row = $log_info->fetch_assoc();
      if ($navigation == 1) {
        if (time() < $paper_end) {
          echo " <input type=\"button\" id=\"start\" style=\"width:" . $button_width . "px; font-weight:bold\" onclick=\"startPaper();\" value=\"Re-Start &gt;&gt;\" name=\"restart\" id=\"start\" />";
        }
      } elseif ($navigation == 0) {
        if ($paper_screens > $row['screen']) {
          echo " <input type=\"button\" id=\"start\" style=\"width:" . $button_width . "px; font-weight:bold\" onclick=\"startPaper();\" value=\"Re-Start &gt;&gt;\" name=\"restart\" id=\"start\" />";
        }
      } else {
        echo "<input type=\"button\" id=\"start\" style=\"width:" . $button_width . "px\" value=\"Start &gt;&gt;\" name=\"start\" id=\"start\" disabled />\n";
      }
    } elseif ($test_type != 2 and (time() < $paper_start or time() > $paper_end)) {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"Start &gt;&gt;\" name=\"start\" disabled />\n";
      echo '<br /><div class="w"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;Paper not available at this time.</div>';
    } elseif ($test_type == 2 and (time()+(15*60)) < $paper_start or time() > $paper_end) {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"Start &gt;&gt;\" name=\"start\" disabled />\n";
      echo '<br /><div class="w"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;Paper not available at this time.</div>';
    } else {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"Start &gt;&gt;\" name=\"start\" id=\"start\" onclick=\"startPaper();\" onkeypress=\"startPaper();\" />\n";
    }
  }
  echo '<br />&nbsp;';
  
  $display_date = '';
  if ($test_type != 2) {
    // Display previous attempts
    $old_started = '';
    $old_screen = 0;
    $temp_no = 0;
    $mark_total = 0;
    $adj_percent = 0;
    if ($log_info->num_rows > 0) {
      //do {
      while ($row = $log_info->fetch_assoc()) {
        if ($temp_no == 0) {
          $old_started = $row['started'];
          echo '<hr style="background-color:#5582D2; color:#5582D2; height:1px; width:80%; border:0" />';
          echo '<table cellpadding="0" cellspacing="0" border="0" align="center">';
          echo '<tr><td colspan="4" style="text-align:center"><strong>Previous Completions</strong></td></tr>';
          if ($row['screen'] > $old_screen) $old_screen = $row['screen'];
        }
        if ($old_started != $row['started'] and $old_started != '') {
          $old_screen = 0;
          if ($test_type == 0) {
            displayPrevTake($mark_total,$adj_percent,$total_random_mark,$marking,$display_date,$paper_type);
          } else {
            if ($low_bandwidth == 0) {
              echo "<tr><td><img src=\"./artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<span style=\"color:#808080\">$display_date</span></td><td>&nbsp;</td></tr>\n";
            } else {
              echo "<tr><td><span style=\"color:#808080\">$display_date</span></td><td>&nbsp;</td></tr>\n";
            }
          }
          $mark_total = 0;
        }
        $old_started = $row['started'];
        $temp_no++;
        if ($row['screen'] > $old_screen) $old_screen++;
        $mark_total += $row['mark'];
        $display_date = $row['temp_date'];
        $rerun_date = $row['started'];
        $paper_type = $row['paper_type'];
      //} while ($row = $log_info->fetch_assoc());
      }
      $log_info->close();

      if ($test_type == 0) {
        displayPrevTake($mark_total,$adj_percent,$total_random_mark,$marking,$display_date,$paper_type);
      } else {
        if ($low_bandwidth == 0) {
          echo "<tr><td><img src=\"./artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<span style=\"color:#808080\">$display_date";
        } else {
          echo "<tr><td><span style=\"color:#808080\">$display_date";
        }
        echo '</span></td><td>&nbsp;</td></tr>';
      }
      echo '</td></tr></table><br />';
    } else {
      if ($test_type != 2) echo '<hr style="background-color:#5582D2; color:#5582D2; height:1px; width:80%; border:0" /><p style="color:#808080">(You have not yet taken this paper.)</p><br />';
    }
  }
  $mysqli->close();
  ?>
</td>
</tr>
</table>
</div>
</form>
</body>
</html>
