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
* CRUD operations on the log_start_time table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class LogStartTime {

  private $user_id;
  private $paper_id;

  /*
   * @var mysqli $db
   */

  private $db;

  public function __construct( $user_id, $paper_id, mysqli $db ) {
    $this->user_id   = $user_id;
    $this->paper_id  = $paper_id;
    $this->db    = $db;
  }

  /**
   * Gets the time the user start_time the paper
   * @param int $user_id
   * @param int $paper_id
   * @return string
   */
  public function get_start_time() {

    $query = 'SELECT
                  start_time
              FROM
                log_start_time
              WHERE
                userID = ?
              AND
                paperID = ?;';

    $stmt  = $this->db->prepare( $query );

    $stmt->bind_param( 'ii', $this->user_id, $this->paper_id );
    $stmt->execute();
    $stmt->store_result();

    if( $stmt->num_rows < 1 ){
      $stmt->close();
      return false;
    }

    $bindResult = $stmt->bind_result( $start_time );

    $stmt->fetch();
    $stmt->close();

    return $start_time;

  }

  public function save() {

    // BP Using date() is more reliable when interacting
    // with the front end javascript timer than Mysql server's NOW()


    $query    = 'INSERT INTO
                  log_start_time
                          ( userID
                          , paperID
                          , start_time
                          )
                 VALUES
                    ( ?
                    , ?
                    , ? )';

    $stmt     = $this->db->prepare( $query );
    $start_time  = date ( 'Y-m-d H:i:s' );

    $stmt->bind_param('iis', $this->user_id, $this->paper_id, $start_time );
    $stmt->execute();
    $stmt->close();

    return $start_time;

  }

  public function delete() {

    $query    = 'DELETE FROM
                   log_start_time
                 WHERE
                   userID  = ?
                 AND
                   paperID = ?';

    $stmt  = $this->db->prepare( $query );

    $stmt->bind_param('ii', $this->user_id, $this->paper_id );
    $stmt->execute();
    $stmt->close();

  }

}