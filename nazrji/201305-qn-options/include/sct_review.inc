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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/dbutils.class.php';
require_once $cfg_web_root . 'include/auth.inc';
require_once $cfg_web_root . 'classes/authentication.class.php';
require_once $cfg_web_root . 'include/errors.inc';

// Check for key parameters
$crypt_name = check_var('id', 'RESPONSE', true, false, true);

$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

$stmt = $mysqli->prepare("SELECT property_id, password FROM properties WHERE crypt_name=?");
$stmt->bind_param('s', $crypt_name);
$stmt->execute();
$stmt->bind_result($paperID, $paper_password);
$stmt->fetch();
$stmt->close();

if ($paper_password == '') {
  $notice->access_denied($mysqli, $string, $string['notallowed'], true, true);
}

$newauth=array(
  array('loginformfields',array('storedata' => true, 'fields' => array(array('name'=>'reviewer_name', 'description'=>$string['name'], 'type'=>'input', 'defaultvalue'=>''), array('name'=>'reviewer_email', 'description'=>$string['email'], 'type'=>'input', 'defaultvalue'=>''))), 'SCT Reviewer Data Info'),
  array('fixedlist', array('authusers' => array('sctreviewer' => $paper_password)), 'SCT Reviewer List')
);
$configObject->set('authentication', $newauth);

session_name('RogoAuthentication');
$return = session_start();

$authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
$authentication->do_authentication($string);

if($authentication->get_userid() != -9999) {
  $notice->access_denied($mysqli, $string, $string['authfailed'], true, true);
}


function getPaperTitle($paperID, $db) {
  $paper_title = '';
  
  $stmt = $db->prepare("SELECT paper_title FROM properties WHERE property_id=?");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->bind_result($paper_title);
  $stmt->fetch();
  $stmt->close();
  
  return $paper_title;  
}
?>