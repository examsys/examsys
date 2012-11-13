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

require_once '../include/staff_auth.inc';
require_once '../lang/' . $language . '/include/question_types.inc';
require_once '../classes/stateutils.class.php';
require_once '../classes/moduleutils.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$typeSQL = '';
$type = '';
if (isset($_GET['type'])) {
  $type = $_GET['type'];
  if ($_GET['type'] != '%') {
    $typeSQL = " AND q_type = '" . $_GET['type'] . "'";
  }
}
if (isset($_GET['userid'])) {
  $userid = $_GET['userid']; //TODO fix this
} else {
  $userid = '';
}
if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
} else {
  $keyword = '';
}
if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

if (isset($_GET['checked'])) {
  if ($_GET['checked'] == 'true') {
    $state_checked = true;
  } else {
    $state_checked = false;
  }
} elseif (isset($state['myquestions']) and $state['myquestions'] == 'true') {
  $state_checked = true;
} else {
  $state_checked = false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Rogo: <?php echo $string['questionbank'] . ' ' . $cfg_install_type; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .d {padding-left:6px; padding-right:2px; padding-top:4px; padding-bottom:2px; vertical-align:top}
    .owner {color:#A5A5A5}
    .qline {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .qline:hover {background-color:#eee}
    .qline.highlight {background-color:#B3C8E8}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script language="JavaScript">
    function myQuestions(thisObj) {
      var content = $(thisObj).is(':checked');
      window.location = 'list.php?type=<?php echo $_GET['type']; ?>&checked=' + content;
    }
  </script>
</head>

<body onclick="hideMenus(event)" onselectstart="return false">
<?php
  require '../include/question_list_options.inc';
?>

<div id="content" class="content" onclick="hideMenus(event)">
<table class="header">
<?php
  $question_no = 0;
  $display_no = 0;
  $bank_type = '';
  $module_sql = '';
  
  if ($keyword != '%' and $keyword != '' and $type == '%') {
    $parts = explode(';',$keyword);
    $bank_type = ": '" . $parts[1] . "'";
  }
  if ($module != '') {
    $bank_type = ': ' . $module;
  }
  if ($_GET['type'] != '%') {
    $bank_type = ': ' . $_GET['type'];
  }
  $staff_modules_sql = '';
  if ($module != '') {
    if (in_array($module, $staff_modules)) {
      $idMod = module_utils::get_idMod($module, $mysqli);
      $module_sql = "idMod = $idMod";
    } else {
      echo "<tr><td colspan=\"4\">" . $string['notinteam'] . "</td></tr>\n</body>\n</html>\n";
      exit;
    }
  } else {
    if (count($staff_modules) > 0) {
      $staff_modules_sql = implode(',', array_keys($staff_modules));
      $staff_modules_sql = " (idMod IN ($staff_modules_sql)";
      $staff_modules_sql .= " OR users.id=" . $userObject->get_user_ID() . ") AND ";
    }
  }
  
  if ($module_sql != '') {
    $module_sql = '(' . $module_sql .') AND';
  } else {
    // Reset to just look for current owners paper if not on any teams.
    $module_sql .= "users.id=" . $userObject->get_user_ID() . " AND";
  }

  if ($keyword != '%' and $keyword != '') {
    $keyword = ' AND keywordID=' . $parts[0];
  } else {
    $keyword = '';
  }

  $hits = 0;
  $display_no = 0;
  
  $query_string = "SELECT questions.q_id, title, initials, surname, ownerID, leadin_plain AS leadin, q_type, q_media, DATE_FORMAT(last_edited,'$cfg_short_date') AS last_edited, locked, status FROM (users, questions, questions_modules)";
  if ($keyword != '%' and $keyword != '') {
  	$query_string .= " LEFT JOIN keywords_question ON questions.q_id=keywords_question.q_id";
  }
  if ($state_checked == 'true') {
    $query_string .= " WHERE questions.q_id = questions_modules.q_id AND $module_sql $staff_modules_sql users.id=questions.ownerID AND ownerID=" . $userObject->get_user_ID() . " $typeSQL $keyword AND status != 'retired' AND deleted IS NULL GROUP BY q_id ORDER BY leadin_plain, q_id";
  } else {
    $query_string .= " WHERE questions.q_id = questions_modules.q_id AND $module_sql $staff_modules_sql users.id=questions.ownerID $typeSQL $keyword AND status != 'retired' AND deleted IS NULL GROUP BY q_id ORDER BY leadin_plain, q_id";
  }
  $search_results = $mysqli->prepare($query_string);
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($q_id, $title, $initials, $surname, $ownerID, $leadin, $q_type, $q_media, $last_edited, $locked, $status);

  echo "<tr onclick=\"qOff();\"><th colspan=\"3\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionbank'] . "&nbsp;(" . number_format($search_results->num_rows) . ")</strong>$bank_type</div></th>";
  echo "<th colspan=\"2\" style=\"text-align:right\" nowrap><input class=\"chk\" type=\"checkbox\" onclick=\"myQuestions(this);\" name=\"myquestions\" id=\"myquestions\" value=\"on\"";
  if ($state_checked == 'true') echo ' checked="checked"';
  echo " />&nbsp;<nobr>" . $string['myquestionsonly'] . "</nobr>&nbsp;</th></tr>\n";

  echo "<tr onclick=\"qOff();\"><th style=\"text-align:right; width:15px\">&nbsp;<img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" /></th>\n";
  echo "<th>&nbsp;" . $string['question'] . "&nbsp;</td>\n";
  echo "<th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['type'] . "&nbsp;</th>\n";
  echo "<th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['modified'] . "&nbsp;</th>\n";
  echo "<th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['status'] . "&nbsp;</th></tr>\n";
  echo "<tr><th class=\"bevel\" colspan=\"5\"></th></tr>\n";

  while ($search_results->fetch()) {
    echo '<tr class="qline"';
    if ($locked != '') {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2c',event)\" ondblclick=\"editQ(); return false;\">";
      echo "<td><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Question Locked\" /></td>";
    } else {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2b',event)\" ondblclick=\"editQ(); return false;\">";
      echo "<td></td>";
    }
    $tmp_leadin = $leadin;
    
    if (strpos($tmp_leadin,'class="mee"') === false) {
      $tmp_leadin = strip_tags($tmp_leadin);                                     // No equation, strip all tags
      if (strlen($tmp_leadin) > 160) {
        $tmp_leadin = substr($tmp_leadin,0,160) . '...';
      }
    } else {
      $tmp_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($tmp_leadin,"<div>,<span>"))));
      $tmp_leadin = preg_replace('/ style="[\w-,:; \']*"/i', '', $tmp_leadin);   // Equation present, strip some formatting
    }
    
    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:#C00000">' . $string['noquestionleadin'] . '</span>';
    if ($userObject->has_role('Demo')) {
      $owner = 'Dr J, Bloggs';
    } else {
      $owner = "$title $initials, $surname";
    }
    echo "<td class=\"d\">$tmp_leadin <span class=\"owner\">($owner)</span></td>";
    echo "<td class=\"d\" onclick=\"qOff()\"><nobr>" . $string[$q_type] . "</nobr></td>";
    echo "<td class=\"d\" onclick=\"qOff()\">$last_edited</td>\n";
    echo "<td class=\"d\" onclick=\"qOff()\">" . $string[strtolower($status)] . "</td></tr>\n";
    $display_no++;
  }
  $search_results->close();
  $mysqli->close();
?>
</table>
</div>

</body>
</html>