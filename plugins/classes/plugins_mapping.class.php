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
* Mapping plugin functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

namespace plugins;

/**
 * Abstract mapping class.
 * 
 * This class should be extend by classes used define mapping plugins.
 */
abstract class plugins_mapping extends \plugins\plugins {
    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type = 'mapping';
    /**
     * Do mapping
     * Get mapping plugin and map
     * @param mysqli $db db object
     * @param string $type type of mapping
     * @param string $source variable to map
     * @return string $target mapped variable
     */
    static public function do_mapping($db, $source) {
        $mappingplugin_name = \plugin_manager::get_plugin_type_enabled('plugin_mapping');
        // Only one mapping plugin should be enabeld at anyone time so the array 
        // returned by get_plugin_type_enabled should only be of length 1.
        if (count($mappingplugin_name) > 0) {
            $mappingplugin_name = $mappingplugin_name[0];
            $mappingplugin_object = 'plugins\\mapping\\' . $mappingplugin_name . '\\' . $mappingplugin_name;
            $mapping = new $mappingplugin_object($db);
            return $mapping->get_mapping($source);
        } else {
            return $source;
        }
    }
    /**
     * Get mapping.
     * @param string $source
     * @return string source if mapping not found, target of mapping otherwise
     */
    abstract public function get_mapping($source);
}