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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../../../include/sysadmin_auth.inc';

echo "<h2>Building combined JS files</h2>";
$debug = 0;

$files = array();
$files[] = "jquery/async.js";
$files[] = "jquery/jquery.caret.js";
$files[] = "jquery/jquery.class.js";
$files[] = "jquery/jquery.pxem.js";
$files[] = "jquery/jquery.scale9.js";
$files[] = "jquery/jquery.textarea.js";
$files[] = "jquery/jquery.cookie.js";
$files[] = "jquery/json2.js";
$files[] = "jquery/jquery.xml2json.js";

$files[] = "js/mee_comp.js";

$files[] = "js/mee.main.js";
$files[] = "js/mee.main.edit.js";
$files[] = "js/mee.main.display.js";
$files[] = "js/mee.tools.html.js";
$files[] = "js/mee.parser.js";
$files[] = "js/mee.data.js";
$files[] = "js/mee.data.tex.js";
$files[] = "js/mee.data.chars.js";

$files[] = "js/mee.elem.js";
$files[] = "js/mee.elem.accent.js";
$files[] = "js/mee.elem.boxed.js";
$files[] = "js/mee.elem.space.js";
$files[] = "js/mee.elem.input.js";
$files[] = "js/mee.elem.answer.js";
$files[] = "js/mee.elem.bond.js";

$files[] = "js/mee.elemset.js";
$files[] = "js/mee.elemset.normal.js";
$files[] = "js/mee.elemset.basic.js";
$files[] = "js/mee.elemset.array.js";

$files[] = "js/mee.toolbar.js";
$files[] = "js/mee.base.js";
$files[] = "js/mee.images.js";

$files[] = "js/mee.undo.js";
$files[] = "js/mee.symhist.js";
$files[] = "js/mee.font.js";
$files[] = "js/mee.maxima.js";

require("include/jsmin.php");
$js = "/*DO NOT MODIFY THIS FILE*/\n";
foreach ($files as $file)
{
	echo "Compressing $file<br>";
	$js .= JSMin::minify(file_get_contents("../".$file)) . "\n";
	//$js .= file_get_contents("../".$file) . "\n";
}
file_put_contents("../js/mee.js",$js);
echo "Saved as js/mee.js<br>";
?>
