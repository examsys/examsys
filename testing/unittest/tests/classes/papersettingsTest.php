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
        $paper_settings->updateSetting('remote_summative', 0, $this->paper['id']);
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
        $expected['remote_summative'] = array('value' => 1, 'type' => \Config::BOOLEAN, 'category' => 'security');
        $expected['seb_enabled'] = array('value' => null, 'type' => \Config::BOOLEAN, 'category' => 'seb');
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
        $this->expectExceptionMessage('invalid_paper_setting');
        $paper_settings->getSetting('doesnotexist');
    }

    /**
     * Test verifying type value
     * @group paper
     */
    public function testVerifyValue(): void
    {
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $this->assertEquals(0, $paper_settings->VerifyValue(\Config::BOOLEAN, ''));
        $this->assertEquals(1, $paper_settings->VerifyValue(\Config::BOOLEAN, '3'));
    }

    /**
     * Test getting setting type
     * @group paper
     */
    public function testGetType(): void
    {
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $this->assertEquals(\Config::BOOLEAN, $paper_settings->getType('remote_summative'));
    }

    /**
     * Test creating a category
     * @group paper
     */
    public function testCreatePaperSettingsCategories(): void
    {
        PaperSettings::createPaperSettingsCategories($this->db, array('test'));
        $queryTable = $this->query(
            array(
                'columns' => array('category'),
                'table' => 'paper_settings_category'
            )
        );
        $expectedTable = array(
            0 => array(
                'category' => 'feedback',
            ),
            1 => array(
                'category' => 'general',
            ),
            2 => array(
                'category' => 'postscript',
            ),
            3 => array(
                'category' => 'prologue',
            ),
            4 => array(
                'category' => 'reference',
            ),
            5 => array(
                'category' => 'reviewers',
            ),
            6 => array(
                'category' => 'rubric',
            ),
            7 => array(
                'category' => 'seb',
            ),
            8 => array(
                'category' => 'security',
            ),
            9 => array(
                'category' => 'test',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test creating a setting declaration
     * @group paper
     */
    public function testCreatePaperSettingsSetting(): void
    {
        $supported = '{"osce": 0, "survey": 0, "offline": 0, "progress": 1, "formative": 1, "summative": 1, "peer_review": 0}';
        PaperSettings::createPaperSettingsSetting($this->db, 'test', 'general', 'boolean', $supported);
        $queryTable = $this->query(
            array(
                'columns' => array('setting', 'category', 'type', 'supported'),
                'table' => 'paper_settings_setting'
            )
        );
        $expectedTable = array(
            0 => array(
                'setting' => 'remote_summative',
                'category' => 'security',
                'type' => 'boolean',
                'supported' => '{"osce": 0, "survey": 0, "offline": 0, "progress": 0, "formative": 0, "summative": 1, "peer_review": 0}'
            ),
            1 => array(
                'setting' => 'seb_enabled',
                'category' => 'seb',
                'type' => 'boolean',
                'supported' => '{"osce": 0, "survey": 0, "offline": 0, "progress": 1, "formative": 0, "summative": 1, "peer_review": 0}'
            ),
            2 => array(
                'setting' => 'test',
                'category' => 'general',
                'type' => 'boolean',
                'supported' => $supported
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test getting if setting is enabled for a category
     * @group paper
     */
    public function testSettingsCategoryEnabled(): void
    {
        $paper_settings = new \PaperSettings($this->paper['id'], $this->paper['papertype']);
        $this->assertTrue($paper_settings->settingsCategoryEnabled('seb'));
        $paper_settings2 = new \PaperSettings($this->paper2['id'], $this->paper2['papertype']);
        $this->assertFalse($paper_settings2->settingsCategoryEnabled('seb'));
    }
}
