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

$string['administrativetools'] = 'Administrative Tools';
$string['helpsupport'] = 'Help &amp; Support';
$string['aboutrogo'] = 'About Rog&#333; %s';
$string['html5warn'] = 'Rog&#333; needs HTML5 support, please upgrade your browser to a newer version.';
$string['furtherassistance'] = 'For further assistance contact: <a href="mailto:%s">%s</a>';
$string['pagenotfound'] = 'Page not Found';
$string['signinmsg'] = 'The page you are trying to access requires authentication. Please sign in using your username and password:';
$string['username'] = 'Username';
$string['password'] = 'Password';
$string['signin'] = 'Sign In';
$string['signout'] = 'Sign Out';
$string['home'] = 'Home';
$string['help'] = 'Help';
$string['line'] = 'Line';
$string['cancel'] = 'Cancel';
$string['ok'] = 'OK';
$string['save'] = 'Save';
$string['colours'] = 'Colours';
$string['new'] = 'New';
$string['old'] = 'Old';
$string['Objectives Feedback report'] = 'Objectives Feedback report';
$string['Questions Feedback report'] = 'Questions Feedback report';
$string['accessdenied'] = 'Access Denied';
$string['accessdeniedadmin'] = '<strong>Access Denied</strong><br />you are attempting to access a admin only screen.';
$string['accessdeniedsysadmin'] = '<strong>Access Denied</strong><br />you are attempting to access a system admin only screen.';
$string['denied_paper'] = 'You are attempting to access a <strong>paper you do not have rights to</strong>!';
$string['denied_folder'] = 'You are attempting to access a <strong>folder you do not have rights to</strong>!';
$string['denied_question'] = 'You are attempting to access a <strong>question you do not have rights to</strong>!';
$string['denied_team'] = 'You are not a member of the <strong>%s</strong> module team.';
$string['noldapusernamesupplied'] = 'No LDAP username or password supplied';
$string['incorrectpassword'] = 'Incorrect password provided';
$string['ldapserverunavailable'] = 'LDAP Server Unavalable';
$string['noldapaccount'] = 'No LDAP account found!';
$string['ldapservernosearch'] = 'LDAP Server Unavailable: unable to search';
$string['nodatabaseconnection'] = 'Could not connect to the database please contact your administrator';
$string['as'] = 'as';
$string['localtsauthfailed'] = 'Local Rog&#333; authentication failed.';
$string['notsaccount'] = 'No Rog&#333; account found!';
$string['tempaccount'] = 'Temporary Account';
$string['authenticationfailed'] = 'Authentication with Rogō has failed, please remember:';
$string['usernamecasesensitive'] = 'Usernames/passwords are case-sensitive';
$string['pressf5'] = "Press 'F5' on the keyboard to try logging in again";
$string['tsonldap'] = 'Rogō uses the LDAP passwords';
$string['ifstuckinvigilator'] = 'If you are still stuck ask an invigilator for a';
$string['forgottenpassword'] = 'Forgotten Password';
$string['warning'] = 'Warning';
$string['secureconnection'] = 'Secure Connection Required';
$string['secureconnectionmsg'] = 'Rog&#333; can only be accessed through a secure web connection. Instead use:';
$string['errormsg'] = 'If you repeatedly get this error please contact:';
$string['ltifirstlogin'] = 'Rog&#333; Authorisation';
$string['ltifirstlogindesc'] = 'Rogō e-assessment system needs to confirm your identity to ensure you have the correct permissions.  This will only happen on your first visit and at infrequent intervals afterwards to ensure security.<br /><br /><input type="button" value=" Log In " onclick="window.location=\'./\'" />';
$string['ltinotconfigured'] = 'The link you have followed has not yet been configured.';
$string['LTIFAILURE'] = 'LTI Failure';
$string['expiredtimestamp'] = 'Timestamp has now expired. Please go back and relaunch the link (do not hit refresh in the browser).';
$string['UserMissing'] = 'Associated User Missing';
$string['UserMissingDesc'] = 'Somehow the associated user is missing from the rogo user system this is a serious error and you need to contact support';
$string['impersonatepriv'] = 'You do not have sufficient privileges to impersonate users.';

$string['NoAuthenticationConfigured'] = 'No Authentication configured';
$string['NoAuthenticationConfiguredmessage'] = 'No Authentication configuration has been set in the config file. Please contact your local system administrator.';
$string['Authentication_callback_failure1'] = 'Failed to register callback for section ';
$string['Authentication_callback_failure2'] = ' with function';
$string['Authentication_issue1'] = 'Authentication Issue';
$string['Authentication_issue2'] = 'The authentication plugins couldnt log you in and, they the plugins didnt provide any further form or redirect. Press F5 to refresh if this is still unsuccessful please contact support: <a href="mailto:%s">%s</a>.<p>Please include the following debug in your email:</p><div style="margin-left:100px;">%s</div>';
$string['Authentication_issue2nodebug'] = 'The authentication plugins couldnt log you in and, they the plugins didnt provide any further form or redirect. Press F5 to refresh if this is still unsuccessful please contact support: <a href="mailto:%s">%s</a>.';
$string['Authentication_notloggedin1'] = 'Authentication Issue';
$string['Authentication_notloggedin2'] = 'You are not logged in. Press F5 to refresh if this is still unsuccessful please contact support: <a href="mailto:%s">%s</a><p>Please include the following debug in your email:</p><div style="margin-left:100px;">%s</div>';
$string['Authentication_notloggedin2nodebug'] = 'You are not logged in. Press F5 to refresh if this is still unsuccessful please contact support: <a href="mailto:%s">%s</a>';
$string['NoLookupConfigured'] = 'No lookup configured';
$string['NoLookupConfiguredmessage'] = 'No lookup configuration has been set in the config file. Please contact your local system administrator.';
$string['guestbutton'] = 'Guest Login';
$string['authentication_lti_authmessage'] = 'Please Login to authenticate the LTI Connection.';
$string['authentication_lti_reauthmessage'] = 'Please Login to re-authenticate the LTI Connection.';
$string['lti_not_allow_add_selfreg'] = 'Automatic adding of students on this type of module hasnt been enabled in the config for module: ';

$string['denied_role'] = 'Your user role of %s does not allow you access.';
$string['Error'] = 'Error';
$string['NoAcademicSession'] = 'No Academic Session is set.';
$string['NoFieldMetadata'] = 'No field in the metadata set for groups.';
$string['NoGroup'] = 'No Group can be found for the current user.';
$string['note'] = 'Note';
$string['studentname'] = 'Student Name';
$string['loggedinas'] = 'Logged in as';
$string['crondisallowed'] = 'cron attempted to run disallowed script';
$string['register'] = 'Register as Staff User';
$string['register1'] = 'Register as ';
$string['demomodule'] = 'Demo Module';
$string['name'] = 'Name';
$string['eassessmentmanagementsystem'] = 'eAssessment Management System';
?>
