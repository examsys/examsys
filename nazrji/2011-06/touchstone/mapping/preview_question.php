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
* @author Simon Wilkinson, Anthony Brown
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
  $question_offset = 1;
  $question_no = 1;
?>
<html>
<head>
<title>Preview</title>
<style type="text/css">
  body {background-color:white; color:black; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
  li {margin-left:15px; margin-right:15px; font-size:100%}
  select, input {font-family:Arial,sans-serif; font-size:100%}
  table {font-size:100%}
  pre {font-family:Arial,sans-serif; font-size:100%}
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
  function displayQuestion($mysqlidb) {
    $question_no = $_GET['qNo'] - 1;
    $old_q_id = 0;
    
    $question_data = $mysqlidb->prepare("SELECT q_type, q_id, score_method, marks, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $_GET['q_id']);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $marks, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
    $num_rows = $question_data->num_rows;
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
    echo "<col width=\"40\"><col>\n";
    while ($row = $question_data->fetch()) {
      if ($old_q_id != $q_id) {
        $tmp_questions_array[0]['theme'] = trim($theme);
        $tmp_questions_array[0]['scenario'] = trim($scenario);
        $tmp_questions_array[0]['leadin'] = trim($leadin);
        $tmp_questions_array[0]['notes'] = trim($notes);
        $tmp_questions_array[0]['q_type'] = $q_type;
        $tmp_questions_array[0]['q_id'] = $q_id;
        $tmp_questions_array[0]['display_pos'] = $display_pos;
        $tmp_questions_array[0]['score_method'] = $score_method;
        $tmp_questions_array[0]['q_media'] = $q_media;
        $tmp_questions_array[0]['q_media_width'] = $q_media_width;
        $tmp_questions_array[0]['q_media_height'] = $q_media_height;
        $tmp_questions_array[0]['dismiss'] = $dismiss;
      }
      $tmp_questions_array[0]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks'=>$marks);
    }
    $question_data->close();

    //look for brabching and random questions and overwrite as needed
    $questions_array = array();
    foreach ($tmp_questions_array as &$question) {
      if ($question['q_type'] == 'random') {
        randomQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen);
      } elseif ($question['q_type'] == 'branching') {
        branchingQOverwrite($questions_array,$question,$paper_type,$user_answers,$current_screen);	  
      } else {
        $questions_array[] = $question;
      }
    }
    unset($tmp_questions_array);
  
    //display the questions
    foreach($questions_array as &$question) {
      if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr><td colspan=\"2\"><span style=\"background-color:#FFC0C0\">&nbsp;&nbsp;&nbsp;&nbsp;</span> = unanswered question</td></tr>\n";
      if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
      if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      display_question($question, $paper_type, $current_screen, $previous_q_type, $question_no, $question_offset, $user_answers);	
      $previous_q_type = $question['q_type'];
      $q_displayed++;
    }
    echo "<table>\n";
  }

  displayQuestion($mysqli);
?>
 </body>
 </html>
