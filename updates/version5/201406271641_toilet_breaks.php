<?php
if (!$updater_utils->does_table_exist('toilet_breaks')) {
  $sql = <<< QUERY
CREATE TABLE `toilet_breaks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `paperID` mediumint(8) unsigned NOT NULL,
  `break_taken` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `paperID` (`paperID`)
) AUTO_INCREMENT=0;
QUERY;
  $updater_utils->execute_query($sql, true);

	// Add in permissions for Staff users.
  $sql = "GRANT SELECT ON " . $cfg_db_database . ".toilet_breaks TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
  $updater_utils->execute_query($sql, true);	// Add in permissions for staff users.
  
	// Add in permissions for Invigilator users.
  $sql = "GRANT SELECT, INSERT, DELETE ON " . $cfg_db_database . ".toilet_breaks TO '" . $cfg_db_inv_username . "'@'" . $cfg_web_host . "'";
  $updater_utils->execute_query($sql, true);
}
?>