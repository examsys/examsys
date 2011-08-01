<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Authentication routine which permits sysAdmin only access.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once 'staff_student_auth.inc';
  
  if (strpos($userroles,'SysAdmin') === false) {
    access_denied('<strong>Access Denied</strong><br />you are attempting to access a system admin only screen.',true);
  } 

  // Get teams.
  $teams = array();
  $team_details = $mysqli->query("SELECT name FROM teams WHERE memberID='$userID' ORDER BY name");
  while ($team_row = $team_details->fetch_assoc()) {
    $teams[] = $team_row['name'];
  }
  $team_details->close();

  $types_array = array('Formative Self-Assessments','Progress Tests','Summative Exams','Surveys','OSCE Stations','Spotters');
?>