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
* Copies a paper (e.g. properties table) and possibly the questions on the paper.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/media.inc';
require_once '../include/mapping.inc';

$paperid = check_var('paperID', 'POST', true, false, true);

if (!Paper_utils::paper_exists($paperid, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!Paper_utils::is_paper_title_unique($_POST['new_paper'], $mysqli)) {			// If the paper title is unique.
  ?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  </head>
  <body>
  <table border="0" width="100%" height="100%">
  <tr><td valign="middle">
  <div align="center">

  <table border="0" cellpadding="4" cellspacing="1" style="background-color:#FF0000">
  <tr>
  <td valign="middle" style="background-color: white"><img src="../artwork/exclamation_red_bg.png" width="32" height="32" alt="<?php echo $string['warning']; ?>" />&nbsp;&nbsp;<span style="font-size:150%; font-weight:bold; color:#C00000"><?php echo $string['titlewarning']; ?></span></td>
  </tr>
  <tr>
  <td style="background-color:#FFC0C0">
  <p style="font-size:90%"><?php printf($string['nameused'], $_POST['new_paper']); ?></p>

  <div align="center"><input style="width:120px" type="button" value="<?php echo $string['back'] ?>" name="back" onclick="window.history.go(-1);"></div>
  </td>
  </tr>
  </table>

  </div>
  </td></tr>
  </table>
  </body>
  </html>
  <?php
  exit;
}

/**
 * This function compares the old and the new courses session objectives to see which can be copied.
 * 
 * @param array $mappings_copy_objID - objectives to map
 * @param array $old_course - old course objective information
 * @param array $new_course - new course objective information
 */
function copy_between_sessions (&$mappings_copy_objID, &$old_course, &$new_course) {
      foreach ($old_course as $module => &$sessions) {
        foreach ($sessions as $identifier => &$session) {
          if (!empty($session['objectives'])) {
            foreach ($session['objectives'] as &$obj) {
              if (isset( $obj['id'])) {
                $old_objID = $obj['id'];
              } else {
                $old_objID = NULL;
              }
              if (isset($obj['guid'])) {
                $old_objGUID = $obj['guid'];
              } else {
                $old_objGUID = NULL;
              }
              // VLE Objectives.
              if (!empty($new_course[$module][$identifier]['VLE']) and !empty($old_course[$module][$identifier]['VLE'])) {
                if ($new_course[$module][$identifier]['VLE'] == $old_course[$module][$identifier]['VLE']) {
                    if (isset($new_course[$module][$identifier]['objectives'])){
                        foreach ($new_course[$module][$identifier]['objectives'] as $new_obj) {
                          if (((array_key_exists('id', $new_obj) and $new_obj['id'] == $old_objID)
                                  or (array_key_exists('guid', $new_obj) and $new_obj['guid'] == $old_objGUID))
                                  and (array_key_exists('content', $new_obj) and array_key_exists('content', $obj)
                                          and $new_obj['content'] == $obj['content'])) {
                            // Build a list of objectives that are still in both sessions
                            $mappings_copy_objID[$old_objID] = $new_obj['id'];
                            break;
                          }
                        }
                    }
                }
              // Internal Rogo Objectives.
              } else {
                foreach ($new_course as $module => &$sessions) {
                  foreach ($sessions as $identifier => &$session) {
                      if (isset($session['objectives'])){
                        foreach ($session['objectives'] as $new_obj) {
                          if (array_key_exists('content', $new_obj) and array_key_exists('content', $obj)) {
                            // Brefore comparing the contents strip out all no alpha numeric characters and convert to lowecase.
                            $new_content_check = strtolower($new_obj['content']);
                            $new_content_check = preg_replace("/[^a-z0-9]/", '', $new_content_check);
                            $old_content_check = strtolower($obj['content']);
                            $old_content_check = preg_replace("/[^a-z0-9]/", '', $old_content_check);
                            if ($new_content_check == $old_content_check) {
                                // Build a list of objectives that are still in both sessions
                                $mappings_copy_objID[$old_objID] = $new_obj['id'];
                                break;
                            }
                          }
                        }
                      }
                  }
                }
              }
            }
          }
        }
      }
}

$calendar_year = $new_calendar_year = '';
$moduleID = NULL;
$error = array();
if ($_POST['copytype'] == 'paperonly') {        // Copy the paper only!
  // Copy the properties (properties table)
  $new_paper_id = copyProperties($mysqli, $calendar_year, $new_calendar_year, $moduleIDs, $userObject, $configObject);

  // Copy the question pointers (papers table)
  $result = $mysqli->prepare("SELECT question, screen, display_pos FROM papers WHERE paper = ?");
  $result->bind_param('i', $paperid);
  $result->execute();
  $result->store_result();
  $result->bind_result($question, $screen, $display_pos);
  $qids = array();
  while ($result->fetch()) {
    $qids[] = $question;
    
    Paper_utils::add_question($new_paper_id, $question, $screen, $display_pos, $mysqli);
  }
  $result->close();

  // If we are copying in the same session we can copy the objectives
  if (count($qids) > 0) {
    $qids = implode(',', $qids);
    if ($new_calendar_year == $calendar_year) {
      $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, idMod, $new_paper_id as paper_id, question_id, obj_id,"
        . " calendar_year, vle_api, map_level FROM relationships WHERE question_id IN ($qids) AND paper_id = ?)");
      $result->bind_param('i', $paperid);
      $result->execute();
      $result->close();
    } else {
        // We are copying between sessions we need to check for changed sessions/objectives
        $mappings_copy_objID = array();
        $old_course = getObjectives($moduleIDs, $calendar_year, $paperid, '', $mysqli);
        $new_course = getObjectives($moduleIDs, $new_calendar_year, $paperid, '', $mysqli);
        if (count($old_course) > 0 and count($new_course) > 0) {
            copy_between_sessions($mappings_copy_objID, $old_course, $new_course);
            //Copy the objectives for each session where the objective still exists
            $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, idMod, ? as paper_id, question_id, ?, ?, vle_api, map_level"
              . " FROM relationships WHERE question_id IN ($qids) AND paper_id = ? AND obj_id = ?)");
            foreach ($mappings_copy_objID as $oldmapid => $newmapid) {
                $result->bind_param('iisii', $new_paper_id, $newmapid, $new_calendar_year, $paperid, $oldmapid);
                $result->execute();
            }
           $result->close();
        }

    }
  }
} else {    // Copy the paper and the questions.
  // Copy the properties (properties table)
  $new_paper_id = copyProperties($mysqli, $calendar_year, $new_calendar_year, $moduleIDs, $userObject, $configObject);
  $mediadirectory = rogo_directory::get_directory('media');

  // Get question statuses
  $default_status = -1;
  $status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
  // Set copies of retired questions to default statuses
  foreach ($status_array as $tmp_status) {
    if ($tmp_status->get_is_default()) {
      $default_status = $tmp_status->id;
      break;
    }
  }

  // Copy the question and option data (questions and options tables)
  $old_qids = array();
  $new_qids = array();
  $q_no = 0;
  $result = $mysqli->prepare("SELECT question, screen, display_pos FROM papers WHERE paper = ? ORDER BY display_pos");
  $result->bind_param('i', $paperid);
  $result->execute();
  $result->store_result();
  $result->bind_result($question, $screen, $display_pos);
  while ($result->fetch()) {
    $line = 0;
    $qData = $mysqli->prepare("SELECT * FROM questions LEFT JOIN options ON questions.q_id = options.o_id WHERE q_id = ? ORDER BY id_num");
    $qData->bind_param('i', $question);
    $qData->execute();
    $qData->store_result();
    $qData->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $owner, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $score_method, $settings, $guid, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks_correct, $marks_incorrect, $marks_partial);
    while ($qData->fetch()) {
      $old_qids[$question] = $question;
      // Question data
      if ($line == 0) {
        if ($q_type != 'info') $q_no++;
        if (trim($q_media) != '') {
          $media_array = array();
          $media_array = explode('|', $q_media);
          $new_q_media = '';
          $image_part = 0;
          foreach ($media_array as $individual_media) {
            if ($line == 0) {
              $new_media_name = '';
              if (trim($individual_media) != '' and trim($individual_media) != 'NULL') {
                $new_media_name = unique_filename($individual_media);
                if (file_exists($mediadirectory->fullpath($individual_media))) {
                  if (!copy($mediadirectory->fullpath($individual_media), $mediadirectory->fullpath($new_media_name))) {
                    $error[] = sprintf($string['copyerror'], $individual_media);
                    // If the image is missing dont put the file name in the new question
                    $new_media_name = '';
                  }
                } else {
                  $new_media_name = '';
                }
              }
              if ($image_part == 0) {
                $new_q_media = $new_media_name;
              } else {
                $new_q_media .= '|' . $new_media_name;
              }
            }
            $image_part++;
          }
        } else {
          $new_q_media = '';
        }
      }

      // Option data
      if (trim($o_media) != '') {
        $media_array = array();
        $media_array = explode('|',$o_media);
        $new_o_media = '';
        foreach ($media_array as $individual_media) {
          if (trim($individual_media) != '' and trim($individual_media) != 'NULL') {
            $new_media_name = unique_filename($individual_media);
            if (file_exists($mediadirectory->fullpath($individual_media))) {
              if (!copy($mediadirectory->fullpath($individual_media), $mediadirectory->fullpath($new_media_name))) {
                $error[] = sprintf($string['copyerror'], $individual_media);
                //if the image is missing don't put the file name in the new question
                $new_media_name = '';
              }
            } else {
              $new_media_name = '';
            }
            if ($new_o_media == '') {
              $new_o_media = $new_media_name;
            } else {
              $new_o_media .= '|' . $new_media_name;
            }
          }
        }
      } else {
        $new_o_media = '';
      }
      if ($marks_correct == '') $marks_correct = 1;
      if ($line == 0) {  // First record - write out the question, all the rest are options.
        $bloom = (empty($bloom)) ? NULL : $bloom;

        if ($status_array[$status]->get_retired()) {
          $new_status = $default_status;
        } else {
          $new_status = $status;
        }

        $server_ipaddress = str_replace('.', '', NetworkUtils::get_server_address());
        $guid = $server_ipaddress . uniqid('', true);

        $addQuestion = $mysqli->prepare("INSERT INTO questions (q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, last_edited, bloom, scenario_plain, leadin_plain, checkout_time, checkout_authorID, deleted, locked, std, status, q_option_order, score_method, settings, guid) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?)");

        if ($mysqli->error) {
          try {
            throw new Exception("MySQL error $mysqli->error <br /> Query:<br /> ", $mysqli->errno);
          }
          catch (Exception $e) {
            echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
            echo nl2br($e->getTraceAsString());
          }
        }

        $addQuestion->bind_param('ssssssssisssssssissss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $userObject->get_user_ID(), $new_q_media, $q_media_width, $q_media_height, $bloom, $scenario_plain, $leadin_plain, $std, $new_status, $q_option_order, $score_method, $settings, $guid);
        $addQuestion->execute();
        $new_qids[] = $question_id = $mysqli->insert_id;
        if ($q_type == 'enhancedcalc') $calculation_qid_map[$q_id] = $question_id;
        $addQuestion->close();

        // Add in a record to the papers table.
        Paper_utils::add_question($new_paper_id, $question_id, $screen, $display_pos, $mysqli);

        // Create a track changes record to say where question was copied from.
        $logger = new Logger($mysqli);
        $logger->track_change('Copied Question', $question_id, $userObject->get_user_ID(), $question, $question_id, 'Copied Question');
        // Create a track changes record to say new question added to paper.
        $logger->track_change('Paper', $new_paper_id, $userObject->get_user_ID(), '', $question_id, 'Add Question');

        // Lookup and copy the keywords
        $keyword_result = $mysqli->prepare("SELECT keywordID FROM keywords_question WHERE q_id = ?");
        $keyword_result->bind_param('i', $question);
        $keyword_result->execute();
        $keyword_result->store_result();
        $keyword_result->bind_result($keywordID);
        while ($keyword_result->fetch()){
          $addKeyword = $mysqli->prepare("INSERT INTO keywords_question VALUES (?, ?)");
          $addKeyword->bind_param('ii', $question_id, $keywordID);
          $addKeyword->execute();
          $addKeyword->close();
        }
        $keyword_result->close();
      }
      
      // Look for and fix links in linked enhancedcalc questions
      if ($q_type == 'enhancedcalc') {
        require_once('../plugins/questions/enhancedcalc/enhancedcalc.class.php');
        if (!isset($configObj)) {
                $configObj = Config::get_instance();
        }

        $tmp_questions_array['theme']		= trim($theme);
        $tmp_questions_array['scenario']	= trim($scenario);
        $tmp_questions_array['leadin']		= trim($leadin);
        $tmp_questions_array['notes']		= trim($notes);
        $tmp_questions_array['q_type']		= $q_type;
        $tmp_questions_array['q_id']		= $question_id; //the newly inserted question ID!
        $tmp_questions_array['score_method']	= $score_method;
        $tmp_questions_array['status']		= $status;
        $tmp_questions_array['display_method']	= $display_method;
        $tmp_questions_array['settings']	= $settings;
        $tmp_questions_array['q_media']		= $q_media;
        $tmp_questions_array['q_media_width']	= $q_media_width;
        $tmp_questions_array['q_media_height']	= $q_media_height;
        $tmp_questions_array['q_option_order']	= $q_option_order;
        $tmp_questions_array['dismiss']		= '';
        $tmp_questions_array['leadin_plain']	 = trim($leadin_plain);
        $tmp_questions_array['standards_setting'] = $std;

        $q = new EnhancedCalc($configObj);
        $q->load($tmp_questions_array);

        $vars = $q->get_question_vars();
        $questionChanged = false;
        foreach($vars as $var_name => $var_data) {
            $linked_q_id = 0;

            if ($q->is_linked_question_var($var_data['min'])) {
                list($linked_var_name,$linked_q_id) = $q->parse_linked_question_var($var_data['min']);
                if (isset($calculation_qid_map[$linked_q_id])) {
                    $vars[$var_name]['min'] = 'var' . $linked_var_name . $calculation_qid_map[$linked_q_id];
                    $questionChanged = true;
                }
            }

            if ($q->is_linked_question_var($var_data['max'])) {
                list($linked_var_name,$linked_q_id) = $q->parse_linked_question_var($var_data['max']);
                if (isset($calculation_qid_map[$linked_q_id])) {
                    $vars[$var_name]['max'] = 'var' . $linked_var_name . $calculation_qid_map[$linked_q_id];
                    $questionChanged = true;
                }
            }

            if ($q->is_linked_ans($var_data['min'])) {
                $linked_q_id = $q->parse_linked_ans($var_data['min']);
                if (isset($calculation_qid_map[$linked_q_id])) {
                    $vars[$var_name]['min'] = 'ans' . $calculation_qid_map[$linked_q_id];
                    $questionChanged = true;
                }
            }

            if ($q->is_linked_ans($var_data['max'])) {
                $linked_q_id = $q->parse_linked_ans($var_data['max']);
                if (isset($calculation_qid_map[$linked_q_id])) {
                    $vars[$var_name]['max'] = 'ans' . $calculation_qid_map[$linked_q_id];
                    $questionChanged = true;
                }
            }

        }

        if ($questionChanged == true) {
            // Update the question!
            $q->set_question_vars($vars);
            $q->save($mysqli);
        }
          
      }
      
      if ($q_type != 'enhancedcalc') {  // Calculation questions have no options.
        $addOption = $mysqli->prepare("INSERT INTO options VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
        $addOption->bind_param('isssssssidd', $question_id, $option_text, $new_o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
        $addOption->execute();
        $addOption->close();
      }
      $line++;
    }
    $qData->free_result();
    $qData->close();
  }
  $result->free_result();
  $result->close();

  // If we are copying in the same session we can copy the objectives
  if ($new_calendar_year == $calendar_year) {
    $i = 0;
    foreach ($old_qids as $old_id) {
      $new_question_id = $new_qids[$i];
      $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, idMod, '$new_paper_id', '$new_question_id', obj_id,"
        . " calendar_year, vle_api, map_level FROM relationships WHERE question_id = $old_id AND paper_id = ?)");
      $result->bind_param('i', $paperid);
      $result->execute();
      $result->close();
      $i++;
    }
  } else {
    // We are copying between sessions we need to check for changed sessions/objectives
    $mappings_copy_objID = array();
    $old_course = getObjectives($moduleIDs, $calendar_year, $paperid, '', $mysqli);
    $new_course = getObjectives($moduleIDs, $new_calendar_year, $paperid, '', $mysqli);
    if (count($old_course) > 0 and count($new_course) > 0) {
        copy_between_sessions($mappings_copy_objID, $old_course, $new_course);

        // Copy the objectives for each session where the objective still exists
        $result = $mysqli->prepare("INSERT INTO relationships (SELECT NULL, idMod, ?, ?, ?, ?, vle_api, map_level FROM"
          . " relationships WHERE question_id = ? AND paper_id = ? AND obj_id = ?)");
        $nw_paperid = 0;
        $nw_qid = 0;
        $nw_mapid = 0;
        $nw_calyr = 0;
        $nw_oldid = 0;
        $nw_oldpapid = 0;
        $bw_oldoid = 0;
        $result->bind_param('iiisiii', $nw_paperid, $nw_qid, $nw_mapid, $nw_calyr, $nw_oldid, $nw_oldpapid, $nw_oldoid);
        if ($mysqli->error) {
          $error[] = 'mysqli error ' . $mysql->error;
        }
        $i=0;
        foreach ($old_qids as $old_id) {
          foreach ($mappings_copy_objID as $oldmapid => $newmapid) {
            $nw_paperid		= $new_paper_id;
            $nw_qid		= $new_qids[$i];
            $nw_mapid		= $newmapid;
            $nw_calyr		= $new_calendar_year;
            $nw_oldid		= $old_id;
            $nw_oldpapid	= $paperid;
            $nw_oldoid		= $oldmapid;
            $result->execute();
            if ($mysqli->error) {
              $error[] = 'mysqli error ' . $mysql->error;
            }
          }
          $i++;
        }
        $result->close();
      }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['copypaper'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../body.css" />
  <link rel="stylesheet" type="text/css" href="../submenu.css" />
</head>
<?php
  if (count($error) == 0) {
  	echo "<body onload=\"javascript:window.location='" . $configObject->get('cfg_root_path') . "/paper/details.php?paperID=$new_paper_id&module=" . $_POST['module'] . "&folder=" . $_POST['folder'] . "';\">";
  } else {
?>
  <body onclick="hideMenus()">
  <div id="content">
  <br />
  <br />
  <br />
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr><td valign="middle">
    <div align="center">

    <table border="0" cellpadding="4" cellspacing="1" style="background-color:#C0C0C0; text-align:left">
    <tr>
    <td valign="middle" style="background-color:white"><img src="../artwork/exclamation_red_bg.png" width="32" height="32" alt="<?php echo $string['warning']; ?>" />&nbsp;&nbsp;<span style="font-size:150%; font-weight:bold; color:#C00000"><?php echo $string['filecopywarning']; ?></span></td>
   </tr>
   <tr>
   <td style="background-color:#EAEAEA"><ul>
   <?php
    echo "<li style=\"font-size:90%\">" . $string['completemsg'] . "</li>\n";
    foreach ($error as $msg) {
      echo "<li style=\"font-size:90%\">$msg</li>\n";
    }
   ?>
    </ul>
    <div style="text-align:center"><input type="button" name="OK" value=" <?php echo $string['ok']; ?> " onclick="javascript:window.location='<?php echo $configObject->get('cfg_root_path') . '/paper/details.php?paperID=' . $new_paper_id . '&module=' . $_POST['module'] . '&folder=' . $_POST['folder']; ?>'" style="width:100px" /></div>
    <br />
    </td>
    </tr>
    </table>
    </div>
    </td></tr>
    </table>
  </div>
<?php
}
$mysqli->close();

/**
 * Copies the paper properties record.
 *
 * @param object $db						- Link to MySQL database
 * @param string $calendar_year	- Looks up and updates the academic session - used with learning objectives
 * @param string $moduleIDs			- Looks up and updates the modules the paper is on - used with learning objectives
 * @param object $userObj				- Currently logged in user object.
 * @param object $configObject	- Configuration settings object.
 *
 * @return int - ID of the newly inserted property record.
 */
function copyProperties($db, &$calendar_year, &$new_calendar_year, &$moduleIDs, $userObj, $configObject) {

  $userID = $userObj->get_user_ID();
  $moduleIDs = Paper_utils::get_modules($_POST['paperID'], $db);

  $result = $db->prepare("SELECT property_id,
    paper_title,
    start_date,
    end_date,
    timezone,
    paper_type,
    paper_prologue,
    paper_postscript,
    bgcolor,
    fgcolor,
    themecolor,
    labelcolor,
    fullscreen,
    marking,
    bidirectional,
    pass_mark,
    distinction_mark,
    paper_ownerID,
    folder,
    labs,
    rubric,
    calculator,
    exam_duration,
    deleted,
    created,
    random_mark, 
    total_mark,
    display_correct_answer,
    display_question_mark,
    display_students_response,
    display_feedback,
    hide_if_unanswered,
    calendar_year,
    external_review_deadline,
    internal_review_deadline,
    sound_demo,
    latex_needed,
    password,
    retired,
    crypt_name,
    recache_marks
    FROM properties WHERE property_id = ? LIMIT 1");
  $result->bind_param('i', $_POST['paperID']);
  $result->execute();
  $result->store_result();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $timezone, $paper_type, $paper_prologue, $paper_postscript, $bgcolor, $fgcolor, $themecolor, $labelcolor, $fullscreen, $marking, $bidirectional, $pass_mark, $distinction_mark, $paper_owner, $folder, $labs, $rubric, $calculator, $exam_duration, $deleted, $created, $random_mark, $total_mark, $display_correct_answer, $display_question_mark, $display_students_response, $display_feedback, $hide_if_unanswered, $calendar_year,  $external_review_deadline, $internal_review_deadline, $sound_demo, $latex_needed, $password, $retired, $crypt_name, $recache_marks);
  $result->fetch();
  $result->close();

  $paper_type = $_POST['paper_type'];      // Override the paper type with what is posted.
  if ($paper_type == 2 and $configObject->get('cfg_summative_mgmt')) {
		$duration = 0;
		if (isset($_POST['duration_hours'])) {
			$duration += ($_POST['duration_hours'] * 60);
		}
		if (isset($_POST['duration_mins'])) {
			$duration += $_POST['duration_mins'];
		}
		$tmp_exam_duration = $duration;
  } else {
    $tmp_exam_duration = $exam_duration;
  }

  if ($paper_type == 2) {
    if ($configObject->get('cfg_summative_mgmt')) {
      $tmp_start_date = NULL;
      $tmp_end_date = NULL;
			$labs = NULL;
    } else {
      $tmp_start_date = '20200505090000';
      $tmp_end_date = '20200505100000';
    }
  } else {
    $tmp_start_date = $start_date;
    $tmp_end_date = $end_date;
  }
  $tmp_random_mark = $random_mark;
  if ($tmp_random_mark == '') $tmp_random_mark = NULL;
  $tmp_total_mark = $total_mark;
  if ($tmp_total_mark == '') $tmp_total_mark = NULL;

  $tmp_external_review_deadline = $external_review_deadline;
  if ($tmp_external_review_deadline == '') $tmp_external_review_deadline = NULL;

  $tmp_internal_review_deadline = $internal_review_deadline;
  if ($tmp_internal_review_deadline == '') $tmp_internal_review_deadline = NULL;

  if (isset($_POST['session'])) {
    $new_calendar_year = $_POST['session'];
    if ($new_calendar_year == '') {
      $new_calendar_year = NULL;
    }
  } else {
		$academic_year_title = Paper_utils::academic_year_from_title($_POST['new_paper']);
		if ($academic_year_title !== false) {
			$new_calendar_year = $academic_year_title;
		} else {
			$new_calendar_year = $calendar_year;
		}
  }

  $assessment = new assessment($db, $configObject);
  $unixtime = time();
  $created = date("Y-m-d H:i:s", $unixtime);
  $params = array(
    'paper_title' => array('s', $_POST['new_paper']),
    'start_date' => array('s', $tmp_start_date),
    'end_date' => array('s', $tmp_end_date),
    'timezone' => array('s', $timezone),
    'paper_type' => array('s', $paper_type),
    'paper_prologue' => array('s', $paper_prologue),
    'paper_postscript' => array('s', $paper_postscript),
    'bgcolor' => array('s', $bgcolor),
    'fgcolor' => array('s', $fgcolor),
    'themecolor' => array('s', $themecolor),
    'labelcolor' => array('s', $labelcolor),
    'fullscreen' => array('s', $fullscreen),
    'marking' => array('s', $marking),
    'bidirectional' => array('s', $bidirectional),
    'pass_mark' => array('i', $pass_mark),
    'distinction_mark' => array('i', $distinction_mark),
    'paper_ownerID' => array('i', $userID),
    'folder' => array('s', $folder),
    'labs' => array('s', $labs),
    'rubric' => array('s', $rubric),
    'calculator' => array('i', $calculator),
    'exam_duration' => array('i', $tmp_exam_duration),
    'deleted' => array('s', NULL),
    'created' => array('s', $created),
    'random_mark' => array('d', $tmp_random_mark),
    'total_mark' => array('i', $tmp_total_mark),
    'display_correct_answer' => array('s', $display_correct_answer),
    'display_question_mark' => array('s', $display_question_mark),
    'display_students_response' => array('s', $display_students_response),
    'display_feedback' => array('s', $display_feedback),
    'hide_if_unanswered' => array('s', $hide_if_unanswered),
    'calendar_year' => array('i', $new_calendar_year),
    'external_review_deadline' => array('s', $tmp_external_review_deadline),
    'internal_review_deadline' => array('s', $tmp_internal_review_deadline),
    'sound_demo' => array('s', $sound_demo),
    'latex_needed' => array('i', $latex_needed),
    'password' => array('s', $password),
    'retired' => array('s', NULL),
    'recache_marks' => array('i', 0)
  );
  $new_paper_id = $assessment->db_insert_assessment($params);
  $update_params = array('crypt_name' => array('s', $new_paper_id . $unixtime . $userID));
  $assessment->db_update_assessment($new_paper_id, $update_params);
  

  // Get the old reviewers and populate the new paper with.
  $result2 = $db->prepare("SELECT reviewerID, type FROM properties_reviewers WHERE paperID = ?");
  $result2->bind_param('i', $_POST['paperID']);
  $result2->execute();
  $result2->store_result();
  $result2->bind_result($reviewerID, $type);
  while ($result2->fetch()) {
    $stmt = $db->prepare("INSERT INTO properties_reviewers VALUES (NULL, ?, ?, ?)");
    $stmt->bind_param('iis', $new_paper_id, $reviewerID, $type);
    $stmt->execute();
    $stmt->close();
  }
  $result2->close();

  // Set the modules on the new paper
  Paper_utils::update_modules($moduleIDs, $new_paper_id, $db, $userObj);

  if ($paper_type == $assessment::TYPE_SUMMATIVE and $configObject->get('cfg_summative_mgmt')) {
    if (isset($_POST['barriers_needed'])) {
      $barriers_needed = 1;
    } else {
      $barriers_needed = 0;
    }
    $assessment->schedule($new_paper_id, $_POST['period'], $barriers_needed, $_POST['cohort_size'], $_POST['notes'], $_POST['sittings'], $_POST['campus']);
  }



  return $new_paper_id;
}
?>
</body>
</html>
