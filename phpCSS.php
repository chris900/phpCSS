<?php

require_once 'Selector.php';
require_once 'Element.php';

class phpCSS {
	private $dom;

	public function __construct($htmlfile) {
		if ($path = realpath($htmlfile)) {
			$htmlfile = file_get_contents($path);
		}
	
		$this->dom = new DomDocument();
		@$this->dom->loadHTML($htmlfile);
		$this->root_elm = new Element($this->dom->documentElement);
	}
	
	private function findAllBySelector($elms, $sel, $trans) {
		$ret_elms = array();
		foreach ($elms as $elm) {
			switch ($trans) {
				case 'end':
					break;
				case 'child':
					if ($elm->elm->hasChildNodes()) {
						foreach($elm->elm->childNodes as $item) {
							if (is_a($item, 'DOMElement')) {
								$e = new Element($item);
								if ($e->matchesSingleSelector($sel)) {
									$ret_elms[] = $e;
								}
							}
						}
					}
					break;
				case 'sibling':
					if ($sib = $elm->elm->nextSibling) {
						$e = new Element($sib);
						if (is_a($sib, 'DOMElement')) {
							if ($e->matchesSingleSelector($sel)) {
								$ret_elms[] = $e;
							}
						}
						$ret_elms = array_merge($ret_elms, $this->findAllBySelector(array($e), $sel, 'sibling'));
					}
					break;
				case 'immediate_sibling':
					if ($sib = $elm->elm->nextSibling) {
						$e = new Element($sib);
						if (is_a($sib, 'DOMElement')) {
							if ($e->matchesSingleSelector($sel)) {
								$ret_elms[] = $e;
							}
						}
						else {
							$ret_elms = array_merge($ret_elms, $this->findAllBySelector(array($e), $sel, 'immediate_sibling'));
						}
					}
					break;
				default: //for descend & start
					if ($trans == 'start' && ($sel['name'] === '*' || $sel['name'] === 'html')) {
						if ($elm->matchesSingleSelector($sel)) {
							$ret_elms[] = $elm;		
						}
						break;
					}
					if ($elm->elm->hasChildNodes()) {
						foreach($elm->elm->childNodes as $item) {
							if (is_a($item, 'DOMElement')) {
								$e = new Element($item);
								if ($e->matchesSingleSelector($sel)) {
									$ret_elms[] = $e;
								}
								$ret_elms = array_merge($ret_elms, $this->findAllBySelector(array($e), $sel, 'descend'));
							}
						}
					}
					break;
			}
		}
		return $ret_elms;
	}
	
	public function find($sel, $selms = false, $limit = false) {
		$selUtil = new SelectorUtil();
		$selectors = $selUtil->format($sel);
		if ($selms === false) {
			$selms = array($this->root_elm);
		}
		$parent_trans = 'start';
		
		foreach ($selectors as $selector) {
			$elms = $selms;
			foreach ($selector as $bit) {
				$elms = $this->findAllBySelector($elms, $bit, $parent_trans);
				$parent_trans = $bit['trans'];
				
				if (empty($elms) || $bit['trans'] == 'end') {
					break;
				}
			}
			if ($limit !== false) {
				$limit--;
				if ($limit <= 0) {
					return $elms;
				}
			}
		}
		if (empty($elms)) {
			return false;
		}
		return $elms;
	}

	public function selector_used($selector) {
		$elms = array($this->root_elm);
		$new_elms = false;
		$parent_trans = 'start';

		foreach ($selector as $bit) {			
			$elms = $this->findAllBySelector($elms, $bit, $parent_trans);
			$parent_trans = $bit['trans'];
			
			if (empty($elms)) {
				return false;
			}
			else if ($bit['trans'] == 'end') {
				return true;
			}
		}
		
		return true;
	}
}

