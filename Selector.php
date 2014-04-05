<?php


function spaces_to_next_char($as, $pos) {
	$length = count($as);
	$c = 0;
	for ($i = $pos; $i < $length; $i++) {
		$l = $as[$i];
		if ($l !== ' ') {
			return $c;
		}
		$c++;
	}
	return false;
}

function next_char_after_spaces($as, $pos) {
	$length = count($as);
	for ($i = $pos; $i < $length; $i++) {
		$l = $as[$i];
		if ($l !== ' ') {
			return $l;
		}
	}
	return false;
}
		

Class SelectorUtil {
	public $Data = false;

	public function __construct($sstring = false) {
		if ($sstring) {
			$this->Data = $this->format($sstring);
		}
	}
	
	public function toString($data = false) {
		if (!$data) {
			$data = $this->Data;
		}
		$r = '';
		foreach ($data as $a1) {
			foreach ($a1 as $a2) {
				if ($a2['name']) {
					$r .= $a2['name'];
				}
				if ($a2['id']) {
					$r .= '#'.$a2['id'];
				}
				if (!empty($a2['classes'])) {
					foreach($a2['classes'] as $cls) {
						$r .= '.'.$cls;
					}
				}
				if (!empty($a2['attr'])) {
					foreach($a2['attr'] as $attr) {
						$delim = '"';
						if (isset($attr['delim']) && $attr['delim']) {
							$delim = $attr['delim'];
						}
						$r .= '['.$attr['name'].$attr['opp'].$delim.$attr['value'].$delim.']';
					}
				}
				if (!empty($a2['pseudos'])) {
					foreach($a2['pseudos'] as $psd => $pvals) {
						foreach ($pvals as $pval) {
							$r .= ':'.$psd;
							if ($pval !== false) {
								$r .= '('.$pval.')';
							}
						}
					}
				}
				
				if ($a2['trans'] == 'descend') {
					$r .= ' ';
				}
				else if ($a2['trans'] == 'immediate_sibling') {
					$r .= ' + ';
				}
				else if ($a2['trans'] == 'sibling') {
					$r .= ' ~ ';
				}
				else if ($a2['trans'] == 'child') {
					$r .= ' > ';
				}
				else if ($a2['trans'] == 'end') {
					$r .= ', ';
				}
				else {
					$r .= ', ';
				}
			}
		}
		if (substr($r, -2) === ', ') {
			$r = substr($r, 0, -2);
		}
		return $r;
	}
	
	public function format($sstring) {
		$sstring = trim($sstring) . "\n";
		
		$as = str_split($sstring);
		$fin = array();
		$sel_count = 0;
		$desc_count = 0;
		$attr_count = 0;
		$pseudo = '';
		$last_letter = '';
		$key = false;
		$delim = false;
		$fdelim = false;
		$next_selector = false;
		$trans_selector = false;
		$reading_word = false;
		$word = '';
		
		$length = count($as);
		for ($i = 0; $i < $length; $i++) {
			$l = $as[$i];
			switch ($l) {
				case "\n":
					if ($key) {
						$fin[$sel_count][$desc_count][$key] = $word;
					}
					$fin[$sel_count][$desc_count]['trans'] = 'end';
					break;
				case ',':
					if ($reading_word) {
						if ($delim === false && $fdelim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$fin[$sel_count][$desc_count]['trans'] = 'end';
							$reading_word = false;
							$key = false;
							$word = '';
							$attr_count = 0;
							$next_selector = true;
						}
						else {
							$word .= $l;
						}
					}
					else {
						$fin[$sel_count][$desc_count]['trans'] = 'end';
						$attr_count = 0;
						$next_selector = true;
						$key = false;
						$delim = false;
						$fdelim = false;
						$word = '';
					}
					break;
				case ' ':
					if ($reading_word) {
						if ($delim === false) {
							if ($fdelim !== '[') {
								if ($key) {
									$fin[$sel_count][$desc_count][$key] = $word;
								}
								$reading_word = false;
								$key = false;
								$word = '';
							}
							if ($fdelim === false) {
								$nc = next_char_after_spaces($as, $i);
								if ($nc && $nc !== ',') {
									$fin[$sel_count][$desc_count]['trans'] = 'descend';
									$trans_selector = true;
									$attr_count = 0;
								}
								else {
									$snc = spaces_to_next_char($as, $i);
									if ($snc) {
										$i += $snc - 1;
									}
								}
							}
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = false;
						$delim = false;
					}
					break;
				case '+': 
					if ($delim === false && $fdelim === false) {
						$fin[$sel_count][$desc_count]['trans'] = 'immediate_sibling';
						$trans_selector = true;
						$attr_count = 0;
					}
					break;
				case '~': 
					if ($delim === false && $fdelim === false) {
						$fin[$sel_count][$desc_count]['trans'] = 'sibling';
						$trans_selector = true;
						$attr_count = 0;
					}
					break;
				case '>': 
					if ($delim === false && $fdelim === false) {
						$fin[$sel_count][$desc_count]['trans'] = 'child';
						$trans_selector = true;
						$attr_count = 0;
					}
					break;
				case '#':
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'id';
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'id';
						$reading_word = true;
					}
					break;
				case '.':
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'class';
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'class';
						$reading_word = true;
					}
					break;
				case ':':
					if ($reading_word) {
						if ($delim === false) {
							if ($last_letter !== ':') {
								if ($key) {
									$fin[$sel_count][$desc_count][$key] = $word;
								}
								$reading_word = true;
								$key = 'pseudo';
								$word = '';
							}
						}
						else {
							$word .= $l;
						}
					}
					if (!$reading_word) {
						$key = 'pseudo';
						$reading_word = true;
					}
					break;
				case '[':
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'attr_name';
							$fdelim = '[';
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'attr_name';
						$word = '';
						$reading_word = true;
					}
					break;
				case ']':
					if ($delim === false) {
						if ($key) {
							$fin[$sel_count][$desc_count][$key] = $word;
						}
						if (isset($fin[$sel_count][$desc_count]['attr_name']) && isset($fin[$sel_count][$desc_count]['attr_opp']) && isset($fin[$sel_count][$desc_count]['attr_val'])) {
							$fin[$sel_count][$desc_count]['attr'][$attr_count] = array('name' => $fin[$sel_count][$desc_count]['attr_name'], 'opp' => $fin[$sel_count][$desc_count]['attr_opp'], 'value' => $fin[$sel_count][$desc_count]['attr_val'], 'delim' => $fin[$sel_count][$desc_count]['attr_delim']);
							unset($fin[$sel_count][$desc_count]['attr_name']);
							unset($fin[$sel_count][$desc_count]['attr_opp']);
							unset($fin[$sel_count][$desc_count]['attr_val']);
							unset($fin[$sel_count][$desc_count]['attr_delim']);
						}
						$reading_word = false;
						$key = false;
						$fdelim = false;
						$word = '';
					}
					else {
						$word .= $l;
					}
					break;
				case '=': 
					if ($delim === false && $fdelim === '[') {
						$op = '=';
						switch ($last_letter) {
							case '~': 
								$op = '~=';
								break;
							case '^':
								$op = '^=';
								break;
							case '$':
								$op = '$=';
								break;
							case '*':
								$op = '*=';
								break;
							case '|':
								$op = '|=';
								break;
						}
						$fin[$sel_count][$desc_count]['attr_opp'] = $op;
						break;
					}
					/*
				case '~': 
					if ($delim === false) {
						break;
					}
					*/
				case '^': 
					if ($delim === false) {
						break;
					}
				case '$': 
					if ($delim === false) {
						break;
					}
				case '*': 
					if ($delim === false) {
						if (!isset($as[$i+1]) || (isset($as[$i+1]) && $as[$i+1] !== '=')) {
							if (!$reading_word) {
								$key = 'name';
								$reading_word = true;
							}
							$word .= $l;
						}
						break;
					}
				case '|': 
					if ($delim === false) {
						break;
					}
				case '(':
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'pseudo_val';
							$delim = '(';
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'pseudo_val';
						$word = '';
						$reading_word = true;
					}
					break;
				case ')':
					if ($reading_word) {
						if ($delim == '(') {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = false;
							$key = false;
							$delim = false;
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					break;
				case '"':
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'attr_val';
							$delim = '"';
							$word = '';
							$fin[$sel_count][$desc_count]['attr_delim'] = $delim;
						}
						else if ($delim === '"') {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = false;
							$key = false;
							$delim = false;
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'attr_val';
						$delim = '"';
						$word = '';
						$reading_word = true;
						$fin[$sel_count][$desc_count]['attr_delim'] = $delim;
					}
					break;
				case "'":
					if ($reading_word) {
						if ($delim === false) {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = true;
							$key = 'attr_val';
							$delim = "'";
							$word = '';
							$fin[$sel_count][$desc_count]['attr_delim'] = $delim;
						}
						else if ($delim === "'") {
							if ($key) {
								$fin[$sel_count][$desc_count][$key] = $word;
							}
							$reading_word = false;
							$key = false;
							$delim = false;
							$word = '';
						}
						else {
							$word .= $l;
						}
					}
					else {
						$key = 'attr_val';
						$delim = "'";
						$word = '';
						$reading_word = true;
						$fin[$sel_count][$desc_count]['attr_delim'] = $delim;
					}
					break;
				default:
					if (!$reading_word) {
						$key = 'name';
						$reading_word = true;
					}
					$word .= $l;
			}
			$last_letter = $l;
			if (isset($fin[$sel_count][$desc_count]['class'])) {
				$fin[$sel_count][$desc_count]['classes'][] = $fin[$sel_count][$desc_count]['class'];
				unset($fin[$sel_count][$desc_count]['class']);
			}
			if (isset($fin[$sel_count][$desc_count]['pseudo'])) {
				$pseudo = $fin[$sel_count][$desc_count]['pseudo'];
				$fin[$sel_count][$desc_count]['pseudos'][$pseudo][] = false;
				unset($fin[$sel_count][$desc_count]['pseudo']);
			}
			if ($pseudo && isset($fin[$sel_count][$desc_count]['pseudo_val'])) {
				foreach ($fin[$sel_count][$desc_count]['pseudos'][$pseudo] as $psdk => $psdtmp) {
					if ($psdtmp === false) {
						unset($fin[$sel_count][$desc_count]['pseudos'][$pseudo][$psdk]);
					}
				}
				$fin[$sel_count][$desc_count]['pseudos'][$pseudo][] = trim($fin[$sel_count][$desc_count]['pseudo_val']);
				unset($fin[$sel_count][$desc_count]['pseudo_val']);
			}
			
			if ($next_selector) {
				$next_selector = false;
				$sel_count++;
				$desc_count = 0;
			}
			if ($trans_selector) {
				$trans_selector = false;
				$desc_count++;
			}
		}
		
		foreach ($fin as $k1 => $sels) {
			foreach ($sels as $k2 => $bits) {
				if (!isset($bits['name'])) {
					$fin[$k1][$k2]['name'] = false;
				}
				
				if (!isset($bits['id'])) {
					$fin[$k1][$k2]['id'] = false;
				}
				
				if (!isset($bits['classes'])) {
					$fin[$k1][$k2]['classes'] = array();
				}
				
				if (!isset($bits['attr'])) {
					$fin[$k1][$k2]['attr'] = array();
				}
				
				if (!isset($bits['pseudos'])) {
					$fin[$k1][$k2]['pseudos'] = array();
				}
				
				if (!isset($bits['trans'])) {
					$fin[$k1][$k2]['trans'] = 'end';
				}
			}
		}
		
		return $fin;
	}
	
}


