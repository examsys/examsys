<?php

if ($updater_utils->check_version("6.2.0")) {
    if (!$updater_utils->has_updated('rogo1662_plugins')) {
        // New plugins table.
        $createsql = "CREATE TABLE `plugins` (
            `component` VARCHAR(50) NOT NULL,
            `version` VARCHAR(50) NOT NULL,
            `type`  VARCHAR(50) NOT NULL,
            `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`component`))";
        $updater_utils->execute_query($createsql, true);
        $updater_utils->record_update('rogo1662_plugins');
    }
}

