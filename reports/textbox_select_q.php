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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $propertyObj->get_paper_type();
$paper = $propertyObj->get_paper_title();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['textboxmarking'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <style type="text/css">
    a {color:blue; text-decoration:none; cursor:pointer}
    p {margin-top:0; padding-top:0}
    td {padding-bottom: 10px}
    .warning {width: 12px; height: 11px; margin-right: 4px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(214);
	
  $candidate_no = 0;
  if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
    // Get how many students took the paper.
    $result = $mysqli->prepare("SELECT DISTINCT lm.userID FROM log_metadata lm INNER JOIN users u ON lm.userID = u.id WHERE lm.paperID = ? AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ? AND lm.started <= ? AND (u.roles LIKE '%Student%' OR u.roles = 'graduate')");
    $result->bind_param('iss', $paperID, $startdate, $enddate);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($result->fetch()) {
      $candidate_no++;
    }
    $result->close();
  }

  $second_mark = array();
  if (isset($_GET['phase']) and $_GET['phase'] == 2) {
    // Get the usernames of papers to second mark.
    $second_mark = textbox_marking_utils::get_remark_users($paperID, $mysqli);
  }

  $phase_description = '';
  if (!isset($_GET['phase'])) {
    $phase_description .= $string['finalisemarks'];
    $tmp_phase = '';
  } elseif ($_GET['phase'] == 1) {
    $phase_description .= $string['primarymarking'];
    $tmp_phase = '&phase=1';
  } elseif ($_GET['phase'] == 2) {
    $phase_description .= $string['secondmarking'];
    $tmp_phase = '&phase=2';
  }

  $out_of = (isset($_GET['phase']) and $_GET['phase'] == 2) ? count($second_mark) : $candidate_no;
  if ($candidate_no > 0) $phase_description .= ": " . number_format($out_of) . " " . $string['candidates'];

  echo "<div id=\"content\">\n";
  
  echo "<div class=\"head_title\">\n";
  echo "<img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />\n";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';
  echo '<div class="page_title">' . $phase_description . '</div>';
  echo "</div>\n";

  echo "<br />\n<div class=\"key\">" . $string['msg'] . "</div>\n";

  echo "<blockquote>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";

  $question_no = 1;
  $result = $mysqli->prepare("SELECT q_id, leadin_plain, q_type FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type != 'info' ORDER BY display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $leadin, $q_type);
  while ($result->fetch()) {
    if ($q_type == 'textbox') {
      if (($paper_type == '0' or $paper_type == '1' or $paper_type == '2') and isset($_GET['phase'])) {
        // Check how many candidates are marked for this question.
        $candidates_marked = 0;
        $marked = $mysqli->prepare("SELECT mark FROM textbox_marking WHERE paperID = ? AND q_id = ? AND logtype = ? AND phase = ?");
        $marked->bind_param('iiii', $paperID, $q_id, $paper_type, $_GET['phase']);
        $marked->execute();
        $marked->bind_result($mark);
        while ($marked->fetch()) {
          if ($mark !== null) {
            $candidates_marked++;
          }
        }
        $marked->close();
      } elseif ($_GET['action'] == 'finalise') {
        $candidates_marked = 0;
        // Check how many candidates are marked for this question.
        $marked = $mysqli->prepare("SELECT mark FROM log$paper_type, log_metadata, users WHERE log$paper_type.metadataID = log_metadata.id AND log_metadata.userID = users.id AND (roles LIKE '%Student%' OR roles = 'graduate') AND paperID = ? AND q_id = ?");
        $marked->bind_param('ii', $paperID, $q_id);
        $marked->execute();
        $marked->bind_result($mark);
        while ($marked->fetch()) {
          if ($mark !== null) {
            $candidates_marked++;
          }
        }
        $marked->close();
      } else {
        $candidates_marked = $candidate_no;
      }

      echo '<tr><td style="text-align:right; vertical-align:top; white-space:nowrap;">';
      if ($candidates_marked < $out_of) {
        echo '<img src="../artwork/small_yellow_warning_icon.gif" class="warning" title="Warning ' . ($candidate_no - $candidates_marked) . ' marks missing" />';
      }
      echo $question_no . '.</td>';
      if ($candidates_marked < $out_of) {
        echo '<td style="background-color:#FFDDDD">';
      } else {
        echo '<td>';
      }
      if ($_GET['action'] == 'finalise') {
        echo "<a href=\"textbox_finalise_marks.php";
      } else {
        echo "<a href=\"textbox_marking.php";
      }
      echo "?q_id=$q_id&qNo=$question_no&paperID=$paperID&startdate=$startdate&enddate=$enddate&folder=" . $_GET['folder'] . "&module=" . $_GET['module'] . "&repcourse=" . $_GET['repcourse'] . "$tmp_phase\">" . trim($leadin) . "</a></td></tr>\n";
    }
    $question_no++;
  }
  $result->close();
  $mysqli->close();
  echo "</table>\n";
?>
</div>
</body>
</html>