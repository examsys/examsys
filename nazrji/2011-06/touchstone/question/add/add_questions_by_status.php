<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
?>
<html>
<head>
<title>by Status</title>
<style>
body {margin:0px; background-color:white; color:black; font-family:Arial,sans-serif; font-size:90%}
a:link {color:black}
a:visited {color:black}
a:hover {color:black}
.divider {font-size:90%; padding-left:16px; padding-bottom:2px; font-weight:bold}
.f {float:left; width:375px; padding-left:12px; font-size:90%}
</style>
</head>

<body>
<br />
<div class="f"><a href="add_questions_list_status.php?status=Normal" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" border="0" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Normal">Normal</a></div>
<div class="f"><a href="add_questions_list_status.php?status=Retired" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" border="0" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Retired">Retired</a></div>
<div class="f"><a href="add_questions_list_status.php?status=Incomplete" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" border="0" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Incomplete">Incomplete</a></div>
<div class="f"><a href="add_questions_list_status.php?status=Experimental" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" border="0" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Experimental">Experimental</a></div>
<div class="f"><a href="add_questions_list_status.php?status=Beta" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" border="0" align="middle" /></a>&nbsp;<a href="add_questions_list_status.php?status=Beta">Beta</a></div>

</body>
</html>