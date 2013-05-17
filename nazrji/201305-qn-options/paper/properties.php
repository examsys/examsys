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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../lang/' . $language . '/include/timezones.inc';
require_once '../classes/paperutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/questionutils.class.php';
require_once '../classes/logger.class.php';
require_once '../classes/paperproperties.class.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
if (!$properties) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$logger = new Logger($mysqli);

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

function format_modules($text, $modules) {
  if ($text == '') return '';

  $formatted_string = '';
  $parts = explode(',', $text);
  foreach ($parts as $part) {
    if ($formatted_string == '') {
      $formatted_string = $modules[$part]['code'];
    } else {
      $formatted_string .= ', ' . $modules[$part]['code'];
    }
  }

  return $formatted_string;
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
  } elseif ($method == 102) {
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

function output_labs($labs, $cfg_summative_mgmt, $paper_type, $userObject, $db) {
  if ($cfg_summative_mgmt and $paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
    $r1class = 'r1disabled';
    $r2class = 'r2disabled';
    $disabled = ' disabled';
    $html = "<div style=\"height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%\">";
  } elseif ($paper_type == '4') {
    $r1class = 'r1disabled';
    $r2class = 'r2disabled';
    $disabled = ' disabled';
    $html = "<div style=\"height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%\">";
  } else {
    $r1class = 'r1';
    $r2class = 'r2';
    $disabled = '';
    $html = "<div style=\"height:278px; overflow-y:scroll;border:1px solid #7F9DB9; font-size:90%\">";
  }

  $current_labs = explode(',', $labs);

  $result = $db->prepare("SELECT labs.id, name, campus, COUNT(ip_addresses.id) FROM labs, ip_addresses WHERE labs.id = ip_addresses.lab GROUP BY ip_addresses.lab ORDER BY campus, name");
  $result->execute();
  $result->bind_result($lab_id, $lab_name, $lab_campus, $computer_no);
  $lab_no = 0;
  $old_campus = '';
  while ($result->fetch()) {
    if ($old_campus != $lab_campus) {
      $html .= "<div><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" />&nbsp;<strong>$lab_campus</strong></div>\n";
    }
    $match = false;
    foreach ($current_labs as $individual_lab) {
      if ($lab_id == $individual_lab) $match = true;
    }
    if ($match) {
      $html .= "<div class=\"$r2class\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\" checked>&nbsp;<label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
    } else {
      $html .= "<div class=\"$r1class\" style=\"padding-left:40px\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled onclick=\"toggle('divlab$lab_no')\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\">&nbsp;<label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
    }
    $lab_no++;
    $old_campus = $lab_campus;
  }
  $result->close();
  $html .= "<input type=\"hidden\" name=\"lab_no\" value=\"$lab_no\" /></div>";

  return $html;
}

function getSchools($staff_modules, $db) {
  $schools = array();

  $staff_modules_list = implode("','", $staff_modules);

  $result = $db->prepare("SELECT DISTINCT schools.id FROM schools, modules WHERE modules.schoolid=schools.id AND modules.moduleid IN ('$staff_modules_list')");
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

if (isset($_POST['Submit'])) {
  // Setup old details before updating database with new values
  $old_paper_type = $properties->get_paper_type();
  $old_labs = $properties->get_labs();
  $old_paper_title = $properties->get_paper_title();
  $old_calendar_year = $properties->get_calendar_year();
  $old_timezone = $properties->get_timezone();
  $old_password = $properties->get_password();
  $old_exam_duration = $properties->get_exam_duration();
  $old_start_date = $properties->get_start_date();
  $old_end_date = $properties->get_end_date();
  $old_fullscreen = $properties->get_fullscreen();
  $old_bidirectional = $properties->get_bidirectional();
  $old_paper_prologue = $properties->get_paper_prologue();
  $old_paper_postscript = $properties->get_paper_postscript();
  $old_rubric = $properties->get_rubric();
  $old_marking = $properties->get_marking();
  $old_pass_mark = $properties->get_pass_mark();
  $old_distinction_mark = $properties->get_distinction_mark();
  $old_sound_demo = $properties->get_sound_demo();
  $old_calculator = $properties->get_calculator();
  $old_bgcolor = $properties->get_bgcolor();
  $old_fgcolor = $properties->get_fgcolor();
  $old_themecolor = $properties->get_themecolor();
  $old_labelcolor = $properties->get_labelcolor();
  $old_modules = array_keys(Paper_utils::get_modules($paperID, $mysqli));
  sort($old_modules, SORT_NUMERIC);
  $old_modules = implode(',', $old_modules);
  $old_externals = $properties->get_externals();
  $old_internals = $properties->get_internal_reviewers();
  $old_external_review_deadline = $properties->get_external_review_deadline();
  $old_internal_review_deadline = $properties->get_internal_review_deadline();
  $old_display_students_response = $properties->get_display_students_response();
  $old_display_question_mark = $properties->get_display_question_mark();
  $old_hide_if_unanswered = $properties->get_hide_if_unanswered();
  $old_display_correct_answer = $properties->get_display_correct_answer();
  $old_display_feedback = $properties->get_display_feedback();
  $old_folder = $properties->get_folder();

  // Check that the new paper name is not already used by any other paper (i.e. unique).
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE paper_title = ? LIMIT 1");
  $result->bind_param('s', $_POST['paper_title']);
  $result->execute();
  $result->bind_result($paper_title);
  $result->store_result();
  if ($result->num_rows == 0 or $old_paper_title == $_POST['paper_title']) {
    $paper_title = $_POST['paper_title'];
    if (isset($_POST['paper_type'])) {
      $paper_type = $_POST['paper_type'];
    } else {
      $paper_type = $old_paper_type;
    }

    if (isset($_POST['bidirectional']) and $_POST['bidirectional'] == 1) {
      $bidirectional = 1;
    } else {
      $bidirectional = 0;
    }
    if ($properties->get_paper_type() == '6') {
      if (isset($_POST['display_photos'])) {
        $display_correct_answer = 1;
      } else {
        $display_correct_answer = 0;
      }
    } else {
      if (isset($_POST['display_correct_answer'])) {
        $display_correct_answer = 1;
      } else {
        $display_correct_answer = 0;
      }
    }
    if (isset($_POST['display_students_response'])) {
      $display_students_response = 1;
    } else {
      $display_students_response = 0;
    }
    if ($properties->get_paper_type() == '6') {
      $display_question_mark = $_POST['review'];
    } else {
      if (isset($_POST['display_question_mark'])) {
        $display_question_mark = 1;
      } else {
        $display_question_mark = 0;
      }
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

    if (($configObject->get('cfg_summative_mgmt') and $paper_type == '2' and $userObject->has_role(array('Admin','SysAdmin'))) or !$configObject->get('cfg_summative_mgmt') or  $paper_type != '2') {
  		$local_time = new DateTimeZone($configObject->get('cfg_timezone'));
  		$target_timezone = new DateTimeZone($_POST['timezone']);

      $null_start_date = false;
      if ($_POST['fyear'] == '' and $_POST['fmonth'] == '' and $_POST['fday'] == '' and $_POST['ftime'] == '') {
        $null_start_date = true;
        $tmp_start_date = NULL;
      } else {
        $leap = is_leap($_POST['fyear']);
        if ($leap == true and $_POST['fmonth'] == '02' and ($_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '29';
        if ($leap == false and $_POST['fmonth'] == '02' and ($_POST['fday'] == '29' or $_POST['fday'] == '30' or $_POST['fday'] == '31')) $_POST['fday'] = '28';
        if (($_POST['fmonth'] == '04' or $_POST['fmonth'] == '06' or $_POST['fmonth'] == '09' or $_POST['fmonth'] == '11') and $_POST['fday'] == '31') $_POST['fday'] = '30';

        $start_date = new dateTime($_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'], $target_timezone);
        $start_date->setTimezone($local_time);

        if ($_POST['timezone'] < 0) {
          $start_date->modify("+" . abs($_POST['timezone']) . " hour");
        } elseif ($_POST['timezone'] > 0) {
          $start_date->modify("-" . $_POST['timezone'] . " hour");
        }

        $tmp_start_date = $start_date->format("YmdHis");
      }

      $null_end_date = false;
      if ($_POST['tyear'] == '' and $_POST['tmonth'] == '' and $_POST['tday'] == '' and $_POST['ttime'] == '') {
        $null_end_date = true;
        $tmp_end_date = NULL;
      } else {
        $leap = is_leap($_POST['tyear']);

        if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '29';
        if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '28';
        if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') $_POST['tday'] = '30';

        $end_date = new dateTime($_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'], $target_timezone);
        $end_date->setTimezone($local_time);

        if ($_POST['timezone'] < 0) {
          $end_date->modify("+" . abs($_POST['timezone']) . " hour");
        } elseif ($_POST['timezone'] > 0) {
          $end_date->modify("-" . $_POST['timezone'] . " hour");
        }
        $tmp_end_date = $end_date->format("YmdHis");
      }
      $timezone = $_POST['timezone'];
      $calendar_year = $_POST['calendar_year'];
      $exam_duration = ($_POST['exam_duration'] == 'NULL') ? NULL : $_POST['exam_duration'];

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
    } else {
      // If we are in here the paper type is 2 and summative management is on.
      // Set times to the old time settings.
      $tmp_start_date = $old_start_date;
      $tmp_end_date   = $old_end_date;
      $timezone       = $old_timezone;
      $calendar_year  = $old_calendar_year;
      $exam_duration  = $old_exam_duration;
      $lab_string     = $old_labs;
    }

    $leap = is_leap($_POST['ext_tyear']);
    if ($leap == true and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '29';
    if ($leap == false and $_POST['ext_tmonth'] == '02' and ($_POST['ext_tday'] == '29' or $_POST['ext_tday'] == '30' or $_POST['ext_tday'] == '31')) $_POST['ext_tday'] = '28';
    if (($_POST['ext_tmonth'] == '04' or $_POST['ext_tmonth'] == '06' or $_POST['ext_tmonth'] == '09' or $_POST['ext_tmonth'] == '11') and $_POST['ext_tday'] == '31') $_POST['ext_tday'] = '30';

    if ($_POST['ext_tyear'] == '' or $_POST['ext_tmonth'] == '' or $_POST['ext_tday'] == '') {
      $external_review_deadline = NULL;
      $ext_date = '';
    } else {
      $ext_date = new DateTime($_POST['ext_tyear'] . '-' . $_POST['ext_tmonth'] . '-' . $_POST['ext_tday']);
      $external_deadline = $ext_date->format('Y-m-d');
      $external_review_deadline = $ext_date->format('U');
    }
    unset($tmp_date);

    $leap = is_leap($_POST['int_tyear']);
    if ($leap == true and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '29';
    if ($leap == false and $_POST['int_tmonth'] == '02' and ($_POST['int_tday'] == '29' or $_POST['int_tday'] == '30' or $_POST['int_tday'] == '31')) $_POST['int_tday'] = '28';
    if (($_POST['int_tmonth'] == '04' or $_POST['int_tmonth'] == '06' or $_POST['int_tmonth'] == '09' or $_POST['int_tmonth'] == '11') and $_POST['int_tday'] == '31') $_POST['int_tday'] = '30';

    if ($_POST['int_tyear'] == '' or $_POST['int_tmonth'] == '' or  $_POST['int_tday'] == '') {
      $internal_review_deadline = NULL;
      $int_date = '';
    } else {
      $int_date = new DateTime($_POST['int_tyear'] . '-' . $_POST['int_tmonth'] . '-' . $_POST['int_tday']);
      $internal_deadline = $int_date->format('Y-m-d');
      $internal_review_deadline = $int_date->format('U');
    }

    $paper_modules = array();
    $first_module_id = '';

    for ($i=0; $i<$_POST['module_no']; $i++) {
      if (isset($_POST['module' . $i])) {
        if (count($paper_modules) == 0) {
          $paper_modules[$_POST['module' . $i]] = $_POST['module' . $i];
          $first_module_idMod = $_POST['module' . $i];
          $first_module_id = $_POST['module' . $i];
        } else {
          $paper_modules[$_POST['module' . $i]] = $_POST['module' . $i];
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

    $tmp_prologue = $_POST['paper_prologue'];
    $tmp_prologue = clearMSOtags($tmp_prologue);

    if (isset($_POST['osce_marking_guidance'])) {
      $tmp_postscript = $_POST['osce_marking_guidance'];
      $tmp_postscript = clearMSOtags($tmp_postscript);
    } else {
      $tmp_postscript = $_POST['paper_postscript'];
      $tmp_postscript = clearMSOtags($tmp_postscript);
    }

    if ($properties->get_paper_type() == '6') {
      $tmp_rubric = $_POST['type'];      // Reuse the 'rubric' field to store which field in the metadata to use for groups.
    } else {
      $tmp_rubric = $_POST['rubric_text'];
      $tmp_rubric = clearMSOtags($tmp_rubric);
    }

    $tmp_marking = $_POST['marking'];
    if ($tmp_marking == '') $tmp_marking = '0';
    if ($tmp_marking == '2') $tmp_marking = $_POST['std_set'];

    $tmp_pass_mark = (isset($_POST['pass_mark'])) ? $_POST['pass_mark'] : 0;
    if ($tmp_pass_mark == '') $tmp_pass_mark = 40;

    $tmp_distinction_mark = (isset($_POST['distinction_mark']) and $_POST['distinction_mark'] != '') ? $_POST['distinction_mark'] : 70;
    $tmp_calculator = (isset($_POST['calculator'])) ? $_POST['calculator'] : 0;

    if (isset($_POST['sound_demo'])) {
      $tmp_sound_demo = 1;
    } else {
      $tmp_sound_demo = 0;
    }

    if ($locked) {
      $paper_title = $old_paper_title;
      $timezone = $old_timezone;
      $exam_duration = $old_exam_duration;
      $password = $old_password;
      $calendar_year = $old_calendar_year;
      $tmp_rubric = $old_rubric;
      $fullscreen = $old_fullscreen;
      $tmp_start_date = $old_start_date;
      $tmp_end_date = $old_end_date;
    } else {
      $password = trim($_POST['password']);
      $fullscreen = $_POST['fullscreen'];
    }
    $bgcolor = $_POST['bgcolor'];
    $fgcolor = $_POST['fgcolor'];
    $themecolor = $_POST['themecolor'];
    $labelcolor = $_POST['labelcolor'];
    $folderID = $_POST['folderID'];

    if ($locked) {
      $editProperties = $mysqli->prepare("UPDATE properties SET marking = ?, pass_mark = ?, distinction_mark = ?, display_correct_answer = ?, display_students_response = ?, display_question_mark = ?, display_feedback = ? WHERE property_id = ?");
      $editProperties->bind_param('siiiiiii', $tmp_marking, $tmp_pass_mark, $tmp_distinction_mark, $display_correct_answer, $display_students_response, $display_question_mark, $display_feedback, $paperID);
      $editProperties->execute();
      $editProperties->close();
    } elseif ($configObject->get('cfg_summative_mgmt') and $paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
      $editProperties = $mysqli->prepare("UPDATE properties SET paper_title = ?, paper_prologue = ?, paper_postscript = ?, bgcolor = ?, fgcolor = ?, themecolor = ?, labelcolor = ?, fullscreen = ?, marking = ?, bidirectional = ?, pass_mark = ?, distinction_mark = ?, folder = ?, rubric = ?, calculator = ?, display_correct_answer = ?, display_students_response = ?, display_question_mark = ?, display_feedback = ?, hide_if_unanswered = ?, external_review_deadline = ?, internal_review_deadline = ?, sound_demo = ?, password = ? WHERE property_id = ?");
      $editProperties->bind_param('ssssssssssiississsssssssi', $paper_title, $tmp_prologue, $tmp_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $tmp_marking, $bidirectional, $tmp_pass_mark, $tmp_distinction_mark, $folderID, $tmp_rubric, $tmp_calculator, $display_correct_answer, $display_students_response, $display_question_mark, $display_feedback, $hide_if_unanswered, $external_deadline, $internal_deadline, $tmp_sound_demo, $password, $paperID);
      $editProperties->execute();
      $editProperties->close();

      Paper_utils::update_modules($paper_modules, $paperID, $mysqli, $userObject);

      $paper_modules = Paper_utils::get_modules($paperID, $mysqli);

      Paper_utils::update_reviewers($old_externals, $new_externals, 'external', $paperID, $mysqli);
      Paper_utils::update_reviewers($old_internals, $new_internals, 'internal', $paperID, $mysqli);
    } else {

      $editProperties = $mysqli->prepare("UPDATE properties SET paper_title = ?, paper_type = ?, start_date = ?, end_date = ?, timezone = ?, paper_prologue = ?, paper_postscript = ?, bgcolor = ?, fgcolor = ?, themecolor = ?, labelcolor = ?, fullscreen = ?, marking = ?, bidirectional = ?, pass_mark = ?, distinction_mark = ?, folder = ?, labs = ?, rubric = ?, calculator = ?, exam_duration = ?, display_correct_answer = ?, display_students_response = ?, display_question_mark = ?, display_feedback = ?, hide_if_unanswered = ?, calendar_year = ?, external_review_deadline = ?, internal_review_deadline = ?, sound_demo = ?, password = ? WHERE property_id = ?");
      $editProperties->bind_param('ssssssssssssssiisssiissssssssssi', $paper_title, $paper_type, $tmp_start_date, $tmp_end_date, $timezone, $tmp_prologue, $tmp_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $tmp_marking, $bidirectional, $tmp_pass_mark, $tmp_distinction_mark, $folderID, $lab_string, $tmp_rubric, $tmp_calculator, $exam_duration, $display_correct_answer, $display_students_response, $display_question_mark, $display_feedback, $hide_if_unanswered, $calendar_year, $external_deadline, $internal_deadline, $tmp_sound_demo, $password, $paperID);
      $editProperties->execute();
      $editProperties->close();

      Paper_utils::update_modules($paper_modules, $paperID, $mysqli, $userObject);

      $paper_modules = Paper_utils::get_modules($paperID, $mysqli);

      Paper_utils::update_reviewers($old_externals, $new_externals, 'external', $paperID, $mysqli);
      Paper_utils::update_reviewers($old_internals, $new_internals, 'internal', $paperID, $mysqli);
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
    if (isset($_POST['old_questions_report']) and $_POST['old_questions_report'] == '' and isset($_POST['questions_report']) and $_POST['questions_report'] == '1') {
      $editProperties = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL, ?, NOW(), 'questions')");
      $editProperties->bind_param('i', $paperID);
      $editProperties->execute();
      $editProperties->close();

      $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', 'Question-based Feedback', 'feedback');
    }

    // Track changes
    if (isset($start_date)) {
      $tmp_start_date = $start_date->format('U');
    }
    if (isset($end_date)) {
      $tmp_end_date = $end_date->format('U');
    }

    if ($paper_title != $old_paper_title)                             $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_paper_title, $paper_title, 'name');
    if ($disabled == '') {   // If disabled is set then don't check certain disabled fields.
      if ($fullscreen != $old_fullscreen)                             $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_fullscreen, $fullscreen, 'display');
      if ($bidirectional != $old_bidirectional)                       $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_bidirectional, $bidirectional, 'navigation');
      if ($properties->get_paper_type() != '6') {
        if ($tmp_calculator != $old_calculator)                       $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_calculator, $tmp_calculator, 'displaycalculator');
      }
      if ($lab_string != $old_labs)                                   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_labs, $lab_string, 'labs');
      $new_modules = array_keys($paper_modules);
      sort($new_modules, SORT_NUMERIC);
      $new_modules = implode(',', $new_modules);
      if ($new_modules != $old_modules)                               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_modules, $new_modules, 'modules');
    }
    if ($tmp_start_date != $old_start_date)                           $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_start_date, $tmp_start_date, 'startdate');
    if ($tmp_end_date != $old_end_date)                               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_end_date, $tmp_end_date, 'enddate');
    if ($calendar_year != $old_calendar_year)                         $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_calendar_year, $calendar_year, 'session');
    if ($timezone != $old_timezone)                                   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_timezone, $timezone, 'timezone');
    if ($password != $old_password)                                   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_password, $password, 'password');
    if ($exam_duration != $old_exam_duration)                         $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_exam_duration, $exam_duration, 'duration');
    if ($tmp_rubric != $old_rubric)                                   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_rubric, $tmp_rubric, 'rubric');
    if ($tmp_prologue != $old_paper_prologue)                         $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_paper_prologue, $tmp_prologue, 'prologue');
    if ($tmp_postscript != $old_paper_postscript)                     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_paper_postscript, $tmp_postscript, 'postscript');
    if ($tmp_pass_mark != $old_pass_mark)                             $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_pass_mark, $tmp_pass_mark, 'passmark');
    if ($tmp_distinction_mark != $old_distinction_mark)               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_distinction_mark, $tmp_distinction_mark, 'distinction');
    if ($tmp_sound_demo != $old_sound_demo)                           $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_sound_demo, $tmp_sound_demo, 'demosoundclip');
    if ($properties->get_paper_type() == '6') {
      if ($display_correct_answer != $old_display_correct_answer)     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_correct_answer, $display_correct_answer, 'photos');
    } else {
      if ($display_correct_answer != $old_display_correct_answer)     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_correct_answer, $display_correct_answer, 'correctanswerhighlight');
    }
    if ($bgcolor != $old_bgcolor)                                     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_bgcolor, $bgcolor, 'background');
    if ($fgcolor != $old_fgcolor)                                     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_fgcolor, $fgcolor, 'foreground');
    if ($themecolor != $old_themecolor)                               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_themecolor, $themecolor, 'theme');
    if ($labelcolor != $old_labelcolor)                               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_labelcolor, $labelcolor, 'labelsnotes');

    if (implode(',',$new_externals) != implode(',',$old_externals))   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', $old_externals), implode(',', $new_externals), 'externals');
    if (implode(',',$new_internals) != implode(',',$old_internals))   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', $old_internals), implode(',', $new_internals), 'internals');
    if ($external_review_deadline != $old_external_review_deadline)   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_external_review_deadline, $external_review_deadline, 'externalreviewdeadline');
    if ($internal_review_deadline != $old_internal_review_deadline)   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_internal_review_deadline, $internal_review_deadline, 'internalreviewdeadline');
    if ($tmp_marking != $old_marking)                                 $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_marking, $tmp_marking, 'method');
    if ($properties->get_paper_type() == '6') {
      if ($_POST['review'] != $old_display_question_mark)             $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_question_mark, $_POST['review'], 'review');
    } else {
      if ($display_question_mark != $old_display_question_mark)       $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_question_mark, $display_question_mark, 'question_marks');
    }
    if ($display_students_response != $old_display_students_response) $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_students_response, $display_students_response, 'ticks_crosses');
    if ($hide_if_unanswered != $old_hide_if_unanswered)               $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_hide_if_unanswered, $hide_if_unanswered, 'hideallfeedback');
    if ($display_feedback != $old_display_feedback)                   $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_display_feedback, $display_feedback, 'textfeedback');
    if ($folderID != $old_folder)                                     $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_folder, $folderID, 'folder');

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

    // Set any metadata security
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

    $new_meta = '';
    for ($i=0; $i<$_POST['meta_dropdown_no']; $i++) {
      $meta_type = $_POST['meta_type' . $i];
      $meta_value = $_POST['meta_value' . $i];

      $editProperties = $mysqli->prepare("DELETE FROM paper_metadata_security WHERE paperID = ? AND name = ?");
      $editProperties->bind_param('is', $paperID, $meta_type);
      $editProperties->execute();
      $editProperties->close();

      if ($meta_value != '') {
        $editProperties = $mysqli->prepare("INSERT INTO paper_metadata_security VALUES (NULL, ?, ?, ?)");
        $editProperties->bind_param('iss', $paperID, $meta_type, $meta_value);
        $editProperties->execute();
        $editProperties->close();

        if ($new_meta == '') {
          $new_meta = $meta_type . ':' . $meta_value;
        } else {
          $new_meta .= ', ' . $meta_type . ':' . $meta_value;
        }
      }
    }
    if ($old_meta != $new_meta) $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_meta, $new_meta, 'restricttometadata');

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

    <title><?php echo $string['edittitle']; ?></title>

    <meta http-equiv="pragma" content="no-cache" />
    <script type="text/javascript">
      function closeWindow() {
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
      }
      function updateParent() {
        window.opener.parent.location = "details.php?paperID=<?php echo $paperID; ?>&module=<?php echo $first_module_id; ?>";
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

    <title><?php echo $string['edittitle']; ?></title>
  </head>
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
  $result = $mysqli->prepare("SELECT marks_incorrect FROM papers, questions, options WHERE papers.question = questions.q_id AND questions.q_id = options.o_id AND paper = ?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($marks_incorrect);
  while ($result->fetch()) {
    if ($marks_incorrect < 0) {
      $neg_marking = true;
    }
  }
  $result->close();

  // Load textual feedback
  $textual_feedback = Paper_utils::get_textual_feedback($_GET['paperID'], $mysqli);

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
  } else {
    $sum_disabled = '';
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['propertiestitle'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <style type="text/css">
    body {background-color:#F1F5FB}
    table {text-align:left}
    .r1 {text-indent:-23px; padding-left:23px; background-color:white}
    .r2 {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
    .r1disabled {text-indent:-23px; padding-left:23px; background-color:white; color:#808080}
    .r2disabled {text-indent:-23px; padding-left:23px; background-color:#DDDDDD; color:#808080}
    select {margin:0px; padding:0px}
    input[type="text"] {margin:0px}
    .button_on {background-image:url("../artwork/2007_button_on.png")}
    .button_over {background-image:url("../artwork/2007_button_over.png")}
    .button_off {background-image:none}
  </style>

  <?php echo $cfg_editor_javascript; ?>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<?php
  if ($properties->get_paper_type() == '2' or $properties->get_paper_type() == '5') {
?>
  <script type="text/javascript" src="../js/jquery-ui.1.8.16.min.js"></script>
  <script type="text/javascript" src="../js/jquery.datecopy.js"></script>
  <script type="text/javascript">
    $(function () {
      $('.datecopy').change(dateCopy);
    })
  </script>
<?php
}
?>
  <script type="text/javascript">
    $(getMeta);

    function getMeta() {
      var mod_codes = '';
      var module_no = document.getElementById('module_no').value;

      for (i=0; i<module_no; i++) {
        if (document.getElementById('module' + i).checked == true) {
          if (mod_codes == '') {
            mod_codes = document.getElementById('module' + i).value;
          } else {
            mod_codes += ',' + document.getElementById('module' + i).value;
          }
        }
      }
      $('#metadata_security').load('getMetdataSecurity.php', 'modules=' + mod_codes + '&paperID=<?php echo $_GET['paperID']; ?>&session=' + $('#session').val() );
      $('#reference_list').load('getAvailableRefMaterial.php', 'modules=' + mod_codes + '&paperID=<?php echo $_GET['paperID']; ?>');
    }

    function objreportURL() {
      if (document.getElementById('objectives_report').checked == true) {
        document.getElementById('objreport').style.display = 'block';
      } else {
        document.getElementById('objreport').style.display = 'none';
      }
    }

    function toggle(objectID) {
      if (document.getElementById(objectID).className == 'r2') {
        document.getElementById(objectID).className = 'r1';
      } else {
        document.getElementById(objectID).className = 'r2';
      }
    }

    function checkForm() {
      if (document.edit_form.fyear.value > document.edit_form.tyear.value) {
        alert ("<?php echo $string['availablefromyear']; ?>");
        return false;
      } else if (document.edit_form.fyear.value == document.edit_form.tyear.value && document.edit_form.fmonth.value > document.edit_form.tmonth.value) {
        alert ("<?php echo $string['availablefrommonth']; ?>");
        return false;
      } else if (document.edit_form.fyear.value == document.edit_form.tyear.value && document.edit_form.fmonth.value == document.edit_form.tmonth.value && document.edit_form.fday.value > document.edit_form.tday.value) {
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
        if (document.edit_form.fday.value != document.edit_form.tday.value || document.edit_form.fmonth.value != document.edit_form.tmonth.value || document.edit_form.fyear.value != document.edit_form.tyear.value) {
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
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        } else if (document.edit_form.int_tday.options[document.edit_form.int_tday.selectedIndex].value == '') {
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        } else if (document.edit_form.int_tyear.options[document.edit_form.int_tyear.selectedIndex].value == '') {
          alert("<?php echo $string['msg6a']; ?>");
          return false;
        }
      }

      if (document.edit_form.paper_title.value == '') {
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

    function buttonclick(sectionID, tabID) {
      document.getElementById('general').style.display = 'none';
      document.getElementById('security').style.display = 'none';
      document.getElementById('reviewers').style.display = 'none';
      document.getElementById('feedback').style.display = 'none';
      document.getElementById('rubric').style.display = 'none';
      document.getElementById('prologue').style.display = 'none';
      document.getElementById('postscript').style.display = 'none';
      document.getElementById('reference').style.display = 'none';
      document.getElementById('changes').style.display = 'none';
      document.getElementById(sectionID).style.display = '';

      $('#tab1').removeClass("button_on");
      $('#tab2').removeClass("button_on");
      $('#tab3').removeClass("button_on");
      $('#tab4').removeClass("button_on");
      $('#tab5').removeClass("button_on");
      $('#tab6').removeClass("button_on");
      $('#tab7').removeClass("button_on");
      $('#tab8').removeClass("button_on");
      $('#tab9').removeClass("button_on");

      $('#' + tabID).addClass("button_on");
      $('#' + tabID).removeClass("button_over");
    }

    function buttonover(tabID) {
      if (!$('#' + tabID).hasClass("button_on")) {
        $('#' + tabID).addClass("button_over");
        $('#' + tabID).removeClass("button_off");
      }
    }

    function buttonout(tabID) {
      if ($('#' + tabID).hasClass("button_over")) {
        $('#' + tabID).addClass("button_off");
        $('#' + tabID).removeClass("button_over");
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

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:140px">
<?php
if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
  echo "<tr><td id=\"tab1\" class=\"button_off\" style=\"height:25px; color:#00156E; cursor:default\" onmouseover=\"buttonover('tab1')\" onmouseout=\"buttonout('tab1')\" onclick=\"buttonclick('general','tab1')\">&nbsp;" . $string['generaltab'] . "</td></tr>\n";
  echo "<tr><td id=\"tab2\" class=\"button_on\" style=\"height:25px; color:#00156E; cursor:default\" onmouseover=\"buttonover('tab2')\" onmouseout=\"buttonout('tab2')\" onclick=\"buttonclick('security','tab2')\">&nbsp;" . $string['securitytab'] . "</td></tr>\n";
} else {
  echo "<tr><td id=\"tab1\" class=\"button_on\" style=\"height:25px; color:#00156E; cursor:default\" onmouseover=\"buttonover('tab1')\" onmouseout=\"buttonout('tab1')\" onclick=\"buttonclick('general','tab1')\">&nbsp;" . $string['generaltab'] . "</td></tr>\n";
  echo "<tr><td id=\"tab2\" class=\"button_off\" style=\"height:25px; color:#00156E; cursor:default\" onmouseover=\"buttonover('tab2')\" onmouseout=\"buttonout('tab2')\" onclick=\"buttonclick('security','tab2')\">&nbsp;" . $string['securitytab'] . "</td></tr>\n";
}
if ($properties->get_paper_type() != '6') {
  echo '<tr><td id="tab3" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab3\')" onmouseout="buttonout(\'tab3\')" onclick="buttonclick(\'feedback\',\'tab3\')">&nbsp;' . $string['feedback'] . '</td></tr>';
  echo '<tr><td id="tab4" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab4\')" onmouseout="buttonout(\'tab4\')" onclick="buttonclick(\'reviewers\',\'tab4\')">&nbsp;' . $string['reviewerstab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab3" class="button_off" style="display:none">&nbsp;' . $string['feedback'] . '</td></tr>';
  echo '<tr><td id="tab4" class="button_off" style="display:none">&nbsp;' . $string['reviewerstab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
  echo '<tr><td id="tab5" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab5\')" onmouseout="buttonout(\'tab5\')" onclick="buttonclick(\'rubric\',\'tab5\')">&nbsp;' . $string['rubrictab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab5" class="button_off" style="display:none">&nbsp;' . $string['rubrictab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5') {
  echo '<tr><td id="tab6" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab6\')" onmouseout="buttonout(\'tab6\')" onclick="buttonclick(\'prologue\',\'tab6\')">&nbsp;' . $string['prologuetab'] . '</td></tr>';
  echo '<tr><td id="tab7" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab7\')" onmouseout="buttonout(\'tab7\')" onclick="buttonclick(\'postscript\',\'tab7\')">&nbsp;' . $string['postscripttab'] . '</td></tr>';
} else {
  echo '<tr><td id="tab6" class="button_off" style="display:none">&nbsp;' . $string['prologuetab'] . '</td></tr>';
  echo '<tr><td id="tab7" class="button_off" style="display:none">&nbsp;' . $string['postscripttab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
  echo '<tr><td id="tab8" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover(\'tab8\')" onmouseout="buttonout(\'tab8\')" onclick="buttonclick(\'reference\',\'tab8\')">&nbsp;' . $string['referencematerial'] . '</td></tr>';
} else {
  echo '<tr><td id="tab8" class="button_off" style="display:none">&nbsp;' . $string['referencematerial'] . '</td></tr>';
}
?>
<tr><td id="tab9" class="button_off" style="height:25px; color:#00156E; cursor:default" valign="middle" onmouseover="buttonover('tab9')" onmouseout="buttonout('tab9')" onclick="buttonclick('changes','tab9')">&nbsp;<?php echo $string['changes']; ?></td></tr>
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

     echo '<tr><td align="right" valign="top">' . $string['url'] . '&nbsp;</td><td colspan="3">';
     switch ($properties->get_paper_type()) {
       case '2':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "\" target=\"_blank\" style=\"color:blue\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "</a> " . $string['onlyonexamday'];
         break;
       case '4':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/osce/\" target=\"_blank\" style=\"color:blue\">" . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/osce/</a> " . $string['onlyonexamday'];
         break;
       case '5':
         echo $string['na'];
         break;
       case '6':
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/peer_review/form.php?id=" . urlencode($properties->get_crypt_name()) ."\" target=\"_blank\" style=\"color:blue\">" . $configObject->get('cfg_root_path') . "/peer_review/form.php?id=" . urlencode($properties->get_crypt_name()) ."</a>";
         break;
       default:
         echo "<a href=\"" . $configObject->get('cfg_root_path') . "/user_index.php?id=" . urlencode($properties->get_crypt_name()) ."\" target=\"_blank\" style=\"color:blue\">" . $configObject->get('cfg_root_path') . "/user_index.php?id=" . urlencode($properties->get_crypt_name()) ."</a>";
     }
     echo "</td></tr>\n";
     echo "<tr><td align=\"right\" valign=\"top\">" . $string['name'] . "&nbsp;</td><td colspan=\"3\"><input type=\"text\" size=\"75\" maxlength=\"255\" value=\"" . $properties->get_paper_title() . "\" name=\"paper_title\"$disabled /><input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\"></td></tr>\n";
   ?>
    <tr><td align="right" valign="top"><?php echo $string['type']; ?>&nbsp;</td><td>
   <?php
    if ($properties->get_paper_type() == '0') {
      echo "<select name=\"paper_type\" onclick=\"changeType();\">";
      echo "<option value=\"0\" selected=\"selected\" />" . $string['formative self-assessment'] . "</option>\n";
      echo "<option value=\"1\" />" . $string['progress test'] . "</option>\n";
    } elseif ($properties->get_paper_type() == '1') {
      echo "<select name=\"paper_type\" onclick=\"changeType();\">";
      echo "<option value=\"0\" />" . $string['formative self-assessment'] . "</option>\n";
      echo "<option value=\"1\" selected=\"selected\" />" . $string['progress test'] . "</option>\n";
    } else {
      echo "<select name=\"paper_type\" disabled>";
      $tmp_types = array('formative self-assessment', 'progress test', 'summative exam', 'survey', 'osce station', 'offline paper', 'peer review');
      echo "<option value=\"0\" selected=\"selected\" />" . $string[$tmp_types[$properties->get_paper_type()]] . "</option>\n";
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
    echo "</select>\n</td></tr>\n";

    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if ($properties->get_paper_type() == '4') {
      echo '<input type="hidden" name="bgcolor" value="' . $properties->get_bgcolor() . '" />';
      echo '<input type="hidden" name="fgcolor" value="' . $properties->get_fgcolor() . '" />';
      echo '<input type="hidden" name="themecolor" value="' . $properties->get_themecolor() . '" />';
      echo '<input type="hidden" name="labelcolor" value="' . $properties->get_labelcolor() . '" />';
      echo '<input type="hidden" name="fullscreen" value="' . $properties->get_fullscreen() . '" />';
    } else {
      echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA;color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['displayoptions'] ."</td></tr>\n";
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
        echo "&nbsp;" . $string['ifavailable'] . "</td></tr>\n";
      } else {
        if ($properties->get_calculator() == 1) {
          $checked = ' checked="checked"';
        } else {
          $checked = '';
        }
        echo "<tr><td align=\"right\">" . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"calculator\" name=\"calculator\"$checked$disabled /> <label for=\"calculator\">" . $string['displaycalculator'] . "</label></td>";

        if ($properties->get_sound_demo() == 1) {
          $checked = ' checked="checked"';
        } else {
          $checked = '';
        }
        echo "<td align=\"right\">" . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"sound_demo\" name=\"sound_demo\"$checked$disabled /> <label for=\"sound_demo\">" . $string['demosoundclip'] . "</label></td></tr>\n";
      }

      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    }
    echo "<tr><td colspan=\"4\" style=\"background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB\">&nbsp;" . $string['marking'] . "</td></tr>\n";
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if ($properties->get_paper_type() == '4') {    // OSCE Stations
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\">\n<select name=\"pass_mark\" id=\"pass_mark\">\n";
      if ($properties->get_pass_mark() == 102) {
        echo "<option value=\"102\">N/A</option>";
      } else {
        echo "<option value=\"102\" selected>N/A</option>";
      }
      if ($properties->get_pass_mark() == 101) {
        echo "<option value=\"101\" selected>Borderline Method</option>";
      } else {
        echo "<option value=\"101\">Borderline Method</option>";
      }
      for ($i=0; $i<=100; $i++) {
        if ($i == $properties->get_pass_mark()) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td></tr>";
      echo "<tr><td align=\"right\" valign=\"top\"><nobr>" . $string['overallclassification'] . "</nobr>&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\">";
    ?>
      <option value="3"<?php if ($properties->get_marking() == '3') echo ' selected'; ?> /><?php echo $string['overallclass2']; ?></option>
      <option value="4"<?php if ($properties->get_marking() == '4') echo ' selected'; ?> /><?php echo $string['overallclass3']; ?></option>
      <option value="6"<?php if ($properties->get_marking() == '6') echo ' selected'; ?> /><?php echo $string['overallclass4']; ?></option>
  <?php
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"4\">" . $string['markingguidance'] . "</td></tr>\n";
    echo "<tr><td colspan=\"4\">" . wysiwyg_editor('oEdit1', 'osce_marking_guidance', $properties->get_paper_prologue(), 684, 230);
  ?>
</td></tr>
    <?php
      echo "</select></td></tr>\n";
    } elseif ($properties->get_paper_type() == '6') {  // Peer Review
      $review = $properties->get_display_question_mark();

      echo "<tr><td align=\"right\">" . $string['groupdetails'] . "&nbsp;</td><td><select name=\"type\">\n";
      echo "<option value=\"\" selected>&nbsp;</option>\n";

      $modules_array = Paper_utils::get_modules($paperID, $mysqli);

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
    } else {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['passmark'] . "&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\"";
      if ($properties->get_paper_type() == '3') echo ' disabled';
      echo '>';
      for ($i=0; $i<=100; $i++) {
        if ($i == $properties->get_pass_mark()) {
          echo "<option value=\"$i\" selected>$i%</option>\n";
        } else {
          echo "<option value=\"$i\">$i%</option>\n";
        }
      }
      echo "</select></td><td rowspan=\"2\" style=\"text-align:right\" valign=\"top\">" . $string['method'] . "&nbsp;</td><td rowspan=\"2\">";
    ?>
       <input type="radio" id="marking1" name="marking" value="0"<?php if ($properties->get_marking() == '0') echo ' checked'; ?> /><?php echo $string['noadjustment']; ?><br />
       <input type="radio" id="marking2" name="marking" value="1"<?php
       if ($properties->get_marking() == '1') {
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
        if (substr($properties->get_marking(), 0, 1) == '2') echo ' checked';
        echo " />";
        echo $string['stdset'] . ' <select name="std_set">';
        $std_set_details->bind_result($std_set_title, $std_set_surname, $std_set_initials, $std_set_reviewer, $std_set_display_date, $std_set_date, $group_review);
        while ($std_set_details->fetch()) {
          if ($group_review == 'No') {
            if ($properties->get_marking() == "2,$std_set_reviewer,$std_set_date") {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\" selected>$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
            } else {
              echo "<option value=\"2,$std_set_reviewer,$std_set_date\">$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
            }
          } else {
            if ($properties->get_marking() == "2,$std_set_reviewer,$std_set_date") {
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
    if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2') {
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['distinction'] . "</td><td><select name=\"distinction_mark\">";
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

<table id="prologue" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/prologue_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['prologueheading']; ?></td></tr>
  <?php
    echo "<tr><td>" . wysiwyg_editor('oEdit2', 'paper_prologue', $properties->get_paper_prologue(), 722, 520);
  ?>
</td></tr>
</table>

<table id="postscript" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/postscript_heading_icon.png" width="22" height="29" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['postscriptheading']; ?></td></tr>
<?php
    echo "<tr><td>" . wysiwyg_editor('oEdit3', 'paper_postscript', $properties->get_paper_postscript(), 722, 520);
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
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/security_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['securityheading']; ?></td></tr>
<tr>
<td style="text-align:center; vertical-align:top" colspan="2">
<?php

    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" style=\"width:100%; padding-bottom:10px\">\n";
    echo "<tr><td align=\"right\">" . $string['session'] . "</td><td><select name=\"calendar_year\" id=\"session\" onchange=\"getMeta();\"$sum_disabled>\n<option value=\"\">" . $string['na'] .  "</option>\n";
    
    $stop_year = date("Y") + 3;
    for ($year=2002; $year<$stop_year; $year++) {
      $next_year = ($year - 2000) + 1;
      $value = $year . '/' . $next_year;
      echo "<option value=\"" . $value . "\"";
      if ($properties->get_calendar_year() == $value) echo 'selected';
      echo ">";
      echo $value . "</option>\n";
    }

    if ($properties->get_paper_type() == '4') {
      echo "</select></td><td></td><td><input type=\"hidden\" size=\"20\" name=\"password\" value=\"" . $properties->get_password() . "\" /></td></tr>\n";
    } else {
      echo "</select></td><td align=\"right\">" . $string['password'] . "</td><td><input type=\"text\" size=\"20\" name=\"password\" value=\"" . $properties->get_password() . "\"$disabled /></td></tr>\n";
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
    echo "<td align=\"right\">" . $string['duration'] . "</td><td><select name=\"exam_duration\"$sum_disabled>";
    $minutes = array('NULL'=>$string['na'],'15'=>'15','20'=>'20','25'=>'25','30'=>'30','35'=>'35','40'=>'40','45'=>'45','50'=>'50','55'=>'55','60'=>'60','65'=>'65','70'=>'70','75'=>'75','80'=>'80','85'=>'85','90'=>'90','95'=>'95','100'=>'100','110'=>'110','120'=>'120','150'=>'150','180'=>'180');
    foreach ($minutes as $key => $value) {
      echo "<option value=\"" . $key . "\"";
      if ($properties->get_exam_duration() == $key) echo 'selected="selected"';
      echo ">";
      echo $value . "</option>\n";
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
    echo "</select>\n";
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
    echo "</select>\n";
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
    echo "</select>\n<select id=\"ftime\" name=\"ftime\" class=\"datecopy\"$sum_disabled>\n";
    // Available from Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    if ($start_date == '') {
      echo '<option value=""></option>';
    }
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00' and $start_date != '') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
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
    echo "</select>\n";

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
    echo "</select>\n";
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
    echo "</select>&nbsp;<select id=\"ttime\" name=\"ttime\" class=\"datecopy\"$sum_disabled>\n";
    // Available to Hour
    if ($end_date == '') {
      echo '<option value=""></option>';
    }
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00' and $end_date != '') {
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

    $modules_array = Paper_utils::get_modules($paperID, $mysqli);

    $q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

    $total_modules = array_merge($staff_modules, $modules_array);

    $module_sql = implode("','", $total_modules);
    if ($module_sql != '') $module_sql = "'$module_sql'";

    if ($module_sql == '') {
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"0\" /></div>\n";
    } else {
      $module_array = $userObject->get_staff_accessable_modules();
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
          if (in_array($module['id'], $staff_modules) or $userObject->has_role('SysAdmin')) {
            echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMeta();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\" checked $disabled>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          } else {
            echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['idMod'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['idMod'] . "\" checked>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          }
        } else {
          echo "<div class=\"r1\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no'); getMeta();\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\"$disabled>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
        }
        $module_no++;
        $old_school = $module['school'];
      }
      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
    }
    echo "</td>\n";

    echo "<td>" . output_labs($properties->get_labs(), $configObject->get('cfg_summative_mgmt'), $properties->get_paper_type(), $userObject, $mysqli) . "</td></tr>\n";

  ?>
  </td></tr>
  <tr><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB; padding:2px" colspan="2">&nbsp;<?php echo $string['restricttometadata']; ?></td></tr>
  <tr><td style="vertical-align:top; height:110px" colspan="2"><div style="height:116px; overflow-y:scroll;border:1px solid #7F9DB9; font-size:90%" id="metadata_security"></div></td></tr>
  </table>
  </td></tr>
</table>

<table id="rubric" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
  <tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/rubric_heading_icon.png" width="34" height="34" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['rubricheading']; ?></td></tr>
  <tr><td><?php echo wysiwyg_editor('oEdit4', 'rubric_text', $properties->get_rubric(), 722, 520); ?></td></tr>
</table>

<table id="feedback" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
  <tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/feedback_heading_icon.png" width="34" height="34" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['feedbackheading']; ?></td></tr>

  <?php
     echo "<tr><td colspan=\"2\" valign=\"top\">";

     echo "<table cellspacing=\"0\" cellpadding=\"6\" border=\"0\" style=\"margin:15px\">\n";

     if (in_array($properties->get_paper_type(), array('0', '1', '2', '4', '5'))) {
       echo '<tr><td><img src="../artwork/feedback_release_icon.png" width="48" height="48" />';
       // Objectives-based Feedback
       $idfeedback_release = '';
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id = ? AND type = 'objectives'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->store_result();
       $feedback_details->fetch();
       $ob_feedback_release = ($feedback_details->num_rows > 0) ? '1' : '';
       echo "<td><input type=\"hidden\" name=\"old_objectives_report\" value=\"$ob_feedback_release\" />";
       if ($ob_feedback_release == '') {
         echo "<input type=\"radio\" name=\"objectives_report\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"objectives_report\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
       } else {
         echo "<input type=\"radio\" name=\"objectives_report\" value=\"1\" checked=\"checked\" />". $string['on'] . "</td><td><input type=\"radio\" name=\"objectives_report\" value=\"0\" />" . $string['off'] . "</td>";
       }
       $feedback_details->close();

       echo "<td>" . $string['objectivesreport'] . "<br /><a href=\"https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=" . $properties->get_crypt_name() . "\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/mapping/user_feedback.php?id=" . $properties->get_crypt_name() . "</a></td></tr>\n";
     }
     if ($q_feedback_enabled and in_array($properties->get_paper_type(), array('1', '2', '4', '5'))) {
       echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
       echo '<tr><td><img src="../artwork/question_release_icon.png" width="48" height="48" />';
       // Question-based Feedback
       $q_feedback_release = '';
       $feedback_details = $mysqli->prepare("SELECT idfeedback_release FROM feedback_release WHERE paper_id = ? AND type = 'questions'");
       $feedback_details->bind_param('i', $_GET['paperID']);
       $feedback_details->execute();
       $feedback_details->store_result();
       $feedback_details->fetch();
       $q_feedback_release = ($feedback_details->num_rows > 0) ? '1' : '';
       echo "<td><input type=\"hidden\" name=\"old_questions_report\" value=\"$q_feedback_release\" />";
       if ($q_feedback_release == '') {
         echo "<input type=\"radio\" name=\"questions_report\" value=\"1\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"questions_report\" value=\"0\" checked=\"checked\" />" . $string['off'] . "</td>";
       } else {
         echo "<input type=\"radio\" name=\"questions_report\" value=\"1\" checked=\"checked\" />" . $string['on'] . "</td><td><input type=\"radio\" name=\"questions_report\" value=\"0\" />" . $string['off'] . "</td>";
       }
       $feedback_details->close();

       echo "<td>" . $string['questionfeedback'] . "<br />";
       if ($properties->get_paper_type() == '2') echo '<span style="color:#C00000">' . $string['feedbackwarning'] . '</span></br />';
       echo "<a href=\"https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=" . $properties->get_crypt_name() . "\" style=\"color:blue\" target=\"_blank\">https://" . $_SERVER['HTTP_HOST'] . "/paper/feedback.php?id=" . $properties->get_crypt_name() . "</a></td></tr>\n";
     }

     echo "</table>\n";

     if ($properties->get_paper_type() == '0') {
       echo '<table cellpadding="3" cellspacing="0" border="0" id="feedback_on" style="width:100%">';
     } else {
       echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="width:100%; display:none">';
     }
     if ($properties->get_paper_type() != '4') {
     ?>
     <tr><td colspan="4"style="background-color:#E5EFFA; color:#00156E; border-bottom: 1px solid #CFDBEB">&nbsp;<?php echo $string['answerscreensettings']; ?></td></tr>
     <tr><td colspan="4">&nbsp;</td></tr>
     <tr><td style="width:33%"><input type="checkbox" name="display_students_response" value="1"<?php if ($properties->get_display_students_response() == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['ticks_crosses'];?></td><td style="width:33%"><input type="checkbox" name="display_question_mark" value="1"<?php if ($properties->get_display_question_mark() == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['question_marks'];?></td><td rowspan="2" style="width:33%; text-indent:-24px; padding-left:24px"><input type="checkbox" name="hide_if_unanswered" value="1"<?php if ($properties->get_hide_if_unanswered() == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['hideallfeedback'];?></td></tr>
     <tr><td><input type="checkbox" name="display_correct_answer" value="1"<?php if ($properties->get_display_correct_answer() == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['correctanswerhighlight'];?></td><td><input type="checkbox" name="display_feedback" value="1"<?php if ($properties->get_display_feedback() == '1') echo ' checked'; ?> />&nbsp;<?php echo $string['textfeedback'];?></td></tr>
     <?php
     }
     echo "</table>\n";
     if ($properties->get_paper_type() != '0') {
       echo '<div id="feedback_off">';
     } else {
       echo '<div id="feedback_off" style="display:none">';
     }
     echo "<br />&nbsp;</div>";

     echo "</td></tr>\n";

     if ($properties->get_paper_type() != '2' and $properties->get_paper_type() != '4') {
       echo "<tr><td colspan=\"2\"style=\"background-color:#E5EFFA; color:#00156E; border-bottom: 1px solid #CFDBEB\">&nbsp;" . $string['textualfeedback'] . "</td></tr>\n";
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
         echo "</select></td><td><textarea name=\"feedback_msg$i\" cols=\"60\" rows=\"1\" style=\"width:620px\">$msg</textarea></td></tr>\n";
       }
     }
     ?>
</table>

<table id="reviewers" style="font-size:90%; width:100%; height:460px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/reviewers_heading_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['reviewersheading']; ?></td></tr>
<tr>
<td align="center" colspan="2">
<table cellpadding="1" cellspacing="2" border="0">
<tr><td colspan="3">&nbsp;<?php
  $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='sct'");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($sct_no);
  $result->fetch();
  $result->close();
  if ($sct_no > 0) {
    echo '<a href="' . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '" target="_blank" style="color:blue">' . $protocol . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '</a>';
  }

?></td></tr>
<tr><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB">&nbsp;<?php echo $string['internalreviewers']; ?></td><td>&nbsp;&nbsp;</td><td style="background-color:#E5EFFA; color:#00156E; border-bottom:1px solid #CFDBEB">&nbsp;<?php echo $string['externalexaminers']; ?></td></tr>
<tr><td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
    if ($properties->get_internal_review_deadline() == '') {
      $split_year = 0;
      $split_month = 0;
      $split_day = 0;
    } else {
      $internal_review_deadline = DateTime::createFromFormat('U', $properties->get_internal_review_deadline(), $local_time);
      $internal_review_deadline->setTimezone($local_time);

      $split_year = $internal_review_deadline->format('Y');
      $split_month = $internal_review_deadline->format('m');
      $split_day = $internal_review_deadline->format('d');
    }

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
    if ($properties->get_external_review_deadline() == '') {
      $split_year = 0;
      $split_month = 0;
      $split_day = 0;
    } else {
      $external_review_deadline = DateTime::createFromFormat('U', $properties->get_external_review_deadline(), $local_time);
      $external_review_deadline->setTimezone($local_time);

      $split_year = $external_review_deadline->format('Y');
      $split_month = $external_review_deadline->format('m');
      $split_day = $external_review_deadline->format('d');
    }

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
  echo "<tr><td><div style=\"width:343px; height:473px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";

  // Get all users for teams within the schools of the current user
  // Also get all admin users for those schools
  $school_sql = '';
  $admin_school_sql = '';
  $schools = getSchools($staff_modules, $mysqli);

  if (count($schools) > 0) {
    $schools_list = implode(',', $schools);
    $school_sql = "AND schoolid IN ($schools_list)";
    $admin_school_sql = <<< SQL
UNION SELECT DISTINCT users.id, title, initials, surname, first_names
FROM users, admin_access
WHERE users.id=admin_access.userID AND admin_access.schools_id IN ($schools_list)
SQL;
  }

  // Make sure that current reviewers always appear on the list
  $current_internals = $properties->get_internal_reviewers();
  $current_internals_sql = '';
  if (count($properties->get_internal_reviewers()) > 0) {
    $current_internals_sql = 'UNION SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE id IN (' . implode(',', $current_internals) . ')';
  }

  $query = "SELECT DISTINCT users.id, title, initials, surname, first_names FROM users, modules_staff, modules WHERE roles != 'Left' AND users.id = modules_staff.memberID AND modules.id = modules_staff.idMod $school_sql $admin_school_sql $current_internals_sql ORDER BY surname, initials";

  $internal_details = $mysqli->prepare($query);
  $internal_details->execute();
  $internal_details->bind_result($internal_id, $internal_title, $internal_initials, $internal_surname, $internal_first_names);
  $internal_no = 0;
  while ($internal_details->fetch()) {
    $match = false;
    foreach ($current_internals as $individual_internal) {
      if ($internal_id == $individual_internal) $match = true;
    }
    if ($match) {
      echo "<div class=\"r2\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\" checked>&nbsp;<label for=\"internal$internal_no\">" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    } else {
      echo "<div class=\"r1\" id=\"divinternal$internal_no\"><input type=\"checkbox\" onclick=\"toggle('divinternal$internal_no')\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\">&nbsp;<label for=\"internal$internal_no\">" . ucwords(strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    }
    $internal_no++;
  }
  $internal_details->close();
  echo "<input type=\"hidden\" id=\"internal_no\" name=\"internal_no\" value=\"$internal_no\" /></div></td><td></td>";

  echo "<td><div style=\"width:343px; height:473px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $current_externals = $properties->get_externals();
  $external_details = $mysqli->prepare("SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE roles='External Examiner' AND grade != 'left' ORDER BY surname, initials");
  $external_details->execute();
  $external_details->bind_result($external_id, $external_title, $external_initials, $external_surname, $external_first_names);
  $examiner_no = 0;
  while ($external_details->fetch()) {
    $match = false;
    foreach ($current_externals as $individual_external) {
      if ($external_id == $individual_external) $match = true;
    }
    if ($match) {
      echo "<div class=\"r2\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\" checked>&nbsp;<label for=\"examiner$examiner_no\">" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
    } else {
      echo "<div class=\"r1\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" onclick=\"toggle('divexaminer$examiner_no')\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\">&nbsp;<label for=\"examiner$examiner_no\">" . ucwords(strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
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

<table id="reference" style="width:100%; font-size:90%; height:590px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/toggle_log.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['referenceheading']; ?></td></tr>
<tr><td style="vertical-align:top"><div id="reference_list"></div></td></tr>
</table>

<table id="changes" style="width:100%; font-size:90%; height:460px; display:none" border="0" cellpadding="0" cellspacing="0">
<tr><td style="background-image:url('../artwork/blank_heading.png'); color:#001687; height:49px; font-size:110%" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../artwork/version_icon.png" width="32" height="32" alt="Icon" align="middle" />&nbsp;&nbsp;<?php echo $string['changesheading']; ?></td></tr>
<tr><td style="vertical-align:top"><div id="change_list" style="height:550px; overflow-y:scroll;border:1px solid #7F9DB9">
<table cellspacing="0" cellpadding="2" border="0" style="width:100%">
<?php
$modules = module_utils::get_module_list_by_id($mysqli);

$user_list = array();
$results = $mysqli->prepare("SELECT id, title, surname FROM users WHERE roles != 'student'");
$results->execute();
$results->bind_result($id, $title, $surname);
while ($results->fetch()) {
  $user_list[$id] = $title . ' ' . $surname;
}
$results->close();

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
$changes = $logger->get_changes('Paper', $paperID);
$rows = count($changes);
for ($i=0; $i<$rows; $i++) {
  $part = $changes[$i]['part'];

  $old = $changes[$i]['old'];
  $new = $changes[$i]['new'];
  if ($part == 'startdate' or $part == 'enddate') {
    $old = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $old);
    $new = date($configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $new);
  } elseif ($part == 'externalreviewdeadline' or $part == 'internalreviewdeadline') {
    if ($old != '') $old = date($configObject->get('cfg_long_date_php'), $old);
    if ($new != '') $new = date($configObject->get('cfg_long_date_php'), $new);
  } elseif ($part == 'modules') {
    $old = format_modules($old, $modules);
    $new = format_modules($new, $modules);
  } elseif ($part == 'folder') {
    $old = format_folders($old, $folders);
    $new = format_folders($new, $folders);
  } elseif ($part == 'method') {
    $old = format_method($old, $string);
    $new = format_method($new, $string);
  } elseif ($part == 'displaycalculator' or $part == 'demosoundclip' or $part == 'photos' or $part == 'ticks_crosses' or $part == 'hideallfeedback' or $part == 'textfeedback' or $part == 'correctanswerhighlight' or $part == 'question_marks') {
    $old = format_on_off($old, $string);
    $new = format_on_off($new, $string);
  } elseif ($part == 'externals' or $part == 'internals') {
    $old = format_user($old, $user_list);
    $new = format_user($new, $user_list);
  } elseif ($part == 'background' or $part == 'foreground' or $part == 'theme' or $part == 'labelsnotes') {
    $old = format_color($old);
    $new = format_color($new);
  } elseif ($part == 'referencematerial') {
    $old = format_referencematerial($old, $reference_material);
    $new = format_referencematerial($new, $reference_material);
  } elseif ($part == 'display') {
    $old = format_display($old, $string);
    $new = format_display($new, $string);
  } elseif ($part == 'navigation') {
    $old = format_navigation($old, $string);
    $new = format_navigation($new, $string);
  } elseif ($part == 'review') {
    $old = format_review($old, $string);
    $new = format_review($new, $string);
  } elseif ($part == 'passmark') {
    $old = format_passmark($old, $string);
    $new = format_passmark($new, $string);
  }

  if (isset($string[$part])) $part = $string[$part];
  echo "<tr><td>" . ucfirst($part) . "</td><td>$old</td><td>$new</td><td>" . date($configObject->get('cfg_short_date_php') . ' ' . $configObject->get('cfg_short_time_php'), $changes[$i]['date']) . "</td><td>" . $changes[$i]['title'] . " " . $changes[$i]['surname'] . "</td><tr>\n";
}
?>
</table>
</div></td></tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" style="width:100px" name="Submit" value="<?php echo $string['ok']; ?>">&nbsp;<input type="button" name="home" style="width:100px" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></td></tr>
</table>

<input type="hidden" name="noadd" value="<?php if (isset($_GET['noadd'])) echo $_GET['noadd']; ?>" />
<input type="hidden" name="caller" value="<?php echo $_GET['caller']; ?>" />
</form>
<?php
  }
$mysqli->close();
?>
</body>
</html>
