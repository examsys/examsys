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
* Displays summative exams and OSCEs
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/sidebar_menu.inc';
require '../include/sort.inc';
require '../include/year_tabs.inc';
require_once '../lang/' . $language . '/include/timezones.inc';

if (isset($_GET['calyear'])) {
  $current_year = $_GET['calyear'];
} else {
  $current_year = date("Y");
}

function display_paper($day_no, $subtract, $current_year, $current_month, $paper, &$papers, &$cellID, $string, $default_timezone) {
	
	if ($paper['start_time'] == $paper['end_time'] or ($paper['labs'] == '' and $paper['password'] == '') or $paper['duration'] == '') {
		$problem = true;
	} else {
		$problem = false;
	}			
	if ($paper['start_day'] == ($day_no - $subtract) and $paper['cal_year'] == $current_year and $paper['month'] == $current_month) {
		$papers++;
		echo '<tr><td class="warn_icon">';
		if ($problem) {
			echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" align="texttop" alt="' . $string['warning'] . '" title="' . $string['warning'] . '" />';
		}
		if ($paper['timezone'] != $default_timezone) {
			echo '<img src="../artwork/timezone_16.png" width="16" height="16" align="texttop" alt="' . $string['timezone'] . '" title="' . $string['timezone'] . '" />';
		}
		if ($paper['password'] != '') {
			echo '<img src="../artwork/key_12.png" width="12" height="12" alt="' . $string['password'] . '" title="' . $string['password'] . '"  />';
		}
    $metadata = '';
    if (isset($paper['metadata'])) {
      foreach ($paper['metadata'] as $individual_metadata) {
        if ($metadata != '') {
          $metadata .= '<br />';
        }
        $metadata .= $individual_metadata['name'] . ': ' . $individual_metadata['value'];
      }
    }
		echo '</td><td>' . $paper['start_hour'];
		if ($paper['start_minute'] != 0) {
			echo ':' . $paper['start_minute'];
		}
		echo '&nbsp;' . $paper['am_pm'] . '</td>';
		echo "<td class=\"p\"><div class=\"pd\"><a id=\"p$cellID\" href=\"../paper/details.php?paperID=" . $paper['property_id'] . "&module=" . $paper['idMod'] . "&folder=\" onmouseover=\"showCallout($cellID, '" . $paper['start_time'] . "', '" . $paper['end_time'] . "', '" . $paper['duration'] . "', '" . $paper['labs'] . "', '" . $paper['password'] . "', '" . $paper['timezone'] . "', '$metadata')\" onmouseout=\"hideCallout()\">" . $paper['paper_title'] . "</a></div></td></tr>";
		$cellID++;
	}
}

$default_timezone = $timezone_array[$configObject->get('cfg_timezone')];

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title>Rog&#333;: <?php echo $string['calendar'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<?php echo $configObject->get('cfg_js_root') ?>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script language="JavaScript">
  var lab_names = new Array();
<?php
  // Get computer lab information.
  $lab_details = array($string['default']=>array('-1'=>$string['alllabs']));
  $stmt = $mysqli->prepare("SELECT id, building, room_no, campus FROM labs WHERE room_no != '' ORDER BY campus, building, room_no");
  $stmt->execute();
  $stmt->bind_result($id, $building, $room_no, $campus);
  while ($stmt->fetch()) {
    $lab_details[$campus][$id] = $building . ' - ' . $room_no;
		echo "  lab_names[$id] = \"$room_no - $building\"\n";
  }
  $stmt->close();
?>

  function showCallout(cellID, start_time, end_time, duration, labs, password, timezone, metadata) {
		var p = $('#p' + cellID);
		var position = p.position();
		
		var left_pos = position.left;
		if (left_pos + 302 > $(window).width()) {
			left_pos = $(window).width() - 302;
			$('.notch').css('left', '180px');
		} else {
			$('.notch').css('left', '20px');
		}
		$('#callout').css('left', left_pos);
		$('#callout').css('top', position.top + p.height() + 12);
		$('#duration').html(duration + ' mins');
		
		if (start_time == end_time) {
			$('#start_time2').html(start_time);
			$('#end_time2').html(end_time);
			$('#start_time_warning').show();
			$('#end_time_warning').show();
			$('#start_time_ok').hide();
			$('#end_time_ok').hide();
		} else {
			$('#start_time1').html(start_time);
			$('#end_time1').html(end_time);
			$('#start_time_ok').show();
			$('#end_time_ok').show();
			$('#start_time_warning').hide();
			$('#end_time_warning').hide();
		}
		
		if (duration == '') {
			$('#duration_warning').show();
			$('#duration_ok').hide();
		} else {
			$('#duration_ok').show();
			$('#duration_warning').hide();
		}
		
		if (timezone != '<?php echo $default_timezone; ?>') {
			$('#timezone').html(timezone);
			$('#timezone_row').show();
		} else {
			$('#timezone_row').hide();
		}
		
		if (labs == '' && password == '') {
			$('#lab_warning').show();
			$('#lab_ok').hide();
			lab_html = '';
		} else {
			$('#lab_ok').show();
			$('#lab_warning').hide();
			if (labs == '') {
				lab_html = '';
			} else {
				lab_parts = labs.split(","); 
				lab_html = '';
				$.each(lab_parts, function(key, value) {
					if (lab_html == '') {
						lab_html = lab_names[value];
					} else {
						lab_html += '<br />' + lab_names[value];
					}
				});
			}
		}
		$('#labs').html(lab_html);
		$('#password').html(password);
		if (password == '') {
			$('#pw_row').hide();
		} else {
			$('#pw_row').show();
		}
		$('#metadata').html(metadata);
		if (metadata == '') {
			$('#metadata_row').hide();
		} else {
			$('#metadata_row').show();
		}
		$('#callout').show();
  }
  
  function hideCallout() {
    $('#callout').hide();
  }
	
	$(document).ready(function() {
	  $('#lab').change(function() {
		  $('#theform').submit();
		});
		
	  $('#school').change(function() {
		  $('#theform').submit();
		});
		
	});
</script>
<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/calendar.css" />
<link rel="stylesheet" type="text/css" href="../css/tabs.css" />
</head>

<body>

<div id="callout" class="callout border-callout">
<b class="border-notch notch"></b>
<b class="notch"></b>
<table cellpadding="0" cellspacing="0" style="width:100%">
<tr id="timezone_row"><td><img src="../artwork/timezone_16.png" width="16" height="16" /></td><td class="field"><?php echo $string['timezone']; ?></td><td id="timezone"></td></tr>
<tr id="start_time_ok"><td></td><td class="field"><?php echo $string['starttime']; ?></td><td id="start_time1" style="width:90%"></td></tr>
<tr id="end_time_ok"><td></td><td class="field"><?php echo $string['endtime']; ?></td><td id="end_time1"></td></tr>
<tr id="start_time_warning" class="warning"><td class="warn_icon"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /></td><td class="field"><strong><?php echo $string['starttime']; ?></strong></td><td id="start_time2" style="width:90%"></td></tr>
<tr id="end_time_warning" class="warning"><td class="warn_icon"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /></td><td class="field"><strong><?php echo $string['endtime']; ?></strong></td><td id="end_time2"></td></tr>
<tr id="duration_ok"><td></td><td class="field"><?php echo $string['duration']; ?></td><td id="duration"></td></tr>
<tr id="duration_warning" class="warning"><td class="warn_icon"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /></td><td class="field"><strong><?php echo $string['duration']; ?></strong></td><td><?php echo $string['duration_warning']; ?></td></tr>
<tr id="lab_ok"><td></td><td class="field"><?php echo $string['labs']; ?></td><td id="labs"></td></tr>
<tr id="lab_warning" class="warning"><td class="warn_icon"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /></td><td class="field"><strong><?php echo $string['labs']; ?></strong></td><td><?php echo $string['lab_warning']; ?></td></tr>
<tr id="pw_row"><td class="warn_icon"><img src="../artwork/key_12.png" width="12" height="12" /></td><td class="field"><?php echo $string['password']; ?></td><td id="password" style="font-family:'Courier New'; font-weight:bold"></td></tr>
<tr id="metadata_row"><td class="warn_icon"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /></td><td class="field"><?php echo $string['metadata']; ?></td><td id="metadata"></td></tr>
</table>
</div>

<?php
  //get faculty and school info
  $schools = array($string['default']=>array('-1'=>$string['allschools']));
  $stmt = $mysqli->prepare("SELECT schools.id, faculty.name, school FROM schools, faculty WHERE faculty.id = schools.facultyID ORDER BY faculty.name, school");
  $stmt->execute();
  $stmt->bind_result($id, $faculty, $school);
  while ($stmt->fetch()) {
    $schools[$faculty][$id] = $school;
  }
  $stmt->close();

  //get computer lab info
  $lab_details = array($string['default']=>array('-1'=>$string['alllabs']));
  $stmt = $mysqli->prepare("SELECT id, building, room_no, campus FROM labs ORDER BY campus, building, room_no");
  $stmt->execute();
  $stmt->bind_result($id, $building, $room_no, $campus);
  while ($stmt->fetch()) {
    $lab_details[$campus][$id] = $building . ' - ' . $room_no;
  }
  $stmt->close();
?>

<form action="" method="get" id="theform">
<table class="header">
<tr><th>
<?php
  if (isset($_GET['module'])) {
    echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a></div>';
  } else {
    if ($userObject->has_role('SysAdmin')) {
      echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">' . $string['administrativetools'] . '</a></div>';
    } else {
      echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a></div>';
    }
  }
?>
<div style="font-size:200%; margin-left:10px"><strong><?php echo $string['calendar']; ?>:</strong> <?php echo $current_year; ?></div></th>
<th style="text-align:right">
<?php

  echo "<select name=\"lab\" id=\"lab\">";
  foreach ($lab_details as $campus => $lab) {
    echo "<optgroup label=\"$campus\">";
    foreach ($lab as $id => $title) {
      $selected = '';
      if (isset($_GET['lab']) and $id == $_GET['lab']) $selected = 'selected '; 
      echo "<option value=\"$id\" $selected>$title</option>";
    }
    echo "</optgroup>";
  }
  echo "</select>&nbsp;";

  echo "<select name=\"school\" id=\"school\">";
  foreach ($schools as $fac => $sch) {
    echo "<optgroup label=\"$fac\">";
    foreach ($sch as $id => $title) {
      $selected = '';
      if (isset($_GET['school']) and $id == $_GET['school']) $selected = 'selected '; 
      echo "<option value=\"$id\" $selected>$title</option>";
    }
    echo "</optgroup>";
  }
  echo "</select>&nbsp;";
  echo "<input type=\"hidden\" name=\"calyear\" value=\"$current_year\" /><br />";

  if (isset($_GET['module'])) {
    $extra = '&module=' . $_GET['module'];
  } else {
    $extra = '';
  }
?>
<div style="text-align:right; vertical-align:bottom"><?php echo drawTabs($current_year, 'calendar', 3, 2, $extra); ?></div>
</th>
</tr>
<tr><td colspan="2" style="border:0px; background-color:#1E3C7B; height:5px"></td></tr>
</table>
<br />
<?php
  function getDayOfWeek($day, $month, $year, $CalendarSystem) {
    // CalendarSystem = 1 for Gregorian Calendar
    if ($month < 3) {
      $month = $month + 12;
      $year = $year - 1;
    }
    return ($day + (2 * $month) + intval(6 * ($month + 1) / 10) + $year + intval($year/4) - intval($year/100) + intval($year/400) + $CalendarSystem) % 7;
  }

  $current_month = 1;

  // Get lab information.
  $lab_list = array();
  $stmt = $mysqli->prepare("SELECT id, room_no, name FROM labs");
  $stmt->execute();
  $stmt->bind_result($lab_id, $room_no, $name);
  while ($stmt->fetch()) {
    $lab_list[$lab_id]['room_no'] = $room_no;
    $lab_list[$lab_id]['name'] = $name;
  }
  $stmt->close();
  
  //show only exams in a particular school
  $schools_sql = '';
  if (isset($_GET['school']) and $_GET['school'] != '') {
    foreach ($schools as $fac => $sch) {
      foreach ($sch as $id => $title) {
        if ($id == $_GET['school']) {
          $school_name = $title;
          break 2;
        }
      }
    }
    //get the module list
    $schools_sql = '';
    $stmt = $mysqli->prepare("SELECT moduleid FROM modules WHERE schoolid = ?");
    $stmt->bind_param('i', $_GET['school']);
    $stmt->execute();
    $stmt->bind_result($moduleid);
    while ($stmt->fetch()) {
      if($schools_sql == '') {
        $schools_sql = ' AND (';
      } else {
        $schools_sql .= ' OR ';
      }
      $schools_sql .= " moduleID LIKE '%$moduleid%' ";
    }
    $stmt->close();
    if ($schools_sql != '') $schools_sql .= ')';
  }
  
  if (isset($_GET['lab']) and $_GET['lab'] != -1) {
    $lab_sql = " AND (labs='" . $_GET['lab'] . "' OR labs LIKE '%," . $_GET['lab'] . ",%' OR labs LIKE '" . $_GET['lab'] . ",%' OR labs LIKE '%," . $_GET['lab'] . "')";
  } else {
    $lab_sql = '';
  }
  
  $paper_no = 0;
  $paper_details = array();
  $paper_ids = array();
  if ($schools_sql != '' or !isset($_GET['school']) or (isset($_GET['school']) and ($_GET['school'] == -1 or $_GET['school'] == ''))) {
    // Get papers running on various dates.
    $result = $mysqli->prepare("SELECT password, exam_duration, DATE_FORMAT(start_date,'%Y/%m/%d') AS date, labs, DATE_FORMAT(start_date,'%H:%i') AS start_time, DATE_FORMAT(start_date,'%l') AS start_hour, DATE_FORMAT(start_date,'%i') AS start_minute, DATE_FORMAT(start_date,'%p') AS am_pm, DATE_FORMAT(end_date,'%H:%i') AS end_time, properties.property_id, paper_title, DATE_FORMAT(start_date,'%c') AS month, DATE_FORMAT(start_date,'%Y') AS cal_year, DATE_FORMAT(start_date,'%e') AS start_day, DATE_FORMAT(end_date,'%e') AS end_date, idMod, timezone FROM properties, properties_modules, modules WHERE properties.property_id = properties_modules.property_id AND properties_modules.idmod = modules.id AND start_date >= " . $current_year . "0101000000 AND end_date <= " . $current_year . "1231235959 AND paper_type='2' AND deleted IS NULL $schools_sql $lab_sql ORDER BY start_date");
    $result->execute();
    $result->bind_result($password, $duration, $main_date, $labs, $start_time, $start_hour, $start_minute, $am_pm, $end_time, $property_id, $paper_title, $month, $cal_year, $start_day, $end_date, $idMod, $timezone);
    while ($result->fetch()) {
      $paper_details[$property_id]['labs']        	= $labs;
      $paper_details[$property_id]['date']        	= $main_date;
      $paper_details[$property_id]['start_day']   	= $start_day;
      $paper_details[$property_id]['start_time']  	= $start_time;
      $paper_details[$property_id]['am_pm']       	= $am_pm;
      $paper_details[$property_id]['end_date']    	= $end_date;
			$paper_details[$property_id]['paper_title'] 	= $paper_title;
      if (strlen($paper_details[$property_id]['paper_title']) > 30) {
        $paper_details[$property_id]['paper_title'] = str_replace('_', ' ' , $paper_details[$property_id]['paper_title']);
      }
      $paper_details[$property_id]['property_id'] 	= $property_id;
      $paper_details[$property_id]['month']       	= $month;
      $paper_details[$property_id]['cal_year']    	= $cal_year;
      $paper_details[$property_id]['start_hour']  	= $start_hour;
      $paper_details[$property_id]['start_minute']  = $start_minute;
      $paper_details[$property_id]['end_time']    	= $end_time;
      $paper_details[$property_id]['idMod']       	= $idMod;
      $paper_details[$property_id]['password']    	= $password;
      $paper_details[$property_id]['duration']    	= $duration;
			if ($timezone == '') {
				$paper_details[$property_id]['timezone']		= '';
			} else {
				$paper_details[$property_id]['timezone']		= $timezone_array[$timezone];
			}
      $paper_ids[] = $property_id;
		}
    $result->close();
  }
  
  //get Metadata security
  $result = $mysqli->prepare('SELECT paperID, name, value FROM paper_metadata_security WHERE paperID IN (' . implode(',', $paper_ids) . ')');
  $result->execute();
  $result->bind_result($property_id, $metadata_name, $metadata_value);
  while ($result->fetch()) {
    $paper_details[$property_id]['metadata'][] = array('name'=>$metadata_name, 'value'=>$metadata_value);
  }
  $result->close();
  
  // Sort all papers correctly by start time
  $sortby = 'start_time';
  $ordering = 'asc';
  $paper_details = array_csort($paper_details, $sortby, $ordering);
  
  $cellID = 0;
  for ($i=1; $i<=12; $i++) {
    $current_full_month = date("m", mktime(0, 0, 0, $current_month, 1, $current_year));
    $days_in_month = date("t", mktime(0, 0, 0, $current_month, 1, $current_year));
    $paper_no = 0;

    echo "<div>";
    echo "<table class=\"monthgrid\">\n";
    $tmp_month = strtolower(date("F", mktime(0, 0, 0, $current_month, 1, $current_year)));
    echo "<tr><td class=\"month\"><a name=\"$i\"></a>" . $string[$tmp_month] . "</td></tr>\n";
    echo "<tr><td>";
    echo "<table style=\"width:100%; font-size:85%; margin-left:auto; margin-right:auto\">\n";
    echo "<tr><td class=\"dtext\">" . mb_substr($string['monday'],0,3,'UTF-8') . "</td><td class=\"dtext\">" . mb_substr($string['tuesday'],0,3,'UTF-8') . "</td><td class=\"dtext\">" . mb_substr($string['wednesday'],0,3,'UTF-8') . "</td><td class=\"dtext\">" . mb_substr($string['thursday'],0,3,'UTF-8') . "</td><td class=\"dtext\">" . mb_substr($string['friday'],0,3,'UTF-8') . "</td></tr>";
   
    $day_no = 1;
    $cell_no = 1;
    $subtract = 0;
    $start_day = getDayOfWeek(1, $current_month, $current_year, 1);
    if ($start_day == 6) {
      $start_day = 1;
      $day_no = 3;
    } elseif ($start_day == 0) {
      $start_day = 1;
      $day_no = 2;
    }
  
    do {
			$week_no = NULL;
			$tmp_day_no = $day_no - $subtract;
      for ($col=1; $col<=5; $col++) {
        if (($tmp_day_no) <= $days_in_month) {
				  if ($week_no == NULL) {
					  $week_no = date("W", mktime(0, 0, 0, $current_month, $tmp_day_no, $current_year));
					}
				}
        $tmp_day_no++;
			}
      echo "<tr id=\"week$week_no\">\n";
      for ($col=1; $col<=5; $col++) {
        if (($day_no - $subtract) <= $days_in_month) {
          if (($day_no - $subtract) != date("j") or $current_month != date("n") or $current_year != date("Y")) {
            // Day in month but not today
            if ($week_no == date("W")) {
							echo '<td class="dheadthisweek">';
						} else {
							echo '<td class="dhead">';
            }
						if ($day_no >= $start_day) {
              echo ($day_no-$subtract);
            } else {
              echo '&nbsp;';
              $subtract++;
            }
            echo '</td>';
          } elseif ($day_no >= $start_day) {
            // Today
            echo "<td class=\"dheadtoday\">" . ($day_no-$subtract) . "</td>\n";
          } else {
            // Day not in month
            echo "<td class=\"dheadnomonth\">&nbsp;</td>\n";
            $subtract++;
          }
        } else {
          // Day not in month
          echo "<td class=\"dheadnomonth\">&nbsp;</td>\n";
        }
        $day_no++;
      }
      echo '</tr>';
      $day_no -= 5;  // reset day number.
      
      echo '<tr style="height:80px">';
      for ($col=1; $col<=5; $col++) {
        if (($day_no - $subtract) < 1 or $day_no < $start_day) {    // Day on grid before start of month.
          echo '<td class="daynomonth">&nbsp</td>';
        } elseif (($day_no - $subtract) <= $days_in_month) {
          if (($day_no - $subtract) == date("j") and $current_month == date("n") and $current_year == date("Y")) {  // Current day
            echo '<td class="daycur"';
            echo "\">";
          } else {
            echo '<td class="day"';
            echo "\">";
          }
          $papers = 0;
          echo "<table id=\"month_grid\" style=\"width:100%\">\n";
          foreach ($paper_details as $individual_paper) {
					  display_paper($day_no, $subtract, $current_year, $current_month, $individual_paper, $papers, $cellID, $string, $default_timezone);
          }
          echo "</table>\n";
          if ($papers == 0) echo '&nbsp;';
          
          if ($col == 5) {  // Check for Saturday exams.
            $saturday_exams = false;
            $day_number = '';
            foreach ($paper_details as $individual_paper) {
              if ($individual_paper['start_day'] == (($day_no + 1) - $subtract) and $individual_paper['cal_year'] == $current_year and $individual_paper['month'] == $current_month) {
                $saturday_exams = true;
                $day_number = $individual_paper['start_day'];
              }
            }
          
            if ($saturday_exams == true) {
              echo "<br /><table style=\"width:100%\">";
							if ($day_number == date("j") and $current_month == date("n") and $current_year == date("Y")) {  // Current day
								echo "<tr><td class=\"dheadtoday\" style=\"border-left:0px\">$day_number &#8211; " . $string['saturday'] . "</td></tr>";
							} else {
								echo "<tr><td class=\"dhead\" style=\"border-left:0px\">$day_number &#8211; " . $string['saturday'] . "</td></tr>";
							}
							echo "</table>";              
              echo "<table style=\"padding-top:5px; width:100%\">";            
              foreach ($paper_details as $individual_paper) {
								display_paper($day_no + 1, $subtract, $current_year, $current_month, $individual_paper, $papers, $cellID, $string, $default_timezone);
              }
              echo "</table>";
            }
          }
          
          echo "</td>";
        } else {        // Day on grid after end of month.
          echo '<td class="daynomonth">&nbsp;</td>';
        }
        $day_no++;
      }
      echo "</tr>\n";
      
      $day_no += 2;  // Skip the weekend.
    } while (($day_no-$subtract) <= $days_in_month);
    echo "</table>\n</td></tr>\n</table></div><br />\n";
    
    $current_month++;
  }
  $mysqli->close();
?>
</form>

</body>
</html>
