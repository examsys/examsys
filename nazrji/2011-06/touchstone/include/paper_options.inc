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
* Task menu for papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require $cfg_web_root . 'touchstone/include/sidebar_menu.inc';
$paperID = $_GET['paperID'];

$paper_details = $mysqli->query("SELECT paper_title, moduleID, folder, paper_ownerID, title, initials, surname, moduleID, DATE_FORMAT(start_date,'%Y%m%d%H%i%S') AS start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i%S') AS end_date, DATE_FORMAT(created,'%d/%m/%Y% %H:%i') AS created, MAX(screen) AS screen, fullscreen, MAX(display_pos) AS display_pos, paper_type, externals, internal_reviewers, labs, calendar_year, exam_duration FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND property_id=$paperID GROUP BY paper_title");
while ($row = $paper_details->fetch_assoc()) {
if (date("YmdHis", time()) >= $row['start_date'] and date("YmdHis", time()) <= $row['end_date']) {
  $active_date = 1;
} else {
  $active_date = 0;
}
if (date("YmdHis", time()) >= $row['start_date'] and $row['paper_type'] == '2') {
  $summative_lock = 1;
} else {
  $summative_lock = 0;
}
$max_display_pos = $row['display_pos'];
$max_screen = $row['screen'];
if ($max_screen == '') $max_screen = 0;  // In case screen was blank, i.e. no questions on the paper.
  $paper_title = $row['paper_title'];
  $fullscreen = $row['fullscreen'];
  $paper_ownerID = $row['paper_ownerID'];
  $tmp_module = $row['moduleID'];
  $paper_type = $row['paper_type'];
  $start_date = $row['start_date'];
  $end_date = $row['end_date'];
  $paper_moduleID = $row['moduleID'];
  $paper_author = $row['surname'] . ', ' . $row['initials'] . ' ' . $row['title'];
  $created = $row['created'];
  $tmp_folder = $row['folder'];
  $externals = $row['externals'];
  $internal_reviewers = $row['internal_reviewers'];
  $labs = $row['labs'];
  $session = $row['calendar_year'];
  $exam_duration = $row['exam_duration'];
}
$paper_details->close();

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
  foreach($tmp as $c) {
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
while ($row = $stmt->fetch()) {
  $question_list[$questionID] = 0;
}
$stmt->close();
?>

<script language="JavaScript" src="/touchstone/javascript/sidebar.js"></script>
<script language="JavaScript">
  function startPaper(fullsc) {
    <?php
    if ($paper_type == '4') {      // OSCE
    ?>
      window.open("/osce/form.php?paperID=<?php echo $paperID; ?>&username=test","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } else {
    ?>
    if (fullsc == 0) {
      window.open("/touchstone/paper/start.php?paperID=<?php echo $paperID; ?>&mode=preview","paper","width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    } else {
      window.open("/touchstone/paper/start.php?paperID=<?php echo $paperID; ?>&mode=preview","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    }
    <?php
    }
    ?>
  }

  function addQuestion(qType,paperID) {
    if (paperID == 0) {
      document.location.href='/touchstone/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value;
    } else {
      document.location.href='/touchstone/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value + '&paperID=' + paperID + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>';
    }
  }

  function paperProperties() {
    notice=window.open("/touchstone/paper/properties.php?paperID=<?php echo $paperID; ?>","properties","width=832,height=650,left="+(screen.width/2-412)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function lockedPaperProperties() {
    notice=window.open("/touchstone/paper/properties_locked.php?paperID=<?php echo $paperID; ?>","properties","width=827,height=515,left="+(screen.width/2-412)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function editQuestion() {
    document.location.href='/touchstone/question/edit/' + document.PapersMenu.qType.value + '.php?q_id=' + document.PapersMenu.questionID.value + '&qNo=' + document.PapersMenu.questionNo.value + '&paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&calling=paper&scrOfY=' + document.PapersMenu.scrOfY.value;
  }

  function deleteQuestion() {
    notice=window.open("/touchstone/delete/check_delete_q_pointer.php?questionID=" + document.PapersMenu.pID.value + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&q_id=" + document.PapersMenu.questionID.value + "&scrOfY=" + document.PapersMenu.scrOfY.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function deletePaper() {
    notice=window.open("/touchstone/delete/check_delete_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function retirePaper() {
    notice=window.open("check_retire_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function editScreenNo() {
    notice=window.open("/touchstone/paper/edit_screen_no.php?questionID=" + document.PapersMenu.pID.value + "&paperID=<?php echo $paperID; ?>&screen=" + document.PapersMenu.screenNo.value + "&display_pos=" + document.PapersMenu.oldQuestionID.value + "&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=" + document.getElementById('scrOfY').value + "","properties","width=300,height=200,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-150,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function changeScreen(screenNo) {
    window.location='/touchstone/paper/change_screen_no.php?questionID=' + document.PapersMenu.pID.value + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.oldQuestionID.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value; 
  }
  
  function incScreen() {
    screenNo = document.PapersMenu.screenNo.value;
    screenNo++;
    window.location='/touchstone/paper/change_screen_no.php?questionID=' + document.PapersMenu.pID.value + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.oldQuestionID.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value;
  }
  
  function addQuestions(display, screen_no) {
    winH = screen.height - 80
    winW = screen.width - 80
    notice=window.open("/touchstone/question/add/add_questions_frame.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=" + document.getElementById('scrOfY').value + "&display_pos=" + display + "&max_screen=" + screen_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function noAdd(start, end) {
    winH = screen.height - 40
    notice=window.open("/touchstone/question/add/noadd.php?paperID=<?php echo $paperID; ?>&start_date=" + start + "&end_date=" + end + "","notice","width=770,height=550,left="+(screen.width/2-385)+",top="+(screen.height/2-275)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("/touchstone/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("/touchstone/question/info.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=500,height=500,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function promote() {
    if (document.getElementById('prev_screen').value != '') {
      if (document.getElementById('prev_screen').value < document.getElementById('current_screen').value) {
        document.location.href = "/touchstone/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + document.getElementById('current_pos').value + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('prev_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      } else {
        document.location.href = "/touchstone/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + (parseInt(document.getElementById('current_pos').value) - 1) + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('prev_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      }
    }
  }

  function demote() {
    if (document.getElementById('next_screen').value != '') {
      if (document.getElementById('next_screen').value > document.getElementById('current_screen').value) {
        document.location.href = "/touchstone/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + document.getElementById('current_pos').value + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('next_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      } else {
        document.location.href = "/touchstone/paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&old_pos=" + document.getElementById('current_pos').value + "&new_pos=" + (parseInt(document.getElementById('current_pos').value) + 1) + "&old_screen=" + document.getElementById('current_screen').value + "&new_screen=" + document.getElementById('next_screen').value + "&scrOfY=" + document.getElementById('scrOfY').value;
      }
    }
  }
  
  function showAssStatsMenu(e) {
    hideMenus();
    document.getElementById('copy_submenu').style.display='none';
    document.getElementById('stats_menu').style.display='block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function showCopyMenu(e) {
    hideMenus();
    document.getElementById('stats_menu').style.display='none';
    document.getElementById('copy_submenu').style.display='block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function showChangeScreen(e) {
    hideMenus();
    document.getElementById('stats_menu').style.display='none';
    document.getElementById('copy_submenu').style.display='none';
    document.getElementById('change_screen_submenu').style.display='block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function hideAssStatsMenu(e) {
    document.getElementById('stats_menu').style.display='none';
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
<tr><td style="font-weight:bold">Paper Tasks</td></tr>
<tr><td style="margin:4px">
<?php
  echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" id=\"menu1\" style=\"margin-left:7px; font-size:90%; display:block\">\n";
  if ($paper_type == '5') {      // Offline
    echo "<tr><td style=\"width:20px>3<img src=\"/touchstone/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"Arrow\" border=\"0\" /></td><td class=\"greymenuitem\" style=\"width:166px\">Test &amp; Preview</td></tr>\n";
  } else {
    if ($p_id_count > 0) {
      echo "<tr><td style=\"width:20px\"><a href=\"#\" onclick=\"startPaper($fullscreen); return false;\"><img src=\"/touchstone/artwork/small_play.png\" width=\"16\" height=\"16\" alt=\"Arrow\" border=\"0\" /></a></td><td class=\"menuitem\" style=\"width:166px\"><a href=\"#\" onclick=\"startPaper($fullscreen); return false;\">Test &amp; Preview</a></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\"><img src=\"/touchstone/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"Arrow\" border=\"0\" /></td><td class=\"greymenuitem\" style=\"width:166px\">Test &amp; Preview</td></tr>\n";
    }
  }

  if ($team_member == true) {
    if ($summative_lock == 1) {
      if (strpos($userroles,'SysAdmin') !== false) {
        echo "<tr><td><a href=\"\" onclick=\"noAdd('$start_date','$end_date'); return false;\"><img src=\"/touchstone/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"Add\" border=\"0\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('$start_date','$end_date'); return false;\">Add Questions to Paper</a></td></tr>\n";
        echo "<tr><td><a href=\"\" onclick=\"paperProperties(); return false;\"><img src=\"/touchstone/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"Properties\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"paperProperties(); return false;\">Edit Properties</a></td></tr>\n";
      } else {
        echo "<tr><td><a href=\"\" onclick=\"noAdd('$start_date','$end_date'); return false;\"><img src=\"/touchstone/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"Add\" border=\"0\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('$start_date','$end_date'); return false;\">Add Questions to Paper</a></td></tr>\n";
        echo "<tr><td><a href=\"\" onclick=\"lockedPaperProperties(); return false;\"><img src=\"/touchstone/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"Properties\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"lockedPaperProperties(); return false;\">Edit Properties</a></td></tr>\n";
      }
    } else {
      echo "<tr><td><a href=\"\" onclick=\"addQuestions(" . ($max_display_pos + 1) . ",$max_screen); return false;\"><img src=\"/touchstone/artwork/add_questions_16.gif\" width=\"16\" height=\"16\" alt=\"Add\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"addQuestions(" . ($max_display_pos + 1) . ",$max_screen); return false;\">Add Questions to Paper</a></td></tr>\n";
      echo "<tr><td><a href=\"\" onclick=\"paperProperties(); return false;\"><img src=\"/touchstone/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"Properties\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"paperProperties(); return false;\">Edit Properties</a></td></tr>\n";
    }
    if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2' or $paper_type == '5') {
      if ($p_id_count == 0) {
        echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"Reports\" border=\"0\" /></td><td class=\"greymenuitem\"><span id=\"reports\">Reports</span></td></tr>\n";
      } else {
        echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"Reports\" border=\"0\" /></td><td class=\"menuitem\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\"><span id=\"reports\">Reports</span></a><img src=\"/touchstone/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n";
      }
      if (strpos($checklist,'mapping') !== false) {
        echo "<tr><td><a href=\"/touchstone/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"Statistics\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\">Mapped Objectives</a></td></tr>\n";
      }
      if ($paper_type == '5') {
        echo "<tr><td><a href=\"/touchstone/import/spotter_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/import_csv_16.gif\" width=\"16\" height=\"16\" alt=\"Import marks\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/import/spotter_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">Import Marks</a></td></tr>\n";
      } else {
        if (strpos($checklist,'stdset') !== false) {
          echo "<tr><td><a href=\"/touchstone/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/std_set_icon_16.gif\" width=\"16\" height=\"16\" alt=\"Standards Setting\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">Standards Setting</a></td></tr>\n";
        }
      }
    } elseif ($paper_type == '3') {
      echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"Statistics\" border=\"0\" /></td><td class=\"menuitem\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\"><span id=\"reports\">Reports</span></a><img src=\"/touchstone/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n";
    } elseif ($paper_type == '4') {
        echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"Reports\" border=\"0\" /></td><td class=\"menuitem\"><a onclick=\"showAssStatsMenu(event); return false;\" href=\"#\"><span id=\"reports\">Reports</span></a><img src=\"/touchstone/artwork/submenu_arrow.png\" width=\"16\" height=\"16\" alt=\">\" border=\"0\" /></td></tr>\n"; 
        echo "<tr><td><a href=\"/touchstone/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/import_csv_16.gif\" width=\"16\" height=\"16\" alt=\"Import OSCE marks\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">Import OSCE Marks</a></td></tr>\n";
      if (strpos($checklist,'mapping') !== false) {
        echo "<tr><td><a href=\"/touchstone/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"Statistics\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=$paper_title&sd=$start_date&ed=$end_date&module=" . $module . "&folder=" . $folder . "\">Mapped Objectives</a></td></tr>\n";
      }
    }
  } else {
    echo "<tr><td><img src=\"/touchstone/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"Add\" /></td><td class=\"greymenuitem\">Add Questions to Paper</td></tr>\n";
    echo "<tr><td><img src=\"/touchstone/artwork/properties_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"Properties\" /></td><td class=\"greymenuitem\">Edit Properties</td></tr>\n";
    if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
      echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"Statistics\" border=\"0\" /></td><td class=\"greymenuitem\">Reports</td></tr>\n";
      if (strpos($checklist,'stdset') !== false) {
        echo "<tr><td><a href=\"/touchstone/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"/touchstone/artwork/std_set_icon_16.gif\" width=\"16\" height=\"16\" alt=\"Standards Setting\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">Standards Setting</a></td></tr>\n";
      }
    } else {
      echo "<tr><td><img src=\"/touchstone/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"Statistics\" border=\"0\" /></td><td class=\"greymenuitem\">Reports</td></tr>\n";
    }
  }
?>
<tr><td><a onclick="showCopyMenu(event); return false;" href="#"><img src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy Icon" border="0" /></a></td><td class="menuitem"><a onclick="showCopyMenu(event); return false;" href="#"><span id="copy">Copy Paper</span></a><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>
<?php
  if ($team_member == true and $summative_lock != 1) {
    echo '<tr><td><a href="#" onclick="deletePaper(); return false;"><img src="/touchstone/artwork/delete_paper_16.gif" width="16" height="16" alt="Delete Icon" border="0" /></a></td><td class="menuitem"><a href="#" onclick="deletePaper(); return false;">Delete Paper</a></td></tr>';
  } else {
    echo '<tr><td><img src="/touchstone/artwork/delete_paper_grey_16.gif" width="16" height="16" alt="Delete Icon" /></td><td class="greymenuitem">Delete Paper</td></tr>';
  }
  
  echo '<tr><td><a href="#" onclick="retirePaper(); return false;"><img src="/touchstone/artwork/retire_16.png" width="16" height="16" border="0" alt="Retire Icon" /></a></td><td class="menuitem"><a href="#" onclick="retirePaper(); return false;">Retire Paper</a></td></tr>';

  if ($p_id_count == 0) {
    echo "<tr><td><img src=\"/touchstone/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"Print Icon\" border=\"0\" /></td><td class=\"greymenuitem\">Print Hardcopy version</td></tr>\n";
  } else {
    if ($paper_type == '4') {
      echo "<tr><td><a href=\"/osce/print.php?paperID=$paperID\"><img src=\"/touchstone/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"Print Icon\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/osce/print.php?paperID=$paperID\">Print Hardcopy version</a></td></tr>\n";
    } else {
      echo "<tr><td><a href=\"/touchstone/paper/print.php?paperID=$paperID\"><img src=\"/touchstone/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"Print Icon\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"/touchstone/paper/print.php?paperID=$paperID\">Print Hardcopy version</a></td></tr>\n";
    }
  }
?>
<tr><td width="16"><img src="/touchstone/artwork/ims_16.png" width="16" height="16" alt="qti" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup6','papertasks','qti',myOptions6,myURLs6,event); return false;"><span id="qti">QTI Import/Export</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>
</td></tr>
</table>

<br />
<br />

<table id="currentquestion" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Current Question Tasks</td></tr>
<tr><td style="margin:4px">

<table style="margin-left:7px; display:block; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="/touchstone/artwork/edit_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem">Edit Question</td></tr>
<tr><td><img src="/touchstone/artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem">Information</td></tr>
<tr><td><img src="/touchstone/artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem">Copy onto Paper X...</td></tr>
<tr><td><img src="/touchstone/artwork/promote_disabled.gif" width="16" height="16" alt="Up" /></td><td class="greymenuitem">Move up</td></tr>
<tr><td><img src="/touchstone/artwork/demote_disabled.gif" width="16" height="16" alt="Down" /></td><td class="greymenuitem">Move down</td></tr>
<tr><td><img src="/touchstone/artwork/screen_icon_grey.png" width="16" height="16" alt="Change Screen No" /></td><td class="greymenuitem">Change Screen No.</td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Remove from Paper" /></td><td class="greymenuitem">Remove from Paper</td></tr>
</table>

<table style="margin-left:7px; display:none; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td><img style="cursor:hand" onclick="editQuestion(); return false;" src="/touchstone/artwork/edit.png" width="16" height="16" alt="Edit" /></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><img style="cursor:hand" onclick="questionInfo(); return false;" src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Information" /></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><img style="cursor:hand" onclick="copyToPaper(); return false;" src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" /></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><img style="cursor:hand" onclick="promote(); return false;" id="promoteicon" src="/touchstone/artwork/promote.gif" width="16" height="16" alt="Up" /></td><td class="menuitem"><a id="promotetext" onclick="promote(); return false;">Move up</a></td></tr>
<tr><td><img style="cursor:hand" onclick="demote(); return false;" id="demoteicon" src="/touchstone/artwork/demote.gif" width="16" height="16" alt="Down" /></td><td class="menuitem"><a id="demotetext" onclick="demote(); return false;">Move down</a></td></tr>
<?php
if ($paper_type == '4') {
  echo '<tr><td><img src="/touchstone/artwork/screen_icon_grey.png" width="16" height="16" alt="Screen" /></td><td class="greymenuitem">Change Screen No.</td></tr>';
} else {
  echo '<tr><td><img style="cursor:hand" src="/touchstone/artwork/screen_icon.png" width="16" height="16" alt="Screen" /></td><td class="menuitem"><a href="#" onclick="showChangeScreen(event); return false;"><span id="changescreen">Change screen No</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>';
  //echo '<tr><td><img style="cursor:hand" onclick="editScreenNo(); return false;" src="/touchstone/artwork/screen_icon.png" width="16" height="16" alt="Screen" /></td><td class="menuitem"><a id="screenno" href="#" onclick="editScreenNo(); return false;">Change Screen No.</a> <a href="" onclick="incScreen(); return false;">+</a></td></tr>';
}
?>
<tr><td><img style="cursor:hand" onclick="deleteQuestion(); return false;" src="/touchstone/artwork/red_cross.png" width="16" height="16" alt="Arrow" /></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;">Remove from Paper</a></td></tr>
</table>

<table style="margin-left:7px; display:none; font-size:90%" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td><img style="cursor:hand" onclick="editQuestion(); return false;" src="/touchstone/artwork/edit.png" width="16" height="16" alt="Edit" /></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><img style="cursor:hand" onclick="questionInfo(); return false;" src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Info" /></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><img style="cursor:hand" onclick="copyToPaper(); return false;" src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" /></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><img src="/touchstone/artwork/promote_disabled.gif" width="16" height="16" alt="Up" /></td><td class="greymenuitem">Move up</td></tr>
<tr><td><img src="/touchstone/artwork/demote_disabled.gif" width="16" height="16" alt="Down" /></td><td class="greymenuitem">Move down</td></tr>
<tr><td><img src="/touchstone/artwork/screen_icon_grey.png" width="16" height="16" alt="Change Screen No" /></td><td class="greymenuitem">Change Screen No.</td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Remove from Paper" /></td><td class="greymenuitem">Remove from Paper</td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Question Bank Tasks</td></tr>
<tr><td>
<table cellpadding="3" cellspacing="0" border="0" style="margin-left:7px; font-size:90%">
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup1','banktasks','type',myOptions1,myURLs1,event); return false;"><span id="type">Questions by Type</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup2','banktasks','team',myOptions2,myURLs2,event); return false;"><span id="team">Questions by Team</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event); return false;"><span id="keyword">Questions by Keyword</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td><img src="/touchstone/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup4','banktasks','search',myOptions4,myURLs4,event); return false;"><span id="search">Search</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="hideAssStatsMenu(event); showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event); return false;"><span id="newquestion">Create new Question</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>

<?php
if ($paper_type == '2') {
?>
<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Summative Checklist</td></tr>
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
    echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">Session</a></td><td style=\"color:#C00000\"><strong>Mismatch</strong></td></tr>\n";
  }
  // Times
  if (substr($start_date,-6) == substr($end_date,-6) or substr($start_date,-6) == '000000' or substr($start_date,-6) == '000000') {
    echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">Exam Time</a></td><td style=\"color:#C00000\"><strong>Incorrect</strong></td></tr>\n";
  }
  
  // Duration
  if ($exam_duration == '') {
    echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">Duration</a></td><td style=\"color:#C00000\"><strong>Unset</strong></td></tr>\n";
  }
  
  // Computer labs
  if ($labs == '') {
    echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">Computer Labs</a></td><td style=\"color:#C00000\"><strong>Unset</strong></td></tr>\n";
  }

  // Internal Peer review
  if (strpos($checklist,'peer') !== false) {
    if ($internal_reviewers == '') {
      echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td><a href=\"\" style=\"color:black\" onclick=\"paperProperties(); return false;\">Peer Reviewed</a></td><td style=\"color:#C00000\"><strong>Unset</strong></td></tr>\n";
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
        echo "<tr><td style=\"width:18px\"><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repdegree=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">Peer Reviews</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($internal_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td style=\"width:18px\"><img src=\"/touchstone/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repdegree=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">Peer Reviews</a></td><td style=\"color:#008000\"><strong>OK</strong></td></tr>\n";
      }
    }
  }

  // External examiners
  if (strpos($checklist,'external') !== false) {
    if ($externals == '') {
      echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td>External Reviews</td><td style=\"color:#C00000\"><strong>Unset</strong></td></tr>\n";
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
        echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repdegree=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">External Reviews</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($external_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td><img src=\"/touchstone/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repdegree=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">External Reviews</a></td><td style=\"color:#008000\"><strong>OK</strong></td></tr>\n";
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
      echo "<tr><td><img src=\"/touchstone/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">Standards Set</a></td>";
    } else {
      echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">Standards Set</a></td>";
    }
	
    if ($standards_set == 1) {
      echo "<td style=\"color:#008000\"><strong>OK</strong></td></tr>\n";
    } elseif ($standards_set == 0.5) {
      echo "<td style=\"color:#C00000\"><strong>Incomplete</strong></td></tr>\n";
    } else {
      echo "<td style=\"color:#C00000\"><strong>Unset</strong></td></tr>\n";
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
      echo "<tr><td><img src=\"/touchstone/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">Mapping</a></td><td style=\"color:$tmp_color\"><strong>$mappings_complete/" . count($question_list) . "</strong></td></tr>\n";
    } else {
      echo "<tr><td><img src=\"/touchstone/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" border=\"0\" /></td><td><a style=\"color:black\" href=\"/touchstone/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">Mapping</a></td><td style=\"color:#008000\"><strong>OK</strong></td></tr>\n";
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
      $url = '/touchstone/folder/details.php?module=' . $module_row['moduleid'];
      $modules_array[$title] = $url;
    }
    $module_details->close();
  }
  
  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = '/touchstone/question/list.php?type=%&team=' . $individual_team;
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = '../question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
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
    $keywords_array[htmlspecialchars($tmp_keyword)] = '../question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
  }
  $result->close();

  $screens = array('Insert screen break'=>'javascript:incScreen()','-'=>'-');
  for ($i=1; $i<=60; $i++) {
    $screens['Screen ' . $i] = "javascript:changeScreen($i)";
  }
  
  makeMenu($screens);
  makeMenu(array('Information Block'=>'/touchstone/question/list.php?type=info','Keyword-based Question'=>'/touchstone/question/list.php?type=keyword','Random Question Block'=>'/touchstone/question/list.php?type=random','-'=>'-','Calculation'=>'/touchstone/question/list.php?type=calculation','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/list.php?type=dichotomous','Extended Matching'=>'/touchstone/question/list.php?type=extmatch','Fill-in-the-Blank'=>'/touchstone/question/list.php?type=blank','Flash Interface'=>'/touchstone/question/list.php?type=flash','Image Hotspot'=>'/touchstone/question/list.php?type=hotspot','Labelling'=>'/touchstone/question/list.php?type=labelling','Likert Scale'=>'/touchstone/question/list.php?type=likert','Matrix'=>'/touchstone/question/list.php?type=matrix','Multiple Choice (MCQ)'=>'/touchstone/question/list.php?type=mcq','Multiple Response (MRQ)'=>'/touchstone/question/list.php?type=mrq','Ranking'=>'/touchstone/question/list.php?type=rank','Script Concordance Test (SCT)'=>'/touchstone/question/list.php?type=sct','Text Box'=>'/touchstone/question/list.php?type=textbox','Time/Date'=>'/touchstone/question/list.php?type=timedate'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array('Questions'=>'/touchstone/question/search.php','Papers'=>'/touchstone/paper/search.php','People'=>'/touchstone/users/search.php'));
  
  if ($summative_lock == 1) {
    $params = "?scrOfY=' + document.getElementById('scrOfY').value + '";
  } else {
    $params = "?scrOfY=' + document.getElementById('scrOfY').value + '&paperID=$paperID&folder=$folder&module=$tmp_module";
  }

  makeMenu(array("Information Block"=>"/touchstone/question/add/info.php$params","Keyword-based Question"=>"/touchstone/question/add/keyword_based.php$params","Random Question Block"=>"/touchstone/question/add/random.php$params","-"=>"-","Calculation"=>"/touchstone/question/add/calculation.php$params","Dichotomous (Yes/No and True/False)"=>"/touchstone/question/add/dichotomous.php$params","Extended Matching"=>"/touchstone/question/add/extmatch.php$params","Fill-in-the-Blank"=>"/touchstone/question/add/blank.php$params","Flash Interface"=>"/touchstone/question/add/flash.php$params","Image Hotspot"=>"/touchstone/question/add/hotspot1.php$params","Labelling"=>"/touchstone/question/add/labelling1.php$params","Likert Scale"=>"/touchstone/question/add/likert.php$params","Matrix"=>"/touchstone/question/add/matrix.php$params","Multiple Choice (MCQ)"=>"/touchstone/question/add/mcq.php$params","Multiple Response (MRQ)"=>"/touchstone/question/add/mrq.php$params","Ranking"=>"/touchstone/question/add/rank.php$params","Script Concordance Test (SCT)"=>"/touchstone/question/add/sct.php$params","Text Box"=>"/touchstone/question/add/textbox.php$params","Time/Date"=>"/touchstone/question/add/timedate.php$params"));
  if($summative_lock == 1) {
    makeMenu(array("Export 1.2"=>"/touchstone/qti/export.php?dest=qti12&paperID=$paperID"));
  } else {
    makeMenu(array("Import"=>"/touchstone/qti/import.php?paperID=$paperID","Export 1.2"=>"/touchstone/qti/export.php?dest=qti12&paperID=$paperID"));
  }
  
  hideMenuScript($menuNo);

  require $cfg_web_root . 'touchstone/include/reports_submenu.inc';
  require $cfg_web_root . 'touchstone/include/paper_copy_submenu.inc';
  require $cfg_web_root . 'touchstone/include/change_screen_submenu.inc';
?>
