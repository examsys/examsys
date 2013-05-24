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
* Script used by Nagios to check the service is running
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
  require "../include/load_config.php";
  require_once $cfg_web_root . 'classes/dbutils.class.php';
  require_once $cfg_web_root . 'classes/usernotices.class.php';
  $error = false;
  $notice = UserNotices::get_instance();
  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));
  if (mysqli_connect_error()) {
    echo "ERROR::Can not Connect to MySQL on " . $configObject->get('cfg_db_host');
    $error = true;
  }

  $cfg_ldap_server = '';
  $auth_array = $configObject->get('authentication');
  foreach ($auth_array as $auth_settings) {
    if ($auth_settings[0] == 'ldap') {
      $cfg_ldap_server = $auth_settings[1]['ldap_server'];
    }
  }

  if ($cfg_ldap_server == '') {
    echo "ERROR::No LDAP server defined";
    $error = true;
  } else {
    $ldap = ldap_connect($cfg_ldap_server);
    if (!ldap_bind( $ldap ) ) {
      echo "ERROR::Can not Connect to LDAP @ ". $cfg_ldap_server;
      $error = true;
    }
  }

  if (!$error) {
    echo "OK";
  }

?>