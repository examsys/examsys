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
require '../include/media.inc';
  
if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?")) {
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();

// Get how many screens make up the question paper.
$screen_data = array();
$stmt = $mysqli->prepare("SELECT labs, paper_title, paper_prologue, marking, screen, q_type, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calculator FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND property_id=? AND papers.question=questions.q_id ORDER BY screen");
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($labs, $paper_title, $paper_prologue, $marking, $screen, $q_type, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calculator);
while ($stmt->fetch()) {
  $no_screens = $screen;
  if ($q_type != 'info') {
    if (isset($screen_data[$no_screens] )) {
      $screen_data[$no_screens] += 1;
    } else {
      $screen_data[$no_screens] = 1;
    }
  }
  if ($bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
  if ($fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
  if ($textsize == 'NULL' or $textsize == '') $textsize = 90;
  if ($marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
  if ($themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
  if ($labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
}
$stmt->free_result();
$stmt->close();
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $paper_title ?></title>

  <meta http-equiv="imagetoolbar" content="no">
  <meta http-equiv="imagetoolbar" content="false">
  <style type="text/css">
    body {background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px; margin:0px; border:0px; font-family:Arial,sans-serif; font-size:<?php echo $textsize; ?>%}
    li {margin-left:15px; margin-right:15px; font-size:100%}
    <?php
    if (($bgcolor != 'white' and $bgcolor != '#FFFFFF') or ($fgcolor != 'black' and $fgcolor != '#000000')) {
      echo "select, input {background-color:$bgcolor; color:$fgcolor; font-family:Arial,sans-serif; font-size:100%}\n";
    } else {
      echo "select, input {font-family:Arial,sans-serif; font-size:100%}\n";
    }
    ?>
    table {font-size:100%}
    .paper {margin-left:0px; font-size:180%; color:white; font-weight:bold}
    .question_no {width:40px; text-align:right; vertical-align:top}
    .theme {font-size:150%; padding-left:4px; font-weight:bold; color:<?php echo $themecolor; ?>}
    .notes {font-size:80%; color:<?php echo $labelcolor; ?>}
    .no_marks {color:<?php echo $marks_color; ?>; font-size:80%}
    .active {color:<?php echo $fgcolor; ?>}
    .inactive {color:#C0C0C0}
    .scrno {width:18px; text-align:center; background-color:#003366; font-size:80%}
    .scrnocur {width:18px; text-align:center; background-color:#C00000; font-size:80%}
    .feedback {font-family:Arial,sans-serif; font-style:italic; color:<?php echo $labelcolor; ?>}
  </style>
  <script type="text/javascript">
    function jumpTo() {
      self.location.hash="#<?php echo 'q' . $_GET['qNo']; ?>";
    }
  </script>
</head>
<body onload="jumpTo()">
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php

  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #164994; background-color:#2765AB; background-image:url(\'../artwork/title_gradient.png\'); background-repeat:repeat-y; background-position:center">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  $question_offset = 1;
  if ($no_screens > 1) {
    echo '<table cellspacing="1" cellpadding="1" border="0" style="font-weight:bold; color:white"><tr>';
    for ($i=1; $i<=$no_screens; $i++) {
      echo "<td class=\"scrno\">$i</td>\n";
    }
    echo '</tr></table>';
  }
  echo '</td><td width="160"><img src="../artwork/uni_logo.png" width="160" height="67" alt="UoN Logo" border="0" /></td></tr></table>';

  
  $question_data = $mysqli->prepare("SELECT screen, q_type, q_id, theme, scenario, leadin, q_media, q_media_width, q_media_height, notes, marks_correct, correct_fback FROM (papers, questions, options) WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $question_data->bind_param('i', $_GET['paperID']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $theme, $scenario, $leadin, $q_media, $q_media_width, $q_media_height, $notes, $marks_correct, $correct_fback);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $q_no = 0;
  $old_q_id = 0;
  $old_screen = 1;
  while ($question_data->fetch()) {
    if ($old_screen != $screen) {
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      echo '<tr><td colspan="2"><table cellpadding="0" cellspacing="1" border="0" style="width:100%; height:70px; border-top:1px solid #B5C4DF; background-image:url(\'../artwork/screen_no_background.gif\'); background-repeat:repeat-x">';
      echo "<tr>\n<td width=\"20\">&nbsp;</td>\n";
      echo "<td style=\"vertical-align:top; font-size:90%; font-weight:bold; color:#15428B\">" . $string['screen'] . "&nbsp;$screen</td>\n</tr>\n";
      echo '</table></td></tr>';
    }

    if ($old_q_id != $q_id) {
      if ($q_no+1 == $_GET['qNo'] and $q_type != 'info') {
        $tmp_color = '#FFFFDD';
      } else {
        $tmp_color = $bgcolor;
      }
    
      $li_set = 0;
      echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

      if ($theme != '') echo '<tr><td colspan="2"><p class="theme">' . $theme . '</p></td></tr><tr><td colspan="2">&nbsp;</td></tr>';
      if (trim($notes) != '') echo '<tr><td></td><td class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . strtoupper($string['note']) . ':</strong>&nbsp;' . $notes . '</td></tr>';

      if ($scenario != '') {
        echo "<tr style=\"background-color:$tmp_color\"><td class=\"question_no\">";
        if ($q_type != 'info') {
          $q_no++;
          echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
        }
        if ($calculator == 1) echo '<br /><a href="#" onclick="openCalculator(); return false;"><img src="../artwork/calc.gif" width="18" height="24" alt="Calculator" border="0" /></a>';
        echo "</td><td>$scenario<br />\n<br />";
        $li_set = 1;
      }
      
      if ($q_type == 'info') {
        echo "<tr style=\"background-color:$tmp_color\"><td>";
        $li_set = 0;
      } elseif ($q_type != 'info' and $li_set == 0) {
        $q_no++;
        echo "<tr style=\"background-color:$tmp_color\"><td class=\"question_no\">";
        echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
      }
      if ($li_set == 0) {
        echo "</td><td style=\"background-color:$tmp_color\">";
      }
      if ($q_media != '' and $q_media != NULL) {
        $media_list = explode('|', $q_media);
        $media_list_width = explode('|', $q_media_width);
        $media_list_height = explode('|', $q_media_height);
        for ($i=0; $i<count($media_list); $i++) {
          if ($media_list[$i] != '') {
            echo '<p align="center">' . display_media($media_list[$i], $media_list_width[$i], $media_list_height[$i]) . "</p>\n";
          }
        }
      }
      echo "$leadin</td></tr>\n";
      
      if ($q_type != 'info') {
        echo "<tr style=\"background-color:$tmp_color\"><td></td><td class=\"no_marks\"><br />($marks_correct marks)</td></tr>\n";
        echo "<tr style=\"background-color:$tmp_color\"><td>&nbsp;</td><td class=\"feedback\"><br />" . nl2br($correct_fback) . "</td></tr>\n";
      }
    }    
    $old_q_id = $q_id;
    $old_screen = $screen;
  }

  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

  echo '<table cellpadding="2" cellspacing="0" border="0" style="width:100%; border-top:1px solid #164994; background-color:#2765AB; background-image:url(\'../artwork/title_gradient.png\'); background-repeat:repeat-y; background-position:center">';
  echo '<tr><td style="color:white; font-size:80%; width:250px">&nbsp;&#169; 2011, The University of Nottingham</td><td style="color:white; width:75px; text-align:center">';
  echo '<input type="text" style="background-color:transparent; text-align:center; font-size:80%; color:white; border:0px" id="theTime" size="8" /></td><td align="right">';
  echo '</td></tr></table>';
  $mysqli->close();
?>
</td></tr></table>
</form>
</body>
</html>