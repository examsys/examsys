<?php
// This file is part of Rog?
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Utility class for installer related functionality
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/auth.inc';
require_once $cfg_web_root . 'classes/userutils.class.php';
require_once $cfg_web_root . 'classes/moduleutils.class.php';
require_once $cfg_web_root . 'classes/schoolutils.class.php';
require_once $cfg_web_root . 'classes/facultyutils.class.php';
require_once $cfg_web_root . 'classes/lang.class.php';
require_once $cfg_web_root . 'lang/' . $language . '/include/timezones.inc';

Class InstallUtils {
  public static $db;
  public static $rogo_path;

  public static $warnings;

  public static $cfg_company;
  public static $cfg_short_date;
  public static $cfg_long_date_time;
  public static $cfg_timezone;
  public static $cfg_tmpdir;

  //database config options
  public static $cfg_db_host;
  public static $cfg_db_port;
  public static $cfg_db_username;
  public static $cfg_db_password;
  public static $cfg_db_charset;
  public static $cfg_page_charset;

  public static $cfg_db_student_user;
  public static $cfg_db_student_passwd;
  public static $cfg_db_staff_user;
  public static $cfg_db_staff_passwd;
  public static $cfg_db_external_user;
  public static $cfg_db_external_passwd ;
  public static $cfg_db_sysadmin_user;
  public static $cfg_db_sysadmin_passwd;
  public static $cfg_db_sct_user;
  public static $cfg_db_sct_passwd;
  public static $cfg_db_inv_user;
  public static $cfg_db_inv_passwd;

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
  public static $cfg_use_ldap = 'false';

  public static $cfg_support_email;
  public static $emergency_support_numbers;


  static function displayForm() {
    global $string, $language, $timezone_array;

    ?>
    <script type="text/javascript">
      $(document).ready(function(){
          $("#installForm").validate();
      });

      $(document).ready(function() {
        $('#useLdap').change(function() {
            $('#ldapOptions').toggle();
          });
      });
    </script>
    <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">

      <table class="header"><tr><td><nobr><?php echo $string['company']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="company_name"><?php echo $string['companyname']; ?></label> <input type="text" id="company_name" name="company_name" value="University of" class="required" minlength="2" /></div>

      <table class="header"><tr><td><nobr><?php echo $string['server']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <br />
        <div><label for="tmpdir"><?php echo $string['tempdirectory']; ?></label> <input type="text" id="mysql_admin_pass" name="tmpdir" value="/tmp/" /></div>
        <div style="clear: left"><label for="page_charset"><?php echo $string['pagecharset']; ?></label> <select id="page_charset" name="page_charset"><option value="UTF-8">UTF-8</option><option value="ISO-8859-1">ISO 8859-1</option></select></div>

      <table class="header"><tr><td><nobr><?php echo $string['databaseadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><?php echo $string['needusername']; ?></div>
        <br />
        <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" id="mysql_admin_user" name="mysql_admin_user" class="required" minlength="2" /></div>
        <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" id="mysql_admin_pass" name="mysql_admin_pass"/></div>

        <table class="header"><tr><td><nobr><?php echo $string['databasesetup']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <br />
        <div><label for="mysql_db_host"><?php echo $string['databasehost']; ?></label> <input type="text" value="127.0.0.1" id="mysql_db_host" name="mysql_db_host" class="required" /></div>
        <div><label for="mysql_db_port"><?php echo $string['databaseport']; ?></label> <input type="text" value="3306" id="mysql_db_port" name="mysql_db_port" class="required" /></div>
        <div><label for="mysql_db_name"><?php echo $string['databasename']; ?></label> <input type="text" value="rogo" id="mysql_db_name" name="mysql_db_name" class="required" minlength="3" /></div>
        <div><label for="mysql_db_charset"><?php echo $string['databasecharset']; ?></label> <select id="mysql_db_charset" name="mysql_db_charset"><option value="utf8">UTF-8</option><option value="latin1">latin1</option></select></div>

      <table class="header"><tr><td><nobr><?php echo $string['databaseuser']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="mysql_database_username"><?php echo $string['rdbusername']; ?></label> <input type="text" value="" id="mysql_database_username" name="mysql_database_username" class="required" minlength="3"/></div>
        <div><label for="mysql_database_passwd"><?php echo $string['rdbpassword']; ?></label> <input type="password" value="" id="mysql_database_passwd" name="mysql_database_passwd" class="required" minlength="8" /></div>

      <table class="header"><tr><td><nobr><?php echo $string['timedateformats']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><?php echo sprintf($string['tdformatsare'],'<a href="http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format" target="_blank">MySQL DATE_FORMAT</a>'); ?></div>
        <br />
        <div><label for="cfg_short_date"><?php echo $string['date']; ?></label> <input type="text" id="cfg_short_date" name="cfg_short_date" class="required" minlength="2" value="%d/%m/%y" /> </div>
        <div><label for="cfg_long_date_time"><?php echo $string['datetime']; ?></label> <input type="text" id="cfg_long_date_time" name="cfg_long_date_time" class="required" value="%d/%m/%Y %H:%i" /></div>
        <div><label for="cfg_timezone"><?php echo $string['currenttimezone']; ?></label> <select id="cfg_timezone" name="cfg_timezone">
        <?php
          foreach ($timezone_array as $individual_zone => $display_zone) {
            if ($individual_zone == 'Europe/London') {
              echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
            } else {
              echo "<option value=\"$individual_zone\">$display_zone</option>";
            }
          }
        ?>
        </select></div>

        <table class="header"><tr><td><nobr><?php echo $string['ldapconfiguration']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="useLdap"><?php echo $string['useldap']; ?></label><input id="useLdap" name="useLdap" type="checkbox" /></div>
        <div id="ldapOptions" style="display:none;">
          <br/>
          <div><label for="ldap_server"><?php echo $string['ldapserver']; ?></label> <input type="text" value="" id="ldap_server" name="ldap_server" /></div>
          <div><label for="ldap_search_dn"><?php echo $string['searchdn']; ?></label> <input type="text" value="" id="ldap_search_dn" name="ldap_search_dn" /></div>
          <div><label for="ldap_bind_rdn"><?php echo $string['bindusername']; ?></label> <input type="text" value="" id="ldap_bind_rdn" name="ldap_bind_rdn" /></div>
          <div><label for="ldap_bind_password"><?php echo $string['bindpassword']; ?></label> <input type="password" value="" id="ldap_bind_password" name="ldap_bind_password" /></div>
          <div><label for="ldap_user_prefix"><?php echo $string['userprefix']; ?></label> <input type="text" value="" id="ldap_user_prefix" name="ldap_user_prefix" /> <img src="../artwork/information_icon.gif" class="tipright" width="16" height="16" title="<?php echo $string['userprefixtip'] ?>" /></div>
        </div>

      <table class="header"><tr><td><nobr><?php echo $string['sysadminuser']; ?></nobr></td><td class="line"><hr /></td></tr></table>
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
        <div><label for="SysAdmin_username"><?php echo $string['username']; ?></label> <input type="text" value="" id="SysAdmin_username" name="SysAdmin_username" class="required" minlength="3"/></div>
        <div><label for="SysAdmin_password"><?php echo $string['password']; ?></label> <input type="password" value="" id="SysAdmin_password" name="SysAdmin_password" class="required" minlength="8" /></div>

      <table class="header"><tr><td><nobr><?php echo $string['helpdb']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="loadHelp"><?php echo $string['loadhelp']; ?></label> <input id="loadHelp" name="loadHelp" type="checkbox" checked="checked"/></div>

      <table class="header"><tr><td><nobr><?php echo $string['supportemaila']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div></div>
        <br />
        <div><label for="support_email"><?php echo $string['supportemail']; ?></label> <input type="text" value="" id="support_email" name="support_email" class="" class="email"/> </div>

      <table class="header"><tr><td><nobr><?php echo $string['supportnumbers']; ?></nobr></td><td class="line"><hr /></td></tr></table>
        <div><label for="emergency_support1"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support1" name="emergency_support1" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number1" class="" /></div>
        <div><label for="emergency_support2"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support2" name="emergency_support2" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number2" class="" /></div>
        <div><label for="emergency_support3"><?php echo $string['name']; ?></label> <input type="text" value="" id="emergency_support3" name="emergency_support3" class="" /> <?php echo $string['number']; ?> <input type="text" value="" name="emergency_support_number3" class="" /></div>

      <div class="submit"> <input type="submit" name="install" value="<?php echo $string['install']; ?>" /> </div>
    </form>
    <?php
  }

  static function processForm() {
    global $string;
    self::$cfg_company = $_POST['company_name'];
    //check admin database user name and password and create the connection
    self::$cfg_db_host = $_POST['mysql_db_host'];
    self::$cfg_db_charset = $_POST['mysql_db_charset'];
    self::$cfg_page_charset = $_POST['page_charset'];
    self::$cfg_db_port = $_POST['mysql_db_port'];
    self::$cfg_db_name = $_POST['mysql_db_name'];
    self::$db_admin_username = $_POST['mysql_admin_user'];
    self::$db_admin_passwd = $_POST['mysql_admin_pass'];

    self::$cfg_db_username = $_POST['mysql_database_username'];
    self::$cfg_db_password = $_POST['mysql_database_passwd'];

    self::$cfg_SysAdmin_username = $_POST['SysAdmin_username'];

    self::$cfg_short_date = $_POST['cfg_short_date'];
    self::$cfg_long_date_time = $_POST['cfg_long_date_time'];
    self::$cfg_timezone = $_POST['cfg_timezone'];
    self::$cfg_tmpdir = $_POST['tmpdir'];

    //LDAP
    self::$cfg_ldap_server = $_POST['ldap_server'];
    self::$cfg_ldap_search_dn = $_POST['ldap_search_dn'];
    self::$cfg_ldap_bind_rdn = $_POST['ldap_bind_rdn'];
    self::$cfg_ldap_bind_password = $_POST['ldap_bind_password'];
    if( self::$cfg_ldap_server != '' ) {
      self::$cfg_use_ldap = 'true';
    } else {
      self::$cfg_use_ldap = 'false';
    }
    self::$cfg_ldap_user_prefix = $_POST['ldap_user_prefix'];

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

    //CREATE and populate DB
    self::$db = new mysqli(self::$cfg_db_host , self::$db_admin_username, self::$db_admin_passwd,'',self::$cfg_db_port);

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
    global $cfg_encrypt_salt;
    $cfg_encrypt_salt=$salt;

    self::createDatabase(self::$cfg_db_name, self::$cfg_db_charset);

    //LOAD help if requested
    if (isset($_POST['loadHelp'])) {
      self::loadHelp();
    }

    //Write out the config file
    self::writeConfigFile();

    echo "<p style=\"margin-left:10px\">" . $string['installed'] . "</p>\n";
    echo "<p style=\"margin-left:10px\">" . $string['deleteinstall'] . "</p>\n";
    echo "<p style=\"margin-left:10px\"><a href=\"/staff/\">" . $string['staffhomepage'] . "</a></p>\n";

    self::displayWarnings();

  }


  /**
  * Load the UoN help databases
  *
  */
  static function loadHelp() {
    global $string;
    $staff_help = './staff_help.sql';
    $student_help = './student_help.sql';

    //make sure we are using the right DB
    self::$db->select_db(self::$cfg_db_name);

    if (file_exists($staff_help)) {
      $query = file_get_contents($staff_help);
      self::$db->query("TRUNCATE staff_help");
      self::$db->query($query);
      if (self::$db->errno != 0) {
        self::logWarning(array('501' => $string['logwarning1'] . self::$db->error ));
      }
    } else {
      self::logWarning(array('502'=>  $string['logwarning2']));
    }

    if (file_exists($student_help)) {
      $query = file_get_contents($student_help);
      self::$db->query("TRUNCATE student_help");
      self::$db->query($query);
      if (self::$db->errno != 0) {
        self::logWarning(array('503' =>  $string['logwarning3'] . self::$db->error ));
      }
    } else {
      self::logWarning(array('504'=> $string['logwarning4']));
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
    while ($sql = $tables->next()) {
      $res = self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::displayError(array('012' => $string['displayerror3'] . self::$db->error . "</br> $sql"));
      }
    }

    self::$cfg_db_student_user = self::$cfg_db_name . '_stu';
    self::$cfg_db_student_passwd = gen_password() . gen_password();
    self::$cfg_db_staff_user = self::$cfg_db_name . '_staff';
    self::$cfg_db_staff_passwd = gen_password() . gen_password();
    self::$cfg_db_external_user = self::$cfg_db_name . '_ext';
    self::$cfg_db_external_passwd  = gen_password() . gen_password();
    self::$cfg_db_sysadmin_user = self::$cfg_db_name . '_sys';
    self::$cfg_db_sysadmin_passwd = gen_password() . gen_password();
    self::$cfg_db_sct_user = self::$cfg_db_name . '_sct';
    self::$cfg_db_sct_passwd = gen_password() . gen_password();
    self::$cfg_db_inv_user = self::$cfg_db_name . '_inv';
    self::$cfg_db_inv_passwd = gen_password() . gen_password();

    $priv_SQL = array();
    //create 'database user authentication user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_username . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_password . "'");
    if (self::$db->errno != 0) {
      echo "CREATE USER  '" . self::$cfg_db_username . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_password . "'" . '<br />';
      self::logWarning(array('013'=> $string['wdatabaseuser'] . self::$cfg_db_username . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_username . "'@'" . self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sid TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_modules TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, UPDATE, INSERT, DELETE ON " . $dbname . ".password_tokens TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".admin_access TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT,INSERT ON " . $dbname . ".temp_users TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_keys TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_user TO '". self::$cfg_db_username . "'@'". self::$cfg_db_host . "'";


    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_username . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user student user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_student_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_student_user . $string['wnotcreated']));
    }
   //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_help TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".feedback_release TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".ip_addresses TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".objectives TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".relationships TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT ON " . $dbname . ".student_modules TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".schools TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".question_exclude TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sessions TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".sid TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_searches TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_tutorial_log TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4_overall TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log6 TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_late TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT,INSERT,UPDATE ON " . $dbname . ".temp_users TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".announcements TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";    
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".standards_setting TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".state TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".lti_resource TO '". self::$cfg_db_student_user . "'@'".self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".lti_context TO '". self::$cfg_db_student_user . "'@'". self::$cfg_db_host . "'";


    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_student_user . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user external user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_external_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_external_user . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users TO '". self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".teams TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_help TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4_overall TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_late TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '" . self::$cfg_db_external_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_external_user . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user staff user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_staff_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_staff_user . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".* TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".users_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sid TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".password_tokens TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".special_needs TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".student_modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".student_notes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".questions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".questions_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".options TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".properties TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".feedback_release TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".paper_notes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".standards_setting TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".ebel TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".question_exclude TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".keywords_question TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".keywords_user TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".objectives TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_material TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_modules TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".reference_papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".relationships TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".recent_papers TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".folders TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".teams TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_searches TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".help_tutorial_log TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log4 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log4_overall TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log6 TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".log_late TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".textbox_marking TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".textbox_remark TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".track_changes TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".temp_users TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sessions TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".state TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_resource TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".lti_context TO '". self::$cfg_db_staff_user . "'@'". self::$cfg_db_host . "'";

    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach ($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_staff_user . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user SCT user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_sct_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_sct_user . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".papers TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".questions_metadata TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".options TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".paper_notes TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".sct_reviews TO '". self::$cfg_db_sct_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_sct_user . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user Invigilator user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_inv_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_inv_user . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".student_modules TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".users TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".sid TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".ip_addresses TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".labs TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $dbname . ".properties TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".student_notes TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".paper_notes TO '". self::$cfg_db_inv_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_inv_user . $string['wnotpermission']));
      }
    }

    $priv_SQL = array();
    //create 'database user sysadmin user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_sysadmin_user . "'@'". self::$cfg_db_host . "' IDENTIFIED BY '" . self::$cfg_db_sysadmin_passwd . "'");
    if (self::$db->errno != 0) {
      self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_sysadmin_user . $string['wnotcreated']));
    }
    //$priv_SQL[] = "REVOKE ALL PRIVILEGES ON $dbname.* FROM '". self::$cfg_db_sysadmin_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP  ON " . $dbname . ".* TO '". self::$cfg_db_sysadmin_user . "'@'". self::$cfg_db_host . "'";
    $priv_SQL[] = "FLUSH PRIVILEGES";
    foreach($priv_SQL as $sql) {
      self::$db->query($sql);
        @ob_flush();
        @flush();
      if (self::$db->errno != 0) {
	    echo self::$db->error . "<br />";
        self::logWarning(array('013'=> $string['wdatabaseuser']. self::$cfg_db_sysadmin_user . $string['wnotpermission']));
      }
    }

    //create sysadmin user
    UserUtils::createUser(  $_POST['SysAdmin_username'],
                            $_POST['SysAdmin_password'],
                            $_POST['SysAdmin_title'],
                            $_POST['SysAdmin_first'],
                            $_POST['SysAdmin_last'],
                            $_POST['SysAdmin_email'],
                            'University Lecturer',
                            '',
                            '1',
                            'Staff,SysAdmin',
                            '',
                            self::$db
                          );

    //create 100 guest accounts
    for ($i=1; $i<=100; $i++) {
      UserUtils::createUser(  'user' . $i,
                              '', //blank password will be generated
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

     //add traing school
    $facultyID = FacultyUtils::addFaculty('Administrative and Support Units',
                                        self::$db
                                     );

    $scoolID = SchoolUtils::addSchool(  $facultyID,
                                        'Training',
                                        self::$db
                                     );

     //create special modules
     ModuleUtils::addModules(  'TRAIN',
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
                                NULL,
                                NULL,
                                self::$db
                             );

    ModuleUtils::addModules(   'SYSTEM',
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
                                NULL,
                                NULL,
                                self::$db
                             );

    //FLUSH PRIVILEGES
    self::$db->query("FLUSH PRIVILEGES");
    if (self::$db->errno != 0) {
      self::logWarning(array('014'=> $string['logwarning20']));
    }
  }

  /**
  * Check that we do not have a config file and that we can write one
  *
  */
  static function configFile() {
  global $string;
    $rogo_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    if (file_exists($rogo_path . '/config/config.inc.php')) {
      $errors['90'] =  "<p>" . sprintf($string['errors1'],$rogo_path."/config/config.inc.php") . "</p>";
      $errors['90'] .= "<p>" . sprintf($string['errors2'],"<a href=\"/staff\">") . "</a></p>";
    }
  }

  /**
  * Check that we do not have a config file and that we can write one
  *
  */
  static function configFileIsWriteable() {
    global $string;
    $rogo_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $rogo_path = str_ireplace('/updates/version4.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    if (is_writable($rogo_path . '/config/config.inc.php')) {
      return true;
    } else {
      return false;
    }
  }

  /**
  * Check Apache can write to the required directories
  *
  */
  static function checkDirPermissionsPre() {
    global $string;
    self::$rogo_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    //media
    if (!is_writable(self::$rogo_path . '/media')) {
      $errors['102'] = sprintf($string['errors4'], self::$rogo_path);
    }
    //qti imports
    if (!is_writable(self::$rogo_path . '/qti/imports')) {
      $errors['103'] = sprintf($string['errors5'], self::$rogo_path);
    }
    //qti exports
    if (!is_writable(self::$rogo_path . '/qti/exports')) {
      $errors['104'] = sprintf($string['errors6'], self::$rogo_path);
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
    self::$rogo_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    //tmp
    if (!is_writable($_POST['tmpdir'])) {
      $errors['100'] = sprintf($string['errors3'], $_POST['tmpdir']);
    }
    if (count($errors) > 0) {
      self::displayError($errors);
    }
  }

  /**
  * Check for installed software versions PHP, Apache
  *
  */
  static function checkSoftware() {
    global $string;
    $errors = array();
    //apache
    $apache = explode('/',$_SERVER['SERVER_SOFTWARE']);
    $apache_min_ver = '2.0';
    if ( isset($apache[0]) and isset($apache[1]) ) {
      if ($apache[0] != 'Apache') {
        $errors['200'] = $string['errors8'].$apache[1];
      }
      $ver = explode(' ',$apache[1]);
      if (isset($ver[0]) and $ver[0] < $apache_min_ver) {
        $errors['201'] = $string['errors9']. $ver[0];
      }
    }

    //php
    $php_min_ver = '5.0';
    if (phpversion() < $php_min_ver) {
      $errors['202'] = $string['errors10'];
    }
    $phpModules = get_loaded_extensions();
    if ( !in_array('mysqli',$phpModules) ) {
      $errors['203'] = $string['errors11'];
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
    echo "<div class=\"error\">\n";
    if (is_array($error)) {
      foreach($error as $errCode => $message) {
        echo "\t<div>".$string['errors13']." $errCode:: $message</div>\n";
      }
    }
    echo "</div>\n";
    self::displayFooter();
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
      echo "<h2>". $string['errors14']."</h2>";
      echo "<div class=\"warning\">\n";
      foreach(self::$warnings as $message) {
        echo "\t<div>".$string['errors15']." $message</div>\n";
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
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
    <head>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
      <title>Rog&#333; Install script</title>
      <style type="text/css">
        html { padding: 0px; margin: 0px; width: 100%}
        body { padding: 0px; margin: 0px; width: 100%; font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black }
        .error { float: none; color: red; padding-left: .5em; vertical-align: top; }
        .warning { float: none; color: red; padding-left: .5em; vertical-align: top; }
        label { float:left; width:160px; padding-left:0em; text-align:right; padding-right:6px}
        p { clear: both; }
        .submit { margin-left: 42%; padding-top:2em; }
        table {border:none;padding:0px}
        table.topbar {font-weight: bold; width:100%; border-collapse:collapse; border:0px}
        .topbar td {background-color:#F1F5FB}
        .header {margin-top:1.5em;  margin-bottom:0.5em;  width:97%; color:#1E3287}
        .header hr  {border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:98%;}
        td.line {width:98%}

        input {width:200px}
        form {padding: 1em}
        form div {padding-left: 2em}
      </style>
      <link rel="stylesheet" type="text/css" href="../css/tipTip.css" />
      <script language="text/javascript" type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
      <script language="text/javascript" type="text/javascript" src="../js/jquery.validate.min.js"></script>
      <script language="text/javascript" type="text/javascript" src="../js/jquery.tipTip.minified.js"></script>
      <script type="text/javascript">
        $(function(){
          $(".tipright").tipTip({defaultPosition: 'right'});
        });
      </script>
    </head>
    <body>
    <table class="topbar" cellpadding="0" cellspacing="0">
      <tr>
        <td><div style="font-size:26pt; font-weight:bold; color:#001979">&nbsp;<?php echo $string['systeminstallation']; ?></div><div style="position:relative; left:51px; top:-6px; font-size:10pt; color:#001979; font-weight:bold">version <?php echo $version; ?></td>
        <td style="text-align:right; padding-top:4px; padding-right:15px"><img src="../artwork/rogo_logo.gif" width="137" height="61" alt="logo" border="0" /></td>
      </tr>
      <tr>
        <td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td>
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
    global $version;

    $config = <<<CONFIG
<?php
/**
*
* config file
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

if (empty(\$root)) \$root = str_replace('/config', '/', str_replace('\\\\', '/', dirname(__FILE__)));
require \$root . '/include/path_functions.inc.php';

\$rogo_version = '{rogo_version}';
\$cfg_web_root = get_root_path() . '/';
\$cfg_root_path = rtrim('/' . trim(str_replace(\$_SERVER['DOCUMENT_ROOT'], '', \$cfg_web_root), '/'), '/');
\$protocol = 'https://';
\$cfg_page_charset 	   = '{cfg_page_charset}';
\$cfg_company = '{cfg_company}';

\$cfg_tmpdir = '{cfg_tmpdir}';

// Local database
  \$cfg_db_username = '{cfg_db_username}';
  \$cfg_db_passwd   = '{cfg_db_passwd}';
  \$cfg_db_database = '{cfg_db_database}';
  \$cfg_db_host 	   = '{cfg_db_host}';
  \$cfg_db_charset 	   = '{cfg_db_charset}';
//student db user
  \$cfg_db_student_user = '{cfg_db_student_user}';
  \$cfg_db_student_passwd = '{cfg_db_student_passwd}';
//staff db user
  \$cfg_db_staff_user = '{cfg_db_staff_user}';
  \$cfg_db_staff_passwd = '{cfg_db_staff_passwd}';
//external examiner db user
  \$cfg_db_external_user = '{cfg_db_external}';
  \$cfg_db_external_passwd = '{cfg_db_external_passwd}';
//sysdamin db user
  \$cfg_db_sysadmin_user = '{cfg_db_sysadmin_user}';
  \$cfg_db_sysadmin_passwd = '{cfg_db_sysadmin_passwd}';
//sct db user
  \$cfg_db_sct_user = '{cfg_db_sct_user}';
  \$cfg_db_sct_passwd = '{cfg_db_sct_passwd}';
//invigilator db user
  \$cfg_db_inv_user = '{cfg_db_inv_user}';
  \$cfg_db_inv_passwd = '{cfg_db_inv_passwd}';
  // Date formats in MySQL DATE_FORMAT format
  \$cfg_short_date = '{cfg_short_date}';
  \$cfg_long_date_time = '{cfg_long_date_time}';
  \$cfg_timezone = '{cfg_timezone}';
  date_default_timezone_set(\$cfg_timezone);

// SMS Imports
  \$cfg_sms_api = '';

//LDAP
  \$cfg_ldap_server        = '{cfg_ldap_server}';
  \$cfg_ldap_search_dn     = '{cfg_ldap_search_dn}';
  \$cfg_ldap_bind_rdn      = '{cfg_ldap_bind_rdn}';
  \$cfg_ldap_bind_password = '{cfg_ldap_bind_password}';
  \$cfg_ldap_user_prefix   = '{cfg_ldap_user_prefix}';
  \$cfg_use_ldap           = {cfg_use_ldap};
  \$cfg_encrypt_salt       = '{cfg_encrypt_salt}';    // Do not alter if not on LDAP.

// Institutional email domains
// If using external authentication (e.g. LDAP) list the domains that will authenticate against the external system
// This will allow you to change the password of any users that do not match against those domains (e.g. external examiners)
  \$cfg_institutional_domains = array('nottingham.ac.uk');

// Root path for JS
  \$cfg_js_root = <<< SCRIPT
<script type="text/javascript">
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

//Server specific configuration based on hostname.
switch (strtolower(\$_SERVER['HTTP_HOST'])) {
  case 'rogo.local':
    \$cfg_install_type = ' (local)';
    break;
  default:
    \$cfg_install_type = '';
    error_reporting(0);
    break;
}

//Warnings
  \$cfg_hour_warning = 10;       // Warning for summative exams

//Assistance
  \$support_email = '{cfg_support_email}';
  \$emergency_support_numbers = {emergency_support_numbers};

//Global DEBUG OUTPUT
  if (isset(\$_SERVER['PHP_AUTH_USER']) AND \$_SERVER['PHP_AUTH_USER'] == '{SysAdmin_username}') {
    require_once \$_SERVER['DOCUMENT_ROOT'] . 'include/debug.inc';
  } else {
    \$dbclass = 'mysqli';
  }
  ?>
CONFIG;

    $config = str_replace('{rogo_version}', $version, $config);
    $config = str_replace('{SysAdmin_username}', 'USERNMAE_FOR_DEBUG', $config);
    $config = str_replace('{cfg_db_host}', self::$cfg_db_host, $config);
    $config = str_replace('{cfg_db_port}', self::$cfg_db_port, $config);
    $config = str_replace('{cfg_db_charset}', self::$cfg_db_charset, $config);
    $config = str_replace('{cfg_page_charset}', self::$cfg_page_charset, $config);
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
    $config = str_replace('{cfg_db_sysadmin_user}', self::$cfg_db_sysadmin_user, $config);
    $config = str_replace('{cfg_db_sysadmin_passwd}', self::$cfg_db_sysadmin_passwd, $config);
    $config = str_replace('{cfg_db_sct_user}', self::$cfg_db_sct_user, $config);
    $config = str_replace('{cfg_db_sct_passwd}', self::$cfg_db_sct_passwd, $config);
    $config = str_replace('{cfg_db_inv_user}', self::$cfg_db_inv_user, $config);
    $config = str_replace('{cfg_db_inv_passwd}', self::$cfg_db_inv_passwd, $config);

    $config = str_replace('{cfg_support_email}', self::$cfg_support_email, $config);
    $config = str_replace('{emergency_support_numbers}', self::$emergency_support_numbers, $config);

    $config = str_replace('{cfg_short_date}', self::$cfg_short_date, $config);
    $config = str_replace('{cfg_long_date_time}', self::$cfg_long_date_time, $config);
    $config = str_replace('{cfg_timezone}', self::$cfg_timezone, $config);
    $config = str_replace('{cfg_tmpdir}', self::$cfg_tmpdir, $config);

    $config = str_replace('{cfg_ldap_server}', self::$cfg_ldap_server, $config);
    $config = str_replace('{cfg_ldap_search_dn}', self::$cfg_ldap_search_dn, $config);
    $config = str_replace('{cfg_ldap_bind_rdn}', self::$cfg_ldap_bind_rdn, $config);
    $config = str_replace('{cfg_ldap_bind_password}', self::$cfg_ldap_bind_password, $config);
    $config = str_replace('{cfg_ldap_user_prefix}', self::$cfg_ldap_user_prefix, $config);
    $config = str_replace('{cfg_use_ldap}', self::$cfg_use_ldap, $config);
    


    global $cfg_encrypt_salt;
    $salt=$cfg_encrypt_salt; //=$salt;

    $config = str_replace('{cfg_encrypt_salt}', $salt, $config);

    $config = str_replace('{SERVER_NAME}', $_SERVER['HTTP_HOST'], $config);

    if (file_exists(self::$rogo_path . '/config/config.inc.php')) {
      rename(self::$rogo_path . '/config/config.inc.php', self::$rogo_path . '/config/config.inc.old.php');
    }

    if (file_put_contents(self::$rogo_path . '/config/config.inc.php', $config) === false) {
      self::displayError(array(300=>'Could not write config file !'));
    }
  }

}

class databaseTables {

  private $tableList = array();

  function __construct($charset) {
    $this->tableList['admin_access'] = <<<QUERY
      CREATE TABLE `admin_access` (
        `adminID` int(11) NOT NULL auto_increment,
        `userID` int(11) default NULL,
        `schools_id` int(11) default NULL,
        PRIMARY KEY (`adminID`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['announcements'] = <<<QUERY
      CREATE TABLE `announcements` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) DEFAULT NULL,
        `staff_msg` text,
        `student_msg` text,
        `icon` varchar(255) DEFAULT NULL,
        `startdate` datetime DEFAULT NULL,
        `enddate` datetime DEFAULT NULL,
        `deleted` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

   $this->tableList['courses'] = <<<QUERY
        CREATE TABLE `courses` (
          `id` int(11) NOT NULL auto_increment,
          `name` varchar(255) default NULL,
          `description` varchar(255) default NULL,
          `deleted` datetime default NULL,
          `schoolid` int(11) default NULL,
          PRIMARY KEY (`id`),
          KEY `degree` (`name`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['ebel'] = <<<QUERY
          CREATE TABLE `ebel` (
            `id` int(11) NOT NULL auto_increment,
            `setterID` mediumint(8) unsigned default NULL,
            `date_set` datetime default NULL,
            `category` char(3) default NULL,
            `percentage` float default NULL,
            PRIMARY KEY (`id`),
            KEY `SETTER_AND_DATE` (`setterID`,`date_set`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['ebel_grid_templates'] = <<<QUERY
          CREATE TABLE `ebel_grid_templates` (
            `id` int(11) NOT NULL auto_increment,
            `EE` tinyint(4) default NULL,
            `EI` tinyint(4) default NULL,
            `EN` tinyint(4) default NULL,
            `ME` tinyint(4) default NULL,
            `MI` tinyint(4) default NULL,
            `MN` tinyint(4) default NULL,
            `HE` tinyint(4) default NULL,
            `HI` tinyint(4) default NULL,
            `HN` tinyint(4) default NULL,
            `EE2` tinyint(4) default NULL,
            `EI2` tinyint(4) default NULL,
            `EN2` tinyint(4) default NULL,
            `ME2` tinyint(4) default NULL,
            `MI2` tinyint(4) default NULL,
            `MN2` tinyint(4) default NULL,
            `HE2` tinyint(4) default NULL,
            `HI2` tinyint(4) default NULL,
            `HN2` tinyint(4) default NULL,
            `name` varchar(255) default NULL,
            PRIMARY KEY  (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['faculty'] = <<<QUERY
          CREATE TABLE `faculty` (
            `id` int(11) NOT NULL auto_increment,
            `name` varchar(80) default NULL,
            `deleted` datetime default NULL,
            PRIMARY KEY  (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['feedback_release'] = <<<QUERY
        CREATE TABLE `feedback_release` (
          `idfeedback_release` int(11) NOT NULL auto_increment,
          `paper_id` smallint(5) default NULL,
          `date` datetime NOT NULL,
          `type` enum('objectives','questions') default NULL,
          PRIMARY KEY  (`idfeedback_release`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['folders'] = <<<QUERY
        CREATE TABLE `folders` (
          `id` int(4) NOT NULL auto_increment,
          `ownerID` mediumint(8) unsigned default NULL,
          `name` text,
          `team_name` varchar(255) default NULL,
          `created` datetime default NULL,
          `color` enum('yellow','red','green','blue','grey') default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_log'] = <<<QUERY
        CREATE TABLE `help_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `accessed` datetime default NULL,
          `pageID` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_searches'] = <<<QUERY
        CREATE TABLE `help_searches` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `searched` datetime default NULL,
          `searchstring` text,
          `hits` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_tutorial_log'] = <<<QUERY
        CREATE TABLE `help_tutorial_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `accessed` datetime default NULL,
          `tutorial` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['ip_addresses'] = <<<QUERY
        CREATE TABLE `ip_addresses` (
          `id` int(11) NOT NULL auto_increment,
          `lab` smallint(5) unsigned default NULL,
          `address` char(15) default NULL,
          `hostname` char(255) default NULL,
          `low_bandwidth` tinyint(4) default '0',
          PRIMARY KEY  (`id`),
          KEY `lab` (`lab`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['keywords_question'] = <<<QUERY
        CREATE TABLE `keywords_question` (
          `q_id` int(11) default NULL,
          `keywordID` int(11) default NULL,
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['keywords_user'] = <<<QUERY
        CREATE TABLE `keywords_user` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `keyword` char(255) default NULL,
          `keyword_type` enum('personal','team') default NULL,
          PRIMARY KEY  (`id`),
          KEY `username` (`userID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['labs'] = <<<QUERY
        CREATE TABLE `labs` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(255) default NULL,
          `campus` varchar(255) default NULL,
          `building` varchar(255) default NULL,
          `room_no` varchar(255) default NULL,
          `timetabling` text,
          `it_support` text,
          `plagarism` text,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log0'] = <<<QUERY
        CREATE TABLE `log0` (
          `id` int(8) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime NOT NULL default '0000-00-00 00:00:00',
          `q_paper` smallint(5) unsigned NOT NULL default '0',
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`),
          KEY `started` (`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log1'] = <<<QUERY
        CREATE TABLE `log1` (
          `id` int(8) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime NOT NULL default '0000-00-00 00:00:00',
          `q_paper` smallint(5) unsigned NOT NULL default '0',
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`),
          KEY `started` (`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log2'] = <<<QUERY
        CREATE TABLE `log2` (
          `id` int(8) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime NOT NULL default '0000-00-00 00:00:00',
          `q_paper` smallint(5) unsigned NOT NULL default '0',
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`),
          KEY `started` (`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log3'] = <<<QUERY
        CREATE TABLE `log3` (
          `id` int(8) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime NOT NULL default '0000-00-00 00:00:00',
          `q_paper` smallint(5) unsigned NOT NULL default '0',
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`),
          KEY `started` (`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log4'] = <<<QUERY
        CREATE TABLE `log4` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime default NULL,
          `q_paper` smallint(5) unsigned default NULL,
          `q_id` int(11) default NULL,
          `rating` text,
          `q_parts` varchar(50) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log4_overall'] = <<<QUERY
        CREATE TABLE `log4_overall` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime default NULL,
          `q_paper` smallint(5) unsigned default NULL,
          `overall_rating` text,
          `numeric_score` int(11) default NULL,
          `feedback` text,
          `student_grade` char(25) default NULL,
          `examinerID` mediumint(8) unsigned default NULL,
          `osce_type` enum('electronic','paper') default NULL,
          `year` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log5'] = <<<QUERY
        CREATE TABLE `log5` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime default NULL,
          `q_paper` smallint(5) unsigned default NULL,
          `q_id` int(11) default NULL,
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log6'] = <<<QUERY
        CREATE TABLE `log6` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` smallint(6) default NULL,
          `reviewerID` mediumint(9) default NULL,
          `peerID` mediumint(9) default NULL,
          `started` datetime default NULL,
          `q_id` int(11) default NULL,
          `rating` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_late'] = <<<QUERY
        CREATE TABLE `log_late` (
          `id` int(8) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `started` datetime NOT NULL default '0000-00-00 00:00:00',
          `q_paper` smallint(5) unsigned NOT NULL default '0',
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log_metadata'] = <<<QUERY
        CREATE TABLE `log_metadata` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(9) default NULL,
          `paperID` smallint(6) default NULL,
          `started` datetime default NULL,
          `ipaddress` char(15) default NULL,
          `student_grade` char(25) default NULL,
          `year` tinyint(4) default NULL,
          `attempt` tinyint(4) default NULL,
          PRIMARY KEY  (`id`),
          KEY `userID` (`userID`,`paperID`,`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['modules'] = <<<QUERY
        CREATE TABLE `modules` (
          `id` int(11) NOT NULL auto_increment,
          `moduleid` char(25) default NULL,
          `fullname` text,
          `active` tinyint(4) default NULL,
          `vle_api` varchar(255) default NULL,
          `checklist` varchar(255) default NULL,
          `sms` varchar(255) default NULL,
          `selfenroll` tinyint(4) default NULL,
          `schoolid` int(11) default NULL,
          `neg_marking` tinyint(1) default NULL,
          `ebel_grid_template` int(11) default NULL,
          PRIMARY KEY  (`id`),
          KEY `guideid` (`moduleid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['objectives'] = <<<QUERY
        CREATE TABLE `objectives` (
          `obj_id` int(11) NOT NULL,
          `objective` text NOT NULL,
          `moduleID` char(25) NOT NULL,
          `identifier` bigint(20) unsigned NOT NULL,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16') NOT NULL,
          `sequence` int(11) default NULL,
          PRIMARY KEY  (`obj_id`,`moduleID`,`calendar_year`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['options'] = <<<QUERY
        CREATE TABLE `options` (
          `o_id` int(4) NOT NULL default '0',
          `option_text` text,
          `o_media` varchar(255) default NULL,
          `o_media_width` varchar(4) default NULL,
          `o_media_height` varchar(4) default NULL,
          `feedback_right` text,
          `feedback_wrong` text,
          `correct` text,
          `id_num` int(11) NOT NULL auto_increment,
          `marks_correct` float default NULL,
          `marks_incorrect` float default NULL,
          `marks_partial` float default NULL,
          PRIMARY KEY  (`id_num`),
          KEY `o_id` (`o_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['paper_metadata_security'] = <<<QUERY
        CREATE TABLE `paper_metadata_security` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` int(11) default NULL,
          `name` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['paper_notes'] = <<<QUERY
        CREATE TABLE `paper_notes` (
          `note_id` int(11) NOT NULL auto_increment,
          `note` text,
          `note_date` datetime default NULL,
          `paper_id` smallint(6) default NULL,
          `note_authorID` mediumint(9) default NULL,
          `note_workstation` varchar(15) default NULL,
          PRIMARY KEY  (`note_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['papers'] = <<<QUERY
        CREATE TABLE `papers` (
          `p_id` int(4) NOT NULL auto_increment,
          `paper` smallint(5) unsigned NOT NULL default '0',
          `question` int(4) unsigned NOT NULL default '0',
          `screen` tinyint(2) unsigned NOT NULL default '0',
          `display_pos` smallint(5) unsigned default NULL,
          PRIMARY KEY  (`p_id`),
          KEY `paper` (`paper`),
          KEY `question_idx` (`question`),
          KEY `screen` (`screen`),
          KEY `paper_2` (`paper`,`display_pos`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['password_tokens'] = <<<QUERY
        CREATE TABLE `password_tokens` (
          `id` int(11) NOT NULL auto_increment,
          `user_id` int(11) NOT NULL,
          `token` char(16) NOT NULL,
          `time` datetime NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['properties'] = <<<QUERY
        CREATE TABLE `properties` (
          `property_id` smallint(5) unsigned NOT NULL auto_increment,
          `paper_title` varchar(255) default NULL,
          `start_date` datetime default NULL,
          `end_date` datetime default NULL,
          `timezone` varchar(255) default NULL,
          `paper_type` enum('0','1','2','3','4','5','6') default NULL,
          `paper_prologue` text,
          `paper_postscript` text,
          `bgcolor` varchar(20) default NULL,
          `fgcolor` varchar(20) default NULL,
          `themecolor` varchar(20) default NULL,
          `labelcolor` varchar(20) default NULL,
          `fullscreen` enum('0','1') NOT NULL default '0',
          `marking` char(60) default NULL,
          `bidirectional` enum('0','1') NOT NULL default '0',
          `pass_mark` tinyint(4) default NULL,
          `distinction_mark` tinyint(4) default NULL,
          `paper_ownerID` mediumint(9) default NULL,
          `folder` varchar(255) default NULL,
          `labs` text,
          `rubric` text,
          `calculator` tinyint(4) default NULL,
          `externals` text,
          `exam_duration` smallint(6) default NULL,
          `deleted` datetime default NULL,
          `created` datetime default NULL,
          `random_mark` float default NULL,
          `total_mark` mediumint(9) default NULL,
          `display_correct_answer` enum('0','1') default NULL,
          `display_question_mark` enum('0','1') default NULL,
          `display_students_response` enum('0','1') default NULL,
          `display_feedback` enum('0','1') default NULL,
          `hide_if_unanswered` enum('0','1') default NULL,
          `moduleID` text,
          `calendar_year` enum('2002/03','2003/04','2004/05','2005/06','2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') default NULL,
          `internal_reviewers` text,
          `external_review_deadline` date default NULL,
          `internal_review_deadline` date default NULL,
          `sound_demo` enum('0','1') default NULL,
          `latex_needed` tinyint(4) default '0',
          `password` char(20) default NULL,
          `retired` datetime default NULL,
          `crypt_name` varchar(32) default NULL,
          PRIMARY KEY  (`property_id`),
          KEY `paper_title` (`paper_title`),
          KEY `paper_owner` (`paper_ownerID`),
          KEY `question_type` (`paper_type`),
          KEY `crypt_name_idx` (`crypt_name`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;


    $this->tableList['question_exclude'] = <<<QUERY
        CREATE TABLE `question_exclude` (
          `id` int(11) NOT NULL auto_increment,
          `q_paper` int(11) default NULL,
          `q_id` int(11) default NULL,
          `parts` varchar(255) default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `date` datetime default NULL,
          `reason` text,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['questions'] = <<<QUERY
        CREATE TABLE `questions` (
          `q_id` int(4) NOT NULL auto_increment,
          `q_type` enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based') default NULL,
          `theme` text,
          `scenario` text,
          `leadin` text,
          `correct_fback` text,
          `incorrect_fback` text,
          `display_method` text,
          `notes` text,
          `ownerID` mediumint(9) default NULL,
          `q_media` text,
          `q_media_width` varchar(100) default NULL,
          `q_media_height` varchar(100) default NULL,
          `creation_date` datetime default NULL,
          `last_edited` datetime default NULL,
          `bloom` enum('Knowledge','Comprehension','Application','Analysis','Synthesis','Evaluation') default NULL,
          `q_group` text,
          `scenario_plain` text,
          `leadin_plain` text,
          `checkout_time` datetime default NULL,
          `checkout_authorID` mediumint(8) unsigned default NULL,
          `deleted` datetime default NULL,
          `locked` datetime default NULL,
          `std` varchar(100) default NULL,
          `status` enum('Normal','Retired','Incomplete','Experimental','Beta') default NULL,
          `q_option_order` enum('display order','alphabetic','random') default NULL,
          `score_method` enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark') default NULL,
          PRIMARY KEY  (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

$this->tableList['questions_metadata'] = <<<QUERY
        CREATE TABLE `questions_metadata` (
          `id` int(11) NOT NULL auto_increment,
          `questionID` int(11) default NULL,
          `type` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['recent_papers'] = <<<QUERY
        CREATE TABLE `recent_papers` (
          `userID` mediumint(8) unsigned NOT NULL default '0',
          `paperID` mediumint(9) NOT NULL default '0',
          `accessed` datetime default NULL,
          PRIMARY KEY  (`userID`,`paperID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_material'] = <<<QUERY
        CREATE TABLE `reference_material` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) DEFAULT NULL,
          `content` text,
          `width` smallint(5) unsigned DEFAULT NULL,
          `created` datetime DEFAULT NULL,
          `deleted` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_modules'] = <<<QUERY
        CREATE TABLE `reference_modules` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `refID` mediumint(8) unsigned DEFAULT NULL,
          `moduleID` mediumint(8) unsigned DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_papers'] = <<<QUERY
        CREATE TABLE `reference_papers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `paperID` mediumint(9) DEFAULT NULL,
          `refID` mediumint(9) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['relationships'] = <<<QUERY
        CREATE TABLE `relationships` (
          `rel_id` int(11) NOT NULL auto_increment,
          `module_id` char(15) NOT NULL,
          `paper_id` int(11) NOT NULL,
          `question_id` int(11) NOT NULL,
          `obj_id` int(11) NOT NULL,
          `calendar_year` enum('2006/07','2007/08','2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          PRIMARY KEY  (`rel_id`),
          KEY `module_id_idx` (`module_id`),
          KEY `paper_id_idx` (`paper_id`),
          KEY `calendar_year` (`calendar_year`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['review_comments'] = <<<QUERY
        CREATE TABLE `review_comments` (
          `id` int(11) NOT NULL auto_increment,
          `q_paper` int(11) default NULL,
          `q_id` int(11) default NULL,
          `category` tinyint(4) default NULL,
          `comment` text,
          `reviewer` mediumint(8) unsigned default NULL,
          `reviewed` datetime default NULL,
          `action` enum('Not actioned','Read - disagree','Read - actioned') default NULL,
          `response` text,
          `review_type` enum('External','Internal') default NULL,
          `ipaddress` varchar(15) default NULL,
          `duration` mediumint(9) default NULL,
          `screen` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['schools'] = <<<QUERY
        CREATE TABLE `schools` (
          `id` int(11) NOT NULL auto_increment,
          `school` char(255) default NULL,
          `facultyID` int(11) default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sct_reviews'] = <<<QUERY
        CREATE TABLE `sct_reviews` (
          `id` int(11) NOT NULL auto_increment,
          `reviewer_name` text,
          `reviewer_email` text,
          `paperID` smallint(5) unsigned default NULL,
          `q_id` int(4) default NULL,
          `answer` tinyint(4) default NULL,
          `reason` text,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sessions'] = <<<QUERY
        CREATE TABLE `sessions` (
          `sess_id` int(11) NOT NULL auto_increment,
          `identifier` bigint(20) unsigned NOT NULL,
          `moduleID` char(25) NOT NULL,
          `title` text NOT NULL,
          `source_url` text,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          `occurrence` datetime default NULL,
          PRIMARY KEY  (`identifier`,`moduleID`,`calendar_year`),
          KEY `sess_id` (`sess_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sid'] = <<<QUERY
        CREATE TABLE `sid` (
          `student_id` char(15) default NULL,
          `userID` mediumint(8) unsigned NOT NULL default '0',
          PRIMARY KEY  (`userID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sms_imports'] = <<<QUERY
        CREATE TABLE `sms_imports` (
          `id` int(11) NOT NULL auto_increment,
          `updated` date default NULL,
          `moduleid` char(25) default NULL,
          `enrolements` int(11) default NULL,
          `enrolement_details` text,
          `deletions` int(11) default NULL,
          `deletion_details` text,
          `import_type` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['special_needs'] = <<<QUERY
        CREATE TABLE `special_needs` (
          `special_id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `background` varchar(20) default NULL,
          `foreground` varchar(20) default NULL,
          `textsize` int(11) default NULL,
          `extra_time` tinyint(4) default NULL,
          `marks_color` varchar(20) default NULL,
          `themecolor` varchar(20) default NULL,
          `labelcolor` varchar(20) default NULL,
          `font` varchar(50) default NULL,
          PRIMARY KEY  (`special_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['staff_help'] = <<<QUERY
        CREATE TABLE `staff_help` (
          `id` smallint(6) NOT NULL auto_increment,
          `title` text,
          `body` text,
          `body_plain` text,
          `type` enum('page','pointer') default NULL,
          `checkout_time` datetime default NULL,
          `checkout_authorID` mediumint(8) unsigned default NULL,
          `roles` enum('SysAdmin','Admin','Staff') default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`),
          FULLTEXT KEY `title` (`title`,`body_plain`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['standards_setting'] = <<<QUERY
        CREATE TABLE `standards_setting` (
          `id` int(11) NOT NULL auto_increment,
          `setterID` mediumint(8) unsigned default NULL,
          `questionID` int(11) default NULL,
          `std_set` datetime default NULL,
          `rating` text,
          `paperID` smallint(5) unsigned NOT NULL default '0',
          `method` enum('Modified Angoff','Angoff (Yes/No)','Ebel') default NULL,
          `group_review` text,
          PRIMARY KEY  (`id`),
          KEY `paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['state'] = <<<QUERY
        CREATE TABLE `state` (
          `userID` int(10) unsigned DEFAULT NULL,
          `state_name` varchar(255) DEFAULT NULL,
          `content` varchar(255) DEFAULT NULL,
          `page` varchar(255) DEFAULT NULL,
          UNIQUE KEY `idx_user_state` (`userID`,`state_name`,`page`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['student_help'] = <<<QUERY
        CREATE TABLE `student_help` (
          `id` smallint(6) NOT NULL auto_increment,
          `title` text,
          `body` text,
          `body_plain` text,
          `type` enum('page','pointer') default NULL,
          `checkout_time` datetime default NULL,
          `checkout_authorID` mediumint(8) unsigned default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`),
          FULLTEXT KEY `title` (`title`,`body_plain`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['student_modules'] = <<<QUERY
        CREATE TABLE `student_modules` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `moduleid` char(15) NOT NULL,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') default NULL,
          `attempt` tinyint(4) default NULL,
          `auto_update` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['student_notes'] = <<<QUERY
        CREATE TABLE `student_notes` (
          `note_id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `note` text,
          `note_date` datetime default NULL,
          `paper_id` smallint(5) unsigned NOT NULL default '0',
          `note_authorID` mediumint(8) unsigned default NULL,
          PRIMARY KEY  (`note_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sys_errors'] = <<<QUERY
        CREATE TABLE `sys_errors` (
          `id` int(11) NOT NULL auto_increment,
          `occurred` datetime default NULL,
          `userID` int(11) default NULL,
          `errtype` enum('Notice','Warning','Fatal Error','Unknown') default NULL,
          `errstr` text,
          `errfile` text,
          `errline` int(11) default NULL,
          `fixed` datetime default NULL,
          `php_self` text,
          `query_string` text,
          `request_method` enum('GET','HEAD','POST','PUT','DELETE') default NULL,
          `paperID` int(11) default NULL,
          `post_data` text,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['teams'] = <<<QUERY
        CREATE TABLE `teams` (
          `groupID` int(4) NOT NULL auto_increment,
          `name` char(255) default NULL,
          `memberID` mediumint(8) unsigned default NULL,
          `added` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
          `type` enum('System','Custom') default NULL,
          PRIMARY KEY  (`groupID`),
          KEY `name` (`name`(20))
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['temp_users'] = <<<QUERY
        CREATE TABLE `temp_users` (
          `id` int(11) NOT NULL auto_increment,
          `first_names` char(60) default NULL,
          `surname` char(50) default NULL,
          `title` enum('Dr','Miss','Mr','Mrs','Ms','Professor') default NULL,
          `student_id` char(10) default NULL,
          `assigned_account` char(10) default NULL,
          `reserved` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['textbox_marking'] = <<<QUERY
        CREATE TABLE `textbox_marking` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` int(11) default NULL,
          `q_id` int(11) default NULL,
          `answer_id` int(11) default NULL,
          `markerID` mediumint(8) unsigned default NULL,
          `mark` float default NULL,
          `comments` text,
          `date` datetime default NULL,
          `phase` tinyint(4) default NULL,
          `logtype` tinyint(4) default NULL,
          `student_userID` mediumint(8) unsigned default NULL,
          PRIMARY KEY  (`id`),
          KEY `paperID` (`paperID`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['textbox_remark'] = <<<QUERY
        CREATE TABLE `textbox_remark` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` int(11) default NULL,
          `userID` mediumint(8) unsigned default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['track_changes'] = <<<QUERY
        CREATE TABLE `track_changes` (
          `id` int(4) NOT NULL auto_increment,
          `type` varchar(40) default NULL,
          `typeID` int(4) default NULL,
          `editor` mediumint(8) unsigned default NULL,
          `old` text,
          `new` text,
          `changed` datetime default NULL,
          `part` text,
          PRIMARY KEY  (`id`),
          KEY `typeID` (`typeID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['users'] = <<<QUERY
        CREATE TABLE `users` (
          `password` char(90) default NULL,
          `grade` char(30) default NULL,
          `surname` char(35) default NULL,
          `initials` char(10) default NULL,
          `title` varchar(30) default NULL,
          `username` char(15) default NULL,
          `email` char(65) default NULL,
          `roles` char(40) default NULL,
          `id` smallint(6) NOT NULL auto_increment,
          `first_names` char(60) default NULL,
          `gender` enum('Male','Female') default NULL,
          `last_login` datetime default NULL,
          `special_needs` tinyint(4) default '0',
          `yearofstudy` tinyint(4) default NULL,
          PRIMARY KEY  (`id`),
          KEY `username_index` (`username`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['users_metadata'] = <<<QUERY
        CREATE TABLE `users_metadata` (
          `id` int(11) NOT NULL auto_increment,
          `userID` int(11) default NULL,
          `moduleID` int(11) default NULL,
          `type` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          `calendar_year` enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;


    $this->tableList['lti_user'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_user` (
          `oauth_consumer_key` varchar(200) NOT NULL,
          `user_id` varchar(200) NOT NULL,
          `rogo_id` int(11) NOT NULL,
          `updated_on` datetime,
          PRIMARY KEY (`oauth_consumer_key`,`user_id`),
          KEY `rogo_id` (`rogo_id`)
         ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;


    $this->tableList['lti_resource'] = <<<QUERY
        CREATE TABLE IF NOT EXISTS `lti_resource` (
        `oauth_consumer_key` varchar(255) NOT NULL DEFAULT '',
        `lti_resource_id` varchar(255) NOT NULL,
        `internal_id` varchar(255) DEFAULT NULL,
        `itype` varchar(255) DEFAULT NULL,
        `updated` datetime,
        PRIMARY KEY (`oauth_consumer_key`,`lti_resource_id`),
        KEY `destination2` (`itype`),
        KEY `destination` (`internal_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;


    $this->tableList['lti_keys'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_keys` (
          `id` mediumint(9) NOT NULL AUTO_INCREMENT,
          `oauth_consumer_key` char(255)NOT NULL,
          `secret` char(255)DEFAULT NULL,
          `name` char(255) DEFAULT NULL,
          `context_id` char(255) DEFAULT NULL,
          `deleted` datetime,
          `updated_at` datetime,
          PRIMARY KEY (`id`),
          KEY `oauth_consumer_key` (`oauth_consumer_key`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

      $this->tableList['lti_context'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_context` (
          `oauth_consumer_key` VARCHAR( 255 ) NOT NULL ,
          `lti_context_id` VARCHAR( 255 ) NOT NULL ,
          `c_internal_id` VARCHAR( 255 ) NOT NULL ,
          `updated_on` DATETIME NOT NULL,
          PRIMARY KEY (`oauth_consumer_key`,`lti_context_id`),
          KEY `c_internal_id` (`c_internal_id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;


  }

  function next() {
    if (count($this->tableList) > 0) {
      return array_pop($this->tableList);
    } else {
      return false;
    }
  }
}

?>