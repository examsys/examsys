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
  require '../../include/question_types.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['bykeywords']; ?></title>
<style>
html {height:100%; border-left:1px solid #95AEC8}
body {margin:0px; background-color:white; color:black; font-family:Arial,sans-serif; font-size:80%}
a:link {color:black}
a:visited {color:black}
a:hover {color:black}
.f {padding-left:2px}
.n {text-align:right; padding-right:2px}
</style>

<script language="JavaScript">
  function orderTable(order_val, direction_val) {
    document.keywordsform.order.value = order_val;
    document.keywordsform.direction.value = direction_val;
    document.keywordsform.submit();
  }
  
  function Qpreview(qID) {
    parent.parent.previewurl.location = '../view_question.php?q_id=' + qID;
  }
  
  function populateTicks() {
    q_array = parent.top.controls.document.theform.questions_to_add.value.split(",");
    for (i=0; i<q_array.length; i++) { 
      var obj = document.getElementById(q_array[i]);
      if (obj != null) {
        obj.checked = true;
      }
    }
  }
</script>
</head>

<body onload="populateTicks()">
<?php
  if (isset($_POST['order'])) {
    $order = $_POST['order'];
    $direction = $_POST['direction'];
  } else {
    $order = 'leadin';
    $direction = 'asc';
  }
  
  echo "<form name=\"theform\" method=\"post\" action=\"\">\n";
  echo "<input type=\"hidden\" name=\"screen\" value=\"1\" />\n";
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:100%\">\n";
  echo "<tr style=\"background-color:#F1F5FB\"><td valign=\"top\" colspan=\"5\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['bykeywords'] . "</td></tr>\n";

  if ($order == 'leadin' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','desc'); return false;\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','desc'); return false;\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','desc'); return false;\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  }  

  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"5\"><img src=\"../../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";

  if (!isset($_POST['keyword_no'])) {
    echo "</table>\n</body>\n</html>\n";
    exit;
  }
  
  $teams = '';
  $sql = "SELECT name FROM teams WHERE memberID=$userID";
  $keywords = $mysqli->query($sql);
  while($row = $keywords->fetch_assoc()) {
    if ($teams == '') {
      $teams = "'" . $row['name'] . "'";
    } else {
      $teams .= ",'" . $row['name'] . "'";
    }
  }
  
  $keyword_ids = '';
  for ($i=0; $i<$_POST['keyword_no']; $i++) {
    if (isset($_POST["keyword$i"])) {
      if ($keyword_ids == '') {
        $keyword_ids = $_POST["keyword$i"];
      } else {
        $keyword_ids .= ',' . $_POST["keyword$i"];
      }
    }
  }
  if ($keyword_ids == '') {
    echo "</table>\n</body>\n</html>\n";
    exit;
  }
  
  $old_id = '';
  $sql = "SELECT questions.q_id, leadin_plain, q_type, DATE_FORMAT(last_edited,'$cfg_short_date') AS display_date, locked, parts FROM (questions, keywords_question) LEFT JOIN question_exclude ON questions.q_id=question_exclude.q_id WHERE questions.q_id=keywords_question.q_id AND keywords_question.keywordID IN ($keyword_ids) AND (ownerID=$userID OR q_group IN ($teams)) AND status != 'retired' AND deleted IS NULL ORDER BY $order $direction, questions.q_id";
  $keywords = $mysqli->query($sql);
  $old_id;
  while($row = $keywords->fetch_assoc()) {
    if ($row['q_id'] != $old_id) {
      echo "<tr><td style=\"width:20px\">";
      if ($row['locked'] != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="Locked" />';
      echo "</td><td style=\"width:25px\"><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"" . $row['q_id'] . "\" value=\"" . $row['q_id'] . "\" /></td>";
      if ($row['parts'] == '') {
        echo '<td onclick="Qpreview(' . $row['q_id'] . ')">';
      } else {
        echo '<td style="color:red; text-decoration:line-through" onclick="Qpreview(' . $row['q_id'] . ')">';
      }
      echo $row['leadin_plain'] . "</td><td>" . fullQuestionType($row['q_type']) . "</td><td style=\"padding-left:5px; padding-right:2px\">" . $row['display_date'] . "</td></tr>\n";
    }
    $old_id = $row['q_id'];
  }

?>
</table>
</form>

<?php
  echo "<form name=\"keywordsform\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n";
  foreach ($_POST as $key=>$value) {
    if ($key != 'submit' and $key != 'order' and $key != 'direction') {
      echo "<input type=\"hidden\" name=\"$value\" value=\"$value\" />\n";
    }
  }
?>
<input type="hidden" name="order" value="" />
<input type="hidden" name="direction" value="" />
</form>
</body>
</html>