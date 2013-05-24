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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once './include/staff_student_auth.inc';
require_once './include/errors.inc';
require_once './include/paper_security.inc';

require_once './classes/paperutils.class.php';
require_once './classes/moduleutils.class.php';
require_once './classes/logmetadata.class.php';
require_once './classes/timer.class.php';
require_once './classes/lab_factory.class.php';
require_once './classes/lab.class.php';
require_once './classes/log_extra_time.class.php';
require_once './classes/log_lab_end_time.class.php';
require_once './classes/summativetimer.class.php';
require_once './classes/paperproperties.class.php';

check_var('id', 'GET', true, false, false);

function display_duration($normal, $extra_time_mins, $special_needs_percentage) {
  $mins = $normal;
  if ($extra_time_mins != NULL) $mins .= ' + ' . $extra_time_mins;
  if ($special_needs_percentage != NULL) $mins .= ' + ' . ($normal/100)*$special_needs_percentage;

  return $mins;
}

function displayPrevTake($markTotal, $adjPercent, $totalRandomMark, $marking_style, $disDate, $type) {
  global $rerun_date, $total_marks, $low_bandwidth;

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

$special_needs_percentage = 0;
$textsize = 100;
$font = 'Arial';

if ($userObject->is_special_needs()) {
  //look up special_needs data
  $special_needs_percentage = $userObject->get_special_needs_percentage();
  $textsize = $userObject->get_textsize($textsize);
  $font = $userObject->get_font($font);
}

// Adjust text size
$textsize -= 5;

if ($userObject->is_temporary_account()) {
  $person = '<img src="./artwork/guest_account_16.png" width="16" height="16" alt="Guest User" /> ' . $string['guestaccount'] . ' (' . $userObject->get_temp_title() . ' ' . $userObject->get_temp_surname() . ')';
} else {
  $person = $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname();
}
$total_random_mark = 0;
$total_marks = 0;

//get paper info
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $notice->access_denied($mysqli, $string, $string['papernotfound'], true, true);    //this will exit php
}

//get lab info
$current_ip_address = NetworkUtils::get_ipaddress();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_ip($current_ip_address)){
  $lab_name = $lab_object->get_name();
  $lab_id   = $lab_object->get_id();
}

$property_id        = $propertyObj->get_property_id();
$paper_title        = $propertyObj->get_paper_title();
$total_random_mark  = $propertyObj->get_random_mark();
$total_marks        = $propertyObj->get_total_mark();
$navigation         = $propertyObj->get_bidirectional();

$paper_screens      = Paper_utils::get_numder_of_screens($property_id, $mysqli);

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

$modIDs = array_keys(Paper_utils::get_modules($property_id, $mysqli));

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

$previously_submitted = 0;

$low_bandwidth = 0;
if ($userObject->has_role('Student')) {
  // Check for additional password on the paper
  check_paper_password($password, $string, true);

  //Check this PC is registered for this exam
  $low_bandwidth = check_labs($test_type, $labs, $current_ip_address, $password, $string, $mysqli);

  $attempt = check_modules($userObject, $modIDs, $calendar_year, $mysqli);
}

$display_remaining_time = false;
$remaining_minutes = '';
$remaining_seconds = '';

/*
 * BP If the duration is set then create a timer to calculate and display the remaining time
 */
$extra_time = null;

$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
// $log_metadata->get_record will return true if this user has stared this exam. false otherwise
$exam_started = $log_metadata->get_record();

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

      // nazrji - remove pending consultation with Exams Office
      // if ($remaining_time > ($exam_duration_sec + ($exam_duration_sec * $student_object['special_needs_percentage']) + $extra_time_secs) ) {
      //   // sanity check if we have longer remaining then the exam duration set the time remaining
      //   // to the exam duration (happens in summative exams if we have not started yet)
      //   $remaining_time = $exam_duration_sec + ($exam_duration_sec * $student_object['special_needs_percentage']) + $extra_time_secs;
      //  }
      if ($exam_started == false and $remaining_time == 0) {
        // sanity check if we have not started the exam but time remaing is 0
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['startscreen']; ?></title>

  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <link rel="stylesheet" type="text/css" href="./css/user_index.css" />
  <style type="text/css">
    body {margin-top:25px; font-size:<?php echo $textsize; ?>%; font-family: <?php echo $font ?>}
  </style>

  <script type="text/javascript" src="./js/student_help.js"></script>
  <script language="JavaScript">
  function startPaper() {
    var paperURL = "./paper/start.php?id=<?php echo $_GET['id']; ?>";
<?php
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
    document.getElementById('start').value = '<?php echo $string['restart']; ?>';
  }
  function reviewPaper(started,type) {
    exam = window.open("./paper/finish.php?id=<?php echo $_GET['id']; ?>&previous="+started+"&log_type="+type+"","paper","fullscreen=<?php echo $fullscreen; ?>,width="+(screen.width-80)+",height="+(screen.height-80)+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      exam.focus();
    }
  }
  </script>
</head>
<body>
<form name="theform">
<?php
if ($textsize > 120) {
  $table_width = 90;
  $button_width = 150;
} else {
  $table_width = 80;
  $button_width = 115;
}
?>
<table cellpadding="3" cellspacing="0" border="0" style="margin-left:auto; margin-right:auto; font-size:100%; border-top:1px solid #95AEC8;border-left:1px solid #95AEC8; border-right:1px solid #95AEC8; background-color:white; width:<?php echo $table_width; ?>%">
<tr>
<?php
  $icon_types = array('formative.png', 'progress.png', 'summative.png', 'survey.png');
  echo '<td colspan="2"><table cellspacing="4" cellpadding="0" border="0" style="width:100%"><tr><td style="vertical-align:top; width:52px">&nbsp;<img src="./artwork/' . $icon_types[$test_type] . '" width="48" height="48" alt="Icon" />';
  echo "</td><td><span class=\"title\">$paper_title</span>";
  echo "<div class=\"logout\"><a href=\"logout.php\"><img src=\"./artwork/student_logout.png\" width=\"24\" height=\"24\" alt=\"Log Out\" /></a></div><div class=\"logout\" style=\"padding-right:8px\"><a class=\"logout\" href=\"logout.php\">" . $string['signout'] . "</a></div>";
  echo "</td>\n</tr></table></td></tr>";
  echo "<tr>\n</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"margin-left:auto; margin-right:auto;border:1px solid #95AEC8;background-color:#F1F5FB\" width=\"$table_width%\">\n";
  echo '<tr><td colspan="4">&nbsp;</td>';
  if ($test_type == 2) {
    if (file_exists($cfg_web_root . 'users/photos/' . $userObject->get_username() . '.jpg')) {
      echo '<td rowspan="';
      if ($sound_demo == '1') {
        echo '8';
      } else {
        echo '7';
      }
      echo '" style="border-left:1px solid #95AEC8;background-color:white;width:180px;text-align:center;vertical-align:bottom"><img src="./users/photos/' . $userObject->get_username() . '.jpg" width="180" height="270" border="0" alt="Photo" /></td>';
    }
  }
  echo '</tr>';
  if ($rubric != '') echo '<tr><td class="f" style="vertical-align:top"><nobr>' . $string['rubric'] . '</nobr></td><td colspan="3" style="text-align:justify; line-height:140%; padding-right:20px; padding-bottom:15px">' . $rubric . '</td></tr>';

  if ($test_type != '2') {
    $html = '';
    if (time() < $paper_start or time() > $paper_end) {
      $html = ' class="warn"';
    }
    echo '<tr><td class="f"><nobr>' . $string['availability'] . '</nobr></td><td colspan="3"' . $html . '>' . $display_start_date . ' ' . $string['to'] . ' ' . $display_end_date;
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
  $metadata = Paper_utils::get_metadata($property_id, $mysqli);
  foreach ($metadata as $security_type=>$security_value) {
    $html = '';
    if (!$userObject->has_metadata($modIDs, $security_type, $security_value)) {
      $metadata_security = false;
      $html = ' class="warn"';
    }
    echo "<tr><td class=\"f\">$security_type</td><td$html>$security_value</td><td></td><td></td></tr>\n";
  }

  echo '<tr><td class="f"><nobr>' . $string['screens'] . '</nobr></td><td>' . $paper_screens . '</td>';
  echo '<td class="f">' . $string['navigation'] . '</td><td>';
  if ($navigation == 1) {
    echo $string['bidirectional'];
  } else {
    echo $string['unidirectional'];
  }
  echo '</td></tr>';
  if ($test_type < 3) {
    echo '<tr><td class="f">' . $string['marks'] . '</td>';
    echo '<td colspan="3">' . $total_marks;
    if ($marking == 1) echo ' (' . $string['adjusted'] . ' ' . number_format($total_random_mark, 2, '.', ',') . ')';
    echo '</td></tr>';
  }
  echo "<tr><td class=\"f\"><nobr>&nbsp;" . $string['currentuser'] . "</nobr></td><td>$person</td>";
  if ($exam_duration) {
    echo '<td class="f">' . $string['duration'] . '</td><td>' . display_duration($exam_duration, $extra_time_mins, $special_needs_percentage) . ' ' . $string['minutes'] . '</td>';
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
       <td><?php echo $remaining_minutes .' '. $string['mins'] . ' ' . $remaining_seconds  .' '. $string['secs']?></td>
    </tr>

    <?php
  }

  if ($sound_demo == '1') {
    echo "<tr><td colspan=\"4\" style=\"text-align:center\"><span class=\"testclip\">" . $string['testclip'] . "</span>&nbsp;&nbsp;<object type=\"application/x-shockwave-flash\" data=\"./paper/player_mp3_maxi.swf\" width=\"200\" height=\"20\">\n";
    echo "<param name=\"wmode\" value=\"transparent\" />\n";
    echo "<param name=\"movie\" value=\"./paper/player_mp3_maxi.swf\" />\n";
    echo "<param name=\"FlashVars\" value=\"mp3={$configObject->get('cfg_root_path')}/paper/sound_demo.mp3&amp;showstop=1&amp;showvolume=1&amp;bgcolor1=ffa50b&amp;bgcolor2=d07600\" />\n";
    echo "</object></td></tr>\n";
  }

  echo '<tr><td style="text-align:center" colspan="4"><br />';
  if ($test_type == 2) echo "<div style=\"color:#C00000;font-size:90%\">" . $string['donotstart'] . "</div>\n";
  echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"" . $string['help'] . "\" name=\"help\" onclick=\"launchHelp(31);\" onkeypress=\"launchHelp(31);\" />\n";
  if ($test_type == 2) {
    $paper_utils = Paper_utils::get_instance();
    $paper_display = array();
    $paper_no = $paper_utils->get_active_papers($paper_display, array('1', '2'), $userObject, $mysqli, $property_id);
    if ($paper_no > 0) echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"" . $string['switchpapers'] . "\" name=\"switch\" onclick=\"window.location='../index.php'\" />&nbsp;&nbsp;&nbsp;&nbsp;\n";
  }

  $display_date = '';

  if ($test_type == 0) {
    $log_info = $mysqli->prepare("SELECT l.screen, SUM(l.mark) AS mark, DATE_FORMAT(lm.started,\"%Y%m%d%H%i%s\") AS started, 0 AS paper_type, DATE_FORMAT(lm.started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.paperID = ? AND lm.userID = ? GROUP BY started DESC, l.screen UNION SELECT l.screen, SUM(l.mark) AS mark, DATE_FORMAT(lm.started,\"%Y%m%d%H%i%s\") AS started, 1 AS paper_type, DATE_FORMAT(lm.started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.paperID = ? AND lm.userID = ? GROUP BY started DESC, l.screen");
    $log_info->bind_param('iiii', $property_id, $userObject->get_user_ID(), $property_id, $userObject->get_user_ID());
  } else {
    $log_info = $mysqli->prepare("SELECT MAX(l.screen) AS screen, SUM(l.mark) AS mark, DATE_FORMAT(lm.started,\"%Y%m%d%H%i%s\") AS started, ? AS paper_type, DATE_FORMAT(lm.started,\"%d/%m/%Y %H:%i\") AS temp_date FROM log$test_type l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.paperID = ? AND lm.userID = ? GROUP BY started DESC");
    $log_info->bind_param('iii', $test_type, $property_id, $userObject->get_user_ID());
  }
  $log_info->execute();
  $log_info->bind_result($log_max_screen, $log_mark, $log_started, $log_paper_type, $log_temp_date);
  $log_info->store_result();
  if ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
    echo "<input type=\"button\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"" . $string['start'] . "\" name=\"start\" id=\"start\" onclick=\"startPaper();\" onkeypress=\"startPaper();\" />\n";
    if (time() < $paper_start or time() > $paper_end) {
      echo '<div style="font-size:90%;color:#C00000"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;' . $string['papernotavailablestudents'] . '</div>';
    }
  } else {
    $hide_restart = true;

    if (($navigation == 1 and time() < $paper_end) or ($navigation == 0 and $paper_screens > $log_max_screen)) {
      $hide_restart = false;
    }

    // Has the student run out of time or clicked the 'Finish' button?
    $no_time_left = ($display_remaining_time === true and (int)$remaining_time === 0);

    if ($no_time_left) {
      $hide_restart = true;
    }

    if ($hide_restart == true or $metadata_security == false) {
      $disabled = "<input type=\"button\" id=\"start\" style=\"width:" . $button_width . "px\" value=\"" . $string['start'] . "\" name=\"start\" id=\"start\" disabled />\n";
      echo $disabled;
    } elseif ($test_type > 0 and $log_info->num_rows > 0) {
      echo "<input type=\"button\" id=\"start\" style=\"width:" . $button_width . "px; font-weight:bold\" onclick=\"startPaper();\" value=\"" . $string['restart'] . "\" name=\"restart\" id=\"start\" />";
    } elseif ($test_type != 2 and (time() < $paper_start or time() > $paper_end)) {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"" . $string['start'] . "\" name=\"start\" disabled />\n";
      echo '<br /><div class="w"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;' . $string['papernotavailable'] . '</div>';
    } elseif ($test_type == 2 and (time()+(15*60)) < $paper_start or time() > $paper_end) {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px\" value=\"" . $string['start'] . "\" name=\"start\" disabled />\n";
      echo '<br /><div class="w"><img src="./artwork/small_warning_16.png" width="16" height="16" alt="!" />&nbsp;' . $string['papernotavailable'] . '</div>';
    } else {
      echo "<input type=\"button\" style=\"width:" . $button_width . "px; font-weight:bold\" value=\"" . $string['start'] . "\" name=\"start\" id=\"start\" onclick=\"startPaper();\" onkeypress=\"startPaper();\" />\n";
    }
  }
  echo '<br />&nbsp;';

  if ($test_type != 2) {
    // Display previous attempts
    $old_started = '';
    $old_screen = 0;
    $temp_no = 0;
    $mark_total = 0;
    $adj_percent = 0;
    if ($log_info->num_rows > 0) {
      while ($log_info->fetch()) {
        if ($temp_no == 0) {
          $old_started = $log_started;
          echo '<hr />';
          echo '<table cellpadding="0" cellspacing="0" border="0" align="center">';
          echo '<tr><td colspan="4" style="text-align:center"><strong>' . $string['previouscompletions'] . '</strong></td></tr>';
          if ($log_max_screen > $old_screen) $old_screen = $log_max_screen;
        }
        if ($old_started != $log_started and $old_started != '') {
          $old_screen = 0;
          if ($test_type == 0) {
            displayPrevTake($mark_total, $adj_percent, $total_random_mark, $marking, $display_date, $paper_type);
          } else {
            if ($low_bandwidth == 0) {
              echo "<tr><td><img src=\"./artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;&nbsp;<span style=\"color:#808080\">$display_date</span></td><td>&nbsp;</td></tr>\n";
            } else {
              echo "<tr><td><span style=\"color:#808080\">$display_date</span></td><td>&nbsp;</td></tr>\n";
            }
          }
          $mark_total = 0;
        }
        $old_started = $log_started;
        $temp_no++;
        if ($log_max_screen > $old_screen) $old_screen++;
        $mark_total += $log_mark;
        $display_date = $log_temp_date;
        $rerun_date = $log_started;
        $paper_type = $log_paper_type;
      }
      $log_info->close();

      if ($test_type == 0) {
        displayPrevTake($mark_total, $adj_percent, $total_random_mark, $marking, $display_date, $paper_type);
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
      echo '<hr />' . $string['nottakenpaper'] . '</p><br />';
    }
  }
  $mysqli->close();
  ?><div class="powered"><i>powered by</i> Rog&#333; <?php echo $configObject->get('rogo_version'); ?></div></td></tr></table>

</form>
</body>
</html>
