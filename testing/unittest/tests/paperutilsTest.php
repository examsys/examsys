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
 * Test paperutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperutilstest extends unittestdatabase {
    
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperutilsTest" . DIRECTORY_SEPARATOR . "paperutils.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "paperutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test complete paper deletion
     * @group assessment
     */
    public function test_complete_delete_paper() {
        // Check successful deletion.
        $this->assertTrue(Paper_utils::complete_delete_paper(2, $this->db));
        $querypropertiestable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id FROM properties');
        $querypropertiesmodulestable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT * FROM properties_modules');
        $expectedpropertiestable = $this->get_expected_data_set('deleteproperties')->getTable("properties");
        $expectedpropertiesmodulestable = $this->get_expected_data_set('deleteproperties')->getTable("properties_modules");
        // Check properties table deletion.
        $this->assertTablesEqual($expectedpropertiestable, $querypropertiestable);
        // Check properties_modules table deletion.
        $this->assertTablesEqual($expectedpropertiesmodulestable, $querypropertiesmodulestable);  
    }
    
    /**
     * Test get papers by session
     * @group gradebook
     */
    public function test_get_papers_by_session() {
        $papers = array(2);
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_papers_by_session('2016', 1, $this->db));
    }
    
    /**
     * Test get finalised papers
     * @group gradebook
     */
    public function test_get_finalised_papers() {
        $papers = array(2);
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 2, $this->db));
        $papers = array();
        $this->assertEquals($papers, Paper_utils::get_finalised_papers('2016', 1, $this->db));
    }
    
    /**
     * Test get available papers - paper type provided
     * @group assessment
     */
    public function test_get_available_papers_type() {
        // Load user.
        $this->userobject->load(1);
        $order = "paper_title";
        $direction = "asc";
        $papers = array();
        $created1 = ' ' . strftime($this->config->get('cfg_long_date'), strtotime("2017-01-09 14:30:00"));
        $created2 = ' ' . strftime($this->config->get('cfg_long_date'), strtotime("2017-01-09 14:31:00"));
        $papers[1] = array('paper_title'=>'Paper 1', 'paper_type'=>'2', 'created'=>$created1, 'title'=>'Dr', 'initials'=>'JL', 'surname'=>'Baxter');
        $papers[1]['moduleid'][0] = "ABC100";
        $papers[2] = array('paper_title'=>'Paper 2', 'paper_type'=>'2', 'created'=>$created2, 'title'=>'Dr', 'initials'=>'JL', 'surname'=>'Baxter');
        $papers[2]['moduleid'][0] = "ABC100";
        $papers[2]['moduleid'][1] = "ABC200";
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, '2', null));
    }

    /**
     * Test get available papers - team id provided
     * @group assessment
     */
    public function test_get_available_papers_team() {
        // Load user.
        $this->userobject->load(1);
        $order = "paper_title";
        $direction = "asc";
        $papers = array();
        $created1 = ' ' . strftime($this->config->get('cfg_long_date'), strtotime("2017-01-09 14:30:00"));
        $created2 = ' ' . strftime($this->config->get('cfg_long_date'), strtotime("2017-01-09 14:31:00"));
        $papers[1] = array('paper_title'=>'Paper 1', 'paper_type'=>'2', 'created'=>$created1, 'title'=>'Dr', 'initials'=>'JL', 'surname'=>'Baxter');
        $papers[1]['moduleid'][0] = "ABC100";
        $papers[2] = array('paper_title'=>'Paper 2', 'paper_type'=>'2', 'created'=>$created2, 'title'=>'Dr', 'initials'=>'JL', 'surname'=>'Baxter');
        $papers[2]['moduleid'][0] = "ABC100";
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, 1));
    }
    
    /**
     * Test get available papers - paper type provided, non available
     * @group assessment
     */
    public function test_get_available_papers_type_none() {
        // Load user.
        $this->userobject->load(2);
        $order = "paper_title";
        $direction = "asc";
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, '2', null));
    }

    /**
     * Test get available papers - team id provided, non available
     * @group assessment
     */
    public function test_get_available_papers_team_none() {
        // Load user.
        $this->userobject->load(2);
        $order = "paper_title";
        $direction = "asc";
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, 3));
    }
    
    /**
     * Test get available papers - team id and paper type not provided
     * @group assessment
     */
    public function test_get_available_papers_null() {
        // Load user.
        $this->userobject->load(2);
        $order = "paper_title";
        $direction = "asc";
        $papers = array();
        $this->assertEquals($papers, PaperUtils::get_available_papers($this->userobject, $order, $direction, null, null));
    }

    /**
     * Test get copy paper properties
     * @group assessment
     */
    public function test_copyProperties() {
        // Load user.
        $this->userobject->load(1);
        $postparams['paperID'] = 2;
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017; 
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        $this->assertEquals(2016, $papercopy['calendar_year']);
        $this->assertEquals(2017, $papercopy['new_calendar_year']);
        $this->assertEquals(array(1 => 'ABC100', 2 => 'ABC200'), $papercopy['moduleIDs']);
        $this->assertEquals(3, $papercopy['new_paper_id']);
        $querypropertiestable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, calendar_year, paper_type, paper_ownerID, exam_duration FROM properties');
        $querypropertiesmodulestable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT * FROM properties_modules');
        $expectedpropertiestable = $this->get_expected_data_set('copyproperties')->getTable("properties");
        $this->assertTablesEqual($expectedpropertiestable, $querypropertiestable);
        $expectedpropertiesmodulestable = $this->get_expected_data_set('copyproperties')->getTable("properties_modules");
        $this->assertTablesEqual($querypropertiesmodulestable, $querypropertiesmodulestable);
    }

    /**
     * Test get copy objectives between sessions
     * @group assessment
     */
    public function test_copy_between_sessions() {
        $this->userobject->load(1);
        $postparams['paperID'] = 1;
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017; 
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Need require until mapping made a class.
        $cfg_web_root = get_root_path() . '/';
        require_once $cfg_web_root . 'include/mapping.inc';
        $old_course = getObjectives($papercopy['moduleIDs'], $papercopy['calendar_year'], 1, '', $this->db);
        $new_course = getObjectives($papercopy['moduleIDs'], $papercopy['new_calendar_year'], 1, '', $this->db);
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array(123 => 126, 124 => 127, 125 => 128);
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions, vle mistmatch
     * @group assessment
     */
    public function test_copy_between_sessions_mismatch() {
        $this->userobject->load(1);
        $postparams['paperID'] = 2;
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017; 
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Need require until mapping made a class.
        $cfg_web_root = get_root_path() . '/';
        require_once $cfg_web_root . 'include/mapping.inc';
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => '', // null VLE
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array();
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions - cmap objectives
     * @group assessment
     */
    public function test_copy_between_sessions_cmap() {
        $this->userobject->load(1);
        $postparams['paperID'] = 2;
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017; 
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array(16606 => '16608');
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }

    /**
     * Test get copy objectives between sessions - cmap objectives, no mappings
     * @group assessment
     */
    public function test_copy_between_sessions_cmap_nomappings() {
        $this->userobject->load(1);
        $postparams['paperID'] = 2;
        $postparams['paper_type'] = 1;
        $postparams['new_paper'] = 'paper copy test';
        $postparams['session'] = 2017; 
        $moduleIDs = null;
        $calendar_year = $new_calendar_year = '';
        $papercopy = PaperUtils::copyProperties($calendar_year, $new_calendar_year, $moduleIDs, $postparams);
        // Fake getObjectives return. Ideally we would mock the CMAP response but that involves more rework.
        $old_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16605',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2016,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Communicate clearly, sensitively and effectively with patients and their relatives or carers, and with other health care providers.',
                        'id' => '16606',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $new_course = array('A14ACE' => array(
            'ab0a3310-c125-11e2-bcdc-005056ad00ea' => array (
                'identifier' => '16607',
                'guid' => 'ab0a3310-c125-11e2-bcdc-005056ad00ea',
                'class_code' => '',
                'title' => 'Generic skills',
                'occurrance' => 'Non-timetabled',
                'calendar_year' => 2017,
                'VLE' => 'UoNCM',
                'source_url' => '',
                'mapped' => 0,
                'objectives' => array(
                    1 => array(
                        'content' => 'Content does not match',
                        'id' => '16608',
                        'guid' => 'ab0a33a6-c125-11e2-bcdc-005056ad00ea',
                        'mapped' => 0
                        )
                    )
                )
            )
        );
        $mappings_copy_objID = Paper_utils::copy_between_sessions($old_course, $new_course);
        $expected_mappings = array();
        $this->assertEquals($expected_mappings, $mappings_copy_objID);
    }
}