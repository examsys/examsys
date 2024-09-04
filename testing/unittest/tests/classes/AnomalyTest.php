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
 * Tests the Anomaly class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group anomaly
 */
class AnomalyTest extends \testing\unittest\unittestdatabase
{
    /**
     * The test paper
     * @var array $paper
     */
    protected $paper;

    /**
     * The test anomaly
     * @var array $anomaly
     */
    protected $anomaly;

    /**
     * The another test anomaly
     * @var array $anomaly
     */
    protected $anomaly2;

    /**
     * @inheritDoc
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->paper = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'modulename' => 'Training Module',
                'remote' => 1
            )
        );
        $datagenerator = $this->get_datagenerator('anomaly', 'core');
        $this->anomaly = $datagenerator->createAnomaly(
            array(
                'userid' => $this->student['id'],
                'paperid' => $this->paper['id'],
                'screen' => 2,
                'type' => \Anomaly::CLOCK,
                'previous' => 'Tue Aug 19 1975 23:15:30 GMT+0200 (CEST)',
                'current' => 'Tue Aug 19 1975 23:10:30 GMT+0200 (CEST)'
            )
        );
        $this->anomaly2 = $datagenerator->createAnomaly(
            array(
                'userid' => $this->admin['id'],
                'paperid' => $this->paper['id'],
                'screen' => 1,
                'type' => \Anomaly::CLOCK,
                'previous' => 'Tue Aug 20 1976 07:30:30 GMT+0200 (CEST)',
                'current' => 'Tue Aug 20 1976 07:10:30 GMT+0200 (CEST)'
            )
        );
    }

    /**
     * Tests the anomaly detection setting
     * @param bool $enabled
     * @param string $papertype
     * @dataProvider dataAnomalyDetectionEnabled
     */
    public function testAnomalyDetectionEnabled(bool $enabled, string $papertype): void
    {
        $types = \PaperUtils::getTypeList();
        $type = array_search($papertype, $types);
        $anomaly[$type] = $enabled;
        $this->config->set_setting('paper_anomaly_detection', $anomaly, \Config::ASSOC);
        // Only Progress and Summatives can be enabled.
        if ($papertype != '1' and $papertype != '2') {
            $expected = false;
        } else {
            $expected = $enabled;
        }
        $this->assertEquals($expected, Anomaly::anomalyDetectionEnabled($papertype));
    }

    /**
     * Data used to test AnomalyDetectionEnabled
     *
     * @return array
     */
    public function dataAnomalyDetectionEnabled(): array
    {
        return [
            'formative off' => [false, '0'],
            'progress off' => [false, '1'],
            'summative off' => [false, '2'],
            'formative on' => [true, '0'],
            'progress on' => [true, '1'],
            'summative on' => [true, '2'],
        ];
    }

    /**
     * Tests retreiving anomaly data
     */
    public function testGetAnomalies(): void
    {
        $items = [];
        $expected = new \AnomalyItem();
        $expected->userid = $this->student['id'];
        $expected->forename = $this->student['first_names'];
        $expected->surname = $this->student['surname'];
        $expected->sid = $this->student['student_id'];
        $expected->type = $this->anomaly['typename'];
        $expected->timestamp = date(
            $this->config->get('cfg_very_short_datetime_php'),
            $this->anomaly['timestamp']
        );
        $expected->details = $this->anomaly['details'];
        $expected->screen = $this->anomaly['screen'];
        $items[] = $expected;
        $expectedarray = array(
            'from' => 1,
            'to' => 1,
            'total' => 1,
            'pages' => 1,
            'items' => $items
        );
        // Anomaly exists.
        $this->assertEquals(
            $expectedarray,
            \Anomaly::getAnomalies(
                $this->paper['id'],
                $this->paper['papertype'],
                time() - 1000,
                time(),
                100,
                1,
                true
            )
        );
        // No anomalies exist.
        $expectedarray = array(
            'from' => 0,
            'to' => 0,
            'total' => 0,
            'pages' => 0,
            'items' => []
        );
        $this->assertEquals(
            $expectedarray,
            \Anomaly::getAnomalies(
                $this->paper['id'],
                $this->paper['papertype'],
                time() + 1000,
                time() + 2000,
                100,
                1,
                true
            )
        );
        // Inlcuding staff anomalies.
        $expected = new \AnomalyItem();
        $expected->userid = $this->admin['id'];
        $expected->forename = $this->admin['first_names'];
        $expected->surname = $this->admin['surname'];
        $expected->sid = null;
        $expected->type = $this->anomaly2['typename'];
        $expected->timestamp = date(
            $this->config->get('cfg_very_short_datetime_php'),
            $this->anomaly2['timestamp']
        );
        $expected->details = $this->anomaly2['details'];
        $expected->screen = $this->anomaly2['screen'];
        $items[] = $expected;
        $expectedarray = array(
            'from' => 1,
            'to' => 2,
            'total' => 2,
            'pages' => 1,
            'items' => $items
        );
        $this->assertEquals(
            $expectedarray,
            \Anomaly::getAnomalies(
                $this->paper['id'],
                $this->paper['papertype'],
                time() - 1000,
                time(),
                100,
                1,
                false
            )
        );
    }

    /**
     * Tests get anomaly type
     */
    public function testType(): void
    {
        $this->assertEquals('clock', \Anomaly::getType(\Anomaly::CLOCK));
        $this->assertEquals('unknown', \Anomaly::getType(999));
    }

    /**
     * Tests insert anomaly data
     */
    public function testInsert(): void
    {
        $previous = 'Tue Aug 19 1975 20:15:30 GMT+0200 (CEST)';
        $current = 'Tue Aug 19 1975 19:15:30 GMT+0200 (CEST)';
        $data = array(
            'userid' => $this->student['id'],
            'paperid' => $this->paper['id'],
            'screen' => 2,
            'previous' => $previous,
            'current' => $current,
        );
        $anomaly = new ClockAnomaly($data);
        $insert = $anomaly->insert();
        // Test properties table is as expected.
        $queryTable = $this->query(array('columns' => array('id', 'type', 'time', 'details', 'userID',
            'paperID', 'screen'), 'table' => 'anomaly'));
        $expectedTable = array(
            0 => array(
                'id' => $this->anomaly['id'],
                'type' => $this->anomaly['type'],
                'time' => $this->anomaly['timestamp'],
                'details' => json_encode($this->anomaly['details']),
                'userID' => $this->anomaly['userid'],
                'paperID' => $this->anomaly['paperid'],
                'screen' => $this->anomaly['screen'],
            ),
            1 => array(
                'id' => $this->anomaly2['id'],
                'type' => $this->anomaly2['type'],
                'time' => $this->anomaly2['timestamp'],
                'details' => json_encode($this->anomaly2['details']),
                'userID' => $this->anomaly2['userid'],
                'paperID' => $this->anomaly2['paperid'],
                'screen' => $this->anomaly2['screen'],
            ),
            2 => array(
                'id' => $insert['id'],
                'type' => \Anomaly::CLOCK,
                'time' => $insert['timestamp'],
                'details' => json_encode(array('previous' => $previous, 'current' => $current)),
                'userID' => $this->student['id'],
                'paperID' => $this->paper['id'],
                'screen' => 2,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Tests exception on missing user data on insert anomaly
     */
    public function testInsertUserException(): void
    {
        $this->expectExceptionMessage('Anomaly::insert() userid missing');
        $previous = 'Tue Aug 19 1975 20:15:30 GMT+0200 (CEST)';
        $current = 'Tue Aug 19 1975 19:15:30 GMT+0200 (CEST)';
        $data = array(
            'paperid' => $this->paper['id'],
            'screen' => 2,
            'previous' => $previous,
            'current' => $current,
        );
        $anomaly = new ClockAnomaly($data);
        $anomaly->insert();
    }

    /**
     * Tests exception on missing user data on insert anomaly
     */
    public function testInsertPaperException(): void
    {
        $this->expectExceptionMessage('Anomaly::insert() paperid missing');
        $previous = 'Tue Aug 19 1975 20:15:30 GMT+0200 (CEST)';
        $current = 'Tue Aug 19 1975 19:15:30 GMT+0200 (CEST)';
        $data = array(
            'userid' => $this->student['id'],
            'screen' => 2,
            'previous' => $previous,
            'current' => $current,
        );
        $anomaly = new ClockAnomaly($data);
        $anomaly->insert();
    }

    /**
     * Tests exception on missing screen data on insert anomaly
     */
    public function testInsertScreenException(): void
    {
        $this->expectExceptionMessage('Anomaly::insert() screen missing');
        $previous = 'Tue Aug 19 1975 20:15:30 GMT+0200 (CEST)';
        $current = 'Tue Aug 19 1975 19:15:30 GMT+0200 (CEST)';
        $data = array(
            'userid' => $this->student['id'],
            'paperid' => $this->paper['id'],
            'previous' => $previous,
            'current' => $current,
        );
        $anomaly = new ClockAnomaly($data);
        $anomaly->insert();
    }
}
