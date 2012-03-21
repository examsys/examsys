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
* Re-order questions based on AJAX call from drag and drop list from paper.details.php
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';

if (isset($_POST['module']) and isset($_POST['objective']) and isset($_POST['question']) and isset($_POST['session'])) {
  $module = $_POST['module'];
  $objective = $_POST['objective'];
  $question = $_POST['question'];
  $session = $_POST['session'];

  $result = $mysqli->prepare("DELETE FROM relationships WHERE module_id=? AND question_id=? AND obj_id=? AND calendar_year=? AND paper_id IS NULL");
  $result->bind_param('siis', $module, $question, $objective, $session);
  $result->execute();
  $result->close();

  print 'SUCCESS';
} else {
  print 'INVALID INPUT';
}

