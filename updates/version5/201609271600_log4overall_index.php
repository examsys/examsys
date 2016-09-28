<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1965_log4index')) {

    $altersql = "ALTER TABLE `log4` ADD INDEX `log4_overallID` (`log4_overallID`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1965_log4index');
}
