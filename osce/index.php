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
 * This is the homepage a member of staff logs into to take an online OSCE.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';

$paper_no = 0;
$paper_display = array();

// Get a list of OSCE stations that are live.
$result = $mysqli->prepare("SELECT crypt_name, paper_title FROM properties WHERE paper_type = '4' AND deleted IS NULL AND start_date < DATE_ADD(NOW(), interval 5 minute) AND end_date > DATE_ADD(NOW(), interval 5 minute) ORDER BY paper_title");
$result->execute();
$result->bind_result($crypt_name, $paper_title);
while ($result->fetch()) {
    $paper_display[$paper_no]['id'] = $crypt_name;
    $paper_display[$paper_no]['paper_title'] = $paper_title;
    $paper_no++;
}
$result->close();

if ($paper_no == 1) {
    // There is only one paper live, just redirect.
    header('location: ' . $configObject->get('cfg_root_path') . '/osce/class_list.php?id=' . $paper_display[0]['id']);
    exit();
} elseif ($paper_no == 0) {
    // No live OSCE stations can be found.
    $notice->display_notice_and_exit($mysqli, $string['warning'], $string['cannotfind'], $string['cannotfind'], '../artwork/exclamation_48.png', '#C00000', true, true);
} else {
    // Multiple OSCE stations are found, present a list of choices to the user.
    echo "<html>\n<head>\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$configObject->get('cfg_page_charset')}\" />\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n<title>" . $string['exams'] . "</title>\n</head>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/body.css\" />\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/osce_list.css\" />\n<body>\n";
  
    echo '<div class="title">' . $string['multiplestations'] . "</div>\n";

    echo '<p style="margin-left:10px">' . $string['pleaseselect'] . "</p>\n";
    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
    for ($i = 0; $i < $paper_no; $i++) {
        echo '<tr><td width="66" style="text-align:right"><a href="' . $configObject->get('cfg_root_path') . '/osce/class_list.php?id=' . $paper_display[$i]['id'] . "\"><img src=\"../artwork/osce.png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station\" border=\"0\" /></a></td>\n";
        echo '  <td><a href="' . $configObject->get('cfg_root_path') . '/osce/class_list.php?id=' . $paper_display[$i]['id'] . '">' . $paper_display[$i]['paper_title'] . "</a></td></tr>\n";
    }
    echo "</table>\n";
}

$mysqli->close();
?>
</body>
</html>
