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
 * Restores an item in the recycle bin (i.e. set the deleted time/date to NULL).
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/errors.php';
check_var('item_id', 'GET', true, false, false);
$items = explode(',', $_GET['item_id']);

for (
    $i = 0; $i < count($items); $i++
) {
    $type = mb_substr($items[$i], 0, 1);
    $item_id = mb_substr($items[$i], 1);
    if ($type == 'p') {
        // Papers
          // Get the paper title of the restored paper.
          $result = $mysqli->prepare('SELECT paper_title FROM properties WHERE property_id = ?');
        $result->bind_param('i', $item_id);
        $result->execute();
        $result->bind_result($deleted_paper_title);
        $result->fetch();
        $result->close();
        // Check to see if the original paper name has been reused by any active papers.
          $split_title = explode('[deleted', $deleted_paper_title);
        $tmp_title = trim($split_title[0]);
        $result = $mysqli->prepare('SELECT paper_title FROM properties WHERE paper_title = ? and property_id != ?');
        $result->bind_param('si', $tmp_title, $item_id);
        $result->execute();
        $result->store_result();
        $result->bind_result($paper_title);
        $result->fetch();
        if ($result->num_rows == 0) {
            $new_title = trim($split_title[0]);
        } else {
            $new_title = $deleted_paper_title;
        }
        $result->close();
        $assessment = new assessment($mysqli, $configObject);
        $update_params = array(
        'deleted' => array('s', null),
        'paper_title' => array('s', $new_title)
        );
        $assessment->db_update_assessment($item_id, $update_params);
        $result = $mysqli->prepare('SELECT question, deleted FROM (papers, questions) WHERE paper = ? AND papers.question = questions.q_id');
        $result->bind_param('i', $item_id);
        $result->execute();
        $result->store_result();
        $result->bind_result($question, $deleted);
        while ($result->fetch()) {
            if ($deleted != '') {
                // If the question has been deleted in the question bank then remove from the paper.
                  $deleteQuery = $mysqli->prepare('DELETE FROM papers WHERE paper = ? AND question = ?');
                $deleteQuery->bind_param('ii', $item_id, $question);
                $deleteQuery->execute();
                $deleteQuery->close();
            }
        }
    } elseif ($type == 'f') {
        // Folders
          // Get the name of the restored folder;
          $result = $mysqli->prepare('SELECT name FROM folders WHERE id = ?');
        $result->bind_param('i', $item_id);
        $result->execute();
        $result->bind_result($deleted_folder_title);
        $result->fetch();
        $result->close();
        // Check to see if the original folder name has been reused.
          $split_title = explode('[deleted', $deleted_folder_title);
        $tmp_title = trim($split_title[0]);
        $result = $mysqli->prepare('SELECT name FROM folders WHERE name = ? and id != ?');
        $result->bind_param('si', $tmp_title, $item_id);
        $result->execute();
        $result->store_result();
        $result->bind_result($folder_title);
        $result->fetch();
        if ($result->num_rows == 0) {
            $new_title = trim($split_title[0]);
        } else {
            $new_title = $deleted_folder_title;
        }
        $result->close();
        $restore = $mysqli->prepare('UPDATE folders SET deleted = NULL, name = ? WHERE id = ?');
        $restore->bind_param('si', $new_title, $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 'a') {
        // Academic Session
          $restore = $mysqli->prepare('UPDATE academic_year SET deleted = NULL, deletedby = NULL WHERE calendar_year = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 'q') {
        // Questions
          $restore = $mysqli->prepare('UPDATE questions SET deleted = NULL WHERE q_id = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 'm') {
        // Modules
          $restore = $mysqli->prepare('UPDATE modules SET mod_deleted = NULL WHERE id = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 'c') {
        // Courses
          $restore = $mysqli->prepare('UPDATE courses SET deleted = NULL WHERE id = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 's') {
        // schools
          $restore = $mysqli->prepare('UPDATE schools SET deleted = NULL WHERE id = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    } elseif ($type == 'u') {
        // schools
          $restore = $mysqli->prepare('UPDATE faculty SET deleted = NULL WHERE id = ?');
        $restore->bind_param('i', $item_id);
        $restore->execute();
        $restore->close();
    }
}
$mysqli->close();
header('location: ./recycle_list.php');
