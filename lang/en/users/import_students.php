<?php
require '../lang/' . $language . '/include/user_search_options.inc';

$string['sendwelcomeemail'] = 'Send welcome email to user';
$string['importstudents'] = 'Import Students';
$string['csvfile'] = 'CSV File:';
$string['import'] = 'Import';
$string['msg1'] = 'Rogō can bulk upload student details and create new accounts from CSV files. The first row should be a header row containing the following fields:';
$string['msg2'] = "The extra fields 'Modules' and 'Session' can be added to enrol the new students on the specified module at the same time.";
$string['loading'] = 'Loading...';
$string['followingerrors'] = 'No users added due to the following errors:';
$string['usersadded'] = 'users added';
$string['usersupdated'] = 'existing users updated';
$string['missingcolumn'] = 'Missing \'%s\' Colum from import please add it.';
$string['finished'] = 'Finished';
$string['loadstudents'] = 'Rogō: Load Students';

$string['emailmsg1'] = 'Create new user account';
$string['emailmsg2'] = 'Dear $title $surname,';
$string['emailmsg3'] = 'A new account has been created to access the online assessment and survey system TouchStone. Your personal authentication details are the same as your university log in details.';
$string['emailmsg4'] = 'Note:';
$string['emailmsg5'] = 'Never share your university username/password with anyone.';
$string['emailmsg6'] = 'Cheating in summative examinations is an academic offence and will not be tolerated.';
$string['emailmsg7'] = 'Could not send mail to <strong>$user_email</strong>.';
?>