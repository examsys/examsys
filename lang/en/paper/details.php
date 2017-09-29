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

require '../lang/' . $language . '/include/paper_options.php';
require '../lang/' . $language . '/include/months.php';
require '../lang/' . $language . '/paper/new_paper2.php';

$configObject = Config::get_instance();

$string['start'] = 'Start';
$string['owner'] = 'Owner';
$string['question'] = 'Question';
$string['type'] = 'Type';
$string['marks'] = 'Marks';
$string['modified'] = 'Modified';
$string['passmark'] = 'Pass Mark';
$string['randommark'] = 'random mark';
$string['screen'] = 'Screen';
$string['paperpublishedwarning'] = '<strong>Paper grades have been published.</strong>&nbsp;&nbsp;&nbsp;Marking adjustment can no longer occur.';
$string['paperlockedwarning'] = '<strong>Paper Locked</strong>&nbsp;&nbsp;&nbsp;This paper is now locked and cannot be modified.';
$string['paperlockedclick'] ='Click for more details';
$string['earlywarning'] = '<strong>Time/Date Warning</strong>&nbsp;&nbsp;&nbsp;This paper is scheduled to start before %sam';
$string['farfuturewarning'] = '<strong>Time/Date Warning</strong>&nbsp;&nbsp;&nbsp;This paper is scheduled for a long way in the future (%s)';
$string['nooptionsdefined'] = 'No options defined for question';
$string['noquestionscreen'] = '<strong>Warning:</strong> there are no questions on this screen.<br />This will produce an error if the paper is tested!';
$string['markswarning'] = 'Screen %d has %d marks which is %d%% of the paper total. Please insert additional screen breaks to minimise data loss in the event of a computer crash.';
$string['duplicateoptions'] = 'Duplicate options. MCQ options must be unique.';
$string['nocorrect'] = 'No correct answer specified';
$string['zeromarks'] = 'Warning zero marks set.';
$string['toomanycorrect'] = 'Too many correct options';
$string['mismatchbrackets'] = 'Mismatching brackets found.';
$string['mismatchblanktags'] = 'Mismatching opening/closing [blank] tags.';
$string['answermissing'] = 'Correct answer missing for some options.';
$string['nolabels'] = 'No labels added to image.';
$string['mcqsurvey'] = "MCQ with 'other' should only be used on surveys";
$string['dichotomouswarning'] = '%d out of %d';
$string['warning'] = 'Warning';
$string['Duplicate questions'] = 'Duplicate questions';
$string['following_questions'] = 'The following questions are';
$string['variablenomarks'] = 'Warning: Variable number of marks';
$string['paperdeleted'] = 'Paper Deleted';
$string['deleted_msg1'] = 'Paper <strong>%s</strong> has been deleted.';
$string['deleted_msg2'] = 'It can still be recovered from the <a href="' . $configObject->get('cfg_root_path') . '/delete/recycle_list.php" style="color:blue">recycle bin</a>.';
$string['deleted_msg3'] = 'You do not own this paper, you will need to get <a href="mailto:%s" style="color:blue">%s %s</a> to recover it.';
$string['addscreenbreak'] = 'Add screen break';
$string['deletescreenbreak'] = 'Delete screen break';
$string['next'] = 'Next >>';
$string['na'] = 'N/A';
$string['nomatchsession'] = 'The session in the paper title (%s) does not match the paper session (%s).';
$string['notsummativeexams'] = 'Should not use with Summative Exams';
$string['problemwithpaper'] = 'There was a problem loading the paper';
$string['warning'] = 'Warning';
$string['overall_classification_warning'] = 'Please don\'t forget to set the overall classification for this test: ';
$string['clickhere'] = 'Click here to set the overall classification';
?>
