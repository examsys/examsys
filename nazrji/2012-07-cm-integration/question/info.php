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
  require '../include/errors.inc';
  
  check_var('q_id', 'GET', true, false);
  
  function multiPartQuestion($type) {
    if ($type == 'blank' or $type == 'dichotomous' or $type == 'extmatch' or $type == 'hotspot' or $type == 'labelling' or $type == 'matrix') {
      return true;
    } else {
      return false;
    }
  }
  
  function displayParts($perform_data, $q_type) {
    $html = '';
    $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii');
    if (multiPartQuestion($q_type)) {
      for ($i=0; $i<count($perform_data); $i++) {
        $html .= $numerals[$i] . '.<br />';
      }
    }
    
    return $html;
  }
  
  function displayP($perform_data, $q_type) {
    $html = '';
    
    if (multiPartQuestion($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= pWarning(number_format($single_data['p']/100, 2)) . '<br />';
      }
    } else {
      $html = pWarning(number_format($perform_data[1]['p']/100, 2));
    }
    
    return $html;
  }
  
  function pWarning($value) {
    if ($value < 0.2) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  function displayD($perform_data, $q_type) {
    $html = '';
    
    if (multiPartQuestion($q_type)) {
      foreach ($perform_data as $single_data) {
        $html .= dWarning(number_format($single_data['d']/100, 2)) . '<br />';
      }
    } else {
      $html = dWarning(number_format($perform_data[1]['d']/100, 2));
    }
    
    return $html;
  }
    
  function dWarning($value) {
    if ($value <= 0.15) {
      return '<span style="color:#C00000">' . $value . '</span>';
    } else {
      return $value;
    }
  }
    
  function check4Copies($mysqlidb) {
    $row_number = 0;
    
    // Get the ID of the original question.
    $copy_data = $mysqlidb->prepare("SELECT old FROM track_changes WHERE type='Copied Question' AND typeID=? LIMIT 1");
    $copy_data->bind_param('i', $_GET['q_id']);
    $copy_data->execute();
    $copy_data->bind_result($copyID);
    $copy_data->store_result();
    $copy_data->fetch();
    $copy_data->close();
        
    if (isset($copyID)) {
      // Look up what paper it was used on.
      $copy_question_no = 0;
      $row_no = 1;
      $copy_data = $mysqlidb->prepare("SELECT property_id, paper_title, question, q_type FROM (papers, properties, questions) WHERE properties.property_id=papers.paper AND papers.question=questions.q_id AND paper=(select paper from papers where question=? limit 1) ORDER BY screen, display_pos");
      $copy_data->bind_param('i', $copyID);
      $copy_data->execute();
      $copy_data->bind_result($copy_paperID, $copy_paper_title, $copy_question, $copy_q_type);
      $copy_data->store_result();
      while ($copy_data->fetch()) {
        if ($copy_q_type != 'info') $row_number++;
        if ($copy_question == $copyID) $copy_question_no = $row_number;
      }
      $copy_data->close();
      if ($copy_question_no == 0) {
        echo "<tr><td>Copy of</td><td>Question ID #$copyID</td></tr>\n";
      } else {
        echo "<tr><td>Copy of</td><td>Question No $copy_question_no. on <a href=\"\" onclick=\"loadPaper('$copy_paperID')\">$copy_paper_title</a></td></tr>\n";
      }
    } else {
      echo "<tr><td></td><td></td></tr>\n";
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Information<?php echo " $cfg_install_type"; ?></title>
  <style type="text/css">
    body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; color:black; font-size:90%}
    table {font-size:100%}
    a {color:blue}
    th {text-align:left}
    td {vertical-align:top}
    .screen {font-size:90%; color:#808080}
    .num {text-align:right; padding-right:6px}
  </style>
  
  <script type="text/javascript">
    function loadPaper(paperID) {
      window.opener.location = "../paper/details.php?paperID=" + paperID;
      window.close();
    }
    
    function loadModule(moduleID) {
      window.opener.location = "../folder/details.php?module=" + moduleID;
      window.close();
    }
  </script>
</head>

<body>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
<tr>
<td colspan="2" valign="middle" style="background-color:white; text-align:left; border-bottom:1px solid #CCD9EA">
<table cellpadding="0" cellspacing="0" border="0">
<tr><td><img src="../artwork/lrg_info_icon.png" width="37" height="37" alt="Information" /></td><td style="font-family:Arial,sans-serif; font-size:16pt; font-weight:bold; color:#5582D2">&nbsp;&nbsp;<?php echo $string['questioninformation']; ?></td></tr>
</table>
</td>
</tr>
<?php
  $line_no = 0;
  $icons = array('formative','progress','summative','survey','osce','offline');
  
  $performance = array();

  $result = $mysqli->prepare("SELECT paperID, cohort_size, DATE_FORMAT(taken,\"$cfg_short_date\"), part_no, p, d FROM performance_main, performance_details WHERE performance_main.id=performance_details.perform_id AND q_id=?");
  $result->bind_param('i', $_GET['q_id']);
  $result->execute();
  $result->bind_result($paperID, $cohort_size, $taken, $part_no, $p, $d);
  while ($result->fetch()) {
    $performance[$paperID][$part_no] = array('cohort'=>$cohort_size, 'taken'=>$taken, 'p'=>$p, 'd'=>$d);
  }
  $result->close();
  
  $result = $mysqli->prepare("SELECT email, title, surname, initials, paper_title, paper_type, paper, screen, DATE_FORMAT(creation_date,\"$cfg_long_date_time\") AS creation_date, DATE_FORMAT(last_edited,\"$cfg_long_date_time\") AS last_edited, q_group, DATE_FORMAT(locked,\"$cfg_long_date_time\") AS locked, properties.deleted, status, q_type FROM (users, papers, questions, properties) WHERE properties.property_id=papers.paper AND users.id=questions.ownerID AND question=? AND papers.question=questions.q_id");
  $result->bind_param('i', $_GET['q_id']);
  $result->execute();
  $result->bind_result($email, $title, $surname, $initials, $paper_title, $paper_type, $paper, $screen, $creation_date, $last_edited, $q_group, $locked, $deleted, $status, $q_type);
  $result->store_result();
  if ($result->num_rows > 0) {
    while ($result->fetch()) {
      if ($line_no == 0) {
        echo "<tr><td width=\"60\" style=\"vertical-align:top\">" . $string['author'] . "</td><td>$title $initials $surname (<a href=\"mailto:$email\">$email</a>)</td></tr>\n";
        echo "<tr><td>" . $string['status'] . "</td><td>" . $string[strtolower($status)] . "</td></tr>\n";
        echo "<tr><td>" . $string['created'] . "</td><td>$creation_date</td></tr>\n";
        echo "<tr><td>" . $string['modified'] . "</td><td>$last_edited</td></tr>\n";
        echo "<tr><td>" . $string['locked'] . "</td><td>$locked</td></tr>\n";
        $split_group = explode(';',$q_group);
        echo "<tr><td style=\"vertical-align:top\">" . $string['teams'] . "</td><td>";
        foreach ($split_group as $individual_group) {
          echo "<a href=\"\" onclick=\"loadModule('$individual_group')\">$individual_group</a><br />";
        }
        echo "</td></tr>\n";
        
        check4Copies($mysqli);
      
        echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
        echo "<tr><td colspan=\"2\">" . $string['followingpapers'] . "</td></tr>\n";
        echo "</table>\n<div style=\"margin:5px; display:block; height:250px; overflow-y:scroll; border:1px solid #95AEC8; font-size:90%; background-color:white\">\n<table border=\"0\" style=\"width:100%\">";
        echo "<tr><th></th><th>Paper Name</th><th>Screen No</th><th>Exam Date</th><th>Cohort</th><th></th><th>P</th><th>D</th></tr>\n";
      }
      echo "<tr><td><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"0\" /></td>";
      $title_split = explode('[deleted', $paper_title);
      if (isset($title_split[1])) {
        echo "<td><a href=\"\" style=\"color:#808080\" onclick=\"loadPaper('$paper')\">" . $title_split[0] . "</a></td>";
      } else {
        echo "<td><a href=\"\" onclick=\"loadPaper('$paper')\">" . $title_split[0] . "</a></td>";
      }
      if ($deleted != '') {
        echo "<td style=\"color:red\">&lt;deleted " . str_replace(']','',$title_split[1]) . "&gt;</td>";
      } else {
        echo "<td class=\"num\">$screen</td>";
      }
      
      if (isset($performance[$paper][1]['taken'])) {
        echo "<td>" . $performance[$paper][1]['taken'] . "</td><td class=\"num\">" . $performance[$paper][1]['cohort'] . "</td><td>" . displayParts($performance[$paper], $q_type) . "</td><td class=\"num\">" . displayP($performance[$paper], $q_type) . "</td><td class=\"num\">" . displayD($performance[$paper], $q_type) . "</td>";
      } else {
        echo "<td></td><td></td><td></td><td></td><td></td>";
      }
      echo "</tr>\n";
      $line_no++;
    }
    echo "</table>\n</div>\n";
  } else {
    $question_data = $mysqli->prepare("SELECT email, title, surname, initials, DATE_FORMAT(creation_date,\"%d/%m/%Y %H:%i\") AS creation_date, DATE_FORMAT(last_edited,\"%d/%m/%Y %H:%i\") AS last_edited, q_group FROM (users, questions) WHERE users.id=questions.ownerID AND q_id=?");
    $question_data->bind_param('i', $_GET['q_id']);
    $question_data->execute();
    $question_data->bind_result($email, $title, $surname, $initials, $creation_date, $last_edited, $q_group);
    $question_data->store_result();
    while ($question_data->fetch()) {
      if (strpos($userroles,'Demo') !== false) {
        $owner = 'Dr J, Bloggs (<a href="">joe.bloggs@uni.ac.uk</a>)';
      } else {
        $owner = '$title $initials $surname (<a href=\"mailto:$email\">$email</a>)';
      }
      echo "<tr><td width=\"90\" valign=\"top\"><strong>" . $string['author'] . "</strong></td><td>$owner</td></tr>\n";
      echo "<tr><td><strong>" . $string['created'] . "</strong></td><td>$creation_date</td></tr>\n";
      echo "<tr><td><strong>" . $string['modified'] . "</strong></td><td>$last_edited</td></tr>\n";
      echo "<tr><td><strong>" . $string['locked'] . "</strong></td><td>$locked</td></tr>\n";
      if ($q_group == '') $q_group = '<span style="color:#808080">N/A</span>';
      echo "<tr><td><strong>" . $string['teams'] . "</strong></td><td>$q_group</td></tr>\n";

      check4Copies($mysqli);
      
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      echo "<tr><td colspan=\"2\"><strong>" . $string['followingpapers'] . "</strong>\n";
      echo "<br />" . $string['notused'] . "</td></tr>\n</table>";
    }
    $question_data->close();
  }
  $result->close();
  $mysqli->close();
?>
<br />
<div align="center">
<form>
<input type="button" style="width: 120px" name="ok" onclick="javascript:window.close();" value="<?php echo $string['close']; ?>" />
</form>
</div>
</body>
