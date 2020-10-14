<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.2.0') and !$updater_utils->has_updated('GJLU-258_safeexambrowser')) {
    $sql = <<<EOF
CREATE TABLE `paper_metadata` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`paperID` MEDIUMINT(8) UNSIGNED NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`value` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `paperID_name_value` (`name`, `value`, `paperID`),
	INDEX `idx_paperID` (`paperID`),
    CONSTRAINT `paper_metadata_fk1`
        FOREIGN KEY (`paperID`) REFERENCES `properties` (`property_id`)
        ON DELETE CASCADE
)
EOF;
    $updater_utils->execute_query($sql, false);
    $updater_utils->execute_query("GRANT SELECT ON " . $configObject->get('cfg_db_database') . ".paper_metadata TO '". $configObject->get('cfg_db_username') . "'@'". $configObject->get('cfg_web_host') . "'", false);
    $updater_utils->execute_query("GRANT SELECT ON " . $configObject->get('cfg_db_database') . ".paper_metadata TO '". $configObject->get('cfg_db_student_user') . "'@'". $configObject->get('cfg_web_host') . "'", false);
    $updater_utils->execute_query("GRANT DELETE,INSERT,SELECT,UPDATE ON " . $configObject->get('cfg_db_database') . ".paper_metadata TO '". $configObject->get('cfg_db_staff_user') . "'@'". $configObject->get('cfg_web_host') . "'", false);

    // Install setting category
    $categories = array('seb');
    PaperSettings::createPaperSettingsCategories($update_mysqli, $categories);

    // Install setting declaration.
    PaperSettings::createPaperSettingsSetting(
        $update_mysqli,
        'seb_enabled',
        'seb',
        'boolean',
        '{"formative": 0, "progress": 1, "summative": 1, "survey": 0, "osce": 0, "offline": 0, "peer_review": 0}'
    );

    $updater_utils->record_update('GJLU-258_safeexambrowser');
}
