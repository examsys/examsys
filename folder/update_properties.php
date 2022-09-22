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
 * Update folder properties
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$folderID = check_var('folderID', 'POST', true, false, true);
$new_folder = check_var('folder', 'POST', true, false, true);

$ownerID = 0;

$result = $mysqli->prepare('SELECT ownerID FROM folders WHERE id = ?');
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($ownerID);
$result->fetch();
$result->close();

if ($ownerID != $userObject->get_user_ID()) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$unique_name = true;
$moduleno = param::optional('module_no', 0, param::INT, param::FETCH_REQUEST);
$oldprefix = param::optional('old_prefix', '', param::TEXT, param::FETCH_REQUEST);
$oldfolder = param::required('old_folder', param::TEXT, param::FETCH_REQUEST);
$color = param::required('color', param::TEXT, param::FETCH_REQUEST);

$module_array = array();
for ($i = 0; $i < $moduleno; $i++) {
    $module = param::optional('module' . $i, null, param::INT, param::FETCH_REQUEST);
    if (!is_null($module)) {
        $module_array[] = $module;
    }
}

if ($oldprefix != '') {
    $new_folder = $oldprefix . ';' . $new_folder;
}

if (mb_strtolower($new_folder) != mb_strtolower($oldfolder)) {
    $result = $mysqli->prepare('SELECT name FROM folders WHERE name = ? AND ownerID = ? LIMIT 1');
    $result->bind_param('si', $new_folder, $userObject->get_user_ID());
    $result->execute();
    $result->store_result();
    $result->bind_result($name);
    $result->fetch();
    if ($result->num_rows > 0) {
        $result->free_result();
        $result->close();
        echo json_encode('DUPLICATE');
        exit();
    } else {
        // Alter the name of the folder in the 'folders' table first.
        $editProperties = $mysqli->prepare('UPDATE folders SET name = ?, color = ? WHERE id = ? AND ownerID = ?');
        $editProperties->bind_param('sssi', $new_folder, $color, $folderID, $userObject->get_user_ID());
        $editProperties->execute();
        $editProperties->close();

        $result2 = $mysqli->prepare('UPDATE folders SET name = REPLACE(name, ? , ?) WHERE name LIKE ? AND ownerID = ?');
        $t0 = $oldfolder . ';';
        $t1 = $new_folder . ';';
        $t2 = $oldfolder . ';%';
        $result2->bind_param('sssi', $t0, $t1, $t2, $userObject->get_user_ID());
        $result2->execute();
        $result2->close();

        // Alter the prefix of any child folders.
        if ($mysqli->error) {
            echo json_encode('ERROR');
            exit();
        }

        // Next update the folder name in the 'properties' table (moves papers).
        $editProperties = $mysqli->prepare('UPDATE properties SET folder = ? WHERE folder = ? AND paper_ownerID = ?');
        $editProperties->bind_param('ssi', $new_folder, $oldfolder, $userObject->get_user_ID());
        $editProperties->execute();
        $editProperties->close();
    }
    $result->free_result();
    $result->close();
} else {
    $editProperties = $mysqli->prepare('UPDATE folders SET color = ? WHERE id = ? AND ownerID = ?');
    $editProperties->bind_param('sii', $color, $folderID, $userObject->get_user_ID());
    $editProperties->execute();
    $editProperties->close();
}

if (count($module_array) > 0) {
    //set the folder staff_modules
    $editProperties = $mysqli->prepare('DELETE FROM folders_modules_staff WHERE folders_id = ?');
    $editProperties->bind_param('i', $folderID);
    $editProperties->execute();
    $editProperties->close();

    $editProperties = $mysqli->prepare('INSERT INTO folders_modules_staff VALUES(?, ?)');
    foreach ($module_array as $idMod) {
        $editProperties->bind_param('ii', $folderID, $idMod);
        $editProperties->execute();
    }
    $editProperties->close();
}
echo json_encode('SUCCESS');
