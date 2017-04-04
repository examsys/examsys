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
 * Search for users linked to the the external system.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package LTi
 */

require_once dirname(__DIR__) . '/include/sysadmin_auth.inc';
require_once dirname(__DIR__) . '/include/errors.php';
require_once __DIR__ . '/ims-lti/UoN_LTI.php';
require_once dirname(__DIR__) . '/include/toprightmenu.inc';

// Required parameters for the page.
$lti_key = check_var('LTIkeysid', '_GET', true, true, true);
// Optional parameters.
$internalid = check_var('internalid', '_POST', false, true, true);
$externalid = check_var('externalid', '_POST', false, true, true);

// Check a valid LTi link had been specified.
$lti = new UoN_LTI();
$lti->init_lti0($mysqli);
if (!$lti->lti_key_exists($lti_key)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$lti_details = $lti->get_lti_key($lti_key);

// Get the data to be displayed on the page.
$formvalues = array();
if (!is_null($internalid)) {
  $internal_results = $lti->get_links_by_username($internalid, $lti_details['id']);
  $formvalues['internalid'] = $internalid;
} else {
  $internal_results = array();
}

if (!is_null($externalid)) {
  $external_results = $lti->get_user_by_external_id($externalid, $lti_details['oauth_consumer_key']);
  $formvalues['externalid'] = $externalid;
} else {
  $external_results = array();
}

$results = array_merge($external_results, $internal_results);

// Render the page.
$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$additionaljs = <<<JS
  <script type="text/javascript" src="../js/list.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="js/search_users.js"></script>
JS;
$additionalcss = <<<CSS
  <link rel="stylesheet" type="text/css" href="../css/list.css"/>
CSS;
$breadcrumb = array(
  $string['home'] => "../../index.php",
  $string['administrativetools'] => "../admin/index.php",
  $string['ltikeys'] => "lti_keys_list.php"
);
$lang = array(
  'title' => sprintf($string['ltiusersearch'], $lti_details['name']),
  'unlink' => $string['deletelink'],
  'search' => $string['search'],
  'search_desc' => $string['searchdesc'],
  'searchinternalid' => $string['searchinternalid'],
  'searchexternalid' => $string['searchexternalid'],
);
$menuimages = array(
  'unlink_dimmed' => 'red_cross_grey.png',
  'unlink' => 'red_cross.png',
);
$menuscripts = array(
  'search' => "?LTIkeysid=$lti_key",
  'unlink' => "../delete/check_unlink_user.php?LTIkeysid=$lti_key",
  'form' => $formvalues,
);

$render->render_admin_header($lang, $additionaljs, $additionalcss);
$render->render_admin_options($menuscripts, $menuimages, $lang, $toprightmenu, 'lti/user_search_menu.html');
$render->render_admin_content($breadcrumb, $lang);
$render->render($results, $string, 'lti/search_users.html');
$render->render_admin_footer();
