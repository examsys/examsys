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

//HTML5 part
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/question/edit/hotspot_correct.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/question/edit/area.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/paper/hotspot_answer.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/paper/hotspot_question.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/paper/label_answer.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/include/months.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/question/sct_shared.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lang/' . $language . '/include/paper_security.php';

$string['survey'] = 'Survey';
$string['assessment'] = 'Assessment';
$string['finish'] = 'Finish';
$string['screen'] = 'Screen';
$string['clarificationscreen'] = 'Screen %s of %s';
$string['mark'] = 'mark';
$string['marks'] = 'marks';
$string['note'] = 'Note';
$string['true'] = 'True';
$string['false'] = 'False';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['abstain'] = '<Abstain>';
$string['na'] = 'N/A';
$string['other'] = 'Other';
$string['unanswered'] = 'Unanswered';
$string['unansweredquestion'] = '= unanswered question';
$string['negmarking'] = 'negative marking';
$string['bonusmark'] = 'for correct options, plus %d bonus %s for fully correct order';
$string['calculator'] = 'Calculator';
$string['timeremaining'] = 'Time remaining';
$string['finishnote'] = 'Complete all questions before clicking "Finish", you will not be able to go back.';
$string['gobackpink'] = 'When you go back unanswered questions will be highlighted in pink.';
$string['fireexit'] = 'Fire Exit';
$string['pleasecomplete'] = 'Complete all questions before clicking "Screen %d >", you will not be able to go back.';
$string['javacheck1'] = 'Have you completed all the questions on this screen, you will NOT be able to go back.<br /><br /><strong>Are you sure you wish to continue?</strong>';
$string['javacheck2'] = "Are you sure you wish to finish?<br /><br /><strong>After clicking 'OK' you will not be able to go back.</strong>";
$string['javacheck3'] = 'Have you completed all the questions on this screen, you will NOT be able to go back.<br /><br /><strong>The answer to question(s) [X] should be provided as it forms the basis of a subsequent question.</strong>';
$string['error_random'] = '<strong>ERROR:</strong> Unable to find unique question for random question block.';
$string['error_keywords'] = '<strong>ERROR:</strong> Unable to find unique question for supplied keywords.';
$string['error_paper'] = 'The requested paper cannot be found.';
$string['error_qtype'] = 'No question type defined.';
$string['holddownctrlkey'] = '(Hold down <CTRL> key, then click mouse to toggle options on/off)';
$string['msgselectable1'] = 'Too many options selected!\n\nOnly';
$string['msgselectable2'] = 'items can be selected in this question.';
$string['msgselectable3'] = 'You have already selected';
$string['msgselectable4'] = '.\n\nPlease select a different ranking.';
// Ajax failed save message
$string['savefailed'] = 'Save Failed!';
$string['tryagain'] = 'If the problem persists please inform an invigilator / administrator.';
$string['questionclarification'] = 'Question Clarification';
$string['question'] = 'Question';
$string['answer_to'] = 'answer to';
$string['decimal_places'] = 'decimal places';
$string['significant_figures'] = 'significant figures';
$string['forcesave'] = 'Your time has expired and your answers have been saved';
$string['failedanswer'] = 'You failed to answer question %s, this provides the value required for this question. This question will also be treated as if you failed to answer it.';
$string['answerrequired'] = 'The answer to this question(s) [X] should be provided as it forms the basis of a subsequent question. If you cannot answer the question then you have the option to pass. Any subsequent dependent questions will be treated as if you failed to answer them.';
$string['answerrequired_confirm'] = 'Do you still wish to pass this question or go back and answer it?';
$string['go_back'] = 'Go back';
$string['pass'] = 'Pass';
$string['entervalidcalcanswer'] = 'Invalid: Answer should be numerical (with a unit if required), or left blank if you are skipping the question.';
$string['papercopyright'] = '© %s, %s';
// Media.
$string['iframes'] = 'Your browser does not support iframes!';
$string['image'] = 'Image';
$string['audiofile'] = 'Audio File';
$string['datafile'] = 'Data File';
$string['delete'] = 'Delete';
$string['deleteimage'] = 'Delete image';
$string['audioclip'] = 'Audio Clip';
$string['questionmark'] = '?';
$string['cancel'] = 'Cancel';
$string['ok'] = 'OK';
$string['goback'] = 'Go back';
$string['pass'] = 'Pass';
$string['previousscreen'] = '< Screen %s'; 
$string['nextscreen'] = 'Screen %s >';
$string['threeinfo'] = 'Hold the left mouse button to rotate the object, hold the right button to pan, and use the mouse wheel to zoom in/out.';
$string['threereset'] = 'Reset';
$string['threeload'] = 'Load';
$string['threeplyerror'] = 'Your browser does not support the display of %s files.';
