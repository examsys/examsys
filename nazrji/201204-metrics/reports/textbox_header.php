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
require '../classes/stateutils.class.php';


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Textbox Marking</title>
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
  a {color:blue}
  </style>
  <link rel="stylesheet" type="text/css" href="../css/breadcrumb.css" />
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript">
    function hideMarked() {
      if (document.getElementById('hidemarked').checked == 1) {
        setting = " checked";
      } else {
        setting = "";
      }
      
      parent.body.$('.marked').toggle();

    }
  </script>
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
    $result = $mysqli->prepare("SELECT DISTINCT log_metadata.userID FROM (log$paper_type, log_metadata) WHERE log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID AND log$paper_type.userID=log_metadata.userID AND q_paper=? AND log_metadata.started>=? AND log_metadata.started<=? AND student_grade NOT IN ('university lecturer','University Secretary','IT Support','University Admin','Technical Staff')");
    $result->bind_param('iss', $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($row = $result->fetch()) {
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

  echo "<table class=\"header\" style=\"font-size:90%\">\n<tr><th style=\"height:52px\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php" target="_top">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '" target="_top">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '" target="_top">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '" target="_top">' . $paper . '</a></div><div style="margin-left:10px; font-size:220%; color:black; font-weight:bold">' . $phase_description . '</div></th>';
  echo "<th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(1); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a><br /><input class=\"chk\" type=\"checkbox\" name=\"hidemarked\" id=\"hidemarked\" value=\"1\" onclick=\"hideMarked();\"";
  if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') echo ' checked';
  echo "  /> " . $string['hidemarked'] . "</th></tr>\n";
  echo "<tr><td colspan=\"2\" class=\"bevel\"></th></tr>\n";
  echo "</table>\n";
?>
</body>
</html>