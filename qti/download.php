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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once 'include/inc.php';
$file = check_var('file', 'GET', true, false, true);
$file = str_replace('..', '', $file);
$path = check_var('path', 'GET', true, false, true);
$path = str_replace('..', '', $path);
$title = check_var('path', 'GET', true, false, true);
$qtiexportdirectory = rogo_directory::get_directory('qti_export');
$base_dir = $qtiexportdirectory->location();
$accessfile = $base_dir . $path . '/access.xml';
if (!file_exists($accessfile)) {
    exit;
}

$xmlStr = file_get_contents($accessfile);
$xml = simplexml_load_string($xmlStr);
if ($userObject->get_user_ID() != $xml->owner) {
    exit;
}

$xmlfile = $base_dir . $path . '/' . $file;
$ext = mb_strtolower(mb_substr($file, mb_strrpos($file, '.') + 1));
$filename = $file;
if ($title) {
    $filename = CleanFileName($title) . '.' . $ext;
}

function head($text)
{

    header($text);
}

head('Pragma: public');
if ($ext == 'xml') {
    head('Content-Type: text/xml; charset=UTF-8');
} elseif ($ext == 'zip') {
    head('Content-Type: application/zip');
} elseif ($ext == 'png') {
    head('Content-Type: image/png');
} elseif ($ext == 'gif') {
    head('Content-Type: image/gif');
} elseif ($ext == 'jpg' || $ext == 'jpeg') {
    head('Content-Type: image/jpeg');
}
head('Content-Length: ' . filesize($xmlfile));
head('Content-Disposition: attachment;filename="' . $filename . '"');
$fp = fopen($xmlfile, 'r');
fpassthru($fp);
fclose($fp);
