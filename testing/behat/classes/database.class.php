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

namespace testing\behat;
use InstallUtils,
    Config,
    cli_utils,
    mysqli;

/**
 * This class is used to manage the database used for Rogo behat testing.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @category behat
 */
class database {
  /**
   * Creates a Rogo database for behat testing.
   * 
   * @throws Exception
   */
  public static function install_database() {
    $config = Config::get_instance();
    $config->use_behat_site();
    InstallUtils::$cli = true;
    InstallUtils::$behat_install = true;
    // Check that the php environment is setup correctly.
    InstallUtils::checkSoftware();
    // Setup the InstallUtils class for installation.
    InstallUtils::$cfg_db_basename = $config->get('cfg_db_database');
    InstallUtils::$cfg_db_name = $config->get('cfg_db_database');
    InstallUtils::$cfg_web_host = $config->get('cfg_web_host');
    InstallUtils::$cfg_rogo_data = $config->get('cfg_behat_data');
    $connected = self::get_db_details();
    if (!$connected) {
      throw new Exception('Could not connect to database. Aborting.');
    }

    // Preset the database usernames to the details of the live site.
    InstallUtils::$cfg_db_username = $config->get('base_database') . '_auth';
    InstallUtils::$cfg_db_student_user = $config->get('base_database') . '_stu';
    InstallUtils::$cfg_db_staff_user = $config->get('base_database') . '_staff';
    InstallUtils::$cfg_db_external_user = $config->get('base_database') . '_ext';
    InstallUtils::$cfg_db_sysadmin_user = $config->get('base_database') . '_sys';
    InstallUtils::$cfg_db_webservice_user = $config->get('base_database') . '_web';
    InstallUtils::$cfg_db_sct_user = $config->get('base_database') . '_sct';
    InstallUtils::$cfg_db_inv_user = $config->get('base_database') . '_inv';
    InstallUtils::$cfg_cron_user = 'cron';

    // Details of the admin user.
    InstallUtils::$sysadmin_username = 'admin';
    InstallUtils::$sysadmin_password = 'admin';
    InstallUtils::$sysadmin_first = 'Admin';
    InstallUtils::$sysadmin_last = 'User';
    InstallUtils::$sysadmin_title = 'Miss';
    InstallUtils::$sysadmin_email = 'admin@example.com';

    // Ensure that an existing Rogo behat database and users are deleted.
    self::drop_db();

    // Start installing the base Rogo database.
    InstallUtils::checkDBUsers();
    InstallUtils::createDirectories();
    InstallUtils::createDatabase($config->get('cfg_db_database'), $config->get('cfg_db_charset'));
  }

  /**
   * Gets the database admin username and password.
   *
   * @return boolean
   */
  public static function get_db_details() {
    $config = Config::get_instance();
    cli_utils::prompt('Database setup');
    InstallUtils::$db_admin_username = $config->get('cfg_behat_db_user');
    InstallUtils::$db_admin_passwd = $config->get('cfg_behat_db_password');
    $connected = self::connect_database(InstallUtils::$db_admin_username, InstallUtils::$db_admin_passwd);
    return $connected;
  }

  /**
   * Drop the behat database and users.
   */
  public static function drop_db() {
    $config = Config::get_instance();
    $config->use_behat_site();
    $basedb = $config->get('base_database');
    // If it exists drop the behat database.
    $dbname = InstallUtils::$cfg_db_name;
    $dbaccesspoint = InstallUtils::$cfg_web_host;
    $res = InstallUtils::$db->prepare("SHOW DATABASES LIKE '$dbname'");
    $res->execute();
    $res->store_result();
    if ($res->num_rows > 0) {
      InstallUtils::$db->query("DROP DATABASE $dbname");
    }

    // Remove permissions from the DB users.
    $usernames = array('auth'=>300, 'stu'=>301, 'staff'=>302, 'ext'=>303, 'sys'=>304, 'sct'=>305, 'inv'=>306);
    foreach ($usernames as $username=>$err_code){
      $test_username = $basedb . '_' . $username;
      if (InstallUtils::does_user_exist($test_username)) {
        InstallUtils::$db->query("REVOKE ALL PRIVILEGES ON $dbname.* FROM '$test_username'@'$dbaccesspoint'");
      }
    }
  }

  /**
   * Connect to the Rogo database.
   *
   * @param string $username
   * @param string $password
   * @return boolean
   */
  public static function connect_database($username, $password) {
    $config = Config::get_instance();
    $config->use_behat_site();
    InstallUtils::$db = new mysqli($config->get('cfg_db_host'), $username, $password, '', $config->get('cfg_db_port'));
    if (mysqli_connect_error()) {
      InstallUtils::$db = null;
      return false;
    }
    return true;
  }
}
