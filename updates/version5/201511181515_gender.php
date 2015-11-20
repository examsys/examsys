<?php

if (!$updater_utils->has_updated('rogo1584_gender')) {
    // Adding Mx to title.
    $altersql = "ALTER TABLE temp_users CHANGE title title enum('Dr','Miss','Mr','Mrs','Ms','Professor','Mx') default NULL";
    $updater_utils->execute_query($altersql, true);
    // Adding Other to gender.
    $altersql = "ALTER TABLE users CHANGE gender gender enum('Male','Female', 'Other') default NULL";
    $updater_utils->execute_query($altersql, true);
    $updater_utils->record_update('rogo1584_gender');
}
