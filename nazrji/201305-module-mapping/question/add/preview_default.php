<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../../include/load_config.php';
require_once $cfg_web_root . 'classes/lang.class.php';

$html = <<< HTML
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset={$configObject->get('cfg_page_charset')}" />
  <title>{$string['preview']}</title>
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
</head>
<body style='color:#808080'>
<p>{$string['previewmsg']}</p>
</body>
</html>
HTML;

echo $html;
?>