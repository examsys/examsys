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
* Sidebar menu with question search options.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
require_once '../classes/stateutils.class.php';

?>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/state.js"></script>
<script language="JavaScript">
  function getLastID(IDs) {
    var id_list = IDs.split(",");
    last_elm = id_list.length - 1;
    
    return id_list[last_elm];
  }

  function checkForm() {
    if (searchoptions.searchterm.value == "") {
      alert ("<?php echo $string['entersearchterm'];?>");
      return false;
    }
  }
  
   function editQ() {
    var loc = '../question/edit/index.php?q_id=' + getLastID(document.PapersMenu.questionID.value) + '&paperID=find:<?php if(isset($_GET['paperID'])) echo $_GET['paperID']; ?>&calling=find';
    if (document.PapersMenu.qType.value == 'random' || document.PapersMenu.qType.value == 'keyword_based') {
      loc += '&type=' + document.PapersMenu.qType.value;
    }
    document.location = loc;
  }

  function deleteQuestion() {
    notice=window.open("../delete/check_delete_q_original.php?q_id=" + document.PapersMenu.questionID.value + "&divID=" + document.PapersMenu.oldQuestionID.value + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("../question/info.php?q_id=" + getLastID(document.PapersMenu.questionID.value) + "","question_info","width=700,height=600,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("../question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("../question/link_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","linktopaper","width=600,height=" + (screen.height-50) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,10);
    if (window.focus) {
      notice.focus();
    }
  }
  
  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src') == '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif') ? '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/up_arrow_icon.gif' : '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);
    
    updateState('menu' + ID, current);
  }

  function updateDropdownState(mySel, NameOfState) {
    setting = mySel.options[mySel.selectedIndex].value;
    updateState(NameOfState, setting);    
  }
  
  function selectDateRadio() {
    document.getElementById('question_date5').checked = true;
  }

</script>

<div id="left-sidebar" class="sidebar">
<form id="PapersMenu" name="PapersMenu"  action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return checkForm()">

<br />

<table cellpadding="4" cellspacing="0" border="0" width="185">
<tr><td style="margin:4px">
<div style="font-size:110%"><strong><?php echo $string['wordorphrase']; ?></strong><br /><input type="text" name="searchterm" size="25" style="width:98%" value="<?php if (isset($_POST['searchterm'])) echo $_POST['searchterm']; ?>" /></div>
<div style="font-size:110%"><strong><?php echo $string['questiontype']; ?></strong><br /><select name="searchtype">
<option value="%"><?php echo $string['alltypes']; ?></option>
<?php
  $qTypes = array('area'=>$string['area'],'calculation'=>$string['calculation'],'dichotomous'=>$string['dichotomous'],'extmatch'=>$string['extmatch'],'blank'=>$string['blank'],'flash'=>$string['flash'],'hotspot'=>$string['hotspot'],'info'=>$string['info'],'labelling'=>$string['labelling'],'likert'=>$string['likert'],'matrix'=>$string['matrix'],'mcq'=>$string['mcq'],'mrq'=>$string['mrq'],'rank'=>$string['rank'],'sct'=>$string['sct'],'textbox'=>$string['textbox'],'true_false'=>$string['true_false']);
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
  if (isset($state['checkbox10']) and $state['checkbox10'] == 'true') {
    echo "<input class=\"chk\" type=\"checkbox\" id=\"checkbox10\" name=\"locked\" value=\"1\" checked=\"checked\" /> " . $string['inclockedquestions'];
  } else {
    echo "<input class=\"chk\" type=\"checkbox\" id=\"checkbox10\" name=\"locked\" value=\"1\" /> " . $string['inclockedquestions'];
  }
?>
<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color:black" onclick="updateMenu(8);"><?php echo $string['questionsections']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(8);"><?php
    if (isset($state['menu8']) and $state['menu8'] == 'block') {
      echo "<img id=\"icon8\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon8\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($state['menu8'])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu8\" style=\"display:" . $state['menu8'] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu8\" style=\"display:none\">\n";
  }
  echo '<tr><td>';
  if (isset($state['checkbox1']) and $state['checkbox1'] == 'false') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox1\" name=\"theme\" /> " . $string['theme'] . "</div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox1\" name=\"theme\" checked=\"checked\" /> " . $string['theme'] . "</div>\n";
  }
  if (isset($state['checkbox2']) and $state['checkbox2'] == 'false') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox2\" name=\"scenario\" /> " . $string['scenario'] . "</div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox2\" name=\"scenario\" checked=\"checked\" /> " . $string['scenario'] . "</div>\n";
  }
  if (isset($state['checkbox3']) and $state['checkbox3'] == 'false') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox3\" name=\"leadin\" /> " . $string['leadin'] . "</div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox3\" name=\"leadin\" checked=\"checked\" /> " . $string['leadin'] . "</div>\n";
  }
  if (isset($state['checkbox4']) and $state['checkbox4'] == 'false') {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox4\" name=\"options\" /> " . $string['options'] . "</div>\n";
  } else {
    echo "<div><input class=\"chk\" type=\"checkbox\" id=\"checkbox4\" name=\"options\" checked=\"checked\" /> " . $string['options'] . "</div>\n";
  }
?>
</td></tr>
</table>

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-size:110%; font-weight:bold; color:black" onclick="updateMenu(6);"><?php echo $string['whenmodified']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(6);"><?php
    if (isset($state['menu6']) and $state['menu6'] == 'block') {
      echo '<img id="icon6" src="../artwork/up_arrow_icon.gif" width="9" height="9" alt="Hide" border="0" />';
    } else {
      echo '<img id="icon6" src="../artwork/down_arrow_icon.gif" width="9" height="9" alt="Show" border="0" />';
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($state['menu6'])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:" . $state['menu6'] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:none\">\n";
  }
  
  $date_options = array("dont remember"=>$string['dont remember'], "week"=>$string['week'], "month"=>$string['month'], "year"=>$string['year'], "specify"=>$string['specify']);
  $i = 1;
  foreach ($date_options as $date_value=>$date_label) {
    if ((isset($_POST['question_date']) and $_POST['question_date'] == $date_value) or (!isset($_POST['question_date']) and $date_value == 'dont remember')) {
      echo "<tr><td colspan=\"2\"><input type=\"radio\" id=\"question_date$i\" name=\"question_date\" value=\"$date_value\" checked />&nbsp;" . $string[$date_value] . "</td></tr>";
    } else {
      echo "<tr><td colspan=\"2\"><input type=\"radio\" id=\"question_date$i\" name=\"question_date\" value=\"$date_value\" />&nbsp;" . $string[$date_value] . "</td></tr>";
    }
    $i++;
  }
?>

<tr><td style="text-align:right"><?php echo $string['from']; ?>&nbsp;</td><td>
<?php
  if (isset($_POST['fday'])) {
    $target = $_POST['fday'];
  } else {
    $target = 0;
  }
  echo "<select name=\"fday\" onchange=\"selectDateRadio()\">\n";
  for ($i=1; $i<=31; $i++) {
    if ($i < 10) {
      if ($i == $target) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == $target) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  if (isset($_POST['fmonth'])) {
    $target = $_POST['fmonth'];
  } else {
    $target = 0;
  }
  echo "</select><select name=\"fmonth\" onchange=\"selectDateRadio()\">\n";
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      if ($i == $target) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == $target) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  echo "</select><select name=\"fyear\" onchange=\"selectDateRadio()\">\n";
  for ($i=1999; $i<=date("Y"); $i++) {
    if (isset($_POST['fyear']) and $_POST['fyear'] == $i) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
?>
</select>
</td></tr>
<tr><td style="text-align:right"><?php echo $string['to']; ?>&nbsp;</td><td>
<?php
  if (isset($_POST['tday'])) {
    $target = $_POST['tday'];
  } else {
    $target = date("d");
  }
  echo "<select name=\"tday\" onchange=\"selectDateRadio()\">\n";
  for ($i=1; $i<=31; $i++) {
    if ($i < 10) {
      if ($i == $target) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == $target) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  if (isset($_POST['tday'])) {
    $target = $_POST['tday'];
  } else {
    $target = date("m");
  }
  echo "</select><select name=\"tmonth\" onchange=\"selectDateRadio()\">\n";
  for ($i=1; $i<=12; $i++) {
    if ($i < 10) {
      if ($i == $target) {
        echo "<option value=\"0$i\" selected>0$i</option>\n";
      } else {
        echo "<option value=\"0$i\">0$i</option>\n";
      }
    } else {
      if ($i == $target) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
  }
  if (isset($_POST['tyear'])) {
    $target = $_POST['tyear'];
  } else {
    $target = date("Y");
  }
  echo "</select><select name=\"tyear\" onchange=\"selectDateRadio()\">\n";
  for ($i=1999; $i<=date("Y"); $i++) {
    if ($i == $target) {
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
  <tr><td><a href="#" style="font-weight:bold; color: black" onclick="updateMenu(9);"><?php echo $string['metadata']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(9);"><?php
    if (isset($state['menu9']) and $state['menu9'] == 'block') {
      echo "<img id=\"icon9\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon9\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($state['menu9'])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:" . $state['menu9'] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:none\">\n";
  }
?>
  <tr><td>
  <div style="margin-left:4px"><?php echo $string['module']; search_utils::display_team_dropdown($teams, $userroles, $userID, $mysqli); ?></div>
  <div style="margin-left:4px"><?php echo $string['owner']; search_utils::display_owners_dropdown($teams, $userroles, $userID, $mysqli, 'questions'); ?></div>
  <div style="margin-left:4px"><br /><?php echo $string['status']; search_utils::display_status_dropdown(); ?></div>
  <div style="margin-left:4px"><br /><?php echo $string['blooms']; search_utils::display_blooms_dropdown(); ?></div>
  
  <div style="margin-left:4px"><?php echo $string['keyword']; ?> <select style="width:175px" name="keywordID"><br />
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


<div style="text-align:center">
<?php
  if (isset($_GET['paperID'])) {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/paper/details.php?paperID=' . $_GET['paperID'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/folder/details.php?module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } else {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/staff/index.php\'"/>';
  }
?>
&nbsp;<input style="width:80px" type="submit" name="submit" value="<?php echo $string['search']; ?>" /></div>
</td></tr>
</table>

<br />
<div class="submenuheading" id="banktasks"><?php echo $string['currentquestiontasks']; ?></div>

<table style="margin-left:12px; font-size:90%; line-height:160%; display:block" cellpadding="3" cellspacing="0" border="0" id="menu2a">
<tr><td class="ico"><img src="../artwork/edit_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem"><?php echo $string['editquestion']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem"><?php echo $string['information']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem"><?php echo $string['copyontopaperx']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem"><?php echo $string['linktopaper']; ?></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Delete" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
</table>

<table style="margin-left:12px; font-size:90%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2b">
<tr><td class="ico"><a id="edit" onclick="editQ(); return false;" href="#"><img src="../artwork/edit.png" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><a id="information" href="#" onclick="questionInfo(); return false;"><img src="../artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="../artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="../artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td class="ico"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img src="../artwork/red_cross.png" width="16" height="16" alt="Delete" border="0" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><?php echo $string['deletequestion']; ?></a></td></tr>
</table>

<table style="margin-left:12px; font-size:90%; line-height:160%; display:none" cellpadding="3" cellspacing="0" border="0" id="menu2c">
<tr><td class="ico"><a id="edit" onclick="editQ(); return false;" href="#"><img src="../artwork/edit.png" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><a id="information" href="#" onclick="questionInfo(); return false;"><img src="../artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="../artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="../artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaper']; ?></a></td></tr>
<tr><td class="ico"><img src="../artwork/red_cross_grey.png" width="16" height="16" alt="Delete" border="0" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
</table>

<input type="hidden" name="questionID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="screenNo" value="" />
<input type="hidden" name="oldQuestionID" value="" />

</form>
</div>
