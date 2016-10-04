<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1971_question_modules')) {

    // Delete bad references.
    $deletesql = "DELETE FROM questions_modules WHERE q_id NOT IN (SELECT q_id FROM questions)";
    $updater_utils->execute_query($deletesql, true);

    $deletesql = "DELETE FROM questions_modules WHERE idMod NOT IN (SELECT id FROM modules)";
    $updater_utils->execute_query($deletesql, true);

    // Make reference columns same type as referred columns.
    $altersql = "ALTER TABLE `questions_modules`
        ALTER `q_id` DROP DEFAULT,
        ALTER `idMod` DROP DEFAULT";
    $updater_utils->execute_query($altersql, true);

    $altersql = "ALTER TABLE `questions_modules`
        CHANGE COLUMN `q_id` `q_id` INT(4) NOT NULL FIRST,
        CHANGE COLUMN `idMod` `idMod` INT(11) NOT NULL AFTER `q_id`";
    $updater_utils->execute_query($altersql, true);

    // Add an index to speed up some queries.
    $altersql = "ALTER TABLE `questions_modules` ADD INDEX `idx_q_id` (`q_id`)";
    $updater_utils->execute_query($altersql, true);

    // Add fks.
    $altersql = "ALTER TABLE `questions_modules`
        ADD CONSTRAINT `questions_modules_fk1` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`),
        ADD CONSTRAINT `questions_modules_fk2` FOREIGN KEY (`idMod`) REFERENCES `modules` (`id`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1971_question_modules');
}
