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
* Handles paper display and the recording of marks to the 'logX' tables.
* Start.php continues calling itself while there are further screens to be displayed and then calls 'finish.php'
* to end.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
require_once '../include/staff_student_auth.inc';
require_once '../include/paper_security.php';
require_once '../include/display_functions.inc';
require_once '../include/errors.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
$userObject = UserObject::get_instance();

if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {    // Special users have their own separate UI.
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['accessdenied'], $msg, $string['accessdenied'], $configObject->get('cfg_root_path') . '/artwork/access_denied.png', '#C00000', true, true);
}

// Get parameters.
$id = check_var('id', 'GET', true, false, true, param::ALPHANUM); // While it is an int, the numbers are too large for 32-bit PHP.
$mode = param::optional('mode', '', param::ALPHA);
$getmode = param::optional('mode', '', param::ALPHA, param::FETCH_GET);
$post_screen = param::optional('current_screen', null, param::INT, param::FETCH_POST);
$get_qid = param::optional('q_id', null, param::INT, param::FETCH_GET);
$q_number = param::optional('qNo', null, param::INT, param::FETCH_GET);
$do_not_record = param::optional('dont_record', false, param::BOOLEAN, param::FETCH_GET);
$refpane = param::optional('refpane', 0, param::INT, param::FETCH_POST);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);
$papertype = $propertyObj->get_paper_type();
$deleted = $propertyObj->get_deleted();

// If the paper has been deleted we should exit as this is an invalid page
// and Deny access to offline papers.
if ($deleted != NULL or $papertype == '5') {
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

$paperID = $propertyObj->get_property_id();

/*
 *
 * Setup some feature related flags
 *
 */

// Are we in a staff test and preview mode?
$is_preview_mode = ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and $mode === 'preview');

// Are we on the first screen?
$is_first_launch = is_null($post_screen);

// Are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ($is_preview_mode == true and $getmode === 'preview');

// Are we in a staff single question test mode?
$is_question_preview_mode = !is_null($get_qid);

if (!$is_first_launch) require '../include/marking_functions.inc';
$screen_data = $propertyObj->get_screens($is_question_preview_mode, $get_qid);
$no_screens = $propertyObj->get_max_screen();

// Is this a type of paper that allows only one attempt?
$do_restart = ($is_first_launch and ($papertype == 1 or $papertype == 2 or $papertype == 3));

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$attempt = 1;                 //default attempt to 1 overwritten if the student is resit candidate by (check_modules)
$low_bandwidth = 0;           //default to off overwritten by (check_labs) if lab has low_bandwidth set
$lab_name = NULL;             //default overwritten by (check_labs)
$lab_id = NULL;
$current_address = NetworkUtils::get_client_address();

//get the module Ids for this paper
$modIDs = array_keys(Paper_utils::get_modules($paperID, $mysqli));
$moduleID = $propertyObj->get_modules();

if ($userObject->has_role('Staff') and check_staff_modules($moduleID, $userObject)) {
  // No further security checks.
} else {    // Treat as student with extra security checks.

  // Check for additional password on the paper.
  check_paper_password($propertyObj->get_property_id(), $propertyObj->get_password(), $string, $mysqli);

  // Check time security.
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli, $is_first_launch);

  //Check room security.
  $low_bandwidth = check_labs($papertype,
    $propertyObj->get_labs(),
    $current_address,
    $propertyObj->get_password(),
    $string,
    $mysqli
  );

  // Check modules if the user is a student and the paper is not formative.
  $attempt = check_modules($userObject, $modIDs, $propertyObj->get_calendar_year(), $string, $mysqli);

  // Check for any metadata security restrictions.
  check_metadata($paperID, $userObject, $modIDs, $string, $mysqli);

  // Check if the student has clicked 'Finish'.
  check_finished($propertyObj, $userObject, $string, $mysqli);

  if ($papertype == '2') {
    // Check current IP address with that of attempt in log.
    // Warn user they are logged into mulitple devices in this exam and log them out.
    check_ipmismatch($paperID, $current_address, $string, $userObject, $mysqli);
  }
}

// Get lab info used in log metadata.
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)) {
  $lab_name = $lab_object->get_name();
  $lab_id = $lab_object->get_id();
}

/*
* Set the default state
*/
$log_metadata = null;
$current_screen = 1;
$is_fire_alarm = param::optional('fire_alarm', false, param::BOOLEAN, param::FETCH_POST);
$summative_exam_session_started = false; //lab timing stated by invigilators

/*
* Extract the posted variables.
*/
if (!$is_first_launch) {
  $button_pressed = param::optional('button_pressed', '', param::TEXT, param::FETCH_POST);
  if ($button_pressed == 'next') {
    $current_screen = $post_screen;
  } elseif ($button_pressed == 'previous') {
    $current_screen = $post_screen - 2;
  } elseif ($button_pressed == 'jumpscreen') {
    $current_screen = param::optional('jumpscreen', 0, param::INT, param::FETCH_POST);
  } elseif ($is_fire_alarm) {
    $current_screen = $post_screen;
  }
}

// Set up new metadata record or get existing one.
$log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);

if ($is_preview_mode_first_launch == true or ($is_first_launch and !$do_restart)) {
  //in preview mode or for non-restartable papers always start a new session if we have relaunched the window
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);

} elseif ($log_metadata->get_record() == false) { //load the data and check for no records
  // Check the time again, just in case the user realised they can add a post screen check to get around the first launch check.
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli, true);
  //we have no log_metadata record so make one
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);
}
$metadataID = $log_metadata->get_metadata_id();

//  Check if paper should display a timer.
$allow_timing = $propertyObj->display_timer();

/*
* BP Determine the student's end_date timestamp for a summative exam that has been 'Started'.
* This is also used further down to make sure that the timer does not close the window if the exam session hasn't been 'started' by an invigilator
* If a summative exam session has been started  then record late answers in log_late
*/
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $papertype == '2' and !$is_question_preview_mode) {
  // Has this lab had an end time set?
  $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

/*
* Save any posted answers
*
* Note: if Ajax saving is enabled: After a successful Ajax save the form is posted as the user moves to the next screen
*                                with dont_record set to true so this is not executed
*/
if (!$is_question_preview_mode) {
  if (!$is_first_launch and !$do_not_record) {
    record_marks($paperID, $mysqli, $papertype, $metadataID);
  }
}

/*
* Load up any previously submitted user answers from the appropriate log table(s)
*
* Note: If the user has gone passed the end of the exam (possible in some cases if security is relaxed)
*       records could exist in 2 logs the original paper type log and log_late
*
*/
$log = log::get_paperlog($papertype);
$check_log_late = false;
// Check for submissions after the end date and set them to save in log_late if we are not in preview_mode or a summative exam session as not been started
if ($is_preview_mode === false and time() > $propertyObj->get_end_date() and ($log->get_papertype() == '1' or ($log->get_papertype() == '2' and $paper_scheduled and $summative_exam_session_started === false))) {
  $check_log_late = true;
}
$l = $log->get_previous_answers($metadataID, $do_restart, $current_screen, $check_log_late);
$user_answers = $l['user_answers'];
$user_dismiss = $l['user_dismiss'];
$user_order = $l['user_order'];
$used_questions = $l['used_questions'];
$previous_duration = $l['previous_duration'];
$screen_pre_submitted = $l['screen_pre_submitted'];
$current_screen = $l['current_screen'];

if ($propertyObj->get_bidirectional() == 0 and $do_restart) {   // Linear
  $current_screen = $log_metadata->get_highest_screen() + 1;
  if ($current_screen > $no_screens) {
    $current_screen = $no_screens;
  }
}

// Load any reference materials.
list($reference_materials, $max_ref_width) = $propertyObj->load_reference_materials();

if (isset($low_bandwidth) and $low_bandwidth == 1) {
  // Lowbandwidth
  ob_start('ob_gzhandler');   // enable compression
}

$url_mod = ($is_question_preview_mode) ? '&q_id=' . $get_qid . '&qNo=' . $q_number : '';
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$renderpath = $texteditorplugin->get_render_paths();
$renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
$render = new render($configObject, $renderpath);
$headerdata = array(
  'css' => array(
    '/css/start.css',
  ),
  'scripts' => array(
    '/js/jquery-1.11.1.min.js',
    '/js/jquery.validate.min.js',
    '/js/validation/jquery.paper.enhancedcalc.min.js',
    '/js/start.min.js',
  ),
  'metadata' => array(
    'pragma' => 'no-cache',
  ),
);
if ($papertype == '3') {
  $lang['title'] = $string['survey'];
} else {
 $lang['title'] = $string['assessment'];
}
if (Paper_utils::need_interactiveQ($screen_data, $current_screen, $mysqli)) {
  $headerdata['scripts'][] = '/js/html5.images.min.js';
  $headerdata['scripts'][] = '/js/qsharedf.js';
  $headerdata['scripts'][] = '/js/qlabelling.js';
  $headerdata['scripts'][] = '/js/qhotspot.js';
  $headerdata['scripts'][] = '/js/qarea.js';
}
if($configObject->get_setting('core', 'paper_mathjax')) {
  $headerdata['scripts'][] = '/js/mathjax-config.min.js';
  $headerdata['scripts'][] = '/node_modules/mathjax/MathJax.js?config=TeX-MML-AM_HTMLorMML';
}
if($propertyObj->get_calculator()) {
  $headerdata['scripts'][] = '/js/jquery-ui-1.10.4.min.js';
  $headerdata['scripts'][] = '/js/jcalc98.min.js';
  $headerdata['scripts'][] = '/js/jcalc98uon.min.js';
  $headerdata['css'][] = '/css/jcalc98.css';
}

// Check if 3d file types are enabled and load js.
if($configObject->get_setting('core', 'paper_threejs')) {
  $headerdata['scripts'] = array_merge($headerdata['scripts'], threed_handler::get_js());
  $headerdata['css'] = array_merge($headerdata['css'], threed_handler::get_css());
}
$headerdata['mee'] = $configObject->get_setting('core', 'paper_mee');
$headerdata['texteditor'] = $texteditorplugin->get_header_file();
$render->render($headerdata, $lang, 'header.html');
?>

<script>
  var lang_string = <?php echo json_encode($jstring); ?>;
</script>

<?php

  /*
  *
  * Build the paper structure
  *
  */
  $question_no = 0;
  $q_displayed = 0;
  $tmp_questions_array = $propertyObj->build_paper($is_question_preview_mode, $get_qid, $q_number);

  // Look for random questions and overwrite as needed
  $questions_array = array();
  $hidden = array();
  foreach ($tmp_questions_array as $question) {
    if ($question['q_type'] == 'random') {
      $question = $propertyObj->randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
      if ($current_screen == $question['screen']) {
        $hidden[$question['no_on_screen']] = $question['q_id'];
      }
    } elseif ($question['q_type'] == 'keyword_based') {
      $question = $propertyObj->keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
      if ($current_screen == $question['screen'] and $question['q_id'] != -1) {
        $hidden[$question['no_on_screen']] = $question['q_id'];
      }
    }
    if ($question['q_type'] == 'enhancedcalc') {
      require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
      if (!isset($configObj)) {
        $configObj = Config::get_instance();
      }
      $question['object'] = new EnhancedCalc($configObj);
      $question['object']->load($question);
    }
    $questions_array[] = $question;
  }
  unset($tmp_questions_array);

  $incomplete_screens = get_unanswered_screens($no_screens, $screen_data, $user_answers, $questions_array, $paperID, $mysqli);

  // BP If the duration is set then show timer
  $timer_label = '';
  $timed = false;
  $special_needs_percentage = $userObject->get_special_needs_percentage();
  $remaining_time = null;
  if ($allow_timing and $propertyObj->get_exam_duration() != null) {
    $timed = true;
    // Summative type. Time is only active in live.
    if (($papertype == '2') and $is_preview_mode === false) {

      // Has the student been allotted extra time by an invigilator?
      $student_object['user_ID'] = $userObject->get_user_ID();
      $student_object['special_needs_percentage'] = $special_needs_percentage;
      $log_extra_time = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);

      // Do not time the exam if the invigilator has not clicked on the 'Start' button
      if ($summative_exam_session_started !== false) {
        $summative_timer  = new SummativeTimer($log_extra_time);
        $remaining_time   = $summative_timer->calculate_remaining_time_secs();
        $timer_label      = $string['timeremaining'] . ':';
      }

    } else {

      $timer          = new Timer($log_metadata, $propertyObj->get_exam_duration(), $special_needs_percentage);
      $start_datetime = $timer->get_start_datetime();

      if ($start_datetime === false) {
        $timer->start();
      }

      $remaining_time = $timer->calculate_remaining_time();
      $timer_label    = $string['timeremaining'] . ':';
    }
  }

  if($propertyObj->get_calculator()) {
    $render->render(null, null, 'jcalc98.html');
  }

  if ($current_screen < $no_screens) {
    $contentdata['action'] = Url::fromGlobals();
  } else {
    $contentdata['action'] = "/finish.php?id=" . $id . $url_mod;
  }
  $contentdata['hidden'] = $hidden;
  $contentdata['previewmode'] = $is_question_preview_mode;

  if (!$is_question_preview_mode) {
    $contentdata['papertitle'] = $propertyObj->get_paper_title();
    $question_offset = 0;
    if ($no_screens > 1) {
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          $contentdata['screen'][$i]['screentype'] = 'scr_cur';
        } else {
          if ($incomplete_screens[$i] == 1) {
          $contentdata['screen'][$i]['screentype'] = 'scr_un';
          } else {
          $contentdata['screen'][$i]['screentype'] = 'scr_ans';
          }
        }
        $no_questions = 0;
        if (isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            $no_questions++;
          }
        }
        $contentdata['screen'][$i]['noquestions'] = $no_questions;
        if ($no_questions === 1) {
          $contentdata['screen'][$i]['noquestionsclass'] = 'question';
        } else {
          $contentdata['screen'][$i]['noquestionsclass'] = 'questions';
        }
        if ($i < $current_screen and isset($screen_data[$i])) {
          foreach ($screen_data[$i] as $screen_question) {
            if ($screen_question[0] != 'info' ) {
              $question_offset++;
            }
          }
        }
        $contentdata['screen'][$i]['pageno'] = $i;
      }
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == $current_screen) {
          $contentdata['screen'][$i]['screentype2'] = 'scr_arrow';
        } else {
          $contentdata['screen'][$i]['screentype2'] = 'scr_spacer';
        }
      }
    }
  }

  $themedirectory = rogo_directory::get_directory('theme');
  $logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
  $contentdata['logopath'] = $logo_path;

  $midexam_clarification = $configObject->get_setting('core', 'summative_midexam_clarification');

  if ($papertype === '3') {
    $calculator = 0;
  } else {
    $calculator = $propertyObj->get_calculator();
  }

  if (in_array('students', $midexam_clarification)) {
    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    $contentdata['examclarification'] = $exam_announcementObj->display_student_announcements();
  }

  $render->render($contentdata, $string, 'paper/header.html');

  // Get linked question parents.
  $linked = PaperUtils::get_linked_question_parents($questions_array);
  
  // Display each question
  $unanswered = false;
  foreach ($questions_array as &$question) {

    // Question not on this screen, don't display
    if ($question['screen'] != $current_screen) {
      continue;
    }

    // Check if calculation question is a parent of a future question.
    if ($question['q_type'] === 'enhancedcalc') {
      if (in_array($question['q_id'], $linked)) {
        $question['object']->set_link_parent();
      }
    }
    // refer to all questions on displayed question
    $question['paper_questions'] = &$questions_array;
    $questionrender = new questionrender($question['q_type']);
    $questionrender->questiondata->unansweredkey = false;
    if ($screen_pre_submitted == 1 and $q_displayed == 0) {
      $questionrender->questiondata->unansweredkey = true;
    }
    $questionrender->questiondata->labelcolour = $labelcolor;
    $questionrender->questiondata->displaycalc = $calculator;
    $questionrender->display_question($screen_pre_submitted, $q_displayed, $string, $question, $paperID, $current_screen, $question_no, $user_answers);
    if ($questionrender->questiondata->unanswered) {
      $unanswered = true;
    }
    $q_displayed++;
  }

  $current_screen++;

  $footer_data['current_screen'] = $current_screen;
  $footer_data['nextbutton'] = sprintf($string['nextscreen'], $footer_data['current_screen']);
  $footer_data['page_start'] = date("YmdHis", time());
  $footer_data['old_screen'] = $current_screen - 1;
  $footer_data['previous_duration'] = $previous_duration;
  $footer_data['refpane'] = $refpane;

  if ($is_question_preview_mode === true) {
    $submitype = "preview";
  } elseif ($propertyObj->get_bidirectional() == 0) {
    $submitype = "linear";
  } else {
    $submitype = "bidirectional";
  }
  if ($is_question_preview_mode) {
    $footer_data['previewmode'] = 1;
  } else {
    if ($is_preview_mode) {
      $footer_data['previewmode'] = 2;
    } else {
      $footer_data['previewmode'] = 0;
    }
    $footer_data['msg'] = '';
    if ($current_screen > $no_screens) {
      $footer_data['msg'] = $string['finishnote'];
    } elseif ($propertyObj->get_bidirectional() == 0) {
      $footer_data['msg'] = sprintf($string['pleasecomplete'], $current_screen);
    }
  }

  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff')) and $is_question_preview_mode) {
    $footer_data['adminview'] = true;
  } else {
    $footer_data['adminview'] = false;
    if ($papertype == '2') {
      $footer_data['fire'] = true;
    } else {
      $footer_data['fire'] = false;
    }

    $footer_data['timerlabel'] = $timer_label;

    $footer_data['bidirectional'] = false;
    if ($propertyObj->get_bidirectional() == 1 and $no_screens > 1) {
      $footer_data['bidirectional'] = true;
      if ($current_screen > 2) {
        $footer_data['previous'] = true;
        $footer_data['previousscreen'] = $current_screen - 2;
        $footer_data['previousbutton'] = sprintf($string['previousscreen'], $footer_data['previousscreen']);
      } else {
        $footer_data['previous'] = false;
      }
      if (in_array($papertype, array('0', '1', '2'))) {
        $footer_data['jumpscreen'] = true;
        $options = array();
        for ($i = 1; $i <= $no_screens; $i++) {
          $selected = $i == ($current_screen - 1) ? ' selected' : '';
          $options[$i] = $selected;
        }
        $footer_data['jumpscreenoptions'] = $options;
      } else {
        $footer_data['jumpscreen'] = false;
      }
    }
    if ($current_screen > $no_screens) {
      $footer_data['endscreen'] = true;
    } else {
      $footer_data['endscreen'] = false;
    }

  }

$footer_data['copyright'] = sprintf ($string['papercopyright'], date("Y"), $configObject->get_setting('core', 'misc_company'));

$render->render($footer_data, $string, 'paper/footer.html');
$render->render(array(), $string, 'paper/overlays.html');
// Paper dataset.
$dataset['name'] = 'paper';
$dataset['attributes']['pid'] = $id;
$dataset['attributes']['urlmod'] = html_entity_decode($url_mod);
$dataset['attributes']['submittype'] = $submitype;
$dataset['attributes']['refcount'] = count($reference_materials);
$dataset['attributes']['savefreq'] = (($configObject->get_setting('core', 'paper_autosave_frequency') + rand(-5,5)) * 1000);
// Set the time out of one requst to be the maximum total time plus 5s for network latency
// PHP handles normal timeouts. This is just to make sure the user won't wait forever if somthing
// weird happens.
$settimeout = $configObject->get_setting('core', 'paper_autosave_settimeout');
$retrylimit = $configObject->get_setting('core', 'paper_autosave_retrylimit');
$backofffactor = $configObject->get_setting('core', 'paper_autosave_backoff_factor');
$dataset['attributes']['savetimeout'] = ceil((($retrylimit * $backofffactor * $settimeout) + $settimeout + 5)) * 1000;
$dataset['attributes']['saveretry'] = $retrylimit;
$dataset['attributes']['timed'] = $timed;
$render->render($dataset, array(), 'paper/dataset.html');
// CSS dataset.
$datasetcss['name'] = 'css';
$datasetcss['attributes']['bgcolor'] = $bgcolor;
$datasetcss['attributes']['fgcolor'] = $fgcolor;
$datasetcss['attributes']['font'] = $font;
$datasetcss['attributes']['textsize'] = $textsize;
$datasetcss['attributes']['unanswered_color'] = $unanswered_color;
$datasetcss['attributes']['themecolor'] = $themecolor;
$datasetcss['attributes']['marks_color'] = $marks_color;
$datasetcss['attributes']['dismiss_color'] = $dismiss_color;
$datasetcss['attributes']['max_ref_width'] = $max_ref_width;
$datasetcss['attributes']['special_needs'] = $userObject->is_special_needs();
$render->render($datasetcss, array(), 'paper/dataset.html');
// User dataset.
$datasetuser['name'] = 'user';
$datasetuser['attributes']['student'] = $userObject->has_role('Student');
if (!is_null($remaining_time)) {
  $datasetuser['attributes']['remaining_time'] = $remaining_time;
}
$render->render($datasetuser, array(), 'paper/dataset.html');

if (count($reference_materials) > 0) {
  $refdata = array(
    'ref' => $reference_materials,
    'refpane' => $refpane
  );
  $render->render($refdata, $string, 'paper/refmaterial.html');
}
$mysqli->close();

$footerdata = array();
if ($unanswered) {
  $footerdata['scripts'][] = '/js/paperfooter.min.js';
}
$render->render($footerdata, array(), 'footer.html');