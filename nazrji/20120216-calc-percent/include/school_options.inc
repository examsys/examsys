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

?>
<script language="JavaScript">
  function editSchool() {
    window.location.href='./edit_school.php?schoolid=' + document.getElementById('divID').value;
  }
  
  function deleteSchool() {
    notice=window.open("../delete/check_delete_school.php?schoolID=" + document.getElementById('divID').value + "","schools","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />


<table style="margin-left:9px; font-size:90%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<?php
if ($faculties > 0) {
?>
  <tr><td class="ico"><a href="add_school.php" target="body"><img src="../artwork/school_icon_16.png" width="16" height="16" alt="<?php echo $string['createschool']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_school.php"><?php echo $string['createschool']; ?></a></td></tr>
<?php
} else {
?>
  <tr><td class="ico"><img src="../artwork/school_icon_16_grey.png" width="16" height="16" alt="<?php echo $string['createschool']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['createschool']; ?></td></tr>
<?php
}
?>

<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editschool']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['editschool']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['deleteschool']; ?></td></tr>
</table>

<table style="margin-left:9px; font-size:90%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td class="ico"><a href="add_school.php" target="body"><img src="../artwork/school_icon_16.png" width="16" height="16" alt="<?php echo $string['createschool']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_school.php"><?php echo $string['createschool']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editSchool()" src="../artwork/edit.png" width="16" height="16" alt="<?php echo $string['editschool']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="editSchool(); return false;"><?php echo $string['editschool']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteSchool()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="deleteSchool(); return false;"><?php echo $string['deleteschool']; ?></a></td></tr>
</table>
</td></tr>
</table>

<input type="hidden" id="divID" name="divID" value="" />
</form>
</div>
