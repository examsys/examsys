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
 * Delete the LTi link between an external system and ExamSys.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package LTi
 */

require_once dirname(__DIR__) . '/include/sysadmin_auth.inc';
require_once dirname(__DIR__) . '/include/errors.php';
require_once dirname(__DIR__) . '/LTI/ims-lti/UoN_LTI.php';

// Required parameters for the page.
$lti_key = check_var('LTIkeysid', '_GET', true, true, true);
$selected = check_var('id', '_GET', true, true, true);
$selected_array = explode('-', $selected);

// Check a valid LTi link had been specified.
$lti = new UoN_LTI();
$lti->init_lti0($mysqli);
if (!$lti->lti_key_exists($lti_key)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Check the information to be deleted is in the correct form.
if (!is_array($selected_array) || count($selected_array) !== 2) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Do the delete.
$lti_details = $lti->get_lti_key($lti_key);
$lti->delete_user_link($selected_array[0], $lti_details['oauth_consumer_key'], $selected_array[1]);

// Display a confirmation it is done.
$render = new render($configObject);
$headerdata = array(
  'css' => array(
    '/css/check_delete.css',
  ),
  'scripts' => array(
    '/js/require.js',
    '/js/main.min.js',
    '/LTI/js/unlink_user.min.js',
  ),
);
$data = array(
  'message' => $string['unlinked'],
  'button' => $string['ok'],
);

$render->render($headerdata, $string, 'header.html');
$render->render($data, $string, 'message.html');
$render->render_admin_footer();
