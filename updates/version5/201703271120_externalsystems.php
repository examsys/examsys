<?php

if ($updater_utils->check_version("6.4.0")) {
    if (!$updater_utils->has_updated('externalsys')) {
        // External systems.
        $createsql = "CREATE TABLE external_systems (
            id int(8) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(30) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE INDEX `name_idx` (`name`))";
        $updater_utils->execute_query($createsql, true);
        // Oauth client mappings to external systems.
        $createsql = "CREATE TABLE external_systems_mapping (
            client_id varchar(80) NOT NULL,
            ext_id int(8) NOT NULL,
            PRIMARY KEY (client_id),
            UNIQUE INDEX `client_id_idx` (`client_id`))";
        $updater_utils->execute_query($createsql, true);
        $altersql = "ALTER TABLE external_systems_mapping ADD CONSTRAINT external_systems_mapping_fk1 FOREIGN KEY (ext_id) REFERENCES external_systems(id)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE external_systems_mapping ADD CONSTRAINT external_systems_mapping_fk2 FOREIGN KEY (client_id) REFERENCES oauth_clients(client_id)";
        $updater_utils->execute_query($altersql, true);
        // Add external systems entry for ims enterprise.
        $datasql = "INSERT IGNORE INTO external_systems (name, type) values ('ims_enterprise', 'plugin')";
        $updater_utils->execute_query($datasql, true);
        // Add api super user.
        $configObject->set_setting('api_allow_superuser', 0, 'boolean');
        // Set log location of api.
        $configObject->set_setting('apilogfile', '', 'string');
        // Alter external system keys.
        // courses.
        $altersqldrop = "DROP INDEX `externalid` on courses";
        $updater_utils->execute_query($altersqldrop, true);
        $altersqladd = "ALTER TABLE courses ADD UNIQUE KEY `externalid` (`externalid`, `externalsys`)";
        $updater_utils->execute_query($altersqladd, true);
        // faculty.
        $altersqldrop = "DROP INDEX `externalid` on faculty";
        $updater_utils->execute_query($altersqldrop, true);
        $altersqladd = "ALTER TABLE faculty ADD UNIQUE KEY `externalid` (`externalid`, `externalsys`)";
        $updater_utils->execute_query($altersqladd, true);
        // modules.
        $altersqldrop = "DROP INDEX `externalid` on modules";
        $updater_utils->execute_query($altersqldrop, true);
        $altersqladd = "ALTER TABLE modules ADD UNIQUE KEY `externalid` (`externalid`, `sms`)";
        $updater_utils->execute_query($altersqladd, true);
        // properties.
        $altersqldrop = "DROP INDEX `externalid` on properties";
        $updater_utils->execute_query($altersqldrop, true);
        $altersqladd = "ALTER TABLE properties ADD UNIQUE KEY `externalid` (`externalid`, `externalsys`)";
        $updater_utils->execute_query($altersqladd, true);
        // schools.
        $altersqldrop = "DROP INDEX `externalid` on schools";
        $updater_utils->execute_query($altersqldrop, true);
        $altersqladd = "ALTER TABLE schools ADD UNIQUE KEY `externalid` (`externalid`, `externalsys`)";
        $updater_utils->execute_query($altersqladd, true);
        $updater_utils->record_update('externalsys');
    }
}
