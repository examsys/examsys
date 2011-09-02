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

require '../../include/staff_auth.inc';
require '../../include/question_types.inc';
?>
<html>
<head>
<title>Add new Question</title>
<style>
  body {margin:0px; font-family:Arial,sans-serif; background-color:white; color:black; font-size:80%}
</style>

<script language="JavaScript">
  function Qpreview(qID) {
    parent.previewurl.location = '../view_question.php?q_id=' + qID;
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
  if (isset($_GET['display_pos'])) {
    $display_pos = $_GET['display_pos'];
  } else {
    $display_pos = 1;
  }

  if (isset($_GET['order'])) {
    $order = $_GET['order'];
    $direction = $_GET['direction'];
  } else {
    $order = 'leadin';
    $direction = 'asc';
  }

  echo "<form name=\"theform\">\n";
  ?>
  <input type="hidden" name="screen" value="1" />
  <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; width:100%">
  <?php
  echo "<tr><td colspan=\"5\" style=\"background-color:#F1F5FB; font-size:160%; font-weight:bold\">&nbsp;All My Questions</td></tr>\n";
  if ($order == 'leadin' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=desc\">Question</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">Type</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">Question</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">Type</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">Question</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=desc\">Type</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">Question</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">Type</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">Question</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">Type</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=desc\">Modified</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td colspan=\"2\">&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">Question</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">Type</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc\">Modified</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  }  
  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"5\"><img src=\"../../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  
  $id = 0;
  if ($order == 'leadin') $order = 'leadin_plain';
  $query_string = "SELECT q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_date, locked FROM questions WHERE ownerID=$userID AND status != 'retired' AND deleted IS NULL ORDER BY $order $direction";
  $question_data = $mysqli->query($query_string);
  $question_array = array();
  while ($row = $question_data->fetch_assoc()) {
    $tmp_leadin = strip_tags($row['leadin']);
    if (strlen($tmp_leadin) > 160) $tmp_leadin = substr($tmp_leadin,0,160) . '...';
    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">WARNING: no question lead-in!</span>';
      
    echo "<tr><td>";
    if ($row['locked'] != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="Locked" />';
    echo "</td><td><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"" . $row['q_id'] . "\" value=\"" . $row['q_id'] . "\" /></td><td style=\"padding-left:8px\" onclick=\"Qpreview(" . $row['q_id'] . ")\">$tmp_leadin</td><td>&nbsp;" . fullQuestionType($row['q_type']) . "</td><td>&nbsp;" . $row['display_date'] . "</td></tr>\n";
  }
  $question_data->close();
  $mysqli->close();
?>
</table>
</form>
</body>
</html>