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

require '../include/staff_auth.inc';
require '../include/print_functions.inc';

function ordinal_suffix($number) {
  $suffix = $number;
  switch($number) {
    case 1:
      $suffix .= 'st';
      break;
    case 2:
      $suffix .= 'nd';
      break;
    case 3:
      $suffix .= 'rd';
      break;
    default:
      $suffix .= 'th';
      break;
  }
  return $suffix;
}

function display_media($filename,$width,$height,$imageid) {
  // Is the file an image or something else (e.g. RasMol)?
  if (strtolower(substr($filename, -4)) == '.gif' or strtolower(substr($filename, -4)) == '.jpg' or strtolower(substr($filename, -4)) == 'jpeg' or strtolower(substr($filename, -4)) == '.png') {
    $html = "<img src=\"../media/$filename\" width=\"$width\" height=\"$height\" border=\"0\" alt=\"Image\" />";
  } elseif (strtolower(substr($filename, -4)) == '.wav' or strtolower(substr($filename, -4)) == '.wma' or strtolower(substr($filename, -4)) == '.mid') {
    $html = "<img src=\"audio_icon_32.gif\" width=\"32\" height=\"32\" alt=\"Audio File\" /><a href=\"../media/$filename\">Audio Clip</a>";
  } elseif (strtolower(substr($filename, -4)) == '.doc' or strtolower(substr($filename, -4)) == '.ppt' or strtolower(substr($filename, -4)) == '.xls' or strtolower(substr($filename, -4)) == '.pdf') {
    $html = "<iframe src=\"../media/$filename\" width=\"$width\" height=\"$height\" align=\"center\">Your browser does not support iframes!</iframe>";
  } elseif (strtolower(substr($filename, -3)) == '.rm') {
    $html = "<embed src=\"../media/$filename\" width=\"240\" height=\"180\" controls=\"ImageWindow autostart=false\" console=\"TheConsole\"><br />\n";
    $html .= "<embed src=\"../media/$filename\" width=\"240\" height=\"30\" controls=\"ControlPanel autostart=false\" console=\"TheConsole\"><br />\n";
    $html .= "<embed src=\"../media/$filename\" width=\"240\" height=\"20\" controls=\"StatusBar autostart=false\" console=\"TheConsole\">";
  } elseif (strtolower(substr($filename, -4)) == '.avi') {
    $html = "<embed src=\"/touchstone/media/$filename\" width=\"$width\" height=\"$height\" autoplay=true loop=false></embed>";
  } else {
    $html = "<embed src=\"../media/$filename\" width=\"$width\" height=\"$height\" border=\"1\" alt=\"Data File\"></embed>";
  }

  return $html;
}

// Extract the get variables.
if (isset($_GET['no_screens'])) {
  $no_screens = $_GET['no_screens'];
  $current_screen = $_GET['current_screen'];
  $sessionid = $_GET['sessionid'];
  $previous = $_GET['previous'];
  $userid = $_GET['userid'];
  $surname = $_GET['surname'];
}

// Get how many screens make up the question paper.
if (!isset($no_screens)) {
  $screen_data = array();
  for ($i=1; $i<=40; $i++) {
    $screen_data[$i] = 0;
  }
  $result = $mysqli->prepare("SELECT DISTINCT paper_title, marking, screen, leadin, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional, q_type FROM properties, papers, questions WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=? ORDER BY screen");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $marking, $screen, $leadin, $start_date, $end_date, $bgcolor, $fgcolor, $themecolor, $labelcolor, $bidirectional, $q_type);
  while ($row = $result->fetch()) {
    $no_screens = strval($screen);
    if($q_type != 'info') {
      $screen_data[$no_screens] += 1;
    }
  }
  $result->close();
  $current_screen = 1;
}
echo "<html>\n<head>\n<title>$paper_title</title>\n";
?>
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">

<style type="text/css">
  body {background-color: <?php echo $bgcolor; ?>; color: <?php echo $fgcolor; ?>; padding: 0px; margin-top: 0px; margin-left:0px; margin-right:0px; margin-bottom:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
  li {margin-left: 15px; margin-right: 15px; font-family:Arial,sans-serif; font-size:100%}
  select, input {font-size:100%}
  table {font-size:100%}
  .raised_tbl {background-color:white; border:none}
  .paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:black; font-weight:bold}
  .question_no {width:40px; text-align:right; vertical-align:top}
  .theme {font-size:150%; font-weight:bold; color:<?php echo $themecolor; ?>}
  .notes {font-size:90%; color:<?php echo $labelcolor; ?>}
  .no_marks {color:#808080; font-size:80%}
  .active {color:<?php echo $fgcolor; ?>}
  .inactive {color:#C0C0C0}
</style>
<script language="JavaScript" src="../javascript/start.js"></script>
<script language="JavaScript" src="../javascript/flash_include.js"></script>
</head>
<body onload="javascript:window.print()">
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php
  $question_offset = 1;
  echo '<table cellpadding="4" cellspacing="0" border="0" width="100%">';
  echo '<tr><td class="raised_tbl"><div class="paper">' . $paper_title . '</div>';
  echo '</td><td align="center" class="raised_tbl" width="167"><img src="../artwork/black_uon_logo.png" width="167" height="70" alt="Logo" border="0" /></td></tr></table>';

  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $marks = 0;
  $old_theme = '';
  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  $result = $mysqli->prepare("SELECT paper_type, q_type, q_id, score_method, marks, paper_prologue, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes FROM properties, papers, questions, options WHERE properties.property_id=papers.paper AND paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_type, $q_type, $q_id, $score_method, $marks, $paper_prologue, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes);
  $li_set = 0;
  while ($row = $result->fetch()) {
    if ($question_no == 0 and $current_screen == 1 and $paper_prologue != '') {
      echo '<p style="text-align: justify">' . $paper_prologue . '</p>';
    }

    if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    if ($old_q_id != $q_id) {          // New Question
      $qn_set = false;
      
      // Print the options of the previous question
      if ($old_leadin != '') {
        display_options($options_array, $old_q_id, $old_theme);
      }
      if ($li_set == 1) {
        echo "</td></tr>\n";
        $li_set = 0;
      }
      
      if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
      
      if ($theme != '') {
        if ($old_q_type == 'likert') echo '</table>\n<br />\n<table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
        echo '<tr><td colspan="2"><p class="theme">' . $theme . '</p></td></tr>';
      }
      
      if (trim($notes) != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;' . make_para_if_not(trim($notes)) . '</td></tr>';

      if ($scenario != '' and $q_type != 'info' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert' and $q_type != 'sct') {
        echo '<tr><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>' . make_para_if_not(trim($scenario));
        echo "</td></tr>\n";
        $qn_set = true;
      }
      if ($q_media != '' and $q_media != NULL and $q_type != 'sct' and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'extmatch' and $q_type != 'matrix') {
        $q_no = (!$qn_set) ? ($question_no + $question_offset).'.' : '';
        if ($li_set == 0) echo '<tr><td class="question_no">' . $q_no . '.&nbsp;</td><td>';
        $li_set = 1;
        $qn_set = true;
        if (substr($q_media, -4) == '.gif' or substr($q_media, -4) == '.jpg' or substr($q_media, -4) == 'jpeg' or substr($q_media, -4) == '.png') {
          echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height,$question_no) . "</p>\n";
        } else {
          echo "<p>" . display_media($q_media,$q_media_width,$q_media_height,$question_no) . "</p>\n";
        }
      }
      if ($q_type != 'hotspot' and $q_type != 'likert' and $q_type != 'calculation' and $q_type != 'info' and $q_type != 'sct') {
        $q_no = (!$qn_set) ? ($question_no + $question_offset).'.' : '';
        if ($li_set == 0) echo '<tr><td class="question_no">' . $q_no . '&nbsp;</td><td>';
        $li_set = 1;
        echo make_para_if_not(trim($leadin));
      }
      if ($q_type == 'info') {
        if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:10px; padding-right:10px">';
        if ($q_media != '' and $q_media != NULL) {
          echo '<p align="center">' . display_media($q_media,$q_media_width,$q_media_height,$question_no) . "</p>\n";
        }
        echo make_para_if_not(trim($leadin));
        $li_set = 1;
        $question_no--;
      }

      $old_leadin = $leadin;
      $old_q_type = $q_type;
      $old_q_id = $q_id;
      $old_theme = $theme;
      $options_array = array();          // Clear options array
      $question_no++;
    }

    $options_array[] = array('q_type'=>$q_type, 'score_method'=>$score_method, 'correct'=>$correct, 'scenario'=>$scenario, 'leadin'=>$leadin, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks'=>$marks, 'paper_type'=>$paper_type);
  }         // End of While loop
  $result->free_result();
  $result->close();

  // Print the options for the last question on the screen.
  display_options($options_array, $old_q_id, $old_theme);

  $current_screen++;
  echo "</table>\n";

  echo "</td>\n</tr>\n</table>\n\n";
  $mysqli->close();
?>
</body>
</html>