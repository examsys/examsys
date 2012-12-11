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
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title>Add new Question</title>
<script language="javascript">
  var questions = new Array();
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
      echo "questions.push([$item, '" . addslashes($leadin) . "']);";
    }
    echo "window.top.opener.addQuestionsToList(questions);";
    $mysqli->close();
  ?>
  window.top.close();
</script>
</head>

<body>
</body>
</html>