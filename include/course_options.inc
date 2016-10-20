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
  function editCourse() {
    document.location.href='./edit_course.php?courseID=' + $('#lineID').val();
  }

  function deleteCourse() {
    notice=window.open("../delete/check_delete_course.php?courseID=" + $('#lineID').val() + "","notice","width=450,height=210,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210, screen.height/2-105);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">
<div id="menu1a">
	<div class="menuitem"><a href="add_course.php"><img class="sidebar_icon" src="../artwork/degree_icon_16.png" alt="" /><?php echo $string['createcourse'] ?></a></div>
	<div class="menuitem"><a href="bulk_import_courses.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['bulkcourseimport'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="" /><?php echo $string['editcourse'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletecourse'] ?></div>
</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"><a href="add_course.php"><img class="sidebar_icon" src="../artwork/degree_icon_16.png" alt="" /><?php echo $string['createcourse'] ?></a></div>
	<div class="menuitem"><a href="bulk_import_courses.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['bulkcourseimport'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="editCourse(); return false;"><img class="sidebar_icon" src="../artwork/edit.png" alt="" /><?php echo $string['editcourse'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="deleteCourse(); return false;"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><?php echo $string['deletecourse'] ?></a></div>
</div>

<?php
if ($userObject->has_role(array('SysAdmin'))) {
    $smsplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
    foreach($smsplugin_name as $name) {
        $smspluginns = 'plugins\SMS\\' . $name . '\\' . $name;
        $smsplugin = new $smspluginns($mysqli, $userObject->get_user_ID());
        $courseimport = $smsplugin->supports_course_import() ;
        if ($courseimport !== false) {
?>
<div id="menu1b">
    <div id="sms" class="menuitem">
        <a class="help_tip" title="<?php echo $courseimport['tooltip'] ?>" href="<?php echo $courseimport['url'] ?>"><img class="sidebar_icon" src="../artwork/sync_16.png" alt="" /><?php echo $courseimport['blurb'] ?></a>
    </div>
</div>
<?php
        }
    }
}
?>


<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
