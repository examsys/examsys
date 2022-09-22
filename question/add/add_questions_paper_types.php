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
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>by Paper</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {font-size:90%}
    a:link {color:black}
    a:visited {color:black}
    a:hover {color:black}
    .divider {font-size:90%; padding-left:16px; padding-bottom:2px; font-weight:bold}
    .f {float:left; width:375px; padding-left:12px; font-size:90%}
        img {padding:5px}
  </style>
</head>

<body>
<br />
<table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr><?php echo $string['papersbytype']; ?> (6)</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%" /></td></tr></table>
<br />
<div class="f"><a href="add_questions_paper_list.php?paper_type=0" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=0"><?php echo $string['formative self-assessment']; ?></a></div>
<div class="f"><a href="add_questions_paper_list.php?paper_type=1" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=1"><?php echo $string['progress test']; ?></a></div>
<div class="f"><a href="add_questions_paper_list.php?paper_type=2" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=2"><?php echo $string['summative exam']; ?></a></div>
<div class="f"><a href="add_questions_paper_list.php?paper_type=3" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=3"><?php echo $string['survey']; ?></a></div>
<div class="f"><a href="add_questions_paper_list.php?paper_type=4" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=4"><?php echo $string['osce station']; ?></a></div>
<div class="f"><a href="add_questions_paper_list.php?paper_type=5" target="_top"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?paper_type=5"><?php echo $string['offline paper']; ?></a></div>
<br clear="all" />
<?php
  $teams = $userObject->get_staff_modules();
?>
<br />
<table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr><?php echo $string['papersbyteam']; ?> (<?php echo count($teams); ?>)</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%" /></td></tr></table>
<br />
<?php

foreach ($teams as $teamID => $team_name) {
    echo '<div class="f"><a href="add_questions_paper_list.php?teamID=' . $teamID . '"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a><a href="add_questions_paper_list.php?teamID=' . $teamID . '">' . $team_name .  '</a></div>';
}
?>

</body>
</html>