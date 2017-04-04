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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

// TODO: error handling for AJAX calls

ob_start('ob_gzhandler');
require '../include/staff_student_auth.inc';
require '../include/question_types.php';
require '../include/errors.php';
require '../include/calculate_marks.inc';
require_once '../include/std_set_shared_functions.inc';

$paperID = check_var('paperID', 'GET', true, false, true, param::INT);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);
$unlock = param::optional('unlock', false, param::BOOLEAN);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
$mediadirectory = rogo_directory::get_directory('media');
$configObject = Config::get_instance();
$assessment = new assessment($mysqli, $configObject);

// Unlock code - emergency use only!
// Can only unlock if current user is SysAdmin!
if ($unlock and $userObject->has_role('SysAdmin')) {
  $tmp_date = new DateTime();
  $tmp_date->modify('+28 day');
  $tmp_start_date = $tmp_date->format('Ymd' . '100000');
  $tmp_end_date = $tmp_date->format('Ymd' . '100000');

  // Update the paper date so that it does not immediately re-lock
  $update_params = array(
    'start_date' => array('s', $tmp_start_date),
    'end_date' => array('s', $tmp_end_date)
  );
  $assessment->db_update_assessment($paperID, $update_params);

  // Update the questions to take lock off
  $editPaper = $mysqli->prepare("UPDATE questions INNER JOIN papers ON questions.q_id = papers.question AND paper = ? SET questions.locked = NULL");
  $editPaper->bind_param('i', $paperID);
  $editPaper->execute();
  $editPaper->close();
  $summative_lock = false;
}

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Redirect students to their page.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  if ($properties->get_paper_type() == '2') {
    // Display 'Page not Found' for summative exams. For these go to the proper summative exam homepage.
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
  } else {
    header("location: user_index.php?id=" . $properties->get_crypt_name());
    exit();
  }
}

// Can the user acsess the paper?
$paper_ownerID = Paper_utils::get_ownerID($paperID, $mysqli);

// This papers' modules
$paper_modules = array();

$on_staff_module = false;
if ($userObject->has_role('SysAdmin') or $paper_ownerID == $userObject->get_user_ID()) {
  $on_staff_module = true;
} elseif($userObject->has_role('Standards Setter')) {
  // Do nothing
} else {
  $paper_modules = Paper_utils::get_modules($paperID, $mysqli);
  foreach ($paper_modules as $paper_moduleID => $paper_module) {
    if ($userObject->is_staff_user_on_module($paper_moduleID)) {
      $on_staff_module = true;
    }
  }
}

if ($on_staff_module == false and !in_array('SYSTEM', array_values($paper_modules))) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], '/artwork/page_not_found.png', '#C00000', true, true);
}

if ($properties->get_paper_type() == '4') {		// OSCE
	$killer_questions = new Killer_question($paperID, $mysqli);
	$killer_questions->load();
}

$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();

/**
 * Displays a warning if any questions are duplicated on the paper (not Surveys).
 * @param array $q_screens	- Array of question IDs. If there are more than one element for the same ID you have a duplicate.
 * @param array $string			- Language translations
 */
function check_duplicates($q_screens, $string) {
  foreach ($q_screens as $q_screen=>$qs) {
    if (count($qs) > 1) {
      echo "<tr><td colspan=\"2\" class=\"warnicon\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" /></td><td colspan=\"4\" class=\"warn\">&nbsp;<strong>" . $string['Duplicate questions'] . ":</strong> Q" . implode(', Q', $qs) . "</td></tr>\n";
    }
  }
}

/**
 * Displays warnings if there are certain problems with a question.
 * @param string $q_type				- Type of the question.
 * @param array $temp_array			- Holds information about all the questions on the current paper. Any warnings are added to this array.
 * @param int $row_no						- Number of the element to address in $temp_array.
 * @param int $q_id							- ID of the question.
 * @param string $tmp_excluded 	- Which parts of the question are/are not excluded.
 * @param array $option_text		- Array of the options for the question
 * @param array $correct_array	- Array of which options are correct
 * @param array $string					- Language translations
 * @param array $status_array		- Array of status objects
 * @param object $db						- MySQLi connection.
 */
function checkProblems($q_type, &$temp_array, $row_no, $tmp_excluded, $option_text, $correct_array, $string, $status_array, $settings, $properties, $db) {
	$question_marks = $temp_array[$row_no]['original_marks'];
	$status 				= $temp_array[$row_no]['status'];
	$score_method 	= $temp_array[$row_no]['score_method'];

  if ($tmp_excluded == '0000000000000000000000000000000000000000' and $status_array[$status]->get_validate()) {
    if ($score_method == 'SelectedPositive' and $q_type == 'mrq') {
      if ($question_marks > (count($option_text) / 2)) $temp_array[$row_no]['warnings'] = $string['toomanycorrect'];
    } elseif ($q_type == 'dichotomous') {
      if ($score_method == 'Mark per Option' and $question_marks < count($option_text)) $temp_array[$row_no]['warnings'] = sprintf($string['dichotomouswarning'], $question_marks, count($option_text));
    } elseif ($q_type == 'mcq' and $correct_array[0] == '') {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($q_type == 'enhancedcalc') {
      $bkt_mismatch = false;
      $formula = $settings['answers'];
      foreach ($formula as $form) {
        $opening_bkt = substr_count($form['formula'], '(');
        $closing_bkt = substr_count($form['formula'], ')');
        if ($opening_bkt !== $closing_bkt) {
          $bkt_mismatch = true;
        }
      }
      if ($bkt_mismatch) {
        $temp_array[$row_no]['warnings'] = $string['mismatchbrackets'];
      }
    } elseif ($q_type == 'mrq' and !in_array('y', $correct_array)) {
      $temp_array[$row_no]['warnings'] = $string['nocorrect'];
    } elseif ($q_type == 'textbox' and $question_marks == 0) {
      $temp_array[$row_no]['warnings'] = $string['zeromarks'];
    } elseif ($q_type == 'blank') {
		  $open_blank = substr_count($option_text[0], '[blank'); 
		  $close_blank = substr_count($option_text[0], '[/blank');
		  if ($open_blank != $close_blank) {
				$temp_array[$row_no]['warnings'] = $string['mismatchblanktags'];
			}
		} elseif ($q_type == 'extmatch' or $q_type == 'matrix') {
      $matching_scenarios = explode('|', $temp_array[$row_no]['scenario']);
      $matching_media     = explode('|', $temp_array[$row_no]['q_media']);
      $matching_correct   = explode('|', $correct_array[0]);

      $text_scenarios = 0;
      for ($part_id=0; $part_id<count($matching_scenarios); $part_id++) {
        if (trim(strip_tags($matching_scenarios[$part_id])) != '') $text_scenarios++;
      }
      $media_scenarios = 0;
      for ($part_id=1; $part_id<count($matching_media); $part_id++) {
        if ($matching_media[$part_id] != '') $media_scenarios++;
      }
      $scenario_no = max($text_scenarios, $media_scenarios);

      $correct_answers = 0;
      for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
        if ($matching_correct[$part_id] != '') $correct_answers++;
      }

      if ($score_method == 'Mark per Option' and $correct_answers < $scenario_no) $temp_array[$row_no]['warnings'] = $string['answermissing'];
    } elseif ($q_type == 'labelling') {
      if (!have_valid_labels($temp_array[$row_no]['correct'])) {
        $temp_array[$row_no]['warnings'] = $string['nolabels'];
      }
    } elseif ($q_type == 'random' and $properties->get_paper_type() == '2') {
      $temp_array[$row_no]['warnings'] = $string['notsummativeexams'];
    } elseif ($q_type == 'keyword_based' and $properties->get_paper_type() == '2') {
      $temp_array[$row_no]['warnings'] = $string['notsummativeexams'];
    }
    if ($q_type == 'mcq' and $score_method == 'vertical_other') {
      $temp_array[$row_no]['warnings'] = $string['mcqsurvey'];
    }
    if ($q_type == 'mcq') {  // Check duplicate options
      $have_text = false;
      $option_text_check = array();
      foreach ($option_text as $option) {
        if ($option != '') {
          $have_text = true;
          $option_text_check[] = $option;
        }
      }
      if ($have_text) {
        $option_text_copy = array_map('strtolower', $option_text_check);
        $unique_options = array_unique($option_text_copy);
        if (count($option_text_copy) > count($unique_options)) {
          $temp_array[$row_no]['warnings'] = $string['duplicateoptions'];
        }
      }
      
    }
  }
}

/**
 * Check if a labelling question has any labels added to the canvas
 * @param $correct Correct answer string for the question
 * @return bool
 */
function have_valid_labels($correct) {
  $ok = false;

  $tmp_first_split = explode(';', $correct);
  $tmp_second_split = explode('$', $tmp_first_split[11]);
	
  for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
    if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
      $ok = true;
      break;
    }
  }

  return $ok;
}

/**
 * Get details of all the questions that make up a random question block.
 * @param int $questionID       - ID of the random question to look up.
 * @param object $configObject  - Configuration object.
 * @return array                - Array of the questions that make up a random question block.
 */
function randomDetails($questionID, $configObject, $db) {
  $question_no = 0;
  $random_questions = array();
  $old_q_id = '';
  $old_score_method = '';
  $old_q_media_width = '';
  $old_q_media_height = '';
  $old_correct = array();
  $old_option_text = array();

  $result = $db->prepare("SELECT theme, options1.q_id, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.option_text, q_type, display_method, score_method, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}'), status, settings FROM random_link AS options1, questions LEFT JOIN options AS options2 ON questions.q_id = options2.o_id WHERE options1.q_id=questions.q_id AND options1.id=? ");
  $result->bind_param('i', $questionID);
  $result->execute();
  $result->store_result();
  if ($result->num_rows > 0) {
    $result->bind_result($theme, $q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks, $option_text, $q_type, $display_method, $score_method, $display_last_edited, $status, $settings);
    while ($result->fetch()) {
      if ($old_q_id != $q_id and $old_q_id != '') {
        $old_leadin = QuestionUtils::clean_leadin($old_leadin);
        $random_questions[$question_no]['theme'] = $old_theme;
        $random_questions[$question_no]['q_id'] = $old_q_id;
        $random_questions[$question_no]['type'] = $old_q_type;
        $random_questions[$question_no]['leadin'] = $old_leadin;
        $random_questions[$question_no]['scenario'] = $old_scenario;
        $random_questions[$question_no]['scenario'] = $old_scenario;
        $random_questions[$question_no]['correct'] = $old_correct;
        $random_questions[$question_no]['status'] = $old_status;
        $random_questions[$question_no]['settings'] = $old_settings;
        $random_questions[$question_no]['display_last_edited'] = $display_last_edited;
        $random_questions[$question_no]['marks'] = qMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        $old_correct = array();
        $old_option_text = array();
        $question_no++;
      }
      $old_theme = $theme;
      $old_q_id = $q_id;
      $old_q_type = $q_type;
      $old_leadin = $leadin;
      $old_scenario = $scenario;
      $old_status = $status;
      $old_settings = $settings;
      $old_marks = $marks;
      $old_correct[] = $correct;
      $old_option_text[] = $option_text;
      $old_display_method = $display_method;
      $old_score_method = $score_method;
      $old_q_media_width = $q_media_width;
      $old_q_media_height = $q_media_height;
    }

    // Write out the last question.
    $old_leadin = QuestionUtils::clean_leadin($old_leadin);
    $random_questions[$question_no]['theme'] = $old_theme;
    $random_questions[$question_no]['q_id'] = $old_q_id;
    $random_questions[$question_no]['type'] = $old_q_type;
    $random_questions[$question_no]['leadin'] = $old_leadin;
    $random_questions[$question_no]['scenario'] = $old_scenario;
    $random_questions[$question_no]['correct'] = $old_correct;
    $random_questions[$question_no]['status'] = $old_status;
    $random_questions[$question_no]['settings'] = $old_settings;
    $random_questions[$question_no]['display_last_edited'] = $display_last_edited;
    $random_questions[$question_no]['marks'] = qMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
    $random_questions[$question_no]['random_mark'] = qRandomMarks($old_q_type, '', $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
  }
  $result->close();
  return $random_questions;
}

function random_qMarks($random_questions) {
  $min = 999;
  $max = 0;

  foreach ($random_questions as $individual_question) {
    if ($individual_question['marks'] > $max) $max = $individual_question['marks'];
    if ($individual_question['marks'] < $min) $min = $individual_question['marks'];
  }

  if ($min == $max) {
    return $min;
  } else {
    return 'ERR';
  }
}

/**
 * Check the parts of a question to see if they contain equations and therefore need to include LaTeX processing code
 * @param string $leadin
 * @param string $scenario
 * @param string $option_text
 * @param string $score_method
 * @param string $correct_fback
 * @param string $feedback_right
 * @return int
 */
function check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right) {
  $latex = 0;

  // latex check [tex]
  if (strpos($leadin,'[tex]') !== false or strpos($scenario,'[tex]') !== false or strpos($option_text,'[tex]') !== false or strpos($score_method,'[tex]') !== false or strpos($correct_fback,'[tex]') !== false or strpos($feedback_right,'[tex]') !== false) {
    $latex = 1;
  }

  // latex check [tex]
  if (strpos($leadin,'[texi]') !== false or strpos($scenario,'[texi]') !== false or strpos($option_text,'[texi]') !== false or strpos($score_method,'[texi]') !== false or strpos($correct_fback,'[texi]') !== false or strpos($feedback_right,'[texi]') !== false) {
    $latex = 1;
  }

  // latex check $$
  if (strpos($leadin,'$$') !== false or strpos($scenario,'$$') !== false or strpos($option_text,'$$') !== false or strpos($score_method,'$$') !== false or strpos($correct_fback,'$$') !== false or strpos($feedback_right,'$$') !== false) {
    $latex = 1;
  }

  // latex check class="mee" (with or without quotes)
  if (check_latex_class(array($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right))) {
    $latex = 1;
  }

  return $latex;
}

/**
 * @param $candidates Array of candidate strings to check for inclusion of the MEE class
 * @return bool True if at least one of the candidates contains the class
 */
function check_latex_class($candidates) {
  foreach ($candidates as $candidate) {
    if (strpos($candidate,'class="mee"') !== false or strpos($candidate,'class=mee') !== false) {
      return true;
    }
  }
  return false;
}

/**
 * Check the random questions on the paper to see if they require LaTeX
 * @param $q_ids
 * @param $mysqli
 * @return int
 */
function check_latex_random($q_ids, $mysqli) {
  $q_ids = implode(',', $q_ids);
  $latex = 0;
  if ($q_ids != '') {
    $result = $mysqli->prepare("SELECT leadin, scenario, option_text, score_method, correct_fback, feedback_right FROM questions INNER JOIN options ON questions.q_id = options.o_id WHERE questions.q_id IN ($q_ids)");
    $result->execute();
    $result->store_result();
    $result->bind_result($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
    while ($result->fetch()) {
      $latex = check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
      if ($latex == 1) {
        break;
      }
    }
  }
  return $latex;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('rogo_version') . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <link rel="stylesheet" type="text/css" href="../css/adhocwindow.css" />
  <!--[if lt IE 8]>
  <style type="text/css">
    td.ie-fullwidth {
      width: 100%!important;
    }
    #content td.t, td.t {
      width:158px;
      min-width:158px
    }
  </style>
  <![endif]-->
  <style type="text/css">
    <?php
      if ($language != 'en') {
        echo "#content td.t, td.t {width: 180px !important}\n";
      } else {
        echo "#content td.d, td.d {width: 130px !important}\n";
      }
      echo QuestionStatus::generate_status_css($status_array);
    ?>
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="../js/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/jquery.rquerystring.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/page_scroll.js"></script>
  <script type="text/javascript" src="../js/adhocwindow.js"></script>
<script defer="defer">
  var paperID = '<?php echo $paperID ?>';

  function addQID(qID, pID, clearall) {
    if (clearall) {
      $('#questionID').val(',' + qID);
      $('#pID').val(',' + pID);
    } else {
      $('#questionID').val($('#questionID').val() + ',' + qID);
      $('#pID').val($('#pID').val() + ',' + pID);
    }
  }

  function subQID(qID, pID) {
    var tmpq = ',' + qID;
    var tmpp = ',' + pID;
    $('#questionID').val($('#questionID').val().replace(tmpq, ''));
    $('#pID').val($('#pID').val().replace(tmpp, ''));
  }

  function clearAll() {
    $('.highlight').removeClass('highlight');
  }

  function selQ(questionNo, questionID, lineID, qType, screenNo, pID, current_pos, menuID, subparts, evt) {
    $('#menu2a').hide();
    if (menuID == '2b') {
      $('#menu2c').hide();
    } else {
      $('#menu2b').hide();
    }
    $('#menu' + menuID).show();

    $('#questionNo').val(questionNo);
    $('#qType').val(qType);
    $('#screenNo').val(screenNo);
    $('#current_pos').val(current_pos);

    if (evt.ctrlKey == false && evt.metaKey == false) {
      clearAll();
      $('#link_' + lineID).addClass('highlight');
      addQID(questionID, pID, true);
    } else {
      if ($('#link_' + lineID).hasClass('highlight')) {
        $('#link_' + lineID).removeClass('highlight');
        subQID(questionID, pID);
      } else {
        $('#link_' + lineID).addClass('highlight');
        addQID(questionID, pID, false);
      }
    }

    if (qType == 'info') {
      $('.clarification').removeClass('menuitem');
      $('.clarification').addClass('greymenuitem');
    } else {
      $('.clarification').removeClass('greymenuitem');
      $('.clarification').addClass('menuitem');
    }
    
    if (qType == 'random') {
      var row = '';
      for (i=1; i<=subparts; i++) {
        row = document.getElementById('r' + lineID + '_' + i);
        if (row.style.display == 'none') {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      }
    }
    hideMenus();

    $('#stats_menu').hide();
    $('#copy_submenu').hide();
    $('#copy_from_submenu').hide();

    if (evt != null) {
      evt.cancelBubble = true;
    }

    if (typeof deActivateAddBreak != 'undefined') {
      var deleteLink = $('#delete_break');
      deActivateDelete(deleteLink);
      var addLink = $('#add_break');
      activateAddBreak(addLink);
    }
		
<?php
	if ($properties->get_paper_type() == '4') {			// OSCE stations
?>
		if ( $("#icon_" + questionNo).hasClass("info_class") ) {
			$("span.killer").addClass('greymenuitem');
		} else if ( $("#icon_" + questionNo).hasClass("killer_icon") ) {
			$("span.killer").html('<?php echo $string['unsetkillerquestion']; ?>');
			$("span.killer").removeClass('greymenuitem');
		} else {
			$("span.killer").html('<?php echo $string['setkillerquestion']; ?>');
			$("span.killer").removeClass('greymenuitem');
		}
<?php
	}
?>
		
    if ($('#questionID').val() == '') {
      qOff();
    }
  }


  function edQ(questionNo, questionID, qType) {
    var loc = "../question/edit/index.php?q_id=" + questionID + "&qNo=" + questionNo + "&paperID=<?php echo $paperID; ?>&calling=paper&scrOfY=" + $('#scrOfY').val();
    if (qType == 'random' || qType == 'keyword_based') {
      loc += '&type=' + qType;
    }
    document.location = loc;
  }

  function qOff() {
    $('#menu2a').show();
    $('#menu2b').hide();
    $('#menu2c').hide();
    clearAll();

    $('#stats_menu').hide();
    $('#copy_submenu').hide();
    $('#copy_from_submenu').hide();

    hideMenus();

    if (typeof deActivateAddBreak != 'undefined') {
      var addLink = $('#add_break');
      deActivateAddBreak(addLink);
    }
  }

  $(function () {
    <?php
    $scrOfY = param::optional('scrOfY', null, param::INT, param::FETCH_GET);
		if ($scrOfY) {
			echo "  window.scrollTo(0," . $scrOfY . ");\n";
		}
		?>
	
		$('#left-sidebar').click(function() {
			$('#copy_submenu').hide();
            $('#copy_from_submenu').hide();
		});

		$(window).click(function(event) {
			hideMenus();
			hideAssStatsMenu(event);
		});
	});
</script>
<?php
  $user_details = UserUtils::get_user_details($properties->get_paper_ownerid(), $mysqli);
  $paper_owner = $user_details['title']  . ' ' . $user_details['initials'] . ', ' . $user_details['surname'];

  if (date("U", time()) >= $properties->get_start_date() and date("U", time()) <= $properties->get_end_date()) {
    $active_date = 1;
  } else {
    $active_date = 0;
  }

  if (!$properties->get_summative_lock()) {
?>
  <script type="text/javascript" src="../js/jquery.paperdetails.js"></script>
<?php
  }
?>
</head>

<body>
<?php
  if ($properties->get_deleted() != '') {
  ?>
    <div id="left-sidebar" class="sidebar">
    </div>
    <div id="content">
      <br />
  <?php
    echo "<div style=\"position:absolute;left:230px;top:10px\"><img src=\"../artwork/exclamation_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"color:#C00000; margin-left:70px;font-size:160%\">" . $string['paperdeleted'] . "</h1>\n";
    $deleted_parts = explode('[deleted', $properties->get_paper_title());
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"height:1px;border:none;margin-left:70px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-top:10px; margin-left:70px\">" . sprintf($string['deleted_msg1'], $deleted_parts[0]) . "</p>\n\n<br />\n<ul style=\"margin-left:80px\">\n";
    if ($properties->get_paper_ownerid() == $userObject->get_user_ID()) {
      echo "<li>" . $string['deleted_msg2'] . "</li>\n";
    } else {
      $tmp_owner = $properties->get_paper_ownerid();
			$owner_details = UserUtils::get_user_details($tmp_owner, $mysqli);
      echo "<li>" . sprintf($string['deleted_msg3'], $owner_details['email'], $owner_details['title'], $owner_details['surname']). "</li>\n";
    }
    echo "</ul>";
    echo "</div>\n</body>\n</html>\n";
    $mysqli->close();
    exit;
  }

  // Log the hit in recent_papers.
  Paper_utils::log_hit($userObject->get_user_ID(), $paperID, $mysqli);

  $old_p_id           = 0;
  $row_no             = 0;
  $row_no2            = 0;
  $old_display_pos    = -1;
  $temp_array         = array();
  $latex              = 0;
  $old_q_id           = 0;
  $old_q_type         = '';
  $old_marks          = 0;
  $old_option_text    = array();
  $old_o_media        = array();
  $old_correct        = '';
  $old_display_method = '';
  $old_score_method   = '';
  $old_q_media        = '';
  $old_q_media_width  = '';
  $old_q_media_height = '';
  $old_scenario       = '';
  $total_random_mark  = 0;
  $total_marks        = 0;
  $options            = 0;
  $neg_marking        = false;
  $rnd_q_ids          = array();
  $q_mod_check        = array();

  // Get the questions (if any).
  $result = $mysqli->prepare("SELECT theme, ownerID, p_id, q_id, q_type, screen, leadin, scenario, option_text, o_media, correct, display_method, score_method, q_media, q_media_width, q_media_height, marks_correct, marks_incorrect, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_last_edited, display_pos, status, correct_fback, feedback_right, locked, settings FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos, o_id");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($theme, $ownerID, $p_id, $q_id, $q_type, $screen, $leadin, $scenario, $option_text, $o_media, $correct, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $marks_correct, $marks_incorrect, $display_last_edited, $display_pos, $status, $correct_fback, $feedback_right, $locked, $settings);

  while ($result->fetch()) {

    if ($q_type == 'sct') {
      $parts = explode('~', $leadin);
      $leadin = $parts[0];
    }

    if (!is_null($settings) and !is_array($settings)) {
      $settings = json_decode($settings, true);
    }
    if (isset($settings['marks_correct'])) {
      $marks_correct = $settings['marks_correct'];
    }
    if (isset($settings['marks_incorrect'])) {
      $marks_incorrect = $settings['marks_incorrect'];
    }
    if ($latex == 0) {
      if ($q_type == 'random') {
        $rnd_q_ids = array_merge($rnd_q_ids, random_utils::get_random_qids_for_question($q_id, $mysqli));
      } else {
        $latex = check_latex($leadin, $scenario, $option_text, $score_method, $correct_fback, $feedback_right);
      }
    }
    // Check for negative marking
    if ($marks_incorrect < 0) {
      $neg_marking = true;
    }

		if ($old_p_id != $p_id or $old_display_pos != $display_pos) {
      // Check for status that's excluded from marking
      $do_marking = ($row_no2 > 0 and !$status_array[$temp_array[$row_no2]['status']]->get_exclude_marking());

      if ($old_display_pos != -1) {
        $temp_array[$row_no2]['options'] = $options;
        if (empty($old_o_media)) {
          $temp_array[$row_no2]['o_media'] = array();
        } else {
          $temp_array[$row_no2]['o_media'] = $old_o_media;
        }
      }
      $options = 0;
      $tmp_exclude = $exclusions->get_exclusions_by_qid($old_q_id);
      if ($old_q_type == 'random') {
        $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
        if ($do_marking) {
          $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
          if (count($temp_array[$row_no2]['random']) > 0) {
            $total_random_mark += $temp_array[$row_no2]['random'][0]['random_mark'];
          }
        }
      } else {
        $temp_array[$row_no2]['original_marks'] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        if ($do_marking) {
          $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
          $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
        }
      }
      if ($do_marking) $total_marks += $temp_array[$row_no2]['marks'];
      $temp_array[$row_no2]['display_method'] = $old_display_method;
      $temp_array[$row_no2]['score_method'] = $old_score_method;
      if ($row_no2 > 0 and $properties->get_paper_type() < 3) {
        checkProblems($old_q_type, $temp_array, $row_no2, $tmp_exclude, $old_option_text, $old_correct, $string, $status_array, $old_settings, $properties, $mysqli);
      }
      $old_correct      = array();
      $old_option_text  = array();
      $old_o_media      = array();
      $old_marks = 0;
      $row_no2++;

      $row_no++;
      $temp_array[$row_no]['theme']           = $theme;
      $temp_array[$row_no]['screen']          = $screen;
      $temp_array[$row_no]['q_type']          = $q_type;
      $temp_array[$row_no]['fulltext']        = QuestionUtils::clean_leadin($leadin, 0);
      $temp_array[$row_no]['leadin']          = QuestionUtils::clean_leadin($leadin, $configObject->get('cfg_search_leadin_length'));
      $temp_array[$row_no]['scenario']        = $scenario;
      $temp_array[$row_no]['p_id']            = $p_id;
      $temp_array[$row_no]['q_id']            = $q_id;
      $temp_array[$row_no]['display_last_edited'] = $display_last_edited;
      $temp_array[$row_no]['q_media']         = $q_media;
      $temp_array[$row_no]['q_media_width']   = $q_media_width;
      $temp_array[$row_no]['q_media_height']  = $q_media_height;
      $temp_array[$row_no]['ownerID']         = $ownerID;
      $temp_array[$row_no]['display_pos']     = $display_pos;
      $temp_array[$row_no]['correct']         = $correct;
      $temp_array[$row_no]['status']          = $status;
      $temp_array[$row_no]['warnings']        = '';
      $temp_array[$row_no]['random']          = array();

      $q_mod_check[] = $q_id;

      if ($q_type == 'random') {
        $temp_array[$row_no]['random'] = randomDetails($q_id, $configObject, $mysqli);
      }

      // If summative paper is locked and the question is unlocked
      // - lock it
      if ($properties->get_summative_lock() and $locked == '') {
        QuestionUtils::lock_question($q_id, $mysqli);
      // If summative paper is not locked and the question is locked
      // - unlock it if it has not been answered by a student
      } elseif (!$properties->get_summative_lock() and $locked != '') {
        if (!QuestionUtils::question_answered_in_summative($p_id, $q_id, $mysqli)) {
          QuestionUtils::unlock_question($q_id, $mysqli);
        }
      }
    }
    $old_p_id           = $p_id;
    $old_q_id           = $q_id;
    $old_display_pos    = $display_pos;
    $old_q_type         = $q_type;
    $old_display_method = $display_method;
    $old_score_method   = $score_method;
    $old_correct[]      = $correct;
    $old_scenario       = $scenario;
    $old_q_media        = $q_media;
    $old_q_media_width  = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_settings       = $settings;
    $old_option_text[]  = $option_text;
    if (trim($o_media != '')) {
      $old_o_media[]    = $o_media;
    }
    $old_marks          = $marks_correct;

    if (!empty($option_text) or (!empty($correct) and (in_array($q_type, array('labelling', 'hotspot', 'area', 'true_false')))) or in_array($q_type, array('info', 'likert', 'flash', 'enhancedcalc'))) $options++;
  }
  $result->close();

	if (!$properties->get_summative_lock()) {
		$q_mod_check = array_unique($q_mod_check);
		if (count($q_mod_check) > 0) {
			$q_mod_found = QuestionUtils::multi_get_modules($q_mod_check, $mysqli);
			$paper_modules = Paper_utils::get_modules($paperID, $mysqli);
			foreach ($q_mod_check as $tmp_q_id) {
				foreach ($paper_modules as $p_mod_id => $mod) {
					if (!isset($q_mod_found[$tmp_q_id][$p_mod_id])) {
						QuestionUtils::add_modules($paper_modules, $tmp_q_id, $mysqli);			// Question is not on a module that the paper is assigned to so add.
						break;
					}
				}
			}
		}
	}

  if ($row_no > 0) {
    $temp_array[$row_no]['options'] = $options;
    $temp_array[$row_no]['o_media'] = $old_o_media;
    $tmp_exclude = $exclusions->get_exclusions_by_qid($old_q_id);

    // Check for status that's excluded from marking
    $do_marking = ($row_no2 > 0 and !$status_array[$temp_array[$row_no2]['status']]->get_exclude_marking());

    if ($old_q_type == 'random') {
      $temp_array[$row_no2]['original_marks'] = random_qMarks($temp_array[$row_no2]['random']);
      if ($do_marking) {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += isset($temp_array[$row_no2]['random'][0]['random_mark']) ?  $temp_array[$row_no2]['random'][0]['random_mark'] : 0;
      }
    } else {
      $temp_array[$row_no2]['original_marks'] = qMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      if ($do_marking) {
        $temp_array[$row_no2]['marks'] = $temp_array[$row_no2]['original_marks'];
        $total_random_mark += qRandomMarks($old_q_type, $tmp_exclude, $old_marks, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      }
    }
    if ($do_marking) $total_marks += $temp_array[$row_no2]['marks'];
    $temp_array[$row_no2]['display_pos'] = $old_display_pos;
    $temp_array[$row_no2]['score_method'] = $old_score_method;
    if ($properties->get_paper_type() < 3) {
      $tmp_exclude = $exclusions->get_exclusions_by_qid($old_q_id);

			checkProblems($old_q_type, $temp_array, $row_no2, $tmp_exclude, $old_option_text, $old_correct, $string, $status_array, $old_settings, $properties, $mysqli);
		}
		
    // If we had random questions on paper need to check if they need LaTeX
    if ($latex == 0 and count($rnd_q_ids) > 0) {
      $latex = check_latex_random($rnd_q_ids, $mysqli);
    }
		
    if ((round($total_random_mark, 4) != round($properties->get_random_mark(), 4) or $total_marks != $properties->get_total_mark() or $latex != $properties->get_latex_needed()) and $properties->get_paper_type() != '3') {   // Calculate random and total marks
      $update_params = array(
        'random_mark' => array('d', $total_random_mark),
        'total_mark' => array('i', $total_marks),
        'latex_needed' => array('i', $latex)
      );
      $assessment->db_update_assessment($paperID, $update_params);

      // Update standard set as marks has changed.
      $no_reviews = 0;
      $reviews = get_reviews($mysqli, 'index', $paperID, $total_marks, $no_reviews);
      foreach ($reviews as $review) {
        if ($review['method'] != 'Hofstee') {
          updateDB($review, $mysqli);
        }
      }
    }
  }
  try {
    require '../include/paper_options.php';
  } catch (Exception $e) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['problemwithpaper'], $msg, $string['problemwithpaper'], '/artwork/page_not_found.png', '#C00000', true, true);
  }
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">

<?php
  $exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
  $exam_announcements = $exam_announcementObj->get_announcements();

  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
  if ($module) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($module, $mysqli) . '</a>';
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/type.php?module=' . $module . '&type=' . $properties->get_paper_type() . '">' . Paper_utils::type_to_name($properties->get_paper_type(), $string) . '</a>';
  } elseif ($folder) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . folder_utils::get_folder_name($folder, $mysqli) . '</a>';
  } else {
    $paper_modules = Paper_utils::get_modules($paperID, $mysqli);  // Get the modules from paper properties
    reset($paper_modules);
    $moduleID = key($paper_modules);
    if ($moduleID != '') {
      $module_code = $paper_modules[$moduleID];
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $moduleID . '">' . $module_code . '</a>';
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/type.php?module=' . $moduleID . '&type=' . $properties->get_paper_type() . '">' . Paper_utils::type_to_name($properties->get_paper_type(), $string) . '</a>';
    }
  }
  echo '</div>';
  $title_class = 'page_title';
  if ($properties->get_retired() != '') {
    $title_class .= ' retired';
  }
  echo '<div onclick="qOff()" class="' . $title_class . '">' . $properties->get_paper_title() . '</div>';
  echo "</div>\n";
  
  echo "<table style=\"table-layout: fixed\" class=\"header\" id=\"sortable\">\n";
  // Blank row to preserve table layout when using table-layout: fixed - needed to increase IE8 latex rendering speed.
  echo "<tr><td class=\"icon\"></td><td class=\"q_no\"></td><td></td><td class=\"t\"></td><td class=\"m\"></td><td class=\"d\"></td></tr>";
  if ($properties->get_retired() == '') {
    echo "<tr>\n";
  } else {
    echo "<tr class=\"retired\">\n";
  }
  if ($userObject->has_role('Demo')) {
    $paper_owner = 'Mr J, Bloggs';
  }
  echo "<th colspan=\"3\" style=\"font-size:90%;padding-left:10px\"><strong>" . $string['start'] . ":</strong> ";
  if ($properties->get_start_date() == '') {
    echo '<span style="color:#808080">&lt;unscheduled&gt;</span>';
  } else {
    echo $properties->get_display_start_date();
  }
  echo "</th><th colspan=\"3\" style=\"text-align:right;font-size:90%\"><strong>" . $string['owner'] . ":</strong> $paper_owner&nbsp;</th></tr>\n";
  if ($properties->get_retired() == '') {
    echo '<tr class="details-head">';
  } else {
    echo '<tr class="details-head retired">';
  }
  ?>
    <th class="icon">&nbsp;</th>
    <th>&nbsp;</th>
    <th class="q-cell"><?php echo $string['question']; ?></th>
    <th class="t vert_div">&nbsp;<?php echo $string['type']; ?>&nbsp;</th>
    <th class="m vert_div">&nbsp;<?php echo $string['marks']; ?>&nbsp;</th>
    <th class="d vert_div">&nbsp;<?php echo $string['modified']; ?>&nbsp;</th>
    </tr>
   <?php
    $gradebook = new gradebook($mysqli);
    $graded = $gradebook->paper_graded($paperID);
    if ($graded) {
      echo "<tr><td colspan=\"2\"><div class=\"yellowwarn\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"32\" height=\"32\" alt=\"Published\" /></div></td><td colspan=\"4\" style=\"vertical-align:middle\"><div class=\"yellowwarn\">" . $string['paperpublishedwarning'] . " <a href=\"#\" class=\"blacklink\" onclick=\"launchHelp(189); return false;\">". $string['paperlockedclick'] ."</a></div></td></tr>\n";
    } else {
      if ($properties->get_summative_lock()) {
        echo "<tr><td colspan=\"2\"><div class=\"yellowwarn\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"32\" height=\"32\" alt=\"Locked\" /></div></td><td colspan=\"4\" style=\"vertical-align:middle\"><div class=\"yellowwarn\">" . $string['paperlockedwarning'] . " <a href=\"#\" class=\"blacklink\" onclick=\"launchHelp(189); return false;\">". $string['paperlockedclick'] ."</a></div></td></tr>\n";
      } elseif ($properties->get_paper_type() == '2' and $properties->get_start_date() !== null) {
        $tmp_hour = date("G", $properties->get_start_date());
        if (date("Y", $properties->get_start_date()) > (date("Y") + 1)) {
          echo "<tr><td colspan=\"2\" style=\"width:40px; line-height:0\" class=\"redwarn\"><img src=\"../artwork/late_warning_icon.png\" width=\"32\" height=\"32\" alt=\"Warning\" /></td><td colspan=\"4\" class=\"redwarn\">";
          printf($string['farfuturewarning'], $properties->get_display_start_date());
          echo "</td></tr>\n";
        } elseif ($tmp_hour < $configObject->get('cfg_hour_warning')) {
          echo "<tr><td colspan=\"2\" style=\"width:40px; line-height:0\" class=\"redwarn\"><img src=\"../artwork/late_warning_icon.png\" width=\"32\" height=\"32\" alt=\"Warning\" /></td><td colspan=\"4\" class=\"redwarn\">";
          printf($string['earlywarning'], $configObject->get('cfg_hour_warning'));
          echo "</td></tr>\n";
        }
      }
    }
	if ($properties->get_calendar_year() !== null) {
		$tmp_match = Paper_utils::academic_year_from_title($properties->get_paper_title());
		
		if ($tmp_match !== false and $tmp_match != $properties->get_calendar_year()) {
			echo "<tr><td colspan=\"6\" style=\"padding: 0\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%; font-size:100%\">\n";
			echo "<tr><td class=\"redwarn\" style=\"width:40px; line-height:0\"><img src=\"../artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\"Warning\" /></td><td colspan=\"7\" class=\"redwarn\"><strong>" . $string['warning'] . "</strong>&nbsp;&nbsp;";
			printf($string['nomatchsession'], $tmp_match, $properties->get_calendar_year());
			echo "</td></tr>\n</table>\n</td></tr>\n";
		}
	}

	$q_screen = array();
  $screen_marks = 0;
  $old_screen = 0;
  $question_number = 0;
  $marks_incorrect_error = false;
  $paper_warnings = array();
  $nooptions = array('keyword_based', 'random');
  for ($x=1; $x<=$row_no; $x++) {
    $status = $status_array[$temp_array[$x]['status']];
    if ($temp_array[$x]['options'] == 0 and isset($temp_array[$x]['o_media']) and count($temp_array[$x]['o_media']) == 0 and !in_array($temp_array[$x]['q_type'], $nooptions) and ($temp_array[$x]['q_type'] != 'textbox' or $temp_array[$x]['correct'] != 'placeholder')) {
      $temp_array[$x]['warnings'] .= $string['nooptionsdefined'];
    }
    if ($status->get_display_warning()) $paper_warnings['status'][$status->get_name()][] = $question_number + 1;
    if ($old_screen != $temp_array[$x]['screen']) {
      if ($old_screen > 0) {
        $tmp_screen_mean = ($total_marks == 0) ? 0 : ($screen_marks / $total_marks);
      }
      $screen_marks = 0;
      if ($old_screen < ($temp_array[$x]['screen'] - 1)) {
        for ($missing=1; $missing<($temp_array[$x]['screen'] - $old_screen); $missing++) {
          echo '<tr id="link_break' . ($old_screen + $missing) . '" class="breakline qline screenerror"><td colspan="6" class="ie-fullwidth"><h4><span class="opaque">' . $string['screen'] . '&nbsp' . ($old_screen + $missing) . '&nbsp;</span></h4></td></tr>';
          echo '<tr><td colspan="6" style="height:55px; background-image:url(../artwork/no_questions_gradient.png); repeat:repeat-x; color:white; background-color:#C00000; padding-left:15px; padding-top:4x">' . $string['noquestionscreen'] . '</td></tr>';
        }
      }
      echo '<tr id="link_break' . $temp_array[$x]['screen'] . '" class="breakline qline"><td colspan="6" class="ie-fullwidth"><h4><span class="subsect opaque">' . $string['screen'] . '&nbsp' . $temp_array[$x]['screen'] . '&nbsp;</span></h4></td></tr>';
    }
    $old_screen = $temp_array[$x]['screen'];

    $higlight_class = '';
    $status_class = '';
    if (!$status_array[$temp_array[$x]['status']]->get_exclude_marking() and $temp_array[$x]['marks'] == 0 and $temp_array[$x]['q_type'] != 'info' and $properties->get_paper_type() != '3' and $properties->get_paper_type() != '4' and $exclusions->get_exclusions_by_qid($temp_array[$x]['q_id']) != '0000000000000000000000000000000000000000') {
      $higlight_class = ' excluded';
    } else {
      $status_class = ' status' . $temp_array[$x]['status'];
    }

		$killer = 0;
		if (isset($killer_questions) and $killer_questions->is_killer_question($temp_array[$x]['q_id'])) {
			$killer = 1;
			$killer_class = ' killer_icon';
		} else {
			$killer_class = '';
		}
		
    $theme_class = '';
    $theme_str = '';
    if (trim($temp_array[$x]['theme']) != '') {
      $theme_class = ' q_theme';
      $theme_str = "<h4 class=\"theme\">" . trim($temp_array[$x]['theme']) . "</h4>\n";
    }

    echo "<tr id=\"link_$x\" onselectstart=\"return false\" onmousedown=\"if (typeof event.preventDefault != 'undefined') { event.preventDefault(); }\" class=\"link_$x qline{$theme_class}{$status_class}{$higlight_class}";

    $prevous_screen = '';
    $next_screen = '';
    if ($temp_array[$x]['q_type'] != 'info') {
      $q_screen[$temp_array[$x]['q_id']][] = ($question_number+1);
    }

    if (isset($temp_array[$x - 1]['screen'])) {
      $prevous_screen = $temp_array[$x - 1]['screen'];
    }
    $next_screen = '';
    if (isset($temp_array[$x + 1]['screen'])) {
      $next_screen = $temp_array[$x + 1]['screen'];
    }

    if ($properties->get_summative_lock()) {
      echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "',$x,'" . $temp_array[$x]['q_type'] . "'," . $temp_array[$x]['screen'] . "," . $temp_array[$x]['p_id'] . "," . $temp_array[$x]['display_pos'] . ",'2c'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number + 1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
    } else {
      echo "\" onclick=\"selQ(" . ($question_number+1) . ",'" . $temp_array[$x]['q_id'] . "',$x,'" . $temp_array[$x]['q_type'] . "'," . $temp_array[$x]['screen'] . "," . $temp_array[$x]['p_id'] . "," . $temp_array[$x]['display_pos'] . ",'2b'," . count($temp_array[$x]['random']) . ",event);\" ondblclick=\"edQ(" . ($question_number + 1) . "," . $temp_array[$x]['q_id'] . ",'" . $temp_array[$x]['q_type'] . "');\">";
    }

    if ($temp_array[$x]['q_type'] == 'info') {
			$info_class = ' info_class';
		} else {
			$info_class = '';
		}

    echo "<td id=\"icon_" . ($question_number+1) . "\" class=\"{$killer_class}{$info_class}\">";
    if ($temp_array[$x]['q_type'] == 'random') {
      $dice_no = rand(1, 6);
      if ($temp_array[$x]['leadin'] == '') {
        $temp_array[$x]['leadin'] = 'Random question block';
      }
      echo '<img src="../artwork/dice' . $dice_no . '.png" width="14" height="14" alt="folder" style="position:relative; left:1px;" />';
    } elseif ($temp_array[$x]['q_type'] == 'keyword_based') {
      echo '<img src="../artwork/keyword_q.png" width="14" height="14" alt="folder" style="position:relative; left:1px;" />';
    }
    echo '</td>';

    if ($temp_array[$x]['q_type'] == 'info') {
      echo '<td class="q_no"><img src="../artwork/black_white_info_icon.png" width="6" height="12" alt="Info" />&nbsp;</td>';
    } else {
      $question_number++;
      echo "<td class=\"q_no\">$question_number.</td>";
    }

    echo "<td class=\"l\" ";
    if (strlen($temp_array[$x]['fulltext']) > $configObject->get('cfg_search_leadin_length')) {
      echo ' onmouseover="showAdHocWindow(event,\''.htmlspecialchars($temp_array[$x]['fulltext']).'\');" ';
      echo ' onmouseleave="hideAdHocWindow();" ';
    }
    echo '>';
    echo $theme_str;
    if ($temp_array[$x]['q_type'] == 'random') {
      echo $temp_array[$x]['leadin'];
      if ($temp_array[$x]['warnings'] != '') {
        echo '<span class="q_warning">' . $temp_array[$x]['warnings'] . '</span>';
      }
    } elseif ($temp_array[$x]['leadin'] != '') {
      echo $temp_array[$x]['leadin'];
			if ($exclusions->get_exclusions_by_qid($temp_array[$x]['q_id']) != '0000000000000000000000000000000000000000') {
				echo ' <img src="../artwork/exclude_small.gif" width="15" height="11" alt="Excluded" />';
			}
      if (isset($exam_announcements[$temp_array[$x]['q_id']])) echo ' <img src="../artwork/comment_14_11.png" width="14" height="11" alt="Exam Clarification" />';
      if ($temp_array[$x]['warnings'] != '') echo '<div class="q_warning">' . $temp_array[$x]['warnings'] . '</div>';
    } elseif (strpos($temp_array[$x]['q_media'],'.swf') !== false) {
      echo "<img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" />";
    } elseif (strpos($temp_array[$x]['q_media'],'.flv') !== false) {
      echo "<img src=\"../artwork/flash_icon.png\" width=\"48\" height=\"48\" alt=\"Embedded Flash object\" border=\"0\" />";
    } else {
      echo "<img src=\"" . $mediadirectory->url($temp_array[$x]['q_media']) . "\" width=\"" . ($temp_array[$x]['q_media_width'] / 3) . "\" height=\"" . ($temp_array[$x]['q_media_height'] /3) . "\" alt=\"Media file\" border=\"1\" />";
    }
    echo "</td>";

    echo '<td class="t">';
    // Display position out of sync.
    if ($x <> $temp_array[$x]['display_pos']) {
      $temp_array[$x]['display_pos'] = $x;
      $editPaper = "UPDATE papers SET display_pos = $x WHERE p_id = " . $temp_array[$x]['p_id'];
      if (!$mysqli->query($editPaper)) {
        display_error("Paper order Error","Problem with query: $editPaper");
      }
    }

    echo $string[$temp_array[$x]['q_type']] . '</td>';
    if ($properties->get_paper_type() == '3' or $properties->get_paper_type() == '6') {
      echo '<td style="text-align:right; vertical-align:top; color:#C0C0C0">' . $string['na'] . '</td>';
    } elseif ($properties->get_paper_type() == '4') {
      $temp_array[$x]['score_method'] = str_replace('|',',',$temp_array[$x]['score_method']);
      $temp_array[$x]['score_method'] = str_replace(',false','',$temp_array[$x]['score_method']);
      echo '<td style="text-align:right; vertical-align:top">' . $temp_array[$x]['marks'] . '</td>';
    } elseif ($temp_array[$x]['q_type'] == 'info' or $temp_array[$x]['q_type'] == 'keyword_based') {
      echo '<td>&nbsp;</td>';
    } else {
      if (!$status_array[$temp_array[$x]['status']]->get_exclude_marking() and $temp_array[$x]['marks'] === 'ERR') {
        // Only ever get in here for random questions
        if (count($temp_array[$x]['marks']) > 0) {
          echo '<td style="text-align:right; vertical-align:top"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" title="' . $string['variablenomarks'] . '" alt="' . $string['variablenomarks'] . '" /></td>';
        }
        $marks_incorrect_error = true;
      } elseif ($status_array[$temp_array[$x]['status']]->get_exclude_marking()) {
        echo '<td style="text-align:right; vertical-align:top">' . $string['na'] . '</td>';
      } else {
        echo '<td class="m">' . $temp_array[$x]['marks'] . '</td>';
      }
    }
    if (!$status_array[$temp_array[$x]['status']]->get_exclude_marking()) {
    	$screen_marks += $temp_array[$x]['marks'];
    }
    echo '<td class="d">' . $temp_array[$x]['display_last_edited'] . '</td>';
    echo "</tr>\n";
    if ($temp_array[$x]['q_type'] == 'random') {
      $sub_question = 1;
      foreach ($temp_array[$x]['random'] as $random_question) {
        echo "<tr style=\"display:none\" ondblclick=\"edQ(" . $question_number . "," . $random_question['q_id'] . ",'" . $random_question['type'] . "');\" id=\"r" . $x . "_" . $sub_question . "\"><td></td><td></td><td class=\"s\">&#149&nbsp;" . $random_question['leadin'] . "</td><td class=\"t\">" . fullQuestionType($random_question['type'], $string) . "</td>";
        if ($temp_array[$x]['marks'] == 'ERR') {
          echo "<td class=\"errmk\">" . $random_question['marks'] . "</td>";
        } else {
          echo "<td class=\"m\">" . $random_question['marks'] . "</td>";
        }
        echo "<td class=\"d\">" . $random_question['display_last_edited'] . "</td></tr>\n";
        $sub_question++;
      }
    }
  }

  if ($total_marks != 0) {
    if ($row_no > 0 and $properties->get_paper_type() != '3' and $properties->get_paper_type() != '4') {
      echo "<tr><td colspan=\"4\"></td><td id=\"marks_total\" style=\"border-top:1px solid black; padding-right:4px\" align=\"right\">";
      if ($marks_incorrect_error == true) {
        echo '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['variablenomarks'] . '" />';
      } else {
        echo $total_marks;
      }
      echo "</td><td><nobr>&nbsp;&nbsp;" . $string['passmark'] . ":&nbsp;" . $properties->get_pass_mark() . "%&nbsp;</nobr></td></tr>\n";
      echo "<tr><td colspan=\"4\"></td><td style=\"color:#808080; text-align:right\">" . round($total_random_mark, 2) . "&nbsp;</td><td style=\"color:#808080\">(" . round(((round($total_random_mark, 2) / $total_marks) * 100), 0) . "%) " . $string['randommark'] . "</td></tr>\n";
    }
  }

  if ($properties->get_paper_type() != '3') {
    check_duplicates($q_screen, $string);
  }

  // Final paper warnings.
  if ($properties->get_paper_type() == '2') {
    if (isset($paper_warnings['status']) and count($paper_warnings['status']) > 0) {
      $first = true;
      echo "<tr><td colspan=\"2\" class=\"warnicon\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" /></td><td colspan=\"4\" class=\"warn\"><strong>" . $string['following_questions'] . ":</strong> ";
      foreach ($paper_warnings['status'] as $name => $warn_qs) {
        if (!$first) {
          echo ', ';
        }
        echo "<strong>'$name'</strong> Q" . implode(', Q', $warn_qs) ;
        $first = false;
      }
      echo "</td></tr>\n";
    }
  }

    if (!$properties->get_summative_lock()) {
        if ($properties->get_marking() == 1 and $neg_marking == true) {     // Can't use random mark with negative marking
            $update_params = array(
            'marking' => array('s', 0)
            );
            $assessment->db_update_assessment($paperID, $update_params);
        }
    }
?>
</table>

<?php
  ob_flush();
  flush();
	
  if ($properties->get_recache_marks() == 1 and count($temp_array) > 0) {
    $startdate = $properties->get_raw_start_date();
    $enddate   = $properties->get_raw_end_date();
?>
    <script>
      $.post('../reports/recache_class_totals.php', {paperID: '<?php echo $paperID; ?>', startdate: '<?php echo $startdate; ?>', enddate: '<?php echo $enddate; ?>'});  // AJAX off to class totals to recache marks.
    </script>
<?php
    $properties->set_recache_marks(0);  // Set the recache to zero to stop it caching again.
    $properties->save();
  }

  $mysqli->close();
?>

<div id="response"></div>
</div>

</body>
</html>

