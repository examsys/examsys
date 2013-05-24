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

require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/include/paper_types.inc';

$string['propertiestitle'] = 'Properties';
$string['edittitle'] = 'Edit';
$string['warning'] = 'Warning paper name already used by another assessment!';
$string['availablefromyear'] = 'The available from year is greater than the available to year - this is illogical!';
$string['availablefrommonth'] = 'The available from month is greater than the available to month - this is illogical!';
$string['availablefromday'] = 'The available from day is greater than the available to day - this is illogical!';
$string['msg1'] = 'There are no modules selected. Papers must be assigned to at least one module.';
$string['msg2'] = 'Summative paper start and end dates must be on the same day (\'Access Rights\' tab).';
$string['msg3'] = 'You must specify a duration for Summative exams.\nThis should be normal duration excluding any extra time for dyslexic students.';
$string['msg4'] = 'You must specify an academic year for Summative exams (\'Access Rights\' tab).';
$string['msg5'] = 'OSCEs must be assigned to at least one module.';
$string['msg6a'] = 'You have set some internal reviewers but not specified a deadline.';
$string['msg6'] = 'You have set some external examiners but not specified a deadline.';
$string['msg7'] = 'Please enter a name for the Paper.';
$string['msg8'] = 'This is a closed-book examination and students may not refer to any other source or person in taking this paper. No electronic equipment, other than the examination computer, may be used. Dictionaries are <em>not</em> allowed with one exception. Those whose first language is <em>not</em> English may use a standard translation dictionary to translate between that language and English provided that neither language is the subject of this examination. Subject specific translation dictionaries are not permitted. You are not permitted to take any paper or notes out of the examination room during or after the examination. Any rough notes that you make on the paper provided will be collected by staff and destroyed.';

// General tab
$string['generaltab'] = 'General';
$string['generalheading'] = 'Paper name, marking and display options';
$string['paperdetails'] = 'Paper Details';
$string['onlyonexamday'] = '(only on exam day)';
$string['url'] = 'URL';
$string['name'] = 'Name';
$string['type'] = 'Type';
$string['folder'] = 'Folder';
$string['feedback'] = 'Feedback';
$string['displayoptions'] = 'Display Options';
$string['display'] = 'Display';
$string['windowed'] = 'Windowed';
$string['fullscreen'] = 'Full Screen (IE only)';
$string['navigation'] = 'Navigation';
$string['bidirectional'] = 'Bidirectional';
$string['unidirectional'] = 'Unidirectional (linear)';
$string['background'] = 'Background';
$string['foreground'] = 'Foreground';
$string['theme'] = 'Theme';
$string['labelsnotes'] = 'Labels/Notes';
$string['calculator'] = 'Calculator';
$string['displaycalculator'] = 'display calculator';
$string['audio'] = 'Audio';
$string['demosoundclip'] = 'demo sound clip';
$string['marking'] = 'Marking';
$string['overallclassification'] = 'Overall Classification';
$string['markingguidance'] = 'Examiner Marking Guidance';
$string['overallclass1'] = '&lt;Automatic&gt;';
$string['overallclass2'] = 'Clear Fail | Borderline | Clear Pass';
$string['overallclass3'] = 'Fail | Borderline fail | Borderline pass | Pass | Good pass';
$string['overallclass4'] = 'Clear FAIL | BORDERLINE | Clear PASS | Honours PASS';
$string['passmark'] = 'Pass Mark';
$string['distinction'] = 'Distinction';
$string['method'] = 'Method';
$string['noadjustment'] = 'No Adjustment';
$string['calculatrrandommark'] = 'Calculate Random Mark';
$string['stdset'] = 'Std Set';
$string['ticks_crosses'] = 'Ticks/Crosses';
$string['question_marks'] = 'Question Marks';
$string['hideallfeedback'] = 'Hide all feedback if<br />unanswered';
$string['correctanswerhighlight'] = 'Correct Answer Highlight';
$string['textfeedback'] = 'Text Feedback';
$string['photos'] = 'Photos';
$string['ifavailable'] = 'if available';
$string['review'] = 'Review';
$string['allpeerspergroup'] = 'All peers per group';
$string['singlereview'] = 'Single Review';
$string['numberfrom'] = 'Number from';
$string['groupdetails'] = 'Group Details';

// Security tab
$string['securitytab'] = 'Security';
$string['securityheading'] = 'Control the access rights over which students can see the paper.';
$string['session'] = 'Session';
$string['password'] = 'Password';
$string['timezone'] = 'Time Zone';
$string['modules'] = 'Modules';
$string['duration'] = 'Duration';
$string['mins'] = 'mins';
$string['availablefrom'] = 'Available from';
$string['to'] = 'to';
$string['restricttolabs'] = 'Restrict to Labs';
$string['restricttometadata'] = 'Restrict to Metadata';
$string['na'] = 'N/A';

// Reviewers tab
$string['reviewerstab'] = 'Reviewers';
$string['reviewersheading'] = 'Set internal/external reviewers and deadlines.';
$string['internalreviewers'] = 'Internal Reviewers';
$string['externalexaminers'] = 'External Examiners';
$string['deadline'] = 'Deadline:';

// Exam Rubric tab
$string['rubrictab'] = 'Exam Rubric';
$string['rubricheading'] = 'Exam rubric displayed to students before they start a summative exam.';

// Prologue tab
$string['prologuetab'] = 'Prologue';
$string['prologueheading'] = 'Text displayed at the top of screen 1 when paper is started.';

// Postscript tab
$string['postscripttab'] = 'Postscript';
$string['postscriptheading'] = "Text displayed after the student clicks 'Finish' at the end.";

// Reference Material tab
$string['referencematerial'] = 'Reference Material';
$string['referenceheading'] = 'Control which reference materials are available to the paper.';
$string['nomaterials'] = 'There are no reference materials available for the module(s) assigned to this paper.<br /><br />Reference material can be added by clicking the \'Reference Material\' option from a module screen (<a href="" style="color:blue" onclick="launchHelp(296); return false;">see help</a>).';

// Feedback tab
$string['feedbackheading'] = 'Feedback available to students';
$string['feedbackwarning'] = '<strong>Note:</strong> This will release questions including the correct answers and marks to students.';
$string['on'] = 'On';
$string['off'] = 'Off';
$string['objectivesreport'] = 'Objectives-based Feedback';
$string['questionfeedback'] = 'Question-based Feedback';
$string['textualfeedback'] = 'Textual Feedback';
$string['above'] = 'Above';
$string['message'] = 'Message';
$string['answerscreensettings'] = 'Answer Screen Settings';

// Changes tab
$string['changes'] = 'Changes';
$string['changesheading'] = 'List of changes to the current paper.';
$string['part'] = 'Part';
$string['old'] = 'Old';
$string['new'] = 'New';
$string['date'] = 'Date';
$string['author'] = 'Author';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['retired'] = 'Retired';
$string['externalreviewdeadline'] = 'External Review Deadline';
$string['internalreviewdeadline'] = 'Internal Review Deadline';

//Colour picker
$string['colour'] = 'Colour';
$string['themecolours'] = 'Theme Colours';
$string['standardcolours'] = 'Standard Colours';
$string['more'] = 'More...';
$string['cancel'] = 'Cancel';
$string['OK'] = 'OK';


?>