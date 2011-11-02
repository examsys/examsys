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
  $question_id = insert_into_questions('branching','',$_POST['decisionID'],$_POST['description'],'','','','',$userID,'',0,0,date("YmdHis"),date("YmdHis"),'',getTeams(),$_POST['status']);

  // Insert into Options
  for ($i=1; $i<=$_POST['branch_no']; $i++) {
    if ($_POST["answer" . $i . "_no"] != '0') {
      $individual_question = '';
      for ($separate_question = 0; $separate_question < $_POST['answer' . $i . '_no']; $separate_question++) {
        
        if ($_POST['question' . $i . '_text' . $separate_question] != '') {
          $tempID = $_POST["question" . $i . "_id" . $separate_question];
        } else {
          $tempID = '';
        }
      
        if ($tempID != '') {
          if ($individual_question == '') {
            $individual_question = $tempID;
          } else {
            $individual_question .= ',' . $tempID;
          }
        }
      }
      insert_into_options($question_id,$individual_question,'','','','','','','','','');
    }
  }

  // Save keywords
  $changes = false;
  saveKeywords($question_id, $userID, $changes, false, $mysqli);

  $paperID = $_POST['paperID'];
  // Insert into Papers
  if (isset($_POST['addpaper'])) {
    insert_into_papers($paperID,$question_id,true);
    saveObjMappings($paperID,$question_id,$mysqli);
  }

  setcookie("default_team", $_POST['team'], time()+31536000);

  redirect($paperID);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>New Branching Question</title>
<link rel="stylesheet" href="../../css/add_edit.css" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script language="JavaScript">
  function addQuestion() {
    winH = screen.height - 80;
    winW = screen.width - 80;
    notice=window.open("branch_source_question.php?paperID=<?php echo $_GET['paperID']; ?>","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
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

  function sourceQuestion() {
    winTop = (screen.height / 2) - 250;
    winLeft = (screen.width / 2) - 300;
    window.open("branch_source_question.php?paperID=<?php echo $_GET['paperID']; ?>","paper","width=600,height=500,left=" + winLeft + ",top=" + winTop + ",scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
  }

  function addQuestion(questionlist, question_no, q_no) {
    winH = screen.height - 80;
    winW = screen.width - 80;
    objectID = 'answer' + q_no + '_no';
    notice=window.open("add_random_questions_frame.php?q_no=" + q_no + "&questionlist=" + questionlist + "&question_no=" + question_no + "","notice","width=" + winW + ",height=" + winH + ",left=40,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=yes,resizable");
    if (window.focus) {
      notice.focus();
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
<input type="hidden" name="branch_no" id="branch_no" value="0" />
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr height="70" style="background-color:#DFECFF">
    <td width="400">
      <img style="position:absolute; left:8px; top:2px;" src="../artwork/edit_question.png" width="64" height="64" alt="Edit Logo" />
      <span style="position:absolute; left:80px; top:0px; font-family:'Arial Black',Arial,sans-serif; font-size:24pt">New Question</span>
      <span style="position:absolute; left:80px; top:40px; font-family:Arial,sans-serif; font-size:12pt; font-weight:bold">(Branching Question Block)</span></td>
      <td style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
  </tr>
</table>
<?php
  echo displayEditTab();
?>
  <br />
    <table cellpadding="3" cellspacing="0" border="0" align="center">
    <tr>
      <td class="field">Description</td>
      <td><input type="text" name="description" size="60" /></div></td>
    </tr>
    <tr>
      <td class="field">Decision Question</td>
      <td><div id="decisionquestion" style="width:500px"></div><input type="hidden" name="decisionID" id="decisionID" value="" /><input type="button" name="addquestion" value="Select Question" style="width:150px" onclick="sourceQuestion();" /></td>
    </tr>
    <tr id="heading" style="display:none">
      <td colspan="2"><div class="section">Branches</div></td
    </tr>
    <?php
    for ($branch=1; $branch<=20; $branch++) {
      echo "<tr id=\"line$branch\" style=\"display:none\">\n";
      echo "<td class=\"field\"><div id=\"option$branch\"></div></td>\n";
      echo "<td><div name=\"answer$branch\" id=\"answer$branch\" style=\"width:650px; height:60px; background-color:white; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\" /></div><input type=\"button\" name=\"addquestion\" value=\"Add Question(s)\" style=\"width:150px\" onclick=\"addQuestion('answer$branch','answer" . $branch . "_no','$branch');\" /><input type=\"text\" name=\"answer" . $branch . "_no\" id=\"answer" . $branch . "_no\" value=\"0\" /></div></td>\n";
      echo "</tr>\n";
    }

    echo echoMetadata('','','',1,$mysqli,false,'','');
    ?>
    <tr>
      <td colspan="2">&nbsp;<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" /><input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" /><input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" /><input type="hidden" name="scrOfY" value="<?php echo $_GET['scrOfY']; ?>" /></td>
    </tr>
    <tr>
    <?php
      if ($_GET['paperID'] != '' and substr($_GET['paperID'],0,5) != 'list:') {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;<input style="width:150px" type="submit" name="addpaper" value="Add to Bank &amp; Paper">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      } else {
        echo '<td colspan="2" style="text-align:center"><input style="width:150px" type="submit" name="addbank" value="Add to Bank">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:100px" type="button" name="cancel" value="Cancel" onclick="history.back()" /></td>';
      }
    ?>
    </tr>
  </table>
  </div>

<?php
  echo displayMappingTabAdd($_GET['paperID'],$mysqli,date('d/m/Y'));
}
$mysqli->close();
?>
</form>
</body>
</html>
