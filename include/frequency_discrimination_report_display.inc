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
 * Display functions for Frequency and Discrimination Analysis report
 *
 * @author    Simon Wilkinson
 * @author    Richard Aspden
 * @version   1.1
 * @copyright Copyright (c) 2014-2020 The University of Nottingham
 * @package
*/

/**
 * Prints HTML header
 *
 * @param render   $render Render class object
 * @param string[] $string Language strings
 */
function render_head(render $render, array $string)
{
    $configObject = Config::get_instance();
    $texteditorplugin = \plugins\plugins_texteditor::get_editor();

    $headerdata = array(
        'css' => array(
            '/css/header.css',
            '/css/key.css',
            '/css/finish.css',
            '/css/warnings.css',
            '/css/html5.css',
            '/css/fdreport.css',
            '/node_modules/mediaelement/build/mediaelementplayer.min.css',
        ),
        'scripts' => array(
            '/js/freqdiscinit.min.js',
        ),
    );

    $headerdata['mathjax'] = 0; // Changed from false to 0 for HTML display to match previous output
    if ($configObject->get_setting('core', 'paper_mathjax')) {
        $headerdata['mathjax'] = 1;
    }

    $headerdata['three'] = 0; // bool->int here too
    if ($configObject->get_setting('core', 'paper_threejs')) {
        $headerdata['three'] = 1;
        $headerdata['scripts'] = array_merge($headerdata['scripts'], threed_handler::get_js());
        $headerdata['css'] = array_merge($headerdata['css'], threed_handler::get_css());
    } // This doesn't output "0" in rogo-config anymore using the new header text
    $headerdata['mee'] = $configObject->get_setting('core', 'paper_mee');

    $lang = ['title' => $string['frequencydiscrimination']];

    $headerdata['texteditor'] = $texteditorplugin->get_header_file();
    $editor = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
    $headerdata['editor'] = $editor[0];

    $render->render($headerdata, $lang, 'header.html');

    require '../include/toprightmenu.inc';
    echo draw_toprightmenu(154);
}

/**
 * Render post-question summary
 *
 * @param render   $render    Rendering object
 * @param string[] $string    Language strings
 * @param mixed[]  $pstats    P statistics
 * @param mixed[]  $dstats    D statistics
 */
function render_summary(render $render, array $string, array $pstats, array $dstats)
{
    ?>
  </table>
  <br />

  <div class="subsect_table"><div class="subsect_title"><?php echo $string['summary'] ?></div><div class="subsect_hr"><hr noshade="noshade" /></div></div>

  <table cellpadding="0" cellspacing="0" style="width:650px; margin-left:40px">
  <tr><td colspan="2" style="padding-left:4px"><?php echo $string['msg']; ?></td></tr>
  <tr>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0">
  <tr style="font-weight:bold"><td><?php echo $string['difficulty']; ?></td><td style="text-align:center">p</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['veryeasy']; ?></td><td>&gt; 0.8</td><td style="text-align:right"><?php echo $pstats['ve']; ?></td><td></td></tr>
  <tr><td><?php echo $string['easy']; ?></td><td>0.6-0.8</td><td style="text-align:right"><?php echo $pstats['e']; ?></td><td></td></tr>
  <tr><td><?php echo $string['moderate']; ?></td><td>0.4-0.6</td><td style="text-align:right"><?php echo $pstats['m']; ?></td><td></td></tr>
  <tr><td><?php echo $string['hard']; ?></td><td>0.2-0.4</td><td style="text-align:right"><?php echo $pstats['h']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['veryhard']; ?></td><td>&lt; 0.2</td><td style="text-align:right"><?php echo $pstats['vh']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning1']; ?>" class="in-exclusion" /></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php
    if (isset($pstats['no'])) {
        echo number_format($pstats['total'] / $pstats['no'], 2);
    }
    ?></td><td></td><td></td></tr>
  </table>
  </td>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0" >
  <tr style="font-weight:bold"><td><?php echo $string['discrimination']; ?></td><td style="text-align:center">d</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['highest']; ?></td><td>&gt;= 0.35</td><td style="text-align:right"><?php echo $dstats['highest']; ?></td><td></td></tr>
  <tr><td><?php echo $string['high']; ?></td><td>0.25-0.35</td><td style="text-align:right"><?php echo $dstats['high']; ?></td><td></td></tr>
  <tr><td><?php echo $string['intermediate']; ?></td><td>0.15-0.25</td><td style="text-align:right"><?php echo $dstats['intermediate']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['low']; ?></td><td>&lt; 0.15</td><td style="text-align:right"><?php echo $dstats['low']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning2']; ?>" class="in-exclusion" /></td></tr>
  <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php
    if (isset($dstats['no'])) {
        echo number_format($dstats['total'] / $dstats['no'], 2);
    }
    ?></td><td></td><td></td></tr>
  </table>
  </td>
  </tr>
  </table>
    <?php
}

/**
 * Render pre-footer OK/cancel bar
 *
 * @param int      $ex_no  Final exclusion number recorded on paper
 * @param string[] $string Language strings
 * @param bool     $graded Whether paper has been graded yet or not
 */
function render_prefooter(int $ex_no, array $string, bool $graded)
{
    ?>
<input type="hidden" name="question_no" value="<?php echo $ex_no; ?>" />
    <div align="center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" <?php
    if ($graded) {
        echo 'disabled="disabled"';
    } else {
        echo 'class="ok"';
    }
    ?>/><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="history.back()" class="cancel" /></div>
  </form>
</div>
    <?php
}

/**
 * Render footer
 *
 * @param int      $paperID       Paper ID
 * @param string   $startdate     Paper start date
 * @param string   $enddate       Paper end date
 * @param string   $language      System language in use
 * @param string   $cfg_root_path Root install path
 * @param string[] $string        Language strings
 * @param render   $render        Rendering object
 */
function render_footer($paperID, $startdate, $enddate, $language, $cfg_root_path, $string, $render)
{
    $dataset['name'] = 'dataset';
    $dataset['attributes']['id'] = $paperID;
    $dataset['attributes']['startdate'] = $startdate;
    $dataset['attributes']['enddate'] = $enddate;
    $dataset['attributes']['language'] = $language;
    $dataset['attributes']['rootpath'] = $cfg_root_path;
    $render->render($dataset, array(), 'dataset.html');
    // JS utils dataset.
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    $render->render(array('rootpath' => $cfg_root_path), html5_helper::get_instance()->get_lang_strings(), 'html5_footer.html');
    $render->render(array(), array(), 'footer.html');
}

/**
 * Prints not attempted message
 *
 * @param string   $paper_title Paper title
 * @param \mysqli  $mysqli      Database object
 * @param string[] $string      Language strings
 */
function render_notattempted($paper_title, $mysqli, $string)
{
    $notice = UserNotices::get_instance();
    echo '<div id="content"><div class="head_title">';

    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if (isset($_GET['folder']) and $_GET['folder'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';

    echo '<div class="page_title">' . $string['reporttitle'] . "</div>\n";
    echo "</div>\n";
    echo $notice->info_strip($string['PaperNotAttempted'], 100);
}

/**
 * Render display header with legend etc
 *
 * @param string   $paper_title    Paper title
 * @param bool     $graded         Paper graded flag
 * @param int      $user_total     Total users that have taken this paper
 * @param int      $cohort_percent
 * @param int      $user_no
 * @param \mysqli  $mysqli         Database object
 * @param string[] $string         Language strings
 */
function render_display_header($paper_title, $graded, $user_total, $cohort_percent, $user_no, $mysqli, $string)
{
    echo '<div id="content">' . "\n"
    . '<form name="theform" action="' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '" method="post" autocomplete="off">' . "\n"
    . '<div class="head_title">'
    . '<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>' . "\n"
    . '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if (isset($_GET['folder']) and $_GET['folder'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';

    echo '<div class="page_title">' . $string['reporttitle'] . "</div>\n";
    echo "</div>\n";
    if ($graded) {
        echo '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
        echo '<tr><td><div class="yellowwarn"><img src="../artwork/paper_locked_padlock.png" width="32" height="32" alt="Published" /></div></td><td style="vertical-align:middle"><div class="yellowwarn">' . $string['paperpublishedwarning'] . ' <a href="#" class="blacklink" onclick="launchHelp(189); return false;">' . $string['paperlockedclick'] . "</a></div></td></tr>\n";
        echo '</table>';
    }
    echo '<br /><div class="key">'
    . '<table cellpadding="2" cellspacing="0" border="0">'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['totalcandidatenumber'] . '</td><td style="width:375px">' . number_format($user_total) . '</td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['p_warning'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right"><nobr>' . $string['groupsizes'] . '</nobr></td><td>' . $cohort_percent . '% (' . $user_no . ' ' . $string['pergroup'] . ')</td><td rowspan="7" style="vertical-align:top"><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['d_warning'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['boldstems'] . '</td><td>' . $string['correctanswers'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">p =</td><td>' . $string['p_definition'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">d =</td><td>' . $string['d_definition'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">t =</td><td>' . $string['t_definition'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">u =</td><td>' . $string['u_definition'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">l =</td><td>' . $string['l_definition'] . '</td></tr>'
    . '<tr><td style="margin:0px; font-weight:bold; text-align:right">X =</td><td>' . $string['X_definition'] . '</td></tr>'
    . '</table></div><br />'
    . '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
}

/**
 * Render formatted P (difficulty) stats
 *
 * @param float    $value  Unformatted numeric value
 * @param string[] $string Language strings
 *
 * @return string
 */
function format_pStats(float $value, array $string)
{
    if ($value < 0.2) {
        return '<nobr><span style="color:#C00000">p=' . number_format($value, 2) . '</span>&nbsp;<img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning1'] . '" class="in-exclusion" /></nobr>';
    } else {
        return 'p=' . number_format($value, 2);
    }
}

/**
 * Render formatted D (discrimination) stats
 *
 * @param float    $value  Unformatted numeric value
 * @param string[] $string Language strings
 *
 * @return string
 */
function format_dStats(float $value, array $string)
{
    if ($value < 0.15) {
        return '<nobr><span style="color:#C00000">d=' . number_format($value, 2) . '</span>&nbsp;<img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning2'] . '" class="in-exclusion" /></nobr>';
    } else {
        return 'd=' . number_format($value, 2);
    }
}

/**
 * Render formatted X (unanswered) stats
 *
 * @param mixed[] $values Array with percentage and count values
 *
 * @return string
 */
function format_xStats(array $values)
{
    return 'X=' . number_format($values['percent']) . '% (' . $values['count'] . ')';
}

/**
 * Render exclude button
 *
 * @param int    $buttonID    Button ID from data structure
 * @param int    $question_id Question ID
 * @param string $status      Current exclusion status
 * @param int    $parts       Question parts
 * @param float  $marks       Marks available
 *
 * @return string
 */
function excludeButton($buttonID, $question_id, $status, $parts, $marks)
{
    if (mb_strpos($status, '1') !== false) {
        $html = '<input type="hidden" name="status_' . $buttonID . '" id="status_' . $buttonID . '" value="';
        for (
            $i = 0; $i < $marks; $i++
        ) {
            $html .= '1';
        }
        $html .= '" /><input type="hidden" name="id_' . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_on.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" data-id=\"$buttonID\" data-parts=\"$parts\" data-marks=\"$marks\"  width=\"23\" height=\"22\" alt=\"Exclude\" class=\"in-exclusion\" />";
    } else {
        $html = '<input type="hidden" name="status_' . $buttonID . '" id="status_' . $buttonID . '" value="';
        for (
            $i = 0; $i < $marks; $i++
        ) {
            $html .= '0';
        }
        $html .= '" /><input type="hidden" name="id_' . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_off.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" data-id=\"$buttonID\" data-parts=\"$parts\" data-marks=\"$marks\" width=\"23\" height=\"22\" alt=\"Exclude\" class=\"in-exclusion\" />";
    }

    return $html;
}

/**
 * Display compiled question data
 *
 * @param $render        render   Render object
 * @param $question_data mixed[]  Compiled question data from the compile function
 * @param $string        string[] Language strings
 * @param $language      string   Language in use
  */

function display_question(render $render, array $question_data, array $string, string $language)
{
    $configObject = Config::get_instance();
    $question = \questiondata::get_datastore($question_data['type']);
    $mediadirectory = rogo_directory::get_directory('media');

    if (!empty($question_data['theme'])) {
        echo '<tr><td colspan="2"><h1 style="color:' . $question_data['theme_color'] . '">' . $question_data['theme'] . '</h1></td></tr>' . "\n";
    }
    echo "<tr>\n";

    if (!in_array($question_data['type'], array('extmatch', 'matrix', 'textbox'))) {
        if ($question_data['type'] == 'info') {
            echo '<td colspan="2" style="padding-left:15px">' . $question_data['leadin'] . "\n";
        } else {
            echo '<td class="q_no" data-qno="' . $question_data['no'] . '" data-qtype="' . $question_data['type'] . '" data-qmedia="' . ($question_data['media'][0] ?? '') . '" data-qcorrect="' . $question_data['legacy_correct'] . '">' . $question_data['no'] . '.&nbsp;</td><td><div';
            if ((in_array($question_data['type'], array('dichotomous', 'labelling', 'blank', 'hotspot')) and $question_data['score_method'] == 'Mark per Question') or $question_data['type'] == 'flash') {
                echo ' id="q_' . ($question_data['ex_no']) . '_1"';
                if ($question_data['excluded']) {
                    echo ' class="excluded"';
                }
            }
            echo '>';
            if (!empty($question_data['scenario'])) {
                echo $question_data['scenario'] . '<br /><br />' . "\n";
            }
            if (!in_array($question_data['type'], array('hotspot', 'enhancedcalc', 'flash', 'area'))) {
                echo $question_data['leadin'] . '</div>' . "\n";
            }

            if (isset($question_data['media']) and !in_array($question_data['type'], array('hotspot', 'labelling', 'flash', 'area'))) {
                echo '<div class="mediadiv">';
                $question->set_media(...$question_data['media']);
                $render->render($question, $string, 'paper/media.html');
                echo "</div>\n";
            }
            if (!in_array($question_data['type'], array('hotspot', 'labelling', 'enhancedcalc', 'blank', 'flash', 'area'))) {
                echo '<p>' . "\n" . '<table cellpadding="4" cellspacing="0" border="0">' . "\n";
            }
        }

        switch ($question_data['type']) {
            case 'random':
                echo '<div class="q_warning">' . $string['randomwarning'] . '</div>' . "\n";
                break;
            case 'keyword_based':
                echo '<div class="q_warning">' . $string['keywordwarning'] . '</div>' . "\n";
                break;
            case 'area':
                echo '<div id="q_' . ($question_data['ex_no']) . '_1"';
                if ($question_data['excluded']) {
                    echo ' class="excluded"';
                }
                echo '>' . $question_data['leadin'] . "\n";
                if ($question_data['excluded']) {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], '1', 1, 1);
                } else {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], '0', 1, 1);
                }
                echo '</div><p>';
                $question->set_media(...$question_data['media']);
                $render->render($question, $string, 'paper/media.html');
                echo '</p>' . "\n"
                . '<table>' . "\n"
                . '<tr style="font-weight:bold"><td>t=' . number_format($question_data['t']) . '%</td>'
                . '<td>u=' . number_format($question_data['u']) . '%</td>'
                . '<td>l=' . number_format($question_data['l']) . '%</td>'
                . '<td>' . $string['FullMarks'] . '</td></tr>' . "\n"
                . '<tr><td>t=' . number_format($question_data['partial_t']) . '%</td>'
                . '<td>u=' . number_format($question_data['partial_u']) . '%</td>'
                . '<td>l=' . number_format($question_data['partial_l']) . '%</td>'
                . '<td>' . $string['PartialMarks'] . '</td></tr>' . "\n"
                . '<tr><td>t=' . number_format($question_data['incorrect_t']) . '%</td>'
                . '<td>u=' . number_format($question_data['incorrect_u']) . '%</td>'
                . '<td>l=' . number_format($question_data['incorrect_l']) . '%</td>'
                . '<td>' . $string['Incorrect'] . '</td></tr>' . "\n"
                . '</table>' . "\n"
                . '<table>' . "\n"
                . '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
                . '<td colspan="3">' . format_dStats($question_data['d_stats'], $string) . '</td>'
                . '<td colspan="2">' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
                break;
            case 'blank':
                echo '<br />';
                if ($question_data['score_method'] == 'Mark per Question') {
                    if ($question_data['excluded']) {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', (count($question_data['parts']))), 1, (count($question_data['parts'])));
                    } else {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', (count($question_data['parts']))), 1, (count($question_data['parts'])));
                    }
                }

                $formatted_replacements = [];
                foreach ($question_data['parts'] as $i => $part) { // First loop, to format the string
                    if ($question_data['display_method'] == 'dropdown') {
                        // This may be more efficient as an array_map, but doing functional for now
                        $formatted_replacements[] = ' <strong>' . chr($i + 64) . '.</strong> <select><option value="">' . $part['answers'][0] . '</option></select>';
                    } else {
                        $formatted_replacements[] = ' <strong>' . chr($i + 64) . '.</strong> <input type="text" size="20" value="' . $part['answers'][0] . '" />';
                    }
                }

                vprintf($question_data['formatted_string'], $formatted_replacements);

                echo '<table cellspacing="0" cellpadding="4" border="0" style="margin-left:20px">' . "\n";
                foreach ($question_data['parts'] as $i => $part) { // Second loop, to display all options
                    echo '<tr><td>' . chr($i + 64) . '.</td>';
                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded']) {
                            echo '<td>' . excludeButton($part['ex_no'], $question_data['id'], $part['excluded'], 1, 1) . '</td>';
                        } else {
                            echo '<td>' . excludeButton($part['ex_no'], $question_data['id'], 0, 1, 1) . '</td>';
                        }
                    }

                    echo '<td>' . format_pStats($part['p_stats'], $string) . '</td>'
                    . '<td>' . format_dStats($part['d_stats'], $string) . '</td>'
                    . '<td>t=' . number_format($part['t']) . '%</td>'
                    . '<td>u=' . number_format($part['u']) . '%</td>'
                    . '<td>l=' . number_format($part['l']) . '%</td>'
                    . '<td>' . format_xStats($part['x_stats'], $string) . '</td>';

                    if (isset($part['std'])) {
                        echo '<td class="std">' . $part['std'] . '</td>';
                    }

                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded'] and $part['excluded']) {
                            echo '<td id="q_' . $part['ex_no'] . '_1" class="excluded">';
                        } else {
                            echo '<td id="q_' . $part['ex_no'] . '_1">';
                        }
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1">';
                    }

                    if ($question_data['display_method'] == 'dropdown') {
                        echo $part['answers'][0];
                    } else {
                        echo implode(', ', $part['answers']);
                    }
                    echo '</td>';

                    if ($question_data['display_method'] == 'textboxes') {
                        echo '<td><input type="button" class="blankcorrect" data-qid="' . $question_data['id'] . '" data-i="' . $i . '" value="' . $string['Correct'] . '" /></td>';
                    }
                    echo '</tr>';
                }
                echo '</table>' . "\n";
                break;
            case 'dichotomous':
                if ($question_data['score_method'] == 'Mark per Question') {
                    if ($question_data['excluded']) {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', count($question_data['parts'])), 1, count($question_data['parts']));
                    } else {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', count($question_data['parts'])), 1, count($question_data['parts']));
                    }
                }
                foreach ($question_data['parts'] as $i => $part) {
                    echo '<tr><td>';
                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded']) {
                            echo excludeButton($part['ex_no'], $question_data['id'], $part['exclusions'], 1, 1);
                        } else {
                            echo excludeButton($part['ex_no'], $question_data['id'], '', 1, 1);
                        }
                    }
                    echo '</td>'
                    . '<td>' . format_pStats($part['p_stats'], $string) . '</td>'
                    . '<td>' . format_dStats($part['d_stats'], $string) . '</td>'
                    . '<td>t=' . number_format($part['p'] * 100) . '%</td>'
                    . '<td>u=' . number_format($part['p_top'] * 100) . '%</td>'
                    . '<td>l=' . number_format($part['p_bottom'] * 100) . '%</td>'
                    . '<td>' . format_xStats($part['x_stats']) . '</td>'
                    . '<td><span class="std">' . $part['std'] . '</span></td>'
                    . '<td><strong>';
                    if ($part['correct']) {
                        echo 'True';
                    } else {
                        echo 'False';
                    }
                    echo '</strong></td>';
                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded'] == '1' and $part['excluded'] == '1') {
                            echo '<td id="q_' . $part['ex_no'] . '_1" class="excluded">';
                        } else {
                            echo '<td id="q_' . $part['ex_no'] . '_1">';
                        }
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1">';
                    }
                    echo $part['option_text'] . '</td></tr>' . "\n";
                }
                break;
            case 'enhancedcalc':
                echo '<p>' . "\n"
                . '<table cellpadding="4" cellspacing="0" border="0">' . "\n"
                . '<tr><td>';
                if ($question_data['excluded']) {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], 1, 1);
                } else {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], '', 1, 1);
                }
                echo '</td>'
                . '<td style="width:60px"><strong>t=' . $question_data['t'] . '%</strong></td>'
                . '<td><strong>u=' . $question_data['u'] . '%</strong></td>'
                . '<td><strong>l=' . $question_data['l'] . '%</strong></td>'
                . '<td><span class="std">' . $question_data['std'] . '</span></td>';
                if ($question_data['excluded']) {
                    echo '<td id="q_' . $question_data['ex_no'] . '_1" class="excluded">';
                } else {
                    echo '<td id="q_' . $question_data['ex_no'] . '_1">';
                }
                echo $question_data['leadin'] . '</td>'
                . '<td><input class="calccorrect" type="button" data-qid="' . $question_data['id'] . '" value="' . $string['Correct'] . '" /></td>'
                . '</tr>' . "\n"
                . '<tr><td colspan="7">&nbsp;</td></tr>'
                . '<tr><td>'
                . '</td><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
                . '<td>' . format_dStats($question_data['d_stats'], $string) . '</td>'
                . '<td colspan="4">' . format_xStats($question_data['x_stats']) . '</td></tr>';
                break;
            case 'true_false':
                if ($question_data['excluded']) {
                    echo '<tr><td colspan="4">' . excludeButton($question_data['ex_no'], $question_data['id'], '11', 2, 2) . '</td></tr>' . "\n";
                } else {
                    echo '<tr><td colspan="4">' . excludeButton($question_data['ex_no'], $question_data['id'], '00', 2, 2) . '</td></tr>' . "\n";
                }
                echo '<tr><td>t=' . number_format($question_data['p_true'] * 100) . '%</td>'
                . '<td>u=' . number_format($question_data['p_top_true'] * 100) . '%</td>'
                . '<td>l=' . number_format($question_data['p_bottom_true'] * 100) . '%</td>';
                if ($question_data['correct']) {
                    echo '<td><span class="std">' . $question_data['std'] . '</span></td>';
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1">';
                    }
                    echo '<strong>' . $string['True'] . '</strong>';
                } else {
                    echo '<td></td>';
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_1">';
                    }
                    echo $string['True'];
                }
                echo '</td></tr>' . "\n"
                . '<tr><td>t=' . number_format($question_data['p_false'] * 100) . '%</td>'
                . '<td>u=' . number_format($question_data['p_top_false'] * 100) . '%</td>'
                . '<td>l=' . number_format($question_data['p_bottom_false'] * 100) . '%</td>';
                if (!$question_data['correct']) {
                    echo '<td><span class="std">' . $question_data['std'] . '</span></td>';
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_2" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_2">';
                    }
                    echo '<strong>' . $string['False'] . '</strong>';
                } else {
                    echo '<td></td>';
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_2" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_2">';
                    }
                    echo $string['False'];
                }
                echo '</td></tr>' . "\n"
                . '<tr><td colspan="4">&nbsp;</td></tr>' . "\n"
                . '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>' // These had * 100 in, check data
                . '<td>' . format_dStats($question_data['d_stats'], $string) . '</td>'
                . '<td colspan="2">' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
                break;
            case 'labelling':
                if ($question_data['score_method'] == 'Mark per Question') {
                    if ($question_data['excluded']) {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', count($question_data['parts'])), 1, count($question_data['parts']));
                    } else {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', count($question_data['parts'])), 1, count($question_data['parts']));
                    }
                }

                $tmp_height = $question_data['media'][2];
                if ($tmp_height < ($question_data['max_label'] * 55)) {
                    $tmp_height = ($question_data['max_label'] * 55);
                }

                //<!-- ======================== HTML5 part rep disc ================= -->
                echo "<canvas id='canvas" . $question_data['no'] . "' width='" . ($question_data['media'][1] + 220)
                . "' height='" . $tmp_height . "'></canvas>\n"
                . "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n"
                . '<br />' . "\n"
                . '<p>' . "\n"
                . '<table cellpadding="4" cellspacing="0" border="0">' . "\n";

                foreach ($question_data['parts'] as $i => $part) {
                    echo '<tr><td>' . chr($i + 64) . '.</td>';
                    foreach ($part['options'] as $option) {
                        if ($question_data['score_method'] == 'Mark per Option') {
                            if ($question_data['excluded']) {
                                echo '<td>' . excludeButton($option['ex_no'], $question_data['id'], $option['exclusions'], 1, 1) . '</td>';
                            } else {
                                echo '<td>' . excludeButton($option['ex_no'], $question_data['id'], '', 1, 1) . '</td>';
                            }
                            echo '<td>' . format_pStats($option['p_stats'], $string) . '</td>'
                                . '<td>' . format_dStats($option['d_stats'], $string) . '</td>'
                                . '<td>t=' . number_format($option['p'] * 100) . '%</td>'
                                . '<td>u=' . number_format($option['p_top'] * 100) . '%</td>'
                                . '<td>l=' . number_format($option['p_bottom'] * 100) . '%</td>'
                                . '<td>' . format_xStats($option['x_stats']) . '</td>'
                                . '<td><span class="std">' . $option['std'] . '</span></td>';
                            if ($question_data['excluded'] and $option['excluded'] == '1') {
                                echo '<td id="q_' . $option['ex_no'] . '_1" class="excluded">';
                            } else {
                                echo '<td id="q_' . $option['ex_no'] . '_1">';
                            }
                        } else {
                            echo '<td></td>'
                            . '<td>' . format_pStats($option['p_stats'], $string) . '</td>'
                            . '<td>' . format_dStats($option['d_stats'], $string) . '</td>'
                            . '<td>t=' . number_format($option['p'] * 100) . '%</td>'
                            . '<td>u=' . number_format($option['p_top'] * 100) . '%</td>'
                            . '<td>l=' . number_format($option['p_bottom'] * 100) . '%</td>'
                            . '<td>' . format_xStats($option['x_stats']) . '</td>'
                            . '<td><span class="std">' . $option['std'] . '</span></td>'
                            . '<td>';
                        }
                        if (isset($option['media'])) {
                            echo '<img src="' . $mediadirectory->url($option['media'][0]) . '" width="' . $option['media'][1] . '" height="' . $option['media'][2] . '" alt="" border="1" />';
                        } else {
                            echo '<strong>' . $option['option_text'] . '</strong>';
                        }
                        echo '</td></tr>' . "\n";
                    }
                }
                break;
            case 'flash':
                if ($question_data['excluded']) {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], 1, 1, 1);
                } else {
                    echo excludeButton($question_data['ex_no'], $question_data['id'], 0, 1, 1);
                }
                echo $question_data['leadin'];
                $flashrender = new render(
                    $configObject,
                    dirname(__DIR__)
                    . DIRECTORY_SEPARATOR . 'plugins'
                    . DIRECTORY_SEPARATOR . 'questions'
                    . DIRECTORY_SEPARATOR . $question_data['type']
                    . DIRECTORY_SEPARATOR . 'templates'
                );
                $flashdata['questionno'] = $question_data['no'];
                $flashdata['displayscenario'] = false;
                if ($question_data['scenario'] != '') {
                    $flashdata['displayscenario'] = true;
                }
                $flashdata['scenario'] = $question_data['scenario'];
                $flashrender->render($flashdata, $string, 'flash.html');
                break;
            case 'hotspot':
                if ($question_data['score_method'] == 'Mark per Question') {
                    if ($question_data['excluded']) {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', count($question_data['parts'])), 1, count($question_data['parts']));
                    } else {
                        echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', count($question_data['parts'])), 1, count($question_data['parts']));
                    }
                }
                echo '<div id="coords' . $question_data['no'] . '" data-value="' . $question_data['coords'] . '"></div><div
                    id="question' . $question_data['no'] . '"
                    class="html5 hotspot analysis"
                    data-number="' . $question_data['no'] . '"
                    data-type="hotspot"
                    data-mode="analysis"
                    data-image="' . $mediadirectory->url($question_data['media'][0]) . '"
                    data-image-width="' . $question_data['media'][1] . '"
                    data-image-height="' . $question_data['media'][2] . '"
                    data-setup="' . $question_data['tmp_correct'] . '"
                    data-answers="' . $question_data['coords'] . '"
                    ></div><p><table cellpadding="4" cellspacing="0" border="0">' . "\n";
                foreach ($question_data['parts'] as $i => $part) {
                    echo '<tr><td>' . chr($i + 64) . '.</td><td>';
                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded']) {
                            echo excludeButton($part['ex_no'], $question_data['id'], $part['exclusions'], 1, 1);
                        } else {
                            echo excludeButton($part['ex_no'], $question_data['id'], '', 1, 1);
                        }
                    }
                    echo '</td><td>' . format_pStats($part['p_stats'], $string) . '</td>'
                    . '<td>' . format_dStats($part['d_stats'], $string) . '</td>'
                    . '<td>t=' . number_format($part['p'] * 100, 0) . '%</td>'
                    . '<td>u=' . number_format($part['p_top'] * 100, 0) . '%</td>'
                    . '<td>l=' . number_format($part['p_bottom'] * 100, 0) . '%</td>'
                    . '<td>' . format_xStats($part['x_stats']) . '</td>'
                    . '<td><span class="std">' . $part['std'] . '</span></td>';

                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($question_data['excluded'] and $part['exclusions'] == '1') {
                            echo '<td id="q_' . $part['ex_no'] . '_1" class="excluded">';
                        } else {
                            echo '<td id="q_' . $part['ex_no'] . '_1">';
                        }
                    } else {
                        echo '<td>';
                    }
                    echo '<strong>' . $part['option_text'] . '</strong></td></tr>' . "\n";
                }
                break;
            case 'likert':
                echo '<tr>' . "\n";
                for ($i = 0; $i < count($question_data['scale']) - 1; $i++) {
                    echo '<td>' . $question_data['scale'][$i] . '</td>';
                }
                echo '<td>(' . $string['unanswered'] . ')</td></tr>' . "\n"
                . "<tr>\n";
                foreach ($question_data['parts'] as $part) {
                    echo '<td style="text-align:center">t=' . number_format($part['t']) . '%</td>';
                }
                echo '<td>' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
                break;
            case 'mcq':
                if ($question_data['excluded']) {
                    echo '<tr><td colspan="3">' . excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], $question_data['option_count'], 1) . "</td></tr>\n";
                } else {
                    echo '<tr><td colspan="3">' . excludeButton($question_data['ex_no'], $question_data['id'], '', $question_data['option_count'], 1) . "</td></tr>\n";
                }
                foreach ($question_data['parts'] as $i => $part) {
                    if ($part['correct'] == true) {
                        echo '<tr style="font-weight:bold">'
                        . '<td>t=' . number_format($part['t']) . '%</td>'
                        . '<td>u=' . number_format($part['u']) . '%</td>'
                        . '<td>l=' . number_format($part['l']) . '%</td>'
                        . '<td><span class="std">' . $question_data['std'] . '</span></td>';
                    } else {
                        echo '<tr><td class="grey">t=' . number_format($part['t']) . '%</td>'
                        . '<td class="grey">u=' . number_format($part['u']) . '%</td>'
                        . '<td class="grey">l=' . number_format($part['l']) . '%</td>'
                        . '<td></td>';
                    }
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '">';
                    }
                    if (isset($part['option_text'])) {
                        echo $part['option_text'] . "\n";
                    }
                    if (isset($part['media'])) {
                        echo '<br />';
                        $question->set_media(...$part['media']);
                        $render->render($question, $string, 'paper/media.html');
                    }
                    echo "</td></tr>\n";
                }
                echo '<tr><td class="grey">t=' . number_format($question_data['t']) . '%</td>'
                . '<td class="grey">u=' . number_format($question_data['u']) . '%</td>'
                . '<td class="grey">l=' . number_format($question_data['l']) . '%</td>'
                . '<td></td>'
                . '<td style="color:#C00000">&lt;abstain&gt;</td></tr>' . "\n"
                . '<tr><td colspan="3">&nbsp;</td></tr>' . "\n"
                . '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
                . '<td>' . format_dStats($question_data['d_stats'], $string) . '</td>'
                . '<td>' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
                break;
            case 'mrq':
                if ($question_data['excluded']) {
                    echo '<tr><td colspan="3">' . excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], count($question_data['parts']), count($question_data['parts'])) . "</td></tr>\n";
                } else {
                    echo '<tr><td colspan="3">' . excludeButton($question_data['ex_no'], $question_data['id'], '', count($question_data['parts']), count($question_data['parts'])) . "</td></tr>\n";
                }
                foreach ($question_data['parts'] as $i => $part) {
                    if ($part['correct'] == true) {
                        echo '<tr style="font-weight:bold"><td>t=' . number_format($part['t']) . '%</td>'
                        . '<td>u=' . number_format($part['u']) . '%</td>'
                        . '<td>l=' . number_format($part['l']) . '%</td>'
                        . '<td><span class="std">' . $part['std'] . '</span></td>';
                    } else {
                        echo '<tr><td class="grey">t=' . number_format($part['t']) . '%</td>'
                        . '<td class="grey">u=' . number_format($part['u']) . '%</td>'
                        . '<td class="grey">l=' . number_format($part['l']) . '%</td>'
                        . '<td></td>';
                    }
                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '">';
                    }
                    if (isset($part['option_text'])) {
                        echo $part['option_text'] . "\n";
                    }
                    if (isset($part['media'])) {
                        echo '<br />';
                        $question->set_media(...$part['media']);
                        $render->render($question, $string, 'paper/media.html');
                    }
                    echo '</td></tr>' . "\n";
                }
                // Abstain (temp label)
                echo '<tr><td class="grey">t=' . number_format($question_data['t']) . '%</td>'
                . '<td class="grey">u=' . number_format($question_data['u']) . '%</td>'
                . '<td class="grey">l=' . number_format($question_data['l']) . '%</td>'
                . '<td></td>'
                . '<td id="q_' . $question_data['ex_no'] . '_abstain"><span style="color:#C00000">&lt;' . $string['abstain'] . '&gt;</span></td></tr>' . "\n"
                . '<tr><td colspan="3">&nbsp;</td></tr>' . "\n"
                . '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
                . '<td>' . format_dStats($question_data['d_stats'], $string) . '</td>'
                . '<td>' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
                break;
            case 'rank':
                if ($question_data['excluded']) {
                    echo '<tr><td colspan="4">' . excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], count($question_data['parts']), count($question_data['parts']) + 1) . "</td></tr>\n";
                } else {
                    echo '<tr><td colspan="4">' . excludeButton($question_data['ex_no'], $question_data['id'], '', count($question_data['parts']), count($question_data['parts']) + 1) . "</td></tr>\n";
                }
                foreach ($question_data['parts'] as $i => $part) {
                    if ($question_data['excluded']) {
                        echo '<tr><td id="q_' . $question_data['ex_no'] . '_' . ($i + 1) . '" colspan="6" class="excluded">' . $part['option_text'] . '</td></tr>' . "\n";
                    } else {
                        echo '<tr><td id="q_' . $question_data['ex_no'] . '_' . ($i + 1) . '" colspan="6">' . $part['option_text'] . '</td></tr>' . "\n";
                    }
                    foreach ($part['options'] as $rank_position => $rank_part) {
                        if ($rank_part['correct']) {
                            echo '<tr><td><strong>u=' . number_format($rank_part['u']) . '%</strong></td>'
                            . '<td><strong>l=' . number_format($rank_part['l']) . '%</strong></td>'
                            . '<td><span class="std">' . $rank_part['std'] . '</span></td>'
                            . '<td style="font-weight:bold">' . $rank_position;
                        } else {
                            echo '<tr><td class="grey">u=' . number_format($rank_part['u']) . '%</td>'
                            . '<td class="grey">l=' . number_format($rank_part['l']) . '%</td>'
                            . '<td></td>'
                            . '<td>' . $rank_position;
                        }
                        switch ($rank_position) {
                            case 1:
                                echo $string['rank_1'];
                                break;
                            case 2:
                                echo $string['rank_2'];
                                break;
                            case 3:
                                echo $string['rank_3'];
                                break;
                            default:
                                echo $string['rank_other'];
                                break;
                        }
                        if ($rank_part['correct']) {
                            echo '</td><td>&nbsp;</td></tr>' . "\n";
                        } else {
                            echo '</td><td style="width:50%">&nbsp;</td></tr>' . "\n";
                        }
                    }
                    echo '<tr><td colspan="4">' . format_xStats($part['x_stats']) . '</td></tr>' . "\n"
                    . '<tr><td colspan="4">&nbsp;</td></tr>' . "\n";
                }
                echo '<tr><td><strong>u=' . number_format($question_data['u']) . '%</strong></td>'
                . '<td><strong>l=' . number_format($question_data['l']) . '%</strong></td>'
                . '<td><span class="std">' . $question_data['std'] . '</span></td>'
                . '<td style="font-weight:bold">' . $string['AllItemsCorrect'] . '</td></tr>' . "\n"
                . '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
                . '<td colspan="3">' . format_dStats($question_data['d_stats'], $string) . '</td></tr>' . "\n";
                break;
            case 'sct':
                echo '<tr><td colspan="3">' . excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], count($question_data['parts']), 1) . '</td></tr>' . "\n";
                foreach ($question_data['parts'] as $i => $part) {
                    if ($part['correct']) {
                        echo '<tr class="grey correct">';
                    } else {
                        echo '<tr class="grey">';
                    }
                    echo '<td>t=' . number_format($part['t']) . '%</td>'
                    . '<td>u=' . number_format($part['u']) . '%</td>'
                    . '<td>l=' . number_format($part['l']) . '%</td>'
                    . '<td></td>';

                    if ($question_data['excluded']) {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '" class="excluded">';
                    } else {
                        echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '">';
                    }
                    echo $part['option_text'] . '</td></tr>' . "\n";
                }
                echo '<tr><td colspan="3">&nbsp;</td></tr>' . "\n";
                echo '<tr><td>' . format_pStats($question_data['p'], $string) . '</td>'
                . '<td>' . format_xStats($question_data['x_stats']) . '</td></tr>';
                break;
        }
        if (!in_array($question_data['type'], array('info', 'blank', 'flash'))) {
            echo '</table></p>' . "\n";
        }
    } elseif ($question_data['type'] == 'textbox') {
        echo '<td class="q_no">' . $question_data['no'] . '.&nbsp;</td><td>';
        if ($question_data['excluded']) {
            echo '<div class="excluded" id="q_' . $question_data['ex_no'] . '_1">'
            . excludeButton($question_data['ex_no'], $question_data['id'], $question_data['exclusions'], 1, 1);
        } else {
            echo '<div id="q_' . $question_data['ex_no'] . '_1">'
            . excludeButton($question_data['ex_no'], $question_data['id'], '', 1, 1);
        }
        echo '&nbsp;' . $question_data['leadin'] . '</div>'
        . '<table cellpadding="4" cellspacing="0" border="0">';

        if ($configObject->get_setting('core', 'rpt_fd_show_wordlist')) {
            echo '<tr><td colspan="2"><strong>' . $string['TopGroup'] . ':</strong></td>'
            . '<td colspan="2"><strong>' . $string['BottomGroup'] . ':</strong></td></tr>' . "\n"
            . '<tr><td colspan="2">(' . $string['meanWordCount'] . ' = ' . round($question_data['mean_word_count']['top']) . ')</td>'
            . '<td colspan="2">(' . $string['meanWordCount'] . ' = ' . round($question_data['mean_word_count']['bottom']) . ')</td></tr>';

            for ($i = 0; $i < 40; $i++) {
                if (isset($question_data['words']['top'][$i]['used']) or isset($question_data['words']['bottom'][$i]['used'])) {
                    echo '<tr>';
                    if (isset($question_data['words']['top'][$i])) {
                        echo '<td>' . $question_data['words']['top'][$i]['used'] . '</td>'
                        . '<td>' . $question_data['words']['top'][$i]['word'] . '</td>';
                    } else {
                        echo '<td></td><td></td>';
                    }
                    if (isset($question_data['words']['bottom'][$i])) {
                        echo '<td>' . $question_data['words']['bottom'][$i]['used'] . '</td>'
                        . '<td>' . $question_data['words']['bottom'][$i]['word'] . '</td>';
                    } else {
                        echo '<td></td><td></td>';
                    }
                    echo '</tr>';
                }
            }
        }
        echo '<tr><td colspan="4">&nbsp;</td></tr>' . "\n";
        foreach ($question_data['parts'] as $i => $part) {
            if ($part['t'] == 0) {
                echo '<tr><td>t=0%</td>';
            } else {
                echo '<tr><td>t=' . number_format($part['t']) . '%</td>';
            }
            if ($part['u'] == 0) {
                echo '<td>u=0%</td>';
            } else {
                echo '<td>u=' . number_format($part['u']) . '%</td>';
            }
            if ($part['l'] == 0) {
                echo '<td>l=0%</td>';
            } else {
                echo '<td>l=' . number_format($part['l']) . '%</td>';
            }
            echo '<td></td>';
            if ($question_data['excluded']) {
                echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '" class="excluded">';
            } else {
                echo '<td id="q_' . $question_data['ex_no'] . '_' . $i . '">';
            }
            if ($part['mark'] == 'unmarked') {
                echo $string['unmarked'] . "\n" . '</td></tr>' . "\n"; // For localisation
            } else {
                $mark = preg_replace('/^([0-9]+)\.0/', '\1', $part['mark']); // Strip hanging .0s
                echo $mark . ' ' . (($mark == '1') ? $string['mark'] : $string['marks']) . "\n" . '</td></tr>' . "\n";
            }
        }
        if ($question_data['unmarked']) {
            echo '<tr><td>p=<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="Warning" /></td>'
            . '<td>d=<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="Warning" /></td><td colspan="2"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="Warning" />&nbsp;' . sprintf($string['unmarkedscripts'], $question_data['unmarked_count']) . '</td></tr>' . "\n";
        } else {
            echo '<tr><td>' . format_pStats($question_data['p_stats'], $string) . '</td>'
              . '<td>' . format_dStats($question_data['d_stats'], $string)  . '</td>'
              . '<td colspan="2">' . format_xStats($question_data['x_stats']) . '</td></tr>' . "\n";
        }
        echo '</table></td></tr>' . "\n";
    } elseif ($question_data['type'] == 'matrix') {
        echo '<tr><td class="q_no">' . $question_data['no'] . '.&nbsp;</td><td>';

        if ($question_data['score_method'] == 'Mark per Question') {
            if ($question_data['excluded']) {
                echo '<div id="q_' . $question_data['ex_no'] . '_1" class="excluded">' . $question_data['leadin'] . '</div>';
            } else {
                echo '<div id="q_' . $question_data['ex_no'] . '_1">' . $question_data['leadin'] . '</div>';
            }
            if ($question_data['excluded']) {
                echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', count($question_data['parts'])), 1, count($question_data['parts']));
            } else {
                echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', count($question_data['parts'])), 1, count($question_data['parts']));
            }
        } else {
            echo '<div>' . $question_data['leadin'] . '</div>';
        }

        echo '<p>' . "\n"
        . '<table cellpadding="2" cellspacing="0" border="1" class="matrix">' . "\n"
        . '<tr><td colspan="' . $question_data['cols'] . '">&nbsp;</td><td>&nbsp;</td>';
        for ($i = 1; $i <= count($question_data['options']); $i++) {
            echo '<td>' . $question_data['options'][$i] . '</td>';
        }
        echo '</tr>' . "\n";

        foreach ($question_data['parts'] as $i => $part) {
            echo '<tr>' . "\n";
            foreach ($question_data['options'] as $option_no => $option) {
                if ($option_no == 1) {
                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($part['excluded']) {
                            echo '<td>' .  excludeButton($part['ex_no'], $question_data['id'], $part['exclusions'], 1, 1) . '</td>';
                        } else {
                            echo '<td>' .  excludeButton($part['ex_no'], $question_data['id'], '', 1, 1) . '</td>';
                        }
                    }

                    echo '<td>' . chr($i + 64) . '.</td>'
                    . '<td style="font-weight:bold">' . format_pStats($part['p_stats'], $string) . '</td>'
                    . '<td style="font-weight:bold">' . format_dStats($part['d_stats'], $string) . '</td>'
                    . '<td style="font-weight:bold">t=' . number_format($part['p'] * 100, 0) . '%</td>'
                    . '<td style="font-weight:bold">u=' . number_format($part['p_top'] * 100, 0) . '%</td>'
                    . '<td style="font-weight:bold">l=' . number_format($part['p_bottom'] * 100, 0) . '%</td>'
                    . '<td style="font-weight:bold">' . format_xStats($part['x_stats']) . '</td>';

                    if (isset($part['std'])) {
                        echo '<td class="std"><strong>' . $part['std'] . '</strong></td>';
                    }

                    if ($question_data['score_method'] == 'Mark per Option') {
                        if ($part['excluded']) {
                            echo '<td class="excluded" id="q_' . $part['ex_no'] . '_1">';
                        } else {
                            echo '<td id="q_' . $part['ex_no'] . '_1">';
                        }
                    } else {
                        echo '<td>';
                    }
                    echo $part['scenario'] . '</td>';
                }

                if ($part['correct'] == $option_no) {
                    echo '<td style="text-align:center; background-color:#C0FFC0"><input type="radio" name="q' . $question_data['id'] . '_' . $i . '" checked /></td>';
                } else {
                    echo '<td style="text-align:center"><input type="radio" name="q' . $question_data['id'] . '_' . $i . '" /></td>';
                }
            }
            echo '</tr>' . "\n";
        }
        echo '</table>' . "\n" . '</td></tr>' . "\n";
    } elseif ($question_data['type'] == 'extmatch') {
        echo '<tr><td class="q_no">' . $question_data['no'] . '.&nbsp;</td><td>';
        if ($question_data['score_method'] == 'Mark per Question') {
            if ($question_data['excluded']) {
                echo '<div id="q_' . $question_data['ex_no'] . '_1" class="excluded">';
            } else {
                echo '<div id="q_' . $question_data['ex_no'] . '_1">';
            }
        } else {
            echo '<div>';
        }
        echo $question_data['leadin'] . '</div>' . "\n";
        if ($question_data['score_method'] == 'Mark per Question') {
            if ($question_data['excluded']) {
                echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('1', count($question_data['parts'])), 1, count($question_data['parts']));
            } else {
                echo excludeButton($question_data['ex_no'], $question_data['id'], str_repeat('0', count($question_data['parts'])), 1, count($question_data['parts']));
            }
        }
        echo '<ol class="extmatch">';
        if (isset($question_data['media'])) {
            echo '<div class="mediadiv">';
            $question->set_media(...$question_data['media']);
            $render->render($question, $string, 'paper/media.html');
            echo "</div>\n";
        }
        foreach ($question_data['parts'] as $i => $part) {
            echo '<li>' . "\n";
            if (isset($part['media'])) {
                echo '<p>';
                $question->set_media(...$part['media']);
                $render->render($question, $string, 'paper/media.html');
                echo "</p>\n";
            }
            if (isset($part['scenario'])) {
                echo '<div>' . $part['scenario'] . "</div>\n";
            }
            if ($question_data['score_method'] == 'Mark per Option') {
                if ($question_data['excluded']) {
                    echo '<div>' . excludeButton($part['ex_no'], $question_data['id'], $part['exclusions'], count($part['options']), $question_data['correct_stems']) . '</div>';
                } else {
                    echo '<div>' . excludeButton($part['ex_no'], $question_data['id'], '', count($part['options']), $question_data['correct_stems']) . '</div>';
                }
            }
            echo '<div><table cellpadding="4" cellspacing="0" border="0">' . "\n";
            foreach ($part['options'] as $option_no => $option) {
                $classes = [];
                if ($option['specific_answers']) {
                    echo '<tr style="font-weight:bold">'
                    . '<td>t=' . number_format($option['t']) . '%</td>'
                    . '<td>u=' . number_format($option['u']) . '%</td>'
                    . '<td>l=' . number_format($option['l']) . '%</td>'
                    . '<td><span class="std">' . $question_data['std'] . '</span></td>';
                    $classes[] = 'correct';
                } else {
                    echo '<tr>'
                    . '<td class="grey">t=' . number_format($option['t']) . '%</td>'
                    . '<td class="grey">u=' . number_format($option['u']) . '%</td>'
                    . '<td class="grey">l=' . number_format($option['l']) . '%</td>'
                    . '<td></td>';
                }
                if ($question_data['score_method'] == 'Mark per Option') {
                    if ($question_data['excluded'] and $option['excluded'] == '1') { // Moved this into the Mark per Option check, seemed to make sense, no bugs appear to have been introduced
                        $classes[] = 'excluded';
                    }
                    if (!empty($classes)) {
                        echo '<td class="' . implode(',', $classes) . '" id="q_' . $option['ex_no'] . '_' . $option_no . '">';
                    } else {
                        echo '<td id="q_' . $option['ex_no'] . '_' . $option_no . '">';
                    }
                } elseif (!empty($classes)) {
                    echo '<td class="' . implode(',', $classes) . '">';
                } else {
                    echo '<td>'; // this is currently overcomplicated and can be cleaned up later, but needed the output to match the old version for comparison
                }
                echo chr($option_no + 64) . '. ' . $option['option_text'] . '</td></tr>' . "\n";
            }
            echo '<tr><td colspan="4">&nbsp;</td></tr>'
            . '<tr><td>' . format_pStats($part['p_stats'], $string) . '</td>'
            . '<td>' . format_dStats($part['d_stats'], $string) . '</td>'
            . '<td colspan="2">' . format_xStats($part['x_stats']) . '</td>'
            . '</tr>';
            if ($i < count($question_data['parts'])) {
                echo '<tr><td colspan="4">&nbsp;</td></tr>';
            }
            echo "</table></div></li>\n";
        }
        echo "</ol>\n";
    }
    echo "</td></tr>\n"
    . '<tr><td colspan="2">&nbsp;</td></tr>' . "\n";
}
