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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['textboxmarking'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
    table {font-size:100%}
    a {color:blue;text-decoration:none;cursor:pointer}
    p {margin-top:0px;padding-top:0px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_type AS paper_type, paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_type, $paper);
  $result->fetch();
  $result->close();
  
  $candidate_no = 0;
  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
    // Get how many students took the paper.
    $result = $mysqli->prepare("SELECT DISTINCT log_metadata.userID FROM (log$paper_type, log_metadata) WHERE log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND q_paper=? AND DATE_ADD(log_metadata.started, INTERVAL 2 MINUTE)>=? AND log_metadata.started<=? AND student_grade NOT IN ('university lecturer','University Secretary','IT Support','University Admin','Technical Staff')");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($result->fetch()) {
      $candidate_no++;
    }
    $result->close();
  }

  if (!isset($_GET['phase'])) {
    $phase_description = $string['finalisemarks'];
    $tmp_phase = '';
  } elseif ($_GET['phase'] == 1) {
    $phase_description = $string['primarymarking'];
    $tmp_phase = '&phase=1';
  } elseif ($_GET['phase'] == 2) {
    $phase_description = $string['secondmarking'];
    $tmp_phase = '&phase=2';
  }
  if ($candidate_no > 0) $phase_description .= " - $candidate_no " . $string['candidates'];

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }

  echo "<table class=\"header\" style=\"font-size:90%\">\n<tr><th>";
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div><div style="margin-left:10px; font-size:220%; color:black; font-weight:bold">' . $phase_description . '</div></th>';
  echo "<th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(214); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
  echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";

  echo "<br />\n<div style=\"margin:20px; background-color:#E4EEFC; border:1px solid #B5C4DF; padding:10px; font-size:90%\">" . $string['msg'] . "</div>\n";

  echo "<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";

  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, leadin, q_type FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type!='info' ORDER BY display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $leadin, $q_type);
  while ($result->fetch()) {
    if ($q_type == 'textbox') {
      if (($paper_type == '1' or $paper_type == '2') and isset($_GET['phase'])) {
        // Check how many candidates are marked for this question.
        $marked = $mysqli->prepare("SELECT COUNT(id) FROM textbox_marking WHERE paperID=? AND q_id=? AND logtype=? AND phase=?");
        $marked->bind_param('iiii', $_GET['paperID'], $q_id, $paper_type, $_GET['phase']);
        $marked->execute();
        $marked->bind_result($candidates_marked);
        $marked->fetch();
        $marked->close();
      } else {
        $candidates_marked = $candidate_no;
      }
      
      echo '<tr><td style="text-align:right; vertical-align:top">';
      if ($candidates_marked < $candidate_no) echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="Warning ' . ($candidate_no - $candidates_marked) . ' marks missing" border="0" />';
      echo $question_no . '.</td>';
      if ($candidates_marked == $candidate_no) {
        echo '<td>';
      } else {
        echo '<td style="background-color:#FFC0C0">';
      }
      if ($_GET['action'] == 'finalise') {
        echo "<a href=\"textbox_finalise_marks.php";
      } else {
        if ($_GET['ws'] == '0') {
          echo "<a href=\"textbox_mark_frame.php";
        } else {
          echo "<a href=\"textbox_mark_frame_ws.php";
        }
      }
      echo "?ws=1&q_id=$q_id&qNo=$question_no&paperID=" . $_GET['paperID'] . "&startdate=" . $_GET['startdate'] . "&enddate=" . $_GET['enddate'] . "&folder=" . $_GET['folder'] . "&module=" . $_GET['module'] . "&repcourse=" . $_GET['repcourse'] . "$tmp_phase\">$leadin</a></td></tr>\n";
    }
    $question_no++;
  }
  $result->close();
  $mysqli->close();
  echo "</table>\n";
?>
</body>
</html>