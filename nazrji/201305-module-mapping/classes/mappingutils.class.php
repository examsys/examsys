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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class MappingUtils {
  /**
   * Get the VLE API that is in effect for the given module and academic year
   * either from the module itself or from existing relationships
   * @param  integer $idMod         ID of the module
   * @param  string  $session       Calendar year in the form YYYY/YY (e.g. 2012/13)
   * @param  array   $vle_api_cache List of chached API references
   * @param  mysqli  $db            DB link
   * @return string                 Name of the VLE API that is in effect
   */
  public static function get_vle_api($idMod, $session, &$vle_api_cache, $db) {
    if (!isset($vle_api_cache[$idMod][$session])) {
      // Are there any existing relationships for the module in this session?
      $stmt = $db->prepare("SELECT vle_api FROM relationships WHERE idMod IN (" . $idMod . ") AND calendar_year = ? LIMIT 1");
      $stmt->bind_param('s', $session);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $stmt->bind_result($vle_api);
        $stmt->fetch();
        $stmt->close();
      } else {
        // No existing relationships. Use VLE API as defined in the module
        $stmt = $db->prepare("SELECT vle_api FROM modules WHERE id=? LIMIT 1");
        $stmt->bind_param('s', $idMod);
        $stmt->execute();
        $stmt->bind_result($vle_api);
        $stmt->fetch();
        $stmt->close();
      }

      $vle_api_cache[$idMod][$session] = $vle_api;
    } else {
      $vle_api = $vle_api_cache[$idMod][$session];
    }

    return $vle_api;
  }

  /**
   * Get the mapping levels (e.g. session or module) of a list of modules
   * @param  array $modules Array of modules in the form array(<idMod> => <module code>)
   * @return array          Array of idMods with mapping level for the module
   */
  public static function get_mapping_levels($modules, $db) {
    // Get the mapping levels of the modules
    $mod_in = (is_array($modules)) ? implode(',', array_keys($module_list)) : $modules;
    $sql = "SELECT id, map_level FROM modules WHERE id IN ($mod_in)";
    $stmt = $db->prepare($sql);
    $stmt->bind_result($lvl_idMod, $map_level);
    $stmt->execute();
    while ($stmt->fetch()) {
      $map_levels[$lvl_idMod] = $map_level;
    }

    return $map_levels;
  }
}
