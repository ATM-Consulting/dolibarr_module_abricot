<?php

class TTrigger {
	
	function __construct() {
		$this->TTrigger=array();
		
		
	}
	function execute(&$ATMdb, &$object, $className, $state) {
		
	}

	/**
	 * @param $ATMdb
	 * @param $object
	 * @param $className
	 * @param $state
	 * @return int -1 on error 0 idle or 1 on success
	 * Todo : il n'y a aucune gestion d'erreurs il faut en ajouter une vrai, j'ai ajouté le minimum pour l'instant...
	 */
	function run(&$ATMdb, &$object, $className, $state) {
		/* Execute les triggers */
		//print "Exécution du trigger ($className, $state)<br>";
		if(empty( $this->TTrigger )) { $this->loadTrigger($ATMdb); }

		if(empty($this->TTrigger)){
			return 0;
		}

		foreach($this->TTrigger as $trigger) {
			
			if(!empty($trigger['path']) && is_file($trigger['path'])) {
				require_once($trigger['path']);
				
				if(!empty($trigger['objectName']) && class_exists($trigger['objectName'])) {
					
					$t = new $trigger['objectName'];
					
					if(method_exists($t,'execute')) {
						$resTriggerRun = $t->execute($ATMdb, $object, $className, $state);
						if($resTriggerRun < 0){
							return -1;
						}
					}
				}
			}
		}

		return 0;
	}

	/**
	 * @param $ATMdb
	 * @param $path
	 * @param $objectName
	 * @return void
	 */
	function register(&$ATMdb, $path, $objectName) {
		/* Enregistre un nouveau trigger avec le chemin à charger et la method à appeler */
		
		//TODO add db
		
		$this->TTrigger[]=array('path'=>$path, 'objectName'=>$objectName);
		
	}

	/**
	 * Est-ce-que cette methode à un jour servie à quelque chose ?
	 * @param $ATMdb
	 * @return array
	 */
	function loadTrigger(&$ATMdb) {
		/* Charge la liste des triggers à exécuter */
		$this->TTrigger=array();
		
		
		return $this->TTrigger;
	}
}
