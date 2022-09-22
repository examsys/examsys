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
 * Delete a team or personal keyword.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';
$keywordIDs = check_var('keywordID', 'POST', true, false, true);
$keyword_names = array();
$result = $mysqli->prepare('SELECT keyword FROM keywords_user WHERE id IN (' . mb_substr($keywordIDs, 1) . ')');
$result->execute();
$result->bind_result($keyword);
while ($result->fetch()) {
    $keyword_names[] = $keyword;
}
$result->close();
if (count($keyword_names) < mb_substr_count($keywordIDs, ',')) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}


$keyword_list = explode(',', mb_substr($keywordIDs, 1));
foreach ($keyword_list as $individualID) {
    // Delete the keyword
    $result = $mysqli->prepare('DELETE FROM keywords_user WHERE id = ?');
    $result->bind_param('i', $individualID);
    $result->execute();
    $result->close();
    // Remove the deleted keyword from questions
    $result = $mysqli->prepare('DELETE FROM keywords_question WHERE keywordID = ?');
    $result->bind_param('i', $individualID);
    $result->execute();
    $result->close();
}

$render = new render($configObject);
$lang['title'] = $string['title'];
$lang['success'] = $string['msg'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
