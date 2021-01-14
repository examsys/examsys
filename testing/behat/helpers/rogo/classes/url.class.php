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

namespace testing\behat\helpers\rogo;

/**
 * Helpers for generating URLs to Rogo pages for use in Behat.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class Url
{
    /**
     * Generates the URL to a user profile page.
     *
     * @param int $userid The id of the user the page is for.
     * @param string $tab Optional tab the page should be on.
     * @return string
     */
    public static function userProfile(int $userid, string $tab = ''): string
    {
        switch ($tab) {
            case 'Accessibility':
                $sectionparam = '&tab=accessibility';
                break;
            case 'Admin':
                $sectionparam = '&tab=admin';
                break;
            case 'Metadata':
                $sectionparam = '&tab=metadata';
                break;
            case 'Modules':
                $sectionparam = '&tab=moduless';
                break;
            case 'Notes':
                $sectionparam = '&tab=notes';
                break;
            case 'Roles':
                $sectionparam = '&tab=roles';
                break;
            case 'Teams':
                $sectionparam = '&tab=teams';
                break;
            default:
                $sectionparam = '';
                break;
        }
        return "/users/details.php?userID={$userid}{$sectionparam}";
    }

    /**
     * Generates the URL to a paper details page.
     *
     * @param int $paperid The id of the paper.
     * @return string
     */
    public static function paperDetails(int $paperid): string
    {
        return '/paper/details.php?paperID=' . $paperid;
    }

    /**
     * Generates the URL to a class totals report.
     *
     * @param int $paperid The id of the paper.
     * @param array $filters the report filters
     * @return string
     */
    public static function classTotals(
        int $paperid,
        array $filters
    ): string {
        $startdate = new \DateTime('tomorrow');
        $startdate->setTime(12, 00);
        $enddate = new \DateTime('tomorrow');
        $enddate->setTime(13, 00);
        $defaults = array(
            'startdate' => $startdate->format('YmdHis'),
            'enddate' => $enddate->format('YmdHis'),
            'repmodule' => '',
            'repcourse' => '%',
            'sortby' => 'name',
            'module' => '',
            'folder' => '',
            'percent' => '100',
            'absent' => '0',
            'studentsonly' => '1',
            'ordering' => 'asc',
        );
        $return = array_merge($defaults, $filters);
        $settings = array_intersect_key($return, $defaults);
        return '/reports/class_totals.php?paperID='
            . $paperid
            . '&startdate=' . $settings['startdate']
            . '&enddate=' . $settings['enddate']
            . '&repmodule=' . $settings['repmodule']
            . '&repcourse=' . $settings['repcourse']
            . '&sortby=' . $settings['sortby']
            . '&module=' . $settings['module']
            . '&folder=' . $settings['folder']
            . '&percent=' . $settings['percent']
            . '&absent=' . $settings['absent']
            . '&studentsonly=' . $settings['studentsonly']
            . '&ordering=' . $settings['ordering'];
    }
}
