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
    notice=window.open("/touchstone/delete/check_delete_q_original.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=420,height=190,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
    notice=window.open("/touchstone/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,20);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("/touchstone/question/link_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","properties","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,20);
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
<tr><td style="font-weight:bold"><?php echo $string['questionbank']; ?></td></tr>
<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%; margin-left:7px">
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><span id="type"><?php echo $string['questionsbytype']; ?></span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Teams" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;"><span id="team"><?php echo $string['questionsbyteam']; ?></span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/question_list_icon.gif" width="16" height="16" alt="List Keywords" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;"><span id="keyword"><?php echo $string['questionsbykeyword']; ?></span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/xmag.png" width="16" height="16" alt="Search" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><span id="search"><?php echo $string['search']; ?></span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
<tr><td width="16"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" border="0" /></td><td class="menuitem"><a href="#" onclick="showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><span id="newquestion"><?php echo $string['createnewquestion']; ?></span><img src="/touchstone/artwork/submenu_arrow.png" width="16" height="16" alt=">" border="0" /></a></td></tr>
</table>

</td></tr>
</table>

<br />
<br />

<table cellpadding="0" cellspacing="0" border="0" width="185" style="margin-left:2px; font-size:100%">
<tr><td style="font-weight:bold"><?php echo $string['currentquestiontasks']; ?></td></tr>
<table cellpadding="0" cellspacing="0" border="0" style="margin-left:7px; font-size:90%">
<tr><td>

<table style="font-size:100%; line-height:160%; display:block" cellpadding="4" cellspacing="0" border="0" id="menu2a">
<tr><td><img src="/touchstone/artwork/edit_icon_16_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem"><?php echo $string['editquestion']; ?></td></tr>
<tr><td><img src="/touchstone/artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem"><?php echo $string['information']; ?></td></tr>
<tr><td><img src="/touchstone/artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem"><?php echo $string['copyontopaperx']; ?></td></tr>
<tr><td><img src="/touchstone/artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem"><?php echo $string['linktopaperx']; ?></td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="4" cellspacing="0" border="0" id="menu2b">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="/touchstone/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaperx']; ?></a></td></tr>
<tr><td><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img src="/touchstone/artwork/red_cross.png" width="16" height="16" alt="Delete" border="0" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><?php echo $string['deletequestion']; ?></a></td></tr>
</table>

<table style="font-size:100%; line-height:160%; display:none" cellpadding="4" cellspacing="0" border="0" id="menu2c">
<tr><td><a id="edit" onclick="editQuestion(); return false;" href="#"><img src="/touchstone/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" border="0" /></a></td><td class="menuitem"><a id="edit" onclick="editQuestion(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td><a id="information" href="#" onclick="questionInfo(); return false;"><img src="/touchstone/artwork/information_icon.gif" width="16" height="16" alt="Information" border="0" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="/touchstone/artwork/copy_icon.gif" width="16" height="16" alt="Copy" border="0" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="/touchstone/artwork/link.png" width="16" height="16" alt="Link 2 Paper" border="0" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaperx']; ?></a></td></tr>
<tr><td><img src="/touchstone/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" border="0" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
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
   
  //makeMenu($full_modules_array);
  makeMenu(array($string['info']=>'/touchstone/question/list.php?type=info',$string['keyword_based']=>'/touchstone/question/list.php?type=keyword',$string['random']=>'/touchstone/question/list.php?type=random','-'=>'-',$string['calculation']=>'/touchstone/question/list.php?type=calculation',$string['dichotomous']=>'/touchstone/question/list.php?type=dichotomous',$string['extmatch']=>'/touchstone/question/list.php?type=extmatch',$string['blank']=>'/touchstone/question/list.php?type=blank',$string['flash']=>'/touchstone/question/list.php?type=flash',$string['hotspot']=>'/touchstone/question/list.php?type=hotspot',$string['labelling']=>'/touchstone/question/list.php?type=labelling',$string['likert']=>'/touchstone/question/list.php?type=likert',$string['matrix']=>'/touchstone/question/list.php?type=matrix',$string['mcq']=>'/touchstone/question/list.php?type=mcq',$string['mrq']=>'/touchstone/question/list.php?type=mrq',$string['rank']=>'/touchstone/question/list.php?type=rank',$string['sct']=>'/touchstone/question/list.php?type=sct',$string['textbox']=>'/touchstone/question/list.php?type=textbox',$string['timedate']=>'/touchstone/question/list.php?type=timedate'));
  makeMenu($teams_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>'/touchstone/question/search.php',$string['papers']=>'/touchstone/paper/search.php',$string['people']=>'/touchstone/users/search.php'));
  makeMenu(array($string['info']=>"/touchstone/question/add/info.php",$string['keyword_based']=>"/touchstone/question/add/keyword_based.php",$string['random']=>"/touchstone/question/add/random.php","-"=>"-",$string['calculation']=>"/touchstone/question/add/calculation.php",$string['dichotomous']=>"/touchstone/question/add/dichotomous.php",$string['extmatch']=>"/touchstone/question/add/extmatch.php",$string['blank']=>"/touchstone/question/add/blank.php",$string['flash']=>"/touchstone/question/add/flash.php",$string['hotspot']=>"/touchstone/question/add/hotspot1.php",$string['labelling']=>"/touchstone/question/add/labelling1.php",$string['likert']=>"/touchstone/question/add/likert.php",$string['matrix']=>"/touchstone/question/add/matrix.php",$string['mcq']=>"/touchstone/question/add/mcq.php",$string['mrq']=>"/touchstone/question/add/mrq.php",$string['rank']=>"/touchstone/question/add/rank.php",$string['sct']=>"/touchstone/question/add/sct.php",$string['textbox']=>"/touchstone/question/add/textbox.php",$string['timedate']=>"/touchstone/question/add/timedate.php"));

  hideMenuScript($menuNo);
?>