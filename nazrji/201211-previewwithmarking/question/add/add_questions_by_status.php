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
  
  <title><?php echo $string['bystatus']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {font-size:90%}
    a:link {color:black}
    a:visited {color:black}
    a:hover {color:black}
    .divider {font-size:90%; padding-left:16px; padding-bottom:2px; font-weight:bold}
    .f {float:left; width:375px; padding-left:12px; font-size:90%}
  </style>
</head>

<body>
<br />
<div class="f"><a href="add_questions_list_status.php?status=Normal"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Normal"><?php echo $string['normal']; ?></a></div>
<div class="f"><a href="add_questions_list_status.php?status=Retired"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Retired"><?php echo $string['retired']; ?></a></div>
<div class="f"><a href="add_questions_list_status.php?status=Incomplete"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Incomplete"><?php echo $string['incomplete']; ?></a></div>
<div class="f"><a href="add_questions_list_status.php?status=Experimental"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Experimental"><?php echo $string['experimental']; ?></a></div>
<div class="f"><a href="add_questions_list_status.php?status=Beta"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Beta"><?php echo $string['beta']; ?></a></div>

</body>
</html>