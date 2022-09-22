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
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$mode = param::optional('mode', '', param::INT, param::FETCH_GET);
$module = param::optional('module', '', param::INT, param::FETCH_GET);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['externalexaminers'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
  <style>
    body {font-size: 90%}
    input {width: 180px; margin: 1px}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/pickexternalinit.min.js"></script>
 </head>

<body>
<?php
require '../include/toprightmenu.inc';
echo draw_toprightmenu();

if ($mode == 0) {
    $type = $string['initialinvitation'];
} elseif ($mode == 1) {
    $type = $string['reminder'];
} else {
    $type = $string['viewcomments'];
}

?>
<div class="head_title" style="font-size:90%">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a>
  <?php
    if ($module != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . module_utils::get_moduleid_from_id($module, $mysqli) . '</a>';

        $module_url = '&module=' . $module;
    } else {
        $module_url = '';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . $module_url . '">' . $properties->get_paper_title() . '</a>';
    ?>
  </div>
  <div class="page_title"><?php echo $string['externalexaminers'] ?>: <span style="font-weight:normal"><?php echo $type ?></span></div>
</div>

<?php
$externals = $properties->get_externals();

if (count($externals) > 0) {
    echo "<br />\n<div class=\"key\">" . $string['msg'] . "</div>\n<div style=\"margin: 15px\">\n";
    foreach ($externals as $externalID => $external_name) {
        echo "<input type=\"button\" name=\"$externalID\" id=\"$externalID\" value=\"$external_name\" class=\"external\" /><br />";
    }
} else {
    echo $notice->info_strip($string['noexternals'], 100) . "\n";
}

// JS utils dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['paper'] = $paperID;
$miscdataset['attributes']['mode'] = $mode;
$miscdataset['attributes']['module'] = $module;
$miscdataset['attributes']['external'] = $externalID;
$render->render($miscdataset, array(), 'dataset.html');

$mysqli->close();
?>
  </div>
</body>
</html>
