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
* Task menu for papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require $cfg_web_root . 'include/sidebar_menu.inc';
$paperID = $_GET['paperID'];
$max_screen = 0;

$stmt = $mysqli->prepare("SELECT paper_title, moduleID, folder, paper_ownerID, moduleID, DATE_FORMAT(start_date,'%Y%m%d%H%i%S') AS start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i%S') AS end_date, DATE_FORMAT(created,'%d/%m/%Y% %H:%i') AS created, MAX(screen) AS screen, fullscreen, MAX(display_pos) AS display_pos, paper_type, externals, internal_reviewers, labs, calendar_year, exam_duration, crypt_name FROM properties LEFT JOIN papers ON properties.property_id=papers.paper WHERE property_id=? GROUP BY paper_title");
$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->bind_result($paper_title, $paper_moduleID, $tmp_folder, $paper_ownerID, $tmp_module, $start_date, $end_date, $created, $max_screen, $fullscreen, $max_display_pos, $paper_type, $externals, $internal_reviewers, $labs, $session, $exam_duration, $crypt_name);
while ($stmt->fetch()) {
  if (date("YmdHis", time()) >= $start_date and date("YmdHis", time()) <= $end_date) {
    $active_date = 1;
  } else {
    $active_date = 0;
  }
  if (date("YmdHis", time()) >= $start_date and $paper_type == '2') {
    $summative_lock = 1;
  } else {
    $summative_lock = 0;
  }
}
$stmt->close();

if (isset($_GET['module']) and $_GET['module'] != '') {
  $module = $_GET['module'];
  $tmp_module = $_GET['module'];
} else {
  $module = '';
}
if (isset($_GET['folder']) and $_GET['folder'] != '') {
  $folder = $_GET['folder'];
} else {
  $folder = '';
}

$stmt = $mysqli->prepare("SELECT checklist FROM modules WHERE moduleid IN ('" . str_replace(",","','",$tmp_module) . "')");
$stmt->execute();
$stmt->bind_result($tmp_checklist);
$check[] = array();
while ($stmt->fetch()) {
  $tmp = explode(',',$tmp_checklist);
  foreach ($tmp as $c) {
    $check[$c] = $c;
  }
}
$checklist = implode(',',array_keys($check));
$stmt->close();

$question_list = array();
$stmt = $mysqli->prepare("SELECT question FROM (papers, questions) WHERE papers.question=questions.q_id AND q_type != 'info' AND paper=?");
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->bind_result($questionID);
while ($stmt->fetch()) {
  $question_list[$questionID] = 0;
}
$stmt->close();
?>

<?php echo $cfg_js_root ?>
<script language="JavaScript" src="<?php echo $cfg_root_path ?>/js/sidebar.js"></script>
<script language="JavaScript">
  function startPaper(fullsc) {
    <?php
    if ($paper_type == '4') {      // OSCE
    ?>
      window.open("<?php echo $cfg_root_path ?>/osce/form.php?id=<?php echo $crypt_name; ?>&username=test","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } elseif ($paper_type == '6') {
    ?>
      window.open("<?php echo $cfg_root_path ?>/peer_review/form.php?id=<?php echo $crypt_name; ?>","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } else {
    ?>
    if (fullsc == 0) {
      window.open("<?php echo $cfg_root_path ?>/paper/start.php?id=<?php echo $crypt_name; ?>&mode=preview","paper","width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    } else {
      window.open("<?php echo $cfg_root_path ?>/paper/start.php?id=<?php echo $crypt_name; ?>&mode=preview","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    }
    <?php
    }
    ?>
  }

  function addQuestion(qType, paperID) {
    if (paperID == 0) {
      document.location.href='<?php echo $cfg_root_path ?>/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value;
    } else {
      document.location.href='<?php echo $cfg_root_path ?>/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value + '&paperID=' + paperID + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>';
    }
  }

  function paperProperties() {
    <?php
    if ($paper_type == '6') {
    ?>
    notice=window.open("<?php echo $cfg_root_path ?>/peer_review/properties.php?paperID=<?php echo $paperID; ?>","properties","width=862,height=650,left="+(screen.width/2-431)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } else {
    ?>
    notice=window.open("<?php echo $cfg_root_path ?>/paper/properties.php?paperID=<?php echo $paperID; ?>","properties","width=862,height=650,left="+(screen.width/2-431)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    }
    ?>
    if (window.focus) {
      notice.focus();
    }
    this.event.returnValue = false;
    return false;
  }

  function lockedPaperProperties() {
    notice=window.open("<?php echo $cfg_root_path ?>/paper/properties_locked.php?paperID=<?php echo $paperID; ?>","properties","width=862,height=650,left="+(screen.width/2-431)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
    this.event.returnValue = false;
    return false;
  }

  function editQuestion() {
    var loc = '<?php echo $cfg_root_path ?>/question/edit/index.php?q_id=' + document.PapersMenu.questionID.value + '&qNo=' + document.PapersMenu.questionNo.value + '&paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&calling=paper&scrOfY=' + document.PapersMenu.scrOfY.value;
    if (document.PapersMenu.qType.value == 'random' || document.PapersMenu.qType.value == 'keyword_based') {
      loc += '&type=' + document.PapersMenu.qType.value;
    }
    document.location = loc;
  }

  function deleteQuestion() {
    notice=window.open("<?php echo $cfg_root_path ?>/delete/check_delete_q_pointer.php?questionID=" + document.PapersMenu.pID.value + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&q_id=" + document.PapersMenu.questionID.value + "&scrOfY=" + document.PapersMenu.scrOfY.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function deletePaper() {
    notice=window.open("<?php echo $cfg_root_path ?>/delete/check_delete_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function retirePaper() {
    notice=window.open("<?php echo $cfg_root_path ?>/paper/check_retire_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function editScreenNo() {
    notice=window.open("<?php echo $cfg_root_path ?>/paper/edit_screen_no.php?questionID=" + document.PapersMenu.pID.value + "&paperID=<?php echo $paperID; ?>&screen=" + document.PapersMenu.screenNo.value + "&display_pos=" + document.PapersMenu.oldQuestionID.value + "&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=" + document.getElementById('scrOfY').value + "","properties","width=300,height=200,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-150,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function changeScreen(screenNo) {
    window.location='<?php echo $cfg_root_path ?>/paper/change_screen_no.php?questionID=' + document.PapersMenu.pID.value + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.oldQuestionID.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value;
  }
  
  function incScreen() {
    screenNo = document.PapersMenu.screenNo.value;
    screenNo++;
    window.location='<?php echo $cfg_root_path ?>/paper/change_screen_no.php?questionID=' + document.PapersMenu.pID.value + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.oldQuestionID.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value;
  }
  
  function addQuestions(display, screen_no) {
    winH = screen.height - 80
    winW = screen.width - 80
    notice=window.open("<?php echo $cfg_root_path ?>/question/add/add_questions_frame.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=" + document.getElementById('scrOfY').value + "&display_pos=" + display + "&max_screen=" + screen_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function noAdd(start, end) {
    winH = screen.height - 40
    notice=window.open("<?php echo $cfg_root_path ?>/question/add/noadd.php?paperID=<?php echo $paperID; ?>&start_date=" + start + "&end_date=" + end + "","notice","width=770,height=550,left="+(screen.width/2-385)+",top="+(screen.height/2-275)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("<?php echo $cfg_root_path ?>/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("<?php echo $cfg_root_path ?>/question/link_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","linktopaper","width=500,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function questionInfo() {
    notice=window.open("<?php echo $cfg_root_path ?>/question/info.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=500,height=500,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function promote() {
    if (document.getElementById('prev_screen').value != '') {
      if (document.getElementById('prev_screen').value < document.getElementById('current_screen').value) {
        document.location.href = "<?php echo $cfg_root_path ?>/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + document.getElementById('current_pos').value + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('prev_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      } else {
        document.location.href = "<?php echo $cfg_root_path ?>/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + (parseInt(document.getElementById('current_pos').value) - 1) + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('prev_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      }
    }
  }

  function demote() {
    if (document.getElementById('next_screen').value != '') {
      if (document.getElementById('next_screen').value > document.getElementById('current_screen').value) {
        document.location.href = "<?php echo $cfg_root_path ?>/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + document.getElementById('current_pos').value + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('next_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      } else {
        document.location.href = "<?php echo $cfg_root_path ?>/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + (parseInt(document.getElementById('current_pos').value) + 1) + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('next_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      }
    }
  }
  
  function showAssStatsMenu(e) {
    hideMenus();
    document.getElementById('copy_submenu').style.display = 'none';
    document.getElementById('stats_menu').style.display = 'block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function showCopyMenu(e) {
    hideMenus();
    document.getElementById('stats_menu').style.display = 'none';
    document.getElementById('copy_submenu').style.display = 'block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function showChangeScreen(e) {
    hideMenus();
    document.getElementById('stats_menu').style.display = 'none';
    document.getElementById('copy_submenu').style.display = 'none';
    document.getElementById('change_screen_submenu').style.display = 'block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function hideAssStatsMenu(e) {
    document.getElementById('stats_menu').style.display = 'none';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }  
</script>

<div id="left-sidebar" class="sidebar">

<br />
<form name="PapersMenu" action="">
<input type="hidden" name="questionNo" value="" />
<input type="hidden" name="questionID" value="" />
<input type="hidden" name="pID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="screenNo" value="" />
<input type="hidden" name="oldQuestionID" value="" />
<input type="hidden" name="scrOfY" id="scrOfY" value="0" />
<input type="hidden" name="current_pos" id="current_pos" value="" />
<input type="hidden" name="prev_screen" id="prev_screen" value="" />
<input type="hidden" name="next_screen" id="next_screen" value="" />
<input type="hidden" name="current_screen" id="current_screen" value="" />
<?php
$team_member = false;
if ($paper_ownerID == $userID or strpos($userroles,'Admin') !== false or strpos($userroles,'SysAdmin') !== false) {
  $team_member = true;
} else {
  foreach ($teams as $individual_team) {
    if (strpos($paper_moduleID,$individual_team) !== false) $team_member = true;
  }
}

$stmt = $mysqli->prepare("SELECT COUNT(p_id) FROM papers WHERE paper=?");
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->bind_result($p_id_count);
$stmt->fetch();
$stmt->close();

?>
<table id="papertasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['papertasks']; ?></td></tr>
<tr><td style="margin:4px">
<?php
  echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" id=\"menu1\" style=\"margin-left:7px; font-size:90%; display:block\">\n";
  if ($paper_type == '5') {      // Offline
    echo "<tr><td style=\"width:20px>3<img src=\"{$cfg_root_path}/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" border=\"0\" /></td><td class=\"greymenuitem\" style=\"width:166px\">" . $string['testpreview'] . "</td></tr>\n";
  } else {
    if ($p_id_count > 0) {
      echo "<tr><td style=\"width:20px\"><a href=\"#\" onclick=\"startPaper($fullscreen); return false;\"><img src=\"{$cfg_root_path}/artwork/small_play.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" border=\"0\" /></a></td><td class=\"menuitem\" style=\"width:166px\"><a href=\"#\" onclick=\"startPaper($fullscreen); return false;\">" . $string['testpreview'] . "</a></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\"><img src=\"{$cfg_root_path}/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" border=\"0\" /></td><td class=\"greymenuitem\" style=\"width:166px\">" . $string['testpreview'] . "</td></tr>\n";
    }
  }

  if ($team_member == true) {
    if ($summative_lock == 1) {
      if (strpos($userroles,'SysAdmin') !== false) {
        echo "<tr><td><a href=\"\" onclick=\"noAdd('$start_date','$end_date'); return false;\"><img src=\"{$cfg_root_path}/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" border=\"0\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('$start_date','$end_date'); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
        echo "<tr><td><a href=\"\" onclick=\"return paperProperties();\"><img src=\"{$cfg_root_path}/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editproperties'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"return paperProperties();\">" . $string['editproperties'] . "</a></td></tr>\n";
      } else {
        echo "<tr><td><a href=\"\" onclick=\"noAdd('$start_date','$end_date'); return false;\"><img src=\"{$cfg_root_path}/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" border=\"0\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('$start_date','$end_date'); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
        echo "<tr><td><a href=\"\" onclick=\"return lockedPaperProperties();\"><img src=\"{$cfg_root_path}/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editproperties'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"return lockedPaperProperties();\">" . $string['editproperties'] . "</a></td></tr>\n";
      }
    } else {
      $max_screen = ($max_screen != '') ? $max_screen : 0;
      echo "<tr><td><a href=\"\" onclick=\"addQuestions(" . ($max_display_pos + 1) . ",$max_screen); return false;\"><img src=\"{$cfg_root_path}/artwork/add_questions_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"addQuestions(" . ($max_display_pos + 1) . ",$max_screen); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
      echo "<tr><td><a href=\"\" onclick=\"return paperProperties();\"><img src=\"{$cfg_root_path}/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editproperties'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"return paperProperties();\">" . $string['editproperties'] . "</a></td></tr>\n";
    }
    if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2' or $paper_type == '5' or $paper_type == '6') {
      if ($p_id_count == 0) {
        echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"greymenuitem\"><span id=\"reports\">" . $string['reports'] . "</span></td></tr>\n";
      } else {
        echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"menuitem\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\">" . $string['reports'] . "</a></span><img src=\"{$cfg_root_path}/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n";
      }
      if (strpos($checklist,'mapping') !== false and $paper_type != '6') {
        echo "<tr><td><a href=\"{$cfg_root_path}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"" . $string['mappedobjectives'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\">" . $string['mappedobjectives'] . "</a></td></tr>\n";
      }
      if ($paper_type == '5') {
        echo "<tr><td><a href=\"{$cfg_root_path}/import/spotter_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/import_csv_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['importmarks'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/import/spotter_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['importmarks'] . "</a></td></tr>\n";
      } elseif ($paper_type != '6') {
        if (strpos($checklist,'stdset') !== false) {
          echo "<tr><td><a href=\"{$cfg_root_path}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/std_set_icon_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['standardssetting'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['standardssetting'] . "</a></td></tr>\n";
        }
      }
    } elseif ($paper_type == '3') {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"menuitem\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\">" . $string['reports'] . "</a></span><img src=\"{$cfg_root_path}/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n";
    } elseif ($paper_type == '4') {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"menuitem\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\">" . $string['reports'] . "</a></span><img src=\"{$cfg_root_path}/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n";
      echo "<tr><td><a href=\"{$cfg_root_path}/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/import_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['importoscemarks'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['importoscemarks'] . "</a></td></tr>\n";
      if (strpos($checklist,'mapping') !== false) {
        echo "<tr><td><a href=\"{$cfg_root_path}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"" . $string['mappedobjectives'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\">" . $string['mappedobjectives'] . "</a></td></tr>\n";
      }
    }
  } else {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" /></td><td class=\"greymenuitem\">" . $string['addquestionspaper'] . "</td></tr>\n";
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/properties_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editproperties'] . "\" /></td><td class=\"greymenuitem\">" . $string['editproperties'] . "</td></tr>\n";
    if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"greymenuitem\">" . $string['reports'] . "</td></tr>\n";
      if (strpos($checklist,'stdset') !== false) {
        echo "<tr><td><a href=\"{$cfg_root_path}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$cfg_root_path}/artwork/std_set_icon_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['standardssetting'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['standardssetting'] . "</a></td></tr>\n";
      }
    } else {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" border=\"0\" /></td><td class=\"greymenuitem\">" . $string['reports'] . "</td></tr>\n";
    }
  }
?>
<tr><td><a onclick="showCopyMenu(event); return false;" href="#"><img src="<?php echo $cfg_root_path ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copypaper']; ?>" border="0" /></a></td><td class="menuitem"><span id="copy"><a onclick="showCopyMenu(event); return false;" href="#"><?php echo $string['copypaper']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<?php
  if ($team_member == true and $summative_lock != 1) {
    echo '<tr><td><a href="#" onclick="deletePaper(); return false;"><img src="' . $cfg_root_path . '/artwork/delete_paper_16.gif" width="16" height="16" alt="' . $string['deletepaper'] . '" border="0" /></a></td><td class="menuitem"><a href="#" onclick="deletePaper(); return false;">' . $string['deletepaper'] . '</a></td></tr>';
  } else {
    echo '<tr><td><img src="' . $cfg_root_path . '/artwork/delete_paper_grey_16.gif" width="16" height="16" alt="' . $string['deletepaper'] . '" /></td><td class="greymenuitem">' . $string['deletepaper'] . '</td></tr>';
  }
  
  echo '<tr><td><a href="#" onclick="retirePaper(); return false;"><img src="' . $cfg_root_path . '/artwork/retire_16.png" width="16" height="16" border="0" alt="' . $string['retirepaper'] . '" /></a></td><td class="menuitem"><a href="#" onclick="retirePaper(); return false;">' . $string['retirepaper'] . '</a></td></tr>';

  if ($p_id_count == 0) {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" border=\"0\" /></td><td class=\"greymenuitem\">" . $string['printhardcopy'] . "</td></tr>\n";
  } else {
    if ($paper_type == '4') {
      echo "<tr><td><a href=\"{$cfg_root_path}/osce/print.php?paperID=$paperID\"><img src=\"{$cfg_root_path}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/osce/print.php?paperID=$paperID\">" . $string['printhardcopy'] . "</a></td></tr>\n";
    } else {
      echo "<tr><td><a href=\"{$cfg_root_path}/paper/print.php?id=$crypt_name\"><img src=\"{$cfg_root_path}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$cfg_root_path}/paper/print.php?id=$crypt_name\">" . $string['printhardcopy'] . "</a></td></tr>\n";
    }
  }
?>
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/ims_16.png" width="16" height="16" alt="<?php echo $string['qtiimportexport']; ?>" border="0" /></td><td class="menuitem"><span id="qti"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup6','papertasks','qti',myOptions6,myURLs6,event); return false;"><?php echo $string['qtiimportexport']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
</table>
</td></tr>
</table>

<br />
<br />

<table id="currentquestion" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['currentquestiontasks']; ?></td></tr>
<tr><td style="margin:4px">

<table style="margin-left:7px; display:block; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></td><td class="greymenuitem"><?php echo $string['editquestion']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/information_icon_grey.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></td><td class="greymenuitem"><?php echo $string['information']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/copy_icon_grey.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></td><td class="greymenuitem"><?php echo $string['copyontopaperx']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem"><?php echo $string['linktopaper']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/promote_disabled.gif" width="16" height="16" alt="<?php echo $string['moveup']; ?>" /></td><td class="greymenuitem"><?php echo $string['moveup']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/demote_disabled.gif" width="16" height="16" alt="<?php echo $string['movedown']; ?>" /></td><td class="greymenuitem"><?php echo $string['movedown']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/screen_icon_grey.png" width="16" height="16" alt="<?php echo $string['changescreenno']; ?>" /></td><td class="greymenuitem"><?php echo $string['changescreenno']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></td><td class="greymenuitem"><?php echo $string['removefrompaper']; ?></td></tr>
</table>

<table style="margin-left:7px; display:none; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td><img style="cursor:hand" onclick="editQuestion(); return false;" src="<?php echo $cfg_root_path ?>/artwork/edit.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="questionInfo(); return false;" src="<?php echo $cfg_root_path ?>/artwork/information_icon.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="copyToPaper(); return false;" src="<?php echo $cfg_root_path ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="linkToPaper(); return false;" src="<?php echo $cfg_root_path ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="promote(); return false;" id="promoteicon" src="<?php echo $cfg_root_path ?>/artwork/promote.gif" width="16" height="16" alt="<?php echo $string['moveup']; ?>" /></td><td class="menuitem"><a id="promotetext" onclick="promote(); return false;"><?php echo $string['moveup']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="demote(); return false;" id="demoteicon" src="<?php echo $cfg_root_path ?>/artwork/demote.gif" width="16" height="16" alt="<?php echo $string['movedown']; ?>" /></td><td class="menuitem"><a id="demotetext" onclick="demote(); return false;"><?php echo $string['movedown']; ?></a></td></tr>
<?php
if ($paper_type == '4') {
  echo '<tr><td><img src="' . $cfg_root_path . '/artwork/screen_icon_grey.png" width="16" height="16" alt="' . $string['changescreenno'] . '" /></td><td class="greymenuitem">' . $string['changescreenno'] . '</td></tr>';
} else {
  echo '<tr><td><img style="cursor:hand" src="' . $cfg_root_path . '/artwork/screen_icon.png" width="16" height="16" alt="' . $string['changescreenno'] . '" /></td><td class="menuitem"><a href="#" onclick="showChangeScreen(event); return false;"><span id="changescreen">' . $string['changescreenno'] . '</span><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>';
}
?>
<tr><td><img style="cursor:hand" onclick="deleteQuestion(); return false;" src="<?php echo $cfg_root_path ?>/artwork/red_cross.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><?php echo $string['removefrompaper']; ?></a></td></tr>
</table>

<table style="margin-left:7px; display:none; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td><img style="cursor:hand" onclick="editQuestion(); return false;" src="<?php echo $cfg_root_path ?>/artwork/edit.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="questionInfo(); return false;" src="<?php echo $cfg_root_path ?>/artwork/information_icon.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="copyToPaper(); return false;" src="<?php echo $cfg_root_path ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td><img style="cursor:hand" onclick="linkToPaper(); return false;" src="<?php echo $cfg_root_path ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/promote_disabled.gif" width="16" height="16" alt="<?php echo $string['moveup']; ?>" /></td><td class="greymenuitem"><?php echo $string['moveup']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/demote_disabled.gif" width="16" height="16" alt="<?php echo $string['movedown']; ?>" /></td><td class="greymenuitem"><?php echo $string['movedown']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/screen_icon_grey.png" width="16" height="16" alt="<?php echo $string['changescreenno']; ?>" /></td><td class="greymenuitem"><?php echo $string['changescreenno']; ?></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></td><td class="greymenuitem"><?php echo $string['removefrompaper']; ?></td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['questionbanktasks']; ?></td></tr>
<tr><td>
<table cellpadding="3" cellspacing="0" border="0" style="margin-left:7px; font-size:90%">
<tr><td width="16"><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="<?php echo $string['questionsbytype']; ?>" border="0" /></td><td class="menuitem"><span id="type"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><?php echo $string['questionsbytype']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="<?php echo $string['questionsbyteam']; ?>" border="0" /></td><td class="menuitem"><span id="team"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><?php echo $string['questionsbyteam']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/question_list_icon.gif" width="16" height="16" alt="<?php echo $string['questionsbykeyword']; ?>" border="0" /></td><td class="menuitem"><span id="keyword"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><?php echo $string['questionsbykeyword']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/xmag.png" width="16" height="16" alt="<?php echo $string['search']; ?>" border="0" /></td><td class="menuitem"><span id="search"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><?php echo $string['search']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<tr><td><img src="<?php echo $cfg_root_path ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="<?php echo $string['createnewquestion']; ?>" border="0" /></td><td class="menuitem"><span id="newquestion"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><?php echo $string['createnewquestion']; ?></a></span><img src="<?php echo $cfg_root_path ?>/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
</table>

</td></tr>
</table>

<?php
if ($paper_type == '2') {
?>
<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['summativechecklist']; ?></td></tr>
<tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%" style="margin-left:7px; padding-bottom:2px; font-size:90%">

<?php
  // Session
  $year_in_title = false;
  $tmp_match = '';
  if (preg_match( '/\d\d\d\d[\/-]\d\d\d\d/' , $paper_title , $matches) == 1) {
    $year_in_title = true;
    $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
  } elseif (preg_match( '/\d\d\d\d[\/-]\d\d/' , $paper_title , $matches) == 1) {
    $year_in_title = true;
    $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
  } elseif (preg_match( '/\d\d[\/-]\d\d/' , $paper_title , $matches) == 1) {
    $year_in_title = true;
    $tmp_match = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
  }
  if ($year_in_title == true and $tmp_match != $session) {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">" . $string['session'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['mismatch'] . "</strong></td></tr>\n";
  }
  // Times
  if (substr($start_date,-6) == substr($end_date,-6) or substr($start_date,-6) == '000000' or substr($start_date,-6) == '000000') {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">" . $string['examtime'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['incorrect'] . "</strong></td></tr>\n";
  }
  
  // Duration
  if ($exam_duration == '') {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">" . $string['duration'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
  }
  
  // Computer labs
  if ($labs == '') {
    echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">" . $string['computerlabs'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
  }

  // Internal Peer review
  if (strpos($checklist,'peer') !== false) {
    if ($internal_reviewers == '') {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">" . $string['peerreviewed'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
    } else {
      $tmp_array = explode(',',$internal_reviewers);
      $internal_array = array();
      foreach ($tmp_array as $tmp_internal) {
        $internal_array[$tmp_internal] = 0;
      }

      $stmt = $mysqli->prepare("SELECT count(id), reviewer FROM review_comments WHERE q_paper=? AND review_type='internal' GROUP BY reviewer, reviewed");
      $stmt->bind_param('i', $_GET['paperID']);
      $stmt->execute();
      $stmt->bind_result($review_records, $reviewer);
      while ($row = $stmt->fetch()) {
        $internal_array[$reviewer] = 1;
      }
      $stmt->close();
      $reviews_complete = 0;
      foreach ($tmp_array as $tmp_internal) {
        if ($internal_array[$tmp_internal] == 1) $reviews_complete++;
      }
      if ($reviews_complete < count($internal_array)) {
        if ($reviews_complete == 0) {
          $tmp_color = '#C00000';
        } else {
          $tmp_color = '#F27000';
        }
        echo "<tr><td style=\"width:18px\"><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($internal_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td style=\"width:18px\"><img src=\"{$cfg_root_path}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
      }
    }
  }

  // External examiners
  if (strpos($checklist,'external') !== false) {
    if ($externals == '') {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td>" . $string['externalreviews'] . "</td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
    } else {
      $tmp_array = explode(',',$externals);
      $external_array = array();
      foreach ($tmp_array as $tmp_external) {
        $external_array[$tmp_external] = 0;
      }

      $reviews_complete = 0;
      $stmt = $mysqli->prepare("SELECT count(id), reviewer FROM review_comments WHERE q_paper=? AND review_type='external' GROUP BY reviewer, reviewed");
      $stmt->bind_param('i', $_GET['paperID']);
      $stmt->execute();
      $stmt->bind_result($review_records, $reviewer);
      while ($row = $stmt->fetch()) {
        if (isset($external_array[$reviewer]) AND $external_array[$reviewer] === 0) {
          $reviews_complete++;
        }
      }
      $stmt->close();

      if ($reviews_complete < count($external_array)) {
	    if ($reviews_complete == 0) {
		  $tmp_color = '#C00000';
		} else {
		  $tmp_color = '#F27000';
		}
        echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($external_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td><img src=\"{$cfg_root_path}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
      }
    }
  }

  // Standards Set
  $standard_set = 0;
  $standards_set = 0;
  if (strpos($checklist,'stdset') !== false) {
    $stmt = $mysqli->prepare("SELECT COUNT(id), setterID FROM standards_setting WHERE paperID=? GROUP BY setterID");
    $stmt->bind_param('i', $_GET['paperID']);
    $stmt->execute();
    $stmt->bind_result($set_set_records, $setterID);
    while ($row = $stmt->fetch()) {
      if ($set_set_records >= count($question_list) and $standard_set == 0) {
        $standards_set = 1;
      } elseif ($set_set_records < count($question_list) and ($standard_set == 0 or $standard_set == 1)) {
        $standards_set = 0.5;
      }
    }
    $stmt->close();
    if ($standards_set == 1) {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
    } else {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
    }
	
    if ($standards_set == 1) {
      echo "<td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
    } elseif ($standards_set == 0.5) {
      echo "<td style=\"color:#C00000\"><strong>" . $string['incomplete'] . "</strong></td></tr>\n";
    } else {
      echo "<td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
    }
  }

  // Mapped
  if (strpos($checklist,'mapping') !== false) {
    $mappings_complete = 0;
    $stmt = $mysqli->prepare("SELECT DISTINCT question_id FROM relationships WHERE paper_id=?");
    $stmt->bind_param('i', $_GET['paperID']);
    $stmt->execute();
    $stmt->bind_result($questionID);
    while ($row = $stmt->fetch()) {
      if (isset($question_list[$questionID]) and $question_list[$questionID] === 0) {
        $mappings_complete++;
      }
    }
    $stmt->close();
    if ($mappings_complete < count($question_list)) {
	  if ($mappings_complete == 0) {
	    $tmp_color = '#C00000';
	  } else {
      $tmp_color = '#F27000';
	  }
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td style=\"color:$tmp_color\"><strong>$mappings_complete/" . count($question_list) . "</strong></td></tr>\n";
    } else {
      echo "<tr><td><img src=\"{$cfg_root_path}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"{$cfg_root_path}/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
    }
  }
}
?>
</table>

</td></tr>
</table>

</td></tr>
</table>
</form>
</div>
<?php
  // Module folders.
  if (count($teams) > 0) {
    $modules_array = array();
    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
    while ($module_row = $module_details->fetch_assoc()) {
      $title = $module_row['moduleid'] . ' - ' . str_replace("'","\'",$module_row['fullname']);
      $url = $cfg_root_path . '/folder/details.php?module=' . $module_row['moduleid'];
      $modules_array[$title] = $url;
    }
    $module_details->close();
  }
  
  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = $cfg_root_path . '/question/list.php?type=%&team=' . $individual_team;
  }

  // Keywords
  $keywords_array = array();
  $keyword_no = 0;
  $old_moduleID = '';
  $stmt = $mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($moduleID, $tmp_keyword, $keywordID);
  while ($stmt->fetch()) {
    if ($old_moduleID != $moduleID) $keywords_array[$keyword_no] = "#$moduleID";
    $keywords_array[htmlspecialchars($tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
    $old_moduleID = $moduleID;
    $keyword_no++;
  }
  if ($stmt->num_rows > 0) $keywords_array[$keyword_no] = '#Personal Keywords';
  $stmt->close();

  $result = $mysqli->prepare("SELECT DISTINCT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($keywordID, $tmp_keyword);
  while ($result->fetch()) {
    $keywords_array[htmlspecialchars($tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
  }
  $result->close();

  $screens = array($string['insertscreenbreak']=>'javascript:incScreen()','-'=>'-');
  for ($i=1; $i<=60; $i++) {
    $screens['Screen ' . $i] = "javascript:changeScreen($i)";
  }
  
  makeMenu($screens);
  makeMenu(array($string['info']=>$cfg_root_path . '/question/list.php?type=info',$string['keyword_based']=>$cfg_root_path . '/question/list.php?type=keyword',$string['random']=>$cfg_root_path . '/question/list.php?type=random','-'=>'-',$string['calculation']=>$cfg_root_path . '/question/list.php?type=calculation',$string['dichotomous']=>$cfg_root_path . '/question/list.php?type=dichotomous',$string['extmatch']=>$cfg_root_path . '/question/list.php?type=extmatch',$string['blank']=>$cfg_root_path . '/question/list.php?type=blank',$string['flash']=>$cfg_root_path . '/question/list.php?type=flash',$string['hotspot']=>$cfg_root_path . '/question/list.php?type=hotspot',$string['labelling']=>$cfg_root_path . '/question/list.php?type=labelling',$string['likert']=>$cfg_root_path . '/question/list.php?type=likert',$string['matrix']=>$cfg_root_path . '/question/list.php?type=matrix',$string['mcq']=>$cfg_root_path . '/question/list.php?type=mcq',$string['mrq']=>$cfg_root_path . '/question/list.php?type=mrq',$string['rank']=>$cfg_root_path . '/question/list.php?type=rank',$string['sct']=>$cfg_root_path . '/question/list.php?type=sct',$string['textbox']=>$cfg_root_path . '/question/list.php?type=textbox'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$cfg_root_path . '/question/search.php',$string['papers']=>$cfg_root_path . '/paper/search.php',$string['people']=>$cfg_root_path . '/users/search.php'));
  
  if ($summative_lock == 1) {
    $params = "&scrOfY=' + document.getElementById('scrOfY').value + '";
  } else {
    $params = "&scrOfY=' + document.getElementById('scrOfY').value + '&paperID=$paperID&folder=$folder&module=$tmp_module&calling=paper";
  }

  makeMenu(array($string['info']=>"$cfg_root_path/question/edit/index.php?type=info$params",$string['keyword_based']=>"$cfg_root_path/question/edit/index.php?type=keyword_based$params",$string['random']=>"$cfg_root_path/question/edit/index.php?type=random$params","-"=>"-",$string['calculation']=>"$cfg_root_path/question/edit/index.php?type=calculation$params",$string['dichotomous']=>"$cfg_root_path/question/edit/index.php?type=dichotomous$params",$string['extmatch']=>"$cfg_root_path/question/edit/index.php?type=extmatch$params",$string['blank']=>"$cfg_root_path/question/edit/index.php?type=blank$params",$string['flash']=>"$cfg_root_path/question/edit/index.php?type=flash$params",$string['hotspot']=>"$cfg_root_path/question/edit/index.php?type=hotspot$params",$string['labelling']=>"$cfg_root_path/question/edit/index.php?type=labelling$params",$string['likert']=>"$cfg_root_path/question/edit/index.php?type=likert$params",$string['matrix']=>"$cfg_root_path/question/edit/index.php?type=matrix$params",$string['mcq']=>"$cfg_root_path/question/edit/index.php?type=mcq$params",$string['mrq']=>"$cfg_root_path/question/edit/index.php?type=mrq$params",$string['rank']=>"$cfg_root_path/question/edit/index.php?type=rank$params",$string['sct']=>"$cfg_root_path/question/edit/index.php?type=sct$params",$string['textbox']=>"$cfg_root_path/question/edit/index.php?type=textbox$params"));
  if($summative_lock == 1) {
    makeMenu(array($string['export12']=>"$cfg_root_path/qti/export.php?dest=qti12&paperID=$paperID"));
  } else {
    makeMenu(array($string['import']=>"$cfg_root_path/qti/import.php?paperID=$paperID&module=$module",$string['export12']=>"$cfg_root_path/qti/export.php?dest=qti12&paperID=$paperID"));
  }
  
  hideMenuScript($menuNo);

  require $cfg_web_root . 'include/reports_submenu.inc';
  require $cfg_web_root . 'include/paper_copy_submenu.inc';
  require $cfg_web_root . 'include/change_screen_submenu.inc';
?>
