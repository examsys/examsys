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
 * HTML renderer class
 * 
 * @author Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class html_renderer {

  /**
   * Render a select input element
   * 
   * @param array $options
   * @param string $selectid
   * @param string $selectname
   * @param string $selectedid
   * @param string $label
   * @param string $default_description
   * @param string $tooltip
   */
  public function select($options, $selectid, $selectname, $selectedid, $label, $default_description, $tooltip = '') {
    $html = '<div><div class="label"><label for="' . $selectid . '">' . $label . '</label>';
    if (!empty($tooltip)) {
      $html .= $this->tooltip($tooltip, true);
    }
    $html .= '</div><div><div>';

    $html .= '<select id="' . $selectid . '" name="' . $selectname . '">';
    $selected = '';
    foreach ($options as $optionid => $option) {
      if ($optionid == $selectedid) {
        $selected = 'selected="selected"';
      }
      $html .= '<option ' . $selected . ' value="' . $optionid . '">' . $option . '</option>';
      $selected = '';
    }
    $html .= '</select>';
    $html .= '</div><div class="form-defaultinfo">' . $default_description . '</div></div>';


    $html .= '</div>';

    echo $html;
  }

  /**
   * Render a text input form element
   *
   * @param string $name
   * @param string $id
   * @param string $label
   * @param string $default
   * @param string $default_description
   * @param string $tooltip
   * @param bool $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function text_input($name, $id, $label, $default, $default_description, $tooltip = '', $return = false) {
    $input = '<div><div class="label">';
    $input .= '<label for="imsfilelocation">' . $label . '</label>';
    if (!empty($tooltip)) {
      $input .= $this->tooltip($tooltip);
    }
    $input .= '</div><div><div>';
    $input .= '<input type="text" size="30" id="' . $id . '" name="' . $name . '" value="' . $default . '">';
    $input .= '</div>';

    if (!empty($default_description)) {
      $input .= '<div class="form-defaultinfo">' . $default_description . '</div>';
    }

    $input .= '</div><div></div>';
    $input .= '</div>';

    if ($return) {
      return $input;
    }
    echo $input;
  }

  /**
   * Render a checkbox input form element
   *
   * @param string $name
   * @param string $id
   * @param string $label
   * @param string $default
   * @param string $default_description
   * @param string $tooltip
   * @param bool $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function checkbox_input($name, $id, $label, $default, $default_description, $tooltip = '', $return = false) {
    $checked = '';

    if (!empty($default)) {
      $checked = ' checked="checked" ';
    }
    $input = '<div><div class="label">';
    $input .= '<label for="imsfilelocation">' . $label . '</label>';
    if (!empty($tooltip)) {
      $input .= $this->tooltip($tooltip, true);
    }
    $input .= '</div><div><div>';
    $input .= '<input type="checkbox" ' . $checked . ' id="' . $id . '" name="' . $name . '" value="1">';
    $input .= '</div>';

    if (!empty($default_description)) {
      $input .= '<div class="form-defaultinfo">' . $default_description . '</div>';
    }

    $input .= '</div><div></div>';

    $input .= '</div>';

    if ($return) {
      return $input;
    }
    echo $input;
  }

  /**
   * Render a tooltip
   * 
   * @param string $text
   * @param string $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function tooltip($text, $return = false) {
    $configObj = Config::get_instance();
    $html = '<div class="tooltip">';
    $html .= '<img alt="' . $text . '" src="' . $configObj->get('cfg_root_path') . '/artwork/tooltip_icon.gif" class="help_tip" title="' . $text . '" />';
    $html .= '</div>';
    if ($return) {
      return $html;
    }
    echo $html;
  }

  /**
   * Render an html tag with text, class and attibutes.
   * 
   * @param string $tag
   * @param string $text
   * @param string $class
   * @param array $attributes
   * @param bool $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function tag($tag, $text, $class = '', $attributes = null, $return = false) {

    $attributes_html = '';
    $class_html = '';
    if (!empty($attributes)) {
      foreach ($attributes as $attribute => $value) {
        $attributes_html .= " $attribute=" . '"' . $value . '"';
      }
    }

    if (!empty($class)) {
      $class_html .= ' class="' . $class . '"';
    }

    $extra = trim(" $class_html $attributes_html");
    $html = "<$tag $extra>";
    $html .= $text;
    $html .= "</$tag>";
    if ($return) {
      return $html;
    }
    echo $html;
  }

  /**
   * Render a start div tag
   * 
   * @param string $class
   * @param bool $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function start_div($class = '', $return = false) {

    $class_html = '';

    if (!empty($class)) {
      $class_html .= " class=$class";
    }

    $html = "<div $class_html>";
    if ($return) {
      return $html;
    }
    echo $html;
  }

  /**
   * Render a closing div tag
   * @param bool $return
   * @return string|void
   */
  public function end_div($return = false) {
    $html = "</div>";
    if ($return) {
      return $html;
    }
    echo $html;
  }

  /**
   * Render a heading with optional tooltip
   * 
   * @param string $tag
   * @param string $text
   * @param string $tooltip
   * @param bool $return
   * @return string|void Return the HTML or echo it depending on the $return parameter
   */
  public function heading($tag, $text, $tooltip = '', $return = false) {
    $html = $this->start_div('heading', true);
    $html .= $this->start_div('', true);
    $html .= $this->tag($tag, $text, '', null, true);
    $html .= $this->end_div(true);
    if (!empty($tooltip)) {
      $html .= $this->tooltip($tooltip, true);
    }
    $html .= $this->end_div(true);

    if ($return) {
      return $html;
    }
    echo $html;
  }
}
