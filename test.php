<?php
chdir(dirname(__FILE__));

require_once "phpCSS.php";


$PC = new phpCSS('./test.html');


$found = $PC->find("#cont p");
foreach ($found as $elm) {
	echo $elm->getValue() . "\n";
}

