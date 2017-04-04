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

$breakID  = check_var('breakID', 'GET', true, false, true);

$details = ToiletBreaks::toilet_break_by_id($breakID, $mysqli);

if ($details === false) {
  echo "<div style=\"padding:10px\">" . $string['err'] . "</div>\n";
} else {
  echo "<div style=\"padding:10px\">" . $string['toiletbreak'] . "</div>\n";
  echo "<div style=\"padding:10px\">" . $details . "</div>\n";
}

$mysqli->close();
?>