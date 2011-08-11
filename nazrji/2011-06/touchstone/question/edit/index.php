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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// TODO: handle keyword based and random
// TODO: validation in JS
// TODO: replace comment OK etc. icons with CSS BG image?
// TODO: disable mapping tab for info and likert

require '../../include/staff_auth.inc';
require_once '../../classes/question.class.php';
require_once '../../classes/logger.class.php';
require '../../include/edit.inc';
require '../../include/media.inc';
require '../../include/metadata.inc';

$question = null;
$logger = new Logger($mysqli);
$paper_id = (empty($_GET['paper_id'])) ? -1 : $_GET['paper_id'];
$module = (empty($_GET['module'])) ? '' : $_GET['module'];

$critical_error = '';

$q_no = (empty($_GET['q_no'])) ? '' : $_GET['q_no'];
$q_type = '';
$q_type_full = '';
 
if(empty($_REQUEST['q_id'])) {
  // We're adding a new question
  $question = new Question($mysqli, $userID);
  
  if (empty($_GET['type'])) {
    $critical_error = 'No question type defined.';
  } elseif (!in_array($_GET['type'], array_keys(Question::$types))) {
    $critical_error = 'Unknown question type <em>' . htmlentities($_GET['type']) . '</em>.';
  } else {
    $question->set_type($_GET['type']);
  }
} else {
  // We're editing an existion question
  if($paper_id == -1) {
    $critical_error = 'No paper defined for question.';
  } elseif(!ctype_digit($paper_id)) {
    $critical_error = 'Invalid paper ID.';
  } else {
    try {
      $question = new Question($mysqli, $userID, $_REQUEST['q_id']);
    } catch (Exception $ex) {
      $critical_error = $ex->getMessage();
    }
  }
}

if($critical_error == '') {
  // Populate an array containing existing values so that we can track changes
  $part_names = $question->get_editable_fields();
  
  
  // Save data
  // TODO: Save 'Correct'
  // TODO: 'Limited Save'
  if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
  //  if ($_POST['correct'] != $_POST['old_correct']) {
  //    // Update the 'options' table with the new correct answer.
  //    $result = $mysqli->prepare("UPDATE options SET correct=? WHERE o_id=?");
  //    $result->bind_param('si', $_POST['correct'], $q_id);
  //    $result->execute();  
  //    $result->close();
  //
  //    // Record the change in 'track_changes'.
  //    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL, 'Post Exam Answer change',?,$userID,?,?,NOW(),'Correct Answer')");
  //    $result->bind_param('iss', $q_id, $_POST['old_correct'], $_POST['correct']);
  //    $result->execute();  
  //    $result->close();
  //
  //    // Remark the student's answers in 'log2'.
  //    $result = $mysqli->prepare("SELECT DISTINCT user_answer FROM log2 WHERE q_id=? AND q_paper=?");
  //    $result->bind_param('ii', $q_id, $_POST['paperID']);
  //    $result->execute();  
  //    $result->store_result();
  //    $result->bind_result($user_answer);
  //    while ($row = $result->fetch()) {
  //      if ($user_answer == $_POST['correct']) {
  //        $updateLog = $mysqli->prepare("UPDATE log2 SET mark=1 WHERE user_answer=? AND q_id=? AND q_paper=?");
  //        $updateLog->bind_param('sii', $user_answer, $q_id, $_POST['paperID']);
  //        $updateLog->execute();  
  //        $updateLog->close();
  //      } else {
  //        $updateLog = $mysqli->prepare("UPDATE log2 SET mark=0 WHERE user_answer=? AND q_id=? AND q_paper=?");
  //        $updateLog->bind_param('sii', $user_answer, $q_id, $_POST['paperID']);
  //        $updateLog->execute();  
  //        $updateLog->close();
  //      }
  //    }
  //    $result->free_result();
  //    $result->close();
  //  }
  //  redirect();
  } elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
    if ($question->id == -1 or check_fullSave($question->id,$mysqli)) {
      // Write out curriculum mapping.
  //    saveObjMappings($_POST['paperID'],$q_id,$mysqli);
  //
      $changes = false;
      
      $part_names = $question->get_editable_fields();
      foreach($part_names as $section_name) {
        if(isset($_POST["$section_name"])) {
          $method = "set_$section_name";
          $question->$method($_POST["$section_name"]);
        }
      }
      
      // Strip MS Office HTML.
      $question->set_scenario(clearMSOtags($question->get_scenario()));
      $question->set_leadin(clearMSOtags($question->get_leadin()));
   
      // Handle changes in media
      $old_media = $question->get_media();
      if ($_FILES['q_media']['name'] != $old_media['filename'] and ($_FILES['q_media']['name'] != 'none' and $_FILES['q_media']['name'] != '')) {
        if ($old_media['filename'] != '') {
          deleteMedia($old_media['filename']);
        }
        $question->set_media(uploadFile('q_media'));
      } else {
        // Delete existing media if asked
        if (isset($_POST['delete_media0']) AND $_POST['delete_media0'] == 'on') {
          deleteMedia($old_media['filename']);
          $question->set_media(array('filename' => '', 'width' => 0, 'height' => 0));
        }
      }
      

      // TODO: check usage of old saveKeywords function - USED IN LIMITED SAVE FUNCTION
      save_keywords($question, $userID, true, $mysqli);
      
      // TODO: check usage of old getTeams function - USED IN LIMITED SAVE FUNCTION
      if (isset($_POST['teams'])) {
        $question->set_teams($_POST['teams']);
      }
      
      for ($option_no = 1; $option_no < $question->max_options; $option_no++) {
        $option = null;
        
        if (isset($_POST["optionid$option_no"]) and $_POST["optionid$option_no"] != -1) {
          // Editing existing option
          $option = $question->options[$_POST["optionid$option_no"]];
          
          // Save individual fields
          $part_names = $option->get_editable_fields();
          foreach ($part_names as $section_name) {
            $field = ($section_name != 'correct') ? 'option_' . $section_name . $option_no : 'correct';
            if (isset($_POST[$field])) {
              $method = "set_$section_name";
              $option->$method($_POST[$field]);
            }
          }
          
          // Save fields that are the same across options
          $part_names = $option->get_unified_fields();
          foreach ($part_names as $section_name => $section_label) {
            $field = 'option_' . $section_name;
            $get_method = "get_$section_name";
            $old_value = $option->$get_method();
            if (isset($_POST[$field]) and $_POST[$field] != $old_value) {
              $set_method = "set_$section_name";
              $option->$set_method($_POST[$field]);
              $question->add_unified_field_modification($section_name, $section_label, $old_value, $_POST[$field]);
            }
          }
        } else {
          // Create new option if have text or media
          if (!empty($_POST["option_text$option_no"]) or ($_FILES["option_media$option_no"]['name'] != 'none' and $_FILES["option_media$option_no"]['name'] != '')) {
            $correct_fb = (isset($_POST["option_correct_fback$option_no"])) ? $_POST["option_correct_fback$option_no"] : '';
            $incorrect_fb = (isset($_POST["option_incorrect_fback$option_no"])) ? $_POST["option_incorrect_fback$option_no"] : '';
            $data = array('question_id' => $question->id, 'text' => $_POST["option_text$option_no"], 'correct_fback' => $correct_fb, 'incorrect_fback' => $incorrect_fb, 'correct' => $_POST['option_correct'], 'marks' => 1);
            $option = new Option($mysqli, $userID, $data);
            $question->options[] = $option;
          }
        }
        
        if ($option != null) {
          // Handle changes in media
          $old_media = $option->get_media();
          if ($_FILES["option_media$option_no"]['name'] != $old_media['filename'] and ($_FILES["option_media$option_no"]['name'] != 'none' and $_FILES["option_media$option_no"]['name'] != '')) {
            if ($old_media['filename'] != '') {
              deleteMedia($old_media['filename']);
            }
            $option->set_media(uploadFile("option_media$option_no"));
          } else {
            // Delete existing media if asked
            if (isset($_POST["delete_media$option_no"]) AND $_POST["delete_media$option_no"] == 'on') {
              deleteMedia($old_media['filename']);
              $option->set_media(array('filename' => '', 'width' => 0, 'height' => 0));
            }
          }
        }
        
      }
  //    
  //    save_external_responses($mysqli);
  // 
  
      try {
    	  if (!$question->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
    } else {
      // Limited save
  //    do_limitedSave($q_id, $mysqli, $userID);
    }
  //  redirect();
  } elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
    redirect();
  }


  $q_type_display = (!empty($_REQUEST['q_no'])) ? ' ' . $_REQUEST['q_no'] : '';
  if ($question->get_type() != '') {
    $q_type_full = Question::$types[$question->get_type()];
    $q_type_display .= " &ndash; $q_type_full";
  }
} else {
  // Bad things have happened
  $q_type_display = '';
}

$mode = (empty($_REQUEST['q_id'])) ? 'Add' : 'Edit';

?>
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo $mode . ' Question - ' . $q_type_full .  ' ' . $cfg_install_type ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<link rel="stylesheet" href="../../css/screen.css" type="text/css" />
<link rel="stylesheet" href="../../css/add_edit_new.css" type="text/css" />

<?php echo $cfg_editor_javascript; ?>
<script type="text/javascript" src="../../javascript/staff_help.js"></script>
<script type="text/javascript" src="../../javascript/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../../javascript/jquery.addedit.js"></script>
</head>
<body>
	<div id="page-header">
		<div id="page-help">
			<a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" alt="Help" border="0" height="16" width="16" />
			</a>
		</div>
		<div id="page-header-inner">
			<h1><?php echo $mode . ' Question' . $q_type_display ?></h1>
		</div>

<?php 
if ($critical_error == '') {
?>
    <div class="tab-bar">
      <div class="tab-holder">
        <p class="question-stats">
          <strong>Created:</strong>&nbsp;12/04/2011&nbsp;&nbsp;&nbsp;&nbsp;<strong>Modified:</strong>&nbsp;02/08/2011
        </p>
        <ol class="tabs">
          <li class="on"><a href="#">Editor</a></li>
          <li><a href="#">Changes</a></li>
          <li><a href="#">Comments</a></li>
          <li><a href="#">Mapping</a></li>
        </ol>
      </div>
    </div>
<?php
}
?>
	</div>

<?php
if($critical_error != '') {
  // We have a major error so won't even display a form
?>
  <div id="major-error" class="edit-spacer">
    <div id="major-error-inner">
      <h1>Error</h1>
      <p><?php echo $critical_error ?></p>
    </div>
  </div>
<?php
} else {
  $disabled = check_edit_rights($question->id, $question->get_checkout_author_id(), $question->get_checkout_time(), $question->get_locked(), $mysqli);
  
  $query_string = '';
  if($question->id != -1) {
    $query_string = '?q_id=' . $question->id;
    $query_string .= ($q_no != '') ? '&q_no=' . $q_no : '';
    $query_string .= ($paper_id != -1) ? '&paper_id=' . $paper_id : '';
    $query_string .= ($module != '') ? '&module=' . $module : '';
  }
  
  // TODO: client side validation
?>

	<form name="edit_form" method="post" action="./<?php echo $query_string ?>" enctype="multipart/form-data">
    <div id="tabbed-content">
			<div id="editor" class="tab-area">
        
				<div class="message">
					<p>
						<span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.
					</p>
				</div>
        
<?php
if(!empty($errors)) {
?>
        <div id="errors" class="form">
          <ul>
<?php
  foreach($errors as $error) {
?>
            <li><?php echo $error ?></li>
<?php
  }
?>
          </ul>
        </div>

<?php
}
?>
        
        <div class="form">
          <h2>Question</h2>
        </div>
        
<?php 
$x = $question->get_type();
require_once '../../include/question/addedit/' . $question->get_type() . '.php'
?>

        <div class="form">
          <h2>Metadata</h2>
        </div>
        
<?php
// TODO: check usage of old echoMetadata function - SAFE TO REMOVE
echo echo_metadata($mysqli, $question, true, $module, $disabled);
?>        
        
      </div>

      <div id="changes" class="tab-area">
<?php
$changes = $question->get_changes();
// TODO: remove 'changes_tab.inc'?
echo render_changes($changes);
?>
      </div>

      <div id="comments" class="tab-area">
        <table class="data">
          <thead>
            <tr>
              <th style="width:16px"></th>
              <th style="width:14%">Reviewer</th>
              <th style="width:39%">Comments</th>
              <th style="width:5%">Action&nbsp;Taken</th>
              <th style="width:39%">Internal Response</th>
            </tr>
        </thead>
        <tbody>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr T Boswell<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action0">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid0" value="34725" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response0"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr S Crusz<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action1">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid1" value="34887" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response1"></textarea>
              </td>
            </tr>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor W Irving<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action2">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid2" value="34782" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response2"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor R James<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <select name="action3">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
                <input type="hidden" name="commentid3" value="36913" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="response3"></textarea>
              </td>
            </tr>
            <tr>
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Professor D Mack<br /><span class="note">External</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <input type="hidden" name="commentid4" value="37078" />
                <select name="action4">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
              </td>
              <td>
                <textarea cols="60" rows="3" name="response4"></textarea>
              </td>
            </tr>
            <tr class="alt">
              <td><img src="../../artwork/ok_comment.png" width="16" height="16" alt="OK" /></td>
              <td>Dr V Weston<br /><span class="note">Internal</span></td>
              <td><span class="note">No Comments</span></td>
              <td>
                <input type="hidden" name="commentid5" value="34719" />
                <select name="action5">
                  <option value="Not actioned" selected="selected">Not actioned</option>
                  <option value="Read - disagree">Read - disagree</option>
                  <option value="Read - actioned">Read - actioned</option>
                </select>
              </td>
              <td>
                <textarea rows="3" cols="60" name="response5"></textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="mapping" class="tab-area">
        <h2>SYSTEM Objectives</h2>
        
        <ul class="objectives">
          <li class="top">
            <a href="#">Set internally held objectives <span class="note">10/03/11</span></a>
            <ul class="hide">
              <li><input id="SYSTEMobj0" name="SYSTEMobj0" value="861" type="checkbox" /> <label for="SYSTEMobj0">Objective1</label></li>
              <li><input id="SYSTEMobj1" name="SYSTEMobj1" value="862" type="checkbox" /> <label for="SYSTEMobj1">Objective2</label></li>
              <li><input id="SYSTEMobj2" name="SYSTEMobj2" value="863" type="checkbox" /> <label for="SYSTEMobj2">Objective3</label></li>
              <li><input id="SYSTEMobj3" name="SYSTEMobj3" value="864" type="checkbox" /> <label for="SYSTEMobj3">Objective4</label></li>
            </ul>
          </li>
          <li class="top">
            <a href="#">Session2 Set internally held objectives <span class="note">11/03/11</span></a>
            <ul class="hide">
              <li><input id="SYSTEMobj4" name="SYSTEMobj4" value="865" type="checkbox" /> <label for="SYSTEMobj4">Session2 Objective1</label></li>
              <li><input id="SYSTEMobj5" name="SYSTEMobj5" value="866" type="checkbox" /> <label for="SYSTEMobj5">Session2 Objective2</label></li>
              <li><input id="SYSTEMobj6" name="SYSTEMobj6" value="867" type="checkbox" /> <label for="SYSTEMobj6">Session2 Objective3</label></li>
              <li><input id="SYSTEMobj7" name="SYSTEMobj7" value="868" type="checkbox" /> <label for="SYSTEMobj7">Session2 Objective4</label></li>
              <li><input id="SYSTEMobj8" name="SYSTEMobj8" value="869" type="checkbox" /> <label for="SYSTEMobj8">Session2 Objective5</label></li>
            </ul>
          </li>
        </ul>
        
        <p class="warning"><input value="-1" id="none_of_the_above" name="none_of_the_above" type="checkbox" /><label for="none_of_the_above"><strong>None of the Above</strong></label><br />Check here if the current question does not match any of the above objectives from SYSTEM.</p>
        
<?php 
// TODO: All of these need to use the dynamic value. Most come from mapping tab
?>        
        <input name="checkout_author" value="<?php echo $userID ?>" type="hidden" />
        <input id="SYSTEM_objectiveCount" name="SYSTEM_objectiveCount" value="9" type="hidden" />
        <input name="SYSTEM_session" value="2010/11" type="hidden" />
        <input name="SYSTEM_old_mappings" value="" type="hidden" />
        <input id="paper_id" name="paperID" value="<?php echo $paper_id ?>" type="hidden" />
        <input id="questionID" name="questionID" value="<?php echo $question->id ?>" type="hidden" />
        <input id="modules" name="modules" value="SYSTEM" type="hidden" />
      </div>
    </div>

    <div id="button-bar">
      <input type="hidden" name="q_id" value="<?php echo $question->id ?>" />
      <input id="submit-save" name="submit" value="Save Changes" type="submit" class="submit" />
      <input id="submit-cancel" name="submit-cancel" value="Cancel" onclick="formCancel();" type="submit" class="submit" />
    </div>
  </form>
<?php
}
?>
</body>
</html>