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
 * Test papersettings class
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class PaperSetttingsTest extends unittestdatabase
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

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->config->set_setting('summative_remote', 1, 'boolean');
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
        $this->paper2 = $datagenerator->create_paper(
            array(
                'papertitle' => 'test formative',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '0',
                'modulename' => 'Training Module'
            )
        );
    }

    /**
     * Test inserting and updating settings.
     * @group paper
     */
    public function testUpdateSetting(): void
    {
        $queryTable = $this->query(
            array(
                'columns' => array('paperid', 'setting', 'value'),
                'table' => 'paper_settings'
            )
        );
        $expectedTable = array(
            0 => array(
                'setting' => 'remote_summative',
                'value' => '1',
                'paperid' => $this->paper['id'],
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $paper_settings->updateSetting('remote_summative', 0, \Config::BOOLEAN, $this->paper['id']);
        $queryTable = $this->query(
            array(
                'columns' => array('paperid', 'setting', 'value'),
                'table' => 'paper_settings'
            )
        );
        $expectedTable = array(
            0 => array(
                'setting' => 'remote_summative',
                'value' => '0',
                'paperid' => $this->paper['id'],
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get settings
     * @group paper
     */
    public function testGet(): void
    {
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $expected['security']['remote_summative'] = array('value' => 1, 'type' => \Config::BOOLEAN);
        $this->assertEquals($expected, $paper_settings->get());
        // Not supported.
        $paper_settings = new \PaperSettings($this->paper2['id'], $this->paper2['papertype']);
        $expected = array();
        $this->assertEquals($expected, $paper_settings->get());
    }

    /**
     * Test getting a setting
     * @group paper
     */
    public function testGetSetting(): void
    {
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $this->assertEquals('1', $paper_settings->getSetting('remote_summative'));
        $this->assertNull($paper_settings->getSetting('doesnotexist'));
    }
}
