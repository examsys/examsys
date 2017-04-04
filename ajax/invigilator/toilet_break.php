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

require '../../include/invigilator_auth.inc';
require_once '../../include/errors.php';

$userID  = check_var('userID', 'POST', true, false, true);
$paperID = check_var('paperID', 'POST', true, false, true);

// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  exit();
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
  exit();
}

ToiletBreaks::add_toilet_break($userID, $paperID, $mysqli);
    
$mysqli->close();
?>