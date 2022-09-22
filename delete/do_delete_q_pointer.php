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
 * Remove the link between a question an a paper.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';

check_var('questionID', 'POST', true, false, false);
check_var('pID', 'POST', true, false, false);
$tmp_paperID = check_var('paperID', 'POST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($tmp_paperID, $mysqli, $string);

// Check that the paper is not summative and not locked.
if ($properties->get_summative_lock()) {
    exit;
}

$tmp_pIDs = explode(',', mb_substr($_POST['pID'], 1));
$tmp_questionIDs = explode(',', mb_substr($_POST['questionID'], 1));

for ($i = 0; $i < count($tmp_pIDs); $i++) {
    if ($result = $mysqli->prepare('DELETE FROM papers WHERE p_id = ?')) {
        $result->bind_param('i', $tmp_pIDs[$i]);
        $result->execute();
        $result->close();

        // Look up any std set IDs for the paper.
        $std_setIDs = array();
        $result = $mysqli->prepare('SELECT id FROM std_set WHERE paperID = ?');
        $result->bind_param('i', $tmp_paperID);
        $result->execute();
        $result->bind_result($id);
        while ($result->fetch()) {
            $std_setIDs[] = $id;
        }
        $result->close();

        // Delete any corresponding standard setting record for that question and paper.
        if (count($std_setIDs) > 0) {
            $sql = 'DELETE FROM std_set_questions WHERE questionID IN (' . implode(',', $tmp_questionIDs) . ') AND std_setID IN (' . implode(',', $std_setIDs) . ')';
            $result = $mysqli->prepare($sql);
            $result->execute();
            $result->close();
        }

        // Create a track changes record to say new question added.
        $logger = new Logger($mysqli);
        $logger->track_change('Paper', $tmp_paperID, $userObject->get_user_ID(), $tmp_questionIDs[$i], '', 'Delete Question');
    } else {
        display_error('Papers Delete Error', $string['showerror']);
    }
}

if ($_POST['paperID'] != '') {
    if ($result = $mysqli->prepare('UPDATE properties SET random_mark = NULL, total_mark = NULL WHERE property_id = ?')) {
        $result->bind_param('i', $tmp_paperID);
        $result->execute();
        $result->close();
    } else {
        display_error($string['updateerror'], $string['showerror']);
    }
}
$mysqli->close();

$render = new render($configObject);
$lang['title'] = $string['questiondeleted'];
$lang['success'] = $string['msg'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
