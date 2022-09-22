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

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
    <title><?php echo page::title('ExamSys: ' . $string['statisticsreports']); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/admin.css" />

    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
</head>

<body>

<?php
  require '../include/toprightmenu.inc';
    
    echo draw_toprightmenu();

  $mysqli->close();
?>

<div id="content">
  
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['statisticsreports']; ?></div>
</div>
  
<?php
  $summative_year =  date('Y');
if (date('n') < 7) {
    $summative_year--;
}

    $menudata = array();
    $menudata['papersbyschool']         = array('papers_by_school.php?calyear=' . $summative_year, 'paper_stats.png');
    $menudata['questionsbyschool']  = array('questions_by_school.php', 'question_stats.png');
    $menudata['summativeexamstats'] = array('summative_stats.php?calyear=' . $summative_year, 'summative_scheduling.png');
    $menudata['summativefeedback']  = array('summative_feedback.php?calyear=' . $summative_year, 'summative_feedback_stats.png');

foreach ($menudata as $menukey => $menuitem) {
    $parts = explode('.php', $menuitem[0]);
    echo '<a class="blacklink" href="' . $menuitem[0] . '" id="' . $parts[0] . '">';
    echo '<div class="container"><img src="../artwork/' . $menuitem[1] . '" alt="" class="icon" /><br />' . $string[$menukey] . '</div></a>';
}

?>
</div>

</body>
</html>
