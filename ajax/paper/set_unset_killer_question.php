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
*	Work out if a question is set to be a killer question or not and then
* set or unset accordingly.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';

$paperID	= check_var('paperID', 'POST', true, false, true);
$q_id			= check_var('q_id', 'POST', true, false, true);
$qNumber	= check_var('qNumber', 'POST', true, false, true);

$killer_questions = new Killer_question($paperID, $mysqli);
$killer_questions->load();			// Get the existing killer questions for the paper.

$logger = new Logger($mysqli);

if ($killer_questions->is_killer_question($q_id)) {
	$killer_questions->unset_question($q_id);
  $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'on', 'off', "killer question $qNumber");
} else {
	$killer_questions->set_question($q_id);
  $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), 'off', 'on', "killer question $qNumber");
}
$killer_questions->save();

$mysqli->close();
?>
