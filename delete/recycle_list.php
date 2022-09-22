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
 * Displays a list of deleted papers, questions and folders.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';

/**
 * Converts a time/date from 20140301103059 into 01/03/2014 10:30.
 *
 * Please use date_utils::rogoToDisplay instead. This function will be removed in the future.
 *
 * @param string $tmp_date - The date that needs to be convered.
 * @return string
 * @deprecated since 7.2.0
 */
function dateDisplay($tmp_date)
{
    return date_utils::rogoToDisplay($tmp_date);
}

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
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['recyclebin']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    .icon {width:20px; text-align:right; padding-right:8px}
    .f {float:left; width:375px; height:74px; padding-left:12px}
    .h {display: block}
    .qline {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .qline:hover {background-color:#FFE7A2}
    .qline.highlight {background-color:#B3C8E8}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/recycleinit.min.js"></script>
</head>

<body>
<?php
  require '../include/recycle_options_menu.inc';
  require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
$recycleObj = new RecycleBin();
$recycle_bin = $recycleObj->get_recyclebin_contents();

$mysqli->close();

$sortby = 'name';
if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
}

$ordering = 'asc';
if (isset($_GET['ordering'])) {
    $ordering = $_GET['ordering'];
}

if (count($recycle_bin) > 0) {
    $recycle_bin = \sort::array_csort($recycle_bin, $sortby, $ordering);
}

?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a></div>
  <div class="page_title"><?php echo $string['recyclebin'] ?></div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
<thead>
  <tr>
    <th style="width:16px"></th>
    <th style="width:500px"><?php echo $string['name']; ?></th>
    <th style="width:130px" class="{sorter: 'datetime'} col"><?php echo $string['datedeleted'] ?></th>
    <th style="width:120px" class="col"><?php echo $string['type'] ?></th>
  </tr>
</thead>
<tbody>
<?php

$paper_types = array('Formative Self-Assessment', 'Progress Test', 'Summative Exam', 'Survey', 'OSCE Station', 'Offline Paper', 'Peer Review');
$paper_icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif', 'survey_16.gif', 'osce_16.gif', 'offline_16.gif', 'peer_16.gif');
$list_size = count($recycle_bin);
for ($item = 0; $item < $list_size; $item++) {
    $split_name = explode('[deleted', $recycle_bin[$item]['name']);
    if ($recycle_bin[$item]['type'] == 'paper') {
        $temp_type = $recycle_bin[$item]['subtype'];
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"p" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/' . $paper_icons[$temp_type] . '" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string[mb_strtolower($paper_types[$temp_type])] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'folder') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"f" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/yellow_folder.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['folder'] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'academic_year') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"a" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/add_sessions_16.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['academicsession'] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'modules') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"m" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/module_icon_16.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['module'] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'courses') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"c" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/degree_icon_16.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['course'] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'schools') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"s" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/school_icon_16.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['school'] . "</nobr></td></tr>\n";
    } elseif ($recycle_bin[$item]['type'] == 'faculty') {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"u" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/faculty_16.png" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string['faculty'] . "</nobr></td></tr>\n";
    } else {
        echo "<tr class=\"l\" id=\"link_$item\" data-lineid=\"$item\" data-itemid=\"q" . $recycle_bin[$item]['id'] . '"><td class="icon"><img src="../artwork/question_item_icon.gif" width="16" height="16" /></td><td>' . $split_name[0] . '</td><td>' . date_utils::rogoToDisplay($recycle_bin[$item]['deleted']) . '</td><td><nobr>' . $string[mb_strtolower($recycle_bin[$item]['subtype'])] . "</nobr></td></tr>\n";
    }
}
?>
</tbody>
</table>

</div>
<?php
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
