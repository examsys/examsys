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
 * Delete a question(s) in the question bank.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';

$qIDs = check_var('q_id', 'POST', true, false, true);
if ($qIDs[0] == ',') {
    $qIDs = mb_substr($qIDs, 1);
}

$tmp_q_ids = explode(',', $_POST['q_id']);

$result = $mysqli->prepare("SELECT DISTINCT paper_title, paper, paper_type FROM (papers, properties) WHERE papers.paper = properties.property_id AND properties.deleted IS NULL AND question IN ($qIDs)");
$result->execute();
$result->store_result();
$result->bind_result($paper_title, $paper, $paper_type);
$found = $result->num_rows;
$result->close();

if ($found == 0) {    // Only delete if the question is on zero papers.
    for ($i = 1; $i < count($tmp_q_ids); $i++) {
        $qID = $tmp_q_ids[$i];

        QuestionUtils::delete_question($qID, $mysqli);
    }
}

$render = new render($configObject);
$lang['title'] = $string['questiondeleted'];
$lang['success'] = $string['msg'];
$data = array();
$render->render($data, $lang, 'admin/do_delete.html');
