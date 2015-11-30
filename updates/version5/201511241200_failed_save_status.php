<?php

if (!$updater_utils->has_updated('rogo1586_failsave')) {
    // Truncate table.
    $truncatesql = "TRUNCATE TABLE save_fail_log";
    $updater_utils->execute_query($truncatesql, true);
    // Add new status column.
    $altersql = "ALTER TABLE save_fail_log
        ADD COLUMN `status` VARCHAR(50) NULL DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $updater_utils->record_update('rogo1586_failsave');
}
