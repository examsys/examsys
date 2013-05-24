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

require '../lang/' . $language . '/include/paper_options.inc';
require 'shared.inc';
require '../lang/' . $language . '/question/edit/likert_scales.php';

$string['import'] = 'Import';
$string['import2'] = 'Import';
$string['importfromqti'] = 'Import from QTI';
$string['file'] = 'File';
$string['qtiimporterror'] = 'There has been an error importing your QTI file';
$string['qtiimported'] = 'Your QTI file has been imported';
$string['questionproblems'] = 'Some of your questions did not import correctly.';
$string['hadproblemsimporting'] = '%d out of %d questions had problems importing.';
$string['importedquestions'] = 'Imported %d question(s).';
$string['backtopaper'] = 'Back to paper';
$string['errmsg1'] = 'This export type is not supported';
$string['errmsg2'] = 'This import type is not supported';
$string['invalidxml'] = '%s is an invalid XML file';
$string['invalidzip'] = 'Invalid Zip file Uploaded';
//$string['invalidzip2'] = '%s is an invalid XML file';
$string['noqtiinzip'] = 'No QTI XML files in the zip file';
$string['qunsupported'] = 'Question type %s not yet supported';
$string['noresponsegroups'] = 'Response groups are not currently supported.';
$string['norenderextensions'] = 'Render extensions are not currently supported.';
$string['mrq1other'] = 'Multiple Response - 1 mark per True Option with Other';
$string['nomultiplecard'] = 'All sets of labels are different and we have multiple cardinality, question is not supported in Rog&#333;.';
$string['labelsetserror'] = 'Label sets for all question stems arent the same, prehaps this should be imprted as a blank with dropdowns??';
$string['nomultiinputs'] = 'Questions with multiple numeric imputs cannot be imported';
$string['blanktypeerror'] = 'Blank type question with not dropdowns or text entries';
$string['addingsub'] = 'Adding sub item - render_fib with no children';
$string['posnocond'] = 'Positive outcome with no condition, unable to work out correct answer';
$string['multiplepos'] = 'Multiple positive values on outcome, correct answer may be wrong';
$string['multiposmultiopt'] = 'Multiple positive outcomes, with multiple options on an outcome, correct answer may be wrong';
$string['nomatchinglabel'] = 'Unable to find label matching information';
$string['nolikertfeedback'] = 'Rog&#333; doesn\'t store any feedback for likert questions so it has been lost';
$string['nocorrect'] = 'Unable to find a correct answer';
$string['multipleconds'] = 'Found multiple conditions that are scoring the question, ignoring all but the 1st';
$string['mrqnoismulti'] = 'Trying to load MRQ without ismulti set!';
$string['importingtext'] = 'Importing text entry question with marking criteria. This will not be automatically marked in Rog&#333;';
$string['someneg'] = 'Some negatives - 1 mark per true option with negative';
$string['noneg'] = 'No negatives and multiple positives, 1 mark per true option';

$string['qtiimport'] = 'QTI Import';
$string['imported1_2'] = 'Imported from QTI 1.2 file';
$string['paperlocked'] = 'Paper Locked';
$string['paperlockedmsg'] = 'This paper is now locked and cannot be modified.';

$string['loadingsection'] = 'Loading section';
$string['loadingblank'] = 'Loading blank string';
$string['loadingblankdrop'] = 'Loading blank dropdown';
$string['fileoutput'] = 'File Output';

$string['type'] = 'Paper Type';
?>