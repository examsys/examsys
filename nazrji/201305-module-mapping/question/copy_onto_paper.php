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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require '../include/media.inc';
require '../classes/dateutils.class.php';
require_once '../classes/questionutils.class.php';
require_once '../classes/paperutils.class.php';
require_once '../classes/logger.class.php';

check_var('q_id', 'GET', true, false, false);

if (!QuestionUtils::question_exists(substr($_GET['q_id'],1), $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript">
    function checkForm() {
      var checkOption = $('input:radio[name=property_id]:checked').val();

      if (typeof checkOption == 'undefined') {
        alert("Please select which paper you would like to add the question to.");
        return false;
      }
    }

    function resizeList() {
      var winW = 630, winH = 460;
      if (document.body && document.body.offsetWidth) {
        winW = document.body.offsetWidth;
        winH = document.body.offsetHeight;
      }
      if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
        winW = document.documentElement.offsetWidth;
        winH = document.documentElement.offsetHeight;
      }
      if (window.innerWidth && window.innerHeight) {
        winW = window.innerWidth;
        winH = window.innerHeight;
      }
      winH -= 170;
      document.getElementById('paperlist').style.height = winH + 'px';
    }

  </script>
</head>

<body onload="resizeList();" onresize="resizeList();">

<?php
  echo "<form style=\"width:100%; height:100%;\" method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?q_id=" . $_GET['q_id'] . "\">\n";
?>
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/copy_onto_paper.png" width="32" height="32 alt="<?php echo $string['copyontopaper']; ?>" /></td><td class="midblue_header" style="background-color:white; font-size:150%; font-weight:bold; border-bottom:1px solid #CCD9EA"><?php echo $string['copyontopaper']; ?></td></tr>
  </table>


  <p style="margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_warning_16.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" border="0" /><?php echo $string['msg1']; ?></p>
  <p style="margin:4px; text-align:justify; font-size:70%"><img src="../artwork/small_padlock.png" width="16" height="16" alt="<?php echo $string['warning']; ?>" border="0" /><?php echo $string['msg2']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="paperlist">
  <table cellpadding="0" cellspacing="1" border="0" width="95%">
<?php
  $result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_title, start_date, end_date, paper_type FROM properties, properties_modules, modules WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = modules.id AND (paper_ownerID=? OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')) AND deleted IS NULL  ORDER BY paper_title");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and $end_date != '' and date("Y-m-d H:i:s") > $end_date) {
      // echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\"><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_warning_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" border=\"0\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\" id=\"$property_id\"><label for=\"$property_id\">$paper_title</label></td></tr>\n";
    }
  }
  $result->close();

  echo "</table>\n</div>";
  echo "<div align=\"center\"><input type=\"submit\" style=\"width:120px\" name=\"submit\" value=\"" . $string['ok'] . "\" />&nbsp;&nbsp;<input type=\"button\" style=\"width:120px\" name=\"cancel\" onclick=\"window.close();\" value=\"" . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['copyontopaper']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; text-align:center}
  </style>
</head>
<body>
<?php
  $property_id = $_POST['property_id'];
  $q_id = $_GET['q_id'];
  $logger = new Logger($mysqli);

  //- Handle paper data first ------------------------------------------------------------------------------------------------------------------------------------

  // Get the maximum display position for an existing paper.
  $result = $mysqli->prepare("SELECT MAX(display_pos), MAX(screen) FROM papers WHERE paper=?");
  $result->bind_param('i', $property_id);
  $result->execute();
  $result->bind_result($display_pos, $screen);
  $result->fetch();
  $result->close();
  if ($screen == '') $screen = 1;
  $display_pos++;                     // Add one to put new question right at the end.

  //- Copy the question(s) ------------------------------------------------------------------------------------------------------------------------------------------
  $q_IDs = explode(',', $_GET['q_id']);

  for ($i=1; $i<count($q_IDs); $i++) {
    $result = $mysqli->prepare("SELECT * FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $result->bind_param('i', $q_IDs[$i]);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $owner, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_author, $deleted, $locked, $std, $status, $q_option_order, $score_method, $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $id_num, $marks_correct, $marks_incorrect, $marks_partial);
    $line = 0;
    while ($result->fetch()) {
      // Question data
      if ($q_media != '' and $q_media != 'NULL' and $line == 0) {
        $media_array = array();
        $media_array = explode("|", $q_media);
        $new_q_media = '';
        foreach ($media_array as $individual_media) {
          if ($individual_media != '' and $individual_media != 'NULL') {
            $new_media_name = unique_filename($individual_media);
            if (file_exists("../media/$individual_media")){
              if (!copy("../media/$individual_media","../media/$new_media_name")) {
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

      // Option data
      $o_id = $q_IDs[$i];
      if ($o_media != '') {
        $media_array = array();
        $media_array = explode("|",$o_media);
        $new_o_media = '';
        foreach ($media_array as $individual_media) {
          if ($individual_media != '' and $individual_media != 'NULL') {
            $new_media_name = unique_filename($individual_media);
            if (file_exists("../media/$individual_media")){
              if (!copy("../media/$individual_media","../media/$new_media_name")) {
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

      if ($line == 0) {  // First record - write out the question, all the rest are options.
        $addQuestion = $mysqli->prepare("INSERT INTO questions VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, 'Normal', ?, ?)");
        $addQuestion->bind_param('ssssssssisssssssss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $userObject->get_user_ID(), $new_q_media, $q_media_width, $q_media_height, $bloom, $scenario_plain, $leadin_plain, $std, $q_option_order, $score_method);
        $addQuestion->execute();
        $question_id = $mysqli->insert_id;
        $addQuestion->close();

        // Create a track changes record to say where question came from.
        $question_id = intval($question_id);
        $success = $logger->track_change('Copied Question', $question_id, $userObject->get_user_ID(), $q_IDs[$i], $question_id, 'Copied Question');

        // Lookup and copy the keywords
        $keywords = QuestionUtils::get_keywords($q_IDs[$i], $mysqli);
        QuestionUtils::add_keywords($keywords, $question_id, $mysqli);

        // Lookup modules
        $modules = QuestionUtils::get_modules($q_IDs[$i], $mysqli);
        QuestionUtils::add_modules($modules, $question_id, $mysqli);

      }
      $addOption = $mysqli->prepare("INSERT INTO options VALUES(?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
      $addOption->bind_param('isssssssddd', $question_id, $option_text, $new_o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
      $addOption->execute();
      $addOption->close();
      $line++;
    }
    $result->free_result();
    $result->close();

    //- Add the question to the paper ------------------------------------------------------------------------------------------------------------------------------
    Paper_utils::add_question($property_id, $question_id, $screen, $display_pos, $mysqli);

    // Create a track changes record to say new question added.
    $success = $logger->track_change('Paper', $property_id, $userObject->get_user_ID(), '', $question_id, 'Add Question');
  }

  echo "<p>" . sprintf($string['success'], Paper_utils::get_title($property_id, $mysqli)) . "</p>\n";
  echo "<p><input type=\"button\" value=\"" . $string['ok'] . "\" style=\"width:100px\" onclick=\"window.close();\" /></p>\n";

  $mysqli->close();
}
?>
</body>
</html>