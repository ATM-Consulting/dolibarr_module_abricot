<?php
/*
 * Configuration du noyau
 * 
 * Les constantes suivantes devront déjà être définie
 * 
 * DB_DRIVER, DB_NAME, DB_USER, DB_PASS,DB_HOST // pour les connexions bases de données
 * BATIGOOG_DB_NAME, BATIGOOG_DB_USER, BATIGOOG_DB_PASS, BATIGOOG_DB_HOST // en cas d'utilisation de BatiGooG
 * 
 */ 
 	if(defined('ATM_CORE_INCLUDED')) {
 		null;
		
 	}
	else {
	
		 	if(!defined('DOL_PACKAGE')) define('DOL_PACKAGE',false); /* Modèle de donnée DOLIBARR ou Standart ? impact la classe objet standart */
		 
		 	// define('OBJETSTD_MAKETABLEFORME'); // à décommenter pour création automatique des tables /!\ ne pas laisser en prod !
		 
		 	if(DOL_PACKAGE) {
				/*
				 * Adaptation Dolibarr
				 */
				define('OBJETSTD_MASTERKEY', 'rowid');
				define('OBJETSTD_DATECREATE', 'date_cre');
				define('OBJETSTD_DATEUPDATE', 'date_maj');
				define('OBJETSTD_DATEMASK', 'date_');
			
			}	
			else {
				define('OBJETSTD_MASTERKEY', 'id');
				define('OBJETSTD_DATECREATE', 'dt_cre');
				define('OBJETSTD_DATEUPDATE', 'dt_maj');
				define('OBJETSTD_DATEMASK', 'dt_');
				
			}
				
		 
		 	//RS : pour faire marcher les importsql
			if(!defined('ROOT')){
				define('ROOT','/var/www/ATM/atm-core/');
				define('HTTP','http://127.0.0.1/ATM/atm-core/');
			}
		
			if(!defined('COREROOT')) {
				define('COREROOT',ROOT);	
				define('COREHTTP',HTTP);
			}
			
			define('CORECLASS',COREROOT.'includes/class/');
			define('COREFCT',COREROOT.'includes/');
			
		/*
		 * Inclusion des classes Core
		 * Le require_once permet de surcharger préalablement ces classes
		 */
		 	if(!defined('NO_CORE_DB')){
			   	require_once(CORECLASS.'class.pdo.db.php');
		 	}
		 	require_once(CORECLASS.'class.objet_std.php');
		 	if(DOL_PACKAGE) {
		 		require_once(CORECLASS.'class.objet_std_dolibarr.php');
			}
		 	require_once(CORECLASS.'class.trigger.php');
		 	require_once(CORECLASS.'class.reponse.mail.php');
		 	require_once(CORECLASS.'class.cache_file.php');
		 	require_once(CORECLASS.'class.requete.core.php');
		 	require_once(CORECLASS.'class.tools.php');
		
		
		
		
		
			if(!defined('NO_USE_IHM')) {
			 	require_once(CORECLASS.'class.form.core.php');
			 	require_once(CORECLASS.'class.tbl.php');
			}
		
			if(defined('USE_TBS')) {
			 	require_once(CORECLASS.'tbs_class.php');
			 	require_once(CORECLASS.'tbs_plugin_opentbs.php');
				require_once(CORECLASS.'plugins/tbs_plugin_bypage.php');
				require_once(CORECLASS.'plugins/tbs_plugin_navbar.php');
				require_once(CORECLASS.'class.template.tbs.php');
				require_once(CORECLASS.'class.list.tbs.php');
			}
			
			if(defined('USE_EXTEND_CLASS')) {
		 		require_once(CORECLASS.'class.photo.php');
			}
		
			if(defined('USE_SOAP')) {
		 		require_once(CORECLASS.'class.soap.php');
			}
			
			if(defined('USE_CALENDAR')) {
				require_once(CORECLASS.'class.iCalReader.php');
			}
			
		
		/*
		 * Inclusion des fonctions
		 */
		
		 	if(!defined('NO_USE_FONCTION')) {
		 		require_once(COREFCT.'fonctions-core.php');		
		 	}
		 
			define('ATM_CORE_INCLUDED', true);
			
		}
	
 