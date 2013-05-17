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
* Handles paper display and the recording of marks to the 'logX' tables. Uses functions within 'display_functions.inc' to process specific
* types of questions. Start.php continues calling itself while there are further screens to be displayed and then calls 'finish.php'
* to end.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
require_once '../include/staff_student_auth.inc';
require_once '../include/paper_security.inc';
require_once '../include/display_functions.inc';
require_once '../include/media.inc';
require_once '../include/errors.inc';
require_once '../classes/paperutils.class.php';
require_once '../classes/timer.class.php';
require_once '../classes/log_extra_time.class.php';
require_once '../classes/log_lab_end_time.class.php';
require_once '../classes/summativetimer.class.php';
require_once '../classes/logmetadata.class.php';
require_once '../classes/paperproperties.class.php';

$userObject = UserObject::get_instance();

check_var('id', 'GET', true, false, false);

function randomQOverwrite($random_q_data, $user_answers, &$screen_data, &$used_questions, $db, $string) {
  $selected_q_id = '';
  $current_screen = $random_q_data['screen'];
  $q_no = $random_q_data['no_on_screen'];

  if (isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }

  if ($selected_q_id == '') {
    // Generate a random question ID.
    $random_q_no = count($random_q_data['options']);
    $try = 0;
    $unique = false;
    while ($unique == false and $try < 9999) {
      $selected_no = rand(0, $random_q_no-1);
      $selected_q_id = $random_q_data['options'][$selected_no]['option_text'];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  } else {
    $unique = true;
  }

  $question['assigned_number'] = $random_q_data['assigned_number'];
  $question['no_on_screen'] = $question['display_pos'] = $q_no;
  $question['screen'] = $random_q_data['screen'];

  if ($unique) {
    // Look up selected question and overwrite data.
    $question_data = $db->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    while ($question_data->fetch()) {
      if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
        $question['theme'] = $theme;
        $question['scenario'] = $scenario;
        $question['leadin'] = $leadin;
        $question['notes'] = $notes;
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = '';
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    }

    // Overwrite the screen data.
    $screen_no = count($screen_data);
    for ($i=1; $i<=$screen_no; $i++) {
      if (isset($screen_data[$i])) {
        $q_no = count($screen_data[$i]);
      } else {
        $q_no = 0;
      }
      for ($a=0; $a<$q_no; $a++) {
        if ($screen_data[$i][$a][1] == $random_q_data['q_id']) {
          $screen_data[$i][$a][0] = $q_type;
          $screen_data[$i][$a][1] = $q_id;
        }
      }
    }
  } else {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_random'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'] = array();
  }

  return $question;
}

function keywordQOverwrite($random_q_data, $user_answers, &$screen_data, $used_questions, $db, $string) {
  $selected_q_id = '';
  $unique = true;
  $current_screen = $random_q_data['screen'];
  $q_no = $random_q_data['no_on_screen'];

  if (isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }

  if ($selected_q_id == '') {
    // Generate a random question ID from keywords.
    $question_ids = array();
    $question_data = $db->prepare("SELECT DISTINCT q_id FROM keywords_question WHERE keywordID = ?");
    $question_data->bind_param('i', $random_q_data['options'][0]['option_text']);
    $question_data->execute();
    $question_data->bind_result($q_id);
    while ($question_data->fetch()) {
      $question_ids[] = $q_id;
    }
    $question_data->close();
    shuffle($question_ids);

    $try = 0;
    $unique = false;
    while ($unique == false and $try < count($question_ids)) {
      $selected_q_id = $question_ids[$try];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  }

  if ($unique) {
    // Look up selected question and overwrite the question data.
    $question_data = $db->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id = ? AND questions.q_id = options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    while ($question_data->fetch()) {
      if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
        $question['assigned_number'] = $random_q_data['assigned_number'];
        $question['no_on_screen'] = $q_no;
        $question['screen'] = $random_q_data['screen'];
        $question['theme'] = $theme;
        $question['scenario'] = $scenario;
        $question['leadin'] = $leadin;
        $question['notes'] = $notes;
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['display_pos'] = $q_no;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = '';
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    }
    $question_data->close();

    // Overwrite the screen data.
    $screen_no = count($screen_data);
    for ($i=1; $i<=$screen_no; $i++) {
      if (isset($screen_data[$i])) {
        $q_no = count($screen_data[$i]);
      } else {
        $q_no = 0;
      }
      for ($a=0; $a<$q_no; $a++) {
        if ($screen_data[$i][$a][1] == $random_q_data['q_id']) {
          $screen_data[$i][$a][0] = $q_type;
          $screen_data[$i][$a][1] = $q_id;
        }
      }
    }
  } else {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_keywords'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['display_pos'] = $q_no;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'] = array();
  }

  return $question;
}

if (isset($_POST['sessionid'])) require '../include/marking_functions.inc';

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$paperID = $propertyObj->get_property_id();

/*
 *
 * Setup som feature related flags
 *
 */
//are we in a staff test and preview mode?
$is_preview_mode = ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and isset($_REQUEST['mode']) and $_REQUEST['mode'] == 'preview');
// Are we on the first screen
$is_first_launch = !isset($_POST['current_screen']);
//are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ($is_preview_mode == true and isset($_GET['mode']) and $_GET['mode'] == 'preview');
//are we in a staff single question testmode
$is_question_preview_mode = (isset($_GET['q_id']));

// Get how many screens make up the question paper.
$screen_data = array();
if ($is_question_preview_mode) {
  $stmt = $mysqli->prepare("SELECT 1, q_type, question
                            FROM
                              (papers, questions)
                            WHERE
                              papers.paper = ? AND
                              papers.question=questions.q_id AND
                              questions.q_id=?
                              ORDER BY
                                screen
                            ");
  $stmt->bind_param('ii', $paperID, $_GET['q_id']);
} else {
  $stmt = $mysqli->prepare("SELECT
                              screen, q_type, question
                            FROM
                              (papers, questions)
                            WHERE
                              papers.paper = ? AND
                              papers.question=questions.q_id
                            ORDER BY
                              screen, display_pos");
  $stmt->bind_param('i', $paperID);
}
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($screen, $q_type, $q_id);

while ($stmt->fetch()) {
  $no_screens = $screen;
  if ($q_type != 'info') {
    $screen_data[$no_screens][] = array($q_type, $q_id);
  }
}
$stmt->free_result();
$stmt->close();

//store the original paper type - needed to retrieve answers from the correct log and functionality related decisions
$original_paper_type = $propertyObj->get_paper_type();

// Is this a type of paper that allows only one attempt?
$do_restart = ($is_first_launch and ($original_paper_type == 1 or $original_paper_type == 2));

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color);


$attempt = 1;                 //default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;           //default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = NULL;             //default overwritten by (check_labs)
$lab_id = NULL;
$current_ip_address = NULL;   //default overwritten by (check_labs)


$current_ip_address = NetworkUtils::get_ipaddress();

//get the module Ids for this paper
$modIDs = array_keys(Paper_utils::get_modules($paperID, $mysqli));

if ($userObject->has_role('Student')) {

  // Check for additional password on the paper
  check_paper_password($propertyObj->get_password(), $string);

  // Check time security
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date());

  //Check room security
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(),
                                $propertyObj->get_labs(),
                                $current_ip_address,
                                $propertyObj->get_password(),
                                $string,
                                $mysqli
                              );

  // check modules if the user is a student and the paper is not formative
  $attempt = check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $mysqli);

  // Check for any metadata security restrictions
  check_metadata($paperID, $userObject, $modIDs, $string, $mysqli);
}

//get lab info used in log metadata
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_ip($current_ip_address)){
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}

/*
* Set the default state
*/
$log_metadata = null;
$sessionid = false;
$current_screen = 1;
$is_fire_alarm = ( isset($_POST['fire_alarm']) and $_POST['fire_alarm'] == '1' );
$summative_exam_session_started = false; //lab timing stated by invigilators
$allow_timing = false;

/*
* Extract the posted variables.
*/
if (isset($_POST['sessionid'])) {
  if ($_POST['button_pressed'] == 'next') {
    $current_screen = $_POST['current_screen'];
  } elseif ($_POST['button_pressed'] == 'prevous') {
    $current_screen = $_POST['current_screen'] - 2;
  } elseif ($_POST['button_pressed'] == 'jump_screen') {
    $current_screen = $_POST['jump_screen'];
  } elseif ($_POST['fire_alarm'] == 1) {
    $current_screen = $_POST['current_screen'];
  }
}

//lookup previous sessionid from log_metadata.started property_id
$log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);

if ($is_preview_mode_first_launch == true or ($is_first_launch and !$do_restart)) {

  //in preview mode or for non-restartable papers always start a new session if we have relaunched the window
  $log_metadata->create_new_record($current_ip_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);

} elseif ($log_metadata->get_record() == false) { //load the data and check for no records
  //we have no log_metadata record so make one
  $log_metadata->create_new_record($current_ip_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);
}

$sessionid = $log_metadata->get_session_id();
$metadataid = $log_metadata->get_metadata_id();

// Only allow timing if ALL the modules of the paper allow
$allow_timing = module_utils::modules_allow_timing($modIDs, $mysqli);

/*
* BP Determine the student's end_date timestamp for a summative exam that has been 'Started'.
* This is also used further down to make sure that the timer does not close the window if the exam session hasn't been 'started' by an invigilator
* If a summative exam session has been started  then record late answers in log_late
*/
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2' and !$is_question_preview_mode) {
  //has this lab had an end time set?
  $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

//check for submissions after the end date and set them to save in log_late if we are not in preview_mode or a summative exam session as not been started
if ($is_preview_mode === false and time() > $propertyObj->get_end_date() and ($propertyObj->get_paper_type() == '1' or ($propertyObj->get_paper_type() == '2' and $paper_scheduled and $summative_exam_session_started === false))) {
  $propertyObj->set_paper_type('_late');
}

/*
* Save any posted answers
*
* N.B if Ajax saving is enabled: After a successful Ajax save the form is posted as the user moves to the next screen
*                                with dont_record set to true so this is not executed
*/
if ($is_question_preview_mode == false) {
  if ((isset($_POST['old_screen']) and $_POST['old_screen'] != '') and (!isset($_GET['dont_record']) or $_GET['dont_record'] != true)) {
    record_marks($paperID, $mysqli, $userObject->get_user_ID(), $propertyObj->get_paper_type(), $grade, $year, $attempt, $userroles, $metadataid);
  }
}

/*
* Load up any previously submitted user answers from the appropriate log table(s)
*
* N.B If the user has gone passed the end of the exam (possible in some cases if security is relaxed)
*     records could exist in 2 logs the original paper type log and log_late
*
*/
$user_answers = array();
$previous_duration = 0;
$screen_pre_submitted = 0;
if ($sessionid !== false or $is_fire_alarm == true) {
  // Get users previous answers from the log.
  if ($propertyObj->get_paper_type() == '_late') {
    //if we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below
    $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$original_paper_type WHERE metadataID = ?");
    $log_data->bind_param('i', $metadataid);
    $log_data->execute();
    $log_data->store_result();
    $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
    $user_answers = array();
    $used_questions[$log_q_id] = $log_q_id;
    while ($log_data->fetch()) {
      $user_answers[$log_screen][$log_q_id] = $log_user_answer;
      $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
      $user_order[$log_screen][$log_q_id] = $option_order;
      // Bump up the current screen if restarting
      if ($do_restart and $log_screen > $current_screen) {
        $current_screen = $log_screen;
      }
      if ($log_screen == $current_screen) {
        $previous_duration = $log_duration;
        $screen_pre_submitted = 1;
      }
    }
    $log_data->close();
  }
  //get user answers from whichever log is pointed to by log$paper_type
  $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log" . $propertyObj->get_paper_type() . " WHERE metadataID = ? ORDER BY id");
  $log_data->bind_param('i', $metadataid);
  $log_data->execute();
  $log_data->store_result();
  $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
  if ($log_data->num_rows > 0) {
    while ($log_data->fetch()) {
      $user_answers[$log_screen][$log_q_id] = $log_user_answer;
      $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
      $user_order[$log_screen][$log_q_id] = $option_order;
      $used_questions[$log_q_id] = $log_q_id;
      // Bump up the current screen if restarting
      if ($do_restart and $log_screen > $current_screen) {
        $current_screen = $log_screen;
      }
      if ($log_screen == $current_screen) {
        $previous_duration = $log_duration;
        $screen_pre_submitted = 1;
      }
    }
  }
  $log_data->close();
}

/*
*
* Get any Reference Material
*
*/
$reference_materials = array();
$ref_no = 0;
$max_ref_width = 0;
$stmt = $mysqli->prepare("SELECT title, content, width FROM (reference_material, reference_papers) WHERE reference_material.id = reference_papers.refID AND paperID = ?");
$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->bind_result($reference_title, $reference_material, $reference_width);
while ($stmt->fetch()) {
  $reference_materials[$ref_no]['title'] = $reference_title;
  $reference_materials[$ref_no]['material'] = $reference_material;
  $reference_materials[$ref_no]['width'] = $reference_width;
  if ($reference_width > $max_ref_width) {
    $max_ref_width = $reference_width;
  }
  $ref_no++;
}
$stmt->close();

require '../config/start.inc';
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html>\n<head>\n";

$url_mod = ($is_question_preview_mode) ? '&q_id=' . $_GET['q_id'] : '';
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="pragma" content="no-cache" />
<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<?php
if ($propertyObj->get_paper_type() == '3') {
  echo "<title>" . $string['survey'] . "</title>\n";
} else {
  echo "<title>" . $string['assessment'] . "</title>\n";
}

$css = '';
if ($userObject->is_special_needs() and $bgcolor != '#FFFFFF') {
  $css .= "select,input{background-color:$bgcolor;color:$fgcolor;font-family:$font,sans-serif}\n";
}
if (($bgcolor != '#FFFFFF' and $bgcolor != 'white') or ($fgcolor != '#000000' and $fgcolor != 'black') or $textsize != 90) {
  $css .= "body {background-color:$bgcolor;color:$fgcolor;font-size:$textsize%}\n";
}
if ($font != 'Arial') {
  if (strpos($font,' ') === false) {
    $css .= "body {font-family:$font,sans-serif}\n";
    $css .= "pre {font-family:$font,sans-serif}\n";
  } else {
    $css .= "body {font-family:'$font',sans-serif}\n";
    $css .= "pre {font-family:'$font',sans-serif}\n";
  }
}
if ($themecolor != '#316AC5') {
  $css .= ".theme {color:$themecolor}\n";
}
if ($marks_color != '#808080') {
  $css .= ".mk {color:$marks_color}\n";
}
if ($fgcolor != '#000000' and $fgcolor != 'black') {
  $css .= ".act {color:$fgcolor}\n";
}
if ($unanswered_color != '#FFC0C0') {
  $css .= ".unans {background-color:$unanswered_color}\n";
  $css .= ".scr_un {background-color:$unanswered_color}\n";
}
if (count($reference_materials) > 0) {
  $css .= "#maincontent {position:fixed; right:" . ($max_ref_width + 1) . "px}\n";
  $css .= ".framecontent {width:" . ($max_ref_width - 12) . "px}\n";
  $css .= ".refhead {width:" . ($max_ref_width - 12) . "px;}\n";
}
if ($css != '') {
  echo "<style type=\"text/css\">\n$css\n</style>\n";
}
?>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<?php if ($propertyObj->get_latex_needed() == 1) {?>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<?php }?>
<script type="text/javascript" src="../js/flash_include.js"></script>
<script type="text/javascript" src="../js/jquery.flash_q.js"></script>
<script language="javascript">
  window.history.go(1);
<?php
  if (count($reference_materials) > 0) {
    echo "\$(document).ready(function() {\n";
    if (isset($_POST['refpane'])) {
      echo "  changeRef(" . $_POST['refpane'] . ");\n";
    } else {
      echo "  resizeReference();\n";
    }
    echo "$(window).resize(resizeReference);";
    echo "});\n";
  }
?>
  var lang = {
  <?php
  $langstrings = array('msgselectable1', 'msgselectable2', 'msgselectable3', 'msgselectable4');
  $first = true;
  foreach ($langstrings as $langstring) {
    if (!$first) {
      echo ',';
    }
    echo "'{$langstring}':'{$string[$langstring]}'";
    $first = false;
  }
  ?>
  };

  var getWinH = function() {
    var winH = 460;
    if (document.body && document.body.offsetWidth) {
      winH = document.body.offsetHeight;
    }
    if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
      winH = document.documentElement.offsetHeight;
    }
    if (window.innerWidth && window.innerHeight) {
      winH = window.innerHeight;
    }
    return winH;
  }

  var changeRef = function(refID) {
    document.getElementById('refpane').value = refID;
    winH = getWinH();
    resizeReference();
    var flag = 0;
    <?php
      if (count($reference_materials) > 0) {
        echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
        echo "      if (i == refID) {\n";
        echo "        document.getElementById('framecontent' + i).style.display = 'block';\n";
        echo "        document.getElementById('refhead' + i).style.top = (31 * i) + 'px';\n";
        echo "        flag = 1;\n";
        echo "      } else {\n";
        echo "        document.getElementById('framecontent' + i).style.display = 'none';\n";
        echo "        if (flag == 0) {\n";
        echo "          document.getElementById('refhead' + i).style.top = (31 * i) + 'px';\n";
        echo "        } else {\n";
        echo "          document.getElementById('refhead' + i).style.top = (winH - (" . count($reference_materials) . " - i) * 31) + 'px';\n";
        echo "        }\n";
        echo "      }\n";
        echo "    }\n";
      }
    ?>
  }

  var resizeReference = function() {
    winH = getWinH();
<?php
  if (count($reference_materials) > 0) {
    $subtract = (31 * count($reference_materials)) + 11;
    echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
    echo "      document.getElementById('framecontent' + i).style.height = (winH - $subtract) + 'px';\n";
    echo "    }\n";
?>
    var mainWidth = $('body').outerWidth() - $('#framecontent0').outerWidth(true);
    $('#maincontent').width(mainWidth);
<?php
  }
?>
  }

  var submitted = false;
<?php
  if ($is_question_preview_mode === true) {
?>
  var confirmSubmit = function() {
    return true;
  }
<?php
  } elseif ($propertyObj->get_bidirectional() == 0) {
?>
  var confirmSubmit = function() {
    if (submitted == true) {
      return false;
    }
    var agree = confirm("<?php echo $string['javacheck1']; ?>");
    if (agree) {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    } else {
      document.body.style.cursor = '';
      return false;
    }
  }
<?php
  } else {
?>
  var confirmSubmit = function() {
	  if (submitted == true) {
      return false;
    }
    if (document.questions.button_pressed.value == 'finish') {
      var agree = confirm("<?php echo $string['javacheck2']; ?>");
      if (agree) {
        document.body.style.cursor = 'wait';
        submitted = true;
        return true;
      } else {
        $('#savemsg').html("");
        document.body.style.cursor = 'default';
        return false;
      }
    } else {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    }
  }
  var jumpScreen = function () {
      document.questions.button_pressed.value='jump_screen';
      $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id']; ?>&dont_record=true");
      return userSubmit(null);
  }
<?php
  }
?>

  <?php //Bind save function to the screen for fault tolerant form saving ?>
  var usingAjax = false;
  var submitType = '';
  var autoSaveRef = '';
  var last_saved_user_awnsers = null;    <?php //holds the data of the last successful auto save ?>
  $(document).ready(function () {
      <?php  //we have javascript replace the form submit buttons to enable ajax saving ?>
      usingAjax = true;
      $('#next').replaceWith('<?php echo "<input id=\"next\" type=\"button\" value=\"" . $string['screen'] . " " . ($current_screen + 1) . " &gt;\" />&nbsp;";?>');
      $('#next').click(userSubmit);

      $('#prevous').replaceWith('<?php echo "<input id=\"prevous\" type=\"button\" value=\"&nbsp;&lt; " . $string['screen'] . " " . ($current_screen - 1) . "&nbsp;\" />&nbsp;";?>');
      $('#prevous').click(userSubmit);

      $('#finish').replaceWith('<?php echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />&nbsp;";?>');
      $('#finish').click(userSubmit);

      <?php //attach ui events ?>
      $('.rankselect').change(rankCheck);
      $(".calc-answer").keydown(filterKeypress);

       <?php //setup autosave ?>
      startAutoSave();
  });

  <?php //normal user submit by clicking on next, prevous, finish or jump screen ?>
  var userSubmit = function (event) {
    submitType = 'userSubmit';
    stopAutoSave();
    if (!!event) {
      $('#button_pressed').attr('value',event.target.id);
    }
    if (confirmSubmit()) {
      $('#saveError').fadeOut('slow');
      $('#savemsg').html("<img src=\"../artwork/busy.gif\" width=\"20\" height=\"20\" alt=\"Wait\" />")

      <?php //log which method the users submitted the page via ?>
      if (!!event) {
        if(event.target.id == 'finish') {
          $('#qForm').attr('action',"finish.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");  
        } else {
          $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");
        }
      }
      ajaxSave();
    }
  }

  <?php  //called when a user has run out of time by UpdateTimerWithRemainingTime in start.js ?>
  var forceSave = function() {
    stopAutoSave();
    submitType = 'forcedSubmit';
    $('#qForm').attr('action',"finish.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");
    ajaxSave();
  }

  <?php  //called on auto save time out ?>
  var autoSave = function() {
    submitType = 'autoSave';
    
    <?php //this could take longer than the autosave timeout stop auto save to stop duplicate events ?>
    stopAutoSave();
    
    <?php //save any data from wysiwyg  ?>
    if(typeof(tinyMCE) != "undefined"){
      tinyMCE.triggerSave();
    }
    var formData = $('#qForm').serialize();

    <?php //only auto save if the data has changed ?>
    if(last_saved_user_awnsers !== formData) {
      $('#savemsg').html("<?php echo $string['auto_saving']; ?>")
      ajaxSave();
    } else {
      <?php //rereister the autosave timer ?>
      startAutoSave();
    }
  }

  var startAutoSave = function () {
    clearTimeout(autoSaveRef);<?php //Cancel any outstanding timeouts to make sure only one auto save is ever registered?>
    autoSaveRef = setTimeout("autoSave()",<?php echo (($configObject->get('cfg_autosave_frequency') + rand(-5,5)) * 1000); ?>);
  }

  var stopAutoSave = function() {
    clearTimeout(autoSaveRef);
  }

  var ajaxSave = function () {
    <?php //hide any errors ?>
    $('#saveError').fadeOut('fast');
    <?php //random page ID to stop IE caching results. arrrggg ?>
    date = new Date();
    randomPageID = date.getTime();
    $('#randomPageID').val(randomPageID);
    if(typeof(tinyMCE) != "undefined"){
      tinyMCE.triggerSave();
    }
    $.ajax({
          url: 'save_screen.php?id=<?php echo $_GET['id'] . $url_mod; ?>&rnd=' + randomPageID + '<?php echo html_entity_decode($url_mod) ?>',
          type: 'post',
          data: $('#qForm').serialize(),
          dataType: 'html',
          timeout: <?php 
                        //set the time out of one requst to be the maximum total time plus 5s for network latency
                        //php handles nomal timeouts. This is just to make sure the user wont wait forever if somthing
                        //weird happens
                        echo ceil((($configObject->get('cfg_autosave_retrylimit') * $configObject->get('cfg_autosave_backoff_factor') * $configObject->get('cfg_autosave_settimeout')) + $configObject->get('cfg_autosave_settimeout') + 5)) * 1000; 
                   ?>,
          cache: false,
          tryCount : 0,
          retryLimit : <?php echo $configObject->get('cfg_autosave_retrylimit'); //try 3 times before erroring ?>, 
          beforeSend: function() {
          },
          fail: function() {
            if(this.retry()) {
              return;
            } else  {
              saveFail();
              return;
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            if (textStatus == 'timeout' ) {
              <?php
              //we have timed out either  the server has gone away or somthing went wrong in the network
              //get the user to retry
              ?>
              saveFail();
              return;
            } else if (textStatus == 'error') {
              if(this.retry()) {
                return;
              } else  {
                saveFail();
                return;
              }
            }
            saveFail();
            return;
          },
          success: function (ret_data, jqXHR, textStatus) {
              if (ret_data == randomPageID) {
                  <?php //cache the form data to look for changes on next auto save ?>
                  last_saved_user_awnsers = this.data;
                  saveSuccess();
                  return;
              }
              if(this.retry()) {
                return;
              } else  {
                saveFail();
                return;
              }
          },
          retry: function (){
            <?php //retry if we can ?>
            this.tryCount++;
            if (this.tryCount <= this.retryLimit) {
              <?php //indicate the retry on the url ?>
              if(this.tryCount == 1) {
                this.url = this.url + "&retry=" + this.tryCount;
              } else {
                this.url = this.url.replace("&retry=" + (this.tryCount - 1), "&retry=" + this.tryCount);
              }
              $.ajax(this);
              return true;
            }
            return false
          }
      });
    return;
  }

  var saveSuccess = function () {
    <?php //rereister the autosave timer ?>
    startAutoSave();
    if (submitType == 'userSubmit') {
      $('#qForm').submit();
      return true;
    } else if(submitType == 'forcedSubmit') {
      $('#qForm').submit();
    } else {
      $('#savemsg').html("<?php echo $string['auto_ok']; ?>");
      <?php //clear auto save message ?>
      setTimeout("$('#savemsg').html(\"\")",5000);
    }
  }

  var saveFail = function () {
    <?php //rereister the autosave timer ?>
    startAutoSave();
      
    $('#saveError').fadeIn('fast');
    $('#savemsg').html("");
    document.body.style.cursor = 'default';
    submitted = false;
    
    return false;
  }

  var fire = function (scrno) {
    submitType = 'userSubmit';
    document.questions.button_pressed.value='fire_exit';
    if (usingAjax) {
        document.questions.action="fire_evacuation.php?id=<?php echo $_GET['id']; ?>&dont_record=true";
    } else {
        document.questions.action="fire_evacuation.php?id=<?php echo $_GET['id']; ?>";
    }
    ajaxSave();
  }
</script>
<script type="text/javascript" src="../js/start.js"></script>
</head>
<?php

  /*
  *
  * Build the paper structure
  *
  */
  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';
  if ($is_question_preview_mode) {
    $question_data = $mysqli->prepare("SELECT
                                          1,
                                          q_type,
                                          q_id,
                                          score_method,
                                          display_method,
                                          marks_correct,
                                          marks_incorrect,
                                          marks_partial,
                                          theme,
                                          scenario,
                                          leadin,
                                          correct,
                                          REPLACE(option_text,'\t','') AS option_text,
                                          q_media,
                                          q_media_width,
                                          q_media_height,
                                          o_media,
                                          o_media_width,
                                          o_media_height,
                                          notes,
                                          display_pos,
                                          q_option_order
                                      FROM
                                          papers, questions, options
                                      WHERE
                                        paper=? AND
                                        q_id=? AND
                                        papers.question=questions.q_id AND
                                        questions.q_id = options.o_id
                                      ORDER BY
                                      display_pos,
                                      id_num");
    $question_data->bind_param('ii', $paperID, $_GET['q_id']);
  } else {
    $question_data = $mysqli->prepare("SELECT
                                            screen,
                                            q_type,
                                            q_id,
                                            score_method,
                                            display_method,
                                            marks_correct,
                                            marks_incorrect,
                                            marks_partial,
                                            theme,
                                            scenario,
                                            leadin,
                                            correct,
                                            REPLACE(option_text,'\t','') AS option_text,
                                            q_media,
                                            q_media_width,
                                            q_media_height,
                                            o_media,
                                            o_media_width,
                                            o_media_height,
                                            notes,
                                            display_pos,
                                            q_option_order
                                        FROM
                                            papers, questions, options
                                        WHERE
                                          paper=? AND
                                          papers.question=questions.q_id AND
                                          questions.q_id = options.o_id
                                        ORDER BY
                                        display_pos,
                                        id_num");
    $tmp_pid = $paperID;
    $question_data->bind_param('i', $tmp_pid);
  }
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;

  $q_no = 0;
  $assigned_number = 0;
  $no_on_screen = 0;
  $old_screen = 0;
  //build the questions_array
  $tmp_questions_array = array();
  while ($question_data->fetch()) {
    if ($q_no == 0 or $tmp_questions_array[$q_no]['q_id'] != $q_id or $tmp_questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      if ($screen != $old_screen) {
        $no_on_screen = 0;
      }
      if ($q_type != 'info') {
        $assigned_number++;
        $no_on_screen++;
      }
      $tmp_questions_array[$q_no]['assigned_number'] = $assigned_number;
      $tmp_questions_array[$q_no]['no_on_screen'] = $no_on_screen;
      $tmp_questions_array[$q_no]['screen'] = $screen;
      $tmp_questions_array[$q_no]['theme'] = trim($theme);
      $tmp_questions_array[$q_no]['scenario'] = trim($scenario);
      $tmp_questions_array[$q_no]['leadin'] = trim($leadin);
      $tmp_questions_array[$q_no]['notes'] = trim($notes);
      $tmp_questions_array[$q_no]['q_type'] = $q_type;
      $tmp_questions_array[$q_no]['q_id'] = $q_id;
      $tmp_questions_array[$q_no]['display_pos'] = $display_pos;
      $tmp_questions_array[$q_no]['score_method'] = $score_method;
      $tmp_questions_array[$q_no]['display_method'] = $display_method;
      $tmp_questions_array[$q_no]['q_media'] = $q_media;
      $tmp_questions_array[$q_no]['q_media_width'] = $q_media_width;
      $tmp_questions_array[$q_no]['q_media_height'] = $q_media_height;
      $tmp_questions_array[$q_no]['q_option_order'] = $q_option_order;
      $tmp_questions_array[$q_no]['dismiss'] = '';
      $used_questions[$q_id] = 1;
    }
    $tmp_questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    $old_screen = $screen;
  }
  $question_data->close();

  //look for random questions and overwrite as needed
  $questions_array = array();
  $hidden_html = '';
  foreach ($tmp_questions_array as $question) {
    if ($question['q_type'] == 'random') {
      $question = randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $mysqli, $string);
      if ($current_screen == $question['screen']) {
        $hidden_html .= "\n<input type=\"hidden\" name=\"q" . $question['no_on_screen'] . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
      }
    } elseif ($question['q_type'] == 'keyword_based') {
      $question = keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $mysqli, $string);
      if ($current_screen == $question['screen'] and $question['q_id'] != -1) {
        $hidden_html .= "\n<input type=\"hidden\" name=\"q" . $question['no_on_screen'] . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
      }
    }
    $questions_array[] = $question;
  }
  unset($tmp_questions_array);

  $unanswered = false;

  $incomplete_screens = get_unanswered_screens($no_screens, $screen_data, $user_answers, $questions_array, $paperID, $mysqli);

  // BP If the duration is set then show timer

  $method = 'StartClock()';
  $timer_label = '';

  $special_needs_percentage = $userObject->get_special_needs_percentage();
  if ($allow_timing and $propertyObj->get_exam_duration() != null) {
    // Summative type. Time is only active in live.
    if (($propertyObj->get_paper_type() == '2' or $original_paper_type == 2) and $is_preview_mode === false) {

      //has the student been allotted extra time by an invigilator
      $student_object['user_ID'] = $userObject->get_user_ID();
      $student_object['special_needs_percentage'] = $special_needs_percentage;
      $log_extra_time = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);

      // Do not time the exam if the invigilator has not clicked on the 'Start' button
      if ($summative_exam_session_started !== false) {
        $summative_timer    = new SummativeTimer($log_extra_time);
        $remaining_time     = $summative_timer->calculate_remaining_time_secs();
        $method             = 'StartTimer(' . $remaining_time . ', true)';
        $timer_label        = $string['timeremaining'] . ':';
      }

    } else {

      $timer          = new Timer($log_metadata, $propertyObj->get_exam_duration(), $special_needs_percentage);
      $start_datetime = $timer->get_start_datetime();

      if ($start_datetime === false) {
        $timer->start();
      }

      $remaining_time = $timer->calculate_remaining_time();
      $method         = 'StartTimer(' . $remaining_time . ', true)';
      $timer_label    = $string['timeremaining'] . ':';
    }
  }

  if ($userObject->has_role('Student')) {
    echo '<body oncontextmenu="return false;"onload="' . $method . ';" onclose="KillClock();">';
  } else {
    echo '<body onload="' . $method . ';" onunload="KillClock();">';
  }

  echo "<div id=\"maincontent\">\n";

  if ($current_screen < $no_screens) {
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . $url_mod . "\">";
  } else {
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'] . $url_mod . "\">";
  }
  echo $hidden_html;
  ?>
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<?php
  if (!$is_question_preview_mode) {
    echo "<tr><td valign=\"top\">\n";
    echo $top_table_html;
    echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div>';
    $question_offset = 0;
    if ($no_screens > 1) {
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          echo '<div class="scr_cur"';
        } else {
          if ($incomplete_screens[$i] == 1) {
            echo '<div class="scr_un"';
          } else {
            echo '<div class="scr_ans"';
          }
        }
        $no_questions = 0;
        if (isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            $no_questions++;
          }
        }
        if ($no_questions == 1) {
          echo ' title="' . $no_questions . ' question">';
        } else {
          echo ' title="' . $no_questions . ' questions">';
        }

        if ($i < $current_screen and isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            if ($screen_question[0] != 'info' ) {
              $question_offset++;
            }
          }
        }
        echo "$i</div>\n";
      }
      echo "<div style=\"clear:both\"></div>\n";


      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          echo '<div class="scr_arrow"></div>';
        } else {
          echo '<div class="scr_spacer">&nbsp;</div>';
        }
      }

    }
    echo '</td>';
    echo $logo_html;
  } else {
    echo '<tr><td>';
  }

  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  //display the questions
  $calculator = $propertyObj->get_calculator(); //GLABAL NEEDS FIXING
  foreach($questions_array as &$question) {
    if ($question['screen'] == $current_screen) {
      if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr style=\"display:none\" id=\"unansweredkey\"><td colspan=\"2\"><span class=\"unans\">&nbsp;&nbsp;&nbsp;&nbsp;</span> " . $string['unansweredquestion'] . "</td></tr>\n";
      if ($q_displayed == 0 and $current_screen == 1 and $propertyObj->get_paper_prologue() != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $propertyObj->get_paper_prologue() . '</td></tr>';
      if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      display_question($question, $propertyObj->get_paper_type(), $current_screen, $previous_q_type, $question_no, $user_answers, $unanswered);
      $previous_q_type = $question['q_type'];
      $q_displayed++;
    }
  }

  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"sessionid\" value=\"$sessionid\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"\" />\n";
  echo "<input type=\"hidden\" id=\"randomPageID\" name=\"randomPageID\" value=\"\" />\n";
  if (isset($_REQUEST['mode']) and $_REQUEST['mode'] == 'preview') {
    echo "<input type=\"hidden\" id=\"mode\" name=\"mode\" value=\"preview\" />\n";
  } else {
    if ($current_screen > $no_screens) {
      echo "<br />\n<div class=\"note\" style=\"text-align:center;font-size:90%\">";
      if (isset($low_bandwidth) and $low_bandwidth == 0) echo '<img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;';
      if (!isset($_GET['q_id'])) {
        echo $string['finishnote'];
        if ($propertyObj->get_bidirectional() == 1) echo "<br />" . $string['gobackpink'];
      }
      echo "</div>\n<br />\n";
    } elseif ($propertyObj->get_bidirectional() == 0) {
      echo "<br />\n<div class=\"note\" style=\"text-align:center;font-size:90%\">";
      if (isset($low_bandwidth) and $low_bandwidth == 0) echo '<img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;';
      printf($string['pleasecomplete'], $current_screen);
      echo "</div>\n<br >\n";
    }
  }

  echo '<div id="saveError"><img alt="Warning" src="/artwork/no_save.png" /> <div><strong>' .  $string['savefailed'] . '</strong><br />' . $string['tryagain'] . '</div></div>';

  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff')) and $is_question_preview_mode) {
    echo "&nbsp;&nbsp;<input id=\"finish\" type=\"submit\" name=\"next\" onclick=\"document.questions.button_pressed.value='finish';\" value=\"" . $string['finish'] . "\" />\n";
    echo "<input type=\"hidden\" name=\"refpane\" id=\"refpane\" value=\"" . ($ref_no - 1) . "\" />\n";
  } else {
    echo $bottom_html;
    ?>
    <span>
    <?php
    if ($propertyObj->get_exam_duration() != null) {
      echo $timer_label;
    }

    ?>
    <span id="theTime" type="text" class="thetime"></span>
    </span>
    <?php
    echo '</td><td align="right">';

    echo '<span id="savemsg"></span>';
    if ($propertyObj->get_bidirectional() == 1 and $no_screens > 1) {
      if ($current_screen > 2) echo "<input input id=\"prevous\" type=\"submit\" name=\"prev\" onclick=\"document.questions.button_pressed.value='previous';\" value=\"&nbsp;&lt; " . $string['screen'] . " " . ($current_screen - 2) . "&nbsp;\" />&nbsp;";
      if ($original_paper_type == '0' or $original_paper_type == '1' or $original_paper_type == '2') {
        echo "<select name=\"jump_screen\" onchange=\"jumpScreen()\">";
        for ($i=1; $i<=$no_screens; $i++) {
          if ($i == ($current_screen - 1)) {
            echo "<option value=\"$i\" selected>$i</option>";
          } else {
            echo "<option value=\"$i\">$i</option>";
          }
        }
        echo "</select>&nbsp;";
      }
    }
    echo "<input type=\"hidden\" name=\"refpane\" id=\"refpane\" value=\"" . ($ref_no - 1) . "\" />\n";
    if ($current_screen > $no_screens) {
      echo "<input id=\"finish\" type=\"submit\" name=\"next\" onclick=\"document.questions.button_pressed.value='finish';\" value=\"" . $string['finish'] . "\" />&nbsp;\n";
    } else {
      echo "<input id=\"next\" type=\"submit\" name=\"next\" value=\"" . $string['screen'] . " $current_screen &gt;\" />&nbsp;\n";
    }
    echo '</td></tr></table>';
  }
?>
</td></tr></table>
</form>
</div>
<?php

if (count($reference_materials) > 0) {
  $top = 0;
  $ref_no = 0;
  foreach ($reference_materials as $reference_material) {
    echo "<div class=\"refhead\" id=\"refhead" . $ref_no . "\" onclick=\"changeRef(" . $ref_no . ")\" style=\"top:{$top}px\">" . $reference_material['title'] . "</div>\n";
    echo "<div class=\"framecontent\" id=\"framecontent" . $ref_no . "\" style=\"top:" . (31 + $top) . "px\">\n" . $reference_material['material'] . "</div>\n";
    $top+=31;
    $ref_no++;
  }
}
$mysqli->close();

if (isset($_POST['refpane'])) {
  echo "<script language=\"JavaScript\">\n";
  echo "  changeRef(" . $_POST['refpane'] . ");\n";
  echo "</script>\n";
}

if ($unanswered) {
  echo "<script language=\"JavaScript\">\n";
  echo "  document.getElementById('unansweredkey').style.display = '';\n";
  echo "</script>\n";
}
?>

</body>
</html>









