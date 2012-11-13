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
 * Base class for modifyable objects in Rogō
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once 'exceptions.inc.php';

Class RogoObject {
  protected $_fields_editable = array();
  protected $_modified_fields = array();
  
  // 'Compound' fields are concatenated within a question
  protected $_fields_compound = array();
  
  /**
   * Record the value of a modified field so that it can be used for change tracking
   * @param string $name
   * @param string $value
   */
  protected function set_modified_field($name, $value, $message = '') {
    if(!array_key_exists($name, $this->_modified_fields)) {
      $this->_modified_fields[$name]['value'] = $value;
      $this->_modified_fields[$name]['message'] = $message;
    }
  }
  
  /**
   * The the array of fields (properties) for this class
   * MUST be implemented by sub-classes
   * @return multitype:string 
   */
  public function get_editable_fields() {
    throw new MethodNotImplementedException("Method 'get_editable_fields' not implemented.");
  }
  
  /**
   * The the array of compound fields (properties) for this class
   * @return multitype:string 
   */
  public function get_compound_fields() {
    return $this->_fields_compound;
  }

  /**
   * Has the question been changed?
   * @return boolean
   */
  public function has_changes() {
    return (count($this->_modified_fields) > 0);
  }
  
  /**
   * replace [tex][/tex] tags with <div class="mee"></div> in none wysiwyg editors 
   * befor it is saved to the database
   *
   * @param string $text the text to be processed
   */
  protected function replace_tex($text) {
    //swap [tex] before saving to db <div class="mee">
    preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);
    if(count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('[tex]','[/tex]'),array('<div class="mee">','</div>'),$m);
        $text = str_replace($m, $new, $text);
      }
    } 
    
    //swap [texi] before saving to db <div class="mee">
    preg_match_all("#\[texi\](.*?)\[/texi\]#si",$text,$tex_matches);
    if(count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('[texi]','[/texi]'),array('<span class="mee">','</span>'),$m);
        $text = str_replace($m, $new, $text);
      }
    } 
    return $text;
  }
  
  /**
   * replace <div class="mee"></div> tags with [tex][/tex] in none wysiwyg editors 
   * befor it is displayed in the editor
   *
   * @param string $text the text to be processed
   */
  protected function replace_mee_div($text) {
    preg_match_all("#<div class=\"mee\">(.*?)\</div>#si",$text,$tex_matches);
    if(count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('<div class="mee">','</div>'),array('[tex]','[/tex]'),$m);
        $text = str_replace($m, $new, $text);
      }
    }
    preg_match_all("#<span class=\"mee\">(.*?)\</span>#si",$text,$tex_matches);
    if(count($tex_matches[0]) > 0) {
      foreach($tex_matches[0] as $m) {
        $new = str_replace(array('<span class="mee">','</span>'),array('[texi]','[/texi]'),$m);
        $text = str_replace($m, $new, $text);
      }
    }
    return $text;
  }
}

