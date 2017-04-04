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

require_once '../lang/' . $language . '/include/paper_types.php';
require_once '../lang/' . $language . '/paper/new_paper1.php';
require_once '../lang/' . $language . '/include/months.php';

$string['availability'] = 'Availability';
$string['summativeexamdetails'] = 'Summative Exam Details';
$string['academicsession'] = 'Academic Session';
$string['timezone'] = 'Time Zone';
$string['from'] = 'From';
$string['to'] = 'To';
$string['modules'] = 'Module(s)';
$string['finish'] = 'Finish';
$string['msg4'] = 'There are no modules selected. Papers must be assigned to at least one module.';
$string['msg5'] = "The name '%s' is already in use. Please select an alternative paper title.";
$string['msg6'] = 'This is a closed-book examination and students may not refer to any other source or person in taking this paper. No electronic equipment, other than the examination computer, may be used. Dictionaries are <em>not</em> allowed with one exception. Those whose first language is <em>not</em> English may use a standard translation dictionary to translate between that language and English provided that neither language is the subject of this examination. Subject specific translation dictionaries are not permitted. You are not permitted to take any paper or notes out of the examination room during or after the examination. Any rough notes that you make on the paper provided will be collected by staff and destroyed.';

$string['barriersneeded'] = 'Barriers Needed';
$string['duration'] = 'Duration';
$string['daterequired'] = 'Date required';
$string['cohortsize'] = 'Cohort Size';
$string['wholecohort'] = 'whole cohort';
$string['sittings'] = 'Sittings';
$string['campus'] = 'Campus';
$string['notes'] = 'Notes';
$string['hrs'] = 'hrs';
$string['mins'] = 'mins';

$string['msg7'] = 'WARNING: You must specify which date you require the exam to run in.';
$string['msg8'] = 'WARNING: You must specify a duration in minutes that the exam will last.';
$string['msg9'] = 'WARNING: You must specify a size for the cohort.';
$string['msg10'] = 'WARNING: A paper cannot finish before it starts';
$string['na'] = 'N/A';
?>