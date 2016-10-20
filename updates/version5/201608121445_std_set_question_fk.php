<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1885_std_set_question_fk')) {

    $altersql = "ALTER TABLE std_set_questions ADD CONSTRAINT `std_set_questions_fk1` FOREIGN KEY (`std_setID`) REFERENCES `std_set` (`id`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1885_std_set_question_fk');
}
