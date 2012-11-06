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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Rogō</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style>
    html {height:99.2%}
    body {height:100%; margin-top:4px; margin-left:0px; margin-right:6px; margin-bottom:2px; background-color:#DFECFF}
  </style>
</head>

<body>

<iframe src="add_questions_list_unused.php" name="iframeurl" width="100%" height="60%" style="border:1px solid #95AEC8" frameborder="0">
  <p><?php echo $string['browsererr'];?></p>
</iframe>

<iframe src ="preview_default.php" name="previewurl" width="100%" height="39%" style="border:1px solid #95AEC8" frameborder="0">
  <p><?php echo $string['browsererr'];?></p>
</iframe>
</body>
</html>