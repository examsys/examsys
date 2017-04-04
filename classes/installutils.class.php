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

require_once __DIR__ . '/../include/auth.inc';

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

  public static $cfg_auth_ldap = 'false';
  public static $cfg_auth_lti = 'true';
  public static $cfg_auth_internal = 'true';
  public static $cfg_auth_guest = 'true';
  public static $cfg_auth_impersonation = 'true';

  public static $cfg_lookup_ldap_server;
  public static $cfg_lookup_ldap_search_dn;
  public static $cfg_lookup_ldap_bind_rdn;
  public static $cfg_lookup_ldap_bind_password;
  public static $cfg_lookup_ldap_user_prefix;

  public static $cfg_uselookupLdap = 'false';
  public static $cfg_uselookupXML = 'false';
  
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
    ?>
    <script type="text/javascript" src="../js/system_tooltips.js"></script>
    <script>
      $(function () {
        $("#installForm").validate();
      
        $('#useLdap').change(function() {
          $('#ldapOptions').toggle();
        });
      
        $('#uselookupLdap').change(function() {
          $('#ldaplookupOptions').toggle();
        });
      });
    </script>
    <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">

      <table class="h"><tr><td><nobr><?php echo $string['company']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="company_name"><?php echo $string['companyname']; ?></label> <input type="text" id="company_name" name="company_name" value="University of" class="required" minlength="2" /></div>

      <table class="h"><tr><td><nobr><?php echo $string['server']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <br />
        <div><label for="web_host"><?php echo $string['webhost']; ?></label> <input type="text" value="127.0.0.1" id="web_host" name="web_host" class="required" minlength="3" maxlength="10" /></div>
        <div><label for="rogo_data"><?php echo $string['datadirectory']; ?></label> <input type="text" id="rogo_data" name="rogo_data" value="<?php echo dirname(__DIR__) . DIRECTORY_SEPARATOR ?>" /></div>
        <div><label for="tmpdir"><?php echo $string['tempdirectory']; ?></label> <input type="text" id="tmpdir" name="tmpdir" value="/tmp/" /></div>

      <table class="h"><tr><td><nobr><?php echo $string['databaseadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><?php echo $string['needusername']; ?></div>
        <br />
        <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" id="mysql_admin_user" name="mysql_admin_user" class="required" minlength="2" /></div>
        <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" id="mysql_admin_pass" name="mysql_admin_pass"/></div>

        <table class="h"><tr><td><nobr><?php echo $string['databasesetup']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <br />
        <div><label for="mysql_db_host"><?php echo $string['databasehost']; ?></label> <input type="text" value="127.0.0.1" id="mysql_db_host" name="mysql_db_host" class="required" /></div>
        <div><label for="mysql_db_port"><?php echo $string['databaseport']; ?></label> <input type="text" value="3306" id="mysql_db_port" name="mysql_db_port" class="required" /></div>
        <div><label for="mysql_db_name"><?php echo $string['databasename']; ?></label> <input type="text" value="rogo" id="mysql_db_name" name="mysql_db_name" class="required" minlength="3" /></div>
        <div><label for="mysql_baseusername"><?php echo $string['rdbbasename']; ?></label> <input type="text" value="rogo" id="mysql_baseusername" name="mysql_baseusername" class="required" minlength="3" maxlength="10" /></div>

      <table class="h"><tr><td><nobr><?php echo $string['timedateformats']; ?></nobr></td><td class="line"><hr /></td></tr></table>
<?php
$mysql_date_url = 'http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format';
$php_date_url = 'http://www.php.net/manual/en/function.date.php';
?>
        <div><label for="cfg_short_date"><?php echo sprintf($string['date'], '<a href="' . $mysql_date_url . '" target="_blank">MySQL</a>'); ?></label> <input type="text" id="cfg_short_date" name="cfg_short_date" class="required" minlength="2" value="%d/%m/%y" /></div>
        <div><label for="cfg_long_date"><?php echo sprintf($string['longdate'], '<a href="' . $mysql_date_url . '" target="_blank">MySQL</a>'); ?></label> <input type="text" id="cfg_long_date" name="cfg_long_date" class="required" minlength="2" value="%d/%m/%Y" /></div>
        <div><label for="cfg_long_date_time"><?php echo sprintf($string['longdatetime'], '<a href="' . $mysql_date_url . '" target="_blank">MySQL</a>'); ?></label> <input type="text" id="cfg_long_date_time" name="cfg_long_date_time" class="required" value="%d/%m/%Y %H:%i" /></div>
        <div><label for="cfg_short_date_time"><?php echo sprintf($string['shortdatetime'], '<a href="' . $mysql_date_url . '" target="_blank">MySQL</a>'); ?></label> <input type="text" id="cfg_short_date_time" name="cfg_short_date_time" class="required" value="%d/%m/%y %H:%i" /></div>
        <div><label for="cfg_long_date_php"><?php echo sprintf($string['longdatephp'], '<a href="' . $php_date_url . '" target="_blank">PHP</a>'); ?></label> <input type="text" id="cfg_long_date_php" name="cfg_long_date_php" class="required" value="d/m/Y" /></div>
        <div><label for="cfg_short_date_php"><?php echo sprintf($string['shortdatephp'], '<a href="' . $php_date_url . '" target="_blank">PHP</a>'); ?></label> <input type="text" id="cfg_short_date_php" name="cfg_short_date_php" class="required" value="d/m/y" /></div>
        <div><label for="cfg_long_time_php"><?php echo sprintf($string['longtimephp'], '<a href="' . $php_date_url . '" target="_blank">PHP</a>'); ?></label> <input type="text" id="cfg_long_time_php" name="cfg_long_time_php" class="required" value="H:i:s" /></div>
        <div><label for="cfg_short_time_php"><?php echo sprintf($string['shorttimephp'], '<a href="' . $php_date_url . '" target="_blank">PHP</a>'); ?></label> <input type="text" id="cfg_short_time_php" name="cfg_short_time_php" class="required" value="H:i" /></div>
        <div><label for="cfg_search_leadin_length"><?php echo $string['searchleadinlength']; ?></label> <input type="text" id="cfg_search_leadin_length" name="cfg_search_leadin_length" value="160" /></div>
        <div><label for="cfg_timezone"><?php echo $string['currenttimezone']; ?></label> <select id="cfg_timezone" name="cfg_timezone">
        <?php
          $default_timezone = date_default_timezone_get();
          if ($default_timezone == 'UTC') $default_timezone = 'Europe/London';
          foreach ($timezone_array as $individual_zone => $display_zone) {
            if ($individual_zone == $default_timezone) {
              echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
            } else {
              echo "<option value=\"$individual_zone\">$display_zone</option>";
            }
          }
        ?>
        </select></div>

        <table class="h"><tr><td><nobr><?php echo $string['authentication']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="useLti"><?php echo $string['allowlti']; ?></label><input id="useLti" name="useLti" type="checkbox" checked="checked" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Allow authentication from successful LTI launch" /></div><br />
        <div><label for="useInternal"><?php echo $string['allowintdb']; ?></label><input id="useInternal" name="useInternal" type="checkbox" checked="checked" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Allow authentication from internal Rogo user database" /></div><br />
        <div><label for="useGuest"><?php echo $string['allowguest']; ?></label><input id="useGuest" name="useGuest" type="checkbox" checked="checked" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Allow guest temporary accouts for students who forget their normal log in details" /></div><br /><br />
        <div><label for="useImpersonation"><?php echo $string['allowimpersonation']; ?></label><input id="useImpersonation" name="useImpersonation" type="checkbox" checked="checked" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Allow SysAdmin users to impersonate other users" /></div><br clear="all" /><br />
        <div><label for="useLdap"><?php echo $string['useldap']; ?></label><input id="useLdap" name="useLdap" type="checkbox" /></div>
        <div id="ldapOptions" style="display:none">
          <br/>
          <div><label for="ldap_server"><?php echo $string['ldapserver']; ?></label> <input type="text" value="" id="ldap_server" name="ldap_server" /></div>
          <div><label for="ldap_search_dn"><?php echo $string['searchdn']; ?></label> <input type="text" value="" id="ldap_search_dn" name="ldap_search_dn" /></div>
          <div><label for="ldap_bind_rdn"><?php echo $string['bindusername']; ?></label> <input type="text" value="" id="ldap_bind_rdn" name="ldap_bind_rdn" /></div>
          <div><label for="ldap_bind_password"><?php echo $string['bindpassword']; ?></label> <input type="password" value="" id="ldap_bind_password" name="ldap_bind_password" /></div>
          <div><label for="ldap_user_prefix"><?php echo $string['userprefix']; ?></label> <input type="text" value="" id="ldap_user_prefix" name="ldap_user_prefix" /> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['userprefixtip'] ?>" /></div>
        </div>


        <table class="h"><tr><td><nobr><?php echo $string['lookup']; ?></nobr></td><td class="line"><hr /></td></tr></table>


        <div><label for="uselookupLdap"><?php echo $string['useldap']; ?></label><input id="uselookupLdap" name="uselookupLdap" type="checkbox" /></div>
        <div id="ldaplookupOptions" style="display:none;">
            <br/>
            <div><label for="ldap_lookup_server"><?php echo $string['ldapserver']; ?></label> <input type="text" value="" id="ldap_lookup_server" name="ldap_lookup_server" /></div>
            <div><label for="ldap_lookup_search_dn"><?php echo $string['searchdn']; ?></label> <input type="text" value="" id="ldap_lookup_search_dn" name="ldap_lookup_search_dn" /></div>
            <div><label for="ldap_lookup_bind_rdn"><?php echo $string['bindusername']; ?></label> <input type="text" value="" id="ldap_lookup_bind_rdn" name="ldap_lookup_bind_rdn" /></div>
            <div><label for="ldap_lookup_bind_password"><?php echo $string['bindpassword']; ?></label> <input type="password" value="" id="ldap_lookup_bind_password" name="ldap_lookup_bind_password" /></div>
            <div><label for="ldap_lookup_user_prefix"><?php echo $string['userprefix']; ?></label> <input type="text" value="" id="ldap_lookup_user_prefix" name="ldap_lookup_user_prefix" /> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['userprefixtip'] ?>" /></div>
        </div><br clear="all" />
        <div><label for="uselookupXML"><?php echo $string['allowlookupXML']; ?></label><input id="uselookupXML" name="uselookupXML" type="checkbox" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Allow guest temporary accouts for students who forget their normal log in details" /></div><br clear="all" /><br />


        <table class="h"><tr><td><nobr><?php echo $string['sysadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><?php echo $string['initialsysadmin']; ?></div>
        <br />
        <div><label for="SysAdmin_title"><?php echo $string['title']; ?></label>
          <select id="SysAdmin_title" name="SysAdmin_title" class="required">
		<?php
		  if ($language != 'en') {
		    echo "<option value=\"\"></option>\n";
		  }
		  $titles = explode(',', $string['title_types']);
		  foreach ($titles as $tmp_title) {
		    echo "<option value=\"$tmp_title\" selected>$tmp_title</option>";
		  }
		  ?>
          </select>
        </div>
        <div><label for="SysAdmin_first"><?php echo $string['firstname']; ?></label> <input type="text" value="" name="SysAdmin_first" id="SysAdmin_first" class="required" /> </div>
        <div><label for="SysAdmin_last"><?php echo $string['surname']; ?></label> <input type="text" value="" id="SysAdmin_last" name="SysAdmin_last" class="required" minlength="3" /> </div>
        <div><label for="SysAdmin_email"><?php echo $string['emailaddress']; ?></label> <input type="text" value="" id="SysAdmin_email" name="SysAdmin_email" class="required email" /></div>
        <div><label for="SysAdmin_username"><?php echo $string['username']; ?></label> <input type="text" value="" id="SysAdmin_username" name="SysAdmin_username" class="required" minlength="3" /></div>
        <div><label for="SysAdmin_password"><?php echo $string['password']; ?></label> <input type="password" value="" id="SysAdmin_password" name="SysAdmin_password" class="required" minlength="8" /></div>

      <table class="h"><tr><td><nobr><?php echo $string['helpdb']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="loadHelp"><?php echo $string['loadhelp']; ?></label> <input id="loadHelp" name="loadHelp" type="checkbox" checked="checked" /></div>
        
      <table class="h"><tr><td><nobr><?php echo $string['translationpack']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="loadtranslations"><?php echo $string['loadtranslations']; ?></label> <input id="loadtranslations" name="loadtranslations" type="checkbox"/></div><br/><br/>
        <div><?php echo sprintf($string['manualtranslations'], $configObject->getxml('translations', 'url')); ?></div>

      <table class="h"><tr><td><nobr><?php echo $string['labsecuritytype']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label><?php echo $string['IP']; ?></label> <input name="labsecuritytype" value="ipaddress" type="radio" checked = "checked" /><img src="../artwork/tooltip_icon.gif" class="help_tip" title="Rogo can lock summative exams to either IP address or hostname. If your institution uses static IPs then chose IP address otherwise chose hostname. " /></div>
        <div><label><?php echo $string['hostname']; ?></label> <input name="labsecuritytype" type="radio" value="hostname" /></div>
      
      <table class="h"><tr><td><nobr><?php echo $string['supportemaila']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div></div>
        <br />
        <div><label for="support_email"><?php echo $string['supportemail']; ?></label> <input type="text" value="" id="support_email" name="support_email" class="" class="email" /> </div>

      <table class="h"><tr><td><nobr><?php echo $string['supportnumbers']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="emergency_support1"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support1" name="emergency_support1" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number1" class="" /></div>
        <div><label for="emergency_support2"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support2" name="emergency_support2" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number2" class="" /></div>
        <div><label for="emergency_support3"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support3" name="emergency_support3" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number3" class="" /></div>

      <div class="submit"> <input type="submit" name="install" value="<?php echo $string['install']; ?>" class="ok" /> </div>
    </form>
    <?php
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
  
  static function processForm() {
    global $string, $cfg_encrypt_salt;
    $configObject = Config::get_instance();
    
    self::$cfg_company = $_POST['company_name'];

    self::$cfg_db_host = $_POST['mysql_db_host'];
    self::$cfg_db_charset = 'utf8';
    self::$cfg_db_port = $_POST['mysql_db_port'];
    self::$cfg_db_name = $_POST['mysql_db_name'];
    self::$db_admin_username = $_POST['mysql_admin_user'];
    self::$db_admin_passwd = $_POST['mysql_admin_pass'];
    
    // Check mysql version.
    $check = mysqli_connect(self::$cfg_db_host, self::$db_admin_username, self::$db_admin_passwd);

    if (mysqli_connect_error()) {
      self::displayError(array('001' => mysqli_connect_error()));
    }

    $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
    $mysql_version = mysqli_get_server_version($check);
    if($mysql_version < $mysql_min_ver) {
        self::displayError(array('002' => sprintf($string['errors17'], $mysql_min_ver, $mysql_version)));
    }
    $check->close();
    
    self::$cfg_web_host = $_POST['web_host'];
    self::$cfg_rogo_data = $_POST['rogo_data'];
    if (!file_exists(self::$cfg_rogo_data)) {
      self::displayError(array('003' => sprintf($string['errors18'], self::$cfg_rogo_data)));
    }
    if (!is_writable(self::$cfg_rogo_data)) {
      self::displayError(array('004' => sprintf($string['errors19'], self::$cfg_rogo_data)));
    }
    self::createDirectories();

    // On windows we must escape the slashes.
    self::$cfg_rogo_data = str_replace('\\', '\\\\', self::$cfg_rogo_data);

    self::$cfg_db_basename = $_POST['mysql_baseusername'];

    self::$cfg_SysAdmin_username = $_POST['SysAdmin_username'];

    self::$cfg_short_date = $_POST['cfg_short_date'];
    self::$cfg_long_date = $_POST['cfg_long_date'];
    self::$cfg_long_date_time = $_POST['cfg_long_date_time'];
    self::$cfg_short_date_time = $_POST['cfg_short_date_time'];
    self::$cfg_long_date_php = $_POST['cfg_long_date_php'];
    self::$cfg_short_date_php = $_POST['cfg_short_date_php'];
    self::$cfg_long_time_php = $_POST['cfg_long_time_php'];
    self::$cfg_short_time_php = $_POST['cfg_short_time_php'];
    self::$cfg_search_leadin_length = $_POST['cfg_search_leadin_length'];
    self::$cfg_timezone = $_POST['cfg_timezone'];
    self::$cfg_tmpdir = $_POST['tmpdir'];
    if (self::$cfg_long_date_time == "%d/%m/%Y %H:%i") {
      self::$cfg_tablesorter_date_time = 'uk';
    } else {
      self::$cfg_tablesorter_date_time = 'us';
    }
    //Authentication
    if (isset($_POST['useLti'])) {
      self::$cfg_auth_lti = true;
    } else {
      self::$cfg_auth_lti = false;
    }
    if (isset($_POST['useInternal'])) {
      self::$cfg_auth_internal = true;
    } else {
      self::$cfg_auth_internal = false;
    }
    if (isset($_POST['useGuest'])) {
      self::$cfg_auth_guest = true;
    } else {
      self::$cfg_auth_guest = false;
    }
    if (isset($_POST['useImpersonation'])) {
      self::$cfg_auth_impersonation = true;
    } else {
      self::$cfg_auth_impersonation = false;
    }
    if (isset($_POST['useLdap'])) {
      self::$cfg_auth_ldap = true;
    } else {
      self::$cfg_auth_ldap = false;
    }


    //LDAP
    self::$cfg_ldap_server = $_POST['ldap_server'];
    self::$cfg_ldap_search_dn = $_POST['ldap_search_dn'];
    self::$cfg_ldap_bind_rdn = $_POST['ldap_bind_rdn'];
    self::$cfg_ldap_bind_password = $_POST['ldap_bind_password'];
    if (self::$cfg_ldap_server != '') {
      self::$cfg_auth_ldap = true;
    } else {
      self::$cfg_auth_ldap = false;
    }
    self::$cfg_ldap_user_prefix = $_POST['ldap_user_prefix'];

    //LDAP for lookup
    self::$cfg_lookup_ldap_server = $_POST['ldap_lookup_server'];
    self::$cfg_lookup_ldap_search_dn = $_POST['ldap_lookup_search_dn'];
    self::$cfg_lookup_ldap_bind_rdn = $_POST['ldap_lookup_bind_rdn'];
    self::$cfg_lookup_ldap_bind_password = $_POST['ldap_lookup_bind_password'];
    self::$cfg_lookup_ldap_user_prefix = $_POST['ldap_lookup_user_prefix'];

    //ASSISTANCE
    self::$cfg_support_email = $_POST['support_email'];
    self::$emergency_support_numbers = 'array(';
    for ($i = 1; $i<=3; $i++) {
      if ($_POST["emergency_support$i"] != '') {
        self::$emergency_support_numbers .= "'" . $_POST["emergency_support$i"] . "'=>'" . $_POST["emergency_support_number$i"] . "', ";
      }
    }
    self::$emergency_support_numbers = rtrim(self::$emergency_support_numbers, ', ');
    self::$emergency_support_numbers .= ')';
    
    
    //Other settings 
    self::$cfg_labsecuritytype = $_POST['labsecuritytype'];
  
    // Check we can write to the config file first if not passwords will be lost!
    $rogo_path = str_ireplace('/install/index.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));

    if (file_exists($rogo_path . '/config/config.inc.php')) {
      if (!is_writable($rogo_path . '/config/config.inc.php')) {
        self::displayError(array(300=>'Could not write config file!'));
      }
    } elseif (!is_writable($rogo_path . '/config')) {
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

    self::createDatabase(self::$cfg_db_name, self::$cfg_db_charset);

    // Create constraints.
    self::createConstraints();
    
    // Load default data
    self::loadData();
    
    // Update sys_updates table
    self::updateSysUpdates();
    
    //LOAD help if requested
    if (isset($_POST['loadHelp'])) {
      self::loadHelp();
    }

    // Download language packs and install.
    if (isset($_POST['loadtranslations'])) {
      self::download_langpacks();
    }

    //Write out the config file
    self::writeConfigFile();

    // Fix help file image paths.
    if (isset($_POST['loadHelp'])) {
      // Set db object in config.
      @$mysqli = new mysqli(self::$cfg_db_host, self::$db_admin_username, self::$db_admin_passwd, self::$cfg_db_name, self::$cfg_db_port);
      if ($mysqli->connect_error == '') {
        $mysqli->set_charset(self::$cfg_db_charset);
      }
      $configObject->set_db_object($mysqli);
      require_once '../include/path_functions.inc.php';
      $cfg_web_root = get_root_path() . '/';
      $cfg_root_path = rtrim('/' . trim(str_replace(normalise_path($_SERVER['DOCUMENT_ROOT']), '', $cfg_web_root), '/'), '/');
      $configObject->set('cfg_root_path', $cfg_root_path);
      self::correct_staff_path();
      self::correct_student_path();
    }

    // Install composer and dependencies.
    try {
      $composer_method = composer_utils::INSTALL_NODEV;
      composer_utils::setup($composer_method);
    } catch (Exception $e) {
      // Non fatal warning.
      echo "<div class=\"warning\">\n";
      echo "\t<div>" . $e->getMessage() . "</div>\n";
      echo "</div>\n";
    }

    if (!is_array(self::$warnings)) {
      echo "<p style=\"margin-left:10px\">" . $string['installed'] . "</p>\n";
      echo "<p style=\"margin-left:10px\">" . $string['deleteinstall'] . "</p>\n";
      echo "<p style=\"margin-left:10px\"><input type=\"button\" class=\"ok\" name=\"home\" value=\"" . $string['staffhomepage'] . "\" onclick=\"window.location='../index.php'\" /></p>\n";
    } else {
      self::displayWarnings();
    }
  }

  /**
   * Correct path of staff help file images as may not be in root web server directory.
   */
  static public function correct_staff_path() {
    set_time_limit(0);
    $configObject = Config::get_instance();
    $webroot = $configObject->get('cfg_root_path');
    // Ensure there is a trailing slash.
    if (substr($webroot, -1) !== '/') {
      $webroot .= '/';
    }
    // The substitution will replace the old src tag with a new one that.
    $regexp = '#src="\/getfile\.php\?type\=help_staff&amp;filename\=(.*?)"#';
    $substitution = 'src="' . $webroot . 'getfile.php?type=help_staff&amp;filename=$1"';
    // If we find any images in help files update them.
    $result = $configObject->db->prepare("SELECT id, body FROM staff_help WHERE body LIKE '%<img%'");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $body);
    while ($result->fetch()) {
      $newbody = preg_replace($regexp, $substitution, $body);
      if ($newbody != $body) {
        // There was a change, so update the record.
        $update = $configObject->db->prepare("UPDATE staff_help SET body = ? WHERE id = ?");
        $update->bind_param('si', $newbody, $id);
        $update->execute();
        $update->close();
      }
    }
    $result->close();
  }

  /**
   * Correct path of student help file images as may not be in root web server directory.
   */
  static public function correct_student_path() {
    set_time_limit(0);
    $configObject = Config::get_instance();
    $webroot = $configObject->get('cfg_root_path');
    // Ensure there is a trailing slash.
    if (substr($webroot, -1) !== '/') {
      $webroot .= '/';
    }
    // The substitution will replace the old src tag with a new one that.
    $regexp = '#src="\/getfile\.php\?type\=help_student&amp;filename\=(.*?)"#';
    $substitution = 'src="' . $webroot . 'getfile.php?type=help_student&amp;filename=$1"';
    // If we find any images in help files update them.
    $result = $configObject->db->prepare("SELECT id, body FROM student_help WHERE body LIKE '%<img%'");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $body);
    while ($result->fetch()) {
      $newbody = preg_replace($regexp, $substitution, $body);
      if ($newbody != $body) {
        // There was a change, so update the record.
        $update = $configObject->db->prepare("UPDATE student_help SET body = ? WHERE id = ?");
        $update->bind_param('si', $newbody, $id);
        $update->execute();
        $update->close();
      }
    }
    $result->close();
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
      // Download language packs.
      $fullurl = $url . '/' . $version . '/rogo.zip';
      $file = @file_get_contents($fullurl);
      if ($file === false or file_put_contents("translations.zip", $file) === false) {
        echo "Error downloading language packs from $fullurl, you will need to manually install them.";
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
          echo('Cannot extract language packs, you will need to manually extract them.');
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
    $staff_help = './staff_help.sql';
    $student_help = './student_help.sql';

    //make sure we are using the right DB
    self::$db->select_db(self::$cfg_db_name);

    self::$db->autocommit(false);
    if (file_exists($staff_help)) {
      $query = file_get_contents($staff_help);
      self::$db->query("TRUNCATE staff_help");


      self::$db->multi_query($query);
      if (self::$db->error) {
        try {
          throw new Exception("MySQL error " . self::$db->error . " <br /> Query:<br /> ", self::$db->errno);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
          echo nl2br($e->getTraceAsString());
        }
        self::$db->rollback();
      }

      if (self::$db->errno != 0) {
        self::logWarning(array('501' => $string['logwarning1'] . self::$db->error));
        $ext = '';
      }
      while (self::$db->more_results()) {
        self::$db->next_result();
        if (self::$db->error) {
          try {
            throw new Exception("MySQL error " . self::$db->error . " <br /> Query:<br /> ", self::$db->errno);
          } catch (Exception $e) {
            echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
            echo nl2br($e->getTraceAsString());
          }
          self::$db->rollback();
        }
      }
    } else {
      self::logWarning(array('502' => $string['logwarning2']));
    }
    self::$db->commit();

    if (file_exists($student_help)) {
      $query = file_get_contents($student_help);
      self::$db->query("TRUNCATE student_help");

      self::$db->multi_query($query);
      if (self::$db->error) {
        try {
          throw new Exception("MySQL error " . self::$db->error . " <br /> Query:<br /> ", self::$db->errno);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
          echo nl2br($e->getTraceAsString());
        }
        self::$db->rollback();
      }
      if (self::$db->errno != 0) {
        self::logWarning(array('503' => $string['logwarning3'] . self::$db->error));
        $ext = '';
        while (self::$db->more_results()) {
          self::$db->next_result();
          if (self::$db->error) {
            try {
              throw new Exception("MySQL error " . self::$db->error . " <br /> Query:<br /> ", self::$db->errno);
            } catch (Exception $e) {
              echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
              echo nl2br($e->getTraceAsString());
            }
            self::$db->rollback();
          }
        }
      }
    } else {
      self::logWarning(array('504' => $string['logwarning4']));
    }
    self::$db->commit();
    self::$db->autocommit(true);
  }

  /**
   * Create constrainsts to maintain database referential integrity
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
    $configObject->set_setting('paper_timezones', $timezones, 'timezones');
    $configObject->set_setting('summative_cohort_sizes', $cohorts, 'csv');
    $configObject->set_setting('paper_max_duration', 779, 'integer');
    $configObject->set_setting('summative_max_sittings', 6, 'integer');
    $configObject->set_setting('summative_hide_external', 0, 'boolean');
    $configObject->set_setting('summative_warn_external', 0, 'boolean');
    $configObject->set_setting('cfg_lti_allow_module_self_reg', 0, 'boolean');
    $configObject->set_setting('cfg_lti_allow_staff_module_register', 0, 'boolean');
    $configObject->set_setting('cfg_lti_allow_module_create', 0, 'boolean');
    $configObject->set_setting('lti_integration', 'default', 'string');
    $configObject->set_setting('lti_auth_timeout', 9072000, 'integer');
    $configObject->set_setting('cfg_gradebook_enabled', 1, 'boolean');
    $configObject->set_setting('cfg_api_enabled', 1, 'boolean');
    $configObject->set_setting('paper_marks_postive', range(1, 20), 'csv');
    $configObject->set_setting('paper_marks_negative', array(0, -0.25, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10), 'csv');
    $configObject->set_setting('paper_marks_partial', array_merge(range(0, 1, 0.1), range(2, 5)), 'csv');
    
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
      self::$sysadmin_username = $_POST['SysAdmin_username'];
    }
    if (empty(self::$sysadmin_password)) {
      self::$sysadmin_password = $_POST['SysAdmin_password'];
    }
    if (empty(self::$sysadmin_title)) {
      self::$sysadmin_title = $_POST['SysAdmin_title'];
    }
    if (empty(self::$sysadmin_first)) {
      self::$sysadmin_first = $_POST['SysAdmin_first'];
    }
    if (empty(self::$sysadmin_last)) {
      self::$sysadmin_last = $_POST['SysAdmin_last'];
    }
    if (empty(self::$sysadmin_email)) {
      self::$sysadmin_email = $_POST['SysAdmin_email'];
    }
  }
  
  /**
  * create the database and users if they do not exist
  *
  */
  static function createDatabase($dbname, $dbcharset) {
    global $string;
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
    $tables = new databaseTables($dbcharset);
    self::$db->autocommit(false);
    while ($sql = $tables->next()) {
      $res = self::$db->query($sql);
      @ob_flush();
      @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('012' => $string['displayerror3'] . self::$db->error . "<br /> $sql"));
        try {
          $err=self::$db->error;
          $mess=self::$db->errno;
          throw new Exception("MySQL error $err", $mess);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        }
        self::$db->rollback();
      }
    }
   self::$db->commit();

    self::generateUserName('cfg_db_username', self::$cfg_db_basename . '_auth');
    self::$cfg_db_password = gen_password() . gen_password();

    self::generateUserName('cfg_db_student_user', self::$cfg_db_basename . '_stu');
    self::$cfg_db_student_passwd = gen_password() . gen_password();
    self::generateUserName('cfg_db_staff_user', self::$cfg_db_basename . '_staff');
    self::$cfg_db_staff_passwd = gen_password() . gen_password();
    self::generateUserName('cfg_db_external_user', self::$cfg_db_basename . '_ext');
    self::$cfg_db_external_passwd  = gen_password() . gen_password();
    self::generateUserName('cfg_db_internal_user', self::$cfg_db_basename . '_int');
    self::$cfg_db_internal_passwd  = gen_password() . gen_password();
    self::generateUserName('cfg_db_sysadmin_user', self::$cfg_db_basename . '_sys');
    self::$cfg_db_sysadmin_passwd = gen_password() . gen_password();
    self::generateUserName('cfg_db_webservice_user', self::$cfg_db_basename . '_web');
    self::$cfg_db_webservice_passwd = gen_password() . gen_password();
    self::generateUserName('cfg_db_sct_user', self::$cfg_db_basename . '_sct');
    self::$cfg_db_sct_passwd = gen_password() . gen_password();
    self::generateUserName('cfg_db_inv_user', self::$cfg_db_basename . '_inv');
    self::$cfg_db_inv_passwd = gen_password() . gen_password();

    self::generateUserName('cfg_cron_user', 'cron');
    self::$cfg_cron_passwd = gen_password() . gen_password();

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
        echo self::$db->error . "<br />";
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
  * Check that we do not have a config file and that we can write one
  *
  */
  static function configFile() {
    global $string;

    $rogo_path = str_ireplace('/install/index.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));
    $errors = array();
    if (file_exists($rogo_path . '/config/config.inc.php')) {
      $errors['90'] =  sprintf($string['errors1'], $rogo_path."/config/config.inc.php");
      self::displayError($errors);
    }
  }

  /**
  * Check that  config file is writeable
  *
  */
  static function configFileIsWriteable() {

    $rogo_path = '';

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/install/index.php')  !== false) {
      $rogo_path = str_ireplace('/install/index.php','',  normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/updates/version4.php') !== false) {
      $rogo_path = str_ireplace('/updates/version4.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/updates/version5.php') !== false) {
      $rogo_path = str_ireplace('/updates/version5.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (is_writable($rogo_path . '/config/config.inc.php')) {
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

    $rogo_path = '';

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/install/index.php')  !== false) {
      $rogo_path = str_ireplace('/install/index.php','',normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/updates/version4.php') !== false) {
      $rogo_path = str_ireplace('/updates/version4.php','',normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (strpos(normalise_path($_SERVER['SCRIPT_FILENAME']), '/updates/version5.php') !== false) {
      $rogo_path = str_ireplace('/updates/version5.php','',normalise_path($_SERVER['SCRIPT_FILENAME']));
    }

    if (is_writable($rogo_path . '/config')) {
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
    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  /**
  * Check Apache can write to the required directories.
  */
  static function checkDirPermissionsPre() {
    global $string;
    // This should work for both windows and UNIX style paths.
    self::$rogo_path = str_ireplace('/install/index.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));
    $errors = array();

    if (!is_writable(self::$rogo_path . '/config/config.inc.php')) {
      if (!is_writable(self::$rogo_path . '/config')) {
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
    self::$rogo_path = str_ireplace('/install/index.php','', normalise_path($_SERVER['SCRIPT_FILENAME']));
    $errors = array();
    //tmp
    if (!is_writable($_POST['tmpdir'])) {
      $errors['100'] = sprintf($string['errors3'], $_POST['tmpdir']);
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
  * Check for installed software versions PHP
  *
  */
  static function checkSoftware() {
    global $string;
    $configObject = Config::get_instance();
    $errors = array();

    if (!empty($_SERVER['SERVER_SOFTWARE'])) {
      $server = preg_split("/[\/ ]/", $_SERVER['SERVER_SOFTWARE']);
    } else {
      $server = array('');
    }
    // php
    $php_min_ver = $configObject->getxml('php', 'min_version');
    $phpversion = phpversion();
    if ($phpversion < $php_min_ver) {
      $errors['202'] = sprintf($string['errors10'], $php_min_ver, $phpversion);
    }
    $phpModules = get_loaded_extensions();
    $extensions = $configObject->getxml('php', 'extensions');
    $errorcode = 202;
    foreach ($extensions->extension as $extension) {
        $errorcode += 1;
        if (!in_array($extension, $phpModules) ) {
          $errors[$errorcode] = sprintf($string['errors11'], $extension);
        }
    }
    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  /**
  * Check we are accessing through HTTPS for security
  *
  */
  static function checkHTTPS() {
    global $string;

    if ($_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8080) {
      self::displayError(array(100=> $string['errors12']));
      return false;
    }
    return true;
  }

  /**
  * Display errors with a nice message
  *
  */
  static function displayError($error = '') {
    global $string;

    if (!self::$cli) {
      echo "<div class=\"error\">\n";
    }
    if (is_array($error)) {
      foreach($error as $errCode => $message) {
        if (self::$cli) {
          cli_utils::prompt($string['errors13'] . "$errCode: $message");
        } else {
          echo "\t<div><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"!\" /> <strong>" . $string['errors13'] . " $errCode:</strong> $message</div>\n";
        }
      }
    }
    if (!self::$cli) {
      echo "</div>\n";
      self::displayFooter();
    }
    exit;
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
  * Display warnings with a nice message
  *
  */
  static function displayWarnings() {
    global $string;

    if (is_array(self::$warnings)) {
      echo "<h1>". $string['errors14']."</h1>";
      echo "<div class=\"warning\">\n";
      foreach(self::$warnings as $message) {
        echo "\t<div>" . $string['errors15'] . " $message</div>\n";
      }
      echo "</div>\n";
    }

  }

  /**
  * Display header
  *
  */
  static function displayHeader() {
    global $string, $version;

    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

      <title>Rog&#333; Install script</title>

      <link rel="stylesheet" type="text/css" href="../css/body.css" />
      <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
      <link rel="stylesheet" type="text/css" href="../css/header.css" />
      <style type="text/css">
        body {font-size:90%}
        h1 {margin-left:16px; font-size:140%; color:#1F497D}
        .error {float:none; color:#C00000; padding-left: .5em; vertical-align:top}
        .warning {float:none; color:#C00000; padding-left: .5em; vertical-align:top}
        label {float:left; width:175px; padding-left:0em; text-align:right; padding-right:6px}
        p {clear:both}
        .submit {margin-left:42%; padding-top:2em}
        table {border:none;padding:0px}
        .h {margin-top:1.5em; margin-bottom:0.5em; width:97%; color:#1E3287}
        .h hr {border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:98%}
        td.line {width:98%}
        input[type=text], input[type=password] {width:200px}
        form {padding:1em}
        form div {padding-left:2em; clear:both;}
      </style>

      <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
      <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
      <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
      <script>
        $(function() {
          $(document).tooltip();
        });
      </script>
    </head>
    <body>
    <table cellpadding="0" cellspacing="0" border="0" class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
      <img class="logo_img" src="../artwork/r_logo.gif" alt="logo" />
      <div class="logo_lrg_txt">Rog&#333; <?php echo $version; ?></div>
      <div class="logo_small_txt">System Installation</div>
      </th>
      <th style="text-align:right; padding-right:10px">
      <img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" />
      </th>
      </tr>
    </table>
    <?php
  }

  /**
  * Display footer
  *
  */
  static function displayfooter() {
    ?>
      </body>
      </html>
    <?php
  }

  static function writeConfigFile() {
    global $version, $cfg_encrypt_salt;

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

if (empty(\$root)) \$root = str_replace('/config', '/', str_replace('\\\\', '/', dirname(__FILE__)));
require \$root . '/include/path_functions.inc.php';

\$rogo_version = '{rogo_version}';
\$cfg_web_root = get_root_path() . '/';
\$cfg_root_path = rtrim('/' . trim(str_replace(normalise_path(\$_SERVER['DOCUMENT_ROOT']), '', \$cfg_web_root), '/'), '/');
\$cfg_secure_connection = true;    // If true site must be accessed via HTTPS
\$cfg_page_charset 	   = 'UTF-8';
\$cfg_company = '{cfg_company}';
\$cfg_academic_year_start = '07/01';
\$cfg_tmpdir = '{cfg_tmpdir}';

\$cfg_summative_mgmt = false;     // Set this to true for central summative exam administration.
\$cfg_client_lookup = '{labsecuritytype}'; //ipadress or name


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
  \$cfg_search_leadin_length = '{cfg_search_leadin_length}';
  \$cfg_timezone = '{cfg_timezone}';
  date_default_timezone_set(\$cfg_timezone);
// cron user
  \$cfg_cron_user = '{cfg_cron_user}';
  \$cfg_cron_passwd = '{cfg_cron_passwd}';

// Reports
  \$percent_decimals = 2;

// Standard Setting
  \$hofstee_defaults = array('pass'=>array(0, 'median', 0, 100), 'distinction'=>array('median', 100, 0, 100));
  \$hofstee_whole_numbers = true;

// SMS Imports
  \$cfg_sms_api = '';

\$authentication_fields_required_to_create_user = array('username', 'title', 'firstname', 'surname', 'email', 'role');

//Authentication settings
\$authentication = array(
  {cfg_authentication_arrays}
);
\$cfg_password_expire = 30;    // Set in days

\$enhancedcalculation = array('host' => 'localhost', 'port'=>6311,'timeout'=>5); //default enhancedcalc Rserve config options

//but use phpEval as default for enhanced calculation questions
\$enhancedcalc_type = 'phpEval'; //set the enhanced calculation to use php for maths
\$enhancedcalculation = array(); //no config options for phpEval plugin

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

//Editor
  \$cfg_editor_name = 'tinymce';
  \$cfg_editor_javascript = <<< SCRIPT
\$cfg_js_root
<script type="text/javascript" src="\$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="\$cfg_root_path/tools/tinymce/jscripts/tiny_mce/tiny_config.js"></script>
SCRIPT;

if(!isset(\$_SERVER['HTTP_HOST'])) {
  \$_SERVER['HTTP_HOST']='';
}

//A server specifc display name can be appended to rogo with the following
\$cfg_install_type = '';

//Warnings
  \$cfg_hour_warning = 10;       // Warning for summative exams

//Paper auto saving settings
  \$cfg_autosave_settimeout = 5; //Maximum time to wait for one request to succeed
  \$cfg_autosave_frequency = 30; //How often to auto save in seconds
  \$cfg_autosave_retrylimit = 3; //How many times to retry a failed save befor informing the user
  \$cfg_autosave_backoff_factor = 1.5; //each retry is lenghtend to \$cfg_autosave_settimeout + (\$cfg_autosave_backoff_factor * \$cfg_autosave_settimeout * retryCount);

//Assistance
  \$support_email = '{cfg_support_email}';
  \$emergency_support_numbers = {emergency_support_numbers};
  \$midexam_clarification = array('invigilators', 'students');
  
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

  //oauth settings
  \$cfg_oauth_access_lifetime = 1209600; // length of access token lifetime.
  \$cfg_oauth_refresh_token_lifetime = 1209600; // length of refresh token lifetime.
  \$cfg_oauth_always_issue_new_refresh_token = true; // enable or disable refresh tokens.
  
  //IMS enterprise setting
  \$cfg_ims_enabled = false;
  
  // Override db config settings with configs in this file?
  \$file_config_override = true;
  ?>
CONFIG;

    $config = str_replace('{rogo_version}', $version, $config);
    $config = str_replace('{SysAdmin_username}', 'USERNMAE_FOR_DEBUG', $config);
    $config = str_replace('{cfg_web_host}', self::$cfg_web_host, $config);
    $config = str_replace('{cfg_rogo_data}', self::$cfg_rogo_data, $config);
    $config = str_replace('{cfg_db_host}', self::$cfg_db_host, $config);
    $config = str_replace('{cfg_db_port}', self::$cfg_db_port, $config);
    $config = str_replace('{cfg_db_charset}', self::$cfg_db_charset, $config);
    $config = str_replace('{cfg_company}', self::$cfg_company, $config);

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

    $config = str_replace('{cfg_support_email}', self::$cfg_support_email, $config);
    $config = str_replace('{emergency_support_numbers}', self::$emergency_support_numbers, $config);

    $config = str_replace('{cfg_short_date}', self::$cfg_short_date, $config);
    $config = str_replace('{cfg_long_date}', self::$cfg_long_date, $config);
    $config = str_replace('{cfg_long_date_time}', self::$cfg_long_date_time, $config);
    $config = str_replace('{cfg_short_date_time}', self::$cfg_short_date_time, $config);
    $config = str_replace('{cfg_long_date_php}', self::$cfg_long_date_php, $config);
    $config = str_replace('{cfg_short_date_php}', self::$cfg_short_date_php, $config);
    $config = str_replace('{cfg_long_time_php}', self::$cfg_long_time_php, $config);
    $config = str_replace('{cfg_short_time_php}', self::$cfg_short_time_php, $config);
    $config = str_replace('{cfg_search_leadin_length}', self::$cfg_search_leadin_length, $config);
    $config = str_replace('{cfg_timezone}', self::$cfg_timezone, $config);
    $config = str_replace('{cfg_tmpdir}', self::$cfg_tmpdir, $config);
    $config = str_replace('{cfg_tablesorter_date_time}', self::$cfg_tablesorter_date_time, $config);
    $config = str_replace('{labsecuritytype}', self::$cfg_labsecuritytype, $config);

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

    $lookup_arrays= array();
    if (self::$cfg_uselookupLdap) {
      $lookup_arrays[]=  "array('ldap', array('ldap_server' => '{cfg_lookup_ldap_server}', 'ldap_search_dn' => '{cfg_lookup_ldap_search_dn}', 'ldap_bind_rdn' => '{cfg_lookup_ldap_bind_rdn}', 'ldap_bind_password' => '{cfg_lookup_ldap_bind_password}', 'ldap_user_prefix' => '{cfg_lookup_ldap_user_prefix}', 'ldap_attributes' => array('sAMAccountName' => 'username', 'sn' => 'surname', 'title' => 'title', 'givenName' => 'firstname', 'department' => 'school', 'mail' => 'email',  'cn' => 'username',  'employeeType' => 'role',  'initials' => 'initials'), 'lowercasecompare' => true, 'storeprepend' => 'ldap_'), 'LDAP')";
    }
    if (self::$cfg_uselookupXML) {
      $lookup_arrays[]= "array('XML', array('baseurl' => 'http://exports/', 'userlookup' => array( 'url' => '/student.ashx?campus=uk', 'mandatoryurlfields' => array('username'), 'urlfields' => array('username' => 'username'), 'xmlfields' => array('StudentID' => 'studentID', 'Title' => 'title', 'Forename' => 'firstname', 'Surname' => 'surname', 'Email' => 'email', 'Gender' => 'gender', 'YearofStudy' => 'yearofstudy', 'School' => 'school', 'Degree' => 'degree', 'CourseCode' => 'coursecode', 'CourseTitle' => 'coursetitle', 'AttendStatus' => 'attendstatus'), 'oneitemreturned' => true, 'override' => array('firstname' => true), 'storeprepend' => 'sms_userlookup_')), 'XML')";
    }

    $config = str_replace('{cfg_lookup_arrays}', implode(",\n  ", $lookup_arrays), $config);

    $salt = $cfg_encrypt_salt; //=$salt;

    $config = str_replace('{cfg_encrypt_salt}', $salt, $config);

    $config = str_replace('{cfg_ldap_server}', self::$cfg_ldap_server, $config);
    $config = str_replace('{cfg_ldap_search_dn}', self::$cfg_ldap_search_dn, $config);
    $config = str_replace('{cfg_ldap_bind_rdn}', self::$cfg_ldap_bind_rdn, $config);
    $config = str_replace('{cfg_ldap_bind_password}', self::$cfg_ldap_bind_password, $config);
    $config = str_replace('{cfg_ldap_user_prefix}', self::$cfg_ldap_user_prefix, $config);


    $config = str_replace('{cfg_lookup_ldap_server}', self::$cfg_lookup_ldap_server, $config);
    $config = str_replace('{cfg_lookup_ldap_search_dn}', self::$cfg_lookup_ldap_search_dn, $config);
    $config = str_replace('{cfg_lookup_ldap_bind_rdn}', self::$cfg_lookup_ldap_bind_rdn, $config);
    $config = str_replace('{cfg_lookup_ldap_bind_password}', self::$cfg_lookup_ldap_bind_password, $config);
    $config = str_replace('{cfg_lookup_ldap_user_prefix}', self::$cfg_lookup_ldap_user_prefix, $config);

    $config = str_replace('{SERVER_NAME}', $_SERVER['HTTP_HOST'], $config);

    if (file_exists(self::$rogo_path . '/config/config.inc.php')) {
      rename(self::$rogo_path . '/config/config.inc.php', self::$rogo_path . '/config/config.inc.old.php');
    }

    if (file_put_contents(self::$rogo_path . '/config/config.inc.php', $config) === false) {
      self::displayError(array(300=>'Could not write config file!'));
    }
  }
}
