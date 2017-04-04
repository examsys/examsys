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
  function editLTIkeys() {
    window.location.href = './edit_LTIkeys.php?LTIkeysid=' + $('#lineID').val();
  }

  function deleteLTIkeys() {
    notice = window.open("../delete/check_delete_LTIkeys.php?LTIkeysID=" + $('#lineID').val() + "", "LTIkeyss", "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
    if (window.focus) {
      notice.focus();
    }
  }

  function searchUserLinks() {
    window.location.href = './search_users.php?LTIkeysid=' + $('#lineID').val();
  }
</script>

<div id="left-sidebar" class="sidebar">
  <form name="myform" autocomplete="off">
    <br/>

		<div id="menu1a">
			<div class="menuitem"><a href="add_LTIkeys.php"><img class="sidebar_icon" src="../artwork/lti_key_16.png" alt="<?php echo $string['createLTIkeys'] ?>" /><?php echo $string['createLTIkeys'] ?></a></div>
			<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="<?php echo $string['editLTIkeys'] ?>" /><?php echo $string['editLTIkeys'] ?></div>
			<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="<?php echo $string['deleteLTIkeys'] ?>" /><?php echo $string['deleteLTIkeys'] ?></div>
      <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/link_grey.png" alt="<?php echo $string['searchuserlinks'] ?>" /><?php echo $string['searchuserlinks'] ?></div>
		</div>

		<div style="display:none" id="menu1b">
			<div class="menuitem"><a href="add_LTIkeys.php"><img class="sidebar_icon" src="../artwork/lti_key_16.png" alt="<?php echo $string['createLTIkeys'] ?>" /><?php echo $string['createLTIkeys'] ?></a></div>
			<div class="menuitem"><a href="#" onclick="editLTIkeys(); return false;"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editLTIkeys'] ?>" /><?php echo $string['editLTIkeys'] ?></a></div>
			<div class="menuitem"><a href="#" onclick="deleteLTIkeys(); return false;"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="<?php echo $string['deleteLTIkeys'] ?>" /><?php echo $string['deleteLTIkeys'] ?></a></div>
      <div class="menuitem"><a href="#" onclick="searchUserLinks(); return false;"><img class="sidebar_icon" src="../artwork/clear_lti_link.png" alt="<?php echo $string['searchuserlinks'] ?>" /><?php echo $string['searchuserlinks'] ?></a></div>
		</div>

    <input type="hidden" id="lineID" name="lineID" value=""/>
  </form>
</div>
