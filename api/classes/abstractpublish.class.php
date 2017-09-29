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
* Abstract API publish functionality
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Abstract publish class.
 * 
 * This class should be extend by classes used to publish rogo data such as the gradebook
 */
abstract class abstractpublish {
           
    /**
     * Abstract get function
     * 
     * Operation to get published rogo data.
     * @param string $filtername - component name
     * @param integer $filterid - component id
     * @param string $externalsys - external system
     * @return array response to operation, published data or error message.
     */
    abstract public function get($filtername, $filterid, $externalsys = null);
    
    /**
     * The database connection.
     */
    protected $db;
    
    /**
     * Constructor
     * @param mysqli $mysqli the database connection
     */
    public function __construct($mysqli) {
        $this->db = $mysqli;
    }
    
}