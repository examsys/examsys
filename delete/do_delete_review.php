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
 * Delete a standards setting review.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';

$std_setID = check_var('std_setID', 'POST', true, false, true);

$row_no = 0;
$result = $mysqli->prepare('SELECT id FROM std_set WHERE id = ?');
$result->bind_param('i', $std_setID);
$result->execute();
$result->store_result();
$row_no = $result->num_rows;
$result->close();

if ($row_no == 0) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Delete main std_set record.
$result = $mysqli->prepare('DELETE FROM std_set WHERE id = ?');
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Delete from sthe std_set_questions table.
$result = $mysqli->prepare('DELETE FROM std_set_questions WHERE std_setID = ?');
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Delete from ebel table.
$result = $mysqli->prepare('DELETE FROM ebel WHERE std_setID = ?');
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Delete from hofstee table.
$result = $mysqli->prepare('DELETE FROM hofstee WHERE std_setID = ?');
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Clear any dangling properties
$old_marking = '2,' . $std_setID;
$result = $mysqli->prepare("UPDATE properties SET marking = '0' WHERE marking = ?");
$result->bind_param('s', $old_marking);
$result->execute();
$result->close();

$render = new render($configObject);
$lang['title'] = $string['title'];
$lang['success'] = $string['success'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
