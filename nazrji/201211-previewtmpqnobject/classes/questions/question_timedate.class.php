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
 * Class for Time/Date questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

Class QuestionTIMEDATE extends Question {
  
  protected $correct = '';
  protected $format = '1';
  protected $start_year = '';
  protected $end_year = '';
  protected $td_year = '';
  protected $td_month = '';
  protected $td_day = '';
  protected $td_hours = '';
  protected $td_minutes = '';
  protected $td_seconds = '';
  public $max_options = 1;
  protected $_allow_change_marking_method = false;
  
  protected $_formats;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'option_order', 'bloom', 'status', 'correct', 'format', 'start_year', 'end_year', 'td_year', 'td_month', 'td_day', 'td_hours', 'td_minutes', 'td_seconds');
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);

    $this->_formats = array('1' => 'dd/MM/yyyy hh:mm:ss', '2' => 'dd/MM/yyyy hh:mm', '3' => 'dd/MM/yyyy', '4' => 'mm/dd/yyyy', '5' => 'dd/MMMM/yyyy', '6' => 'hh:mm:ss', '7' => 'hh:mm (' . strtolower($this->_lang_strings['date']) . ')', '8' => 'hh:mm (' . $this->_lang_strings['duration'] . ')');

    // Populate the pseudo variables
    $this->get_display_method();
    $this->get_correct();
  }
  
  // ACCESSORS
  
  /**
   * Get the available time/date formats
   * @return integer
   */
  public function get_formats() {
    return $this->_formats;
  }
  
  /**
   * Get the question time/date format as an integer
   * @return integer
   */
  public function get_format() {
    $this->get_display_method();
    return $this->format;
  }
  
  /**
   * Get the question time/date format as a string
   * @return string
   */
  public function get_format_string() {
    return $this->_formats[$this->get_format()];
  }
  
  /**
   * Set the question time/date format
   * @param string $value
   */
  public function set_format($value) {
    if ($value != $this->format) {
      $this->add_unified_field_modification('format', 'format', $this->_formats[$this->format], $this->_formats[$value]);
      $this->format = $value;
    }
    $this->set_display_method('dummy');
  }
  
  /**
   * Get the question start year
   * @return string
   */
  public function get_start_year() {
    $this->get_display_method();
    return $this->start_year;
  }
  
  /**
   * Set the question start year
   * @param string $value
   */
  public function set_start_year($value) {
    if ($value != $this->start_year) {
      $this->set_modified_field('start_year', $this->start_year);
      $this->start_year = $value;
    }
    $this->set_display_method('dummy');
  }
  
  /**
   * Get the question end year
   * @return string
   */
  public function get_end_year() {
    $this->get_display_method();
    return $this->end_year;
  }
  
  /**
   * Set the question end year
   * @param string $value
   */
  public function set_end_year($value) {
    $this->end_year = $value;
    $this->set_display_method('dummy');
  }
  
  /**
   * Get the question Time/Date year
   * @return string
   */
  public function get_td_year() {
    $this->get_correct();
    return $this->td_year;
  }
  
  /**
   * Set the question Time/Date year
   * @param string $value
   */
  public function set_td_year($value) {
    $this->td_year = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the question Time/Date month
   * @return string
   */
  public function get_td_month() {
    $this->get_correct();
    return $this->td_month;
  }
  
  /**
   * Set the question Time/Date month
   * @param string $value
   */
  public function set_td_month($value) {
    $this->td_month = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the question Time/Date day
   * @return string
   */
  public function get_td_day() {
    $this->get_correct();
    return $this->td_day;
  }
  
  /**
   * Set the question Time/Date day
   * @param string $value
   */
  public function set_td_day($value) {
    $this->td_day = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the question Time/Date hours
   * @return string
   */
  public function get_td_hours() {
    $this->get_correct();
    return $this->td_hours;
  }
  
  /**
   * Set the question Time/Date hours
   * @param string $value
   */
  public function set_td_hours($value) {
    $this->td_hours = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the question Time/Date minutes
   * @return string
   */
  public function get_td_minutes() {
    $this->get_correct();
    return $this->td_minutes;
  }
  
  /**
   * Set the question Time/Date minutes
   * @param string $value
   */
  public function set_td_minutes($value) {
    $this->td_minutes = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the question Time/Date seconds
   * @return string
   */
  public function get_td_seconds() {
    $this->get_correct();
    return $this->td_seconds;
  }
  
  /**
   * Set the question Time/Date seconds
   * @param string $value
   */
  public function set_td_seconds($value) {
    $this->td_seconds = $value;
    $this->set_correct('dummy');
  }
  
  /**
   * Get the correct answer for this question.  Actually comes from a single option for this question
   * @return string
   */
  public function get_correct() {
    if (count($this->options) == 0) {
      $option = new Option($this->_mysqli, $this->_user_id, $this, 1, $this->_lang_strings);
    } else {
      $option = reset($this->options);
    }
    
    $this->parse_correct($option->get_correct());
    $this->correct = $option->get_correct();
    
    return $this->correct;
  }
  
  /**
   * Set the correct answer for this question by applying it, in the correct format, to a single option
   * @param string $value
   */
  public function set_correct($value) {
    if (count($this->options) == 0) {
      $option = Option::option_factory($this->_mysqli, $this->_user_id, $this, 1, $this->_lang_strings);
      $this->options[] = $option;
    } else {
      $option = reset($this->options);
    }
    $value = $this->format_correct();
    
    if ($value != $option->get_correct()) {
      $this->set_modified_field('correct', $option->get_correct());
      $option->set_correct($value);
      $this->correct = $value;
    }
  }
  
  /**
   * Get the display method for this question and unpack into pseudo-properties
   * @return string
   */
  public function get_display_method() {
    if ($this->display_method != '') {
      $parts = explode('|', $this->display_method);
      $this->format = $parts[0];
      $this->start_year = $parts[1];
      $this->end_year = $parts[2];
    }    
    return $this->display_method;
  }
  
  /**
   * Set the display method for this question by building from pseudo-properties
   * @param string $value
   */
  public function set_display_method($value) {
    $this->display_method = $this->format . '|' . $this->start_year . '|' . $this->end_year;
  }
  
  
  // PRIVATE FUNCTIONS
  
  protected function parse_correct($value) {
    if ($value != '') {
      if (strpos($value, ' ') !== false) {
        $parts = explode(' ', $value);
        $date_parts = explode('/', $parts[0]);
        $time_parts = explode(':', $parts[1]);
      } elseif (strpos($value, '/') !== false) {
        $date_parts = explode('/', $value);
      } elseif (strpos($value, ':') !== false) {
        $time_parts = explode(':', $value);
      }
      $this->td_year = (isset($date_parts[2])) ? $date_parts[2] : '';
      $this->td_hours = (isset($time_parts[0])) ? $time_parts[0] : '';
      $this->td_minutes = (isset($time_parts[1])) ? $time_parts[1] : '';
      $this->td_seconds = (isset($time_parts[2])) ? $time_parts[2] : '';
      
      if ($this->format != 4) {
        $this->td_month = (isset($date_parts[1])) ? $date_parts[1] : '';
        $this->td_day = (isset($date_parts[0])) ? $date_parts[0] : '';
      } else {
        $this->td_month = (isset($date_parts[0])) ? $date_parts[0] : '';
        $this->td_day = (isset($date_parts[1])) ? $date_parts[1] : '';
      }
    }
  }
  
  protected function format_correct() {
    $rval = '';
    switch ($this->format) {
      case 1:
        $rval = $this->td_day . '/' . $this->td_month . '/' . $this->td_year . ' ' . $this->td_hours . ':' . $this->td_minutes . ':' . $this->td_seconds;
        break;
      case 2:
        $rval = $this->td_day . '/' . $this->td_month . '/' . $this->td_year . ' ' . $this->td_hours . ':' . $this->td_minutes;
        break;
      case 3:
      case 5:
        $rval = $this->td_day . '/' . $this->td_month . '/' . $this->td_year;
        break;
      case 4:
        $rval = $this->td_month . '/' . $this->td_day . '/' . $this->td_year;
        break;
      case 6:
        $rval = $this->td_hours . ':' . $this->td_minutes . ':' . $this->td_seconds;
        break;
      case 7:
      case 8:
        $rval = $this->td_hours . ':' . $this->td_minutes;
        break;
    }
    return $rval;
  }
}

