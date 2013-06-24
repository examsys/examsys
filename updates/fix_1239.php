<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

$result = $mysqli->prepare("SELECT question_exclude.id, question_exclude.q_id, COUNT(o_id), q_type FROM questions, question_exclude, options WHERE question_exclude.q_id = questions.q_id AND (q_type = 'mrq' OR q_type = 'rank') AND questions.q_id = options.o_id GROUP BY questions.q_id");
$result->execute();
$result->bind_result($id, $q_id, $option_no, $q_type);
$result->store_result();
while ($result->fetch()) {

  $parts = str_repeat('1', $option_no);
  
  if ($q_type == 'rank') {
    $parts .= '1';  // Add an extra 1 for bonus marks.
  }

  $update = $mysqli->prepare("UPDATE question_exclude SET parts = ? WHERE id = ?");
  $update->bind_param('si', $parts, $id);
  $update->execute();
  $update->close();

}
$result->close();

$mysqli->close();

?>