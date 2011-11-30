<?php

//niko abs

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
require '../../include/errors.inc';
?>
<html>
<head>
<title>Rogō</title>
<style>
  body {margin:0px; font-family:Arial,sans-serif; color:black; background-color:white; font-size:80%}
</style>

<script language="JavaScript">
  function Qpreview(qID) {
    parent.previewurl.location = '../view_question.php?q_id=' + qID;
  }
  
  function populateTicks() {
    var q_array = parent.top.controls.document.theform.questions_to_add.value.split(",");
    //q_array = Array(786,4283,3339);
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
  echo "<tr><td colspan=\"4\" style=\"background-color:#F1F5FB; font-size:160%; font-weight:bold\">&nbsp;" . $string['myunusedquestions'] . "</td></tr>\n";
  if ($order == 'leadin' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=desc\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=asc\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=asc\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=desc\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=asc\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=asc\">" . $string['modified'] . "</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'creation_date' and $direction == 'asc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=desc\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  } elseif ($order == 'creation_date' and $direction == 'desc') {
    echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</td><td><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=creation_date&direction=asc\">" . $string['modified'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  }  
  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"4\"><img src=\"../../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  
  $id = 0;
  if ($order == 'leadin') $order = 'leadin_plain';
  $query_string = "SELECT question, q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'$cfg_short_date') AS display_date FROM papers RIGHT JOIN questions ON papers.question=questions.q_id WHERE questions.ownerID=$userID AND status != 'retired' AND deleted IS NULL ORDER BY $order $direction";
  $question_data = $mysqli->query($query_string);
  $question_array = array();
  while ($row = $question_data->fetch_assoc()) {
    if ($row['question'] == NULL) {
      $tmp_leadin = strip_tags($row['leadin']);
      if (strlen($tmp_leadin) > 160) $tmp_leadin = substr($tmp_leadin,0,160) . '...';
      if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">' . $string['warningnoleadin'] . '</span>';
      
      echo "<tr><td><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"" . $row['q_id'] . "\" id=\"" . $row['q_id'] . "\" value=\"" . $row['q_id'] . "\" /></td><td style=\"padding-left:8px\" onclick=\"Qpreview(" . $row['q_id'] . ")\">$tmp_leadin</td><td>&nbsp;<nobr>" . $string[$row['q_type']] . "</nobr></td><td>&nbsp;" . $row['display_date'] . "</td></tr>\n";
    }
  }
  $question_data->close();
  $mysqli->close();
?>
</table>
</form>
</body>
</html>