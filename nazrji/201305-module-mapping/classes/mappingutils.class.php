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

require_once 'relationship.class.php';

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
      $rels = Relationship::find($db, $idMod, $session, '', '', 1);
      if ($rels !== false and count($rels) > 0) {
        $vle_api = $rels[0]->get_vle_api();
        $map_level = $rels[0]->get_map_level();
      } else {
        // No existing relationships. Use VLE API as defined in the module
        $stmt = $db->prepare("SELECT vle_api, map_level FROM modules WHERE id=? LIMIT 1");
        $stmt->bind_param('s', $idMod);
        $stmt->execute();
        $stmt->bind_result($vle_api, $map_level);
        $stmt->fetch();
        $stmt->close();
      }

      $vle_api_data = array('api' => $vle_api, 'level' => $map_level);
      $vle_api_cache[$idMod][$session] = $vle_api_data;
    } else {
      $vle_api_data = $vle_api_cache[$idMod][$session];
    }

    return $vle_api_data;
  }
}
