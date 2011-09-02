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
require '../../include/errors.inc'; 
require '../../include/media.inc';
require '../../include/question_types.inc';
?>
<html>
<head>
<title>Add new Question</title>
<style>
  body {margin:0px; font-family:Arial,sans-serif; color:black}
  p, td {font-size:80%}
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

<body onload="populateTicks(); document.search.searchterm.focus();">
<?php

  if (isset($_GET['display_pos'])) {
    $display_pos = $_GET['display_pos'];
  } else {
    $display_pos = 1;
  }

  ?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr style="background-color:#F1F5FB">
  <td colspan="5">
  <form name="search" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  &nbsp;<strong>Word/phrase</strong> <input style="font-size:90%" type="text" size="30" name="searchterm" <?php if(isset($_GET['searchterm'])) echo 'value="' . $_GET['searchterm'] . '" '; ?>/> <strong>in</strong> 
  <select name="searchtype" style="font-size: 90%">
    <option value="%">(all types)</option>
    <option value="calculation" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'calculation') echo 'selected '; ?>>Calculation</option>
    <option value="dichotomous" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'dichotomous') echo 'selected '; ?>>Dichotomous</option>
    <option value="extmatch" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'extmatch') echo 'selected '; ?>>Extended Matching</option>
    <option value="blank" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'blank') echo 'selected '; ?>>Fill-in-the-Blank</option>
    <option value="flash" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'flash') echo 'selected '; ?>>Flash Interface</option>
    <option value="hotspot" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'hotspot') echo 'selected '; ?>>Image Hotspot</option>
    <option value="info" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'info') echo 'selected '; ?>>Information Block</option>
    <option value="labelling" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'labelling') echo 'selected '; ?>>Labelling</option>
    <option value="likert" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'likert') echo 'selected '; ?>>Likert Scale</option>
    <option value="matrix" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'matrix') echo 'selected '; ?>>Matrix</option>
    <option value="mcq" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'mcq') echo 'selected '; ?>>Multiple Choice</option>
    <option value="mrq" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'mrq') echo 'selected '; ?>>Multiple Response</option>
    <option value="rank" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'rank') echo 'selected '; ?>>Ranking</option>
    <option value="sct" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'sct') echo 'selected '; ?>>Script Concordance</option>
    <option value="textbox" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'textbox') echo 'selected '; ?>>Text Box</option>
    <option value="timedate" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'timedate') echo 'selected '; ?>>Time/Date</option>
  </select>
  <select name="owner" style="font-size:90%">
  <option value="%">(any owner)</option>
  <option value="<?php echo $_SERVER['PHP_AUTH_USER']; ?>">(my questions only)</option>
  <option value="%" style="background-color:#ECE9D8"></option>
  <?php
  $search_results = $mysqli->query("SELECT DISTINCT id, REPLACE(title,'Professor','Prof') AS title, initials, surname FROM users WHERE roles LIKE 'Staff%' OR roles LIKE '%SysAdmin%' ORDER BY surname");
  while ($row = $search_results->fetch_assoc()) {
    if (isset($_GET['search'])) {
      if ($row['id'] == $_GET['owner']) {
        echo "<option value=\"" . $row['id'] . "\" selected>"  . $row['surname'] . ", " . $row['initials'] . " " . $row['title'] . "</option>\n";
      } else {
        echo "<option value=\"" . $row['id'] . "\">"  . $row['surname'] . ", " . $row['initials'] . " " . $row['title'] . "</option>\n";
      }
    } else {
      if ($row['id'] == $userID) {
        echo "<option value=\"" . $row['id'] . "\" selected>"  . $row['surname'] . ", " . $row['initials'] . " " . $row['title'] . "</option>\n";
      } else {
        echo "<option value=\"" . $row['id'] . "\">"  . $row['surname'] . ", " . $row['initials'] . " " . $row['title'] . "</option>\n";
      }
    }
  }
  $search_results->close();
  ?>
  </select>&nbsp;<input type="submit" value=" Search " name="search" />
  </form>
  </td>
  </tr>
<?php
  if (isset($_GET['owner'])) {
    $owner = $_GET['owner'];
  } else {
    $owner = '';
  }
  if (isset($_GET['searchterm'])) {
    $searchterm = $_GET['searchterm'];
  } else {
    $searchterm = '';
  }
  if (isset($_GET['searchtype'])) {
    $searchtype = $_GET['searchtype'];
  } else {
    $searchtype = '';
  }
  if (isset($_GET['order'])) {
    $order = $_GET['order'];
    $direction = $_GET['direction'];
  } else {
    $order = 'leadin';
    $direction = 'asc';
  }
  echo "<tr style=\"background-color:#F1F5FB\"><td>&nbsp;</td><td>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;";
  if ($order == 'leadin' and $direction == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;</td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Question</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Type</a>&nbsp;</td><td><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">Modified</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></td></tr>\n";
  }  
?>
  <tr style="height:4px"><td valign="top" colspan="5"><img src="../../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
  echo "<form name=\"theform\" method=\"post\" action=\"\">\n";
  echo '<input type="hidden" name="screen" value="1" />';
  
  if (isset($_GET['search']) or isset($_GET['order'])) {
    $old_id = 0;
    $searchterm = '%' . $_GET['searchterm'] . '%';
    //$result = $mysqli->prepare("SELECT questions.q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_date, locked, parts FROM questions LEFT JOIN question_exclude ON questions.q_id=question_exclude.q_id WHERE questions.ownerID LIKE ? AND (leadin_plain LIKE ? OR theme LIKE ? OR scenario_plain LIKE ? OR notes LIKE ?) AND q_type LIKE ? AND deleted IS NULL ORDER BY $order $direction, q_id");
    $result = $mysqli->prepare("SELECT questions.q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'%d/%m/%y') AS display_date, locked, parts FROM questions LEFT JOIN (keywords_question, keywords_user) ON questions.q_id=keywords_question.q_id LEFT JOIN question_exclude ON questions.q_id=question_exclude.q_id WHERE (keywords_question.keywordID=keywords_user.id OR keywords_question.keywordID is null) AND questions.ownerID LIKE ? AND (leadin_plain LIKE ? OR theme LIKE ? OR scenario_plain LIKE ? OR notes LIKE ? OR keyword=?) AND q_type LIKE ? AND deleted IS NULL ORDER BY $order $direction, q_id");
    $result->bind_param('sssssss', $_GET['owner'], $searchterm, $searchterm, $searchterm, $searchterm, $_GET['searchterm'], $_GET['searchtype']);
    $result->execute();  
    $result->bind_result($q_id, $q_type, $leadin, $q_media, $q_media_width, $q_media_height, $display_date, $locked, $parts);
    while ($row = $result->fetch()) {
      if ($q_id != $old_id) {
        $tmp_leadin = strip_tags($leadin);
        if (strlen($tmp_leadin) > 160) $tmp_leadin = substr($tmp_leadin,0,160) . '...';
        if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">WARNING: no question lead-in!</span>';
      
        echo "<tr><td style=\"width:16px\">";
        if ($locked != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="Locked" />';
        echo "</td><td><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"$q_id\" value=\"$q_id\" /></td>";
        if ($parts == '') {
          echo '<td onclick="Qpreview(' . $q_id . ')">';
        } else {
          echo '<td onclick="Qpreview(' . $q_id . ')" style="color:red; text-decoration:line-through">';
        }
        echo $tmp_leadin . "</td><td>&nbsp;" . fullQuestionType($q_type) . "</td><td>&nbsp;$display_date</td></tr>\n";
      }
      $old_id = $q_id;
    }
    $result->close();
  }
  $mysqli->close();
  ?>
</form>
</table>
</body>
</html>