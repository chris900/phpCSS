<?php

//removes multiple delimiters if they are next to each other
function multi_explode ($d, $s) {
	$t = explode($d, $s);
	$r = array();
	foreach ($t as $k => $v) {
		if ($v !== '') {
			$r[] = $v;
		}
	}
	return $r;
}

class Element {
	public $elm;
	public $parent;

	public function __construct($elm) {
		$this->elm = $elm;
		$this->parent = $this->elm->parentNode;
	}
	
	public function getName() {
		return $this->elm->nodeName;
	}
	public function isCalled($name) {
		if ($name === '*' || $name === 'html' || $name === 'body') {
			return true;   // quick fix for * html
		}
		return ($this->getName() === $name);
	}
	
	public function getValue() {
		return $this->elm->nodeValue;
	}
	
	public function getId() {
		if ($this->elm->hasAttributes()) {
			if ($ret = $this->elm->attributes->getNamedItem('id')) {
				return $ret->nodeValue;
			}
		}
		return false;
	}
	public function isId($id) {
		if ($ret = $this->getId()) {
			return ($ret === $id);
		}
		return false;
	}
	
	public function getClasses() {
		if ($this->elm->hasAttributes()) {
			if ($ret = $this->elm->attributes->getNamedItem('class')) {
				return multi_explode(' ', $ret->nodeValue);
			}
		}
		return false;
	}
	public function hasClass($class) {
		if ($ret = $this->getClasses()) {
			return (in_array($class, $ret));
		}
		return false;
	}
	
	public function getAttributes() {
		if ($this->elm->hasAttributes()) {
			$ret = array();
			foreach ($this->elm->attributes as $attr) {
				$ret[$attr->nodeName] = trim($attr->nodeValue);
			}
			return $ret;
		}
		return false;
	}
	public function getAttributeValue($attr) {
		if ($ret = $this->getAttributes()) {
			if (isset($ret[$attr])) {
				return $ret[$attr];
			}
		}
		return false;
	}
	public function AttributeMatches($attr, $opp, $val) {
		if ($ret = $this->getAttributeValue($attr)) {
			if ($opp == '~=') {
				$list = multi_explode(' ', $ret);
				return (in_array($val, $list));
			}
			else if ($opp == '^=') {
				return (substr($ret, 0, strlen($val)) == $val);
			}
			else if ($opp == '$=') {
				return (substr($ret, -strlen($val)) == $val);
			}
			else if ($opp == '*=') {
				retrun (strpos($ret, $val) !== false);
			}
			else if ($opp == '|=') {
				$list = multi_explode('-', $ret);
				return ($val == $list[0]);
			}
			else {  // if $opp is '='
				return ($ret == $val);
			}
		}
		return false;
	}
	
	public function matchesSingleSelector($sel) {
		if ($sel['name'] !== false) {
			if ($sel['name'] == '*') {
				return true;
			}
			else if (!$this->isCalled($sel['name'])) {
				return false;
			}
		}
		
		if ($sel['id'] !== false) {
			if (!$this->isId($sel['id'])) {
				return false;
			}
		}
		
		if (!empty($sel['classes'])) {
			foreach ($sel['classes'] as $class) {
				if (!$this->hasClass($class)) {
					return false;
				}
			}
		}
		
		if (!empty($sel['attr'])) {
			foreach ($sel['attr'] as $attr) {
				if (!$this->attributeMatches($attr['name'], $attr['opp'], $attr['value'])) {
					return false;
				}
			}
		}
		
		if (!empty($sel['pseudos'])) {
			foreach ($sel['pseudos'] as $pseudo => $pval) {
				if (!$this->is($pseudo, $pval)) {
					return false;
				}
			}
		}
		return true;
	}
	
	public function is($value, $data = false) {
		switch (strtolower($value)) {
			case 'root':
				if (!$this->isCalled('html')) {
					return false;
				}
				break;
			case 'nth-child':
				
				break;
			case 'nth-last-child':
			
				break;
			case 'nth-of-type':
			
				break;
			case 'nth-last-of-type':
			
				break;
			case 'first-child':
				if (!$this->parent->firstChild->isSameNode($this->elm)) {
					return false;
				}
				break;
			case 'last-child':
				if (!$this->parent->lastChild->isSameNode($this->elm)) {
					return false;
				}
				break;
			case 'first-of-type':
				
				break;
			case 'last-of-type':
			
				break;
			case 'only-child':
				if (!$this->parent->lastChild->isSameNode($this->parent->firstChild)) {
					return false;
				}
				break;
			case 'only-of-type':
				
				break;
			case 'empty':
				if (!$this->elm->hasChildNodes()) {
					return false;
				}
				else {
					foreach($elm->elm->childNodes as $item) {
						if (is_a($item, 'DOMElement')) {
							return false;
						}
					}
				}
				break;
			case 'link':
				if (!$this->isCalled('a')) {
					return false;
				}
				break;
			case 'visited':
				if (!$this->isCalled('a')) {
					return false;
				}
				break;
			case 'active':
				return true;
				break;
			case 'hover':
				return true;
				break;
			case 'focus':
				return true;
				break;
			case 'target':
				return true;
				break;
			case 'lang':
				return true;
				break;
			case 'enabled':
				return true;
				break;
			case 'disabled':
				return true;
				break;
			case 'checked':
				return true;
				break;
			case 'first-line':
				if (!$this->elm->hasChildNodes()) {
					return false;
				}
				break;
			case 'first-letter':
				if (!$this->elm->hasChildNodes()) {
					return false;
				}
				break;
			case 'before':
				return true;
				break;
			case 'after':
				return true;
				break;
			case 'not':
				$su = new SelectorUtil($data);
				if ($e->matchesSingleSelector($su->Data[0][0])) {
					return false;
				}
				break;
		}
		
		return true;
	}
}





