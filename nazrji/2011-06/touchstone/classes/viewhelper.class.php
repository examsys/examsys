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
* Helper method for display templates
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

class ViewHelper {
  
  /**
   * Render the options for a select box
   * @param array $options options as $value => $text associative array
   * @param string $selected the value to be selected in the options
   * @param int $tablevel number of tab characters to use at the start of the option string
   * @param string $css_class a CSS class to be applied to ALL options 
   */
  public static function render_options($options, $selected = '', $tablevel = 0, $css_class = '') {
    $html = '';
    foreach ($options as $value => $text) {
      $html .= str_repeat("\t", $tablevel);
      $sel = ($selected == $value) ? ' selected="selected"' : '';
      $css_class = ($css_class != '') ? " $css_class" : $css_class;
      $html .= "<option value=\"$value\"{$sel}{$css_class}>$text</option>\n";
    }
    return $html;
  }
}
