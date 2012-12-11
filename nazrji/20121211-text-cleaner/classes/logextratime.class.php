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
* CRUD operations on the log_extra_time table
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class LogExtraTime {

  private $user_id;
  private $paper_id;

  /*
   * @var mysqli $db
   */

  private $db;

  public function __construct( $lab_id, $paper_id, mysqli $db ) {
    $this->lab_id    = $lab_id;
    $this->paper_id  = $paper_id;
    $this->db        = $db;
  }

  /**
   * Gets the time the user started the paper
   * @param int $user_id
   * @param int $paper_id
   * @return string
   */
  public function get_end_time() {

    $query = 'SELECT
                  started
              FROM
                log_extra_time
              WHERE
                labID   = ?
              AND
                paperID = ?';

    $stmt  = $this->db->prepare( $query );

    $stmt->bind_param( 'ii', $this->lab_id, $this->paper_id );
    $stmt->execute();
    $stmt->store_result();

    if( $stmt->num_rows < 1 ){
      $stmt->close();
      return false;
    }

    $bindResult = $stmt->bind_result( $end_time );

    $stmt->fetch();
    $stmt->close();

    return $end_time;

  }

  public function save( $invigilator_id ) {


    $query    = 'INSERT INTO
                    log_extra_time
                            ( labID
                            , paperID
                            , invigilatorID
                            , end_time )
                   VALUES
                      ( ?
                      , ?
                      , ?
                      , ? )';

    $stmt     = $this->db->prepare( $query );

    $stmt->bind_param('is', $invigilator_id, $end_time );
    $stmt->execute();
    $stmt->close();

    return $end_time;

  }

  public function delete( $invigilator_id ) {

    $query    = 'DELETE FROM
                   log_extra_time
                 WHERE
                   labID   = ?
                 AND
                   paperID = ?';

    $stmt  = $this->db->prepare( $query );

    $stmt->bind_param('i', $invigilator_id );
    $stmt->execute();
    $stmt->close();

  }

}