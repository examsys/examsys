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
 * Displays the results of a question search.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
set_time_limit(0);
$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
if (isset($_SERVER['QUERY_STRING'])) {
    $_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
}
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['questionsearch']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/tablesort.css" />
  <link rel="stylesheet" type="text/css" href="../css/question_list.css" />
  <link rel="stylesheet" type="text/css" href="../css/question_leadin_popup.css" />
  <style type="text/css">
        <?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-root="<?php echo $configObject->get('cfg_root_path'); ?>"
            data-mathjax="<?php echo $configObject->get_setting('core', 'paper_mathjax'); ?>">
  </script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/questionsearchinit.min.js"></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
?>
</head>

<?php
if (isset($_GET['submit'])) {
    echo "<body>\n";
    require '../include/question_search_options.inc';
    require '../include/toprightmenu.inc';
    echo draw_toprightmenu();
    echo "<div id=\"content\" class=\"content\">\n";
} else {
    echo "<body>\n";
    require '../include/question_search_options.inc';
    require '../include/toprightmenu.inc';
    echo draw_toprightmenu();
    echo "<div id=\"content\" class=\"content\">\n";
    echo "<div class=\"head_title\">\n";
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if (isset($_GET['module'])) {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '</div><div class="page_title">' . $string['questionsearch'] . "</div>\n</div>\n";
    ?>
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
  <thead>
  <tr>
    <th class="vert_div" style="width:50%"><?php echo $string['question'] ?></th>
    <th class="vert_div" style="width:12%"><?php echo $string['owner'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['type'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['modified'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['status'] ?></th>
  </tr>
  </thead>
  </table>
    <?php
}

if (isset($_GET['submit'])) {
    $error = '';
    if (!isset($_GET['theme']) and !isset($_GET['scenario']) and !isset($_GET['leadin']) and !isset($_GET['options']) and !isset($_GET['keywords']) and !isset($_GET['feedback'])) {
        $error = $string['notickedfields'];
    }

    if (!isset($_GET['status'])) {
        $error = $string['notickedstatus'];
    }

    if (($_GET['searchterm'] == '' or $_GET['searchterm'] == '%') and $_GET['owner'] == '' and  (isset($_GET['status']) and count($_GET['status']) == count($status_array)) and $_GET['bloom'] == '%' and $_GET['keywordID'] == '' and $_GET['module'] == '' and $_GET['question_date'] == 'dont remember' and $_GET['qType'] == '') {
        $error = $string['narrowyoursearch'];
    }

    if ($error != '') {
        echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
        ?>
    <thead>
    <tr>
      <th class="vert_div" style="width:50%"><?php echo $string['question'] ?></th>
      <th class="vert_div" style="width:12%"><?php echo $string['owner'] ?></th>
      <th class="vert_div" style="width:10%"><?php echo $string['type'] ?></th>
      <th class="vert_div" style="width:10%"><?php echo $string['modified'] ?></th>
      <th class="vert_div" style="width:10%"><?php echo $string['status'] ?></th>
    </tr>
    </thead>
    </table>
        <?php
        echo $notice->info_strip($error, 100) . "\n</body>\n</html>\n";
        exit;
    }

    $params = '';
    $variables = array();
    $keywordsSQL = '';
    if ($_GET['keywordID'] != '') {
        $keywordsSQL = 'AND keywordID = ?';
        $variables[] = intval($_GET['keywordID']);
        $params .= 'i';
    }

    $searchterm = $mysqli->real_escape_string($_GET['searchterm']);
    if ($searchterm == '') {
        $search_string = '';
    } else {
        if (isset($_GET['theme']) and $_GET['theme']) {
            $themeSQL = ' OR theme LIKE ?';
            $variables[] = '%' . $searchterm . '%';
            $params .= 's';
        } else {
            $themeSQL = '';
        }

        if (isset($_GET['scenario']) and $_GET['scenario']) {
            $scenarioSQL = ' OR scenario_plain LIKE ?';
            $variables[] = '%' . $searchterm . '%';
            $params .= 's';
        } else {
            $scenarioSQL = '';
        }

        if (isset($_GET['leadin']) and $_GET['leadin']) {
            $leadinSQL = ' OR leadin_plain LIKE ?';
            $variables[] = '%' . $searchterm . '%';
            $params .= 's';
        } else {
            $leadinSQL = '';
        }

        if (isset($_GET['options']) and $_GET['options']) {
            $stemsSQL = ' OR option_text LIKE ?';
            $variables[] = '%' . $searchterm . '%';
            $params .= 's';
        } else {
            $stemsSQL = '';
        }

        if (isset($_GET['feedback']) and $_GET['feedback']) {
            $feedbackSQL = ' OR correct_fback LIKE ? OR incorrect_fback LIKE ? OR feedback_right LIKE ? OR feedback_wrong LIKE ?';
            $variables[] = '%' . $searchterm . '%';
            $variables[] = '%' . $searchterm . '%';
            $variables[] = '%' . $searchterm . '%';
            $variables[] = '%' . $searchterm . '%';
            $params .= 'ssss';
        } else {
            $feedbackSQL = '';
        }

        $qIDSQL = ' OR questions.q_id = ?';
        $variables[] = $searchterm;
        $params .= 's';
        $search_string = $themeSQL . $scenarioSQL . $leadinSQL . $stemsSQL . $feedbackSQL . $qIDSQL;
        $search_string = 'AND (' . mb_substr($search_string, 4) . ')';
    }

    if ($_GET['module'] != '') {
        $module_string = ' AND idMod = ?';
        $variables[] = $_GET['module'];
        $params .= 'i';
    } else {
        $module_string = '';
    }

    if ($_GET['owner'] != '' or count($staff_modules) == 0) {
        $user_string = ' AND questions.ownerID=?';
        $variables[] = $_GET['owner'];
        $params .= 'i';
    } else {
        // If no specific owner set lock down by team (apart from SysAdmin).
        if (count($staff_modules) > 0 and $_GET['module'] == '') {
            $user_string = implode(',', array_keys($staff_modules));
            $user_string = " AND (idMod IN ($user_string) OR users.id={$userObject->get_user_ID()})";
        } else {
            $user_string = '';
        }
    }

    if (isset($_GET['status'])) {
        $status_string = ' AND questions.status IN (' . implode(',', $_GET['status']) . ')';
    } else {
        $status_string = '';
    }

    if (isset($_GET['locked']) and $_GET['locked'] == '1') {
        $locked_string = '';
    } else {
        $locked_string = ' AND locked IS NULL';
    }

    if (isset($_GET['filtercorrect'])) {
        $correct_string = '';
        switch ($_GET['filtercorrect']) {
            case 'blank':
                $correct_string = " AND correct = ''";
                break;
            case 'nonblank':
                $correct_string = " AND correct != ''";
                break;
        }
    }

    if ($_GET['question_date'] == 'dont remember') {
        $last_edited = '';
    } else {
        switch ($_GET['question_date']) {
            case 'week':
                                                                                                                                $from_date = date('YmdHis', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 7, date('Y')));
                $to_date = date('YmdHis');

                break;
            case 'month':
                                                                                                                                $from_date = date('YmdHis', mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));
                $to_date = date('YmdHis');

                break;
            case 'year':
                                                                                                                                $from_date = date('YmdHis', mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y') - 1));
                $to_date = date('YmdHis');

                break;
            case 'specify':
                                                                                                                                $from_date = $_GET['fyear'] . $_GET['fmonth'] . $_GET['fday'] . '000000';
                $to_date = $_GET['tyear'] . $_GET['tmonth'] . $_GET['tday'] . '235959';

                break;
        }
        $last_edited = 'AND last_edited > ? AND last_edited < ?';
        $variables[] = $from_date;
        $variables[] = $to_date;
        $params .= 'ss';
    }

    if ($_GET['searchtype'] == '%') {
        $q_type = '';
    } else {
        $q_type = 'AND q_type LIKE ?';
        $variables[] = $_GET['searchtype'];
        $params .= 's';
    }

    if ($_GET['bloom'] == '%') {
        $bloom = '';
    } else {
        $bloom = 'AND bloom LIKE ?';
        $variables[] = $_GET['bloom'];
        $params .= 's';
    }

    if ($keywordsSQL == '') {
        $sql = 'SELECT DISTINCT title, initials, surname, q_type,'
            . ' questions.q_id, theme, leadin, leadin_plain,'
            . " DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS last_edited,"
            . ' ownerID, locked, status, name FROM (questions, question_statuses, users)'
            . ' LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id'
            . ' LEFT OUTER JOIN options ON options.o_id = questions.q_id'
            . ' WHERE questions.status = question_statuses.id'
            . " AND questions.ownerID = users.id $search_string $module_string $user_string $status_string $locked_string $correct_string $last_edited $q_type $bloom"
            . ' AND deleted IS NULL ORDER BY leadin_plain';
    } else {
        $sql = 'SELECT DISTINCT title, initials, surname, q_type,'
            . " questions.q_id, theme, leadin, leadin_plain, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS last_edited,"
            . ' ownerID, locked, status, name FROM (questions, question_statuses, users, keywords_question)'
            . ' LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id'
            . ' LEFT OUTER JOIN options ON options.o_id = questions.q_id'
            . ' WHERE questions.status = question_statuses.id'
            . " AND questions.q_id = keywords_question.q_id $keywordsSQL"
            . " AND questions.ownerID = users.id $search_string $module_string $user_string $status_string $locked_string $correct_string $last_edited $q_type $bloom"
            . ' AND deleted IS NULL ORDER BY leadin_plain, questions.q_id';
    }

    $result = $mysqli->prepare($sql);
    if (count($variables) > 0) {
        array_unshift($variables, $params);
        foreach ($variables as $key => $value) {
            $tmp[$key] = &$variables[$key];
        }
        call_user_func_array(array($result,'bind_param'), $tmp);
    }
    $result->execute();
    $result->store_result();
    $result->bind_result($title, $initials, $surname, $q_type, $q_id, $theme, $leadin, $leadin_plain, $last_edited, $ownerID, $locked, $status, $status_name);
    $hits = $result->num_rows;
    echo "<div class=\"head_title\">\n";
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if ($_GET['module']) {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '</div><div class="page_title">' . $string['questionsearch'] . ' (' . number_format($hits) . '):&nbsp;<span style="font-weight: normal">';
    if (isset($_GET['searchterm']) and $_GET['searchterm'] != '') {
        echo "'" . $_GET['searchterm'] . "'";
    } elseif (isset($_GET['searchtype']) and $_GET['searchtype'] != '%') {
        echo $string[$_GET['searchtype']];
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
        echo module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
    }
    echo "</span></div>\n</div>\n";
    ?>
  <table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
  <thead>
  <tr>
    <th class="vert_div" style="width:50%"><?php echo $string['question'] ?></th>
    <th class="vert_div" style="width:12%"><?php echo $string['owner'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['type'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['modified'] ?></th>
    <th class="vert_div" style="width:10%"><?php echo $string['status'] ?></th>
  </tr>
  </thead>
  <tbody>
    <?php
    $display_no = 0;
    $leadinlength = $configObject->get_setting('core', 'misc_search_leadin_length');
    while ($result->fetch()) {
        $status_class = ' status' . $status_array[$status]->id;
        echo '<tr class="q' . $status_class . '"';
        if ($locked != '') {
            echo " id=\"l$display_no\" data-qid=\"$q_id\" data-qtype=\"$q_type\" data-dispno=\"$display_no\"  data-seltype=\"Q\">";
        } else {
            echo " id=\"l$display_no\" data-qid=\"$q_id\" data-qtype=\"$q_type\" data-dispno=\"$display_no\"  data-seltype=\"L\">";
        }
        $fullText = QuestionUtils::clean_leadin($leadin, 0);
        $tmp_leadin = QuestionUtils::clean_leadin($leadin, $leadinlength);
        if (trim($tmp_leadin) == '') {
            $tmp_leadin = '<span style="color:red">' . $string['noquestionleadin'] . '</span>';
        }

        if ($q_type == 'sct') {
            $sct_parts = explode('~', $tmp_leadin);
            $tmp_leadin = $sct_parts[0];
        }
        if ($locked != '') {
            echo '<td class="l';
        } else {
            echo '<td class="u';
        }
        if (mb_strlen($fullText) > $leadinlength) {
            echo ' extended-leadin" data-extended-leadin="' . htmlspecialchars($fullText);
        }
        echo '">';
        if (trim($theme) != '') {
            echo '<span class="t">' . $theme . '</span><br />&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        echo $tmp_leadin . '</td>';
        echo "<td>$title $initials $surname</td>";
        echo '<td><nobr>' . $string[$q_type] . '</nobr></td>';
        echo '<td>' . $last_edited . '</td>';
        echo '<td>' . $status_name . '</td></tr>';
        $display_no++;
    }
    $result->close();
    ?>
  </tbody>
  </table>
    <?php
    if ($hits == 0) {
        echo $notice->info_strip($string['noquestionsfound'], 100);
    }

    $mysqli->close();
}
?>
</div>
<?php
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
