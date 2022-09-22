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
 * Utility class paper notes functions.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class PaperNotes
{
    /**
     * Creates a list of students who have notes against a particular paper.
     * @param int $paperID    - ID of the paper we wish to look up.
     * @param object $db    - MySQL connection
     * @return array
     */
    public static function get_all_notes_by_paper($paperID, $db)
    {
        $notes = array();
        // Query any student notes for the current paper
        $result = $db->prepare('SELECT userID FROM student_notes WHERE paper_id = ?');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->bind_result($userID);
        while ($result->fetch()) {
            $notes[$userID] = 'y';
        }
        $result->close();

        return $notes;
    }

    /**
     * Retrieves a note based on a paper and IP address.
     * @param int $paperID    - ID of the paper we wish to look up.
     * @param string $address - The IP address of the workstation where the note was made.
     * @param object $db      - MySQL connection
     * @return array          - Array containing the id and text of a paper note.
     */
    public static function get_note($paperID, $address, $db)
    {
        $result = $db->prepare('SELECT note_id, note FROM paper_notes WHERE paper_id = ? AND note_workstation = ?');
        $result->bind_param('is', $paperID, $address);
        $result->execute();
        $result->bind_result($note_id, $note);
        $result->fetch();
        $result->close();

        return array('note_id' => $note_id, 'note' => $note);
    }

    /**
     * Adds a new paper note.
     * @param string $note    - The text of the note (message).
     * @param int $paperID    - ID of the paper the note is associated with.
     * @param int $authorID   - User ID of the member of staff/invigilator creating the note.
     * @param object $db      - MySQL connection
     */
    public static function add_note($note, $paperID, $authorID, $db)
    {
        $current_address = NetworkUtils::get_client_address();

        $result = $db->prepare('INSERT INTO paper_notes VALUES (NULL, ?, NOW(), ?, ?, ?)');
        $result->bind_param('siis', $note, $paperID, $authorID, $current_address);
        $result->execute();
        $result->close();
    }

    /**
     * Updates an existing paper note.
     * @param string $note    - The text of the note (message).
     * @param int $note_id    - ID of the paper note.
     * @param object $db      - MySQL connection
     */
    public static function update_note($note, $note_id, $db)
    {
        $result = $db->prepare('UPDATE paper_notes SET note = ? WHERE note_id = ?');
        $result->bind_param('si', $note, $note_id);
        $result->execute();
        $result->close();
    }
}
