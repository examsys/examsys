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
require './display_functions.inc';
require '../include/errors.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';

$id = check_var('id', 'GET', true, false, true);
$refpane = param::optional('refpane', 0, param::INT, param::FETCH_POST);
// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$start_of_day_ts = strtotime('midnight');

/*
* Set the default colour scheme for this paper and allow current users' special settings to override
* $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color are passed by reference!!
*/
$bgcolor = $fgcolor = $textsize = $marks_color = $themecolor = $labelcolor = $font = $unanswered_color = $dismiss_color = '';
$propertyObj->set_paper_colour_scheme($userObject, $bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font, $unanswered_color, $dismiss_color);

$marking                            = $propertyObj->get_marking();
$paperID                            = $propertyObj->get_property_id();
$start_date                     = $propertyObj->get_start_date();
$paper_type                     = $propertyObj->get_paper_type();
$original_paper_type    = $propertyObj->get_paper_type();
$paper_prologue             = $propertyObj->get_paper_prologue();

require '../config/start.inc';

// Get how many screens make up the question paper.
$screen_data = $propertyObj->get_screens(false);
$no_screens = $propertyObj->get_max_screen();

// Determine which review deadline to use.
if ($userObject->has_role('External Examiner')) {
    $review_type = 'External';
    $review_deadline = strtotime($propertyObj->get_external_review_deadline());
} else {
    $review_type = 'Internal';
    $review_deadline = strtotime($propertyObj->get_internal_review_deadline());
}

// Create a new review object.
$review = new Review($paperID, $userObject->get_user_ID(), $review_type, $mysqli);

// Get standards setting data
if ($marking{0} == '2') {
    $standards_setting = array();
    $tmp_parts = explode(',', $marking);
  
    $standard_setting = new StandardSetting($mysqli);
    $standards_setting = $standard_setting->get_ratings_by_question($tmp_parts[1]);
} else {
    $standards_setting = array();
}

// Load any reference materials.
list($reference_materials, $max_ref_width) = $propertyObj->load_reference_materials();

// Extract the posted variables.
$current_screen = 1;
if (isset($_POST['next'])) {
    $current_screen = $_POST['current_screen'];
} elseif (isset($_POST['prev'])) {
    $current_screen = $_POST['current_screen'] - 2;
} elseif (isset($_POST['jump_screen'])) {
    $current_screen = $_POST['jump_screen'];
}

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">

<title><?php echo $propertyObj->get_paper_title() ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<link rel="stylesheet" type="text/css" href="../css/html5.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />
<link rel="stylesheet" type="text/css" href="../css/review.css" />
<link rel="stylesheet" href="../node_modules/mediaelement/build/mediaelementplayer.min.css"/>

<script id="rogoconfig"
        data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
        data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>"
        data-three="<?php echo $configObject->get_setting('core', 'paper_threejs'); ?>">
</script>
<script src='../js/require.js'></script>
<script src='../js/main.min.js'></script>
<script src='../js/reviewinit.min.js'></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
  $texteditorplugin->display_header();

  // Check if any 3d file types are enabled and render js.
  threed_handler::render_js($string);

  $render = new render($configObject);
if ($propertyObj->get_calculator()) {
    $render->render(null, null, 'jcalc98_header.html');
}
?>
</head>
<body>
<?php
if ($propertyObj->get_calculator()) {
    $render->render(null, null, 'jcalc98.html');
}
?>
<div id="maincontent">
<?php
if ($current_screen < $no_screens) {
    echo '<form method="post" id="qForm" name="questions" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id;
} else {
    echo '<form method="post" id="qForm" name="questions" action="finish.php?id=' . $id;
}
echo '" autocomplete="off">';   // Warning message only in linear navigation mode.
?>
  <table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
  <tr><td valign="top">
  <?php
    if (isset($_POST['old_screen']) and (($_POST['old_screen'] != '' and $start_of_day_ts <= $review_deadline and time() <= $start_date) or $start_date == '')) {
        $review->record_comments($_POST['old_screen']);
    }

    echo $top_table_html;
    echo '<tr><td><div class="paper">' . $propertyObj->get_paper_title() . '</div>';
    $question_offset = 0;
    if ($no_screens > 1) {
        for ($i = 1; $i <= $no_screens; $i++) {
            if ($i == $current_screen) {
                echo '<div class="scr_cur"';
            } else {
                echo '<div class="scr_ans"';
            }
            $no_questions = 0;
            if (isset($screen_data[$i])) {
                foreach ($screen_data[$i] as $screen_question) {
                    $no_questions++;
                }
            }
            if ($no_questions == 1) {
                echo ' title="' . $no_questions . ' question">';
            } else {
                echo ' title="' . $no_questions . ' questions">';
            }

            if ($i < $current_screen and isset($screen_data[$i])) {
                foreach ($screen_data[$i] as $screen_question) {
                    if ($screen_question[0] != 'info') {
                        $question_offset++;
                    }
                }
            }
            echo "$i</div>\n";
        }
        echo "<div style=\"clear:both\"></div>\n";


        for ($i = 1; $i <= $no_screens; $i++) {
            if ($i == $current_screen) {
                echo '<div class="scr_arrow"></div>';
            } else {
                echo '<div class="scr_spacer">&nbsp;</div>';
            }
        }
    }
    echo '</td>';
    echo $logo_html;
  
    if (($start_of_day_ts > $review_deadline or time() > $start_date) and $start_date != '') {
        echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td class=\"redwarn\" style=\"width:40px; line-height:0\"><img src=\"../artwork/late_warning_icon.png\" width=\"32\" height=\"32\" alt=\"Clock\" /></td><td class=\"redwarn\"><strong>{$string['deadlineexpired']}</strong>&nbsp;&nbsp;&nbsp;{$string['deadlinepassed']}</td></tr></table>\n";
    }
  
    $previous_duration = 0;
    $screen_pre_submitted = 0;
    
    // Load past reviews from the database.
    $review->load_reviews();
    
    $old_leadin = '';
    $old_q_type = '';
    $old_q_id = 0;
    $question_no = 0;
    $q_displayed = 0;
    $marks = 0;
    $old_theme = '';
    $previous_q_type = '';

    $user_answers = array();
    $questions_array = $propertyObj->build_paper(false, null, null);

    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
    echo "<col width=\"40\"><col>\n";
  
    // Random / Keyword questions.
    $tmp_questions_array = array();
    $tmp_q_no = 0;
    foreach ($questions_array as &$question) {
        if ($question['q_type'] != 'info') {
            $tmp_q_no++;
        }
        if ($question['q_type'] == 'random') {
            $tmp_questions_array[] = $propertyObj->randomQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
        } elseif ($question['q_type'] == 'keyword_based') {
            $tmp_questions_array[] = $propertyObj->keywordQOverwrite($question, $user_answers, $screen_data, $used_questions, $string);
        } else {
            $tmp_questions_array[] = $question;
        }
    }
    unset($questions_array);

    // Display the questions
    foreach ($tmp_questions_array as &$question) {
        // Question not on this screen, don't display
        if ($question['screen'] != $current_screen) {
            continue;
        }

        if ($question['q_type'] == 'enhancedcalc') {
            require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
            if (!isset($configObj)) {
                $configObj = Config::get_instance();
            }
            $question['object'] = new EnhancedCalc($configObj);
            $question['object']->load($question);
        }

        if ($screen_pre_submitted == 1 and $q_displayed == 0) {
            echo "<tr><td colspan=\"2\"><span style=\"background-color:#FFC0C0\">&nbsp;&nbsp;&nbsp;&nbsp;</span> = unanswered question</td></tr>\n";
        }
        if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') {
            echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
        }
        if ($q_displayed == 0 and $question['theme'] == '') {
            echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
        }

        if (!$userObject->has_role('External Examiner') and !$userObject->has_role('Internal Reviewer')) {
            echo '<tr><td colspan="2"><a href="/question/edit/index.php?q_id=' . $question['q_id'] . "&qNo=$question_no&paperID=$paperID\" target=\"_blank\" style=\"padding-left: 40px;\">{$string['editquestion']}</a></td></tr>";
        }

        display_question($configObject, $question, $propertyObj->get_paper_type(), $propertyObj->get_calculator(), $current_screen, $previous_q_type, $question_no, $question_offset, $start_of_day_ts, $texteditorplugin);
        $previous_q_type = $question['q_type'];
        $q_displayed++;
    }
  
    echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

    $current_screen++;
    echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
    echo '<input type="hidden" name="page_start" value="' . date('YmdHis', time()) . "\" />\n";
    echo '<input type="hidden" name="old_screen" value="' . ($current_screen - 1) . "\" />\n";
    echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
    echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"\" />\n";

    echo $bottom_html;
    echo '<span style="color:white">
      <span id="theTime" type="text" class="thetime"></span>
  </span>';
    echo '</td><td align="right">';
    if ($propertyObj->get_bidirectional() == 1 and $no_screens > 1) {
        if ($current_screen > 2) {
            echo '<input id="previous" type="submit" name="prev" value="&lt; ' . $string['screen'] . ' ' . ($current_screen - 2) . '" />';
        }
        if ($original_paper_type == '0' or $original_paper_type == '1' or $original_paper_type == '2') {
            echo '<select name="jump_screen" id="jumpscreen">';
            for ($i = 1; $i <= $no_screens; $i++) {
                if ($i == ($current_screen - 1)) {
                    echo "<option value=\"$i\" selected>$i</option>";
                } else {
                    echo "<option value=\"$i\">$i</option>";
                }
            }
            echo '</select>';
        }
    }
    if ($current_screen > $no_screens) {
        echo '<input id="finish" type="submit" name="next" value="' . $string['finish'] . '" />';
    } else {
        echo '<input id="next" type="submit" name="next" value="' . $string['screen'] . ' ' . $current_screen . ' &gt;" />';
    }
    echo '</td></tr></table>';

    ?>
</td></tr></table>
</form>
</div>
<?php

if (count($reference_materials) > 0) {
    $refdata = array(
    'ref' => $reference_materials,
    'refpane' => $refpane
    );
    $render->render($refdata, $string, 'paper/refmaterial.html');
}
$mysqli->close();

// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
// Dataset.
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['language'] = $language;
$miscdataset['attributes']['rootpath'] = $cfg_root_path;
$miscdataset['attributes']['bidirectional'] = (bool)$propertyObj->get_bidirectional();
$miscdataset['attributes']['id'] = $id;
$miscdataset['attributes']['self'] = $_SERVER['PHP_SELF'];
$render->render($miscdataset, array(), 'dataset.html');
// CSS dataset.
$datasetcss['name'] = 'css';
$datasetcss['attributes']['bgcolor'] = $bgcolor;
$datasetcss['attributes']['fgcolor'] = $fgcolor;
$datasetcss['attributes']['font'] = $font;
$datasetcss['attributes']['textsize'] = $textsize;
$datasetcss['attributes']['unanswered_color'] = $unanswered_color;
$datasetcss['attributes']['themecolor'] = $themecolor;
$datasetcss['attributes']['marks_color'] = $marks_color;
$datasetcss['attributes']['dismiss_color'] = $dismiss_color;
$datasetcss['attributes']['max_ref_width'] = $max_ref_width;
$datasetcss['attributes']['special_needs'] = $userObject->is_special_needs();
$render->render($datasetcss, array(), 'dataset.html');
// Paper dataset.
$dataset['name'] = 'paper';
$dataset['attributes']['refcount'] = count($reference_materials);
$render->render($dataset, array(), 'dataset.html');
$render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
?>
<input type="hidden" name="refpane" id="refpane" value="<?php echo $refpane; ?>" />
</body>
</html>
