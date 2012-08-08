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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../classes/dateutils.class.php';

// Calculate what the current academic session is.
$session = date_utils::get_current_academic_year();
?>
<script language="JavaScript">
  function editModule() {
    window.location.href='./edit_module.php?moduleid=' + document.getElementById('moduleID').value;
  }

  function jumpToModule() {
    window.location = '<?php echo $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path; ?>/folder/details.php?module=' + document.getElementById('moduleID').value;
  }
  
  function studentCohort() {
    window.location = '<?php echo $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path; ?>/users/search.php?search_surname=&search_username=&student_id=&team=' + $('#moduleID').val() + '&calendar_year=<?php echo $session; ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
  }
  
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table style="margin-left:4px; font-size:90%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td class="ico"><a href="add_module.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td class="ico"><a href="bulk_import_modules.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="bulk_import_modules.php"><?php echo $string['bulkmoduleimport']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['editmodule']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['modulefolder']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</td></tr>
</table>

<table style="margin-left:4px; font-size:90%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td class="ico"><a href="add_module.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td class="ico"><a href="bulk_import_modules.php"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="bulk_import_modules.php"><?php echo $string['bulkmoduleimport']; ?></a></td></tr>
<tr><td class="ico"><img onclick="editModule()" src="../artwork/edit.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="editModule(); return false;"><?php echo $string['editmodule']; ?></a></td></tr>
<tr><td class="ico"><img onclick="jumpToModule()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="jumpToModule(); return false;"><?php echo $string['modulefolder']; ?></a></td></tr>
<tr><td class="ico"><img onclick="studentCohort()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="studentCohort(); return false;"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</a></td></tr>
</table>
</td></tr>
</table>

<input type="hidden" name="moduleID" id="moduleID" value="" />
<input type="hidden" name="divID" value="" />
</form>
</div>
