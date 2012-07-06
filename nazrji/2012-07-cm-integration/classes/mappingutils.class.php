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
 * Helper functions related to Mapping objectives
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

class MappingUtils {
  public static function get_vle_api($module_id, $session, &$vle_api_cache, $rogodb) {

    if(!isset($vle_api_cache[$module_id])) {
      // Are there any existing relationships for the module in this session?
      $stmt = $rogodb->prepare("SELECT vle_api FROM relationships WHERE module_id LIKE ? AND calendar_year=? LIMIT 1");
      $mod_like = '%' . $module_id . '%';
      $stmt->bind_param('ss', $mod_like, $session);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $stmt->bind_result($vle_api);
        $stmt->fetch();
        $stmt->close();
      } else {
        // No existing relationships. Use VLE API as defined in the module
        $stmt = $rogodb->prepare("SELECT vle_api FROM modules WHERE moduleid=? LIMIT 1");
        $stmt->bind_param('s', $module_id);
        $stmt->execute();
        $stmt->bind_result($vle_api);
        $stmt->fetch();
        $stmt->close();
      }

      $vle_api_cache[$module_id] = $vle_api;
    } else {
      $vle_api = $vle_api_cache[$module_id];
    }

    return $vle_api;
  }
}
