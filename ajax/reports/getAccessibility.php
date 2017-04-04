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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';

$userID  = check_var('userID', 'GET', true, false, true);

$result = $mysqli->prepare("SELECT background, foreground, textsize, extra_time, marks_color, themecolor, labelcolor, font, unanswered, dismiss, medical, breaks FROM special_needs WHERE userID = ?");
$result->bind_param('i', $userID);
$result->execute();
$result->bind_result($background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unanswered, $dismiss, $medical, $breaks);
$result->fetch();
$result->close();

echo '<div style="padding-left:10px; font-weight:bold; color:#295AAD">' . $string['accessibility'] . '</div>';
echo '<table style="padding:10px">';
if ($extra_time != '') {
  echo "<tr><td>" . $string['extratime'] . "</td><td>$extra_time%</td></tr>";
}
if ($background != '') {
  echo "<tr><td>" . $string['backgroundcolour'] . "</td><td><div class=\"swatch\" style=\"background-color:$background\">&nbsp;</div></td></tr>";
}
if ($foreground != '') {
  echo "<tr><td>" . $string['foregroundcolour'] . "</td><td><div class=\"swatch\" style=\"background-color:$foreground\">&nbsp;</div></td></tr>";
}
if ($marks_color != '') {
  echo "<tr><td>" . $string['markscolour'] . "</td><td><div class=\"swatch\" style=\"background-color:$marks_color\">&nbsp;</div></td></tr>";
}
if ($themecolor != '') {
  echo "<tr><td>" . $string['themecolour'] . "</td><td><div class=\"swatch\" style=\"background-color:$themecolor\">&nbsp;</div></td></tr>";
}
if ($labelcolor != '') {
  echo "<tr><td>" . $string['labelcolour'] . "</td><td><div class=\"swatch\" style=\"background-color:$labelcolor\">&nbsp;</div></td></tr>";
}
if ($unanswered != '') {
  echo "<tr><td>" . $string['unansweredbackground'] . "</td><td><div class=\"swatch\" style=\"background-color:$unanswered\">&nbsp;</div></td></tr>";
}
if ($dismiss != '') {
  echo "<tr><td>" . $string['questiondismiss'] . "</td><td><div class=\"swatch\" style=\"background-color:$dismiss\">&nbsp;</div></td></tr>";
}
if ($textsize != '') {
  echo "<tr><td>" . $string['fontsize'] . "</td><td>$textsize%</td></tr>";
}
if ($font != '') {
  echo "<tr><td>" . $string['typeface'] . "</td><td style=\"font-family:$font, sans-serif\">$font</td></tr>";
}
if ($medical != '') {
  echo "<tr><td>Medical</td><td style=\"font-family:$font, sans-serif\">$medical</td></tr>";
}
if ($breaks != '') {
  echo "<tr><td>Breaks</td><td style=\"font-family:$font, sans-serif\">$breaks</td></tr>";
}
echo '</table>';

$mysqli->close();
?>