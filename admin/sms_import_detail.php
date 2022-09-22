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

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
require '../include/errors.php';
check_var('day', 'GET', true, false, false);

function get_list($list, $db)
{

    $html = '';
    if ($list != '') {
        $result = $db->prepare("SELECT id, title, surname, first_names FROM users WHERE username IN ('" . str_replace(',', "','", $list) . "') ORDER BY surname, initials");
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $title, $surname, $first_names);
        while ($result->fetch()) {
            $html .= '<a href="../users/details.php?userID=' . $id . '">' . $title . ' ' . $surname . ', ' . $first_names . '</a><br />';
        }
    }

    return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['smsupdatesummary']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
  .no {text-align:right}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/smsdetailinit.min.js"></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
?>

<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./list_modules.php"><?php echo $string['modules']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="sms_import_summary.php"><?php echo $string['smsimportsummary']; ?></a></div>
  <div class="page_title"><?php echo $string['smsimportson'] ?> <?php echo mb_substr($_GET['day'], 6, 2) . '/' . mb_substr($_GET['day'], 4, 2) . '/' . mb_substr($_GET['day'], 0, 4) ?></div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0">
  <thead>
    <tr>
      <th class="vert_div col10"><?php echo $string['moduleid'] ?></div></th>
      <th class="vert_div"><?php echo $string['academicyear'] ?></th>
      <th class="vert_div"><?php echo $string['enrolements'] ?></th>
      <th class="vert_div"><?php echo $string['enrolementdetails'] ?></th>
      <th class="vert_div"><?php echo $string['deletions'] ?></th>
      <th class="vert_div"><?php echo $string['deletiondetails'] ?></th>
      <th class="vert_div"><?php echo $string['importtype'] ?></th>
    </tr>
</thead>
<tbody>
<?php
  $result = $mysqli->prepare('SELECT '
      . 'idMod, moduleid, sms_imports.academic_year, enrolements, enrolement_details, deletions, deletion_details, import_type '
      . 'FROM sms_imports, modules, academic_year '
      . 'WHERE sms_imports.idMod=modules.id '
      . 'AND sms_imports.academic_year = academic_year.calendar_year '
      . 'AND updated=? ORDER BY moduleid');
  $db = $mysqli;
  if ($db->error) {
      try {
          throw new Exception("MySQL error $db->error <br /> Query:<br /> $query", $db->errno);
      } catch (Exception $e) {
          echo 'Error No: ' . $e->getCode() . ' - ' . $e->getMessage() . '<br />';
          echo nl2br($e->getTraceAsString());
      }
  }

  $result->bind_param('s', $_GET['day']);
  $result->execute();
  $result->store_result();
  $result->bind_result($idMod, $moduleid, $academic_year, $enrolements, $enrolement_details, $deletions, $deletion_details, $import_type);
  while ($result->fetch()) {
      echo "<tr><td class=\"col10\"><a href=\"../module/index.php?module=$idMod\">$moduleid</a></td><td>$academic_year</td><td class=\"no\">$enrolements</td><td>" . get_list($enrolement_details, $mysqli) . "</td><td class=\"no\">$deletions</td><td>" . get_list($deletion_details, $mysqli) . '</td><td>' . $import_type . "</td></tr>\n";
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
