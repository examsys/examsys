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
// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['bystatus']; ?></title>

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
<?php
foreach ($status_array as $sid => $status) {
    ?>
<div class="f"><a href="add_questions_list.php?type=status&status=<?php echo $sid ?>"><img src="../../artwork/yellow_folder.png" width="48" height="48" alt="Folder" align="middle" /></a>&nbsp;<a href="add_questions_list.php?type=status&status=<?php echo $sid ?>"><?php echo $status->get_name(); ?></a></div>
    <?php
}
?>
</body>
</html>
