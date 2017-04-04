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

require '../include/staff_auth.inc';
require './display_functions.inc';
require '../include/errors.php';
require '../include/media.inc';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

check_var('id', 'GET', true, false, false);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$start_of_day_ts = strtotime('midnight');

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$marking 							= $propertyObj->get_marking();
$paperID 							= $propertyObj->get_property_id();
$start_date						= $propertyObj->get_start_date();
$paper_type						= $propertyObj->get_paper_type();
$original_paper_type	= $propertyObj->get_paper_type();
$paper_prologue 			= $propertyObj->get_paper_prologue();

require '../config/start.inc';

// Get how many screens make up the question paper.
$screen_data = array();
$stmt = $mysqli->prepare("SELECT screen, q_type, question FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id ORDER BY screen, display_pos");
$stmt->bind_param('i', $paperID);
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

// Determine which review deadline to use.
if ($userObject->has_role('External Examiner')) {
	$review_type = 'External';
	$review_deadline = strtotime($propertyObj->get_external_review_deadline());
} else {
	$review_type = 'Internal';
	$review_deadline = strtotime($propertyObj->get_internal_review_deadline());
}

// Create a new review object.
$review = new Review($paperID, $userObject->get_user_ID(), $review_type, $mysqli);

// Get standards setting data
if ($marking{0} == '2') {
  $standards_setting = array();
  $tmp_parts = explode(',', $marking);
  
  $standard_setting = new StandardSetting($mysqli);
  $standards_setting = $standard_setting->get_ratings_by_question($tmp_parts[1]);
} else {
  $standards_setting = array();
}

/**
 * Get randmon question
 *
 * @param array $questions - temp array of questions
 * @param array $random_q_data - a question
 * @param int $q_no - question index in array
 * @param array $used_questions - previsouly used questions
 * @param db $mysqli
 */
function randomQOverwrite(&$questions, $random_q_data, $q_no, &$used_questions, $mysqli) {
  $try = 0;
  $unique = false;
  while ($unique == false and $try < 9999) {
    $selected_q_id = random_utils::generate_random_qid_from_block($random_q_data['q_id'], $mysqli);
    if (!isset($used_questions[$selected_q_id])) $unique = true;
    $try++;
  }
  $used_questions[$selected_q_id] = 1;
  

  // Look up selected question and overwrite data.
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect"
    . ", marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width,"
    . " q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND"
    . " questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $selected_q_id);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect,
    $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height,
    $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
  while ($question_data->fetch()) {
    if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
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
  if (isset($question)) {
    $questions[] = $question;
    echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
  }
}

/**
 * Get keyword question
 *
 * @param array $questions - temp array of questions
 * @param array $random_q_data - a question
 * @param int $q_no - question index in array
 * @param array $used_questions - previsouly used questions
 * @param db $mysqli
 */
function keywordQOverwrite(&$questions, $random_q_data, $q_no, &$used_questions, $mysqli) {
  // Get the keyword id.
  $keyword_id = keyword_utils::get_keywordid_for_question($random_q_data['q_id'], $mysqli);
  // Generate a random question ID from keywords.
  $question_ids = array();
  $question_data = $mysqli->prepare("SELECT DISTINCT k.q_id FROM keywords_question k, questions q WHERE k.q_id = q.q_id AND"
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
  
  if ($unique) {
    // Look up selected question and overwrite data.
    $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect,"
      . " marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width,"
      . " q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND"
      . " questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect,
      $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height,
      $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    while ($question_data->fetch()) {
      if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
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
    echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
  } else {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_keywords'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['display_pos'] = $q_no;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'][] = array();
  }
  $questions[] = $question;
}

/*
*
* Load any Reference Material into an array.
* @param int $paperID - ID of the current paper
* @param object $db   - Mysqli object
* @return array				- Array of all reference material relevant to the current paper.
*/
function load_reference_materials($paperID, $db) {
	$reference_materials = array();
	$ref_no = 0;
	$stmt = $db->prepare("SELECT title, content, width FROM (reference_material, reference_papers) WHERE reference_material.id = reference_papers.refID AND paperID = ?");
	$stmt->bind_param('i', $paperID);
	$stmt->execute();
	$stmt->bind_result($reference_title, $reference_material, $reference_width);
	while ($stmt->fetch()) {
		$reference_materials[$ref_no]['title']		= $reference_title;
		$reference_materials[$ref_no]['material']	= $reference_material;
		$reference_materials[$ref_no]['width']		= $reference_width;
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

// Load any reference materials.
$reference_materials	= load_reference_materials($paperID, $mysqli);
$max_ref_width 				= get_max_reference_width($reference_materials);

// Extract the posted variables.
$current_screen = 1;
if (isset($_POST['next'])) {
  $current_screen = $_POST['current_screen'];
} elseif (isset($_POST['prev'])) {
  $current_screen = $_POST['current_screen'] - 2;
} elseif (isset($_POST['jump_screen'])) {
  $current_screen = $_POST['jump_screen'];
}

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">

<title><?php echo $propertyObj->get_paper_title() ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />
<link rel="stylesheet" type="text/css" href="../css/review.css" />
<style type="text/css">
  .var {font-weight: bold}
  .value {display:none}
<?php
$css = '';

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
if (count($reference_materials) > 0) {
  $css .= "#maincontent {position:fixed; right:" . ($max_ref_width + 1) . "px}\n";
  $css .= ".framecontent {width:" . ($max_ref_width - 12) . "px}\n";
  $css .= ".refhead {width:" . ($max_ref_width - 12) . "px;}\n";
}
if ($css != '') {
  echo $css;
}
?>

</style>

<script type="text/javascript" src="start.js"></script>
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
    if (isset($_COOKIE['refpane'])) {
      echo "  changeRef(" . $_COOKIE['refpane'] . ");\n";
    } else {
      echo "  resizeReference();\n";
    }
    echo "});\n";
  }
?>

var lang = {
  <?php
  $langstrings = array('javacheck2','msgselectable1','msgselectable2','msgselectable3','msgselectable4');
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

  var changeRef = function(refID) {
    $('#refpane').val(refID);
		winH = $(window).height();
    resizeReference();
    var flag = 0;
    <?php
      if (count($reference_materials) > 0) {
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
        echo "          $('#refhead' + i).css('top', (winH - (" . count($reference_materials) . " - i) * 31) + 'px');\n";
        echo "        }\n";
        echo "      }\n";
        echo "    }\n";
      }
    ?>
  }

  function resizeReference() {
		winH = $(window).height();
<?php
    if (count($reference_materials) > 0) {
      $subtract = (31 * count($reference_materials)) + 11;
      echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
      echo "      $('#framecontent' + i).css('height', (winH - $subtract) + 'px');\n";
      echo "    }\n";
    }
?>
    var mainWidth = $('body').outerWidth() - $('#framecontent0').outerWidth(true);
    $('#maincontent').width(mainWidth);
  }
<?php
  if ($propertyObj->get_bidirectional() == 0) {
?>
  function confirmSubmit() {
    var agree = confirm("<?php echo $string['confirmsubmit'] ?>");
    if (agree) {
      $('body').css('cursor','wait');
      return true;
    } else {
      return false;
    }
  }
<?php
  } else {
  }
?>
  $(function () {
    $(function() {
      $('.reveal').click(function() {
        $('.var').toggle();
        $('.value').toggle();
      });
    });

    $('#jumpscreen').change(function () {
      $('#button_pressed').val('jump_screen');
      $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id'] ?>&dont_record=true");
      $('#qForm').submit();
    });
    
    $('#previous').click(function() {
      $('body').css('cursor','wait');
      $('#qForm').attr('action', '<?php $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']?>');
    });
    
    $('#next').click(function() {
      $('body').css('cursor','wait');
    });
    
    $('#finish').click(function() {
      $('body').css('cursor','wait');
    });
    
    $('#qForm').submit(function(e) {
      $('.commentsbox').each(function() {
        if ($(this).val() != '') {
          var commentID = $(this).attr('id');
          var commentNo = commentID.substr(11);
          if ( $('input[name=exttype' + commentNo + ']:checked', '#qForm').val() == undefined) {
            alert("Please select one of the radio buttons for question " + commentNo);
            $('body').css('cursor','default');
            e.preventDefault();
          }
        }
      });      
      
      
    });
  });
</script>
</head>
<body onload="StartClock()" onunload="KillClock()">
<div id="maincontent">
<?php
if ($current_screen < $no_screens) {
  echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'];
} else {
  echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'];
}
echo '" onsubmit="return confirmSubmit()" autocomplete="off">';   // Warning message only in linear navigation mode.
?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php
  if (isset($_POST['old_screen']) and (($_POST['old_screen'] != '' and $start_of_day_ts <= $review_deadline and time() <= $start_date) or $start_date == '')) {
    $review->record_comments($_POST['old_screen']);
  }

  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div>';
  $question_offset = 0;
  if ($no_screens > 1) {
    for ($i=1; $i<=$no_screens; $i++) {
      if ($i == $current_screen) {
        echo '<div class="scr_cur"';
      } else {
        echo '<div class="scr_ans"';
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
  
  if (($start_of_day_ts > $review_deadline or time() > $start_date) and $start_date != '') {
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td class=\"redwarn\" style=\"width:40px; line-height:0\"><img src=\"../artwork/late_warning_icon.png\" width=\"32\" height=\"32\" alt=\"Clock\" /></td><td class=\"redwarn\"><strong>{$string['deadlineexpired']}</strong>&nbsp;&nbsp;&nbsp;{$string['deadlinepassed']}</td></tr></table>\n";  
  }
  
  $previous_duration = 0;
  $screen_pre_submitted = 0;
	
	// Load past reviews from the database.
	$review->load_reviews();
	
  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';

  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, correct_fback, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, display_pos, q_option_order FROM papers, questions LEFT JOIN options ON questions.q_id = options.o_id WHERE paper = ? AND screen = ? AND papers.question = questions.q_id ORDER BY display_pos, id_num");
  $question_data->bind_param('ii', $paperID, $current_screen);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $correct_fback, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $q_no = 0;
  // Build the questions_array
  $used_questions = array();

  while ($question_data->fetch()) {
    if ($q_no == 0 or $questions_array[$q_no]['q_id'] != $q_id or $questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      $questions_array[$q_no]['theme'] = trim($theme);
      $questions_array[$q_no]['scenario'] = trim($scenario);
      $questions_array[$q_no]['leadin'] = trim($leadin);
      $questions_array[$q_no]['notes'] = trim($notes);
      $questions_array[$q_no]['q_type'] = $q_type;
      $questions_array[$q_no]['q_id'] = $q_id;
      $questions_array[$q_no]['display_pos'] = $display_pos;
      $questions_array[$q_no]['score_method'] = $score_method;
      $questions_array[$q_no]['display_method'] = $display_method;
      $questions_array[$q_no]['settings'] = $settings;
      $questions_array[$q_no]['q_media'] = $q_media;
      $questions_array[$q_no]['q_media_width'] = $q_media_width;
      $questions_array[$q_no]['q_media_height'] = $q_media_height;
      $questions_array[$q_no]['q_option_order'] = $q_option_order;
      $questions_array[$q_no]['correct_fback'] = $correct_fback;
      $questions_array[$q_no]['dismiss'] = '';
      $used_questions[$q_no] = 1;
      if (isset($standards_setting[$q_id])) $questions_array[$q_no]['std'] = $standards_setting[$q_id];
    }
    $questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $question_data->close();

  // Random / Keyword questions.
  $tmp_questions_array = array();
  $tmp_q_no = 0;
  foreach ($questions_array as &$question) {

    if ($question['q_type'] != 'info') {
      $tmp_q_no++;
    }
    if ($question['q_type'] == 'random') {
        randomQOverwrite($tmp_questions_array, $question, $tmp_q_no, $used_questions, $mysqli);
    } elseif ($question['q_type'] == 'keyword_based') {
        keywordQOverwrite($tmp_questions_array, $question, $tmp_q_no, $used_questions, $mysqli);
    } else {
      $tmp_questions_array[] = $question;
    }

  }
  unset($questions_array);

  // Display the questions
  foreach ($tmp_questions_array as &$question) {
    if ($question['q_type'] == 'enhancedcalc') {
      require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
      if (!isset($configObj)) {
        $configObj = Config::get_instance();
      }
      $question['object'] = new EnhancedCalc($configObj);
      $question['object']->load($question);
    }

    if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr><td colspan=\"2\"><span style=\"background-color:#FFC0C0\">&nbsp;&nbsp;&nbsp;&nbsp;</span> = unanswered question</td></tr>\n";
    if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    
    display_question($configObject, $question, $propertyObj->get_paper_type(), $propertyObj->get_calculator(), $current_screen, $previous_q_type, $question_no, $question_offset, $start_of_day_ts);
    $previous_q_type = $question['q_type'];
    $q_displayed++;
  }
  
  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"\" />\n";

  echo $bottom_html;
  echo '<input type="text" style="background-color:transparent; text-align:center; color:white; border:0" id="theTime" size="8" /></td><td align="right">';
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
      echo "</select>";
    }
  }
  if ($current_screen > $no_screens) {
  	echo '<input id="finish" type="submit" name="next" value="' . $string['finish'] . '" />';
  } else {
    echo '<input id="next" type="submit" name="next" value="' . $string['screen'] . ' ' . $current_screen . ' &gt;" />';
  }
  echo '</td></tr></table>';

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
    $top += 31;
    $ref_no++;
  }
}
$mysqli->close();

if (isset($_COOKIE['refpane'])) {
  echo "<script>\n";
  echo "  changeRef(" . $_COOKIE['refpane'] . ");\n";
  echo "</script>\n";
}
?>

</body>
</html>