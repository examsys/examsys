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
require '../include/media.inc';
require '../include/std_set_functions.inc';

function ebelDropdown($dropdownID, $selected) {
  $html = "<select name=\"$dropdownID\" onchange=\"recountCategories();\">\n";
  $html .= "<option value=\"0\"></option>\n";
  $selected = intval($selected * 100);
  for ($individual_category=0; $individual_category<=100; $individual_category++) {
    if ($individual_category == $selected) {
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
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Standards Setting<?php echo ' ' . $cfg_install_type; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
// Get any questions to exclude.
$excluded = array();
$result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($q_id, $parts);
while ($result->fetch()) {
  $excluded[$q_id] = $parts;
}
$result->close();

// Get how many screens make up the question paper.
$screen_data = array();
$result = $mysqli->prepare("SELECT DISTINCT paper_title, paper_type, paper_prologue, marking, screen, leadin, start_date, end_date, bgcolor, fgcolor, themecolor, labelcolor, bidirectional FROM (properties, papers, questions) WHERE papers.question=questions.q_id AND properties.property_id=papers.paper AND paper=? ORDER BY screen, p_id");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($paper_title, $paper_type, $paper_prologue, $marking, $screen, $leadin, $start_date, $end_date, $bgcolor, $fgcolor, $themecolor, $labelcolor, $bidirectional);
while ($result->fetch()) {
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
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
body {width:100%; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>; padding:0px;margin:0px; border:0px; font-family:Arial,sans-serif; font-size:90%}
pre {width:90%}
li {margin-left:15px; margin-right:15px; font-family:Arial,sans-serif; font-size:100%}
select, input {font-size:100%}
table {font-size:100%}
p {margin-top:0px; padding-top:0px}
.raised_tbl {background-color:#5582D2; border-left:solid #90C8FF 1px; border-right:solid #003060 1px; border-top:solid #90C8FF 1px; border-bottom: solid #003060 1px}
.paper {margin-left:0px; font-family:Arial,sans-serif; font-size:180%; color:white; font-weight:bold}
.question_no {width:40px; text-align:right; vertical-align:top}
.theme {font-size:150%; font-weight:bold; color:<?php echo $themecolor; ?>; padding-left:20px}
.notes {font-size:80%; color: <?php echo $labelcolor; ?>}
.mk {padding-top:4px; color:#808080; font-size:80%}
.active {color:<?php echo $fgcolor; ?>}
.inactive {color:#C0C0C0}
.heading {background-color:#EBEADB; color:black; font-family:Arial,sans-serif}
.matrix {border:1px solid #808080; border-collapse:collapse}
.matrix td {border:1px solid #808080}
.extmatch li {padding-bottom:14px; vertical-align:text-bottom; list-style-type:upper-alpha}
.scr_no {margin-left:25px}
.screenbrk {
  color:#15428B;
  font-weight:bold;
  font-size:90%;
  height:70px;
  width:100%;
  border-top: 1px solid #B5C4DF;
  background: -moz-linear-gradient(top, #E4EEFC, #FFFFFF);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#E4EEFC', endColorstr='#FFFFFF');
}
</style>

<script language="JavaScript" src="../js/jquery-1.6.1.min.js"></script>
<script language="JavaScript" src="../tools/mee/mee/js/mee_src.js"></script>
<script language="JavaScript" src="../js/ie_fix.js"></script>
<script language="JavaScript" src="../js/flash_include.js"></script>
<script language="JavaScript" src="../js/staff_help.js"></script>
<script language="JavaScript">
<?php
  if ($_GET['method'] == 'ebel') {
?>
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
      var question_marks = parseInt(document.getElementById('std' + i + '_marks').value);
      switch (document.getElementById('valstd' + i).value) {
        case 'EE':
          EE += question_marks;
          break;
        case 'EI':
          EI += question_marks;
          break;
        case 'EN':
          EN += question_marks;
          break;
        case 'ME':
          ME += question_marks;
          break;
        case 'MI':
          MI += question_marks;
          break;
        case 'MN':
          MN += question_marks;
          break;
        case 'HE':
          HE += question_marks;
          break;
        case 'HI':
          HI += question_marks;
          break;
        case 'HN':
          HN += question_marks;
          break;
      }
      switch (document.getElementById('valstd' + i).value) {
        case 'EE':
        case 'exclude_EE':
          origEE += question_marks;
          break;
        case 'EI':
        case 'exclude_EI':
          origEI += question_marks;
          break;
        case 'EN':
        case 'exclude_EN':
          origEN += question_marks;
          break;
        case 'ME':
        case 'exclude_ME':
          origME += question_marks;
          break;
        case 'MI':
        case 'exclude_MI':
          origMI += question_marks;
          break;
        case 'MN':
        case 'exclude_MN':
          origMN += question_marks;
          break;
        case 'HE':
        case 'exclude_HE':
          origHE += question_marks;
          break;
        case 'HI':
        case 'exclude_HI':
          origHI += question_marks;
          break;
        case 'HN':
        case 'exclude_HN':
          origHN += question_marks;
          break;
      }
    }
    document.questions.ee.value = EE + ' <?php echo $string['marks']; ?>';
    if (origEE != EE) {
      document.questions.origee.value = origEE;
      document.questions.origee2.value = origEE;
    } else {
      document.questions.origee.value = '';
      document.questions.origee2.value = '';
    }

    document.questions.ei.value = EI + ' <?php echo $string['marks']; ?>';
    if (origEI != EI) {
      document.questions.origei.value = origEI;
      document.questions.origei2.value = origEI;
    } else {
      document.questions.origei.value = '';
      document.questions.origei2.value = '';
    }

    document.questions.en.value = EN + ' <?php echo $string['marks']; ?>';
    if (origEN != EN) {
      document.questions.origen.value = origEN;
      document.questions.origen2.value = origEN;
    } else {
      document.questions.origen.value = '';
      document.questions.origen2.value = '';
    }

    document.questions.me.value = ME + ' <?php echo $string['marks']; ?>';
    if (origME != ME) {
      document.questions.origme.value = origME;
      document.questions.origme2.value = origME;
    } else {
      document.questions.origme.value = '';
      document.questions.origme2.value = '';
    }

    document.questions.mi.value = MI + ' <?php echo $string['marks']; ?>';
    if (origMI != MI) {
      document.questions.origmi.value = origMI;
      document.questions.origmi2.value = origMI;
    } else {
      document.questions.origmi.value = '';
      document.questions.origmi2.value = '';
    }

    document.questions.mn.value = MN + ' <?php echo $string['marks']; ?>';
    if (origMN != MN) {
      document.questions.origmn.value = origMN;
      document.questions.origmn2.value = origMN;
    } else {
      document.questions.origmn.value = '';
      document.questions.origmn2.value = '';
    }

    document.questions.he.value = HE + ' <?php echo $string['marks']; ?>';
    if (origHE != HE) {
      document.questions.orighe.value = origHE;
      document.questions.orighe2.value = origHE;
    } else {
      document.questions.orighe.value = '';
      document.questions.orighe2.value = '';
    }

    document.questions.hi.value = HI + ' <?php echo $string['marks']; ?>';
    if (origHI != HI) {
      document.questions.orighi.value = origHI;
      document.questions.orighi2.value = origHI;
    } else {
      document.questions.orighi.value = '';
      document.questions.orighi2.value = '';
    }

    document.questions.hn.value = HN + ' <?php echo $string['marks']; ?>';
    if (origHN != HN) {
      document.questions.orighn.value = origHN;
      document.questions.orighn2.value = origHN;
    } else {
      document.questions.orighn.value = '';
      document.questions.orighn2.value = '';
    }

    document.questions.easy_total.value = (EE + EI + EN) + ' <?php echo $string['marks']; ?>';
    document.questions.medium_total.value = (ME + MI + MN) + ' <?php echo $string['marks']; ?>';
    document.questions.hard_total.value = (HE + HI + HN) + ' <?php echo $string['marks']; ?>';
    document.questions.essential_total.value = (EE + ME + HE) + ' <?php echo $string['marks']; ?>';
    document.questions.important_total.value = (EI + MI + HI) + ' <?php echo $string['marks']; ?>';
    document.questions.nice_total.value = (EN + MN + HN) + ' <?php echo $string['marks']; ?>';

    document.questions.easy2_total.value = (EE + EI + EN) + '<?php echo $string['marks']; ?>';
    document.questions.medium2_total.value = (ME + MI + MN) + ' <?php echo $string['marks']; ?>';
    document.questions.hard2_total.value = (HE + HI + HN) + ' <?php echo $string['marks']; ?>';
    document.questions.essential2_total.value = (EE + ME + HE) + ' <?php echo $string['marks']; ?>';
    document.questions.important2_total.value = (EI + MI + HI) + ' <?php echo $string['marks']; ?>';
    document.questions.nice2_total.value = (EN + MN + HN) + ' <?php echo $string['marks']; ?>';

    document.questions.ee2.value = EE + ' <?php echo $string['marks']; ?>';
    document.questions.ei2.value = EI + ' <?php echo $string['marks']; ?>';
    document.questions.en2.value = EN + ' <?php echo $string['marks']; ?>';
    document.questions.me2.value = ME + ' <?php echo $string['marks']; ?>';
    document.questions.mi2.value = MI + ' <?php echo $string['marks']; ?>';
    document.questions.mn2.value = MN + ' <?php echo $string['marks']; ?>';
    document.questions.he2.value = HE + ' <?php echo $string['marks']; ?>';
    document.questions.hi2.value = HI + ' <?php echo $string['marks']; ?>';
    document.questions.hn2.value = HN + ' <?php echo $string['marks']; ?>';

    var paper_marks = document.getElementById('total_marks').value;
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
    var cut_score = (cut_marks / paper_marks) * 100;
    document.questions.cut_score.value = '<?php echo $string['papermarks']; ?>=' + paper_marks + ',  <?php echo $string['reviewmarks']; ?>=' + total_marks + ',  <?php echo $string['cutscore']; ?>=' + roundNumber(cut_score/100,1) + '%';

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
    var cut_score = (cut_marks / paper_marks) * 100;
    document.questions.cut_score2.value = '<?php echo $string['papermarks']; ?>=' + document.getElementById('total_marks').value + ',  <?php echo $string['reviewmarks']; ?>=' + total_marks + ',  <?php echo $string['cutscore']; ?>=' + roundNumber(cut_score/100,1) + '%';
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

  echo "\n<table class=\"header\">\n";
  echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>";
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
  }
  echo "&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">" . $string['standardssetting'] . "</a></div>";
  if ($_GET['method'] == 'modified_angoff') {
    $helpID = 98;
    echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">' . $string['modifiedangoffmethod'] . '</div>';
  } elseif ($_GET['method'] == 'ebel') {
    $helpID = 99;
    echo '<div style="font-family:Arial,sans-serif; font-size:200%; color:black; font-weight:bold; margin-left:10px">' . $string['ebelmethod'] . '</div>';
  }
  echo "</th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp($helpID); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";
  echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";

  switch ($_GET['method']) {
    case 'modified_angoff':
      $std_instruction = $string['modangoffstep1'];
      break;
    case 'ebel':
      $std_instruction = $string['step1'];
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
  $old_leadin = '';
  $old_scenario = '';
  $old_notes = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $old_theme = '';
  $old_screen = 1;
  $old_correct_fback = '';
  $total_marks = 0; //Altered as a globle in display_options !!!
  $std_excluded = 0;
  $prologue_show = 1;
  $options_array = array();
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

  $result = $mysqli->prepare("SELECT screen, q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, correct_fback FROM (papers, questions, options) WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($screen, $q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $correct_fback);
  while ($result->fetch()) {
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
        if (count($options_array) > 0) display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded, false);
        if ($old_screen != $screen) {
          echo '<tr><td colspan="2">';
          echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
          echo '</td></tr>';
        }
      }
      $question_no++;
      if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

      if ($theme != '') {
        if ($old_q_type == 'likert') echo '</table><br /><table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
        echo '<tr><td colspan="2" class="theme">' . $theme . '</td></tr>';
      }

      if (trim($notes) != '' and $q_type != 'likert') echo '<tr><td></td><td class="notes"><img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $notes . '</td></tr>';

      if (trim($scenario) != '' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert' and $q_type != 'calculation') {
        echo '<tr><a name="' . $question_no . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td valign="top">' . $scenario . '<br /><br />';
        $li_set = 1;
      }
      if ($q_media != '' and $q_media != NULL and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'extmatch') {
        if (substr($q_media, -4) == '.gif' or substr($q_media, -4) == '.jpg' or substr($q_media, -4) == 'jpeg' or substr($q_media, -4) == '.png') {
          if ($li_set == 0) echo '<tr><a name="' . $question_no . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
          $li_set = 1;
          echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height) . "</p>\n";
        } else {
          if ($li_set == 0) {
            echo '<tr><a name="' . $question_no . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
          }
          $li_set = 1;
          echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height) . "</p>\n";
        }
      }
      if ($q_type != 'likert' and $q_type != 'calculation' and $q_type != 'info' and $q_type != 'hotspot') {
        if ($li_set == 0) {
          echo '<tr><a name="' . $question_no . '"></a><td class="question_no">' . $question_no . '.&nbsp;</td><td>';
        }
        $li_set = 1;
        echo $leadin;
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
      
    }

    $options_array[] = array('q_type'=>$q_type, 'score_method'=>$score_method, 'display_method'=>$display_method, 'correct'=>$correct, 'scenario'=>$scenario, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect);
  }         // End of While loop
  $result->close();

  // Print the options for the last question on the screen.
  if (count($options_array) > 0) display_options($options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded, false);

  echo '</td></tr></table></td></tr>';
  echo "<tr><td colspan=\"2\" style=\"border-top: dotted #808080 1px; color:#808080; font-size:90%; font-weight:bold\">&nbsp;</td>\n</tr>\n";
  echo '</table>';
  if ($_GET['method'] == 'ebel') {
    if ($setterID != '') {
      $result = $mysqli->prepare("SELECT percentage FROM ebel WHERE setterID=? AND date_set=? ORDER BY id");
      $result->bind_param('is', $setterID, $date_id);
      $result->execute();
      $result->bind_result($percentage);
      while ($result->fetch()) {
        $ebel[] = round($percentage, 2);
      }
      $result->close();
    }
    
    if (empty($ebel)) {
      $templateID = '';
      // If empty look to see if there is a default grid to load
      $result = $mysqli->prepare("SELECT ebel_grid_template FROM modules WHERE moduleid=?");
      $result->bind_param('s', $_GET['module']);
      $result->execute();
      $result->bind_result($templateID);
      $result->fetch();
      $result->close();
      if ($templateID == '') {
        $ebel = array('','','','','','','','','','','','','','','','','','');
      } else {
        $result = $mysqli->prepare("SELECT EE, EI, EN, ME, MI, MN, HE, HI, HN, EE2, EI2, EN2, ME2, MI2, MN2, HE2, HI2, HN2, name FROM ebel_grid_templates WHERE id=?");
        $result->bind_param('i', $templateID);
        $result->execute();
        $result->bind_result($ebel[0], $ebel[1], $ebel[2], $ebel[3], $ebel[4], $ebel[5], $ebel[6], $ebel[7], $ebel[8], $ebel[9], $ebel[10], $ebel[11], $ebel[12], $ebel[13], $ebel[14], $ebel[15], $ebel[16], $ebel[17], $name);
        $result->fetch();
        $result->close();
        
        for ($i=0; $i<18; $i++) {
          $ebel[$i] = round($ebel[$i] / 100, 2);
        }
      }
    }
    
    echo "<br />\n<div align=\"center\">\n";
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"90%\" style=\"background-color:#E4EEFC; border: 1px solid #B5C4DF; text-align:left\">\n";
    echo "<tr>\n<td style=\"margin:0px\">" . $string['step2'] . "<br />&nbsp;</td>\n</tr>\n</table>\n</div>\n<br />\n";

    echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td>&nbsp;</td><td style=\"width:200px; text-align:center\"><strong>" . $string['essential'] . "</strong></td><td style=\"width:200px; text-align:center\"><strong>" . $string['important'] . "</strong></td><td style=\"width:200px; text-align:center\"><strong>" . $string['nicetoknow'] . "</strong></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['easy'] . "</strong></td><td style=\"text-align:center; background-color:#F8F8F2\"><input type=\"text\" style=\"text-align:right; background-color:#F8F8F2; border:0px; color:red; text-decoration:line-through\" name=\"origee\" size=\"3\" value=\"0\" /><input type=\"text\" style=\"text-align:right; background-color:#F8F8F2; border:0px\" name=\"ee\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EE',$ebel[0]) . "</td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origei\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"ei\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EI',$ebel[1]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origen\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"en\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EN',$ebel[2]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"easy_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['medium'] . "</strong></td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px solid red; color:red; text-decoration:line-through\" name=\"origme\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"me\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('ME',$ebel[3]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origmi\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"mi\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MI',$ebel[4]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"origmn\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"mn\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MN',$ebel[5]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"medium_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['hard'] . "</strong></td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"orighe\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"he\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HE',$ebel[6]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"orighi\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"hi\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HI',$ebel[7]) . "</td><td style=\"text-align:center; background-color:#C8C8A6\"><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px; color:red; text-decoration:line-through\" name=\"orighn\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px\" name=\"hn\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HN',$ebel[8]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"hard_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"essential_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"important_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"nice_total\" size=\"8\" style=\"text-align:center; border:0px\" /></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td style=\"text-align:center\" colspan=\"3\"><input type=\"text\" style=\"border:0px; text-align:center\" name=\"cut_score\" size=\"70\" value=\"cut score=0%\" /></td></tr>\n";
    echo "</table>\n</div>\n<br />\n";

    echo "<br />\n<div align=\"center\">\n";
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"90%\" style=\"background-color:#E4EEFC; border: 1px solid #B5C4DF; text-align:left\">\n";
    echo "<tr>\n<td style=\"margin:0px\">" . $string['step3'] . "<br />";
    ?>
    <blockquote style="margin-top:8px; margin-bottom:8px">
    <input type="radio" name="distinction_type" value="1"<?php if ($ebel[9] > 0) echo ' checked'; ?> /> <?php echo $string['gridbelow']; ?><br />
    <input type="radio" name="distinction_type" value="2"<?php if ($ebel[9] === '0') echo ' checked'; ?> /> <?php echo $string['top20']; ?><br />
    <input type="radio" name="distinction_type" value="3"<?php if ($ebel[9] === NULL) echo ' checked'; ?> /> <?php echo $string['donotapply']; ?><br />
    </blockquote>
    <?php
    echo "</td>\n</tr>\n</table>\n</div>\n<br />\n";
    echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
    echo "<tr><td>&nbsp;</td><td style=\"width:200px; text-align:center\"><strong>" . $string['essential'] . "</strong></td><td style=\"width:200px; text-align:center\"><strong>" . $string['important'] . "</strong></td><td style=\"width:200px; text-align:center\"><strong>" . $string['nicetoknow'] . "</strong></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['easy'] . "</strong></td><td style=\"text-align:center; background-color:#F8F8F2\"><input type=\"text\" style=\"text-align:right; border:0px; color:red; text-decoration:line-through; background-color:#F8F8F2\" name=\"origee2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; border:0px; background-color:#F8F8F2\" name=\"ee2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EE2',$ebel[9]) . "</td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origei2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"ei2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EI2',$ebel[10]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origen2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"en2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('EN2',$ebel[11]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"easy2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['medium'] . "</strong></td><td style=\"text-align:center; background-color:#F0F0E6\"><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px; color:red; text-decoration:line-through\" name=\"origme2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#F0F0E6; border:0px\" name=\"me2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('ME2',$ebel[12]) . "</td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"origmi2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"mi2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MI2',$ebel[13]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"origmn2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"mn2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('MN2',$ebel[14]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"medium2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
    echo "<tr><td style=\"text-align:right\"><strong>" . $string['hard'] . "</strong></td><td style=\"text-align:center; background-color:#E4E4D2\"><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px; color:red; text-decoration:line-through\" name=\"orighe2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#E4E4D2; border:0px\" name=\"he2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HE2',$ebel[15]) . "</td><td style=\"text-align:center; background-color:#D5D5BB\"><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px; color:red; text-decoration:line-through\" name=\"orighi2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#D5D5BB; border:0px\" name=\"hi2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HI2',$ebel[16]) . "</td><td style=\"text-align:center; background-color:#C8C8A6\"><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px; color:red; text-decoration:line-through\" name=\"orighn2\" size=\"3\" value=\"\" /><input type=\"text\" style=\"text-align:right; background-color:#C8C8A6; border:0px\" name=\"hn2\" size=\"7\" value=\"0\" />&nbsp;" . ebelDropdown('HN2',$ebel[17]) . "</td><td style=\"border:0px\"><input type=\"text\" value=\"\" name=\"hard2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
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
<tr><td><input type="submit" name="submit" value="<?php echo $string['saveexit']; ?>" accesskey="S" style="width:160px" /></td><td><input type="submit" name="continue" value="<?php echo $string['savecontinue']; ?>" accesskey="C" style="width:160px" /></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input onclick="javascript:window.location='index.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>'" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:90px" /></td></tr>

<tr><td colspan="2" style="text-align:center">
<?php
  if (isset($_COOKIE['caabanksave']) and $_COOKIE['caabanksave'] == '1') {
    echo '<input type="checkbox" name="banksave" value="1" checked />&nbsp;' . $string['savebank'];
  } else {
    echo '<input type="checkbox" name="banksave" value="1" />&nbsp;' . $string['savebank'];
  }
  $mysqli->close();
  
?>
</td><td colspan="2"></td></tr>
</table>
</div>
<br />
<input type="hidden" name="total_marks" id="total_marks" value="<?php echo $total_marks - $std_excluded ?>" />
</form>
</body>
</html>