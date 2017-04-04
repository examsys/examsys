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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/question_types.php';
require '../include/mapping.inc';
require '../include/errors.php';
require '../include/display_functions.inc';
require '../include/media.inc';
$jstring = $string; //to pass it to JavaScript HTML5 modules
$paperID = check_var('paperID', 'REQUEST', true, false, true);

if (file_exists($cfg_web_root . "lang/$language/paper/start.php")) {
  require $cfg_web_root . "lang/$language/paper/start.php";
  require $cfg_web_root . "lang/$language/question/edit/index.php";
}

function display_q($configObject, $target_id, $db) {
  $question_data = $db->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM questions LEFT JOIN options ON questions.q_id = options.o_id WHERE q_id = ? ORDER BY id_num");
  $question_data->bind_param('i', $target_id);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $settings, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $old_q_id  = 0;
  while ($question_data->fetch()) {
    if ($old_q_id != $q_id) {
      $question['theme'] = trim($theme);
      $question['scenario'] = trim($scenario);
      $question['leadin'] = trim($leadin);
      $question['notes'] = trim($notes);
      $question['q_type'] = $q_type;
      $question['q_id'] = $q_id;
      $question['score_method'] = $score_method;
      $question['display_method'] = $display_method;
      $question['settings'] = $settings;
      $question['q_media'] = $q_media;
      $question['q_media_width'] = $q_media_width;
      $question['q_media_height'] = $q_media_height;
      $question['dismiss'] = '';
      $question['assigned_number'] = $_GET['qNo'];
    }
    $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $question_data->close();

  $question_no	= 0;
  $paper_type		= 0;
  $unanswered 	= false;

  $question_offset = $_GET['qNo'];

  $screen_pre_submitted = 0;
  $user_answers = array();

	if ($question['q_type'] == 'enhancedcalc') {
		require_once('../plugins/questions/enhancedcalc/enhancedcalc.class.php');
		if (!isset($configObj)) {
			$configObj = Config::get_instance();
		}
		$question['object'] = new EnhancedCalc($configObj);
		$question['object']->load($question);
	}
  
  display_question($configObject, $question, $paper_type, 0, 1, '', $question_no, $user_answers, $unanswered);

  $question_nos[] = $old_q_id;
  echo "</table>\n";
}

$render = new render($configObject);
$render->render_html5_js(json_encode($jstring));
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['objectivemapping']; ?></title>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.mappingform.js"></script>
  <script>
    $(function () {
      $('#cancel').click(function() {
        window.close();
      });
    });  
  </script>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" href="../css/start.css" type="text/css" />
  <link rel="stylesheet" href="../css/mapping_form.css" type="text/css" />
  <style>
    .objheading {font-size:150%; font-weight:bold; color:#316AC5; padding-top:10px; border-top:1px solid #C0C0C0}
  </style>
</head>
<body>
  <div id="maincontent">
<?php

if (isset($_POST['submit'])) {
  // Write out curriculum mapping.
  save_objective_mappings($mysqli, $_POST['objective_modules'], $_POST['paperID'], $_POST['questionID']);
  ?>
  <script>
    window.opener.location = window.opener.location;
    window.close();
  </script>
  <?php
} else {
  display_q($configObject, $_GET['q_id'], $mysqli);

  echo "<div id=\"obj_form\">\n";
  echo "<form method=\"post\" autocomplete=\"off\">";
  try {
    echo render_objectives_mapping_form($mysqli, $paperID, $string);
  } catch(Exception $e) {
  $title = $string['objectives_error'];
  if (isset($string[$e->getMessage()])) {
    $message = $string[$e->getMessage()];
  } else {
    $message = $e->getMessage();
  }
  display_error($title, $message, false);
}
  echo "<br />";
  echo "<input type=\"hidden\" name=\"paperID\" value=\"$paperID\" />\n";
  echo "<input type=\"hidden\" name=\"questionID\" value=\"{$_GET['q_id']}\" />\n";
  echo "<div style=\"text-align:center; width:100%\"><input type=\"submit\" name=\"submit\" value=\"" . $string['save'] . "\" class=\"ok\" /><input class=\"cancel\" id=\"cancel\" type=\"button\" value=\"" . $string['cancel'] . "\" /></div>";

  echo "</form>\n</div>\n";
}
?>
  </div>
</body>
</html>