<?php
// This file is part of Rogō - http://Rogō.org/ heavily based on code original part of Moodle - http://moodle.org
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_imsenterprise', language 'en'.
 *
 * @package    enrol_imsenterprise
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aftersaving...'] = 'Once you have saved your settings, you may wish to';
$string['allowunenrol'] = 'Allow the IMS data to <strong>unenrol</strong> students/teachers';
$string['allowunenrol_desc'] = 'If enabled, course enrolments will be removed when specified in the Enterprise data.';
$string['basicsettings'] = 'Basic settings';
$string['coursesettings'] = 'Module data options';
$string['createschools'] = 'Create new schools if not found in Rogō';
$string['createschools_desc'] = 'If the <org><orgunit> element is present in a course\'s incoming data, its content will be used to specify a school if the module is to be created from scratch. The plugin will NOT re-categorise existing courses.

If no category exists with the desired name, then a hidden category will be created.';
$string['createmodules'] = 'Create new modules if not found in Rogō';
$string['createmodules_desc'] = 'If enabled, the IMS Enterprise enrolment plugin can create new modules for any it finds in the IMS data but not in Rogō\'s database.' ;
$string['createfaculties'] = 'Create new faculties if not found in Rogō';
$string['createprogrammes'] = 'Create new programmes if not found in Rogō';
$string['createprogrammes_desc'] = 'If enabled, the IMS Enterprise enrolment plugin can create new programmes for any it finds in the IMS data but not in Rogō\'s database. ';
$string['createfaculties_desc'] = 'If enabled, the IMS Enterprise enrolment plugin can create new faculties for any it finds in the IMS data but not in Rogō\'s database. ';
$string['createusers'] = 'Create user accounts for users not yet registered in Rogō';
$string['createusers_desc'] = 'IMS Enterprise enrolment data typically describes a set of users. If enabled, accounts can be created for any users not found in Rogō\'s database.

Users are searched for first by their idnumber, and second by their Rogō username. Passwords are not imported by the IMS Enterprise plugin. The use of an authentication plugin is recommended for authenticating users.';
$string['cronfrequency'] = 'Frequency of processing';
$string['default'] = 'Default: ';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['empty'] = 'Empty';
$string['deleteusers'] = 'Delete user accounts when specified in IMS data';
$string['deleteusers_desc'] = 'If enabled, IMS Enterprise enrolment data can specify the deletion of user accounts (if the recstatus flag is set to 3, which represents deletion of an account). As is standard in Rogō, the user record isn\'t actually deleted from Rogō\'s database, but a flag is set to mark the account as deleted.';
$string['doitnow'] = 'perform an IMS Enterprise import right now';
$string['emptyattribute'] = 'Leave it empty';
$string['filelockedmail'] = 'The text file you are using for IMS-file-based enrolments ({$a}) can not be deleted by the cron process.  This usually means the permissions are wrong on it.  Please fix the permissions so that Rogō can delete the file, otherwise it might be processed repeatedly.';
$string['filelockedmailsubject'] = 'Important error: Enrolment file';
$string['fixcasenames'] = 'Change  names to Title Case';
$string['fixcasenames_desc'] = "This would break certain names like: McGuiness, Temple-Nugent, d'Aramitz,
de Porthau";
$string['fixcaseusernames'] = 'Change usernames to lower case';
$string['imstitle'] = 'IMS Enterprise enrolment';
$string['imsrolesdescription'] = 'The IMS Enterprise specification includes 8 distinct role types. Please choose how you want them to be assigned in Rogō, including whether any of them should be ignored.';
$string['imssettings'] = 'IMS Settings';
$string['location'] = 'File location';
$string['logtolocation'] = 'Log file output location (blank for no logging)';
$string['mailadmins'] = 'Notify admin by email';
$string['mailusers'] = 'Notify users by email';
$string['messageprovider:imsenterprise_enrolment'] = 'IMS Enterprise enrolment messages';
$string['manager'] = 'Manager';
$string['miscsettings'] = 'Miscellaneous';
$string['pluginname'] = 'IMS Enterprise file';
$string['pluginname_desc'] = 'This enrolment method will repeatedly check for and process a specially-formatted text file in the location that you specify.  The file must follow the IMS Enterprise specifications containing person, group, and membership XML elements.';
$string['processphoto'] = 'Add user photo data to profile';
$string['processphotowarning'] = 'Warning: Image processing is likely to add a significant burden to the server. You are recommended not to activate this option if large numbers of students are expected to be processed.';
$string['restricttarget'] = 'Only process data if the following target is specified';
$string['restricttarget_desc'] = 'An IMS Enterprise data file could be intended for multiple targets - different LMSes, or different systems within a school/university. It\'s possible to specify in the Enterprise file that the data is intended for one or more named target systems, by naming them in target tags contained within the properties tag.

In general you don\'t need to worry about this. Leave the setting blank and Rogō will always process the data file, no matter whether a target is specified or not. Otherwise, fill in the exact name that will be output inside the target tag.';
$string['role_learner'] = 'Learner  (01)';
$string['role_instructor'] = 'Instructor (02)';
$string['role_contentdeveloper'] = 'Content Developer (03)';
$string['role_member'] = 'Member (04)';
$string['role_manager'] = 'Manager (05)';
$string['role_mentor'] = 'Mentor (06)';
$string['role_administrator'] = 'Administrator (07)';
$string['role_teachingassistant'] = 'Teaching Assistant (08)';
$string['settingfullname'] = 'IMS description tag for the module full name';
$string['settingfullnamedescription'] = 'The full name is a required course field so you have to define the selected description tag in your IMS Enterprise file';
$string['settingmoduleid'] = 'IMS tag for the module code';
$string['settingmoduleiddescription'] = 'The short name is a required course field so you have to define the selected description tag in your IMS Enterprise file';
$string['sourcedidfailback'] = 'Use the &quot;sourcedid&quot; for a person\'s userid if the &quot;userid&quot; field is not found';
$string['sourcedidfailback_desc'] = 'In IMS data, the <sourcedid> field represents the persistent ID code for a person as used in the source system. The <userid> field is a separate field which should contain the ID code used by the user when logging in. In many cases these two codes may be the same - but not always.

Some student information systems fail to output the <userid> field. If this is the case, you should enable this setting to allow for using the <sourcedid> as the Rogō user ID. Otherwise, leave this setting disabled.';
$string['student'] = 'Student';
$string['teacher'] = 'Teacher';
$string['noneditingteacher'] = 'Non-editing teacher';
$string['short'] = 'short';
$string['long'] = 'long';
$string['full'] = 'full';
$string['coursecode'] = 'coursecode';
$string['truncatemodulecodes'] = 'Truncate module codes to this length';
$string['truncatemodulecodes_desc'] = 'In some situations you may have module codes which you wish to truncate to a specified length before processing. If so, enter the number of characters in this box. Otherwise, leave the box blank and no truncation will occur.';
$string['usecapitafix'] = 'Tick this box if using &quot;Capita&quot; (their XML format is slightly wrong)';
$string['usecapitafix_desc'] = 'The student data system produced by Capita has been found to have one slight error in its XML output. If you are using Capita you should enable this setting - otherwise leave it un-ticked.';
$string['usersettings'] = 'User data options';
$string['zeroisnotruncation'] = '0 indicates no truncation';
$string['roles'] = 'Roles';
$string['ignore'] = 'Ignore';
$string['importimsfile'] = 'Import IMS Enterprise file';
$string['zero'] = '0';
$string['staff'] = 'Staff';
$string['invigilator'] = 'Invigilator';
$string['externalexaminer'] = 'External examiner';
$string['orgname'] = 'orgname tag';
$string['orgunit'] = 'orgunit tag';
$string['relationship'] = 'Lookup from relationship';
$string['facultysource'] = 'Source of Faculty name';
$string['facultysource_desc'] = 'The XML tag from which to derive the Faculty name';
$string['schoolsource'] = 'Source of School name';
$string['schoolsource_desc'] = 'The XML tag from which to derive the School name';
$string['programmesource'] = 'Source of Programme name';
$string['programmesource_desc'] = 'The XML tag from which to derive the Programme name';
$string['validatexml'] = 'Validate the XML file against your local DTD file';
$string['validatexml_desc'] = 'Whether to validate the xml against the dtd file'
    . ' (Your local DTD file must be in the same directory as the XML file and contain all proprietary definitions) '
    . 'The DTD file name will need to match the DTD file specified in the XML file';
