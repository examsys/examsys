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

// TODO: JS for convert MRQ to MCQ
// TODO: JS for changing labels for Dichotomous if score method changes
// TODO: handle keyword based and random
// TODO: check - was leadin/scenarion change tracking looking at the plain version?
// TODO: validation in JS
// TODO: replace comment OK etc. icons with CSS BG image?
// TODO: disable mapping tab for info and likert

require '../../include/staff_auth.inc';
require_once '../../classes/question.class.php';
require_once '../../classes/logger.class.php';
require_once '../../classes/viewhelper.class.php';
require '../../include/edit.inc';
require '../../include/media.inc';
require '../../include/metadata.inc';
require '../../include/mapping.inc';

$question = null;
$logger = new Logger($mysqli);
$paper_id = (empty($_GET['paper_id'])) ? -1 : $_GET['paper_id'];
$module = (empty($_GET['module'])) ? '' : $_GET['module'];

$critical_error = '';

$q_no = (empty($_GET['q_no'])) ? '' : $_GET['q_no'];
$q_type = '';
$q_type_full = '';

$errors = array();
 
if(empty($_REQUEST['q_id'])) {
  // We're adding a new question
  
  if (empty($_GET['type'])) {
    $critical_error = 'No question type defined.';
  } elseif (!in_array($_GET['type'], array_keys(Question::$types))) {
    $critical_error = 'Unknown question type <em>' . htmlentities($_GET['type']) . '</em>.';
  } else {
    try {
      $question = Question::question_factory($mysqli, $userID, $_GET['type']);
      $question->set_type($_GET['type']);
    } catch (ClassNotFoundException $ex) {
      $critical_error = $ex->getMessage();
    }
  }
} else {
  // We're editing an existion question
  if($paper_id == -1) {
    $critical_error = 'No paper defined for question.';
  } elseif(!ctype_digit($paper_id)) {
    $critical_error = 'Invalid paper ID.';
  } else {
    try {
      $question = Question::question_factory($mysqli, $userID, $_REQUEST['q_id']);
    } catch (Exception $ex) {
      $critical_error = $ex->getMessage();
    }
  }
}

if($critical_error == '') {
  // Save data
  if (isset($_POST['submit']) and $_POST['submit'] == 'Correct') {
    // TODO: check how this generalises to all question types
    $unified_part_names = $question->get_unified_fields();
    $save_individual = in_array('correct', array_keys($unified_part_names));
    
    if ($save_individual) {
      $errors = $question->update_correct($_POST['option_correct'], $paper_id);
    } else {
      $correct_answers = array();
      $i = 1;
      foreach ($question->options as $option) {
        $correct_answers[] = (isset($_POST['option_correct' . $i])) ? $_POST['option_correct' . $i] : $query_string->get_answer_negative();
        $i++;
      }
      
      $errors = $question->update_correct($correct_answers, $paper_id);
    }
  
    //  redirect();
  } elseif (isset($_POST['submit']) and ($_POST['submit'] == 'Save Changes' or $_POST['submit'] == 'Limited Save')) {
    if ($question->id == -1 or check_fullSave($question->id,$mysqli)) {
      
      $part_names = Question::get_editable_fields();
      foreach($part_names as $section_name) {
        if(isset($_POST["$section_name"])) {
          $value = $_POST["$section_name"];
          
          if ($section_name == 'score_method' and isset($_POST['other']) and $_POST['other'] == 1) $value = 'other';
          
          $method = "set_$section_name";
          $question->$method($value);
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
      

      // TODO: check usage of old getTeams function - USED IN LIMITED SAVE FUNCTION
      if (isset($_POST['teams'])) {
        $question->set_teams($_POST['teams']);
      }
      
      $part_names = Option::get_editable_fields();
  
      for ($option_no = 1; $option_no < $question->max_options; $option_no++) {
        $option = null;
        
        if (isset($_POST["optionid$option_no"]) and $_POST["optionid$option_no"] != -1) {
          // Editing existing option
          $option = $question->options[$_POST["optionid$option_no"]];
          
          // Save editable fields that aren't unified
          $unified_part_names = $question->get_unified_fields();
          foreach ($part_names as $section_name) {
            if (!in_array($section_name, array_keys($unified_part_names))) {
//            $field = ($section_name != 'correct') ? 'option_' . $section_name . $option_no : 'correct';
              $field = 'option_' . $section_name . $option_no;
              
              // If 'correct' is not a unified field then its value if not in POST is 'n'
              if ($section_name == 'correct' and !isset($_POST[$field])) $_POST[$field] = 'n';
              
              if (isset($_POST[$field])) {
                $method = "set_$section_name";
                $option->$method($_POST[$field]);
              }
            }
          }
          
          // Save fields that are the same across options
          foreach ($unified_part_names as $section_name => $section_label) {
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
            $data = array('question_id' => $question->id, 'marks' => 1);
            
            foreach ($part_names as $section_name) {
              if (!in_array($section_name, array_keys($unified_part_names))) {
                $field = 'option_' . $section_name . $option_no;
                
                // If 'correct' is not a unified field then its value if not in POST is 'n'
                if ($section_name == 'correct' and !isset($_POST[$field])) $_POST[$field] = 'n';
                
                if (isset($_POST[$field])) {
                  $data[$section_name] = $_POST[$field];
                }
              }
            }
                        
//            $data = array('question_id' => $question->id, 'text' => $_POST["option_text$option_no"], 'correct_fback' => $correct_fb, 'incorrect_fback' => $incorrect_fb, 'correct' => $_POST['option_correct'], 'marks' => 1);
            $option = new Option($mysqli, $userID, $question, $option_no, $data);
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

    } else {
      // Limited save
      $part_names = array('bloom','status');
      foreach($part_names as $section_name) {
        if(isset($_POST["$section_name"])) {
          $method = "set_$section_name";
          $question->$method($_POST["$section_name"]);
        }
      }
    }
    
    // If not errored then save the question
    if (count($errors) == 0) {
      try {
    	  if(!$question->save()) {
    	    $errors[] = 'Error saving data. Please try again';
    	  } else {
    	    // Possibility that we might be converting a MRQ to MCQ
    	    if(isset($_POST['mcqconvert']) and $_POST['mcqconvert'] == '1') {
    	      $i = 1;
    	      $correct_option = 0;
    	      foreach ($question->options as $option) {
    	        if ($option->get_correct() == 'y') {
    	          $correct_option = $i;
    	          break;
    	        }
    	        $i++;
    	      }
    	      $question = $question->convert_to_mcq($correct_option);
    	    }
    	    
        	// TODO: check usage of old saveKeywords function - USED IN LIMITED SAVE FUNCTION
          save_keywords($question, $userID, true, $mysqli);
      
    	    // TODO: check usage of old save_external_responses function - USED IN LIMITED SAVE FUNCTION
    	    if(isset($_POST['comment_ids']) and isset($_POST['actions']) and isset($_POST['responses'])) {
    	      save_external_responses_new($mysqli, $question, $_POST['comment_ids'], $_POST['actions'], $_POST['responses'], $paper_id);
    	    }
    	    
    	    if (isset($_POST['objective_modules'])) {
    	      // Write out curriculum mapping.
    	      save_objective_mappings($mysqli, $_POST['objective_modules'], $paper_id, $question->id);
    	    }
    	  }
    	} catch (ValidationException $vex) {
    	  $errors[] = $vex->getMessage();
    	}
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

echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
?>
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
  $banner_spacer = '';
  $disabled = check_edit_rights($question->id, $question->get_checkout_author_id(), $question->get_checkout_time('timestamp'), $question->get_locked(), $mysqli);

  if ($disabled != '') {
    $banner_spacer = ' class="banner-spaced"';
    
    if ($disabled == 'mscaa') {
?>
    <div class="banner mscaa">
      <p><strong>MSC-AA Question</strong> This question has been imported from the MSC-AA and cannot be modified.</p>
    </div>
<?php
    } elseif ($disabled == 'locked') {
?>
    <div class="banner">
      <p><strong>Question Locked</strong> This question is now locked and cannot be modified. <a href="#" onclick="launchHelp(161); return false;">Click for more details.</a></p>
    </div>
<?php
    }
  }
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
    <div id="tabbed-content"<?php echo $banner_spacer ?>>
			<div id="editor" class="tab-area">
        
				<div class="message">
					<p>
						<span class="mandatory">*</span> Indicates a <strong>mandatory</strong> field that must be completed.
					</p>
				</div>
        
<?php
if (count($errors) > 0) {
?>
        <div id="errors" class="form">
          <ul>
<?php
  foreach ($errors as $error) {
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
echo render_metadata($mysqli, $question, true, $module, $disabled);
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
<?php
$comments = $question->get_comments($paper_id);
// TODO: remove 'comments_tab.inc'?
echo render_comments($comments);
?>
      </div>

      <div id="mapping" class="tab-area">
<?php
// TODO: remove 'mapping_tab.inc'?
// TODO: how does it work in add? What if the question isn't on a paper?
echo render_objectives_mapping_form($mysqli, $paper_id);
?>
        
      </div>
    </div>

    <div id="button-bar">
<?php
// TODO: check old save_buttons function - SAFE TO REMOVE
echo save_buttons_new($disabled, $question->get_locked(), $userID, $question->get_checkout_author_id(), $paper_id);
// TODO: make cancel jQuery
?>
      <input type="hidden" name="q_id" value="<?php echo $question->id ?>" />
      <input name="checkout_author" value="<?php echo $userID ?>" type="hidden" />
      <input id="paper_id" name="paperID" value="<?php echo $paper_id ?>" type="hidden" />
      <input id="questionID" name="questionID" value="<?php echo $question->id ?>" type="hidden" />
    </div>
  </form>
<?php
}
?>
</body>
</html>