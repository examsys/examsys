<?php

if (!$updater_utils->does_table_exist('marking_override')) {
  $sql = <<< QUERY
CREATE TABLE `marking_override` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `log_id` INT(11) UNSIGNED NOT NULL ,
  `log_type` TINYINT(4) UNSIGNED NOT NULL ,
  `user_id` INT(10) UNSIGNED NOT NULL ,
  `q_id` INT(4) UNSIGNED NOT NULL ,
  `paper_id` MEDIUMINT(8) UNSIGNED NOT NULL ,
  `marker_id` INT(10) UNSIGNED NOT NULL ,
  `date_marked` DATETIME NOT NULL ,
  `new_mark_type` ENUM('correct', 'partial', 'incorrect') NOT NULL ,
  `reason` VARCHAR(255) NULL ,
  PRIMARY KEY (`id`),
  UNIQUE KEY `log_id` (`log_id`, `log_type`)
  ) DEFAULT CHARSET={$cfg_db_charset};
QUERY;

  $updater_utils->execute_query($sql, true);

  $sql = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".marking_override TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
  $updater_utils->execute_query($sql, true);
}

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
