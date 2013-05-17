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

require_once '../classes/dateutils.class.php';

// Calculate what the current academic session is.
$session = date_utils::get_current_academic_year();
?>
<script language="JavaScript">
  function editModule() {
    window.location.href='./edit_module.php?moduleid=' + document.getElementById('lineID').value;
  }

  function jumpToModule() {
    window.location = '<?php echo $configObject->get('cfg_root_path'); ?>/folder/details.php?module=' + $('#lineID').val();
  }

  function studentCohort() {
    window.location = '<?php echo $configObject->get('cfg_root_path') ?>/users/search.php?search_surname=&search_username=&student_id=&module=' + $('#lineID').val() + '&calendar_year=<?php echo $session; ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
  }

  function deleteModule() {
    notice=window.open("../delete/check_delete_module.php?idMod=" + document.getElementById('lineID').value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
<tr><td class="ico"><a href="add_module.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td class="ico"><a href="bulk_import_modules.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="bulk_import_modules.php"><?php echo $string['bulkmoduleimport']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" /></td><td class="greymenuitem"><?php echo $string['editmodule']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="<?php echo $string['deletemodule']; ?>" /></td><td class="greymenuitem"><?php echo $string['deletemodule']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" /></td><td class="greymenuitem"><?php echo $string['modulefolder']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" /></td><td class="greymenuitem"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu1b">
<tr><td class="ico"><a href="add_module.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td class="ico"><a href="bulk_import_modules.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="" /></a></td><td class="menuitem"><a href="bulk_import_modules.php"><?php echo $string['bulkmoduleimport']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editModule()" src="../artwork/edit.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" /></td><td class="menuitem"><a href="" onclick="editModule(); return false;"><?php echo $string['editmodule']; ?></a></td></tr>
<tr><td class="ico"><img onclick="deleteModule()" src="../artwork/red_cross.png" width="16" height="16" alt="<?php echo $string['deletemodule']; ?>" /></td><td class="menuitem"><a href="" onclick="deleteModule(); return false;"><?php echo $string['deletemodule']; ?></a></td></tr>
<tr><td class="ico"><img onclick="jumpToModule()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" /></td><td class="menuitem"><a href="" onclick="jumpToModule(); return false;"><?php echo $string['modulefolder']; ?></a></td></tr>
<tr><td class="ico"><img onclick="studentCohort()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" /></td><td class="menuitem"><a href="" onclick="studentCohort(); return false;"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</a></td></tr>
</table>
</td></tr>
</table>

<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
