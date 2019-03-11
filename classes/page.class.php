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
 * Page package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 */

/**
 * Page helper class.
 */
class page {
  /**
   * Generates page title given access/install type.
   * @param string $pagetitle the descriptive page title
   * @return string
   */
  public static function title($pagetitle) {
    $configObject  = Config::get_instance();
    $userObject = UserObject::get_instance();
    if (!is_null($configObject->db)) {
      // Install type.
      $type = $configObject->get_setting('core', 'system_install_type');
      if (!is_null($type)) {
        $pagetitle .= " " . $type;
      }
    }
    // Checks for logged in users.
    if (!is_null($userObject)) {
      // Demo mode.
      if ($userObject->is_demo()) {
        $langpack = new langpack();
        $pagetitle .= " (" . $langpack->get_string('classes/page', 'demomode') . ")";
      }
      // Impersonated user.
      if ($userObject->is_impersonated()) {
        $langpack = new langpack();
        $pagetitle .= " " . $langpack->get_string('classes/page', 'as') . " " . $userObject->get_title() . " " . $userObject->get_surname();
      }
    }
    return $pagetitle;
  }
}
