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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  header ("Content-Type:text/xml");

  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<stationlist>\n";

  $result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y') FROM properties WHERE paper_type='4' AND start_date>NOW() ORDER BY paper_title");
  $result->execute();
  $result->bind_result($property_id, $paper_title, $osce_date);
  while ($result->fetch()) {
    echo "<station id=\"$property_id\">\n";
    echo "<title>$paper_title</title>\n";
    echo "<date>$osce_date</date>\n";
    echo "</station>\n";
  }
  $result->close();
  
  echo "</stationlist>\n";
?>