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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/media.inc';
require '../../include/add.inc';
require '../../include/metadata.inc';
require '../../include/mapping_tab.inc';
include_once('../../tools/getid3/getid3.php');

if (isset($_POST['addbank']) or isset($_POST['addpaper'])) {
  // Insert into Questions
  $question_id = insert_into_questions('random','','',$_POST['description'],'','','','',$userID,'',0,0,date("YmdHis"),date("YmdHis"),'',getTeams(),$_POST['status'],'display order');
  
  // Insert into Options
  for ($i=0; $i<$_POST['question_no']; $i++) {
    if ($_POST["question_text$i"] != '') {
      $individual_question = $_POST["question_id$i"];
      insert_into_options($question_id,$individual_question,'','','','','','','','','');
    }
  }

  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

  $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID,$question_id);
    saveObjMappings($paperID,$question_id,$mysqli);
  }

  setcookie("default_team", getDefaultTeam(), time()+31536000);

  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['random']; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<script language="JavaScript">
  function addQuestion() {
    winH = screen.height - 80
    winW = screen.width - 80
    notice=window.open("add_random_questions_frame.php?q_no=1&questionlist=questionlist&question_no=question_no","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    if (window.focus) {
      notice.focus();
    }
  
  }

  function showTab(tabID) {
    if (tabID == 'editortab') {
      document.getElementById('editortab').style.display = 'block';
      document.getElementById('mappingtab').style.display = 'none';
    } else if (tabID == 'mappingtab') {
      document.getElementById('editortab').style.display = 'none';
      document.getElementById('mappingtab').style.display = 'block';
    } 
  }
</script>
<script language="JavaScript" src="../../javascript/mapping_tab.js"></script>
<script language="JavaScript" src="../../javascript/metadata.js"></script>
<?php echo $cfg_editor_javascript; ?>
<script language="JavaScript" src="../../javascript/staff_help.js"></script>
</head>

<body>
<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="54" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../../artwork/edit_question_small.png" width="48" height="48" alt="Edit Logo" />
      <span style="position:absolute; left:70px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt"><?php echo $string['add'] . ' ' . $string['question'] . ' &ndash; ' . $string['random']; ?></span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <br />
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="field"><?php echo $string['description']; ?></td>
      <td><input type="text" name="description" size="60" /></div></td>
    </tr>
    <tr>
      <td class="field"><?php echo $string['questions']; ?></td>
      <td><div id="questionlist" style="width:600px; height:500px; background-color:white; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%"></div></td>
    </tr>
    <tr>
    <td></td><td><input type="button" name="addquestion" value="<?php echo $string['addquestions']; ?>" style="width:150px" onclick="addQuestion();" /><input type="hidden" name="question_no" id="question_no" value="0" /></td>
    </tr>
    <?php
      echo echoMetadata('','','',1,$mysqli,false,'','');
    ?>
    <tr>
      <td colspan="2">&nbsp;<?php echo hidden_add_fields(); ?></td>
    </tr>
    <tr>
    <?php
      if (isset($_GET['paperID']) and $_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="' . $string['addtobank'] . '">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="' . $string['addtobankandpaper'] . '">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="' . $string['addtobank'] . '">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back()" /></td>';
      }
    ?>
    </tr>
  </table>
  </div>

<?php
  if (isset($_GET['paperID'])) {
    $paperID = $_GET['paperID'];
  } else {
    $paperID = '';
  }

  echo displayMappingTabAdd($paperID,$mysqli,date('d/m/Y'));
}
$mysqli->close();
?>
</form>
</body>
</html>
