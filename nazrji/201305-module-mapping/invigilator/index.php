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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
require_once '../include/invigilator_auth.inc';
require_once '../classes/usernotices.class.php';
require_once '../include/errors.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/log_lab_end_time.class.php';
require_once '../classes/lab_factory.class.php';
require_once '../classes/lab.class.php';
require_once '../classes/log_extra_time.class.php';
require_once '../classes/logmetadata.class.php';

function get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $db) {
  $paperID = $property_object->get_property_id();

  $configObject = Config::get_instance();

  //create a caching LogExtraTime gets all the results in one hit
  $log_extra_time = new LogExtraTime($log_lab_end_time, array(), $db, true);

  // Get any student notes;
  $notes_array = array();
  $notes_results = $db->prepare("SELECT note_id, userID FROM student_notes WHERE paper_id=?");
  $notes_results->bind_param('i', $paperID);
  $notes_results->execute();
  $notes_results->store_result();
  $notes_results->bind_result($note_id, $tmp_userID);

  while ($notes_results->fetch()) {
    $notes_array[$tmp_userID] = true;
  }

  $notes_results->close();

  ?>

<div class="cohortlist">
    <table style="font-size:100%" cellpadding="2" cellspacing="0" border="0" width="100%">
        <tr>
            <th>
              <?php echo $string['name']; ?>
            </th>

            <th width="200px">
              <?php echo $string['endtime']; ?>
            </th>

            <th>
              <?php echo $string['extension_mins']; ?>
            </th>

        </tr>

      <?php

      //Get all students who should are able to access this paper
      $sql = "SELECT DISTINCT extra_time, modules_student.userID, surname, first_names, title FROM modules_student, users LEFT JOIN special_needs ON users.id = special_needs.userID WHERE idMod IN ( " . $modules . ") AND calendar_year = ? AND modules_student.userID = users.id ORDER BY surname, initials";
      $results = $db->prepare($sql);
      $session = $property_object->get_calendar_year();
      $results->bind_param('s', $session);
      $results->execute();
      $results->store_result();
      $results->bind_result($extra_time_percentage, $student_id, $surname, $first_names, $title);
      $student_object = array();

      while ($results->fetch()) {
        $student_object[$student_id]['user_ID'] = $student_id;
        $student_object[$student_id]['surname'] = $surname;
        $student_object[$student_id]['first_names'] = $first_names;
        $student_object[$student_id]['title'] = $title;
        $student_object[$student_id]['extra_time_percentage'] = $extra_time_percentage;
      }
      $results->close();

      //merge in all students who have submitted records for this paper
      $sql = 'SELECT DISTINCT sn.extra_time, lm.userID, u.surname, u.first_names, u.title FROM log_metadata lm INNER JOIN users u ON lm.userID = u.id LEFT JOIN special_needs sn ON u.id = sn.userID WHERE lm.paperID = ? AND u.username LIKE "user%" ORDER BY u.surname, u.initials';
      $results = $db->prepare($sql);
      $results->bind_param('i', $paperID);
      $results->execute();
      $results->store_result();
      $results->bind_result($extra_time_percentage, $student_id, $surname, $first_names, $title);
      while ($results->fetch()) {
        $student_object[$student_id]['user_ID'] = $student_id;
        $student_object[$student_id]['surname'] = $surname;
        $student_object[$student_id]['first_names'] = $first_names;
        $student_object[$student_id]['title'] = $title;
        $student_object[$student_id]['extra_time_percentage'] = $extra_time_percentage;
      }
      $results->close();

      foreach( $student_object as $student_id => $student_obj) {
          process_student_list($log_lab_end_time, $log_extra_time, $student_obj, $property_object, $configObject, $notes_array, $allow_timing, $string, $db);
      }

      ?>
    </table>
</div>
<?php
}

/*
* @param LogLabEndTime  $log_lab_end_time
* @param UserObject     $student_object
* @param PropertyObject $property_object
* @param Config         $configObject
* @param int            $extra_time_percentage
* @param array          $notes_array
* @param string         $string
* @param mysqli         $mysqli
 */
function process_student_list($log_lab_end_time, $log_extra_time, $student_object, $property_object, $configObject, $notes_array, $allow_timing, $string, $mysqli) {

  // Determine when the current exam session will end

  $lab_session_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

  if ($lab_session_end_datetime == false) {
    $lab_session_end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
  }

  $exam_duration_mins = $property_object->get_exam_duration();

  $class = '';

  if ($exam_duration_mins == NULL) {
    throw new ErrorException('Exam duration is mandatory in summative exams');
  }

  if (is_int($exam_duration_mins) === false) {
    throw new ErrorException('$exam_duration_mins ' . $exam_duration_mins . ' must be an integer');
  }

  $exam_duration_interval = new DateInterval('PT' . $exam_duration_mins . 'M');
  $lab_session_start_datetime = clone $lab_session_end_datetime;
  $lab_session_start_datetime->sub($exam_duration_interval);

  // Determine when the student's exam session will end

  //set userID log_extra_time as we are in cached mode
  $log_extra_time->set_student_object($student_object);

  $student_end_datetime = $lab_session_end_datetime;

  // Calculate whether student's extended 'end time' is before the current session's start time
  // Currently unused but could be altered to exit if student's extra end time is before session's start time

  $is_student_end_before_session_start = $student_end_datetime < $lab_session_start_datetime;

  // Highlight student's who have gone over time

  $current_datetime = new DateTime();

  // Calculate extra time

  $extra_time_secs = $log_extra_time->get_extra_time_secs();
  $extra_time_mins = round($extra_time_secs / 60);

  $special_needs_extra_time_mins = ($exam_duration_mins / 100) * $student_object['extra_time_percentage'];
  $special_needs_extra_time_secs = (int)($special_needs_extra_time_mins * 60);
  $total_extra_time = $extra_time_secs + $special_needs_extra_time_secs;

  $total_extra_time_interval = new DateInterval('PT' . $total_extra_time . 'S');

  $student_end_datetime = $student_end_datetime->add($total_extra_time_interval);

  $paper_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

  $ft = clone $student_end_datetime;
  $ft->setTimezone(new DateTimeZone($property_object->get_timezone()));
  $formatted_end_time = $ft->format($configObject->get('cfg_short_time_php'));

  if ($extra_time_secs > 0 or $special_needs_extra_time_secs > 0) {
    $formatted_end_time = '<strong>' . $formatted_end_time . '</strong>';
  }

  // Get student description
  $tmp_userID = $student_object['user_ID'];
  $surname = $student_object['surname'];
  $first_names = $student_object['first_names'];
  $title = $student_object['title'];

  $paperID = $property_object->get_property_id();

  $has_student_exceeded_end = ($student_end_datetime < $current_datetime);

  if ($has_student_exceeded_end) {
    $class = 'redwarn';
  }

  ?>
<tr class="<?php echo $class; ?>">
    <td style="cursor:hand" onclick="popMenu('<?php echo $tmp_userID ?>', '<?php echo $paperID ?>', <?php echo $allow_timing ? 'true' : 'false'; ?>, event);"/>
  <?php
  if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == true) {
    ?>
      <img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" border="0"/>
    <?php
  }
  ?>
    <?php echo $surname ?><span style="color:#808080">, <?php echo $first_names . ' ' . $title; ?></span>
    </td>

    <td style="text-align:center">
      <?php echo $formatted_end_time; ?>
    </td>


    <td style="text-align:center">
      <?php
      if ($special_needs_extra_time_mins != '') {
        ?>
          <img src="../artwork/accessibility_16.png" width="16" height="16"
               alt="<?php echo $string['extratime'] . '\\'; ?>" border="0"/>
          <span style=""><?php echo $special_needs_extra_time_mins ?></span>
        <?php
      }
      if ($special_needs_extra_time_mins != '' and $extra_time_mins != '') {
        echo ' + ';
      }
      if ($extra_time_mins != '') {
        ?>
          <img src="../artwork/clock_16.png" width="16" height="16" alt="<?php echo $string['extratime'] . '\\'; ?>"
               border="0"/>
          <span style=""><?php echo $extra_time_mins; ?></span>
        <?php
      }
      ?>
    </td>
</tr>
<?php
}


function emergencyNumbers($support_numbers) {
  global $string;

  echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; margin-left:10px\">\n";
  echo "<tr><td colspan=\"3\" style=\"border-bottom: 1px solid #C0C0C0; font-weight:bold\">" . $string['emergencynumbers'] . "</td></tr>\n";
  foreach ($support_numbers as $number => $contact) {
    echo "<tr><td><img src=\"../artwork/call_icon.png\" width=\"53\" height=\"25\" alt=\"call\" border=\"0\" /></td><td>$number</td><td>$contact</td></tr>\n";
  }
  echo "</table>\n";
}

function get_timestamp_from_time($hours, $minutes, $timezone) {
  $tmp_datetime = new DateTime(date('Y-m-d') . $hours . ':' . $minutes . ':00', $timezone);
  return $tmp_datetime->getTimestamp();
}


if (isset($_POST['start_exam_form'])) {
  check_var('paper_id', 'POST', true, false, false);
}

$current_ip_address = NetworkUtils::get_ipaddress();

$lab = new LabFactory($mysqli);

$lab_object = $lab->get_lab_based_on_ip($current_ip_address);
$lab_id = $lab_object->get_id();
$room_name = $lab_object->get_name();

$properties_list = array();

$properties_list = PaperProperties::get_paper_properties_by_lab($lab_object, $mysqli);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

<title>Rogo: <?php echo $string['invigilatoraccess']; ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css"/>
<link rel="stylesheet" type="text/css" href="../css/header.css"/>
<link rel="stylesheet" type="text/css" href="../css/warnings.css"/>
<style type="text/css">
    body {
        color: #000040
    }
    fieldset {
      border: none;
      padding: 0;
    }
    .exam-button {
      width: 100%;
      height: 34px;
      font-size: 120%;
      font-weight: bold;
      /*border: none;*/
      border-top: 2px solid #eee;
      border-left: 2px solid #eee;
      border-bottom: 2px solid #999;
      border-right: 2px solid #999;
      cursor: pointer;
    }
    #start_exam_button {
      background-color: #26A61E;
      color: #fff;
    }
    #end_exam_button {
      background-color: #ff8901;
      color: #fff;
      margin-bottom: 6px;
    }
    .cohortlist {
        border: 1px solid #95AEC8
    }
    #menudiv ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    #menudiv li {
      padding: 3px 0 3px 32px;
      border: 1px solid #fff;
    }
    #menudiv li:hover {
      background-color: #FFE7A2;
      border: 1px solid #FFBD69;
    }
    .menu-time {
      background: #fff url('../artwork/clock_16.png') no-repeat 6px 4px;
    }
    .menu-note {
      background: #fff url('../artwork/notes_icon.gif') no-repeat 6px 4px;
    }
    .tight {
      margin: 4px 0 2px 0;
    }
    .notice {
      border: 1px solid #C00000;
      padding: 0 6px;
      margin-bottom: 6px;
    }
    .notice h1 {
      font-size: 140%;
    }
</style>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript">

  var ie = document.all;
  var ns6 = document.getElementById && !document.all;
  var isMenu = false;
  var menuSelObj = null;
  var overpopupmenu = false;

  function mouseSelect(e) {
      var obj = ns6 ? e.target.parentNode : event.srcElement.parentElement;
      if (isMenu) {
          if (overpopupmenu == false) {
              isMenu = false;
              overpopupmenu = false;
              document.getElementById('menudiv').style.display = 'none';
              return true;
          }
          return true;
      }
  }

  function popMenu(tmpUserID, paperID, showExtension, e) {
    if (!e) var e = window.event;
    var currentX = e.clientX;
    var currentY = e.clientY;
    var scrOfX = getScrollX();
    var scrOfY = getScrollY();

    $('#userID').val(tmpUserID);
    $('#paperID').val(paperID);

    top_pos = currentY + scrOfY;

    if (top_pos > ($(window).height() + scrOfY - 130)) {
        top_pos = $(window).height() + scrOfY - 130;
    }

    if (showExtension) {
      $('.menu-time').show();
    } else {
      $('.menu-time').hide();
    }

    $('#menudiv').css('left', currentX + scrOfX);
    $('#menudiv').css('top', top_pos);

    $('#menudiv').show();

    isMenu = true;
    return false;
  }

  function getScrollX() {
    var scrollOfX = 0;
    if (typeof( window.pageYOffset ) == 'number') {
        //Netscape compliant
        scrollOfX = window.pageXOffset;
    } else if (document.body && ( document.body.scrollLeft || document.body.scrollTop )) {
        //DOM compliant
        scrollOfX = document.body.scrollLeft;
    } else if (document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop )) {
        //IE6 standards compliant mode
        scrollOfX = document.documentElement.scrollLeft;
    }
    return scrollOfX;
  }

  function getScrollY() {
    var scrollOfY = 0;
    if (typeof( window.pageYOffset ) == 'number') {
        //Netscape compliant
        scrollOfY = window.pageYOffset;
    } else if (document.body && ( document.body.scrollLeft || document.body.scrollTop )) {
        //DOM compliant
        scrollOfY = document.body.scrollTop;
    } else if (document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop )) {
        //IE6 standards compliant mode
        scrollOfY = document.documentElement.scrollTop;
    }
    return scrollOfY;
  }


  // please keep these lines on when you copy the source
  // made by: Nicolas - http://www.javascript-page.com
  var clockID = 0;
  function UpdateClock() {
    if (clockID) {
      clearTimeout(clockID);
      clockID = 0;
    }
    var tDate = new Date();
    document.getElementById('theTime').value = "<?php echo $string['currenttime']; ?> " + ((tDate.getHours() < 10) ? "0" : "") + tDate.getHours() +
            ((tDate.getMinutes() < 10) ? ":0" : ":") + tDate.getMinutes() +
            ((tDate.getSeconds() < 10) ? ":0" : ":") + tDate.getSeconds();
    clockID = setTimeout("UpdateClock()", 1000);
  }

  function StartClock() {
    clockID = setTimeout("UpdateClock()", 500);
  }

  function KillClock() {
    if (clockID) {
      clearTimeout(clockID);
      clockID = 0;
    }
  }

  function newStudentNote() {
    var paperID = document.getElementById('paperID').value;
    var userID = document.getElementById('userID').value;

    studentnote = window.open("new_student_note.php?userID=" + userID + "&paperID=" + paperID + "", "studentnote", "width=650,height=430,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

    if (window.focus) {
      studentnote.focus();
    }
  }



  function newPaperNote(paperID) {
    papernote = window.open("new_paper_note.php?paperID=" + paperID + "","papernote","width=650,height=410,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      papernote.focus();
    }
  }

  function extendTime() {
    var paperID = document.getElementById('paperID').value;
    var userID = document.getElementById('userID').value;

    papernote = window.open("extend_time.php?paperID=" + paperID + "&userID=" + userID, "extendtime", "width=250,height=150,left=" + (screen.width / 2 - 300) + ",top=" + (screen.height / 2 - 200) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

    if (window.focus) {
      papernote.focus();
    }
  }

  function resizeLists() {
    var myHeight = 0;
    if (typeof( window.innerWidth ) == 'number') {
      //Non-IE
      myHeight = window.innerHeight;
    } else if (document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight )) {
      //IE 6+ in 'standards compliant mode'
      myHeight = document.documentElement.clientHeight;
    } else if (document.body && ( document.body.clientWidth || document.body.clientHeight )) {
      //IE 4 compatible
      myHeight = document.body.clientHeight;
    }
    myHeight = myHeight - 280;

    var mysheet = document.styleSheets[0];
    var totalrules = mysheet.cssRules ? mysheet.cssRules.length : mysheet.rules.length
    if (mysheet.deleteRule) { //if Firefox
      mysheet.insertRule(".cohortlist {height:" + myHeight + "px; overflow:auto}", totalrules);
    } else if (mysheet.removeRule) { //else if IE
      document.styleSheets[0].addRule(".cohortlist", "height:" + myHeight + "px; overflow:auto");
    }
  }

  document.onmousedown = mouseSelect;

  // Register the events we need
  $(function () {
    $('.menu-time').click(extendTime);
    $('.menu-note').click(newStudentNote);
    StartClock();
    resizeLists();
    $(window).unload(KillClock);
    $(window).resize(resizeLists);
  });
</script>

</head>

<body>

<?php
$popup_width = 180;
if ($language != 'en') {
  $popup_width = 300;
}
?>

<div id="menudiv"
     style="width:<?php echo $popup_width; ?>px; background-color:white; padding:1px; font-size:80%; position:absolute; display:none; top:0px; left:0px; z-index:10000; border:1px solid #868686; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; box-shadow:2px 2px 2px rgba(100, 100, 100, 0.50)"
     onmouseover="javascript:overpopupmenu=true;" onmouseout="javascript:overpopupmenu=false;">
    <table cellspacing="2" cellpadding="0" border="0" style="font-size:100%; background-color:white; width:100%">
        <tr>
            <td>
                <ul>
                  <li class="menu-time"><?php echo $string['extendtime']; ?></li>
                  <li class="menu-note"><?php echo $string['addnote']; ?></li>
                </ul>
            </td>
        </tr>
    </table>
</div>

<table class="header">
    <tr>
        <th>
            <div style="padding-left:10px; font-size:24pt; font-weight:bold">
              <?php
              if ($lab_object->get_name() == '') {
                echo NetworkUtils::get_ipaddress() . $string['unknownlab'];
              } else {
                echo $string['lab'] . ' ' . $lab_object->get_name();
              }
              ?>
            </div>
            <div style="padding-left:10px; font-size:10pt; font-weight:bold"><?php echo $string['invigilatoraccess']; ?></div>
        </th>
        <th style="text-align:right">
            <input type="text"
                   style="background-color:transparent; text-align:right; font-size:180%; border:0px; font-weight:bold"
                   id="theTime"/>
          <?php
          // Only display this if there is the one exam
          if (count($properties_list) < 2) {
            ?>
              <input type="text"
                     style="background-color:transparent; text-align:right; font-size:180%; border:0px; font-weight:bold"
                     id="theEndTime"/>
            <?php
          }
          ?>

            &nbsp;
        </th>
    </tr>
    <tr>
        <th colspan="2" class="bevel"></th>
    </tr>
</table>
<br/>
<?php

if ($properties_list !== false and count($properties_list) > 0) {

  $col_width = round(100 / (count($properties_list) + 1));
  ?>
    <table cellpadding="2" cellspacing="0" border="0" style="font-size:95%">
    <tr>
    <?php

  foreach ($properties_list as $property_object) {

    $title = $property_object->get_paper_title();
    $property_id = $property_object->get_property_id();
    $exam_duration = $property_object->get_exam_duration();
    $start_date = $property_object->get_display_start_date();
    $calendar_year = $property_object->get_calendar_year();

    // Get modules for this paper and check if timing is allowed
    $timed_modules = $all_modules = 0;
    $sql = 'SELECT m.id, m.timed_exams FROM properties_modules pm INNER JOIN modules m ON pm.idMod = m.id WHERE pm.property_id = ?';

    $module_results = $mysqli->prepare($sql);
    $module_results->bind_param('i', $property_id);
    $module_results->execute();
    $module_results->store_result();
    $module_results->bind_result($moduleID, $timed_exams);

    $modules = array();

    while ($module_results->fetch()) {
      $modules[] = $moduleID;
      $all_modules++;
      if ($timed_exams == true) {
        $timed_modules++;
      }
    }

    $allow_timing = ($timed_modules == $all_modules);

    $exam_started = false;

    // Has 'Start' button been submitted

    $log_lab_end_time = new LogLabEndTime($lab_object->get_id(), $property_object, $mysqli);

    $end_datetime = $log_lab_end_time->get_session_end_date_datetime();

    if ($end_datetime == false) {
      $end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
    } else {
      $exam_started = true;
      $started_timestamp = $log_lab_end_time->get_started_timestamp();
      $start_date = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php'), $started_timestamp);
    }

    $disptimezone = new DateTimeZone($property_object->get_timezone());

    if ($allow_timing and isset($_POST['start_exam_form'])) {

      $paper_id = (int)$_POST['paper_id'];

      // Does the submitted paperID correspond it to the currently iterated paper?

      if ($paper_id == (int)$property_id) {
        $invigilator_id = $userObject->get_user_ID();
        $end_datetime = $log_lab_end_time->save($invigilator_id);
        $exam_started = true;
        $start_date = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php'));
      }
    }
?>
      <td style="vertical-align:top; width:<?php echo $col_width; ?>%">
<?php
    if ($allow_timing and isset($_POST['end_exam_form'])) {

      $paper_id = (int)$_POST['paper_id'];

      // Does the submitted paperID correspond it to the currently iterated paper?

      if ($paper_id == (int)$property_id) {
        $end_timestamp = get_timestamp_from_time($_POST['hour'], $_POST['minute'], $disptimezone);
        $exam_duration_s = $exam_duration * 60;

        if (($end_timestamp - $started_timestamp) > $exam_duration_s) {
          // End time is past start time + duration so is OK
          $invigilator_id = $userObject->get_user_ID();
          $time = 'PT' . $_POST['hour'] . 'H' . $_POST['minute'] . 'M';
          $end_datetime = $log_lab_end_time->save($invigilator_id, $time);
        } else {
          $notice = UserNotices::get_instance();
          $notice->display_notice($string['timeerror'], sprintf($string['timeerrormsg'], $exam_duration), '../artwork/red_warning_48.png', '#C00000', false, false);
        }
      }
    }

    $end_datetime->setTimezone($disptimezone);

    $end_date = $end_datetime->format($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php'));
    $end_time = $end_datetime->format($configObject->get('cfg_long_time_php'));

    $end_time_h = $end_datetime->format('H');
    $end_time_m = $end_datetime->format('i');

    if (count($properties_list) < 2) {
      ?>

        <script language="JavaScript" type="text/javascript">
            document.getElementById('theEndTime').value = "<?php echo $string['end'] ?> <?php echo $end_time; ?>";
        </script>

      <?php
    }

    ?>
          <div style="display:inline">
              <img src="../artwork/summative.png" align="left" width="48" height="48" alt="paper icon" border="0"/>
          </div>

          <div style="margin-left:52px; display:block">
              <strong><?php echo $title ?></strong>
              <table style="width: 100%">

                  <tr>
                      <td>
                        <?php echo $string['start'] ?>:
                      </td>
                      <td>
                        <?php echo $start_date ?>
                      </td>
                      <td><strong><?php if ($allow_timing) echo $string['timedexam']; ?></strong></td>
                  </tr>

                  <tr>
                      <td>
                        <?php echo $string['end']; ?>:
                      </td>
                      <td>
                        <?php echo $end_date;   ?>
                      </td>
                      <td rowspan="2" style="width: 45%; vertical-align: top">
                        <?php
                        if ($allow_timing) {
                        ?>
                        <form id="start_exam_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                          <input name="paper_id" type="hidden" value="<?php echo $property_id; ?>"/>
                          <?php
                            if ($exam_started) {
                          ?>
                            <fieldset id="set_end">
                              <input id="end_exam_button" name="end_exam_form" type="submit" value="<?php echo $string['endat_but'] ?>" class="exam-button" /><br />
                              <?php echo $string['time'] ?>:<select id="hour" name="hour">
                                  <?php for($hr=0; $hr<24; $hr++) { $selected = ''; if($hr == $end_time_h) { $selected = 'selected'; } echo '<option value="' . $hr . '"' . $selected . '>' . str_pad($hr, 2, '0', STR_PAD_LEFT) . '</option>'; } ?>
                              </select>
                              :
                              <select id="minute" name="minute">
                                <?php for($hr=0; $hr<60; $hr++) { $selected = ''; if($hr == $end_time_m) { $selected = 'selected'; } echo '<option value="' . $hr . '"' . $selected . '>' . str_pad($hr, 2, '0', STR_PAD_LEFT) . '</option>'; } ?>
                              </select>
                            </fieldset>
                          <?php                          
                            } else {
                          ?>
                            <fieldset id="start_exam">
                              <input id="start_exam_button" name="start_exam_form" type="submit" value="<?php echo $string['start_but'] ?>" class="exam-button" />
                            </fieldset>
                          <?php
                            }
                          ?>
                        </form>
                        <?php
                        }
                        ?>
                      </td>
                  </tr>
                  <tr>
                      <td>
                        <?php echo $string['duration']; ?>:
                      </td>
                      <td>
                        <?php echo $exam_duration . '  ' . $string['mins']; ?>&nbsp;&nbsp;&nbsp;
                      </td>
                  </tr>

              </table>

              <p class="tight"><a href="" onclick="newPaperNote(<?php echo $property_id; ?>); return false;" style="color:blue">
                <?php echo $string['papernote']; ?>
              </a></p>

            <?php

            $password = $property_object->get_password();

            if ($password != '') {
              ?>
                <br/>Password: <?php echo $password; ?>
              <?php
            }
            ?>
          </div>
          <hr style="border:0px; height:1px" noshade="noshade" size="1"/>


        <?php
        $modules = implode('\',\'', $modules);

        $modules = '\'' . $modules . '\'';

        get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $mysqli);
        ?>
      </td>
    <?php
  }

  ?>

    <td style="vertical-align:top; width:<?php echo $col_width; ?>%">
    <?php
  echo sprintf($string['checklist'], '../lang/' . $language . '/invigilator/');
  ?>
    <br/>
  <?php
  emergencyNumbers($configObject->get('emergency_support_numbers'));
  echo "</td></tr>\n</table>\n";
} else {
  echo "<p style=\"font-weight:bold; color:#C00000\">&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" />&nbsp;" . $string['nopapersfound'] . "</p>";
  emergencyNumbers($configObject->get('emergency_support_numbers'));
}

$mysqli->close();
?>
    <input type="hidden" id="userID" value=""/>
    <input type="hidden" id="paperID" value=""/>
</body>
</html>
