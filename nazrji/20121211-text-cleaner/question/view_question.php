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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/question_types.inc';
  require '../include/display_functions.inc';
  require '../include/media.inc';

  $marks_color = '#808080';
  $themecolor = '#316AC5';
  $labelcolor = '#C00000';
  $textsize = 100;
  $question_offset = 0;
  $question_no = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Preview</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />

  <script type="text/javascript" src="../js/flash_include.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.flash_q.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script language="JavaScript">
    function write_string(p_string) {
      document.write(p_string);
    }
  </script>
</head>
<body>
<div id="maincontent">
<?php
  $old_q_id = '';
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $_GET['q_id']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
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
  $unanswered = false;
  $user_answers[1] = array();
  display_question($question, $paper_type, 1, '', $question_no, $question_offset, $user_answers, $unanswered);	

  $question_nos[] = $old_q_id;
  echo "<table>\n";
 ?>
 </div>
 </body>
 </html>
 