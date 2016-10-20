<?php

if ($updater_utils->check_version("6.2.0")) {
    if (!$updater_utils->has_updated('rogo1829_externalids')) {
        // courses.
        $altersql = "ALTER TABLE courses ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `courses` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `courses` ADD COLUMN `externalsys` VARCHAR(255) NULL DEFAULT NULL AFTER `externalid`";
        $updater_utils->execute_query($altersql, true);
        // faculty.
        $altersql = "ALTER TABLE faculty ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE faculty ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD COLUMN `externalsys` VARCHAR(255) NULL DEFAULT NULL AFTER `externalid`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // schools.
        $altersql = "ALTER TABLE schools ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD COLUMN `externalsys` VARCHAR(255) NULL DEFAULT NULL AFTER `externalid`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE schools ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // modules.
        $altersql = "ALTER TABLE modules ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `modules` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        
        // Drop perms desc column, we need to drop the description before adding the new permissions.
        $altersql = "ALTER TABLE permissions DROP COLUMN description";
        $updater_utils->execute_query($altersql, true);
        // New API perms.
        $insertsql = "INSERT INTO permissions (action) VALUES "
            . "('coursemanagement/update'), "
            . "('schoolmanagement/update'), "
            . "('facultymanagement/update'), "
            . "('modulemanagement/update'), "
            . "('usermanagement/update'), "
            . "('assessmentmanagement/update')";
        $updater_utils->execute_query($insertsql, true);
        // New type column in config type.
        $altersql = "ALTER TABLE `config` ADD COLUMN `type` VARCHAR(10) NULL AFTER `value`";
        $updater_utils->execute_query($altersql, true);
        if (!$updater_utils->has_updated('rogo1559_wsconfig')) {
            // Save json encoded list of timezones.
            global $timezone_array;
            $encoded_timezones = $timezone_array;
            $encoded_cohorts = array('<whole cohort>', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300',
            '301-400', '401-500');
            $configObject = Config::get_instance();
            $configObject->set_db_object($mysqli);
            $configObject->set_setting('timezones', $encoded_timezones, Config::JSON );
            $configObject->set_setting('cohort_sizes', $encoded_cohorts, Config::JSON );
            $configObject->set_setting('max_duration', 779, Config::INTEGER);
            $configObject->set_setting('max_sittings', 6, Config::INTEGER);
            $updater_utils->record_update('rogo1559_wsconfig');
        } else {
            // Update config table with type.
            $altersql = "UPDATE `config` SET type = '" . Config::JSON . "' WHERE setting = 'timezones' AND component = 'core'";
            $updater_utils->execute_query($altersql, true);
            $altersql = "UPDATE `config` SET type = '" . Config::JSON . "' WHERE setting = 'cohort_sizes' AND component = 'core'";
            $updater_utils->execute_query($altersql, true);
            $altersql = "UPDATE `config` SET type = '" . Config::INTEGER . "' WHERE setting = 'max_duration' AND component = 'core'";
            $updater_utils->execute_query($altersql, true);
            $altersql = "UPDATE `config` SET type = '" . Config::INTEGER . "' WHERE setting = 'max_sittings' AND component = 'core'";
            $updater_utils->execute_query($altersql, true);
        }
        $altersql = "UPDATE `config` SET type = null WHERE component = 'plugin_ims'";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1829_externalids');
    }
}