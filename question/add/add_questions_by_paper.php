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

require '../../include/staff_auth.inc';
require '../../include/errors.php';
require '../../include/question_types.php';
$question_paper = check_var('question_paper', 'GET', true, false, true);
if (!Paper_utils::paper_exists($question_paper, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../../artwork/page_not_found.png', '#C00000', true, true);
}

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>by Paper</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <style type="text/css">
    body {font-size:80%}
    .divider {font-size:80%; font-weight:bold; padding-left:6px}
    .s {padding-left:6px}
    .q_no {text-align:right; width:35px}

<?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>">
  </script>
  <script src='../../js/require.js'></script>
  <script src='../../js/main.min.js'></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
?>

</head>

<body>
<?php
  // Get the title of the paper.
  $stmt = $mysqli->prepare('SELECT paper_title FROM properties WHERE property_id = ?');
$stmt->bind_param('i', $question_paper);
$stmt->execute();
$stmt->bind_result($paper_title);
$stmt->fetch();
$stmt->close();
echo "<form name=\"theform\" method=\"post\" action=\"\" autocomplete=\"off\">\n";
echo "<input type=\"hidden\" name=\"screen\" value=\"1\" />\n";
echo "<table class=\"header\">\n";
echo "<tr><th colspan=\"7\" style=\"font-size:160%; font-weight:bold\">&nbsp;$paper_title</th></tr>\n";
echo '<tr><th></th><th></th><th>&nbsp;</th><th class="vert_div">' . $string['question'] . '</th><th class="vert_div">' . $string['type'] . '</th><th style="width:90px" class="vert_div">' . $string['modified'] . '</th><th class="vert_div" style="width:90px">' . $string['status'] . "</th></tr>\n";
// Get the questions in order off the paper.
  $stmt = $mysqli->prepare("SELECT questions.q_id, leadin, q_type, screen, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS last_edited, locked, parts, status FROM (papers, questions) LEFT JOIN question_exclude ON questions.q_id = question_exclude.q_id WHERE papers.paper = ? AND papers.question = questions.q_id ORDER BY screen, display_pos");
$stmt->bind_param('i', $question_paper);
$stmt->execute();
$stmt->bind_result($q_id, $leadin, $q_type, $screen, $last_edited, $locked, $parts, $status);
$old_screen = 0;
$question_no = 0;
while ($stmt->fetch()) {
    if ($q_type != 'info') {
        $question_no++;
    }
    if ($screen > $old_screen) {
        echo '<tr><td colspan="7" style="height:10px"></td></tr>';
        echo '<tr><td colspan="7"><table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $string['screen'] . ' ' . $screen . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
    }
    if ($q_type == 'info') {
        echo '<tr><td class="q_no"><img src="../../artwork/black_white_info_icon.png" width="6" height="12" alt="Info" />&nbsp;</td><td>';
    } else {
        $status_class = 'status' . $status;
        echo "<tr class=\"{$status_class}\"><td class=\"q_no\">$question_no.</td><td>";
    }
    if ($locked != '') {
        echo '<img src="../../artwork/small_padlock.png" width="18" height="18" alt="Locked" />';
    }
    echo "</td><td style=\"width:25px\"><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" name=\"$q_id\" id=\"$q_id\" value=\"$q_id\" /></td>";
    if ($parts == '') {
        echo '<td onclick="Qpreview(' . $q_id . ')">';
    } else {
        echo '<td style="color:red; text-decoration:line-through" onclick="Qpreview(' . $q_id . ')">';
    }
    $leadin = QuestionUtils::clean_leadin($leadin);
    echo $leadin . '</td><td class="s"><nobr>' . fullQuestionType($q_type, $string) . "</nobr></td><td class=\"s\">$last_edited</td><td>" . $status_array[$status]->get_name() . "</td></tr>\n";
    $old_screen = $screen;
}
  $stmt->close();

?>
</table>
</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$jsdataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$render->render($jsdataset, array(), 'dataset.html');
?>
<script src="../../js/addquestionsinit.min.js"></script>
</body>
</html>
