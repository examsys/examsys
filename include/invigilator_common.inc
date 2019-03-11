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

function get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $db) {
  $paperID = $property_object->get_property_id();

  $configObject = Config::get_instance();

  //create a caching LogExtraTime gets all the results in one hit
  $log_extra_time = new LogExtraTime($log_lab_end_time, array(), $db, true);

  // Get any student notes.
  $notes_array = PaperNotes::get_all_notes_by_paper($paperID, $db);
  
  // Get any student toilet breaks.
  $toilet_break_array = ToiletBreaks::get_all_breaks_by_paper($paperID, $db);
  ?>

  <table style="font-size:100%; line-height:150%" cellpadding="2" cellspacing="0" border="0" width="100%">
    <thead>
    <tr>
      <th>
      </th>

      <th>
        <?php echo $string['title'] ?>
      </th>

      <th>
        <?php echo $string['surname'] ?>
      </th>

      <th>
        <?php echo $string['forenames'] ?>
      </th>

      <th>
        <?php echo $string['endtime'] ?>
      </th>

      <th>
        <?php echo $string['extension_mins'] ?>
      </th>

      <th>
      </th>
    </tr>
    </thead>
    <tbody>
  <?php
    // Get guest account details.
    $guest_accounts = array();
    $sql = "SELECT assigned_account, title, first_names, surname FROM temp_users";
    $results = $db->prepare($sql);
    $results->execute();
    $results->bind_result($assigned_account, $title, $first_names, $surname);
    while($results->fetch()) {
      $guest_accounts[$assigned_account] = array('title'=>$title, 'first_names'=>$first_names, 'surname'=>$surname);
    }
    $results->close();
    
    // Get all students who should are able to access this paper
    $sql = "SELECT DISTINCT extra_time, medical, breaks, modules_student.userID, surname, first_names, title FROM modules_student, users LEFT JOIN special_needs ON users.id = special_needs.userID WHERE idMod IN ( " . $modules . ") AND calendar_year = ? AND modules_student.userID = users.id";
    $results = $db->prepare($sql);
    $session = $property_object->get_calendar_year();
    $results->bind_param('s', $session);
    $results->execute();
    $results->store_result();
    $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
    $student_object = array();

    while ($results->fetch()) {
      $student_object[$userID]['user_ID'] = $userID;
      $student_object[$userID]['surname'] = $surname;
      $student_object[$userID]['first_names'] = $first_names;
      $student_object[$userID]['title'] = $title;
      $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
      $student_object[$userID]['medical'] = $medical;
      $student_object[$userID]['breaks'] = $breaks;
    }
    $results->close();

    // Merge in all students who have submitted records for this paper
    $sql = 'SELECT DISTINCT sn.extra_time, sn.medical, sn.breaks, lm.userID, u.surname, u.first_names, u.title FROM log_metadata lm INNER JOIN users u ON lm.userID = u.id LEFT JOIN special_needs sn ON u.id = sn.userID WHERE lm.paperID = ? AND u.username LIKE "user%"';
    $results = $db->prepare($sql);
    $results->bind_param('i', $paperID);
    $results->execute();
    $results->store_result();
    $results->bind_result($extra_time_percentage, $medical, $breaks, $userID, $surname, $first_names, $title);
    while ($results->fetch()) {
      if ($first_names == 'Temporary Account') {
        $username = strtolower($surname);
        
        $student_object[$userID]['user_ID'] = $userID;
        $student_object[$userID]['surname'] = $guest_accounts[$username]['surname'];
        $student_object[$userID]['first_names'] = $guest_accounts[$username]['first_names'];
        $student_object[$userID]['title'] = $guest_accounts[$username]['title'];
        $student_object[$userID]['extra_time_percentage'] = 0;
        $student_object[$userID]['medical'] = '';
        $student_object[$userID]['breaks'] = '';
      } else {
        $student_object[$userID]['user_ID'] = $userID;
        $student_object[$userID]['surname'] = $surname;
        $student_object[$userID]['first_names'] = $first_names;
        $student_object[$userID]['title'] = $title;
        $student_object[$userID]['extra_time_percentage'] = $extra_time_percentage;
        $student_object[$userID]['medical'] = $medical;
        $student_object[$userID]['breaks'] = $breaks;
      }
    }
    $results->close();

    $column = 'surname';
    $sort_order = 'asc';
    $student_object = \sort::array_csort($student_object, $column, $sort_order);

    foreach( $student_object as $student_id => $student_obj) {
      process_student_list($log_lab_end_time, $log_extra_time, $student_obj, $property_object, $configObject, $notes_array, $toilet_break_array, $allow_timing, $string, $db);
    }

    ?>
    </tbody>
  </table>
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
function process_student_list($log_lab_end_time, $log_extra_time, $student_object, $property_object, $configObject, $notes_array, $toilet_break_array, $allow_timing, $string, $mysqli) {

  // Determine when the current exam session will end

  $lab_session_end_datetime = $log_lab_end_time->get_session_end_date_datetime();

  if ($lab_session_end_datetime == false) {
    $lab_session_end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
  }

  $exam_duration_mins = $property_object->get_exam_duration();

  $class = 'student';

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
    $class .= ' redwarn';
  }

  ?>
<tr class="<?php echo $class; ?>" id="l<?php echo $paperID ?>_<?php echo $tmp_userID ?>" onclick="popMenu(<?php echo $tmp_userID ?>, <?php echo $paperID ?>, <?php echo $allow_timing ? 'true' : 'false'; ?>, event);">
  <td>
<?php
  if (isset($notes_array[$tmp_userID]) and $notes_array[$tmp_userID] == 'y') {
    echo '<img src="../artwork/notes_icon.gif" class="16icon" alt="Note" />';
  }
  if (isset($toilet_break_array[$tmp_userID])) {
    for ($i=0; $i<count($toilet_break_array[$tmp_userID]); $i++) {
      echo '<img src="../artwork/wc.png" class="16icon" alt="Toilet" />';
    }
  }
?>
  </td>
  
  <td><?php echo $title ?></td>
  <td><?php echo $surname ?></td>
  <td><?php echo $first_names ?></td>
  <td><?php echo $formatted_end_time ?></td>


  <td>
    <?php
    if ($special_needs_extra_time_mins != '') {
      ?>
        <span style=""><?php echo $special_needs_extra_time_mins ?></span>
      <?php
    }
    if ($special_needs_extra_time_mins != '' and $extra_time_mins != '') {
      echo ' + ';
    }
    if ($extra_time_mins != '') {
      ?>
        <img src="../artwork/clock_16.png" class="small_icon" alt="<?php echo $string['extratime'] . '\\'; ?>" />
        <span style=""><?php echo $extra_time_mins ?></span>
      <?php
    }
    ?>
  </td>
  <td><?php
    $cellID = $paperID . '_' . $tmp_userID;
    if ($student_object['medical'] != '' or $student_object['breaks'] != '') {
      echo "<div id=\"p$cellID\">";
    }
    if ($student_object['medical'] != '') {
      echo '<img src="../artwork/medical_16.gif" class="16icon" alt="Medical" onmouseover="showCallout(\'' . $cellID . '\', \'' . addslashes($student_object['medical']) . '\')" onmouseout="hideCallout()" />';
    }
    if ($student_object['breaks'] != '') {
      echo '<img src="../artwork/moon_16.gif" class="16icon" alt="Breaks" onmouseover="showCallout(\'' . $cellID . '\', \'' . addslashes($student_object['breaks']) . '\')" onmouseout="hideCallout()" />';
    }
    if ($student_object['medical'] != '' or $student_object['breaks'] != '') {
      echo "</div>\n";
    }
    ?></td>
</tr>
<?php
}

?>
