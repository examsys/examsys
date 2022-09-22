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

require '../../include/sysadmin_auth.inc';
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);
if (isset($_GET['id'])) {
    $help_system->restore_page($_GET['id']);
}
$id = null;
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title><?php echo page::title('ExamSys: ' . $string['help']); ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />
  <style type="text/css">
    #contents td {vertical-align:top; border-bottom: 1px solid #295AAD; border-right: 1px solid #295AAD}
    #contents th {color:white}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../../js/require.js'></script>
  <script src='../../js/main.min.js'></script>
  <script type="text/javascript" src="../../js/helpinit.min.js"></script>
</head>

<body>
<div id="wrapper">
  <div id="toolbar">
    <?php $help_system->display_toolbar($id); ?>
  </div>

  <div id="toc">
    <?php $help_system->display_toc($id); ?>
  </div>
  <div id="contents">
    <?php
    $result = $mysqli->prepare("SELECT articleid, title, body_plain, roles, DATE_FORMAT(deleted,'{$configObject->get('cfg_long_date_time')}') AS deleted FROM staff_help WHERE deleted IS NOT NULL AND language = ? ORDER BY title");
    $result->bind_param('s', $language);
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $title, $body, $tmp_roles, $deleted);
    if ($result->num_rows == 0) {
        echo '<p>' . $string['empty'] . "</p>\n";
    } else {
        echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; border-collapse:collapse; border:1px solid #295AAD\">\n";
        echo "<tr style=\"border-collapse:collapse; border-top: 1px solid #295AAD; border-bottom:1px solid #295AAD; border-left:1px solid #295AAD; background-color:#295AAD\">\n";
        echo "<th>&nbsp;</th><th>{$string['title']}</th><th>{$string['content']}</th><th>{$string['access']}</th><th>{$string['deleted']}</th></tr>\n";
        while ($result->fetch()) {
            if ($body == '') {
                $body = '&nbsp;';
            }
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . "?id=$id\"><img src=\"../../artwork/import_16.gif\" class=\"icon16_active\" alt=\"" . $string['restore'] . "\" /></a></td><td><nobr>$title</nobr></td><td>$body</td><td>$tmp_roles</td><td><nobr>$deleted</nobr></td></tr>\n";
        }
        echo "</table>\n";
    }

    $mysqli->close();
    ?>
  </div>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
