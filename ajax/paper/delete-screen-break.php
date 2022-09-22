<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Re-order questions based on AJAX call from drag and drop list from paper.details.php
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../../include/staff_auth.inc';

if (isset($_GET['paperID']) and $_GET['paperID'] != '' and isset($_GET['screen']) and is_numeric($_GET['screen'])) {
    $paper_id = $_GET['paperID'];
    $screen_no = $_GET['screen'];

    $result = $mysqli->prepare('UPDATE papers SET screen=screen-1 WHERE paper=? AND screen>=?');
    $result->bind_param('ii', $paper_id, $screen_no);
    $result->execute();
    $result->close();

    print 'SUCCESS';
} else {
    print 'INVALID INPUT';
}
