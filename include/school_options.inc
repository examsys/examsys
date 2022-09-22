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
    <?php
    if ($faculties > 0) {
        ?>
        <div class="menuitem"><a href="add_school.php"><img class="sidebar_icon" src="../artwork/school_icon_16.png" alt="<?php echo $string['createschool'] ?>" /><?php echo $string['createschool'] ?></a></div>
        <?php
    } else {
        ?>
        <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/school_icon_16_grey.png" alt="<?php echo $string['createschool'] ?>" /><?php echo $string['createschool'] ?></div>
        <?php
    }
    ?>

    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editschool']; ?>" /><?php echo $string['editschool'] ?></div>
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" width="16" height="16" alt="" /><?php echo $string['deleteschool'] ?></div>
</div>

<div style="display:none" id="menu1b">
    <div class="menuitem"><a href="add_school.php"><img class="sidebar_icon" src="../artwork/school_icon_16.png" alt="<?php echo $string['createschool']; ?>" /><?php echo $string['createschool'] ?></a></div>
    <div class="menuitem editschool"><a href="#"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editschool'] ?>" /><?php echo $string['editschool'] ?></a></div>
    <div class="menuitem deleteschool"><a href="#"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><?php echo $string['deleteschool'] ?></a></div>
</div>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>
