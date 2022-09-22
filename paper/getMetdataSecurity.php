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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_student_auth.inc';
require '../include/errors.php';
$paperID = check_var('paperID', 'GET', true, false, true);
?>
<table cellpadding="0" cellspacing="3" border="0" style="width:100%">
<?php
// Get the current metadata settings for the paper
$current_settings = array();
$stmt = $mysqli->prepare('SELECT name, value FROM paper_metadata_security WHERE paperID = ?');
$stmt->bind_param('i', $paperID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($type, $value);
while ($stmt->fetch()) {
    $current_settings[$type] = $value;
}
$stmt->close();
$old_type = '';
$meta_no = 0;
if ($_GET['session'] != '') {
    $sql_session = "AND calendar_year='" . $_GET['session'] . "'";
} else {
    $sql_session = '';
}

// Get the dropdown list values
if ($_GET['modules'] != '') {
    $stmt = $mysqli->prepare('SELECT DISTINCT type, value FROM users_metadata, modules WHERE modules.id = users_metadata.idMod AND modules.id IN (' . $_GET['modules'] . ") $sql_session GROUP BY value, type ORDER BY type, value");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($type, $value);
    while ($stmt->fetch()) {
        if ($old_type != $type) {
            if ($old_type != '') {
                echo '</select></td></tr>';
            }
            echo "<tr><td>$type</td><td><input type=\"hidden\" name=\"meta_type$meta_no\" value=\"$type\" /><select name=\"meta_value$meta_no\">\n<option value=\"\">&lt;any&gt;</option>";
            $meta_no++;
        }
        if (isset($current_settings[$type]) and $current_settings[$type] == $value) {
            echo "<option value=\"$value\" selected>$value</option>\n";
        } else {
            echo "<option value=\"$value\">$value</option>\n";
        }
        $old_type = $type;
    }
    if ($old_type != '') {
        echo '</select></td></tr>';
    }

    $stmt->close();
}

$mysqli->close();
echo "</table>\n<input type=\"hidden\" id=\"meta_dropdown_no\" name=\"meta_dropdown_no\" value=\"$meta_no\" data-loaded='0'/>";
