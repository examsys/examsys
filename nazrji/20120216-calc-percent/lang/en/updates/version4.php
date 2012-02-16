<?php
$string['systemupdate'] = 'System Update';
$string['actionrequired'] = 'Action Required';
$string['readonly'] = "Don't forget to make the <strong>/config/config.inc.php</strong> readonly! (chmod 444)";
$string['finished'] = 'Finished!';
$string['couldnotwrite'] = 'Error: could not write config file!';
$string['msg1'] = 'This script updates the database structures to match the new %s code. No harm will come if this script is run multiple times as it checks the current database structure before applying any changes.';
$string['msg2'] = 'The update script needs the username and password of a MySQL admin user to update the database, users and tables. This username is not saved to the server and is only used by this update script.';
$string['databaseadminuser'] = 'Database Admin User';
$string['dbusername'] = 'DB Username:';
$string['dbpassword'] = 'DB Password:';
$string['startupdate'] = 'Start Update';
$string['warning1'] = 'This update requires that /config/config.inc.php is writeable.';
$string['warning2'] = 'Please chown the file to the webserver and chomod it 644';
$string['updatefromversion'] = 'Update from version';
?>