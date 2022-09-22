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

require_once '../include/staff_auth.inc';
require_once '../include/std_set_functions.inc';
require_once '../include/errors.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';

$paperID = check_var('paperID', 'GET', true, false, true);
check_var('method', 'GET', true, false, false);

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper_title = $propertyObj->get_paper_title();
$paper_type = $propertyObj->get_paper_type();
$paper_prologue = $propertyObj->get_paper_prologue();
$marking = $propertyObj->get_marking();

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

function ebelDropdown($dropdownID, $ebel_grid)
{
    $selected = $ebel_grid[$dropdownID];

    $html = "<select name=\"$dropdownID\" id=\"$dropdownID\" class='recountcat'>\n";
    $html .= "<option value=\"0\"></option>\n";
    $selected = intval($selected * 100);
    for ($individual_category = 0; $individual_category <= 100; $individual_category++) {
        if ($individual_category == $selected) {
            $html .= '<option value="' . ($individual_category / 100) . "\" selected>$individual_category%</option>\n";
        } else {
            $html .= '<option value="' . ($individual_category / 100) . "\">$individual_category%</option>\n";
        }
    }
    $html .= "</select>\n";
    return $html;
}

function check_ebel_distinction_type($reviewID, $db)
{
    $result = $db->prepare('SELECT distinction_score FROM std_set WHERE id = ?');
    $result->bind_param('i', $reviewID);
    $result->execute();
    $result->bind_result($distinction_score);
    $result->fetch();
    $result->close();

    if (is_null($distinction_score)) {
        $type = 'dna';
    } elseif ($distinction_score === '0.000000') {
        $type = 'top20';
    } else {
        $type = 'grid';
    }

    return $type;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['standardssetting']); ?></title>
  <?php
    // Get any questions to exclude.
    $exclusions = new Exclusion($paperID, $mysqli);
    $exclusions->load();

    $current_screen = 1;
    ?>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <link rel="stylesheet" type="text/css" href="../css/std_setting.css" />
  <link rel="stylesheet" type="text/css" href="../css/html5.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <link rel="stylesheet" href="../node_modules/mediaelement/build/mediaelementplayer.min.css"/>
  <style>
        table {table-layout:auto}
        #maincontent {height:auto}
    .var {font-weight: bold}
    .value {display:none}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>"
            data-three="<?php echo $configObject->get_setting('core', 'paper_threejs'); ?>">
  </script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/stdsetreviewinit.min.js"></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
  $texteditorplugin->display_header();

  // Check if any 3d file types are enabled and render js.
  threed_handler::render_js($string);
?>
</head>
<?php
if (isset($_GET['module'])) {
    $module = $_GET['module'];
} else {
    $module = '';
}
if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
} else {
    $folder = '';
}

  echo "<body>\n";
  echo "<div id=\"maincontent\">\n";

  require '../include/toprightmenu.inc';

if ($_GET['method'] == 'modified_angoff') {
      echo draw_toprightmenu(98);
} elseif ($_GET['method'] == 'ebel') {
      echo draw_toprightmenu(99);
}

  echo "<form method=\"post\" name=\"questions\" action=\"record_review.php?paperID=$paperID&method=" . $_GET['method'] . "&module=$module&folder=$folder\" autocomplete=\"off\">\n";

  $reviews = array();

if (isset($_GET['std_setID'])) {
    $standard_setting = new StandardSetting($mysqli);
    $reviews = $standard_setting->get_ratings_by_question($_GET['std_setID']);
}

  // Load default setting from the Questions table and save to reviews array if no existing data
  $result = $mysqli->prepare('SELECT question, std FROM (papers, questions) WHERE paper = ? AND papers.question = questions.q_id');
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($questionID, $std);
while ($result->fetch()) {
    if (!isset($_GET['std_setID'])) {
        $reviews[$questionID] = $std;
    }
    echo '<input type="hidden" name="old' . $questionID . "\" value=\"$std\" />\n";
}
  $result->close();

  echo "\n<div class=\"head_title\" style=\"font-size:90%\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
if ($folder != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
} elseif (isset($_GET['module']) and $_GET['module'] != '') {
    $module_code = module_utils::get_moduleid_from_id($module, $mysqli);
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" />';

if ($userObject->has_role('Standards Setter')) {
    echo $paper_title;
} else {
    echo "<a href=\"../paper/details.php?paperID=$paperID&module=$module&folder=$folder\">$paper_title</a>";
}

  echo "<img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"./index.php?paperID=$paperID&module=$module&folder=$folder\">" . $string['standardssetting'] . '</a></div>';

if ($_GET['method'] == 'modified_angoff') {
    $helpID = 98;
    echo '<div class="page_title">' . $string['modifiedangoffmethod'] . '</div>';
} elseif ($_GET['method'] == 'ebel') {
    $helpID = 99;
    echo '<div class="page_title">' . $string['ebelmethod'] . '</div>';
}
  echo "</div>\n";

switch ($_GET['method']) {
    case 'modified_angoff':
        $std_instruction = $string['modangoffstep1'];
        break;
    case 'ebel':
        $std_instruction = $string['step1'];
        break;
}
?>
  <br />
  <div class="key"><?php echo $std_instruction; ?></div>
  <br />
<?php
  $old_leadin = '';
  $old_scenario = '';
  $old_notes = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $old_theme = '';
  $old_screen = 1;
  $old_correct_fback = '';
  $total_marks = 0; //Altered as a globle in display_options !!!
  $std_excluded = 0;
  $prologue_show = 1;
  $options_array = array();
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";

  $result = $mysqli->prepare("
    SELECT
      screen,
      q_type,
      q_id,
      score_method,
      display_method,
      settings,
      marks_correct,
      marks_incorrect,
      theme,
      scenario,
      leadin,
      correct,
      REPLACE(option_text,'\t','') AS option_text,
      source,
      width,
      height,
      alt,
      notes,
      correct_fback
    FROM (papers, questions)
    LEFT JOIN options ON questions.q_id = options.o_id
    LEFT JOIN options_media ON options.id_num = options_media.oid
    LEFT JOIN media m on options_media.mediaid = m.id
    WHERE paper = ?
    AND papers.question = questions.q_id
    ORDER BY display_pos, id_num
  ");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result(
      $screen,
      $q_type,
      $q_id,
      $score_method,
      $display_method,
      $settings,
      $marks_correct,
      $marks_incorrect,
      $theme,
      $scenario,
      $leadin,
      $correct,
      $option_text,
      $option_media,
      $option_media_width,
      $option_media_height,
      $option_media_alt,
      $notes,
      $correct_fback
  );
  $configObj = Config::get_instance();
  $render = new render($configObj);
  while ($result->fetch()) {
      $questiondata = \questiondata::get_datastore($q_type);
      if ($prologue_show == 1 and $current_screen == 1 and $paper_prologue != '') {
          echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
          $prologue_show = 0;
      }

      if ($question_no == 0) {
          echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      }
      // New Question
      if ($old_q_id != $q_id) {
          // Get Media.
          $media = QuestionUtils::getMediaAsString($q_id);
          $q_media = $media['source'];
          $q_media_height = $media['height'];
          $q_media_width = $media['width'];
          $q_media_alt = $media['alt'];
          $q_media_num = $media['num'];
          // Print the options of the previous question
          $li_set = 0;
          if ($old_leadin != '') {
              if ($li_set == 1) {
                  echo "</td></tr>\n";
              }
              $excluded = $exclusions->get_exclusions_by_qid($old_q_id);
              if (count($options_array) > 0) {
                  display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded, false);
              }
              if ($old_screen != $screen) {
                  echo '<tr><td colspan="2">';
                  echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
                  echo '</td></tr>';
              }
          }
          $question_no++;
          if (($old_q_type == 'likert' and $q_type != 'likert') or ($old_q_type != 'likert' and $q_type == 'likert')) {
              echo "</table>\n<br />\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
          }

          if ($theme != '') {
              if ($old_q_type == 'likert') {
                  echo '</table><br /><table cellpadding="4" cellspacing="0" border="0" width="100%">';  // Close off table if last question was likert scale.
              }
              echo '<tr><td colspan="2" class="theme">' . $theme . '</td></tr>';
          }

          if (trim($notes) != '' and $q_type != 'likert') {
              echo '<tr><td></td><td class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $notes . '</td></tr>';
          }

          if (trim($scenario) != '' and $q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'likert' and $q_type != 'enhancedcalc') {
              echo '<tr><a name="' . $question_no . '"></a><td class="q_no">' . $question_no . '.&nbsp;</td><td valign="top">' . $scenario . '<br /><br />';
              $li_set = 1;
          }
          if ($q_media != '' and $q_media != null and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'extmatch' and $q_type != 'area') {
              if (mb_substr($q_media, -4) == '.gif' or mb_substr($q_media, -4) == '.jpg' or mb_substr($q_media, -4) == 'jpeg' or mb_substr($q_media, -4) == '.png') {
                  if ($li_set == 0) {
                      echo '<tr><a name="' . $question_no . '"></a><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
                  }
                  $li_set = 1;
                  echo '<div class="mediadiv">';
                  $questiondata->set_media($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                  $render->render($questiondata, $string, 'paper/media.html');
                  echo "</div>\n";
              } else {
                  if ($li_set == 0) {
                      echo '<tr><a name="' . $question_no . '"></a><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
                  }
                  $li_set = 1;
                  echo '<div class="mediadiv">';
                  $questiondata->set_media($q_media, $q_media_width, $q_media_height, $q_media_alt, '', true);
                  $render->render($questiondata, $string, 'paper/media.html');
                  echo "</div>\n";
              }
          }
          if ($q_type != 'likert' and $q_type != 'enhancedcalc' and $q_type != 'info' and $q_type != 'hotspot' and $q_type != 'area') {
              if ($li_set == 0) {
                  echo '<tr><a name="' . $question_no . '"></a><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
              }
              $li_set = 1;
              echo $leadin;
          }
          if ($q_type == 'info') {
              if ($li_set == 0) {
                  echo '<tr><td colspan="2" style="padding-left:20px; padding-right:20px">' . $leadin;
              }
              $li_set = 1;
              $question_no--;
          }

          $old_leadin = $leadin;
          $old_scenario = $scenario;
          $old_notes = $notes;
          $old_q_type = $q_type;
          $old_q_id = $q_id;
          $old_theme = $theme;
          $old_screen = $screen;
          $old_correct_fback = $correct_fback;
          $options_array = array();
      }
      $options_array[] = array(
        'q_type' => $q_type,
        'score_method' => $score_method,
        'display_method' => $display_method,
        'settings' => $settings,
        'correct' => $correct,
        'scenario' => $scenario,
        'leadin' => $leadin,
        'q_media' => $q_media,
        'q_media_width' => $q_media_width,
        'q_media_height' => $q_media_height,
        'q_media_alt' => $q_media_alt,
        'q_media_num' => $q_media_num,
        'option_text' => $option_text,
        'o_media' => $option_media,
        'o_media_width' => $option_media_width,
        'o_media_height' => $option_media_height,
        'o_media_alt' => $option_media_alt,
        'marks_correct' => $marks_correct,
        'marks_incorrect' => $marks_incorrect
      );
  }
  $result->close();

  // Print the options for the last question on the screen.
  $excluded = $exclusions->get_exclusions_by_qid($old_q_id);
  if (count($options_array) > 0) {
      display_options($old_screen, $options_array, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_notes, $paper_type, $_GET['method'], $reviews, $excluded, false);
  }

  echo '</td></tr></table></td></tr>';
  echo "<tr><td colspan=\"2\" style=\"border-top: dotted #808080 1px; color:#808080; font-size:90%; font-weight:bold\">&nbsp;</td>\n</tr>\n";
  echo '</table>';
  if ($_GET['method'] == 'ebel') {
      if (isset($_GET['std_setID'])) {
          $result = $mysqli->prepare('SELECT category, percentage FROM ebel WHERE std_setID = ?');
          $result->bind_param('i', $_GET['std_setID']);
          $result->execute();
          $result->bind_result($category, $percentage);
          while ($result->fetch()) {
              $ebel[$category] = (isset($percentage)) ? round($percentage, 2) : null;
          }
          $result->close();
      }

      if (empty($ebel)) {
          $templateID = '';
          // If empty look to see if there is a default grid to load
          $result = $mysqli->prepare('SELECT ebel_grid_template FROM modules WHERE id = ?');
          $result->bind_param('i', $_GET['module']);
          $result->execute();
          $result->bind_result($templateID);
          $result->fetch();
          $result->close();
          if ($templateID == '') {
                $ebel = array('EE' => 0, 'EI' => 0, 'EN' => 0, 'ME' => 0, 'MI' => 0, 'MN' => 0, 'HE' => 0, 'HI' => 0, 'HN' => 0, 'EE2' => 0, 'EI2' => 0, 'EN2' => 0, 'ME2' => 0, 'MI2' => 0, 'MN2' => 0, 'HE2' => 0, 'HI2' => 0, 'HN2' => 0);
          } else {
              $result = $mysqli->prepare('SELECT EE, EI, EN, ME, MI, MN, HE, HI, HN, EE2, EI2, EN2, ME2, MI2, MN2, HE2, HI2, HN2, name FROM ebel_grid_templates WHERE id = ?');
              $result->bind_param('i', $templateID);
              $result->execute();
              $result->bind_result($ebel['EE'], $ebel['EI'], $ebel['EN'], $ebel['ME'], $ebel['MI'], $ebel['MN'], $ebel['HE'], $ebel['HI'], $ebel['HN'], $ebel['EE2'], $ebel['EI2'], $ebel['EN2'], $ebel['ME2'], $ebel['MI2'], $ebel['MN2'], $ebel['HE2'], $ebel['HI2'], $ebel['HN2'], $name);
              $result->fetch();
              $result->close();

              // Foreach
              foreach ($ebel as $key => $value) {
                  $ebel[$key] = round($value / 100, 2);
              }
          }
      }

      echo "<br />\n<div class=\"key\">" . $string['step2'] . "<br />&nbsp;</div>\n<br />\n";

      echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
      echo '<tr><td>&nbsp;</td><td style="width:220px; text-align:center"><strong>' . $string['essential'] . '</strong></td><td style="width:220px; text-align:center"><strong>' . $string['important'] . '</strong></td><td style="width:220px; text-align:center"><strong>' . $string['nicetoknow'] . "</strong></td></tr>\n";
      echo '<tr><td style="text-align:right"><strong>' . $string['easy'] . '</strong></td><td style="text-align:center; background-color:#F8F8F2"><input type="text" style="text-align:right; background-color:#F8F8F2; border:0; color:red; text-decoration:line-through" name="origee" id="origee" size="3" value="0" /><input type="text" style="text-align:right; background-color:#F8F8F2; border:0" name="ee" id="ee" size="7" value="0" />&nbsp;' . ebelDropdown('EE', $ebel) . '</td><td style="text-align:center; background-color:#F0F0E6"><input type="text" style="text-align:right; background-color:#F0F0E6; border:0; color:red; text-decoration:line-through" name="origei" id="origei" size="3" value="" /><input type="text" style="text-align:right; background-color:#F0F0E6; border:0" name="ei" id="ei" size="7" value="0" />&nbsp;' . ebelDropdown('EI', $ebel) . '</td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="origen" id="origen" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="en" id="en" size="7" value="0" />&nbsp;' . ebelDropdown('EN', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"easy_total\" id=\"easy_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
      echo '<tr><td style="text-align:right"><strong>' . $string['medium'] . '</strong></td><td style="text-align:center; background-color:#F0F0E6"><input type="text" style="text-align:right; background-color:#F0F0E6; border:0 solid red; color:red; text-decoration:line-through" name="origme" id="origme" size="3" value="" /><input type="text" style="text-align:right; background-color:#F0F0E6; border:0" name="me" id="me" size="7" value="0" />&nbsp;' . ebelDropdown('ME', $ebel) . '</td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="origmi" id="origmi" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="mi" id="mi" size="7" value="0" />&nbsp;' . ebelDropdown('MI', $ebel) . '</td><td style="text-align:center; background-color:#D5D5BB"><input type="text" style="text-align:right; background-color:#D5D5BB; border:0; color:red; text-decoration:line-through" name="origmn" id="origmn" size="3" value="" /><input type="text" style="text-align:right; background-color:#D5D5BB; border:0" name="mn" id="mn" size="7" value="0" />&nbsp;' . ebelDropdown('MN', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"medium_total\" id=\"medium_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
      echo '<tr><td style="text-align:right"><strong>' . $string['hard'] . '</strong></td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="orighe" id="orighe" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="he" id="he" size="7" value="0" />&nbsp;' . ebelDropdown('HE', $ebel) . '</td><td style="text-align:center; background-color:#D5D5BB"><input type="text" style="text-align:right; background-color:#D5D5BB; border:0; color:red; text-decoration:line-through" name="orighi" id="orighi" size="3" value="" /><input type="text" style="text-align:right; background-color:#D5D5BB; border:0" name="hi" id="hi" size="7" value="0" />&nbsp;' . ebelDropdown('HI', $ebel) . '</td><td style="text-align:center; background-color:#C8C8A6"><input type="text" style="text-align:right; background-color:#C8C8A6; border:0; color:red; text-decoration:line-through" name="orighn" id="orighn" size="3" value="" /><input type="text" style="text-align:right; background-color:#C8C8A6; border:0" name="hn" id="hn" size="7" value="0" />&nbsp;' . ebelDropdown('HN', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"hard_total\" id=\"hard_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
      echo "<tr><td>&nbsp;</td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"essential_total\" id=\"essential_total\" size=\"8\" style=\"text-align:center; border:0\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"important_total\" id=\"important_total\" size=\"8\" style=\"text-align:center; border:0\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"nice_total\" id=\"nice_total\" size=\"8\" style=\"text-align:center; border:0\" /></td></tr>\n";
      echo "<tr><td>&nbsp;</td><td style=\"text-align:center\" colspan=\"3\"><input type=\"text\" style=\"border:0; text-align:center\" name=\"cut_score\" id=\"cut_score\" size=\"70\" value=\"cut score=0%\" /></td></tr>\n";
      echo "</table>\n</div>\n<br />\n";

      echo "<br />\n";
      echo '<div class="key">' . $string['step3'] . '<br />';
        ?>
    <blockquote style="margin-top:8px; margin-bottom:8px">
      <?php
        if (isset($_GET['std_setID'])) {
            $ebel_dist = check_ebel_distinction_type($_GET['std_setID'], $mysqli);
        } else {
            $ebel_dist = 'grid';
        }
        ?>
    <input type="radio" id="distinction_type_grid" name="distinction_type" value="1"<?php if ($ebel_dist == 'grid') {
        echo ' checked="checked"';
                                                                                    } ?> /> <label for="distinction_type_grid"><?php echo $string['gridbelow']; ?></label><br />
    <input type="radio" id="distinction_type_t20" name="distinction_type" value="2"<?php if ($ebel_dist == 'top20') {
        echo ' checked="checked"';
                                                                                   } ?> /> <label for="distinction_type_t20"><?php echo $string['top20']; ?></label><br />
    <input type="radio" id="distinction_type_dna" name="distinction_type" value="3"<?php if ($ebel_dist == 'dna') {
        echo ' checked="checked"';
                                                                                   } ?> /> <label for="distinction_type_dna"><?php echo $string['donotapply']; ?></label><br />
    </blockquote>
      <?php
        echo "</div>\n<br />\n";

        echo "<div align=\"center\">\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
        echo '<tr><td>&nbsp;</td><td style="width:220px; text-align:center"><strong>' . $string['essential'] . '</strong></td><td style="width:220px; text-align:center"><strong>' . $string['important'] . '</strong></td><td style="width:220px; text-align:center"><strong>' . $string['nicetoknow'] . "</strong></td></tr>\n";
        echo '<tr><td style="text-align:right"><strong>' . $string['easy'] . '</strong></td><td style="text-align:center; background-color:#F8F8F2"><input type="text" style="text-align:right; border:0; color:red; text-decoration:line-through; background-color:#F8F8F2" name="origee2" id="origee2" size="3" value="" /><input type="text" style="text-align:right; border:0; background-color:#F8F8F2" name="ee2" id="ee2" size="7" value="0" />&nbsp;' . ebelDropdown('EE2', $ebel) . '</td><td style="text-align:center; background-color:#F0F0E6"><input type="text" style="text-align:right; background-color:#F0F0E6; border:0; color:red; text-decoration:line-through" name="origei2" id="origei2" size="3" value="" /><input type="text" style="text-align:right; background-color:#F0F0E6; border:0" name="ei2" id="ei2" size="7" value="0" />&nbsp;' . ebelDropdown('EI2', $ebel) . '</td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="origen2" id="origen2" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="en2" id="en2" size="7" value="0" />&nbsp;' . ebelDropdown('EN2', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"easy2_total\" id=\"easy2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
        echo '<tr><td style="text-align:right"><strong>' . $string['medium'] . '</strong></td><td style="text-align:center; background-color:#F0F0E6"><input type="text" style="text-align:right; background-color:#F0F0E6; border:0; color:red; text-decoration:line-through" name="origme2" id="origme2" size="3" value="" /><input type="text" style="text-align:right; background-color:#F0F0E6; border:0" name="me2" id="me2" size="7" value="0" />&nbsp;' . ebelDropdown('ME2', $ebel) . '</td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="origmi2" id="origmi2" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="mi2" id="mi2" size="7" value="0" />&nbsp;' . ebelDropdown('MI2', $ebel) . '</td><td style="text-align:center; background-color:#D5D5BB"><input type="text" style="text-align:right; background-color:#D5D5BB; border:0; color:red; text-decoration:line-through" name="origmn2" id="origmn2" size="3" value="" /><input type="text" style="text-align:right; background-color:#D5D5BB; border:0" name="mn2"id="mn2" size="7" value="0" />&nbsp;' . ebelDropdown('MN2', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"medium2_total\" id=\"medium2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
        echo '<tr><td style="text-align:right"><strong>' . $string['hard'] . '</strong></td><td style="text-align:center; background-color:#E4E4D2"><input type="text" style="text-align:right; background-color:#E4E4D2; border:0; color:red; text-decoration:line-through" name="orighe2"id="orighe2" size="3" value="" /><input type="text" style="text-align:right; background-color:#E4E4D2; border:0" name="he2" id="he2" size="7" value="0" />&nbsp;' . ebelDropdown('HE2', $ebel) . '</td><td style="text-align:center; background-color:#D5D5BB"><input type="text" style="text-align:right; background-color:#D5D5BB; border:0; color:red; text-decoration:line-through" name="orighi2" id="orighi2" size="3" value="" /><input type="text" style="text-align:right; background-color:#D5D5BB; border:0" name="hi2" id="hi2" size="7" value="0" />&nbsp;' . ebelDropdown('HI2', $ebel) . '</td><td style="text-align:center; background-color:#C8C8A6"><input type="text" style="text-align:right; background-color:#C8C8A6; border:0; color:red; text-decoration:line-through" name="orighn2" id="orighn2" size="3" value="" /><input type="text" style="text-align:right; background-color:#C8C8A6; border:0" name="hn2" id="hn2" size="7" value="0" />&nbsp;' . ebelDropdown('HN2', $ebel) . "</td><td style=\"border:0\"><input type=\"text\" value=\"\" name=\"hard2_total\" id=\"hard2_total\" size=\"8\" style=\"border: 0px\" /></td></tr>\n";
        echo "<tr><td>&nbsp;</td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"essential2_total\" id=\"essential2_total\" size=\"8\" style=\"text-align:center; border:0\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"important2_total\" id=\"important2_total\" size=\"8\" style=\"text-align:center; border:0\" /></td><td style=\"text-align:center\"><input type=\"text\" value=\"\" name=\"nice2_total\" id=\"nice2_total\" size=\"8\" style=\"text-align:center; border:0\" /></td></tr>\n";
        echo "<tr><td>&nbsp;</td><td style=\"text-align:center\" colspan=\"3\"><input type=\"text\" style=\"border:0; text-align:center\" name=\"cut_score2\" id=\"cut_score2\" size=\"70\" value=\"cut score=0%\" /></td></tr>\n";
        echo "</table>\n</div>\n<br />\n";
  }
  if ($_GET['method'] == 'modified_angoff') {
      echo '<input type="hidden" name="method" value="Modified Angoff" />';
  } else {
      echo '<input type="hidden" name="method" value="Ebel" />';
  }
  echo '<input type="hidden" name="module" value="' . $module . '" />';
  echo '<input type="hidden" name="folder" value="' . $folder . '" />';
  echo '<input type="hidden" name="paperID" value="' . $paperID . '" />';
  if (isset($_GET['std_setID'])) {
      echo '<input type="hidden" name="std_setID" value="' . $_GET['std_setID'] . '" />';
  }
  echo '<input type="hidden" name="stdIDNo" id="stdIDNo" value="' . $stdID . '" />';
    ?>
<div align="center">
<table cellpadding="2" cellspacing="0" border="0">
<tr><td style="text-align:center; color:#808080">ALT + S</td><td style="text-align:center; color:#808080">ALT + C</td><td></td><td></td></tr>
<tr><td><input type="submit" name="submit" value="<?php echo $string['saveexit']; ?>" accesskey="S" style="width:160px" /></td><td><input type="submit" name="continue" value="<?php echo $string['savecontinue']; ?>" accesskey="C" style="width:160px" /></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input onclick="javascript:window.location='index.php?paperID=<?php echo $paperID; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>'" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:90px" /></td></tr>

<tr><td colspan="2" style="text-align:center">
<?php
if (isset($state['banksave']) and $state['banksave'] == 'true') {
    echo '<input class="chk" type="checkbox" id="banksave" name="banksave" value="1" checked />&nbsp;' . $string['savebank'];
} else {
    echo '<input class="chk" type="checkbox" id="banksave" name="banksave" value="1" />&nbsp;' . $string['savebank'];
}
  $mysqli->close();

?>
</td><td colspan="2"></td></tr>
</table>
</div>
<br />
<input type="hidden" name="total_marks" id="total_marks" value="<?php echo $total_marks - $std_excluded ?>" />
</form>
</div>
<?php
// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$miscdataset['attributes']['method'] = $_GET['method'];
$render->render($miscdataset, array(), 'dataset.html');
$render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');

?>
</body>
</html>
