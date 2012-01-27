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
* Allows the properties of a paper to be edited.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/add_edit.inc';  // to clear MS Office tags
require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
require '../lang/' . $language. '/include/timezones.inc';

function modulo($n,$b) {
  return $n-$b*floor($n/$b);
}

if (isset($_POST['Submit'])) {
  // Check that the new paper name is not already used by any other paper (i.e. unique).
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE paper_title=? LIMIT 1");
  $result->bind_param('s', $_POST['paper_title']);
  $result->execute();
  $result->bind_result($paper_title);
  $result->store_result();
  if ($result->num_rows == 0 or $_POST['original_paper_title'] == $_POST['paper_title']) {
    if (isset($_POST['bidirectional']) AND $_POST['bidirectional'] == 1) {
      $bidirectional = 1;
    } else {
      $bidirectional = 0;
    }
    if (isset($_POST['display_correct_answer'])) {
      $display_correct_answer = 1;
    } else {
      $display_correct_answer = 0;
    }
    if (isset($_POST['display_students_response'])) {
      $display_students_response = 1;
    } else {
      $display_students_response = 0;
    }
    if (isset($_POST['display_question_mark'])) {
      $display_question_mark = 1;
    } else {
      $display_question_mark = 0;
    }
    if (isset($_POST['display_feedback'])) {
      $display_feedback = 1;
    } else {
      $display_feedback = 0;
    }
    
    if (isset($_POST['hide_if_unanswered'])) {
      $hide_if_unanswered = '1';
    } else {
      $hide_if_unanswered = '0';
    }
    
    if ((modulo($_POST['fyear'],4) == 0 and modulo($_POST['fyear'],100) != 0) or modulo($_POST['fyear'],400) == 0) {
      $leap = true;
    } else {
      $leap = false;
    }   
    if ($leap == true and $_POST['fmonth'] == '02' and ($_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '29';
    if ($leap == false and $_POST['fmonth'] == '02' and ($_POST['fday'] == '29' or $_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '28';
    if (($_POST['fmonth'] == '04' or $_POST['fmonth'] == '06' or $_POST['fmonth'] == '09' or $_POST['fmonth'] == '11') and $_POST['fday'] == '31') $_POST['fday'] = '30';
    
    $local_time = new DateTimeZone($cfg_timezone);
    $target_timezone = new DateTimeZone($_POST['timezone']);
    
    $start_date = new dateTime($_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'], $target_timezone);
    $start_date->setTimezone($local_time);
        
    if ((modulo($_POST['tyear'],4) == 0 and modulo($_POST['tyear'],100) != 0) or modulo($_POST['tyear'],400) == 0) {
      $leap = true;
    } else {
      $leap = false;
    }   
    if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '29';
    if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '28';
    if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') $_POST['tday'] = '30';
    
    $end_date = new dateTime($_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'], $target_timezone);
    $end_date->setTimezone($local_time);
        
    if ($_POST['timezone'] < 0) {
      $start_date->modify("+" . abs($_POST['timezone']) . " hour");
      $end_date->modify("+" . abs($_POST['timezone']) . " hour");
    } elseif ($_POST['timezone'] > 0) {
      $start_date->modify("-" . $_POST['timezone'] . " hour");
      $end_date->modify("-" . $_POST['timezone'] . " hour");
    }

    if ($leap == true and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '29';
    if ($leap == false and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '29' or $_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '28';
    if (($_POST['ext_tmonth'] == '04' or $_POST['ext_tmonth'] == '06' or $_POST['ext_tmonth'] == '09' or $_POST['ext_tmonth'] == '11') and $_POST['ext_tday'] == '31') $_POST['ext_tday'] = '30';

    $external_review_deadline = $_POST['ext_tyear'] . $_POST['ext_tmonth'] . $_POST['ext_tday'];
    if ($external_review_deadline == '') $external_review_deadline = NULL;
    
    if ($leap == true and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '29';
    if ($leap == false and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '29' or $_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '28';
    if (($_POST['int_tmonth'] == '04' or $_POST['int_tmonth'] == '06' or $_POST['int_tmonth'] == '09' or $_POST['int_tmonth'] == '11') and $_POST['int_tday'] == '31') $_POST['int_tday'] = '30';

    $internal_review_deadline = $_POST['int_tyear'] . $_POST['int_tmonth'] . $_POST['int_tday'];
    if ($internal_review_deadline == '') $internal_review_deadline = NULL;

    $module_string = '';
    $first_module = '';
    for ($i=0; $i<$_POST['module_no']; $i++) {
      if (isset($_POST['module' . $i])) {
        if ($module_string == '') {
          $module_string = $_POST['module' . $i];
          $first_module = $_POST['module' . $i];
        } else {
          $module_string .= ',' . $_POST['module' . $i];
        }
      }
    }
    
    $lab_string = '';
    for ($i=0; $i<$_POST['lab_no']; $i++) {
      if (isset($_POST["lab$i"])) {
        if ($lab_string == '') {
          $lab_string = $_POST["lab$i"];
        } else {
          $lab_string .= ',' . $_POST["lab$i"];
        }
      }
    }
    
    $external_string = '';
    for ($i=0; $i<$_POST['examiner_no']; $i++) {
      if (isset($_POST["examiner$i"])) {
        if ($external_string == '') {
          $external_string = $_POST["examiner$i"];
        } else {
          $external_string .= ',' . $_POST["examiner$i"];
        }
      }
    }
    
    $internal_string = '';
    for ($i=0; $i<$_POST['internal_no']; $i++) {
      if (isset($_POST["internal$i"])) {
        if ($internal_string == '') {
          $internal_string = $_POST["internal$i"];
        } else {
          $internal_string .= ',' . $_POST["internal$i"];
        }
      }
    }
    
    $tmp_prologue = $_POST['paper_prologue'];
    $tmp_prologue = clearMSOtags($tmp_prologue);
    
    if ($_POST['paper_type'] == '4') {
      $tmp_postscript = $_POST['osce_marking_guidance'];
      $tmp_postscript = clearMSOtags($tmp_postscript);
    } else {
      $tmp_postscript = $_POST['paper_postscript'];
      $tmp_postscript = clearMSOtags($tmp_postscript);
    }
    
    $tmp_rubric = $_POST['rubric_text'];
    $tmp_rubric = clearMSOtags($tmp_rubric);
    
    $tmp_marking = $_POST['marking'];
    if ($tmp_marking == '') $tmp_marking = '0';
    if ($tmp_marking == '2') $tmp_marking = $_POST['std_set'];
        
    $tmp_pass_mark = $_POST['pass_mark'];
    if ($tmp_pass_mark == '') $tmp_pass_mark = 40;

    $tmp_distinction_mark = (isset($_POST['distinction_mark']) and $_POST['distinction_mark'] != '') ? $_POST['distinction_mark'] : 70;

    /*
    if (isset($_POST['calculator'])) {
      $tmp_calculator = 1;
    } else {
      $tmp_calculator = 0;
    }
    */
    $tmp_calculator = $_POST['calculator'];

    
    if (isset($_POST['sound_demo'])) {
      $tmp_sound_demo = 1;
    } else {
      $tmp_sound_demo = 0;
    }
    
    $paper_title = $_POST['paper_title'];
    $paper_type = $_POST['paper_type'];
    $tmp_start_date = $start_date->format("YmdHis");
    $tmp_end_date = $end_date->format("YmdHis");
    $timezone = $_POST['timezone'];
    $bgcolor = $_POST['bgcolor'];
    $fgcolor = $_POST['fgcolor'];
    $themecolor = $_POST['themecolor'];
    $labelcolor = $_POST['labelcolor'];
    $fullscreen = $_POST['fullscreen'];
    $folderID = $_POST['folderID'];
    $exam_duration = ($_POST['exam_duration'] == 'NULL') ? NULL : $_POST['exam_duration'];
    $password = trim($_POST['password']);
    $paperID = $_POST['paperID'];

    $editProperties = $mysqli->prepare("UPDATE properties SET paper_title=?, paper_type=?, start_date=?, end_date=?, timezone=?, paper_prologue=?, moduleID=?, paper_postscript=?, bgcolor=?, fgcolor=?, themecolor=?, labelcolor=?, fullscreen=?, marking=?, bidirectional=?, pass_mark=?, distinction_mark=?, folder=?, labs=?, rubric=?, calculator=?, externals=?, exam_duration=?, display_correct_answer=?, display_students_response=?, display_question_mark=?, display_feedback=?, hide_if_unanswered=?, calendar_year=?, internal_reviewers=?, external_review_deadline=?, internal_review_deadline=?, sound_demo=?, password=? WHERE property_id=?");
    $editProperties->bind_param('sssssssssssssssiisssisisssssssssssi', $paper_title, $paper_type, $tmp_start_date, $tmp_end_date, $timezone, $tmp_prologue, $module_string, $tmp_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $tmp_marking, $bidirectional, $tmp_pass_mark, $tmp_distinction_mark, $folderID, $lab_string, $tmp_rubric, $tmp_calculator, $external_string, $exam_duration, $display_correct_answer, $display_students_response, $display_question_mark, $display_feedback, $hide_if_unanswered, $_POST['calendar_year'], $internal_string, $external_review_deadline, $internal_review_deadline, $tmp_sound_demo, $password, $paperID);
    $editProperties->execute();
    $editProperties->close();
    
    // Release objectives-based feedback
    $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id=? AND type='objectives'");
    $editProperties->bind_param('i', $_POST['paperID']);
    $editProperties->execute();
    $editProperties->close();
    if (isset($_POST['objectives_report']) and $_POST['objectives_report'] == 1) {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'objectives')");
      $editProperties->bind_param('i', $_POST['paperID']);
      $editProperties->execute();
      $editProperties->close();
    }

    // Release question-based feedback
    $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id=? AND type='questions'");
    $editProperties->bind_param('i', $_POST['paperID']);
    $editProperties->execute();
    $editProperties->close();
    if (isset($_POST['questions_report']) and $_POST['questions_report'] == 1) {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'questions')");
      $editProperties->bind_param('i', $_POST['paperID']);
      $editProperties->execute();
      $editProperties->close();
    }

    // Set the questions team on this paper.
    $result = $mysqli->prepare("SELECT q_id FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id ORDER BY display_pos");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id);
    while ($result->fetch()) {
      $editPaper = $mysqli->prepare("UPDATE questions SET q_group=? WHERE q_id=?");
      $editPaper->bind_param('si',$first_module, $q_id);
      $editPaper->execute();
      $editPaper->close();
    }
    $result->close();
    
    // Set any metadata security
    for ($i=0; $i<$_POST['meta_dropdown_no']; $i++) {
      $meta_type = $_POST['meta_type' . $i];
      $meta_value = $_POST['meta_value' . $i];
      
      $editProperties = $mysqli->prepare("DELETE FROM paper_metadata_security WHERE paperID=? AND name=?");
      $editProperties->bind_param('is', $paperID, $meta_type);
      $editProperties->execute();
      $editProperties->close();

      if ($meta_value != '') {
        $editProperties = $mysqli->prepare("INSERT INTO paper_metadata_security VALUES (NULL, ?, ?, ?)");
        $editProperties->bind_param('iss', $paperID, $meta_type, $meta_value);
        $editProperties->execute();
        $editProperties->close();
      }
    }

  ?>
    <html>
    <head>
    <title><?php echo $string['edittitle']; ?></title>
    <meta http-equiv="pragma" content="no-cache" />
    <script language="JavaScript">
      function closeWindow() {
<?php
          if ($_POST['noadd'] == 'y') {
        ?>
            window.opener.location = "details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>&folder=<?php if (isset($_POST['folderID'])) echo $_POST['folderID']; ?>&school=";
            window.opener.close();
            window.close();
        <?php
          } else {
        ?>
            window.opener.location = "details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>&folder=<?php if (isset($_POST['folderID'])) echo $_POST['folderID']; ?>&school=";
            window.close();
        <?php
          }
        ?>
      }
      function updateParent() {
        window.opener.parent.location = "details.php?paperID=<?php echo $_POST['paperID']; ?>&module=<?php echo $first_module; ?>";
        window.close();
      }
    </script></head>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent();" /></div>
    </form>
  <?php
  } else {
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
    <html>
    <head><title><?php echo $string['edittitle']; ?></title></head>
    <body>
    <form>
      <br /><?php echo $string['warning']; ?><br />&nbsp;<div align="center"><input type="button" name="back" value="&lt; Back" onclick="javascript: history.go(-1)" /></div>
    </form>
  <?php
  }
} else {
  $option_no = 1;
  
  // Work out if any negative marking is used
  $neg_marking = false;
  $result = $mysqli->prepare("SELECT marks_incorrect FROM papers, questions, options WHERE papers.question=questions.q_id AND questions.q_id=options.o_id AND paper=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($marks_incorrect);
  while ($result->fetch()) {
    if ($marks_incorrect < 0) {
      $neg_marking = true;
    }
  }
  $result->close();
  
  // Get the main properties of the paper
  $result = $mysqli->prepare("SELECT display_students_response, display_correct_answer, display_question_mark, display_feedback, hide_if_unanswered, paper_title, paper_type, start_date, end_date, timezone, paper_prologue, paper_postscript, bgcolor, fgcolor, themecolor, labelcolor, fullscreen, marking, bidirectional, pass_mark, distinction_mark, folder, labs, rubric, calculator, externals, exam_duration, moduleID, calendar_year, internal_reviewers, external_review_deadline, internal_review_deadline, sound_demo, password, crypt_name FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($display_students_response, $display_correct_answer, $display_question_mark, $display_feedback, $hide_if_unanswered, $paper_title, $paper_type, $start_date, $end_date, $timezone, $paper_prologue, $paper_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $folder, $labs, $rubric, $calculator, $externals, $exam_duration, $moduleID, $calendar_year, $internal_reviewers, $external_review_deadline, $internal_review_deadline, $sound_demo, $password, $crypt_name);
  $result->fetch();
  $result->close();
  
  $local_time = new DateTimeZone($cfg_timezone);
  $target_timezone = new DateTimeZone($timezone);
  $start_date = new dateTime($start_date, $local_time);
  $end_date = new dateTime($end_date, $local_time);
  
  $start_date->setTimezone($target_timezone);
  $end_date->setTimezone($target_timezone);
  
  $start_date = $start_date->format("Y/m/d H:i:s");
  $end_date = $end_date->format("Y/m/d H:i:s");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['propertiestitle'] . ' ' . $cfg_install_type; ?></title>

  <style>
    body {font-family:Arial,sans-serif; color:black; background-color:#F1F5FB; margin:0px; font-size:100%}
    table {font-size:100%; text-align:left}
    input,textarea {font-family:Arial,sans-serif; color:black}
    .indenton {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
    .indentoff {text-indent:-23px; padding-left:23px; background-color:white}
  </style>

  <?php echo $cfg_editor_javascript; ?>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    $(getMetadataDropdowns);
  
    function getMetadataDropdowns() {
      var mod_codes = '';
      for (i=0; i<=100; i++) {
        if (document.getElementById('module' + i).checked == true) {
          if (mod_codes == '') {
            mod_codes = document.getElementById('module' + i).value;
          } else {
            mod_codes += ',' + document.getElementById('module' + i).value;
          }
        }
      }
      $('#metadata_security').load('getMetdataSecurity.php', 'modules=' + mod_codes + '&paperID=<?php echo $_GET['paperID']; ?>&session=' + $('#session').val() );
    }
  
    function objreportURL() {
      if (document.getElementById('objectives_report').checked == true) {
        document.getElementById('objreport').style.display = 'block';
      } else {
        document.getElementById('objreport').style.display = 'none';
      }
    }
  
    function toggle(objectID) {
      if (document.getElementById(objectID).className == 'indentoff') {
        document.getElementById(objectID).className = 'indenton';
      } else {
        document.getElementById(objectID).className = 'indentoff';
      }
    }
    
    function checkForm() {
      if (edit_form.fyear.value > edit_form.tyear.value) {
        alert ("<?php echo $string['availablefromyear']; ?>");
        return false;
      } else if (edit_form.fyear.value == edit_form.tyear.value && edit_form.fmonth.value > edit_form.tmonth.value) {
        alert ("<?php echo $string['availablefrommonth']; ?>");
        return false;
      } else if (edit_form.fyear.value == edit_form.tyear.value && edit_form.fmonth.value == edit_form.tmonth.value && edit_form.fday.value > edit_form.tday.value) {
        alert ("<?php echo $string['availablefromday']; ?>");
        return false;
      }
      
      var module_no = document.getElementById('module_no').value;
      var moduleList = '';
      for (var i = 0; i < module_no; i++) {
        objectID = 'module' + i;        
        if (document.getElementById(objectID).checked == true) {
          if (moduleList == '') {
            moduleList = document.getElementById(objectID).value;
          } else {
            moduleList += ',' + document.getElementById(objectID).value;
          }
        }
      }
      if (moduleList == '') {
        alert ("<?php echo $string['msg1']; ?>");
        return false;
      }

      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '2') {
        if (edit_form.fday.value != edit_form.tday.value || edit_form.fmonth.value != edit_form.tmonth.value || edit_form.fyear.value != edit_form.tyear.value) {
          alert ("<?php echo $string['msg2']; ?>");
          return false;
        }
        
        if (document.edit_form.exam_duration.options[document.edit_form.exam_duration.selectedIndex].value == 'NULL') {
          alert ("<?php echo $string['msg3']; ?>");
          return false;
        }
        
        if (document.edit_form.calendar_year.options[document.edit_form.calendar_year.selectedIndex].value == '') {
          alert ("<?php echo $string['msg4']; ?>");
          return false;
        }
      }
    
      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '4') {
        var module_no = document.getElementById('module_no').value;

        var moduleList = '';
        for (var i = 0; i < module_no; i++) {
          objectID = 'module' + i;        
          if (document.getElementById(objectID).checked == true) {
            if (moduleList == '') {
              moduleList = document.getElementById(objectID).value;
            } else {
              moduleList += ',' + document.getElementById(objectID).value;
            }
          }
        }
        if (moduleList == '') {
          alert ("<?php echo $string['msg5']; ?>");
          return false;
        }
      }
      
      var external_set = false;
      for (var i = 0; i < document.getElementById('examiner_no').value; i++) {
        objectID = 'examiner' + i;
        if (document.getElementById(objectID).checked == true) {
          external_set = true;
        }
      }
      if (external_set == true) {
        if (document.edit_form.ext_tmonth.options[document.edit_form.ext_tmonth.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if (document.edit_form.ext_tday.options[document.edit_form.ext_tday.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if (document.edit_form.ext_tyear.options[document.edit_form.ext_tyear.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        }        
      }
     
      var internal_set = false;
      for (var i = 0; i < document.getElementById('internal_no').value; i++) {
        objectID = 'internal' + i;
        if (document.getElementById(objectID).checked == true) {
          internal_set = true;
        }
      }
      if (internal_set == true) {
        if (document.edit_form.int_tmonth.options[document.edit_form.int_tmonth.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if (document.edit_form.int_tday.options[document.edit_form.int_tday.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if (document.edit_form.int_tyear.options[document.edit_form.int_tyear.selectedIndex].value == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        }        
      }
     
      if (edit_form.paper.value == '') {
        alert ("<?php echo $string['msg7']; ?>");
        return false;
      }    
    }

    function changeType() {
      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '0') {
        document.getElementById('feedback_on').style.display = 'block';
        document.getElementById('feedback_off').style.display = 'none';
      } else {
        document.getElementById('feedback_on').style.display = 'none';
        document.getElementById('feedback_off').style.display = 'block';
      }
      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '3') {
        document.getElementById('pass_mark').disabled = true;
        document.getElementById('marking1').disabled = true;
        document.getElementById('marking2').disabled = true;
      } else {
        document.getElementById('pass_mark').disabled = false;
        document.getElementById('marking1').disabled = false;
        document.getElementById('marking2').disabled = false;
      }
      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '2') {
        document.edit_form.tday.value = document.edit_form.fday.options[document.edit_form.fday.selectedIndex].value;
        document.edit_form.tmonth.value = document.edit_form.fmonth.options[document.edit_form.fmonth.selectedIndex].value;
        document.edit_form.tyear.value = document.edit_form.fyear.options[document.edit_form.fyear.selectedIndex].value;
        if (document.getElementById('rubric_text').value == '') {
          oEdit3.loadHTML("<?php echo $string['msg8']; ?>");
        }
      }
    }

    function launchHelp(pageID) {
      var winheight = screen.height-100;
      if (screen.width == 800) {
        notice=window.open("./staff_help/index.php?id=" + pageID + "","help","width=770,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      } else {
        notice=window.open("./staff_help/index.php?id=" + pageID + "","help","width=950,height="+winheight+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
        notice.moveTo(10,10);
      }
    }
    
    function buttonclick(sectionID) {
      document.getElementById('general').style.display = 'none';
      document.getElementById('security').style.display = 'none';
      document.getElementById('reviewers').style.display = 'none';
      <?php
        if ($paper_type != '4' and $paper_type != '5') {
      ?>
      document.getElementById('rubric').style.display = 'none';
      document.getElementById('prologue').style.display = 'none';
      document.getElementById('postscript').style.display = 'none';
      <?php
        }
      ?>
      document.getElementById(sectionID).style.display='';
      
      document.getElementById('button_general').style.background='';
      document.getElementById('button_security').style.background='';
      document.getElementById('button_reviewers').style.background='';
      <?php
        if ($paper_type != '4' and $paper_type != '5') {
      ?>
      document.getElementById('button_rubric').style.background='';
      document.getElementById('button_prologue').style.background='';
      document.getElementById('button_postscript').style.background='';
      <?php
        }
      ?>

      document.getElementById('button_'+sectionID).style.background='url("../artwork/2007_button_on.png")';
    }

    function buttonover(buttonID) {
      if (document.getElementById('button_'+buttonID).style.backgroundImage != 'url("../artwork/2007_button_on.png")') {
        document.getElementById('button_'+buttonID).style.backgroundImage='url("../artwork/2007_button_over.png")';
      }
    }

    function buttonout(buttonID) {
      
      if (document.getElementById('button_'+buttonID).style.backgroundImage != 'url("../artwork/2007_button_on.png")') {
        document.getElementById('button_'+buttonID).style.backgroundImage='';
      }
    }

    function dateCopy(dropdownID) {
      if (document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '2' || document.edit_form.paper_type.options[document.edit_form.paper_type.selectedIndex].value == '4') {
        switch(dropdownID) {
          case "fday":
            document.edit_form.tday.value = document.edit_form.fday.options[document.edit_form.fday.selectedIndex].value;
            break;
          case "fmonth":
            document.edit_form.tmonth.value = document.edit_form.fmonth.options[document.edit_form.fmonth.selectedIndex].value;
            break;
          case "fyear":
            document.edit_form.tyear.value = document.edit_form.fyear.options[document.edit_form.fyear.selectedIndex].value;
            break;
          case "tday":
            document.edit_form.fday.value = document.edit_form.tday.options[document.edit_form.tday.selectedIndex].value;
            break;
          case "tmonth":
            document.edit_form.fmonth.value = document.edit_form.tmonth.options[document.edit_form.tmonth.selectedIndex].value;
            break;
          case "tyear":
            document.edit_form.fyear.value = document.edit_form.tyear.options[document.edit_form.tyear.selectedIndex].value;
            break;
        }
      }
    }
  </script>
</head>
<body onload="window.focus()" onclick="hidePicker();">
<form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php
  require '../tools/colour_picker/colour_picker.inc';
?>
<table border="0" cellpadding="1" cellspacing="5" style="width:100%; height:645px; font-size:90%">
<tr><td valign="top" style="background-color:white; border:1px solid #7F9DB9; width:120px">

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:120px">
<tr><td id="button_general" style="background-image:url('../artwork/2007_button_on.png'); height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('general')" onmouseout="buttonout('general')" onclick="buttonclick('general')">&nbsp;<?php echo $string['generaltab']; ?></td></tr>
<tr><td id="button_security" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('security')" onmouseout="buttonout('security')" onclick="buttonclick('security')">&nbsp;<?php echo $string['securitytab']; ?></td></tr>
<tr><td id="button_reviewers" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('reviewers')" onmouseout="buttonout('reviewers')" onclick="buttonclick('reviewers')">&nbsp;<?php echo $string['reviewerstab']; ?></td></tr>
<?php
if ($paper_type != '4' and $paper_type != '5') {
?>
<tr><td id="button_rubric" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('rubric')" onmouseout="buttonout('rubric')" onclick="buttonclick('rubric')">&nbsp;<?php echo $string['rubrictab']; ?></td></tr>
<tr><td id="button_prologue" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('prologue')" onmouseout="buttonout('prologue')" onclick="buttonclick('prologue')">&nbsp;<?php echo $string['prologuetab']; ?></td></tr>
<tr><td id="button_postscript" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('postscript')" onmouseout="buttonout('postscript')" onclick="buttonclick('postscript')">&nbsp;<?php echo $string['postscripttab']; ?></td></tr>
<?php
}
?>
</table>

</td>

<td style="background-color:white; border:1px solid #7F9DB9" valign="top">

<table id="general" style="height:590px; width:100%; font-size:90%<?php if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') echo ';display:none'; ?>" cellpadding="0" cellspacing="0" border="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/general_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['generalheading']; ?></td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom: 1px solid #CFDBEB\">&nbsp;" . $string['paperdetails'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     if ($paper_type == '2') {
       echo "<tr><td align=\"right\" valign=\"top\">" . $string['url'] . "&nbsp;</td><td colspan=\"3\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "\" target=\"_blank\" style=\"color:blue\">" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "</a> " . $string['onlyonexamday'] . "</td></tr>\n";
     } elseif ($paper_type == '4') {
       echo "<tr><td align=\"right\" valign=\"top\">" . $string['url'] . "&nbsp;</td><td colspan=\"3\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/osce/\" target=\"_blank\" style=\"color:blue\">" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/osce/</a> " . $string['onlyonexamday'] . "</td></tr>\n";
     } else {
       echo "<tr><td align=\"right\" valign=\"top\">" . $string['url'] . "&nbsp;</td><td colspan=\"3\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/user_index.php?id=" . urlencode($crypt_name) ."\" target=\"_blank\" style=\"color:blue\">" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/user_index.php?id=" . urlencode($crypt_name) ."</a></td></tr>\n";
     }
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['name'] . "&nbsp;</td><td colspan=\"3\"><input type=\"text\" size=\"75\" maxlength=\"255\" value=\"$paper_title\" name=\"paper_title\" /><input type=\"hidden\" name=\"original_paper_title\" value=\"$paper_title\"><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\"></td></tr>\n";
   ?>
     <tr><td align="right" valign="top"><?php echo $string['type']; ?>&nbsp;</td><td>
     <select name="paper_type" onclick="changeType();">
     <option value="0"<?php if ($paper_type == '0') echo ' selected'; ?> /><?php echo $string['formative self-assessment']; ?></option>
     <option value="1"<?php if ($paper_type == '1') echo ' selected'; ?> /><?php echo $string['progress test']; ?></option>
     <option value="2"<?php if ($paper_type == '2') echo ' selected'; ?> /><?php echo $string['summative exam']; ?></option>
     <option value="3"<?php if ($paper_type == '3') echo ' selected'; ?> /><?php echo $string['survey']; ?></option>
     <option value="4"<?php if ($paper_type == '4') echo ' selected'; ?> /><?php echo $string['osce station']; ?></option>
     <option value="5"<?php if ($paper_type == '5') echo ' selected'; ?> /><?php echo $string['offline paper']; ?></option>
   <?php
     echo "<td align=\"right\" valign=\"top\">" . $string['folder'] . "&nbsp;</td><td valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\">\n";
     echo "<option value=\"\"></option>";
     $additional = '';
     
     $team_query = $mysqli->prepare("SELECT DISTINCT name FROM teams WHERE memberID=? ORDER BY name");
     $team_query->bind_param('s', $userID);
     $team_query->execute();
     $team_query->store_result();
     $team_query->bind_result($team_name);
     while ($row = $team_query->fetch()) {
       if ($team_name != '') {
         if ($additional == '') {
           $additional = ' OR team_name IN ("' . $team_name . '"';
         } else {
           $additional .= ',"' . $team_name . '"';
         }
       }
     }
     $team_query->close();
     
     if ($additional != '') $additional .= ')';
     if ($folder != '') $additional .= ' OR id=' . $folder;
     
     $folder_details = $mysqli->prepare("SELECT id, name FROM folders WHERE ownerID=? $additional ORDER BY name");
     $folder_details->bind_param('s', $userID);
     $folder_details->execute();
     $folder_details->bind_result($folder_id, $folder_name);
     while ($folder_details->fetch()) {
       $path_parts = substr_count($folder_name,';');
       $folder_array = explode(';',$folder_name);
       $display_name = str_repeat('&nbsp;',$path_parts * 4) . $folder_array[$path_parts];
       if ($folder == $folder_id) {
         echo "<option value=\"" . $folder_id . "\" selected>" . $display_name . "</option>";
       } else {
         echo "<option value=\"" . $folder_id . "\">" . $display_name . "</option>";
       }
     }
     $folder_details->close();
     echo "</select>\n</td></tr>\n";
     
     echo "<tr><td align=\"right\" valign=\"top\">";
     if ($paper_type != '4') echo $string['feedback'] . '&nbsp';
     echo "</td><td colspan=\"3\">";
     if (in_array($paper_type, array('0', '1', '2', '5'))) {
       // Objectives-based Feedback
       $idfeedback_release = '';
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id=? AND type='objectives'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->bind_result($idfeedback_release);
       $feedback_details->fetch();
       if ($idfeedback_release == '') {
         echo "<div><input type=\"checkbox\" value=\"1\" name=\"objectives_report\" />";
       } else {
         echo "<div><input type=\"checkbox\" value=\"1\" name=\"objectives_report\" checked />";
       }
       $feedback_details->close();
     
       echo $string['objectivesreport'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=$crypt_name\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=$crypt_name</a></div>\n";
     }
     if (in_array($paper_type, array('1', '2', '5'))) {
       // Question-based Feedback
       $idfeedback_release = '';
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id=? AND type='questions'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->bind_result($idfeedback_release);
       $feedback_details->fetch();
       if ($idfeedback_release == '') {
         echo "<br /><div><input type=\"checkbox\" value=\"1\" name=\"questions_report\" />";
       } else {
         echo "<br /><div><input type=\"checkbox\" value=\"1\" name=\"questions_report\" checked />";
       }
       $feedback_details->close();
     
       echo $string['questionfeedback'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=$crypt_name\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=$crypt_name</a></div>\n";
     }
     if ($paper_type == '0') {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="width:100%">';
     } else {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="width:100%; display:none">';
     }
     if ($paper_type != '4') {
     ?>
     <tr><td colspan="4">&nbsp;</td></tr>
     <tr><td style="width:33%"><input type="checkbox" name="display_students_response" value="1"<?php if ($display_students_response == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['ticks_crosses'];?></td><td style="width:33%"><input type="checkbox" name="display_question_mark" value="1"<?php if ($display_question_mark == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['question_marks'];?></td><td rowspan="2" style="width:33%; text-indent:-24px; padding-left:24px"><input type="checkbox" name="hide_if_unanswered" value="1"<?php if ($hide_if_unanswered == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['hideallfeedback'];?></td></tr>
     <tr><td><input type="checkbox" name="display_correct_answer" value="1"<?php if ($display_correct_answer == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['correctanswerhighlight'];?></td><td><input type="checkbox" name="display_feedback" value="1"<?php if ($display_feedback == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['textfeedback'];?></td></tr>
     <?php
     }
     echo "</table>\n";
     if ($paper_type != '0') {
       echo '<div id="feedback_off">';
     } else {
       echo '<div id="feedback_off" style="display:none">';
     }
     echo "<br />&nbsp;</div>";
     
     echo "<tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";     
     if ($paper_type == '4') {
       echo '<input type="hidden" name="bgcolor" value="' . $bgcolor . '" />';
       echo '<input type="hidden" name="fgcolor" value="' . $fgcolor . '" />';
       echo '<input type="hidden" name="themecolor" value="' . $themecolor . '" />';
       echo '<input type="hidden" name="labelcolor" value="' . $labelcolor . '" />';
     } else {
       echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA;color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['displayoptions'] ."</td></tr>\n";
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
       if ($fullscreen == 0) {
         echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\">\n<option value=\"0\" selected>" . $string['windowed'] ."</option><option value=\"1\">" . $string['fullscreen'] ."</option>\n</select></td>";
       } else {
         echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\">\n<option value=\"0\">" . $string['windowed'] ."</option><option value=\"1\" selected>" . $string['fullscreen'] ."</option>\n</select></td>";
       }
       if ($bidirectional == 1) {
         echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"><option value=\"0\">" . $string['unidirectional'] ."</option><option value=\"1\"selected>" . $string['bidirectional'] ."</option></select></td></tr>\n";
       } else {
         echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"><option value=\"0\" selected>" . $string['unidirectional'] ."</option><option value=\"1\">" . $string['bidirectional'] ."</option></select></td></tr>\n";
       }
       
       echo "<tr>\n";
       echo "<td align=\"right\">" . $string['background'] . "&nbsp;</td><td><div onclick=\"showPicker('bgcolor',event)\" id=\"span_bgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$bgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"bgcolor\" name=\"bgcolor\" value=\"$bgcolor\" /></td>";
       echo "<td align=\"right\">" . $string['foreground'] . "&nbsp;</td><td><div onclick=\"showPicker('fgcolor',event)\" id=\"span_fgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$fgcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"fgcolor\" name=\"fgcolor\" value=\"$fgcolor\" /></td>";
       echo "</tr>\n";
   
       echo "<tr>\n";
       echo "<td align=\"right\">" . $string['theme'] . "&nbsp;</td><td><div onclick=\"showPicker('themecolor',event)\" id=\"span_themecolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" /></td>";
       echo "<td align=\"right\">" . $string['labelsnotes'] . "&nbsp;</td><td><div onclick=\"showPicker('labelcolor',event)\" id=\"span_labelcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" /></td>";
       echo "</tr>\n";

       echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><select name=\"calculator\">\n";
       for ($calcloop=-1;$calcloop<3;$calcloop++) {
         echo "<option value=\"$calcloop\"";
         if ($calcloop == $calculator) {
           echo " selected";
         }
         echo ">";
         echo $string["displaycalculator$calcloop"];
         echo "</option>\n";
       }
       echo "</select></td>\n";
/*
       if ($calculator == 1) {
         echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" checked /> " . $string['displaycalculator'] . "</td>";
       } else {
         echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"calculator\" /> " . $string['displaycalculator'] . "</td>";
       }
*/
       if ($sound_demo == 1) {
         echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" checked /> " . $string['demosoundclip'] . "</td></tr>\n";
       } else {
         echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" name=\"sound_demo\" /> " . $string['demosoundclip'] . "</td></tr>\n";
       }
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     }
     echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['marking'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     if ($paper_type == '4') {
       echo "<tr><td align=\"right\" valign=\"top\">Overall&nbsp;Classification&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\">";
    ?>
      <option value="5"<?php if ($marking == '5') echo ' selected'; ?> />&lt;Automatic&gt;</option>
      <option value="3"<?php if ($marking == '3') echo ' selected'; ?> />Clear Fail | Borderline | Clear Pass</option>
      <option value="4"<?php if ($marking == '4') echo ' selected'; ?> />Fail | Borderline fail | Borderline pass | Pass | Good pass</option>
      <option value="6"<?php if ($marking == '6') echo ' selected'; ?> />Clear FAIL | BORDERLINE | Clear PASS | Honours PASS</option>
  <?php
    echo "<tr><td colspan=\"4\">" . wysiwyg_editor('oEdit1','osce_marking_guidance',$paper_prologue,684,230);
  ?>
</td></tr>
    <?php
      echo "</select></td></tr>\n";
    } else {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\"";
      if ($paper_type == '3') echo ' disabled';
      echo '>';
      for ($i=0; $i<=100; $i++) {
        if ($i == $pass_mark) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td><td rowspan=\"2\" style=\"text-align:right\" valign=\"top\">" . $string['method'] . "&nbsp;</td><td rowspan=\"2\">";
    ?>
       <input type="radio" id="marking1" name="marking" value="0"<?php if ($marking == '0') echo ' checked'; ?> /><?php echo $string['noadjustment']; ?><br />
       <input type="radio" id="marking2" name="marking" value="1"<?php
       if ($marking == '1') {
          echo ' checked';
       }
       if ($neg_marking) {
        echo ' disabled';
       }
       if ($neg_marking) {
         echo '><span style="color:#808080">' . $string['calculatrrandommark'] . '</span><br />';
       } else {
        echo '>' . $string['calculatrrandommark'] . '<br />';
       }
     
      // Look for any Standard Setting reviews for the paper.
      $std_set_details = $mysqli->prepare("SELECT DISTINCT title, surname, initials, setterID, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, group_review FROM standards_setting, users WHERE standards_setting.setterID=users.id AND paperID=? ORDER BY std_set DESC");
      $std_set_details->bind_param('i', $_GET['paperID']);
      $std_set_details->execute();
      $std_set_details->store_result();
      if ($std_set_details->num_rows > 0) {
        echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"2\"";
        if (substr($marking,0,1) == '2') echo ' checked';
        echo " />";
        echo $string['stdset'] . ' <select name="std_set">';
        $std_set_details->bind_result($std_set_title, $std_set_surname, $std_set_initials, $std_set_reviewer, $std_set_display_date, $std_set_date, $group_review);
        while ($std_set_details->fetch()) {
          if ($group_review == 'No') {
            if ($marking = "2,$std_set_reviewer,$std_set_date") {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\" selected>$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
            } else {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\">$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
            }
          } else {
            if ($marking == "2,$std_set_reviewer,$std_set_date") {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\" selected>Group Review - $std_set_display_date</option>";
            } else {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\">Group Review - $std_set_display_date</option>";
            }
          }
        }
        echo "</select>\n";
        $std_set_details->close();
      } else {
        echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"2\" disabled />";
        echo '<span style="color:#808080">' . $string['stdset'] . '</span>';
      }
    }
    if ($paper_type == '0' or $paper_type == '1' or $paper_type == '2') {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['distinction'] . "</td><td><select name=\"distinction_mark\">";
      for ($i=0; $i<=100; $i++) {
        if ($i == $distinction_mark) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td></tr>\n";
    } else {
      echo "<tr><td></td><td></td></tr>\n";
    }
   ?>
   </table>
</td>
</tr>
</table>

<table id="prologue" style="width:100%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/prologue_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['prologueheading']; ?></td></tr>
  <?php
    echo "<tr><td>" . wysiwyg_editor('oEdit2','paper_prologue',$paper_prologue,688,498);
  ?>
</td></tr>
</table>

<table id="postscript" style="width:100%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/postscript_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['postscriptheading']; ?></td></tr>
<?php
    echo "<tr><td>" . wysiwyg_editor('oEdit3','paper_postscript',$paper_postscript,688,498);
  ?>
</td></tr>
</table>

<?php
  if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
    echo '<table id="security" style="width:100%; font-size:90%; height:590px; display:block" border="0" cellpadding="0" cellspacing="0">';
  } else {
    echo '<table id="security" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">';
  }
?>
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/security_heading_icon.png" width="30" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['securityheading']; ?></td></tr>
<tr>
<td style="text-align:center; vertical-align:top" colspan="2">
<?php
    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" style=\"width:100%; padding-bottom:10px\">\n";
    echo "<tr><td align=\"right\">" . $string['session'] . "</td><td><select name=\"calendar_year\" id=\"session\" onchange=\"getMetadataDropdowns();\">\n<option value=\"\">" . $string['na'] .  "</option>\n";
    $academic_years = array('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16');
    foreach ($academic_years as $value) {
      echo "<option value=\"" . $value . "\"";
      if ($calendar_year == $value) echo 'selected';
      echo ">";
      echo $value . "</option>\n";
    }    
    echo "</select></td><td align=\"right\">" . $string['password'] . "</td><td><input type=\"text\" size=\"20\" name=\"password\" value=\"$password\" /></td></tr>\n";

    echo "<tr><td align=\"right\">" . $string['timezone'] .  "</td><td><select name=\"timezone\">";
    foreach ($timezone_array as $individual_zone => $display_zone) {
      if ($timezone == $individual_zone) {
        echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
      } else {
        echo "<option value=\"$individual_zone\">$display_zone</option>";
      }
    }
    echo '</select></td>';
    echo '<td align="right">' . $string['duration'] . '</td><td><select name="exam_duration">';
    $minutes = array('NULL'=>$string['na'],'15'=>'15','20'=>'20','25'=>'25','30'=>'30','35'=>'35','40'=>'40','45'=>'45','50'=>'50','55'=>'55','60'=>'60','65'=>'65','70'=>'70','75'=>'75','80'=>'80','85'=>'85','90'=>'90','95'=>'95','100'=>'100','110'=>'110','120'=>'120','150'=>'150','180'=>'180');
    foreach ($minutes as $key => $value) {
      echo "<option value=\"" . $key . "\"";
      if ($exam_duration == $key) echo 'selected="selected"';
      echo ">";
      echo $value . "</option>\n";
    }
    echo "</select> " . $string['mins'] . "</td></tr>\n";
    echo "<tr><td align=\"right\" valign=\"top\">" . $string['availablefrom'] . "</td><td>";

    // Split the start date
    $split_year = substr($start_date,0,4);
    $split_month = substr($start_date,5,2);
    $split_day = substr($start_date,8,2);
    $split_hour = substr($start_date,11,2);
    $split_minute = substr($start_date,14,2);

     // Available from Day
    echo "<select name=\"fday\" onchange=\"dateCopy('fday')\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
   // Available from Month
    echo "<select name=\"fmonth\" onchange=\"dateCopy('fmonth')\">\n";
    $months = array('january','february','march','april','may','june','july','august','september','october','november','december');
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available from Year
    echo "<select name=\"fyear\" onchange=\"dateCopy('fyear')\">\n";
    for ($i = 2002; $i < 2021; $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>\n<select name=\"ftime\">\n";
    // Available from Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    echo "</select>\n</td>\n";

    // Split the end date
    $split_year = substr($end_date,0,4);
    $split_month = substr($end_date,5,2);
    $split_day = substr($end_date,8,2);
    $split_hour = substr($end_date,11,2);
    $split_minute = substr($end_date,14,2);

    echo "<td align=\"right\">" . $string['to'] . "&nbsp;</td><td>";
    
     // Available from Day
    echo "<select name=\"tday\" onchange=\"dateCopy('tday')\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";

    // Available to Month
    echo "<select name=\"tmonth\" onchange=\"dateCopy('tmonth')\">\n";
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available to Year
    echo "<select name=\"tyear\" onchange=\"dateCopy('tyear')\">\n";
    for ($i = 2002; $i < (date('Y')+21); $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>&nbsp;<select name=\"ttime\">\n";
    // Available to Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    echo "</select>\n</td></tr>\n";
    echo "</table>\n";

    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px; width:400px\">&nbsp;" . $string['modules'] . "</td><td style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px\">&nbsp;" . $string['restricttolabs'] . "</td></tr>";
    echo "<tr><td rowspan=\"3\" style=\"vertical-align:top\">";
    
    echo "<div style=\"display:block; width:400px; height:425px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
    
    $modules_array = explode(',',$moduleID);
    $total_modules = array_merge($teams, $modules_array);
    
    $module_sql = implode("','", $total_modules);
    if ($module_sql != '') $module_sql = "'$module_sql'";
    
    if ($module_sql == '') {
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"0\" /></div>\n";
    } else {
      $module_array = SearchUtils::getTeams($teams, $userroles, $userID, $mysqli);
      $module_no = 0;
      $old_school = '';
      foreach ($module_array as $module) {
        if ($module['school'] != $old_school) {
          echo "<div style=\"padding-top:2px\"><strong>" . $module['school'] . "</strong></div>";
        }
        $match = false;
        foreach ($modules_array as $separate_module) {
          if ($separate_module == $module['id']) $match = true;
        }
        if ($match == true) {
          if (in_array($module['id'],$teams) or strpos($userroles,'SysAdmin') !== false) {
            echo "<div class=\"indenton\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMetadataDropdowns();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['id'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
          } else {
            echo "<div class=\"indenton\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['id'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
          }
        } else {
          echo "<div class=\"indentoff\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMetadataDropdowns();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['id'] . "\">&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
        }
        $module_no++;  
        $old_school = $module['school'];        
      }
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
    }
    echo "</td>\n";
    
    echo "<td><div style=\"height:278px; overflow-y:scroll;border:1px solid #7F9DB9; font-size:90%\">";
    $current_labs = explode(',',$labs);
    
    $lab_details = $mysqli->prepare("SELECT labs.id, name, campus, COUNT(ip_addresses.id) FROM labs, ip_addresses WHERE labs.id=ip_addresses.lab GROUP BY ip_addresses.lab ORDER BY campus, name");
    $lab_details->execute();
    $lab_details->bind_result($lab_id, $lab_name, $lab_campus, $computer_no);
    $lab_no = 0;
    $old_campus = '';
    while ($row = $lab_details->fetch()) {
      if ($old_campus != $lab_campus) {
        echo "<div><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" />&nbsp;<strong>$lab_campus</strong></div>\n";
      }
      $match = false;
      foreach ($current_labs as $individual_lab) {
        if ($lab_id == $individual_lab) $match = true;
      }
      if ($match) {
        echo "<div class=\"indenton\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\" onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" value=\"$lab_id\" checked>&nbsp;$lab_name <span style=\"color:#808080\">($computer_no)</span></div>\n";
      } else {
        echo "<div class=\"indentoff\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\" onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" value=\"$lab_id\">&nbsp;$lab_name <span style=\"color:#808080\">($computer_no)</span></div>\n";
      }
      $lab_no++;
      $old_campus = $lab_campus;
    }
    $lab_details->close();
    echo "<input type=\"hidden\" name=\"lab_no\" value=\"$lab_no\" /></div></td>\n</tr>";
    
    //echo "</table>\n";
  ?>
  </td></tr>
  <tr><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px" colspan="2">&nbsp;<?php echo $string['restricttometadata']; ?></td></tr>
  <tr><td style="vertical-align:top; height:110px" colspan="2"><div style="height:116px; overflow-y:scroll;border:1px solid #7F9DB9; font-size:90%" id="metadata_security"></div></td></tr>
  </table>
  </td></tr>
</table>

<table id="rubric" style="width:100%; font-size:90%; height:460px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/rubric_heading_icon.png" width="34" height="34" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['rubricheading']; ?></td></tr>
  <?php
    echo "<tr><td>" . wysiwyg_editor('oEdit4','rubric_text',$rubric,686,498);
  ?>
  </td></tr>
</table>

<table id="reviewers" style="width:100%; font-size:90%; height:460px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/reviewers_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['reviewersheading']; ?></td></tr>
<tr>
<td align="center" colspan="2">
<table cellpadding="2" cellspacing="2" border="0" style="width:100%">
<tr><td colspan="3">&nbsp;<?php
  $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='sct'");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($sct_no);
  $result->fetch();
  $result->close();
  if ($sct_no > 0) {
    echo '<a href="' . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . '/reviews/sct_login.php?paperID=' . $_GET['paperID'] . '" target="_blank" style="color:blue">' . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . '/reviews/sct_login.php?paperID=' . $_GET['paperID'] . '</a>';
  }

?></td></tr>
<tr><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB">&nbsp;<?php echo $string['internalreviewers']; ?></td><td>&nbsp;&nbsp;</td><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB">&nbsp;<?php echo $string['externalexaminers']; ?></td></tr>
<tr><td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
    $split_year = substr($internal_review_deadline,0,4);
    $split_month = substr($internal_review_deadline,5,2);
    $split_day = substr($internal_review_deadline,8,2);

    // Available to Day
    echo "<select name=\"int_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // Available to Month
    echo "<select name=\"int_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
     // Available to Year
     echo "<select name=\"int_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
     if ($split_year < date('Y') and $split_year > 1999) {
       $start_year = $split_year;
     } else {
       $start_year = date('Y');
     }
     for ($i = $start_year; $i < (date('Y')+2); $i++) {
       if ($i == $split_year) {
         echo "<option value=\"$i\" selected>$i</option>\n";
       } else {
         echo "<option value=\"$i\">$i</option>\n";
       }
     }
?>
</td><td></td>
<td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
    $split_year = substr($external_review_deadline,0,4);
    $split_month = substr($external_review_deadline,5,2);
    $split_day = substr($external_review_deadline,8,2);

    // Available to Day
    echo "<select name=\"ext_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
           echo "<option value=\"0$i\">";
         }
       } else {
         if ($i == $split_day) {
           echo "<option value=\"$i\" selected>";
         } else {
           echo "<option value=\"$i\">";
         }
       }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // Available to Month
    echo "<select name=\"ext_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
    for ($i=0; $i<12; $i++) {
      $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>$trans_month</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">$trans_month</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">$trans_month</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available to Year
    echo "<select name=\"ext_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
    if ($split_year < date('Y') and $split_year > 1999) {
      $start_year = $split_year;
    } else {
      $start_year = date('Y');
    }
    for ($i = $start_year; $i < (date('Y')+2); $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
?>
</td></tr>
  <?php
  echo "<tr><td><div style=\"width:345px; height:450px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $current_internals = explode(',',$internal_reviewers);
  $internal_details = $mysqli->prepare("SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE roles LIKE 'Staff%' AND grade != 'left' ORDER BY surname, initials");
  $internal_details->execute();
  $internal_details->bind_result($internal_id, $internal_title, $internal_initials, $internal_surname, $internal_first_names);
  $internal_no = 0;
  while ($row = $internal_details->fetch()) {
    $match = false;
    foreach ($current_internals as $individual_internal) {
      if ($internal_id == $individual_internal) $match = true;
    }
    if ($match) {
      echo "<div class=\"indenton\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\" checked>&nbsp;" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></div>\n";
    } else {
      echo "<div class=\"indentoff\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\">&nbsp;" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></div>\n";
    }
    $internal_no++;
  }
  $internal_details->close();
  echo "<input type=\"hidden\" name=\"internal_no\" value=\"$internal_no\" /></div></td><td></td>";

  echo "<td><div style=\"width:345px; height:450px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $current_externals = explode(',',$externals);
  $external_details = $mysqli->prepare("SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE roles='External Examiner' AND grade != 'left' ORDER BY surname, initials");
  $external_details->execute();
  $external_details->bind_result($external_id, $external_title, $external_initials, $external_surname, $external_first_names);
  $examiner_no = 0;
  while ($row = $external_details->fetch()) {
    $match = false;
    foreach ($current_externals as $individual_external) {
      if ($external_id == $individual_external) $match = true;
    }
    if ($match) {
      echo "<div class=\"indenton\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\" checked>&nbsp;" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></div>\n";
    } else {
      echo "<div class=\"indentoff\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\">&nbsp;" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></div>\n";
    }
    $examiner_no++;
  }
  $external_details->close();
  echo "<input type=\"hidden\" name=\"examiner_no\" id=\"examiner_no\" value=\"$examiner_no\" /></div></td>\n</tr>\n";
  ?>
</table>
</td>
</tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" style="width:100px" name="Submit" value="<?php echo $string['ok']; ?>">&nbsp;<input type="button" name="home" style="width:100px" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></td></tr>
</table>

<input type="hidden" name="noadd" value="<?php if (isset($_GET['noadd'])) echo $_GET['noadd']; ?>" />
</form>
<?php
  }
$mysqli->close();
?>
</body>
</html>
