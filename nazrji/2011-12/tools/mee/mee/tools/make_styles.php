<?php
echo "<h2>Building combined css files</h2>";

function compress($buffer) {
	/* remove comments */
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	/* remove tabs, spaces, newlines, etc. */
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	return $buffer;
}

/* your css files */
$output = "/*DO NOT MODIFY THIS FILE*/\n";
$files = array();
$files[] = 'edit.css';
$files[] = 'fonts.css';
$files[] = 'main.css';
$files[] = 'toolbar.css';
foreach($files as $file)
{
	echo "Compressing $file<br>";
	$output .= compress(file_get_contents('../css/' . $file));	
}

file_put_contents("../css/combined.css",$output);
echo "Saved as css/combined.css<br>";
?>