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
 *
 * Utility class for languages and translations.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package core
 */
class langpack
{
    /**
     * Server lang directory type
     * i.e. en
     */
    private $langdir;

    /**
     * Constructor
     */
    public function __construct()
    {
        $configObject = Config::get_instance();
        $cfg_web_root = $configObject->get('cfg_web_root');
        $this->langdir = LangUtils::getLang($cfg_web_root);
    }

    /**
     * Get lang filename of component
     * @param array $component lang component name
     * @return string absolute lang file name
     */
    private function get_filename($component)
    {
        $componentparts = explode('/', $component);
        $file = array_pop($componentparts);
        $path = implode(DIRECTORY_SEPARATOR, $componentparts);
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR
            . 'lang' . DIRECTORY_SEPARATOR . $this->langdir . DIRECTORY_SEPARATOR . $file . '.lang.php';
        return $filename;
    }

    /**
     * Get the string value.
     * @param string $component lang file component name
     * @param string $name string to translate
     * @return string translated value
     */
    public function get_string($component, $name)
    {
        $filename = $this->get_filename($component);
        include $filename;
        return $string[$name];
    }

    /**
     * Get the value of X strings.
     * @param string $component lang file component name
     * @param array $names strings to translate
     * @return array list of translated values
     */
    public function get_strings($component, $names)
    {
        $filename = $this->get_filename($component);
        include $filename;
        $strings = array();
        foreach ($names as $name) {
            $strings[$name] = $string[$name];
        }
        return $strings;
    }

    /**
     * Get the value of all strings for the component.
     * @param string $component lang file component name
     * @return array list of translated values
     */
    public function get_all_strings($component)
    {
        $string = array();
        $filename = $this->get_filename($component);
        include $filename;
        return $string;
    }
}
