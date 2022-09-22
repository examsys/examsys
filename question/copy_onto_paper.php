<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';
require_once '../include/mapping.inc';
require_once '../classes/questionbank.class.php';

check_var('q_id', 'GET', true, false, false);

if (!QuestionUtils::question_exists(mb_substr($_GET['q_id'], 1), $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_GET['type']) and $_GET['type'] == 'objective') {
    $module_code = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
    $qbank = new QuestionBank($_GET['module'], $module_code, $string, $notice, $mysqli);
    $map_outcomes = true;
} else {
    $map_outcomes = false;
}
$mediadirectory = rogo_directory::get_directory('media');
?>

<!DOCTYPE html>
<html style="margin:0px; width:100%; height:100%;">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

    <title><?php echo $string['copyontopaper']; ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/questionlink.css" />
    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src="../js/questionlinkinit.min.js"></script>
</head>
<?php

if (!isset($_POST['submit'])) {
    ?>

    <?php
    echo "<body class='submit'><form style=\"width:100%; height:100%;\" method=\"post\" id=\"theForm\" name=\"theForm\" action=\"" . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . "\" autocomplete=\"off\">\n";
    ?>
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/copy_onto_paper.png" width="32" height="32 alt="<?php echo $string['copyontopaper']; ?>" /></td><td class="midblue_header" style="background-color:white; font-size:150%; font-weight:bold; border-bottom:1px solid #CCD9EA"><?php echo $string['copyontopaper']; ?></td></tr>
  </table>


  <p style="margin-left:20px; margin-right:4px; text-align:justify; font-size:80%; text-indent:-16px"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="<?php echo $string['warning']; ?>" />&nbsp;<?php echo $string['msg1']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="list">
  <table cellpadding="0" cellspacing="1" border="0" width="95%">
    <?php
    $sql = "SELECT DISTINCT properties.property_id, paper_title, start_date, end_date, paper_type FROM properties, properties_modules, modules WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = modules.id AND (paper_ownerID=? OR idMod IN ('" . implode("','", array_keys($staff_modules)) . "')) AND deleted IS NULL ORDER BY paper_title";
    $result = $mysqli->prepare($sql);
    $result->bind_param('i', $userObject->get_user_ID());
    $result->execute();
    $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
    while ($result->fetch()) {
        if (($paper_type == '2' or $paper_type == '4') and $end_date != '' and date('Y-m-d H:i:s') > $end_date) {
            //echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"18\" height=\"18\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\"><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
        } elseif ($start_date < date('Y-m-d H:i:s') and $end_date > date('Y-m-d H:i:s')) {
            echo '<tr><td style="width:16px"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="' . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
        } else {
            echo "<tr><td style=\"width:16px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\" id=\"$property_id\" ><label for=\"$property_id\">$paper_title</label></td></tr>\n";
        }
    }
    $result->close();

    echo "</table>\n</div>";
    echo '<input type="hidden" id="outcomes" name="outcomes" value="" />';
    echo '<div align="center"><img src="../artwork/working.gif" id="working" width="16" height="16" alt="Working" style="display: none" /> <input type="submit" class="ok" name="submit" value="' . $string['ok'] . '" /><input type="button" class="cancel" name="cancel" id="cancel" value="' . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
    $property_id = $_POST['property_id'];
    $properties = PaperProperties::get_paper_properties_by_id($property_id, $mysqli, $string);

    $q_id = $_GET['q_id'];
    $logger = new Logger($mysqli);

    if ($map_outcomes) {
        $yearutils = new yearutils($mysqli);
        $vle_api_cache = array();
        $vle_api_data = MappingUtils::get_vle_api($_GET['module'], $yearutils->get_current_session(), $vle_api_cache, $mysqli);
    }

    //- Handle paper data first ------------------------------------------------------------------------------------------------------------------------------------

    // Get the maximum display position for an existing paper.
    $display_pos    = ($properties->get_max_display_pos() + 1);
    $screen             = $properties->get_max_screen();
    if ($screen == 0) {
        $screen = 1;
    }

    //- Copy the question(s) ------------------------------------------------------------------------------------------------------------------------------------------
    $q_IDs = explode(',', $_GET['q_id']);

    for ($i = 1; $i < count($q_IDs); $i++) {
        $map_guid = array();

        $result = $mysqli->prepare('SELECT
                q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback,
                display_method, notes, ownerid, creation_date, last_edited, bloom,
                scenario_plain, leadin_plain, checkout_time, checkout_authorid, deleted,
                locked, std, status, q_option_order, score_method, settings, guid
            FROM
                questions
            WHERE q_id = ?');
        $result->bind_param('i', $q_IDs[$i]);
        $result->execute();
        $result->store_result();
        $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $owner, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $score_method, $settings, $guid);

        $save_ok = true;

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

        while ($result->fetch()) {
            $o_result = $mysqli->prepare('
                SELECT
                    o_id,
                    option_text,
                    feedback_right,
                    feedback_wrong,
                    correct,
                    source,
                    width,
                    height,
                    alt,
                    ownerid,
                    marks_correct,
                    marks_incorrect,
                    marks_partial
                FROM 
                    options 
                LEFT JOIN options_media ON options.id_num = options_media.oid
                LEFT JOIN media m on options_media.mediaid = m.id
                WHERE o_id = ? 
                ORDER BY id_num
            ');
            $o_result->bind_param('i', $q_IDs[$i]);
            $o_result->execute();
            $o_result->store_result();
            $o_result->bind_result(
                $o_id,
                $option_text,
                $feedback_right,
                $feedback_wrong,
                $correct,
                $option_media,
                $option_media_width,
                $option_media_height,
                $option_media_alt,
                $option_media_owner,
                $marks_correct,
                $marks_incorrect,
                $marks_partial
            );

            if ($status_array[$status]->get_retired()) {
                $new_status = $default_status;
            } else {
                $new_status = $status;
            }

            $server_ipaddress = str_replace('.', '', NetworkUtils::get_server_address());
            $guid = $server_ipaddress . uniqid('', true);

            $mysqli->autocommit(false);

            if ($bloom == '') {
                $bloom = null;
            }
            if ($q_option_order == '') {
                $q_option_order = 'display order';
            }
            if ($score_method == '') {
                $score_method = 'Mark per Option';
            }

            $addQuestion = $mysqli->prepare('INSERT INTO questions
                    (q_id, q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method,
                    notes, ownerID, creation_date, last_edited, bloom, scenario_plain, leadin_plain,
                    checkout_time, checkout_authorID, deleted, locked, std, status, q_option_order,
                    score_method, settings, guid)
                VALUES
                    (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?)');
            $addQuestion->bind_param('ssssssssissssissss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $userObject->get_user_ID(), $bloom, $scenario_plain, $leadin_plain, $std, $new_status, $q_option_order, $score_method, $settings, $guid);
            $res = $addQuestion->execute();
            if ($res === false) {
                $save_ok = false;
            } else {
                $question_id = $mysqli->insert_id;
            }
            $addQuestion->close();

            if ($save_ok) {
                // Copy Question Media.
                $newmedia = array();
                $media = QuestionUtils::getMediaAsString($q_id);
                if ($media['id'] != '') {
                    $media_array = explode('|', $media['source']);
                    $mediawidth_array = explode('|', $media['width']);
                    $mediaheight_array = explode('|', $media['height']);
                    $mediaalt_array = explode('|', $media['alt']);
                    $mediaowner_array = explode('|', $media['owner']);
                    $medianum_array = explode('|', $media['num']);
                    $image_part = 0;
                    foreach ($media_array as $individual_media) {
                        $new_media_name = '';
                        if (trim($individual_media) != '' and trim($individual_media) != 'NULL') {
                            $new_media_name = media_handler::unique_filename($individual_media);
                            if (file_exists($mediadirectory->fullpath($individual_media))) {
                                if (!copy($mediadirectory->fullpath($individual_media), $mediadirectory->fullpath($new_media_name))) {
                                    display_error($string['qcopyerrorno'], sprintf($string['copyerror'], $q_no, $individual_media));
                                } else {
                                    $id = \media_handler::insertMedia(
                                        $new_media_name,
                                        $mediawidth_array[$image_part],
                                        $mediaheight_array[$image_part],
                                        $mediaalt_array[$image_part],
                                        $mediaowner_array[$image_part]
                                    );
                                    if ($id === -1) {
                                        display_error($string['qcopyerrorno'], sprintf($string['copyerror'], $q_no, $individual_media));
                                    } else {
                                        $newmedia[$medianum_array[$image_part]] = $id;
                                    }
                                }
                            }
                        }
                        $image_part++;
                    }
                }
                foreach ($newmedia as $idx => $value) {
                    $ok = \media_handler::linkQuestionToMedia($value, $question_id, $idx);
                    if (!$ok) {
                        display_error($string['qcopyerrorno'], sprintf($string['copyerror'], $q_no, $value));
                    }
                }
            }

            while ($save_ok and $o_result->fetch()) {
                $addOption = $mysqli->prepare('INSERT INTO options
                        (o_id, option_text, feedback_right, feedback_wrong, correct, id_num, marks_correct,
                        marks_incorrect, marks_partial)
                    VALUES
                        (?, ?, ?, ?, ?, NULL, ?, ?, ?)');
                $addOption->bind_param('issssddd', $question_id, $option_text, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
                $res = $addOption->execute();
                if ($res === false) {
                    $save_ok = false;
                }
                if ($save_ok) {
                    $newidnum = $mysqli->insert_id;
                    $addOption->close();
                    $newomedia = false;
                    if (isset($option_media) and $option_media != '') {
                        $new_media_name = media_handler::unique_filename($option_media);
                        if (file_exists($mediadirectory->fullpath($option_media))) {
                            if (
                                !copy(
                                    $mediadirectory->fullpath($option_media),
                                    $mediadirectory->fullpath($new_media_name)
                                )
                            ) {
                                display_error(
                                    'File Copy Error 2',
                                    sprintf($string['error2'], $new_media_name, $option_media)
                                );
                            } else {
                                $id = \media_handler::insertMedia(
                                    $new_media_name,
                                    $option_media_width,
                                    $option_media_height,
                                    $option_media_alt,
                                    $option_media_owner
                                );
                                if ($id === -1) {
                                    $error[] = sprintf($string['copyerror'], $q_no, $option_media);
                                } else {
                                    $newomedia = $id;
                                }
                            }
                        } else {
                            display_error('File Copy Error 4', sprintf($string['error3'], $new_media_name));
                        }
                        if ($newomedia !== false) {
                            $ok = \media_handler::linkOptionToMedia(
                                $newomedia,
                                $newidnum,
                            );
                            if (!$ok) {
                                $error[] = sprintf($string['copyerror'], $q_no, $newomedia);
                            }
                        }
                    }
                }
            }

            if ($save_ok === false) {
                // NO - rollback
                $mysqli->rollback();
            } else {
                // YES - commit the updates to the tables
                $mysqli->commit();
            }
            // Turn auto commit back on so future queries function as before
            $mysqli->autocommit(true);

            if ($save_ok) {
                // Create a track changes record to say where question came from.
                $question_id = intval($question_id);
                $success = $logger->track_change('Copied Question', $question_id, $userObject->get_user_ID(), $q_IDs[$i], $question_id, 'Copied Question');

                // Lookup and copy the keywords
                $keywords = QuestionUtils::get_keywords($q_IDs[$i], $mysqli);
                QuestionUtils::add_keywords($keywords, $question_id, $mysqli);

                // Lookup modules
                $modules = QuestionUtils::get_modules($q_IDs[$i], $mysqli);
                QuestionUtils::add_modules($modules, $question_id, $mysqli);

                if ($map_outcomes) {
                    // Make sure that paper is on the module we're copying from
                    $paper_modules = $properties->get_modules();

                    if (in_array($_GET['module'], array_keys($paper_modules))) {
                        if (isset($_POST['outcomes']) and $_POST['outcomes'] != '') {
                            $outcomes = json_decode($_POST['outcomes'], true);

                            $mappings = $mysqli->prepare('SELECT question_id, obj_id FROM relationships WHERE question_id = ? AND idMod = ?');

                            if ($mysqli->error) {
                                echo $string['showerror'];
                            }
                            $mappings->bind_param('ii', $q_IDs[$i], $_GET['module']);
                            $mappings->execute();
                            $mappings->store_result();
                            $mappings->bind_result($map_q_id, $obj_id);
                            while ($mappings->fetch()) {
                                if (isset($outcomes[$obj_id])) {
                                    $map_guid[$outcomes[$obj_id]] = true;
                                }
                            }
                            $mappings->close();
                            // echo '<br />'.$q_IDs[$i].'<br />';print_r($map_guid);
                        }
                    } else {
                        echo '<p>' . $string['papernotonmodule'] . '</p>';
                    }
                }
            }
        }
        $result->free_result();
        $result->close();

        if ($save_ok) {
            //- Add the question to the paper ------------------------------------------------------------------------------------------------------------------------------
            Paper_utils::add_question($property_id, $question_id, $screen, $display_pos, $mysqli);

            // Create a track changes record to say new question added.
            $success = $logger->track_change('Paper', $property_id, $userObject->get_user_ID(), '', $question_id, 'Add Question');

            if (count($map_guid) > 0) {
                // Get the mappings for the module in the paper's academic year
                $calendar_year = $properties->get_calendar_year();
                $outcomes = $qbank->get_outcomes($calendar_year, $vle_api_data);

                foreach (array_keys($map_guid) as $guid) {
                    // get the IDs of the outcomes for the GUIDs we've been passed
                    if (isset($outcomes[$guid])) {
                        foreach ($outcomes[$guid]['ids'] as $obj_id) {
                              // Add new relationship records for the paper and question
                              $sql = 'INSERT INTO relationships(idMod, paper_id, question_id, obj_id, calendar_year, vle_api, map_level) VALUES(?, ?, ?, ?, ?, ?, ?)';
                              $addRel = $mysqli->prepare($sql);
                              $addRel->bind_param('iiiissi', $_GET['module'], $property_id, $question_id, $obj_id, $calendar_year, $vle_api_data['api'], $vle_api_data['level']);
                              $addRel->execute();
                              $addRel->close();
                        }
                    }
                }
            }
        } else {
            display_error($string['qcopyerrorno'], sprintf($string['qcopyerror'], $q_id));
        }
    }

    echo "<body class='complete'><p>" . sprintf($string['success'], $properties->get_paper_title()) . "</p>\n";
    echo '<p><input type="button" value="' . $string['close'] . '" class="cancel" id="close" /><input type="button" value="' . $string['gotopaper'] . "\" class=\"ok\" id=\"gotopaper\"data-paperid='$property_id' /></p>\n";

    $mysqli->close();
}
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['outcomes'] = $map_outcomes;
$render->render($miscdataset, array(), 'dataset.html');// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
