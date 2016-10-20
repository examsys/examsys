<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1866_random_link')) {
    // Create new table to store random block references.
    $createsql = "CREATE TABLE `random_link` (
        `id` INT(4) NOT NULL,
        `q_id` INT(4) NOT NULL,
        PRIMARY KEY (`id`, `q_id`),
        INDEX `random_link_fk2` (`q_id`),
        CONSTRAINT `random_link_fk1` FOREIGN KEY (`id`) REFERENCES `questions` (`q_id`),
        CONSTRAINT `random_link_fk2` FOREIGN KEY (`q_id`) REFERENCES `questions` (`q_id`)
    )";
    $updater_utils->execute_query($createsql, true);
    
    // Update grants.
    $cfg_db_student_user = $cfg_db_database . '_stu';
    $cfg_db_staff_user = $cfg_db_database . '_staff';
    $cfg_db_external_user = $cfg_db_database . '_ext';
    $cfg_db_internal_user = $cfg_db_database . '_int';
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".random_link TO '". $cfg_db_student_user . "'@'". $cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".random_link TO '" . $cfg_db_external_user . "'@'". $cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".random_link TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".random_link TO '". $cfg_db_staff_user . "'@'". $cfg_web_host . "'";
    foreach ($priv_SQL as $sql) {
        $updater_utils->execute_query($sql, true);
    }
    // Migrate existing references.
    $sql = $mysqli->prepare("SELECT id_num, o_id, option_text FROM options, questions WHERE q_id = o_id AND q_type = 'random'");
    $sql->execute();
    $sql->store_result();
    $sql->bind_result($id_num, $o_id, $option_text);
    // Split transaction if large.
    $limit = 1000;
    $rows = $sql->num_rows();
    if ($rows > $limit) {
        $split = true;
    } else {
        $split = false;
    }
    $count = 0;
    while ($sql->fetch()) {
        $updatesql = "UPDATE options SET option_text = NULL WHERE id_num = $id_num";
        $updater_utils->execute_query($updatesql, true);
        if (!empty($option_text)) {
            // Ingnore duplicate errors.
            $insertsql = "INSERT IGNORE INTO random_link (id, q_id) VALUES ($o_id, $option_text)";
            $updater_utils->execute_query($insertsql, true);
        }
        $count++;
        if ($split and $count == $limit) {
            $mysqli->commit();
            $count = 0;
        }
    }
    $sql->close();
    $updater_utils->record_update('rogo1866_random_link');
}