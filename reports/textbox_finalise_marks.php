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
require_once '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $propertyObj->get_paper_type();

function load_marks($paperID, $q_id, $phase, $db)
{
    $marks = array();

    $result = $db->prepare('SELECT answer_id, mark, comments FROM textbox_marking WHERE paperID = ? AND q_id = ? AND phase = ?');
    $result->bind_param('iii', $paperID, $q_id, $phase);
    $result->execute();
    $result->bind_result($answer_id, $mark, $comment);
    while ($result->fetch()) {
        $marks[$answer_id] = array('mark' => $mark, 'comment' => $comment);
    }
    $result->close();

    return $marks;
}

function load_question_mark($q_id, $db)
{
    $result = $db->prepare('SELECT marks_correct FROM options WHERE o_id = ? LIMIT 1');
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($marks_correct);
    $result->fetch();
    $result->close();

    return $marks_correct;
}

function displayMarks($id, $marks, $override, $user_mark)
{
    $html = '<select name="override' . $id . '" id="override' . $id . '"><option value="NULL"></option>';
    $inc = 0.5;
    for ($i = 0.0; $i <= $marks; $i += $inc) { // ensure $i is a double for === below
        $display_i = $i;
        if ($i == 0.5) {
            $display_i = '&#189;';
        } elseif ($i - floor($i) > 0) {
            $display_i = floor($i) . '&#189;';
        }
        if ($override and $i === $user_mark) {
            $html .= "<option value=\"$i\" selected>$display_i</option>";
        } else {
            $html .= "<option value=\"$i\">$display_i</option>";
        }
    }
    $html .= '</select>';

    return $html;
}

if (isset($_POST['submit'])) {
    for ($i = 1; $i <= $_POST['student_no']; $i++) {
        if (isset($_POST["override$i"]) and $_POST["override$i"] != 'NULL') {
            $tmp_mark = $_POST["override$i"];
        } elseif (isset($_POST["mark$i"])) {
            $tmp_mark = $_POST["mark$i"];
        } else {
            $tmp_mark = null;
        }
        $logtype = $_POST["logtype$i"];
        $log_id = $_POST["log_id$i"];
        $result = $mysqli->prepare("UPDATE log$logtype SET mark = ?, adjmark = ? WHERE id = ?");
        $result->bind_param('ddi', $tmp_mark, $tmp_mark, $log_id);
        $result->execute();
        $result->close();
    }
    header("location: ../reports/textbox_select_q.php?action=finalise&paperID=$paperID&startdate=" . $_POST['startdate'] . '&enddate=' . $_POST['enddate'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '&repcourse=' . $_GET['repcourse']);
    exit();
} else {
    $q_id       = check_var('q_id', 'GET', true, false, true);
    $startdate  = check_var('startdate', 'GET', true, false, true);
    $enddate    = check_var('enddate', 'GET', true, false, true);

    // Check the question exists.
    if (!QuestionUtils::question_exists($q_id, $mysqli)) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }

    $primary_marks = $marks = load_marks($paperID, $q_id, 1, $mysqli);
    $secondary_marks = load_marks($paperID, $q_id, 2, $mysqli);

    $marks_correct = load_question_mark($q_id, $mysqli);

    $render = new render($configObject);
    $lang['title'] =  $string['finalisemarks'];
    $headerdata = array(
        'css' => array(
            '/css/header.css',
            '/css/textbox_finalise_marks.css',
        ),
    );
    $headerdata['mathjax'] = false;
    if ($configObject->get_setting('core', 'paper_mathjax')) {
        $headerdata['mathjax'] = true;
    }
    $render->render($headerdata, $lang, 'header.html');


    $breadcrumb[$string['home']] = '../index.php';
    if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
        $link = '../folder/index.php?folder=' . $_GET['folder'];
        $name = folder_utils::get_folder_name($_GET['folder'], $mysqli);
        $breadcrumb[$name] = $link;
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        $link = '../module/index.php?module=' . $_GET['module'];
        $name = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
        $breadcrumb[$name] = $link;
    }
    $link = '../paper/details.php?paperID=' . $paperID;
    $name = $propertyObj->get_paper_title();
    $breadcrumb[$name] = $link;
    require '../include/toprightmenu.inc';

    $toprightmenu = draw_toprightmenu();
    $render->render_admin_options('', '', $lang, $toprightmenu, 'admin/no_sidebar.html');
    $render->render_admin_content($breadcrumb, $lang);

    echo '<form action="' . $_SERVER['PHP_SELF'] . '?paperID=' . $paperID . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '&repcourse=' . $_GET['repcourse'] . "\" method=\"post\" autocomplete=\"off\">\n";

    echo '<table class="header"><tr><th colspan="2"><div class="page_title"><span style="font-weight:normal"> ' . $string['question'] . ' ' . $_GET['qNo'] . '</span></div></th><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">' . $string['first'] . '</div></th><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">' . $string['second'] . '</div></td><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">' . $string['override'] . '</div></th></tr>';
    echo '<tr><td colspan="4"><img src="../artwork/tooltip_icon.gif" />' . $string['comments'] . '</td></tr>';
    $student_no = 0;

    $time_int = \log::getStartInterval($paper_type);

    // Get student answers
    if ($paper_type == \assessment::TYPE_FORMATIVE) {
        $progress_time_int = \log::getStartInterval(\assessment::TYPE_PROGRESS);
        $sql = <<< SQL
SELECT 0 AS logtype, l.id, lm.userID, l.user_answer, l.mark
  FROM log0 l, log_metadata lm, users u, user_roles ur JOIN roles r ON ur.roleid = r.id
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND u.id = ur.userid
  AND r.name IN ('Student', 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
  AND lm.started <= ?
UNION ALL
SELECT 1 AS logtype, l.id, lm.userID, l.user_answer, l.mark
  FROM log1 l, log_metadata lm, users u, user_roles ur JOIN roles r ON ur.roleid = r.id
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND u.id = ur.userid
  AND r.name IN ('Student', 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL $progress_time_int MINUTE) >= ?
  AND lm.started <= ?
SQL;

        $result = $mysqli->prepare($sql);
        $result->bind_param('iissiiss', $paperID, $q_id, $startdate, $enddate, $paperID, $q_id, $startdate, $enddate);
    } else {
        $sql = <<< SQL
SELECT $paper_type AS logtype, l.id, lm.userID, l.user_answer, l.mark
FROM log{$paper_type} l, log_metadata lm, users u, user_roles ur JOIN roles r ON ur.roleid = r.id
WHERE lm.paperID = ?
AND l.metadataID = lm.id
AND u.id = ur.userid
AND r.name IN ('Student', 'graduate')
AND u.id = lm.userID
AND l.q_id = ?
AND DATE_ADD(lm.started, INTERVAL $time_int MINUTE) >= ?
AND lm.started <= ?;
SQL;

        $result = $mysqli->prepare($sql);
        $result->bind_param('iiss', $paperID, $q_id, $startdate, $enddate);
    }
    $result->execute();
    $result->bind_result($logtype, $log_id, $tmp_userID, $user_answer, $user_mark);
    while ($result->fetch()) {
        $student_no++;
        if (isset($primary_marks[$log_id]['mark']) and $primary_marks[$log_id]['mark'] === $user_mark) {
            $primary_checked = ' checked';
            $secondary_checked = '';
            $override = false;
        } elseif (isset($secondary_marks[$log_id]['mark']) and $secondary_marks[$log_id]['mark'] === $user_mark) {
            $primary_checked = '';
            $secondary_checked = ' checked';
            $override = false;
        } else {
            $primary_checked = '';
            $secondary_checked = '';
            $override = true;
        }
        if (trim($user_answer) != '') {
            echo "<tr class=\"l\"><td class=\"studentno\">$student_no</td><td class=\"ans\">" . nl2br($user_answer) . '<br />&nbsp;</td>';

            if (isset($secondary_marks[$log_id]['mark']) and isset($primary_marks[$log_id]['mark']) and abs($primary_marks[$log_id]['mark'] - $secondary_marks[$log_id]['mark']) > 1) {
                echo '<td class="primary noans">' . $primary_marks[$log_id]['mark'] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id]['mark'] . "\" $primary_checked /></td><td class=\"secondary noans\">" . $secondary_marks[$log_id]['mark'] . "<input type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id]['mark'] . "\" $secondary_checked /><input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td><td class=\"override noans\">" . displayMarks($student_no, $marks_correct, $override, $user_mark);
            } else {
                if (isset($primary_marks[$log_id]['mark'])) {
                    echo '<td class="primary">' . $primary_marks[$log_id]['mark'] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id]['mark'] . "\" $primary_checked />";
                    if (isset($secondary_marks[$log_id]['mark'])) {
                        echo '<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . htmlspecialchars($primary_marks[$log_id]['comment']) . '" />';
                    }
                    echo '</td>';
                } else {
                    echo '<td class="unmarked">' . $string['unmarked'] . '</td>';
                }
                if (isset($secondary_marks[$log_id]['mark'])) {
                    echo '<td class="secondary">' . $secondary_marks[$log_id]['mark'] . "<input class=\"secondarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id]['mark'] . "\" $secondary_checked />";
                    if (isset($primary_marks[$log_id]['mark'])) {
                        echo '<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . htmlspecialchars($secondary_marks[$log_id]['comment']) . '" />';
                    }
                    echo '</td>';
                } else {
                    echo '<td class="secondary missing">&nbsp;</td>';
                }
                echo '<td class="override">' . displayMarks($student_no, $marks_correct, $override, $user_mark);
            }
        } else {
            // User answer is blank.
            echo "<tr class=\"l\"><td class=\"studentno\">$student_no</td><td class=\"ans\" style=\"color: #C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"!\" />&nbsp;" . $string['noanswer'] . '<br />&nbsp;</td>';
            if (isset($primary_marks[$log_id]['mark'])) {
                echo '<td class="primary noans">' . $primary_marks[$log_id]['mark'] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id]['mark'] . "\" $primary_checked/></td>";
            } else {
                echo '<td class="unmarked">' . $string['unmarked'] . '</td>';
            }
            if (isset($secondary_marks[$log_id]['mark'])) {
                echo '<td class="secondary noans"">' . $secondary_marks[$log_id]['mark'] . "<input class=\"secondarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id]['mark'] . "\" $secondary_checked/>
            <input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td>";
            } else {
                echo '<td class="secondary noans missing">&nbsp;</td>';
            }
            echo '<td class="override noans">' . displayMarks($student_no, $marks_correct, $override, $user_mark);
        }
        echo "<input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /><input type=\"hidden\" name=\"logtype$student_no\" value=\"$logtype\" /></td></tr>\n";
    }
    $result->close();
    ?>
<tr><td></td><td align="right"><label for="selectallprimary"><?php echo $string['selectallprimary'] ?></label>&nbsp;<input type="checkbox" id="selectallprimary" name="selectallprimary" value="" /> </td><td></td><td></td></tr>
</table>
<br />
<div style="text-align:center">
<input type="hidden" name="student_no" value="<?php echo $student_no ?>" />
<input type="hidden" name="paperID" value="<?php echo $paperID ?>" />
<input type="hidden" name="startdate" value="<?php echo $startdate ?>" />
<input type="hidden" name="enddate" value="<?php echo $enddate ?>" />

<input type="submit" name="submit" class="ok" value="<?php echo $string['finalisemarks'] ?>" />

</div>
</form>
</div>

    <?php
    $data = [
        'scripts' => array(
            '/js/textboxfinaliseinit.min.js',
        ),
    ];
    $render->render($data, array(), 'footer.html');
}
?>
