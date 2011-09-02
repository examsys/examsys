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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../touchstone/include/staff_auth.inc';
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $paperID = $_GET['paperID'];

  // Get the module ID and calendar year of the OSCE station.
  $result = $mysqli->prepare("SELECT title, surname, first_names, grade, yearofstudy, student_id FROM (users, sid) WHERE id=? AND users.id=sid.userID");
  $result->bind_param('s', $_GET['userID']);
  $result->execute();
  $result->bind_result($title, $surname, $first_names, $grade, $year, $student_id);
  $result->fetch();
  $result->close();

  // Get properties of the paper.
  $result = $mysqli->prepare("SELECT paper_title, bgcolor, fgcolor, labelcolor, themecolor FROM properties WHERE property_id=?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($paper, $bgcolor, $fgcolor, $labelcolor, $themecolor);
  $result->fetch();
  $result->close();
?>
  <html>
  <head>
  <title>OSCE: Frequency Analysis</title>
  <style>
    body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
    .h {background-color:#F1F5FB; color:black}
    .breadcrumb {margin-left:10px; font-size:90%}
    .breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
    .breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
    .breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
    .question {text-align:left; border:1px solid #7F9DB9}
    .rating {width:40px; text-align:right; border:1px solid #7F9DB9}
    .theme {text-align:left; font-size:125%; color:<?php echo $themecolor; ?>; padding-top:10px}
    .overall {border:1px solid #7F9DB9; width:20%; height:35px; text-align:center}
    ul {margin-top:0px; margin-bottom:0px}
  </style>
  <script language="JavaScript">
    function reviewOSCE(userid) {
      var winwidth = 750;
      var winheight = screen.height-80;
      window.open("view_form.php?paperID=<?php echo $paperID; ?>&username="+userid+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }

    function move_in(img_name) {
      document[img_name].src=onImg.src;
    }

    function move_out(img_name) {
      document[img_name].src=offImg.src;
    }

    onImg = new Image;
    onImg.src = '../touchstone/artwork/up_folder_icon_on.gif';
    offImg = new Image;
    offImg.src = '../touchstone/artwork/up_folder_icon_off.gif';
  </script>
  </head>
  
  <body>
<?php
 echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td class=\"h\">";
  if(isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $report_title = 'Frequency Analysis (' . $_GET['repmodule'] . ' students only)';
  } else {
    $report_title = 'Frequency Analysis';
  }
  
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
  echo '<div class="breadcrumb"><a href="../touchstone/index.php">Home</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../touchstone/artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../touchstone/folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../touchstone/artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../touchstone/folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../touchstone/artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../touchstone/paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div>';
  
  echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">$report_title</span></td><td class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../touchstone/artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";

  echo '<tr><td colspan="2" style="height:3px"><img src="../touchstone/artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr></table>';
  
  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"margin:10px; border-collapse:collapse; font-size:100%\"><tr>\n";

  // Query Log4 to get stored ratings per question.
  $old_userID = '';
  $frequencies = array();
  $user_no = 0;
  $result = $mysqli->prepare("SELECT q_id, rating, userID FROM log4 WHERE q_paper=? AND started >= ? AND started <= ? ORDER BY userID");
  $result->bind_param('iss', $_GET['paperID'], $startdate, $enddate);
  $result->execute();
  $result->bind_result($q_id, $rating, $userID);
  while ($row = $result->fetch()) {
    if ($userID != $old_userID) $user_no++;
    if(!isset($frequencies[$q_id])) $frequencies[$q_id] = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0);
    if (isset($frequencies[$q_id][$rating])) {
      $frequencies[$q_id][$rating]++;
    } else {
      $frequencies[$q_id][$rating] = 1;
    }
    $old_userID = $userID;
  }
  $result->close();
  
  // Get the questions.
  $question_no = 1;
  $sub_totals = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0);
  $cell_colors = array('#FFCBCB','#FFE3B3','#C0FFC0');
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, score_method FROM papers, questions WHERE paper=? AND papers.question=questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $score_method);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
      // Header row
      $cols = substr_count($score_method,'|');
      $headings = explode('|',$score_method);
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
      echo "<span style=\"color:$labelcolor\"><img src=\"../touchstone/artwork/notes_icon.gif\" width=\"14\" height=\"14\" border=\"0\" alt=\"note\" />&nbsp;$notes</span><br />\n";
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
  ?>
  </tr></table>

</body>
</html>
