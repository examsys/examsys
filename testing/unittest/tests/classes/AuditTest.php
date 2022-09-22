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
 * Tests the Audit class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group audit
 */
class AuditTest extends \testing\unittest\unittestdatabase
{
    /** @var array Storage for audit data in tests. */
    private $audit;

    /**
     * @inheritDoc
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('audit', 'core');
        $this->audit = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'action' => Audit::ADDROLE,
                'details' => 'Student',
            )
        );
    }

    /**
     * Tests that the retention period can be set.
     */
    public function testSetRetentionPeriod(): void
    {
        Audit::setRetentionPeriod(45);
        // Check tables are correct.
        $queryTable = $this->query(array('table' => 'retention'));
        $expectedTable = array(
            0 => array (
                'table' => 'audit_log',
                'days' => 45,
                'lastrun' => null
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Tests that the retention period can be found.
     */
    public function testGetRetentionPeriod(): void
    {
        $this->assertEquals(90, Audit::getRententionPeriod());
    }

    /**
     * Tests that audit events can be set.
     */
    public function testinsertEvent(): void
    {
        $details = 'Student';
        $this->userobject->load($this->admin['id']);
        Audit::insertEvent(Audit::REMOVEROLE, $this->student['id'], $details);
        // Check tables are correct.
        $queryTable = $this->query(
            array(
                'table' => 'audit_log',
                'columns' => array('userID', 'action', 'details', 'sourceID', 'source')
            )
        );
        $expectedTable = array(
            0 => array (
                'userID' => $this->audit['userID'],
                'action' => $this->audit['action'],
                'details' => $this->audit['details'],
                'sourceID' => $this->audit['sourceID'],
                'source' => $this->audit['source'],
            ),
            1 => array (
                'userID' => $this->student['id'],
                'action' => Audit::REMOVEROLE,
                'details' => $details,
                'sourceID' => $this->admin['id'],
                'source' => 'vendor/bin/phpunit',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Tests that get events funcion gets all events.
     */
    public function testGetEvents(): void
    {
        $starttime = date('Y-m-d H:i:s', strtotime('-' . '1 day'));
        $limit = 10;
        $page = 1;
        $webroot = $this->config->get('cfg_root_path');
        $actual = Audit::getEvents($starttime, $limit, $page);
        $langpack = new langpack();
        $user = $langpack->get_string('classes/audit', 'system');
        $expected = array (
            'to' => '1',
            'from' => '1',
            'total' => '1',
            'pages' => 1.0,
            'items' => array(
                array(
                    'object' => array(
                        array(
                            'url' => $webroot . '/users/details.php?userID=' . $this->audit['userID'],
                            'label' => 'Student',
                        ),
                    ),
                    'objecttype' => 'Role',
                    'time' => $this->audit['time'],
                    'user' => $user,
                    'affecteduser' => array(
                        'url' => $webroot . '/users/details.php?userID=' . $this->audit['userID'],
                        'label' => UserUtils::get_username($this->student['id'], Config::get_instance()->db),
                    ),
                    'source' => array(
                        'url' => '',
                        'label' => $this->audit['source'],
                    ),
                    'eventtype' => 'Add User Role',
                ),
            ),
        );
        $this->assertEquals($expected, $actual);
    }
}
