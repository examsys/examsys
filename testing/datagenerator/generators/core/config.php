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

namespace testing\datagenerator;

/**
 * Sets config settings for testing.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class config extends generator
{
    /**
     * Changes a ExamSys setting.
     *
     * @param array|stdClass $data
     * @throws data_error
     */
    public function change_setting($data)
    {
        // If an object is passed convert it into an array.
        if (is_object($data)) {
            $data = (array)$data;
        }
        // Check that the right type has been passed.
        if (!is_array($data)) {
            throw new data_error('Must pass an array or object');
        }
        $defaults = array(
            'component' => 'core',
            'setting' => 'no_setting',
            'value' => '',
        );
        $values = $this->set_defaults_and_clean($defaults, $data);
        $component = $values['component'];
        $setting = $values['setting'];
        $value = $values['value'];

        $config = \Config::get_instance();
        $type = $config->get_setting_type($component, $setting);
        if (is_null($type)) {
            throw new data_error("$component/$setting is not a valid ExamSys setting");
        }
        if (!$config->check_type($value, $type)) {
            throw new data_error("$value is not of the expected type ($type)");
        }
        $config->set_setting($setting, $value, $type, $component);
    }
}
