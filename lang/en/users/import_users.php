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

require '../lang/' . $language . '/include/user_search_options.php';

$string['sendwelcomeemail'] = 'Send welcome email to user';
$string['csvfile'] = 'CSV File:';
$string['specifyfile'] = 'Please specify a file for upload.';
$string['import'] = 'Import';
$string['msg1'] = 'New users accounts (staff or student) can be created from CSV files. The first row should be a header row containing the following fields:';
$string['msg2'] = "The extra fields 'Modules' and 'Session' can be added to enrol the new students on the specified module at the same time. Also, 'Username' and 'Password' may be specified if a central authentication system is not used.";
$string['loading'] = 'Loading...';
$string['followingerrors'] = 'No users added due to the following errors:';
$string['usersadded'] = 'users added';
$string['usersupdated'] = 'existing users updated';
$string['missingcolumn'] = 'Missing \'%s\' Colum from import please add it.';
$string['finished'] = 'Finished';
$string['newrogoaccount'] = 'New ExamSys Account';
$string['emailmsg1'] = 'Create new user account';
$string['emailmsg2'] = 'Dear';
$string['emailmsg3'] = 'A new account has been created to access the online assessment and survey system ExamSys. Your personal authentication details are the same as your university log in details.';
$string['emailmsg4'] = 'Note:';
$string['emailmsg5'] = 'Never share your university username/password with anyone.';
$string['emailmsg6'] = 'Cheating in summative examinations is an academic offence and will not be tolerated.';
$string['emailmsg7'] = 'Could not send mail to.';
