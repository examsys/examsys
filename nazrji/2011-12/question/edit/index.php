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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

// TODO: replace comment OK etc. icons with CSS BG image?

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
$paper_id = (!isset($_GET['paperID'])) ? -1 : $_GET['paperID'];
$module = (!isset($_GET['module'])) ? '' : $_GET['module'];
$folder = (!isset($_REQUEST['folder'])) ? '' : $_REQUEST['folder'];
$scrofy = (!isset($_REQUEST['scrOfY'])) ? '' : $_REQUEST['scrOfY'];
$calling = (!isset($_REQUEST['calling'])) ? '' : $_REQUEST['calling'];
$keyword = (!isset($_REQUEST['keyword'])) ? '' : $_REQUEST['keyword'];
$team = (!isset($_REQUEST['team'])) ? '' : $_REQUEST['team'];

$critical_error = '';

$q_no = (!isset($_GET['q_no'])) ? '' : $_GET['q_no'];
$q_type_full = '';

$errors = array();

if (!isset($_REQUEST['q_id']) or $_REQUEST['q_id'] == -1) {
  // We're adding a new question
  $mode = $string['add'];
  
  if (!isset($_GET['type'])) {
    $critical_error = $string['typeundefined'];
  } elseif (!in_array($_GET['type'], Question::$types)) {
    $critical_error = sprintf($string['typeinvalid'], htmlentities($_GET['type']));
  } else {
    try {
      $question = Question::question_factory($mysqli, $userID, $string, $_GET['type']);
      $question->set_type($_GET['type']);
      $question->set_owner_id($userID);
      if ($module != '') $question->set_teams(array($module));
    } catch (ClassNotFoundException $ex) {
      $critical_error = $ex->getMessage();
    }
  }
} else {
  // We're editing an existion question
  $mode = $string['edit'];
  
  try {
    $question = Question::question_factory($mysqli, $userID, $string, $_REQUEST['q_id']);
  } catch (Exception $ex) {
    $critical_error = $ex->getMessage();
  }
}

// Handle upload of files for question types that require it
if ($critical_error == '' and $question->requires_media() and (isset($_POST['submit_media']) or isset($_POST['q_media']))) {
  if (isset($_POST['q_media']) and $_POST['q_media'] != '') {
    $new_media['filename'] = $_POST['q_media'];
    $new_media['width'] = (isset($_POST['q_media_width']) and $_POST['q_media_width'] != '') ? $_POST['q_media_width'] : 0;
    $new_media['height'] = (isset($_POST['q_media_height']) and $_POST['q_media_height'] != '') ? $_POST['q_media_height'] : 0;
  } else {
    $new_media = uploadFile('q_media');
  }
  if ($new_media !== false) {
    $question->set_media($new_media);
  } else {
    $critical_error = $string['mediauploaderror'];
  }
  
  // Handle label images for Labelling questions. These never really hit the question object as items in their own right  
  // but are used in parameters to the Flash setup JS function
  if ($question->get_type() == 'labelling') {
    $label_images = array();
    for ($i = 1; $i <= 6; $i++) {
      $lab_media = uploadFile('label_media' . $i);
      if ($lab_media !== false) {
        $label_images[] = $lab_media;
      }
    }
  }
}

if ($critical_error == '') {  
  // Get any existing media
  $current_media = $question->get_media();
  
  $show_media_upload = false;
  $show_correction_intermediate = false;
  if ($question->requires_media() and $current_media['filename'] == '') {
    $show_media_upload = true;
  } elseif (isset($_POST['submit']) and $_POST['submit'] == $string['correct']) {
    if ($question->requires_correction_intermediate() and (!isset($_POST['corrected']) or $_POST['corrected'] != 'OK')) {
      $show_correction_intermediate = true;
    } else {
      $unified_part_names = $question->get_unified_fields();
      $save_individual = in_array('correct', array_keys($unified_part_names));
      
      if ($save_individual) {
        // calculation, mcq
        $part_names = $question->get_change_fields();
        $fields = array();
        foreach ($part_names as $field) {
          if (isset($_POST[$field])) $fields[$field] = $_POST[$field];
        }
        $errors = $question->update_correct($fields, $paper_id);
      } else {
        // dichotomous, mrq, rank, extmatch, matrix
        $first = reset($question->options);
        $compound_part_names = $first->get_compound_fields();
        
        if (is_array($compound_part_names) and in_array('correct', array_keys($compound_part_names))) {
          $loop_limit = $question->max_stems;
        } else {
          $loop_limit = count($question->options);
        }
        $part_names = $question->get_change_fields();
        $correct_answers = array();
        foreach ($part_names as $field) {
          $i = 1;
          for ($i = 1; $i <= $loop_limit; $i++) {
            $correct_answers[$field][] = (isset($_POST[$field . $i])) ? $_POST[$field . $i] : $question->get_answer_negative();
          }
        }
        
        $errors = $question->update_correct($correct_answers, $paper_id);
      }
  
      redirect();
    }
  } elseif ((isset($_POST['submit']) and ($_POST['submit'] == $string['save'] or $_POST['submit'] == $string['limitedsave'])) or isset($_POST['addbank']) or isset($_POST['addpaper'])) {
   // Save data
    if ($question->id == -1 or check_fullSave($question->id,$mysqli)) {
      
      $part_names = $question->get_editable_fields();
      $compound_fields = $question->get_compound_fields();
      $question->populate($part_names, $_POST, $compound_fields);
      
      // Handle changes in media if not a compound field
      if (!in_array('media', $question->get_compound_fields())) {
        $question->populate_media('q_media', $_FILES, $_POST);
      }
            
      // Save compound fields
      $question->populate_compound($compound_fields, $_POST, array('media'), $prefix='question_');

      // Handle changes in media for compound fields
      if (in_array('media', $compound_fields)) {
        $question->populate_compound_media($_FILES, $_POST, 'q_media', 'question_media');
      }
      
      // Strip MS Office HTML.
      $question->set_scenario(clearMSOtags($question->get_scenario()));
      $question->set_leadin(clearMSOtags($question->get_leadin()));
   

      if (isset($_POST['teams'])) {
        $question->set_teams($_POST['teams']);
      }
      
      $unified_part_names = $question->get_unified_fields();
            
      for ($option_no = 1; $option_no <= $question->max_options; $option_no++) {
        $option = null;
        
        if (isset($_POST["optionid$option_no"]) and $_POST["optionid$option_no"] != -1) {
          // Editing existing option
          $option = $question->options[$_POST["optionid$option_no"]];
          $part_names = $option->get_editable_fields();
          
          // Build arrays for compound fields
          $compound_fields = $option->get_compound_fields();
          if (!isset($existing_values)) $existing_values = array();
          $option->populate_compound(array_keys($compound_fields), $_POST, $existing_values, 'option_');
          
          // Save editable fields that aren't unified
          $option->populate($part_names, $option_no, $_POST, array_merge(array_keys($unified_part_names), array_keys($compound_fields)), 'option_');
          
          // Save fields that are the same across options
          $option->populate_unified($unified_part_names, $_POST, array_keys($compound_fields), 'option_');
        } else {
          // Create new option if have required data
          $option = Option::option_factory($mysqli, $userID, $question, $option_no, $string, array('marks' => 1));
          
          if ($option->minimum_fields_exist($_POST, $_FILES, $option_no)) {
            $correct_fb = (isset($_POST["option_correct_fback$option_no"])) ? $_POST["option_correct_fback$option_no"] : '';
            $incorrect_fb = (isset($_POST["option_incorrect_fback$option_no"])) ? $_POST["option_incorrect_fback$option_no"] : '';
            
            $part_names = $option->get_editable_fields();
            
            // Build arrays for compound fields
            $compound_fields = $option->get_compound_fields();
            if (!isset($existing_values)) $existing_values = array();
            $option->populate_compound(array_keys($compound_fields), $_POST, $existing_values, 'option_');
                                    
            // Save editable fields that aren't unified
            $option->populate($part_names, $option_no, $_POST, array_merge(array_keys($unified_part_names), array_keys($compound_fields)), 'option_');
            
            // Save fields that are the same across options
            $option->populate_unified($unified_part_names, $_POST, array_keys($compound_fields), 'option_', false);

            $question->options[] = $option;
          }
        }
        
        if ($option != null and !in_array('media', $question->get_compound_fields())) {
          // Handle changes in media
          $old_media = $option->get_media();
          if (isset($_FILES["option_media$option_no"]) and $_FILES["option_media$option_no"]['name'] != $old_media['filename'] and ($_FILES["option_media$option_no"]['name'] != 'none' and $_FILES["option_media$option_no"]['name'] != '')) {
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
    	    $errors[] = $string['datasaveerror'];
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
    	    
          // Insert into Papers
          if (isset($_POST['addpaper'])) {
            insert_into_papers($paper_id, $question->id);
          }
            	    
          save_keywords($question, $userID, true, $mysqli, $string);
      
    	    if(isset($_POST['comment_ids']) and isset($_POST['actions']) and isset($_POST['responses'])) {
    	      save_external_responses($mysqli, $question, $_POST['comment_ids'], $_POST['actions'], $_POST['responses'], $paper_id);
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
    
    if (count($errors) == 0) redirect();
  } elseif (isset($_POST['submit-cancel']) and $_POST['submit-cancel'] == $string['cancel']) {
    $question->clear_checkout();
    redirect();
  }


  $q_type_display = (!empty($_REQUEST['q_no'])) ? ' ' . $_REQUEST['q_no'] : '';
  if ($question->get_type() != '') {
    $q_type_full = $string[$question->get_type()];
    $q_type_display .= " &ndash; $q_type_full";
  }
} else {
  // Bad things have happened
  $q_type_display = '';
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo $mode . ' ' . $string['question'] . ' - ' . $q_type_full .  ' ' . $cfg_install_type ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<link rel="stylesheet" href="../../css/screen.css" type="text/css" />
<link rel="stylesheet" href="../../css/add_edit_new.css" type="text/css" />

<?php echo $cfg_editor_javascript; ?>
<script type="text/javascript" src="../../javascript/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../../javascript/staff_help.js"></script>
<script type="text/javascript" src="../../javascript/jquery.touchstone.js"></script>
<script type="text/javascript" src="../../javascript/jquery.addedit.js"></script>
<script type="text/javascript" src="../../javascript/staff_help.js"></script>
<script type="text/javascript" src="../../javascript/jquery.formhelpers.js"></script>
<?php
if ($question != null and file_exists($cfg_web_root . 'javascript/validation/jquery.' . $question->get_type() . '.js')):
?>
<script type="text/javascript" src="../../javascript/jquery.validate.min.js"></script>
<script type="text/javascript" src="../../javascript/validation/jquery.<?php echo $question->get_type() ?>.js"></script>
<?php
endif;
if ($question != null and $question->requires_flash()):
?>
<script type="text/javascript" src="../../javascript/ie_fix.js"></script>
<script type="text/javascript" src="../../javascript/flash_include.js"></script>
<?php
endif;
?>
<script type="text/javascript">
var lang = {
<?php
$langstrings = array('allowpartial', 'validationerror', 'enterleadin', 'showmore', 'hidemore', 'enteroption', 'mrqconvert', 'entervignette');
$first = true;
foreach ($langstrings as $langstring) {
  if (!$first) {
    echo ',';
  }
  echo "'{$langstring}':'{$string[$langstring]}'";
  $first = false;
}
?>
};
</script>
<script type="text/javascript" src="/tools/mee/mee/js/mee_src.js"></script>
</head>
<body>
  <div id="debug" class="debug"></div>
	<div id="page-header">
		<div id="page-help">
			<a href="#" onclick="launchHelp(1); return false;"><img src="../../artwork/small_help_icon.gif" alt="Help" border="0" height="16" width="16" />
			</a>
		</div>
		<div id="page-header-inner">
			<h1><?php echo $mode . ' ' . $string['question'] . $q_type_display ?></h1>
		</div>

<?php 
if ($critical_error == '') {
  $mapping_enabled = ($question->allow_mapping()) ? '' : ' class="disabled"';
  $creation_date = ($mode == 'Edit') ? strftime($cfg_short_date, $question->get_created('timestamp')) : strftime($cfg_short_date, time());
  $modified_date = ($question->get_last_edited('timestamp')) ? strftime($cfg_short_date, $question->get_last_edited('timestamp')) : $string['na'];
?>
    <div class="tab-bar">
      <div class="tab-holder">
        <p class="question-stats">
          <strong><?php echo $string['created'] ?></strong>&nbsp;<?php echo $creation_date ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $string['modified'] ?></strong>&nbsp;<?php echo $modified_date ?>
        </p>
        <ol class="tabs">
          <li class="on"><a href="#" rel="editor"><?php echo $string['editor'] ?></a></li>
          <li><a href="#" rel="changes"><?php echo $string['changes'] ?></a></li>
          <li><a href="#" rel="comments"><?php echo $string['comments'] ?></a></li>
          <li<?php echo $mapping_enabled ?>><a href="#" rel="mapping"><?php echo $string['mapping'] ?></a></li>
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
      <p><?php echo $string['mscaamsg'] ?></p>
    </div>
<?php
    } elseif ($disabled == 'locked') {
?>
    <div class="banner">
      <p><?php echo $string['lockedmsg'] ?></p>
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
      <h1><?php echo $string['error'] ?></h1>
      <p><?php echo $critical_error ?></p>
    </div>
  </div>
<?php
} else {
  
  $query_string = '';
  if($question->id != -1) {
    $query_string = '?q_id=' . $question->id;
  } else {
    $query_string .= '?type=' . $question->get_type();;
  }
  $query_string .= ($q_no != '') ? '&amp;q_no=' . $q_no : '';
  $query_string .= ($paper_id != -1) ? '&amp;paperID=' . $paper_id : '';
  $query_string .= ($module != '') ? '&amp;module=' . $module : '';

?>
	<form id="edit_form" name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . $query_string ?>" enctype="multipart/form-data" class="clearinput">
<?php
  if ($show_media_upload) {
    $upload_file = "../../include/question/addedit/media_upload/media_upload_{$question->get_type()}.php";
    include $upload_file;
  }
?>

    <div id="tabbed-content"<?php echo $banner_spacer ?>>
			<div id="editor" class="tab-area">
        
				<div class="message">
					<p>
						<span class="mandatory">*</span> <?php echo $string['mandatory'] ?>
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
        <div id="question-holder" class="clearfix">
          <div class="form">
            <h2><?php echo $string['question'] ?></h2>
          </div>
        
<?php 
if ($question->get_type() != '') require_once '../../include/question/addedit/' . $question->get_type() . '.php'
?>

          <div class="form">
            <h2><?php echo $string['metadata'] ?></h2>
          </div>
        
<?php
echo render_metadata($mysqli, $question, $question->use_bloom(), $module, $disabled, $string);
?>
        </div>
      </div>

      <div id="changes" class="tab-area">
<?php
$changes = $question->get_changes();
echo render_changes($changes, $string);
?>
      </div>

      <div id="comments" class="tab-area">
<?php
$comments = $question->get_comments($paper_id);
echo render_comments($comments, $string);
?>
      </div>

      <div id="mapping" class="tab-area">
<?php
echo render_objectives_mapping_form($mysqli, $paper_id, $string);
?>
        
      </div>
    </div>

    <div id="button-bar">
<?php
echo save_buttons($mode, $disabled, $question->get_locked(), $question->allow_correction(), $userID, $question->get_checkout_author_id(), $paper_id, $string);
?>
      <input type="hidden" name="q_id" value="<?php echo $question->id ?>" />
      <input name="checkout_author" value="<?php echo $userID ?>" type="hidden" />
      <input id="calling" name="calling" value="<?php echo $calling ?>" type="hidden" />
      <input id="module" name="module" value="<?php echo $module ?>" type="hidden" />
      <input id="folder" name="folder" value="<?php echo $folder ?>" type="hidden" />
      <input id="scrOfY" name="scrOfY" value="<?php echo $scrofy ?>" type="hidden" />
      <input id="paperID" name="paperID" value="<?php echo $paper_id ?>" type="hidden" />
      <input id="keyword" name="keyword" value="<?php echo $keyword ?>" type="hidden" />
      <input id="team" name="team" value="<?php echo $team ?>" type="hidden" />
      <input id="question_id" name="question_id" value="<?php echo $question->id ?>" type="hidden" />
    </div>
  </form>
<?php
}
?>
</body>
</html>