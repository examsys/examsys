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
class IE_Main
{
    public $output;
    public $errors = array();
    public $warnings = array();

    public function AddError($message, $id = '0')
    {
        $this->errors[$id][] = $message;
    }

    public function AddWarning($message, $id = '0')
    {
        $this->warnings[$id][] = $message;
    }

    public function Save($params, &$data)
    {
        $this->AddError('This export type is not supported');
        //  $this->AddError($string['errmsg1']);
    }

    public function Load($params)
    {
        $this->AddError('This import type is not supported');
        //  $this->AddError($string['errmsg2']);
    }
}
