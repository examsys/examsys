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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/question_types.inc';
require '../include/mapping.inc';
require '../include/display_functions.inc';

if (file_exists($cfg_web_root . "lang/$language/paper/start.php")) {
  require $cfg_web_root . "lang/$language/paper/start.php";
}
require '../include/media.inc';
$paperID = $_GET['paperID'];

function display_q($mysqlidb) {
  global $bgcolor;
  $question_data = $mysqlidb->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $_GET['q_id']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
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
      $question['q_media'] = $q_media;
      $question['q_media_width'] = $q_media_width;
      $question['q_media_height'] = $q_media_height;
      $question['dismiss'] = '';
    }
    $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $question_data->close();
  
  $question_no = 0;
  $paper_type = 0;
  $bgcolor = 'white';
  $unanswered = false;
  display_question($question, $paper_type, 1, '', $question_no, $question_offset, array(), $unanswered);	
  $question_nos[] = $old_q_id;
  echo "</table>\n";
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Objective Mapping</title>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/mapping_tab.js"></script>
  <script type="text/javascript" src="../js/flash_include.js"></script>
  <script type="text/javascript" src="../js/ie_fix.js"></script>
  <script type="text/javascript" src="../js/jquery.flash_q.js"></script>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%}
    h1 {font-size:150%; font-weight:bold; color:#316AC5; margin-left:15px; padding-top:10px}
    p {margin-top:0px; padding-top:0px}
    .paper {margin-left:0px; font-size:180%; color:white; font-weight:bold}
    .q_no {width:40px; text-align:right; vertical-align:top}
    .theme {font-size:150%; padding-left:4px; font-weight:bold; color:#316AC5}
    .notes {font-size:80%; color:#C00000}
    .mk {color:#808080; font-size:80%}
  </style>
</head>
<body>
<?php

if (isset($_POST['submit']) AND $_POST['submit'] == 'Save Changes') {
  // Write out curriculum mapping.
  saveObjMappings($_POST['paperID'],$_POST['questionID'],$mysqli);
  ?>
  <script language="JavaScript">
    window.opener.location = window.opener.location;
    window.close();
  </script>
  <?php
} else {
  display_q($mysqli);

  echo "<div style=\"margin-left:10px\">\n";
  echo "<form method=\"post\">";
  echo displayObjectivesMappingForm($paperID, $mysqli, $configObject->get('cfg_root_path'));
  echo "<br />";
  echo "<div style=\"text-align:center; width:100%\"><input type=\"submit\" name=\"submit\" value=\"Save Changes\" />&nbsp;";
  echo "<input style=\"width:120px\" type=\"button\" value=\"Cancel\" onclick=\"window.close()\"/></div>";

  echo "</form>\n</div>\n";
}
?>
</body>
</html>