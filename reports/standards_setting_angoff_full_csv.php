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
* @author Richard Whitefoot (UEA)
* @version 1.0
* @copyright Copyright (c) 2015
* @package
*/

require '../include/staff_auth.inc';
require '../include/media.inc';
require '../include/std_set_functions.inc';
require_once '../include/errors.inc';
require_once '../classes/exclusion.class.php';
require_once '../classes/paperproperties.class.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Get some properties of the paper.
$paper_title    = $propertyObj->get_paper_title();
$paper_type     = $propertyObj->get_paper_type();
$paper_prologue = $propertyObj->get_paper_prologue();

// Get any questions to exclude.
$exclusions = new Exclusion($paperID, $mysqli);
$exclusions->load();

  $stmt = $mysqli->prepare("SELECT ssq.rating, ss.setterID, ss.method, u.title, u.initials, u.surname, p.display_pos FROM papers p INNER JOIN questions q ON p.question=q.q_id LEFT JOIN std_set_questions ssq ON p.question=ssq.questionID LEFT JOIN std_set ss ON ssq.std_setID=ss.id LEFT JOIN users u ON ss.setterID=u.id WHERE p.paper = '" . $paperID . "' ORDER BY p.display_pos");


  $stmt->execute();
  $stmt->bind_result($rating, $setter_id, $method, $title, $initials, $surname, $display_pos);

  echo "Question Number,Theme,Method,Standard Setter,Rating\n";
  echo "<br>";
  while ($stmt->fetch()) {

    // @TODO, see what exclusions code does?
    #$excluded = $exclusions->get_exclusions_by_qid($old_q_id);

    // @TODO: Check sence of rating output
    
    // @TODO: Lang files
    
    echo $display_pos . ",theme," . $method . "," . $title . " " . $initials . " " . $surname . "," . $rating;

    echo "<br>";
  }
  $stmt->close();

$mysqli->close();
?>