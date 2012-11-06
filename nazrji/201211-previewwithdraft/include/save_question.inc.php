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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

/**
 * Get a question object, either new ('Add' mode) or populated from database ('Edit' mode)
 * @param  string $mode           Editing mode - 'Add' or 'Edit'
 * @param  string $critical_error Description of any breaking errors that we encounter
 * @param  int    $userID
 * @param  array  $string         Array of translated language strings
 * @param  object $mysqli         mysqli database connection
 * @return object                 The Question object
 */
function get_question(&$mode, &$critical_error, $userID, $paper_id, $string, $mysqli, $question_mode='live') {
  $question = false;

	if (!isset($_REQUEST['q_id']) or $_REQUEST['q_id'] == -1) {
	  // We're adding a new question
	  $mode = $string['add'];

	  if (!isset($_GET['type'])) {
	    $critical_error = $string['typeundefined'];
	  } elseif (!in_array($_GET['type'], Question::$types)) {
	    $critical_error = sprintf($string['typeinvalid'], htmlentities($_GET['type']));
	  } else {
	    try {
	      $question = Question::question_factory($mysqli, $userID, $string, $_GET['type'], $question_mode);
	      $question->set_type($_GET['type']);
	      $question->set_owner_id($userID);
	      $question->set_teams(Paper_utils::get_modules($paper_id, $mysqli));
	    } catch (ClassNotFoundException $ex) {
	      $critical_error = $ex->getMessage();
	    }
	  }
	} else {
	  // We're editing an existing question
	  $mode = $string['edit'];

	  try {
	    $question = Question::question_factory($mysqli, $userID, $string, $_REQUEST['q_id'], $question_mode);
	  } catch (Exception $ex) {
	    $critical_error = $ex->getMessage();
	  }
	}

  return $question;
}

/**
 * Populate a question object with data from the POST
 * @param  object $question The question object
 * @param  int $userID
 * @param  object $mysqli   mysqli database connection
 * @return boolean          True of OK to go ahead with saving question
 */
function populate_question($question, $userID, $mysqli) {
  $do_save = false;

  if ($question->id == -1 or check_fullSave($question->id, $mysqli)) {

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

    $question_teams = array();
    if (isset($_POST['teams'])) {
      //$question_teams = array_combine($_POST['teams'], $_POST['teams']);
      foreach($_POST['teams'] as $idMod) {
        $question_teams[$idMod] = module_utils::get_moduleID($idMod, $mysqli);
      }
    }
    $question->set_teams($question_teams);

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

    $do_save = true;
  }

  return $do_save;
}

/**
 * Save a populated question to the database
 * @param  object $question The question object
 * @param  int $userID
 * @param  int $paper_id
 * @param  string $mode     Editing mode - 'Add' or 'Edit'
 * @param  object $mysqli   mysqli database connection
 * @return array            Array containing any errors encountered during the saving process
 */
function save_question($question, $userID, $paper_id, $mode, $string, &$state, $mysqli, $question_mode='live') {
  $errors = array();

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

      if ($question_mode == 'live') {
        save_keywords($question, $userID, true, $mysqli, $string);

        if (isset($_POST['objective_modules'])) {
          // Write out curriculum mapping.
          save_objective_mappings($mysqli, $_POST['objective_modules'], $paper_id, $question->id);
        }

        // Save a default team if defined
        if ($mode == 'Add') {
          $team_for_state = '';
          if ($module != '') {
            $team_for_state = $module;
          } else {
            $q_teams = $question->get_teams();
            if (is_array($q_teams) and count($q_teams) > 0) $team_for_state = $q_teams[0];
          }
          $state = $stateutil->setState($userID, 'default_team', $team_for_state, '/question/edit/index.php', $mysqli);
        }

        // Stuff not to do on correction/limited save
        if (!isset($_POST['submit']) or $_POST['submit'] != $string['correct']) {
          // Save review comments and responses
          if(isset($_POST['comment_ids']) and isset($_POST['actions']) and isset($_POST['responses'])) {
            save_external_responses($mysqli, $question, $_POST['comment_ids'], $_POST['actions'], $_POST['responses'], $paper_id);
          }

          // For likert, save the scale to a state to ease creation of multiple questions with same scale
          if ($mode == 'Add' and $question->get_type() == 'likert') {
            $scale_type = $question->get_scale_type();
            $state = $stateutil->setState($userID, 'likert_format', $scale_type, '/question/edit/index.php', $mysqli);

            if ($scale_type == 'custom') {
              $state = $stateutil->setState($userID, 'likert_format', implode('|', $question->get_all_custom_scales()), '/question/edit/index.php', $mysqli);
            }
          }
        }
      }
    }
  } catch (ValidationException $vex) {
    $errors[] = $vex->getMessage();
  }

  return $errors;
}
