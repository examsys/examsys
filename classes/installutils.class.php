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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

Class InstallUtils {
  public static $db;
  public static $rogo_path;

  public static $warnings;

  public static $cfg_company;
  public static $cfg_short_date;
  public static $cfg_long_date;
  public static $cfg_long_date_time;
  public static $cfg_short_date_time;
  public static $cfg_long_date_php;
  public static $cfg_short_date_php;
  public static $cfg_long_time_php;
  public static $cfg_short_time_php;
  public static $cfg_search_leadin_length;
  public static $cfg_timezone;
  public static $cfg_tmpdir;
  public static $cfg_tablesorter_date_time;

  //database config options
  public static $cfg_db_host;
  public static $cfg_db_port;
  public static $cfg_db_username;
  public static $cfg_db_password;
  public static $cfg_db_charset;
  public static $cfg_db_engine;
  public static $cfg_db_help_engine;

  public static $cfg_root_path;
  public static $cfg_web_host;
  public static $cfg_rogo_data;
  public static $cfg_db_basename;
  public static $cfg_db_student_user;
  public static $cfg_db_student_passwd;
  public static $cfg_db_staff_user;
  public static $cfg_db_staff_passwd;
  public static $cfg_db_external_user;
  public static $cfg_db_external_passwd;
  public static $cfg_db_internal_user;
  public static $cfg_db_internal_passwd;
  public static $cfg_db_sysadmin_user;
  public static $cfg_db_sysadmin_passwd;
  public static $cfg_db_webservice_user;
  public static $cfg_db_webservice_passwd;
  public static $cfg_db_sct_user;
  public static $cfg_db_sct_passwd;
  public static $cfg_db_inv_user;
  public static $cfg_db_inv_passwd;

  public static $cfg_cron_user;
  public static $cfg_cron_passwd;

  public static $cfg_db_name;
  public static $db_admin_username;
  public static $db_admin_passwd;

  public static $support_email;
  public static $cfg_SysAdmin_username;

  public static $cfg_ldap_server;
  public static $cfg_ldap_search_dn;
  public static $cfg_ldap_bind_rdn;
  public static $cfg_ldap_bind_password;
  public static $cfg_ldap_user_prefix;

  public static $cfg_auth_ldap = false;
  public static $cfg_auth_lti = true;
  public static $cfg_auth_internal = true;
  public static $cfg_auth_guest = true;
  public static $cfg_auth_impersonation = true;

  public static $cfg_lookup_ldap_server;
  public static $cfg_lookup_ldap_search_dn;
  public static $cfg_lookup_ldap_bind_rdn;
  public static $cfg_lookup_ldap_bind_password;
  public static $cfg_lookup_ldap_user_prefix;

  public static $cfg_uselookupLdap = false;
  public static $cfg_uselookupXML = false;

  public static $cfg_labsecuritytype;

  public static $cfg_support_email;
  public static $emergency_support_numbers;

  /** @var bool Stores if this is a behat installation. */
  public static $behat_install = false;

  /** @var bool Stores if this is a phpunit installation. */
  public static $phpunit_install = false;

  /** @var string The username of the admin account. */
  public static $sysadmin_username;
  /** @var string The password for the admin account. */
  public static $sysadmin_password;
  /** @var string The title of the admin user. */
  public static $sysadmin_title;
  /** @var string The first name of the admin user. */
  public static $sysadmin_first;
  /** @var string The last name of the admin user. */
  public static $sysadmin_last;
  /** @var string The e-mail address for the admin user. */
  public static $sysadmin_email;

  /** Stores if the install is being done via cli. */
  public static $cli = false;

  /** Stores if the settings for cli install. */
  private static $settings;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  static function displayForm() {
    global $string, $language, $timezone_array;
    $configObject = Config::get_instance();
    $render = new render($configObject);
    $data['action'] = Url::fromGlobals();
    $data['dirsep'] = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    $default_timezone = date_default_timezone_get();
    if ($default_timezone == 'UTC') $default_timezone = 'Europe/London';
    $data['timezones'] = $timezone_array;
    $data['defaulttz'] = $default_timezone;
    $data['loadlangpacks'] = true;
    if (LangUtils::langPackInstalled($language)) {
      $data['loadlangpacks'] = false;
    }
    $data['titles'] = explode(',', $string['title_types']);
    $string['translationsurl'] = $configObject->getxml('translations', 'url');
    $render->render($data, $string, '/install/form.html');
  }

  /**
   * Determines if a database user already exists.
   *
   * @param string $username - The name of the user to be tested.
   *
   * @return bool - True = user exists, False = user does not exist.
   */
  static function does_user_exist($username) {
    $result = self::$db->prepare('SELECT User FROM mysql.user WHERE user = ?');
    $result->bind_param('s', $username);
    $result->execute();
    $result->store_result();
    $num_rows = $result->num_rows;

    $result->close();

    if ($num_rows < 1) {
      return false;
    }

    return true;
  }

  /**
   * Load and verify settings file.
   */
  static function loadSettings() {
    self::$settings = json_encode(simplexml_load_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.xml', 'SimpleXMLElement', LIBXML_NOCDATA));
  }

  /**
   * Get settings.
   *
   * @param integer $type parameter type
   * @param boolean $required is parameter required
   * @param string $parent name of xml node
   * @param string $child xml child node name
   * @param string $grandchild xml grandchild node name
   * @return value of xml node
   */
  static function getSettings($type, $required, $parent, $child = '', $grandchild = '') {
    $setting = $parent . '//' . $child . '//' . $grandchild;
    $xmldata = json_decode(self::$settings);
    if (is_string($parent)) {
      if (isset($xmldata->$parent)) {
        if ($child == '' and $grandchild == '') {
          return self::check_setting($xmldata->$parent, $type, $required, $setting);
        } elseif ($child != '' and $grandchild == '') {
          if (isset($xmldata->$parent->$child)) {
             return self::check_setting($xmldata->$parent->$child, $type, $required, $setting);
          }
        } else {
          if (isset($xmldata->$parent->$child->$grandchild)) {
             return self::check_setting($xmldata->$parent->$child->$grandchild, $type, $required, $setting);
          }
        }
      }
    }
    return self::check_setting(null, $type, $required, $setting);
  }

  /**
   * Check and clean a setting
   *
   * @param string $value value of setting
   * @param integer $type type of setting
   * @param boolean $required is setting required
   * @param string $setting xml path of setting
   * @return cleaned settings
   */
  private static function check_setting($value, $type, $required, $setting) {
    global $string;
    if (is_array($value)) {
      $clean = param::clean_array($value, $type, $required);
    } else {
      $clean = param::clean($value, $type);
    }
    if (empty($clean) or $clean === false) {
      if ($required) {
        throw new MissingParameter(sprintf($string['invalidsetting'], $setting));
      } else {
        switch ($type) {
          case param::BOOLEAN:
            $clean = false;
            break;
          default:
            $clean = null;
            break;
        }
      }
    }
    return $clean;
  }

  static function processForm($args = array()) {
    global $string, $cfg_encrypt_salt;
    $configObject = Config::get_instance();

    if (!self::$cli) {
      self::$cfg_company = param::required('company_name', param::TEXT, param::FETCH_POST);
      self::$cfg_db_host = param::required('mysql_db_host', param::TEXT, param::FETCH_POST);
      self::$cfg_db_port = param::required('mysql_db_port', param::INT, param::FETCH_POST);
      self::$cfg_db_name = param::required('mysql_db_name', param::TEXT, param::FETCH_POST);
      self::$cfg_db_engine = param::required('mysql_db_engine', param::ALPHA, param::FETCH_POST);
      self::$cfg_db_help_engine = param::required('mysql_db_help_engine', param::ALPHA, param::FETCH_POST);
      self::$db_admin_username = param::required('mysql_admin_user', param::TEXT, param::FETCH_POST);
      self::$db_admin_passwd = param::required('mysql_admin_pass', param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_company = self::getSettings(param::TEXT, true, 'company');
      self::$cfg_db_host = param::clean($args['mysql_db_host'], param::TEXT);
      self::$cfg_db_port = param::clean($args['mysql_db_port'], param::INT);
      self::$cfg_db_name = param::clean($args['mysql_db_name'], param::TEXT);
      self::$cfg_db_engine = self::getSettings(param::ALPHA, true, 'database', 'engine');
      self::$cfg_db_help_engine = self::getSettings(param::ALPHA, true, 'database', 'help_engine');
      self::$db_admin_username = param::clean($args['mysql_admin_user'], param::TEXT);
      self::$db_admin_passwd = param::clean($args['mysql_admin_pass'], param::TEXT);
    }

    self::$cfg_db_charset = 'utf8';

    // Check mysql version.
    if (!requirements::check_db(self::$cfg_db_host, self::$db_admin_username, self::$db_admin_passwd)) {
      $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
      self::displayError(array('002' => sprintf($string['errors17'], $mysql_min_ver)), true);
    }

    if (!self::$cli) {
      self::$cfg_web_host = param::required('web_host', param::TEXT, param::FETCH_POST);
      self::$cfg_rogo_data = param::required('rogo_data', param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_web_host = self::getSettings(param::TEXT, true, 'server', 'host');
      self::$cfg_rogo_data = self::getSettings(param::TEXT, true, 'server', 'data');
    }
    if (!file_exists(self::$cfg_rogo_data)) {
      self::displayError(array('003' => sprintf($string['errors18'], self::$cfg_rogo_data)));
    }
    if (!is_writable(self::$cfg_rogo_data)) {
      self::displayError(array('004' => sprintf($string['errors19'], self::$cfg_rogo_data)));
    }
    self::createDirectories();

    // On windows we must escape the slashes.
    self::$cfg_rogo_data = str_replace('\\', '\\\\', self::$cfg_rogo_data);

    if (!self::$cli) {
      self::$cfg_db_basename = param::required('mysql_baseusername', param::TEXT, param::FETCH_POST);
      self::$cfg_SysAdmin_username = param::required('SysAdmin_username', param::TEXT, param::FETCH_POST);
      self::$cfg_short_date = param::required('cfg_short_date', param::TEXT, param::FETCH_POST);
      self::$cfg_long_date = param::required('cfg_long_date', param::TEXT, param::FETCH_POST);
      self::$cfg_long_date_time = param::required('cfg_long_date_time', param::TEXT, param::FETCH_POST);
      self::$cfg_short_date_time = param::required('cfg_short_date_time', param::TEXT, param::FETCH_POST);
      self::$cfg_long_date_php = param::required('cfg_long_date_php', param::TEXT, param::FETCH_POST);
      self::$cfg_short_date_php = param::required('cfg_short_date_php', param::TEXT, param::FETCH_POST);
      self::$cfg_long_time_php = param::required('cfg_long_time_php', param::TEXT, param::FETCH_POST);
      self::$cfg_short_time_php = param::required('cfg_short_time_php', param::TEXT, param::FETCH_POST);
      self::$cfg_search_leadin_length = param::required('cfg_search_leadin_length', param::INT, param::FETCH_POST);
      self::$cfg_timezone = param::required('cfg_timezone', param::TEXT, param::FETCH_POST);
      self::$cfg_tmpdir = param::required('tmpdir', param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_db_basename = self::getSettings(param::TEXT, true, 'database', 'prefix');
      self::$cfg_SysAdmin_username = self::getSettings(param::TEXT, true, 'sysadmin', 'username');
      self::$cfg_short_date = self::getSettings(param::TEXT, true, 'timedate', 'mysqlshortdate');
      self::$cfg_long_date = self::getSettings(param::TEXT, true, 'timedate', 'mysqllongdate');
      self::$cfg_long_date_time = self::getSettings(param::TEXT, true, 'timedate', 'mysqllongdatetime');
      self::$cfg_short_date_time = self::getSettings(param::TEXT, true, 'timedate', 'mysqlshortdatetime');
      self::$cfg_long_date_php = self::getSettings(param::TEXT, true, 'timedate', 'phplongdate');
      self::$cfg_short_date_php = self::getSettings(param::TEXT, true, 'timedate', 'phpshortdate');
      self::$cfg_long_time_php = self::getSettings(param::TEXT, true, 'timedate', 'phplongdatetime');
      self::$cfg_short_time_php = self::getSettings(param::TEXT, true, 'timedate', 'phpshortdatetime');
      self::$cfg_search_leadin_length = 160;
      self::$cfg_timezone = self::getSettings(param::TEXT, true, 'timedate', 'timezone');
      self::$cfg_tmpdir = self::getSettings(param::TEXT, true, 'server', 'temp');
    }
    if (self::$cfg_long_date_time == "%d/%m/%Y %H:%i") {
      self::$cfg_tablesorter_date_time = 'uk';
    } else {
      self::$cfg_tablesorter_date_time = 'us';
    }
    //Authentication
    if (!self::$cli) {
      self::$cfg_auth_lti = param::optional('useLti', false, param::BOOLEAN, param::FETCH_POST);
      self::$cfg_auth_internal = param::optional('useInternal', false, param::BOOLEAN, param::FETCH_POST);
      self::$cfg_auth_guest = param::optional('useGuest', false, param::BOOLEAN, param::FETCH_POST);
      self::$cfg_auth_impersonation = param::optional('useImpersonation', false, param::BOOLEAN, param::FETCH_POST);
      self::$cfg_auth_ldap = param::optional('useLdap', false, param::BOOLEAN, param::FETCH_POST);
    } else {
      self::$cfg_auth_lti = self::getSettings(param::BOOLEAN, false, 'authentication', 'lti');
      self::$cfg_auth_internal = self::getSettings(param::BOOLEAN, false, 'authentication', 'internaldb');
      self::$cfg_auth_guest = self::getSettings(param::BOOLEAN, false, 'authentication', 'summativeguestlogin');
      self::$cfg_auth_impersonation = self::getSettings(param::BOOLEAN, false, 'authentication', 'userimpersonation');
      self::$cfg_auth_ldap = self::getSettings(param::BOOLEAN, false, 'authentication', 'ldap');
    }

    //LDAP
    if (!self::$cli) {
      self::$cfg_ldap_server = param::optional('ldap_server', null, param::TEXT, param::FETCH_POST);
      self::$cfg_ldap_search_dn = param::optional('ldap_bind_rdn', null, param::TEXT, param::FETCH_POST);
      self::$cfg_ldap_bind_rdn = param::optional('ldap_bind_rdn', null, param::TEXT, param::FETCH_POST);
      self::$cfg_ldap_bind_password = param::optional('ldap_bind_password', null, param::TEXT, param::FETCH_POST);
      self::$cfg_ldap_user_prefix = param::optional('ldap_user_prefix', null, param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_ldap_server = self::getSettings(param::TEXT, false, 'ldap', 'server');
      self::$cfg_ldap_search_dn = self::getSettings(param::TEXT, false, 'ldap', 'searchdn');
      self::$cfg_ldap_bind_rdn = self::getSettings(param::TEXT, false, 'ldap', 'username');
      self::$cfg_ldap_bind_password = self::getSettings(param::TEXT, false, 'ldap', 'password');
      self::$cfg_ldap_user_prefix = self::getSettings(param::TEXT, false, 'ldap', 'prefix');
    }

    //LDAP for lookup
    if (!self::$cli) {
      self::$cfg_uselookupLdap = param::optional('uselookupLdap', null, param::BOOLEAN, param::FETCH_POST);
      self::$cfg_lookup_ldap_server = param::optional('ldap_lookup_server', null, param::TEXT, param::FETCH_POST);
      self::$cfg_lookup_ldap_search_dn = param::optional('ldap_lookup_search_dn', null, param::TEXT, param::FETCH_POST);
      self::$cfg_lookup_ldap_bind_rdn = param::optional('ldap_lookup_bind_rdn', null, param::TEXT, param::FETCH_POST);
      self::$cfg_lookup_ldap_bind_password = param::optional('ldap_lookup_bind_password', null, param::TEXT, param::FETCH_POST);
      self::$cfg_lookup_ldap_user_prefix = param::optional('ldap_lookup_user_prefix', null, param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_uselookupLdap = self::getSettings(param::BOOLEAN, false, 'lookup', 'ldap');
      self::$cfg_lookup_ldap_server = self::getSettings(param::TEXT, false, 'ldap', 'server');
      self::$cfg_lookup_ldap_search_dn = self::getSettings(param::TEXT, false, 'ldap', 'searchdn');
      self::$cfg_lookup_ldap_bind_rdn = self::getSettings(param::TEXT, false, 'ldap', 'username');
      self::$cfg_lookup_ldap_bind_password = self::getSettings(param::TEXT, false, 'ldap', 'password');
      self::$cfg_lookup_ldap_user_prefix = self::getSettings(param::TEXT, false, 'ldap', 'prefix');
    }
    // XML for lookup.
    if (!self::$cli) {
      self::$cfg_uselookupXML = param::optional('uselookupXML', null, param::BOOLEAN, param::FETCH_POST);
    } else {
      self::$cfg_uselookupXML = self::getSettings(param::BOOLEAN, false, 'lookup', 'xml');
    }
    //ASSISTANCE
    if (!self::$cli) {
      self::$cfg_support_email = param::required('support_email', param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_support_email = self::getSettings(param::TEXT, true, 'supportemail');
    }
    self::$emergency_support_numbers = array();
    for ($i = 1; $i<=3; $i++) {
      if (!self::$cli) {
        $supportname = param::optional("emergency_support$i", null, param::TEXT, param::FETCH_POST);
        if (!is_null($supportname)) {
          self::$emergency_support_numbers[$supportname] = param::optional("emergency_support_number$i", null, param::TEXT, param::FETCH_POST);
        }
      } else {
        $name = self::getSettings(param::TEXT, false, "contact$i", 'name');
        $number = self::getSettings(param::TEXT, false, "contact$i", 'telephone');
        if (is_string($name) and is_string($number)) {
          self::$emergency_support_numbers[$name] = $number;
        }
      }
    }
    //Other settings
    if (!self::$cli) {
      self::$cfg_labsecuritytype = param::optional("labsecurity", false, param::TEXT, param::FETCH_POST);
    } else {
      self::$cfg_labsecuritytype = self::getSettings(param::TEXT, true, "labsecurity", 'type');
    }
    // Check we can write to the config file first if not passwords will be lost!
    self::$rogo_path = dirname(__DIR__);
    if (file_exists(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
      if (!is_writable(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
        self::displayError(array(300=>'Could not write config file!'));
      }
    } elseif (!is_writable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config')) {
      self::displayError(array(300=>'Could not write config file!'));
    }

    //CREATE and populate DB
    self::$db = new mysqli(self::$cfg_db_host, self::$db_admin_username, self::$db_admin_passwd, '', self::$cfg_db_port);

    if (mysqli_connect_error()) {
      self::displayError(array('001' => mysqli_connect_error()));
    }
    self::$db->set_charset(self::$cfg_db_charset);

    //create salt as this is needed to generate the passwords that are created in the next function rather than created during config file settings
    $salt = '';
    $characters = 'abcdefghijklmnopqrstuvwxzyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i=0; $i<16; $i++) {
      $salt .= substr($characters, rand(0,61), 1);
    }
    $cfg_encrypt_salt = $salt;

    $authentication = array(
      array('internaldb', array('table' => '', 'username_col' => '', 'passwd_col' => '', 'id_col' => '', 'sql_extra' => '', 'encrypt' => 'SHA-512', 'encrypt_salt' => $cfg_encrypt_salt), 'Internal Database')
    );
    $configObject->set('authentication', $authentication);

    InstallUtils::checkDBUsers();

    self::createDatabase(self::$cfg_db_name, self::$cfg_db_charset, self::$cfg_db_engine, self::$cfg_db_help_engine);

    // Create constraints.
    self::createConstraints();

    // Load default data
    self::loadData();

    // Update sys_updates table
    self::updateSysUpdates();

    // Get Help and lang pack parameters.
    if (!self::$cli) {
      $load_help = param::optional('loadHelp', false, param::BOOLEAN, param::FETCH_POST);
      $download_lang = param::optional('loadtranslations', false, param::BOOLEAN, param::FETCH_POST);
    } else {
      $configObject->set('cfg_root_path', self::getSettings(param::TEXT, true, 'server', 'root'));
      $load_help = self::getSettings(param::BOOLEAN, false, 'help');
      $download_lang = self::getSettings(param::BOOLEAN, false, 'translations');
    }

    //LOAD help if requested
    if ($load_help) {
      self::loadHelp();
    }
    // Download language packs and install.
    if ($download_lang) {
      try {
        self::download_langpacks();
      } catch (Exception $e) {
        switch ($e->getMessage()) {
          case 'CANNOT_DOWNLOAD_XML':
            self::logWarning(array('600' => $string['cannotdownloadxml']));
          case 'CANNOT_DOWNLOAD_ZIP':
            self::logWarning(array('601' => $string['cannotdownloadzip']));
            break;
          default:
            self::logWarning(array('602' => $string['cannotextract']));
            break;
        }
      }
    }

    //Write out the config file
    self::writeConfigFile();

    if (!is_array(self::$warnings)) {
      $data['displaywarnings'] = true;
      $data['warnings'] = self::$warnings;
    } else {
      $data['displaywarnings'] = false;
    }
    if (!self::$cli) {
      $render = new render($configObject);
      $render->render($data, $string, '/install/processed.html');
    } else {
      cli_utils::prompt($string['installed']);
    }
  }

  /**
   * Download and install language packs.
   */
  static public function download_langpacks() {
    $configObject = Config::get_instance();
    $version = $configObject->getxml('version');
    $url = $configObject->getxml('translations', 'url');
    if (!is_null($url)) {
      $workingdir = getcwd();
      chdir(dirname(__DIR__));
      // Download supported languages xml.
      $url = $configObject->getxml('translations', 'url');
      $fullurl = $url . '/' . $version . '/languages.xml';
      $file = @file_get_contents($fullurl);
      if ($file === false or file_put_contents('languages.xml', $file) === false) {
        throw new Exception('CANNOT_DOWNLOAD_XML');
      }
      // Download language packs.
      $fullurl = $url . '/' . $version . '/rogo.zip';
      $file = @file_get_contents($fullurl);
      if ($file === false or file_put_contents("translations.zip", $file) === false) {
        throw new Exception('CANNOT_DOWNLOAD_ZIP');
      } else {
        // Unzip archive.
        $zip = new ZipArchive;
        $res = $zip->open('translations.zip');
        if ($res === TRUE) {
          $zip->extractTo(getcwd());
          $zip->close();
          // Remove zip and temporary directories.
          unlink('translations.zip');
        } else {
          throw new Exception('CANNOT_EXTRACT');
        }
      }
      chdir($workingdir);
    }
  }

  /**
  * Load the Help databases
  *
  */
  static function loadHelp() {
    global $string;
    // Set db object in config.
    @$mysqli = new mysqli(self::$cfg_db_host, self::$db_admin_username, self::$db_admin_passwd, self::$cfg_db_name, self::$cfg_db_port);
    if ($mysqli->connect_error == '') {
      $mysqli->set_charset(self::$cfg_db_charset);
    }
    $configObject = Config::get_instance();
    $configObject->set_db_object($mysqli);
    // Staff help files.
    try {
      OnlineHelp::load_staff_help();
    } catch (Exception $ex) {
      if ($ex->getMessage() === 'CANNOT_FIND') {
        self::logWarning(array('502' => $string['logwarning2']));
      } else {
        self::logWarning(array('501' => $string['logwarning1']));
      }
    }
    // Student help files.
    try {
      OnlineHelp::load_student_help();
    } catch (Exception $ex) {
      if ($ex->getMessage() === 'CANNOT_FIND') {
        self::logWarning(array('503' => $string['logwarning3']));
      } else {
        self::logWarning(array('504' => $string['logwarning4']));
      }
    }
  }

  /**
   * Create constraints to maintain database referential integrity
   */
  static function createConstraints() {
    $alter = array();
    $alter[] = "ALTER TABLE sms_imports ADD CONSTRAINT sms_imports_fk0 FOREIGN KEY (academic_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE sessions ADD CONSTRAINT sessions_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE relationships ADD CONSTRAINT relationships_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE properties ADD CONSTRAINT properties_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE objectives ADD CONSTRAINT objectives_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE modules_student ADD CONSTRAINT modules_student_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE users_metadata ADD CONSTRAINT users_metadata_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $alter[] = "ALTER TABLE gradebook_user ADD CONSTRAINT gradebook_user_fk0 FOREIGN KEY (paperid) REFERENCES gradebook_paper(paperid)";
    $alter[] = "ALTER TABLE labs ADD CONSTRAINT labs_fk0 FOREIGN KEY (campus) REFERENCES campus(id)";
    $alter[] = "ALTER TABLE lti_context ADD CONSTRAINT lticontext_fk0 FOREIGN KEY (c_internal_id) REFERENCES modules(id)";
    $alter[] = "ALTER TABLE keywords_link ADD CONSTRAINT `keywords_link_fk1` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`)";
    $alter[] = "ALTER TABLE keywords_link ADD CONSTRAINT `keywords_link_fk2` FOREIGN KEY (`keyword_id`) REFERENCES `keywords_user` (`id`)";
    $alter[] = "ALTER TABLE std_set_questions ADD CONSTRAINT `std_set_questions_fk1` FOREIGN KEY (`std_setID`) REFERENCES `std_set` (`id`)";
    $alter[] = "ALTER TABLE random_link ADD CONSTRAINT `random_link_fk1` FOREIGN KEY (`id`) REFERENCES `questions` (`q_id`)";
    $alter[] = "ALTER TABLE random_link ADD CONSTRAINT `random_link_fk2` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`)";
    $alter[] = "ALTER TABLE users_metadata ADD CONSTRAINT `users_metadata_fk1` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $alter[] = "ALTER TABLE questions_module ADD CONSTRAINT `questions_modules_fk1` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`)";
    $alter[] = "ALTER TABLE questions_module ADD CONSTRAINT `questions_modules_fk2` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $alter[] = "ALTER TABLE questions ADD CONSTRAINT `questions_fk1` FOREIGN KEY (`status`) REFERENCES `question_statuses` (`id`)";
    $alter[] = "ALTER TABLE paper_feedback ADD CONSTRAINT `paper_feedback_fk1` FOREIGN KEY (`paperID`) REFERENCES `properties` (`property_id`)";
    $alter[] = "ALTER TABLE paper_metadata_security ADD CONSTRAINT `paper_metadata_security_fk1` FOREIGN KEY (`paperID`) REFERENCES `properties` (`property_id`)";
    $alter[] = "ALTER TABLE modules_staff ADD CONSTRAINT `modules_staff_fk1` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $alter[] = "ALTER TABLE modules_staff ADD CONSTRAINT `modules_staff_fk2` FOREIGN KEY (`memberID`) REFERENCES `users` (`id`)";
    $alter[] = "ALTER TABLE review_comments ADD CONSTRAINT `metadata_fk0` FOREIGN KEY (`metadataID`) REFERENCES `review_metadata` (`id`)";
    $alter[] = "ALTER TABLE external_systems_mapping ADD CONSTRAINT `external_systems_mapping_fk1` FOREIGN KEY (`ext_id`) REFERENCES `external_systems` (`id`)";
    $alter[] = "ALTER TABLE external_systems_mapping ADD CONSTRAINT `external_systems_mapping_fk2` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`client_id`)";

    foreach ($alter as $a) {
        $res = self::$db->prepare($a);
        $res->execute();
        $res->close();
    }

  }

  /**
   * Load default data needed for rogo to function
   */
  static function loadData() {
    global $string, $timezone_array;
    // Add 3 academic sessions to the the new user started.
    $calendaryear = date('Y');
    $previouscalendaryear = date('Y') - 1;
    $nextcalendaryear = date('Y') + 1;
    $nextyear = date('y') + 1;
    $currentyear = date('y');
    $futureyear = date('y') + 2;
    $academicyear = $calendaryear . '/' . $nextyear;
    $previousacademicyear = $previouscalendaryear . '/' . $currentyear;
    $nextacademicyear = $nextcalendaryear . '/' . $futureyear;
    $insert = self::$db->prepare('INSERT INTO academic_year VALUES (?, ?, 1, 1, NULL, NULL), (?, ?, 1, 1, NULL, NULL), (?, ?, 1, 1, NULL, NULL)');
    $insert->bind_param('isisis', $previouscalendaryear, $previousacademicyear, $calendaryear, $academicyear, $nextcalendaryear, $nextacademicyear);
    $insert->execute();
    $insert->close();
    // Add user psermissions.
    $permissions = array('assessmentmanagement/create',
        'assessmentmanagement/update',
        'assessmentmanagement/delete',
        'assessmentmanagement/schedule',
        'gradebook',
        'modulemanagement/create',
        'modulemanagement/update',
        'modulemanagement/delete',
        'modulemanagement/enrol',
        'modulemanagement/unenrol',
        'usermanagement/create',
        'usermanagement/update',
        'usermanagement/delete',
        'coursemanagement/create',
        'coursemanagement/delete',
        'coursemanagement/update',
        'schoolmanagement/create',
        'schoolmanagement/delete',
        'schoolmanagement/update',
        'facultymanagement/create',
        'facultymanagement/delete',
        'facultymanagement/update');
    foreach ($permissions as $permission) {
        $insert = self::$db->prepare("INSERT INTO permissions (action) VALUES (?)");
        $insert->bind_param('s', $permission);
        $insert->execute();
        $insert->close();
    }
    // Add default campus
    $insert = self::$db->prepare("INSERT INTO campus (name, isdefault) VALUES ('Main Campus', 1)");
    $insert->execute();
    $insert->close();
    // Save json encoded list of timezones.
    $timezones = $timezone_array;
    $cohorts = array('<whole cohort>', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300',
        '301-400', '401-500');
    $configObject = Config::get_instance();
    $configObject->set_db_object(self::$db);
    $configObject->set_setting('paper_timezones', $timezones, Config::TIMEZONES);
    $configObject->set_setting('summative_cohort_sizes', $cohorts, Config::CSV);
    $configObject->set_setting('paper_max_duration', 779, Config::INTEGER);
    $configObject->set_setting('summative_max_sittings', 6, Config::INTEGER);
    $configObject->set_setting('summative_hide_external', 0, Config::BOOLEAN);
    $configObject->set_setting('summative_warn_external', 0, Config::BOOLEAN);
    $configObject->set_setting('cfg_lti_allow_module_self_reg', 0, Config::BOOLEAN);
    $configObject->set_setting('cfg_lti_allow_staff_module_register', 0, Config::BOOLEAN);
    $configObject->set_setting('cfg_lti_allow_module_create', 0, Config::BOOLEAN);
    $configObject->set_setting('lti_integration', 'default', Config::STRING);
    $configObject->set_setting('lti_auth_timeout', 9072000, Config::INTEGER);
    $configObject->set_setting('cfg_gradebook_enabled', 1, Config::BOOLEAN);
    $configObject->set_setting('cfg_api_enabled', 1, Config::BOOLEAN);
    $configObject->set_setting('paper_marks_postive', range(1, 20), Config::CSV);
    $configObject->set_setting('paper_marks_negative', array(0, -0.25, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10), Config::CSV);
    $configObject->set_setting('paper_marks_partial', array_merge(range(0, 1, 0.1), range(2, 5)), Config::CSV);
    $configObject->set_setting('paper_mathjax', 1, Config::BOOLEAN);
    $configObject->set_setting('paper_editor_supports_mathjax',array("plain"), Config::CSV);
    $configObject->set_setting('misc_logo_main', 'logo.png', Config::STRING);
    $configObject->set_setting('misc_logo_email', 'alt_logo.png', Config::STRING);
    $configObject->set_setting('api_allow_superuser', 0, Config::BOOLEAN);
    $configObject->set_setting('apilogfile', '', Config::STRING);
    $configObject->set_setting('rogo_version', $configObject->getxml('version'), Config::VERSION);
    $configObject->set_setting('misc_company', self::$cfg_company, Config::STRING);
    $configObject->set_setting('misc_dictionary_file', '', Config::STRING);
    $configObject->set_setting('cfg_calc_type', 'phpEval', Config::STRING);
    $configObject->set_setting('cfg_calc_settings', array('host' => '', 'port' => '', 'timeout' => ''), Config::ASSOC);
    $configObject->set_setting('system_maintenance_mode', 0, Config::BOOLEAN);
    $configObject->set_setting('cfg_summative_mgmt', 0, Config::BOOLEAN);
    $configObject->set_setting('system_hostname_lookup', self::$cfg_labsecuritytype, Config::BOOLEAN);
    $configObject->set_setting('system_academic_year_start', '07/01', Config::STRING);
    $configObject->set_setting('misc_search_leadin_length', self::$cfg_search_leadin_length, Config::INTEGER);
    $configObject->set_setting('rpt_percent_decimals', 2, Config::INTEGER);
    $configObject->set_setting('stdset_hofstee_pass', array(
        'min_pass' => 0,
        'max_pass' => 'median',
        'min_fail' => 0,
        'max_fail' => 100
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_distinction', array(
        'min_pass' => 'median',
        'max_pass' => 100,
        'min_fail' => 0,
        'max_fail' => 100
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_whole_numbers', true, Config::BOOLEAN);
    $configObject->set_setting('summative_hour_warning', 10, Config::INTEGER);
    $configObject->set_setting('system_install_type', '', Config::STRING);
    $configObject->set_setting('cfg_ims_enabled', false, Config::BOOLEAN);
    $contact_count = 0;
    foreach (self::$emergency_support_numbers as $name => $number) {
      $contact_count++;
      $configObject->set_setting('emergency_support_contact' . $contact_count, array(
          'name' => $name,
          'number' => $number
          ), Config::ASSOC);
    }
    $i = $contact_count;
    while ($i < 3) {
      $i++;
      $configObject->set_setting('emergency_support_contact' . $i, array(
          'name' => '',
          'number' => ''
          ), Config::ASSOC);
    }
    $configObject->set_setting('support_contact_email', array(self::$cfg_support_email), Config::EMAIL);
    $configObject->set_setting('api_oauth_access_lifetime', 1209600, Config::INTEGER);
    $configObject->set_setting('api_oauth_refresh_token_lifetime', 1209600, Config::INTEGER);
    $configObject->set_setting('api_oauth_always_issue_new_refresh_token', true, Config::BOOLEAN);
    $configObject->set_setting('paper_autosave_settimeout', 10, Config::INTEGER);
    $configObject->set_setting('paper_autosave_frequency', 180, Config::INTEGER);
    $configObject->set_setting('paper_autosave_retrylimit', 3, Config::INTEGER);
    $configObject->set_setting('paper_autosave_backoff_factor', 1.5, Config::DOUBLE);
    $configObject->set_setting('summative_midexam_clarification', array('invigilators', 'students'), Config::CSV);
    $configObject->set_setting('system_password_expire', 30, Config::INTEGER);
    $configObject->set_setting('misc_editor_name', 'tinymce', Config::STRING);
    // Add external systems.
    $insert = self::$db->prepare("INSERT INTO external_systems (name, type) values ('ims_enterprise', 'plugin')");
    $insert->execute();
    $insert->close();
    self::createDefaultUsers();
    self::createDefaultFacultiesSchoolsModules();
    self::createQuestionStatuses();
  }

  /**
   * Update the sys updates table as we just did a clean install and do not want the update process
   * running these updates again.
   *
   * This list should not be added to as all new updates should be tied to a release.
   */
  static function updateSysUpdates() {
    $current_datetime = date('Y-m-d H:i:s');
    $updates = array('convert_calc_ans_done',
    'sct_fix',
    'textbox_fix',
    'textbox_update',
    'labelling_search',
    'ext_match_graphics_fix',
    'status_fix',
    'keyword_loop',
    'errorstate_signed_log0',
    'errorstate_signed_log0_deleted',
    'errorstate_signed_log1',
    'errorstate_signed_log1_deleted',
    'errorstate_signed_log2',
    'errorstate_signed_log3',
    'errorstate_signed_log_late');
    foreach ($updates as $update) {
        $insert = self::$db->prepare('INSERT INTO sys_updates VALUES (?, ?)');
        $insert->bind_param('ss', $update, $current_datetime);
        $insert->execute();
        $insert->close();
    }
  }

  /**
   * This function prevents the username being set again if it has already been set one time.
   *
   * @param string $uservariable The name of the variable to set
   * @param string $name The username value to set.
   */
  static public function generateUserName($uservariable, $name) {
    if (empty(self::$$uservariable)) {
      self::$$uservariable = $name;
    }
  }

  /**
   * Ensure that the admin details are filled in.
   */
  static protected function get_sysadmin_details() {
    if (empty(self::$sysadmin_username)) {
      if (!self::$cli) {
        self::$sysadmin_username = param::required('SysAdmin_username', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_username = self::getSettings(param::TEXT, true, 'sysadmin', 'username');
      }
    }
    if (empty(self::$sysadmin_password)) {
      if (!self::$cli) {
        self::$sysadmin_password = param::required('SysAdmin_password', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_password = self::getSettings(param::TEXT, true, 'sysadmin', 'password');
      }
    }
    if (empty(self::$sysadmin_title)) {
      if (!self::$cli) {
        self::$sysadmin_title = param::required('SysAdmin_title', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_title = self::getSettings(param::TEXT, true, 'sysadmin', 'title');
      }
    }
    if (empty(self::$sysadmin_first)) {
        if (!self::$cli) {
        self::$sysadmin_first = param::required('SysAdmin_first', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_first = self::getSettings(param::TEXT, true, 'sysadmin', 'forename');
      }
    }
    if (empty(self::$sysadmin_last)) {
        if (!self::$cli) {
        self::$sysadmin_last = param::required('SysAdmin_last', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_last = self::getSettings(param::TEXT, true, 'sysadmin', 'surname');
      }
    }
    if (empty(self::$sysadmin_email)) {
      if (!self::$cli) {
        self::$sysadmin_email = param::required('SysAdmin_email', param::TEXT, param::FETCH_POST);
      } else {
        self::$sysadmin_email = self::getSettings(param::TEXT, true, 'sysadmin', 'email');
      }
    }
  }

  /**
  * create the database and users if they do not exist
  *
  */
  static function createDatabase($dbname, $dbcharset, $dbengine = 'InnoDB', $dbhelpengine = 'MyISAM') {
    global $string;
    $configObject = Config::get_instance();
    $configObject->db = self::$db;
    $res = self::$db->prepare("SHOW DATABASES LIKE '$dbname'");
    $res->execute();
    $res->store_result();
    @ob_flush();
    @flush();
    if ($res->num_rows > 0) {
      self::displayError(array('010' => sprintf($string['displayerror1'],$dbname)));
    }
    $res->close();

    switch ($dbcharset) {
      case 'utf8':
        $collation = 'utf8_general_ci';
        break;
      default:
        $collation = 'latin1_swedish_ci';
    }

    self::$db->query("CREATE DATABASE $dbname CHARACTER SET = $dbcharset COLLATE = $collation"); //have to use query here oldvers of php throw an error
    if (self::$db->errno != 0) {
      self::displayError(array('011' => $string['displayerror2']));
    }

    //select the newly created database
    self::$db->change_user(self::$db_admin_username, self::$db_admin_passwd,self::$cfg_db_name);

    //create tables
    $tables = new databaseTables($dbcharset, $dbengine, $dbhelpengine);
    self::$db->autocommit(false);
    while ($sql = $tables->next()) {
      $res = self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('012' => $string['displayerror3'] . self::$db->error . "<br /> $sql"));
        self::$db->rollback();
      }
    }
   self::$db->commit();
    $enc = new encryp();
    self::generateUserName('cfg_db_username', self::$cfg_db_basename . '_auth');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_password = $generated_password['password'];

    self::generateUserName('cfg_db_student_user', self::$cfg_db_basename . '_stu');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_student_passwd = $generated_password['password'];
    self::generateUserName('cfg_db_staff_user', self::$cfg_db_basename . '_staff');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_staff_passwd = $generated_password['password'];
    self::generateUserName('cfg_db_external_user', self::$cfg_db_basename . '_ext');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_external_passwd  = $generated_password['password'];
    self::generateUserName('cfg_db_internal_user', self::$cfg_db_basename . '_int');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_internal_passwd  = $generated_password['password'];
    self::generateUserName('cfg_db_sysadmin_user', self::$cfg_db_basename . '_sys');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_sysadmin_passwd = $generated_password['password'];
    self::generateUserName('cfg_db_webservice_user', self::$cfg_db_basename . '_web');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_webservice_passwd = $generated_password['password'];
    self::generateUserName('cfg_db_sct_user', self::$cfg_db_basename . '_sct');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_sct_passwd = $generated_password['password'];
    self::generateUserName('cfg_db_inv_user', self::$cfg_db_basename . '_inv');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_db_inv_passwd = $generated_password['password'];

    self::generateUserName('cfg_cron_user', 'cron');
    $generated_password = $enc->gen_password(false, 16);
    self::$cfg_cron_passwd = $generated_password['password'];

    $priv_SQL = array();
    //create 'database user authentication user' and grant permissions
    self::$db->query("CREATE USER '" . self::$cfg_db_username . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_password . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_username . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_username . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".admin_access TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".courses TO '" . self::$cfg_db_username . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".client_identifiers TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_keys TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_user TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules_student TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE, INSERT, DELETE ON " . $dbname . ".password_tokens TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".sid TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT,INSERT ON " . $dbname . ".temp_users TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".config TO '". self::$cfg_db_username . "'@'". self::$cfg_web_host . "'";

    $priv_SQL[] = "FLUSH PRIVILEGES";

    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_username . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
   self::$db->commit();


    $priv_SQL = array();
    //create 'database user student user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_student_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_student_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
   //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".announcements TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".cache_median_question_marks TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".cache_paper_stats TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".cache_student_paper_marks TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".exam_announcements TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".feedback_release TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_searches TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_tutorial_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".client_identifiers TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_question TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_link TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".random_link TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4_overall TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log6 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".log_extra_time TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".log_lab_end_time TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_late TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".lti_resource TO '". self::$cfg_db_student_user . "'@'".self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".lti_context TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".marking_override TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".modules_student TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".objectives TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_feedback TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_exclude TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".relationships TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".sid TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sessions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".std_set TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".std_set_questions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".state TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_help TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".temp_users TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".killer_questions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".save_fail_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".academic_year TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".config TO '". self::$cfg_db_student_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";

    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_student_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
   self::$db->commit();
    $priv_SQL = array();
    //create 'database user external user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_external_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_external_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_log TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_searches TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_question TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_link TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".random_link TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4_overall TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_late TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules_staff TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".review_metadata TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".std_set TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".std_set_questions TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".staff_help TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_help TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_reviewers TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".client_identifiers TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_modules TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".log_extra_time TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".log_lab_end_time TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules_student TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_exclude TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".marking_override TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sid TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_notes TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_notes TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".exam_announcements TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".relationships TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".feedback_release TO '" . self::$cfg_db_external_user . "'@'" . self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".cache_paper_stats TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_feedback TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".objectives TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sessions TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".academic_year TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".config TO '". self::$cfg_db_external_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_external_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
   self::$db->commit();

    $priv_SQL = array();
    //create 'database user internal user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_internal_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_internal_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_log TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_searches TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_question TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules_staff TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".review_metadata TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".staff_help TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '" . self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_reviewers TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".keywords_link TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".random_link TO '". self::$cfg_db_internal_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_internal_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
   self::$db->commit();

    $priv_SQL = array();
    //create 'database user staff user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_staff_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_staff_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".* TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".cache_median_question_marks TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".cache_paper_stats TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".cache_student_paper_marks TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".ebel TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".exam_announcements TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".feedback_release TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".folders TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".folders_modules_staff TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_searches TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_tutorial_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".hofstee TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".keywords_question TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".keywords_link TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".random_link TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".keywords_user TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log4 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log4_overall TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log5 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log6 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log_late TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_resource TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_context TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".marking_override TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".modules_staff TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".modules_student TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".objectives TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".options TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".paper_notes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".paper_feedback TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".password_tokens TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".performance_main TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".performance_details TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties_modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".question_exclude TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".questions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".question_statuses TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".questions_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".questions_modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".recent_papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_material TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".relationships TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".review_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, DELETE ON " . $dbname . ".scheduling TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sessions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sid TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sms_imports TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".special_needs TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".std_set TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".std_set_questions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".state TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".student_notes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".temp_users TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".textbox_marking TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".textbox_remark TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".track_changes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".users_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".access_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties_reviewers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".killer_questions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".save_fail_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $dbname . ".toilet_breaks TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".academic_year TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_web_host . "'";

    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_staff_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
   self::$db->commit();

    $priv_SQL = array();
    //create 'database user SCT user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_sct_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_sct_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_notes TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions_metadata TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".config TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sct_reviews TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_sct_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
    self::$db->commit();

    $priv_SQL = array();
    //create 'database user Invigilator user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_inv_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install ) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_inv_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".exam_announcements TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".client_identifiers TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".log2 TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $dbname . ".log_metadata TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log_extra_time TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log_lab_end_time TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules_student TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".paper_notes TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_modules TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".student_notes TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sid TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, DELETE ON " . $dbname . ".toilet_breaks TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".academic_year TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".temp_users TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".config TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties_reviewers TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".state TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".staff_help TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_inv_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
    self::$db->commit();

    $priv_SQL = array();
    //create 'database user sysadmin user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_sysadmin_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_sysadmin_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_sysadmin_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_sysadmin_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP  ON " . $dbname . ".* TO '". self::$cfg_db_sysadmin_user . "'@'". self::$cfg_web_host . "'";
    //create 'database user webservice user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "' IDENTIFIED BY '" . self::$cfg_db_webservice_passwd . "'");
    if (self::$db->errno != 0 && !self::$behat_install && !self::$phpunit_install) {
      self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_webservice_user . $string['wnotcreated'] . ' ' . self::$db->error ));
    }
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".* TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".faculty TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".schools TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".courses TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".modules_student TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".modules TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".modules_staff TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".sid TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties_modules TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".scheduling TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".track_changes TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";


    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_sysadmin_user . $string['wnotpermission'] . ' ' . self::$db->error ));
        self::$db->rollback();
      }
    }
    self::$db->commit();

    //FLUSH PRIVILEGES
    self::$db->query("FLUSH PRIVILEGES");
    if (self::$db->errno != 0) {
      self::logWarning(array('014'=> $string['logwarning20']));
    }
    self::$db->commit();
    self::$db->autocommit(false);
  }

  /**
   * Creates the default set of faculties, schools and modules in the Rogo database.
   *
   * @return void
   */
  protected static function createDefaultFacultiesSchoolsModules() {
    // Add unknown school & faculty
    $facultyID = FacultyUtils::add_faculty('UNKNOWN Faculty',
      self::$db
    );

    $scoolID = SchoolUtils::add_school(  $facultyID,
      'UNKNOWN School',
      self::$db
    );

     //add traing school
    $facultyID = FacultyUtils::add_faculty('Administrative and Support Units',
                                        self::$db
                                     );

    $scoolID = SchoolUtils::add_school(  $facultyID,
                                        'Training',
                                        self::$db
                                     );

     //create special modules
     module_utils::add_modules( 'TRAIN',
                                'Training Module',
                                1,
                                $scoolID,
                                '',
                                '',
                                0,
                                false,
                                false,
                                false,
                                true,
                                null,
                                null,
                                self::$db,
                                0,
                                0,
                                1,
                                1,
																'07/01'
                             );

    module_utils::add_modules(  'SYSTEM',
                                'Online Help',
                                1,
                                $scoolID,
                                '',
                                '',
                                0,
                                true,
                                true,
                                true,
                                true,
                                null,
                                null,
                                self::$db,
                                0,
                                0,
                                1,
                                1,
																'07/01'
                             );
    self::$db->commit();
  }

  /**
   * Creates the deafult Rogo users in the database:
   * - The system admin
   * - The cron user
   * - 100 guest accounts
   *
   * @return void
   */
  protected static function createDefaultUsers() {
    //create sysadmin user
    self::get_sysadmin_details();
    UserUtils::create_user( self::$sysadmin_username,
                            self::$sysadmin_password,
                            self::$sysadmin_title,
                            self::$sysadmin_first,
                            self::$sysadmin_last,
                            self::$sysadmin_email,
                            'University Lecturer',
                            '',
                            '1',
                            'Staff,SysAdmin',
                            '',
                            self::$db
                          );

    //create cron user
    UserUtils::create_user( self::$cfg_cron_user,
                            self::$cfg_cron_passwd,
                            '',
                            '',
                            'cron',
                            '',
                            '',
                            '',
                            '',
                            'Staff,SysCron',
                            '',
                            self::$db
                          );

    //create 100 guest accounts
    for ($i=1; $i<=100; $i++) {
      // In the behat site guest users should have a password that matches their username.
      // If it is live site install the guest should have a random password generated.
      $guestpassword = (self::$behat_install) ? 'user' . $i : '';
      UserUtils::create_user( 'user' . $i,
                              $guestpassword,
                              'Dr',
                              'A',
                              'User' . $i,
                              '',
                              'none',
                              '',
                              '1',
                              'Student',
                              '',
                              self::$db
                            );
    }
    self::$db->commit();
  }

  /**
  * Check that we do not have a config file and display error if we do.
  *
  */
  static function configFile() {
    global $string;
    $errors = array();
    if (self::config_exists()) {
      $errors['90'] =  $string['errors1'];
      self::displayError($errors);
    }
  }

  /**
   * Does the config file exist.
   * @return boolean
   */
  static function config_exists() {
    $rogo_path = dirname(__DIR__);
    if (file_exists($rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
      return true;
    }
    return false;
  }
  
  /**
  * Check that  config file is writeable
  *
  */
  static function configFileIsWriteable() {
    if (is_writable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
      return true;
    } else {
      return false;
    }
  }

  /**
  * Check that we write to the /config/ dir
  *
  */
  static function configPathIsWriteable() {
    if (is_writable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Create the default question statuses.
   *
   * @global type $string Language strings
   */
  protected static function createQuestionStatuses() {
    global $string;

    // Create default question statuses
    $statuses = array(
      array('name' => 'Normal', 'exclude_marking' => false, 'retired' => false, 'is_default' => true, 'change_locked' => true, 'validate' => true, 'display_warning' => 0, 'colour' => '#000000', 'display_order' => 0),
      array('name' => 'Retired', 'exclude_marking' => false, 'retired' => true, 'is_default' => false, 'change_locked' => true, 'validate' => false, 'display_warning' => 1, 'colour' => '#808080', 'display_order' => 1),
      array('name' => 'Incomplete', 'exclude_marking' => false, 'retired' => false, 'is_default' => false, 'change_locked' => false, 'validate' => false, 'display_warning' => 1, 'colour' => '#000000', 'display_order' => 2),
      array('name' => 'Experimental', 'exclude_marking' => true, 'retired' => false, 'is_default' => false, 'change_locked' => false, 'validate' => true, 'display_warning' => 0, 'colour' => '#808080', 'display_order' => 3),
      array('name' => 'Beta', 'exclude_marking' => false, 'retired' => false, 'is_default' => false, 'change_locked' => false, 'validate' => true, 'display_warning' => 1, 'colour' => '#000000', 'display_order' => 4)
    );

    foreach ($statuses as $data) {
      $qs = new QuestionStatus(self::$db, $string, $data);
      $qs->save();
    }
  }

  /**
   * Ensures that the rogo user directories are created.
   */
  public static function createDirectories() {
    global $string;
    $errors = array();
    //media
    $mediadirectory = rogo_directory::get_directory('media');
    $mediadirectory->create();
    if (!$mediadirectory->check_permissions()) {
      $errors['102'] = sprintf($string['errors3'], $mediadirectory->location());
    }
    //qti imports
    $qtiimportdirectory = rogo_directory::get_directory('qti_import');
    $qtiimportdirectory->create();
    if (!$qtiimportdirectory->check_permissions()) {
      $errors['103'] = sprintf($string['errors3'], $qtiimportdirectory->location());
    }
    //qti exports
    $qtiexportdirectory = rogo_directory::get_directory('qti_export');
    $qtiexportdirectory->create();
    if (!$qtiexportdirectory->check_permissions()) {
      $errors['104'] = sprintf($string['errors3'], $qtiexportdirectory->location());
    }
    // email_templates.
    $emailtemplatesdirectory = rogo_directory::get_directory('email_templates');
    $emailtemplatesdirectory->create();
    if (!$emailtemplatesdirectory->check_permissions()) {
      $errors['105'] = sprintf($string['errors3'], $emailtemplatesdirectory->location());
    }
    // user photos.
    $photodirectory = rogo_directory::get_directory('user_photo');
    $photodirectory->create();
    if (!$photodirectory->check_permissions()) {
      $errors['106'] = sprintf($string['errors3'], $photodirectory->location());
    }
    // Student help images.
    $studenthelp = rogo_directory::get_directory('help_student');
    $studenthelp->create();
    $studenthelp->copy_from_default();
    if (!$studenthelp->check_permissions()) {
      $errors['107'] = sprintf($string['errors3'], $studenthelp->location());
    }
    // Staff help images.
    $staffhelp = rogo_directory::get_directory('help_staff');
    $staffhelp->create();
    $staffhelp->copy_from_default();
    if (!$staffhelp->check_permissions()) {
      $errors['108'] = sprintf($string['errors3'], $staffhelp->location());
    }
    //theme
    $themedirectory = rogo_directory::get_directory('theme');
    $themedirectory->create();
    $themedirectory->copy_from_default();
    if (!$themedirectory->check_permissions()) {
      $errors['109'] = sprintf($string['errors3'], $themedirectory->location());
    }
    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  /**
  * Check Apache can write to the required directories.
  */
  static function checkDirPermissionsPre() {
    global $string;
    $errors = array();
    self::$rogo_path = dirname(__DIR__);
    if (!is_writable(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
      if (!is_writable(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config')) {
        $errors['901'] = sprintf($string['errors16'], self::$rogo_path, self::$rogo_path);
      }
    }

    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  /**
  * Check Apache can write to the required directories
  *
  */
  static function checkDirPermissionsPost() {
    global $string;
    $errors = array();
    if (!self::$cli) {
      $tmp = param::required('tmpdir', param::TEXT, param::FETCH_POST);
    } else {
      $tmp = self::getSettings(param::TEXT, true, 'server', 'temp');
    }
    //tmp
    if (!is_writable($tmp)) {
      $errors['100'] = sprintf($string['errors3'], $tmp);
    }
    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  static function checkDBUsers() {
    $errors = array();

    $usernames = array('auth'=>300, 'stu'=>301, 'staff'=>302, 'ext'=>303, 'sys'=>304, 'sct'=>305, 'inv'=>306);
    foreach ($usernames as $username=>$err_code){
      $test_username = self::$cfg_db_basename . '_' . $username;
      if (self::does_user_exist($test_username)) {
        $errors[$err_code] = "User '" . $test_username . "' already exists.";

      }
    }

    if (count($errors) > 0) {
      self::displayError($errors);
    }

  }

  /**
   * Display errors with a nice message
   * @param string|array $error error message(s)
   * @param boolean $fatal is error fatal
   *
   */
  static function displayError($error = '', $fatal = true) {
    global $string;
    if (is_array($error)) {
      if (self::$cli) {
        foreach($error as $errCode => $message) {
          $filter = FILTER_SANITIZE_STRING;
          $options = array(
            'options' => array(
              'default' => null,
             ),
            'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
          );
          cli_utils::prompt($string['errors13'] . "$errCode: " . filter_var($message, $filter, $options));
        }
      } else {
        $configObject = Config::get_instance();
        $render = new render($configObject);
        $data['error'] = $error;
        $render->render($data, $string, '/install/error.html');
      }
    }
    if (!self::$cli) {
      if ($fatal) {
        self::displayFooter();
      }
    }
    if ($fatal) {
        exit;
    }
  }

  /**
  * Log warnings with a nice message
  *
  */
  static function logWarning($warning = '') {
    if (is_array($warning)) {
      foreach($warning as $key => $val) {
        self::$warnings[] = $key . ':: ' . $val;
      }
    }
  }

  /**
  * Display header
  *
  */
  static function displayHeader() {
    global $string;
    $configObject = Config::get_instance();
    $render = new render($configObject);
    $headerdata = array(
      'css' => array(
        '/css/rogo_logo.css',
        '/css/header.css',
        '/css/install.css',
      ),
      'scripts' => array(
        '/js/jquery-1.11.1.min.js',
        '/js/jquery.validate.min.js',
        '/js/jquery-ui-1.10.4.min.js',
        '/js/install.min.js',
        '/js/system_tooltips.js',
      ),
    );
    $lang['title'] = $string['install'];
    $lang['blurb'] = $string['systeminstallation'];
    $lang['altintallicon'] = $string['altintallicon'];
    $data['version'] = $configObject->getxml('version');
    require_once dirname(__DIR__) . '/include/path_functions.inc.php';
    $cfg_web_root = get_root_path();
    // Ensure there is a trailing slash.
    if (substr($cfg_web_root, -1) !== '/') {
      $cfg_web_root .= '/';
    }
    self::$cfg_root_path = rtrim('/' . trim(str_replace(normalise_path($_SERVER['DOCUMENT_ROOT']), '', $cfg_web_root), '/'), '/');
    $configObject->set('cfg_root_path', self::$cfg_root_path);
    $render->render($headerdata, $lang, 'header.html');
    $render->render($data, $lang, '/install/header.html');
  }

  /**
  * Display footer
  *
  */
  static function displayfooter() {
    $configObject = Config::get_instance();
    $render = new render($configObject);
    $render->render_admin_footer();
  }

  static function writeConfigFile() {
    global $cfg_encrypt_salt;
    require_once dirname(__DIR__) . '/include/path_functions.inc.php';
    $config = <<<CONFIG
<?php
/**
*
* config file
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

\$cfg_web_root = '{cfg_web_root}';
\$cfg_root_path = '{cfg_root_path}';
\$cfg_secure_connection = true;    // If true site must be accessed via HTTPS
\$cfg_page_charset 	   = 'UTF-8';
\$cfg_tmpdir = '{cfg_tmpdir}';

  \$cfg_web_host = '{cfg_web_host}';
  \$cfg_rogo_data = '{cfg_rogo_data}';

// Local database
  \$cfg_db_username = '{cfg_db_username}';
  \$cfg_db_passwd   = '{cfg_db_passwd}';
  \$cfg_db_database = '{cfg_db_database}';
  \$cfg_db_host 	  = '{cfg_db_host}';
  \$cfg_db_charset 	= '{cfg_db_charset}';
//student db user
  \$cfg_db_student_user = '{cfg_db_student_user}';
  \$cfg_db_student_passwd = '{cfg_db_student_passwd}';
//staff db user
  \$cfg_db_staff_user = '{cfg_db_staff_user}';
  \$cfg_db_staff_passwd = '{cfg_db_staff_passwd}';
//external examiner db user
  \$cfg_db_external_user = '{cfg_db_external}';
  \$cfg_db_external_passwd = '{cfg_db_external_passwd}';
//internal reviewer db user
  \$cfg_db_internal_user = '{cfg_db_internal}';
  \$cfg_db_internal_passwd = '{cfg_db_internal_passwd}';
//sysdamin db user
  \$cfg_db_sysadmin_user = '{cfg_db_sysadmin_user}';
  \$cfg_db_sysadmin_passwd = '{cfg_db_sysadmin_passwd}';
//sct db user
  \$cfg_db_sct_user = '{cfg_db_sct_user}';
  \$cfg_db_sct_passwd = '{cfg_db_sct_passwd}';
//invigilator db user
  \$cfg_db_inv_user = '{cfg_db_inv_user}';
  \$cfg_db_inv_passwd = '{cfg_db_inv_passwd}';
//sysdamin db user
  \$cfg_db_webservice_user = '{cfg_db_webservice_user}';
  \$cfg_db_webservice_passwd = '{cfg_db_webservice_passwd}';
// Date formats in MySQL DATE_FORMAT format
  \$cfg_short_date = '{cfg_short_date}';
  \$cfg_long_date = '{cfg_long_date}';
  \$cfg_long_date_time = '{cfg_long_date_time}';
  \$cfg_tablesorter_date_time = '{cfg_tablesorter_date_time}';
  \$cfg_short_date_time = '{cfg_short_date_time}';
  \$cfg_long_date_php = '{cfg_long_date_php}';
  \$cfg_short_date_php = '{cfg_short_date_php}';
  \$cfg_long_time_php = '{cfg_long_time_php}';
  \$cfg_short_time_php = '{cfg_short_time_php}';
  \$cfg_timezone = '{cfg_timezone}';
  date_default_timezone_set(\$cfg_timezone);
// cron user
  \$cfg_cron_user = '{cfg_cron_user}';
  \$cfg_cron_passwd = '{cfg_cron_passwd}';

// SMS Imports
  \$cfg_sms_api = '';

\$authentication_fields_required_to_create_user = array('username', 'title', 'firstname', 'surname', 'email', 'role');

//Authentication settings
\$authentication = array(
  {cfg_authentication_arrays}
);

//Lookup settings
\$lookup = array(
  {cfg_lookup_arrays}
);

// Objectives mapping
\$vle_apis = array();

// Root path for JS
  \$cfg_js_root = <<< SCRIPT
<script>
  if (typeof cfgRootPath == 'undefined') {
    var cfgRootPath = '\$cfg_root_path';
  }
</script>
SCRIPT;

if(!isset(\$_SERVER['HTTP_HOST'])) {
  \$_SERVER['HTTP_HOST']='';
}

//Global DEBUG OUTPUT
  //require_once \$_SERVER['DOCUMENT_ROOT'] . 'include/debug.inc';   // Uncomment for debugging output (after uncommenting, comment out line below)
  \$dbclass = 'mysqli';

  \$display_auth_debug = false; // set this to display debug on failed authentication

  \$displayerrors = false;  // overrides settings in php for errors not to be shown to screen (true enables)

  \$displayallerrors = false; // display/logs any error the system has including notices (true enables)

  \$errorshutdownhandling=true; //enables log at shutdown (allows you to catch reasons behind fatal errors etc including mysqli errors (true enables)

  \$errorcontexthandling = 'improved'; //improved gives a good capture of context variables while filtering for security of display/saved data, basic captures all but doesnt run and security routines, none doesnt capture any context variables

  //used for debugging
  \$debug_lang_string = false;  // set to true to show lang string in stored system_error_log messages

  // Override db config settings with configs in this file?
  \$file_config_override = true;
  ?>
CONFIG;

    $cfg_web_root = get_root_path();
    // Ensure there is a trailing slash.
    if (substr($cfg_web_root, -1) !== '/') {
      $cfg_web_root .= '/';
    }
    $config = str_replace('{cfg_web_root}', $cfg_web_root, $config);
    if (self::$cli) {
      self::$cfg_root_path = self::getSettings(param::TEXT, true, 'server', 'root');
    }
    $config = str_replace('{cfg_root_path}', self::$cfg_root_path, $config);
    $config = str_replace('{SysAdmin_username}', 'USERNMAE_FOR_DEBUG', $config);
    $config = str_replace('{cfg_web_host}', self::$cfg_web_host, $config);
    $config = str_replace('{cfg_rogo_data}', self::$cfg_rogo_data, $config);
    $config = str_replace('{cfg_db_host}', self::$cfg_db_host, $config);
    $config = str_replace('{cfg_db_port}', self::$cfg_db_port, $config);
    $config = str_replace('{cfg_db_charset}', self::$cfg_db_charset, $config);

    $config = str_replace('{cfg_db_database}', self::$cfg_db_name, $config);
    $config = str_replace('{cfg_db_username}', self::$cfg_db_username, $config);
    $config = str_replace('{cfg_db_passwd}', self::$cfg_db_password, $config);
    $config = str_replace('{cfg_db_student_user}', self::$cfg_db_student_user, $config);
    $config = str_replace('{cfg_db_student_passwd}', self::$cfg_db_student_passwd, $config);
    $config = str_replace('{cfg_db_staff_user}', self::$cfg_db_staff_user, $config);
    $config = str_replace('{cfg_db_staff_passwd}', self::$cfg_db_staff_passwd, $config);
    $config = str_replace('{cfg_db_external}', self::$cfg_db_external_user, $config);
    $config = str_replace('{cfg_db_external_passwd}', self::$cfg_db_external_passwd, $config);
    $config = str_replace('{cfg_db_internal}', self::$cfg_db_internal_user, $config);
    $config = str_replace('{cfg_db_internal_passwd}', self::$cfg_db_internal_passwd, $config);
    $config = str_replace('{cfg_db_sysadmin_user}', self::$cfg_db_sysadmin_user, $config);
    $config = str_replace('{cfg_db_sysadmin_passwd}', self::$cfg_db_sysadmin_passwd, $config);
    $config = str_replace('{cfg_db_sct_user}', self::$cfg_db_sct_user, $config);
    $config = str_replace('{cfg_db_sct_passwd}', self::$cfg_db_sct_passwd, $config);
    $config = str_replace('{cfg_db_inv_user}', self::$cfg_db_inv_user, $config);
    $config = str_replace('{cfg_db_inv_passwd}', self::$cfg_db_inv_passwd, $config);
    $config = str_replace('{cfg_db_webservice_user}', self::$cfg_db_webservice_user, $config);
    $config = str_replace('{cfg_db_webservice_passwd}', self::$cfg_db_webservice_passwd, $config);
    $config = str_replace('{cfg_cron_user}', self::$cfg_cron_user, $config);
    $config = str_replace('{cfg_cron_passwd}', self::$cfg_cron_passwd, $config);

    $config = str_replace('{cfg_short_date}', self::$cfg_short_date, $config);
    $config = str_replace('{cfg_long_date}', self::$cfg_long_date, $config);
    $config = str_replace('{cfg_long_date_time}', self::$cfg_long_date_time, $config);
    $config = str_replace('{cfg_short_date_time}', self::$cfg_short_date_time, $config);
    $config = str_replace('{cfg_long_date_php}', self::$cfg_long_date_php, $config);
    $config = str_replace('{cfg_short_date_php}', self::$cfg_short_date_php, $config);
    $config = str_replace('{cfg_long_time_php}', self::$cfg_long_time_php, $config);
    $config = str_replace('{cfg_short_time_php}', self::$cfg_short_time_php, $config);
    $config = str_replace('{cfg_timezone}', self::$cfg_timezone, $config);
    $config = str_replace('{cfg_tmpdir}', self::$cfg_tmpdir, $config);
    $config = str_replace('{cfg_tablesorter_date_time}', self::$cfg_tablesorter_date_time, $config);

    $authentication_arrays = array();
    if (self::$cfg_auth_lti) {
      $authentication_arrays[] = "array('ltilogin', array(), 'LTI Auth')";
    }
    if (self::$cfg_auth_guest) {
      $authentication_arrays[] = "array('guestlogin', array(), 'Guest Login')";
    }
    if (self::$cfg_auth_impersonation) {
      $authentication_arrays[] = "array('impersonation', array('separator' => '_'), 'Impersonation')";
    }
    if (self::$cfg_auth_internal) {
      $authentication_arrays[] = "array('internaldb', array('table' => 'users', 'username_col' => 'username', 'passwd_col' => 'password', 'id_col' => 'id', 'encrypt' => 'SHA-512', 'encrypt_salt' => '{cfg_encrypt_salt}'), 'Internal Database')";
    }
    if (self::$cfg_auth_ldap) {
      $authentication_arrays[] = "array('ldap', array('table' => 'users', 'username_col' => 'username', 'id_col' => 'id', 'ldap_server' => '{cfg_ldap_server}', 'ldap_search_dn' => '{cfg_ldap_search_dn}', 'ldap_bind_rdn' => '{cfg_ldap_bind_rdn}', 'ldap_bind_password' => '{cfg_ldap_bind_password}', 'ldap_user_prefix' => '{cfg_ldap_user_prefix}'), 'LDAP')";
    }

    $config = str_replace('{cfg_authentication_arrays}', implode(",\n  ", $authentication_arrays), $config);

    $lookup_arrays = array();
    if (self::$cfg_uselookupLdap) {
      $lookup_arrays[] = "array('ldap', array('ldap_server' => '{cfg_lookup_ldap_server}', 'ldap_search_dn' => '{cfg_lookup_ldap_search_dn}', 'ldap_bind_rdn' => '{cfg_lookup_ldap_bind_rdn}', 'ldap_bind_password' => '{cfg_lookup_ldap_bind_password}', 'ldap_user_prefix' => '{cfg_lookup_ldap_user_prefix}', 'ldap_attributes' => array('sAMAccountName' => 'username', 'sn' => 'surname', 'title' => 'title', 'givenName' => 'firstname', 'department' => 'school', 'mail' => 'email',  'cn' => 'username',  'employeeType' => 'role',  'initials' => 'initials'), 'lowercasecompare' => true, 'storeprepend' => 'ldap_'), 'LDAP')";
    }
    if (self::$cfg_uselookupXML) {
      $lookup_arrays[] = "array('XML', array('baseurl' => 'http://exports/', 'userlookup' => array( 'url' => '/student.ashx?campus=uk', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML')";
    }

    $config = str_replace('{cfg_lookup_arrays}', implode(",\n  ", $lookup_arrays), $config);

    $salt = $cfg_encrypt_salt;

    $config = str_replace('{cfg_encrypt_salt}', $salt, $config);
    if (self::$cfg_auth_ldap) {
      $config = str_replace('{cfg_ldap_server}', self::$cfg_ldap_server, $config);
      $config = str_replace('{cfg_ldap_search_dn}', self::$cfg_ldap_search_dn, $config);
      $config = str_replace('{cfg_ldap_bind_rdn}', self::$cfg_ldap_bind_rdn, $config);
      $config = str_replace('{cfg_ldap_bind_password}', self::$cfg_ldap_bind_password, $config);
      $config = str_replace('{cfg_ldap_user_prefix}', self::$cfg_ldap_user_prefix, $config);
    }
    if (self::$cfg_uselookupLdap) {
      $config = str_replace('{cfg_lookup_ldap_server}', self::$cfg_lookup_ldap_server, $config);
      $config = str_replace('{cfg_lookup_ldap_search_dn}', self::$cfg_lookup_ldap_search_dn, $config);
      $config = str_replace('{cfg_lookup_ldap_bind_rdn}', self::$cfg_lookup_ldap_bind_rdn, $config);
      $config = str_replace('{cfg_lookup_ldap_bind_password}', self::$cfg_lookup_ldap_bind_password, $config);
      $config = str_replace('{cfg_lookup_ldap_user_prefix}', self::$cfg_lookup_ldap_user_prefix, $config);
    }

    if (file_exists(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
      rename(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php' , self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.old.php');
    }

    if (file_put_contents(self::$rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php', $config) === false) {
      self::displayError(array(300=>'Could not write config file!'));
    }
  }
}
