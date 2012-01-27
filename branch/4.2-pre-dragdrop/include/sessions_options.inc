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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  if (in_array($_GET['module'], $teams) === false and strpos($userroles,'SysAdmin') === false) {
    exit;
  }
  
  $stmt = $mysqli->prepare("SELECT calendar_year, vle_api FROM student_modules, modules WHERE student_modules.moduleid=modules.moduleid AND student_modules.moduleid=? ORDER BY student_modules.id DESC LIMIT 1");
  $stmt->bind_param('s', $_GET['module']);
  $stmt->execute();
  $stmt->bind_result($session, $vle_api);
  $stmt->fetch();
  $stmt->close(); 

  if ($vle_api != '') {
    require_once("../apis/$vle_api.inc");
    $objsBySession = getObjectives($_GET['module'], $session);
  } else {
    require_once '../include/mapping.inc';
  }
?>
<script language="JavaScript">
  function addSession() {
    <?php
      if ($vle_api == 'NLE') {
        echo "alert(\"This is an NLE-based module. To add a new session it must be added in the NLE.\");\n";
      } else {
        echo "document.location.href=\"add_session.php?module=" . $_GET['module'] . "\";\n";
      }
    ?>
  }
  function importFromNLE() {
  <?php
      if ($vle_api == 'NLE') {
        echo "alert(\"This is an NLE-based module. To add a new session it must be added in the NLE.\");\n";
      } else {
        echo "document.location.href=\"copy_session_from_NLE.php?module=" . $_GET['module'] . "\";\n";
      }
    ?>
  }

  function editSession() {
    document.location.href='./edit_session.php?identifier=' + document.myform.identifier.value + '&module=<?php echo $_GET['module']; ?>&calendar_year=' + document.myform.session.value;
  }
  
  function editNLESession() {
    alert("This is an NLE-based module. To change its session objectives you must edit the NLE.");
  }
  
  function deleteSession() {
    notice=window.open("../delete/check_delete_session.php?moduleID=<?php echo $_GET['module']; ?>&session=" + document.myform.session.value + "&identifier=" + document.myform.identifier.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function deleteNLESession() {
    alert("This is an NLE-based module. To delete this session it must be removed from the NLE.");
  }

  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src') == '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif') ? '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/up_arrow_icon.gif' : '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "caaadmin[" + ID + "]";
    document.cookie = NameOfCookie + "=" + current +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function jumpTo() {
    window.top.location = '<?php echo $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path; ?>/paper_frame.php?paperID=' + document.releaseform.paperID.value;
  }

  function showSessCopyMenu(e) {
    document.getElementById('session_copy_menu').style.display='block';
    if (!e) var e = window.event;
    e.cancelBubble = true;
  }

  function hideSessCopyMenu(e) {
    document.getElementById('session_copy_menu').style.display='none';
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['sessions']; ?></td></tr>
<tr><td>
<table cellpadding="2" cellspacing="0" border="0" style="margin-left:7px; font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><img src="../artwork/new_session_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><?php echo $string['createsession']; ?></a></td></tr>
<?php
if (strpos($userroles,'SysAdmin') !== false) {
?>
  <tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><?php echo $string['importnle']; ?></a></td></tr>
<?php
}
if ($vle_api != 'NLE') {
  echo '<tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="load_session_from_txt.php?module=' . $_GET['module'] . '">' . $string['importfile'] . '</a></td></tr>';
}
?>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['editsession']; ?></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['deletesession']; ?></td></tr>
<?php
if ($vle_api != 'NLE') {
	echo '<tr><td><img onclick="showSessCopyMenu(event)" src="../artwork/copy_icon.gif" width="16" height="16" alt="Copy year" border="0" /></td><td class="menuitem"><a href="#" onclick="showSessCopyMenu(event); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
else {
	echo '<tr><td><img onclick="alert(\'Copy year is not available for NLE modules\') return false;" src="../artwork/copy_icon.gif" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="#" onclick="alert(\'Copy year is not available for NLE modules\'); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
?>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><img src="../artwork/new_session_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><?php echo $string['createsession']; ?></a></td></tr>
<?php
if (strpos($userroles,'SysAdmin') !== false) {
?>
  <tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><?php echo $string['importnle']; ?></a></td></tr>
<?php
}
if ($vle_api != 'NLE') {
  echo '<tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="load_session_from_txt.php?module=' . $_GET['module'] . '">' . $string['importfile'] . '</a></td></tr>';
}
?>
<tr><td><img onclick="hideSessCopyMenu(event); editSession()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="hideSessCopyMenu(event); editSession(); return false;"><?php echo $string['editsession']; ?></a></td></tr>
<tr><td><img onclick="hideSessCopyMenu(event); deleteSession()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="hideSessCopyMenu(event); deleteSession(); return false;"><?php echo $string['deletesession']; ?></a></td></tr>
<?php
if ($vle_api != 'NLE') {
  echo '<tr><td><img onclick="showSessCopyMenu(event)" src="../artwork/copy_icon.gif" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="showSessCopyMenu(event); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
else {
	echo '<tr><td><img onclick="alert(\'Copy year is not available for NLE modules\') return false;" src="../artwork/copy_icon.gif" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="#" onclick="alert(\'Copy year is not available for NLE modules\'); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
?>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1c">
<tr><td><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><img src="../artwork/new_session_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); addSession(); return false;"><?php echo $string['createsession']; ?></a></td></tr>
<?php
if (strpos($userroles,'SysAdmin') !== false) {
?>
  <tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><?php echo $string['importnle']; ?></a></td></tr>
<?php
}
if ($vle_api != 'NLE') {
  echo '<tr><td><a href="#" onclick="hideSessCopyMenu(event); importFromNLE(); return false;"><img src="../artwork/import_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="load_session_from_txt.php?module=' . $_GET['module'] . '">' . $string['importfile'] . '</a></td></tr>';
}
?>
<tr><td><img onclick="hideSessCopyMenu(event); editNLESession()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="hideSessCopyMenu(event); editNLESession(); return false;"><?php echo $string['editsession']; ?></a></td></tr>
<tr><td><img onclick="hideSessCopyMenu(event); deleteNLESession()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="hideSessCopyMenu(event); deleteNLESession(); return false;"><?php echo $string['deletesession']; ?></a></td></tr>
<?php
if ($vle_api != 'NLE') {
  echo '<tr><td><img onclick="showSessCopyMenu(event)" src="../artwork/copy_icon.gif" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="showSessCopyMenu(event); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
else {
	echo '<tr><td><img onclick="alert(\'Copy year is not available for NLE modules\') return false;" src="../artwork/copy_icon.gif" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="#" onclick="alert(\'Copy year is not available for NLE modules\'); return false;"><span>' . $string['copyyear'] . '</span></a><img src="' . $cfg_root_path . '/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></td></tr>';
}
?>
</table>

</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="session" value="" /><br />
<input type="hidden" name="identifier" value="" /><br />
<input type="hidden" name="divID" value="" /><br />
<input type="hidden" name="oldDivID" value="" />
<input type="hidden" name="VLE" value="" />
</form>
</div>
<?php
require $cfg_web_root . 'include/session_copy_submenu.inc';
?>
