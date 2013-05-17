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
* The sidebar menu of the user management section.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
require_once '../classes/stateutils.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli, $configObject->get('cfg_root_path') . '/users/search.php');

if (isset($_GET['moduleID'])) {
  $moduleID = $_GET['moduleID'];
} else {
  $moduleID = '';
}
if (isset($_GET['calendar_year'])) {
  $calendar_year = $_GET['calendar_year'];
} else {
  $calendar_year = '';
}
?>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/state.js"></script>
<script language="JavaScript">
  function updateDropdownState(mySel, NameOfState) {
    setting = mySel.options[mySel.selectedIndex].value;
    updateState(NameOfState, setting);
  }
  
  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src').indexOf('down_arrow_icon.gif')!=-1) ? '{$configObject->get('cfg_root_path')}/artwork/up_arrow_icon.gif' : '{$configObject->get('cfg_root_path')}/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);
    
    updateState('advanced', current);
    
  }

  function viewProfileSingle() {
    document.location.href='details.php?userID=' + getLastID(document.getElementById('userID').value);
  }
  
  function deleteUser() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_user.php?id=" + document.getElementById('userID').value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>
<?php
if (isset($_GET['search_surname'])) {
  $search_surname = stripslashes($_GET['search_surname']);
} else {
  $search_surname = '';
}

if (isset($_GET['search_username'])) {
  $search_username = $_GET['search_username'];
} else {
  $search_username = '';
}

if (isset($_GET['student_id'])) {
  $search_student_id = $_GET['student_id'];
} else {
  $search_student_id = '';
}
if (isset($username) and $search_surname == '' and $search_username == '' and $search_student_id == '' and !isset($_GET['module']) and !isset($_GET['calendar_year'])) {
  $search_username = $username;
}
?>
<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="search.php" method="get">
<br />

<table cellpadding="0" cellspacing="0" border="0" width="190">
<tr><td>
<div><strong><?php echo $string['genmsg']; ?></strong></div>
<div style="font-size:50%">&nbsp;</div>
<div><?php echo $string['name']; ?><br /><input type="text" name="search_surname" size="18" style="width:95%" value="<?php echo $search_surname; ?>" /></div>

<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%">
<tr><td><?php echo $string['username']; ?></td><td>&nbsp;</td><td><?php echo $string['studentid']; ?></td></tr>
<tr><td><input type="text" name="search_username" size="9" value="<?php echo $search_username; ?>" /></td><td>&nbsp;</td><td><input type="text" name="student_id" size="9" value="<?php echo $search_student_id; ?>" /></td></tr>
</table>
<div><?php echo $string['module']; ?><br /><?php 
search_utils::display_staff_modules_dropdown($userObject, $mysqli); 
?></div>

<div><?php echo $string['academicyear']; ?><br /><select name="calendar_year">
<option value="%"><?php echo $string['anyyear']; ?></option>
<?php
  $result = $mysqli->prepare("SELECT DISTINCT calendar_year FROM modules_student ORDER BY calendar_year DESC");
  $result->execute();
  $result->bind_result($tmp_calendar_year);
  while ($result->fetch()) {
    if ($tmp_calendar_year == $calendar_year) {
      echo "<option value=\"$tmp_calendar_year\" selected>$tmp_calendar_year</option>\n";
    } else {
      echo "<option value=\"$tmp_calendar_year\">$tmp_calendar_year</option>\n";
    }
  }
  $result->close();
?>
</select></div>
<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color:black" onclick="updateMenu(3);"><?php echo $string['advanced']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(3);"><?php
    if (isset($state['advanced']) and $state['advanced'] == 'block') {
      echo "<img id=\"icon3\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon3\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($state['advanced'])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu3\" style=\"display:" . $state['advanced'] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu3\" style=\"display:none\">\n";
  }
  echo '<tr><td>';
  if (isset($state['chkbox1']) and $state['chkbox1'] == 'false') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox1\" name=\"students\" /> <label for=\"chkbox1\">" . $string['students'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox1\" name=\"students\" checked /> <label for=\"chkbox1\">" . $string['students'] . "</label></div>\n";
  }
  if (isset($state['chkbox2']) and $state['chkbox2'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox2\" name=\"graduates\" checked /> <label for=\"chkbox2\">" . $string['graduates'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox2\" name=\"graduates\" /> <label for=\"chkbox2\">" . $string['graduates'] . "</label></div>\n";
  }
  if (isset($state['chkbox3']) and $state['chkbox3'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox3\" name=\"leavers\" checked /> <label for=\"chkbox3\">" . $string['leavers'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox3\" name=\"leavers\" /> <label for=\"chkbox3\">" . $string['leavers'] . "</label></div>\n";
  }
  if (isset($state['chkbox4']) and $state['chkbox4'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox4\" name=\"suspended\" checked /> <label for=\"chkbox4\">" . $string['suspended'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox4\" name=\"suspended\" /> <label for=\"chkbox4\">" . $string['suspended'] . "</label></div>\n";
  }
  //----------------------------
  echo "<hr noshade=\"noshade\" style=\"height:1px; border:none; background-color:#95AEC8; color:#95AEC8\" />\n";
  if (isset($state['chkbox5']) and $state['chkbox5'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox5\" name=\"staff\" checked /> <label for=\"chkbox5\">" . $string['staff'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox5\" name=\"staff\" /> <label for=\"chkbox5\">" . $string['staff'] . "</label></div>\n";
  }
  if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
    if (isset($state['chkbox6']) and $state['chkbox6'] == 'true') {
      echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox6\" name=\"adminstaff\" checked /> <label for=\"chkbox6\">" . $string['staffadmin'] . "</label></div>\n";
    } else {
      echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox6\" name=\"adminstaff\" /> <label for=\"chkbox6\">" . $string['staffadmin'] . "</label></div>\n";
    }
  }
  if (isset($state['chkbox7']) and $state['chkbox7'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox7\" name=\"inactive\" checked /> <label for=\"chkbox7\">" . $string['inactivestaff'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox7\" name=\"inactive\" /> <label for=\"chkbox7\">" . $string['inactivestaff'] . "</label></div>\n";
  }
  if (isset($state['chkbox8']) and $state['chkbox8'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox8\" name=\"externals\" checked /> <label for=\"chkbox8\">" . $string['externalexaminers'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox8\" name=\"externals\" /> <label for=\"chkbox8\">" . $string['externalexaminers'] . "</label></div>\n";
  }
  if (isset($state['chkbox9']) and $state['chkbox9'] == 'true') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox9\" name=\"invigilators\" checked /> <label for=\"chkbox9\">" . $string['invigilators'] . "</label></div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"chkbox9\" name=\"invigilators\" /> <label for=\"chkbox9\">" . $string['invigilators'] . "</label></div>\n";
  }
?>
</td></tr>
</table>

<br />

<div style="text-align:center">
<?php
  if (isset($_GET['module'])) {
    $module = $_GET['module'];
  } else {
    $module = '';
  }

  if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
  } else {
    $folder = '';
  }

  if (isset($_GET['paperID'])) {
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'' . $configObject->get('cfg_root_path') . '/paper/details.php?paperID=' . $_GET['paperID'] . '&module=' . $module . '&folder=' . $folder . '\'"/>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'' . $configObject->get('cfg_root_path') . '/folder/details.php?module=' . $module . '&folder=' . $folder . '\'"/>';
  } else {
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'' . $configObject->get('cfg_root_path') . '/staff/index.php\'"/>';
  }
?>
&nbsp;<input style="width:90px" type="submit" name="submit" value="<?php echo $string['search']; ?>" /></div>
</td></tr>
</table>

<br />
<br />

<div class="submenuheading"><?php echo $string['currentusertasks']; ?></div>

<table class="sidebar_tbl" id="menu2a">
<tr><td><img src="../artwork/user_file_icon_grey_16.gif" width="16" height="16" alt="User Details" /></td><td class="greymenuitem"><?php echo $string['viewuserfile']; ?></td></tr>
<?php
  if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<tr><td class="ico"><a href="create_new_user.php"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="' . $string['createnewuser'] . '" /></td><td class="menuitem"><a href="create_new_user.php">' . $string['createnewuser'] . '</a></td></tr>';
    if ($userObject->has_role('SysAdmin')) {
      echo '<tr><td class="ico"><img src="../artwork/delete_user_16_grey.png" width="16" height="16" alt="' . $string['deleteuser'] . '" /></a></td><td class="greymenuitem">' . $string['deleteuser'] . '</td></tr>';
    }
  }
?>
</table>

<table class="sidebar_tbl" style="display:none" id="menu2b">
<tr><td class="ico"><a href="#" onclick="viewProfileSingle(); return false;"><img src="../artwork/user_file_icon_16.gif" width="16" height="16" alt="<?php echo $string['viewuserfile']; ?>" /></a></td><td class="menuitem"><a id="edit" onclick="viewProfileSingle(); return false;" href="#"><?php echo $string['viewuserfile']; ?></a></td></tr>
<?php
  if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<tr><td class="ico"><img src="../artwork/small_user_icon_grey.gif" width="16" height="16" alt="' . $string['createnewuser'] . '" /></td><td class="greymenuitem">' . $string['createnewuser'] . '</td></tr>';
  }
?>
</table>

<table class="sidebar_tbl" style="display:none" id="menu2c">
<tr><td class="ico"><a onclick="viewProfileSingle(); return false;" href="#"><img src="../artwork/user_file_icon_16.gif" width="16" height="16" alt="<?php echo $string['viewuserfile']; ?>" /></a></td><td class="menuitem"><a id="edit" onclick="viewProfileSingle(); return false;" href="#"><?php echo $string['viewuserfile']; ?></a></td></tr>
<?php
  if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<tr><td class="ico"><a href="create_new_user.php"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="' . $string['createnewuser'] . '" /></a></td><td class="menuitem"><a href="create_new_user.php">' . $string['createnewuser'] . '</a></td></tr>';
  }
  if ($userObject->has_role('SysAdmin')) {
    echo '<tr><td class="ico"><a href="#" onclick="deleteUser(); return false;"><img src="../artwork/delete_user_16.png" width="16" height="16" alt="' . $string['deleteuser'] . '" /></a></td><td class="menuitem"><a href="#" onclick="deleteUser(); return false;">' . $string['deleteuser'] . '</a></td></tr>';
  }
?>
</table>

<br />
<?php
if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
  echo "<div class=\"submenuheading\">{$string['cohorttasks']}</div>\n";

  echo "<table class=\"sidebar_tbl\">\n";
  echo '<tr><td class="ico"><a href="import_users.php"><img src="../artwork/upload_16.gif" width="16" height="16" alt="' . $string['importusers'] . '" /></a></td><td class="menuitem"><a href="import_users.php">' . $string['importusers'] . '</a></td></tr>';
  echo '<tr><td class="ico"><a href="import_modules.php"><img src="../artwork/upload_16.gif" width="16" height="16" alt="' . $string['importmodules'] . '" /></a></td><td class="menuitem"><a href="import_modules.php">' . $string['importmodules'] . '</a></td></tr>';
  echo "</table>\n";
}
?>

<input type="hidden" id="userID" name="userID" value="" />
</form>
</div>
