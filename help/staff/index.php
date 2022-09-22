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
require_once '../../include/errors.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = 1;
}

$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);

if (isset($_GET['highlight'])) {
    $help_system->set_highlight($_GET['highlight']);
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title><?php echo page::title('ExamSys: ' . $string['help']); ?></title>

    <link rel="stylesheet" type="text/css" href="../../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../../css/help.css" />
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
        <?php $help_system->display_page($id); ?>
      </div>
    </div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['srcofy'] = param::optional('srcOfY', 0, param::FLOAT, param::FETCH_GET);
$render->render($miscdataset, array(), 'dataset.html');
?>
  </body>  
  
</html>
