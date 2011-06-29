<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Utility class for installer related functionality
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once $_SERVER['DOCUMENT_ROOT'] . '/touchstone/classes/userutils.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/touchstone/classes/moduleutils.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/touchstone/classes/schoolutils.class.php';

Class InstallUtils {
	
  public static $db;
   public static $touchstone_path;
  
  //database config options
  public static $cfg_db_host;
  public static $cfg_db_port;
  public static $cfg_db_username;
  public static $cfg_db_password;
  public static $cfg_db_name;
  public static $db_admin_username;
  public static $db_admin_passwd;
  
  public static $ts_version = '4.0';
  public static $support_email;
  public static $cfg_SysAdmin_username;
  public static $emergency_support_numbers;
  
  public static $cfg_ldap_server;
  public static $cfg_ldap_search_dn;
  public static $cfg_ldap_bind_rdn;
  public static $cfg_ldap_bind_password;
  public static $cfg_use_ldap = 'false';
  
  static function displayForm() {
    ?>
    <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <h2>Database Admin User</h2>
      <fieldset>
        <div>The installer need the username and password of a MySQL admin user to create the database and required tables. This username is not saved to the server and is only used by this install script.</div>
        <br />
        <div>Username: <input type="text" value="" name="mysql_admin_user" class="required" minlength="2" /> </div>
        <div>Password: <input type="password" value="" name="mysql_admin_pass"/></div>
      </fieldset>
      
      <h2>Database Setup</h2>
      <fieldset>
        <div></div>
        <br />
        <div>Database host: <input type="text" value="127.0.0.1" name="mysql_db_host" class="required" /> </div>
        <div>Database port: <input type="text" value="3306" name="mysql_db_port" class="required" /> </div>
        <div>Database Name: <input type="text" value="" name="mysql_db_name" class="required" minlength="3" /> </div>
        <h3>Database user</h3>
        <div>username: <input type="text" value="" name="mysql_touchstone_username" class="required" minlength="3"/></div>
        <div>password: <input type="password" value="" name="mysql_touchstone_passwd" class="required" minlength="8" /></div>
      </fieldset>
      
      <h2>TouchStone SysAdmin User</h2>
      <fieldset>
        <div></div>
        <br />
        <div>Title: <input type="text" value="" name="SysAdmin_title" class="required" /> </div>
        <div>First Name: <input type="text" value="" name="SysAdmin_first" class="required" /> </div>
        <div>Surname: <input type="text" value="" name="SysAdmin_last" class="required" minlength="3" /> </div>
        <div>Email Address: <input type="text" value="" name="SysAdmin_email" class="required email" /></div>
        <div>username: <input type="text" value="" name="SysAdmin_username" class="required" minlength="3"/></div>
        <div>password: <input type="password" value="" name="SysAdmin_password" class="required" minlength="8" /></div>
      </fieldset>
      <div> <input type="submit" name="install" value="Install Touchstone" /> </div>
    </form>
    <?php
  }
  
  static function  processForm() {
    
    //check admin database user name and password and create the connection
    self::$cfg_db_host = $_POST['mysql_db_host'];
    self::$cfg_db_port = $_POST['mysql_db_port'];
    self::$cfg_db_name = $_POST['mysql_db_name'];
    self::$db_admin_username = $_POST['mysql_admin_user'];
    self::$db_admin_passwd = $_POST['mysql_admin_pass'];
    self::$cfg_db_username = $_POST['mysql_touchstone_username'];
    self::$cfg_db_password = $_POST['mysql_touchstone_passwd'];
    
    self::$cfg_SysAdmin_username = $_POST['SysAdmin_username'];

    

    self::$db = new mysqli(self::$cfg_db_host , self::$db_admin_username, self::$db_admin_passwd,'',self::$cfg_db_port);
    if (mysqli_connect_error()) {
      self::displayError(array('001' => mysqli_connect_error()));  
    }
    self::createDatabase(self::$cfg_db_name);
    
    self::writeConfigFile();
  }
  
  /**
  * create the database and users if they do not exist
  *
  */
  static function createDatabase($dbname) {
    
    $res = self::$db->prepare("SHOW DATABASES LIKE '$dbname'");
    $res->execute();
    $res->store_result();
    if ($res->num_rows > 0) {
      self::displayError(array('010' => "The database name '$dbname' is in use please use a different one")); 
    }
    $res->close();
    
    $res = self::$db->prepare("CREATE DATABASE $dbname");
    $res->execute();
    if (self::$db->errno != 0) {
      self::displayError(array('011' => "The database '$dbname' could not be created please check the admin users permissions")); 
    }
    $res->close();
    
    //select the newly created database
    self::$db->change_user(self::$db_admin_username, self::$db_admin_passwd,self::$cfg_db_name);
    
    //create tables
    $tables = new touchStoneTables();
    while ($sql = $tables->next()) {
      $res = self::$db->query($sql);
      if (self::$db->errno != 0) {
        self::displayError(array('012' => "could not create table. " . self::$db->error )); 
      }
    }
    
    //create touchstone 'database user' and grant permissions
    self::$db->query("CREATE USER  '" . self::$cfg_db_username . "'@'localhost'");
    if (self::$db->errno != 0) {
      self::displayWarning(array('013'=>'Database user ' . self::$cfg_db_username . ' could not be created'));
    } 
    
    self::$db->query("GRANT SELECT,INSERT,UPDATE,DELETE ON " . self::$cfg_db_name . ".* TO '" . self::$cfg_db_name . "'@'localhost' IDENTIFIED BY '" . self::$cfg_db_password . "'");
    echo self::$db->error;
    if (self::$db->errno != 0) {
      self::displayWarning(array('013'=>'Database user ' . self::$cfg_db_username . ' could not set permissions'));
    }  
    
    //create touchstone sysadmin user 
    UserUtils::createUser(  $_POST['SysAdmin_username'], 
                            $_POST['SysAdmin_password'], 
                            $_POST['SysAdmin_title'],
                            $_POST['SysAdmin_first'],
                            $_POST['SysAdmin_last'], 
                            $_POST['SysAdmin_email'], 
                            'University Lecturer', 
                            '',
                            '', 
                            '1', 
                            'Staff,SysAdmin', 
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
                              '', 
                              '1', 
                              'Student', 
                               self::$db
                            );
     }
     
     //add traing school
     SchoolUtils::addSchool(  'Administrative and Support Units',
                              'Training',
                               self::$db
                            );
     
     //create special modules
     ModuleUtils::addModules(  'TRAIN', 
                                'Training Module', 
                                1, 
                                'Training', 
                                '',
                                '', 
                                false, 
                                false, 
                                false, 
                                true,
                                self::$db
                             );
    
    ModuleUtils::addModules(   'SYSTEM', 
                                'Online Help', 
                                1, 
                                'Training', 
                                '',
                                '', 
                                true, 
                                true, 
                                true, 
                                true,
                                self::$db
                             );
                          
    //FLUSH PRIVILEGES
    self::$db->query("FLUSH PRIVILEGES");
    if (self::$db->errno != 0) {
      self::displatWarning(array('014'=>'Unable to FLUSH PRIVILEGES'));
    }  
  }
  
  /**
  * Check that we do not have a config file and that we can write one 
  *
  */
  static function configFile() {
    $touchstone_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    if (file_exists($touchstone_path . '/config/config.inc')) {
      $errors['90'] = "<p>TouchStone has already been installed! remove/rename $touchstone_path/config/config.inc to run set up again.</p>";
      $errors['90'] .= "<p>or go to the <a href=\"/touchstone\">staff interfaces</a></p>";
    }
  }
  
  /**
  * Check for installed software versions PHP, Apache 
  *
  */
  static function checkDirPermissions() {
    self::$touchstone_path = str_ireplace('/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
    $errors = array();
    //tmp
    if (!is_writable('/tmp')) {
      $errors['100'] = "TouchStone requires /tmp to exist and be writeable to the webserver";
    }
    //media
    if (!is_writable(self::$touchstone_path . '/media')) {
      $errors['102'] = "TouchStone requires $touchstone_path/media to exist and be writeable to the webserver";
    }    
    //qti imports
    if (!is_writable(self::$touchstone_path . '/qti/imports')) {
      $errors['103'] = "TouchStone requires $touchstone_path/qti/imports to exist and be writeable to the webserver";
    }
    //qti exports
    if (!is_writable(self::$touchstone_path . '/qti/exports')) {
      $errors['104'] = "TouchStone requires $touchstone_path/qti/exports to exist and be writeable to the webserver";
    }
    //temp
    if (!is_writable(self::$touchstone_path . '/temp')) {
      $errors['105'] = "TouchStone requires $touchstone_path/temp to exist and be writeable to the webserver";
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
    $errors = array();
    //apache
    $apache = explode('/',$_SERVER['SERVER_SOFTWARE']);
    $apache_min_ver = '2.0';
    if ( isset($apache[0]) and isset($apache[1]) ) {
      if ($apache[0] != 'Apache') {
        $errors['200'] = "TouchStone requires Apache version $apache_min_ver" . $apache[1];
      }
      $ver = explode(' ',$apache[1]);
      if (isset($ver[0]) and $ver[0] < $apache_min_ver) {
        $errors['201'] = "TouchStone requires Apache version $apache_min_ver or above you have " . $ver[0];
      }
    }
    
    //php
    $php_min_ver = '5.0';
    if (phpversion() < $php_min_ver) {
      $errors['202'] = "TouchStone requires PHP version $php_min_ver or above";
    }
    $phpModules = get_loaded_extensions();
    if ( !in_array('mysqli',$phpModules) ) {
      $errors['203'] = "TouchStone requires the PHP mysqli moduel to function please install or activate it.";
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
    if ($_SERVER['SERVER_PORT'] != 443 and $_SERVER['SERVER_PORT'] != 8080) {
      self::displayError(array(100=>'TouchStone can only be accessed through https. Plese update you apache config.'));
      return false;
    }
    return true;
  }
  
  /**
  * Display errors with a nice message 
  *
  */
  static function displayError($error = '') {
    echo "<div class=\"error\">\n";
    if (is_array($error)) {
      foreach($error as $errCode => $message) {
        echo "\t<div>Error $errCode:: $message</div>\n";
      }
    }
    echo "</div>\n";
    self::displayFooter();
    exit;
  }
  
  /**
  * Display errors with a nice message 
  *
  */
  static function displayWarning($warning = '') {
    echo "<div class=\"error\">\n";
    if (is_array($warning)) {
      foreach($warning as $code => $message) {
        echo "\t<div>Warning $code:: $message</div>\n";
      }
    }
    echo "</div>\n";
  }
  
  /**
  * Display header 
  *
  */
  static function displayHeader() {
    ?>
    <html>
    <head>
      <title>TouchStone Install script</title>
      <style type="text/css">
        label { width: 10em; float: left; }
        label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }
        p { clear: both; }
        .submit { margin-left: 12em; }
        em { font-weight: bold; padding-right: 1em; vertical-align: top; }
      </style>
      <script language="text/javascript" type="text/javascript" src="../javascript/jquery-1.6.1.min.js"></script>
      <script language="text/javascript" type="text/javascript" src="../javascript/jquery.validate.min.js"></script>
      <script>
        $(document).ready(function(){
          $("#installForm").validate();
        });
      </script>
    </head>
    <body>
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
  
    $config = <<<CONFIG
<?php
/**
* 
* config file
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

\$ts_version = '{ts_version}';
define('TOUCHSTONE', 'true');
define('DIR_SEPARATOR', '/');
\$cfg_web_root = (substr(\$_SERVER['DOCUMENT_ROOT'], -1) == DIR_SEPARATOR) ? \$_SERVER['DOCUMENT_ROOT'] : \$_SERVER['DOCUMENT_ROOT'] . DIR_SEPARATOR;
\$protocol = 'https://';

\$news_msg = '';

// Local database
  \$cfg_db_username = '{cfg_db_username}';
  \$cfg_db_passwd   = '{cfg_db_passwd}';
  \$cfg_db_database = '{cfg_db_database}';
  \$cfg_db_host 	   = '{cfg_db_host}';

// SMS Imports
  \$cfg_sms_sources = array('&lt;No lookup&gt;'=>'');
  
//LDAP
  \$cfg_ldap_server        = '{cfg_ldap_server}';
  \$cfg_ldap_search_dn     = '{cfg_ldap_search_dn}';
  \$cfg_ldap_bind_rdn      = '{cfg_ldap_bind_rdn}';
  \$cfg_ldap_bind_password = '{cfg_ldap_bind_password}';
  \$cfg_use_ldap           = {cfg_use_ldap};

//Editor
  \$cfg_editor_name = 'tinymce';
  \$cfg_editor_javascript = "<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n<script language=\"JavaScript\" src=\"/touchstone/tools/tinymce/jscripts/tiny_mce/tiny_config.js\"></script>\n";

//Server specific configuration basaed on hostname.
switch (strtolower(\$_SERVER['HTTP_HOST'])) {
  case '{SERVER_NAME}':
    \$cfg_install_type = '';
    break;
}

//Warnings
  \$cfg_hour_warning = 10;       // Warning for summative exams
  error_reporting(-1);          // PHP error reporting 
  
//Assistance
  \$support_email = '{support_email}';
  \$emergency_support_numbers = {emergency_support_numbers};

//Global DEBUG OUTPUT
  if (isset(\$_SERVER['PHP_AUTH_USER']) AND \$_SERVER['PHP_AUTH_USER'] == '{SysAdmin_username}') {
    require_once \$_SERVER['DOCUMENT_ROOT'] . 'touchstone/include/debug.inc';
  } else {
    \$dbclass = 'mysqli';
  }
  ?>
CONFIG;

    $config = str_replace('{ts_version}',self::$ts_version,$config);
    $config = str_replace('{SysAdmin_username}','');
    $config = str_replace('{cfg_db_host}',self::$cfg_db_host,$config);
    $config = str_replace('{cfg_db_port}',self::$cfg_db_port,$config);
    $config = str_replace('{cfg_db_database}',self::$cfg_db_name,$config);
    $config = str_replace('{cfg_db_username}',self::$cfg_db_username,$config);
    $config = str_replace('{cfg_db_passwd}',self::$cfg_db_password,$config);
    
    $config = str_replace('{support_email}',self::$support_email,$config);
    $config = str_replace('{emergency_support_numbers}',self::$emergency_support_numbers = '',$config);
    
    $config = str_replace('{cfg_ldap_server}',self::$cfg_ldap_server,$config);
    $config = str_replace('{cfg_ldap_search_dn}',self::$cfg_ldap_search_dn,$config);
    $config = str_replace('{cfg_ldap_bind_rdn}',self::$cfg_ldap_bind_rdn,$config);
    $config = str_replace('{cfg_ldap_bind_password}',self::$cfg_ldap_bind_password,$config);
    $config = str_replace('{cfg_use_ldap}',self::$cfg_use_ldap,$config);
    
    $config = str_replace('{SERVER_NAME}',$_SERVER['HTTP_HOST'],$config);
    
    if (file_exists(self::$touchstone_path . '/config/config.inc')) {
      rename(self::$touchstone_path . '/config/config.inc', self::$touchstone_path . '/config/config.inc.old');
    }
    
    if (file_put_contents(self::$touchstone_path . '/config/config.inc', $config) === false) {
      self::displayError(array(300=>'Could not write config file !'));
    }
  }    

}

class touchStoneTables {

  public static $tableList = array();
  
  function __construct() {
   $this->tableList['degrees'] = <<<QUERY
          CREATE TABLE `degrees` (
          `id` int(11) NOT NULL auto_increment,
          `school` varchar(255) default NULL,
          `degree` varchar(255) default NULL,
          `description` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `degree` (`degree`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['ebel'] = <<<QUERY
          CREATE TABLE `ebel` (
            `id` int(11) NOT NULL auto_increment,
            `setterID` mediumint(8) unsigned default NULL,
            `date_set` datetime default NULL,
            `category` char(3) default NULL,
            `percentage` float default NULL,
            PRIMARY KEY  (`id`),
            KEY `SETTER_AND_DATE` (`setterID`,`date_set`)
          ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['faculty'] = <<<QUERY
          CREATE TABLE `faculty` (
            `id` int(11) NOT NULL auto_increment,
            `name` varchar(80) default NULL,
            PRIMARY KEY  (`id`)
          ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['feedback_release'] = <<<QUERY
         CREATE TABLE `feedback_release` (
          `idfeedback_release` int(11) NOT NULL auto_increment,
          `paper_id` smallint(5) default NULL,
          `date` datetime NOT NULL,
          PRIMARY KEY  (`idfeedback_release`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['folders'] = <<<QUERY
        CREATE TABLE `folders` (
          `id` int(4) NOT NULL auto_increment,
          `ownerID` mediumint(8) unsigned default NULL,
          `name` text,
          `team_name` varchar(255) default NULL,
          `created` datetime default NULL,
          `color` enum('yellow','red','green','blue') default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['help_log'] = <<<QUERY
        CREATE TABLE `help_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `accessed` datetime default NULL,
          `pageID` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['help_tutorial_log'] = <<<QUERY
        CREATE TABLE `help_tutorial_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` mediumint(8) unsigned default NULL,
          `accessed` datetime default NULL,
          `tutorial` varchar(255) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['keywords_question'] = <<<QUERY
        CREATE TABLE `keywords_question` (
          `q_id` int(11) default NULL,
          `keywordID` int(11) default NULL,
          KEY `q_id` (`q_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['keywords_user'] = <<<QUERY
        CREATE TABLE `keywords_user` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `keyword` char(255) default NULL,
          `keyword_type` enum('personal','team') default NULL,
          PRIMARY KEY  (`id`),
          KEY `username` (`userID`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['labs'] = <<<QUERY
        CREATE TABLE `labs` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(255) default NULL,
          `campus` enum('University Park','Jubilee','King''s Meadow','Derby','Malaysia','Ningbo','Sutton Bonington','Other') default NULL,
          `building` varchar(255) default NULL,
          `room_no` varchar(255) default NULL,
          `timetabling` text,
          `it_support` text,
          `plagarism` text,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
          ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=5140579 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
          `year` enum('year1','year2','year3','year4','year5','year6','cp1','cp2','cp3','f1','graduate') default NULL,
          `examinerID` mediumint(8) unsigned default NULL,
          `osce_type` enum('electronic','paper') default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0  DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['modules'] = <<<QUERY
        CREATE TABLE `modules` (
          `id` int(11) NOT NULL auto_increment,
          `moduleid` char(25) default NULL,
          `fullname` text,
          `active` tinyint(4) default NULL,
          `school` varchar(255) default NULL,
          `vle_api` varchar(255) default NULL,
          `checklist` varchar(255) default NULL,
          `sms` varchar(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `guideid` (`moduleid`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
QUERY;

    $this->tableList['objectives'] = <<<QUERY
        CREATE TABLE `objectives` (
          `obj_id` int(11) NOT NULL,
          `objective` text NOT NULL,
          `moduleID` char(25) NOT NULL,
          `identifier` bigint(20) unsigned NOT NULL,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          `sequence` int(11) default NULL,
          PRIMARY KEY  (`obj_id`,`moduleID`,`calendar_year`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
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
          `marks` float default NULL,
          PRIMARY KEY  (`id_num`),
          KEY `o_id` (`o_id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['papers'] = <<<QUERY
        CREATE TABLE `papers` (
          `p_id` int(4) NOT NULL auto_increment,
          `paper` smallint(5) unsigned NOT NULL default '0',
          `question` smallint(4) unsigned NOT NULL default '0',
          `screen` tinyint(2) unsigned NOT NULL default '0',
          `display_pos` smallint(5) unsigned default NULL,
          PRIMARY KEY  (`p_id`),
          KEY `paper` (`paper`),
          KEY `question_idx` (`question`),
          KEY `screen` (`screen`),
          KEY `paper_2` (`paper`,`display_pos`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
QUERY;

    $this->tableList['properties'] = <<<QUERY
        CREATE TABLE `properties` (
          `property_id` smallint(5) unsigned NOT NULL auto_increment,
          `paper_title` varchar(255) default NULL,
          `start_date` datetime default NULL,
          `end_date` datetime default NULL,
          `timezone` varchar(255) default NULL,
          `paper_type` enum('0','1','2','3','4','5') default NULL,
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
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          `internal_reviewers` text,
          `external_review_deadline` date default NULL,
          `internal_review_deadline` date default NULL,
          `sound_demo` enum('0','1') default NULL,
          `latex_needed` tinyint(4) default '0',
          `password` char(20) default NULL,
          PRIMARY KEY  (`property_id`),
          KEY `paper_title` (`paper_title`),
          KEY `paper_owner` (`paper_ownerID`),
          KEY `question_type` (`paper_type`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['questions'] = <<<QUERY
        CREATE TABLE `questions` (
          `q_id` int(4) NOT NULL auto_increment,
          `q_type` enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','timedate','info','extmatch','random','sct','keyword_based') default NULL,
          `theme` text,
          `scenario` text,
          `leadin` text,
          `correct_fback` text,
          `incorrect_fback` text,
          `score_method` text,
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
          PRIMARY KEY  (`q_id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
QUERY;

    $this->tableList['recent_papers'] = <<<QUERY
        CREATE TABLE `recent_papers` (
          `userID` mediumint(8) unsigned NOT NULL default '0',
          `paperID` mediumint(9) NOT NULL default '0',
          `accessed` datetime default NULL,
          PRIMARY KEY  (`userID`,`paperID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['relationships'] = <<<QUERY
        CREATE TABLE `relationships` (
          `rel_id` int(11) NOT NULL auto_increment,
          `module_id` char(15) NOT NULL,
          `paper_id` int(11) NOT NULL,
          `question_id` int(11) NOT NULL,
          `obj_id` int(11) NOT NULL,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          PRIMARY KEY  (`rel_id`),
          KEY `module_id_idx` (`module_id`),
          KEY `paper_id_idx` (`paper_id`),
          KEY `calendar_year` (`calendar_year`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['schools'] = <<<QUERY
        CREATE TABLE `schools` (
          `id` int(11) NOT NULL auto_increment,
          `faculty` varchar(80) default NULL,
          `school` char(255) default NULL,
          PRIMARY KEY  (`id`),
          KEY `faculty` (`faculty`(1))
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['sessions'] = <<<QUERY
        CREATE TABLE `sessions` (
          `sess_id` int(11) NOT NULL auto_increment,
          `identifier` bigint(20) unsigned NOT NULL,
          `moduleID` char(25) NOT NULL,
          `title` text NOT NULL,
          `source_url` text,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          `occurrence` datetime default NULL,
          PRIMARY KEY  (`identifier`,`moduleID`,`calendar_year`),
          KEY `sess_id` (`sess_id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['sid'] = <<<QUERY
        CREATE TABLE `sid` (
          `student_id` char(15) default NULL,
          `userID` mediumint(8) unsigned NOT NULL default '0',
          PRIMARY KEY  (`userID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
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
          `import_type` enum('manual','SATURN UK','SATURN Malaysia','SATURN China','ARC') default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['student_modules'] = <<<QUERY
        CREATE TABLE `student_modules` (
          `id` int(11) NOT NULL auto_increment,
          `userID` mediumint(8) unsigned default NULL,
          `moduleid` char(15) NOT NULL,
          `calendar_year` enum('2008/09','2009/10','2010/11','2011/12','2012/13','2013/14','2014/15','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20') NOT NULL,
          `attempt` tinyint(4) default NULL,
          `auto_update` tinyint(4) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['textbox_remark'] = <<<QUERY
        CREATE TABLE `textbox_remark` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` int(11) default NULL,
          `userID` mediumint(8) unsigned default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1
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
        ) ENGINE=MyISAM AUTO_INCREMENT=159021 DEFAULT CHARSET=latin1
QUERY;

    $this->tableList['users'] = <<<QUERY
        CREATE TABLE `users` (
          `password` char(40) default NULL,
          `grade` char(30) default NULL,
          `surname` char(35) default NULL,
          `initials` char(10) default NULL,
          `title` enum('Dr','Miss','Mr','Mrs','Ms','Professor') default NULL,
          `username` char(15) default NULL,
          `email` char(65) default NULL,
          `roles` char(40) default NULL,
          `id` smallint(6) NOT NULL auto_increment,
          `faculty` varchar(80) default NULL,
          `first_names` char(60) default NULL,
          `gender` enum('Male','Female') default NULL,
          `last_login` datetime default NULL,
          `special_needs` tinyint(4) default '0',
          `yearofstudy` tinyint(4) default NULL,
          PRIMARY KEY  (`id`),
          KEY `username_index` (`username`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 PACK_KEYS=1
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