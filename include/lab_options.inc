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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>

<div id="left-sidebar" class="sidebar">
<form name="labform" autocomplete="off">
<div id="menu1a">
    <div class="menuitem"><a href="add_lab.php"><img class="sidebar_icon" src="../artwork/new_lab_16.png" alt="" /><?php echo $string['createnewlab'] ?></a></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="" /><?php echo $string['vieweditdetails'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletelab'] ?></div>
    <div class="menuitem"><a href="campus/list_campuses.php"><img class="sidebar_icon" src="../artwork/new_campus_16.png" alt="" /><?php echo $string['listcampuses'] ?></a></div>
</div>

<div style="display:none" id="menu1b">
    <div class="menuitem"><a href="add_lab.php"><img class="sidebar_icon" src="../artwork/new_lab_16.png" alt="" /><?php echo $string['createnewlab'] ?></a></div>
    <div class="menuitem" id="viewlab"><img class="sidebar_icon" src="../artwork/edit.png" alt="" /><a href="" onclick="return false;"><?php echo $string['vieweditdetails'] ?></a></div>
    <div class="menuitem" id="deletelab"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><a href="" onclick="return false;"><?php echo $string['deletelab'] ?></a></div>
    <div class="menuitem"><a href="campus/list_campuses.php"><img class="sidebar_icon" src="../artwork/new_campus_16.png" alt="" /><?php echo $string['listcampuses'] ?></a></div>
</div>

<input type="hidden" name="labID" id="labID" value="" />
<input type="hidden" name="labNo" id="labNo" value="" />
<input type="hidden" name="oldLabNo" id="oldLabNo" value="" />
</form>
</div>
