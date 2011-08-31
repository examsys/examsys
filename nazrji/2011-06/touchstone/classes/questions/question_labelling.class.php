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
 * Class for Image Hotspot questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once realpath(dirname(__FILE__).DIR_SEPARATOR.'../options/option_hotspot.class.php');

Class QuestionLABELLING extends Question {
  
  protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
      
  protected $points1 = '';
  protected $_requires_media = true;
  protected $_requires_flash = true;
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);

    // Convert the max number of options into a list of variables
    $this->option_order = 'display_order';
    $this->_fields_editable[] = 'points1';
    $this->_change_field_map['points1'] = 'points';
  }

  // ACCESSORS
  
  public function get_points1() {
    if (count($this->options) > 0) {
      $option = reset($this->options);
      $this->points1 = $option->get_correct();
    }
    return $this->points1;
  }
  
  public function set_points1($value) {
    // Correct label locations if too far over.
    $first_split = explode(';',$value);
    $second_split = explode('$',$first_split[8]);
    $tmp_coords = '';
    $a = 0;
    $b = 0;
    foreach ($second_split as $stuff) {
      if ($a == 2 and $stuff < 150 and $b == 0) $stuff = 8;
      if ($a == 2 and $stuff < 150 and $b == 9) $stuff = 110;
      if ($tmp_coords == '') {
        $tmp_coords = $stuff;
      } else {
        $tmp_coords .= '$' . $stuff;
      }
      $a++;
      if ($a == 4) {
        $a = 0;
        $b++;
      }
    }
    $tmp_points = $first_split[0] . ';' . $first_split[1] . ';' . $first_split[2] . ';' . $first_split[3] . ';' . $first_split[4] . ';' . $first_split[5] . ';' . $first_split[6] . ';' . $first_split[7] . ';' . $tmp_coords;
    for ($i=9; $i<count($first_split); $i++) {
      $tmp_points .= ';' . $first_split[$i];
    }
    
    if ($tmp_points != $this->get_points1()) {
      $this->set_modified_field('points1', $this->points1);
      $this->points1 = $tmp_points;
    }
    
    if (count($this->options) > 0) {
      $option = reset($this->options);
      $option->set_correct($this->points1);
    } else {
      $this->options[] = new OptionHOTSPOT($this->_mysqli, $this->_user_id, $this, 1, array('correct' => $this->points1));
    }
  }
}

