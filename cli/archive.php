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

/**
 * Archive data for graduated and left users
 * Moves formative and progress user responses to archive tables
 * @author Simon Wilkinson
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

// Only run from the command line!
if (PHP_SAPI != 'cli') {
    die("Please run this script from the CLI!\n");
}

set_time_limit(0);

$rogo_path = dirname(__DIR__);
if (!file_exists($rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
    echo 'ExamSys is not installed.';
    exit(0);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';

// Lets look to see what arguments have been passed.
$options = '';
$longoptions = array(
    'help',
    'archive',
    'commit',
    'account:',
    'target::',
    'batch::',
    'userlimit::',
    'skip::',
    'noprogress::',
    'noformative::'
);

$optionslist = getopt($options, $longoptions);
$help = 'ExamSys archive script options. Archives all graduated and left accounts, unless the target param is specified'
    . PHP_EOL . PHP_EOL . "--help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "--account, \t\tRogo account to log process against [Required]"
    . PHP_EOL . PHP_EOL . "--archive, \t\tArchive data to a seperate database (default no) [Optional]"
    . PHP_EOL . PHP_EOL . "--target, \t\tTarget a single user account [Optional]"
    . PHP_EOL . PHP_EOL . "--batch, \t\tBatch size (default 50 papers) [Optional]"
    . PHP_EOL . PHP_EOL . "--commit, \t\tCommit transactions (default no) [Optional]"
    . PHP_EOL . PHP_EOL . "--userlimit, \t\tNumber of users to archive (default 100 user) [Optional]"
    . PHP_EOL . PHP_EOL . "--skip, \t\tSkip already archived users (default no) [Optional]"
    . PHP_EOL . PHP_EOL . "--noprogress, \t\tDo not archive progress tests (default archive) [Optional]"
    . PHP_EOL . PHP_EOL . "--noformative, \t\tDo not archive formative tests (default archive) [Optional]";

if (isset($optionslist['help']) or !isset($optionslist['account'])) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

if (isset($optionslist['account'])) {
    $account = $optionslist['account'];
}
$account = param::clean($account, param::TEXT);
if (is_null($account)) {
    cli_utils::prompt('Invalid account supplied');
    exit(0);
}

if (isset($optionslist['archive'])) {
    $archive = true;
} else {
    $archive = false;
}

if (isset($optionslist['commit'])) {
    $commit = true;
} else {
    cli_utils::prompt('*** This is a dry run nothing will be committed to the database ***');
    $commit = false;
}

if (isset($optionslist['target'])) {
    $target = 1;
    $targetted_user = $optionslist['target'];
} else {
    $target = 0;
}

if (isset($optionslist['batch'])) {
    $batchsize = $optionslist['batch'];
} else {
    $batchsize = 50;
}

if (isset($optionslist['userlimit'])) {
    $userlimit = $optionslist['userlimit'];
} else {
    $userlimit = 100;
}

if (isset($optionslist['skip'])) {
    $skiparchived = true;
} else {
    $skiparchived = false;
}

if (isset($optionslist['noformative'])) {
    $formative = false;
} else {
    $formative = true;
}


if (isset($optionslist['noprogress'])) {
    $progress = false;
} else {
    $progress = true;
}


if ($target == 0) {
    cli_utils::prompt('Archiving ' . $userlimit . ' LEFT and GRADUATE accounts');
} else {
    $targetted_user = param::clean($targetted_user, param::TEXT);
    if (is_null($targetted_user)) {
        cli_utils::prompt('Invalid target user supplied');
        exit(0);
    }
    cli_utils::prompt('Archiving account: ' . $targetted_user);
}

$cfg_db_host = $configObject->get('cfg_db_host');
$cfg_db_port = $configObject->get('cfg_db_port');
$cfg_db_database = $configObject->get('cfg_db_database');
$charset = 'utf8mb4';
$cfg_db_sysadmin_user = $configObject->get('cfg_db_sysadmin_user');
$cfg_db_sysadmin_passwd = $configObject->get('cfg_db_sysadmin_passwd');

@$mysqli = new mysqli($cfg_db_host, $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database, $cfg_db_port);
if ($mysqli->connect_error == '') {
    $mysqli->set_charset($charset);
} else {
    cli_utils::prompt('Unable to connect to database - ' . $mysqli->connect_error);
    exit(0);
}

// Setup archive database connection.
if ($archive) {
    cli_utils::prompt('Connecting to archive database');
    $cfg_archivedb_host = $configObject->get('cfg_archivedb_host');
    $cfg_archivedb_port = $configObject->get('cfg_archivedb_port');
    $cfg_archivedb_database = $configObject->get('cfg_archivedb_database');
    $cfg_archivedb_sysadmin_user = $configObject->get('cfg_archivedb_username');
    $cfg_archivedb_sysadmin_passwd = $configObject->get('cfg_archivedb_passwd');
    @$mysqliarchive = new mysqli($cfg_archivedb_host, $cfg_archivedb_sysadmin_user, $cfg_archivedb_sysadmin_passwd, $cfg_archivedb_database, $cfg_archivedb_port);
    if ($mysqliarchive->connect_error == '') {
        $mysqliarchive->set_charset($charset);
    } else {
        cli_utils::prompt('Unable to connect to archive database - ' . $mysqliarchive->connect_error);
        exit(0);
    }
} else {
    $mysqliarchive = $mysqli;
    $cfg_archivedb_database = $cfg_db_database;
}

$logger = new Logger($mysqli);

$stmt = $mysqli->prepare('SELECT id FROM ' . $cfg_db_database . '.users WHERE username = ?');
$stmt->bind_param('s', $account);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userid);
if ($stmt->num_rows() !== 1) {
    cli_utils::prompt('User `' . $account . '` does not exist');
    $stmt->close();
    exit(0);
}
$stmt->fetch();
$stmt->close();

cli_utils::prompt('Start Archive Process ' . date('Y-m-d H:i:s'));

$log0_deleted_overall = 0;
$log1_deleted_overall = 0;

// Archive X left and graduate accounts. Not already archived.
if ($target == 0) {
    $sql = 'SELECT distinct u.id FROM '
        . $cfg_db_database . '.users u, '
        . $cfg_db_database . '.user_roles ur, '
        . $cfg_db_database . '.roles r, '
        . $cfg_db_database . '.log_metadata lm
    WHERE
        ur.roleid = r.id 
    AND u.id = ur.userid
    AND u.id = lm.userID
    AND r.name IN ("graduate", "left")';
    if ($skiparchived) {
        $sql .= ' AND u.id NOT IN (SELECT distinct userID from log_metadata_deleted)';
    }
} else {
    $sql = 'SELECT id FROM '
        . $cfg_db_database . '.users u
    WHERE
        u.username = ?';
}
$stmt = $mysqli->prepare($sql);
if ($target == 1) {
    $stmt->bind_param('s', $targetted_user);
}
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_to_archive);
if ($target != 1) {
    $numusers = $stmt->num_rows();
    cli_utils::prompt($numusers . ' users to potentially archive');
}
$potential_users = array();
while ($stmt->fetch()) {
    $potential_users[] = $user_to_archive;
}
$stmt->close();

// Prepare queries.
$selectquery0 = $mysqli->prepare(get_logselectquery('log0', $cfg_db_database));
$selectquery1 = $mysqli->prepare(get_logselectquery('log1', $cfg_db_database));
$metaselectquery = $mysqli->prepare(get_metaselectquery($cfg_db_database));
$logquery0 = $mysqliarchive->prepare(get_loginsertquery('log0_deleted', $cfg_archivedb_database));
$logquery1 = $mysqliarchive->prepare(get_loginsertquery('log1_deleted', $cfg_archivedb_database));
$metalogquery = $mysqliarchive->prepare(get_metainsertquery($cfg_archivedb_database));
$deletequerylog0 = $mysqli->prepare('DELETE FROM ' . $cfg_db_database . '.log0 WHERE metadataID = ?');
$deletequerylog1 = $mysqli->prepare('DELETE FROM ' . $cfg_db_database . '.log1 WHERE metadataID = ?');
$deletequerymd = $mysqli->prepare('DELETE FROM ' . $cfg_db_database . '.log_metadata WHERE id = ?');

// Start transaction.
$mysqli->autocommit(false);

// Formative papers.
if ($formative) {
    $usercount = 0;
    for ($i = 0; $i < count($potential_users); $i++) {
        // Get log0 data.
        $selectquery0->bind_param('i', $potential_users[$i]);
        $selectquery0->execute();
        $selectquery0->bind_result(
            $id,
            $q_id,
            $mark,
            $adjmark,
            $totalpos,
            $user_answer,
            $errorstate,
            $screen,
            $duration,
            $updated,
            $dismiss,
            $option_order,
            $metadataID
        );
        $selectquery0->store_result();
        $log0_deleted = 0;
        $archivecount = 0;
        $oldmetdataID = 0;
        while ($selectquery0->fetch()) {
            // Insert into archive formative log.
            $logquery0->bind_param(
                'iiiiisiiisssi',
                $id,
                $q_id,
                $mark,
                $adjmark,
                $totalpos,
                $user_answer,
                $errorstate,
                $screen,
                $duration,
                $updated,
                $dismiss,
                $option_order,
                $metadataID
            );

            if ($metadataID != $oldmetdataID) {
                if ($oldmetdataID !== 0) {
                    $archivecount++;
                    // Archive the Metadata.
                    archiveMetaData($metaselectquery, $oldmetdataID, $metalogquery, $deletequerymd);
                    // Delete from formative log.
                    $deletequerylog0->bind_param('i', $oldmetdataID);
                    $deletequerylog0->execute();
                }
                $oldmetdataID = $metadataID;
            }

            // Commit every batchsize number of paper attempts.
            if ($archivecount === $batchsize) {
                // Commit transaction.
                if ($commit) {
                    $mysqli->commit();
                } else {
                    $mysqli->rollback();
                }
                $archivecount = 0;
            }

            $logquery0->execute();
            $log0_deleted++;
        }

        $log0_deleted_overall += $log0_deleted;

        // Archive the left over.
        if ($oldmetdataID !== 0) {
            archiveMetaData($metaselectquery, $oldmetdataID, $metalogquery, $deletequerymd);
            $deletequerylog0->bind_param('i', $oldmetdataID);
            $deletequerylog0->execute();
            if ($commit) {
                $mysqli->commit();
            } else {
                $mysqli->rollback();
            }
        }

        // Record the delete in audit trail
        if ($log0_deleted > 0) {
            cli_utils::prompt($log0_deleted . ' formative logs archived for user ' . $potential_users[$i]);
            $logger->track_change(
                'Deleted records from log0',
                $potential_users[$i],
                $account,
                $log0_deleted,
                0,
                'Clear old logs'
            );
            $usercount++;
            if ($commit) {
                $mysqli->commit();
            } else {
                $mysqli->rollback();
            }
        }

        // Only archive the number of users specified.
        if (!$target and $usercount >= $userlimit) {
            cli_utils::prompt($usercount . '/' . $userlimit . ' users complete (formative)');
            break;
        }
    }
}

// Progress tests.
if ($progress) {
    $usercount = 0;
    for ($i = 0; $i < count($potential_users); $i++) {
        // Get log1 data.
        $selectquery1->bind_param('i', $potential_users[$i]);
        $selectquery1->execute();
        $selectquery1->bind_result(
            $id,
            $q_id,
            $mark,
            $adjmark,
            $totalpos,
            $user_answer,
            $errorstate,
            $screen,
            $duration,
            $updated,
            $dismiss,
            $option_order,
            $metadataID
        );
        $selectquery1->store_result();
        $log1_deleted = 0;
        $archivecount = 0;
        $oldmetdataID = 0;
        while ($selectquery1->fetch()) {
            // Insert into archive formative log.
            $logquery1->bind_param(
                'iiiiisiiisssi',
                $id,
                $q_id,
                $mark,
                $adjmark,
                $totalpos,
                $user_answer,
                $errorstate,
                $screen,
                $duration,
                $updated,
                $dismiss,
                $option_order,
                $metadataID
            );

            // Increment count for each new paper attempt.
            if ($metadataID != $oldmetdataID) {
                if ($oldmetdataID !== 0) {
                    $archivecount++;
                    // Archive the Metadata.
                    archiveMetaData($metaselectquery, $oldmetdataID, $metalogquery, $deletequerymd);
                    // Delete from progress log.
                    $deletequerylog1->bind_param('i', $oldmetdataID);
                    $deletequerylog1->execute();
                }
                $oldmetdataID = $metadataID;
            }

            // Commit every batchsize number of paper attempts.
            if ($archivecount === $batchsize) {
                // Commit transaction.
                if ($commit) {
                    $mysqli->commit();
                } else {
                    $mysqli->rollback();
                }
                $archivecount = 0;
            }

            $logquery1->execute();
            $log1_deleted++;
        }

        $log1_deleted_overall += $log1_deleted;

        // Archive the left over.
        if ($oldmetdataID !== 0) {
            archiveMetaData($metaselectquery, $oldmetdataID, $metalogquery, $deletequerymd);
            $deletequerylog1->bind_param('i', $oldmetdataID);
            $deletequerylog1->execute();
            if ($commit) {
                $mysqli->commit();
            } else {
                $mysqli->rollback();
            }
        }

        // Record the delete in audit trail
        if ($log1_deleted > 0) {
            cli_utils::prompt($log1_deleted . ' progress logs archived for user ' . $potential_users[$i]);
            $logger->track_change(
                'Deleted records from log1',
                $potential_users[$i],
                $account,
                $log1_deleted,
                0,
                'Clear old logs'
            );
            $usercount++;
            if ($commit) {
                $mysqli->commit();
            } else {
                $mysqli->rollback();
            }
        }

        // Only archive the number of users specified.
        if (!$target and $usercount >= $userlimit) {
            cli_utils::prompt($usercount . '/' . $userlimit . ' users complete (progress)');
            break;
        }
    }
}
$logquery0->close();
$selectquery0->close();
$logquery1->close();
$selectquery1->close();
$deletequerylog0->close();
$deletequerylog1->close();
$deletequerymd->close();
$mysqli->close();
if ($archive) {
    $mysqliarchive->close();
}

cli_utils::prompt('Log0 records archived: ' . $log0_deleted_overall);
cli_utils::prompt('Log1 records archived: ' . $log1_deleted_overall);
cli_utils::prompt('End Archive Process ' . date('Y-m-d H:i:s'));

/**
 * Get the log table select query
 * @param string $table log table we want
 * @param string $database the database to select from
 * @return string
 */
function get_logselectquery($table, $database)
{
    return 'SELECT
        l.id, l.q_id, l.mark, l.adjmark, l.totalpos, l.user_answer, l.errorstate, l.screen, l.duration,
        l.updated, l.dismiss, l.option_order, l.metadataID
    FROM '
        . $database . '.' . $table . ' l INNER JOIN ' . $database . '.log_metadata lm ON l.metadataID = lm.id
    WHERE lm.userID = ? ORDER BY lm.id, l.id';
}

/**
 * Get the insert log query
 * @param string $table log table we want
 * @param string $database the database to insert into
 * @return string
 */
function get_loginsertquery($table, $database)
{
    return 'INSERT INTO '
        . $database . '.' . $table
        . ' (id, q_id, mark, adjmark, totalpos, user_answer, errorstate, screen, duration,
            updated, dismiss, option_order, metadataID)
            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
}

/**
 * Get the metadata table select query
 * @param string $database the database to select from
 * @return string
 */
function get_metaselectquery($database)
{
    return 'SELECT
        lm.id, lm.userID, lm.paperID, lm.started, lm.ipaddress, lm.student_grade, lm.year, lm.attempt,
        lm.completed, lm.lab_name, lm.highest_screen
    FROM '
        . $database . '.log_metadata lm
    WHERE lm.id = ?';
}

/**
 * Get the insert metadata query
 * @param $database
 * @return string
 */
function get_metainsertquery($database)
{
    return 'INSERT INTO '
        . $database . '.log_metadata_deleted
        (id, userID, paperID, started, ipaddress, student_grade, year, attempt, completed,
        lab_name, highest_screen)
        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
}

/**
 * Archive the metadata
 * @param string $metaselectquery query to select metadata
 * @param int $oldmetdataID metadata id
 * @param string $metalogquery query to insert metadata
 * @param string $deletequerymd query to delete metadata
 */
function archiveMetaData($metaselectquery, $oldmetdataID, $metalogquery, $deletequerymd)
{
    $metaselectquery->bind_param('i', $oldmetdataID);
    $metaselectquery->execute();
    $metaselectquery->bind_result(
        $metaid,
        $userID,
        $paperID,
        $started,
        $ipaddress,
        $student_grade,
        $year,
        $attempt,
        $completed,
        $lab_name,
        $highest_screen
    );
    $metaselectquery->store_result();
    $metaselectquery->fetch();

    // Insert into archive meta log.
    $metalogquery->bind_param(
        'iiisssiissi',
        $metaid,
        $userID,
        $paperID,
        $started,
        $ipaddress,
        $student_grade,
        $year,
        $attempt,
        $completed,
        $lab_name,
        $highest_screen
    );
    $metalogquery->execute();

    // Delete from meta log.
    $deletequerymd->bind_param('i', $metaid);
    $deletequerymd->execute();
}
