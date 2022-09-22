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
 * Utility class student notes functions.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class StudentNotes
{
    /**
     * Return the contents of a specific student note.
     * @param int $paperID    - The paper ID we wish to look up.
     * @param int $userID     - The user ID we wish to look up
     * @param object $db    - MySQL connection
     * @return bool|array   - False if no note found, otherwise array containing its details.
     */
    public static function get_note($paperID, $userID, $db)
    {
        $result = $db->prepare("SELECT note_id, note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i') AS note_date, au.title, au.initials, au.surname, su.title, su.initials, su.surname, student_id, su.username FROM (student_notes, users au, users su) LEFT JOIN sid ON su.id = sid.userID WHERE student_notes.note_authorID = au.id AND student_notes.userID = su.id AND paper_id = ? AND student_notes.userID = ?");
        $result->bind_param('ii', $paperID, $userID);
        $result->execute();
        $result->bind_result($note_id, $note, $note_date, $author_title, $author_initials, $author_surname, $student_title, $student_initials, $student_surname, $student_id, $student_username);
        $result->store_result();
        if ($result->num_rows == 0) {
            return false;
        }
        $result->fetch();
        $result->close();

        return array('note_id' => $note_id, 'note' => $note, 'date' => $note_date, 'author_title' => $author_title, 'author_initials' => $author_initials, 'author_surname' => $author_surname, 'student_title' => $student_title, 'student_initials' => $student_initials, 'student_surname' => $student_surname, 'student_id' => $student_id, 'student_username' => $student_username);
    }

    /**
     * Creates a new student note record.
     * @param int $student_userID - The user ID of the student.
     * @param string $note    - The text of the note (message).
     * @param int $paperID    - ID of the paper the note is associated with.
     * @param int $authorID   - User ID of the member of staff/invigilator creating the note.
     * @param object $db    - MySQL connection
     */
    public static function add_note($student_userID, $note, $paperID, $authorID, $db)
    {
        $result = $db->prepare('INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)');
        $result->bind_param('isii', $student_userID, $note, $paperID, $authorID);
        $result->execute();
        $result->close();
    }

    /**
     * Updates an existing student note.
     * @param string $note    - The text of the note (message).
     * @param int $note_id    - ID of note.
     * @param object $db    - MySQL connection
     */
    public static function update_note($note, $note_id, $db)
    {
        $result = $db->prepare('UPDATE student_notes SET note = ? WHERE note_id = ?');
        $result->bind_param('si', $note, $note_id);
        $result->execute();
        $result->close();
    }
}
