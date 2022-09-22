<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * The sidebar menu of the user management section.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'errors.php';
$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState($configObject->get('cfg_root_path') . '/users/search.php');
$calendar_year = check_var('calendar_year', 'GET', false, false, true);

?>

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
if (isset($username) and $search_surname == '' and $search_username == '' and $search_student_id == '' and !isset($_GET['module']) and is_null($calendar_year)) {
    $search_username = $username;
}
?>
<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="search.php" method="get" autocomplete="off">
<br />

<table cellpadding="0" cellspacing="0" border="0" style="width:210px; font-size:110%">
<tr><td>
<div><strong><?php echo $string['genmsg'] ?></strong></div>
<div style="font-size:50%">&nbsp;</div>
<div><?php echo $string['name'] ?><br /><input type="text" name="search_surname" size="18" style="width:95%" value="<?php echo $search_surname ?>" /></div>

<table cellpadding="0" cellspacing="0" border="0">
<tr><td style="padding-left:0; padding-right:10px"><?php echo $string['username']; ?></td><td><?php echo $string['studentid'] ?></td></tr>
<tr><td style="padding-left:0; padding-right:10px"><input type="text" name="search_username" size="10" value="<?php echo $search_username ?>" /></td><td><input type="text" id="student_id" name="student_id" size="10" value="<?php echo $search_student_id ?>"/></td></tr>
</table>
<div><?php echo $string['module'] ?><br /><?php
search_utils::display_staff_modules_dropdown($userObject, $string, $mysqli);
?></div>

<div><?php echo $string['academicyear'] ?><br /><select name="calendar_year">
<option value="%"><?php echo $string['anyyear'] ?></option>
<?php
  $yearutils = new yearutils($mysqli);
echo $yearutils->get_calendar_year_dropdown_options(2, $calendar_year, $string, $yearutils::USERS);
?>
</select></div>
<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr id="advancedmenu" data-menuid="3"><td><a href="#" style="font-weight:bold; color:black"><?php echo $string['advanced'] ?></a></td>
  <td style="text-align:right"><a href="#"><?php
    if (isset($state['advanced']) and $state['advanced'] == 'block') {
        echo '<img id="icon3" src="../artwork/up_arrow_icon.gif" width="10" height="9" alt="Hide" />';
    } else {
        echo '<img id="icon3" src="../artwork/down_arrow_icon.gif" width="10" height="9" alt="Show" />';
    }
    ?></a></td></tr>
  </table>

<?php if (isset($state['advanced']) and $state['advanced'] == 'block') :
    ?>
  <div id="menu3" style="margin-left:15px; width:180px; display: <?= $state['advanced'] ?>">
    <?php
else :
    ?>
  <div id="menu3" style="margin-left:15px; width:180px; display:none">
    <?php
endif; ?>

<?php
if (isset($state['chkbox1']) and $state['chkbox1'] == 'false') {
    echo '<div><input class="chk" type="checkbox" id="chkbox1" name="students" /><label for="chkbox1">' . $string['students'] . "</label></div>\n";
} else {
    echo '<div><input class="chk" type="checkbox" id="chkbox1" name="students" checked /><label for="chkbox1">' . $string['students'] . "</label></div>\n";
}
if (isset($state['chkbox2']) and $state['chkbox2'] == 'true') {
    echo '<div><input class="chk" type="checkbox" id="chkbox2" name="graduates" checked /><label for="chkbox2">' . $string['graduates'] . "</label></div>\n";
} else {
    echo '<div><input class="chk" type="checkbox" id="chkbox2" name="graduates" /><label for="chkbox2">' . $string['graduates'] . "</label></div>\n";
}
if (isset($state['chkbox3']) and $state['chkbox3'] == 'true') {
    echo '<div><input class="chk" type="checkbox" id="chkbox3" name="leavers" checked /><label for="chkbox3">' . $string['leavers'] . "</label></div>\n";
} else {
    echo '<div><input class="chk" type="checkbox" id="chkbox3" name="leavers" /><label for="chkbox3">' . $string['leavers'] . "</label></div>\n";
}
if (isset($state['chkbox4']) and $state['chkbox4'] == 'true') {
    echo '<div><input class="chk" type="checkbox" id="chkbox4" name="suspended" checked /><label for="chkbox4">' . $string['suspended'] . "</label></div>\n";
} else {
    echo '<div><input class="chk" type="checkbox" id="chkbox4" name="suspended" /><label for="chkbox4">' . $string['suspended'] . "</label></div>\n";
}
if (isset($state['chkbox12']) and $state['chkbox12'] == 'true') {
    echo '<div><input class="chk" type="checkbox" id="chkbox12" name="locked" checked /><label for="chkbox12">' . $string['locked'] . "</label></div>\n";
} else {
    echo '<div><input class="chk" type="checkbox" id="chkbox12" name="locked" /><label for="chkbox12">' . $string['locked'] . "</label></div>\n";
}
  //----------------------------
  echo "<hr noshade=\"noshade\" style=\"height:1px; border:none; background-color:#808080; color:#808080\" />\n";
if (isset($state['chkbox5']) and $state['chkbox5'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox5" name="staff" checked /><label for="chkbox5">' . $string['staff'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox5" name="staff" /><label for="chkbox5">' . $string['staff'] . "</label></div>\n";
}
if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
    if (isset($state['chkbox6']) and $state['chkbox6'] == 'true') {
        echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox6" name="adminstaff" checked /><label for="chkbox6">' . $string['staffadmin'] . "</label></div>\n";
    } else {
        echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox6" name="adminstaff" /><label for="chkbox6">' . $string['staffadmin'] . "</label></div>\n";
    }
}
if ($userObject->has_role('SysAdmin')) {
    if (isset($state['chkbox10']) and $state['chkbox10'] == 'true') {
        echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox10" name="sysadminstaff" checked /><label for="chkbox10">' . $string['staffsysadmin'] . "</label></div>\n";
    } else {
        echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox10" name="sysadminstaff" /><label for="chkbox10">' . $string['staffsysadmin'] . "</label></div>\n";
    }
}
if (isset($state['chkbox11']) and $state['chkbox11'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox11" name="standardsstaff" checked /><label for="chkbox11">' . $string['staffstandardssetter'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox11" name="standardsstaff" /><label for="chkbox11">' . $string['staffstandardssetter'] . "</label></div>\n";
}
if (isset($state['chkbox7']) and $state['chkbox7'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox7" name="inactive" checked /><label for="chkbox7">' . $string['inactivestaff'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox7" name="inactive" /><label for="chkbox7">' . $string['inactivestaff'] . "</label></div>\n";
}
if (isset($state['chkbox8']) and $state['chkbox8'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox8" name="externals" checked /><label for="chkbox8">' . $string['externalexaminers'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox8" name="externals" /><label for="chkbox8">' . $string['externalexaminers'] . "</label></div>\n";
}
if (isset($state['chkbox13']) and $state['chkbox13'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox13" name="internals" checked /><label for="chkbox13">' . $string['internalreviewers'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox13" name="internals" /><label for="chkbox13">' . $string['internalreviewers'] . "</label></div>\n";
}
if (isset($state['chkbox9']) and $state['chkbox9'] == 'true') {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox9" name="invigilators" checked /><label for="chkbox9">' . $string['invigilators'] . "</label></div>\n";
} else {
    echo '<div><input class="chk chkstaff" type="checkbox" id="chkbox9" name="invigilators" /><label for="chkbox9">' . $string['invigilators'] . "</label></div>\n";
}
?>
</div>

<br />

<div style="text-align:center">
    <?= ucfirst($string['show']) ?>
    <select name="limit">
        <option>100</option>
        <option selected>1000</option>
        <option>10000</option>
    </select>
    <?= mb_strtolower($string['results']) ?>
</div>

<br />

<div style="text-align:center"><input class="ok" type="submit" name="submit" value="<?php echo $string['search'] ?>" /></div>
</td></tr>
</table>

<br />
<br />

<div class="submenuheading"><?php echo $string['usertasks'] ?></div>

<div id="menu2a">
<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/user_file_icon_grey_16.gif" alt="<?php echo $string['viewuserfile'] ?>" /><?php echo $string['viewuserfile'] ?></div>
<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/report_grey_16.png" alt="<?php echo $string['performsummary'] ?>" /><?php echo $string['performsummary'] ?></div>
<?php
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<div class="menuitem"><a href="create_new_user.php"><img class="sidebar_icon" src="../artwork/small_user_icon.gif" alt="' . $string['createnewuser'] . '" />' . $string['createnewuser'] . '</a></div>';
    if ($userObject->has_role('SysAdmin')) {
        echo '<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="' . $string['deleteuser'] . '" />' . $string['deleteuser'] . '</div>';
    }
    echo '<div class="menuitem"><a href="import_users.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importusers'] . '" />' . $string['importusers'] . '</a></div>';
    echo '<div class="menuitem"><a href="import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importmodules'] . '" />' . $string['importmodules'] . '</a></div>';
}
?>
</div>

<div id="menu2b">
<div class="menuitem viewprofile"><img class="sidebar_icon" src="../artwork/user_file_icon_16.gif" alt="<?php echo $string['viewuserfile'] ?>" /><?php echo $string['viewuserfile'] ?></div>
<div class="menuitem" id="performancesummary2b"><img class="sidebar_icon" src="../artwork/report_16.png" alt="<?php echo $string['performsummary'] ?>" /><?php echo $string['performsummary'] ?></div>
<?php
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/small_user_icon_grey.gif" alt="' . $string['createnewuser'] . '" />' . $string['createnewuser'] . '</div>';
    echo '<div class="menuitem"><a href="import_users.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importusers'] . '" />' . $string['importusers'] . '</a></div>';
    echo '<div class="menuitem"><a href="import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importmodules'] . '" />' . $string['importmodules'] . '</a></div>';
}
?>
</div>

<div id="menu2c">
<div class="menuitem viewprofile"><img class="sidebar_icon" src="../artwork/user_file_icon_16.gif" alt="<?php echo $string['viewuserfile'] ?>" /><?php echo $string['viewuserfile'] ?></div>
<div class="menuitem" id="performancesummary2c"><img class="sidebar_icon" src="../artwork/report_16.png" alt="<?php echo $string['performsummary'] ?>" /><?php echo $string['performsummary'] ?></div>
<?php
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo '<div class="menuitem"><a href="create_new_user.php"><img class="sidebar_icon" src="../artwork/small_user_icon.gif" alt="' . $string['createnewuser'] . '" />' . $string['createnewuser'] . '</a></div>';
}
if ($userObject->has_role('SysAdmin')) {
    echo '<div class="menuitem" id="deleteuser"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="' . $string['deleteuser'] . '" /><a href="#">' . $string['deleteuser'] . '</a></div>';
}
if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
    echo '<div class="menuitem"><a href="import_users.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importusers'] . '" />' . $string['importusers'] . '</a></div>';
    echo '<div class="menuitem"><a href="import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="' . $string['importmodules'] . '" />' . $string['importmodules'] . '</a></div>';
}
?>
</div>

<input type="hidden" id="userID" name="userID" value="" />
<input type="hidden" id="roles" name="roles" value="<?php if (isset($user_details['roles'])) {
    echo $user_details['roles'];
                                                    } ?>" />
</form>
</div>
