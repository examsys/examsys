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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

?>
<script>
  $(document).ready(function() {
      $('#sms').click(function() {
        $('#sms').append('<img src="../artwork/working.gif" class="busyicon" />');
     });
  });
  function addFaculty() {
    facultywin=window.open("add_faculty.php","faculties","width=450,height=220,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }

  function editFaculty() {
    facultywin=window.open("edit_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=450,height=220,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }
  
  function deleteFaculty() {
    notice=window.open("../delete/check_delete_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=450,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-225,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">
<br />

<div id="menu1a">
	<div class="menuitem"><a href="#" onclick="addFaculty(); return false;"><img class="sidebar_icon" src="../artwork/faculty_16.png" alt="" /><?php echo $string['createfaculty'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="" /><?php echo $string['editfaculty'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletefaculty'] ?></div>
</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"><a href="#" onclick="addFaculty(); return false;"><img class="sidebar_icon" src="../artwork/faculty_16.png" alt="" /><?php echo $string['createfaculty'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="editFaculty(); return false;"><img class="sidebar_icon" onclick="editFaculty()" src="../artwork/edit.png" alt="" /><?php echo $string['editfaculty'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="deleteFaculty(); return false;"><img class="sidebar_icon" onclick="deleteCourse()" src="../artwork/red_cross.png" alt="" /><?php echo $string['deletefaculty'] ?></a></div>
</div>


<?php
if ($userObject->has_role(array('SysAdmin'))) {
    $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
    foreach($smsplugin_name as $name) {
        $smspluginns = 'plugins\SMS\\' . $name . '\\' . $name;
        $smsplugin = new $smspluginns($userObject->get_user_ID());
        $facultyimport = $smsplugin->supports_faculty_import();
        if ($facultyimport !== false) {
?>
<div id="menu1b">
    <div id="sms" class="menuitem">
        <a class="help_tip" title="<?php echo $facultyimport['tooltip'] ?>" href="<?php echo $facultyimport['url'] ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $facultyimport['blurb'] ?></a>
    </div>
</div>
<?php
        }
    }
}
?>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>
