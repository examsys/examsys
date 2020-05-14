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

use testing\unittest\unittestdatabase;

/**
 * Testcase for class Statistics.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group statistics
 */
class StatisticsTest extends unittestdatabase
{
    /**
     * The test paper
     * @var array $paper
     */
    protected $paper;

    /**
     * The test paper
     * @var array $paper2
     */
    protected $paper2;

    /*
     * @var array Storage for user data in tests
     */
    private $user;

    /*
     * @var array Storage for lab data in tests
     */
    private $lab;

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $datagenerator->create_campus(array('name' => 'Test Campus', 'isdefault' => 1));
        $this->lab = $datagenerator->create_lab(
            array(
                'name' => 'Test lab',
                'building' => 'Test building',
                'room' => 1
            )
        );
        $datagenerator = $this->get_datagenerator('users', 'core');
        $this->user = $datagenerator->create_user(
            array(
                'surname' => 'test3',
                'username' => 'unit3',
                'grade' => 'TEST2',
                'sid' => '141516171819',
                'special_needs' => array('breaks' => 'one an hour'),
            )
        );
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $module3 = $datagenerator->create_module(
            array('fullname' => 'Test module 3', 'moduleid' => 'TEST3', 'timed_exams' => 1)
        );
        $datagenerator->create_enrolment(
            array('userid' => $this->user['id'], 'moduleid' => $module3['id'])
        );
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->paper = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'modulename' => 'Training Module',
                'remote' => 1,
                'startdate' => '2020-10-01 12:30:00',
                'enddate' => '2020-10-01 13:30:00',
            )
        );
        $this->paper2 = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative 2',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'modulename' => 'Training Module',
                'startdate' => '2020-06-01 12:30:00',
                'enddate' => '2020-06-01 13:30:00',
                'labs' => $this->lab['name'],
            )
        );
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $question = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'test'
            )
        );
        $datagenerator->add_question_to_paper(
            array(
                'paper' => $this->paper['id'],
                'question' => $question['id'],
                'screen' => 1,
                'displaypos' => 1
            )
        );
        $datagenerator->add_question_to_paper(
            array(
                'paper' => $this->paper2['id'],
                'question' => $question['id'],
                'screen' => 1,
                'displaypos' => 1
            )
        );
        $logdatagenerator = $this->get_datagenerator('log', 'core');
        $meta = $logdatagenerator->create_metadata(
            array(
                'userID' => $this->user['id'],
                'paperID' => $this->paper['id'],
                'year' => 1,
                'started' => '2020-10-01 12:31:00'
            )
        );
        $meta2 = $logdatagenerator->create_metadata(
            array(
                'userID' => $this->user['id'],
                'paperID' => $this->paper2['id'],
                'year' => 1,
                'started' => '2020-06-01 12:32:00'
            )
        );
        $logdatagenerator->create_summative(
            array(
                'q_id' => $question['id'],
                'metadataID' => $meta['id'],
                'screen' => 1,
                'user_answer' => 1
            )
        );
        $logdatagenerator->create_summative(
            array(
                'q_id' => $question['id'],
                'metadataID' => $meta2['id'],
                'screen' => 1,
                'user_answer' => 1
            )
        );
    }

    /**
     * Test get summative papers.
     */
    public function testGetSummativePapers(): void
    {
        $stats = new Statistics();
        $expected2020[$this->paper['id']] = array(
            'title' => $this->paper['papertitle'],
            'month' => date('m', strtotime($this->paper['startdate'])),
            'start' => $this->paper['startdate'],
            'end' => $this->paper['enddate'],
            'labs' => $this->paper['labs']
        );
        $expected2019[$this->paper2['id']] = array(
            'title' => $this->paper2['papertitle'],
            'month' => date('m', strtotime($this->paper2['startdate'])),
            'start' => $this->paper2['startdate'],
            'end' => $this->paper2['enddate'],
            'labs' => $this->paper2['labs']
        );
        // Check 2020 academic year.
        $this->assertEquals($expected2020, $stats->getSummativePapers(2020));
        // Check 2019 academic year.
        $this->assertEquals($expected2019, $stats->getSummativePapers(2019));
    }

    /**
     * Test get summative papers details.
     */
    public function testGetSummativePapersDetails(): void
    {
        $stats = new Statistics();
        $expected2020[$this->paper2['id']] = array(
            'title' => $this->paper2['papertitle'],
            'start' =>  $stats->getMonthStart('2019', '06'),
            'end' =>  $stats->getMonthEnd('2019', '06')
        );
        $expected2019[$this->paper['id']] = array(
            'title' => $this->paper['papertitle'],
            'start' =>  $stats->getMonthStart('2020', '10'),
            'end' =>  $stats->getMonthEnd('2020', '10')
        );
        // Check October.
        $this->assertEquals($expected2019, $stats->getSummativePapersDetails('10', '2020'));
        // Check November.
        $this->assertEquals(array(), $stats->getSummativePapersDetails('11', '2020'));

        // Check June.
        $this->assertEquals($expected2020, $stats->getSummativePapersDetails('06', '2019'));
        // Check July.
        $this->assertEquals(array(), $stats->getSummativePapersDetails('07', '2019'));
    }

    /**
     * Test get student count.
     */
    public function testGetStudentCount(): void
    {
        $stats = new Statistics();
        $expected[$this->user['id']] = 1;
        $this->assertEquals($expected, $stats->getStudentCount($this->paper['id'], $this->paper['startdate'], $this->paper['enddate']));
    }

    /**
     * Test generate month data.
     */
    public function testGenerateMonth(): void
    {
        $stats = new Statistics();
        $expected = array(
            'link' => 'summative_stats_detail.php?calyear=2014&month=02',
            'linklabel' => 'February',
            'paperno' => 5,
            'paperunusedno' => 1,
            'studentsperpaper' => 1.4,
            'monthmin' => 4,
            'monthmax' => 8,
            'numberstudents' => '7',
        );
        $actual = $stats->generateMonth(
            '02',
            5,
            1,
            7,
            4,
            8,
            '2014'
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test generate lab data.
     */
    public function testGenerateLabStats(): void
    {
        $stats = new Statistics();
        $expected[$this->lab['name']] = 1;
        $actual = $stats->generateLabStats(array($this->lab['id']));
        $this->assertEquals($expected, $actual);
    }
}
