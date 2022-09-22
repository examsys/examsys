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
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$modID = check_var('module', 'GET', true, false, true);

$module = module_utils::get_moduleid_from_id($modID, $mysqli);

if (!$module) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['manageobjectives']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    .obj_no {text-align:right; padding-right:6px}
    .zero_obj_no {text-align:right; padding-right:6px; color:#C00000}
    .title {padding-left:6px}
    .indent {padding-left:24px}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/sessionslistinit.min.js"></script>
 </head>

<body>
<?php
  require '../include/sessions_options.inc';
  require '../include/toprightmenu.inc';
    
    echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $modID ?>"><?php echo $module ?></a></div>
  <div class="page_title"><?php echo $string['manageobjectives'] ?></div>
</div>
<?php
  echo "<table class=\"header\">\n";
  echo '<tr><th class="col10">' . $string['date'] . "</th>\n";
  echo '<th class="col">' . $string['name'] . "</th>\n";
  echo '<th class="col">' . $string['objectives'] . "</th><th>&nbsp;</th></tr>\n";

  $old_session = '';
  $id = 0;
  $first = true;
if (!empty($objsBySession) and isset($objsBySession[$module])) {
    foreach ($objsBySession[$module] as $session) {
        if (isset($session['objectives'])) {
            $objectives_no = count($session['objectives']);
        } else {
            $objectives_no = 0;
        }
        if ($old_session != $session['calendar_year']) {
            if (!$first) {
                echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
            }
            $first = false;
            echo '<tr><td colspan="4"><table border="0" class="subsect" style="margin-left:10px; width:99%"><tr><td><nobr>' . $session['calendar_year'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
        }
        if (isset($session['identifier'])) {
            $identifier = $session['identifier'];
        } else {
            $identifier = '';
        }
        if ($session['VLE'] != '') {
            echo "<tr class=\"l\" id=\"$id\" data-identifier=\"$identifier\" data-year=\"" . $session['calendar_year'] . '" data-vle="' . $session['VLE'] . "\" ondblclick=\"editVLESession('" . $session['calendar_year'] . "');\">";
        } else {
            echo "<tr class=\"l\" id=\"$id\" data-identifier=\"$identifier\" data-year=\"" . $session['calendar_year'] . '" data-vle="' . $session['VLE'] . '" data-id="' . $session['identifier'] . '" data-year="' . $session['calendar_year'] . '">';
        }
        echo '<td class="indent">' . $session['occurrance'] . '</td><td class="title">' . $session['title'] . '</td>';
        if ($objectives_no == 0) {
            echo "<td class=\"zero_obj_no\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"Warning\" />&nbsp;$objectives_no</td>";
        } else {
            echo "<td class=\"obj_no\">$objectives_no</td>";
        }
        echo "<td>&nbsp;</td></tr>\n";
        $old_session = $session['calendar_year'];
        $id++;
    }
}

  $mysqli->close();
?>
</table>
</div>

<?php
// Dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['module'] = $modID;
$miscdataset['attributes']['vle'] = $vle_api;
if ($vle_api != '') {
    $miscdataset['attributes']['vlename'] = $vle_name;
    $miscdataset['attributes']['vlehumanname'] = $vle_name_a;
}
$render->render($miscdataset, array(), 'dataset.html');
// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
