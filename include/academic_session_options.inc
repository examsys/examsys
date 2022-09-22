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
 * Options menu bar for adacemic session admin screen.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

?>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">
<div id="menu1a">

    <div class="menuitem"><a href="add_academic_session.php"><img class="sidebar_icon" src="../artwork/add_sessions_16.png" alt="<?php echo $string['createsession'] ?>" /><?php echo $string['createsession'] ?></a></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editsession']; ?>" /><?php echo $string['editsession'] ?></div>
        <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" width="16" height="16" alt="" /><?php echo $string['deletesession'] ?></div>

</div>

<div style="display:none" id="menu1b">
    <div class="menuitem"><a href="add_academic_session.php"><img class="sidebar_icon" src="../artwork/add_sessions_16.png" alt="<?php echo $string['createsession']; ?>" /><?php echo $string['createsession'] ?></a></div>
    <div class="menuitem editsession"><a href="#"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editsession'] ?>" /><?php echo $string['editsession'] ?></a></div>
        <div class="menuitem deletesession"><a href="#"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><?php echo $string['deletesession'] ?></a></div>
</div>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>


