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
$string['webhost'] = 'WebServer host';
$string['datadirectory'] = 'Rogo data directory';
$string['databaseport'] = 'Database port';
$string['databasename'] = 'Database Name';
$string['databaseuser'] = 'Rog&#333; Database user';
$string['rdbusername'] = 'Username';
$string['rdbpassword'] = 'Password';
$string['timedateformats'] = 'Time/Date formats';
$string['date'] = 'Date (%s)';
$string['longdate'] = 'Long Date (%s)';
$string['shortdatetime'] = 'Short Date/Time (%s)';
$string['longdatetime'] = 'Long Date/Time (%s)';
$string['longdatephp'] = 'Long date (%s)';
$string['shortdatephp'] = 'Short date (%s)';
$string['longtimephp'] = 'Long time (%s)';
$string['shorttimephp'] = 'Short time (%s)';
$string['searchleadinlength'] = 'Search results lead-in length';
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
$string['title_types'] = "Mx,Mr,Mrs,Miss,Ms,Dr,Professor";
$string['firstname'] = 'First Name';
$string['surname'] = 'Surname';
$string['emailaddress'] = 'Email Address';
$string['username'] = 'Username';
$string['password'] = 'Password';
$string['helpdb'] = 'Rog&#333; Help Database';
$string['loadhelp'] = 'Load Help';
$string['translationpack'] = 'Rog&#333; Translations';
$string['loadtranslations'] = 'Download All Translation Packs';
$string['manualtranslations'] = 'Alternatively you can download individual translation packs <a target="_blank" href = "%s">here</a>.';
$string['supportemaila'] = 'Support Email';
$string['supportemail'] = 'Support Email';
$string['supportnumbers'] = 'Emergency Support Numbers';
$string['name'] = 'Name';
$string['number'] = 'Number';
$string['install'] = 'Install Rog&#333;';
$string['installed'] = 'Rog&#333; is now successfully installed.';
$string['deleteinstall'] = 'For security reasons please delete the install directory.';
$string['config'] = 'Configure settings';
$string['invalidsetting'] = 'Setting %s either not provided or invalid!';

$string['logwarning1'] = 'could not load staff_help.sql, could not install staff help';
$string['logwarning2'] = 'cannot find staff_help.sql, could not install staff help';
$string['logwarning3'] = 'could not load student_help.sql, could not install student help';
$string['logwarning4'] = 'cannot find student_help.sql, could not install student help';
$string['displayerror1'] = 'The database name \'%s\' is in use please use a different one';
$string['displayerror2'] = 'The database \'%s\' could not be created please check the admin users permissions';
$string['displayerror3'] = 'could not create table.';
$string['wdatabaseuser'] = 'Database user ';
$string['wnotcreated'] = ' could not be created';
$string['wnotpermission'] = ' could not set permissions';
$string['logwarning20'] = 'Unable to FLUSH PRIVILEGES';
$string['errors1'] = 'Rog&#333; has already been installed!<ul>Please contact a system adminstrator. You should go back to your <li><a href="../index.php"> Homepage</a></li></ul>';
$string['errors3'] = 'Rog&#333; requires %s to exist and be writeable to the webserver';
$string['errors7'] = 'Rog&#333; requires %s/temp to exist and be writeable to the webserver';
$string['errors10'] = 'Rog&#333; requires PHP version %s or above you have %s';
$string['errors11'] = 'Rog&#333; requires the PHP %s module to function please install or activate it.';
$string['errors12'] = 'Rog&#333; can only be accessed through https. Please update you apache config.';
$string['errors13'] = 'Error';
$string['errors14'] = 'The following warnings were generated';
$string['errors15'] = 'Warning';
$string['errors16'] = 'Rog&#333; requires ability to write its config file %s/config/config.inc.php. One way to fix this is you can temporarily allow write access to %s/config and change permissions once update has run.';
$string['errors17'] = 'Rog&#333; requires MYSQL version %s or above you have %s';
$string['errors18'] = 'The Rog&#333; data directory requires %s path to exist';
$string['errors19'] = 'The Rog&#333; data directory requires %s must be writable';
$string['installscript'] = 'Rog&#333; Install script';
$string['systeminstallation'] = 'System Installation';
$string['labsecuritytype'] = "Summative Exam Lab Security";
$string['IP'] = "IP address";
$string['hostname'] = "Machine hostname";

$string['databaseengine'] = "Main Database Engine";
$string['databasehelpengine'] = "Help Database Engine";
$string['databaseenginetooltip'] = "InnoDB reccommended";
$string['helpdatabaseenginetooltip'] = "MyISAM recommended if MySQL version < 5.5";
?>
