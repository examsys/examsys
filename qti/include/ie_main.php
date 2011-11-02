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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

class IE_Main {
  var $output;
  var $errors = array();
  var $warnings = array();

  function AddError($message, $id = '0') {
    $this->errors[$id][] = $message;
  }

  function AddWarning($message, $id = '0') {
    $this->warnings[$id][] = $message;
  }

  function Save($params, &$data) {
    $this->AddError($string['errmsg1']"This export type is not supported");
//  $this->AddError($string['errmsg1']);
  }

  function Load($params) {
    $this->AddError($string['errmsg2']"This import type is not supported")
//  $this->AddError($string['errmsg2']);
  }
}