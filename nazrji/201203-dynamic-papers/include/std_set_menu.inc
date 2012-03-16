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
<div id="left-sidebar" class="sidebar">
<br />
<script language="JavaScript">
  function deleteReview() {
    notice=window.open("../delete/check_delete_review.php?setterID=" + document.StdSetMenu.setterID.value + "&paperID=<?php echo $_GET['paperID']; ?>&dateID=" + document.StdSetMenu.dateID.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
  function editReview() {
    var methodType = '';
    if (document.StdSetMenu.method.value == 'Modified Angoff') {
      methodType = 'modified_angoff';
    } else {
      methodType = 'ebel';
    }  

    if (groupReview == 'No') {
      window.location.href = "individual_review.php?setterID=" + document.StdSetMenu.setterID.value + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&dateID=" + document.StdSetMenu.dateID.value + "&method=" + methodType;
    } else {
      window.location.href = "group_set_angoff.php?reviewers=" + document.StdSetMenu.setterID.value + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&dateID=" + document.StdSetMenu.dateID.value + "&method=" + methodType;
    }
  }
</script>

<form name="StdSetMenu" action="">
<table cellpadding="0" cellspacing="0" border="0" width="185" style="font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['individualrating']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="3" cellspacing="0" border="0" id="menu1" style="margin-left:7px; font-size:90%">
<tr><td width="16"><a href="#" onclick="launchHelp(97); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td><td class="menuitem"><a href="#" onclick="launchHelp(97); return false;"><?php echo $string['aboutstandardssetting']; ?></a></td></tr>
<tr><td><a href="individual_review.php?paperID=<?php echo $_GET['paperID']; ?>&method=modified_angoff&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>"><img src="../artwork/std_set_icon_16.gif" width="16" height="16" alt="Angoff Method" border="0" /></a></td><td class="menuitem"><a href="individual_review.php?paperID=<?php echo $_GET['paperID']; ?>&method=modified_angoff&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>"><?php echo $string['createmodifiedangoff']; ?></a></td></tr>
<tr><td><a href="individual_review.php?paperID=<?php echo $_GET['paperID']; ?>&method=ebel&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>"><img src="../artwork/std_set_icon_16.gif" width="16" height="16" alt="Ebel Method" border="0" /></a></td><td class="menuitem"><a href="individual_review.php?paperID=<?php echo $_GET['paperID']; ?>&method=ebel&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>"><?php echo $string['createebelmethod']; ?></a></td></tr>
</table>
</td></tr>
</table>

<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['currentrating']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="0" cellspacing="0" border="0" id="menu2" style="font-size:90%">
<tr><td>

<table style="font-size:100%; margin-left:7px; display:block" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="Arrow" /></td><td class="greymenuitem"><?php echo $string['editrating']; ?></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Arrow" /></td><td class="greymenuitem"><?php echo $string['delete']; ?></td></tr>
</table>

<table style="font-size:100%; margin-left:7px; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td><a id="edit" onclick="editReview(); return false;" href="#"><img src="../artwork/edit.png" width="16" height="16" alt="Arrow" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editReview(); return false;" href="#"><?php echo $string['editrating']; ?></a></td></tr>
<tr><td><a id="delete" href="#" onclick="deleteReview(); return false;"><img src="../artwork/red_cross.png" width="16" height="16" alt="Arrow" border="0" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteReview(); return false;"><?php echo $string['delete']; ?></a></td></tr>
</table>

<table style="font-size:100%; margin-left:7px; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="Arrow" /></td><td class="greymenuitem"><?php echo $string['editrating']; ?></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Arrow" /></td><td class="greymenuitem"><?php echo $string['delete']; ?></td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['grouprating']; ?></td></tr>
<tr><td style="margin:4px">
<table cellpadding="3" cellspacing="0" border="0" id="menu3" style="margin-left:7px; font-size:90%">
<?php 
if(isset($no_reviews) and $no_reviews > 1) {
?>
<tr><td><a href="get_group.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&method=modified_angoff"><img src="../artwork/small_users_icon.png" width="16" height="16" alt="Angoff Method" border="0" /></a></td><td class="menuitem"><a href="get_group.php?paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&folder=<?php echo $_GET['folder']; ?>&method=modified_angoff"><?php echo $string['modifiedangoff']; ?></a></td></tr>
<?php } else { ?>
<tr><td><img src="../artwork/small_users_icon_grey.png" width="16" height="16" alt="Angoff Method" border="0" /></td><td class="greymenuitem"><?php echo $string['modifiedangoff']; ?></td></tr>
<?php }?>
</table>
</td></tr>
</table>

<input type="hidden" name="setterID" value="" />
<input type="hidden" name="dateID" value="" />
<input type="hidden" name="reviewID" value="" />
<input type="hidden" name="oldReviewID" value="" />
<input type="hidden" name="method" value="" />
</form>
</div>