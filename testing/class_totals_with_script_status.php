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
 * This script is designed to compare marks between the Class Totals report and students' actual exam scripts (finish.php).
 * It works by:
 *   1. Get summative exam papers in the require date range.
 *   2. For each paper call class_totals.php and parse for student IDs and marks.
 *   3. For each student call finish.php and compare the mark.
 *   4. Echo errors for any which do not match.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require dirname(__DIR__) . '/include/sysadmin_auth.inc';

$end_dateSQL = 'NOW()';
if (isset($_GET['period']) and $_GET['period'] != '') {
  if ($_GET['period'] == 'day') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 DAY)';
  } elseif ($_GET['period'] == 'week') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 WEEK)';
  } elseif ($_GET['period'] == 'month') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 MONTH)';
  } elseif ($_GET['period'] == 'year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 YEAR)';
  } elseif ($_GET['period'] == '2year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 2 YEAR)';
  } elseif ($_GET['period'] == '3year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 3 YEAR)';
  } elseif ($_GET['period'] == '6year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 6 YEAR)';
  }
} else {
  $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 5 YEAR)';
}

$papers = 0;
if (isset($_GET['paper']) and $_GET['paper'] != '') {
  $stmt = $mysqli->prepare("SELECT property_id FROM properties WHERE property_id=?");
  $stmt->bind_param('i', $_GET['paper']);
} else {
  $stmt = $mysqli->prepare("SELECT property_id FROM properties WHERE paper_type = '2' AND start_date > $start_dateSQL AND end_date < $end_dateSQL AND deleted IS NULL ORDER BY start_date");
}
$stmt->execute();
$stmt->bind_result($paperID);
while ($stmt->fetch()) {
  $papers++;
}
$stmt->close();

$results = array();
$stmt = $mysqli->prepare("SELECT paper_id, status, errors FROM class_totals_test_local WHERE user_id = ? ORDER BY id");
$stmt->bind_param('i', $userObject->get_user_ID());
$stmt->execute();
$stmt->bind_result($paper_id, $status, $errors);
while ($stmt->fetch()) {
  $results[] = array('paper_id' => $paper_id, 'status' => $status, 'errors' => $errors);
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Testing: Class Totals</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {
      font-size: 90%;
    }
    #content {
      margin: 15px;
    }
    .papers, .papers ul {
      list-style: none;
      padding-left: 8px;
    }
    .papers li {
      min-height: 26px;
      padding-left: 22px;
    }
    .failure {
      background: transparent url('../artwork/cross.gif') no-repeat left top;
    }
    .success {
      background: transparent url('../artwork/tick.gif') no-repeat left top;
    }
    .in_progress {
      background: transparent url('../artwork/working.gif') no-repeat left top;
    }
  </style>
  <script src="../js/jquery-1.11.1.min.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      setTimeout(refreshPage, 15000); // milliseconds
    });
    function refreshPage() {
      if ($('#refresh').is(':checked')) {
        window.location = location.href;
      }
    }
  </script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php">Administrative Tools</a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../testing/index.php">Testing</a></div>
  <div class="page_title">Summative Exam Check: <span style="font-weight:normal">Status</span></div>
</div>
  
<div id="content">
<?php
if (count($results) == 0):
?>
  <p>No papers analysed.
<?php
    if (count($papers) > 0):
?>
    Refresh page for updates.
<?php
    endif;
?>
  </p>
<?php
else:
?>
  <p><input type="checkbox" id="refresh" name="refresh" checked="checked" /> <label for="refresh">Auto refresh</label></p>
  <p>Analysed <?php echo count($results) ?> out of <?php echo $papers ?> (<?php echo number_format(((count($results))/$papers * 100), 0) ?>%)</p>
  <ul class="papers">
<?php
  foreach ($results as $result) {
?>
    <li class="<?php echo $result['status'] ?>"><a href="../paper/details.php?paperID=<?php echo $result['paper_id'] ?>">Paper ID <?php echo $result['paper_id'] ?></a>
<?php
    if ($result['status'] == 'failure') {
      echo $result['errors'];
    }
?>
    </li>
<?php
  }
?>
  </ul>
<?php
  if (count($results) == $papers):
?>
    <p>Analysis complete.</p>
<?php
  endif;
endif;
?>
</div>
</div>
</body>
</html>
