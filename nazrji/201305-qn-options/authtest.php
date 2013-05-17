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


//require_once './include/staff_student_auth.inc';


$root = str_replace('/include', '/', str_replace('\\', '/', dirname(__FILE__)));
require_once $root . '/include/auth.inc';
include_once $root . '/include/load_config.php';
if (!file_exists($root . 'config/config.inc.php')) {
  $cfg_root_path = rtrim('/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $root), '/'), '/');
  $notice->display_notice_and_exit($mysqli, 'Install Required', 'Rog&#333; needs installing before use. The Installer is avalable <a href="' . $cfg_root_path . '/install/index.php">here</a>.', 'Install Required', '../artwork/software_64.png', $title_color = '#C00000', true, true);
}

require_once $cfg_web_root . 'classes/configobject.class.php';
require_once $cfg_web_root . 'classes/lang.class.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/common.inc'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require_once $cfg_web_root . 'classes/dbutils.class.php';
require_once $cfg_web_root . 'classes/networkutils.class.php';
require_once $cfg_web_root . 'classes/userobject.class.php';
require_once $cfg_web_root . 'classes/authentication.class.php';


$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_staff_user'), $configObject->get('cfg_db_staff_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));



//array('alreadyloggedin', array('disabled' => true), 'Disabled Already Logged In'),
$newauth=array(
array('loginformfields',array('fields' => array(array('name'=>'Button1','description'=>'Description1','type'=>'input','defaultvalue'=>''),array('name'=>'Button2','description'=>'Description2','type'=>'input','defaultvalue'=>''))),'SCT Reviewer Data Info'),
array('fixedlist', array('authusers' => array('sctreviewer' => '@Password1')), 'SCT Reviewer List')
);
$configObject->set('authentication',$newauth);



session_name('RogoAuthentication');
$return = session_start();

$authentication = new Authentication($configObject, $mysqli, $_REQUEST, $_SESSION);
$authentication->do_authentication($string);

if($authentication->username!='sctreviewer') {
  print "not expected user";
  exit();
}




$getauthobj = new stdClass();
//$authentication->get_auth_obj($getauthobj);

//$userObject = UserObject::get_instance();
//$userObject->db_user_change();

$authentication->display_debug();
$userObject = UserObject::get_instance();
var_dump($authentication);
var_dump($_REQUEST);
var_dump($userObject);