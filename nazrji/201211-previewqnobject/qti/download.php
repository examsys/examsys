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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

require_once 'include/inc.php';

$file = GetVar('file');
$file = str_replace("..", "", $file);
$path = GetVar('path');
$path = str_replace("..", "", $path);
$title = GetVar('title');

$base_dir = $cfg_web_root.'qti/exports/';

$accessfile = $base_dir.$path."/access.xml";
if (!file_exists($accessfile)) exit;

$xmlStr = file_get_contents($accessfile);
$xml = simplexml_load_string($xmlStr);

if ($userID != $xml->owner) exit;

$xmlfile = $base_dir.$path."/".$file;
$ext = strtolower(substr($file, strrpos($file, ".") + 1));

$filename = $file;
if ($title) $filename = CleanFileName($title).".".$ext;

function head($text) {
  //echo($text."\n");
  header($text);
}

head('Pragma: public');
if ($ext == "xml") {
  head('Content-Type: text/xml; charset=UTF-8');
} elseif ($ext == "zip") {
  head('Content-Type: application/zip');
} else if ($ext == "png") {
  head('Content-Type: image/png');
} else if ($ext == "gif") {
  head('Content-Type: image/gif');
} else if ($ext == "jpg" || $ext == "jpeg") {
  head('Content-Type: image/jpeg');
}
head('Content-Length: '.filesize($xmlfile));
head('Content-Disposition: attachment;filename="'.$filename.'"');

$fp = fopen($xmlfile, 'r');
fpassthru($fp);
fclose($fp);
