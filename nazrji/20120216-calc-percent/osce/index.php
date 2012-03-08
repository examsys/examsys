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

require '../include/staff_auth.inc';

$paper_no = 0;
$paper_display = array();

$result = $mysqli->prepare("SELECT property_id, paper_title FROM properties WHERE paper_type='4' AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 5 minute) AND end_date > DATE_ADD(NOW(),interval 5 minute) ORDER BY paper_title");
$result->execute();
$result->bind_result($property_id, $paper_title);
while ($result->fetch()) {
  $paper_display[$paper_no]['id'] = $property_id;
  $paper_display[$paper_no]['paper_title'] = $paper_title;
  $paper_no++;
}
$result->close();

if ($paper_no == 1) {
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/osce/class_list.php?paperID=" . $paper_display[0]['id']);
} elseif ($paper_no == 0) {
  echo "<html>\n<head>\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$cfg_page_charset}\" />\n\t<title>" . $string['exams'] . "</title>\n<style type=\"text/css\">\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#4465A2; font-size:140%}\n</style>\n</head>\n<body>\n";
  echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"../artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:60px\">" . $string['cannotfind'] . "</h1>\n";
  echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n";
  exit;
} else {
  echo "<html>\n<head>\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$cfg_page_charset}\" />\n\t<title>" . $string['exams'] . "</title>\n</head>\n<body style=\"font-family:Arial,sans-serif\">\n";
  echo "<h1>" . $string['multiplestations'] . "</h1>\n";
  echo "<p><em>" . $string['pleaseselect'] . "</em></p>\n";
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
  for ($i=0; $i<$paper_no; $i++) {
    echo "<tr><td width=\"66\" style=\"text-align:right\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/osce/class_list.php?osceID=" . $paper_display[$i]['id'] . "\"><img src=\"../artwork/osce" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: OSCE Station\" border=\"0\" /></a></td>\n";
    echo "  <td><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/osce/class_list.php?paperID=" . $paper_display[$i]['id'] . "\" style=\"color:blue\">" . $paper_display[$i]['paper_title'] . "</a></td></tr>\n";
  }
  echo "</table>\n";
}

$mysqli->close();
?>
</body>
</html>
