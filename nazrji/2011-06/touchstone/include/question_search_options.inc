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
* Sidebar menu with question search options.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
?>
<script language="JavaScript">
  function checkForm() {
    if (searchoptions.searchterm.value == "") {
      alert ("Please enter a search term or phrase.");
      return false;
    }
  }
  
  function updateCookies(ID) {
    if (document.getElementById('checkbox' + ID).checked == 1) {
      setting = " checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    if (ID == 6) {
      NameOfCookie = "myquestions";
    } else {
      NameOfCookie = "questionsearch[" + ID + "]";
    }
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function updateCookieOwner(mySel,NameOfCookie) {
    setting = mySel.options[mySel.selectedIndex].value;
    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function editQuestion() {
    document.location.href='../question/edit/' + document.PapersMenu.qType.value + '.php?q_id=' + document.PapersMenu.questionID.value + '&paperID=find:<?php if(isset($_GET['paperID'])) echo $_GET['paperID']; ?>&calling=find';
  }

  function deleteQuestion() {
    notice=window.open("../delete/check_delete_q_original.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("../question/info.php?q_id=" + document.PapersMenu.questionID.value + "","question_info","width=500,height=500,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("../question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=500,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("../question/add/add_question_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","linktopaper","width=500,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src') == '$protocol" . $_SERVER['HTTP_HOST'] . "/touchstone/artwork/down_arrow_icon.gif') ? '$protocol" . $_SERVER['HTTP_HOST'] . "/touchstone/artwork/up_arrow_icon.gif' : '$protocol" . $_SERVER['HTTP_HOST'] . "/touchstone/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "questionsearch[" + ID + "]";
    document.cookie = NameOfCookie + "=" + current +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }
</script>

</script>

<div id="left-sidebar" class="sidebar">
<form id="PapersMenu" name="PapersMenu"  action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return checkForm()">

<br />

<table cellpadding="4" cellspacing="0" border="0" width="185">
<tr><td style="margin:4px">
<div style="font-size:110%"><strong>Word or phrase</strong><br /><input type="text" name="searchterm" size="25" style="width:98%" value="<?php if (isset($_POST['searchterm'])) echo $_POST['searchterm']; ?>" /></div>
<div style="font-size:110%"><strong>Question Type</strong><br /><select name="searchtype">
<option value="%">(all types)</option>
<?php
  $qTypes = array('calculation'=>'Calculation','dichotomous'=>'Dichotomous','extmatch'=>'Extended Matching','blank'=>'Fill-in-the-Blank','flash'=>'Flash Interface','hotspot'=>'Image Hotspot','info'=>'Information Block','labelling'=>'Labelling','likert'=>'Likert Scale','matrix'=>'Matrix','mcq'=>'Multiple Choice','mrq'=>'Multiple Response','rank'=>'Ranking','sct'=>'Script Concordance','textbox'=>'Textbox','timedate'=>'Time/Date');
  foreach ($qTypes as $tmp_type=>$tmp_title) {
    if (isset($_POST['searchtype']) and $_POST['searchtype'] == $tmp_type) {
      echo "<option value=\"$tmp_type\" selected>$tmp_title</option>\n";
    } else {
      echo "<option value=\"$tmp_type\">$tmp_title</option>\n";
    }
  }
?>
</select></div>
<?php
  if (isset($_COOKIE['questionsearch'][10])) {
    echo "<input type=\"checkbox\" id=\"checkbox10\" onclick=\"updateCookies(10);\" name=\"locked\" value=\"1\"" . $_COOKIE['questionsearch'][10] . " /> include Locked questions\n";
  } else {
    echo "<input type=\"checkbox\" id=\"checkbox10\" onclick=\"updateCookies(10);\" name=\"locked\" value=\"1\" /> include Locked questions\n";
  }
?>
<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color:black" onclick="updateMenu(8);">Question Sections</a></td>
  <td align="right"><a href="#" onclick="updateMenu(8);"><?php
    if (isset($_COOKIE['questionsearch'][8]) and $_COOKIE['questionsearch'][8] == 'block') {
      echo "<img id=\"icon8\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon8\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['questionsearch'][8])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu8\" style=\"display:" . $_COOKIE['questionsearch'][8] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu8\" style=\"display:none\">\n";
  }
  echo '<tr><td>';
  if (isset($_COOKIE['questionsearch'][1])) {
    echo "<div><input type=\"checkbox\" id=\"checkbox1\" onclick=\"updateCookies(1);\" name=\"theme\"" . $_COOKIE['questionsearch'][1] . " /> Theme/Heading</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox1\" onclick=\"updateCookies(1);\" name=\"theme\" checked /> Theme</div>\n";
  }
  if (isset($_COOKIE['questionsearch'][2])) {
    echo "<div><input type=\"checkbox\" id=\"checkbox2\" onclick=\"updateCookies(2);\" name=\"scenario\"" . $_COOKIE['questionsearch'][2] . " /> Scenario</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox2\" onclick=\"updateCookies(2);\" name=\"scenario\" checked /> Scenario</div>\n";
  }
  if (isset($_COOKIE['questionsearch'][3])) {
    echo "<div><input type=\"checkbox\" id=\"checkbox3\" onclick=\"updateCookies(3);\" name=\"leadin\"" . $_COOKIE['questionsearch'][3] . " /> Lead-in</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox3\" onclick=\"updateCookies(3);\" name=\"leadin\" checked /> Lead-in</div>\n";
  }
  if (isset($_COOKIE['questionsearch'][4])) {
    echo "<div><input type=\"checkbox\" id=\"checkbox4\" onclick=\"updateCookies(4);\" name=\"options\"" . $_COOKIE['questionsearch'][4] . " /> Options</div>\n";
  } else {
    echo "<div><input type=\"checkbox\" id=\"checkbox4\" onclick=\"updateCookies(4);\" name=\"options\" checked /> Options</div>\n";
  }
?>
</td></tr>
</table>

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-size:110%; font-weight:bold; color:black" onclick="updateMenu(6);">When was it modified?</a></td>
  <td align="right"><a href="#" onclick="updateMenu(6);"><?php
    if (isset($_COOKIE['questionsearch'][6]) and $_COOKIE['questionsearch'][6] == 'block') {
      echo '<img id="icon6" src="../artwork/up_arrow_icon.gif" width="9" height="9" alt="Hide" border="0" />';
    } else {
      echo '<img id="icon6" src="../artwork/down_arrow_icon.gif" width="9" height="9" alt="Show" border="0" />';
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['questionsearch'][6])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:" . $_COOKIE['questionsearch'][6] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:none\">\n";
  }
  
  $date_options = array("dont remember"=>"Don't remember","week"=>"Within the last week","month"=>"Past month","year"=>"Within the past year","specify"=>"Specify dates");
  foreach($date_options as $date_value=>$date_label) {
    if ((isset($_POST['question_date']) and $_POST['question_date'] == $date_value) or (!isset($_POST['question_date']) and $date_value == 'dont remember')) {
      echo "<tr><td colspan=\"2\"><input type=\"radio\" name=\"question_date\" value=\"$date_value\" checked />&nbsp;$date_label</td></tr>";
    } else {
      echo "<tr><td colspan=\"2\"><input type=\"radio\" name=\"question_date\" value=\"$date_value\" />&nbsp;$date_label</td></tr>";
    }
  }
?>

<tr><td style="text-align:right">from&nbsp;</td><td>
<?php
  echo "<select name=\"fday\">\n";
  for ($i=1; $i<=31; $i++) {
    if ($i < 10) {
      echo "<option value=\"0$i\">0$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select><select name=\"fmonth\">\n";
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      echo "<option value=\"0$i\">0$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
  echo "</select><select name=\"fyear\">\n";
  for ($i=1999; $i<=date("Y"); $i++) {
    echo "<option value=\"$i\">$i</option>\n";
  }
?>
</select>
</td></tr>
<tr><td style="text-align:right">to&nbsp;</td><td>
<?php
  echo "<select name=\"tday\">\n";
  for ($i=1; $i<=31; $i++) {
    if ($i < 10) {
      if ($i == date("d")) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == date("d")) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  echo "</select><select name=\"tmonth\">\n";
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      if ($i == date("m")) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == date("m")) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  echo "</select><select name=\"tyear\">\n";
  for ($i=1999; $i<=date("Y"); $i++) {
    if ($i == date("Y")) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
?>
</select>
</td></tr>
<tr><td>&nbsp;</td></tr>
</table>

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color: black" onclick="updateMenu(9);">Metadata</a></td>
  <td align="right"><a href="#" onclick="updateMenu(9);"><?php
    if (isset($_COOKIE['questionsearch'][9]) and $_COOKIE['questionsearch'][9] == 'block') {
      echo "<img id=\"icon9\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon9\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['questionsearch'][9])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:" . $_COOKIE['questionsearch'][9] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:none\">\n";
  }
?>
  <tr><td>
  <div style="margin-left:4px">Module: <?php SearchUtils::displayTeamDropdown($teams, $userroles, $userID, $mysqli); ?></div>
  <div style="margin-left:4px">Owner: <?php SearchUtils::displayOwnersDropdown($teams, $userroles, $userID, $mysqli, 'questions'); ?></div>
  <div style="margin-left:4px">Status:<br /><?php SearchUtils::displayStatusDropdown(); ?></div>
  <div style="margin-left:4px">Blooms:<br /><?php SearchUtils::displayBloomsDropdown(); ?></div>
  
  <div style="margin-left:4px">Keyword: <select style="width:175px" name="keywordID"><br />
  <option value=""></option>
<?php
  $old_moduleID = '';
  $stmt = $mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
  $stmt->execute();
  $stmt->bind_result($moduleID, $keyword, $keywordID);
  while ($stmt->fetch()) {
    if ($old_moduleID != $moduleID) {
	  if ($old_moduleID != '') echo "</optgroup>\n";
	  echo "<optgroup label=\"$moduleID\">\n";
	}
    if (isset($_POST['keywordID']) and $_POST['keywordID'] == $keywordID) {
      echo "<option value=\"$keywordID\" selected>$keyword</option>\n";
    } else {
      echo "<option value=\"$keywordID\">$keyword</option>\n";
    }
	$old_moduleID = $moduleID;
  }
  if ($stmt->num_rows > 0) echo "</optgroup>\n";
  $stmt->close();
  
  echo "<optgroup label=\"Personal Keywords\">\n";
  $stmt = $mysqli->prepare("SELECT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->bind_result($keywordID, $keyword);
  while ($stmt->fetch()) {
    if (isset($_POST['keywordID']) and $_POST['keywordID'] == $keywordID) {
      echo "<option value=\"$keywordID\" selected>$keyword</option>\n";
    } else {
      echo "<option value=\"$keywordID\">$keyword</option>\n";
    }
  }
  $stmt->close();
  echo "</optgroup>\n";
?>
</select></div>
</td></tr>
</table>

<br />
<br />

<div style="text-align:center">
<?php
  if (isset($_GET['paperID'])) {
    echo '<input style="width:80px" type="button" name="back" value="Back" onclick="window.location=\'/touchstone/paper/details.php?paperID=' . $_GET['paperID'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<input style="width:80px" type="button" name="back" value="Back" onclick="window.location=\'/touchstone/folder/details.php?module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } else {
    echo '<input style="width:80px" type="button" name="back" value="Back" onclick="window.location=\'/touchstone/index.php\'"/>';
  }
?>
&nbsp;<input style="width:80px" type="submit" name="submit" value="Search" /></div>
</td></tr>
</table>

<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Current Question Tasks</td></tr>
<table cellpadding="0" cellspacing="0" border="0" style="margin-left:6px; font-size:90%">
<tr><td>

<table style="font-size:100%; line-height:160%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem">Edit Question</td></tr>
<tr><td><img src="../artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem">Information</td></tr>
<tr><td><img src="../artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem">Copy onto Paper X...</td></tr>
<tr><td><img src="../artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem">Link to paper</td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Delete" /></td><td class="greymenuitem">Delete Question</td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="../artwork/edit.png" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="../artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="../artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="../artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;">Link to paper</a></td></tr>
<tr><td><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img src="../artwork/red_cross.png" width="16" height="16" alt="Delete" border="0" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;">Delete Question</a></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="../artwork/edit.png" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="../artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="../artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="../artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;">Link to paper</a></td></tr>
<tr><td><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Delete" border="0" /></td><td class="greymenuitem">Delete Question</td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="questionID" value="" />
<input type="hidden" name="pID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="screenNo" value="" />
<input type="hidden" name="oldQuestionID" value="" />

</form>
</div>
