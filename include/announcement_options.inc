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
<form name="myform" autocomplete="off">
<div id="menu1a">
    <div class="menuitem"><a href="add_announcement.php"><img class="sidebar_icon" src="../artwork/megaphone_16.png" alt="<?php echo $string['createannouncement'] ?>" /><?php echo $string['createannouncement'] ?></a></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="<?php echo $string['editannouncement'] ?>" /><?php echo $string['editannouncement'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="<?php echo $string['deleteannouncement'] ?>" /><?php echo $string['deleteannouncement'] ?></div>
</div>

<div style="display:none" id="menu1b">
    <div class="menuitem"><a href="add_announcement.php"><img class="sidebar_icon" src="../artwork/megaphone_16.png" alt="<?php echo $string['createannouncement'] ?>" /><?php echo $string['createannouncement'] ?></a></div>
    <div class="menuitem"><a href="#" id="edit"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editannouncement'] ?>" /><?php echo $string['editannouncement'] ?></a></div>
    <div class="menuitem"><a href="#" id="delete"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="<?php echo $string['deleteannouncement'] ?>" /><?php echo $string['deleteannouncement'] ?></a></div>
</div>

<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
