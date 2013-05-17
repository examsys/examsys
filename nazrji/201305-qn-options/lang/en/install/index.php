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

$string['company'] = 'Company';
$string['companyname'] = 'Company Name';
$string['databaseadminuser'] = 'Database Admin User';
$string['server'] = 'Server';
$string['tempdirectory'] = 'Temp Directory';
$string['needusername'] = 'The installer need the username and password of a MySQL admin user to create the database and required tables. This username is not saved to the server and is only used by this install script.';
$string['dbusername'] = 'DB Username';
$string['dbpassword'] = 'DB Password';
$string['databasesetup'] = 'Database Setup';
$string['databasehost'] = 'Database host';
$string['databaseport'] = 'Database port';
$string['databasename'] = 'Database Name';
$string['databasecharset'] = 'Database Character Set';
$string['databaseuser'] = 'Rogō Database user';
$string['pagecharset'] = 'Page Character Set';
$string['rdbusername'] = 'Username';
$string['rdbpassword'] = 'Password';
$string['timedateformats'] = 'Time/Date formats';
$string['date'] = 'Date (%s)';
$string['datetime'] = 'Date/Time (%s)';
$string['longdatephp'] = 'Long date (%s)';
$string['shortdatephp'] = 'Short date (%s)';
$string['longtimephp'] = 'Long time (%s)';
$string['shorttimephp'] = 'Short time (%s)';
$string['currenttimezone'] = 'Current Timezone';
$string['authentication'] = 'Authentication';

$string['allowlti'] = 'Allow via LTI';
$string['allowintdb'] = 'Internal database';
$string['allowguest'] = 'Guest log in (for summative exams)';
$string['allowimpersonation'] = 'User impersonation (SysAdmin only)';
$string['useldap'] = 'Use LDAP';

$string['lookup'] = 'Lookup Data Sources';
$string['allowlookupXML'] = 'use XML (Will need customising in config file)';
$string['rdbbasename'] = 'Basepart of username';

$string['ldapserver'] = 'LDAP server';
$string['searchdn'] = 'Search dn';
$string['bindusername'] = 'bind username';
$string['bindpassword'] = 'bind password';
$string['userprefix'] = 'Username prefix';
$string['userprefixtip'] = 'Prefix for username in LDAP search, e.g. &quot;sAMAccountName=&quot;';
$string['sysadminuser'] = 'Rogō SysAdmin User';
$string['initialsysadmin'] = 'An initial SysAdmin user account is required to log in and create further normal staff accounts and generally administer the system.';
$string['title'] = 'Title';
$string['title_types'] = "Mr,Mrs,Miss,Ms,Dr,Professor";
$string['firstname'] = 'First Name';
$string['surname'] = 'Surname';
$string['emailaddress'] = 'Email Address';
$string['username'] = 'Username';
$string['password'] = 'Password';
$string['helpdb'] = 'Rogō Help Database';
$string['loadhelp'] = 'Load Help';
$string['supportemaila'] = 'Support Email';
$string['supportemail'] = 'Support Email';
$string['supportnumbers'] = 'Emergency Support Numbers';
$string['name'] = 'Name';
$string['number'] = 'Number';
$string['install'] = 'Install Rogō';
$string['installed'] = 'Rogō is now successfully installed.';
$string['deleteinstall'] = 'For security reasons please delete the install directory.';
$string['staffhomepage'] = 'Go to staff homepage';

$string['logwarning1'] = 'could not load staff_hlep.sql, could not install staff help';
$string['logwarning2'] = 'cannot find staff_hlep.sql, could not install staff help';
$string['logwarning3'] = 'could not load student_help.sql, could not install student help';
$string['logwarning4'] = 'cannot find student_help.sql, could not install student help';
$string['displayerror1'] = 'The database name \'%s\' is in use please use a different one';
$string['displayerror2'] = 'The database \'%s\' could not be created please check the admin users permissions';
$string['displayerror3'] = 'could not create table.';
$string['wdatabaseuser'] = 'Database user ';
$string['wnotcreated'] = ' could not be created';
$string['wnotpermission'] = ' could not set permissions';
$string['logwarning20'] = 'Unable to FLUSH PRIVILEGES';
$string['errors1'] = 'Rogo has already been installed! remove/rename %s to run set up again.';
$string['errors2'] = 'or go to the %s staff interfaces';
$string['errors3'] = 'Rogo requires %s to exist and be writeable to the webserver';
$string['errors4'] = 'Rogo requires %s/media to exist and be writeable to the webserver';
$string['errors5'] = 'Rogo requires %s/qti/imports to exist and be writeable to the webserver';
$string['errors6'] = 'Rogo requires %s/qti/exports to exist and be writeable to the webserver';
$string['errors7'] = 'Rogo requires %s/temp to exist and be writeable to the webserver';
$string['errors8'] = 'Rogo requires Apache version $apache_min_ver';
$string['errors9'] = 'Rogo requires Apache version $apache_min_ver or above you have';
$string['errors10'] = 'Rogo requires PHP version $php_min_ver or above';
$string['errors11'] = 'Rogo requires the PHP mysqli module to function please install or activate it.';
$string['errors12'] = 'Rogō can only be accessed through https. Please update you apache config.';
$string['errors13'] = 'Error';
$string['errors14'] = 'The following warnings were generated';
$string['errors15'] = 'Warning';
$string['errors16'] = 'Rogo requires ability to write its config file %s/include/load_config.php, One way to fix this is you can temporarily allow write access to %s/config and change permissions once update has run.';
$string['installscript'] = 'Rogō Install script';
$string['systeminstallation'] = 'System Installation';
?>
