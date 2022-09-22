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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/question_types.php';
require '../include/display_functions.inc';
require_once '../include/errors.php';

$qid = check_var('q_id', 'GET', true, false, true);

$marks_color = '#808080';
$themecolor = '#316AC5';
$labelcolor = '#C00000';
$textsize = 100;
$question_no = 0;
$old_q_id = '';
$question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, settings, marks_correct,
    marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text,
    source, width, height, alt, notes, settings
    FROM questions
    LEFT JOIN options on questions.q_id = options.o_id
    LEFT JOIN options_media ON options.id_num = options_media.oid
    LEFT JOIN media m on options_media.mediaid = m.id
    WHERE q_id= ? ORDER BY id_num");
$question_data->bind_param('i', $qid);
$question_data->execute();
$question_data->store_result();
$question_data->bind_result(
    $q_type,
    $q_id,
    $score_method,
    $display_method,
    $settings,
    $marks_correct,
    $marks_incorrect,
    $marks_partial,
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
    $setting
);
$num_rows = $question_data->num_rows;
while ($question_data->fetch()) {
    if ($old_q_id != $q_id) {
        $question['theme'] = trim($theme);
        $question['scenario'] = trim($scenario);
        $question['leadin'] = trim($leadin);
        $question['notes'] = trim($notes);
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['settings'] = $settings;
        $media = QuestionUtils::getMediaAsString($qid);
        $question['q_media'] = $media['source'];
        $question['q_media_width'] = $media['width'];
        $question['q_media_height'] = $media['height'];
        $question['q_media_alt'] = $media['alt'];
        $question['q_media_num'] = $media['num'];
        $question['dismiss'] = '';
        $question['settings'] = $settings;
        $question['screen'] = 1;
        // Preview questions are always on the first screen.
        if ($q_type == 'enhancedcalc') {
            if (!is_array($settings)) {
                $settings = json_decode($settings, true);
            }
            if (!isset($question['object'])) {
                require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
                $question['object'] = new EnhancedCalc($configObject);
                $question['object']->load($question);
            }
        }
    }
    $question['options'][] = array(
        'correct' => $correct,
        'option_text' => $option_text,
        'o_media' => $option_media,
        'o_media_width' => $option_media_width,
        'o_media_height' => $option_media_height,
        'o_media_alt' => $option_media_alt,
        'marks_correct' => $marks_correct,
        'marks_incorrect' => $marks_incorrect,
        'marks_partial' => $marks_partial
    );
}
$question_data->close();
$question_no = 0;
$paper_type = 0;
$unanswered = false;
$user_answers[1] = array();
$question['assigned_number'] = (isset($_GET['qNo'])) ? $_GET['qNo'] : 1;
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['preview'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/html5.css" />
  <link rel="stylesheet" href="../../node_modules/mediaelement/build/mediaelementplayer.min.css"/>
<?php
$css = PaperProperties::paperCss(
    $userObject,
    UserObject::BGCOLOUR,
    UserObject::FGCOLOUR,
    UserObject::TEXTSIZE,
    UserObject::MARKSCOLOUR,
    UserObject::THEMECOLOUR,
    UserObject::LABELCOLOUR,
);

echo $css;
?>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>"
            data-three="<?php echo $configObject->get_setting('core', 'paper_threejs'); ?>">
  </script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script type="text/javascript" src="../js/startinit.min.js"></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
?>

  <?php

    // Check if any 3d file types are enabled and render js.
    threed_handler::render_js($string);
    ?>
</head>
<body>
<div id="maincontent">
  <table cellpadding="4" cellspacing="0" border="0" width="100%" style="table-layout:fixed">
  <col width="40"><col>
<?php
  display_question($configObject, $question, $paper_type, 0, 1, '', $question_no, $user_answers, $unanswered, $texteditorplugin);
$question_nos[] = $old_q_id;
echo '</table></div>';
$render = new render($configObject);
// Paper dataset.
  $dataset['name'] = 'paper';
$dataset['attributes']['timed'] = false;
$dataset['attributes']['refcount'] = 0;
$render->render($dataset, array(), 'dataset.html');
// User dataset.
  $datasetuser['name'] = 'user';
$datasetuser['attributes']['student'] = false;
$render->render($datasetuser, array(), 'dataset.html');
// JS utils dataset.
  $jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
  $miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$render->render($miscdataset, array(), 'dataset.html');
$render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
?>
 </body>
 </html>
