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
 * Tests the Audit Search class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group search
 */
class AuditSearchTest extends testing\unittest\unittestdatabase
{
    /** @var array audit entries. */
    protected $audit;

    /**
     * @see testing\unittest\unittestdatabase::datageneration()
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('audit', 'core');
        $this->audit[] = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'action' => Audit::ADDROLE,
                'details' => 'Student',
            )
        );
        $this->audit[] = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'action' => Audit::REMOVEROLE,
                'details' => 'Student',
            )
        );
        $this->audit[] = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'details' => Audit::ADDROLE,
                'action' => 'Student',
            )
        );
    }

    /**
     * Tests search
     */
    public function testSearch()
    {
        $search = new AuditSearch();
        $search->setLimit(100);
        $search->setPage(1);
        $search->setTime(date('Y-m-d H:i:s', strtotime('-1 day')));

        $result = $search->execute();
        self::assertInstanceOf(SearchResult::class, $result);
        self::assertEquals(count($this->audit), $result->total);

        $result->query->bind_result($userID, $action, $time, $sourceID, $source, $details);
        $i = 0;
        while ($result->query->fetch()) {
            self::assertEquals($this->audit[$i]['userID'], $userID);
            self::assertEquals($this->audit[$i]['action'], $action);
            self::assertEquals($this->audit[$i]['details'], $details);
            self::assertEquals($this->audit[$i]['time'], $time);
            self::assertEquals($this->audit[$i]['sourceID'], $sourceID);
            self::assertEquals($this->audit[$i]['source'], $source);
            $i++;
        }
        $result->query->close();
    }
}
