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
* Authentication routine which permits sysAdmin only access.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once 'staff_student_auth.inc';
  
if (strpos($userroles,'SysAdmin') === false) {
  access_denied($string['accessdeniedsysadmin'], true);
} 

// Get teams.
$teams = array();
$result = $mysqli->prepare("SELECT name FROM teams WHERE memberID=? ORDER BY name");
$result->bind_param('i', $userID);
$result->execute();
$result->bind_result($team_name);
while ($result->fetch()) {
  $teams[] = $team_name;
}
$result->close();

$types_array = array('Formative Self-Assessments', 'Progress Tests', 'Summative Exams', 'Surveys', 'OSCE Stations', 'Offline Papers', 'Peer Review');
?>