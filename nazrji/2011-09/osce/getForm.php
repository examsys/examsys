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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../touchstone/include/staff_auth.inc';
  header ("Content-Type:text/xml");

  $result = $mysqli->prepare("SELECT paper_title, marking FROM properties WHERE property_id=? LIMIT 1");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($paper_title, $marking);
  $result->fetch();
  $result->close();
  
  $marking_types = array(3=>'Clear Fail | Borderline | Clear Pass',4=>'Fail | Borderline fail | Borderline pass | Pass | Good pass',5=>'Automatic',6=>'Clear FAIL | BORDERLINE | Clear PASS | Honours PASS');

  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<form>\n";
  echo "<metadata>\n";
  echo "<title>$paper_title</title>\n";
  echo "<classification>" . $marking_types[$marking] . "</classification>\n";
  echo "<feedback><![CDATA[1]]></feedback>\n";
  echo "</metadata>\n";
  
  echo "<questionlist>";
  $result = $mysqli->prepare("SELECT q_id, theme, leadin, score_method FROM papers, questions WHERE papers.question=questions.q_id AND paper=? ORDER BY display_pos");
  $result->bind_param('i', $_GET['id']);
  $result->execute();
  $result->bind_result($q_id, $theme, $leadin, $score_method);
  while ($result->fetch()) {
    $score_method = str_replace('|false','',$score_method);
    $score_method = str_replace('|true','',$score_method);
    
    echo "<question id=\"$q_id\">\n";
    echo "<theme>$theme</theme>\n";
    echo "<prompt>" . trim($leadin) . "</prompt>\n";
    echo "<marking>$score_method</marking>\n";
    echo "</question>\n";
  }
  $result->close();
  echo "</questionlist>";
  
  echo "</form>\n";
?>