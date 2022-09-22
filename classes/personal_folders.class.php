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
 *
 * Personal Folders class.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class personal_folders
{
    private $folderlst;
    private $folderlst2;
    private $mysqli;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->mysqli = null;
    }

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function loadpersonalfolders($userID)
    {
        // -- Display personal folders --------------------------------------
        if (!isset($teams)) {
            $teams = getUserTeams($userID, $this->mysqli);
        }
        $module_sql = '';
        foreach ($teams as $individual_team) {
            if (trim($individual_team) != '') {
                $module_sql .= " OR team_name LIKE '%$individual_team%'";
            }
        }
        $resulta = $this->mysqli->prepare("SELECT id, name, team_name, color FROM folders WHERE (ownerID=? $module_sql)  AND deleted IS NULL ORDER BY name, id");
        $resulta->bind_param('i', $userID);
        $resulta->execute();
        $resulta->bind_result($id, $name, $team_name, $color);
        $resulta->store_result();
        while ($resulta->fetch()) {
            $count = mb_substr_count($name, ';');
            $exp = explode(';', $name);
            $name = array_pop($exp);
            $folderlst[] = array($id, $name, $team_name, $color, $count);
        }
        $resulta->close();
        $this->folderlst = $folderlst;
    }

    public function process()
    {
        $folderlst = $this->folderlst;
        $parent[0] = 0;
        foreach ($folderlst as $v) {
            list($id, $name, $team_name, $color, $count) = $v;
            $count1 = $count + 1;
            $folderlst2[$id] = array($id, $name, $team_name, $color, $count, $parent[$count]);
            $parent[$count1] = $id;
        }
        $this->folderlst2 = $folderlst2;
    }

    public function dump()
    {
        print 'FOLDERLST<pre>';
        print_r($this->folderlst);
        print '</pre><br>FOLDERLST2<pre>';
        print_r($this->folderlst2);
        print '</pre>';
    }

    public function getfolders($folder)
    {
        $retlst = array();
        foreach ($this->folderlst2 as $v) {
            list($id, $name, $team_name, $color, $count, $parent) = $v;
            if ($parent == $folder) {
                $retlst[] = array($id, $name, $team_name, $color, $count, $parent);
            }
        }
        return ($retlst);
    }

    public function countfolders($folder)
    {
        $lst = $this->getfolders($folder);
        return count($lst);
    }

    public function gettests($folder)
    {
        $tests = array();
        if ($folder != 0) {
            $mysqli = $this->mysqli;
            $results = $mysqli->prepare('SELECT property_id,paper_title,start_date,end_date,paper_type,paper_ownerID,deleted,crypt_name FROM properties WHERE folder=? AND deleted IS NULL');
            $results->bind_param('i', $folder);
            $results->execute();
            $results->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted, $crypt_name);
            $results->store_result();
            if ($results->num_rows() > 0) {
                while ($results->fetch()) {
                    $tests[] = array($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted, $crypt_name);
                }
            }
            $results->close();
        }
        return $tests;
    }


    public function counttests($folder)
    {
        $lst = $this->gettests($folder);
        return count($lst);
    }

    public function listtree($folder, $block_id, $plk, $level)
    {
        global $icons;
        $lst = $this->getfolders($folder);
        foreach ($lst as $v) {
            list($id, $name, $team_name, $color, $count, $parent) = $v;
            $cntfold = $this->countfolders($id);
            $cnttest = $this->counttests($id);
            if (($cnttest + $cntfold) > 0) {
                echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\" onclick=\"showHide($block_id)\"  /><a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">&nbsp;$name</a></div>\n";
                echo "<div id=\"block$block_id\" style=\"display:none; padding-left:22px\">";
                @ob_flush();
                @flush();
                if ($cntfold > 0) {
                    list($block_id, $plk) = $this->listtree($id, $block_id + 1, $plk, 0);
                }
                if ($cnttest > 0) {
                    $lst2 = $this->gettests($id);
                    foreach ($lst2 as $v2) {
                        list($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted, $crypt_name) = $v2;
                        echo '<div style="padding-left:24px"><a href="?paperlinkID=' . $plk . '"><img src="../artwork/' . $icons[$paper_type] . '_16.gif" width="16" height="16" border="0" alt="' . $paper_type . '" /></a>&nbsp;<a class="recent"';
                        if (mb_strpos($paper_title, '[deleted') !== false) {
                                echo ' style="color:#808080"';
                        }
                        echo 'href="?paperlinkID=' . $plk . '">' . $paper_title . "</a></div>\n";
                        @ob_flush();
                        @flush();
                        $_SESSION['postlookup'][$plk] = array($crypt_name, 0);
                        $plk++;
                    }
                }
                $block_id++;
                echo '</div>';
            } else {
                //no subfolders or tests
                echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\"   />&nbsp;$name</div>\n";
            }
        }
        @ob_flush();
        @flush();
        return (array($block_id, $plk));
    }
}
