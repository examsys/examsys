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
* Task menu for question list.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require $cfg_web_root . 'touchstone/include/sidebar_menu.inc';
?>
<script src="/touchstone/javascript/sidebar.js" type="text/javascript"></script>
<script language="JavaScript">
  function addToPaper(questionID) {
    notice=window.open("add_question_to_paper.php?q_id="+questionID+"","properties","width=420,height=500,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-250);
  }

  function updateCookies() {
    if (document.getElementById('myquestions').checked == 1) {
      setting = " checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "myquestions";
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());

    window.location='<?php echo "list.php?type=$type"; if (isset($_GET['team'])) echo "&team=" . $_GET['team']; if (isset($_GET['keyword'])) echo "&keyword=" . $_GET['keyword']; ?>';
  }
  
  function selQ(questionID, lineID, qType, menuID) {
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
    document.getElementById('menu2a').style.display = 'none';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    document.getElementById(menuID).style.display = 'block';

    document.PapersMenu.questionID.value = questionID;
    document.PapersMenu.qType.value = qType;
    
    document.getElementById(lineID).style.backgroundColor = '#B3C8E8';
    document.PapersMenu.oldQuestionID.value = lineID;
  }
  
  function editQ() {
    top.location.href='/touchstone/question/edit/' + document.PapersMenu.qType.value + '.php?q_id=' + document.PapersMenu.questionID.value + '&paper=list:' + document.PapersMenu.qType.value + '&calling=list';
  }

  function deleteQuestion() {
    notice=window.open("/touchstone/delete/check_delete_q_original.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("/touchstone/question/info.php?q_id=" + document.PapersMenu.questionID.value + "","question_info","width=500,height=500,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("/touchstone/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=500,height=" + (screen.height-100) + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,10);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("/touchstone/question/add/add_question_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","properties","width=420,height=500,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }
  function lon(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
    }
  }

  function loff(lineID) {
    if (lineID != document.PapersMenu.oldQuestionID.value) {
      document.getElementById(lineID).style.backgroundColor = '';
    }
  }

  function qOff() {
    document.getElementById('menu2a').style.display = 'block';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    tmp_ID = document.PapersMenu.oldQuestionID.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="">

<br />

<table id="banktasks" cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Question Bank</td></tr>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><span id="type">Questions by Type</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;"><span id="team">Questions by Team</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;"><span id="keyword">Questions by Keyword</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><span id="search">Search</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><span id="newquestion">Create new Question</span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold">Current Question Tasks</td></tr>
<table cellpadding="0" cellspacing="0" border="0" style="margin-left:7px; font-size:90%">
<tr><td>

<table style="font-size:100%; line-height:160%; display:block" cellpadding="4" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="/touchstone/artwork/edit_icon_16_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem">Edit Question</td></tr>
<tr><td><img src="/touchstone/artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem">Information</td></tr>
<tr><td><img src="/touchstone/artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem">Copy onto Paper X...</td></tr>
<tr><td><img src="/touchstone/artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem">Link to paper</td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" /></td><td class="greymenuitem">Delete Question</td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="4" cellspacing="0" border="0" id="menu2b">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="/touchstone/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;">Link to paper</a></td></tr>
<tr><td><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img src="/touchstone/artwork/red_cross.png" width="16" height="16" alt="Delete" border="0" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;">Delete Question</a></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="4" cellspacing="0" border="0" id="menu2c">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#">Edit Question</a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;">Information</a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;">Copy onto Paper X...</a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="/touchstone/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;">Link to paper</a></td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" border="0" /></td><td class="greymenuitem">Delete Question</td></tr>
</table>

</td></tr>
</table>

</td></tr>
</table>

<br />

<input type="hidden" name="questionID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="oldQuestionID" value="" />
</form>

</div>

<?php
  // Module folders.
  if (count($teams) > 0) {
    $modules_array = array();
    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
    while ($module_row = $module_details->fetch_assoc()) {
      $title = $module_row['moduleid'] . ' - ' . str_replace("'","\'",$module_row['fullname']);
      $url = '/touchstone/folder/details.php?module=' . $module_row['moduleid'];
      $modules_array[$title] = $url;
    }
    $module_details->close();
  }
  
  // Teams
  $teams_array = array();
  foreach($teams as $individual_team) {
    $teams_array[$individual_team] = '/touchstone/question/list.php?type=%&team=' . $individual_team;
  }

  // Keywords
  $keywords_array = array();
  $keyword_no = 0;
  $old_moduleID = '';
  $stmt = $mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($moduleID, $tmp_keyword, $keywordID);
  while ($stmt->fetch()) {
    if ($old_moduleID != $moduleID) $keywords_array[$keyword_no] = "#$moduleID";
    $keywords_array[htmlspecialchars($tmp_keyword)] = '/touchstone/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
	$old_moduleID = $moduleID;
	$keyword_no++;
  }
  if ($stmt->num_rows > 0) $keywords_array[$keyword_no] = '#Personal Keywords';
  $stmt->close();

  $result = $mysqli->prepare("SELECT DISTINCT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($keywordID, $tmp_keyword);
  while ($result->fetch()) {
    $keywords_array[htmlspecialchars($tmp_keyword)] = '/touchstone/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
  }
  $result->close();
   
  makeMenu(array('Information Block'=>'/touchstone/question/list.php?type=info','Keyword-based Question'=>'/touchstone/question/list.php?type=keyword','Random Question Block'=>'/touchstone/question/list.php?type=random','-'=>'-','Calculation'=>'/touchstone/question/list.php?type=calculation','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/list.php?type=dichotomous','Extended Matching'=>'/touchstone/question/list.php?type=extmatch','Fill-in-the-Blank'=>'/touchstone/question/list.php?type=blank','Flash Interface'=>'/touchstone/question/list.php?type=flash','Image Hotspot'=>'/touchstone/question/list.php?type=hotspot','Labelling'=>'/touchstone/question/list.php?type=labelling','Likert Scale'=>'/touchstone/question/list.php?type=likert','Matrix'=>'/touchstone/question/list.php?type=matrix','Multiple Choice (MCQ)'=>'/touchstone/question/list.php?type=mcq','Multiple Response (MRQ)'=>'/touchstone/question/list.php?type=mrq','Ranking'=>'/touchstone/question/list.php?type=rank','Text Box'=>'/touchstone/question/list.php?type=textbox','Time/Date'=>'/touchstone/question/list.php?type=timedate'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array('Questions'=>'/touchstone/question/search.php','Papers'=>'/touchstone/paper/search.php','People'=>'/touchstone/users/search.php'));
  makeMenu(array('Information Block'=>'/touchstone/question/add/info.php','Keyword-based Question'=>'/touchstone/question/add/keyword_based.php','Random Question Block'=>'/touchstone/question/add/random.php','-'=>'-','Calculation'=>'/touchstone/question/add/calculation.php','Dichotomous (Yes/No and True/False)'=>'/touchstone/question/add/dichotomous.php','Extended Matching'=>'/touchstone/question/add/extmatch.php','Fill-in-the-Blank'=>'/touchstone/question/add/blank.php','Flash Interface'=>'/touchstone/question/add/flash.php','Image Hotspot'=>'/touchstone/question/add/hotspot1.php','Labelling'=>'/touchstone/question/add/labelling1.php','Likert Scale'=>'/touchstone/question/add/likert.php','Matrix'=>'/touchstone/question/add/matrix.php','Multiple Choice (MCQ)'=>'/touchstone/question/add/mcq.php','Multiple Response (MRQ)'=>'/touchstone/question/add/mrq.php','Ranking'=>'/touchstone/question/add/rank.php','Text Box'=>'/touchstone/question/add/textbox.php','Time/Date'=>'/touchstone/question/add/timedate.php'));

  hideMenuScript($menuNo);
?>