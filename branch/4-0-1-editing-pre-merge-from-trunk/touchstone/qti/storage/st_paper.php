<?php

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