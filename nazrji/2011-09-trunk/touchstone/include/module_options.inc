<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once '../classes/dateutils.class.php';

// Calculate what the current academic session is.
$session = DateUtils::get_current_academic_year();
?>
<script language="JavaScript">
  function editModule() {
    window.location.href='./edit_module.php?moduleid=' + document.getElementById('moduleID').value;
  }

  function jumpToModule() {
    window.location = '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/touchstone/folder/details.php?module=' + document.myform.moduleID.value;
  }
  
  function studentCohort() {
    window.location = '<?php echo $protocol . $_SERVER['HTTP_HOST']; ?>/touchstone/users/search.php?search_surname=&search_username=&student_id=&team=' + document.myform.moduleID.value + '&calendar_year=<?php echo $session; ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_degreeID=&tmp_yearID=';
  }
  
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><a href="add_module.php" target="body"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['editmodule']; ?></td></tr>
<tr><td><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['modulefolder']; ?></td></tr>
<tr><td><img src="../artwork/link_grey.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" border="0" /></td><td class="greymenuitem"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</td></tr>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><a href="add_module.php" target="body"><img src="../artwork/module_icon_16.png" width="16" height="16" alt="<?php echo $string['createmodule']; ?>" border="0" /></a></td><td class="menuitem"><a href="add_module.php"><?php echo $string['createmodule']; ?></a></td></tr>
<tr><td><img onclick="editModule()" src="../artwork/edit.png" width="16" height="16" alt="<?php echo $string['editmodule']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="editModule(); return false;"><?php echo $string['editmodule']; ?></a></td></tr>
<tr><td><img onclick="jumpToModule()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['modulefolder']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="jumpToModule(); return false;"><?php echo $string['modulefolder']; ?></a></td></tr>
<tr><td><img onclick="studentCohort()" src="../artwork/link.png" width="16" height="16" alt="<?php echo $string['studentcohort']; ?>" border="0" /></td><td class="menuitem"><a href="" onclick="studentCohort(); return false;"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</a></td></tr>
</table>
</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="moduleID" id="moduleID" value="" />
<input type="hidden" name="divID" value="" />
</form>
</div>
