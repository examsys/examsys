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
 * Utility class for reference material related functionality
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class refmaterials_utils
{
    /**
     * See if a reference material ID actually exists.
     * @return true or false.
     */
    public static function refmaterials_exist($refID, $db)
    {
        $row_no = 0;

        $result = $db->prepare('SELECT id FROM reference_material WHERE id = ?');
        $result->bind_param('i', $refID);
        $result->execute();
        $result->store_result();
        $result->bind_result($id);
        $result->fetch();
        $row_no = $result->num_rows;
        $result->close();

        return $row_no > 0;
    }

    public static function check_access($userObj, $refID, $db)
    {
        $permission_granted = false;

        $result = $db->prepare('SELECT idMod FROM reference_modules WHERE refID = ?');
        $result->bind_param('i', $refID);
        $result->execute();
        $result->store_result();
        $result->bind_result($idMod);
        while ($result->fetch()) {
            if ($userObj->is_staff_user_on_module($idMod)) {
                $permission_granted = true;
            }
        }
        $result->close();

        return $permission_granted;
    }

    public static function delete($refID, $db)
    {
        // Update deleted to NOW in reference_material
        $result = $db->prepare('UPDATE reference_material SET deleted = NOW() WHERE id = ?');
        $result->bind_param('i', $refID);
        $result->execute();
        $result->close();

        // Delete any links to the reference material in papers
        $result = $db->prepare('DELETE FROM reference_papers WHERE refID = ?');
        $result->bind_param('i', $refID);
        $result->execute();
        $result->close();
    }
}
