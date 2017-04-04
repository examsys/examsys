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
* Displays a summary of a particular paper. Initial screen called by a VLE and is used to launch start.php.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';


// Redirect Invigilators to their own areas.
if ($userObject->has_role('Invigilator')) {
  header("location: ../invigilator/");
  exit();
}

check_var('id', 'GET', true, false, false);

function load_attempts($test_type, $paperID, $userObj, $db) {
  $prev_attempts = array();

  $result = $db->prepare("SELECT lm.id, MAX(l.screen) AS screen, SUM(l.mark) AS mark, DATE_FORMAT(lm.started,\"%Y%m%d%H%i%s\") AS started, ? AS paper_type, DATE_FORMAT(lm.started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log_metadata lm LEFT JOIN log$test_type l ON l.metadataID = lm.id WHERE started IS NOT NULL AND lm.paperID = ? AND lm.userID = ? AND screen IS NOT NULL GROUP BY started DESC");
  $result->bind_param('iii', $test_type, $paperID, $userObj->get_user_ID());
  $result->execute();
  $result->bind_result($metadataID, $log_max_screen, $log_mark, $log_started, $log_paper_type, $log_temp_date);
  while ($result->fetch()) {
    $prev_attempts[$log_started] = array('metadataID'=>$metadataID, 'max_screen'=>$log_max_screen, 'max_mark'=>$log_mark, 'paper_type'=>$log_paper_type, 'temp_date'=>$log_temp_date);
  }
  $result->close();
	
  if ($test_type == '0') {
    // If type is Formative query the Progress Test log table as well and add into array if max screen is not blank.
    $result = $db->prepare("SELECT lm.id, MAX(l.screen) AS screen, SUM(l.mark) AS mark, DATE_FORMAT(lm.started,\"%Y%m%d%H%i%s\") AS started, 1 AS paper_type, DATE_FORMAT(lm.started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log_metadata lm LEFT JOIN log1 l ON l.metadataID = lm.id WHERE started IS NOT NULL AND lm.paperID = ? AND lm.userID = ? AND screen IS NOT NULL GROUP BY started DESC");
    $result->bind_param('ii', $paperID, $userObj->get_user_ID());
    $result->execute();
    $result->bind_result($metadataID, $log_max_screen, $log_mark, $log_started, $log_paper_type, $log_temp_date);
    while ($result->fetch()) {
      if ($log_max_screen > 0) {
        $prev_attempts[$log_started] = array('metadataID'=>$metadataID, 'max_screen'=>$log_max_screen, 'max_mark'=>$log_mark, 'paper_type'=>$log_paper_type, 'temp_date'=>$log_temp_date);
      }
    }
    $result->close();
  }

  return $prev_attempts;
}

function is_timedate_ok($startdate, $enddate) {
  if (time() < $startdate or time() > $enddate) {
    return false;
  } else {
    return true;
  }
}

function is_timedate_ok_and_within_15min($startdate, $enddate) {
  if ((time()+(15*60)) < $startdate or time() > $enddate) {
    return false;
  } else {
    return true;
  }
}

function has_time_remaining($propertyObj, $remaining_time) {
  if ($propertyObj->get_exam_duration() === null) {
    return true;
  }

  if ($remaining_time === false) {
    return true;
  }

  if ((int)$remaining_time === 0) {
    return false;
  }

  return true;
}

function have_previously_started($attempts) {
  if (count($attempts) == 0) {
    return false;
  } else {
    return true;
  }
}

function calculate_duration($normal, $extra_time_mins, $special_needs_percentage) {
  $mins = $normal;
  if ($extra_time_mins != NULL) $mins .= ' + ' . $extra_time_mins;
  if ($special_needs_percentage != NULL) $mins .= ' + ' . ($normal / 100) * $special_needs_percentage;

  return $mins;
}

function displayPrevTake($markTotal, $totalRandomMark, $marking_style, $disDate, $type, $metadataID) {
  global $total_marks, $low_bandwidth;

  if ($low_bandwidth == 0) {
    echo "<tr><td><img src=\"../artwork/bullet_outline.gif\" class=\"bullet\" alt=\"bullet\" /><a href=\"\" onclick=\"reviewPaper($metadataID,$type); return false;\">$disDate</a></td><td style=\"text-align:right\" width=\"70\">";
  } else {
    echo "<tr><td><a href=\"\" onclick=\"reviewPaper($metadataID,$type); return false;\">$disDate</a></td><td style=\"text-align:right\" width=\"70\">";
  }
  if ($total_marks > 0) {
		if ($marking_style == 1) {
			$adjPercent = number_format((($markTotal-$totalRandomMark)/($total_marks-$totalRandomMark))*100, 1, '.', ',');
			if ($adjPercent < 0) $adjPercent = 0;
			echo $adjPercent . '%';
		} else {
			echo number_format(($markTotal/$total_marks)*100, 1, '.', ',') . '%';
		}
  }
  echo '</td></tr>';
}

$special_needs_percentage = 0;
$textsize = 100;
$font = 'Arial';

if ($userObject->is_special_needs()) {
  // Look up special_needs data
  $special_needs_percentage = $userObject->get_special_needs_percentage();
  $textsize = $userObject->get_textsize($textsize);
  $font = $userObject->get_font($font);
}

if ($userObject->is_temporary_account()) {
  $person = '<img src="../artwork/guest_account_16.png" width="16" height="16" alt="Guest User" /> ' . $string['guestaccount'] . ' (' . $userObject->get_temp_title() . ' ' . $userObject->get_temp_surname() . ')';
} else {
  $person = $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname();
}
$total_random_mark = 0;
$total_marks = 0;

// Create paper object.
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

// Get lab information.
$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)){
  $lab_id   = $lab_object->get_id();
} else {
  $lab_id = null;
}

$property_id        = $propertyObj->get_property_id();
$paper_title        = $propertyObj->get_paper_title();
$total_random_mark  = $propertyObj->get_random_mark();
$total_marks        = $propertyObj->get_total_mark();
$navigation         = $propertyObj->get_bidirectional();
$paper_screens      = $propertyObj->get_max_screen();
$test_type          = $propertyObj->get_paper_type();
$paper_start        = $propertyObj->get_start_date();
$paper_end          = $propertyObj->get_end_date();
$timezone           = $propertyObj->get_timezone();
$fullscreen         = $propertyObj->get_fullscreen();
$marking            = $propertyObj->get_marking();
$labs               = $propertyObj->get_labs();
$rubric             = $propertyObj->get_rubric();
$exam_duration      = $propertyObj->get_exam_duration();
$exam_duration_sec  = $exam_duration * 60;
$calendar_year      = $propertyObj->get_calendar_year();
$sound_demo         = $propertyObj->get_sound_demo();
$password           = $propertyObj->get_password();
$modIDs             = array_keys($propertyObj->get_modules());
$deleted            = $propertyObj->get_deleted();

// If OSCE paper or if the paper has been deleted we should exit as this is an invalid page.
if ($test_type == '4' OR $deleted != NULL) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

// If the start / end date has not been set yet we need to set $display_start_date to '' to prevent errors on hidden input variables.
if (empty($paper_start) or empty ($paper_end)) {
	$display_start_date = '';
} else {
	// Adjust for timezones.
	$UK_time = new DateTimeZone("Europe/London");
	$target_timezone    = new DateTimeZone($timezone);
	$display_start_date = DateTime::createFromFormat('U', $paper_start, $UK_time);
	$display_end_date   = DateTime::createFromFormat('U', $paper_end, $UK_time);

	$display_start_date->setTimezone($target_timezone);
	$display_end_date->setTimezone($target_timezone);

	$tmp_cfg_long_date_time = str_replace('%', '', $configObject->get('cfg_long_date_time'));

	$display_start_date = $display_start_date->format($tmp_cfg_long_date_time);
	$display_end_date   = $display_end_date->format($tmp_cfg_long_date_time);
}
$previously_submitted = 0;

$low_bandwidth = 0;
if ($userObject->has_role('Student')) {
  // Check for additional password on the paper
  check_paper_password($propertyObj->get_property_id(), $password, $string, $mysqli, true);

  //Check this PC is registered for this exam
  $low_bandwidth = check_labs($test_type, $labs, $current_address, $password, $string, $mysqli);

  $attempt = check_modules($userObject, $modIDs, $calendar_year, $string, $mysqli);
}

$display_remaining_time = false;
$remaining_minutes = '';
$remaining_seconds = '';

/*
 * BP If the duration is set then create a timer to calculate and display the remaining time
 */
$extra_time = null;
$remaining_time = 0;
$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
// $log_metadata->get_record will return true if this user has stared this exam. false otherwise
$exam_started = $log_metadata->get_record('', false);

if ($exam_duration !== null) {

  if ($test_type == '2') {
    $student_object['special_needs_percentage'] = $special_needs_percentage;
    $student_object['user_ID']   = $userObject->get_user_ID();
    $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
    $log_extra_time   = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);
    $extra_time_secs  = $log_extra_time->get_extra_time_secs();
    $extra_time_mins  = $extra_time_secs / 60;
    $summative_timer  = new SummativeTimer( $log_extra_time );
    $remaining_time   = $summative_timer->calculate_remaining_time_secs();
    if ($remaining_time !== false) {
      $display_remaining_time = true;

      if ($exam_started == false and $remaining_time == 0) {
        // Sanity check if we have not started the exam but time remaing is 0
        // happens in summative exams if we have the start and end time set wider
        // then the paper duration e.g in multiple sittings
        $remaining_time = $exam_duration_sec + $extra_time_secs;
        $display_remaining_time = false;
      }
    }
    $extra_time_mins    = $extra_time_secs / 60;
  } else {
    if ($test_type == '1') {
      $display_remaining_time = true;
    }
    $studentID       = $userObject->get_user_ID();
    $timer           = new Timer($log_metadata, $exam_duration, $special_needs_percentage);
    $remaining_time  = $timer->calculate_remaining_time();

    $extra_time_mins = null;
  }

  $remaining_minutes = (int) ($remaining_time / 60);
  $remaining_seconds = (int) ($remaining_time % 60);
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['startscreen']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/user_index.css" />
  <style type="text/css">
    <?php
    if (isset($_SESSION['_lti_context'])) {
      echo "  body {background-color:transparent !important;font-size:$textsize%; font-family:$font}\n";
    } else {
      echo "  body {font-size:$textsize%; font-family:$font}\n";
    }
    ?>
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/student_help.js"></script>
  <script>
  function startPaper() {
<?php
	if ($userObject->has_role('External Examiner')) {
		echo '  var paperURL = "../reviews/start.php?id=' . $_GET['id'] . '";'; // External examiners
	} else {
		echo '  var paperURL = "../paper/start.php?id=' . $_GET['id'] . '";';   // Normal staff and students
	}
	if ($userObject->has_role(array('Staff','Admin','SysAdmin')) and isset($_GET['mode']) and $_GET['mode'] == 'preview') {
?>
    paperURL += '&mode=preview';
<?php
  }
?>
    exam = window.open(paperURL,"paper","fullscreen=<?php echo $fullscreen; ?>,width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,menubar=no,titlebar=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes");
    if (window.focus) {
      exam.focus();
    }
  }

  function reviewPaper(metadataID, type) {
    exam = window.open("finish.php?id=<?php echo $_GET['id']; ?>&metadataID="+metadataID+"&log_type="+type+"","paper","fullscreen=<?php echo $fullscreen; ?>,width="+(screen.width-80)+",height="+(screen.height-80)+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      exam.focus();
    }
  }
  
  $(function () {
    $(document).click(function() {
      $('#toprightmenu').fadeOut();
    });
    
    $(document).tooltip({ items: ".help_tip[title]", position: { my: "top+10", at: "center+125" }  });
  });
  </script>
</head>
<body>
<div style="text-align:right; padding-right:2px;"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
  require '../include/toprightmenu.inc';
	echo draw_toprightmenu(14);
?>
<br clear="all" />
<form name="theform" autocomplete="off">
<?php
if ($textsize > 120) {
  $table_width = 90;
  $button_width = 160;
} else {
  $table_width = 80;
  $button_width = 125;
}
?>
<table cellpadding="0" cellspacing="0" border="0" style="margin-left:auto; margin-right:auto; margin-top:40px; font-size:100%; border-top:1px solid #95AEC8;border-left:1px solid #95AEC8; border-right:1px solid #95AEC8; background-color:white; width:<?php echo $table_width; ?>%">
<tr>
<?php
  $icon_types = array('formative', 'progress', 'summative', 'survey');
  echo '<td colspan="2"><table cellspacing="4" cellpadding="0" border="0" style="width:100%"><tr><td style="width:52px"><img src="../artwork/' . $icon_types[$test_type] . '.png" style="width:48px; height:48px; padding-left:4px" alt="Icon" />';
  echo "</td><td><span class=\"paper_title\">$paper_title</span></td>\n</tr></table></td></tr>";
  echo "<tr>\n</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:95%; margin-left:auto; margin-right:auto;border:1px solid #95AEC8;background-color:#F1F5FB\" width=\"$table_width%\">\n";
  echo '<tr><td colspan="4">&nbsp;</td>';
  if ($test_type == 2) {
    $student_photo = UserUtils::student_photo_exist($userObject->get_username());
    $photodirectory = rogo_directory::get_directory('user_photo');
    if ($student_photo !== false) {
      $photo_size = getimagesize($photodirectory->fullpath($student_photo));
      echo '<td rowspan="';
      if ($sound_demo == '1') {
        echo '8';
      } else {
        echo '7';
      }
      echo '" style="vertical-align:top; padding:8px"><div class="photoid">' . $string['photoid'] 
          . '</div><img src="' . $photodirectory->url($student_photo) . '" ' . $photo_size[3]
          . ' alt="Photo" style="border: 10px solid white" /></td>';
    }
  }
  echo '</tr>';
  if ($rubric != '') echo '<tr><td class="f" style="vertical-align:top"><nobr>' . $string['rubric'] . '</nobr></td><td colspan="3" style="text-align:justify; line-height:140%; padding-right:20px; padding-bottom:15px">' . $rubric . '</td></tr>';

  if ($test_type != '2') {
    $html = '';
    if ((time() < $paper_start or time() > $paper_end) and !$userObject->has_role('External Examiner')) {
      $html = ' class="warn"';
    }
    if (empty($paper_start) or empty ($paper_end)) {
      // The start / end date has not been set yet so display Availability: Not set to the user.
      echo '<tr><td class="f"><nobr>' . $string['availability'] . '</nobr></td><td colspan="3"' . $html . '>' . $string['notset'];
    } else {
      echo '<tr><td class="f"><nobr>' . $string['availability'] . '</nobr></td><td colspan="3"' . $html . '>' . $display_start_date . ' ' . $string['to'] . ' ' . $display_end_date;
    }
    if ($timezone != 'Europe/London') echo ' (' . str_replace('_',' ',$timezone) . ')';
  }
  echo '<input type="hidden" name="startdate" value="' . $display_start_date . '" /><input type="hidden" name="testtype" value="' . $test_type . "\" /></td></tr>\n";
  echo "<tr><td class=\"f\"><nobr>" . $string['candidates'] . "</nobr></td><td colspan=\"3\">";
  $html = '';
  foreach ($modIDs as $modID) {
    $mod_details = module_utils::get_full_details_by_ID($modID, $mysqli);
    if ($html == '') {
      $html = $mod_details['moduleid'];
    } else {
      $html .= ', ' . $mod_details['moduleid'];
    }
  }
  echo $html . '</td></tr>';

  // Display any metadata
  $metadata_security = true;
  $metadata_msg = '';
  $metadata = Paper_utils::get_metadata($property_id, $mysqli);
	if (!$userObject->is_temporary_account()) {			// Do not check metadata security if temporary account
		foreach ($metadata as $security_type=>$security_value) {
			$html = '';
			if (!$userObject->has_metadata($modIDs, $security_type, $security_value)) {
				$metadata_security = false;
				$metadata_msg = sprintf($string['metadata_msg'], $security_type, $security_value);
				$html = ' class="warn"';
			}
			echo "<tr><td class=\"f\">$security_type</td><td$html>$security_value</td><td></td><td></td></tr>\n";
		}
	}

  echo '<tr><td class="f"><nobr>' . $string['screens'] . '</nobr></td><td>' . $paper_screens . '</td>';
  echo '<td class="f">' . $string['navigation'] . '</td><td>';
  if ($navigation == 1) {
    echo $string['bidirectional'] . ' <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_bidirectional'] . '" />';
  } else {
    echo $string['unidirectional'] . ' <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_unidirectional'] . '" />';
  }
  echo '</td></tr>';
  if ($test_type < 3) {
    echo '<tr><td class="f">' . $string['marks'] . '</td>';
    echo '<td colspan="3">' . $total_marks;
    if ($marking == 1) {
      echo ' (' . $string['adjusted'] . ' ' . number_format($total_random_mark, 2, '.', ',') . ')';
      
      echo ' <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_adjustmark'] . '" />';
    }
    echo '</td></tr>';
  }
  echo "<tr><td class=\"f\"><nobr>&nbsp;" . $string['currentuser'] . "</nobr></td><td>$person</td>";
  if ($exam_duration) {
    $duration_mins = calculate_duration($exam_duration, $extra_time_mins, $special_needs_percentage);
    echo '<td class="f">' . $string['duration'] . '</td><td>' . StringUtils::nice_duration($duration_mins, $string) . '</td>';
  } else {
    echo '<td></td><td></td>';
  }
  echo '</tr>';

  if ($display_remaining_time === true) {
    ?>
    <tr>
       <td></td>
       <td></td>
       <td class="f"><?php echo $string['timeremaining'] ?></td>
       <?php
       if ($remaining_time == 0) {
         echo '<td><span style="background-color:#C00000; color:white">&nbsp;' . $remaining_minutes .' '. $string['mins'] . ' ' . $remaining_seconds  .' '. $string['secs'] . '&nbsp;</span></td>';
       } else {
         echo '<td>' . $remaining_minutes .' '. $string['mins'] . ' ' . $remaining_seconds  .' '. $string['secs'] . '</td>';
       }
       ?>
    </tr>

    <?php
  }

  if ($sound_demo == '1') {
    echo "<tr><td colspan=\"4\" style=\"text-align:center\">";
    echo "<audio src=\"{$configObject->get('cfg_root_path')}/paper/sound_demo.mp3\" controls>\n";
    echo "</audio> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_testclip'] . "\" />\n";
    echo "</td></tr>\n";
  }

  $prev_attempts = load_attempts($test_type, $property_id, $userObject, $mysqli);

  $start_label = $string['start'];
  if ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin', 'External Examiner'))) {
    $start_available      = true;
    $remaining_available  = true;
    $metadata_security    = true;
  } else {
    $start_available = false;
    $remaining_available = false;

    switch ($test_type) {
      case '0':
       $start_available = is_timedate_ok($paper_start, $paper_end);
       $remaining_available = true;
       break;
      case '1':
       $start_available = is_timedate_ok($paper_start, $paper_end);
       $remaining_available = has_time_remaining($propertyObj, $remaining_time);
       break;
      case '2':
       $start_available = is_timedate_ok_and_within_15min($paper_start, $paper_end);
       $remaining_available = has_time_remaining($propertyObj, $remaining_time);
       break;
      case '3':
       $start_available = is_timedate_ok($paper_start, $paper_end);
       $remaining_available = has_time_remaining($propertyObj, $remaining_time);
       break;
    }
  }

  echo '<tr><td style="text-align:center" colspan="4"><br />';

  if ($start_available === false) {
    echo "<div style=\"color:#C00000;font-size:90%\">" . $string['papernotavailable'] . "</div>\n";
  } elseif ($remaining_available === false) {
    echo "<div style=\"color:#C00000;font-size:90%\">" . $string['timeexpired'] . "</div>\n";
  } elseif ($metadata_security === false) {
    echo "<div style=\"color:#C00000;font-size:90%\">$metadata_msg</div>\n";
  } elseif ($test_type == '2' and !$userObject->has_role('External Examiner')) {
    echo "<div style=\"color:#C00000;font-size:90%\">" . $string['donotstart'] . "</div>\n";
  }

  if ($test_type == 2) {
    $paper_utils = Paper_utils::get_instance();
    $paper_display = array();
    $paper_no = $paper_utils->get_active_papers($paper_display, array('1', '2'), $userObject, $mysqli, $property_id);
    if ($paper_no > 0) echo "<input class=\"ok\" type=\"button\" style=\"margin-right:20px; width:" . $button_width . "px\" value=\"" . $string['switchpapers'] . "\" name=\"switch\" onclick=\"window.location='index.php'\" />";
  }

  $display_date = '';

    if ($start_available and $remaining_available and $metadata_security) {
    echo "<input type=\"button\" class=\"ok\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"$start_label\" name=\"start\" id=\"start\" onclick=\"startPaper();\" onkeypress=\"startPaper();\" />\n";
  } else {
    echo "<input type=\"button\" class=\"notok\" style=\"width:" . $button_width . "px\" value=\"" . $string['start'] . "\" name=\"start\" disabled />\n";
  }

  echo '<br />&nbsp;';

  if ($test_type != '2') {
    // Display previous attempts
    if (count($prev_attempts) > 0) {
      $old_started = '';
      $old_screen = 0;
      $temp_no = 0;
      $mark_total = 0;

			echo '<hr />';
			echo '<table cellpadding="0" cellspacing="0" border="0" align="center">';
			echo '<tr><td colspan="4" style="text-align:center; padding-bottom:0.5em"><strong>' . $string['previouscompletions'] . '</strong></td></tr>';
			
      foreach ($prev_attempts as $log_started=>$prev_details) {
        $log_max_screen = $prev_details['max_screen'];
        $log_mark       = $prev_details['max_mark'];
        $log_paper_type = $prev_details['paper_type'];
        $log_temp_date  = $prev_details['temp_date'];
				$metadataID			= $prev_details['metadataID'];
				
				if ($test_type == 0) {
					displayPrevTake($log_mark, $total_random_mark, $marking, $log_temp_date, $log_paper_type, $metadataID);
				} else {
					if ($low_bandwidth == 0) {
						echo "<tr><td><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<span style=\"color:#808080\">$log_temp_date</span></td><td>&nbsp;</td></tr>\n";
					} else {
						echo "<tr><td><span style=\"color:#808080\">$log_temp_date</span></td><td>&nbsp;</td></tr>\n";
					}
				}
				$mark_total = 0;
        
      }

      echo '</table><br />';
    } else {
      echo '<hr />' . $string['nottakenpaper'] . '</p><br />';
    }
  }
  $mysqli->close();
  ?></td></tr></table>
</form>
<div class="powered"><i>powered by</i> Rog&#333; <?php echo $configObject->get('rogo_version'); ?></div>

	<!-- Cache often used scripts and images -->
	<script src="../js/start.js"></script>
	<img class="noimg" src="../artwork/calc.png" />
	<img class="noimg" src="../artwork/no_save.png" />
	<img class="noimg" src="../artwork/fire_exit.png" />

  <?php
    $render = new render($configObject);
    $render->render_html5_js(json_encode($jstring));
  ?>
	<img class="noimg" src="../js/images/combined.png" />
</body>
</html>
