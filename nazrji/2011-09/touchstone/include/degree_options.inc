<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

?>
<script language="JavaScript">
  function editDegree() {
    document.location.href='./edit_degree.php?degreeID=' + document.getElementById('degID').value;
  }

  function deleteDegree() {
    notice=window.open("../delete/check_delete_degree.php?degreeID=" + document.getElementById('degID').value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><a href="add_degree.php"><img src="../artwork/degree_icon_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_degree.php">Create new Degree</a></td></tr>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem">Edit Degree</td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem">Delete Degree</td></tr>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><a href="add_degree.php"><img src="../artwork/degree_icon_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_module.php">Create new Degree</a></td></tr>
<tr><td><img onclick="editDegree()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="editDegree(); return false;">Edit Degree</a></td></tr>
<tr><td><img onclick="deleteDegree()" src="../artwork/red_cross.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="deleteDegree(); return false;">Delete Degree</a></td></tr>
</table>
</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="id" id="degID" value="" />
<input type="hidden" name="oldID" value="" />
</form>
</div>
