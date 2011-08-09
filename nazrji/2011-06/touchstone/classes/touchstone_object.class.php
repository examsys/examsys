<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Base class for modifyable objects in TouchStone
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

Class TouchStoneObject {
  protected $_fields_editable = array();
  protected $_modified_fields = array();
  
  /**
   * Record the value of a modified field so that it can be used for change tracking
   * @param string $name
   * @param string $value
   */
  protected function set_modified_field($name, $value) {
    if(!array_key_exists($name, $this->_modified_fields)) {
      $this->_modified_fields[$name] = $value;
    }
  }
  
  /**
   * The the array of fields (properties) for this class
   * @return multitype:string 
   */
  public function get_editable_fields() {
    return $this->_fields_editable;
  }
  
  /**
   * Has the question been changed?
   * @return boolean
   */
  public function has_changes() {
    return (count($this->_modified_fields) > 0);
  }
}

