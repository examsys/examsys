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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/errors.php';

// Check for key parameters
$crypt_name = check_var('id', 'REQUEST', true, false, true);

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));
$configObject->set_db_object($mysqli);
// Get some properties from the paper.
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($crypt_name, $mysqli, $string, true);
$paperID = $propertyObj->get_property_id();
$paper_password = $propertyObj->get_decrypted_password();
$original_paper_type = $propertyObj->get_paper_type();

if ($paper_password == '') {
  $notice->access_denied($mysqli, $string, $string['notallowed'], true, true);
}

$newauth = array(
  array('loginformfields', array('storedata' => true, 'fields' => array(array('name'=>'reviewer_name', 'description'=>$string['name'], 'type'=>'text', 'defaultvalue'=>''), array('name'=>'reviewer_email', 'description'=>$string['email'], 'type'=>'text', 'defaultvalue'=>''))), 'SCT Reviewer Data Info'),
  array('fixedlist', array('authusers' => array('sctreviewer' => $paper_password)), 'SCT Reviewer List')
);
$configObject->set('authentication', $newauth);

session_name('RogoAuthentication');
$return = session_start();

$authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
$authentication->do_authentication($string);

if ($authentication->get_userid() != -9999) {
  $notice->access_denied($mysqli, $string, $string['authfailed'], true, true);
}
?>