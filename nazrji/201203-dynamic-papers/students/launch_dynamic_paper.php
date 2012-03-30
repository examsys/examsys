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
* Display a list of the papers that are currently available to a student
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require '../include/mapping.inc';
require_once '../classes/dateutils.class.php';
require '../include/errors.inc';

check_var('module', 'GET', true, false);
check_var('session', 'GET', true, false);
check_var('objectives', 'GET', true, false);
check_var('count', 'GET', true, false);

$module = $_GET['module'];
$session = $_GET['session'];
$objectives = explode(',', $_GET['objectives']);
$count = $_GET['count'];

// Get modules
if ($stmt = $mysqli->prepare("SELECT m.fullname FROM modules m INNER JOIN student_modules sm on m.moduleID = sm.moduleid WHERE m.moduleid=? AND sm.userID = ? AND sm.calendar_year=? AND m.active = 1")) {
  $stmt->bind_param('sis', $module, $userID, $session);
  $stmt->execute();
  $stmt->bind_result($module_name);
  while ($stmt->fetch()) {
		$module_name = $module_name;
  }
}
$stmt->close();

// Clear any data from user's previous papers
if ($stmt = $mysqli->prepare("DELETE FROM log_dynamic WHERE userID=?")) {
  $stmt->bind_param('i', $userID);
  $stmt->execute();
}
$stmt->close();

$session = (isset($_REQUEST['session'])) ? $_REQUEST['session'] : $years[0];

$temp_array = array();
$questionID_list = get_mapped_questions($module, $session, $temp_array, $mysqli);

$objsBySession = getObjectives($module, $session, null, $questionID_list, $mysqli);
unset($objsBySession['none_of_the_above']);

$eligible_qns = array();
foreach ($objectives as $selected) {
  $parts = explode('_', $selected);

  if (isset($objsBySession[$module][$parts[0]])) {
    $mapped_session = $objsBySession[$module][$parts[0]];
    if (isset($mapped_session['objectives']) and is_array($mapped_session['objectives'])) {
      foreach ($mapped_session['objectives'] as $objective) {
        if ($objective['id'] == $parts[1] and isset($objective['mapped']) and is_array($objective['mapped'])) {
          $eligible_qns = array_merge($eligible_qns, $objective['mapped']);
        }
      }
    }
  }
}
$eligible_qns = array_unique($eligible_qns);
shuffle($eligible_qns);
$eligible_qns = array_slice($eligible_qns, 0, $count);
$eligible_qns = implode(',', $eligible_qns);
?>
<form id="dynamic_paper" action="../paper/start.php" method="post">
  <input type="hidden" name="module" value="<?php echo $module ?>" />
  <input type="hidden" name="session" value="<?php echo $session ?>" />
  <input type="hidden" name="dyn_questions" value="<?php echo $eligible_qns ?>" />
  <input type="submit" value="<?php echo $string['clicktostart'] ?>" />
</form>
