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
* Helper method for display templates
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

class ViewHelper {
  
  /**
   * Render the options for a select box
   * @param array $options options as $value => $text associative array
   * @param string $selected the value to be selected in the options
   * @param int $tablevel number of tab characters to use at the start of the option string
   * @param string $css_class a CSS class to be applied to ALL options or an array of classes to be applied to each option individually
   */
  public static function render_options($options, $selected = '', $tablevel = 0, $force_assoc = false, $css_class = '', $label_prefix='', $label_postfix='') {
    $html = '';
    
    // Handle both associative and indexed arrays as $options
    if ($force_assoc or self::is_assoc($options)) {
      $values = array_keys($options);
      $texts = array_values($options);
    } else {
      $values = $texts = $options;
    }
    
    for ($i = 0; $i < count($values); $i++) {
      $value = $values[$i];
      $text = $texts[$i];
      
      $html .= str_repeat("\t", $tablevel);
      $sel = ((is_array($selected) and in_array($value, $selected)) or strval($selected) == strval($value)) ? ' selected="selected"' : '';
      if (!is_array($css_class)) {
        $class = ($css_class != '') ? ' class="' . $css_class . '"' : '';
      } else {
        $class = ($css_class[$i] != '') ? ' class="' . $css_class[$i] . '"' : '';
      }
      
      $html .= "<option value=\"$value\"{$sel}{$class}>{$label_prefix}{$text}{$label_postfix}</option>\n";
    }
    
    return $html;
  }
  
  /**
   * Determine if an array is associative. It does this by camparing array_keys($a) with array_keys(array_keys($a)), which will always be 0,1,2 etc.
   * @param unknown_type $a
   * @return boolean
   */
  private static function is_assoc(array $a){
   return (array_keys($a) !== array_keys(array_keys($a)));
  }
}
