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
 * Edit the keyword
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require '../include/errors.php';

$new_keyword = html_entity_decode(param::required('new_keyword', param::TEXT, param::FETCH_POST));
$keywordID = param::required('keywordID', param::INT, param::FETCH_POST);
$module = param::optional('module', '', param::INT, param::FETCH_REQUEST);

if ($module == '') {
    $type = 'personal';
    $owner = $userObject->get_user_ID();
} else {
    $type = 'team';
    $owner = $module;
}
$exists = false;

$result = $mysqli->prepare('SELECT NULL FROM keywords_user WHERE keyword = ? AND userID = ? AND keyword_type = ? and id != ?');
$result->bind_param('sisi', $new_keyword, $owner, $type, $keywordID);
$result->execute();
$result->store_result();
if ($result->num_rows > 0) {
    $exists = true;
}
$result->close();
if (!$exists) {
    $result = $mysqli->prepare('UPDATE keywords_user SET keyword = ? WHERE id = ?');
    $result->bind_param('si', $new_keyword, $keywordID);
    $result->execute();
    $result->close();
} else {
    echo json_encode('DUPLICATE');
    exit();
}

echo json_encode('SUCCESS');
