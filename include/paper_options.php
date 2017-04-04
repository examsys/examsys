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
* Sidebar menu for papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/mapping.inc';

$userObject = UserObject::get_instance();

$clarif_types = $configObject->get('midexam_clarification');

if (!isset($properties)) {
  $properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
}

if ($properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin', 'Admin')) and $properties->is_live() and $properties->get_bidirectional() == '1' and count($clarif_types) > 0) {
  $exam_clarifications = true;  
} else {
  $exam_clarifications = false;  
}

if (!isset($module)) {
  $module = param::optional('module', '', param::INT, param::FETCH_GET); 
}

if (!isset($folder)) {
  $folder = param::optional('folder', '', param::INT, param::FETCH_GET); 
}

$moduleIDs = $properties->get_modules();
$checklist = '';
if (count($moduleIDs) > 0) {
  $moduleIDs = array_keys($moduleIDs);
  $stmt = $mysqli->prepare("SELECT checklist FROM modules WHERE id IN (" . implode(',', $moduleIDs) . ")");
  $stmt->execute();
  $stmt->bind_result($tmp_checklist);
  $check = array();
  while ($stmt->fetch()) {
    if ($tmp_checklist != '') {
      $tmp = explode(',', $tmp_checklist);
      foreach ($tmp as $c => $type) {
        $check[] = $type;
      }
    }
  }
  $checklist = implode(',', $check);
  $stmt->close();
}
?>

<?php echo $configObject->get('cfg_js_root') ?>
<script type="text/javascript" src="<?php echo $configObject->get('cfg_root_path') ?>/js/sidebar.js"></script>
<script>
  function startPaper(fullsc, preview) {
    var urlMod = (typeof preview == 'undefined' || !preview) ? '' : '&q_id=' + getLastID($('#questionID').val() + '&qNo=' + $('#questionNo').val());
    <?php
    if ($properties->get_paper_type() == '4') {      // OSCE
    ?>
      window.open("<?php echo $configObject->get('cfg_root_path') ?>/osce/form.php?id=<?php echo $properties->get_crypt_name(); ?>&username=test","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } elseif ($properties->get_paper_type() == '6') {
    ?>
      window.open("<?php echo $configObject->get('cfg_root_path') ?>/peer_review/form.php?id=<?php echo $properties->get_crypt_name(); ?>","paper","width=1024,height=600,left=0,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    <?php
    } else {
    ?>
    if (fullsc == 0) {
      window.open("<?php echo $configObject->get('cfg_root_path') ?>/paper/start.php?id=<?php echo $properties->get_crypt_name(); ?>&mode=preview" + urlMod,"paper","width="+(screen.width-80)+",height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    } else {
      window.open("<?php echo $configObject->get('cfg_root_path') ?>/paper/start.php?id=<?php echo $properties->get_crypt_name(); ?>&mode=preview" + urlMod,"paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    }
    <?php
    }
    ?>
  }

  function addQuestion(qType, paperID) {
    if (paperID == 0) {
      document.location.href='<?php echo $configObject->get('cfg_root_path') ?>/question/add/' + qType + '.php?scrOfY=' + $('#scrOfY').val();
    } else {
      document.location.href='<?php echo $configObject->get('cfg_root_path') ?>/question/add/' + qType + '.php?scrOfY=' + $('#scrOfY').val() + '&paperID=' + paperID + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>';
    }
  }

  function paperProperties() {
    <?php
    $html = '';
    $module = param::optional('module', null, param::INT, param::FETCH_GET);
    if (!is_null($module)) {
       $html .= '&module=' . $module; 
    }
    
    $folder = param::optional('folder', null, param::INT, param::FETCH_GET); 
    if (!is_null($folder)) {
      $html .= '&folder=' . $folder;
    }
    ?>
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/paper/properties.php?paperID=<?php echo $paperID; ?>&caller=details<?php echo $html; ?>","properties","width=888,height=650,left="+(screen.width/2-431)+",top="+(screen.height/2-325)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");

    if (window.focus) {
      notice.focus();
    }
    if (this.event) this.event.returnValue = false;
    return false;
  }

  function getLastID(IDs) {
    var id_list = IDs.split(",");
    last_elm = id_list.length - 1;

    return id_list[last_elm];
  }

  function editQuestion() {
    var loc = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID($('#questionID').val()) + '&qNo=' + $('#questionNo').val() + '&paperID=<?php echo $paperID; ?>&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&calling=paper&scrOfY=' + $('#scrOfY').val();
    if ($('#qType').val() == 'random' || $('#qType').val() == 'keyword_based') {
      loc += '&type=' + $('#qType').val();
    }
    document.location = loc;
  }

  function deleteQuestion() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_q_pointer.php?questionID=" + $('#questionID').val() + "&pID=" + $('#pID').val() + "&paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=" + $('#scrOfY').val() + "","notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-105);
    if (window.focus) {
      notice.focus();
    }
  }
	
	function killerq() {
		qID = getLastID($('#questionID').val());
		qNumber = $('#questionNo').val();
		url = '../ajax/paper/set_unset_killer_question.php';
		
		if ( $("#icon_" + qNumber).hasClass("killer_icon") ) {
			$("#icon_" + qNumber).removeClass("killer_icon");
		} else {
			$("#icon_" + qNumber).addClass("killer_icon");
		}
		
		var posting = $.post(url, { paperID: <?php echo $paperID; ?>, q_id: qID, qNumber: qNumber } );
		
	}

  function deletePaper() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_paper.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-105);
    if (window.focus) {
      notice.focus();
    }
  }

  function retirePaper() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/paper/check_retire_paper.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=500,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-105);
    if (window.focus) {
      notice.focus();
    }
  }

  function changeScreen(screenNo) {
    window.location='<?php echo $configObject->get('cfg_root_path') ?>/paper/change_screen_no.php?questionID=' + $('#pID').val() + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + $('#current_pos').val() + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + $('#scrOfY').val();
  }

  function incScreen() {
    screenNo = $('#screenNo').val();
    screenNo++;
    window.location = '<?php echo $configObject->get('cfg_root_path') ?>/paper/change_screen_no.php?questionID=' + getLastID($('#pID').val()) + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.current_pos.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + $('#scrOfY').val();
  }

  function addQuestions(display, screen_no) {
    winH = screen.height - 100
    winW = screen.width - 80
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/add/add_questions_frame.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=" + $('#scrOfY').val() + "&display_pos=" + display + "&max_screen=" + screen_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/copy_onto_paper.php?q_id=" + $('#questionID').val() + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/link_to_paper.php?q_id=" + $('#questionID').val() + "","linktopaper","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/info.php?q_id=" + getLastID($('#questionID').val()) + "","notice","width=700,height=660,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-350,screen.height/2-330);
    if (window.focus) {
      notice.focus();
    }
  }

  function examClarification() {
    if ($('#qType').val() != 'info') {
      notice = window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/exam_clarification.php?paperID=<?php echo $paperID; ?>&q_id=" + getLastID($('#questionID').val()) + "&questionNo=" + $('#questionNo').val() + "&screenNo=" + $('#screenNo').val() + "","notice","width=800,height=450,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      notice.moveTo(screen.width/2-400,screen.height/2-225);
      if (window.focus) {
        notice.focus();
      }
    }
  }

  function showAssStatsMenu(showMenu, e) {
    hideMenus();
    if (showMenu) {
      $('#stats_menu').show("slow");
    } else {
      $('#copy_submenu').hide();
      $('#copy_from_submenu').hide();
      $('#stats_menu').toggle();
    }
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideAssStatsMenu(e) {
    $('#stats_menu').hide();
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }
  
  function showCopyMenu(showMenu, e) {
    hideMenus();
    if (showMenu) {
      $('#copy_submenu').show();
    } else {
      $('#copy_submenu').toggle();
      $('#copy_from_submenu').hide();
      $('#stats_menu').hide();
    }
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function showCopyFromMenu(showMenu, e) {
    hideMenus();
    if (showMenu) {
      $('#copy_from_submenu').show();
    } else {
      $('#copy_from_submenu').toggle();
      $('#copy_submenu').hide();
      $('#stats_menu').hide();
    }
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideCopyMenu(e) {
    $('#copy_submenu').hide();
    $('#copy_from_submenu').hide();
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

</script>

<div id="left-sidebar" class="sidebar">

<form name="PapersMenu" action="" autocomplete="off">
<input type="hidden" id="questionNo" name="questionNo" value="" />
<input type="hidden" id="questionID" name="questionID" value="" />
<input type="hidden" id="pID" name="pID" value="" />
<input type="hidden" id="qType" name="qType" value="" />
<input type="hidden" id="screenNo" name="screenNo" value="" />
<input type="hidden" id="scrOfY" name="scrOfY" value="0" />
<input type="hidden" id="current_pos" name="current_pos" value="" />

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks'] ?></div>
<?php
// - Paper Tasks ----------------------------------------------------------
echo "<div id=\"menu1\">\n";
  if ($properties->get_paper_type() == '5') {      // Offline
    echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_play_grey.png\" alt=\"" . $string['testpreview'] . "\" />" . $string['testpreview'] . "</div>\n";
  } else {
    if ($properties->get_item_no() > 0) {
      echo "<div class=\"menuitem\"><a href=\"#\" onclick=\"startPaper(" . $properties->get_fullscreen() ."); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_play.png\" alt=\"" . $string['testpreview'] . "\" />" . $string['testpreview'] . "</a></div>\n";
    } else {
      echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_play_grey.png\" alt=\"" . $string['testpreview'] . "\" />" . $string['testpreview'] . "</div>\n";
    }
  }

  if ($properties->get_summative_lock() == 1) {
    echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_grey.gif\" alt=\"" . $string['addquestionspaper'] . "\" />" . $string['addquestionspaper'] . "</div>\n";
  } else {
    $max_screen = ($properties->get_max_screen() != '') ? $properties->get_max_screen() : 0;
    echo "<div class=\"menuitem\" onclick=\"addQuestions(" . ($properties->get_max_display_pos() + 1) . ", $max_screen)\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_16.gif\" alt=\"" . $string['addquestionspaper'] . "\" /><a href=\"#\" onclick=\"return false\">" . $string['addquestionspaper'] . "</a></div>\n";
  }
  echo "<div class=\"menuitem\"><a href=\"#\" onclick=\"return paperProperties(); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/properties_icon.gif\" alt=\"" . $string['editproperties'] . "\" />" . $string['editproperties'] . "</a></div>\n";
  if ($properties->get_paper_type() == '2') {
    if (is_null($properties->get_external_review_deadline())) {
      echo "<div class=\"grey menuitem\" id=\"emailexternalsgrey\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_email_grey.png\" alt=\"" . $string['emailexternals'] . "\" />" . $string['emailexternals'] . "</a></div>\n";
    } else {
      echo "<div class=\"menuitem cascade\" id=\"emailexternals\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup1','papertasks','emailexternals',myOptions1,myURLs1,event); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_email.png\" alt=\"" . $string['emailexternals'] . "\" />" . $string['emailexternals'] . "</a></div>\n";
    }
  }
  if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2' or $properties->get_paper_type() == '5' or $properties->get_paper_type() == '6') {
    if ($properties->get_item_no() == 0) {
      echo "<div class=\"grey menuitem greycascade\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon_grey.gif\" alt=\"" . $string['reports'] . "\" />" . $string['reports'] . "</div>\n";
    } else {
      echo "<div class=\"menuitem cascade\"><a href=\"#\" onclick=\"showAssStatsMenu(false, event); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" alt=\"" . $string['reports'] . "\" />" . $string['reports'] . "</a></div>\n";
    }
    if (strpos($checklist, 'mapping') !== false and $properties->get_paper_type() != '6') {
		  if ($properties->get_calendar_year() == '') {
				echo "<div class=\"greymenuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/curriculum_map_small_grey.png\" alt=\"" . $string['mappedobjectives'] . "\" />" . $string['mappedobjectives'] . "</div>\n";
			} else {
				echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/curriculum_map_small.png\" alt=\"" . $string['mappedobjectives'] . "\" />" . $string['mappedobjectives'] . "</a></div>\n";
			}
		}
    if ($properties->get_paper_type() == '5') {
      echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/import/offline_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/import_16.gif\" alt=\"" . $string['importmarks'] . "\" />" . $string['importmarks'] . "</a></div>\n";
    } elseif ($properties->get_paper_type() != '6') {
      if (strpos($checklist, 'stdset') !== false) {
        echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/std_set_icon_16.gif\" alt=\"" . $string['standardssetting'] . "\" />" . $string['standardssetting'] . "</a></div>\n";
      }
    }
  } elseif ($properties->get_paper_type() == '3') {
    echo "<div class=\"menuitem cascade\" id=\"reports\"><a href=\"#\" onclick=\"showAssStatsMenu(false, event); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" alt=\"" . $string['reports'] . "\" />" . $string['reports'] . "</a></div>\n";
  } elseif ($properties->get_paper_type() == '4') {
    echo "<div class=\"menuitem cascade\" id=\"reports\"><a href=\"#\" onclick=\"showAssStatsMenu(false, event); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" alt=\"" . $string['reports'] . "\" />" . $string['reports'] . "</a></div>\n";
    $gradebook = new gradebook($mysqli);
    $graded = $gradebook->paper_graded($paperID);
    if (!$graded) {
      echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/import_16.gif\" alt=\"" . $string['importoscemarks'] . "\" />" . $string['importoscemarks'] . "</a></div>\n";
    }
    if (strpos($checklist, 'mapping') !== false) {
      echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/curriculum_map_small.png\" alt=\"" . $string['mappedobjectives'] . "\" />" . $string['mappedobjectives'] . "</a></div>\n";
    }
  }

	?>
	<div class="menuitem cascade" id="copy"><a href="#" onclick="showCopyMenu(false, event); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copypaper'] ?>" /><?php echo $string['copypaper'] ?></a></div>
	<div class="menuitem cascade" id="copyfrompaper"><a href="#" onclick="showCopyFromMenu(false, event); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyfrompaper'] ?>" /><?php echo $string['copyfrompaper'] ?></a></div>
	<?php
        // Disable paper deletion when summative paper is locked, or summative paper, centrally managed and user is a non admin.
        if ($properties->get_summative_lock() == 1 or ($configObject->get('cfg_summative_mgmt') and $properties->get_paper_type() == '2' and !$userObject->has_role(array('Admin', 'SysAdmin')))) {
          echo '<div class="grey menuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_grey_16.gif" alt="' . $string['deletepaper'] . '" />' . $string['deletepaper'] . '</div>';
        } else {
          echo '<div class="menuitem"><a href="#" onclick="deletePaper(); return false"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_16.gif" alt="' . $string['deletepaper'] . '" />' . $string['deletepaper'] . '</a></div>';
        }

		echo '<div class="menuitem"><a href="#" onclick="retirePaper(); return false;"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/retire_16.png" alt="' . $string['retirepaper'] . '" />' . $string['retirepaper'] . '</a></div>';

		if ($properties->get_item_no() == 0) {
			echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16_disabled.png\" alt=\"" . $string['printhardcopy'] . "\" />" . $string['printhardcopy'] . "</div>\n";
		} else {
			if ($properties->get_paper_type() == '4') {
				echo "<div class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/osce/print.php?paperID=$paperID\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" alt=\"" . $string['printhardcopy'] . "\" />" . $string['printhardcopy'] . "</a></div>\n";
			} else {
				echo "<div class=\"menuitem cascade\" id=\"hardcopy\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup3','papertasks','hardcopy',myOptions3,myURLs3,event); return false;\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" alt=\"" . $string['printhardcopy'] . "\" />" . $string['printhardcopy'] . "</a></div>\n";
			}
		}
	?>
	<div class="menuitem cascade" id="qti"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup2','papertasks','qti',myOptions2,myURLs2,event); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/ims_16.png" alt="<?php echo $string['importexport'] ?>" /><?php echo $string['importexport'] ?></a></div>
</div>

<br />

<?php
// - Current Question Tasks ---------------------------------------------------
?>
<div class="submenuheading" id="currentquestion"><?php echo $string['currentquestiontasks'] ?></div>

<div id="menu2a">
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></div>
	<?php
		if ($exam_clarifications) {
			echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/comment_16_grey.png\" alt=\"\" />" . $string['midexamclarification'] . "</div>\n";
		}
	?>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" alt="<?php echo $string['linktopaper'] ?>" /><?php echo $string['linktopaper'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['removefrompaper'] ?>" /><?php echo $string['removefrompaper'] ?></div>
<?php
	if ($properties->get_paper_type() == '4') {
		echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/skull_16.png\" alt=\"skull\" /><span class=\"killer\">" . $string['unsetkillerquestion'] . "</span></div>\n";
	}
?>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" alt="<?php echo $string['previewquestion'] ?>" /><?php echo $string['previewquestion'] ?></div>
</div>

<div id="menu2b">
	<div class="menuitem" id="edit"><a href="#" onclick="editQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></a></div>
	<div class="menuitem" id="information"><a href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></a></div>
	<?php
		if ($exam_clarifications) {
			echo "<div class=\"menuitem clarification\" id=\"clarification\"><a href=\"#\" onclick=\"examClarification(); return false;\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/comment_16.png\" alt=\"" . $string['information'] . "\" />" . $string['midexamclarification'] . "</a></div>\n";
		}
	?>
	<div class="menuitem" id="copy"><a href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx'] ?></a></div>
	<div class="menuitem" id="link"><a href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaper'] ?>" /><?php echo $string['linktopaper'] ?></a></div>
	<div class="menuitem" id="delete"><a href="#" onclick="deleteQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" alt="<?php echo $string['removefrompaper'] ?>" /><?php echo $string['removefrompaper'] ?></a></div>
<?php
	if ($properties->get_paper_type() == '4') {
		echo "<div class=\"menuitem\" id=\"killerq\" onclick=\"killerq()\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/skull_16.png\" alt=\"skull\" /><span class=\"killer\"><a href=\"#\" onclick=\"return false\">" . $string['unsetkillerquestion'] . "</a></span></div>\n";
	}
?>
	<div class="menuitem" id="preview"><a href="#" onclick="startPaper(0, true); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play.png" alt="<?php echo $string['previewquestion'] ?>" /><?php echo $string['previewquestion'] ?></a></div>
</div>

<div id="menu2c">
	<div class="menuitem" id="edit"><a href="#" onclick="editQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></a></div>
	<div class="menuitem" id="information"><a href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></a></div>
	<?php
		if ($exam_clarifications) {
			echo "<div class=\"menuitem clarification\" id=\"clarification\" onclick=\"examClarification()\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/comment_16.png\" alt=\"" . $string['information'] . "\" /><a href=\"#\" onclick=\"return false\">" . $string['midexamclarification'] . "</a></div>\n";
		}
	?>
	<div class="menuitem" id="copy"><a href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></a></div>
	<div class="menuitem" id="link"><a href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="Link 2 Paper" /><?php echo $string['linktopaper'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['removefrompaper'] ?>" /><?php echo $string['removefrompaper'] ?></div>
<?php
	if ($properties->get_paper_type() == '4') {
		echo "<div class=\"menuitem\" id=\"killerq\"><a href=\"#\" onclick=\"killerq(); return false;\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/skull_16.png\" alt=\"skull\" /><span class=\"killer\">" . $string['unsetkillerquestion'] . "</a></span></div>\n";
	}
?>
	<div class="menuitem" id="preview"><a href="#" onclick="startPaper(0, true); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play.png" alt="<?php echo $string['previewquestion'] ?>" /><?php echo $string['previewquestion'] ?></a></div>
</div>

<!--[if lt IE 9]>
<div class="iefixdiv"></div><div class="iefixdiv"></div>
<![endif]-->

<?php
if ($properties->get_summative_lock() == true) {
?>
<ul id="break_controls" class="menu_list">
<?php
  if ($properties->get_paper_type() != '4') {
?>
  <li id="add_break" class="break greymenuitem"><?php echo $string['addscreenbreak'] ?></li>
  <li id="delete_break" class="greymenuitem"><?php echo $string['deletescreenbreak'] ?></li>
<?php
  }
  echo '</ul>';
} else {
?>
<ul id="break_controls" class="menu_list">
<?php
  if ($properties->get_paper_type() != '4') {
?>
  <li id="add_break" class="break greymenuitem"><a href="#"><?php echo $string['addscreenbreak'] ?></a></li>
  <li id="delete_break" class="greymenuitem"><a href="#"><?php echo $string['deletescreenbreak'] ?></a></li>
<?php
  }
  echo '</ul>';
}
?>
<div id="menu2a">
<?php
  $extra_url = '';
  $module = param::optional('module', null, param::INT, param::FETCH_GET);
  if (!is_null($module)) {
    $extra_url .= '&module=' . $module;
  }
  if ($extra_url != '') $extra_url = '?' . $extra_url;
?>
  <div class="menuitem cascade" id="newquestion"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup0','banktasks','newquestion',myOptions0,myURLs0,event); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="<?php echo $string['createnewquestion'] ?>" /><?php echo $string['createnewquestion'] ?></a></div>
</div>

<?php
if ($properties->get_paper_type() == '2') {
?>
<br />

<table style="width:210px; background-color: #2B569A; color: white !important; margin-bottom:16px">
  <tr>
    <td style="font-size:120%; font-weight:bold; padding-bottom:6px" colspan="3"><?php echo $string['summativechecklist'] ?></td>
  </tr>
<?php
  // Session
  $tmp_match = Paper_utils::academic_year_from_title($properties->get_paper_title());
	
  if ($tmp_match !== false and $tmp_match != $properties->get_calendar_year()) {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['session'] . "</a></td><td>" . $string['mismatch'] . "</td></tr>\n";
  }
  // Times
  if (date("His", $properties->get_start_date()) == date("His", $properties->get_end_date()) or date("His", $properties->get_start_date()) == '000000' or date("His", $properties->get_start_date()) == '000000') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['examtime'] . "</a></td><td>" . $string['incorrect'] . "</td></tr>\n";
  }

  // Duration
  if ($properties->get_exam_duration() == '') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['duration'] . "</a></td><td>" . $string['unset'] . "</td></tr>\n";
  }

  // Computer labs
  if ($properties->get_labs() == '') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['computerlabs'] . "</a></td><td>" . $string['unset'] . "</td></tr>\n";
  }

  // Internal Peer review
  if (strpos($checklist, 'peer') !== false) {
    if (count($properties->get_internal_reviewers()) == 0) {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['peerreviewes'] . "</a></td><td>" . $string['unset'] . "</td></tr>\n";
    } else {
      $tmp_array = $properties->get_internal_reviewers();
      $internal_array = array();
      foreach ($tmp_array as $reviewerID => $reviewer_name) {
        $internal_array[$reviewerID] = 0;
      }

      $stmt = $mysqli->prepare("SELECT DISTINCT reviewerID FROM review_metadata WHERE paperID = ? AND review_type = 'internal' AND complete IS NOT NULL");
      $stmt->bind_param('i', $paperID);
      $stmt->execute();
      $stmt->bind_result($reviewer);
      while ($stmt->fetch()) {
        $internal_array[$reviewer] = 1;
      }
      $stmt->close();
      $reviews_complete = 0;
      foreach ($tmp_array as $reviewerID => $reviewer_name) {
        if ($internal_array[$reviewerID] == 1) $reviews_complete++;
      }

      if ($reviews_complete < count($internal_array)) {
        if ($reviews_complete == 0) {
          $tmp_color = '#C00000';
        } else {
          $tmp_color = '#F27000';
        }
        echo "<tr style=\"height:16px\"><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $paperID . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td>$reviews_complete/" . count($internal_array) . "</td></tr>\n";
      } else {
        echo "<tr><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $paperID . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td>" . $string['ok'] . "</td></tr>\n";
      }
    }
  }

  // External examiners
  if (strpos($checklist, 'external') !== false) {
    if (count($properties->get_externals()) == 0) {
      echo "<tr><td style=\"height:16px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['externalreviews'] . "</td><td>" . $string['unset'] . "</td></tr>\n";
    } else {
      $tmp_array = $properties->get_externals();
      $external_array = array();
      foreach ($tmp_array as $reviewerID => $reviewer_name) {
        $external_array[$reviewerID] = 0;
      }

      $reviews_complete = 0;
      $stmt = $mysqli->prepare("SELECT DISTINCT reviewerID FROM review_metadata WHERE paperID = ? AND review_type = 'external' AND complete IS NOT NULL");
      $stmt->bind_param('i', $paperID);
      $stmt->execute();
      $stmt->bind_result($reviewer);
      while ($stmt->fetch()) {
        if (isset($external_array[$reviewer]) and $external_array[$reviewer] === 0) {
          $reviews_complete++;
        }
      }
      $stmt->close();
      $paperID = param::required('paperID', param::INT, param::FETCH_GET);
      if ($reviews_complete < count($external_array)) {
        echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $paperID . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td>$reviews_complete/" . count($external_array) . "</td></tr>\n";
      } else {
        echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $paperID . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td>" . $string['ok'] . "</td></tr>\n";
      }
    }
  }

  // Standards Set
  $standard_set = 0;
  $standards_set = 0;
  if (strpos($checklist, 'stdset') !== false) {
    $stmt = $mysqli->prepare("SELECT COUNT(std_set.id), setterID FROM std_set_questions, std_set WHERE std_set_questions.std_setID = std_set.id AND paperID = ? GROUP BY setterID");
    $stmt->bind_param('i', $paperID);
    $stmt->execute();
    $stmt->bind_result($set_set_records, $setterID);
    while ($stmt->fetch()) {
      if ($set_set_records >= $properties->get_question_no() and $standard_set == 0) {
        $standards_set = 1;
      } elseif ($set_set_records < $properties->get_question_no() and ($standard_set == 0 or $standard_set == 1)) {
        $standards_set = 0.5;
      }
    }
    $stmt->close();
    $paperID = param::required('paperID', param::INT, param::FETCH_GET);
    if ($standards_set == 1) {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $paperID . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
    } else {
      echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $paperID . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
    }

    if ($standards_set == 1) {
      echo "<td>" . $string['ok'] . "</td></tr>\n";
    } elseif ($standards_set == 0.5) {
      echo "<td>" . $string['incomplete'] . "</td></tr>\n";
    } else {
      echo "<td>" . $string['unset'] . "</td></tr>\n";
    }
  }

  // Mapped
  if (strpos($checklist, 'mapping') !== false) {
    $mappings_complete = 0;
    $tmp_session = $properties->get_calendar_year();

    $question_list = array();
    $stmt = $mysqli->prepare("SELECT question FROM papers, questions WHERE paper = ? AND papers.question = questions.q_id AND q_type != 'info'");
    $stmt->bind_param('i', $paperID);
    $stmt->execute();
    $stmt->bind_result($questionID);
    while ($stmt->fetch()) {
      $question_list[$questionID] = $questionID;
    }
    $stmt->close();
    $tmp_question_list = implode(',', array_keys($question_list));

    $objIDs = array();

    $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
    $objsBySession = getObjectives($moduleIDs, $tmp_session, $paperID, $tmp_question_list, $mysqli);
    if ($objsBySession !== 'error') {
      foreach ($objsBySession as $moduleCode) {
        foreach ($moduleCode as $sessionID) {
          if (isset($sessionID['objectives'])) {
            foreach ($sessionID['objectives'] as $objective) {
              $ID = $objective['id'];
              $objIDs[$ID] = $ID;
            }
          }
        }
      }
    }

    $mappings = array();
    $rels = Relationship::find($mysqli, '', $tmp_session, $paperID);
    if ($rels !== false and is_array($rels)) {
      foreach ($rels as $rel) {
        if (isset($question_list[$rel->get_question_id()]) and isset($objIDs[$rel->get_objective_id()])) {
          $mappings[$rel->get_question_id()] = $rel->get_question_id();
        }
      }
    }

    $mappings_complete = count($mappings);
    $paperID = param::required('paperID', param::INT, param::FETCH_GET);
    if ($objsBySession == 'error') {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td>Error</td></tr>\n";
    } elseif ($mappings_complete < $properties->get_question_no()) {
      echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td>$mappings_complete/" . $properties->get_question_no() . "</td></tr>\n";
    } else {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td>" . $string['ok'] . "</td></tr>\n";
    }
  }
	echo "</table>\n";
}
?>

</form>
</div>
<?php
  if ($properties->get_summative_lock()) {
    $params = "&scrOfY=' + $('#scrOfY').val() + '";
  } else {
    $params = "&scrOfY=' + $('#scrOfY').val() + '&paperID=$paperID&folder=$folder&module=" . $module. "&calling=paper";
  }

  if ($properties->get_paper_type() == '6') {
    makeMenu(array(
      $string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",
      $string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params")
    );
  } else {
    makeMenu(array(
      $string['info'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info$params",
      $string['keyword_based'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based$params",
      $string['random'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random$params",
      "-" => "-",
      $string['area'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area$params",
      $string['calculation'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc$params",
      $string['dichotomous'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous$params",
      $string['extmatch'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch$params",
      $string['blank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank$params",
      $string['hotspot'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot$params",
      $string['labelling'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling$params",
      $string['likert'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",
      $string['matrix'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix$params",
      $string['mcq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params",
      $string['mrq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq$params",
      $string['rank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank$params",
      $string['sct'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct$params",
      $string['textbox'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox$params",
      $string['true_false'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false$params")
    );
  }

	$importexport_menu = array();
	if (!$properties->get_summative_lock()) {
		$importexport_menu[$string['import']] = $configObject->get('cfg_root_path') . "/qti/import.php?paperID=$paperID&module=$module";
		$importexport_menu[$string['importraf']] = $configObject->get('cfg_root_path') . "/import/rogo_assessment_format.php?paperID=$paperID&module=$module";
		if ($properties->get_question_no() > 0) {
			$importexport_menu['-'] = "-";
		}
	}
	if ($properties->get_question_no() > 0) {
		$importexport_menu[$string['export12']] = $configObject->get('cfg_root_path') . "/qti/export.php?dest=qti12&paperID=$paperID&module=$module";
		$importexport_menu[$string['exportraf']] = $configObject->get('cfg_root_path') . "/export/rogo_assessment_format.php?paperID=$paperID";
	}

	$external_menu['Initial Invitation'] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=0";
	$external_menu['Reminder'] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=1";
	$external_menu['View Comments'] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=2";
  
  makeMenu($external_menu);  

	makeMenu($importexport_menu);
	
  makeMenu(array(
    $string['Continuous'] => $configObject->get('cfg_root_path') . "/paper/print.php?id=" . $properties->get_crypt_name(),
    $string['Page-break per question'] => $configObject->get('cfg_root_path') . "/paper/print.php?id=". $properties->get_crypt_name() . "&break=1")
  );

  hideMenuScript($menuNo);

  require_once $cfg_web_root . 'include/reports_submenu.inc';
  require_once $cfg_web_root . 'include/paper_copy_submenu.inc';
  $render = new render($configObject);
  $lang['papers'] = $string['copyfrompaper'];
  $lang['cancel'] = $string['cancel'];
  $lang['ok'] = $string['ok'];
  $lang['paperslinkquestions'] = $string['paperslinkquestions'];
  $lang['papercopyquestions'] = $string['papercopyquestions'];
  $lang['copyquestionsblurb'] = $string['copyquestionsblurb'];
  $data['action'] = "../paper/copy.php";
  $data['papertype'] = $properties->get_paper_type();
  $data['paperid'] = param::required('paperID', param::INT, param::FETCH_GET);
  $order = 'property_id';
  $direction = 'desc';
  $teamid = param::optional('teamID', null, param::INT, param::FETCH_GET); 
  $data['papers'] = PaperUtils::get_available_papers($userObject, $order, $direction, $properties->get_paper_type(), $module);
  $render->render($data, $lang, 'paper/copy_from_paper_menu.html')
?>
