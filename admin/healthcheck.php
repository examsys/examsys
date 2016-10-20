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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
  require "../include/load_config.php";
  $error = false;
  // Check access to database.
  $mysqli = new mysqli($configObject->get('cfg_db_host') , $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_database'), 3306);
  if ($mysqli->connect_error) {
    echo "ERROR::Can not Connect to MySQL on " . $configObject->get('cfg_db_host') . "\n";
    $error = true;
  }

  $ldapserver = '';
  $auth_array = $configObject->get('authentication');
  foreach ($auth_array as $auth_settings) {
    if ($auth_settings[0] == 'ldap') {
      $ldapserver = $auth_settings[1]['ldap_server'];
      $ldaprdn = $auth_settings[1]['ldap_bind_rdn'];
      $ldappass = $auth_settings[1]['ldap_bind_password'];
    }
  }
  // Check access to LDAP.
  if ($ldapserver != '') {
    $ldap = ldap_connect($ldapserver);
    if (!ldap_bind($ldap, $ldaprdn, $ldappass)) {
      echo "ERROR::Can not Connect to LDAP @ ". $ldapserver . "\n";
      $error = true;
    }
  }
  
  // Check access to media directory.
  try {
    $mediadir = rogo_directory::get_directory('media');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on media directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to media directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to email_templates directory.
  try {
    $mediadir = rogo_directory::get_directory('email_templates');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on email_templates directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to email_templates directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to qti_export directory.
  try {
    $mediadir = rogo_directory::get_directory('qti_export');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on qti_export directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to qti_export directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to qti_import directory.
  try {
    $mediadir = rogo_directory::get_directory('qti_import');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on qti_import directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to qti_import directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to user_photo directory.
  try {
    $mediadir = rogo_directory::get_directory('user_photo');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on user_photo directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to user_photo directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to help_student directory.
  try {
    $mediadir = rogo_directory::get_directory('help_student');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on help_student directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to help_student directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check access to help_staff directory.
  try {
    $mediadir = rogo_directory::get_directory('help_staff');
    if (!$mediadir->check_permissions()) {
      echo "ERROR::Invalid permissions on help_staff directory: " . $mediadir->location() . "\n";
      $error = true;
    }
  } catch (directory_not_found $e) {
    echo "ERROR::Can not Connect to help_staff directory: " . $e->getMessage() . "\n";
    $error = true;
  }
  // Check memcache.
  $hosts = $configObject->get('cfg_memcache_host');
  if (!empty($hosts)) {
    $port = $configObject->get('cfg_memcache_port');
    $memcache = new Memcache;
    $servers = array();
    foreach ($hosts as $memcacheserver) {
        // Add servers.
        $memcache->addServer($memcacheserver, $port);
        $servers[] = $memcacheserver;
    }
    // Get stats to reforce cache.
    $stats = $memcache->getExtendedStats();
    // Get status of servers.
    $memcacheerror = 0;
    foreach ($servers as $host) {
        $status = $stats[$host. ':' . $port];
        if (!$status) {
            $memcacheerror++;
        }
    }
    if ($memcacheerror == count($servers)) {
      echo "ERROR::Memcache server failure\n";
      $error = true;
    };
  }
  if (!$error) {
    echo "Tickety-Boo";
  }

?>