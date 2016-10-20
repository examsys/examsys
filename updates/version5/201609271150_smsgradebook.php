<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo_smsgradebook')) {

    $altersql = "ALTER TABLE `modules_student` ADD INDEX `idx_mod` (`idMod`);";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo_smsgradebook');
}
