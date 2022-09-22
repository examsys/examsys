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
require '../include/errors.php';

$modID = check_var('module', 'GET', true, false, true);

$module_code = module_utils::get_moduleid_from_id($modID, $mysqli);

if (!$module_code) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title><?php echo page::title('ExamSys: ' . $string['referencematerial']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/refmaterialadmin.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/listrefmatinit.min.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
    
    echo draw_toprightmenu(296);

  $reference_materials = array();

  $result = $mysqli->prepare('SELECT reference_material.id, reference_material.title FROM reference_material, reference_modules WHERE reference_material.id = reference_modules.refID AND reference_material.deleted IS NULL AND idMod = ? ORDER BY reference_material.id');
  $result->bind_param('i', $modID);
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $title);
while ($result->fetch()) {
    $sub_result = $mysqli->prepare('SELECT moduleid FROM reference_modules, modules WHERE reference_modules.idMod = modules.id AND refID = ?');
    $sub_result->bind_param('i', $id);
    $sub_result->execute();
    $sub_result->store_result();
    $sub_result->bind_result($moduleid);
    while ($sub_result->fetch()) {
        if (isset($reference_materials[$id]['modules'])) {
            $reference_materials[$id]['modules'] .= ', ' . $moduleid;
        } else {
            $reference_materials[$id]['modules'] = $moduleid;
        }
    }
    $sub_result->close();
    
    $reference_materials[$id]['title'] = $title;
}
  $result->close();

  require '../include/reference_material_options.inc';
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php?module=<?php echo $modID ?>"><?php echo module_utils::get_moduleid_from_id($modID, $mysqli) ?></a></div>
  <div class="page_title"><?php echo $string['referencematerial'] ?></div>
</div>  
  
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
<thead>
  <tr>
    <th style="width:60%; padding-left:20px"><?php echo $string['referencename'] ?></th>
    <th style="width:40%" class="col"><?php echo $string['modules'] ?></th>
  </tr>
</thead>
<tbody>
<?php
foreach ($reference_materials as $id => $details) {
    echo "<tr id=\"$id\" class=\"l\"><td class=\"icon\">" . $details['title'] . '</td><td>' . $details['modules'] . "</td></tr>\n";
}

$mysqli->close();
?>
</tbody>
</table>

</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$dataset['name'] = 'dataset';
$dataset['attributes']['module'] =  $modID;
$render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
