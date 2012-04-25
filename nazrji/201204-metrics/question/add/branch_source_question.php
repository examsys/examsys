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
  
function checkType($id, $type) {
  if ($type == 'mcq') {
    $question .= ' <input type="radio" name="ref" value="' . $id . '">';
  } else {
    $question .= ' <input type="radio" name="ref" value="' . $id . '" disabled>';
  }
  return $question;
}

function findDecisionQ($question_array,$sourceID) {
  $source_question_no = 0;
  $tmp_q_no = 0;
  foreach ($question_array as $question) {
    if ($question['type'] != 'info') $tmp_q_no++;
    if ($question['q_id'] == $sourceID) $source_question_no = $tmp_q_no;
  }
  return $source_question_no;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Descision Question</title>
  <style type="text/css">
  body {color:black; background-color:white; font-family:Arial,sans-serif; margin:0px; font-size:90%}
  .q_no {text-align:right; vertical-align:top; cursor:pointer; width:40px; padding-right:6px}
  .divider {font-family:Arial,sans-serif; font-size:80%; font-weight:bold; padding-left:6px}
  .leadin {width:85%; vertical-align:top}
  </style>
</head>

<body>

<?php
  if (isset($_POST['submit'])) {
    echo "<script language=\"JavaScript\">\n";
    
    // Clear any pre-existing destination questions
    for ($i=1; $i<=20; $i++) {
      echo "window.top.opener.document.getElementById('answer" . $i . "').innerHTML = '';\n";
      echo "window.top.opener.document.getElementById('answer" . $i . "_no').value = 0;\n";
    }
  
    $option_no = 0;
    $result = $mysqli->prepare("SELECT leadin, option_text FROM questions, options WHERE questions.q_id=options.o_id AND o_id=? ORDER BY id_num");
    $result->bind_param('i', $_POST['ref']);
    $result->execute();
    $result->bind_result($leadin, $option_text);
    while ($row = $result->fetch()) {
      $option_no++;
      echo "window.opener.document.getElementById('option$option_no').innerHTML = '" . $option_text . "';\n";
      echo "window.opener.document.getElementById('line$option_no').style.display = 'block';\n";
    }
    // close up any other options
    for ($i=($option_no+1); $i<=20; $i++) {
      echo "window.opener.document.getElementById('option$i').innerHTML = '';\n";
      echo "window.opener.document.getElementById('line$i').style.display = 'none';\n";
    }
    echo "window.opener.document.getElementById('decisionquestion').innerHTML = '" . addslashes($leadin) . "';\n";
    echo "window.opener.document.getElementById('decisionID').value = '" . $_POST['ref'] . "';\n";
    echo "window.opener.document.getElementById('branch_no').value = '" . $option_no . "';\n";
    $result->close();
    
    echo "window.opener.document.getElementById('heading').style.display = 'block';\n";
    echo "window.close();\n";
    echo "</script>\n";
  }
?>

<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table cellpadding="0" cellspacing="0" border="1" style="font-size:100%; width:100%">
<?php
  $question_no = 1;
  $old_screen = 0;
  $previous_question = true;
  
  $paper_details = array();
  $q_no = 0;

  $result = $mysqli->prepare("SELECT q_id, scenario, leadin, q_type, screen FROM papers, questions WHERE paper=? AND papers.question=questions.q_id ORDER BY screen, display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $scenario, $leadin, $q_type, $screen);
  while ($row = $result->fetch()) {
    $paper_details[$q_no]['q_id'] = $q_id;
    $paper_details[$q_no]['scenario'] = $scenario;
    $paper_details[$q_no]['leadin'] = $leadin;
    $paper_details[$q_no]['q_type'] = $q_type;
    $paper_details[$q_no]['screen'] = $screen;
    $q_no++;
  }  
  $result->close();
  
  $current_screen = 0;
  if (isset($_GET['q_id'])) {
    for ($i=0; $i<$q_no; $i++) {
      if ($paper_details[$i]['q_id'] == $_GET['q_id']) $current_screen = $paper_details[$i]['screen'];
    }
  } else {
    $result = $mysqli->prepare("SELECT MAX(screen) FROM papers WHERE paper=?");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($max_screen);
    $result->fetch();
    $result->close();
    if ($max_screen > 1) $current_screen = $max_screen;
  }
  
  for ($i=0; $i<$q_no; $i++) {
    if ($old_screen != $paper_details[$i]['screen']) {
      echo "<tr><td colspan=\"3\" class=\"divider\">Screen " . $paper_details[$i]['screen'] . "</td></tr>\n";
      echo "<tr><td colspan=\"3\" style=\"height:5px\"><img src=\"../artwork/divider_bar.gif\" width=\"290\" height=\"1\" /></td></tr>\n";
    }
    echo "<tr><td style=\"width:30px\">" . checkType($paper_details[$i]['q_id'], $paper_details[$i]['q_type']) . "</td><td class=\"q_no\">$question_no.</td><td class=\"leadin\">";
    if ($paper_details[$i]['q_type'] == 'branching') {
      if ($paper_details[$i]['leadin'] == '') {
        echo 'Branching question set based on Q' . findDecisionQ($paper_details,$paper_details[$i]['scenario']);
      } else {
        echo $paper_details[$i]['leadin'] . '(Q' . findDecisionQ($paper_details,$paper_details[$i]['scenario']) . ')';
      }
    } else {
      echo $paper_details[$i]['leadin'];
    }
    echo "</td></tr>\n";
    if ($paper_details[$i]['q_type'] != 'info') $question_no++;
    $old_screen = $paper_details[$i]['screen'];
  }  

  $mysqli->close();
?>
<tr><td colspan="3">&nbsp;</td></tr>
<tr><td style="text-align:center" colspan="3"><input type="submit" name="submit" value="OK" style="width:100px" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:100px" onclick="window.close()" /></td></tr>
</table>
</form>

</body>
</html>
