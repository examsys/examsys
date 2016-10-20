<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1887_users_metadata_fk2')) {

    $altersql = "ALTER TABLE `users_metadata`
        CHANGE COLUMN `idMod` `idMod` INT(11) NULL DEFAULT NULL AFTER `userID`,
        ADD CONSTRAINT `users_metadata_fk1` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1887_users_metadata_fk2');
}
