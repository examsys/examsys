<?php
// This file is part of Rogo
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

$string['title'] = 'Configuration Settings';
$string['summative_cohort_sizes'] = 'Cohort ranges available when scheduling a summative exam.';
$string['paper_max_duration'] = 'Max time (in minutes) any exam can be set to last for.';
$string['summative_max_sittings'] = 'Max number of sittings a summative exam can be scheduled to require.';
$string['summative_hide_external'] = 'Hide the option of creating summative exams in the user interface. As these will be created by an external system.';
$string['summative_warn_external'] = 'Warn users adding a summative exam in the user interface about the possiblity that an external system may be creating summative assessments.';
$string['paper_timezones'] = 'The time zones that any exam can be run in.';
$string['configblurb'] = 'On this page you can alter the default configuration of Rogō. It is advised that you read all available documentation before making your instance of Rogō a non-default install.';
$string['fileoverride'] = 'This configuration setting is being superseded by the default configuration file';
$string['lti_auth_timeout'] = 'Time in ms that an LTI login is valid for.';
$string['lti_integration'] = 'The LTI integration type in use.';
$string['cfg_lti_allow_staff_module_register'] = 'Allows Rogō to register staff onto the module team if set to true and from LTI launch and staff in VLE.';
$string['cfg_lti_allow_module_self_reg'] = 'Allows Rogō to auto add student to module if selfreg is set for module if from LTI launch.';
$string['cfg_lti_allow_module_create'] = 'Allows rogo to create module if it doesnt exist.';
$string['cfg_cmap_url'] = 'Base URL for curriculum mapping web service.';
$string['cfg_nle_url'] = 'Base URL for NLE web service.';
$string['cfg_moodle_base_url'] = 'Base URL for Moodle website.';
$string['cfg_sms_url'] = 'Base URL of student management system (old style pre plugins).';
$string['cfg_gradebook_enabled'] = 'Enable/Disable Gradebook functionality';
$string['cfg_api_enabled'] = 'Enable/Disable API functionality';
$string['api'] = ' API';
$string['gradebook'] = 'Gradebook';
$string['lti'] = 'LTI Integration';
$string['paper'] = 'All Assessments';
$string['summative'] = 'Summative Assessments';
$string['url'] = 'External System URLs';
$string['misc'] = 'Miscellaneous';
$string['paper_marks_postive'] = 'Drop down options for postive marks available option';
$string['paper_marks_negative'] = 'Drop down options for negative marks available option';
$string['paper_marks_partial'] = 'Drop down options for partial marks available option';
$string['paper_mathjax'] = 'Enable/Disable mathjax rendering in papers';
$string['misc_logo_main'] = 'Logo used in Rogō.';
$string['misc_logo_email'] = 'Logo used in emails.';
$string['api_allow_superuser'] = 'Enable/Disable API super users. These users can call the API for any external system.';
$string['apilogfile'] = 'Location of api log file. Leave blank to disable logging.';
$string['misc_company'] = 'Organisations name.';
$string['misc_dictionary_file'] = 'Dictionary File (if set, enables memorable passwords)';
$string['calc'] = 'Calculation Questions';
$string['cfg_calc_type'] = 'Calculation question integration type (default: phpEval).';
$string['cfg_calc_settings'] = 'Calculation question integration server settings (phpEval: settings not used).';
$string['system'] = 'System settings';
$string['system_maintenance_mode'] = 'Enable/Disable maintenance mode (restricts access to system to sys admin users only)';
$string['cfg_summative_mgmt'] = 'Enable if summative assessment is managed centrally rather than on an adhoc basis.';
$string['system_hostname_lookup'] = 'If enabled hostname used to lookup clients. IP address is used if disabled.';
$string['system_academic_year_start'] = 'Month / Day of academic year start';
$string['misc_search_leadin_length'] = 'Length in characters of question leadin in question search list.';
$string['rpt'] = 'Reports';
$string['rpt_percent_decimals'] = 'Decimal places to display in reports.';
$string['stdset'] = 'Standard setting';
$string['stdset_hofstee_pass'] = 'Default Hofstee pass settings';
$string['stdset_hofstee_distinction'] = 'Default Hofstee distinction settings';
$string['stdset_hofstee_whole_numbers'] = 'Default setting of whole number setting in Hofstee configuration.';
$string['stdset_copy_std_setting'] = 'Copy standard settings when duplicating paper and questions by default';
$string['summative_hour_warning'] = 'Hour in day (24hr clock) to warning users if they are setting an exam to start prior to.';
$string['system_install_type'] = 'Installation type i.e. test (Leave blank for production environments).';
$string['ims'] = 'IMS';
$string['cfg_ims_enabled'] = 'Enable/Disable IMS integration.';
$string['imssettings'] = 'More detailed settings are available ';
$string['contact'] = 'Support Contacts';
$string['emergency_support_contact1'] = 'Details for support contact 1';
$string['emergency_support_contact2'] = 'Details for support contact 2';
$string['emergency_support_contact3'] = 'Details for support contact 3';
$string['support_contact_email'] = 'Support email addresses (Comma seperated list)';
$string['api_oauth_access_lifetime'] = 'Length of access token lifetime (Seconds)';
$string['api_oauth_refresh_token_lifetime'] = 'Length of refresh token lifetime (Seconds)';
$string['api_oauth_always_issue_new_refresh_token'] = 'Enable/Disableisable refresh tokens';
$string['paper_autosave_settimeout'] = 'Maximum time to wait for one paper auto save request to succeed (Seconds)';
$string['paper_autosave_frequency'] = 'How often to auto save a paper (Seconds)';
$string['paper_autosave_retrylimit'] = 'How many times to retry a failed paper auto save before informing the user';
$string['paper_autosave_backoff_factor'] = 'Backoff factor used in paper auto saving calculation.';
$string['summative_midexam_clarification'] = 'Roles that can view mid exam clarifcations during summative exams';
$string['system_password_expire'] = 'Internal database login password lifetime (Days)';
$string['lti_ssl_verifypeer'] = 'Perform ssl peer verification';
$string['lti_ssl_verifyhost'] = 'Perform ssl host verification. 0 disables, 1 is deprecated and should not be used, 2 enables.';
$string['paper_mee'] = 'Enable/Disable deprecated mee maths rendering.';
$string['system_mediatypes'] = 'Media types that can be uploaded to the system.';
$string['paper_threejs'] = 'Enable/Disable deprecated threejs rendering.';
$string['system_maxmediasize'] = 'Maximum size of media file that can be uploaded (in bytes).';
$string['paper_textbox_editor_default'] = 'Default editor used for textbox questions';
$string['editor_plaintext'] = 'Plain text editor';
$string['editor_wysiwyg'] = 'WYSIWYG editor';
$string['editor_mathjax'] = 'MathJax editor';
$string['paper_hide_repeat_scenario'] = 'Hide repeat scenarios on papers (>95% similarity)';
$string['summative_remote'] = 'Enable/Disable remote summative exam functionality.';
$string['summative_issuelink'] = 'Link to issue reporting site for remote summative exams on the paper index screen.';
$string['summative_issuelink2'] = 'Link to issue reporting site for remote summative exams on the paper finish screen.';
$string['paper_types'] = 'Enable/Disable paper types from being available';
$string['paper_breaktime_scale'] = 'Scale for additional break time availble to students during exams.';
$string['paper_breaktime_mins'] = 'If selected scale values are minutes per hour, otherwise they are a percentage of the total exam time.';
$string['paper_pause_exam'] = 'Enable/Disable pausing of remote summative exams for students with accomodations.';
$string['paper_seb_enabled'] = 'Safe Exam Browser key support enabled';
