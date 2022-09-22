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
 * @author Simon Wilkinson, Joseph Baxter
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>
<div id="left-sidebar" class="sidebar">

<br />

<div id="menu1a">
    <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/information_icon_grey.gif" alt="<?php echo $string['displaydetails'] ?>" /><?php echo $string['displaydetails'] ?></div>
</div>

<div style="display:none" id="menu1b">
    <div id="sys_errors_menu" class="menuitem"><a href="#"><img class="sidebar_icon" src="../artwork/information_icon.gif" alt="<?php echo $string['displaydetails'] ?>" /><?php echo $string['displaydetails'] ?></a></div>
</div>

<form name="errorsMenu" autocomplete="off">
<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
