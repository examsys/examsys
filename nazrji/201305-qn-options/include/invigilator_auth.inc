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

/**
* 
* Authentication routine which permits SysAdmin,Admin and Invigilator only access.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once 'staff_student_auth.inc';

if (!$userObject->has_role(array('SysAdmin', 'Admin', 'Invigilator'))) {
  $notice->access_denied($mysqli, $string, 'You are attempting to access a <strong>Invigilator Only</strong> page.', false, true);
} 
?>