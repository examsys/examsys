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

class ST_Paper {
  var $load_id;
  var $save_id;

  var $paper_title;
  var $rubric;
  var $screens = array(); // array of ST_Paper_Screen key by screen no

  var $nextscreen = 1;
  var $nextquestion = 1;

  function GetNextScreenID() {
    $i = $this->nextscreen;
    $this->nextscreen++;
    return $i;
  }

  function GetNextQuestionID() {
    $i = $this->nextquestion;
    $this->nextquestion++;
    return $i;
  }
}

class ST_Paper_Screen {
  var $question_ids = array(); // array of question ids key by ordering
}
