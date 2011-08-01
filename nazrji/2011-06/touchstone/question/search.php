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
* Displays the results of a question search.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/question_types.inc';
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>TouchStone: Question Search<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style style="text/css">
input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
.owner {color:#A5A5A5}
</style>

<script language="JavaScript">
  function selQ(questionID, lineID, qType, menuID) {
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById('link' + tmp_ID).style.backgroundColor = 'white';
    }
    document.getElementById('menu2a').style.display = 'none';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    document.getElementById(menuID).style.display = 'block';

    document.PapersMenu.questionID.value = questionID;
    document.PapersMenu.qType.value = qType;
    
    document.getElementById('link' + lineID).style.backgroundColor = '#B3C8E8';
    document.PapersMenu.oldQuestionID.value = lineID;
  }
  
  function qOff() {
    document.getElementById('menu2a').style.display = 'block';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById('link' + tmp_ID).style.backgroundColor = 'white';
    }
  }
  
  function lon(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById('link' + lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById('link' + lineID).style.backgroundColor = '';
    }
  }
</script>
</head>

<?php
  if (isset($_POST['submit'])) {
    echo "<body>\n";
    require '../include/question_search_options.inc';
    echo "<div id=\"content\" class=\"content\" style=\"font-size:80%\">\n";
  } else {
    echo "<body style=\"margin:0px; background-color:white; color:black\">\n";
    require '../include/question_search_options.inc';
    echo "<div id=\"content\" class=\"content\" style=\"font-size:80%\">\n";
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>Question Search</div></td></tr>";
?>
  <tr>
  <td style="background-color:#F1F5FB" align="right">&nbsp;<img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" /></td>
  <td style="background-color:#F1F5FB">&nbsp;Question&nbsp;</td>
  <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Type&nbsp;</td>
  <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Modified&nbsp;</td></tr>
  <tr style="height:4px"><td valign="top" colspan="4"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
  </table>
<?php
  }

if (isset($_POST['submit'])) {
  $error = '';
  if ($_POST['theme'] == '' and $_POST['scenario'] == '' and $_POST['leadin'] == '' and $_POST['options'] == '' and $_POST['keywords'] == '') {
    $error = 'You have not ticked any fields to search for.';
  }
  
  if ($_POST['searchterm'] == '' and $_POST['owner'] == '' and  $_POST['status'] == '%' and $_POST['bloom'] == '%' and $_POST['keywordID'] == '' and $_POST['status'] == '%' and $_POST['team'] == '' and $_POST['question_date'] == 'dont remember' and $_POST['qType'] == '' ) {
    $error = 'Please narrow your search by entering a search term, question type, date modified or metadata.';
  }
  
  if($error != '') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>Question Search</div></td></tr>";
    ?>
    <tr>
    <td style="background-color:#F1F5FB" align="right">&nbsp;<img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" /></td>
    <td style="background-color:#F1F5FB">&nbsp;Question&nbsp;</td>
    <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Type&nbsp;</td>
    <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Modified&nbsp;</td></tr>
    <tr style="height:4px"><td valign="top" colspan="4"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
    </table>
    <?php
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;$error.</td></tr></table>\n";
    exit;
  }
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

  $params = '';
  $variables = array();
  
  $keywordsSQL = '';
  if ($_POST['keywordID'] != '') {
    $keywordsSQL = 'AND keywordID=?';
    $variables[] = intval($_POST['keywordID']);
    $params .= 'i';
  }

  $searchterm = $_POST['searchterm'];
  if ($searchterm == '') {
    $search_string = '';
  } else {
    if ($_POST['theme']) {
      $themeSQL = ' OR theme LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $themeSQL = '';
    }
    
    if ($_POST['scenario']) {
      $scenarioSQL = ' OR scenario_plain LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $scenarioSQL = '';
    }
    
    if ($_POST['leadin']) {
      $leadinSQL = ' OR leadin_plain LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $leadinSQL = '';
    }
    
    if ($_POST['options']) {
      $stemsSQL = ' OR option_text LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $stemsSQL = '';
    }
    
    $search_string = $themeSQL . $scenarioSQL . $leadinSQL . $stemsSQL;
    $search_string = 'AND (' . substr($search_string, 4) . ')';
  }
  
  if ($_POST['team'] != '') {
    $team_string = ' AND q_group LIKE ?';
    $variables[] = '%' . $_POST['team'] . '%';
    $params .= 's';
  } else {
    $team_string = '';
  }
  
  if ($_POST['owner'] != '' or count($teams) == 0) {
    $user_string = ' AND questions.ownerID=?';
    $variables[] = $_POST['owner'];
    $params .= 'i';
  } else {
    // If no specific owner set lock down by team (apart from SysAdmin).
    if (count($teams) > 0 and $_POST['team'] == '' and strpos($userroles,'SysAdmin') === false) {
      $user_string = ' AND (';
      foreach ($teams as $individual_team) {
        $user_string .= 'q_group LIKE "%' . $individual_team . '%" OR ';
      }
      $user_string .= 'questions.ownerID=' . $userID . ')';
    } else {
      $user_string = '';
    }
  }
  
  if (isset($_POST['status']) and $_POST['status'] != '%') {
    $status_string = ' AND questions.status=?';
    $variables[] = $_POST['status'];
    $params .= 's';
  } else {
    $status_string = '';
  }

  if (isset($_POST['locked']) and $_POST['locked'] != '1') {
    $locked_string = " AND locked IS NULL";
  } else {
    $locked_string = '';
  }

  if ($_POST['question_date'] == 'dont remember') {
    $last_edited = '';
  } else {
    switch ($_POST['question_date']) {
      case 'week':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m"),date("d")-7,date("Y")));
        $to_date = date("YmdHis");
        break;
      case 'month':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
        $to_date = date("YmdHis");
        break;
      case 'year':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y")-1));
        $to_date = date("YmdHis");
        break;
      case 'specify':
        $from_date = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . "000000";
        $to_date = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . "235959";
        break;
    }
    $last_edited = 'AND last_edited>? AND last_edited<?';
    $variables[] = $from_date;
    $variables[] = $to_date;
    $params .= 'ss';
  }
  
  if ($_POST['searchtype'] == '%') {
    $q_type = '';
  } else {
    $q_type = 'AND q_type LIKE ?';
    $variables[] = $_POST['searchtype'];
    $params .= 's';
  }
  
  if ($_POST['bloom'] == '%') {
    $bloom ='';
  } else {
    $bloom = 'AND bloom LIKE ?';
    $variables[] = $_POST['bloom'];
    $params .= 's';
  }
  
  if ($keywordsSQL == '') {
    $result = $mysqli->prepare("SELECT DISTINCT option_text, title, initials, surname, q_type, q_id, theme, scenario_plain, leadin_plain, DATE_FORMAT(last_edited,'%d/%m/%y') AS last_edited, ownerID, locked FROM (questions, users) LEFT JOIN options ON questions.q_id = options.o_id WHERE questions.ownerID=users.id $search_string $team_string $user_string $status_string $locked_string $last_edited $q_type $bloom AND deleted IS NULL ORDER BY leadin_plain, o_id");
  } else {
    $result = $mysqli->prepare("SELECT DISTINCT option_text, title, initials, surname, q_type, questions.q_id, theme, scenario_plain, leadin_plain, DATE_FORMAT(last_edited,'%d/%m/%y') AS last_edited, ownerID, locked FROM (questions, users, keywords_question) LEFT JOIN options ON questions.q_id = options.o_id WHERE questions.q_id=keywords_question.q_id $keywordsSQL AND questions.ownerID=users.id $search_string $team_string $user_string $status_string $locked_string $last_edited $q_type $bloom AND deleted IS NULL ORDER BY leadin_plain, o_id");
  }
  array_unshift($variables, $params);
  foreach($variables as $key => $value) $tmp[$key] = &$variables[$key];
  
  call_user_func_array(array($result,'bind_param'), $tmp);
  $result->execute();
  $result->store_result();
  $result->bind_result($option_text, $title, $initials, $surname, $q_type, $q_id, $theme, $scenario_plain, $leadin_plain, $last_edited, $ownerID, $locked);

  $temp_results = array();
  $hits = 0;
  $old_id = -1;
  while ($row = $result->fetch()) {
    $match = 0;
    if ($old_id != $q_id) {
      $hits++;
    }
    $temp_results[$hits]['q_id'] = $q_id;
    $temp_results[$hits]['title'] = $title;
    $temp_results[$hits]['initials'] = $initials;
    $temp_results[$hits]['surname'] = $surname;
    $temp_results[$hits]['q_type'] = $q_type;
    $temp_results[$hits]['theme'] = strip_tags($theme);
    $temp_results[$hits]['scenario'] = $scenario_plain;
    $temp_results[$hits]['leadin'] = $leadin_plain;
    $temp_results[$hits]['last_edited'] = strip_tags($last_edited);
    $temp_results[$hits]['locked'] = $locked;
    $temp_results[$hits]['ownerID'] = $ownerID;
    $old_id = $q_id;
  }
  $result->close();

  echo "<tr><td style=\"background-color:#F1F5FB\" colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>Questions (" . number_format(count($temp_results)) . "):&nbsp;</strong>" . $_POST['searchterm'] . "</div></td></tr>";
?>
  <tr>
  <td style="background-color:#F1F5FB" align="right">&nbsp;<img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" /></td>
  <td style="background-color:#F1F5FB">&nbsp;Question&nbsp;</td>
  <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Type&nbsp;</td>
  <td style="background-color:#F1F5FB"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Modified&nbsp;</td></tr>
  <tr style="height:4px"><td valign="top" colspan="4"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
  $old_id = -1;
  $old_leadin = '';
  $display_no = 1;
  foreach ($temp_results as $temp_line) {
    if ($temp_results[$display_no]['locked'] != '') {
      echo "<tr id=\"link$display_no\" onmouseover=\"lon($display_no)\" onmouseout=\"loff($display_no)\" onclick=\"selQ('" . $temp_line['q_id'] . "',$display_no, '" . $temp_line['q_type'] . "','menu2c'); return false;\" ondblclick=\"editQuestion('" . $temp_line['q_id'] . "', '" . $temp_line['q_type'] . "'); return false;\"><td><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Question Locked\" /></td>";
    } else {
      echo "<tr id=\"link$display_no\" onmouseover=\"lon($display_no)\" onmouseout=\"loff($display_no)\" onclick=\"selQ('" . $temp_line['q_id'] . "',$display_no, '" . $temp_line['q_type'] . "','menu2b'); return false;\" ondblclick=\"editQuestion('" . $temp_line['q_id'] . "', '" . $temp_line['q_type'] . "'); return false;\"><td></td>";
    }

    $tmp_leadin = trim($temp_line['leadin']);
    if (strlen($tmp_leadin) > 160) $tmp_leadin = substr($tmp_leadin,0,160) . '...';
    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">WARNING: no question lead-in!</span>';
    
    if ($temp_line['ownerID'] == $userID or strpos($userroles,'SysAdmin') !== false) {
      echo "<td style=\"padding-left:6px\">$tmp_leadin <span class=\"owner\">(" . $temp_line['title'] . " " . $temp_line['initials'] . " " . $temp_line['surname'] . ")</span></td>";
    } else {
      echo "<td style=\"padding-left:6px\">$tmp_leadin <span class=\"owner\">(" . $temp_line['title'] . " " . $temp_line['initials'] . " " . $temp_line['surname'] . ")</span></td>";
    }

    echo '<td valign="top" onclick="qOff()">&nbsp;' . fullQuestionType($temp_line['q_type']) . '</td>';
    echo '<td valign="top" onclick="qOff()">&nbsp;' . $temp_line['last_edited'] . '</td></tr>';
    echo "<tr><td colspan=\"3\" style=\"height: 3px\"></td></tr>\n";

    $old_id = $temp_line['q_id'];
    $old_leadin = $temp_line['leadin'];
    $display_no++;
  }
  echo "</table>\n";
  
  if ($hits == 0) {
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;No questions found for specified search criteria.</td></tr></table>\n";
  }
  

  $mysqli->close();
}
?>
</div>
</body>
</html>