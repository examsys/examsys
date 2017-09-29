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

include 'lti_common.php';

$string['NoPapers'] = 'No Papers for this module';
$string['NoPapersDesc'] = 'There are no papers available for this module.  This is probably because you have just created a new link from the VLE with a new module and therefore havent got any papers currently configured.<br /><br />Please <a href="../" target="_blank">launch Rogo</a> to create a paper'; //close the browser (<strong>very important</strong>) and then go to the Rogo homepage and create a paper.

$string['NoModCreateTitle'] = 'Module Creation is not enabled';
$string['NoModCreate'] = 'Module Creation from LTI is not enabled in the config so we cannot create the module with course code: ';
$string['NotAddedToModuleTitle'] = 'Not Added to Module Team';
$string['NotAddedToModule'] = 'Adding to Module Team is not enabled in the config for LTI, this happened for module: ';

$string['NoModCreateTitle2'] = 'Module Creation not run';
$string['NoModCreate2'] = 'Module Creation from LTI has not run as the user does not have permission, so we cannot create the module with course code: ';
$string['moduletranslateerror'] = 'Module code error';
$string['moduletranslatemessage'] = 'There is a problem with the module code as the translation code has resulted in an error. Please contact Learning Team Support <a href="mailto:%s">%s</a>';
$string['moduletranslatecode'] = '<p>Incoming Module Code: %s</p>';
$string['modulecreateerror'] = 'Module creation error';
$string['modulecreatemessage'] = 'Modules cannot be created that do not exist within the student management system. For further assistance contact: <a href="mailto:%s">%s</a>';
$string['redirectmessage'] = 'Please click <a href="%s">here</a> to continue';
$string['modulescaption'] = 'Module(s)';
?>