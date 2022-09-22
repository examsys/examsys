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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

function draw_toprightmenu($helpID = 1)
{
    global $string;

    $configObject = Config::get_instance();
    $userObject       = UserObject::get_instance();
    $rootpath = $configObject->get('cfg_root_path');
    $html = '';

    if ($userObject->is_impersonated()) {
        $impersonated_name = $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname();
        $html .= '<div id="toprightmenu" style="width:250px">';
        $html .= '<div class="impersonate"><img src="' . $rootpath . '/artwork/agent.png" width="32" height="32" alt="Impersonate" style="float:left; padding-right:6px"/><div style="padding-top:1px; padding-right:1.4em">' . $string['loggedinas'] . ':<br /><strong>' . $impersonated_name . '</strong></div></div>';
    } else {
        $html .= '<div id="toprightmenu">';
    }

    if ($userObject->has_role('SysAdmin')) {
        $html .= '<div class="trm_div" id="admintools">' . $string['administrativetools'] . '</div>';
    }
    $role = 'student';
    if ($userObject->has_role(array('Staff'))) {
        $role = 'staff';
    }
    $html .= "<div tabindex='0' class=\"trm_div\" id=\"helplink\" data-role=\"$role\" data-id=\"$helpID\">" . $string['helpsupport'] . '</div>';

    // Only allow access to valid students i.e. not guest accounts.
    if (
        $role === 'student' and
        $configObject->get_setting('core', 'system_user_accessibility')
        and UserUtils::username_is_valid($userObject->get_username())
    ) {
        $html .= '<div tabindex="0" class="trm_div userprofile" id="userprofile">' . $string['userprofile'] . '</div>';
    }

    $html .= '<div tabindex="0" class="trm_div" id="signout" style="background-image: url(\'' . $rootpath . '/artwork/logout16.gif\'); background-repeat: no-repeat; background-position: 0 3px">' . $string['signout'] . '</div>';
    $html .= '<div tabindex="0" class="trm_div" id="aboutrogo">' . sprintf($string['aboutrogo'], $configObject->get_setting('core', 'rogo_version')) . '</div>';

    $html .= '</div>';

    return $html;
}
