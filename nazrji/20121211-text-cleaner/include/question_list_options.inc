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
* Task menu for question list.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';

$modules_array = array();

$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);
?>
<?php echo $configObject->get('cfg_js_root') ?>
<script src="<?php echo $configObject->get('cfg_root_path') ?>/js/sidebar.js" type="text/javascript"></script>
<script language="JavaScript">
  function getLastID(IDs) {
    var id_list = IDs.split(",");
    last_elm = id_list.length - 1;
    
    return id_list[last_elm];
  }

  function addQID(qID,clearall) {
    if (clearall) {
      document.PapersMenu.questionID.value = ',' + qID;
    } else {
      document.PapersMenu.questionID.value = document.PapersMenu.questionID.value + ',' + qID;
    }
  }

  function subQID(qID) {
    var tmpq = ',' + qID;
    document.PapersMenu.questionID.value = document.PapersMenu.questionID.value.replace(tmpq, '');
  }
  
  function clearAll() {
    $('.highlight').removeClass('highlight');
  }
  
  function selQ(questionID, lineID, qType, menuID, evt) {
    document.getElementById('menu2a').style.display = 'none';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    document.getElementById('menu' + menuID).style.display = 'block';

    if (evt.ctrlKey == false) {
      clearAll();
      $('#link_' + lineID).addClass('highlight');
      addQID(questionID, true);
    } else {
      if ($('#link_' + lineID).hasClass('highlight')) {
        $('#link_' + lineID).removeClass('highlight');
        subQID(questionID);
      } else {
        $('#link_' + lineID).addClass('highlight');
        addQID(questionID, false);
      }
    }
    <?php 
    if (isset($_GET['type']) and $_GET['type'] == '%') {
      echo "document.PapersMenu.qType.value = '%';\n";
    } else {
      echo "document.PapersMenu.qType.value = qType;\n";
    }
    ?>
  }
  
  function editQ() {
    var url = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID(document.PapersMenu.questionID.value) + '&paperID=list:' + document.PapersMenu.qType.value + '&calling=list';
    <?php 
      if (isset($_GET['team']) and $_GET['team'] != '') {
          echo "url = url + '&team=" . $_GET['team'] . "';\n";
      }
      if(isset($_GET['keyword']) and $_GET['keyword'] != '') {
          echo "url = url + '&keyword=" . $_GET['keyword'] . "';\n";
      }
    ?>
    window.location.href = url;
  }

  function deleteQuestion() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_q_original.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/info.php?q_id=" + getLastID(document.PapersMenu.questionID.value) + "","question_info","width=700,height=600,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/copy_onto_paper.php?q_id=" + document.PapersMenu.questionID.value + "","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,20);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/link_to_paper.php?q_id=" + document.PapersMenu.questionID.value + "","properties","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300,20);
    if (window.focus) {
      notice.focus();
    }
  }

  function qOff() {
    document.getElementById('menu2a').style.display = 'block';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    document.PapersMenu.questionID.value = '';
    clearAll();
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="">

<div class="submenuheading" id="banktasks"><?php echo $string['questionbank']; ?></div>

<table class="sidebar_tbl">
<tr><td class="ico"><a href="#" onclick="showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" width="16" height="16" alt="List Papers" /></a></td><td class="menuitem sub"><span id="type"><a href="#" onclick="showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><?php echo $string['questionsbytype']; ?></a></span></td></tr>
<?php
  if (count($staff_modules) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"" . $string['questionsbyteam'] . "\" /></td><td class=\"greymenuitem sub\"><span id=\"team\">" . $string['questionsbyteam'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;\"><img src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"List Teams\" /></a></td><td class=\"menuitem sub\"><span id=\"team\"><a href=\"#\" onclick=\"showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;\">" . $string['questionsbyteam'] . "</a></span></td></tr>\n";
  }
  if (count($keywords_array) == 0) {
    echo "<tr><td class=\"ico\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon_grey.gif\" width=\"16\" height=\"16\" alt=\"List Keywords\" /></td><td class=\"greymenuitem sub\"><span id=\"keyword\">" . $string['questionsbykeyword'] . "</span></td></tr>\n";
  } else {
    echo "<tr><td class=\"ico\"><a href=\"#\" onclick=\"showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" width=\"16\" height=\"16\" alt=\"List Keywords\" /></a></td><td class=\"menuitem sub\"><span id=\"keyword\"><a href=\"#\" onclick=\"showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;\">" . $string['questionsbykeyword'] . "</a></span></td></tr>\n";
  }
?>
<tr><td class="ico"><a href="#" onclick="showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" width="16" height="16" alt="Search" /></a></td><td class="menuitem sub"><span id="search"><a href="#" onclick="showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><?php echo $string['search']; ?></a></span></td></tr>
<tr><td class="ico"><a href="#" onclick="showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="newquestion" /></a></td><td class="menuitem sub"><span id="newquestion"><a href="#" onclick="showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><?php echo $string['createnewquestion']; ?></a></span></td></tr>
</table>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['currentquestiontasks']; ?></div>

<table class="sidebar_tbl" id="menu2a">
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_icon_16_grey.png" width="16" height="16" alt="Edit" /></td><td class="greymenuitem"><?php echo $string['editquestion']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" width="16" height="16" alt="Information" /></td><td class="greymenuitem"><?php echo $string['information']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" width="16" height="16" alt="Copy" /></td><td class="greymenuitem"><?php echo $string['copyontopaperx']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" width="16" height="16" alt="Link 2 Paper" /></td><td class="greymenuitem"><?php echo $string['linktopaperx']; ?></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu2b">
<tr><td class="ico"><a id="edit" onclick="editQ(); return false;" href="#"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" /></a></td><td class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><a id="information" href="#" onclick="questionInfo(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" width="16" height="16" alt="Information" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" width="16" height="16" alt="Copy" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" width="16" height="16" alt="Delete" /></a></td><td class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><?php echo $string['deletequestion']; ?></a></td></tr>
</table>

<table class="sidebar_tbl" style="display:none" id="menu2c">
<tr><td class="ico"><a id="edit" onclick="editQ(); return false;" href="#"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" width="16" height="16" alt="Edit" /></a></td><td class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><?php echo $string['editquestion']; ?></a></td></tr>
<tr><td class="ico"><a id="information" href="#" onclick="questionInfo(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" width="16" height="16" alt="Information" /></a></td><td class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><?php echo $string['information']; ?></a></td></tr>
<tr><td class="ico"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" width="16" height="16" alt="Copy" /></a></td><td class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><?php echo $string['copyontopaperx']; ?></a></td></tr>
<tr><td class="ico"><a id="link" href="#" onclick="linkToPaper(); return false;"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" width="16" height="16" alt="Link 2 Paper" /></a></td><td class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><?php echo $string['linktopaperx']; ?></a></td></tr>
<tr><td class="ico"><img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" width="16" height="16" alt="Delete" border="0" /></td><td class="greymenuitem"><?php echo $string['deletequestion']; ?></td></tr>
</table>

<input type="hidden" name="questionID" value="" />
<input type="hidden" name="qType" value="" />
<input type="hidden" name="oldQuestionID" value="" />
<input type="hidden" name="team" value="<?php if(isset($_GET['team'])) echo $_GET['team'];?>" />
</form>

</div>

<?php
  //makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['area']=>$configObject->get('cfg_root_path') . '/question/list.php?type=area',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=calculation',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['flash']=>$configObject->get('cfg_root_path') . '/question/list.php?type=flash',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php',$string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php',$string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=calculation",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['flash']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=flash",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>