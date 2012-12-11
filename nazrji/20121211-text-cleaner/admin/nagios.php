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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/ 
  require "../include/load_config.php";
  require_once $cfg_web_root . 'classes/dbutils.class.php';
  $error = false;
  $mysqli = DBUtils::get_mysqli_link($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);
  if (mysqli_connect_error()) {
    echo "ERROR::Can not Connect to MySQL on $cfg_db_host";
    $error = true;
  }
  
  $ldap = ldap_connect( $cfg_ldap_server );
  if (!ldap_bind( $ldap ) ) {
    echo "ERROR::Can not Connect to LDAP @ $cfg_ldap_server";
    $error = true;
  }
  
  if (!$error) {
    echo "OK";
  }

?>