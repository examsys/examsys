<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/question_types.inc';
  require '../include/display_functions.inc';
  require '../include/media.inc';

  $marks_color = '#808080';
  $themecolor = '#316AC5';
  $labelcolor = '#C00000';
  $question_offset = 0;
  $question_no = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Preview</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<style type="text/css">
  body {background-color:white; color:black; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
  li {margin-left:15px; margin-right:15px; font-size:100%}
  select, input {font-family:Arial,sans-serif; font-size:100%}
  table {font-size:100%}
  pre {font-family:Arial,sans-serif; font-size:100%}
  p {margin-top:0px; padding-top:0px}
  .paper {margin-left:0px; font-size:180%; color:white; font-weight:bold}
  .q_no {width:40px; text-align:right; vertical-align:top}
  .theme {font-size:150%; padding-left:4px; font-weight:bold; color:#316AC5}
  .note {font-size:80%; color:#C00000}
  .mk {color:#808080; font-size:80%}
  .act {color:black}
  .inact {color:#C0C0C0}
  .s0 {width:18px; text-align:center; background-color:#003366; font-size:80%}
  .s1 {width:18px; text-align:center; background-color:#C00000; font-size:80%}
  .likert_button {text-align:center; width:40px; vertical-align:top}
  .unans {background-color:#FFC0C0}
</style>

<script language="JavaScript" src="../javascript/flash_include.js"></script>
<script src="/touchstone/tools/MathJax/MathJax.js"> 
  MathJax.Hub.Config({
    showProcessingMessages: false,
	menuSettings: {zoom:"none"},
    extensions: ["tex2jax.js"],
    jax: ["input/TeX","output/HTML-CSS"],
	preRemoveClass: "MathJax_Preview",
    tex2jax: {
	    showProcessingMessages: false,
	    inlineMath: [["[tex]","[/tex]"],["[tex]","[/tex]"]],
		preview: "none"
	},
	"HTML-CSS": { scale: <?php echo ($textsize + 30); ?>,
	              showMathMenu: false,
	              availableFonts: ["TeX"] 
				}
  });
</script>
<script language="JavaScript">
function write_string(p_string) {
  document.write(p_string);
}
</script>
</head>
<body>
<?php
  $old_q_id = '';
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, marks, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $_GET['q_id']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $marks, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  while ($row = $question_data->fetch()) {
    if ($old_q_id != $q_id) {
      $question['theme'] = trim($theme);
      $question['scenario'] = trim($scenario);
      $question['leadin'] = trim($leadin);
      $question['notes'] = trim($notes);
      $question['q_type'] = $q_type;
      $question['q_id'] = $q_id;
      $question['score_method'] = $score_method;
      $question['q_media'] = $q_media;
      $question['q_media_width'] = $q_media_width;
      $question['q_media_height'] = $q_media_height;
      $question['dismiss'] = '';
    }
    $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks'=>$marks);
  }
  $question_data->close();
  
  $question_no = 0;
  $paper_type = 0;
  $user_answers[1] = array();
  display_question($question, $paper_type, 1, '', $question_no, $question_offset, $user_answers);	

  $question_nos[] = $old_q_id;
  echo "<table>\n";
 ?>
 </body>
 </html>
 