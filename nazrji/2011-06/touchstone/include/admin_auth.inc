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
* Authentication routine which permits Admin only access.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
  require_once 'staff_student_auth.inc';

  if(strpos($userroles,'Admin') === false) {
    Header("WWW-authenticate: basic realm=\"TouchStone\"");
    Header("HTTP/1.0 401 Unauthorised");
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\"><strong>Access Denied</strong><br />you are attempting to access a system admin only screen.</p>\n</body>\n</html>";
    exit;
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