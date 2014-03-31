<?php

class TTrigger {
	
	function __construct() {
		$this->TTrigger=array();
		
		
	}
	function execute(&$ATMdb, &$object, $className, $state) {
		
	}
	function run(&$ATMdb, &$object, $className, $state) {
		/* Execute les triggers */
		//print "Exécution du trigger ($className, $state)<br>";
		if(empty( $this->TTrigger )) { $this->loadTrigger($ATMdb); }
		
		foreach($this->TTrigger as $trigger) {
			
			if(is_file($triger['path'])) {
				require_once($triger['path']);
				
				if(class_exists($trigger['objectName'])) {
					
					$t = new $trigger['objectName'];
					
					if(method_exists($t,'execute')) {
						$t->execute($ATMdb, $object, $className, $state); 
					}
					
				}
			}
		}
		
	}
	
	function register(&$ATMdb, $path, $objectName) {
		/* Enregistre un nouveau trigger avec le chemin à charger et la method à appeler */
		
		//TODO add db
		
		$this->TTrigger[]=array('path'=>$path, 'objectName'=>$objectName);
		
	}
	function loadTrigger(&$ATMdb) {
		/* Charge la liste des triggers à exécuter */
		$this->TTrigger=array();
		
		
		return $this->TTrigger;
	}
}
