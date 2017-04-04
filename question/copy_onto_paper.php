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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';
require '../include/media.inc';
require_once '../include/mapping.inc';
require_once '../classes/questionbank.class.php';

check_var('q_id', 'GET', true, false, false);

if (!QuestionUtils::question_exists(substr($_GET['q_id'],1), $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
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

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html>
<html style="margin:0px; width:100%; height:100%;">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['copyontopaper']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {background-color:#F1F5FB}
    td {font-size:80%}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function checkForm() {
      var checkOption = $('input:radio[name=paperID]:checked').val();

      if (typeof checkOption == 'undefined') {
        alert("Please select which paper you would like to add the question to.");
        return false;
      }
      $('#working').show();
    }

    function resizeList() {
      winH = $(window).height() - 150;

      $('#paperlist').css('height', winH + 'px');
    }

    $(function () {
      resizeList();

      $(window).resize(function() {
        resizeList();
      });

      $('#cancel').click(function() {
        window.close();
      });
<?php
  if ($map_outcomes) {
?>
      $('#outcomes').val(window.opener.getSelectedOutcomes());
<?php
  }
?>
    });
  </script>
</head>

<body>

<?php
  echo "<form style=\"width:100%; height:100%;\" method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "\" autocomplete=\"off\">\n";
?>
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/copy_onto_paper.png" width="32" height="32 alt="<?php echo $string['copyontopaper']; ?>" /></td><td class="midblue_header" style="background-color:white; font-size:150%; font-weight:bold; border-bottom:1px solid #CCD9EA"><?php echo $string['copyontopaper']; ?></td></tr>
  </table>


  <p style="margin-left:20px; margin-right:4px; text-align:justify; font-size:80%; text-indent:-16px"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="<?php echo $string['warning']; ?>" />&nbsp;<?php echo $string['msg1']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="paperlist">
  <table cellpadding="0" cellspacing="1" border="0" width="95%">
<?php
	$sql = "SELECT DISTINCT properties.property_id, paper_title, start_date, end_date, paper_type FROM properties, properties_modules, modules WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = modules.id AND (paper_ownerID=? OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')) AND deleted IS NULL ORDER BY paper_title";
  $result = $mysqli->prepare($sql);
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and $end_date != '' and date("Y-m-d H:i:s") > $end_date) {
      //echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"18\" height=\"18\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\"><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:16px\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:16px\">&nbsp;</td><td><input type=\"radio\" name=\"paperID\" value=\"$property_id\" id=\"$property_id\"><label for=\"$property_id\">$paper_title</label></td></tr>\n";
    }
  }
  $result->close();

  echo "</table>\n</div>";
  echo '<input type="hidden" id="outcomes" name="outcomes" value="" />';
  echo "<div align=\"center\"><img src=\"../artwork/working.gif\" id=\"working\" width=\"16\" height=\"16\" alt=\"Working\" style=\"display: none\" /> <input type=\"submit\" class=\"ok\" name=\"submit\" value=\"" . $string['ok'] . "\" /><input type=\"button\" class=\"cancel\" name=\"cancel\" id=\"cancel\" value=\"" . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
  $property_id = $_POST['paperID'];
	$properties = PaperProperties::get_paper_properties_by_id($property_id, $mysqli, $string);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['copyontopaper']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; text-align:center}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      $("#close").click(function() {
        window.close();
      });
      
      $("#gotopaper").click(function() {
        window.opener.location.href = '../paper/details.php?paperID=<?php echo $property_id ?>';
        window.close();
      });
    });
  </script>
</head>
<body>
<?php
  $property_id = $_POST['paperID'];
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
	$display_pos	= ($properties->get_max_display_pos() + 1);
	$screen 			= $properties->get_max_screen();
	if ($screen == 0) $screen = 1;

  //- Copy the question(s) ------------------------------------------------------------------------------------------------------------------------------------------
  $q_IDs = explode(',', $_GET['q_id']);

  for ($i=1; $i<count($q_IDs); $i++) {
    $map_guid = array();

    $result = $mysqli->prepare("SELECT * FROM questions WHERE q_id = ?");
    $result->bind_param('i', $q_IDs[$i]);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $owner, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $score_method, $settings, $guid);

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

      $o_result = $mysqli->prepare("SELECT * FROM options WHERE o_id=? ORDER BY id_num");
      $o_result->bind_param('i', $q_IDs[$i]);
      $o_result->execute();
      $o_result->store_result();
      $o_result->bind_result($o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks_correct, $marks_incorrect, $marks_partial);

      // Question data
      if ($q_media != '' and $q_media != 'NULL') {
        $media_array = array();
        $media_array = explode("|", $q_media);
        $new_q_media = '';
        foreach ($media_array as $individual_media) {
          if ($individual_media != '' and $individual_media != 'NULL') {
            $new_media_name = unique_filename($individual_media);
            if (file_exists($mediadirectory->fullpath($individual_media))){
              if (!copy($mediadirectory->fullpath($individual_media), $mediadirectory->fullpath($new_media_name))) {
                display_error('File Copy Error 1', sprintf($string['error1'], $new_media_name));
              }
            } else {
              display_error('File Copy Error 3', sprintf($string['error3'], $new_media_name));
            }
            if ($new_q_media == '') {
              $new_q_media = $new_media_name;
            } else {
              $new_q_media .= '|' . $new_media_name;
            }
          }
        }
      }
      
      if ($q_type == "extmatch") { // Above foreach loop remove empty q_media value, which is needed for mapping scenarios and its medias when display/editing a extmatch question.
          $new_q_media = $q_media; // So here need its original value.
      }

      if ($status_array[$status]->get_retired()) {
        $new_status = $default_status;
      } else {
        $new_status = $status;
      }

			$server_ipaddress = str_replace('.', '', NetworkUtils::get_server_address());
      $guid = $server_ipaddress . uniqid('', true);

      $mysqli->autocommit(false);

			if ($bloom == '') 					$bloom = null;  
			if ($q_option_order == '')	$q_option_order = 'display order';
			if ($score_method == '') 		$score_method = 'Mark per Option';

			$addQuestion = $mysqli->prepare("INSERT INTO questions VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?)");
      $addQuestion->bind_param('ssssssssisssssssissss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $userObject->get_user_ID(), $new_q_media, $q_media_width, $q_media_height, $bloom, $scenario_plain, $leadin_plain, $std, $new_status, $q_option_order, $score_method, $settings, $guid);
      $res = $addQuestion->execute();
      if ($res === false) {
        $save_ok = false;
      } else {
        $question_id = $mysqli->insert_id;
      }
      $addQuestion->close();

      $o_medias = array();
      while ($save_ok and $o_result->fetch()) {
        if ($o_media != '') {
          $media_array = array();
          $media_array = explode("|", $o_media);
          $new_o_media = '';
          foreach ($media_array as $individual_media) {
            if ($individual_media != '' and $individual_media != 'NULL') {
              $new_media_name = unique_filename($individual_media);
              if (file_exists($mediadirectory->fullpath($individual_media))){
                if (!copy($mediadirectory->fullpath($individual_media), $mediadirectory->fullpath($new_media_name))) {
                  display_error('File Copy Error 2', sprintf($string['error2'], $new_media_name, $individual_media));
                }
              } else {
                display_error('File Copy Error 4', sprintf($string['error3'], $new_media_name));
              }
              if ($new_o_media == '') {
                $new_o_media = $new_media_name;
              } else {
                $new_o_media .= '|' . $new_media_name;
              }
            }
          }
        }
				

        $addOption = $mysqli->prepare("INSERT INTO options VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
        $addOption->bind_param('isssssssddd', $question_id, $option_text, $new_o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
        $res = $addOption->execute();
        if ($res === false) {
          $save_ok = false;
        }
        $addOption->close();
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

              $mappings = $mysqli->prepare("SELECT question_id, obj_id FROM relationships WHERE question_id = ? AND idMod = ?");

              if ($mysqli->error) {
                echo $string['showerror'];
              }
              $mappings->bind_param('ii', $q_IDs[$i], $_GET['module']);
              $mappings->execute();
              $mappings->store_result();
              $mappings->bind_result($map_q_id, $obj_id);
              while($mappings->fetch()) {
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
        
        foreach(array_keys($map_guid) as $guid) {
          // get the IDs of the outcomes for the GUIDs we've been passed
          if (isset($outcomes[$guid])) {
            foreach($outcomes[$guid]['ids'] as $obj_id) {
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

  echo "<p>" . sprintf($string['success'], $properties->get_paper_title()) . "</p>\n";
  echo "<p><input type=\"button\" value=\"" . $string['close'] . "\" class=\"ok\" id=\"close\" /><input type=\"button\" value=\"" . $string['gotopaper'] . "\" class=\"ok\" id=\"gotopaper\" /></p>\n";

  $mysqli->close();
}
?>
</body>
</html>
