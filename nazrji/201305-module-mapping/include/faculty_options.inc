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
  function addFaculty() {
    facultywin=window.open("/admin/add_faculty.php","faculties","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }

  function editFaculty() {
    facultywin=window.open("/admin/edit_faculty.php?facultyID=" + document.getElementById('lineID').value + "","faculties","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }
  
  function deleteFaculty() {
    notice=window.open("../delete/check_delete_faculty.php?facultyID=" + document.getElementById('lineID').value + "","faculties","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
<tr><td class="ico"><a href="" onclick="addFaculty(); return false;"><img src="../artwork/faculty_16.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="" onclick="addFaculty(); return false;"><?php echo $string['createfaculty']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="" /></td><td class="greymenuitem"><?php echo $string['editfaculty']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" /></td><td class="greymenuitem"><?php echo $string['deletefaculty']; ?></td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu1b">
<tr><td class="ico"><a href="" onclick="addFaculty(); return false;"><img src="../artwork/faculty_16.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="" onclick="addFaculty(); return false;"><?php echo $string['createfaculty']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editFaculty()" src="../artwork/edit.png" width="16" height="16" alt="" /></td><td class="menuitem"><a href="" onclick="editFaculty(); return false;"><?php echo $string['editfaculty']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteCourse()" src="../artwork/red_cross.png" width="16" height="16" alt="" /></td><td class="menuitem"><a href="" onclick="deleteFaculty(); return false;"><?php echo $string['deletefaculty']; ?></a></td></tr>
</table>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>
