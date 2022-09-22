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
 * This pixelates a student photo to protect the student's identity. Useful
 * when ExamSys is in demo mode.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once dirname(__DIR__) . '/include/autoload.inc.php';
autoloader::init();

header('Content-Type: image/jpeg');

$photodirectory = rogo_directory::get_directory('user_photo');
$photoname = UserUtils::student_photo_exist($_GET['username']);

if ($photoname) {
    $fullpath = $photodirectory->fullpath($photoname);
    $fileinfo = new finfo(FILEINFO_MIME_TYPE);
    // Should be able to handle several filetypes.
    switch ($fileinfo->file($fullpath)) {
        case 'image/jpeg':
        case 'image/pjpeg':
            $im = imagecreatefromjpeg($fullpath);
            break;
        case 'image/png':
            $im = imagecreatefrompng($fullpath);
            break;
        case 'image/gif':
            $im = imagecreatefromgif($fullpath);
            break;
        default:
            // Just create a 1 pixel image if everything else fails.
            $im = imagecreate(1, 1);
            break;
    }
} else {
    $im = imagecreate(1, 1);
}

imagefilter($im, IMG_FILTER_PIXELATE, 7, true);

imagejpeg($im);

imagedestroy($im);
