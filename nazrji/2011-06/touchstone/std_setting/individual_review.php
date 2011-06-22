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
require '../include/media.inc';
require '../include/std_set_functions.inc';

function ebelDropdown($dropdownID,$selected) {
  $html = "<select name=\"$dropdownID\" onchange=\"recountCategories();\">\n";
  $html .= "<option value=\"0\"></option>\n";
  for ($individual_category=0; $individual_category<=100; $individual_category++) {
    if ($individual_category == ($selected * 100)) {
      $html .= "<option value=\"" . ($individual_category / 100) . "\" selected>$individual_category%</option>\n";
    } else {
      $html .= "<option value=\"" . ($individual_category / 100) . "\">$individual_category%</option>\n";
    }
  }
  $html .= "</select>\n";
  return $html;
}

if (isset($_POSR['paperID'])) {
  $paperID = $_POST['paperID'];
} else {
  $paperID = $_GET['paperID'];
}
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Standards Setting<?php echo " $cfg_install_type"; ?></title>
<?php
// Get any questions to exclude.
$excluded = array();
$result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($q_id, $parts);
while ($row = $result->fetch()) {
  $excluded[$q_id] = $parts;
}
$result->close();

// Get how many screens make up the question paper.
$screen_data = array();
$result = $mysqli->prepare("SELECT DISTINCT paper_title, paper_type, paper_prologue, marking, screen, leadin, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional FROM (properties, papers, questions) WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=? ORDER BY screen, p_id");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($paper_title, $paper_type, $paper_prologue, $marking, $screen, $leadin, $start_date, $end_date, $bgcolor, $fgcolor, $themecolor, $labelcolor, $bidirectional);
while ($row = $result->fetch()) {
  $no_screens = strval($screen);
  if (isset($screen_data[$no_screens])) {
    $screen_data[$no_screens] += 1;
  } else {
    $screen_data[$no_screens] = 1;
  }
}
$result->close();
$current_screen = 1;
?>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style type="text/css">
  body {width:100%; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px;margin:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
  pre{width:90%}
  li {margin-left:15px; margin-right:15px; font-family:Arial,sans-serif; font-size:100%}
  select, input {font-size:100%}
  table {font-size:100%}
  .raised_tbl {background-color:#5582D2; border-left:solid #90C8FF 1px; border-right:solid #003060 1px; border-top:solid #90C8FF 1px; border-bottom: solid #003060 1px}
  .paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:white; font-weight:bold}
  .question_no {width:40px; text-align:right; vertical-align:top}
  .theme {font-size:150%; font-weight:bold; color:<?php echo $themecolor; ?>; padding-left:20px}
  .notes {font-size:80%; color: <?php echo $labelcolor; ?>}
  .no_marks {color:#808080; font-size:80%}
  .active {color:<?php echo $fgcolor; ?>}
  .inactive {color:#C0C0C0}
  .heading {background-color:#EBEADB; color:black; font-family:Arial,sans-serif}
</style>

<script src="../javascript/ie_fix.js" type="text/javascript"></script>
<script language="JavaScript" src="../javascript/flash_include.js"></script>
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript">
<?php
  if ($_GET['method'] == 'ebel') {
?>
  function load_grid(type) {
    // Add one to all values to get the correct selectedIndex value.
    if (type == 'BMedSci') {
      document.questions.EE.selectedIndex = 66;
      document.questions.EI.selectedIndex = 61;
      document.questions.EN.selectedIndex = 56;
      document.questions.ME.selectedIndex = 61;
      document.questions.MI.selectedIndex = 56;
      document.questions.MN.selectedIndex = 51;
      document.questions.HE.selectedIndex = 56;
      document.questions.HI.selectedIndex = 51;
      document.questions.HN.selectedIndex = 46;
    } else if (type == 'BMBS') {
      document.questions.EE.selectedIndex = 81;
      document.questions.EI.selectedIndex = 61;
      document.questions.EN.selectedIndex = 56;
      document.questions.ME.selectedIndex = 56;
      document.questions.MI.selectedIndex = 51;
      document.questions.MN.selectedIndex = 36;
      document.questions.HE.selectedIndex = 46;
      document.questions.HI.selectedIndex = 36;
      document.questions.HN.selectedIndex = 31;
    }
    recountCategories();
    
    return false;
  }

  function roundNumber(num, dec) {
    var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    return result;
  }

  function recountCategories() {
    var EE = 0;
    var EI = 0;
    var EN = 0;
    var ME = 0;
    var MI = 0;
    var MN = 0;
    var HE = 0;
    var HI = 0;
    var HN = 0;

    var origEE = 0;
    var origEI = 0;
    var origEN = 0;
    var origME = 0;
    var origMI = 0;
    var origMN = 0;
    var origHE = 0;
    var origHI = 0;
    var origHN = 0;

    var question_no = parseInt(document.questions.stdIDNo.value);
    for (i=0; i<question_no; i++) {
      switch (document.getElementById('valstd' + i).value) {
        case 'EE':
          EE++;
          break;
        case 'EI':
          EI++;
          break;
        case 'EN':
          EN++;
          break;
        case 'ME':
          ME++;
          break;
        case 'MI':
          MI++;
          break;
        case 'MN':
          MN++;
          break;
        case 'HE':
          HE++;
          break;
        case 'HI':
          HI++;
          break;
        case 'HN':
          HN++;
          break;
      }
      switch (document.getElementById('valstd' + i).value) {
        case 'EE':
        case 'exclude_EE':
          origEE++;
          break;
        case 'EI':
        case 'exclude_EI':
          origEI++;
          break;
        case 'EN':
        case 'exclude_EN':
          origEN++;
          break;
        case 'ME':
        case 'exclude_ME':
          origME++;
          break;
        case 'MI':
        case 'exclude_MI':
          origMI++;
          break;
        case 'MN':
        case 'exclude_MN':
          origMN++;
          break;
        case 'HE':
        case 'exclude_HE':
          origHE++;
          break;
        case 'HI':
        case 'exclude_HI':
          origHI++;
          break;
        case 'HN':
        case 'exclude_HN':
          origHN++;
          break;
      }
    }
    document.questions.ee.value = EE + ' marks';
    if (origEE != EE) {
      document.questions.origee.value = origEE;
      document.questions.origee2.value = origEE;
    } else {
      document.questions.origee.value = '';
      document.questions.origee2.value = '';
    }

    document.questions.ei.value = EI + ' marks';
    if (origEI != EI) {
      document.questions.origei.value = origEI;
      document.questions.origei2.value = origEI;
    } else {
      document.questions.origei.value = '';
      document.questions.origei2.value = '';
    }

    document.questions.en.value = EN + ' marks';
    if (origEN != EN) {
      document.questions.origen.value = origEN;
      document.questions.origen2.value = origEN;
    } else {
      document.questions.origen.value = '';
      document.questions.origen2.value = '';
    }

    document.questions.me.value = ME + ' marks';
    if (origME != ME) {
      document.questions.origme.value = origME;
      document.questions.origme2.value = origME;
    } else {
      document.questions.origme.value = '';
      document.questions.origme2.value = '';
    }

    document.questions.mi.value = MI + ' marks';
    if (origMI != MI) {
      document.questions.origmi.value = origMI;
      document.questions.origmi2.value = origMI;
    } else {
      document.questions.origmi.value = '';
      document.questions.origmi2.value = '';
    }

    document.questions.mn.value = MN + ' marks';
    if (origMN != MN) {
      document.questions.origmn.value = origMN;
      document.questions.origmn2.value = origMN;
    } else {
      document.questions.origmn.value = '';
      document.questions.origmn2.value = '';
    }

    document.questions.he.value = HE + ' marks';
    if (origHE != HE) {
      document.questions.orighe.value = origHE;
      document.questions.orighe2.value = origHE;
    } else {
      document.questions.orighe.value = '';
      document.questions.orighe2.value = '';
    }

    document.questions.hi.value = HI + ' marks';
    if (origHI != HI) {
      document.questions.orighi.value = origHI;
      document.questions.orighi2.value = origHI;
    } else {
      document.questions.orighi.value = '';
      document.questions.orighi2.value = '';
    }

    document.questions.hn.value = HN + ' marks';
    if (origHN != HN) {
      document.questions.orighn.value = origHN;
      document.questions.orighn2.value = origHN;
    } else {
      document.questions.orighn.value = '';
      document.questions.orighn2.value = '';
    }

    document.questions.easy_total.value = (EE + EI + EN) + ' marks';
    document.questions.medium_total.value = (ME + MI + MN) + ' marks';
    document.questions.hard_total.value = (HE + HI + HN) + ' marks';
    document.questions.essential_total.value = (EE + ME + HE) + ' marks';
    document.questions.important_total.value = (EI + MI + HI) + ' marks';
    document.questions.nice_total.value = (EN + MN + HN) + ' marks';

    document.questions.easy2_total.value = (EE + EI + EN) + ' marks';
    document.questions.medium2_total.value = (ME + MI + MN) + ' marks';
    document.questions.hard2_total.value = (HE + HI + HN) + ' marks';
    document.questions.essential2_total.value = (EE + ME + HE) + ' marks';
    document.questions.important2_total.value = (EI + MI + HI) + ' marks';
    document.questions.nice2_total.value = (EN + MN + HN) + ' marks';

    document.questions.ee2.value = EE + ' marks';
    document.questions.ei2.value = EI + ' marks';
    document.questions.en2.value = EN + ' marks';
    document.questions.me2.value = ME + ' marks';
    document.questions.mi2.value = MI + ' marks';
    document.questions.mn2.value = MN + ' marks';
    document.questions.he2.value = HE + ' marks';
    document.questions.hi2.value = HI + ' marks';
    document.questions.hn2.value = HN + ' marks';

    var cut_marks = 0;
    cut_marks += EE * document.questions.EE.value * 100;
    cut_marks += EI * document.questions.EI.value * 100;
    cut_marks += EN * document.questions.EN.value * 100;
    cut_marks += ME * document.questions.ME.value * 100;
    cut_marks += MI * document.questions.MI.value * 100;
    cut_marks += MN * document.questions.MN.value * 100;
    cut_marks += HE * document.questions.HE.value * 100;
    cut_marks += HI * document.questions.HI.value * 100;
    cut_marks += HN * document.questions.HN.value * 100;
    var total_marks = EE + EI + EN + ME + MI + MN + HE + HI + HN;
    var cut_score = (cut_marks / total_marks) * 100;
    //if (cut_score > 0 && total_marks > 0) {
      document.questions.cut_score.value = 'paper marks=' + document.getElementById('total_marks').value + ',  review marks=' + total_marks + ',  cut score=' + roundNumber(cut_score/100,1) + '%';
    //}

    cut_marks = 0;
    cut_marks += EE * document.questions.EE2.value * 100;
    cut_marks += EI * document.questions.EI2.value * 100;
    cut_marks += EN * document.questions.EN2.value * 100;
    cut_marks += ME * document.questions.ME2.value * 100;
    cut_marks += MI * document.questions.MI2.value * 100;
    cut_marks += MN * document.questions.MN2.value * 100;
    cut_marks += HE * document.questions.HE2.value * 100;
    cut_marks += HI * document.questions.HI2.value * 100;
    cut_marks += HN * document.questions.HN2.value * 100;
    var total_marks = EE + EI + EN + ME + MI + MN + HE + HI + HN;
    var cut_score = (cut_marks / total_marks) * 100;
    //if (cut_score > 0 && total_marks > 0) {
       document.questions.cut_score2.value = 'paper marks=' + document.getElementById('total_marks').value + ',  reviewed marks=' + total_marks + ',  cut score=' + roundNumber(cut_score/100,1) + '%';
    //}
  }
<?php
  }
?>
</script>
</head>
<?php
  if (isset($_GET['module'])) {
    $module = $_GET['module'];
  } else {
    $module = '';
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

  if ($_GET['method'] == 'ebel') {
    echo "<body onload=\"recountCategories();\">\n";
  } else {
    echo "<body>\n";
  }

  echo "<form method=\"post\" name=\"questions\" action=\"record_review.php?paperID=$paperID&method=" . $_GET['method'] . "&module=$module&folder=$folder\">\n";

  $reviews = array();
  $setterID = (!empty($_GET['setterID'])) ? $_GET['setterID'] : '';
  $date_id = (!empty($_GET['dateID'])) ? $_GET['dateID'] : '';
  
  if ($setterID != '') {
    $tmp_date_id = $date_id;
    $result = $mysqli->prepare("SELECT std_set, rating, questionID FROM standards_setting WHERE paperID=? AND setterID=? AND std_set=?");
    $result->bind_param('iss', $_GET['paperID'], $setterID, $tmp_date_id);
    $result->execute();
    $result->bind_result($std_set, $rating, $questionID);
    while ($row = $result->fetch()) {
      $reviews[$questionID] = $rating;
    }
    $result->close();
  }
  
  // Load default setting from the Questions table and save to reviews array if no existing data
  $result = $mysqli->prepare("SELECT question, std FROM (papers, questions) WHERE paper=? AND papers.question=questions.q_id");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($questionID, $std);
  while ($row = $result->fetch()) {
    if ($setterID == '') $reviews[$questionID] = $std;
    echo "<input type=\"hidden\" name=\"old" . $questionID . "\" value=\"$std\" />\n";
  }
  $result->close();
?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td valign="top">
<?php

  echo "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a>";
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">Standards Setting</a></div>";
  if ($_GET['method'] == 'modified_angoff') {
    $helpID = 98;
    echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">Modified Angoff method</div>';
  } elseif ($_GET['method'] == 'ebel') {
    $helpID = 99;
    echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">Ebel method</div>';
  }
  echo "</td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp($helpID); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
  echo "<tr style=\"height:4px\"><td colspan=\"2\" valign=\"top\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n";

  switch ($_GET['method']) {
    case 'angoff_yes_no':
      $std_instruction = 'For each question use the light blue dropdown lists to indicate the whether a <strong>borderline</strong> (minimally competent) candidate is expected to get the question correct (yes) or incorrect (no).';
      break;
    case 'modified_angoff':
      $std_instruction = 'For each question use the light blue dropdown lists to indicate the percentage of <strong>borderline</strong> (minimally competent) candidates expected to get each question correct.';
      break;
    case 'ebel':
      $std_instruction = '<strong>Step 1:</strong><br />For each question use the light blue dropdown lists firstly to indicate the <strong>difficulty</strong> of the question (easy, medium, or hard) and then secondly to indicate the question\'s <strong>importance</strong> (essential, important, or nice to know).';
      break;
  }
?>
  <br />
  <div align="center">
  <table cellpadding="4" cellspacing="0" border="0" width="90%" style="background-color:#E4EEFC; border:1px solid #B5C4DF; text-align:left">
  <tr>
  <td style="margin:0px"><?php echo $std_instruction; ?></td>
  </tr>
  </table>
  </div>
  <br />
<?php
  $result = $mysqli->prepare("SELECT screen, q_type, q_id, score_method, marks, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, correct_fback FROM (papers, questions, options) WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($screen, $q_type, $q_id, $score_method, $marks, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $correct_fback);

  $num_rows = $result->num_rows;
  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $old_theme = '';
  $old_screen = 1;
  $old_correct_fback = '';
  $question_offset = 1;
  $total_marks = 0;
  $prologue_show = 1;
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  while ($row = $result->fetch()) {
    if ($prologue_show == 1 and $current_screen == 1 and $paper_prologue != '') {
      echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
      $prologue_show = 0;
    }

    if ($question_no == 0) echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    if ($old_q_id != $q_id) {          // New Question
      // Print the options of the previous question
      $li_set = 0;
      if ($old_leadin != '') {
        if ($li_set == 1) echo "</td></tr>\n";
        display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded);

        if ($old_screen != $screen) {
          echo '<tr><td colspan="2"><table cellpadding="0" cellspacing="1" border="0" style="width:100%; height:70px; border-top:1px solid #B5C4DF; background-image:url(\'../artwork/screen_no_background.gif\'); background-repeat:repeat-x">';
          echo "<tr>\n<td width=\"20\">&nbsp;</td>\n";
          echo "<td style=\"vertical-align:top; font-size:90%; font-weight:bold; color:#15428B\">Screen&nbsp;$screen</td>\n</tr>\n";
          echo '</table></td></tr>';
        }
      }
      if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

      if ($theme != '') {
        if ($old_q_type == 'likert') echo '</table><br /><table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
        echo '<tr><td colspan="2" class="theme">' . $theme . '</td></tr>';
      }

      if ($notes != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $notes . '</td></tr>';

      if ($scenario != '' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert' and $q_type != 'calculation') {
        echo '<tr><a name="' . ($question_no + $question_offset) . '"></a><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td valign="top"><p>' . $scenario . '</p>';
        $li_set = 1;
      }
      if ($q_media != '' and $q_media != NULL and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'extmatch') {
        if (substr($q_media, -4) == '.gif' or substr($q_media, -4) == '.jpg' or substr($q_media, -4) == 'jpeg' or substr($q_media, -4) == '.png') {
          if ($li_set == 0) echo '<tr><a name="' . ($question_no + $question_offset) . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
          $li_set = 1;
          echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height) . "</p>\n";
        } else {
          if ($li_set == 0) {
            echo '<tr><a name="' . ($question_no + $question_offset) . '"></a><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
          }
          $li_set = 1;
          echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height) . "</p>\n";
        }
      }
      if ($q_type != 'likert' and $q_type != 'calculation' and $q_type != 'info') {
        if ($li_set == 0) {
          echo '<tr><a name="' . ($question_no + $question_offset) . '"></a><td class="question_no">' . ($question_no + $question_offset) . '.&nbsp;</td><td>';
        }
        $li_set = 1;
        echo '<p>' . $leadin . '</p>';
      }
      if ($q_type == 'info') {
        if ($li_set == 0) echo '<tr><td colspan="2" style="padding-left:20px; padding-right:20px">' . $leadin;
        $li_set = 1;
        $question_no--;
      }

      $old_leadin = $leadin;
      $old_scenario = $scenario;
      $old_notes = $notes;
      $old_q_type = $q_type;
      $old_q_id = $q_id;
      $old_theme = $theme;
      $old_screen = $screen;
      $old_correct_fback = $correct_fback;
      $options_array = array();          // Clear options array
      $question_no++;
    }

    $options_array[] = array('q_type'=>$q_type, 'score_method'=>$score_method, 'correct'=>$correct, 'scenario'=>$scenario, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks'=>$marks);
  }         // End of While loop
  $result->close();

  // Print the options for the last question on the screen.
  display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded);

  echo '</td></tr></table></td></tr>';
  echo "<tr><td colspan=\"2\" style=\"border-top: dotted #808080 1px; color:#808080; font-size:90%; font-weight:bold\">&nbsp;</td>\n</tr>\n";
  echo '</table>';
  if ($_GET['method'] == 'ebel') {
    if ($setterID != '') {
      $query_string = $mysqli->query("SELECT percentage FROM ebel WHERE setterID=" . $setterID . " AND date_set='" . $date_id . "' ORDER BY id");
      if ($query_string->num_rows > 0) {
        while ($row = $query_string->fetch_assoc()) {
          $ebel[] = $row['percentage'];
        }
      }
      $query_string->close(); 
    }
    if(empty($ebel)) $ebel = array('','','','','','','','','','','','','','','','','','');

    echo "<br />\n<div align=\"center\">\n";
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"90%\" style=\"background-color:#E4EEFC; border: 1px solid #B5C4DF; text-align:left\">\n";
    echo "<tr>\n<td style=\"margin:0px\"><strong>Step 2: Pass Grid</strong><br />For each category (e.g. easy/essential, easy/important, etc) specify the percentage of <strong>borderline candidates</strong> expected to get questions in this category correct.<br />\n<blockquote style=\"margin-top:8px; margin-bottom:8px\"><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;<a href=\"\" onclick=\"return load_grid('BMedSci')\" style=\"color:blue\">Load default BMedSci grid</a><br /><img src=\"../artwork/bullet_outline.gif\" width=\"16\" height=\"16\" alt=\"bullet\" />&nbsp;<a href=\"\" onclick=\"return load_grid('BMBS')\" style=\"color:blue\">Load default BMBS grid</a></blockquote></td>\n</tr>\n</table>\n</div>\n<br />\n";

    echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td>&nbsp;</td><td style=\"width:200px; text-align:center\"><strong>Essential</strong></td><td style=\"width:200px; text-align:center\"><strong>Important</strong></td><td style=\"width:200px; text-align:center\"><strong>Nice to Know</strong></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Easy</strong></td><td style=\"text-align:center; background-color:#F8F8F2\"><input type=\"text\" style=\"text-align:right; background-color:#F8F8F2; border:0px; color:red; text-decoration:line-through\" name=\"origee\" size=\"3\" value=\"0\" /><input type=\"text\" style=\"text-align:right; background-color:#F8F8F2; border:0px\" name=\"ee\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EE',$ebel[0]) . "</td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origei\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"ei\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EI',$ebel[1]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origen\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"en\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EN',$ebel[2]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"easy_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Medium</strong></td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px solid red; color:red; text-decoration:line-through\" name=\"origme\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"me\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('ME',$ebel[3]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origmi\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"mi\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MI',$ebel[4]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"origmn\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"mn\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MN',$ebel[5]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"medium_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Hard</strong></td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"orighe\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"he\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HE',$ebel[6]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"orighi\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"hi\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HI',$ebel[7]) . "</td><td style=\"text-align:center; background-color:#C8C8A6\"><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px; color:red; text-decoration:line-through\" name=\"orighn\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px\" name=\"hn\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HN',$ebel[8]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"hard_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"essential_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"important_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"nice_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\" colspan=\"3\"><input type=\"text\" style=\"border:0px; text-align:center\" name=\"cut_score\" size=\"70\" value=\"cut score=0%\" /></td></tr>\n";
    echo "</table>\n</div>\n<br />\n";

    echo "<br />\n<div align=\"center\">\n";
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"90%\" style=\"background-color:#E4EEFC; border: 1px solid #B5C4DF; text-align:left\">\n";
    echo "<tr>\n<td style=\"margin:0px\"><strong>Step 3: Distinction Grid</strong><br />For each category (e.g. easy/essential, easy/important, etc) specify the percentage of <strong>distinction candidates</strong> expected to get questions in this category correct.<br />";
    ?>
    <blockquote style="margin-top:8px; margin-bottom:8px">
    <input type="radio" name="distinction_type" value="1"<?php if ($ebel[9] > 0) echo ' checked'; ?> /> Use grid below<br />
    <input type="radio" name="distinction_type" value="2"<?php if ($ebel[9] === '0') echo ' checked'; ?> /> Use top 20%<br />
    <input type="radio" name="distinction_type" value="3"<?php if ($ebel[9] === NULL) echo ' checked'; ?> /> Do not apply<br />
    </blockquote>
    <?php
    echo "</td>\n</tr>\n</table>\n</div>\n<br />\n";

    echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td>&nbsp;</td><td style=\"width:200px; text-align:center\"><strong>Essential</strong></td><td style=\"width:200px; text-align:center\"><strong>Important</strong></td><td style=\"width:200px; text-align:center\"><strong>Nice to Know</strong></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Easy</strong></td><td style=\"text-align:center; background-color:#F8F8F2\"><input type=\"text\" style=\"text-align:right; border:0px; color:red; text-decoration:line-through; background-color:#F8F8F2\" name=\"origee2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; border:0px; background-color:#F8F8F2\" name=\"ee2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EE2',$ebel[9]) . "</td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origei2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"ei2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EI2',$ebel[10]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origen2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"en2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EN2',$ebel[11]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"easy2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Medium</strong></td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origme2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"me2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('ME2',$ebel[12]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origmi2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"mi2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MI2',$ebel[13]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"origmn2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"mn2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MN2',$ebel[14]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"medium2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>Hard</strong></td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"orighe2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"he2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HE2',$ebel[15]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"orighi2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"hi2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HI2',$ebel[16]) . "</td><td style=\"text-align:center; background-color:#C8C8A6\"><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px; color:red; text-decoration:line-through\" name=\"orighn2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px\" name=\"hn2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HN2',$ebel[17]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"hard2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"essential2_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"important2_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"nice2_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\" colspan=\"3\"><input type=\"text\" style=\"border:0px; text-align:center\" name=\"cut_score2\" size=\"70\" value=\"cut score=0%\" /></td></tr>\n";
    echo "</table>\n</div>\n<br />\n";
  }
  if ($_GET['method'] == 'modified_angoff') {
    echo '<input type="hidden" name="method" value="Modified Angoff" />';
  } else {
    echo '<input type="hidden" name="method" value="Ebel" />';
  }
  echo '<input type="hidden" name="module" value="' . $module . '" />';
  echo '<input type="hidden" name="folder" value="' . $folder . '" />';
  echo '<input type="hidden" name="paperID" value="' . $paperID . '" />';
  echo '<input type="hidden" name="setterID" value="' . $setterID . '" />';
  echo '<input type="hidden" name="dateID" value="' . $date_id . '" />';
  echo '<input type="hidden" name="stdIDNo" value="' . $stdID . '" />';
?>
<div align="center">
<table cellpadding="2" cellspacing="0" border="0">
<tr><td style="text-align:center; color:#808080">ALT + S</td><td style="text-align:center; color:#808080">ALT + C</td><td></td><td></td></tr>
<tr><td><input type="submit" name="submit" value="Save &amp; Exit" accesskey="S" style="width:160px" /></td><td><input type="submit" name="continue" value="Save &amp; Continue" accesskey="C" style="width:160px" /></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input onclick="javascript:window.location='../paper/details.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>'" type="button" name="cancel" value="Cancel" style="width:90px" /></td></tr>

<tr><td colspan="2" style="text-align:center">
<?php
  if (isset($_COOKIE['caabanksave']) and $_COOKIE['caabanksave'] == '1') {
    echo '<input type="checkbox" name="banksave" value="1" checked />&nbsp;Save ratings into question bank';
  } else {
    echo '<input type="checkbox" name="banksave" value="1" />&nbsp;Save ratings into question bank';
  }
  $mysqli->close();
?>
</td><td colspan="2"></td></tr>
</table>
</div>
<br />
<input type="hidden" name="total_marks" id="total_marks" value="<?php echo $total_marks - $std_excluded; ?>" />
</form>
</body>
</html>