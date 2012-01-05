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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';

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
<script language="JavaScript">
  function updateCookies(ID) {
    if (document.getElementById('checkbox' + ID).checked == 1) {
      setting = "checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "caausermgt[" + ID + "]";
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src') == '$protocol" . $_SERVER['HTTP_HOST'] . "/artwork/down_arrow_icon.gif') ? '$protocol" . $_SERVER['HTTP_HOST'] . "/artwork/up_arrow_icon.gif' : '$protocol" . $_SERVER['HTTP_HOST'] . "/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "caausermgt[" + ID + "]";
    document.cookie = NameOfCookie + "=" + current +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function viewProfileSingle() {
    document.location.href='details.php?userID=' + document.PapersMenu.userID.value;
  }

  function emailCohort() {
    var winwidth = 700;
    var winheight = 550;
    templatewin = window.open("email_cohort.php?surname=" + document.PapersMenu.tmp_surname.value + "&degreeID=" + document.PapersMenu.tmp_degreeID.value + "&yearID=" + document.PapersMenu.tmp_yearID.value + "","emailwin","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    templatewin.moveTo(screen.width/2-350,screen.height/2-275);
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="search.php" method="get">
<br />

<table cellpadding="0" cellspacing="0" border="0" width="190">
<tr><td>
<div><strong><?php echo $string['genmsg']; ?></strong></div>
<div style="font-size:50%">&nbsp;</div>
<div><?php echo $string['name']; ?><br /><input type="text" name="search_surname" size="18" style="width:95%" value="<?php if (isset($_GET['search_surname'])) echo stripslashes($_GET['search_surname']); ?>" /></div>

<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%">
<tr><td><?php echo $string['username']; ?></td><td>&nbsp;</td><td><?php echo $string['studentid']; ?></td></tr>
<tr><td><input type="text" name="search_username" size="9" value="<?php if (isset($_GET['search_username'])) echo $_GET['search_username']; ?>" /></td><td>&nbsp;</td><td><input type="text" name="student_id" size="9" value="<?php if (isset($_GET['student_id'])) echo $_GET['student_id']; ?>" /></td></tr>
</table>
<div><?php echo $string['module']; ?><br /><?php SearchUtils::displayTeamDropdown($teams, $userroles, $userID, $mysqli); ?></div>

<div><?php echo $string['academicyear']; ?><br /><select name="calendar_year">
<option value="%"><?php echo $string['anyyear']; ?></option>
<?php
  $year_details = $mysqli->query("SELECT DISTINCT calendar_year FROM student_modules ORDER BY calendar_year DESC");
  while ($year_row = $year_details->fetch_assoc()) {
    if ($year_row['calendar_year'] == $calendar_year) {
      echo "<option value=\"" . $year_row['calendar_year'] . "\" selected>" . $year_row['calendar_year'] . "</option>\n";
    } else {
      echo "<option value=\"" . $year_row['calendar_year'] . "\">" . $year_row['calendar_year'] . "</option>\n";
    }
  }
?>
</select></div>
<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color:black" onclick="updateMenu(3);"><?php echo $string['advanced']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(3);"><?php
    if (isset($_COOKIE['caausermgt'][3]) and $_COOKIE['caausermgt'][3] == 'block') {
      echo "<img id=\"icon3\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon3\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['caausermgt'][3])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu3\" style=\"display:" . $_COOKIE['caausermgt'][3] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu3\" style=\"display:none\">\n";
  }
  echo '<tr><td>';
  if (isset($_COOKIE['caausermgt'][4]) and $_COOKIE['caausermgt'][4] != 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox4\" onclick=\"updateCookies(4);\" name=\"students\" /> " . $string['students'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox4\" onclick=\"updateCookies(4);\" name=\"students\" checked /> " . $string['students'] . "</div>\n";
  }
  if (isset($_COOKIE['caausermgt'][7]) and $_COOKIE['caausermgt'][7] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox7\" onclick=\"updateCookies(7);\" name=\"graduates\" checked /> " . $string['graduates'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox7\" onclick=\"updateCookies(7);\" name=\"graduates\" /> " . $string['graduates'] . "</div>\n";
  }
  if (isset($_COOKIE['caausermgt'][8]) and $_COOKIE['caausermgt'][8] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox8\" onclick=\"updateCookies(8);\" name=\"leavers\" checked /> " . $string['leavers'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox8\" onclick=\"updateCookies(8);\" name=\"leavers\" /> " . $string['leavers'] . "</div>\n";
  }
  if (isset($_COOKIE['caausermgt'][10]) and $_COOKIE['caausermgt'][10] == 'Suspended') {
    echo "<div><input type=\"checkbox\" id=\"checkbox10\" onclick=\"updateCookies(10);\" name=\"suspended\" checked /> " . $string['suspended'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox10\" onclick=\"updateCookies(10);\" name=\"suspended\" /> " . $string['suspended'] . "</div>\n";
  }
  //----------------------------
  echo "<hr noshade=\"noshade\" style=\"height:1px; border:none; background-color:#95AEC8; color:#95AEC8\" />\n";
  if (isset($_COOKIE['caausermgt'][5]) and $_COOKIE['caausermgt'][5] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox5\" onclick=\"updateCookies(5);\" name=\"staff\" checked /> " . $string['staff'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox5\" onclick=\"updateCookies(5);\" name=\"staff\" /> " . $string['staff'] . "</div>\n";
  }
  if (strpos($userroles,'Admin') !== false) {
    if (isset($_COOKIE['caausermgt'][12]) and $_COOKIE['caausermgt'][12] == 'checked') {
      echo "<div><input type=\"checkbox\" id=\"checkbox12\" onclick=\"updateCookies(12);\" name=\"adminstaff\" checked /> " . $string['staffadmin'] . "</div>\n";
    } else {
      echo "<div><input type=\"checkbox\" id=\"checkbox12\" onclick=\"updateCookies(12);\" name=\"adminstaff\" /> " . $string['staffadmin'] . "</div>\n";
    }
  }
  if (isset($_COOKIE['caausermgt'][9]) and $_COOKIE['caausermgt'][9] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox9\" onclick=\"updateCookies(9);\" name=\"inactive\" checked /> " . $string['inactivestaff'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox9\" onclick=\"updateCookies(9);\" name=\"inactive\" /> " . $string['inactivestaff'] . "</div>\n";
  }
  if (isset($_COOKIE['caausermgt'][6]) and $_COOKIE['caausermgt'][6] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox6\" onclick=\"updateCookies(6);\" name=\"externals\" checked /> " . $string['externalexaminers'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox6\" onclick=\"updateCookies(6);\" name=\"externals\" /> " . $string['externalexaminers'] . "</div>\n";
  }
  if (isset($_COOKIE['caausermgt'][11]) and $_COOKIE['caausermgt'][11] == 'checked') {
    echo "<div><input type=\"checkbox\" id=\"checkbox11\" onclick=\"updateCookies(11);\" name=\"invigilators\" checked /> " . $string['invigilators'] . "</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox11\" onclick=\"updateCookies(11);\" name=\"invigilators\" /> " . $string['invigilators'] . "</div>\n";
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
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="parent.window.location=\'/paper/details.php?paperID=' . $_GET['paperID'] . '&module=' . $module . '&folder=' . $folder . '\'"/>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="parent.window.location=\'/folder/details.php?module=' . $module . '&folder=' . $folder . '\'"/>';
  } else {
    echo '<input style="width:90px" type="button" name="back" value="' . $string['back'] . '" onclick="parent.window.location=\'/staff/index.php\'"/>';
  }
?>
&nbsp;<input style="width:90px" type="submit" name="submit" onclick="document.PapersMenu.oldUserID.value='';" value="<?php echo $string['search']; ?>" /></div>
</td></tr>
</table>

<br />
<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px">
<tr><td style="font-weight:bold"><?php echo $string['currentusertasks']; ?></td></tr>
<table cellpadding="0" cellspacing="0" border="0" id="menu1" style="display:block; margin-left:6px; font-size:90%">
<tr><td>

<table style="font-size:100%; line-height:160%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="../artwork/user_file_icon_grey_16.gif" width="16" height="16" alt="User Details" /></td><td class="greymenuitem"><?php echo $string['viewuserfile']; ?></td></tr>
<?php
  if (strpos($userroles,'Admin') !== false or strpos($userroles,'SysAdmin') !== false) {
    echo '<tr><td><a href="create_new_user.php" target="body"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="Create User" border="0" /></td><td class="menuitem"><a href="create_new_user.php">' . $string['createnewuser'] . '</a></td></tr>';
  } else {
    echo '<tr><td><img src="../artwork/small_user_icon_grey.gif" width="16" height="16" alt="Create User" /></td><td class="greymenuitem">' . $string['createnewuser'] . '</td></tr>';
  }
?>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td><a href="#" onclick="viewProfileSingle(); return false;"><img src="../artwork/user_file_icon_16.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="viewProfileSingle(); return false;" href="#"><?php echo $string['viewuserfile']; ?></a></td></tr>
<tr><td><img src="../artwork/small_user_icon_grey.gif" width="16" height="16" alt="Create" border="0" /></td><td class="greymenuitem"><?php echo $string['createnewuser']; ?></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td><a onclick="viewProfileSingle(); return false;" href="#"><img src="../artwork/user_file_icon_16.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="viewProfileSingle(); return false;" href="#"><?php echo $string['viewuserfile']; ?></a></td></tr>
<tr><td><a href="create_new_user.php" target="body"><img src="../artwork/small_user_icon.gif" width="16" height="16" alt="Create" border="0" /></a></td><td class="menuitem"><a href="create_new_user.php"><?php echo $string['createnewuser']; ?></a></td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px">
<tr><td style="font-weight:bold"><?php echo $string['cohorttasks']; ?></td></tr>
<?php
  if (isset($_COOKIE['caausermgt'][2])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu2\" style=\"display:" . $_COOKIE['caausermgt'][2] . "; margin-left:6px; font-size:90%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu2\" style=\"display:block; margin-left:6px; font-size:90%\">\n";
  }
?>
<tr><td>


<table style="font-size:100%; line-height: 160%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu3a">
<?php
  if (strpos($userroles,'Admin') !== false) {
    echo '<tr><td><a href="load_cohort.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importstudents'] . '" border="0" /></a></td><td class="menuitem"><a href="import_students.php">' . $string['importstudents'] . '</a></td></tr>';
    echo '<tr><td><a href="import_student_module_info.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importmodules'] . '" border="0" /></a></td><td class="menuitem"><a href="import_modules.php">' . $string['importmodules'] . '</a></td></tr>';
  } else {
    echo '<tr><td><img src="../artwork/import_grey_16.gif" width="16" height="16" alt="' . $string['importstudents'] . '" /></td><td class="greymenuitem">' . $string['importstudents'] . '</td></tr>';
  }
?>
<tr><td><img src="../artwork/small_email_grey.png" width="16" height="16" alt="<?php echo $string['emailcohort']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['emailcohort']; ?></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu3b">
<?php
  if (strpos($userroles,'Admin') !== false) {
    echo '<tr><td><a href="load_cohort.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importstudents'] . '" border="0" /></a></td><td class="menuitem"><a href="import_students.php">' . $string['importstudents'] . '</a></td></tr>';
    echo '<tr><td><a href="import_student_module_info.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importmodules'] . '" border="0" /></a></td><td class="menuitem"><a href="import_student_module_info.php">' . $string['importmodules'] . '</a></td></tr>';
  }
?>
<tr><td><a id="copy" href="#" onclick="emailCohort(); return false;"><img src="../artwork/small_email.png" width="16" height="16" alt="<?php echo $string['emailcohort']; ?>" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="emailCohort(); return false;"><?php echo $string['emailcohort']; ?></a></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu3c">
<?php
  if (strpos($userroles,'Admin') !== false) {
    echo '<tr><td><a href="load_cohort.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importstudents'] . '" border="0" /></a></td><td class="menuitem"><a href="import_students.php">' . $string['importstudents'] . '</a></td></tr>';
    echo '<tr><td><a href="import_student_module_info.php"><img src="../artwork/import_16.gif" width="16" height="16" alt="' . $string['importmodules'] . '" border="0" /></a></td><td class="menuitem"><a href="import_modules.php">' . $string['importmodules'] . '</a></td></tr>';
  }
?>
<tr><td><a id="copy" href="#" onclick="emailCohort(); return false;"><img src="../artwork/small_email.png" width="16" height="16" alt="<?php echo $string['emailcohort']; ?>" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="emailCohort(); return false;"><?php echo $string['emailcohort']; ?></a></td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="userID" value="" />
<input type="hidden" name="email" value="" />
<input type="hidden" name="oldUserID" value="" />
<input type="hidden" name="tmp_surname" value="" />
<input type="hidden" name="tmp_degreeID" value="" />
<input type="hidden" name="tmp_yearID" value="" />
</form>
</div>
