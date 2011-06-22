<?php

require_once("st_question.php");
require_once("st_paper.php");

// main storage class, contains a bunch of questions,
// a bunch of papers which link to questions, and possibly some other stuff

class ST_Main
{
	var $papers;
	var $questions;
}

// class to store exported files
class ST_File
{
	var $filename;
	var $title;
	var $path;	
	var $type;
	var $id;
	
	function __construct($filename, $title, $path, $type = 'xml',$id = 0)
	{
		$this->filename = $filename;
		$this->title = $title;
		$this->path = $path;	
		$this->type = $type;
		$this->id = $id;
	}
	
}