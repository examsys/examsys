<?php
// This file is part of Rog?
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Utility class for updater related functionality
*
* @author Ben Parish
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/



Class UpdaterUtils {

  /*
   * @var $mysqli mysqli
   */
  private $mysqli;

  private $db_name;

  public function __construct( mysqli $mysqli
                              ,       $db_name ){
    $this->mysqli  = $mysqli;
    $this->db_name = $db_name;

  }

  public function does_table_exist( $table_name ){

    $sql = 'SELECT
              table_name
            FROM
              information_schema.tables
            WHERE
              table_schema = ?
            AND
              table_name   = ?';

    $result  = $this->mysqli->prepare( $sql );
    $result->bind_param( 'ss', $this->db_name, $table_name );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if( $num_rows < 1 ){
      return false;
    }

    return true;
  }

  public function does_column_type_value_exist( $table_name
                                              , $column_name
                                              , $column_type_value ){

    $sql     = 'SELECT
                  column_type
                FROM
                  information_schema.columns
                WHERE
                  table_schema  = ?
                AND
                  table_name    = ?
                AND
                  column_name   = ?
                AND
                  column_type   = ?';

    $result  = $this->mysqli->prepare( $sql );
    $result->bind_param('ssss', $this->db_name, $table_name, $column_name, $column_type_value );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if( $num_rows < 1 ){
      return false;
    }

    return true;
  }


  public function does_column_exist( $table_name
                                   , $column_name ){

    $sql     = 'SELECT
                  column_name
                FROM
                  information_schema.columns
                WHERE
                  table_schema  = ?
                AND
                  table_name    = ?
                AND
                  column_name   = ?';

    $result  = $this->mysqli->prepare( $sql );
    $result->bind_param('sss', $this->db_name, $table_name, $column_name );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if( $num_rows < 1 ){
      return false;
    }

    return true;
  }

  public function does_index_exist( $table_name, $index_name ){

    $sql     = 'SELECT
                  constraint_name
                FROM
                 information_schema.key_column_usage
                WHERE
                  table_schema  = ?
                AND
                  table_name    = ?
                AND
                  constraint_name   = ?';

    $result  = $this->mysqli->prepare( $sql );
    $result->bind_param('sss', $this->db_name, $table_name, $index_name );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if( $num_rows < 1 ){
      return false;
    }

    return true;
  }


  public function does_tables_priv_exist( $user, $table, $privileges ){

    $this->mysqli->select_db('mysql');

    $privileges = str_replace( ' ', '', $privileges );

    $privileges = ucwords( $privileges );

    $sql     = 'SELECT
                  *
                FROM
                 tables_priv
                WHERE
                  db          = ?
                AND
                  user        = ?
                AND
                  table_name  = ?
                AND
                  table_priv  = ?';

    $result  = $this->mysqli->prepare( $sql );

    $result->bind_param('ssss', $this->db_name, $user, $table, $privileges );
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    $this->mysqli->select_db( $this->db_name );

    if( $num_rows < 1 ){
      return false;
    }

    return true;

  }

}































