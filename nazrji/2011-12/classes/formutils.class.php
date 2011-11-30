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

/**
* 
* Utility class for date related functionality
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

Class FormUtils {
	/**
	 * For select lists, radio buttons or check boxes - check if a given value is selected
	 * @param string $field Value in form field
	 * @param string $value Value to cjeck against
	 * @param string $label Lebel to use in returned string. Should be 'selected' or 'checked'
	 * @return string String to select/check the element
	 */
	public function check_selected($field, $value, $label='selected')
	{
		if($field == $value)
		{
			return $label.'="'.$label.'"';
		}
		
		return '';
	}
	
	/**
	 * Take a string of fields names and an associative array (e.g. $_POST) and return a
	 * new associative array populated with the field names and the value from the first
	 * array or an empty string
	 * @param array $fields
	 * @param array $array
	 * @return array Associative array with keys from $fields and values from $array
	 */
	public function pre_populate($fields, $array)
	{
		$new_array = array();
		
		if(!is_array($array))
		{
			$array = array();
		}
		
		foreach(explode(',', $fields) as $field)
		{
			$new_array[$field] = (!empty($array[$field])) ? $array[$field] : '';
		}
	
		return $new_array;
	}
	
	// VALIDATION 
	
	/**
	 * Check array of required fields (field name => pretty name) agains post variables
	 * @param array $fields List of fields to check existence of in $_POST
	 * @return array List of errors
	 */
	public function check_required($fields)
	{
		$errors = array();
		
		foreach($fields as $name => $pretty_name)
		{
			if(!isset($_POST[$name]) || $_POST[$name] == '')
			{
				$errors[] = "Field $pretty_name is required";
			}
		}
		
		return $errors;
	}
	
	/**
	 * Very simple check to see if input may contain a URL
	 * @param string $text Text to check
	 * @return boolean
	 */
	public function has_URL($text)
	{
		$rval = false;

		if (strpos(strtolower($text), "http", 0) !== false or strpos(strtolower($text), "www") !== false)
		{
			$rval = true;
		}
		
		return $rval;
	}
		
	/**
	 * Very simple check to see if input may contain more than one URL
	 * @param string $text Text to check
	 * @return boolean
	 */
		public function has_multiple_URLs($text)
	{
		$rval = false;

		$i = strpos(strtolower($text), "http",0);
		if ($i === false)
		{
			$rval = false;
		}
		else
		{
			$j = strpos(strtolower($text), "http", $i+1);
			if ($j === false)
			{
				$rval = false;
			}
			else
			{
				$rval = true;
			}
		}
		if (!$rval) {
			$i = strpos(strtolower($text), "www",0);
			if ($i === false)
			{
				$rval = false;
			}
			else
			{
				$j = strpos(strtolower($text), "www", $i+1);
				if ($j === false)
				{
					$rval = false;
				}
				else
				{
					$rval = true;
				}
			}
		}

		return $rval;
	}
		
	/**
	 * Check to see if input string is a URL
	 * @param string $text Text to check
	 * @return boolean
	 */
		public function is_URL($text)
	{
		$rval = false;
		
		if (preg_match("/^(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$/i", $text))
		{
			$rval = true;
		}
		
		return $rval;
	}
		
	/**
	 * Check to see if input string is an email address
	 * @param string $text Text to check
	 * @return boolean
	 */
	public function is_email($text)
	{
		$rval = false;
		
		if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $text))
		{
			$rval = true;
		}
		
		return $rval;
	}
	
	// Get a unique version of a given file name
	/**
	 * Get a unique version of a given file name
	 * @param string $base Filesystem location of the file within which it must be unique
	 * @param unknown_type $name File name
	 * @return string Unique filename
	 */
	public function get_unique_name($base, $name)
	{
		$unique_name = '';
		
		$file_parts = pathinfo($name);
		
    	$modifier = '';
    	$modifier_count = 1;
    	
    	do
    	{
    		$unique_name = $file_parts['filename'].$modifier.'.'.$file_parts['extension'];
    		$modifier = '-'.$modifier_count++;
    	}
    	while(file_exists($base.$unique_name));
    	
    	return $unique_name;
	}
}
