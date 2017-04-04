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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../../include/invigilator_auth.inc';
require_once '../../include/errors.php';
require_once '../../include/invigilator_common.inc';

$paperID = check_var('paperID', 'GET', true, false, true);

$current_address = NetworkUtils::get_client_address();

$lab = new LabFactory($mysqli);

$lab_object = $lab->get_lab_based_on_client($current_address);
$lab_id = $lab_object->get_id();
$room_name = $lab_object->get_name();

$properties_list = array();

$properties_list = PaperProperties::get_paper_properties_by_lab($lab_object, $mysqli);

foreach ($properties_list as $property_object) {
	if ($property_object->get_property_id() == $paperID) {
    // Get modules for this paper and check if timing is allowed
    $timed_modules = $all_modules = 0;
    $sql = 'SELECT m.id, m.timed_exams FROM properties_modules pm INNER JOIN modules m ON pm.idMod = m.id WHERE pm.property_id = ?';

    $module_results = $mysqli->prepare($sql);
    $module_results->bind_param('i', $paperID);
    $module_results->execute();
    $module_results->store_result();
    $module_results->bind_result($moduleID, $timed_exams);

    $modules = array();

    while ($module_results->fetch()) {
      $modules[] = $moduleID;
      $all_modules++;
      if ($timed_exams == true) {
        $timed_modules++;
      }
    }

    $allow_timing = ($timed_modules == $all_modules);
    $log_lab_end_time = new LogLabEndTime($lab_object->get_id(), $property_object, $mysqli);

    $modules = implode('\',\'', $modules);
    $modules = '\'' . $modules . '\'';
        
    get_students($modules, $property_object, $log_lab_end_time, $allow_timing, $string, $mysqli);
  }
}

$mysqli->close();
?>