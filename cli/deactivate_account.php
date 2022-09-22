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
 * Deactivate accounts for graduated and left users
 * Deletes LTI links of users and resets their password
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
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
    'account:',
    'target::',
);

$optionslist = getopt($options, $longoptions);
$help = 'ExamSys archive script options. Archives all graduated and left accounts, unless the target param is specified'
    . PHP_EOL . PHP_EOL . "--help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "--account, \t\tRogo account to log process against [Required]"
    . PHP_EOL . PHP_EOL . "--target, \t\tTarget a single user account [Optional]";

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

if (isset($optionslist['target'])) {
    $target = 1;
    $targetted_user = $optionslist['target'];
} else {
    $target = 0;
}
if ($target == 0) {
    cli_utils::prompt('Deactivating all LEFT and GRADUATE accounts');
} else {
    $targetted_user = param::clean($targetted_user, param::TEXT);
    if (is_null($targetted_user)) {
        cli_utils::prompt('Invalid target user supplied');
        exit(0);
    }
    cli_utils::prompt('Deactivating account: ' . $targetted_user);
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

cli_utils::prompt('Start Process ' . date('Y-m-d H:i:s'));

$lti_user_deleted_overall = 0;

// Deactivate all left and graduate accounts.
if ($target == 0) {
    $sql = 'SELECT u.id FROM '
        . $cfg_db_database . '.users u, '
        . $cfg_db_database . '.user_roles ur, '
        . $cfg_db_database . '.roles r
    WHERE
        ur.roleid = r.id 
    AND u.id = ur.userid
    AND r.name IN ("graduate", "left")';
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
$stmt->bind_result($user_to_delete);
$numusers = $stmt->num_rows();
cli_utils::prompt($numusers . ' users to potentially deactivate');
$usercount = 1;

// Prepare queries.
$deletequerylti = $mysqli->prepare('DELETE FROM ' . $cfg_db_database . '.lti_user WHERE lti_user_equ = ?');

while ($stmt->fetch()) {
    $lti_user_deleted = 0;

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
$deletequerylti->close();

// Reset passwords
$sql = 'UPDATE '
    . $cfg_db_database . '.users u, '
    . $cfg_db_database . '.user_roles ur, '
    . $cfg_db_database . '.roles r
    SET u.password = ""
    WHERE
        ur.roleid = r.id
    AND u.id = ur.userid
    AND r.name IN ("graduate", "left")';
if ($target == 1) {
    $sql .= ' AND u.username = ?';
}
cli_utils::prompt('Resetting passwords');
$updatequery = $mysqli->prepare($sql);
if ($target == 1) {
    $updatequery->bind_param('s', $targetted_user);
}
$roles_string = 'graduate and left';

$updatequery->execute();
if ($updatequery->affected_rows > 0) {
    $logger->track_change('Reset passwords for roles ' . $roles_string, $account, $account, 1, 0, 'Clear old logs');
}
$updatequery->close();

$mysqli->close();

cli_utils::prompt('End Process ' . date('Y-m-d H:i:s'));
