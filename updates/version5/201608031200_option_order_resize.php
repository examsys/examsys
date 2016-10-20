<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1861_option_order_resize')) {

    $altersql = "ALTER TABLE log0 MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log0_deleted MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log1 MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log1_deleted MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log2 MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log3 MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $altersql = "ALTER TABLE log_late MODIFY COLUMN `option_order` varchar(100) DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1861_option_order_resize');
}
