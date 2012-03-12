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
ob_start();

class OB {
  var $content;

  function ClearAndSave() {
    $this->content = ob_get_contents();
    ob_clean();
  }

  function Clear() {
    $this->content = '';
    ob_clean();
  }

  function GetContent() {
    return ob_get_contents();
  }

  function Restore() {
    ob_clean();
    echo $this->content;
  }

  function DoInclude($filename) {
    $this->ClearAndSave();
    include $filename;
    $res = $this->GetContent();
    $this->Restore();
    return $res;
  }
}
