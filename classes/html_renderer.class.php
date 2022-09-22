<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * HTML renderer class
 *
 * @author Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class html_renderer
{
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
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @param bool $intable Whether the element should be rendered as a table row or using divs
     * @return string|void Return html or echo depending on $return parameter
     */
    public function select($options, $selectid, $selectname, $selectedid, $label, $default_description, $tooltip = '', $return = false, $intable = false)
    {
        if ($intable) {
            $html = '<tr class="formrow">';
            $html .= '<td class="formlabel">';
            $html .= '<label for="' . $selectid . '">' . $label . '</label>';
            $html .= '</td>';
            $html .= '<td class="formtooltip">';
            if (!empty($tooltip)) {
                $html .= $this->tooltip($tooltip, true, false);
            }
            $html .= '</td>';
            $html .= '<td class="forminput select">';
            $html .= $this->generate_select_element($selectid, $selectname, $options, $selectedid);
            $html .= '</td>';
            $html .= '<td class="formdefault">';
            $html .= $default_description;
            $html .= '</td>';
            $html .= '</tr>';
        } else {
            $html = '<div><div class="label"><label for="' . $selectid . '">' . $label . '</label>';
            if (!empty($tooltip)) {
                $html .= $this->tooltip($tooltip, true, false);
            }
            $html .= '</div><div><div>';

            $html .= $this->generate_select_element($selectid, $selectname, $options, $selectedid);
            $html .= '</div><div class="form-defaultinfo default">' . $default_description . '</div></div>';
            $html .= '</div>';
        }

        if ($return) {
            return $html;
        }
        echo "\n" . $html;
    }

    /**
     * Generate a select element for the select renderer
     *
     * @param string $selectid
     * @param string $selectname
     * @param array $options
     * @param string $selectedid
     * @return string
     */
    private function generate_select_element($selectid, $selectname, $options, $selectedid)
    {
        $html = '<select id="' . $selectid . '" name="' . $selectname . '">';
        $selected = '';
        foreach ($options as $optionid => $option) {
            if ($optionid == $selectedid) {
                $selected = 'selected="selected"';
            }
            $html .= '<option ' . $selected . ' value="' . $optionid . '">' . $option . '</option>';
            $selected = '';
        }
        $html .= '</select>';
        return $html;
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
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @param bool $intable Whether the element should be rendered as a table row or using divs
     * @return string|void Return html or echo depending on $return parameter
     */
    public function text_input($name, $id, $label, $default, $default_description, $tooltip = '', $return = false, $intable = false)
    {
        if ($intable) {
            $input = '<tr class="formrow">';
            $input .= '<td class="formlabel">';
            $input .= '<label for="' . $id . '">' . $label . '</label>';
            $input .= '</td>';
            $input .= '<td class="formtooltip">';
            if (!empty($tooltip)) {
                $input .= $this->tooltip($tooltip, true, false);
            }
            $input .= '</td>';
            $input .= '<td class="forminput text">';
            $input .= '<input type="text" size="30" id="' . $id . '" name="' . $name . '" value="' . $default . '">';
            $input .= '</td>';
            $input .= '<td class="formdefault">';
            $input .= $default_description;
            $input .= '</td>';
            $input .= '</tr>';
        } else {
            $input = '<div><div class="label">';
            $input .= '<label for="filelocation">' . $label . '</label>';
            if (!empty($tooltip)) {
                $input .= $this->tooltip($tooltip);
            }
            $input .= '</div><div><div>';
            $input .= '<input type="text" size="30" id="' . $id . '" name="' . $name . '" value="' . $default . '">';
            $input .= '</div>';

            if (!empty($default_description)) {
                $input .= '<div class="form-defaultinfo default">' . $default_description . '</div>';
            }

            $input .= '</div><div></div>';
            $input .= '</div>';
        }

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
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @param bool $intable Whether the element should be rendered as a table row or using divs
     * @return string|void Return html or echo depending on $return parameter
     */
    public function checkbox_input($name, $id, $label, $default, $default_description, $tooltip = '', $return = false, $intable = false)
    {
        $checked = '';

        if (!empty($default)) {
            $checked = ' checked="checked" ';
        }

        if ($intable) {
            $input = '<tr class="formrow">';
            $input .= '<td class="formlabel">';
            $input .= '<label for="' . $id . '">' . $label . '</label>';
            $input .= '</td>';
            $input .= '<td class="formtooltip">';
            if (!empty($tooltip)) {
                $input .= $this->tooltip($tooltip, true, false);
            }
            $input .= '</td>';
            $input .= '<td class="forminput text">';
            $input .= '<input type="checkbox" ' . $checked . ' id="' . $id . '" name="' . $name . '" value="1">';
            $input .= '</td>';
            $input .= '<td class="formdefault">';
            $input .= $default_description;
            $input .= '</td>';
            $input .= '</tr>';
        } else {
            $input = '<div><div class="label">';
            $input .= '<label for="' . $id . '">' . $label . '</label>';
            if (!empty($tooltip)) {
                $input .= $this->tooltip($tooltip, true, false);
            }
            $input .= '</div><div><div>';
            $input .= '<input type="checkbox" ' . $checked . ' id="' . $id . '" name="' . $name . '" value="1">';
            $input .= '</div>';

            if (!empty($default_description)) {
                $input .= '<div class="form-defaultinfo checkbox">' . $default_description . '</div>';
            }

            $input .= '</div><div></div>';
            $input .= '</div>';
        }

        if ($return) {
            return $input;
        }
        echo $input;
    }

    /**
     * Render a tooltip
     *
     * @param string $text
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @return string|void Return html or echo depending on $return parameter
     */
    public function tooltip($text, $return = false, $div = true)
    {
        $tag = 'div';
        if (!$div) {
            $tag = 'span';
        }
        $configObj = Config::get_instance();
        $html = '<' . $tag . ' class="tooltip">';
        $html .= '<img alt="' . $text . '" src="' . $configObj->get('cfg_root_path') . '/artwork/tooltip_icon.gif" class="help_tip" title="' . $text . '" />';
        $html .= '</' . $tag . '>';
        if ($return) {
            return $html;
        }
        echo "\n" . $html;
    }

    /**
     * Render an html tag with text, class and attibutes.
     *
     * @param string $tag
     * @param string $text
     * @param string $class
     * @param array $attributes
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @return string|void Return html or echo depending on $return parameter
     */
    public function tag($tag, $text, $class = '', $attributes = null, $return = false)
    {
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
        echo "\n" . $html;
    }

    /**
     * Render a start div tag
     *
     * @param string $class
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @param bool $intable Whether the element should be rendered as a table row or using divs
     * @return string|void Return html or echo depending on $return parameter
     */
    public function start_div($class = '', $return = false, $intable = false)
    {
        $class_html = '';

        if (!empty($class)) {
            $class_html .= " class=$class";
        }

        $html = "<div $class_html>";
        if ($return) {
            return $html;
        }
        echo "\n" . $html;
    }

    /**
     * Render a closing div tag
     * @param bool $return
     * @return string|void
     */
    public function end_div($return = false)
    {
        $html = '</div>';
        if ($return) {
            return $html;
        }
        echo "\n" . $html;
    }

    /**
     * Render a heading with optional tooltip
     *
     * @param string $tag
     * @param string $text
     * @param string $tooltip
     * @param bool $return Whether to return (true) or echo (false) the HTML
     * @param bool $intable Whether the element should be rendered as a table row or using divs
     * @return string|void Return html or echo depending on $return parameter
     */
    public function heading($tag, $text, $tooltip = '', $return = false, $intable = false)
    {
        if ($intable) {
            $html = '<tr class="formrow">';
            $html .= '<td class="formlabel heading">';
            $html .= $this->tag($tag, $text, '', null, true);
            $html .= '</td>';
            $html .= '<td class="formtooltip">';
            if (!empty($tooltip)) {
                $html .= $this->tooltip($tooltip, true, false);
            }
            $html .= '</td>';
            $html .= '<td>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '</td>';
            $html .= '</tr>';
        } else {
            $html = $this->start_div('heading', true);
            $html .= $this->start_div('', true);
            $html .= $this->tag($tag, $text, '', null, true);
            $html .= $this->end_div(true);
            if (!empty($tooltip)) {
                $html .= $this->tooltip($tooltip, true, false);
            }
            $html .= $this->end_div(true);
        }

        if ($return) {
            return $html;
        }
        echo "\n" . $html;
    }
}
