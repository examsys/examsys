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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/viewhelper.class.php';

function render_year_dropdown($years, $module, $string, $selected = '') {
  $html = '<form id="year-form" action="' . $_SERVER['PHP_SELF'] . '?module=' . $module . '" method="post">';
  $html .= '<label for="session">' . $string['academicyear']. '</label> <select id="session" name="session">';
  $html .= ViewHelper::render_options($years, $selected, 1);
  $html .= '</select>';
  $html .= '</form>';

  return $html;
}