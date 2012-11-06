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
 * Return a new object for the chose VLE API
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */


require_once $cfg_web_root . 'classes/exceptions.inc.php';

class VLEFactory {
  public static function GetVLEAPI($vleapi) {
    global $cfg_web_root;

    $classname = 'VLE_' . $vleapi;
    $classfile = 'VLE_' . $vleapi . '.class.php';

    try {
      include_once $classfile;
      $object = new $classname();
    } catch (Exception $ex) {
      throw new ClassNotFoundException(sprintf($lang_strings['noclasserror'], $classname));
    }

    return $object;
  }
}
