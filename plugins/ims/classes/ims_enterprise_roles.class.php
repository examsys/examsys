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
 * Class for dealing with role mappings in IMS Enterprise.
 *
 * Based on code originally part of Moodle - http://moodle.org
 *
 * @package    plugins_IMS
 * @copyright  2010 Eugene Venter
 * @copyright  2015 onwards, University of Nottingham
 * @author     Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk> based on code by Eugene Venter
 */
class ims_enterprise_roles
{
    /** @var imscode => ims role name. Role name mapping. */
    private $imsroles;
    /** $var The code for the Learner role as defined by the IMS Enterprise specification */
    public const ROLE_LEARNER = '01';
    /** $var The code for the Instructor role as defined by the IMS Enterprise specification */
    public const ROLE_INSTRUCTOR = '02';
    /** $var The code for the Content Developer role as defined by the IMS Enterprise specification */
    public const ROLE_CONTENT_DEVELOPER = '03';
    /** $var The code for the Member role as defined by the IMS Enterprise specification */
    public const ROLE_MEMBER = '04';
    /** $var The code for the Manager role as defined by the IMS Enterprise specification */
    public const ROLE_MANAGER = '05';
    /** $var The code for the Mentor role as defined by the IMS Enterprise specification */
    public const ROLE_MENTOR = '06';
    /** $var The code for the Administrator role as defined by the IMS Enterprise specification */
    public const ROLE_ADMINISTRATOR = '07';
    /** $var The code for the TeachingAssistant role as defined by the IMS Enterprise specification */
    public const ROLE_TEACHINGASSISTANT = '08';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->imsroles = array(
        self::ROLE_LEARNER => 'Learner',
        self::ROLE_INSTRUCTOR => 'Instructor',
        self::ROLE_CONTENT_DEVELOPER => 'Content Developer',
        self::ROLE_MEMBER => 'Member',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_MENTOR => 'Mentor',
        self::ROLE_ADMINISTRATOR => 'Administrator',
        self::ROLE_TEACHINGASSISTANT => 'TeachingAssistant'
        );
        // PLEASE NOTE: It may seem odd that "Content Developer" has a space in it
        // but "TeachingAssistant" doesn't. That's what the spec says though!!!
    }

    /**
     * Returns the mapped roles
     *
     * @return array of IMS roles indexed by IMS code.
     */
    public function get_imsroles()
    {
        return $this->imsroles;
    }

    /**
     * This function is only used when first setting up the plugin, to
     * decide which role assignments to recommend by default.
     * For example, IMS role '01' is 'Learner', so may map to 'Student' in ExamSys.
     *
     * @param string $imscode
     */
    public function get_default_rolemapping($imscode)
    {
        switch ($imscode) {
            case self::ROLE_LEARNER:
            case self::ROLE_MEMBER:
                $role = 'Student';
                break;
            case self::ROLE_INSTRUCTOR:
            case self::ROLE_CONTENT_DEVELOPER:
            case self::ROLE_MANAGER:
            case self::ROLE_MENTOR:
            case self::ROLE_ADMINISTRATOR:
            case self::ROLE_TEACHINGASSISTANT:
                $role = 'Staff';
                break;
            default:
                return 0; // Zero for no match.
        }
        return $role;
    }

    /**
     * Check if we are allowed to create a user with this role
     * @param string $role
     * @return boolean
     */
    public static function validate_role($role)
    {
        $allowedroles = array('student', 'staff', 'invigilator', 'external examiner');
        if (in_array($role, $allowedroles)) {
            return true;
        }
        return false;
    }

    /**
     * Get role mappings options
     * @return array Array of role mapping options
     */
    public static function get_role_mappings()
    {
        global $string;

        $rolemappings = array();
        $rolemappings['Ignore'] = $string['ignore'];
        $rolemappings['Student'] = $string['student'];
        $rolemappings['Staff'] = $string['staff'];
        $rolemappings['Invigilator'] = $string['invigilator'];
        $rolemappings['External Examiner'] = $string['externalexaminer'];
        return $rolemappings;
    }
}
