<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

$string['systemupdate'] = 'System Update';
$string['actionrequired'] = 'Action Required';
$string['readonly'] = "Don't forget to make the <strong>/config/config.inc.php</strong> readonly! (chmod 444)";
$string['finished'] = 'Finished!';
$string['couldnotwrite'] = 'Error: could not write config file!';
$string['msg1'] = 'This script updates the database structures to match the new %s code. No harm will come if this script is run multiple times as it checks the current database structure before applying any changes.';
$string['msg2'] = 'The update script needs the username and password of a MySQL admin user to update the database, users and tables. This username is not saved to the server and is only used by this update script.';
$string['databaseadminuser'] = 'Database Admin User';
$string['dbusername'] = 'DB Username';
$string['dbpassword'] = 'DB Password';
$string['onlinehelpsystems'] = 'Online Help Systems';
$string['updatestaffhelp'] = 'Update Staff Help';
$string['updatestudenthelp'] = 'Update Student Help';
$string['startupdate'] = 'Start Update';
$string['warning1'] = 'This update requires that /config/config.inc.php is writeable.';
$string['warning2'] = 'Please chown the file to the webserver and chomod it 644';
$string['warning3'] = 'This update requires that the /config directory is writeable.';
$string['warning4'] = 'Please chown the file to the webserver and chomod it 744';
$string['updatefromversion'] = 'Update from version';
$string['home'] = 'Home';
$string['startingupdate'] = 'Starting Update';
?>