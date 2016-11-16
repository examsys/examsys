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
* Rogo version helper functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Version class
 */
class version {
    /**
     * Rogo version format
     * @var string
     */
    const VERSION_FORMAT = "/^(?P<release>[0-9]{1,3}).(?P<major>[0-9]{1,3}).(?P<minor>[0-9]{1,3})$/";
    /**
     * Check if version is higher.
     * @param string $new new version to check
     * @param string $old old version to check
     * @return boolean true new version higher than old, false otherwise
     */
    static public function is_version_higher($new, $old) {
        preg_match(self::VERSION_FORMAT, $new, $newarray);
        preg_match(self::VERSION_FORMAT, $old, $oldarray);
        if (count($newarray) > 0 and count($oldarray) > 0 ) {
            if ($newarray['release'] > $oldarray['release']) {
                // Higher release number.
                return true;
            } elseif ($newarray['release'] == $oldarray['release']) {
                // Release number matches.
                if ($newarray['major'] > $oldarray['major']) {
                    // Higher major number.
                    return true;
                } elseif ($newarray['major'] == $oldarray['major']) {
                    // Major number matches.
                    if ($newarray['minor'] > $oldarray['minor']) {
                        // Higher minor number.
                        return true;
                    }
                }
            }
        }
        // Old version is higher.
        return false;
    }
    /**
     * Check version is in correct format.
     * @param string $version to check
     * @return boolean true if correct, false otherwise
     */
    static public function check_version_format($version) {
        return preg_match(self::VERSION_FORMAT,$version);
    }
    /**
     * Sort list of versions into ascending order
     * @param array list of versions
     * @return array sorted list of versions
     */
    static public function sort_version($fileversion) {
        $unsorted = array();
        $sorted = array();
        // Filter the relase, major and minor numbers.
        foreach ($fileversion as $version) {
            preg_match(self::VERSION_FORMAT, $version, $filtered);
            $unsorted[] = $filtered;
            $release[] = $filtered['release'];
            $major[] = $filtered['major'];
            $minor[] = $filtered['minor'];
        }
        // Multi sort each array.
        array_multisort($release, SORT_ASC, $major, SORT_ASC, $minor, SORT_ASC, $unsorted);
        // Store the full version in the correct order.
        foreach ($unsorted as $key => $value) {
            $sorted[] = $value[0];
        }
        return $sorted;
    }
}