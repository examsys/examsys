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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require_once '../../classes/moduleutils.class.php';

if (isset($_GET['teamID'])) {
  if (!module_utils::get_moduleid_from_id($_GET['teamID'], $mysqli)) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
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
  <style type="text/css">
  body {font-size:80%}
  a:link {color:black}
  a:visited {color:black}
  a:hover {color:black}
  .f {padding-left:2px; width:20px}
  .s {padding-left:6px}
  </style>
</head>
<?php
/**
 * Build a string for the sorting link on a table column header
 * @param $paper_type Paper type to insert into query string
 * @param $title Link text for the link
 * @param $type Field on which to sort for this link
 * @param $order Current sort order
 * @param $direction Current sort direction
 * @return string
 */
function show_order_link($paper_type, $title, $type, $order, $direction) {
  $html = '<a href="add_questions_paper_list.php?paper_type=' . $paper_type . '&order=' . $type . '&direction=';

  $new_dir = 'asc';
  if ($type == $order) {
    if ($direction == 'asc') {
      $new_dir = 'desc';
    }
  }

  $html .= $new_dir . '">' . $title . '</a>';

  if ($type == $order) {
    $html .= '&nbsp;<img src="../../artwork/' . $new_dir . '.gif" width="9" height="7" border="0" />';
  }

  return $html;
}

$paper_type = (isset($_GET['paper_type'])) ? $_GET['paper_type'] : 0;

if (isset($_GET['order'])) {
  $order = $_GET['order'];
  $direction = $_GET['direction'];
} else {
  $order = 'paper_title';
  $direction = 'asc';
}
?>
<body>
<table class="header">
<tr><th colspan="5"style="font-size:160%; font-weight:bold">&nbsp;<?php echo $string['bypaper'];?></th></tr>
<tr>
  <th>&nbsp;</th>
  <th class="vert_div">&nbsp;<?php echo show_order_link($paper_type, $string['title'], 'paper_title', $order, $direction) ?></th>
  <th class="vert_div">&nbsp;<?php echo show_order_link($paper_type, $string['module'], 'moduleID', $order, $direction) ?></th>
  <th class="vert_div">&nbsp;<?php echo show_order_link($paper_type, $string['owner'], 'surname', $order, $direction) ?></th>
  <th class="vert_div">&nbsp;<?php echo show_order_link($paper_type, $string['created'], 'created', $order, $direction) ?></th>
</tr>
<tr><th colspan="5" class="bevel"></th></tr>
<?php
  $user_teams = $userObject->get_staff_modules();
  $module_id_list = implode(',',array_keys($user_teams));

  $my_teams = '';
  if (count($user_teams) > 0) {
    $my_teams = " OR idMod IN ($module_id_list)";
  }  

  if ($order == 'created') {
    $order = 'CAST(created AS DATE)';
  }

  $paper_icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif', 'survey_16.gif', 'osce_16.gif', 'offline_16.gif', 'peer_review_16.gif');
  $paper_details = array();
  
  if (isset($_GET['paper_type'])) {
    $sql = "SELECT properties.property_id, paper_title, paper_type, DATE_FORMAT(created,' {$configObject->get('cfg_short_date')}') AS created, title, initials, surname, modules.moduleid FROM (properties, properties_modules, modules, users) WHERE properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND paper_type='" . $_GET['paper_type'] . "' AND deleted IS NULL AND paper_ownerID=users.id AND (paper_ownerID=" . $userObject->get_user_ID() . " $my_teams)";
  } else {
    $sql = "SELECT properties.property_id, paper_title, paper_type, DATE_FORMAT(created,' {$configObject->get('cfg_short_date')}') AS created, title, initials, surname, modules.moduleid FROM (properties, properties_modules, modules, users) WHERE properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND idMod = " . $_GET['teamID'] . " AND deleted IS NULL AND paper_ownerID=users.id";
  }
  $sql .= " ORDER BY {$order} " . strtoupper($direction);
  $result = $mysqli->prepare($sql);
  $result->execute();
  $result->bind_result($property_id, $paper_title, $paper_type, $created, $title, $initials, $surname, $moduleid);
  while ($result->fetch()) {
    if (!isset($paper_details[$property_id])) {
      $paper_details[$property_id] = array('paper_title'=>$paper_title, 'paper_type'=>$paper_type, 'created'=>$created, 'title'=>$title, 'initials'=>$initials, 'surname'=>$surname);
    }
    $paper_details[$property_id]['moduleid'][] = $moduleid;
  }
  $result->close();

  foreach ($paper_details as $property_id=>$paper_detail) {
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
</table>
</body>
</html>