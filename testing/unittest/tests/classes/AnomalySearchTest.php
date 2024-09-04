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
 * Tests the Anomaly Search class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group search
 * @group anomaly
 */
class AnomalySearchTest extends testing\unittest\unittestdatabase
{
    /** @var array anomaly entries. */
    protected array $anomaly;

    /** @var array test paper. */
    protected array $paper;

    /** @var array test student. */
    protected array $student1;

    /**
     * @see testing\unittest\unittestdatabase::datageneration()
     */
    public function datageneration(): void
    {
        $usergen = $this->get_datagenerator('users', 'core');
        $this->student1 = $usergen->create_user(['roles' => 'Student', 'sid' => '24680', 'surname' => 'Smith']);
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
        $this->anomaly[] = $datagenerator->createAnomaly(
            array(
                'userid' => $this->student1['id'],
                'paperid' => $this->paper['id'],
                'screen' => 2,
                'type' => \Anomaly::CLOCK,
                'previous' => 'Tue Aug 19 1975 23:15:30 GMT+0200 (CEST)',
                'current' => 'Tue Aug 19 1975 23:10:30 GMT+0200 (CEST)'
            )
        );
        $this->anomaly[] = $datagenerator->createAnomaly(
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
     * Tests search
     */
    public function testSearch()
    {
        $search = new AnomalySearch($this->paper['papertype']);
        $search->setLimit(100);
        $search->setPage(1);
        $search->setParameters(strtotime('-1 day'), strtotime('+1 day'), $this->paper['id'], true);

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(1, $result->total);

        $result->query->bind_result($userid, $forename, $surname, $sid, $type, $timestamp, $details, $screen);
        $i = 0;
        while ($result->query->fetch()) {
            self::assertEquals($this->anomaly[$i]['userid'], $userid);
            self::assertEquals($this->student1['first_names'], $forename);
            self::assertEquals($this->student1['surname'], $surname);
            self::assertEquals($this->student1['sid'], $sid);
            self::assertEquals($this->anomaly[$i]['type'], $type);
            self::assertEquals($this->anomaly[$i]['timestamp'], $timestamp);
            self::assertEquals(json_encode($this->anomaly[$i]['details']), $details);
            self::assertEquals($this->anomaly[$i]['screen'], $screen);
            $i++;
        }
        $result->query->close();
    }
}
