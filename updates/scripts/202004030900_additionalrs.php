<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.2.0')) {
    if (!$updater_utils->has_updated('rogo2767')) {
        // Add select grant to student user.
        $sql = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".toilet_breaks TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        $sqlcategory = 'CREATE TABLE `paper_settings_category` (
            `category` varchar(10) NOT NULL,
            PRIMARY KEY (`category`)
        )';
        $updater_utils->execute_query($sqlcategory, false);

        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_category TO '"
            . $configObject->get('cfg_db_inv_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings_category TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings_category TO '" . $configObject->get('cfg_db_webservice_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        $sqlproperties = 'CREATE TABLE `paper_settings_setting` (
            `setting` varchar(100) NOT NULL,
            `category` varchar(10) NOT NULL,
            `type` varchar(10) NOT NULL,
            `supported` json,
            PRIMARY KEY (`setting`),
            INDEX (`category`),
            FOREIGN KEY paper_settings_property_fk0 (category) REFERENCES paper_settings_category(category)
        )';
        $updater_utils->execute_query($sqlproperties, false);

        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings_setting TO '"
            . $configObject->get('cfg_db_inv_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings_setting TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings_setting TO '" . $configObject->get('cfg_db_webservice_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        $sqlproperties = 'CREATE TABLE `paper_settings` (
            `paperid` mediumint(8) unsigned NOT NULL,
            `setting` varchar(100) NOT NULL,
            `value` TEXT,
            PRIMARY KEY (`paperid`, `setting`),
            FOREIGN KEY paper_settings_fk0 (paperid) REFERENCES properties(property_id),
            FOREIGN KEY paper_settings_fk1 (setting) REFERENCES paper_settings_setting(setting)
        )';
        $updater_utils->execute_query($sqlproperties, false);

        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".paper_settings TO '"
            . $configObject->get('cfg_db_inv_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".paper_settings TO '" . $configObject->get('cfg_db_webservice_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        // Install Categories.
        $categories = array('general', 'security', 'prologue', 'postscript',
            'rubric', 'feedback', 'reviewers', 'reference');
        PaperSettings::createPaperSettingsCategories($update_mysqli, $categories);

        // Install Setting declarations.
        PaperSettings::createPaperSettingsSetting(
            $update_mysqli,
            'remote_summative',
            'security',
            'boolean',
            '{"formative": 0, "progress": 0, "summative": 1, "survey": 0, "osce": 0, "offline": 0, "peer_review": 0}'
        );

        $sql = 'DELETE FROM config WHERE setting = "summative_remote"';
        $updater_utils->execute_query($sql, false);

        // Paper type configuration.
        $papertypes = array(
            'formative' => 1,
            'progress' => 1,
            'summative' => 1,
            'survey' => 1,
            'osce' => 1,
            'offline' => 1,
            'peer_review' => 1
        );
        $configObject->set_setting('paper_types', $papertypes, Config::ASSOC);

        $updater_utils->record_update('rogo2767');
    }
}
