<?php

if ($updater_utils->check_version("6.3.0") and !$updater_utils->has_updated('rogo1983_modules_staff')) {

    // idMod Make reference column same type as referred column.
    $altersql = "ALTER TABLE `modules_staff`
        ALTER `idMod` DROP DEFAULT";
    $updater_utils->execute_query($altersql, true);

    $altersql = "ALTER TABLE `modules_staff`
        CHANGE COLUMN `idMod` `idMod` INT(11) NOT NULL AFTER `groupID`";
    $updater_utils->execute_query($altersql, true);

    // idMod Add fk.
    $altersql = "ALTER TABLE `modules_staff`
        ADD CONSTRAINT `modules_staff_fk1` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $updater_utils->execute_query($altersql, true);

    // memberID Make reference column same type as referred column.
    $altersql = "ALTER TABLE `modules_staff`
        ALTER `memberID` DROP DEFAULT";
    $updater_utils->execute_query($altersql, true);

    $altersql = "ALTER TABLE `modules_staff`
        CHANGE COLUMN `memberID` `memberID` INT(10) UNSIGNED NOT NULL AFTER `idMod`";
    $updater_utils->execute_query($altersql, true);

    // memberID Add fk and index.
    $altersql = "ALTER TABLE `modules_staff`
        ADD INDEX `idx_memberID` (`memberID`),
        ADD CONSTRAINT `modules_staff_fk2` FOREIGN KEY (`memberID`) REFERENCES `users` (`id`)";
    $updater_utils->execute_query($altersql, true);

    // Also add index to question table to hep question importing.
    $altersql = "ALTER TABLE `questions`
        ADD INDEX `idx_guid` (`guid`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1983_modules_staff');
}
