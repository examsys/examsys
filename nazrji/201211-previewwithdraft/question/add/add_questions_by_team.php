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
require_once '../../classes/questionutils.class.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Add new Question</title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <style type="text/css">
    body {font-size:80%}
  </style>
  
  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../../tools/mee/mee/js/mee_src.js"></script>
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
  $team = $_GET['team'];
  $team_sql = "%" . $_GET['team'] . "%";
  
  $module = '';
  $folder = '';
  $scrOfY = '';
  if (isset($_GET['module'])) $module = $_GET['module'];
  if (isset($_GET['folder'])) $folder = $_GET['folder'];
  if (isset($_GET['scrOfY'])) $scrOfY = $_GET['scrOfY'];


  echo "<form name=\"theform\" method=\"post\" action=\"do_add_questions.php?team=$team&display_pos=$display_pos&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "&scrOfY=" . $_GET['scrOfY'] . "\">\n";
  ?>
  <input type="hidden" name="screen" value="1" />
  <table class="header" cellspacing="0" cellpadding="0">
  <?php
  echo "<tr><th colspan=\"5\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['byteam'] . " - " .  $_GET['team'] . "</th></tr>\n";
  if ($order == 'leadin' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=desc\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=asc\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=asc\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=desc\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=asc\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=asc\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=desc\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=leadin&direction=asc\">" . $string['question'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=q_type&direction=asc\">" . $string['type'] . "</a>&nbsp;</th><th><img src=\"../../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?team=$team&module=$module&folder=$folder&scrOfY=$scrOfY&order=last_edited&direction=asc\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  }  
  echo "<tr><td colspan=\"5\" class=\"bevel\"></th></tr>\n";
  
  $id = 0;
  if ($order == 'leadin') $order = 'leadin_plain';
  
  
  $stmt = $mysqli->prepare("SELECT q_id, q_type, leadin, q_media, q_media_width, q_media_height, DATE_FORMAT(last_edited,'$cfg_short_date') AS display_date, locked FROM questions WHERE q_group LIKE ? AND deleted IS NULL ORDER BY $order $direction");
  $stmt->bind_param('s', $team_sql);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($q_id, $q_type, $leadin, $q_media, $q_media_width, $q_media_height, $display_date, $locked);
  if ($stmt->num_rows > 0) {
    while ($stmt->fetch()) {
      $tmp_leadin = QuestionUtils::clean_leadin($leadin);
      if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">' . $string['warningnoleadin'] . '</span>';
      
      echo "<tr><td>";
      if ($locked != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="' . $string['locked'] . '" />';
      echo "</td><td><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"$q_id\" value=\"$q_id\" /></td><td style=\"padding-left:8px\" onclick=\"Qpreview($q_id)\">$tmp_leadin</td><td><nobr>&nbsp;" . $string[$q_type] . "</nobr></td><td>&nbsp;$display_date</td></tr>\n";
    }
    $stmt->close();
  } else {
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;" . $string['warningnoquestion'] . " <strong>" . $_GET['team'] . ".</td></tr></table>\n";
  }
  $mysqli->close();
?>
</table>
</form>
</body>
</html>