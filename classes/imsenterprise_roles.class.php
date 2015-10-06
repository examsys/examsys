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

/**
 * Class for dealing with role mappings in IMS Enterprise.
 *
 * @copyright  2010 Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class imsenterprise_roles {

  /** @var imscode => ims role name. Role name mapping. */
  private $imsroles;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->imsroles = array(
      '01' => 'Learner',
      '02' => 'Instructor',
      '03' => 'Content Developer',
      '04' => 'Member',
      '05' => 'Manager',
      '06' => 'Mentor',
      '07' => 'Administrator',
      '08' => 'TeachingAssistant',
    );
    // PLEASE NOTE: It may seem odd that "Content Developer" has a space in it
    // but "TeachingAssistant" doesn't. That's what the spec says though!!!
  }

  /**
   * Returns the mapped roles
   *
   * @return array of IMS roles indexed by IMS code.
   */
  public function get_imsroles() {
    return $this->imsroles;
  }

  /**
   * This function is only used when first setting up the plugin, to
   * decide which role assignments to recommend by default.
   * For example, IMS role '01' is 'Learner', so may map to 'student' in Rogō.
   *
   * @param string $imscode
   */
  public function determine_default_rolemapping($imscode) {
    global $string;

    switch ($imscode) {
      case '01':
      case '04':
        $shortname = $string['student'];
        break;

      case '02':
      case '03':
      case '06':
      case '08':
        $shortname = $string['staff'];
        break;

      case '05':
      case '07':
        $shortname = $string['sysadmin'];
        break;
      default:
        return $string['ignore']; // Zero for no match.
    }
    return $shortname;
  }

}