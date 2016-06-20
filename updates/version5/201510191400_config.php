<?php

if (!$updater_utils->does_table_exist('config')) {
    // Create a config table.
    $createsql = "CREATE TABLE `config` (
                    `component` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'core',
                    `setting` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                    `value` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`component`,`setting`)
                  ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Rogō configuration variables'";

    $updater_utils->execute_query($createsql, true);

    // Select Permissions for everyone.
    $altersql = "ALTER TABLE `modules` CHANGE COLUMN `moduleid` `moduleid` CHAR(255) NULL DEFAULT NULL";

    $updater_utils->execute_query($altersql, true);
}
