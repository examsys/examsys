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

  require '../include/staff_auth.inc';
  require '../lang/' . $language . '/include/question_types.inc';
  
  $typeSQL = '';
  $type = '';
  if (isset($_GET['type'])) {
    $type = $_GET['type'];
    if ($_GET['type'] != '%') {
      $typeSQL = " AND q_type = '" . $_GET['type'] . "'";
    }
  }
  if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];
  } else {
    $userid = '';
  }
  if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
  } else {
    $keyword = '';
  }
  if (isset($_GET['team'])) {
    $team = $_GET['team'];
  } else {
    $team = '';
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Rogo: <?php echo $string['questionbank'] . ' ' . $cfg_install_type; ?></title>

<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style style="text/css">
  .d {padding-left:6px; padding-right:2px; padding-top:4px; padding-bottom:2px; vertical-align:top}
  .owner {color:#A5A5A5}
</style>
</head>

<body onclick="hideMenus(event)">
<?php
  require '../include/question_list_options.inc';
?>

<div id="content" class="content" style="font-size:80%" onclick="hideMenus(event)">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php
  $question_no = 0;
  $display_no = 0;
  $bank_type = '';
  $team_sql = '';
  
  if ($keyword != '%' and $keyword != '' and $type == '%') {
    $parts = explode(';',$keyword);
    $bank_type = ": '" . $parts[1] . "'";
  }
  if ($team != '') {
    $bank_type = ": team $team";
  }
  if ($type != '%' and $keyword == '%') {
    $bank_type = ": " . $type;
  }

  if (isset($_COOKIE['myquestions']) and $_COOKIE['myquestions'] == 'checked') {
    if ($team != '') {
      $team_sql = 'users.id=' . $userID . ' AND q_group="' . $team . '"';
    } else {
      $team_sql = 'users.id=' . $userID;
    }
  } else {
    if ($team != '') {
      if (in_array($team, $teams)) {
        $team_sql = 'q_group="' . $team . '"';
      } else {
        echo "<tr><td colspan=\"4\">" . $string['notinteam'] . "</td></tr>\n</body>\n</html>\n";
        exit;
      }
    } else {
      if (count($teams) > 0) {
        $team_sql = implode("','", $teams);
        if ($team_sql != '') $team_sql = "q_group IN ('$team_sql')";
        $team_sql .= " OR users.id=$userID";
      }
    }
  }
  
  if ($keyword != '%' and $keyword != '') {
    $keyword = ' AND keywordID=' . $parts[0];
  } else {
    $keyword = '';
  }

  if ($team_sql != '') {
    $team_sql = '(' . $team_sql .') AND';
  } else {
    // Reset to just look for current owners paper if not on any teams.
    $team_sql .= "users.id=$userID AND";
  }

  $hits = 0;
  $display_no = 0;
  
  $query_string = "SELECT questions.q_id, title, initials, surname, ownerID, leadin_plain AS leadin, q_type, q_media, DATE_FORMAT(last_edited,'$cfg_short_date') AS last_edited, locked, status FROM (users, questions)";
  if ($keyword != '%' and $keyword != '') {
  	$query_string .= " LEFT JOIN keywords_question ON questions.q_id=keywords_question.q_id";
  }
  $query_string .= " WHERE $team_sql users.id=questions.ownerID $typeSQL $keyword AND status != 'retired' AND deleted IS NULL ORDER BY leadin_plain, q_id";
  $search_results = $mysqli->prepare($query_string);
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($q_id, $title, $initials, $surname, $ownerID, $leadin, $q_type, $q_media, $last_edited, $locked, $status);

  echo "<tr><td colspan=\"3\" style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionbank'] . "&nbsp;(" . number_format($search_results->num_rows) . ")</strong>$bank_type</div></td>";
  echo "<td colspan=\"2\" style=\"text-align:right; background-color:#F1F5FB\" nowrap><input type=\"checkbox\" onclick=\"updateCookies();\" name=\"myquestions\" id=\"myquestions\" value=\"on\"";
  if (isset($_COOKIE['myquestions'])) echo $_COOKIE['myquestions'];
  echo " />&nbsp;<nobr>" . $string['myquestionsonly'] . "</nobr>&nbsp;</td></tr>\n";

  echo "<tr><td style=\"background-color:#F1F5FB\" align=\"right\" width=\"15\">&nbsp;<img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" /></td>\n";
  echo "<td style=\"background-color:#F1F5FB\">&nbsp;" . $string['question'] . "&nbsp;</td>\n";
  echo "<td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['type'] . "&nbsp;</td>\n";
  echo "<td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['modified'] . "&nbsp;</td>\n";
  echo "<td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['status'] . "&nbsp;</td></tr>\n";
  echo "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"5\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";

  while ($search_results->fetch()) {

    if ($locked != '') {
      echo "<tr id=\"line$display_no\" onmouseover=\"lon('line$display_no')\" onmouseout=\"loff('line$display_no')\" style=\"cursor:pointer\" onclick=\"selQ('$q_id','line$display_no','$q_type','menu2c')\" ondblclick=\"editQ('$q_id','$q_type'); return false;\">";
      echo "<td><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Question Locked\" /></td>";
    } else {
      echo "<tr id=\"line$display_no\" onmouseover=\"lon('line$display_no')\" onmouseout=\"loff('line$display_no')\" style=\"cursor:pointer\" onclick=\"selQ('$q_id','line$display_no','$q_type','menu2b')\" ondblclick=\"editQ('$q_id','$q_type'); return false;\">";
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
    echo "<td class=\"d\">$tmp_leadin <span class=\"owner\">($title $initials $surname)</span></td>";
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
