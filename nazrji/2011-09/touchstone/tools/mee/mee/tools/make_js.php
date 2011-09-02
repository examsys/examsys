<?php
echo "<h2>Building combined JS files</h2>";
$debug = 0;

$files = array();
$files[] = "jquery/jquery.caret.js";
$files[] = "jquery/jquery.class.js";
$files[] = "jquery/jquery.pxem.js";
$files[] = "jquery/jquery.scale9.js";
$files[] = "jquery/jquery.textarea.js";
$files[] = "jquery/jquery.cookie.js";
$files[] = "jquery/json2.js";

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
