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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../include/timezones.php';

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

$paperID = check_var('paperID', 'REQUEST', true, false, true);

/**
 * Define callbacks to be used when retrieving tracked changes
 * @param  array  $changed_reviewers    Array of reviewers referenced in changes
 * @param  array  $changed_labs         Array of labs referenced in changes
 * @return array                        Array of callbacks to be registered with the logger
 */
function setup_change_callbacks(&$changed_reviewers, &$changed_labs) {
  // Define a closure to populate past reviewer IDs
  $reviewers_cb = function($old, $new) use (&$changed_reviewers) {
    $old_reviewers = explode(',', $old);
    $new_reviewers = explode(',', $new);

    // Add any reviewers in the current change to the $changed_reviewers array
    foreach ($old_reviewers as $reviewer) {
      if ($reviewer != '') {
        $changed_reviewers[$reviewer] = false;
      }
    }
    foreach ($new_reviewers as $reviewer) {
      if ($reviewer != '') {
        $changed_reviewers[$reviewer] = false;
      }
    }
  };

  // Define a closure to populate past labs
  $labs_cb = function($old, $new) use (&$changed_labs) {
    $old_labs = explode(',', $old);
    $new_labs = explode(',', $new);

    // Add any labs in the current change to the $changed_labs array
    foreach ($old_labs as $lab) {
      if ($lab != '') {
        $changed_labs[$lab] = false;
      }
    }
    foreach ($new_labs as $lab) {
      if ($lab != '') {
        $changed_labs[$lab] = false;
      }
    }
  };

  // Use the closures for changes
  $callbacks = array('externals' => $reviewers_cb, 'internals' => $reviewers_cb, 'labs' => $labs_cb);

  return $callbacks;
}

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$modules_array = $properties->get_modules();

$q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

// Build up a list of all past reviewers and labs for the 'changes' tab
$changed_reviewers = array();
$changed_labs = array();

$change_callbacks = setup_change_callbacks($changed_reviewers, $changed_labs);

$logger = new Logger($mysqli);

// Get the changes to be used later
$changes = $logger->get_changes('Paper', $paperID, $change_callbacks);

if ($properties->get_summative_lock() and !$userObject->has_role('SysAdmin')) {
  $locked = true;
  $disabled = ' disabled';
} else {
  $locked = false;
  $disabled = '';
}

if (!isset($staff_modules)){
  $staff_modules = get_staff_modules($userObject->get_user_ID(), $mysqli, $userObject);
}

function format_color($color) {
  return '<div style="background-color:' . $color . '; border:1px solid #C0C0C0; width:50px; height:15px"></div>';
}

function format_referencematerial($ID, $refID) {
  if ($ID == '') return '';

  return $refID[$ID];
}

function format_folders($id, $folders) {
  if ($id == '') return '';

  if (isset($folders[$id])) {
    $formatted_string = str_replace(';', '/', $folders[$id]);
  } else {
    $formatted_string = $id;
  }

  return $formatted_string;
}

function format_user($text, $user_list) {
  if ($text == '') return '';

  $formatted_string = '';
  $parts = explode(',', $text);
  foreach ($parts as $part) {
    if ($formatted_string == '') {
      $formatted_string = $user_list[$part];
    } else {
      $formatted_string .= ', ' . $user_list[$part];
    }
  }

  return $formatted_string;
}

function format_lab($lab_id, $lab_list) {
  $formatted_string = '';
	
  $parts = explode(',', $lab_id);
  foreach ($parts as $part) {
	  if (isset($lab_list[$part])) {
			$lab_name = $lab_list[$part];
		} else {
		  $lab_name = 'unknown';
		}
    if ($formatted_string == '') {
      $formatted_string = $lab_name;
    } else {
      $formatted_string .= ', ' . $lab_name;
    }
  }

  return $formatted_string;
}

function format_marking($marking, $string) {
  $marking_string = $marking;

  $marking_type = $marking{0};

  switch ($marking_type) {
    case MARK_NO_ADJUSTMENT:
      $marking_string = $string['noadjustment'];
      break;
    case MARK_RANDOM:
      $marking_string = $string['calculatrrandommark'];
      break;
    case MARK_STD_SET:
      $marking_string = $string['stdset'];
      break;
    case '3':
      $marking_string = $string['overallclass2'];
      break;
    case '4':
      $marking_string = $string['overallclass3'];
      break;
    case '6':
      $marking_string = $string['overallclass4'];
      break;
    case '7':
      $marking_string = $string['overallclass5'];
      break;
  }

  return $marking_string;
}

function format_method($method, $string) {
  if ($method == '0') {
    return $string['noadjustment'];
  } elseif ($method == '1') {
    return $string['calculatrrandommark'];
  } elseif ($method{0} == '2') {
    return $string['stdset'];
  } elseif ($method == '3') {
    return $string['overallclass2'];
  } elseif ($method == '4') {
    return $string['overallclass3'];
  } elseif ($method == '5') {
    return $string['overallclass1'];
  } elseif ($method == '6') {
    return $string['overallclass4'];
  }
}

function format_review($method, $string) {
  if ($method == '0') {
    return $string['singlereview'];
  } else {
    return $string['allpeerspergroup'];
  }
}

function format_passmark($method, $string) {
  if ($method == 101) {
    return 'Borderline Method';
  } elseif ($method == 102 or $method == 127) {
    return 'N/A';
  } else {
    return $method . '%';
  }
}

function format_on_off($data, $string) {
  if ($data == 0) {
    return $string['off'];
  } else {
    return $string['on'];
  }
}

function format_display($data, $string) {
  if ($data == 0) {
    return $string['windowed'];
  } else {
    return $string['fullscreen'];
  }
}

function format_navigation($data, $string) {
  if ($data == 0) {
    return $string['unidirectional'];
  } else {
    return $string['bidirectional'];
  }
}

function is_leap($year) {
  if ((modulo($year, 4) == 0 and modulo($year, 100) != 0) or modulo($year, 400) == 0) {
    return true;
  } else {
    return false;
  }
}

function output_labs($labs, $cfg_summative_mgmt, $paper_type, $userObject, &$changed_labs, $db) {
  if ($cfg_summative_mgmt and $paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
    $r1class = 'r1disabled';
    $r2class = 'r2disabled';
    $disabled = ' disabled';
    $html = "<div id=\"labs_list\" style=\"height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%\">";
  } elseif ($paper_type == '4') {
    $r1class = 'r1disabled';
    $r2class = 'r2disabled';
    $disabled = ' disabled';
    $html = "<div id=\"labs_list\" style=\"height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%\">";
  } else {
    $r1class = 'r1';
    $r2class = 'r2';
    $disabled = '';
    $html = "<div id=\"labs_list\" style=\"height:278px; overflow-y:scroll;border:1px solid #828790; font-size:90%\">";
  }

  $current_labs = explode(',', $labs);

  $result = $db->prepare("SELECT labs.id, labs.name, campus.name, COUNT(client_identifiers.id) FROM labs, client_identifiers, campus
    WHERE labs.id = client_identifiers.lab AND labs.campus = campus.id GROUP BY client_identifiers.lab ORDER BY campus.name, labs.name");
  $result->execute();
  $result->bind_result($lab_id, $lab_name, $lab_campus, $computer_no);
  $lab_no = 0;
  $old_campus = '';
  while ($result->fetch()) {
    if ($old_campus != $lab_campus) {
      //$html .= "<div><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" />&nbsp;<strong>$lab_campus</strong></div>\n";
    	$html .= "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" /> $lab_campus</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
    }
    $match = false;
    foreach ($current_labs as $individual_lab) {
      if ($lab_id == $individual_lab) $match = true;
    }
    if ($match) {
      $html .= "<div class=\"$r2class\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\" checked><label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
    } else {
      $html .= "<div class=\"$r1class\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\"><label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
    }
    $lab_no++;
    $old_campus = $lab_campus;

    if (isset($changed_labs[$lab_id])) {
      $changed_labs[$lab_id] = $lab_name;
    }
  }
  $result->close();
  $html .= "<input type=\"hidden\" name=\"lab_no\" value=\"$lab_no\" /></div>";

  return $html;
}

function getSchools($staff_modules, $db) {
  $schools = array();

  $staff_modules_list = implode("','", $staff_modules);

  $result = $db->prepare("SELECT DISTINCT schools.id FROM schools, modules WHERE modules.schoolid = schools.id AND modules.moduleid IN ('$staff_modules_list')");
  $result->execute();
  $result->bind_result($schoolID);
  while ($result->fetch()) {
    $schools[] = $schoolID;
  }
  $result->close();

  return $schools;
}

function modulo($n,$b) {
  return $n-$b*floor($n/$b);
}

$title_unique = true;

if (isset($_POST['Submit'])) {
  $old_marking = $properties->get_marking();
  $old_paper_title = $properties->get_paper_title();
  $old_externals = $properties->get_externals();
  $old_internals = $properties->get_internal_reviewers();

  if (isset($_POST['paper_title'])) {
	  if ($old_paper_title == $_POST['paper_title']) {
		  $title_unique = true;
		} else {
			$title_unique = Paper_utils::is_paper_title_unique($_POST['paper_title'], $mysqli);
		}
	}
  if ($title_unique) {
    if (isset($_POST['paper_title'])) {  // Check is set, could be disabled.
      $properties->set_paper_title($_POST['paper_title']);
    }
    if (isset($_POST['paper_type']) and ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1')) {
      $properties->set_paper_type($_POST['paper_type']);
    }

    if (isset($_POST['bidirectional'])) {
      $properties->set_bidirectional($_POST['bidirectional']);
    }

    // External system details;
    $extid = check_var('externalid', 'POST', false, false, true);
    $extsys = check_var('externalsys', 'POST', false, false, true);
    if (!is_null($extid)) {
      $properties->set_externalid($extid);
    }
    if (!is_null($extsys)) {
      $properties->set_externalsys($extsys);
    }
    
    if ($properties->get_paper_type() == '6') {
      if (isset($_POST['display_photos'])) {
        $properties->set_display_correct_answer(1);
      } else {
        $properties->set_display_correct_answer(0);
      }
    } else {
      if (isset($_POST['display_correct_answer'])) {
        $properties->set_display_correct_answer(1);
      } else {
        $properties->set_display_correct_answer(0);
      }
    }
    if (isset($_POST['display_students_response'])) {
      $properties->set_display_students_response(1);
    } else {
      $properties->set_display_students_response(0);
    }
    if ($properties->get_paper_type() == '6') {
      $properties->set_display_question_mark($_POST['review']);
    } else {
      if (isset($_POST['display_question_mark'])) {
        $properties->set_display_question_mark(1);
      } else {
        $properties->set_display_question_mark(0);
      }
    }
    if (isset($_POST['display_feedback'])) {
      $properties->set_display_feedback(1);
    } else {
      $properties->set_display_feedback(0);
    }

    if (isset($_POST['hide_if_unanswered'])) {
      $properties->set_hide_if_unanswered('1');
    } else {
      $properties->set_hide_if_unanswered('0');
    }

    if (!isset($_POST['timezone'])) {
      $_POST['timezone'] = $properties->get_timezone();
    }

    if (($configObject->get('cfg_summative_mgmt') and $properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin','Admin'))) or !$configObject->get('cfg_summative_mgmt') or  $properties->get_paper_type() != '2') {
  		$local_time = new DateTimeZone($configObject->get('cfg_timezone'));
  		$target_timezone = new DateTimeZone($_POST['timezone']);

      if (isset($_POST['fyear']) and isset($_POST['fmonth']) and isset($_POST['fday']) and isset($_POST['fhour']) and isset($_POST['fminute'])) {
        $null_start_date = false;
        if ($_POST['fyear'] == '' and $_POST['fmonth'] == '' and $_POST['fday'] == '' and $_POST['fhour'] == '' and $_POST['fminute'] == '') {
          $null_start_date = true;
          $tmp_start_date = NULL;
        } else {
          $leap = is_leap($_POST['fyear']);
          if ($leap == true and $_POST['fmonth'] == '02' and ($_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '29';
          if ($leap == false and $_POST['fmonth'] == '02' and ($_POST['fday'] == '29' or $_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '28';
          if (($_POST['fmonth'] == '04' or $_POST['fmonth'] == '06' or $_POST['fmonth'] == '09' or $_POST['fmonth'] == '11') and $_POST['fday'] == '31') $_POST['fday'] = '30';

          $start_date = new dateTime($_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['fhour'] . $_POST['fminute'], $target_timezone);
          $start_date->setTimezone($local_time);

          if ($_POST['timezone'] < 0) {
            $start_date->modify("+" . abs($_POST['timezone']) . " hour");
          } elseif ($_POST['timezone'] > 0) {
            $start_date->modify("-" . $_POST['timezone'] . " hour");
          }

          $properties->set_start_date($start_date->format('U'));
          $properties->set_raw_start_date($start_date->format('YmdHis'));
        }
      }

      if (isset($_POST['tyear']) and isset($_POST['tmonth']) and isset($_POST['tday']) and isset($_POST['thour']) and isset($_POST['tminute'])) {
        $null_end_date = false;
        if ($_POST['tyear'] == '' and $_POST['tmonth'] == '' and $_POST['tday'] == '' and $_POST['thour'] == '' and $_POST['tminute'] == '') {
          $null_end_date = true;
          $tmp_end_date = NULL;
        } else {
          $leap = is_leap($_POST['tyear']);

          if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '29';
          if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '28';
          if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') $_POST['tday'] = '30';

          $end_date = new dateTime($_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['thour'] . $_POST['tminute'], $target_timezone);
          $end_date->setTimezone($local_time);

          if ($_POST['timezone'] < 0) {
            $end_date->modify("+" . abs($_POST['timezone']) . " hour");
          } elseif ($_POST['timezone'] > 0) {
            $end_date->modify("-" . $_POST['timezone'] . " hour");
          }
          $properties->set_end_date($end_date->format('U'));
          $properties->set_raw_end_date($end_date->format('YmdHis'));
        }
      }
      $properties->set_timezone($_POST['timezone']);

      if (isset($_POST['calendar_year'])) {
        $calendar_year = ($_POST['calendar_year'] == '') ? NULL : $_POST['calendar_year'];
        $properties->set_calendar_year($calendar_year);
      }
      if (isset($_POST['exam_duration_hours']) or isset($_POST['exam_duration_mins'])) {
			  $exam_duration = 0;
				if (isset($_POST['exam_duration_hours'])) {
					$exam_duration += ($_POST['exam_duration_hours'] * 60);
				}
				if (isset($_POST['exam_duration_mins'])) {
					$exam_duration += $_POST['exam_duration_mins'];
				}
        if (!$locked) {
					$properties->set_exam_duration($exam_duration);
				}
			} else {
				$exam_duration = NULL;
        if (!$locked) {
					$properties->set_exam_duration($exam_duration);
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
      $properties->set_labs($lab_string);
    }

    $leap = is_leap($_POST['ext_tyear']);
    if ($leap == true and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '29';
    if ($leap == false and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '29' or $_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '28';
    if (($_POST['ext_tmonth'] == '04' or $_POST['ext_tmonth'] == '06' or $_POST['ext_tmonth'] == '09' or $_POST['ext_tmonth'] == '11') and $_POST['ext_tday'] == '31') $_POST['ext_tday'] = '30';

    if ($_POST['ext_tyear'] == '' or $_POST['ext_tmonth'] == '' or $_POST['ext_tday'] == '') {
      $properties->set_external_review_deadline(NULL);
    } else {
      $tmp_date = new DateTime($_POST['ext_tyear'] . '-' . $_POST['ext_tmonth'] . '-' . $_POST['ext_tday']);
      $properties->set_external_review_deadline($tmp_date->format('Y-m-d'));
      unset($tmp_date);
    }

    $leap = is_leap($_POST['int_tyear']);
    if ($leap == true and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '29';
    if ($leap == false and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '29' or $_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '28';
    if (($_POST['int_tmonth'] == '04' or $_POST['int_tmonth'] == '06' or $_POST['int_tmonth'] == '09' or $_POST['int_tmonth'] == '11') and $_POST['int_tday'] == '31') $_POST['int_tday'] = '30';

    if ($_POST['int_tyear'] == '' or $_POST['int_tmonth'] == '' or  $_POST['int_tday'] == '') {
      $properties->set_internal_review_deadline(NULL);
    } else {
      $tmp_date = new DateTime($_POST['int_tyear'] . '-' . $_POST['int_tmonth'] . '-' . $_POST['int_tday']);
      $properties->set_internal_review_deadline($tmp_date->format('Y-m-d'));
    }

    $paper_modules = array();
    $first_module_id = '';

    for ($i=0; $i<$_POST['module_no']; $i++) {
      if (isset($_POST['mod' . $i])) {
        if (count($paper_modules) == 0) {
          $paper_modules[$_POST['mod' . $i]] = $_POST['mod' . $i];
          $first_module_idMod = $_POST['mod' . $i];
          $first_module_id = $_POST['mod' . $i];
        } else {
          $paper_modules[$_POST['mod' . $i]] = $_POST['mod' . $i];
        }
      }
    }

    $new_externals = array();
    for ($i=0; $i<$_POST['examiner_no']; $i++) {
      if (isset($_POST["examiner$i"])) {
        $new_externals[] = intval($_POST["examiner$i"]);
      }
    }

    $new_internals = array();
    for ($i=0; $i<$_POST['internal_no']; $i++) {
      if (isset($_POST["internal$i"])) {
        $new_internals[] = intval($_POST["internal$i"]);
      }
    }

		$properties->set_paper_prologue(clearMSOtags($_POST['paper_prologue']));

    if (isset($_POST['osce_marking_guidance'])) {
      $properties->set_paper_postscript(clearMSOtags($_POST['osce_marking_guidance']));
    } else {
      $properties->set_paper_postscript(clearMSOtags($_POST['paper_postscript']));
    }

    if ($properties->get_paper_type() == '6') {
      $properties->set_rubric($_POST['type']);      // Reuse the 'rubric' field to store which field in the metadata to use for groups.
    } else {
      $properties->set_rubric(clearMSOtags($_POST['rubric_text']));
    }

    if (!isset($_POST['marking']) and $properties->get_paper_type() == 4) {
      // Do nothing, the marking method is locked.
    } elseif (!isset($_POST['marking']) or $_POST['marking'] == '') {
      $properties->set_marking(MARK_NO_ADJUSTMENT);
    } elseif ($_POST['marking'] == MARK_STD_SET) {
      $properties->set_marking($_POST['std_set']);
    } else {
      $properties->set_marking($_POST['marking']);
    }

    $tmp_pass_mark = (isset($_POST['pass_mark'])) ? $_POST['pass_mark'] : 0;
    if ($tmp_pass_mark == '') $tmp_pass_mark = 40;
    $properties->set_pass_mark($tmp_pass_mark);

    $tmp_distinction_mark = (isset($_POST['distinction_mark']) and $_POST['distinction_mark'] != '') ? $_POST['distinction_mark'] : 70;
    $properties->set_distinction_mark($tmp_distinction_mark);

    if ($properties->get_summative_lock() === false or $userObject->has_role('SysAdmin')) {
      $tmp_calculator = (isset($_POST['calculator'])) ? $_POST['calculator'] : 0;
      $properties->set_calculator($tmp_calculator);
    }

    if (isset($_POST['sound_demo'])) {
      $properties->set_sound_demo(1);
    } else {
      $properties->set_sound_demo(0);
    }
    
    $password = trim($_POST['password']);
    if (!$locked) {
        if ($password != $properties->get_decrypted_password()) {
            $properties->set_password($password);
        }
        $properties->set_fullscreen($_POST['fullscreen']);
    }
    
    $properties->set_bgcolor($_POST['bgcolor']);
    $properties->set_fgcolor($_POST['fgcolor']);
    $properties->set_themecolor($_POST['themecolor']);
    $properties->set_labelcolor($_POST['labelcolor']);
    $properties->set_folder($_POST['folderID']);

    if ($properties->get_paper_type() == '2' and $old_marking != $properties->get_marking()) {
      $properties->set_recache_marks(1);
    }

    // Save any adjusted properties to the database.
    $properties->save();

    if (!$locked or $userObject->has_role(array('SysAdmin', 'Admin'))) {
			$old_modules = $properties->get_modules(true);

      if (!$locked or $userObject->has_role(array('SysAdmin'))) {
        Paper_utils::update_modules($paper_modules, $paperID, $mysqli, $userObject);
      }

      $paper_modules = $properties->get_modules(true);
			
      $utils = new GeneralUtils();
      if (!$utils->arrays_are_equal($old_modules, $paper_modules)) {
        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', $old_modules), implode(',', $paper_modules), 'modules');
      }

      if (Paper_utils::update_reviewers($old_externals, $new_externals, 'external', $paperID, $mysqli)) {
        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_externals)), implode(',', $new_externals), 'externals');
      }
      if (Paper_utils::update_reviewers($old_internals, $new_internals, 'internal', $paperID, $mysqli)) {
        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_internals)), implode(',', $new_internals), 'internals');
      }
    }

    // Release objectives-based feedback
    if (isset($_POST['old_objectives_report']) and $_POST['old_objectives_report'] != '' and isset($_POST['objectives_report']) and $_POST['objectives_report'] == '0') {
      $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'objectives'");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Objectives-based Feedback', '', 'feedback');
    }
    if (isset($_POST['old_objectives_report']) and $_POST['old_objectives_report'] == '' and isset($_POST['objectives_report']) and $_POST['objectives_report'] == '1') {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'objectives')");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Objectives-based Feedback', 'feedback');
    }

    // Release question-based feedback
    if (isset($_POST['old_questions_report']) and $_POST['old_questions_report'] != '' and isset($_POST['questions_report']) and $_POST['questions_report'] == '0') {
      $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'questions'");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Question-based Feedback', '', 'feedback');
    }
    // Include check to $q_feedback_enabled to see if question-based feedback
    // is switched on at the module level.
    if ($q_feedback_enabled and isset($_POST['old_questions_report']) and $_POST['old_questions_report'] == '' and isset($_POST['questions_report']) and $_POST['questions_report'] == '1') {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'questions')");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Question-based Feedback', 'feedback');
    }

    // Release cohort performance feedback
    if (isset($_POST['old_cohort_performance']) and $_POST['old_cohort_performance'] != '' and isset($_POST['cohort_performance']) and $_POST['cohort_performance'] == '0') {
      $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'cohort_performance'");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'Cohort Performance Feedback', '', 'feedback');
    }
    if (isset($_POST['old_cohort_performance']) and $_POST['old_cohort_performance'] == '' and isset($_POST['cohort_performance']) and $_POST['cohort_performance'] == '1') {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'cohort_performance')");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Cohort Performance Feedback', 'feedback');
    }
    
    // Release external examiner feedback
    if (isset($_POST['old_external_examiner']) and $_POST['old_external_examiner'] != '' and isset($_POST['external_examiner']) and $_POST['external_examiner'] == '0') {
      $editProperties = $mysqli->prepare("DELETE FROM feedback_release WHERE paper_id = ? AND type = 'external_examiner'");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'External Examiner Feedback', '', 'feedback');
    }
    if (isset($_POST['old_external_examiner']) and $_POST['old_external_examiner'] == '' and isset($_POST['external_examiner']) and $_POST['external_examiner'] == '1') {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'external_examiner')");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'External Examiner Feedback', 'feedback');
    }

    if ($properties->get_paper_type() != '2' and $properties->get_paper_type() != '4') {    // Update textual feedback if not a summative paper or OSCE station.
      // Get old settings
      $old_textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);
      for ($i=1; $i<10; $i++) {
        if (!isset($old_textual_feedback[$i]['msg'])) {
          $old_textual_feedback[$i]['msg'] = '';
          $old_textual_feedback[$i]['boundary'] = '';
        }
      }

      // Get new settings
      $textual_feedback = array();
      for ($i=1; $i<10; $i++) {
        if (isset($_POST["feedback_msg$i"]) and trim($_POST["feedback_msg$i"]) != '') {
          $textual_feedback[$i]['msg'] = $_POST["feedback_msg$i"];
          $textual_feedback[$i]['boundary'] = $_POST["feedback_value$i"];
        } else {
          $textual_feedback[$i]['msg'] = '';
          $textual_feedback[$i]['boundary'] = '';
        }
      }

      $editProperties = $mysqli->prepare("DELETE FROM paper_feedback WHERE paperID = ?");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

			for ($i=1; $i<10; $i++) {
				$editProperties = $mysqli->prepare("INSERT INTO paper_feedback VALUES (NULL, ?, ?, ?)");
				if (isset($_POST["feedback_msg$i"]) and trim($_POST["feedback_msg$i"]) != '') {
					$editProperties->bind_param('iis', $paperID, $_POST["feedback_value$i"], $_POST["feedback_msg$i"]);
					$editProperties->execute();
				}
				$editProperties->close();

				if ($old_textual_feedback[$i]['msg'] != $_POST["feedback_msg$i"] or $old_textual_feedback[$i]['boundary'] != $_POST["feedback_value$i"]) {
					// log a change
					$logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_textual_feedback[$i]['boundary'] . '%&nbsp;' . $old_textual_feedback[$i]['msg'], $textual_feedback[$i]['boundary'] . '%&nbsp;' . $textual_feedback[$i]['msg'], 'textualfeedback');
				}
			}

    }

    // Get the current (old) metadata security settings from the database.
    $old_meta = '';
    $result = $mysqli->prepare("SELECT name, value FROM paper_metadata_security WHERE paperID = ? ORDER BY name");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $value);
    while ($result->fetch()) {
      if ($old_meta == '') {
        $old_meta = $name . ':' . $value;
      } else {
        $old_meta .= ', ' . $name . ':' . $value;
      }
    }
    $result->close();

    // Loop around the POST fields to get the new metadata security settings.
		$new_meta = '';
    for ($i=0; $i<$_POST['meta_dropdown_no']; $i++) {
      $meta_type = $_POST['meta_type' . $i];
      $meta_value = $_POST['meta_value' . $i];

      if ($meta_value != '') {
        if ($new_meta == '') {
          $new_meta = $meta_type . ':' . $meta_value;
        } else {
          $new_meta .= ', ' . $meta_type . ':' . $meta_value;
        }
      }
    }
		
    if ($old_meta != $new_meta) {
			// The metadata security settings have changed - update the database.
			$logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_meta, $new_meta, 'restricttometadata');
			
      $editProperties = $mysqli->prepare("DELETE FROM paper_metadata_security WHERE paperID = ?");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();
			
			for ($i=0; $i<$_POST['meta_dropdown_no']; $i++) {
				$meta_type = $_POST['meta_type' . $i];
				$meta_value = $_POST['meta_value' . $i];

				if ($meta_value != '') {
					$editProperties = $mysqli->prepare("INSERT INTO paper_metadata_security VALUES (NULL, ?, ?, ?)");
					$editProperties->bind_param('iss', $paperID, $meta_type, $meta_value);
					$editProperties->execute();
					$editProperties->close();
				}
			}
		}

    // Get existing Reference Materials
    $existing_refs = array();
    $result = $mysqli->prepare("SELECT refID FROM reference_papers WHERE paperID = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($refID);
    while ($result->fetch()) {
      $existing_refs[$refID] = $refID;
    }
    $result->close();

    $new_refs = array();
    for ($i=0; $i<$_POST['reference_no']; $i++) {
      if (isset($_POST["ref$i"])) {
        $new_refs[$_POST["ref$i"]] = $_POST["ref$i"];
      }
    }

    foreach ($new_refs as $new_ref) {
      if (isset($existing_refs[$new_ref])) {
        unset($existing_refs[$new_ref]);
      } else {
        $editProperties = $mysqli->prepare("INSERT INTO reference_papers VALUES (NULL, ?, ?)");
        $editProperties->bind_param('ii', $paperID, $new_ref);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', $new_ref, 'referencematerial');
      }
    }
    foreach ($existing_refs as $existing_ref) {
      $editProperties = $mysqli->prepare("DELETE FROM reference_papers WHERE paperID = ? AND refID = ?");
      $editProperties->bind_param('ii', $paperID, $existing_ref);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $existing_ref, '', 'referencematerial');
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
    <meta http-equiv="pragma" content="no-cache" />

    <title><?php echo $string['edittitle']; ?></title>

    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script>
      $(function () {
        $('#home').click(function () {
          window.opener.parent.location = "details.php?paperID=<?php echo $paperID; ?>&module=<?php echo $first_module_id; ?>";
          window.close();
        });

        <?php
          if ($_POST['caller'] == 'scheduling') {
        ?>
            window.opener.location = "../admin/summative_scheduling.php";
            window.close();
        <?php
          } elseif ($_POST['noadd'] == 'y') {
        ?>
            window.opener.location = "details.php?paperID=<?php echo $paperID; ?>&module=<?php echo $first_module_id; ?>&folder=<?php if (isset($_POST['folderID'])) echo $_POST['folderID']; ?>";
            window.opener.close();
            window.close();
        <?php
          } else {
        ?>
            window.opener.location = "details.php?paperID=<?php echo $paperID; ?>&module=<?php echo $first_module_id; ?>&folder=<?php if (isset($_POST['folderID'])) echo $_POST['folderID']; ?>";
            window.close();
        <?php
          }
        ?>
      });
    </script></head>
    <body>
    <form autocomplete="off">
      <br />&nbsp;<div align="center"><input type="button" id="home" name="home" value="   OK   " /></div>
    </form>
  </body>
</html>
<?php
    exit();
  }
}

$option_no = 1;

// Work out if any negative marking is used
$neg_marking = false;
$result = $mysqli->prepare("SELECT marks_incorrect FROM papers, questions, options WHERE papers.question = questions.q_id AND questions.q_id = options.o_id AND paper = ?");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($marks_incorrect);
while ($result->fetch()) {
  if ($marks_incorrect < 0) {
    $neg_marking = true;
  }
}
$result->close();

// Load textual feedback
$textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);

$local_time = new DateTimeZone($configObject->get('cfg_timezone'));
$target_timezone = new DateTimeZone($properties->get_timezone());

if ($properties->get_start_date() != '') {
  $start_date = DateTime::createFromFormat('U', $properties->get_start_date(), $local_time);
  $start_date->setTimezone($target_timezone);
} else {
  $start_date = '';
}

if ($properties->get_end_date() != '') {
  $end_date = DateTime::createFromFormat('U', $properties->get_end_date(), $local_time);
  $end_date->setTimezone($target_timezone);
} else {
  $end_date = '';
}

if ($configObject->get('cfg_summative_mgmt') and $properties->get_paper_type() == '2' and !$userObject->has_role(array('SysAdmin', 'Admin'))) {
  $sum_disabled = ' disabled';
} elseif ($userObject->has_role('Admin') and $locked) {
  $sum_disabled = ' disabled';
} else {
  $sum_disabled = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['propertiestitle'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <link rel="stylesheet" type="text/css" href="../css/properties.css"/>
  <link rel="stylesheet" type="text/css" href="../css/warnings.css"/>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="../js/system_tooltips.js"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_properties.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<?php
  if ($properties->get_paper_type() == '2' or $properties->get_paper_type() == '5') {
?>
  <script type="text/javascript" src="../js/jquery.datecopy.js"></script>
  <script>
    $(function () {
      $('.datecopy').change(dateCopy);
    })
  </script>
<?php
}
?>
  <script>
    $(function () {
      getMeta();
      
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
      $('form').submit(function() {
        return checkForm();
      });
      $('body').click(function () {
        hidePicker();
      });
			<?php
			if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
			  // If 'noadd' is passed through on the URL open up the security tab automatically.
			  echo "buttonclick('security','tab2')\n";
			}
			?>
    });

    function getMeta() {
      var mod_codes = '';
      var module_no = $('#module_no').val();

      for (i=0; i<module_no; i++) {
        if ($('#mod' + i).attr('checked')) {
          if (mod_codes == '') {
            mod_codes = $('#mod' + i).val();
          } else {
            mod_codes += ',' + $('#mod' + i).val();
          }
        }
      }
      $('#metadata_security').load('getMetdataSecurity.php', 'modules=' + mod_codes + '&paperID=<?php echo $paperID; ?>&session=' + $('#session').val() );
      $('#reference_list').load('getAvailableRefMaterial.php', 'modules=' + mod_codes + '&paperID=<?php echo $paperID; ?>');
    }

    function objreportURL() {
      if ($('#objectives_report').attr('checked')) {
        $('#objreport').show();
      } else {
        $('#objreport').hide();
      }
    }

    function toggle(objectID) {
      if ($('#' + objectID).hasClass('r2')) {
        $('#' + objectID).addClass('r1');
        $('#' + objectID).removeClass('r2');
      } else {
        $('#' + objectID).addClass('r2');
        $('#' + objectID).removeClass('r1');
      }
    }

    function checkForm() {
      if ($('#fyear').val() > $('#tyear').val()) {
        alert ("<?php echo $string['availablefromyear']; ?>");
        return false;
      } else if ($('fyear').val() == $('#tyear').val() && $('#fmonth').val() > $('#tmonth').val()) {
        alert ("<?php echo $string['availablefrommonth']; ?>");
        return false;
      } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() > $('#tday').val()) {
        alert ("<?php echo $string['availablefromday']; ?>");
        return false;
      } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() == $('#tday').val() && $('#fhour').val() > $('#thour').val()) {
        alert ("<?php echo $string['availablefromhour']; ?>");
        return false;
      } else if ($('#fyear').val() == $('#tyear').val() && $('#fmonth').val() == $('#tmonth').val() && $('#fday').val() == $('#tday').val() && $('#fhour').val() == $('#thour').val() && $('#fminute').val() > $('#tminute').val()) {
        alert ("<?php echo $string['availablefromminute']; ?>");
        return false;
      }

      var module_no = $('#module_no').val();
      var moduleList = '';
      for (var i = 0; i < module_no; i++) {
        objectID = 'mod' + i;
        if ($('#' + objectID).attr('checked')) {
          if (moduleList == '') {
            moduleList = $('#' + objectID).val();
          } else {
            moduleList += ',' + $('#' + objectID).val();
          }
        }
      }
      if (moduleList == '') {
        alert ("<?php echo $string['msg1']; ?>");
        return false;
      }

      if ($('#paper_type').val() == '2') {
        if ($('#fday').val() != $('#tday').val() || $('#fmonth').val() != $('#tmonth').val() || $('#fyear').val() != $('#tyear').val()) {
          alert ("<?php echo $string['msg2']; ?>");
          return false;
        }
        if ($('#exam_duration_hours').val() == 'NULL' || $('#exam_duration_mins').val() == 'NULL') {
          alert ("<?php echo $string['msg3']; ?>");
          return false;
        }

        if ($('#session').val() == '') {
          alert ("<?php echo $string['msg4']; ?>");
          return false;
        }
      }

      if ($('#paper_type').val() == '4') {
        var module_no = $('#module_no').val();

        var moduleList = '';
        for (var i = 0; i < module_no; i++) {
          objectID = 'mod' + i;
          if ($('#' + objectID).attr('checked')) {
            if (moduleList == '') {
              moduleList = $('#' + objectID).val();
            } else {
              moduleList += ',' + $('#' + objectID).val();
            }
          }
        }
        if (moduleList == '') {
          alert ("<?php echo $string['msg5']; ?>");
          return false;
        }
				
        if ($('#session').val() == '') {
          alert ("<?php echo $string['msg4']; ?>");
          return false;
        }
      }

      var external_set = false;
      for (var i = 0; i < $('#examiner_no').val(); i++) {
        objectID = 'examiner' + i;
        if ($('#' + objectID).attr('checked')) {
          external_set = true;
        }
      }
      if (external_set == true) {
        if ($('#ext_tmonth').val() == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if ($('#ext_tday').val() == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        } else if ($('#ext_tyear').val() == '') {
          alert("<?php echo $string['msg6']; ?>");
          return false;
        }
      }

      var internal_set = false;
      for (var i = 0; i < $('#internal_no').val(); i++) {
        objectID = 'internal' + i;
        if ($('#' + objectID).attr('checked')) {
          internal_set = true;
        }
      }
      if (internal_set == true) {
        if ($('#int_tmonth').val() == '') {
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        } else if ($('#int_tday').val() == '') {
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        } else if ($('#int_tyear').val() == '') {
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        }
      }
    }

    function changeType() {
      if ($('#paper_type').val() == '0') {
        $('#feedback_on').show();
        $('#feedback_off').hide();
      } else {
        $('#feedback_on').hide();
        $('#feedback_off').show();
      }
    }

    function buttonclick(sectionID, tabID) {
      $('#general').hide();
      $('#security').hide();
      $('#reviewers').hide();
      $('#feedback').hide();
      $('#rubric').hide();
      $('#prologue').hide();
      $('#postscript').hide();
      $('#reference').hide();
      $('#changes').hide();

      $('#' + sectionID).show();

      $('.tab').each(function() {
        $(this).removeClass('tabon');
      });
      $('.tabon').each(function() {
        $(this).removeClass('tabon');
        $(this).addClass('tab');
      });
 			$('#' + tabID).removeClass('tab');
 			$('#' + tabID).addClass('tabon');
    }
    </script>
</head>
<body>
<form id="theform" name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
<?php
  require '../tools/colour_picker/colour_picker.inc';
?>
<table border="0" cellpadding="0" cellspacing="5" style="width:100%; height:645px; font-size:90%">
<tr><td valign="top" style="background-color:white; border:1px solid #828790; width:120px">

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:140px">
<?php
if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
  echo "<tr><td id=\"tab1\" class=\"tab\" onclick=\"buttonclick('general','tab1')\">" . $string['generaltab'] . "</td></tr>\n";
  echo "<tr><td id=\"tab2\" class=\"tabon\" onclick=\"buttonclick('security','tab2')\">" . $string['securitytab'] . "</td></tr>\n";
} else {
  echo "<tr><td id=\"tab1\" class=\"tabon\" onclick=\"buttonclick('general','tab1')\">" . $string['generaltab'] . "</td></tr>\n";
  echo "<tr><td id=\"tab2\" class=\"tab\" onclick=\"buttonclick('security','tab2')\">" . $string['securitytab'] . "</td></tr>\n";
}
if ($properties->get_paper_type() != '3' and $properties->get_paper_type() != '6') {
  echo '<tr><td id="tab3" class="tab" onclick="buttonclick(\'feedback\',\'tab3\')">' . $string['feedback'] . '</td></tr>';
  echo '<tr><td id="tab4" class="tab" onclick="buttonclick(\'reviewers\',\'tab4\')">' . $string['reviewerstab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab3" style="display:none">' . $string['feedback'] . '</td></tr>';
  echo '<tr><td id="tab4" style="display:none">' . $string['reviewerstab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '3' and $properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
  echo '<tr><td id="tab5" class="tab" onclick="buttonclick(\'rubric\',\'tab5\')">' . $string['rubrictab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab5" style="display:none">' . $string['rubrictab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5') {
  echo '<tr><td id="tab6" class="tab" onclick="buttonclick(\'prologue\',\'tab6\')">' . $string['prologuetab'] . '</td></tr>';
  echo '<tr><td id="tab7" class="tab" onclick="buttonclick(\'postscript\',\'tab7\')">' . $string['postscripttab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab6" style="display:none">' . $string['prologuetab'] . '</td></tr>';
  echo '<tr><td id="tab7" style="display:none">' . $string['postscripttab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
  echo '<tr><td id="tab8" class="tab" onclick="buttonclick(\'reference\',\'tab8\')">' . $string['referencematerial'] . '</td></tr>';
} else {
  echo '<tr><td id="tab8" style="display:none">' . $string['referencematerial'] . '</td></tr>';
}
?>
<tr><td id="tab9" class="tab" onclick="buttonclick('changes','tab9')"><?php echo $string['changes']; ?></td></tr>
</table>

</td>

<td style="background-color:white; border:1px solid #828790; vertical-align:top">

<table id="general" class="tabsection" style="<?php if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') echo 'display:none'; ?>">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/general_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['generalheading']; ?></td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table class=\"cellpad2\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo "<tr><td colspan=\"4\" class=\"headbar\">&nbsp;" . $string['paperdetails'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

     echo '<tr><td align="right" valign="top">' . $string['url'] . '&nbsp;</td><td colspan="3">';
     switch ($properties->get_paper_type()) {
       case '2':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "\" target=\"_blank\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "</a> " . $string['onlyonexamday'];
         break;
       case '4':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/osce/\" target=\"_blank\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/osce/</a> " . $string['onlyonexamday'];
         break;
       case '5':
         echo $string['na'];
         break;
       case '6':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/peer_review/form.php?id=" . urlencode($properties->get_crypt_name()) ."\" target=\"_blank\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/peer_review/form.php?id=" . urlencode($properties->get_crypt_name()) ."</a>";
         break;
       default:
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/paper/user_index.php?id=" . urlencode($properties->get_crypt_name()) ."\" target=\"_blank\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/paper/user_index.php?id=" . urlencode($properties->get_crypt_name()) ."</a>";
     }
     echo "</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['name'] . "&nbsp;</td><td colspan=\"3\">";
     if (isset($_POST['Submit']) and !$title_unique) {
       echo "<input type=\"text\" size=\"75\" maxlength=\"200\" class=\"errfield\" value=\"" . $_POST['paper_title'] . "\" name=\"paper_title\"$disabled required />";
     } else {
       echo "<input type=\"text\" size=\"75\" maxlength=\"200\" value=\"" . $properties->get_paper_title() . "\" name=\"paper_title\"$disabled required />";
     }
     echo "<input type=\"hidden\" name=\"paperID\" value=\"$paperID\"></td></tr>\n";
   ?>
    <tr><td align="right" valign="top"><?php echo $string['type']; ?>&nbsp;</td><td>
   <?php
    if ($properties->get_paper_type() == '0') {
      echo "<select id=\"paper_type\" name=\"paper_type\" onclick=\"changeType();\">";
      echo "<option value=\"0\" selected=\"selected\" />" . $string['formative self-assessment'] . "</option>\n";
      echo "<option value=\"1\" />" . $string['progress test'] . "</option>\n";
    } elseif ($properties->get_paper_type() == '1') {
      echo "<select id=\"paper_type\" name=\"paper_type\" onclick=\"changeType();\">";
      echo "<option value=\"0\" />" . $string['formative self-assessment'] . "</option>\n";
      echo "<option value=\"1\" selected=\"selected\" />" . $string['progress test'] . "</option>\n";
    } else {
      echo "<select id=\"paper_type\" name=\"paper_type\">";
      $tmp_types = array('formative self-assessment', 'progress test', 'summative exam', 'survey', 'osce station', 'offline paper', 'peer review');
      echo "<option value=\"" . $properties->get_paper_type() . "\" selected=\"selected\" />" . $string[$tmp_types[$properties->get_paper_type()]] . "</option>\n";
    }

    echo "<td align=\"right\" valign=\"top\">" . $string['folder'] . "&nbsp;</td><td valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\">\n";
    echo "<option value=\"\"></option>";
    $additional = '';

    if (is_array($staff_modules) and count($staff_modules) > 0) {
      $additional = ' OR idMod IN (' . implode(',', array_keys($staff_modules)) . ')';
    }

    if ($properties->get_folder() != '') $additional .= ' OR id=' . $properties->get_folder();

    $folder_details = $mysqli->prepare("SELECT DISTINCT id, name FROM folders LEFT JOIN folders_modules_staff ON folders.id = folders_modules_staff.folders_id WHERE (ownerID = ? $additional) AND deleted IS NULL ORDER BY name");
    $folder_details->bind_param('i', $userObject->get_user_ID());
    $folder_details->execute();
    $folder_details->bind_result($folder_id, $folder_name);
    while ($folder_details->fetch()) {
      $path_parts = substr_count($folder_name, ';');
      $folder_array = explode(';', $folder_name);
      $display_name = str_repeat('&nbsp;', $path_parts * 4) . $folder_array[$path_parts];
      if ($properties->get_folder() == $folder_id) {
        echo "<option value=\"" . $folder_id . "\" selected>" . $display_name . "</option>";
      } else {
        echo "<option value=\"" . $folder_id . "\">" . $display_name . "</option>";
      }
    }
    $folder_details->close();
    
    // External system details.
    $sms = \plugins\plugins_sms::get_sms($mysqli);
    if ($sms !== false) {
        echo "</select>\n</td></tr>\n";
        if ($userObject->has_role('SysAdmin')) {
            // Sys admins can edit.
            echo "<tr><td>" . $string['externalsys'] . "</td><td><select name=\"externalsys\">";
            echo "<option value=\"\"></option>\n";
            foreach ($sms as $s) {
                if ($s == $properties->get_externalsys()) {
                  $selected = "selected";
                } else {
                  $selected = "";
                }
                echo "<option value=\"$s\" $selected>$s</option>\n";
            }
            echo "</select></td></tr>";
            echo "<tr><td>" . $string['externalid'] . "</td><td><input type=\"text\" size=\"30\" maxlength=\"255\" name=\"externalid\" value=\"" . $properties->get_externalid() . "\"></td></tr>";
        } else {
            // Non sys admins can only view.
          echo "<tr><td>" . $string['externalid'] . "</td><td>" . $properties->get_externalid() . "</td></tr>";
          echo "<tr><td>" . $string['externalsys']. "</td><td>" . $properties->get_externalsys() . "</td></tr>";
        }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    }
    if ($properties->get_paper_type() == '4') {
      echo '<input type="hidden" name="bgcolor" value="' . $properties->get_bgcolor() . '" />';
      echo '<input type="hidden" name="fgcolor" value="' . $properties->get_fgcolor() . '" />';
      echo '<input type="hidden" name="themecolor" value="' . $properties->get_themecolor() . '" />';
      echo '<input type="hidden" name="labelcolor" value="' . $properties->get_labelcolor() . '" />';
      echo '<input type="hidden" name="fullscreen" value="' . $properties->get_fullscreen() . '" />';
    } else {
      echo "<tr><td colspan=\"4\" class=\"headbar\">&nbsp;" . $string['displayoptions'] ."</td></tr>\n";
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      if ($properties->get_fullscreen() == 0) {
        echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\"$disabled>\n<option value=\"0\" selected>" . $string['windowed'] ."</option><option value=\"1\">" . $string['fullscreen'] ."</option>\n</select></td>";
      } else {
        echo "<tr><td align=\"right\">" . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\"$disabled>\n<option value=\"0\">" . $string['windowed'] ."</option><option value=\"1\" selected>" . $string['fullscreen'] ."</option>\n</select></td>";
      }
      if ($properties->get_bidirectional() == 1) {
        echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"$disabled><option value=\"0\">" . $string['unidirectional'] ."</option><option value=\"1\"selected>" . $string['bidirectional'] ."</option></select></td></tr>\n";
      } else {
        echo "<td align=\"right\">" . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"$disabled><option value=\"0\" selected>" . $string['unidirectional'] ."</option><option value=\"1\">" . $string['bidirectional'] ."</option></select></td></tr>\n";
      }

      echo "<tr>\n";
      echo "<td align=\"right\">" . $string['background'] . "&nbsp;</td><td><div onclick=\"showPicker('bgcolor',event)\" id=\"span_bgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:" . $properties->get_bgcolor() . "\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"bgcolor\" name=\"bgcolor\" value=\"". $properties->get_bgcolor() . "\" /></td>";
      echo "<td align=\"right\">" . $string['foreground'] . "&nbsp;</td><td><div onclick=\"showPicker('fgcolor',event)\" id=\"span_fgcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:" . $properties->get_fgcolor() . "\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"fgcolor\" name=\"fgcolor\" value=\"" . $properties->get_fgcolor() . "\" /></td>";
      echo "</tr>\n";

      echo "<tr>\n";
      echo "<td align=\"right\">" . $string['theme'] . "&nbsp;</td><td><div onclick=\"showPicker('themecolor',event)\" id=\"span_themecolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:" . $properties->get_themecolor() . "\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"" . $properties->get_themecolor() . "\" /></td>";
      echo "<td align=\"right\">" . $string['labelsnotes'] . "&nbsp;</td><td><div onclick=\"showPicker('labelcolor',event)\" id=\"span_labelcolor\" style=\"border:1px solid #C5C5C5; width:20px; background-color:" . $properties->get_labelcolor() . "\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"" . $properties->get_labelcolor() . "\" /></td>";
      echo "</tr>\n";

      if ($properties->get_paper_type() == '6') {
        echo "<tr><td align=\"right\">" . $string['photos'] . "&nbsp;</td><td colspan=\"3\">";
        if ($properties->get_display_correct_answer() == '1') {
          echo "<input type=\"checkbox\" name=\"display_photos\" value=\"1\" checked />";
        } else {
          echo "<input type=\"checkbox\" name=\"display_photos\" value=\"1\" />";
        }
        echo $string['ifavailable'] . "</td></tr>\n";
      } else {
        if ($properties->get_calculator() == 1) {
          $checked = ' checked="checked"';
        } else {
          $checked = '';
        }
        echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"calculator\" name=\"calculator\"$checked$disabled /><label for=\"calculator\">" . $string['displaycalculator'] . "</label> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_calculator'] . "\" /></td>";

        if ($properties->get_sound_demo() == 1) {
          $checked = ' checked="checked"';
        } else {
          $checked = '';
        }
        echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"sound_demo\" name=\"sound_demo\"$checked$disabled /><label for=\"sound_demo\">" . $string['demosoundclip'] . "</label> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_audio'] . "\" /></td></tr>\n";
      }

      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    }
    if ($properties->get_paper_type() != '3') {
      echo "<tr><td colspan=\"4\" class=\"headbar\">&nbsp;" . $string['marking'] . "</td></tr>\n";
    }
    
    $gradebook = new gradebook($mysqli);
    $graded = $gradebook->paper_graded($paperID);
    $published = "";
    if ($graded) {
        $sum_disabled = "disabled=\"disabled\"";
        $published = "disabled=\"disabled\"";
        echo "<tr><td style=\"padding-right:0\"><div class=\"yellowwarn\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"32\" height=\"32\" alt=\"Published\" /></div></td><td colspan=\"3\" style=\"vertical-align:middle; padding-left:0\"><div class=\"yellowwarn\">" . $string['paperpublishedwarning'] . "</div></td></tr>\n";
    }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if ($properties->get_paper_type() == '4') {    // OSCE Stations
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\">\n<select name=\"pass_mark\" id=\"pass_mark\" $published>\n";
      if ($properties->get_pass_mark() == 102) {
        echo "<option value=\"102\">N/A</option>";
      } else {
        echo "<option value=\"102\" selected>N/A</option>";
      }
      if ($properties->get_pass_mark() == 101) {
        echo "<option value=\"101\" selected>" . $string['borderlinemethod'] . "</option>";
      } else {
        echo "<option value=\"101\">" . $string['borderlinemethod'] . "</option>";
      }
      for ($i=0; $i<=100; $i++) {
        if ($i == $properties->get_pass_mark()) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td></tr>";

      $oscestarted = $properties->get_osce_started_status($paperID, $mysqli);
      if($oscestarted) {
        echo "<tr><td align=\"right\" valign=\"top\"><nobr>" . $string['overallclassification'] . ":</nobr>&nbsp;</td><td valign=\"top\" colspan=\"3\">";
        ?>
          <?php if ($properties->get_marking() == '5') echo 'N/A'; ?>
          <?php if ($properties->get_marking() == '7') echo $string['overallclass5']; ?>
          <?php if ($properties->get_marking() == '3') echo $string['overallclass2']; ?>
          <?php if ($properties->get_marking() == '4') echo $string['overallclass3']; ?>
          <?php if ($properties->get_marking() == '6') echo $string['overallclass4']; ?>

      <?php
      } else {
        echo "<tr><td align=\"right\" valign=\"top\"><nobr>" . $string['overallclassification'] . "</nobr>&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\" $published>";
        ?>
          <option value="5"<?php if ($properties->get_marking() == '5') echo ' selected'; ?> />N/A</option>
          <option value="7"<?php if ($properties->get_marking() == '7') echo ' selected'; ?> /><?php echo $string['overallclass5']; ?></option>
          <option value="3"<?php if ($properties->get_marking() == '3') echo ' selected'; ?> /><?php echo $string['overallclass2']; ?></option>
          <option value="4"<?php if ($properties->get_marking() == '4') echo ' selected'; ?> /><?php echo $string['overallclass3']; ?></option>
          <option value="6"<?php if ($properties->get_marking() == '6') echo ' selected'; ?> /><?php echo $string['overallclass4']; ?></option>
          </select>
      <?php
      }
      echo "<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_osceclassification'] . "\" />";
      echo '</td></tr>';
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      echo "<tr><td colspan=\"4\">" . $string['markingguidance'] . "</td></tr>\n";
      echo "<tr><td colspan=\"4\" style=\"padding: 0\"><textarea class=\"mceEditor\" id=\"osce_marking_guidance\" name=\"osce_marking_guidance\" style=\"width:100%; height:230px\">" .  htmlspecialchars($properties->get_paper_postscript(), ENT_NOQUOTES) . "</textarea></td></tr>";

    } elseif ($properties->get_paper_type() == '6') {  // Peer Review
      $review = $properties->get_display_question_mark();

      echo "<tr><td align=\"right\">" . $string['groupdetails'] . "&nbsp;</td><td><select name=\"type\">\n";
      echo "<option value=\"\" selected>&nbsp;</option>\n";

			$modules_array = $properties->get_modules();

      $field_details = $mysqli->prepare("SELECT DISTINCT type FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN (" . implode(',', array_keys($modules_array)) . ") ORDER BY type");
      $field_details->execute();
      $field_details->bind_result($type);
      while ($field_details->fetch()) {
        if ($properties->get_rubric() == $type) {
          echo "<option value=\"$type\" selected>$type</option>\n";
        } else {
          echo "<option value=\"$type\">$type</option>\n";
        }
      }
      $field_details->close();
      echo "</select>\n</td>\n";
      echo "<td align=\"right\">" . $string['numberfrom'] . "</td><td><select name=\"marking\">\n";
      if ($properties->get_marking() == '1') {
        echo "<option value=\"0\">0</option>\n<option value=\"1\" selected>1</option>\n";
      } else {
        echo "<option value=\"0\" selected>0</option>\n<option value=\"1\">1</option>\n";
      }
      echo "</select>\n</td></tr>\n";
      echo '<tr><td align="right">' . $string['review']  . '</td><td>';
      if ($review == '1') {
        echo '<input type="radio" name="review" value="1" checked="checked" />';
      } else {
        echo '<input type="radio" name="review" value="1" />';
      }
      echo $string['allpeerspergroup'] . '<br />';
      if ($review == '0') {
        echo '<input type="radio" name="review" value="0" checked="checked" />';
      } else {
        echo '<input type="radio" name="review" value="0" />';
      }
      echo $string['singlereview'] . '</td></tr>';
    } elseif ($properties->get_paper_type() != '3') {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\" $published>";
      for ($i=0; $i<=100; $i++) {
        if ($i == $properties->get_pass_mark()) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td><td rowspan=\"2\" style=\"text-align:right\" valign=\"top\">" . $string['method'] . "&nbsp;</td><td rowspan=\"2\">";
    ?>
       <input type="radio" id="marking1" name="marking" value="<?php echo MARK_NO_ADJUSTMENT ?>"<?php if ($properties->get_marking() == MARK_NO_ADJUSTMENT) echo ' checked'; ?> <?php echo $published ?>/><?php echo $string['noadjustment'] ?><br />
       <input type="radio" id="marking2" name="marking" value="<?php echo MARK_RANDOM ?>"<?php
       if ($properties->get_marking() == MARK_RANDOM) {
          echo ' checked ';
       }
       if ($neg_marking) {
          echo ' disabled';
       } else {
          echo ' ' . $published;
       }
       if ($neg_marking) {
         echo '><span style="color:#808080">' . $string['calculatrrandommark'] . '</span>&nbsp;<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_random'] . '" /><br />';
       } else {
        echo '>' . $string['calculatrrandommark'] . '&nbsp;<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_random'] . '" /><br />';
       }

      // Look for any Standard Setting reviews for the paper.
      $std_set_array = array();
      $i = 0;

      $std_set_details = $mysqli->prepare("SELECT std_set.id, title, surname, initials, setterID, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, group_review FROM std_set, users WHERE std_set.setterID = users.id AND paperID = ? ORDER BY std_set DESC");
      $std_set_details->bind_param('i', $paperID);
      $std_set_details->execute();
      $std_set_details->bind_result($std_setID, $std_set_title, $std_set_surname, $std_set_initials, $std_set_reviewer, $std_set_display_date, $group_review);
      while ($std_set_details->fetch()) {
        $std_set_array[$i] = array('std_setID'=>$std_setID, 'title'=>$std_set_title, 'surname'=>$std_set_surname, 'initials'=>$std_set_initials, 'reviewer'=>$std_set_reviewer, 'display_date'=>$std_set_display_date, 'group_review'=>$group_review);
        $i++;
      }
      $std_set_details->close();

      if (count($std_set_array) > 0) {
        echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"" . MARK_STD_SET . "\"";
        if (substr($properties->get_marking(), 0, 1) == MARK_STD_SET) echo ' checked';
        echo " $published/>";
        echo $string['stdset'] . ' <select name="std_set" ' . $published . '>';
        foreach ($std_set_array as $std_set_line) {
          $std_set_title = $std_set_line['title'];
          $std_set_surname = $std_set_line['surname'];
          $std_set_initials = $std_set_line['initials'];
          $std_set_reviewer = $std_set_line['reviewer'];
          $std_setID = $std_set_line['std_setID'];
          $std_set_display_date = $std_set_line['display_date'];

          if ($properties->get_marking() == MARK_STD_SET . ",$std_setID") {
            echo "<option value=\"" . MARK_STD_SET . ",$std_setID\" selected>$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
          } else {
            echo "<option value=\"" . MARK_STD_SET . ",$std_setID\">$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
          }

        }
        echo "</select>\n";
      } else {
        echo "<input type=\"radio\" id=\"marking3\" name=\"marking\" value=\"" . MARK_STD_SET . "\" disabled />";
        echo '<span style="color:#808080">' . $string['stdset'] . '</span>';
      }
    }
    if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2') {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['distinction'] . "</td><td><select name=\"distinction_mark\" $published>";
      echo "<option value=\"127\" selected>N/A</option>\n";    // N/A = 127 which should be impossible to ever get.
      for ($i=0; $i<=100; $i++) {
        if ($i == $properties->get_distinction_mark()) {
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

<table id="prologue" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/prologue_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['prologueheading']; ?></td></tr>
<tr><td><textarea class="mceEditor" id="paper_prologue" name="paper_prologue" style="width:100%; height:537px"><?php echo htmlspecialchars($properties->get_paper_prologue(), ENT_NOQUOTES); ?></textarea></td></tr>
</table>

<table id="postscript" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/postscript_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['postscriptheading']; ?></td></tr>
<tr><td><textarea class="mceEditor" id="paper_postscript" name="paper_postscript" style="width:100%; height:537px"><?php echo htmlspecialchars($properties->get_paper_postscript(), ENT_NOQUOTES); ?></textarea></td></tr>
</table>

<table id="security" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/security_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['securityheading']; ?></td></tr>
<?php
  if ($properties->get_summative_lock() and $userObject->has_role(array('SysAdmin'))) {
?>
<tr>
  <td>
    <div class="yellowwarn">
      <img src="../artwork/paper_locked_padlock.png" width="32" height="32" alt="Locked" /><span style="vertical-align:top; padding-left:10px"><?php echo $string['donotchangewarning']; ?></span>
    </div>
  </td>
</tr>
<?php
  }
?>
<tr>
<td style="text-align:center; vertical-align:top">
<?php
    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" style=\"width:100%; padding-bottom:10px\">\n";
    echo "<tr><td align=\"right\">" . $string['session'] . "</td><td><select name=\"calendar_year\" id=\"session\" onchange=\"getMeta();\"$sum_disabled>\n";
    $yearutils = new yearutils($mysqli);
    echo $yearutils->get_calendar_year_dropdown_options($properties->get_paper_type(), $properties->get_calendar_year(), $string);
    echo "</select></td>";

    if ($properties->get_paper_type() == '4') {
      echo "<td></td><td><input type=\"hidden\" size=\"20\" name=\"password\" value=\"" . $properties->get_decrypted_password() . "\" /></td></tr>\n";
    } else {
      echo "<td align=\"right\">" . $string['password'] . "</td><td><input type=\"text\" size=\"20\" name=\"password\" value=\"" . $properties->get_decrypted_password() . "\"$disabled /> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_password'] . "\" /></td></tr>\n";
    }

    echo "<tr><td align=\"right\">" . $string['timezone'] .  "</td><td><select name=\"timezone\"$sum_disabled style=\"width:270px\">";
    foreach ($timezone_array as $individual_zone => $display_zone) {
      if ($properties->get_timezone() == $individual_zone) {
        echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
      } else {
        echo "<option value=\"$individual_zone\">$display_zone</option>";
      }
    }
    echo '</select></td>';
	
		$exam_duration = $properties->get_exam_duration();
		if ($exam_duration == NULL) {
			$duration_hours = 'NULL';
			$duration_mins = 'NULL';
		} else {
			$duration_hours = (int)floor($exam_duration / 60);
			$duration_mins = (int)$exam_duration - ($duration_hours * 60);
		}
		echo "<td align=\"right\">" . $string['duration'] . "</td><td><select id=\"exam_duration_hours\" name=\"exam_duration_hours\"$sum_disabled>";
		if ($duration_hours == 'NULL') {
			echo '<option value="NULL" selected>N/A</option>';
		} else {
			echo '<option value="NULL">N/A</option>';
 		}
		for ($i=0; $i<=12; $i++) {
		  if ($i === $duration_hours) {
				echo "<option value=\"$i\" selected>$i</option>\n";
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
    echo "</select> " . $string['hrs'] . " <select id=\"exam_duration_mins\" name=\"exam_duration_mins\"$sum_disabled>";
		if ($duration_mins == 'NULL') {
			echo '<option value="NULL" selected>N/A</option>'; 
		} else {
			echo '<option value="NULL">N/A</option>'; 
		}
		for ($i=0; $i<60; $i++) {
		  if ($i === $duration_mins) {
				echo "<option value=\"$i\" selected>$i</option>\n";
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
		echo "</select> " . $string['mins'] . "</td></tr>\n";
    echo "<tr><td align=\"right\" valign=\"top\">" . $string['availablefrom'] . "</td><td>";

    // Split the start date if available
    if (isset($start_date) and $start_date != '') {
      $split_year = $start_date->format('Y');
      $split_month = $start_date->format('m');
      $split_day = $start_date->format('d');
      $split_hour = $start_date->format('H');
      $split_minute = $start_date->format('i');
    } else {
      $split_year = $split_month = $split_day = $split_hour = $split_minute = '';
    }

    // Available from Day
    echo "<select name=\"fday\" id=\"fday\" class=\"datecopy\"$sum_disabled>\n";
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
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
    echo '</select>';
   // Available from Month
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    echo "<select name=\"fmonth\" id=\"fmonth\" class=\"datecopy\"$sum_disabled>\n";
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
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
    echo '</select>';
    // Available from Year
    echo "<select name=\"fyear\" id=\"fyear\" class=\"datecopy\"$sum_disabled>\n";
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
    for ($i = 2002; $i < 2021; $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select><select id=\"fhour\" name=\"fhour\" class=\"datecopy\"$sum_disabled>\n";
    // Available from Hour
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
		for ($tmp_hour = 0; $tmp_hour <= 23; $tmp_hour++) {
		  if ($tmp_hour < 10) {
			  $display_hour = '0' . $tmp_hour;
			} else {
			  $display_hour = $tmp_hour;
			}
      if ($display_hour == $split_hour and $start_date != '') {
        echo "<option value=\"" . $display_hour . "\" selected>" . $display_hour . "</option>\n";
      } else {
        echo "<option value=\"" . $display_hour . "\">" . $display_hour . "</option>\n";
      }
    }
    echo '</select>';
		
    echo "</select><select id=\"fminute\" name=\"fminute\" class=\"datecopy\"$sum_disabled>\n";
    // Available from Minute
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
		for ($tmp_minute = 0; $tmp_minute <= 59; $tmp_minute++) {
		  if ($tmp_minute < 10) {
			  $display_minute = '0' . $tmp_minute;
			} else {
			  $display_minute = $tmp_minute;
			}
      if ($display_minute == $split_minute and $start_date != '') {
        echo "<option value=\"" . $display_minute . "\" selected>" . $display_minute . "</option>\n";
      } else {
        echo "<option value=\"" . $display_minute . "\">" . $display_minute . "</option>\n";
      }
    }
    echo "</select>\n</td>\n";

    // Split the end date if available
    if (isset($end_date) and $end_date != '') {
      $split_year = $end_date->format('Y');
      $split_month = $end_date->format('m');
      $split_day = $end_date->format('d');
      $split_hour = $end_date->format('H');
      $split_minute = $end_date->format('i');
    } else {
      $split_year = $split_month = $split_day = $split_hour = $split_minute = '';
    }

    echo "<td align=\"right\">" . $string['to'] . "&nbsp;</td><td>";

     // Available from Day
    echo "<select name=\"tday\" id=\"tday\" class=\"datecopy\"$sum_disabled>\n";
    if ($end_date == '') {
      echo '<option value=""></option>';
    }
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
    echo '</select>';

    // Available to Month
    echo "<select name=\"tmonth\" id=\"tmonth\" class=\"datecopy\"$sum_disabled>\n";
    if ($end_date == '') {
      echo '<option value=""></option>';
    }
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
    echo '</select>';
    // Available to Year
    echo "<select name=\"tyear\" id=\"tyear\" class=\"datecopy\"$sum_disabled>\n";
    if ($end_date == '') {
      echo '<option value=""></option>';
    }
    for ($i = 2002; $i < (date('Y')+21); $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select><select id=\"thour\" name=\"thour\" class=\"datecopy\"$sum_disabled>\n";
    // Available from Hour
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
		for ($tmp_hour = 0; $tmp_hour <= 23; $tmp_hour++) {
		  if ($tmp_hour < 10) {
			  $display_hour = '0' . $tmp_hour;
			} else {
			  $display_hour = $tmp_hour;
			}
      if ($display_hour == $split_hour and $start_date != '') {
        echo "<option value=\"" . $display_hour . "\" selected>" . $display_hour . "</option>\n";
      } else {
        echo "<option value=\"" . $display_hour . "\">" . $display_hour . "</option>\n";
      }
    }
    echo '</select>';
		
    echo "</select><select id=\"tminute\" name=\"tminute\" class=\"datecopy\"$sum_disabled>\n";
    // Available from Minute
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
		for ($tmp_minute = 0; $tmp_minute <= 59; $tmp_minute++) {
		  if ($tmp_minute < 10) {
			  $display_minute = '0' . $tmp_minute;
			} else {
			  $display_minute = $tmp_minute;
			}
      if ($display_minute == $split_minute and $start_date != '') {
        echo "<option value=\"" . $display_minute . "\" selected>" . $display_minute . "</option>\n";
      } else {
        echo "<option value=\"" . $display_minute . "\">" . $display_minute . "</option>\n";
      }
    }
    echo "</select>\n</td></tr>\n";
    echo "</table>\n";

    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td class=\"headbar\" style=\"padding:2px; width:400px\">&nbsp;" . $string['modules'] . "</td><td class=\"headbar\" style=\"padding:2px\">&nbsp;" . $string['restricttolabs'] . "</td></tr>";
    echo "<tr><td rowspan=\"3\" style=\"vertical-align:top\">";

    echo "<div id=\"modules_list\" style=\"display:block; width:400px; height:420px; overflow-y:scroll; border:1px solid #828790; font-size:90%\">";

		$modules_array = $properties->get_modules();

		$total_modules = array_merge($staff_modules, $modules_array);
    
    $module_sql = implode("','", $total_modules);
    if ($module_sql != '') $module_sql = "'$module_sql'";

    $module_no = 0;
    if ($module_sql != '') {
      $module_array = $userObject->get_staff_accessable_modules();
      $old_school = '';
      $old_schoolcode = '';
      foreach ($module_array as $module) {
        if (is_null($module['schoolcode'])) {
          if ($module['school'] != $old_school or !is_null($old_schoolcode)) {
            echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $module['school'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
          }
        } else {
          if ($module['schoolcode'] != $old_schoolcode) {
            echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . $module['schoolcode'] . ' '. $module['school'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
          }
        }
        $match = false;
        foreach ($modules_array as $separate_module) {
          if ($separate_module == $module['id']) $match = true;
        }
        if ($match == true) {
          if (in_array($module['id'], $staff_modules) or $userObject->has_role('SysAdmin')) {
            echo "<div class=\"r2 mod\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMeta();\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\" checked $disabled><label for=\"mod$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          } else {
            echo "<div class=\"r2 mod\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['idMod'] . "\" checked disabled><input type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" style=\"display:none\" value=\"" . $module['idMod'] . "\" checked><label for=\"mod$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          }
        } else {
          echo "<div class=\"r1 mod\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMeta();\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\"$disabled><label for=\"mod$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
        }
        $module_no++;
        $old_school = $module['school'];
        $old_schoolcode = $module['schoolcode'];
      }
    }
    echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
    echo "</td>\n";

    echo "<td>" . output_labs($properties->get_labs(), $configObject->get('cfg_summative_mgmt'), $properties->get_paper_type(), $userObject, $changed_labs, $mysqli) . "</td></tr>\n";

  ?>
  <tr><td class="headbar" style="padding:2px" colspan="2">&nbsp;<?php echo $string['restricttometadata']; ?></td></tr>
  <tr><td style="vertical-align:top; height:110px" colspan="2"><div style="height:111px; overflow-y:scroll;border:1px solid #828790; font-size:90%" id="metadata_security"></div></td></tr>
  </table>
	
  </td></tr>
</table>

<table id="rubric" class="tabsection" style="display: none">
  <tr><td class="tabtitle"><img src="../artwork/rubric_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['rubricheading']; ?></td></tr>
	<tr><td class="sectionmain"><textarea class="mceEditor" id="rubric_text" name="rubric_text" style="width:100%; height:537px"><?php echo htmlspecialchars($properties->get_rubric(), ENT_NOQUOTES); ?></textarea></td></tr>
</table>

<table id="feedback" class="tabsection" style="display: none">
  <tr><td class="tabtitle" colspan="2"><img src="../artwork/feedback_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['feedbackheading']; ?></td></tr>

  <?php
    echo "<tr><td colspan=\"2\" valign=\"top\">";

    echo "<table class=\"cellpad6\" style=\"margin:15px\">\n";

    $feedback_reports = array('objectives'=>'', 'questions'=>'', 'cohort_performance'=>'', 'external_examiner'=>'');

    $feedback_details = $mysqli->prepare("SELECT idfeedback_release, type FROM feedback_release WHERE paper_id = ?");
    $feedback_details->bind_param('i', $paperID);
    $feedback_details->execute();
    $feedback_details->bind_result($idfeedback_release, $type);
    $feedback_details->store_result();
    while ($feedback_details->fetch()) {
      $feedback_reports[$type] = 1;
    }
    $feedback_details->close();

    if (in_array($properties->get_paper_type(), array('0', '1', '2', '4', '5'))) {
      echo '<tr><td><img src="../artwork/feedback_release_icon.png" width="48" height="48" />';
      echo "<td><input type=\"hidden\" name=\"old_objectives_report\" value=\"" . $feedback_reports['objectives'] . "\" />";
      if ($feedback_reports['objectives'] === '') {
        echo "<input type=\"radio\" name=\"objectives_report\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"objectives_report\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
      } else {
        echo "<input type=\"radio\" name=\"objectives_report\" value=\"1\" checked=\"checked\" />". $string['on'] . "</td><td><input type=\"radio\" name=\"objectives_report\" value=\"0\" />" . $string['off'] . "</td>";
      }
      echo "<td>" . $string['objectivesreport'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/objectives_feedback.php?id=" . $properties->get_crypt_name() . "\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/objectives_feedback.php?id=" . $properties->get_crypt_name() . "</a></td></tr>\n";
    }
    if ($q_feedback_enabled and in_array($properties->get_paper_type(), array('1', '2', '4', '5'))) {
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      echo '<tr><td><img src="../artwork/question_release_icon.png" width="48" height="48" />';
      // Question-based Feedback
      echo "<td><input type=\"hidden\" name=\"old_questions_report\" value=\"" . $feedback_reports['questions'] . "\" />";
      if ($feedback_reports['questions'] === '') {
        echo "<input type=\"radio\" name=\"questions_report\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"questions_report\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
      } else {
        echo "<input type=\"radio\" name=\"questions_report\" value=\"1\" checked=\"checked\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"questions_report\" value=\"0\" />" . $string['off'] . "</td>";
      }
      echo "<td>" . $string['questionfeedback'] . "<br />";
      if ($properties->get_paper_type() == '2') echo '<span style="color:#C00000">' . $string['feedbackwarning'] . '</span></br />';
      echo "<a href=\"https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/question_feedback.php?id=" . $properties->get_crypt_name() . "\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/question_feedback.php?id=" . $properties->get_crypt_name() . "</a></td></tr>\n";
    }
     
    if (in_array($properties->get_paper_type(), array('2', '4', '5'))) {
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      echo '<tr><td><img src="../artwork/cohort_performance_icon.png" width="48" height="48" />';
      // Cohort performance-based Feedback
      echo "<td><input type=\"hidden\" name=\"old_cohort_performance\" value=\"" . $feedback_reports['cohort_performance'] . "\" />";
      if ($feedback_reports['cohort_performance'] === '') {
        echo "<input type=\"radio\" name=\"cohort_performance\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"cohort_performance\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
      } else {
        echo "<input type=\"radio\" name=\"cohort_performance\" value=\"1\" checked=\"checked\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"cohort_performance\" value=\"0\" />" . $string['off'] . "</td>";
      }
      echo "<td>" . $string['cohortperformancefeedback'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/performance_summary.php\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/performance_summary.php</a></td></tr>\n";
    }

    if (in_array($properties->get_paper_type(), array('1', '2'))) {
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      echo '<tr><td><img src="../artwork/external_examiner_icon.png" width="48" height="48" />';
      // External Examiner Feedback
      echo "<td><input type=\"hidden\" name=\"old_external_examiner\" value=\"" . $feedback_reports['external_examiner'] . "\" />";
      if ($feedback_reports['external_examiner'] === '') {
        echo "<input type=\"radio\" name=\"external_examiner\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"external_examiner\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
      } else {
        echo "<input type=\"radio\" name=\"external_examiner\" value=\"1\" checked=\"checked\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"external_examiner\" value=\"0\" />" . $string['off'] . "</td>";
      }
      echo "<td>" . $string['externalexaminerfeedback'] . "<br /><span style=\"color:#808080\">" . $string['externalwarning'] . "</span><br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/reviews/\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/reviews/</a></td></tr>\n";
    }

    echo "</table>\n";

    if ($properties->get_paper_type() == '0') {
      echo '<table cellpadding="3" cellspacing="0" border="0" id="feedback_on" style="width:100%">';
    } else {
      echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="width:100%; display:none">';
    }
    if ($properties->get_paper_type() != '4') {
    ?>
    <tr><td colspan="4" class="headbar">&nbsp;<?php echo $string['answerscreensettings']; ?></td></tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td style="width:33%"><input type="checkbox" name="display_students_response" value="1"<?php if ($properties->get_display_students_response() == '1') echo ' checked'; ?> /><?php echo $string['ticks_crosses'] ?></td><td style="width:33%"><input type="checkbox" name="display_question_mark" value="1"<?php if ($properties->get_display_question_mark() == '1') echo ' checked' ?> /><?php echo $string['question_marks'];?></td><td rowspan="2" style="width:33%; text-indent:-24px; padding-left:24px"><input type="checkbox" name="hide_if_unanswered" value="1"<?php if ($properties->get_hide_if_unanswered() == '1') echo ' checked' ?> /><?php echo $string['hideallfeedback'] ?></td></tr>
    <tr><td><input type="checkbox" name="display_correct_answer" value="1"<?php if ($properties->get_display_correct_answer() == '1') echo ' checked' ?> /><?php echo $string['correctanswerhighlight'] ?></td><td><input type="checkbox" name="display_feedback" value="1"<?php if ($properties->get_display_feedback() == '1') echo ' checked'; ?> /><?php echo $string['textfeedback'] ?></td></tr>
    <?php
    }
    echo "</table>\n";
     
    echo "</td></tr>\n";
		echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

    if (!in_array($properties->get_paper_type(), array('2', '4'))) {
      echo "<tr><td colspan=\"2\" class=\"headbar\">&nbsp;" . $string['textualfeedback'] . "</td></tr>\n";
      echo "<tr><td style=\"text-align:center\">" . $string['above'] . "</td><td style=\"text-align:center\">" . $string['message'] . "</td></tr>\n";
      for ($i=1; $i<=10; $i++) {
        echo "<tr><td><select name=\"feedback_value$i\"><option value=\"\"></option>";
        for ($percent=0; $percent<=100; $percent++) {
          if (isset($textual_feedback[$i]['boundary']) and  $textual_feedback[$i]['boundary'] == $percent) {
            echo "<option value=\"$percent\" selected>$percent%</option>";
          } else {
            echo "<option value=\"$percent\">$percent%</option>";
          }
        }
        $msg = '';
        if (isset($textual_feedback[$i]['msg'])) $msg = $textual_feedback[$i]['msg'];
        echo "</select></td><td><textarea name=\"feedback_msg$i\" cols=\"60\" rows=\"1\" style=\"width:620px; height:18px;\">$msg</textarea></td></tr>\n";
      }
    }
    ?>
</table>

<table id="reviewers" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/reviewers_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['reviewersheading']; ?></td></tr>
<tr>
<td align="center" colspan="2">
<table cellpadding="1" cellspacing="2" border="0">
<tr><td colspan="3">&nbsp;<?php
  $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type = 'sct'");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($sct_no);
  $result->fetch();
  $result->close();
  if ($sct_no > 0) {
    echo '<a href="' . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '</a>';
  }

?></td></tr>
<tr><td class="headbar">&nbsp;<?php echo $string['internalreviewers']; ?></td><td>&nbsp;&nbsp;</td><td class="headbar">&nbsp;<?php echo $string['externalexaminers']; ?></td></tr>
<tr><td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
    if ($properties->get_internal_review_deadline() == '') {
      $split_year = 0;
      $split_month = 0;
      $split_day = 0;
    } else {
      $internal_review_deadline = DateTime::createFromFormat('Y-m-d', $properties->get_internal_review_deadline(), $local_time);
      $internal_review_deadline->setTimezone($local_time);

      $split_year = $internal_review_deadline->format('Y');
      $split_month = $internal_review_deadline->format('m');
      $split_day = $internal_review_deadline->format('d');
    }

    // Available to Day
    echo "<select id=\"int_tday\" name=\"int_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
    echo "<select id=\"int_tmonth\" name=\"int_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
     echo "<select id=\"int_tyear\" name=\"int_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
    if ($properties->get_external_review_deadline() == '') {
      $split_year = 0;
      $split_month = 0;
      $split_day = 0;
    } else {
      $external_review_deadline = DateTime::createFromFormat('Y-m-d', $properties->get_external_review_deadline(), $local_time);
      $external_review_deadline->setTimezone($local_time);

      $split_year = $external_review_deadline->format('Y');
      $split_month = $external_review_deadline->format('m');
      $split_day = $external_review_deadline->format('d');
    }

    // Available to Day
    echo "<select id=\"ext_tday\" name=\"ext_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
    echo "<select id=\"ext_tmonth\" name=\"ext_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
    echo "<select id=\"ext_tyear\" name=\"ext_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
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
  echo "<tr><td><div style=\"width:350px; height:468px; overflow-y:scroll; border:1px solid #828790; font-size:90%\">";

  // Get all users for teams within the schools of the current user
  // Also get all admin users for those schools
  $school_sql = '';
  $admin_school_sql = '';
  $schools = getSchools($staff_modules, $mysqli);

  if (count($schools) > 0) {
    $schools_list = implode(',', $schools);
    if ($userObject->has_role('SysAdmin')) {
      $school_sql = 'AND user_deleted IS NULL';
    } else {
      $school_sql = "AND schoolid IN ($schools_list) AND user_deleted IS NULL";
    }
    $admin_school_sql = <<< SQL
UNION SELECT DISTINCT users.id, title, initials, surname, first_names
FROM users, admin_access
WHERE users.id = admin_access.userID AND admin_access.schools_id IN ($schools_list)
AND user_deleted IS NULL
SQL;
  }

  // Make sure that current reviewers always appear on the list
  $current_internals = $properties->get_internal_reviewers();
  $current_internals_sql = '';
  if (count($properties->get_internal_reviewers()) > 0) {
    $current_internals_sql = 'UNION SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE id IN (' . implode(',', array_keys($current_internals)) . ') AND user_deleted IS NULL';
  }
  // Add internal reviwers to list.
  $internal_reviwers = 'UNION SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE roles = "Internal Reviewer" AND user_deleted IS NULL';

  // Dynamically choose tables and join based on role.
  if ($userObject->has_role('SysAdmin')) {
    $tables = "users, modules_staff";
    $join = "users.id = modules_staff.memberID";
  } else {
    $tables = "users, modules_staff, modules";
    $join = "users.id = modules_staff.memberID AND modules.id = modules_staff.idMod";
  }

  $query = "SELECT DISTINCT users.id, title, initials, surname, first_names FROM $tables WHERE roles != 'Left' AND $join $school_sql $admin_school_sql $current_internals_sql $internal_reviwers ORDER BY surname, initials";
  $internal_details = $mysqli->prepare($query);
  $internal_details->execute();
  $internal_details->bind_result($internal_id, $internal_title, $internal_initials, $internal_surname, $internal_first_names);
  $internal_no = 0;
  while ($internal_details->fetch()) {
    $match = false;
    foreach ($current_internals as $reviewerID => $reviewer_name) {
      if ($internal_id == $reviewerID) $match = true;
    }
    if ($match) {
      echo "<div class=\"r2\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\" checked><label for=\"internal$internal_no\">" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    } else {
      echo "<div class=\"r1\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\"><label for=\"internal$internal_no\">" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    }
    $internal_no++;
  }
  $internal_details->close();
  echo "<input type=\"hidden\" id=\"internal_no\" name=\"internal_no\" value=\"$internal_no\" /></div></td><td></td>";

  echo "<td><div style=\"width:350px; height:468px; overflow-y:scroll; border:1px solid #828790; font-size:90%\">";
  $current_externals = $properties->get_externals();
  $external_details = $mysqli->prepare("SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE roles = 'External Examiner' AND grade != 'left' AND user_deleted IS NULL ORDER BY surname, initials");
  $external_details->execute();
  $external_details->bind_result($external_id, $external_title, $external_initials, $external_surname, $external_first_names);
  $examiner_no = 0;
  while ($external_details->fetch()) {
    $match = false;
    foreach ($current_externals as $reviewerID => $reviewer_name) {
      if ($external_id == $reviewerID) $match = true;
    }
    if ($match) {
      echo "<div class=\"r2\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\" checked><label for=\"examiner$examiner_no\">" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
    } else {
      echo "<div class=\"r1\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\"><label for=\"examiner$examiner_no\">" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
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

<table id="reference" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/toggle_log.png" alt="Icon" align="middle" /><?php echo $string['referenceheading'] ?></td></tr>
<tr><td style="vertical-align:top"><div id="reference_list" style="padding: 5px"></div></td></tr>
</table>

<table id="changes" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/version_icon.png" alt="Icon" align="middle" /><?php echo $string['changesheading'] ?></td></tr>
<tr><td style="vertical-align:bottom"><div id="change_list" style="height:543px; overflow-y:scroll">
<table cellspacing="0" cellpadding="2" border="0" style="width:100%">
<?php
$modules = module_utils::get_module_list_by_id($mysqli);

$user_list = array();
if (count($changed_reviewers) > 0) {
  $reviewer_in = implode(',', array_keys($changed_reviewers));
  $results = $mysqli->prepare("SELECT id, title, surname FROM users WHERE id IN ($reviewer_in)");
  $results->execute();
  $results->bind_result($id, $title, $surname);
  while ($results->fetch()) {
    $user_list[$id] = $title . ' ' . $surname;
  }
  $results->close();
}

$reference_material = array();
$results = $mysqli->prepare("SELECT id, title FROM reference_material");
$results->execute();
$results->bind_result($id, $title);
while ($results->fetch()) {
  $reference_material[$id] = $title;
}
$results->close();

$folders = folder_utils::get_all_folders($mysqli);

echo "<tr><th>" . $string['part'] . "</th><th>" . $string['old'] . "</th><th>" . $string['new'] . "</th><th>" . $string['date'] . "</th><th>" . $string['author'] . "</th></tr>";
// Changes retrieved at beginning of file
$rows = count($changes);
for ($i=0; $i<$rows; $i++) {
  $part = $changes[$i]['part'];

  $old = $changes[$i]['old'];
  $new = $changes[$i]['new'];

  switch ($part) {
    case 'startdate':
    case 'enddate':
      $old = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $old);
      $new = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $new);
      break;
    case 'folder':
      $old = format_folders($old, $folders);
      $new = format_folders($new, $folders);
      break;
    case 'method':
      $old = format_method($old, $string);
      $new = format_method($new, $string);
      break;
    case 'displaycalculator':
    case 'demosoundclip':
    case 'photos':
    case 'ticks_crosses':
    case 'hideallfeedback':
    case 'textfeedback':
    case 'correctanswerhighlight':
    case 'question_marks':
      $old = format_on_off($old, $string);
      $new = format_on_off($new, $string);
      break;
    case 'externals':
    case 'internals':
      $old = format_user($old, $user_list);
      $new = format_user($new, $user_list);
      break;
    case 'background':
    case 'foreground':
    case 'theme':
    case 'labelsnotes':
      $old = format_color($old);
      $new = format_color($new);
      break;
    case 'referencematerial':
      $old = format_referencematerial($old, $reference_material);
      $new = format_referencematerial($new, $reference_material);
      break;
    case 'display':
      $old = format_display($old, $string);
      $new = format_display($new, $string);
      break;
    case 'navigation':
      $old = format_navigation($old, $string);
      $new = format_navigation($new, $string);
      break;
    case 'review':
      $old = format_review($old, $string);
      $new = format_review($new, $string);
      break;
    case 'passmark':
    case 'distinction':
      $old = format_passmark($old, $string);
      $new = format_passmark($new, $string);
      break;
    case 'labs':
      $old = format_lab($old, $changed_labs);
      $new = format_lab($new, $changed_labs);
      break;
    case 'marking':
      $old = format_marking($old, $string);
      $new = format_marking($new, $string);
      break;
  }

  if (isset($string[$part])) $part = $string[$part];
  echo "<tr><td>" . ucfirst($part) . "</td><td>$old</td><td>$new</td><td>" . date($configObject->get('cfg_short_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $changes[$i]['date']) . "</td><td>" . $changes[$i]['title'] . " " . $changes[$i]['surname'] . "</td><tr>\n";
}
$mysqli->close();
?>
</table>
</div></td></tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" class="ok" name="Submit" value="<?php echo $string['ok']; ?>" /><input type="button" name="home" class="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></td></tr>
</table>

<input type="hidden" name="noadd" value="<?php if (isset($_GET['noadd'])) echo $_GET['noadd']; ?>" />
<input type="hidden" name="caller" value="<?php if (isset($_GET['caller'])) echo $_GET['caller']; ?>" />
</form>

</body>
</html>
