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

if (isset($_GET['teamID'])) {
    if (!module_utils::get_moduleid_from_id($_GET['teamID'], $mysqli)) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../../artwork/page_not_found.png', '#C00000', true, true);
    }
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>by Paper</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../../css/tablesort.css" />

  <style type="text/css">
    body {font-size:80%}
    a:link {color:black}
    a:visited {color:black}
    a:hover {color:black}
    .f {padding-left:2px; width:20px}
    .s {padding-left:6px}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../../js/require.js'></script>
  <script src='../../js/main.min.js'></script>
</head>
<?php


$order = param::optional('order', null, param::ALPHA, param::FETCH_GET);
$direction = param::optional('direction', null, param::ALPHA, param::FETCH_GET);
if (!is_null($order)) {
    $order = $order;
    $direction = $direction;
} else {
    $order = 'paper_title';
    $direction = 'asc';
}
?>
<body>
<div style="background-color:#EEF4FF; font-size:160%; font-weight:bold">&nbsp;<?php echo $string['bypaper'] ?></div>
<table class="header tablesorter" id="maindata">
<thead>
<tr>
  <th>&nbsp;</th>
  <th class="vert_div"><?php echo $string['title'] ?></th>
  <th class="vert_div"><?php echo $string['module'] ?></th>
  <th class="vert_div"><?php echo $string['owner'] ?></th>
  <th class="vert_div"><?php echo $string['created'] ?></th>
</tr>
</thead>
<tbody>
<?php
  $paper_icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif', 'survey_16.gif', 'osce_16.gif', 'offline_16.gif', 'peer_16.gif');
  $paper_details = array();
  
  $type = param::optional('paper_type', null, param::INT, param::FETCH_GET);
  $teamid = param::optional('teamID', null, param::INT, param::FETCH_GET);
  $paper_details = PaperUtils::get_available_papers($userObject, $order, $direction, $type, $teamid);

foreach ($paper_details as $property_id => $paper_detail) {
    echo '<tr><td class="f"><a href="add_questions_by_paper.php?question_paper=' . $property_id . '"><img src="../../artwork/' . $paper_icons[$paper_detail['paper_type']] . '" width="16" height="16" alt="' . $string['folder'] . '" align="middle" /></a></td><td class="s"><a href="add_questions_by_paper.php?question_paper=' . $property_id . '">' . $paper_detail['paper_title'] . '</a></td><td class="s">';
    $html = '';
    foreach ($paper_detail['moduleid'] as $module) {
        if ($html == '') {
            $html = $module;
        } else {
            $html .= ', ' . $module;
        }
    }
    echo $html . '</td><td class="s">' . $paper_detail['surname'] . ', ' . $paper_detail['initials'] . '. ' . $paper_detail['title'] . '</td><td class="s">' . $paper_detail['created'] . '</td></tr>';
}
  
?>
</tbody>
</table>
<?php
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$render->render($dataset, array(), 'dataset.html');
?>
<script type="text/javascript" src="../../js/paperquestionsinit.min.js"></script>
</body>
</html>
