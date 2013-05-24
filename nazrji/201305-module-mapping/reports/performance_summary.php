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
* Displays an overview of summative and offline reports for a student
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/results_cache.class.php';

$userID = check_var('userID', 'GET', true, false, true);

if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check for valid user ID.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function get_taken_papers($userID, $db) {
  $papers = array();

  $i = 0;
  $result = $db->prepare("SELECT DISTINCT paperID, paper_title, pass_mark, calendar_year FROM log_metadata, properties WHERE log_metadata.paperID = properties.property_id AND paper_type IN ('2', '5') AND userID = ? ORDER BY calendar_year DESC");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paperID, $paper_title, $pass_mark, $calendar_year);
  while ($result->fetch()) {
    $papers[$i]['paperID'] = $paperID;
    $papers[$i]['paper_title'] = $paper_title;
    $papers[$i]['calendar_year'] = $calendar_year;
    $papers[$i]['pass_mark'] = $pass_mark;
    $results_cache = new ResultsCache($db);
    $papers[$i]['stats'] = $results_cache->get_paper_cache($paperID);

    $i++;
  }
  $result->close();

  return $papers;
}

$papers = get_taken_papers($userID, $mysqli);

$results_cache = new ResultsCache($mysqli);
$marks = $results_cache->get_paper_marks_by_student($userID);

?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['performsummary']; ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style>
h1 {font-size:120%; padding:0px; margin-top:15px; margin-bottom:0px}
</style>
</head>

<body>
<?php
$demo = is_demo($userObject);
$student_details = UserUtils::get_user_details($_GET['userID'], $mysqli);
$name = demo_replace($student_details['title'], $demo) . ' ' . demo_replace($student_details['surname'], $demo) . ', ' . demo_replace($student_details['first_names'], $demo);

echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"font-size:80%\">\n";
echo "<tr><th><div style=\"padding-left:10px; font-size:200%; font-weight:bold\">" . $string['performsummary'] . "</div><div style=\"padding-left:10px\">$name</div></th></tr>\n";
echo '<tr><th class="bevel"></th></tr>';
echo "</table>\n<div style=\"margin:10px\">";
  
$old_calendar_year = '';
$plots_output = 0;
$col = 0;

foreach ($papers as $paper) {
  if ($paper['stats']['max_mark'] != '') {
    if ($old_calendar_year != $paper['calendar_year']) {
      echo "<h1>" . $paper['calendar_year'] . "</h1>\n";
      echo '<img src="draw_boxplot.php?part=0" width="51" height="265" alt="' . $string['scale'] . '" />';
      $col = 0;
    }
  
    if ($col == 10) {
      echo '<br /><img src="draw_boxplot.php?part=0" width="51" height="265" alt="' . $string['scale'] . '" />';
      $col = 0;
    }
  
    $q1 = $paper['stats']['q1'];
    $q2 = $paper['stats']['q2'];
    $q3 = $paper['stats']['q3'];
    $min = $paper['stats']['min_percent'];
    $max = $paper['stats']['max_percent'];
    $pass_mark = $paper['pass_mark'];
    $mark = (isset($marks[$paper['paperID']])) ? $marks[$paper['paperID']] : '';
    $exam = $paper['paper_title'];
  
    echo "<img src=\"draw_boxplot.php?exam=$exam&part=1&q1=$q1&q2=$q2&q3=$q3&min=$min&max=$max&passmark=$pass_mark&mark=$mark\" width=\"115\" height=\"265\" alt=\"" . $string['boxplot'] . "\" />";
    $plots_output++;
    $col++;
    $old_calendar_year = $paper['calendar_year'];
  }
}
if ($plots_output == 0) {
  echo "<div style=\"margin:10px\">" . $string['noresults'] . "</div>\n";
}
?>
</div>
</body>
</html>
