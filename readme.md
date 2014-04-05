phpCSS
========

-- use --

$PC = new phpCSS($htmldata);

$elms = $PC->find('div#selector');

foreach ($elms as $elm) {
	$elm->getAttributeValue('class');
	$elm->getName();
}



-- TODO --

FIX FOR * html
