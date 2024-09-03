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
 * @author Simon Wilkinson, Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

$paperID    = check_var('paperID', 'GET', true, false, true);
$q_id       = check_var('q_id', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);
$phase      = check_var('phase', 'GET', true, false, true);
$marked     = ''; // init hiden marked
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $properties->get_paper_type();
$paper_title = $properties->get_paper_title();

// Check the question exists on the paper.
if (!QuestionUtils::question_exists_on_paper($q_id, $paperID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function displayMarks($id, $default, $log_record_id, $log, $halfmarks, $tmp_username, $marks, $string)
{
    $html = '<select id="mark' . $id . '" name="mark' . $id . '" ><option value="NULL"></option>';
    $inc = 1;
    if ($halfmarks == true) {
        $inc = 0.5;
    }
    for ($i = 0; $i <= $marks; $i += $inc) {
        $display_i = $i;
        if ($i == 0.5) {
            $display_i = '&#189;';
        } elseif ($i - floor($i) > 0) {
            $display_i = floor($i) . '&#189;';
        }
        if ($i == $default and is_numeric($default)) {
            $html .= "<option value=\"$i\" selected>$display_i</option>";
        } else {
            $html .= "<option value=\"$i\">$display_i</option>";
        }
    }
    $html .= <<< HTML
</select>&nbsp;<span style="color:black">{$string['marks']}</span><br />&nbsp;
<input type="hidden" id="logrec{$id}" name="logrec{$id}" value="{$log_record_id}">
<input type="hidden" id="log{$id}" name="log{$id}" value="{$log}">
<input type="hidden" id="username{$id}" name="username{$id}" value="{$tmp_username}">
HTML;
    return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>ExamSys: <?php echo $string['textboxmarking']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/textbox_marking.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcements.css" />
    <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/finish.css" />
  <link rel="stylesheet" href="../node_modules/mediaelement/build/mediaelementplayer.min.css"/>
  <style type="text/css">
  .noanswer {background-image: url(../artwork/small_yellow_warning_icon.gif); background-repeat:no-repeat; background-position: 2px center; background-color:#FFC0C0; padding-left:20px; padding-right:5px; color: #800000 !important}
  .marked {color:#808080}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>"
            data-three="<?php echo $configObject->get_setting('core', 'paper_threejs'); ?>">
  </script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <?php
    $texteditorplugin = \plugins\plugins_texteditor::get_editor();
    $texteditorplugin->display_header();

    // Check if any 3d file types are enabled and render js.
    threed_handler::render_js($string);
    ?>
</head>

<body>
<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu();

$time_int = \log::getStartInterval($paper_type);

$candidate_no = 0;
if (in_array($paper_type, [\assessment::TYPE_FORMATIVE, \assessment::TYPE_PROGRESS, \assessment::TYPE_SUMMATIVE])) {
    // Get how many students took the paper.
    $sql = "
    SELECT DISTINCT
        lm.userID
    FROM
        log_metadata lm
        INNER JOIN users u ON lm.userID = u.id,
        user_roles ur JOIN roles r ON ur.roleid = r.id
    WHERE 
        lm.paperID = ?
        AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
        AND lm.started <= ?
        AND u.id = ur.userid
        AND r.name IN ('Student', 'graduate')
    ";
    $result = $mysqli->prepare($sql);
    $result->bind_param('iss', $paperID, $startdate, $enddate);
    $result->execute();
    $result->bind_result($tmp_userID);
    while ($result->fetch()) {
        $candidate_no++;
    }
    $result->close();
}

if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') {
    $marked = 'AND t.mark is NULL';
} else {
    $marked = '';
}

$studentstr = '';
if ($phase == 2) {
    $remark_array = textbox_marking_utils::get_remark_users($paperID, $mysqli);
    $students = array_keys($remark_array);
    $studentstr = 'AND u.id IN (' .  implode(',', $students) . ')';
}

if ($paper_type == \assessment::TYPE_FORMATIVE) {
    $progress_time_int = \log::getStartInterval(\assessment::TYPE_PROGRESS);
    $sql = <<< SQL
SELECT 0 AS logtype, l.id, lm.userID, l.user_answer, t.mark, l.q_id, comments, reminders
  FROM log0 l
  JOIN log_metadata lm ON l.metadataID = lm.id
  JOIN users u ON u.id = lm.userID
  JOIN user_roles ur ON u.id = ur.userid
  JOIN roles r ON ur.roleid = r.id
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND r.name IN ('Student', 'graduate')
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
  AND lm.started <= ? $marked $studentstr
UNION ALL
SELECT 1 AS logtype, l.id, lm.userID, l.user_answer, t.mark, l.q_id, comments, reminders
  FROM log1 l
  JOIN log_metadata lm ON l.metadataID = lm.id
  JOIN users u ON u.id = lm.userID
  JOIN user_roles ur ON u.id = ur.userid
  JOIN roles r ON ur.roleid = r.id
  LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
  WHERE lm.paperID = ?
  AND r.name IN ('Student', 'graduate')
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL $progress_time_int MINUTE) >= ?
  AND lm.started <= ? $marked $studentstr
ORDER BY id
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiissiiiss', $phase, $paperID, $q_id, $startdate, $enddate, $phase, $paperID, $q_id, $startdate, $enddate);
} else {
    $sql = <<< SQL
SELECT $paper_type AS logtype, l.id, lm.userID, l.user_answer, t.mark, l.q_id, comments, reminders
FROM log{$paper_type} l
JOIN log_metadata lm ON l.metadataID = lm.id
JOIN users u ON u.id = lm.userID
JOIN user_roles ur ON u.id = ur.userid
JOIN roles r ON ur.roleid = r.id
LEFT JOIN textbox_marking t ON l.id = t.answer_id AND lm.paperID = t.paperID AND t.phase = ?
WHERE lm.paperID = ?
AND r.name IN ('Student', 'graduate')
AND l.q_id = ?
AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
AND lm.started <= ? $marked $studentstr
ORDER BY l.id
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('iiiss', $phase, $paperID, $q_id, $startdate, $enddate);
}
$answer_no = 0;
$result->execute();
$result->store_result();
$result->bind_result($logtype, $id, $tmp_userID, $user_answer, $student_mark, $textbox_q_id, $comments, $reminders_selected);

$phase_description = '';
if (!isset($_GET['phase'])) {
    $phase_description .= $string['finalisemarks'];
    $tmp_phase = '';
} elseif ($_GET['phase'] == 1) {
    $phase_description .= $string['primarymarking'];
    $tmp_phase = '&phase=1';
} elseif ($_GET['phase'] == 2) {
    $phase_description .= $string['secondmarking'];
    $tmp_phase = '&phase=2';
}
$phase_description .= ' Q' . $_GET['qNo'];
$out_of = $result->num_rows;
$phase_description .= ': <span style="font-weight: normal">' . number_format($out_of) . ' ' . $string['candidates'] . '</span>';

echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
} elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper_title . '</a></div>';
echo '<div style="text-align:right; vertical-align:top"><br /><input class="chk" type="checkbox" name="hidemarked" id="hidemarked" value="1"';
if (isset($state['hidemarked']) and $state['hidemarked'] == 'true') {
    echo ' checked';
}
echo '  /> ' . $string['hidemarked'] . "&nbsp;</div>\n";
echo '<div class="page_title">' . $phase_description . '</div>';
echo "</div>\n";

if ($candidate_no == 0) {
    $msg = sprintf($string['noattempts'], date_utils::rogoToDisplay($startdate), date_utils::rogoToDisplay($enddate));
    echo $notice->info_strip($msg, 100);
    echo "</form>\n</body>\n</html>\n";
    exit();
}

$half_marks = true;
?>
<form id="theform" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&amp;q_id=<?php echo $_GET['q_id'] ?>&amp;startdate=<?php echo $startdate ?>&amp;enddate=<?php echo $enddate ?>&amp;module=<?php echo $_GET['module'] ?>&amp;folder=<?php echo $_GET['folder'] ?>&amp;phase=<?php echo $phase ?>&amp;action=mark&amp;qNo=<?php echo $_GET['qNo'] ?>" method="post" autocomplete="off">
<input type="hidden" id="marker_id" name="marker_id" value="<?php echo $userObject->get_user_ID() ?>" />
<input type="hidden" id="paper_id" name="paper_id" value="<?php echo $paperID ?>" />
<input type="hidden" id="q_id" name="q_id" value="<?php echo $q_id ?>" />
<input type="hidden" id="phase" name="phase" value="<?php echo $phase ?>" />
<?php
// -- Question Pane ---------------------------------------------------------------------------------------------------
?>
<div id="question_pane">
<?php

  echo '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  $question_offset = 1;
    $no_screens = $properties->get_max_screen();
if ($no_screens > 1) {
    echo '<table cellspacing="1" cellpadding="1" border="0" style="font-weight:bold; color:white"><tr>';
    for ($i = 1; $i <= $no_screens; $i++) {
        echo "<td class=\"scr_ans\">$i</td>\n";
    }
    echo '</tr></table>';
}
  echo '</td></tr></table>';

    $marks_array = array();

  echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";

  $q_no = 0;
  $old_q_id = 0;
  $old_screen = 1;
  $reminders = array();

  $question_data = $mysqli->prepare('SELECT screen, q_type, q_id, id_num, option_text, theme, scenario, leadin, notes, marks_correct, correct_fback, settings FROM (papers, questions, options) WHERE paper = ? AND papers.question = questions.q_id AND questions.q_id = options.o_id ORDER BY display_pos, id_num');
  $question_data->bind_param('i', $_GET['paperID']);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($screen, $q_type, $q_id, $option_id, $option_text, $theme, $scenario, $leadin, $notes, $marks_correct, $correct_fback, $settings);
  $num_rows = $question_data->num_rows;
while ($question_data->fetch()) {
    $marks_array[$q_id] = $marks_correct;

    if ($old_screen != $screen) {
        echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
        echo '<tr><td colspan="2"><div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div></td></tr>';
    }

    if ($q_id == $_GET['q_id']) {
        $reminders[] = array('option_id' => $option_id, 'text' => $option_text);
    }

    if ($old_q_id != $q_id) {
        if ($q_no + 1 == $_GET['qNo'] and $q_type != 'info') {
            $tmp_color = '#FFFFDD';
        } else {
            $tmp_color = 'white';
        }

        $li_set = 0;
        echo "<tr><td colspan=\"2\"><a name=\"q_id$q_id\"></a>&nbsp;</td></tr>\n";

        if ($theme != '') {
            echo '<tr><td colspan="2"><p class="theme">' . $theme . '</p></td></tr><tr><td colspan="2">&nbsp;</td></tr>';
        }
        if (isset($notes) && trim($notes) !== '') {
            echo '<tr><td></td><td class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="' . $string['note'] . '" />&nbsp;<strong>' . mb_strtoupper($string['note']) . ':</strong>&nbsp;' . $notes . '</td></tr>';
        }

        if ($scenario != '') {
            echo "<tr style=\"background-color:$tmp_color\"><td class=\"q_no\">";
            if ($q_type != 'info') {
                $q_no++;
                echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
            }
            echo "</td><td>$scenario<br />\n<br />";
            $li_set = 1;
        }

        if ($q_type == 'info') {
            echo "<tr style=\"background-color:$tmp_color\"><td>";
            $li_set = 0;
        } elseif ($q_type != 'info' and $li_set == 0) {
            $q_no++;
            echo "<tr style=\"background-color:$tmp_color\"><td class=\"q_no\">";
            echo "<a name=\"q$q_no\">$q_no.&nbsp;</a>";
        }
        if ($li_set == 0) {
            echo "</td><td style=\"background-color:$tmp_color\">";
        }
        // Get Media.
        $media = QuestionUtils::getMediaAsString($q_id);
        $q_media = $media['source'];
        $q_media_height = $media['height'];
        $q_media_width = $media['width'];
        $q_media_alt = $media['alt'];
        if ($q_media != '' and $q_media != null) {
            $media_list = explode('|', $q_media);
            $media_list_width = explode('|', $q_media_width);
            $media_list_height = explode('|', $q_media_height);
            $media_list_alt = explode('|', $q_media_alt);
            $questiondata = new plugins\questions\textbox\renderdata();
            $render = new render($configObject);
            for ($i = 0; $i < count($media_list); $i++) {
                if (!empty($media_list[$i])) {
                    $questiondata->set_media($media_list[$i], $media_list_width[$i], $media_list_height[$i], $media_list_alt[$i], '', true);
                    echo '<div class="mediadiv">';
                    $render->render($questiondata, $string, 'paper/media.html');
                    echo "</div>\n";
                }
            }
        }
        echo "$leadin</td></tr>\n";

        $questionsettings[$q_id] = json_decode($settings ?? '', true);


        if ($q_type != 'info') {
            echo "<tr style=\"background-color:$tmp_color\"><td></td><td class=\"mk\"><br />($marks_correct " . $string['marks'] . ")</td></tr>\n";
            echo "<tr style=\"background-color:$tmp_color\"><td>&nbsp;</td><td class=\"fback\"><br />" . (isset($correct_fback) ? nl2br($correct_fback) : '') . "</td></tr>\n";
        }
    }
    $old_q_id = $q_id;
    $old_screen = $screen;
}

  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";
?>
</div>
<?php
// -- Answer Pane ---------------------------------------------------------------------------------------------------
?>
<div id="answer_pane">
  <div style="height:30px">
    <div id="save_message"><?php echo $string['answer_saved'] ?></div>
    <div id="save_fail_message"><?php echo $string['saveerror'] ?></div>
  </div>
<?php
if ($result->num_rows == 0) {
    echo '<p>' . $string['nostudents'] . '</p>';
}

  $answer_shown = false;
while ($result->fetch()) {
    if ($phase == 1 or ($phase == 2 and isset($remark_array[$tmp_userID]))) {
        $answer_no++;

        $style = '';

        if (is_numeric($student_mark)) {  // Marked previously so grey out.
            $style .= ' marked';
        }

        if ($answer_shown) {
            $style .= ' hide';
        } else {
            $answer_shown = true;
        }

        echo '<div class="student-answer-block' . $style . '">';

        $out_of = $result->num_rows;
        echo '<p class="theme" style="padding-left:0">' . sprintf($string['mark_progress'], $answer_no, $out_of) . "</p>\n";
        echo '<div id="ans_' . $answer_no . '"><div class="student_ans">' . nl2br(render_user_answer($user_answer, $questionsettings[$textbox_q_id], $string)) . '</div><div class="student_marks">' . displayMarks($answer_no, $student_mark, $id, $logtype, $half_marks, $tmp_userID, $marks_array[$textbox_q_id], $string) . "</div></div>\n";

        if (count($reminders) > 0) {
            $reminders_selected = explode('|', $reminders_selected ?? '');
            echo '<ul class="reminders">';
            foreach ($reminders as $reminder) {
                $remindertext = trim($reminder['text']);
                if (empty($remindertext)) {
                    continue;
                }
                $checked = (in_array($reminder['option_id'], $reminders_selected)) ? ' checked="checked"' : '';
                echo '<li><label><input type="checkbox" id="reminder_' . $answer_no . '" name="reminder_' . $answer_no . '" value="' . $reminder['option_id'] . '" class="reminder"' . $checked . '> ' . $reminder['text'] . '</label></li>';
            }
            echo '</ul>' . "\n";
        }
        echo '<label for="comment_' . $answer_no . '"><strong>' . $string['comments'] . '</strong> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_comments'] . '" /><br /><textarea name="comment' . $answer_no . '" id="comment' . $answer_no . '" rows="6" class="comment-box">' . $comments . '</textarea>' . "\n";

        if ($answer_no != 1 and $answer_no <= $result->num_rows) {
            echo '<input type="submit" id="prev_' . $answer_no . '" class="tbmark ok" data-id="' . $answer_no . '" value="' . $string['previous'] . '" />';
        }
        if ($answer_no != $out_of) {
            echo '<input type="submit" id="next_' . $answer_no . '" class="tbmark ok" style="float:right; margin-right: -5px" data-id="' . $answer_no . '" value="' . $string['next'] . '" />';
        } else {
            echo '<input type="submit" id="finish_' . $answer_no . '" class="tbmark ok" style="float:right; margin-right: -5px" data-id="' . $answer_no . '" value="' . $string['finish'] . '" />';
        }
        echo '</div>' . "\n";
    }
}
  $result->close();

function render_user_answer($answer, $settings, $string)
{
    $answer = trim(textbox_marking_utils::higlightterms($settings, $answer));
    $answer_display = '';
    if ($answer == '') {
        $answer_display = '<span class="noanswer">' . $string['noanswer'] . '</span>';
    }

    return $answer_display . $answer;
}
?>

</div>
</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$jsmiscdataset['name'] = 'dataset';
$jsmiscdataset['attributes']['hash'] = 'q_id' . $_GET['q_id'];
$render->render($jsmiscdataset, array(), 'dataset.html');
?>
<script src='../js/textboxinit.min.js'></script>
</body>
</html>
