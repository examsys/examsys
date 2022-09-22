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
 * Utility class for Folder related functionality
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class folder_utils
{
    /**
     * Returns the name of a folder from an ID
     *
     * @param int $folderID  - ID of the folder to be used
     * @param object $db     - MySQL object
     * @return string the name of the folder.
     */
    public static function get_folder_name($folderID, $db)
    {
        $result = $db->prepare('SELECT name FROM folders WHERE id = ? LIMIT 1');
        $result->bind_param('i', $folderID);
        $result->execute();
        $result->bind_result($name);
        $result->fetch();
        $result->close();

        return $name;
    }

    public static function has_permission($folderID, $userObj, $db)
    {
        $permission = false;

        $folder_owner = folder_utils::get_ownerID($folderID, $db);
        if ($folder_owner == $userObj->get_user_ID()) {
            return true;
        }

        $result = $db->prepare('SELECT idMod FROM folders_modules_staff WHERE folders_id = ?');
        $result->bind_param('i', $folderID);
        $result->execute();
        $result->bind_result($idMod);
        while ($result->fetch()) {
            if ($userObj->is_staff_user_on_module($idMod)) {
                $permission = true;
                break;
            }
        }
        $result->close();

        return $permission;
    }

    /**
     * Creates a new personal folder for a user.
     *
     * @param string $folder_name  - The name of the folder
     * @param object $userObj      - The userObject of the currently logged in user
     * @param object $db           - MySQL object
     * @return string the name of the folder.
     */
    public static function create_folder($folder_name, $userObj, $db)
    {
        if ($folder_query = $db->prepare("INSERT INTO folders VALUES (NULL, ?, ?, NOW(), 'yellow', NULL)")) {
            $folder_query->bind_param('is', $userObj->get_user_ID(), $folder_name);
            $folder_query->execute();
            $folder_query->close();
        } else {
            display_error('New Folder Error', $db->error);
        }
    }

    /**
     * Returns whether a personal staff folder exists or not.
     *
     * @param $folder_name The name of the folder
     * @param $folder_name - The name of the folder to be searched for.
     * @param $userObj         - Currently logged in user object.
     * @param $db                  - Mysqli object
     * @return bool                - True = folder exists, False = it does not exist.
     */
    public static function folder_exists($folder_name, $userObj, $db)
    {
        $result = $db->prepare('SELECT name FROM folders WHERE ownerID = ? AND name = ?');
        $result->bind_param('is', $userObj->get_user_ID(), $folder_name);
        $result->execute();
        $result->store_result();
        if ($result->num_rows() == 0) {
            $duplicate = false;
        } else {
            $duplicate = true;
        }
        $result->close();

        return $duplicate;
    }

    /**
     * Returns a list of all folders.
     *
     * @param object $db       - MySQL object
     * @return array   - Array of folders keyed by the ID of the folder in the database.
     */
    public static function get_all_folders($db)
    {
        $folders = array();

        $result = $db->prepare('SELECT id, name FROM folders');
        $result->execute();
        $result->bind_result($id, $name);
        while ($result->fetch()) {
            $folders[$id] = $name;
        }
        $result->close();

        return $folders;
    }

    /**
     * Returns a the userID of a folder.
     *
     * @param string $folderID - ID of the folder.
     * @param object $db       - MySQL object
     * @return int - ID of the paper owner (false if folder does not exist).
     */
    public static function get_ownerID($folderID, $db)
    {
        $result = $db->prepare('SELECT ownerID FROM folders WHERE id = ? LIMIT 1');
        $result->bind_param('i', $folderID);
        $result->execute();
        $result->bind_result($ownerID);
        $result->store_result();
        $result->fetch();
        if ($result->num_rows == 0) {
            $ownerID = false;
        }
        $result->close();

        return $ownerID;
    }

    /**
     * Returns a list of all parents for the current folder. Used to make
     * a breadcrumb trail at the top of the screen.
     *
     * @param string $orig_folder_name - Name of the current folder.
     * @param object $userObj          - Currently logged in user.
     * @param object $db               - MySQL object
     * @return array   - Array of parents of the current folder.
     */
    public static function get_parent_list($orig_folder_name, $userObj, $db)
    {
        $parent_list = array();
        if (mb_substr_count($orig_folder_name, ';') > 0) {
            $last_semicolon = mb_strrpos($orig_folder_name, ';');
            $path = mb_substr($orig_folder_name, 0, $last_semicolon);
            $parts = explode(';', $path);
            $part_sql = '';
            foreach ($parts as $part) {
                if ($part_sql == '') {
                    $part_sql = $part;
                } else {
                    $part_sql .= ';' . $part;
                }
                $parent_results = $db->prepare('SELECT id, name FROM folders WHERE name = ? AND ownerID = ? LIMIT 1');
                $parent_results->bind_param('si', $part_sql, $userObj->get_user_ID());
                $parent_results->execute();
                $parent_results->bind_result($parent_id, $parent_name);
                $parent_results->fetch();
                $parent_results->close();

                $parent_list[$parent_id] = $parent_name;
            }
        }

        return $parent_list;
    }
}
