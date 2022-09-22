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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'st_question.php';
require_once 'st_paper.php';

// main storage class, contains a bunch of questions,
// a bunch of papers which link to questions, and possibly some other stuff

class ST_Main
{
    public $papers;
    public $questions;
}

// class to store exported files
class ST_File
{
    public $filename;
    public $title;
    public $path;
    public $type;
    public $id;

    public function __construct($filename, $title, $path, $type = 'xml', $id = 0)
    {
        $this->filename = $filename;
        $this->title = $title;
        $this->path = $path;
        $this->type = $type;
        $this->id = $id;
    }
}
