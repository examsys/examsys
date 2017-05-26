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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
require_once '../include/staff_student_auth.inc';
require_once '../include/paper_security.php';
require_once '../include/display_functions.inc';
require_once '../include/media.inc';
require_once '../include/errors.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
$userObject = UserObject::get_instance();

if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {    // Special users have their own separate UI.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['accessdenied'], $msg, $string['accessdenied'], $configObject->get('cfg_root_path') . '/artwork/access_denied.png', '#C00000', true, true);
}

check_var('id', 'GET', true, false, false);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$deleted = $propertyObj->get_deleted();

// If the paper has been deleted we should exit as this is an invalid page.
if ($deleted != NULL) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

$paperID = $propertyObj->get_property_id();

/*
 *
 * Setup some feature related flags
 *
 */

// Are we in a staff test and preview mode?
$is_preview_mode = ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and isset($_REQUEST['mode']) and $_REQUEST['mode'] == 'preview');

// Are we on the first screen?
$is_first_launch = !isset($_POST['current_screen']);

// Are we in a staff test and preview mode and on the first screen?
$is_preview_mode_first_launch = ($is_preview_mode == true and isset($_GET['mode']) and $_GET['mode'] == 'preview');

// Are we in a staff single question test mode?
$is_question_preview_mode = (isset($_GET['q_id']));

if (!$is_first_launch) require '../include/marking_functions.inc';

$screen_data = get_screens($is_question_preview_mode, $paperID, $mysqli);
$no_screens = $propertyObj->get_max_screen();

//store the original paper type - needed to retrieve answers from the correct log and functionality related decisions
$original_paper_type = $propertyObj->get_paper_type();

// Is this a type of paper that allows only one attempt?
$do_restart = ($is_first_launch and ($original_paper_type == 1 or $original_paper_type == 2 or $original_paper_type == 3));

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
$current_address = NULL;   //default overwritten by (check_labs)

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
  check_datetime($propertyObj->get_start_date(), $propertyObj->get_end_date(), $string, $mysqli);

  //Check room security.
  $low_bandwidth = check_labs(  $propertyObj->get_paper_type(),
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
$is_fire_alarm = (isset($_POST['fire_alarm']) and $_POST['fire_alarm'] == '1');
$summative_exam_session_started = false; //lab timing stated by invigilators
$allow_timing = false;

/*
* Extract the posted variables.
*/
if (!$is_first_launch) {
  if ($_POST['button_pressed'] == 'next') {
    $current_screen = $_POST['current_screen'];
  } elseif ($_POST['button_pressed'] == 'previous') {
    $current_screen = $_POST['current_screen'] - 2;
  } elseif ($_POST['button_pressed'] == 'jump_screen') {
    $current_screen = $_POST['jump_screen'];
  } elseif ($is_fire_alarm) {
    $current_screen = $_POST['current_screen'];
  }
}

// Set up new metadata record or get existing one.
$log_metadata = new LogMetadata($userObject->get_user_ID(), $paperID, $mysqli);

if ($is_preview_mode_first_launch == true or ($is_first_launch and !$do_restart)) {
  //in preview mode or for non-restartable papers always start a new session if we have relaunched the window
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);

} elseif ($log_metadata->get_record() == false) { //load the data and check for no records
  //we have no log_metadata record so make one
  $log_metadata->create_new_record($current_address, $userObject->get_grade(), $userObject->get_year(), $attempt, $lab_name);
}
$metadataID = $log_metadata->get_metadata_id();

// Foramtive or Progressive papers that have a duration set should use the timer.
if ($propertyObj->get_paper_type() == '0' || $propertyObj->get_paper_type() == '1') {
    if ($propertyObj->get_exam_duration() != null) {
        $allow_timing = true;
    }
// Summative exams only allow timing if ALL the modules of the paper allow it.
} else if ($propertyObj->get_paper_type() == '2'){
    $allow_timing = module_utils::modules_allow_timing($modIDs, $mysqli);
}

/*
* BP Determine the student's end_date timestamp for a summative exam that has been 'Started'.
* This is also used further down to make sure that the timer does not close the window if the exam session hasn't been 'started' by an invigilator
* If a summative exam session has been started  then record late answers in log_late
*/
$paper_scheduled = ($propertyObj->get_start_date() !== null);
if ($propertyObj->get_exam_duration() != null and $propertyObj->get_paper_type() == '2' and !$is_question_preview_mode) {
  // Has this lab had an end time set?
  $log_lab_end_time = new LogLabEndTime($lab_id, $propertyObj, $mysqli);
  $summative_exam_session_started = $log_lab_end_time->get_session_end_date_datetime();
}

// Check for submissions after the end date and set them to save in log_late if we are not in preview_mode or a summative exam session as not been started
if ($is_preview_mode === false and time() > $propertyObj->get_end_date() and ($propertyObj->get_paper_type() == '1' or ($propertyObj->get_paper_type() == '2' and $paper_scheduled and $summative_exam_session_started === false))) {
  $propertyObj->set_paper_type('_late');
}

/*
* Save any posted answers
*
* Note: if Ajax saving is enabled: After a successful Ajax save the form is posted as the user moves to the next screen
*                                with dont_record set to true so this is not executed
*/
if (!$is_question_preview_mode) {
  if (!$is_first_launch and (!isset($_GET['dont_record']) or $_GET['dont_record'] != true)) {
    record_marks($paperID, $mysqli, $propertyObj->get_paper_type(), $metadataID);
  }
}

/*
* Load up any previously submitted user answers from the appropriate log table(s)
*
* Note: If the user has gone passed the end of the exam (possible in some cases if security is relaxed)
*       records could exist in 2 logs the original paper type log and log_late
*
*/
$user_answers = array();
$previous_duration = 0;
$screen_pre_submitted = 0;

// Get users previous answers from the log.
if ($propertyObj->get_paper_type() == '_late') {
  // If we are after the deadline check for answers in original_paper_type_log - these will be over written below by new answers in log_late below
  $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$original_paper_type WHERE metadataID = ?");
  $log_data->bind_param('i', $metadataID);
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
// Get user answers from whichever log is pointed to by log$paper_type
if ($propertyObj->get_paper_type() == '5') {
  // There is no user answer in Log5 (offline papers) so put NULL instead.
	$log_data = $mysqli->prepare("SELECT id, q_id, NULL AS user_answer, NULL AS duration, NULL AS screen, NULL AS dismiss, NULL AS option_order FROM log" . $propertyObj->get_paper_type() . " WHERE metadataID = ? ORDER BY id");
} else {
	$log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log" . $propertyObj->get_paper_type() . " WHERE metadataID = ? ORDER BY id");
}
$log_data->bind_param('i', $metadataID);
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

if ($propertyObj->get_bidirectional() == 0 and $do_restart) {   // Linear
  $current_screen = $log_metadata->get_highest_screen() + 1;
  if ($current_screen > $no_screens) {
    $current_screen = $no_screens;
  }
}

// Load any reference materials.
$reference_materials	= load_reference_materials($paperID, $mysqli);
$max_ref_width 				= get_max_reference_width($reference_materials);

require '../config/start.inc';
echo "<!DOCTYPE html>\n<html>\n<head>\n";

$url_mod = ($is_question_preview_mode) ? '&q_id=' . $_GET['q_id'] . '&qNo=' . $_GET['qNo'] : '';
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
if ($userObject->is_special_needs() and $bgcolor != '#FFFFFF' and $bgcolor != 'white') {
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
if ($dismiss_color != '#A5A5A5') {
  $css .= ".inact {color:$dismiss_color}";
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
<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<?php
  if ($propertyObj->get_latex_needed() == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-migrate-1.2.1.min.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>\n";
  }

  if (Paper_utils::need_interactiveQ($screen_data, $current_screen, $mysqli)) {
    $render = new render($configObject);
    $render->render_html5_js(json_encode($jstring));
  }

  echo $configObject->get('cfg_js_root');
?>
<script>
  window.history.go(1);
<?php
  if (count($reference_materials) > 0) {
    echo "\$(function () {\n";
    if (isset($_POST['refpane'])) {
      echo "  changeRef(" . $_POST['refpane'] . ");\n";
    } else {
      echo "  changeRef();\n";
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


  <?php
  if (count($reference_materials) > 0) {
      echo "var changeRef = function(refID) {\n";
      echo "\$('#refpane').val(refID);\n";
      echo "winH = \$(window).height();\n";
      echo "resizeReference();\n";
      echo "var flag = 0;\n";

      echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
      echo "      if (i == refID) {\n";
      echo "        $('#framecontent' + i).show();\n";
      echo "        $('#refhead' + i).css('top', (31 * i) + 'px');\n";
      echo "        flag = 1;\n";
      echo "      } else {\n";
      echo "        $('#framecontent' + i).hide();\n";
      echo "        if (flag == 0) {\n";
      echo "          $('#refhead' + i).css('top', (31 * i) + 'px');\n";
      echo "        } else {\n";
      echo "          $('#refhead' + i).css('top', '');\n";
      echo "          $('#refhead' + i).css('bottom', ((" . count($reference_materials) . " - (i + 1)) * 31) + 'px');\n";
      echo "        }\n";
      echo "      }\n";
      echo "    }\n";
      echo "  }\n\n";
      // Expand the first Reference material item.
      echo "  $(document).ready(function() {\n";
      echo "    changeRef(0);\n";
      echo "  });";
    } else {
      echo "var changeRef = function(refID) {};\n";
    }
  ?>


  var resizeReference = function() {
		winH = $(window).height();
<?php
  if (count($reference_materials) > 0) {
    $subtract = (31 * count($reference_materials)) + 11;
    echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
    echo "      $('#framecontent' + i).css('height', (winH - $subtract) + 'px');\n";
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
  var confirmSubmit = function(event) {
    conductSave(event);
  }
<?php
  } elseif ($propertyObj->get_bidirectional() == 0) {   // Linear navigation
?>
  var confirmSubmit = function(event) {
    if ($('#button_pressed').val() == 'finish') {
      showDialog("<?php echo $string['javacheck2'] ?>");
    } else {
      showDialog("<?php echo $string['javacheck1'] ?>");
    }

    $("#dialog_ok").click(function(event) {
      $('body').css('cursor','wait');
      submitted = true;
      $("#overlay").hide();
      conductSave(event);
    });

  }
<?php
  } else {                              // Bi-directional navigation
?>
  var confirmSubmit = function(event) {
	  if (submitted == true) {
      return false;
    }
    if ($('#button_pressed').val() == 'finish') {
      showDialog("<?php echo $string['javacheck2'] ?>");
      $("#dialog_ok").click(function(event) {
        $('body').css('cursor','wait');
        submitted = true;
        $("#overlay").hide();
        conductSave(event);
      });
    } else {
      conductSave(event);
    }
  }

  $(document).ready(function () {
    $('#jumpscreen').change(function () {
      $('#button_pressed').val('jump_screen');
      $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id'] ?>&dont_record=true");
      return userSubmit(null);
    });
  });
<?php
  }

if ($propertyObj->get_paper_type() != '5') { // Do not allow saving for offline papers.
	// Bind save function to the screen for fault tolerant form saving ?>
  var usingAjax = false;
  var submitType = '';
  var autoSaveRef = '';
	var last_save_point = (new Date).getTime();
  var last_saved_user_answers = null;    <?php // Holds the data of the last successful auto save ?>

  $(document).ready(function () {
		<?php  // We have javascript replace the form submit buttons to enable ajax saving ?>
		usingAjax = true;
    last_saved_user_answers = $('#qForm').serialize();
		$('#next').replaceWith('<?php echo "<input id=\"next\" type=\"button\" value=\"" . $string['screen'] . " " . ($current_screen + 1) . " &gt;\" />";?>');
    $('#next').click(checkSubmit);

		$('#previous').replaceWith('<?php echo "<input id=\"previous\" type=\"button\" value=\"&lt; " . $string['screen'] . " " . ($current_screen - 1) . "\" />";?>');
		$('#previous').click(checkSubmit);

		$('#finish').replaceWith('<?php echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />";?>');
		$('#finish').click(checkSubmit);

		<?php // Attach UI events ?>
		$('.rankselect').change(rankCheck);
		$(".calc-answer").keydown(filterKeypress);

		 <?php // Setup autosave ?>
		startAutoSave();

		<?php // Stop forms being submitted with ENTER  ?>
		$('input[type=text]').keydown(function (event) {
				event = event || window.event;
				if (event.keyCode == 13) {
					event.preventDefault();
					return false;
				} else {
					return true;
				}
		});
  });

  <?php // Normal user submit by clicking on next, previous, finish or jump screen ?>
  var checkSubmit = function (event) {
    stopAutoSave();
    if (typeof(tinyMCE) != "undefined") {
      tinyMCE.triggerSave();
    }

    var formData = $('#qForm').serialize();
    submitType = 'userSubmit';
    if (!!event) {
      $('#button_pressed').attr('value',event.target.id);
    }

    $("#dialog_cancel").click(function() {
      $('#savemsg').html("");
      $('body').css('cursor','default');
      $("#overlay").hide();
    });

    confirmSubmit();
  }

  function conductSave(event) {
    if (typeof(tinyMCE) != "undefined") {
      tinyMCE.triggerSave();
    }

    var formData = $('#qForm').serialize();
    submitType = 'userSubmit';
    stopAutoSave();

    $('#saveError').fadeOut('slow');
    $('#savemsg').html("<img src=\"../artwork/busy.gif\" class=\"busyicon\" />");
    <?php // Log which method the users submitted the page via ?>
      if ($('#button_pressed').val() == 'finish') {
        $('#qForm').attr('action',"finish.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");
      } else {
        $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");
      }

    //if (last_saved_user_answers !== formData<?php if (!isset($user_answers[$current_screen])) echo ' || true' ?>) {
      ajaxSave(1);
    //} else {
    //  ajaxSave(0);
    //}
  }

  function showDialog(msg) {
    $("#overlay").show();
    $("#dialog_cancel").focus();
    $("#submit_dialog_msg").html(msg);
    $("#submit_dialog").css('left', (($(window).width() / 2) - 250) + 'px');
    $("#submit_dialog").css('top', (($(window).height() / 2) - 100) + 'px');
  }

  var userSubmit = function (event) {
    <?php // Save any data from wysiwyg  ?>
    if (typeof(tinyMCE) != "undefined") {
      tinyMCE.triggerSave();
    }

    var formData = $('#qForm').serialize();
    submitType = 'userSubmit';
    stopAutoSave();
    if (!!event) {
      $('#button_pressed').attr('value',event.target.id);
    }
    confirmSubmit();
  }

  <?php  // Called when a user has run out of time by UpdateTimerWithRemainingTime in start.js ?>
  var forceSave = function() {
    stopAutoSave();
    ajaxSave(1);
    alert('<?php echo $string['forcesave']; ?>');
    submitType = 'forcedSubmit';
    $('#qForm').attr('action',"finish.php?id=<?php echo $_GET['id'] . $url_mod; ?>&dont_record=true");
    $('#qForm').submit();
  }

  <?php  // Called on auto save time out ?>
  var autoSave = function() {
    submitType = 'autoSave';

    <?php // This could take longer than the autosave timeout stop auto save to stop duplicate events. ?>
    stopAutoSave();

    <?php // Save any data from wysiwyg  ?>
    if (typeof(tinyMCE) != "undefined") {
      tinyMCE.triggerSave();
    }
    var formData = $('#qForm').serialize();

    <?php // Only auto save if the data has changed, OR 20 minutes has elapsed - stop sessions expiring. ?>
		var now_milliseconds = (new Date).getTime();
		var save_diff = now_milliseconds - last_save_point;
    if (last_saved_user_answers !== formData) {
      $('#savemsg').html("<?php echo $string['auto_saving']; ?>")
      ajaxSave(1);
			last_save_point = (new Date).getTime();
    } else if (save_diff > (1000 * 1200)) {
      ajaxSave(0);
			last_save_point = (new Date).getTime();
    } else {
      <?php // Re-register the autosave timer ?>
      startAutoSave();
    }
  }

  var startAutoSave = function () {
    clearTimeout(autoSaveRef);<?php // Cancel any outstanding timeouts to make sure only one auto save is ever registered. ?>
    autoSaveRef = setTimeout("autoSave()",<?php echo (($configObject->get('cfg_autosave_frequency') + rand(-5,5)) * 1000); ?>);
  }

  var stopAutoSave = function() {
    clearTimeout(autoSaveRef);
  }

  var ajaxSave = function (ans_changed) {
    <?php // Hide any errors ?>
    $('#saveError').fadeOut('fast');
    <?php // Random page ID to stop IE caching results. ?>
    date = new Date();
    randomPageID = date.getTime();
    $('#randomPageID').val(randomPageID);
    if (typeof(tinyMCE) != "undefined"){
      tinyMCE.triggerSave();
    }
    $.ajax({
          url: 'save_screen.php?id=<?php echo $_GET['id'] . $url_mod; ?>&ans_changed=' + ans_changed + '&submitType=' + submitType + '&rnd=' + randomPageID + '<?php echo html_entity_decode($url_mod) ?>',
          type: 'post',
          data: $('#qForm').serialize(),
          dataType: 'html',
          timeout: <?php
            // Set the time out of one requst to be the maximum total time plus 5s for network latency
            // PHP handles normal timeouts. This is just to make sure the user won't wait forever if somthing
            // weird happens.
            echo ceil((($configObject->get('cfg_autosave_retrylimit') * $configObject->get('cfg_autosave_backoff_factor') * $configObject->get('cfg_autosave_settimeout')) + $configObject->get('cfg_autosave_settimeout') + 5)) * 1000;
                   ?>,
          cache: false,
          tryCount : 0,
          retryLimit : <?php echo $configObject->get('cfg_autosave_retrylimit'); // Try 3 times before erroring ?>,
          beforeSend: function() {
          },
          fail: function() {
            if (this.retry()) {
              return;
            } else  {
              saveFail('fail', this.url, '');
              return;
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            if (textStatus == 'error') {
              if (this.retry()) {
                return;
              } else {
                // Status is the response code and errorThrown is the HTTP response text.
                if (errorThrown != '') {
                    saveFail(textStatus + ': ' + xhr.status, this.url, errorThrown);
                    return;
                }
              }
            }
            // Just use the xhr status.
            saveFail(textStatus + ': ' + xhr.status, this.url, '');
            return;
          },
          success: function (ret_data, textStatus, jqXHR) {
            if (ret_data == randomPageID) {
              $('#save_failed').val('');
              <?php // Cache the form data to look for changes on next auto save ?>
              last_saved_user_answers = this.data;
              saveSuccess();
              return;
            }
            if (this.retry()) {
              return;
            } else {
              // marking_funcions.inc record_marks failed after retry.
              // red_data can be ERROR, a random generated number or a html page.
              if (ret_data == 'ERROR' || (!isNaN(parseFloat(ret_data)) && isFinite(ret_data))) {
                // record the returned random number or ERROR.
                saveFail('record_marks', this.url, ret_data);
              } else {
                  // Strip out the title of the html as thats is all we are interested in.
                  htmlstart = ret_data.indexOf("<title>") + 7; 
                  htmlend = ret_data.indexOf("</title>"); 
                  htmltitle = ret_data.substring(htmlstart, htmlend);
                  if (htmltitle == '') {
                      htmltitle = 'html response';
                  }
                  saveFail('record_marks', this.url, htmltitle);
              }
              return;
            }
          },
          retry: function (){
            <?php // Retry if we can ?>
            this.tryCount++;
            if (this.tryCount <= this.retryLimit) {
              <?php // Indicate the retry on the url ?>
              if (this.tryCount == 1) {
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
    <?php // Re-register the autosave timer ?>
    startAutoSave();
    if (submitType == 'userSubmit') {
      $('#qForm').submit();
      return true;
    } else if(submitType == 'forcedSubmit') {
      $('#qForm').submit();
    } else {
      $('#savemsg').html("<?php echo $string['auto_ok'] ?>");
      <?php // Clear auto save message ?>
      setTimeout("$('#savemsg').html(\"\")", 5000);
    }
  }

  var saveFail = function (textStatus, url, ret_data) {
    <?php // Re-register the autosave timer ?>
    startAutoSave();

    current_val =  $('#save_failed').val();
    unix_now = Math.round($.now() / 1000);
    if (current_val == '') {
      $('#save_failed').val(unix_now  + '-' + textStatus + '-' + url + '-' + ret_data);
    } else {
      $('#save_failed').val(current_val + '\n' + unix_now + '-' + textStatus + '-' + url + '-' + ret_data);
    }

    $('#saveError').fadeIn('fast');
    $('#savemsg').html("");
    $('body').css('cursor','default');
    submitted = false;

    return false;
  }

  $(document).ready(function () {
    $('#fire_exit').click(function() {
      submitType = 'userSubmit';
      $('#button_pressed').val('fire_exit');
      if (usingAjax) {
        $('#qForm').attr('action',"fire_evacuation.php?id=<?php echo $_GET['id'] ?>&dont_record=true");
      } else {
        $('#qForm').attr('action',"fire_evacuation.php?id=<?php echo $_GET['id'] ?>");
      }
      ajaxSave(1);
    });
  });
<?php
}
?>
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
                                          settings,
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
                                          papers, questions LEFT JOIN options ON questions.q_id = options.o_id
                                      WHERE
                                        paper = ? AND
                                        q_id = ? AND
                                        papers.question = questions.q_id
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
                                            settings,
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
                                            papers, questions LEFT JOIN options ON questions.q_id = options.o_id
                                        WHERE
                                          paper = ? AND
                                          papers.question = questions.q_id
                                        ORDER BY
                                        display_pos,
                                        id_num");
    $tmp_pid = $paperID;
    $question_data->bind_param('i', $tmp_pid);
  }
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;

  $q_no = 0;
  $assigned_number = 0;
  $no_on_screen = 0;
  $old_screen = 0;
  // Build the questions_array
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
			if (isset($_GET['qNo'])) {
				$tmp_questions_array[$q_no]['assigned_number'] = $_GET['qNo'];   // Preview mode, use the number that is passed in.
			} else {
				$tmp_questions_array[$q_no]['assigned_number'] = $assigned_number;
      }
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
      $tmp_questions_array[$q_no]['settings'] = $settings;
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

  // Look for random questions and overwrite as needed
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

  $unanswered = false;

  $incomplete_screens = get_unanswered_screens($no_screens, $screen_data, $user_answers, $questions_array, $paperID, $mysqli);

  // BP If the duration is set then show timer

  $method = 'StartClock()';
  $timer_label = '';

  $special_needs_percentage = $userObject->get_special_needs_percentage();
  if ($allow_timing and $propertyObj->get_exam_duration() != null) {
    // Summative type. Time is only active in live.
    if (($propertyObj->get_paper_type() == '2' or $original_paper_type == 2) and $is_preview_mode === false) {

      // Has the student been allotted extra time by an invigilator?
      $student_object['user_ID'] = $userObject->get_user_ID();
      $student_object['special_needs_percentage'] = $special_needs_percentage;
      $log_extra_time = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);

      // Do not time the exam if the invigilator has not clicked on the 'Start' button
      if ($summative_exam_session_started !== false) {
        $summative_timer  = new SummativeTimer($log_extra_time);
        $remaining_time   = $summative_timer->calculate_remaining_time_secs();
        $method           = 'StartTimer(' . $remaining_time . ', true)';
        $timer_label      = $string['timeremaining'] . ':';
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
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . $url_mod . "\" autocomplete=\"off\">";
  } else {
    echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'] . $url_mod . "\" autocomplete=\"off\">";
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
          echo '<div class="scr_spacer"></div>';
        }
      }

    }
    echo '</td>';
    echo $logo_html;
  } else {
    echo '<tr><td>';
  }

  $midexam_clarification = $configObject->get('midexam_clarification');

  if ($propertyObj->get_paper_type() === '3') {
    $calculator = 0;
  } else {
    $calculator = $propertyObj->get_calculator();
  }

  if (in_array('students', $midexam_clarification)) {
    $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
    echo $exam_announcementObj->display_student_announcements();
  }

  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  // Display the questions
  foreach ($questions_array as &$question) {
    if ($question['screen'] == $current_screen) {
      if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr style=\"display:none\" id=\"unansweredkey\"><td colspan=\"2\"><span class=\"unans\">&nbsp;&nbsp;&nbsp;&nbsp;</span> " . $string['unansweredquestion'] . "</td></tr>\n";
      if ($q_displayed == 0 and $current_screen == 1 and $propertyObj->get_paper_prologue() != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $propertyObj->get_paper_prologue() . '</td></tr>';
      if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

			display_question($configObject, $question, $propertyObj->get_paper_type(), $calculator, $current_screen, $previous_q_type, $question_no, $user_answers, $unanswered);

			$previous_q_type = $question['q_type'];
      $q_displayed++;
    }
  }

  echo "</table></td></tr>\n<tr><td>\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"next\" />\n";
  echo "<input type=\"hidden\" id=\"randomPageID\" name=\"randomPageID\" value=\"\" />\n";
  if ($is_question_preview_mode) {
    echo "<input type=\"hidden\" id=\"mode\" name=\"mode\" value=\"preview\" />\n";
  } else {
    if ($is_preview_mode) {
      echo "<input type=\"hidden\" id=\"mode\" name=\"mode\" value=\"preview\" />\n";
    }
    if ($current_screen > $no_screens) {
      echo "<div class=\"callout\">\n<div id=\"calloutTxt\">" . $string['finishnote'] . "</div><b class=\"notch\"></b></div>\n";
    } elseif ($propertyObj->get_bidirectional() == 0) {
      echo "<div class=\"callout\">\n<div id=\"calloutTxt\">" . sprintf($string['pleasecomplete'], $current_screen) . "</div><b class=\"notch\"></b></div>\n";
    }
  }

  echo '<div id="saveError"><img src="' . $configObject->get('cfg_root_path') . '/artwork/no_save.png" width="60" height="60" alt="Warning" /> <div><span style="color:#C42828; font-weight:bold">' .  $string['savefailed'] . '</span><br />' . $string['tryagain'] . '</div></div>';

  if ($userObject->has_role(array('SysAdmin', 'Admin', 'Staff')) and $is_question_preview_mode) {
    if ($propertyObj->get_paper_type() != '5') { // Do not allow saving for offline papers.
			echo '<input id="finish" type="submit" name="next" value="' . $string['finish'] . '" />';
    }
		echo '<input type="hidden" name="refpane" id="refpane" value="' . (count($reference_materials) - 1) . '" />';
  } else {
    echo $bottom_html;
    ?>
    <span style="color:white">
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
      if ($current_screen > 2) {
        echo '<input id="previous" type="submit" name="prev" value="&lt; ' . $string['screen'] . ' ' . ($current_screen - 2) . '" />';
      }
      if ($original_paper_type == '0' or $original_paper_type == '1' or $original_paper_type == '2') {
        echo '<select name="jump_screen" id="jumpscreen">';
        for ($i=1; $i<=$no_screens; $i++) {
          if ($i == ($current_screen - 1)) {
            echo "<option value=\"$i\" selected>$i</option>";
          } else {
            echo "<option value=\"$i\">$i</option>";
          }
        }
        echo '</select>';
      }
    }
    if ($current_screen > $no_screens) {
			echo '<input id="finish" type="submit" name="next" value="' . $string['finish'] . '" />';
		} else {
      echo '<input id="next" type="submit" name="next" value="' . $string['screen'] . ' ' . $current_screen . ' &gt;" />';
    }
    echo '</td></tr></table>';
    echo '<input type="hidden" name="refpane" id="refpane" value="' . (count($reference_materials) - 1) . '" />';
  }
?>
</td></tr></table>

<textarea id="save_failed" name="save_failed" style="display:none"></textarea>

</form>
</div>
<div id="overlay">
  <div id="submit_dialog">
    <div id="submit_dialog_icon"><img src="../artwork/question_mark_64.png" width="64" height="64" alt="?" /></div><p id="submit_dialog_msg"></p>
    <div id="submit_dialog_buttons"><input type="button" name="dialog_ok" id="dialog_ok" class="ok" value="OK" /><input type="button" name="dialog_cancel" id="dialog_cancel" class="cancel" value="Cancel" />&nbsp;&nbsp;</div>
  </div>
</div>
<?php

if (count($reference_materials) > 0) {
  $top = 0;
  $ref_no = 0;
  foreach ($reference_materials as $reference_material) {
    echo "<div class=\"refhead\" id=\"refhead" . $ref_no . "\" onclick=\"changeRef(" . $ref_no . ")\" style=\"top:{$top}px\">" . $reference_material['title'] . "</div>\n";
    echo "<div class=\"framecontent\" id=\"framecontent" . $ref_no . "\" style=\"top:" . (31 + $top) . "px\">\n" . $reference_material['material'] . "</div>\n";
    $top += 31;
    $ref_no++;
  }
}
$mysqli->close();

if (isset($_POST['refpane'])) {
  echo "<script>\n";
  echo "  changeRef(" . $_POST['refpane'] . ");\n";
  echo "</script>\n";
}

if ($unanswered) {
  echo "<script>\n";
  echo "  $('#unansweredkey').show();\n";
  echo "</script>\n";
}
?>
</body>
</html>

<?php

/**
 * Returns an array of screen information - used for the numbers at the top
 * of the screen.
 * @param bool $is_question_preview_mode  - Are we previewing a single question
 * @param array $paperID                  - ID of the paper to look up
 * @param object $db                      - Mysqli object
 * @return array - Returns an array of screens then question ID and question type.
 *
 */
function get_screens($is_question_preview_mode, $paperID, $db) {
  // Get how many screens make up the question paper.
  $screen_data = array();
  if ($is_question_preview_mode) {
    $stmt = $db->prepare("SELECT 1, q_type, q_id
                              FROM
                                questions
                              WHERE
                                questions.q_id = ?
                              ");
    $stmt->bind_param('i', $_GET['q_id']);
  } else {
    $stmt = $db->prepare("SELECT
                                screen, q_type, question
                              FROM
                                (papers, questions)
                              WHERE
                                papers.paper = ? AND
                                papers.question = questions.q_id
                              ORDER BY
                                screen, display_pos");
    $stmt->bind_param('i', $paperID);
  }
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($screen, $q_type, $q_id);

  while ($stmt->fetch()) {
    if ($q_type != 'info') {    // Do not count information blocks.
      $screen_data[$screen][] = array($q_type, $q_id);
    }
  }
  $stmt->free_result();
  $stmt->close();

  return $screen_data;
}

/**
 * Looks up the source question in a random question block.
 * @param object $configObj 		- Configuration object
 * @param array $random_q_data 	- Holds question information about the parent random question.
 * @param array $user_answers 	- Holds a list of user answers by question ID.
 * @param array $screen_data 		- Holds a list of question types and IDs used on all screens in the paper.
 * @param array $used_questions - Array of question IDs already used on the paper.
 * @param object $db    				- Mysqli object
 * @param array $string   			- Contains language translations.
 *
 */
function randomQOverwrite($random_q_data, $user_answers, &$screen_data, &$used_questions, $db, $string) {
  $selected_q_id = '';
  $current_screen = $random_q_data['screen'];
  $q_no = $random_q_data['no_on_screen'];

  if (isset($user_answers[$current_screen])) {
    // Match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }

  if ($selected_q_id == '') {
    $try = 0;
    $unique = false;
    while ($unique == false and $try < 9999) {
      $selected_q_id = random_utils::generate_random_qid_from_block($random_q_data['q_id'], $db);
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

  $error = false;

  if ($unique) {
    // Look up selected question and overwrite data.
    $question_data = $db->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect,"
      . " marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width,"
      . " q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions LEFT JOIN options"
      . " ON questions.q_id = options.o_id WHERE q_id = ? ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect,
      $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media,
      $o_media_width, $o_media_height, $notes, $q_option_order);
    if ($question_data->num_rows() > 0) {
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
            $question['settings'] = $settings;
            $question['q_media'] = $q_media;
            $question['q_media_width'] = $q_media_width;
            $question['q_media_height'] = $q_media_height;
            $question['q_option_order'] = $q_option_order;
            $question['dismiss'] = '';
          }
          $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media,
              'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct,
              'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
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
        $error = true;
    }
    
  } else {
    $error = true;
  }

  if ($error) {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_random'] . '</span>';
    $question['q_type'] = 'random';
    $question['q_id'] = -1;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'] = array();
  }

  return $question;
}

/**
 * Looks up the source question in a keyword question block.
 * @param object $configObj 		- Configuration object
 * @param array $random_q_data 	- Holds question information about the parent random question.
 * @param array $user_answers 	- Holds a list of user answers by question ID.
 * @param array $screen_data 		- Holds a list of question types and IDs used on all screens in the paper.
 * @param array $used_questions - Array of question IDs already used on the paper.
 * @param object $db    				- Mysqli object
 * @param array $string   			- Contains language translations.
 *
 */
function keywordQOverwrite($random_q_data, $user_answers, &$screen_data, &$used_questions, $db, $string) {
  $selected_q_id = '';
  $unique = true;
  $current_screen = $random_q_data['screen'];
  $q_no = $random_q_data['no_on_screen'];

  if (isset($user_answers[$current_screen])) {
    // Match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }

  if ($selected_q_id == '') {
    // Get the keyword id.
    $keyword_id = keyword_utils::get_keywordid_for_question($random_q_data['q_id'], $db);
    // Generate a random question ID from keywords.
    $question_ids = array();
    $question_data = $db->prepare("SELECT DISTINCT k.q_id FROM keywords_question k, questions q WHERE k.q_id = q.q_id AND"
      . " k.keywordID = ? AND q.deleted is NULL");
    $question_data->bind_param('i', $keyword_id);
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
    $question_data = $db->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect,"
      . " marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width,"
      . " q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions LEFT JOIN options ON"
      . " questions.q_id = options.o_id  WHERE q_id = ? ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect,
      $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media,
      $o_media_width, $o_media_height, $notes, $q_option_order);
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
        $question['settings'] = $settings;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = '';
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media,
          'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct,
          'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
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
    $question['leadin'] = '<span style="color:#C00000">' . $string['error_keywords'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'] = array();
    $question['screen'] = $random_q_data['screen'];
    $question['assigned_number'] = $random_q_data['assigned_number'];
    $question['no_on_screen'] = $question['display_pos'] = $q_no;
  }

  return $question;
}

/*
*
* Load any Reference Material into an array.
* @param int $paperID - ID of the current paper
* @param object $db   - database object
* @return array       - Array of all reference material relevant to the current paper.
*/
function load_reference_materials($paperID, $db) {
	$reference_materials = array();
	$ref_no = 0;
	$stmt = $db->prepare("SELECT title, content, width FROM (reference_material, reference_papers) WHERE reference_material.id = reference_papers.refID AND paperID = ?");
	$stmt->bind_param('i', $paperID);
	$stmt->execute();
	$stmt->bind_result($reference_title, $reference_material, $reference_width);
	while ($stmt->fetch()) {
		$reference_materials[$ref_no]['title'] = $reference_title;
		$reference_materials[$ref_no]['material'] = $reference_material;
		$reference_materials[$ref_no]['width'] = $reference_width;
		$ref_no++;
	}
	$stmt->close();

	return $reference_materials;
}

/*
*
* Looks through and returns the largest width for a set of reference materials.
* @param array $reference_materials - Array of reference materials to check.
* @return int				- The maximum width of any reference material for the current paper.
*/
function get_max_reference_width($reference_materials) {
	$max_ref_width = 0;
  foreach ($reference_materials as $reference_material) {
		if ($reference_material['width'] > $max_ref_width) {
			$max_ref_width = $reference_material['width'];
		}
	}

	return $max_ref_width;
}

?>