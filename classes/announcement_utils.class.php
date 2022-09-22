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
 * Utility class for announcement related functionality.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class announcement_utils
{
    /**
     * See if an announcement ID actually exists.
     * @param int $announcementID - The ID of the announcement to be located.
     * @param object $db          - Link to mysqli
     *
     * @return true or false.
     */
    public static function announcement_exist($announcementID, $db)
    {
        $row_no = 0;

        $result = $db->prepare('SELECT id FROM announcements WHERE id = ?');
        $result->bind_param('i', $announcementID);
        $result->execute();
        $result->store_result();
        $result->bind_result($id);
        $result->fetch();
        $row_no = $result->num_rows;
        $result->close();

        return $row_no > 0;
    }

    /**
     * Sets a mid-exam announcement to deleted.
     * @param int $announcementID - The ID of the announcement to be deleted.
     * @param object $db          - Link to mysqli
     */
    public static function delete($announcementID, $db)
    {
        $result = $db->prepare('UPDATE announcements SET deleted = NOW() WHERE id = ?');
        $result->bind_param('i', $announcementID);
        $result->execute();
        $result->close();
    }

    /**
     * Gets a list of staff announcements that are live.
     * @param object $db  - Database connection
     * @return array      - List of announcements
     */
    public static function get_staff_announcements($db)
    {
        $announcements = array();
        $icons = array('', 'news_64.png', 'new_64.png', 'tip_64.png', 'software_64.png', 'exclamation_64.png', 'sync_64.png', 'megaphone_64.png');

        $result = $db->prepare("SELECT id, title, staff_msg, icon FROM announcements WHERE NOW() > startdate AND NOW() < enddate AND staff_msg != '' AND deleted IS NULL");
        $result->execute();
        $result->bind_result($announcementID, $news_title, $staff_msg, $icon);
        while ($result->fetch()) {
            $announcements[] = array('id' => $announcementID, 'title' => $news_title, 'msg' => $staff_msg, 'icon' => $icons[$icon]);
        }
        $result->close();

        return $announcements;
    }

    /**
     * Gets a list of student announcements that are live.
     * @param object $db  - Database connection
     * @return array      - List of announcements
     */
    public static function get_student_announcements($db)
    {
        $announcements = array();
        $icons = array('', 'news_64.png', 'new_64.png', 'tip_64.png', 'software_64.png', 'exclamation_64.png', 'sync_64.png', 'megaphone_64.png');

        $result = $db->prepare("SELECT id, title, student_msg, icon FROM announcements WHERE NOW() > startdate AND NOW() < enddate AND student_msg != '' AND deleted IS NULL");
        $result->execute();
        $result->bind_result($announcementID, $news_title, $student_msg, $icon);
        while ($result->fetch()) {
            $announcements[] = array('id' => $announcementID, 'title' => $news_title, 'msg' => $student_msg, 'icon' => $icons[$icon]);
        }
        $result->close();

        return $announcements;
    }
}
