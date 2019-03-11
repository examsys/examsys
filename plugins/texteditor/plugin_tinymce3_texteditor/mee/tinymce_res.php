<?php

echo "<pre>";
foreach ($_POST as $var => $value)
{
	echo "$var => \n";
	echo htmlentities($value) . "\n";
}
echo "</pre>";