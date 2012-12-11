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
  function editTemplate() {
    document.location.href='<?php echo $configObject->get('cfg_root_path') ?>/admin/edit_ebel_grid.php?id=' + document.getElementById('lineID').value;
  }
  
  function deleteTemplate() {
    notice=window.open("../delete/check_delete_ebel_template.php?gridID=" + document.getElementById('lineID').value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
<tr><td class="ico"><a href="" onclick="addFaculty(); return false;"><img src="../artwork/grid_16.gif" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="add_ebel_grid.php"><?php echo $string['createnewgrid']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="" /></td><td class="greymenuitem"><?php echo $string['editgrid']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" /></td><td class="greymenuitem"><?php echo $string['deletegrid']; ?></td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu1b">
<tr><td class="ico"><a href="" onclick="addTemplate(); return false;"><img src="../artwork/grid_16.gif" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="add_ebel_grid.php"><?php echo $string['createnewgrid']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editTemplate()" src="../artwork/edit.png" width="16" height="16" alt="" /></td><td class="menuitem"><a href="" onclick="editTemplate(); return false;"><?php echo $string['editgrid']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteTemplate()" src="../artwork/red_cross.png" width="16" height="16" alt="" /></td><td class="menuitem"><a href="" onclick="deleteTemplate(); return false;"><?php echo $string['deletegrid']; ?></a></td></tr>
</table>

<input type="hidden" name="LineID" id="lineID" value="" />
</form>
</div>
