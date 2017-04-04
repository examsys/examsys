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
* Displays Frequency Analysis report for an OSCE station.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID   = check_var('paperID', 'GET', true, false, true);
$startdate = check_var('startdate', 'GET', true, false, true);
$enddate   = check_var('enddate', 'GET', true, false, true);

// Get properties of the paper.
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper			= $propertyObj->get_paper_title();
$labelcolor = $propertyObj->get_labelcolor();
$themecolor = $propertyObj->get_themecolor();
?>
<html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['frequencyanalysis']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
    .question {text-align:left; border:1px solid #909090}
    .rating {width:40px; text-align:right; border:1px solid #909090}
    .theme {text-align:left; font-size:125%; color:<?php echo $themecolor; ?>; padding-top:10px}
    .overall {border:1px solid #909090; width:20%; height:35px; text-align:center}
    ul {margin-top:0; margin-bottom:0}
  </style>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function reviewOSCE(userid) {
      var winwidth = 750;
      var winheight = screen.height-80;
      window.open("view_form.php?paperID=<?php echo $paperID; ?>&username="+userid+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  </script>
  </head>

  <body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
	
  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = $string['frequencyanalysis'] . ' (' . $_GET['repmodule'] . ' ' . $string['studentsonly'] . ')';
  } else {
    $report_title = $string['frequencyanalysis'];
  }

  echo "<div class=\"head_title\">\n";
	echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';

  echo "<div class=\"page_title\">$report_title</div>";
  echo '</div>';

  // Query Log4 to get stored ratings per question.
  $old_userID = '';
  $frequencies = array();
  $user_no = 0;
  $result = $mysqli->prepare("SELECT log4.q_id, log4.rating, l4o.userID FROM log4 INNER JOIN log4_overall l4o ON log4.log4_overallID = l4o.id WHERE l4o.q_paper = ? AND l4o.started >= ? AND l4o.started <= ? ORDER BY l4o.userID");
  $result->bind_param('iss', $_GET['paperID'], $startdate, $enddate);
  $result->execute();
  $result->bind_result($q_id, $rating, $userObject->get_user_ID());
  while ($result->fetch()) {
    if ($userObject->get_user_ID() != $old_userID) $user_no++;
    if (!isset($frequencies[$q_id])) $frequencies[$q_id] = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0);
    if (isset($frequencies[$q_id][$rating])) {
      $frequencies[$q_id][$rating]++;
    } else {
      $frequencies[$q_id][$rating] = 1;
    }
    $old_userID = $userObject->get_user_ID();
  }
  $result->close();

  if ($user_no == 0) {
		echo $notice->info_strip('This paper has not been attempted by anyone.', 100);
  } else {
		echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"margin:10px; border-collapse:collapse\"><tr>\n";
    
		// Get the questions.
    $question_no = 1;
    $sub_totals = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0);
    $cell_colors = array('#FFCBCB', '#FFE3B3', '#C0FFC0');
    $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, display_method FROM papers, questions WHERE paper = ? AND papers.question = questions.q_id ORDER BY display_pos");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $display_method);
    while ($result->fetch()) {
      if ($question_no == 1) {
        // Header row
        $cols = substr_count($display_method, '|');
        $headings = explode('|', $display_method);
        echo '<tr><td></td>';
        for ($i=0; $i<$cols; $i++) {
          echo "<td colspan=\"2\" style=\"text-align:center; color:$labelcolor; font-weight:bold\">" . $headings[$i] . "</td>";
        }
        echo "</tr>\n";
      }
      if (trim($theme) != '') {
        echo "<tr><td colspan=\"4\" class=\"theme\">$theme</td></tr>\n";
      }
      echo "<tr id=\"row_" . $question_no . "\"><td class=\"question\">";
      if (trim($notes) != '') {
        echo "<span style=\"color:$labelcolor\"><img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"note\" />&nbsp;$notes</span><br />\n";
      }
      echo "$leadin</td>";

      for ($i=0; $i<$cols; $i++) {
        if (!isset($frequencies[$q_id][$i]) or $frequencies[$q_id][$i] == '') $frequencies[$q_id][$i] = 0;
        echo "<td class=\"rating\" style=\"background-color:" . $cell_colors[$i] . "\">" . $frequencies[$q_id][$i] . "</td><td class=\"rating\" style=\"background-color:" . $cell_colors[$i] . "\">" . round(($frequencies[$q_id][$i]/$user_no) * 100) . "%</td>";
      }
      echo "</tr>\n";
      $question_no++;
    }
    $result->close();
    $mysqli->close();
  }
  ?>
  </tr></table>

</body>
</html>
