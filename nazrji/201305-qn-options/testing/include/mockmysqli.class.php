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
* Rogō Mock_mysqli, Mock_mysqli_result and Mock_mysqli_stmt classes for testing 
* rogo code with out a real database connection.
* 
*
* All queries will succeed and then return the data loaded using the 
* load_mock_data method. Quries can be forced to fail by 
* calling set_force_fail(true) before you call query or prepare.  
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class Mock_mysqli {
 
  private $data;
  private $num_exicuted;
  private $force_fail;
  private $force_mysqli_stmt_fail;
  public $errno; // 0 == no error 
  public $error_list;
  public $error;

  function __construct() {
    $this->data = array();
    $this->num_rows = false;
    $this->force_mysqli_stmt_fail = false;
    $this->unset_error();
  }

  /**
  * @param  $d A 2d array of rows and cols to be retuned by the query
  * @return void
  */
  function load_mock_data($d) {
    if (!is_array($d)) {
      throw new Exception("The Mock data must be an array()");
    }
    $this->data = $d;
    $this->num_exicuted = -1;
    $this->force_fail = false;
  }
  
  function query($sql) {
    
    if ($this->force_fail) {
      $this->set_error();
      return false;
    } else {
      $this->unset_error();
    }

    if(    stristr($sql, 'SELECT') !== false 
        OR stristr($sql, 'SHOW') !== false 
        OR stristr($sql, 'DESCRIBE') !== false
        OR stristr($sql, 'EXPLAIN') !== false  ) {
      //SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a mysqli_result
      return new Mock_mysqli_result($this->get_data());
    } else {
      //For other successful queries mysqli_query() will return TRUE
      return true;
    }

    //unsuccessful queries return false
    return false;
  }

  function prepare($sql) {
    if($this->force_fail) {
      $this->set_error();
      return false;
    }
    return new Mock_mysqli_stmt( $this->get_data(),
                                 $this->force_mysqli_stmt_fail);
  }

  function set_force_fail($val) {
    if ($val == true) {
      $this->force_fail = $val;
    } else {
      $this->force_fail = false;
    }
  }

  function set_force_mysqli_stmt_fail($val) {
    if ($val == true) {
      $this->force_mysqli_stmt_fail = $val;
    } else {
      $this->force_mysqli_stmt_fail = false;
    }
  }

  private function get_data() {
    $this->num_exicuted++;
    if(isset($this->data[$this->num_exicuted])) {
      return $this->data[$this->num_exicuted];
    } else {
      return array();
    }
  }

  /**
  * Sets the mysqli error vars on error
  * @return void
  */
  private function set_error() {
    $this->errno = 99;
    $this->error = 'Mock_mysqli:: Error Forced by test';
    $this->error_list = array(
                              'errno' => $this->errno,
                              'sqlstate' => 'ER000',
                              'error' => $this->error,
                              );
  }

  /**
  * Sets the mysqli error vars to a non-error state 
  * @return void
  */
  private function unset_error() {
    $this->errno = 0;
    $this->error = '0';
    $this->error_list = array();
  }

}

class Mock_mysqli_result {
  private $data;
  public $num_rows;

  function __construct($d) {
    $this->data = $d;
    $this->num_rows = count($d);
  }

  function free() {
    unset($this->data);
  }

  function fetch_array($resulttype = MYSQLI_BOTH) {
    if (is_array($this->data) and count($this->data) > 0) {
      return array_shift($this->data);
    } else {
      return false;
    }
  }

  //UNIMPLEMENTED 
  //if we ever need to test code that uses these we will need to write them
  function data_seek($offset) {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_all($resulttype = MYSQLI_NUM) {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_assoc() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_field_direct() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_field() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_fields() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_object() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function fetch_row() {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  function field_seek($fieldnr) {
    throw new Exception("Not Implemented in Mock_mysqli_result.");
  }
  //UNIMPLEMENTED END
}

class Mock_mysqli_stmt {
  private $insert_id;
  public $num_rows;
  private $data;
  private $bound_vars_by_ref;
  private $force_mysqli_stmt_fail;
  private $errno; // 0 == no error 
  private $error_list;
  private $error;
  private $bind_param_called;
  private $execute_called;

  function __construct($d, $fail = false) {
    $this->data = $d;
    $this->bound_vars_by_ref = array();
    $this->num_rows = null;
    $this->force_mysqli_stmt_fail = $fail;
    $this->unset_error();
    $this->bind_param_called = false;
    $this->execute_called = false;
  }

  function bind_param() {
    $this->bind_param_called = true;
    return true; //never fails
  }

  function bind_result( &$var0, &$var1 = 'NaN', &$var2 = 'NaN', &$var3 = 'NaN', 
                        &$var4 = 'NaN', &$var5 = 'NaN', &$var6 = 'NaN',
                        &$var7 = 'NaN', &$var8 = 'NaN', &$var9 = 'NaN', 
                        &$var10 = 'NaN', &$var11 = 'NaN', &$var12 = 'NaN', 
                        &$var13 = 'NaN', &$var14 = 'NaN', &$var15 = 'NaN', 
                        &$var16 = 'NaN', &$var17 = 'NaN', &$var18 = 'NaN', 
                        &$var19 = 'NaN', &$var20 = 'NaN', &$var21 = 'NaN', 
                        &$var22 = 'NaN', &$var23 = 'NaN', &$var24 = 'NaN', 
                        &$var25 = 'NaN', &$var26 = 'NaN', &$var27 = 'NaN', 
                        &$var28 = 'NaN', &$var29 = 'NaN') {
    
    if(func_num_args() > 30) {
      throw new Exception("Mock_mysqli_stmt->bind_result can only accept 30" . 
                          "parameters this is a limitation of the Mock class" . 
                          "and can be fixed in the code if needed!");
    }

    for ($i=0; $i < 30; $i++) {
      $var = "var$i";
      $this->bound_vars_by_ref[$i] = &$$var;  
    }

  }

  function close() {
    return true; //never fails
  }

  function execute() {
    $this->execute_called = true;

    if($this->force_mysqli_stmt_fail) {
      $this->set_error("Fail forced by test case");
      return false;
    }
    return true; //never fails
  }

  function fetch() {

    if($this->force_mysqli_stmt_fail) {
      $this->set_error("Fail forced by test case");
      return false;
    }

    if(!$this->execute_called) {
      $this->set_error("Fail fetch called before execute");
      return false;
    }

    if (is_array($this->data) and count($this->data) > 0) {
      $vars = array_shift($this->data);
      if (is_array($vars)) {
        for($i = 0; $i < count($vars); $i++) {
          $this->bound_vars_by_ref[$i] = $vars[$i];
        }
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  function free_result() {
    unset($this->data);
  }

  function  store_result() {
    //set num_rows 
    $this->num_rows = count($this->data);
    return true; //never fails
  }
  
  /**
  * Sets the mysqli error vars on error
  * @return void
  */
  private function set_error($error) {
    $this->errno = 99;
    $this->error = 'Mock_mysqli_stmt:: ' . $error;
    $this->error_list = array(
                              'errno' => $this->errno,
                              'sqlstate' => 'ER000',
                              'error' => $this->error,
                              );
  }

  /**
  * Sets the mysqli error vars to a non-error state 
  * @return void
  */
  private function unset_error() {
    $this->errno = 0;
    $this->error = '0';
    $this->error_list = array();
  }

}
?>