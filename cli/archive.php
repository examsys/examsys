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

/**
 * Archive data for graduated and left users
 * Moves formative and progress user responses to archive tables, deletes LTI links of users and resets their password
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
    echo 'Rogo is not installed.';
    exit(0);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';

// Lets look to see what arguments have been passed.
$options = 'ha:lr';
$longoptions = array(
    'help',
    'account:',
    'ldap',
    'archive',
);

$optionslist = getopt($options, $longoptions);
$help = 'Rogo archive script options'
    . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "-a, --account, \t\tRogo account to log process against [Required]"
    . PHP_EOL . PHP_EOL . "-l, --ldap, \t\tRogo is using ldap accounts [Optional]"
    . PHP_EOL . PHP_EOL . "-r, --archive, \t\tArchive data to a seperate database [Optional]";

if ((isset($optionslist['h']) or isset($optionslist['help'])) or ((!isset($optionslist['a']) and !isset($optionslist['account'])))) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

if (isset($optionslist['a'])) {
    $account = $optionslist['a'];
} elseif (isset($optionslist['account'])) {
    $account = $optionslist['account'];
}

if (isset($optionslist['l']) or isset($optionslist['ldap'])) {
    $ldap = 1;
} else {
    $ldap = 0;
}

if (isset($optionslist['r']) or isset($optionslist['archive'])) {
    $archive = 1;
} else {
    $archive = 0;
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

$stmt = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".users WHERE username = ?");
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

cli_utils::prompt("Start Archive Process " . date("Y-m-d H:i:s"));

$log0_deleted_overall = 0;
$log1_deleted_overall = 0;
$lti_user_deleted_overall = 0;

$stmt = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".users WHERE roles='left' OR roles='graduate'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_to_delete);
$numusers = $stmt->num_rows();
cli_utils::prompt($numusers . ' users to potentially archive');
$usercount = 1;

// Prepare queries.
$selectquery0 = $mysqli->prepare(get_logselectquery("log0", $cfg_db_database));
$logquery0 = $mysqliarchive->prepare(get_loginsertquery("log0_deleted", $cfg_archivedb_database));
$metaselectquery0 = $mysqli->prepare(get_metaselectquery("log0", $cfg_db_database));
$metalogquery0 = $mysqliarchive->prepare(get_metainsertquery($cfg_archivedb_database));
$selectquery1 = $mysqli->prepare(get_logselectquery("log1", $cfg_db_database));
$logquery1 = $mysqliarchive->prepare(get_loginsertquery("log1_deleted", $cfg_archivedb_database));
$metaselectquery1 = $mysqli->prepare(get_metaselectquery("log1", $cfg_db_database));
$metalogquery1 = $mysqliarchive->prepare(get_metainsertquery($cfg_archivedb_database));
$deletequerylti = $mysqli->prepare("DELETE FROM " . $cfg_db_database . ".lti_user WHERE lti_user_equ = ?");
$deletequery0 = $mysqli->prepare("DELETE l, lm FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
$deletequery1 = $mysqli->prepare("DELETE l, lm FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
$lm_check0 = $mysqli->prepare("SELECT count(lm.id) FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
$lm_check1 = $mysqli->prepare("SELECT count(lm.id) FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");

while ($stmt->fetch()) {
    $log0_deleted = 0;
    $log1_deleted = 0;
    $lti_user_deleted = 0;

    $lm_check0->bind_param('i', $user_to_delete);
    $lm_check0->execute();
    $lm_check0->bind_result($lm_count);
    $lm_check0->store_result();
    $lm_check0->fetch();

    cli_utils::prompt('Checking user ' . $usercount . ' / ' . $numusers);

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log0 rows to archive');
        $selectquery0->bind_param('i', $user_to_delete);
        $selectquery0->execute();
        $selectquery0->bind_result($id, $q_id, $mark, $adjmark, $totalpos, $user_answer, $errorstate, $screen, $duration, $updated, $dismiss, $option_order, $metadataID);
        $selectquery0->store_result();
        while($selectquery0->fetch()) {
            $logquery0->bind_param('iiiiisiiisssi', $id, $q_id, $mark, $adjmark, $totalpos, $user_answer, $errorstate, $screen, $duration, $updated, $dismiss, $option_order, $metadataID);
            $logquery0->execute();
        }

        $metaselectquery0->bind_param('i', $user_to_delete);
        $metaselectquery0->execute();
        $metaselectquery0->bind_result($id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name, $highest_screen);
        $metaselectquery0->store_result();
        while($metaselectquery0->fetch()) {
            $metalogquery0->bind_param('iiisssiissi', $id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name, $highest_screen);
            $metalogquery0->execute();
        }

        // Delete from formative log.
        $deletequery0->bind_param('i', $user_to_delete);
        $deletequery0->execute();
        $log0_deleted = $deletequery0->affected_rows;
        $log0_deleted_overall += $log0_deleted;

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log0', $user_to_delete, $account, $log0_deleted, 0, 'Clear old logs');
    }

    $lm_check1->bind_param('i', $user_to_delete);
    $lm_check1->execute();
    $lm_check1->bind_result($lm_count);
    $lm_check1->store_result();
    $lm_check1->fetch();

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log1 rows to archive');
        $selectquery1->bind_param('i', $user_to_delete);
        $selectquery1->execute();
        $selectquery1->bind_result($id, $q_id, $mark, $adjmark, $totalpos, $user_answer, $errorstate, $screen, $duration, $updated, $dismiss, $option_order, $metadataID);
        $selectquery1->store_result();
        while($selectquery1->fetch()) {
            $logquery1->bind_param('iiiiisiiisssi', $id, $q_id, $mark, $adjmark, $totalpos, $user_answer, $errorstate, $screen, $duration, $updated, $dismiss, $option_order, $metadataID);
            $logquery1->execute();
        }

        $metaselectquery1->bind_param('i', $user_to_delete);
        $metaselectquery1->execute();
        $metaselectquery1->bind_result($id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name, $highest_screen);
        $metaselectquery1->store_result();
        while($metaselectquery1->fetch()) {
            $metalogquery1->bind_param('iiisssiissi', $id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name, $highest_screen);
            $metalogquery1->execute();
        }

        // Delete from formative log.
        $deletequery1->bind_param('i', $user_to_delete);
        $deletequery1->execute();
        $log1_deleted = $deletequery1->affected_rows;
        $log1_deleted_overall += $log1_deleted;

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log1', $user_to_delete, $account, $log1_deleted, 0, 'Clear old logs');
    }

    // Delete from lti_user table.
    $deletequerylti->bind_param('i', $user_to_delete);
    $deletequerylti->execute();
    $lti_user_deleted = $deletequerylti->affected_rows;
    $lti_user_deleted_overall += $lti_user_deleted;

    if ($lti_user_deleted > 0) {
        cli_utils::prompt($lti_user_deleted . ' LTI users to delete');
        $logger->track_change('Delete LTI user', $user_to_delete, $account, 1, 0, 'Clear old logs');
    }
    $usercount++;
}
$stmt->close();
$logquery0->close();
$selectquery0->close();
$logquery1->close();
$selectquery1->close();
$metalogquery0->close();
$metaselectquery0->close();
$metalogquery1->close();
$metaselectquery1->close();
$deletequerylti->close();
$deletequery0->close();
$deletequery1->close();
$lm_check0->close();
$lm_check1->close();

// Reset passwords
if ($ldap) {
    cli_utils::prompt('LDAP enabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE " . $cfg_db_database . ".users SET password='' WHERE roles IN('Student', 'graduate', 'left')");
    $roles_string = 'Student, graduate and left';
} else {
    cli_utils::prompt('LDAP disabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE " . $cfg_db_database . ".users SET password='' WHERE roles IN('graduate', 'left')");
    $roles_string = 'graduate and left';
}
$updatequery->execute();
if ($updatequery->affected_rows > 0) {
    $logger->track_change('Reset passwords for roles ' . $roles_string, $account, $account, 1, 0, 'Clear old logs');
}
$updatequery->close();

$mysqli->close();
if ($archive) {
    $mysqliarchive->close();
}

cli_utils::prompt("Log0 records archived: " . $log0_deleted_overall);
cli_utils::prompt("Log1 records archived: " . $log1_deleted_overall);
cli_utils::prompt("End Archive Process " . date("Y-m-d H:i:s"));

/**
 * Get the log table select query
 * @param string $table log table we want
 * @param string $database the database to select from
 * @return string
 */
function get_logselectquery($table, $database) {
    return "SELECT l.id, l.q_id, l.mark, l.adjmark, l.totalpos, l.user_answer, l.errorstate, l.screen, l.duration, l.updated, l.dismiss, l.option_order, l.metadataID FROM " . $database . "." . $table ." l INNER JOIN " . $database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?";
}

/**
 * Get the insert log query
 * @param string $table log table we want
 * @param string $database the database to insert into
 * @return string
 */
function get_loginsertquery($table, $database) {
    return "INSERT INTO " . $database . "." . $table . " (id, q_id, mark, adjmark, totalpos, user_answer, errorstate, screen, duration, updated, dismiss, option_order, metadataID) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
}

/**
 * Get the metadata table select query
 * @param string $table log table we want
 * @param string $database the database to select from
 * @return string
 */
function get_metaselectquery($table, $database) {
    return "SELECT DISTINCT lm.id, lm.userID, lm.paperID, lm.started, lm.ipaddress, lm.student_grade, lm.year, lm.attempt, lm.completed, lm.lab_name, lm.highest_screen FROM " . $database . "." . $table . " l INNER JOIN " . $database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?";
}

/**
 * Get the insert metadata query
 * @param $database
 * @return string
 */
function get_metainsertquery($database) {
    return "INSERT INTO " . $database . ".log_metadata_deleted (id, userID, paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name, highest_screen) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
}
