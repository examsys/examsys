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

<div class="submenuheading" id="papertasks"><?php echo $string['examtasks'] ?></div>

<div class="menuitem"><a href="../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="" /><?php echo $string['jumptopaper'] ?></a></div>
<div class="menuitem"><a href="#" id="edit"><img class="sidebar_icon" src="../artwork/properties_icon.gif" alt="" /></td><td class="menuitem"><?php echo $string['editproperties'] ?></a></div>
<div class="menuitem"><a href="#" id="convert"><img class="sidebar_icon" src="../artwork/formative_16.gif" alt="" /></td><td class="menuitem"><?php echo $string['converttoformative'] ?></a></div>
<div class="menuitem"><a href="mailto:<?php echo $email . '?subject=Scheduling of ' . $paper_title . '&body=Dear ' . $title . ' ' . $surname . ','; ?>"><img class="sidebar_icon" src="../artwork/small_email.png" alt="" /><?php echo $string['Email'] . ' ' . $title . ' ' . $surname; ?></a></div>

<input type="hidden" id="divID" name="divID" value="" />
</form>
</div>
