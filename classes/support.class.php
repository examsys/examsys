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
 * Support package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 */

/**
 * Support helper class.
 */
class support
{
    /**
     * Get support email addresses
     * @return string
     */
    public static function get_email()
    {
        $configObj = Config::get_instance();
        return implode(';', $configObj->get_setting('core', 'support_contact_email'));
    }
    /**
     * Get primary support email address
     * @return string
     */
    public static function get_primary_email()
    {
        $configObj = Config::get_instance();
        $emaillist = $configObj->get_setting('core', 'support_contact_email');
        return $emaillist[0];
    }
}
