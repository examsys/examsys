<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('modules', 'GET', true, false);
?>
<table cellpadding="0" cellspacing="3" border="0" style="width:100%">
<?php
// Get the current metadata settings for the paper
$current_settings = array();
$stmt = $mysqli->prepare("SELECT refID FROM reference_papers WHERE paperID=?");
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($current_refID);
while ($stmt->fetch()) {
  $current_settings[$current_refID] = $current_refID;
}
$stmt->close();

// Get the dropdown list values
$stmt = $mysqli->prepare("SELECT DISTINCT title, reference_material.id FROM reference_material, reference_modules, modules WHERE reference_material.id=reference_modules.refID AND reference_material.deleted IS NULL AND reference_modules.moduleID=modules.id AND modules.moduleid IN ('" . str_replace(",", "','", $_GET['modules']) . "') GROUP BY reference_material.id");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($title, $refID);

$ref_line = 0;
while ($stmt->fetch()) {
  if (isset($current_settings[$refID])) {
    echo "<input type=\"checkbox\" name=\"ref$ref_line\" value=\"$refID\" checked=\"checked\" /> $title<br />";
  } else {
    echo "<input type=\"checkbox\" name=\"ref$ref_line\" value=\"$refID\" /> $title<br />";
  }
  $ref_line++;
}

$stmt->close();

$mysqli->close();
echo "</table>\n<input type=\"hidden\" name=\"reference_no\" value=\"$ref_line\" />";
?>