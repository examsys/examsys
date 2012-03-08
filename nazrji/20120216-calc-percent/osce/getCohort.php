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
  header ("Content-Type:text/xml; charset=UTF-8");

  $result = $mysqli->prepare("SELECT paper_title, moduleID, calendar_year FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($paper_title, $moduleID, $calendar_year);
  $result->fetch();
  $result->close();
  
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<cohort>\n";
  echo "<paper id=\"" . $_GET['id'] . "\">\n";
  echo "<title>$paper_title</title>\n";
  echo "</paper>\n";
  
  echo "<studentlist>";
  $result = $mysqli->prepare("SELECT DISTINCT student_id, title, first_names, surname, username FROM users, sid, student_modules WHERE users.id=sid.userID AND users.id=student_modules.userID AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') AND calendar_year='$calendar_year' ORDER BY surname, initials");
  $result->execute();
  $result->bind_result($student_id, $title, $first_names, $surname, $username);
  while ($result->fetch()) {
    echo "<student id=\"" . $student_id . "\">\n";
    echo "<title>$title</title>\n";
    echo "<forenames><![CDATA[" . htmlentities($first_names, ENT_QUOTES | ENT_IGNORE, "UTF-8") . "]]></forenames>\n";
    echo "<surname><![CDATA[" . htmlentities($surname, ENT_QUOTES | ENT_IGNORE, "UTF-8") . "]]></surname>\n";
    echo "<photo>" . $cfg_web_root . "users/photos/" . $username . ".jpg</photo>\n";
    echo "</student>\n";
  }
  $result->close();
  echo "</studentlist>";
  
  echo "</cohort>\n";
?>