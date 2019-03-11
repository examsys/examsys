<?php
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
 * Memcache session handler
 * See https://serverfault.com/questions/916129/php-7-2-failed-to-read-session-data-memcache
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class memcachesessionhandler extends SessionHandler {
  /**
   * Read session data
   * @param string $id The session id to read data for.
   * @return string
   */
  public function read($id) {
    $data = parent::read($id);
    if(empty($data)) {
      return '';
    } else {
      return $data;
    }
  }
}