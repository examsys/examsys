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

  require '../../include/staff_auth.inc';
  require '../../include/errors.inc';
  require '../../include/media.inc';
  require '../../include/question_types.inc';
  
  $paper = $_GET['paper'];
?>
  <html>
  <head>
  <title>Add new Question</title>
  <style>
    body {margin:0px; font-family:Arial,sans-serif}
    p, td {font-family:Arial,sans-serif; font-size:80%; color:black}
  </style>
  <script type="text/javascript" src="/js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="/tools/mee/mee/js/mee_src.js"></script>
  </head>

  <body>
<?php

  if (isset($_GET['display_pos'])) {
    $display_pos = $_GET['display_pos'];
  } else {
    $display_pos = 1;
  }

  echo "<form name=\"theform\" method=\"post\" action=\"do_add_questions.php?paper=$paper&display_pos=$display_pos\">\n";
  echo "<input type=\"hidden\" name=\"screen\" value=\"1\" />\n";
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr style=\"background-color: #EBEADB\"><td>&nbsp;</td><td><img src=\"header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;xxx Question&nbsp;</td><td><img src=\"header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Type&nbsp;</td><td><img src=\"header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Modified&nbsp;</td></tr>\n";
  echo "<tr style=\"height: 4px\"><td valign=\"top\" colspan=\"4\"><img src=\"header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  
  $id = 0;
  $old_leadin = '';
  $old_q_id = 0;
  $query_string = "SELECT question, q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS last_edited FROM papers RIGHT JOIN questions ON papers.question=questions.q_id WHERE questions.owner='" . $_SERVER['PHP_AUTH_USER'] . "' ORDER BY last_edited, q_id";
  //echo $query_string;
  $question_data = mysql_query($query_string);
  $question_array = array();
  while ($row = mysql_fetch_array($question_data)) {
    if ($row['leadin'] != $old_leadin or ($old_q_id != $row['q_id'] and $row['q_type'] != 'matching' and $row['q_type'] != 'multimatching')) {
      $question_array[$id]['q_id'] = $row['q_id'];
      $question_array[$id]['q_media'] = $row['q_media'];
      $question_array[$id]['q_media_width'] = $row['q_media_width'];
      $question_array[$id]['q_media_height'] = $row['q_media_height'];
      $question_array[$id]['leadin'] = $row['leadin'];
      $question_array[$id]['q_type'] = $row['q_type'];
      $question_array[$id]['question'] = $row['question'];
      $question_array[$id]['last_edited'] = $row['last_edited'];
      $id++;
    }
    $s = $row['leadin'];
    $old_q_id = $row['q_id'];
  }

  for ($i=0; $i<$id; $i++) {
    if ($question_array[$i]['question'] == NULL) {
      if ($question_array[$i]['leadin'] == '') {
        echo "<tr><td><input type=\"checkbox\" name=\"" . $question_array[$i]['q_id'] . "\" value=\"" . $question_array[$i]['q_id'] . "\" /></td><td><img src=\"" . $question_array[$i]['q_media'] . "\" width=\"" . $question_array[$i]['q_media_width'] . "\" height=\"" . $question_array[$i]['q_media_height'] . "\" alt=\"Media file\" /></td><td>&nbsp;" . fullQuestionType($question_array[$i]['q_type']) . "</td><td>&nbsp;" . $question_array[$i]['last_edited'] . "</td></tr>\n";
      } else {
        echo "<tr><td><input type=\"checkbox\" name=\"" . $question_array[$i]['q_id'] . "\" value=\"" . $question_array[$i]['q_id'] . "\" /></td><td>&nbsp;" . strip_tags($question_array[$i]['leadin']) . "</td><td>&nbsp;" . fullQuestionType($question_array[$i]['q_type']) . "</td><td>&nbsp;" . $question_array[$i]['last_edited'] . "</td></tr>\n";
      }
    }
  }
?>
</table>
</form>
</body>
</html>