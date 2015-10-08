<?php
// This file is part of Rogō - http://Rogō.org/
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

namespace plugins\IMS;

/**
 * Mapping between Rogō module attributes and IMS enterprise group description tags
 *
 * @package   enrol_imsenterprise
 * @copyright 2011 Aaron C Spike
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_enterprise_modules {

  /** @var array IMS group description names */
  private $imsnames;

  /** @var array Rogō module field names */
  private $moduleattrs;

  /**
   * Loads default
   */
  public function __construct() {
    $this->imsnames = array(
      'short' => 'short',
      'long' => 'long',
      'full' => 'full',
      'modulecode' => 'modulecode');
    $this->moduleattrs = array('shortname', 'fullname', 'summary');
  }

  /**
   * moduleattrs getter
   * @return array
   */
  public function get_moduleattrs() {
    return $this->moduleattrs;
  }

  /**
   * This function is only used when first setting up the plugin, to
   * decide which name assignments to recommend by default.
   *
   * @param string $moduleattr
   * @return string
   */
  public function determine_default_modulemapping($moduleattr) {
    switch ($moduleattr) {
      case 'fullname':
        $imsname = 'short';
        break;
      case 'shortname':
        $imsname = 'modulecode';
        break;
      default:
        $imsname = 'ignore';
    }

    return $imsname;
  }

}