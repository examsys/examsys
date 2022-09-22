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

namespace plugins\ims;

/**
 * Mapping between ExamSys module attributes and IMS enterprise group description tags
 *
 * Based on code originally part of Moodle - http://moodle.org
 *
 * @package   plugin_enrol
 * @copyright 2011 Aaron C Spike
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ims_enterprise_modules
{

    /** @var array IMS group description names */
    private $imsnames;

    /** @var array ExamSys module field names */
    private $moduleattrs;

    /**
     * Loads default
     */
    public function __construct()
    {
        $this->imsnames = array(
        'short' => 'short',
        'long' => 'long',
        'full' => 'full');
        $this->moduleattrs = array('moduleid', 'fullname');
    }

    /**
     * moduleattrs getter
     * @return array
     */
    public function get_moduleattrs()
    {
        return $this->moduleattrs;
    }

    /**
     * This function is only used when first setting up the plugin, to
     * decide which name assignments to recommend by default.
     *
     * @param string $moduleattr
     * @return string
     */
    public function determine_default_modulemapping($moduleattr)
    {
        switch ($moduleattr) {
            case 'fullname':
                $imsname = 'short';
                break;
            case 'shortname':
                $imsname = 'modulecode';
                break;
            default:
                $imsname = 'ignore';
        }
        return $imsname;
    }
}
