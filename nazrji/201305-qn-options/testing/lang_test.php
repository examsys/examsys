<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version . 
//
// Rogo is distributed in the hope that it will be useful, 
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE .  See the
// GNU General Public License for more details . 
//
// You should have received a copy of the GNU General Public License
// along with Rogo .  If not, see <http://www . gnu . org/licenses/> . 

/**
* 
* Script reads all the language files in /lang folder and compares strings in search for strings that are empty, duplicate, identical etc . 
* 
* @author Nikodem Miranowicz
* @version 1 . 0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
?>
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1 . 0 Transitional//EN" "http://www . w3 . org/TR/xhtml1/DTD/xhtml1-transitional . dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title>lang test</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <style type="text/css">
body {
	padding-left: 20px;
}
table {
	padding-left: 20px;
}</style>
</head>

<body>
<?php

//function for recursive files search
function file_array($path, $exclude) {
  global $files;

  $path = rtrim($path, "/") . "/";
  $result = array();
  $folder_handle = opendir($path);
  while(false !== ($filename = readdir($folder_handle))) {
    if (!in_array(strtolower($filename), $exclude)) {
      if (is_dir($path . $filename . "/")) {
        $result[] = file_array($path . $filename . "/", $exclude);
      } else {
        array_push($files, $path . $filename);
      }
    }
  }
  return $result;
}
 
//function for reading string into the array
function file_array_read($files, $lang) {
  $strings = array(array());
	foreach($files as $filepath) {
    $filepath_parts = preg_split('/\/en\//', $filepath);
    $filepath = $filepath_parts[0] . '/' . $lang . '/' . $filepath_parts[1];
    if (file_exists($filepath)) {
      $file_contents = file_get_contents($filepath);
      $file_contents = preg_replace('/</', '&lt;', $file_contents);
      //split the lines
      $file_lines = preg_split('/\n/', $file_contents);
      foreach ($file_lines as $file_line) {
        $file_line = trim ($file_line);
        //split the string array element line
        if (substr($file_line, 0, 7)=='$string') {
          $file_line_parts = preg_split('/=/', $file_line);
          $line_string = $file_line_parts[0];
          $line_text = trim(substr($file_line, strlen($line_string)));
          //remove comments except for '//cognate'
          if ((strpos($line_text, '//') !== false) and (strpos($line_text, '//cognate') === false)) {
            $line_text = trim(substr($line_text, 0, strpos($line_text, '//')));
          }
          $line_text = preg_replace('/^[\s=\s\"\']+/', '', $line_text);
          $line_text = preg_replace('/[\'\";\s]+$/', '', $line_text);

          $line_string = preg_replace('/^[\s]+/', '', $line_string);
          $line_string = preg_replace('/[\s]+$/', '', $line_string);
          $line_string = substr($line_string, 7, -1);
          $line_string = preg_replace('/^[\[][\']/', '', $line_string);
          $line_string = preg_replace('/[\]]$/', '', $line_string);
          $line_string = preg_replace('/[\']$/', '', $line_string);
          
          if (!isset($strings[$line_string])) {
            $strings[$line_string] = array($filepath_parts[1], $line_string, $line_text, 1);
          } else {
            $strings[$line_string][0] .= '|' . $filepath_parts[1];
            $strings[$line_string][2] .= '|' . $line_text;
            $strings[$line_string][3] += 1;
          }
        }
      }	
    } else {
      $strings[$filepath_parts[1]] = array($filepath_parts[1], '', '', 1);
    }
  }
  return $strings;
}

$last_key = '';
$last_value = '';
 
function display_this($data, $data_index) {
	global $display_text;
  global $last_key,$last_value;
  
  $data_part1= explode('|', $data[0]);
	$data_part2= $data[1];
	$data_part3= explode('|', $data[2]);
	if ($data_part2!='') {
		if ($data_index=='-1') {
      foreach ($data_part1 as $data_key => $data_element) {
        $display_text .= '<tr><td>';
        if (isset($data_part1[$data_key])) $display_text .= '<em>' . $data_part1[$data_key] . '</em>';
        $display_text .= '</td><td>';
        if (isset($data_part2)) {
          if ($data_part2 != $last_key) {
            $display_text .= '<strong>' . $data_part2 . '</strong>';
          }
          else {
            $display_text .= '<span style="color:lightgrey"><strong>&nbsp;&nbsp;&nbsp;-<small>//</small>-</strong></span>';
          }
        }
        $display_text .= '</td><td>';
        
        if (isset($data_part3[$data_key])) {
          if ($data_part3[$data_key] != $last_value) {
            if ($data_part2 != $last_key) {
              $display_text .= $data_part3[$data_key];
            }
            else {
              $display_text .= '<span style="color:IndianRed">' . $data_part3[$data_key] . '</span>';
            }
          }
          else {
            $display_text .= '<span style="color:lightgrey"><strong>&nbsp;&nbsp;&nbsp;-<small>//</small>-</strong></span>';
          }
        }
        $display_text .= '</td></tr>';
        $last_key = $data_part2;
        $last_value = $data_part3[$data_key];
			}
		} else {
			$display_text .= '<tr><td><em>' . $data_part1[$data_index] . '</em></td><td><strong>' . $data_part2 . '</strong></td><td>' . $data_part3[$data_index] . '</td></tr>';
		}
	}
}

//----------------------------------------------------------------------------------------------

//exclusion list
$excluded = explode("|",  ".|..|.ds_store|.svn|en");

//path for lang list
$path=preg_replace('/testing/', '', getcwd()) . 'lang/';

//list of lang folders
$lang_array=Array();

$folder_handle = opendir($path);
while(false !== ($filename = readdir($folder_handle))) {
  if (!in_array(strtolower($filename), $excluded)) {
    if (is_dir($path . $filename . "/")) {
      array_push($lang_array, $filename);
    }
  }
}

//show just the lang specified by '?lang=...'
$spec_lang = '';
if (isset($_GET['lang'])) $spec_lang = $_GET['lang'];

//path for folders inside /en/
$path=preg_replace('/testing/', '', getcwd()) . 'lang/en/';

//searching for files
$files = Array();
$paths = Array();
$paths = file_array($path, $excluded);

//test list of searched files
$strings_en = file_array_read($files, 'en');
if (empty($strings_en[0])) unset($strings_en[0]);

foreach ($lang_array as $lang) {
  if ($spec_lang == '' OR $spec_lang == $lang) {
    $strings_pl = file_array_read($files, $lang);
    if (empty($strings_pl[0])) unset($strings_pl[0]);

    echo '<h2 class="midblue_header">Analysis for: ' . $lang . '</h2>';
    
    //Missing files
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_pl as $strings_key => $strings_data)
      if ($strings_data[0]==$strings_key) $display_text .= '<em>' . $strings_data[0] . '</em><br />';
    echo '<h3>Missing files:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';
    
    //Strings in missing files
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_en as $strings_key => $strings_data) {
      $data_path = explode("|", $strings_data[0]);
      foreach ($data_path as $data_path_key => $data_path_elem) {
        if (isset($strings_pl[$data_path_elem])) display_this($strings_data, $data_path_key);
      }
    }
    echo '<h3>Strings in missing files:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Missing strings
    $last_key = '';
    $last_value = '';
    $display_text = '';
    //var_dump($strings_en);
    foreach ($strings_en as $strings_key => $strings_data)
      if (!isset($strings_pl[$strings_key]) and (!isset($strings_pl[$strings_data[0]]))) display_this($strings_data, -1);
    echo '<h3>Missing strings:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Strings missing from file
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_en as $strings_key => $strings_data) {
      if (isset($strings_pl[$strings_key]) and ($strings_pl[$strings_key][0]!=$strings_data[0])) {
        $data_path1 = explode("|", $strings_data[0]);
        $data_path3 = explode("|", $strings_pl[$strings_key][0]);
        $data_path3 = array_diff($data_path1, $data_path3);
        display_this(Array(implode(", ", $data_path3), $strings_data[1], $strings_data[2], $strings_data[3]), -1);
      }
    }   
    echo '<h3>Strings missing from file:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Files with empty keys for the \'string\' array
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_pl as $strings_key => $strings_data) {
      if ($strings_data[1]=='') {
      $data_path1 = explode("|", $strings_data[0]);
      foreach ($data_path1 as $data_path1_key => $data_path1_elem) {
          $display_text .= '<em>' . $data_path1_elem . '</em><br />';
        }
      }
    }
    echo '<h3>Files with empty keys for the \'string\' array:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Duplicate strings in files
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_pl as $strings_key => $strings_data)	{
      if ($strings_pl[$strings_key][3]>1)	{
        $data_path1 = explode("|", $strings_data[0]);
        $data_path3 = array_unique($data_path1);
        $data_path3 = array_count_values($data_path1);
        if (count($data_path3)!=count($data_path1)) {
          foreach ($data_path3 as $data_path3_key => $data_path3_elem) {
            if ($data_path3_elem>1)  display_this(Array($data_path3_key, $strings_data[1], $strings_data[2], $strings_data[3]), -1);
          }
        }
      }
    }
    echo '<h3>Duplicate strings in files:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Identical texts
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_en as $strings_key => $strings_data) 
      if (isset($strings_pl[$strings_key]) and $strings_pl[$strings_key]==$strings_en[$strings_key] and strpos($strings_data[2],'&lt;&lt;&lt;')==-1) display_this($strings_data, -1);
    echo '<h3>Identical texts:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';

    //Identical strings in files
    $last_key = '';
    $last_value = '';
    $display_text = '';
    foreach ($strings_en as $strings_key => $strings_data)	{
      if ($strings_en[$strings_key][3]>1) {
        $data_path1 = explode("|", $strings_data[2]);
        $data_path3 = explode("|", $strings_pl[$strings_key][2]);
        if (count($data_path3)==count($data_path1)) {
          foreach ($data_path1 as $data_path1_key => $data_path1_elem) {
            if (($data_path1[$data_path1_key]==$data_path3[$data_path1_key]))	{
            display_this($strings_pl[$strings_key], $data_path1_key);
            }
          }
        }
      }
    }
    echo '<h3>Identical strings in files:</h3>';
    if ($display_text=='') $display_text='<tr><td>none</td></tr>';
    echo '<table>'.$display_text.'</table>';
  }
}
 
//---------------------------------------------------------------------------

echo '<hr>';
echo '<h2 class="midblue_header">Analysis for: en</h2>';

//Files with empty keys for the \'string\' array
$last_key = '';
$last_value = '';
$display_text = '';
foreach ($strings_en as $strings_key => $strings_data)	{
	if ($strings_data[1]=='')	{
    $data_path1 = explode("|", $strings_data[0]);
    foreach ($data_path1 as $data_path1_key => $data_path1_elem) {
      $display_text .= '<em>' . $data_path1_elem . '</em><br />';
    }
  }
}
echo '<h3>Files with empty keys for the \'string\' array:</h3>';
	if ($display_text=='') $display_text='<tr><td>none</td></tr>';
echo '<table>'.$display_text.'</table>';

//Duplicate strings in files
$last_key = '';
$last_value = '';
$display_text = '';
foreach ($strings_en as $strings_key => $strings_data)	{
	if ($strings_en[$strings_key][3]>1) {
    $data_path1 = explode("|", $strings_data[0]);
    $data_path3 = array_unique($data_path1);
    $data_path3 = array_count_values($data_path1);
    if (count($data_path3)!=count($data_path1)) {
      foreach ($data_path3 as $data_path3_key => $data_path3_elem) {
        if ($data_path3_elem>1) display_this(Array($data_path3_key, $strings_data[1], $strings_data[2], $strings_data[3]), -1);
      }
    }
  }
}
echo '<h3>Duplicate strings in files:</h3>';
	if ($display_text=='') $display_text='<tr><td>none</td></tr>';
echo '<table>'.$display_text.'</table>';

//Duplicate strings
$last_key = '';
$last_value = '';
$display_text = '';
foreach ($strings_en as $strings_key => $strings_data)	{
	if ($strings_en[$strings_key][3]>1) {
		display_this($strings_data, -1);
	}
}
echo '<h3>Duplicate strings:</h3>';
	if ($display_text=='') $display_text='<tr><td>none</td></tr>';
echo '<table>'.$display_text.'</table>';

echo '<hr>';
?>
</body>