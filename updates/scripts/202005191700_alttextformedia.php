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

if ($updater_utils->check_version('7.2.0')) {
    if (!$updater_utils->has_updated('rogo2747')) {
        // Create new media table.
        $sqlmedia = 'CREATE TABLE `media` (
            `id` int unsigned NOT NULL auto_increment,
            `source` TEXT NOT NULL,
            `width` smallint,
            `height` smallint,
            `alt` TEXT,
            `ownerid` int(11) default NULL,
            PRIMARY KEY (`id`)
        )';
        $updater_utils->execute_query($sqlmedia, false);

        // Create new questions media table.
        $sqlmedia = 'CREATE TABLE `questions_media` (
            `qid` int NOT NULL,
            `mediaid` int unsigned NOT NULL,
            `num` int unsigned NOT NULL,
            UNIQUE INDEX `questions_media_idx0` (`qid`, `mediaid`),
            FOREIGN KEY questions_media_fk0 (qid) REFERENCES questions(q_id),
            FOREIGN KEY questions_media_fk1 (mediaid) REFERENCES media(id)
        )';
        $updater_utils->execute_query($sqlmedia, false);

        // Create new options media table.
        $sqlmedia = 'CREATE TABLE `options_media` (
            `oid` int NOT NULL,
            `mediaid` int unsigned NOT NULL,
            UNIQUE INDEX `options_media_idx0` (`oid`, `mediaid`),
            FOREIGN KEY options_media_fk0 (oid) REFERENCES options(id_num),
            FOREIGN KEY options_media_fk1 (mediaid) REFERENCES media(id)
        )';
        $updater_utils->execute_query($sqlmedia, false);

        // Add grants for new media table.
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".media TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".media TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".media TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".media TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".media TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        // Add grants for new questions media table.
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".questions_media TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".questions_media TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".questions_media TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".questions_media TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".questions_media TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        // Add grants for new options media table.
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".options_media TO '"
            . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".options_media TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".options_media TO '"
            . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".options_media TO '"
            . $configObject->get('cfg_db_sct_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database')
            . ".options_media TO '" . $configObject->get('cfg_db_staff_user') . "'@'"
            . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        // Migrate data to new table. Non extmach first.
        $sqlmigrateselect = $update_mysqli->prepare('SELECT
                q_id,
                q_media,
                q_media_width,
                q_media_height,
                ownerID
            FROM
                questions
            WHERE
                q_media != ""
            AND
                q_type != "extmatch"
            ');
        $sqlmigrateselect->execute();
        $sqlmigrateselect->bind_result($qid, $qmedia, $qwidth, $qheight, $qowner);
        $sqlmigrateselect->store_result();
        $migrateinsert = $update_mysqli->prepare('INSERT INTO media (source, width, height, ownerid) VALUES (?, ?, ?, ?)');
        $insertquestionsmedia = $update_mysqli->prepare('INSERT INTO questions_media (qid, mediaid, num) VALUES (?, ?, ?)');
        while ($sqlmigrateselect->fetch()) {
            $migrateinsert->bind_param('siii', $qmedia, $qwidth, $qheight, $qowner);
            $migrateinsert->execute();
            $mediaid = $update_mysqli->insert_id;
            $count = 0;
            $insertquestionsmedia->bind_param('iii', $qid, $mediaid, $count);
            $insertquestionsmedia->execute();
        }
        $sqlmigrateselect->close();

        // Migrate extmach data.
        $sqlmigrateselect = $update_mysqli->prepare('SELECT
                q_id,
                q_media,
                q_media_width,
                q_media_height,
                ownerID
            FROM
                questions
            WHERE
                q_media != ""
            AND
                q_type = "extmatch"
            ');
        $sqlmigrateselect->execute();
        $sqlmigrateselect->bind_result($qid, $qmedia, $qwidth, $qheight, $qowner);
        $sqlmigrateselect->store_result();
        while ($sqlmigrateselect->fetch()) {
            $mediaarray = explode('|', $qmedia);
            $widtharray  = explode('|', $qwidth);
            $heightarray  = explode('|', $qheight);
            $count = 0;
            foreach ($mediaarray as $media) {
                if ($media != '') {
                    $migrateinsert->bind_param('siii', $media, $widtharray[$count], $heightarray[$count], $qowner);
                    $migrateinsert->execute();
                    $mediaid = $update_mysqli->insert_id;
                    $insertquestionsmedia->bind_param('iii', $qid, $mediaid, $count);
                    $insertquestionsmedia->execute();
                }
                $count++;
            }
        }
        $migrateinsert->close();
        $insertquestionsmedia->close();
        $sqlmigrateselect->close();

        // Drop columns no longer required.
        $sqlalter = 'ALTER TABLE `questions`
            DROP COLUMN `q_media`,
            DROP COLUMN `q_media_width`,
            DROP COLUMN `q_media_height`';
        $updater_utils->execute_query($sqlalter, false);

        // Migrate options data to new table
        $sqlmigrateselect = $update_mysqli->prepare('SELECT
                id_num,
                o_media,
                o_media_width,
                o_media_height,
                ownerID
            FROM
                questions, options
            WHERE
                questions.q_id = options.o_id
            AND
                o_media != ""
            ');
        $sqlmigrateselect->execute();
        $sqlmigrateselect->bind_result($idnum, $omedia, $owidth, $oheight, $qowner);
        $sqlmigrateselect->store_result();
        $migrateinsert = $update_mysqli->prepare('INSERT INTO media (source, width, height, ownerid) VALUES (?, ?, ?, ?)');
        $insertquestionsmedia = $update_mysqli->prepare('INSERT INTO options_media (oid, mediaid) VALUES (?, ?)');
        while ($sqlmigrateselect->fetch()) {
            $migrateinsert->bind_param('siii', $omedia, $owidth, $oheight, $qowner);
            $migrateinsert->execute();
            $mediaid = $update_mysqli->insert_id;
            $insertquestionsmedia->bind_param('ii', $idnum, $mediaid);
            $insertquestionsmedia->execute();
        }
        $sqlmigrateselect->close();

        $sqlalter = 'ALTER TABLE `options`
            DROP COLUMN `o_media`,
            DROP COLUMN `o_media_width`,
            DROP COLUMN `o_media_height`';
        $updater_utils->execute_query($sqlalter, false);
        $updater_utils->record_update('rogo2747');
    }
}
