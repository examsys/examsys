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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';

$title = check_var('title', 'GET', true, false, true);

$id = null;
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  
  <title>Rog&#333;: <?php echo $string['help'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />
  <style type="text/css">
    body {line-height:150%}
    table {font-size:100%}
    a:link {color:#0560A6}
    a:visited {color:#0560A6}
    .row {height:28px; border-bottom: 1px solid #A6CBEB}
  </style>
  
  <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../../js/help.js"></script>
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
    <?php $help_system->display_folder($title); ?>
  </div>

</div>
</body>
</html>
<?php
  $mysqli->close();
?>