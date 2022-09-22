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
 * ExamSys Test Harness.
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
$langpack = new \langpack();
$strings = $langpack->get_all_strings('testing/index');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>ExamSys: <?php echo $strings['testsuite'] ?></title>

   <style>
        .content {font-size:80%}
       li {margin-left:20px; line-height:150%}
    </style>
   <link rel="stylesheet" type="text/css" href="../css/body.css" />
   <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
    h2 {margin-left: 20px; font-size: 150%}
    li {font-size: 110%}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';
echo draw_toprightmenu();
?>
<div id="content">

  <div class="head_title">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $strings['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $strings['administrativetools'] ?></a></div>
    <div class="page_title"><?php echo $strings['testing'] ?></div>
  </div>

  <h2><?php echo $strings['developmenttests'] ?></h2>
    <ol>
        <li><a href="help_test.php"><?php echo $strings['helpconsistency'] ?></a></li>
        <li><a href="online_help_gaps.php"><?php echo $strings['helpgaps'] ?></a></li>
 </ol>

  <h2><?php echo $strings['posttests'] ?></h2>
    <ol>
        <li><a href="class_totals_with_script.php"><?php echo $strings['summativecheck'] ?></a></li>
    <li><a href="checkenhancedcalc.php"><?php echo $strings['calccheck'] ?></a></li>
    <li><a href="test_email.php"><?php echo $strings['emailcheck'] ?></a></li>
    </ol>

</div>
</body>
</html>
