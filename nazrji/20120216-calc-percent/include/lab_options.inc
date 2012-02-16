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
  function viewDetails() {
    document.location.href='./lab_details.php?labID=' + document.getElementById('labID').value;  
    document.getElementById('menu1a').style.display = 'block';
    document.getElementById('menu1b').style.display = 'none';
  }
  
  function deleteLab() {
    notice=window.open("../delete/check_delete_lab.php?labID=" + document.getElementById('labID').value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }

  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="labform">
<br />

<table cellpadding="2" cellspacing="0" border="0" id="menu1" style="font-size:90%">
<tr><td>

<table style="display:block" cellpadding="4" cellspacing="0" border="0" id="menu1a">
<tr><td><a href="create_lab.php" target="body"><img src="../artwork/new_lab_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="create_lab.php"><?php echo $string['createnewlab']; ?></a></td></tr>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['vieweditdetails']; ?></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['deletelab']; ?></td></tr>
</table>

<table style="display:none" cellpadding="4" cellspacing="0" border="0" id="menu1b">
<tr><td><a href="create_lab.php" target="body"><img src="../artwork/new_lab_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="create_lab.php"><?php echo $string['createnewlab']; ?></a></td></tr>
<tr><td><img onclick="viewDetails()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="viewDetails(); return false;"><?php echo $string['vieweditdetails']; ?></a></td></tr>
<tr><td><img onclick="deleteLab()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="deleteLab(); return false;"><?php echo $string['deletelab']; ?></a></td></tr>
</table>
</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="labID" id="labID" value="" />
<input type="hidden" name="labNo" id="labNo" value="" />
<input type="hidden" name="oldLabNo" id="oldLabNo" value="" />
</form>
</div>

</body>
</html>