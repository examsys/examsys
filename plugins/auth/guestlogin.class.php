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
 * Handles Guest account access in ExamSys
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';

class guestlogin_auth extends outline_authentication
{
    public $impliments_api_auth_version = 1;
    public $version = 0.9;

    public function register_callback_routines()
    {
        $callbackarray[] = array(array($this, 'loginbutton'), 'displaystdform', $this->number, $this->name);

        return $callbackarray;
    }

    public function errordisp($displayerrformobj)
    {
        global $string;
        $configObject = Config::get_instance();
        $cfg_root_path = $configObject->get('cfg_root_path');
        if ($_SERVER['PHP_SELF'] == "$cfg_root_path/index.php") {
            $this->savetodebug('adding temp account notice to error screen');
            $message2 = $string['ifstuckinvigilator'] . " <a href=\"$cfg_root_path/users/guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . '</strong></a>';
            $displayerrformobj->li[] = $message2;
        }

        return $displayerrformobj;
    }

    public function loginbutton($displaystdformobj)
    {
        global $string;
        $config = Config::get_instance();
        $this->savetodebug('Button Check');
        // Check client address of current user is in a lab.
        $address = NetworkUtils::get_client_address();
        $labfactory = new LabFactory($this->db);
        $lab = false;
        $lab = $labfactory->get_lab_from_address($address);
        if ($lab) {
            $this->savetodebug('Adding New Button');
            $newbutton = new displaystdformobjbutton();
            $newbutton->type = 'button';
            $newbutton->value = ' ' . $string['guestbutton'] . ' ';
            $newbutton->name = 'guestlogin';
            $newbutton->class = 'guestlogin';
            $displaystdformobj->buttons[] = $newbutton;
            $displaystdformobj->scripts[] = 'guestlogin.min.js';
        }

        return $displaystdformobj;
    }
}
