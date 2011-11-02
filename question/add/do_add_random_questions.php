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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../../include/staff_auth.inc';
?>
<html>
<head>
<title>Add new Question</title>
<script language="javascript">
  <?php
    $newHTML = '';
    $question_no = 0;
    $questions = explode(',',$_POST['questions_to_add']);
    foreach ($questions as $item) {
      $stmt = $mysqli->prepare("SELECT leadin FROM questions WHERE q_id=?");
      $stmt->bind_param('i', $item);
      $stmt->execute();
      $stmt->bind_result($leadin);
      $stmt->fetch();
      $stmt->close();
      
      $leadin = trim(strip_tags($leadin));
      $leadin = preg_replace( '/\r\n/', ' ',$leadin);
      if (strlen($leadin) > 160) $leadin = substr($leadin,0,160) . '...';
      $newHTML .= "<div style=\"background-color:highlight; color:white\" id=\"divquestion_$question_no\"><input type=\"hidden\" name=\"question_id$question_no\" value=\"$item\" /><input type=\"checkbox\" onclick=\"toggle(\'divquestion_$question_no\'); updateList();\" id=\"question_text$question_no\" name=\"question_text$question_no\" value=\"" . addslashes($leadin) . "\" checked>&nbsp;" .  addslashes($leadin) . "</div>";
      $question_no++;
    }
    echo "window.top.opener.document.getElementById('questionlist').innerHTML = window.top.opener.document.getElementById('questionlist').innerHTML + '$newHTML';\n";
    echo "window.top.opener.document.getElementById('question_no').value = parseInt(window.top.opener.document.getElementById('question_no').value) + $question_no;\n";
    $mysqli->close();
  ?>
  window.top.close();
</script>
</head>

<body>
<?php var_dump($_POST); ?>
</body>
</html>