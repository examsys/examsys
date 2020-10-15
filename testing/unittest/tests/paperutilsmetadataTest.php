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
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class PaperUtilsMetadataTest extends unittestdatabase
{
    /**
     * @var array Storage for paper data in tests
     */
    private $paper;

    /**
     * @var array Storage for paper meatadata in tests
     */
    private $meta1;

    /**
     * @var array Storage for paper meatadata in tests
     */
    private $meta2;

    /**
     * Generate data for test.
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
        $meta = $datagenerator->createMetadata($this->paper['id'], ['name' => 'test', 'value' => 'a value']);
        $meta2 = $datagenerator->createMetadata($this->paper['id'], ['name' => 'test', 'value' => 'another value']);
        $this->meta1[$meta['name']] = [$meta['value'], $meta2['value']];
        $meta3 = $datagenerator->createMetadata($this->paper['id'], ['name' => 'test 2', 'value' => 'a value']);
        $this->meta2[$meta3['name']] = [$meta3['value']];
    }

    /**
     * Tests setting paper (non security) metadata
     * @group paper
     */
    public function testSetMetadata(): void
    {
        // Test adding new meta data. duplicate entry should be ignored and not inserted.
        $metadata = array('aaa', 'bbb', 'ccc', 'bbb');
        Paper_utils::set_metadata(
            $this->db,
            $this->paper['id'],
            array('metadata' => $metadata),
            true
        );
        $queryTable = $this->query(
            array(
                'columns' => array('paperID', 'name', 'value'),
                'table' => 'paper_metadata',
                'where' => array(array('column' => 'name', 'value' => 'metadata'))
            )
        );
        $expectedTable = array(
            0 => array (
                'paperID' => $this->paper['id'],
                'name' => 'metadata',
                'value' => $metadata[0]
            ),
            1 => array (
                'paperID' => $this->paper['id'],
                'name' => 'metadata',
                'value' => $metadata[1]
            ),
            2 => array (
                'paperID' => $this->paper['id'],
                'name' => 'metadata',
                'value' => $metadata[2]
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Tests getting paper (non security) metadata
     * @group paper
     */
    public function testGetMetadata(): void
    {
        // Single.
        $this->assertEquals($this->meta1, Paper_utils::get_metadata($this->db, $this->paper['id'], 'test'));
        // All.
        $expected = array_merge($this->meta1, $this->meta2);
        $this->assertEquals($expected, Paper_utils::get_metadata($this->db, $this->paper['id']));
    }

    /**
     * Tests deleting paper (non security) metadata
     * @group paper
     */
    public function testDeleteMetadata(): void
    {
        Paper_utils::delete_metadata($this->db, $this->paper['id'], 'test 2');
        $queryTable = $this->query(
            array(
                'columns' => array('paperID', 'name', 'value'),
                'table' => 'paper_metadata'
            )
        );
        $idx = key($this->meta1);
        $expectedTable = array(
            0 => array (
                'paperID' => $this->paper['id'],
                'name' => $idx,
                'value' => $this->meta1[$idx][0]
            ),
            1 => array (
                'paperID' => $this->paper['id'],
                'name' => $idx,
                'value' => $this->meta1[$idx][1]
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }
}
