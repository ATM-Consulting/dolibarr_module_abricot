<?php

class TObserver {
	
	
	
}

class TConstraint {
	
	static function check(&$value, &$TConstraint,$autoset=false) {
		
		if(empty($TConstraint)) return true;
		else {
			
			if(isset($TConstraint['min'])) {
				if($value<$TConstraint['min'])return TConstraint::setCheck($value, $TConstraint['min'], $autoset);
			}
			if(isset($TConstraint['max'])) {
				if($value>$TConstraint['max']) return TConstraint::setCheck($value, $TConstraint['max'], $autoset);
			}
			if(isset($TConstraint['not-null'])) {
				if(is_null($value)) return false;
			}
		}
		
		return true;		
	}
	
	static function setCheck(&$value, $contraint, $set) {
		if($set) {
			$value = $contraint;
			return true;
		}
		else {
			return false;
		}
	}
	
}
