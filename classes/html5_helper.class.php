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
 * Class of helper functions for html5 questions.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package core
 */
class html5_helper extends RogoStaticSingleton
{
    /**
     * The active instance of this class.
     *
     * @var html5_helper
     */
    protected static $inst;

    /**
     * The name of the class that is instantiated as the singleton.
     *
     * @var string
     */
    protected static $class_name = 'html5_helper';

    /**
     * Get an array of language strings used by html5 questions.
     *
     * @return array
     */
    public function get_lang_strings()
    {
        $hotspot_strings = hotspot_helper::get_instance()->get_lang_strings();
        return $hotspot_strings;
    }
}
