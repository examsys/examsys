<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo_1813')) {
    $search = '$cfg_db_collation';
    switch ($configObject->get('cfg_db_charset')) {
      case 'utf8mb4':
        $new_lines = '$cfg_db_collation = \'utf8mb4_unicode_ci\';' . PHP_EOL;
        break;
      case 'utf8':
        $new_lines = '$cfg_db_collation = \'utf8_general_ci\';' . PHP_EOL;
        break;
      default:
        $new_lines = '$cfg_db_collation = \'latin1_swedish_ci\';' . PHP_EOL;
    }
    $target_line = '$cfg_db_charset';
    $updater_utils->add_line($string, $search, $new_lines, 27, $cfg_web_root, $target_line);
    $replace = 'debug.inc';
    $updater_utils->replace_line($string, $replace, '', $cfg_web_root);
    // Oauth tables changes.
    $sqloauth_clients = "ALTER TABLE oauth_clients MODIFY COLUMN redirect_uri TEXT NOT NULL";
    $updater_utils->execute_query($sqloauth_clients, false);
    $sqloauth_access_tokens = "ALTER TABLE oauth_access_tokens MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_access_tokens, false);
    $sqloauth_authorization_codes = "ALTER TABLE oauth_authorization_codes MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_authorization_codes, false);
    $sqloauth_authorization_codes2 = "ALTER TABLE oauth_authorization_codes MODIFY COLUMN redirect_uri TEXT";
    $updater_utils->execute_query($sqloauth_authorization_codes2, false);
    $sqloauth_refresh_tokens = "ALTER TABLE oauth_refresh_tokens MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_refresh_tokens, false);
    $sqloauth_users = "ALTER TABLE oauth_users MODIFY COLUMN password TEXT";
    $updater_utils->execute_query($sqloauth_users, false);
    $sqloauth_jwt = "ALTER TABLE oauth_jwt MODIFY COLUMN public_key TEXT";
    $updater_utils->execute_query($sqloauth_jwt, false);
    // Config table changes.
    $sqlconfig = "ALTER TABLE config MODIFY COLUMN `component` varchar(100) NOT NULL DEFAULT 'core'";
    $updater_utils->execute_query($sqlconfig, false);
    $sqlconfig2 = "ALTER TABLE config MODIFY COLUMN `setting` varchar(100) NOT NULL DEFAULT ''";
    $updater_utils->execute_query($sqlconfig2, false);
    $sqlconfig3 = "ALTER TABLE config MODIFY COLUMN `value` TEXT";
    $updater_utils->execute_query($sqlconfig3, false);
    // Ebel tables changes.
    $sqlebel = "ALTER TABLE ebel MODIFY COLUMN `category` char(3) NOT NULL";
    $updater_utils->execute_query($sqlebel, false);
    // Keyword question tables changes.
    $sqlkeyword = "ALTER TABLE keywords_question MODIFY COLUMN `q_id` int(11) NOT NULL";
    $updater_utils->execute_query($sqlkeyword, false);
    $sqlkeyword2 = "ALTER TABLE keywords_question MODIFY COLUMN `keywordID` int(11) NOT NULL";
    $updater_utils->execute_query($sqlkeyword2, false);
    // Staff help tables changes.
    $sqlstaff = "ALTER TABLE staff_help MODIFY COLUMN `language` char(5) NOT NULL DEFAULT 'en'";
    $updater_utils->execute_query($sqlstaff, false);
    $sqlstaff2 = "ALTER TABLE staff_help MODIFY COLUMN `lastupdated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    $updater_utils->execute_query($sqlstaff2, false);
    // Student help tables changes.
    $sqlstudent = "ALTER TABLE student_help MODIFY COLUMN `language` char(5) NOT NULL DEFAULT 'en'";
    $updater_utils->execute_query($sqlstudent, false);
    $sqlstudent2 = "ALTER TABLE student_help MODIFY COLUMN `lastupdated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    $updater_utils->execute_query($sqlstudent2, false);
    // Sid table changes.
    $sqlsid = "ALTER TABLE sid MODIFY COLUMN `student_id` char(15) NOT NULL";
    $updater_utils->execute_query($sqlsid, false);
    $sqlsid2 = "ALTER TABLE sid MODIFY COLUMN `userID` int(10) unsigned NOT NULL";
    $updater_utils->execute_query($sqlsid2, false);
    $updater_utils->record_update('rogo_1813');
  }
}