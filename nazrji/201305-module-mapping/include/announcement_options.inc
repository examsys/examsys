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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
?>
<script language="JavaScript">
  function editAnnouncement() {
    window.location.href='./edit_announcement.php?announcementid=' + document.getElementById('lineID').value;
  }
  
  function deleteAnnouncement() {
    notice=window.open("../delete/check_delete_announcement.php?announcementID=" + document.getElementById('lineID').value + "","announcements","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table class="sidebar_tbl" id="menu1a">
<tr><td class="ico"><a href="add_announcement.php"><img src="../artwork/megaphone_16.png" width="16" height="16" alt="<?php echo $string['createannouncement']; ?>" /></a></td><td class="menuitem"><a href="add_announcement.php"><?php echo $string['createannouncement']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editannouncement']; ?>" /></td><td class="greymenuitem"><?php echo $string['editannouncement']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['deleteannouncement']; ?>" /></td><td class="greymenuitem"><?php echo $string['deleteannouncement']; ?></td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu1b">
<tr><td class="ico"><a href="add_announcement.php"><img src="../artwork/megaphone_16.png" width="16" height="16" alt="<?php echo $string['createannouncement']; ?>" /></a></td><td class="menuitem"><a href="add_announcement.php"><?php echo $string['createannouncement']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editAnnouncement()" src="../artwork/edit.png" width="16" height="16" alt="<?php echo $string['editannouncement']; ?>" /></td><td class="menuitem"><a href="" onclick="editAnnouncement(); return false;"><?php echo $string['editannouncement']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteAnnouncement()" src="../artwork/red_cross.png" width="16" height="16" alt="<?php echo $string['deleteannouncement']; ?>" /></td><td class="menuitem"><a href="" onclick="deleteAnnouncement(); return false;"><?php echo $string['deleteannouncement']; ?></a></td></tr>
</table>
</td></tr>
</table>

<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
