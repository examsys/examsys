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


?>
<script language="JavaScript">
  function editLTIkeys() {
    window.location.href = './edit_LTIkeys.php?LTIkeysid=' + document.getElementById('divID').value;
  }

  function deleteLTIkeys() {
    notice = window.open("../delete/check_delete_LTIkeys.php?LTIkeysID=" + document.getElementById('divID').value + "", "LTIkeyss", "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
  <form name="myform">
    <br/>


    <table style="margin-left:9px; font-size:90%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
      <tr>
        <td class="ico"><a href="add_LTIkeys.php" target="body"><img src="../artwork/ims_16.png" width="16" height="16"
                                                                     alt="<?php echo $string['createLTIkeys']; ?>"
                                                                     border="0"/></a></td>
        <td class="menuitem"><a href="add_LTIkeys.php"><?php echo $string['createLTIkeys']; ?></a></td>
      </tr>
      <tr>
        <td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16"
                             alt="<?php echo $string['editLTIkeys']; ?>" border="0"/></td>
        <td class="greymenuitem"><?php echo $string['editLTIkeys']; ?></td>
      </tr>
      <tr>
        <td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="" border="0"/></td>
        <td class="greymenuitem"><?php echo $string['deleteLTIkeys']; ?></td>
      </tr>
    </table>

    <table style="margin-left:9px; font-size:90%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
      <tr>
        <td class="ico"><a href="add_LTIkeys.php" target="body"><img src="../artwork/ims_16.png" width="16" height="16"
                                                                     alt="<?php echo $string['createLTIkeys']; ?>"
                                                                     border="0"/></a></td>
        <td class="menuitem"><a href="add_LTIkeys.php"><?php echo $string['createLTIkeys']; ?></a></td>
      </tr>
      <tr>
        <td class="ico"><img onclick="editLTIkeys()" src="../artwork/edit.png" width="16" height="16"
                             alt="<?php echo $string['editLTIkeys']; ?>" border="0"/></td>
        <td class="menuitem"><a href="" onclick="editLTIkeys(); return false;"><?php echo $string['editLTIkeys']; ?></a>
        </td>
      </tr>
      <tr>
        <td class="ico"><img onclick="deleteLTIkeys()" src="../artwork/red_cross.png" width="16" height="16" alt=""
                             border="0"/></td>
        <td class="menuitem"><a href=""
                                onclick="deleteLTIkeys(); return false;"><?php echo $string['deleteLTIkeys']; ?></a>
        </td>
      </tr>
    </table>
    </td></tr>
    </table>

    <input type="hidden" id="divID" name="divID" value=""/>
  </form>
</div>
