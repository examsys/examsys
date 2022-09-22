<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

function stripTrainModule($module_string)
{
    $new_modules = array();
    $old_modules = explode(',', $module_string);
    foreach ($old_modules as $old_module) {
        if ($old_module != 'TRAIN') {
            $new_modules[] = $old_module;
        }
    }
    return implode(',', $new_modules);
}

// get the id of the TRAIN module
$trainIdMod = module_utils::get_idMod('TRAIN', $mysqli);

// Clear the TRAIN team
$update = $mysqli->prepare('DELETE FROM modules_staff WHERE idMod = ?');
$update->bind_param('i', $trainIdMod);
$update->execute();
$update->close();

// Get all the papers on the TRAIN team
$result = $mysqli->prepare('SELECT properties.property_id FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND idMod = ?');
$result->bind_param('i', $trainIdMod);
$result->execute();
$result->store_result();
$result->bind_result($paperID);
while ($result->fetch()) {
    Paper_utils::remove_modules(array($trainIdMod => 'TRAIN'), $paperID, $mysqli, $userObject);

    $q_result = $mysqli->prepare('SELECT question FROM papers WHERE paper=?');
    $q_result->bind_param('i', $paperID);
    $q_result->execute();
    $q_result->store_result();
    $q_result->bind_result($questionID);
    while ($q_result->fetch()) {
        // Check the question isn't used on any other papers
        $check = $mysqli->prepare('SELECT question FROM papers WHERE question=?');
        $check->bind_param('i', $questionID);
        $check->execute();
        $check->store_result();
        $check->bind_result($questionID);
        $check->fetch();
        if ($check->num_rows == 1) {
            //delete the question its only on 1 training paper
            QuestionUtils::delete_question($questionID, $mysqli);
        } else {
            //remove from the TRAIN module dont delete ;-) its used elsewhere
            QuestionUtils::remove_modules(array($trainIdMod => 'TRAIN'), $questionID, $mysqli);
        }
        $check->close();
    }
    $q_result->close();
    //delete the paper if it is not on any other modules
    $tmp_paper_modules = Paper_utils::get_modules($paperID, $mysqli);
    if (count($tmp_paper_modules) == 0) {
        Paper_utils::delete_paper($paperID, -1, $mysqli);
    }
}
$result->close();

$mysqli->close();
header('location: index.php');
