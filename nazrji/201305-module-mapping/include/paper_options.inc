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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/mapping.inc';

require_once $cfg_web_root . 'classes/paperutils.class.php';
require_once $cfg_web_root . 'classes/paperproperties.class.php';
require_once $cfg_web_root . 'classes/relationship.class.php';

$userObject = UserObject::get_instance();

if (!isset($module)) {
  if (isset($_GET['module'])) {
    $module = $_GET['module'];
  } else{
    $module = '';
  }
}

if (!isset($folder)) {
  if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
  } else{
    $folder = '';
  }
}

if (!isset($properties)) {
  $properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
}

$modules_array = array();

$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);

$moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
$checklist = '';
if (count($moduleIDs) > 0) {
  $moduleIDs = array_keys($moduleIDs);
  $stmt = $mysqli->prepare("SELECT checklist FROM modules WHERE id IN (" . implode(',', $moduleIDs) . ")");
  $stmt->execute();
  $stmt->bind_result($tmp_checklist);
  $check[] = array();
  while ($stmt->fetch()) {
    $tmp = explode(',', $tmp_checklist);
    foreach ($tmp as $c) {
      $check[$c] = $c;
    }
  }
  $checklist = implode(',', array_keys($check));
  $stmt->close();
}
?>

<?php echo $configObject->get('cfg_js_root') ?>
<script language="JavaScript" src="<?php echo $configObject->get('cfg_root_path') ?>/js/sidebar.js"></script>
<script language="JavaScript">
  function startPaper(fullsc, preview) {
    var urlMod = (typeof preview == 'undefined' || !preview) ? '' : '&q_id=' + getLastID(document.PapersMenu.questionID.value);
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
      document.location.href='<?php echo $configObject->get('cfg_root_path') ?>/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value;
    } else {
      document.location.href='<?php echo $configObject->get('cfg_root_path') ?>/question/add/' + qType + '.php?scrOfY=' + document.getElementById('scrOfY').value + '&paperID=' + paperID + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>';
    }
  }

  function paperProperties() {
    <?php
    $html = '';
    if (isset($_GET['module'])) {
      $html .= '&module=' . $_GET['module'];
    }
    if (isset($_GET['folder'])) {
      $html .= '&folder=' . $_GET['folder'];
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
    var loc = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID(document.PapersMenu.questionID.value) + '&qNo=' + document.PapersMenu.questionNo.value + '&paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&calling=paper&scrOfY=' + document.PapersMenu.scrOfY.value;
    if (document.PapersMenu.qType.value == 'random' || document.PapersMenu.qType.value == 'keyword_based') {
      loc += '&type=' + document.PapersMenu.qType.value;
    }
    document.location = loc;
  }

  function deleteQuestion() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_q_pointer.php?questionID=" + document.PapersMenu.questionID.value + "&pID=" + document.PapersMenu.pID.value + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&q_id=" + document.PapersMenu.questionID.value + "&scrOfY=" + document.PapersMenu.scrOfY.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function deletePaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function retirePaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/paper/check_retire_paper.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function changeScreen(screenNo) {
    window.location='<?php echo $configObject->get('cfg_root_path') ?>/paper/change_screen_no.php?questionID=' + document.PapersMenu.pID.value + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.current_pos.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value;
  }

  function incScreen() {
    screenNo = document.PapersMenu.screenNo.value;
    screenNo++;
    window.location='<?php echo $configObject->get('cfg_root_path') ?>/paper/change_screen_no.php?questionID=' + getLastID(document.PapersMenu.pID.value) + '&paperID=<?php echo $paperID; ?>&screen=' + screenNo + '&display_pos=' + document.PapersMenu.current_pos.value + '&folder=<?php echo $folder; ?>&module=<?php echo $module; ?>&scrOfY=' + document.getElementById('scrOfY').value;
  }

  function addQuestions(display, screen_no) {
    winH = screen.height - 80
    winW = screen.width - 80
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/add/add_questions_frame.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=" + document.getElementById('scrOfY').value + "&display_pos=" + display + "&max_screen=" + screen_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function noAdd(start, end) {
    winH = screen.height - 40
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/add/noadd.php?paperID=<?php echo $paperID; ?>&start_date=" + start + "&end_date=" + end + "","notice","width=770,height=550,left="+(screen.width/2-385)+",top="+(screen.height/2-275)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/link_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","linktopaper","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/info.php?q_id=" + getLastID(document.PapersMenu.questionID.value) + "","notice","width=700,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-350,screen.height/2-300);
    if (window.focus) {
      notice.focus();
    }
  }

  function showAssStatsMenu(showMenu, e) {
    hideMenus();
    if (showMenu) {
      document.getElementById('stats_menu').style.display = 'block';
    } else {
      document.getElementById('copy_submenu').style.display = 'none';
      if (document.getElementById('stats_menu').style.display == 'none') {
        document.getElementById('stats_menu').style.display = 'block';
      } else {
        document.getElementById('stats_menu').style.display = 'none';
      }
    }
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideAssStatsMenu(e) {
    document.getElementById('stats_menu').style.display = 'none';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function showCopyMenu(showMenu, e) {
    hideMenus();
    if (showMenu) {
      document.getElementById('copy_submenu').style.display = 'block';
    } else {
      document.getElementById('stats_menu').style.display = 'none';
      if (document.getElementById('copy_submenu').style.display != 'block') {
        document.getElementById('copy_submenu').style.display = 'block';
      } else {
        document.getElementById('copy_submenu').style.display = 'none';
      }
    }
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideCopyMenu(e) {
    document.getElementById('copy_submenu').style.display = 'none';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

</script>

<div id="left-sidebar" class="sidebar">

<form name="PapersMenu" action="">
<input type="hidden" name="questionNo" value="" />
<input type="hidden" name="questionID" value="" />
<input type="hidden" name="pID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="screenNo" value="" />
<input type="hidden" name="scrOfY" id="scrOfY" value="0" />
<input type="hidden" name="current_pos" id="current_pos" value="" />
<div class="submenuheading" id="papertasks"><?php echo $string['papertasks']; ?></div>
<?php
  echo "<table class=\"sidebar_tbl\" id=\"menu1\">\n";
  if ($properties->get_paper_type() == '5') {      // Offline
    echo "<tr><td style=\"width:20px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" /></td><td class=\"greymenuitem\" style=\"width:166px\">" . $string['testpreview'] . "</td></tr>\n";
  } else {
    if ($properties->get_item_no() > 0) {
      echo "<tr><td style=\"width:20px\"><a href=\"#\" onclick=\"startPaper(" . $properties->get_fullscreen() ."); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_play.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" /></a></td><td class=\"menuitem\" style=\"width:166px\"><a href=\"#\" onclick=\"startPaper(" . $properties->get_fullscreen() . "); return false;\">" . $string['testpreview'] . "</a></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_play_grey.png\" width=\"16\" height=\"16\" alt=\"" . $string['testpreview'] . "\" /></td><td class=\"greymenuitem\" style=\"width:166px\">" . $string['testpreview'] . "</td></tr>\n";
    }
  }

  if ($properties->get_summative_lock() == 1) {
    if ($userObject->has_role('SysAdmin')) {
      echo "<tr><td><a href=\"\" onclick=\"noAdd('" . $properties->get_display_start_date() . "','" . $properties->get_display_end_date() . "'); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('" . $properties->get_display_start_date() . "','" . $properties->get_display_end_date() . "'); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
    } else {
      echo "<tr><td><a href=\"\" onclick=\"noAdd('" . $properties->get_display_start_date() . "','" . $properties->get_display_end_date() . "'); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" /></a></td><td class=\"greymenuitem\"><a href=\"#\" onclick=\"noAdd('" . $properties->get_display_start_date() . "','" . $properties->get_display_end_date() . "'); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
    }
  } else {
    $max_screen = ($properties->get_max_screen() != '') ? $properties->get_max_screen() : 0;
    echo "<tr><td><a href=\"\" onclick=\"addQuestions(" . ($properties->get_max_display_pos() + 1) . ", $max_screen); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['addquestionspaper'] . "\" /></a></td><td class=\"menuitem\"><a href=\"#\" onclick=\"addQuestions(" . ($properties->get_max_display_pos() + 1) . ",$max_screen); return false;\">" . $string['addquestionspaper'] . "</a></td></tr>\n";
  }
  echo "<tr><td><a href=\"\" onclick=\"return paperProperties();\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/properties_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['editproperties'] . "\" /></a></td><td class=\"menuitem\"><a href=\"\" onclick=\"return paperProperties();\">" . $string['editproperties'] . "</a></td></tr>\n";
  if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2' or $properties->get_paper_type() == '5' or $properties->get_paper_type() == '6') {
    if ($properties->get_item_no() == 0) {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" /></td><td class=\"greymenuitem\"><span id=\"reports\">" . $string['reports'] . "</span></td></tr>\n";
    } else {
      echo "<tr><td><a onclick=\"showAssStatsMenu(false, event); return false;\" href=\"#\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" /></a></td><td class=\"menuitem sub\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(false, event); return false;\" href=\"#\">" . $string['reports'] . "</a></span></td></tr>\n";
    }
    if (strpos($checklist, 'mapping') !== false and $properties->get_paper_type() != '6') {
      echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"" . $string['mappedobjectives'] . "\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\">" . $string['mappedobjectives'] . "</a></td></tr>\n";
    }
    if ($properties->get_paper_type() == '5') {
      echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/import/offline_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/upload_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['importmarks'] . "\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/import/offline_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['importmarks'] . "</a></td></tr>\n";
    } elseif ($properties->get_paper_type() != '6') {
      if (strpos($checklist, 'stdset') !== false) {
        echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/std_set_icon_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['standardssetting'] . "\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['standardssetting'] . "</a></td></tr>\n";
      }
    }
  } elseif ($properties->get_paper_type() == '3') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" /></td><td class=\"menuitem sub\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(false, event); return false;\" href=\"#\">" . $string['reports'] . "</a></span></td></tr>\n";
  } elseif ($properties->get_paper_type() == '4') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/statistics_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['reports'] . "\" /></td><td class=\"menuitem sub\"><span id=\"reports\"><a onclick=\"showAssStatsMenu(false, event); return false;\" href=\"#\">" . $string['reports'] . "</a></span></td></tr>\n";
    echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/import_16.gif\" width=\"16\" height=\"16\" alt=\"" . $string['importoscemarks'] . "\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/import/osce_marks.php?paperID=$paperID&module=" . $module . "&folder=" . $folder . "\">" . $string['importoscemarks'] . "</a></td></tr>\n";
    if (strpos($checklist, 'mapping') !== false) {
      echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/curriculum_map_small.png\" width=\"16\" height=\"16\" alt=\"" . $string['mappedobjectives'] . "\" border=\"0\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_session.php?paperID=$paperID&paper_title=" . $properties->get_paper_title() . "&sd=" . $properties->get_start_date() . "&ed=" . $properties->get_end_date() . "&module=" . $module . "&folder=" . $folder . "\">" . $string['mappedobjectives'] . "</a></td></tr>\n";
    }
  }

?>
<tr><td><a onclick="showCopyMenu(false, event); return false;" href="#"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copypaper']; ?>" /></a></td><td class="menuitem sub"><span id="copy"><a onclick="showCopyMenu(false, event); return false;" href="#"><?php echo $string['copypaper']; ?></a></span></td></tr>
<?php
  if ($properties->get_summative_lock() != 1) {
    echo '<tr><td><a href="#" onclick="deletePaper(); return false;"><img src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_16.gif" width="16" height="16" alt="' . $string['deletepaper'] . '" border="0" /></a></td><td class="menuitem"><a href="#" onclick="deletePaper(); return false;">' . $string['deletepaper'] . '</a></td></tr>';
  } else {
    echo '<tr><td><img src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_grey_16.gif" width="16" height="16" alt="' . $string['deletepaper'] . '" /></td><td class="greymenuitem">' . $string['deletepaper'] . '</td></tr>';
  }

  echo '<tr><td><a href="#" onclick="retirePaper(); return false;"><img src="' . $configObject->get('cfg_root_path') . '/artwork/retire_16.png" width="16" height="16" border="0" alt="' . $string['retirepaper'] . '" /></a></td><td class="menuitem"><a href="#" onclick="retirePaper(); return false;">' . $string['retirepaper'] . '</a></td></tr>';

  if ($properties->get_item_no() == 0) {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" /></td><td class=\"greymenuitem\">" . $string['printhardcopy'] . "</td></tr>\n";
  } else {
    if ($properties->get_paper_type() == '4') {
      echo "<tr><td><a href=\"{$configObject->get('cfg_root_path')}/osce/print.php?paperID=$paperID\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" /></a></td><td class=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/osce/print.php?paperID=$paperID\">" . $string['printhardcopy'] . "</a></td></tr>\n";
    } else {
      echo "<tr><td><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup6','papertasks','hardcopy',myOptions6,myURLs6,event); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['printhardcopy'] . "\" /></a></td><td class=\"menuitem sub\"><span id=\"hardcopy\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); showMenu('popup6','papertasks','hardcopy',myOptions6,myURLs6,event); return false;\">" . $string['printhardcopy'] . "</a></span></td></tr>\n";
    }
  }
?>
<tr><td width="16"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/ims_16.png" width="16" height="16" alt="<?php echo $string['qtiimportexport']; ?>" /></td><td class="menuitem sub"><span id="qti"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup5','papertasks','qti',myOptions5,myURLs5,event); return false;"><?php echo $string['qtiimportexport']; ?></a></span></td></tr>
</table>

<br />

<div class="submenuheading" id="currentquestion"><?php echo $string['currentquestiontasks']; ?></div>

<table class="sidebar_tbl" id="menu2a">
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></td><td class="greymenuitem"><?php echo $string['editquestion']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></td><td class="greymenuitem"><?php echo $string['information']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></td><td class="greymenuitem"><?php echo $string['copyontopaperx']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem"><?php echo $string['linktopaper']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></td><td class="greymenuitem"><?php echo $string['removefrompaper']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" width="16" height="16" alt="<?php echo $string['previewquestion']; ?>" /></td><td class="greymenuitem"><?php echo $string['previewquestion']; ?></td></tr>
</table>

<table class="sidebar_tbl" id="menu2b">
<tr><td class="ico"><a id="edit" onclick="editQuestion(); return false;" href="#"><img style="cursor:hand" onclick="editQuestion(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><a id="information" href="#" onclick="questionInfo(); return false;"><img style="cursor:hand" onclick="questionInfo(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img style="cursor:hand" onclick="copyToPaper(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="link" href="#" onclick="linkToPaper(); return false;"><img style="cursor:hand" onclick="linkToPaper(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td class="ico"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img style="cursor:hand" onclick="deleteQuestion(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><?php echo $string['removefrompaper']; ?></a></td></tr>
<tr><td class="ico"><a id="preview" onclick="startPaper(0, true); return false;" href="#"><img style="cursor:hand" onclick="startPaper(0, true); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play.png" width="16" height="16" alt="<?php echo $string['previewquestion']; ?>" /></a></td><td class="menuitem"><a id="edit" onclick="startPaper(0, true); return false;" href="#"><?php echo $string['previewquestion']; ?></a></td></tr>
</table>

<table class="sidebar_tbl" id="menu2c">
<tr><td class="ico"><img style="cursor:hand" onclick="editQuestion(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" width="16" height="16" alt="<?php echo $string['editquestion']; ?>" /></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><img style="cursor:hand" onclick="questionInfo(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" width="16" height="16" alt="<?php echo $string['information']; ?>" /></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><img style="cursor:hand" onclick="copyToPaper(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" width="16" height="16" alt="<?php echo $string['copyontopaperx']; ?>" /></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><img style="cursor:hand" onclick="linkToPaper(); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['removefrompaper']; ?>" /></td><td class="greymenuitem"><?php echo $string['removefrompaper']; ?></td></tr>
<tr><td class="ico"><a id="preview" onclick="startPaper(0, true); return false;" href="#"><img style="cursor:hand" onclick="startPaper(0, true); return false;" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play.png" width="16" height="16" alt="<?php echo $string['previewquestion']; ?>" /></a></td><td class="menuitem"><a id="edit" onclick="startPaper(0, true); return false;" href="#"><?php echo $string['previewquestion']; ?></a></td></tr>
</table>

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
  <li id="add_break" class="break greymenuitem"><?php echo $string['addscreenbreak']; ?></li>
  <li id="delete_break" class="greymenuitem"><?php echo $string['deletescreenbreak']; ?></li>
<?php
  }
  echo '</ul>';
} else {
?>
<ul id="break_controls" class="menu_list">
<?php
  if ($properties->get_paper_type() != '4') {
?>
  <li id="add_break" class="break greymenuitem"><a href="#"><?php echo $string['addscreenbreak']; ?></a></li>
  <li id="delete_break" class="greymenuitem"><a href="#"><?php echo $string['deletescreenbreak']; ?></a></li>
<?php
  }
  echo '</ul>';
}
?>
<div class="submenuheading" id="banktasks"><?php echo $string['questionbanktasks']; ?></div>

<table class="sidebar_tbl">
<tr><td class="ico"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="<?php echo $string['questionsbytype']; ?>" /></a></td><td class="menuitem sub"><span id="type"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><?php echo $string['questionsbytype']; ?></a></span></td></tr>
<?php
  if (count($staff_modules) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['questionsbyteam'] . "\" /></td><td class=\"greymenuitem sub\"><span id=\"team\">" . $string['questionsbyteam'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['questionsbyteam'] . "\" /></a></td><td class=\"menuitem sub\"><span id=\"team\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;\">" . $string['questionsbyteam'] . "</a></span></td></tr>\n";
  }
  if (count($keywords_array) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"{$string['questionsbykeyword']}\" /></td><td class=\"greymenuitem sub\"><span id=\"keyword\">{$string['questionsbykeyword']}</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"{$string['questionsbykeyword']}\" /></a></td><td class=\"menuitem sub\"><span id=\"keyword\"><a href=\"#\" onclick=\"hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;\">{$string['questionsbykeyword']}</a></span></td></tr>\n";
  }
?>
<tr><td class="ico"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" width="16" height="16" alt="<?php echo $string['search']; ?>" /></a></td><td class="menuitem sub"><span id="search"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><?php echo $string['search']; ?></a></span></td></tr>
<tr><td class="ico"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="<?php echo $string['createnewquestion']; ?>" /></a></td><td class="menuitem sub"><span id="newquestion"><a href="#" onclick="hideAssStatsMenu(event); hideCopyMenu(event); showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><?php echo $string['createnewquestion']; ?></a></span></td></tr>
</table>

<?php
if ($properties->get_paper_type() == '2') {
?>
<br />

<div class="submenuheading"><?php echo $string['summativechecklist']; ?></div>

<table class="sidebar_tbl">
<?php
  // Session
  $year_in_title = false;
  $tmp_match = '';
  if (preg_match( '/\d\d\d\d[\/-]\d\d\d\d/' , $properties->get_paper_title(), $matches) == 1) {
    $year_in_title = true;
    $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
  } elseif (preg_match( '/\d\d\d\d[\/-]\d\d/' , $properties->get_paper_title(), $matches) == 1) {
    $year_in_title = true;
    $tmp_match = substr($matches[0],0,4) . '/' . substr($matches[0],-2);
  } elseif (preg_match( '/\d\d[\/-]\d\d/' , $properties->get_paper_title(), $matches) == 1) {
    $year_in_title = true;
    $tmp_match = '20' . substr($matches[0],0,2) . '/' . substr($matches[0],-2);
  }
  if ($year_in_title == true and $tmp_match != $properties->get_calendar_year()) {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['session'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['mismatch'] . "</strong></td></tr>\n";
  }
  // Times
  if (date("His", $properties->get_start_date()) == date("His", $properties->get_end_date()) or date("His", $properties->get_start_date()) == '000000' or date("His", $properties->get_start_date()) == '000000') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['examtime'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['incorrect'] . "</strong></td></tr>\n";
  }

  // Duration
  if ($properties->get_exam_duration() == '') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['duration'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
  }

  // Computer labs
  if ($properties->get_labs() == '') {
    echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['computerlabs'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
  }

  // Internal Peer review
  if (strpos($checklist, 'peer') !== false) {
    if ($properties->get_internal_reviewers() == '') {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a href=\"\" class=\"checklist\" onclick=\"paperProperties(); return false;\">" . $string['peerreviewed'] . "</a></td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
    } else {
      $tmp_array = $properties->get_internal_reviewers();
      $internal_array = array();
      foreach ($tmp_array as $tmp_internal) {
        $internal_array[$tmp_internal] = 0;
      }

       $stmt = $mysqli->prepare("SELECT count(id), reviewer FROM review_comments WHERE q_paper = ? AND review_type = 'internal' GROUP BY reviewer, reviewed");
      $stmt->bind_param('i', $_GET['paperID']);
      $stmt->execute();
      $stmt->bind_result($review_records, $reviewer);
      while ($stmt->fetch()) {
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
        echo "<tr><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($internal_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['peerreviewes'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
      }
    }
  }

  // External examiners
  if (strpos($checklist, 'external') !== false) {
    if (count($properties->get_externals()) == 0) {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td>" . $string['externalreviews'] . "</td><td style=\"color:#C00000\"><strong>" . $string['unset'] . "</strong></td></tr>\n";
    } else {
      $tmp_array = $properties->get_externals();
      $external_array = array();
      foreach ($tmp_array as $tmp_external) {
        $external_array[$tmp_external] = 0;
      }

      $reviews_complete = 0;
      $stmt = $mysqli->prepare("SELECT count(id), reviewer FROM review_comments WHERE q_paper = ? AND review_type = 'external' GROUP BY reviewer, reviewed");
      $stmt->bind_param('i', $_GET['paperID']);
      $stmt->execute();
      $stmt->bind_result($review_records, $reviewer);
      while ($stmt->fetch()) {
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
        echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td style=\"color:$tmp_color\"><strong>$reviews_complete/" . count($external_array) . "</strong></td></tr>\n";
      } else {
        echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $_GET['paperID'] . "&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=" . $module . "&folder=" . $folder . "&percent=100&absent=0&direction=asc\">" . $string['externalreviews'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
      }
    }
  }

  // Standards Set
  $standard_set = 0;
  $standards_set = 0;
  if (strpos($checklist, 'stdset') !== false) {
    $stmt = $mysqli->prepare("SELECT COUNT(id), setterID FROM standards_setting WHERE paperID = ? GROUP BY setterID");
    $stmt->bind_param('i', $_GET['paperID']);
    $stmt->execute();
    $stmt->bind_result($set_set_records, $setterID);
    while ($row = $stmt->fetch()) {
      if ($set_set_records >= $properties->get_question_no() and $standard_set == 0) {
        $standards_set = 1;
      } elseif ($set_set_records < $properties->get_question_no() and ($standard_set == 0 or $standard_set == 1)) {
        $standards_set = 0.5;
      }
    }
    $stmt->close();
    if ($standards_set == 1) {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
    } else {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $folder . "\">" . $string['standardsset'] . "</a></td>";
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

    if ($mappings_complete < $properties->get_question_no()) {
      if ($mappings_complete == 0) {
        $tmp_color = '#C00000';
      } else {
        $tmp_color = '#F27000';
      }
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td style=\"color:$tmp_color\"><strong>$mappings_complete/" . $properties->get_question_no() . "</strong></td></tr>\n";
    } else {
      echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/bullet_icon.gif\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_mappings_by_question.php?paperID=" . $_GET['paperID'] . "&folder=" . $folder . "&module=" . $module . "\">" . $string['mapping'] . "</a></td><td style=\"color:#008000\"><strong>" . $string['ok'] . "</strong></td></tr>\n";
    }
  }
}
?>
</table>

</td></tr>
</table>
</form>
</div>
<?php
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['area']=>$configObject->get('cfg_root_path') . '/question/list.php?type=area',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=calculation',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php',$string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php',$string['people']=>$configObject->get('cfg_root_path') . '/users/search.php', '-'=>'-', $string['studentcohort']=>$configObject->get('cfg_root_path') . '/users/search.php?paperID=' . $_GET['paperID']));

  if ($properties->get_summative_lock()) {
    $params = "&scrOfY=' + document.getElementById('scrOfY').value + '";
  } else {
    $params = "&scrOfY=' + document.getElementById('scrOfY').value + '&paperID=$paperID&folder=$folder&module=" . $module. "&calling=paper";
  }

  if ($properties->get_paper_type() == '6') {
    makeMenu(array($string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params"));
  } else {
    makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info$params",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based$params",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random$params","-"=>"-",$string['area']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area$params",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=calculation$params",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous$params",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch$params",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank$params",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot$params",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling$params",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix$params",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq$params",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank$params",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct$params",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox$params",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false$params"));
  }

  if ($properties->get_summative_lock()) {
    makeMenu(array($string['export12']=>"{$configObject->get('cfg_root_path')}/qti/export.php?dest=qti12&paperID=$paperID&module=$module"));
  } else {
    makeMenu(array($string['import']=>"{$configObject->get('cfg_root_path')}/qti/import.php?paperID=$paperID&module=$module",$string['export12']=>"{$configObject->get('cfg_root_path')}/qti/export.php?dest=qti12&paperID=$paperID"));
  }
  makeMenu(array($string['Continuous']=>$configObject->get('cfg_root_path') . "/paper/print.php?id=" . $properties->get_crypt_name(), $string['Page-break per question']=>$configObject->get('cfg_root_path') . "/paper/print.php?id=". $properties->get_crypt_name() . "&break=1"));

  hideMenuScript($menuNo);

  require_once $cfg_web_root . 'include/reports_submenu.inc';
  require_once $cfg_web_root . 'include/paper_copy_submenu.inc';
  require_once $cfg_web_root . 'include/change_screen_submenu.inc';
?>
