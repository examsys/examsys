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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
$idMod = $_GET['module'];

$yearutils = new yearutils($mysqli);
$session = $yearutils->get_current_session();
$vle_api_cache = array();
$vle_api_data = MappingUtils::get_vle_api($idMod, $session, $vle_api_cache, $mysqli);
$vle_api = $vle_api_data['api'];
$map_level = $vle_api_data['level'];
$module_code = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);

if ($vle_api != '') {
  $vle = CMFactory::GetCMAPI($vle_api);
  $vle->setMappingLevel($map_level);
  $objsBySession = $vle->getObjectives($module_code, $session, $mysqli);
  $vle_name = $vle->getFriendlyName();
  $vle_name_a = $vle->getFriendlyName(true);
} else {
  require_once '../include/mapping.inc';
  $modules_array = array($idMod => $module_code);
  $objsBySession = getObjectives($modules_array, $session, '', '', $mysqli, 'all');
}
?>
<script>
  function addSession() {
    <?php
      if ($vle_api != '') {
        echo "alert(\"This is {$vle_name_a}-based module. To add a new session it must be added in the {$vle_name}.\");\n";
      } else {
        echo "document.location.href=\"add_session.php?module=" . $modID . "\";\n";
      }
    ?>
  }

  function editSession() {
    document.location.href='./edit_session.php?identifier=' + $('#identifier').val() + '&module=<?php echo $modID ?>&calendar_year=' + $('#session').val();
  }

  function editVLESession() {
<?php
  if ($vle_api != '') {
?>
    alert("This is <?php echo $vle_name_a ?>-based module. To change its session objectives you must edit the <?php echo $vle_name ?>.");
<?php
  }
?>
  }

  function deleteSession() {
    notice=window.open("../delete/check_delete_session.php?moduleID=<?php echo $modID ?>&session=" + $('#session').val() + "&identifier=" + $('#identifier').val() + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function deleteVLESession() {
<?php
if ($vle_api != '') {
?>
    alert("This is <?php echo $vle_name_a ?>-based module. To delete this session it must be removed from the <?php echo $vle_name ?> system.");
<?php
}
?>
  }

  function showSessCopyMenu(e) {
    $('#session_copy_menu').show();
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideSessCopyMenu(e) {
    $('#session_copy_menu').hide();
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">

<div id="menu1a">
<div class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><img class="sidebar_icon" src="../artwork/shortcut_calendar_icon.png" alt="" /><?php echo $string['createsession'] ?></a></div>
<?php
if ($vle_api == '') {
  echo '<div class="menuitem"><a href="load_session_from_txt.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />' . $string['importfile'] . '</a></div>';
  //This has not been written yet see ticket #708
  //echo '<div class="menuitem"><a href="export_session_to_txt.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />Export Sessions</a></div>';
}
?>
<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="" /><?php echo $string['editsession'] ?></div>
<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletesession'] ?></div>
<?php
if ($vle_api == '') {
	echo '<div class="menuitem cascade"><a href="#" onclick="showSessCopyMenu(event); return false;"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="Copy year" />' . $string['copyyear'] . '</a></div>';
} else {
	echo '<div class="menuitem" onclick="alert(\'Copy year is not available for ' . $vle_name . ' modules\'); return false;"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="" /><a href="#" onclick="return false">' . $string['copyyear'] . '</a></div>';
}
?>
</div>

<div style="display:none" id="menu1b">
<div class="menuitem" onclick="hideSessCopyMenu(event); addSession()"><img class="sidebar_icon" src="../artwork/shortcut_calendar_icon.png" alt="" /><a href="#" onclick="return false"><?php echo $string['createsession']; ?></a></div>
<?php
if ($vle_api == '') {
  echo '<div class="menuitem"><a href="load_session_from_txt.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />' . $string['importfile'] . '</a></div>';
  //This has not bee written yet see ticket #708
  //echo '<div class="menuitem"><a href="export_session_to_xml.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />Export Sessions</a></div>';
}
?>
<div class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); editSession(); return false;"><img class="sidebar_icon" src="../artwork/edit.png" alt="" /><?php echo $string['editsession'] ?></a></div>
<div class="menuitem"<a href="#" onclick="hideSessCopyMenu(event); deleteSession(); return false;"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><?php echo $string['deletesession'] ?></a></div>
<?php
if ($vle_api == '') {
  echo '<div class="menuitem cascade"><a href="#" onclick="showSessCopyMenu(event); return false;"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="" />' . $string['copyyear'] . '</a></div>';
}
else {
	echo '<div class="menuitem cascade"><a href="#" onclick="alert(\'Copy year is not available for ' . $vle_name . ' modules\'); return false;"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="" />' . $string['copyyear'] . '</a></div>';
}
?>
</div>

<div style="display:none" id="menu1c">
<div class="menuitem" onclick="hideSessCopyMenu(event); addSession();"><img class="sidebar_icon" src="../artwork/shortcut_calendar_icon.png" alt="" /><a href="#" onclick="return false"><?php echo $string['createsession']; ?></a></div>
<?php
if ($vle_api == '') {
  echo '<div class="menuitem"><a href="load_session_from_txt.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />' . $string['importfile'] . '</a></div>';
  //This has not been written yet see ticket #708
  //echo '<div class="menuitem"><a href="export_session_to_xml.php?module=' . $modID . '"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" />Export Sessions</a></div>';
}
?>
<div class="menuitem" onclick="hideSessCopyMenu(event); editVLESession()"><img class="sidebar_icon" src="../artwork/edit.png" alt="" /><a href="" onclick="return false"><?php echo $string['editsession']; ?></a></div>
<div class="menuitem" onclick="hideSessCopyMenu(event); deleteVLESession()"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><a href="" onclick="return false"><?php echo $string['deletesession']; ?></a></div>
<?php
if ($vle_api == '') {
  echo '<div class="menuitem cascade" onclick="showSessCopyMenu(event)"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="" /><a href="" onclick="return false">' . $string['copyyear'] . '</a></div>';
}
else {
	echo '<div class="menuitem" onclick="alert(\'Copy year is not available for ' . $vle_name . ' modules\')"><img class="sidebar_icon" src="../artwork/copy_icon.gif" alt="" /><a href="#" onclick="return false">' . $string['copyyear'] . '</a></div>';
}
?>
</div>

<input type="hidden" id="session" name="session" value="" /><br />
<input type="hidden" id="identifier" name="identifier" value="" /><br />
<input type="hidden" id="divID" name="divID" value="" /><br />
<input type="hidden" id="oldDivID" name="oldDivID" value="" />
<input type="hidden" id="VLE" name="VLE" value="" />
</form>
</div>
<?php
require $cfg_web_root . 'include/session_copy_submenu.inc';
?>
