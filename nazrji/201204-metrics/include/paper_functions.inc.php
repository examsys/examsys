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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

function get_module_folder_details(&$module, &$folder, &$folder_name, $userroles, $teams, $paper_modules, $mysqli) {
  if ($module != '') {
    $folder = '';
    $paper_modules = explode(',',$module);
    if (count($paper_modules) > 0) {     // Paper is on multiple modules
      if (strpos($userroles,'Admin') !== false) {
        $module = $paper_modules[0];
      } else {
        for ($i=count($paper_modules)-1; $i>0; $i--) {
          if (in_array($paper_modules[$i], $teams)) {
            $module = $paper_modules[$i];
          }
        }
      }
    }
  } elseif ($folder != '') {
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();

    $module = '';
  } else {
    $module = $paper_modules[0];
    $folder = '';
  }
}
