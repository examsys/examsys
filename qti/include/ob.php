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

ob_start();

class OB
{
    public $content;

    public function ClearAndSave()
    {
        $this->content = ob_get_contents();
        ob_clean();
    }

    public function Clear()
    {
        $this->content = '';
        ob_clean();
    }

    public function GetContent()
    {
        return ob_get_contents();
    }

    public function Restore()
    {
        ob_clean();
        echo $this->content;
    }

    public function DoInclude($filename)
    {
        $this->ClearAndSave();
        include $filename;
        $res = $this->GetContent();
        $this->Restore();
        return $res;
    }
}
