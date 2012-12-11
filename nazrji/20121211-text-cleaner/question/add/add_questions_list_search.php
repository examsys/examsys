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
require_once '../../classes/searchutils.class.php';
require_once '../../classes/questionutils.class.php';
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rogō</title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <style type="text/css">
    body {font-size:80%}
    p, td {font-size:90%}
  </style>
  
  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../../tools/mee/mee/js/mee_src.js"></script>
  <script language="JavaScript">
    function Qpreview(qID) {
      parent.previewurl.location = '../view_question.php?q_id=' + qID;
    }

    function populateTicks() {
      q_array = parent.top.controls.document.getElementById('questions_to_add').value.split(",");
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
  <table class="header">
  <tr>
  <th colspan="5">
  <form name="search" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  &nbsp;<strong><?php echo $string['wordphrase']; ?></strong> <input type="text" size="30" name="searchterm" <?php if (isset($_GET['searchterm'])) echo 'value="' . $_GET['searchterm'] . '" '; ?>/> <strong><?php echo $string['in']; ?></strong> 
  <select name="searchtype">
    <option value="%"><?php echo $string['anytype']; ?></option>
    <option value="area" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'area') echo 'selected '; ?>><?php echo $string['area']; ?></option>
    <option value="calculation" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'calculation') echo 'selected '; ?>><?php echo $string['calculation']; ?></option>
    <option value="dichotomous" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'dichotomous') echo 'selected '; ?>><?php echo $string['dichotomous']; ?></option>
    <option value="extmatch" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'extmatch') echo 'selected '; ?>><?php echo $string['extmatch']; ?></option>
    <option value="blank" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'blank') echo 'selected '; ?>><?php echo $string['blank']; ?></option>
    <option value="flash" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'flash') echo 'selected '; ?>><?php echo $string['flash']; ?></option>
    <option value="hotspot" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'hotspot') echo 'selected '; ?>><?php echo $string['hotspot']; ?></option>
    <option value="info" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'info') echo 'selected '; ?>><?php echo $string['info']; ?></option>
    <option value="labelling" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'labelling') echo 'selected '; ?>><?php echo $string['labelling']; ?></option>
    <option value="likert" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'likert') echo 'selected '; ?>><?php echo $string['likert']; ?></option>
    <option value="matrix" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'matrix') echo 'selected '; ?>><?php echo $string['matrix']; ?></option>
    <option value="mcq" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'mcq') echo 'selected '; ?>><?php echo $string['mcq']; ?></option>
    <option value="mrq" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'mrq') echo 'selected '; ?>><?php echo $string['mrq']; ?></option>
    <option value="rank" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'rank') echo 'selected '; ?>><?php echo $string['rank']; ?></option>
    <option value="sct" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'sct') echo 'selected '; ?>><?php echo $string['sct']; ?></option>
    <option value="textbox" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'textbox') echo 'selected '; ?>><?php echo $string['textbox']; ?></option>
    <option value="true_false" <?php if (isset($_GET['searchtype']) and $_GET['searchtype'] == 'true_false') echo 'selected '; ?>><?php echo $string['true_false']; ?></option>
  </select>
  <?php
    search_utils::display_owners_dropdown($userObject, $mysqli, 'questions', 100);
  ?>
  &nbsp;<input type="submit" value=" <?php echo $string['search']; ?> " name="search" />
  </form>
  </th>
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
    $order = 'leadin_plain';
    $direction = 'asc';
  }

  echo "<tr><th>&nbsp;</th><th>&nbsp;</th><th class=\"vert_div\">&nbsp;";
  if ($order == 'leadin_plain' and $direction == 'asc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'leadin_plain' and $direction == 'desc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=desc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?order=leadin_plain&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=q_type&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?order=last_edited&direction=asc&owner=$owner&searchterm=$searchterm&searchtype=$searchtype\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  }  
?>
  <tr><th colspan="5" class="bevel"></th></tr>
<?php
  echo "<form name=\"theform\" method=\"post\" action=\"\">\n";
  echo '<input type="hidden" name="screen" value="1" />';
  
  if (isset($_GET['search']) or isset($_GET['order'])) {
    $old_id = 0;
    $searchterm = '%' . $_GET['searchterm'] . '%';
    
    if ($order == 'q_type') $order = 'CAST(q_type AS CHAR)';
    
    if ($_GET['owner'] == '') {
      $teams = array_keys($userObject->get_staff_modules());
      $result = $mysqli->prepare("SELECT DISTINCT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_date, locked FROM (questions_modules, questions, options) WHERE questions.q_id=questions_modules.q_id AND (idMod IN (" . implode(',', $teams) . ") OR questions.ownerID=?) AND questions.q_id=options.o_id AND (leadin_plain LIKE ? OR theme LIKE ? OR scenario_plain LIKE ? OR notes LIKE ? OR option_text LIKE ?) AND q_type LIKE ? AND deleted IS NULL ORDER BY $order $direction, questions.q_id");
      $result->bind_param('issssss', $userObject->get_user_ID(), $searchterm, $searchterm, $searchterm, $searchterm, $searchterm, $_GET['searchtype']);
    } else {
      $result = $mysqli->prepare("SELECT DISTINCT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_date, locked FROM (questions, options) WHERE questions.q_id=options.o_id AND questions.ownerID=? AND (leadin_plain LIKE ? OR theme LIKE ? OR scenario_plain LIKE ? OR notes LIKE ? OR option_text LIKE ?) AND q_type LIKE ? AND deleted IS NULL ORDER BY $order $direction, q_id");
      $result->bind_param('issssss', $_GET['owner'], $searchterm, $searchterm, $searchterm, $searchterm, $searchterm, $_GET['searchtype']);
    }
    $result->execute();  
    $result->bind_result($q_id, $q_type, $leadin, $display_date, $locked);
    while ($result->fetch()) {
      $tmp_leadin = QuestionUtils::clean_leadin($leadin);
      if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">' . $string['warningnoleadin'] . '</span>';
    
      echo "<tr><td style=\"width:16px\">";
      if ($locked != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="' . $string['locked'] . '" />';
      echo "</td><td><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"$q_id\" value=\"$q_id\" /></td><td onclick=\"Qpreview($q_id)\">$tmp_leadin</td><td><nobr>&nbsp;" . $string[$q_type] . "</nobr></td><td>&nbsp;$display_date</td></tr>\n";
    }
    $result->close();
  }
  $mysqli->close();
  ?>
</form>
</table>
</body>
</html>