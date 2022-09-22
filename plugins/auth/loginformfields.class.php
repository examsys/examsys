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
 * Handles extra fields at login
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class loginformfields_auth extends outline_authentication
{
    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        // maybe need to think of some function that will fail the auth if not received the data in the field?

        $callbackarray[] = array(array($this, 'loginformfields'), 'displaystdform', $this->number, $this->name);
        if (isset($this->settings['storedata']) and $this->settings['storedata'] === true) {
            $callbackarray[] = array(array($this, 'store_data'), 'sessionstore', $this->number, $this->name);
        }

        return $callbackarray;
    }

    public function store_data($sessionstoreobj)
    {
        $list = array();
        if (isset($this->settings['fields']) and is_array($this->settings['fields'])) {
            foreach ($this->settings['fields'] as $fielddata) {
                if (is_object($fielddata)) {
                    $list[] = $fielddata->name;
                } else {
                    $list[] = $fielddata['name'];
                }
            }
        }
        foreach ($list as $name) {
            $this->savetodebug('session store of input data key is ' . $name);
            if (isset($_REQUEST[$name])) {
                    $this->session['authenticationObj']['loginformfields'][$name] = $_REQUEST[$name];
            }
        }

        return $sessionstoreobj;
    }

    public function loginformfields($displaystdformobj)
    {
        global $string;
        $this->savetodebug('Adding Login Form Fields');
        if (isset($this->settings['fields']) and is_array($this->settings['fields'])) {
            foreach ($this->settings['fields'] as $fielddata) {
                if (is_object($fielddata)) {
                    $this->savetodebug('Adding New Field as object');
                    $displaystdformobj->fields[] = $fielddata;
                } else {
                    $this->savetodebug('Adding New Field of type:' . $fielddata['type'] . ' with name:' . $fielddata['name'] . ' description:' . $fielddata['description'] . ' and default value of:' . $fielddata['defaultvalue']);
                    $newfield = new displaystdformobjfield();
                    $newfield->type = $fielddata['type'];
                    $newfield->description = $fielddata['description'];
                    $newfield->defaultvalue = $fielddata['defaultvalue'];
                    $newfield->name = $fielddata['name'];
                    $displaystdformobj->fields[] = $newfield;
                }
            }
        }

        return $displaystdformobj;
    }
}
