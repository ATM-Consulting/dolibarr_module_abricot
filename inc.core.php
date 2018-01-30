<?php
/*
 Copyright (C) 2003-2013 Alexis Algoud <azriel68@gmail.com>
 Copyright (C) 2013-2015 ATM Consulting <support@atm-consulting.fr>

 This program and all files within this directory and sub directory
 is free software: you can redistribute it and/or modify it under 
 the terms of the GNU General Public License as published by the 
 Free Software Foundation, either version 3 of the License, or any 
 later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



/*
 * Configuration du noyau
 * 
 * Les constantes suivantes devront déjà être définie
 * 
 * DB_DRIVER, DB_NAME, DB_USER, DB_PASS,DB_HOST // pour les connexions bases de données
 */ 
 	if(defined('ATM_CORE_INCLUDED')) {
 		null;
		
 	}
	else {

			define('OBJETSTD_MASTERKEY', 'rowid');
			define('OBJETSTD_DATECREATE', 'date_cre');
			define('OBJETSTD_DATEUPDATE', 'date_maj');
			define('OBJETSTD_DATEMASK', 'date_');
			
						 
			if(!defined('ROOT')){
				define('ROOT',dol_buildpath('/abricot/'));
				define('HTTP',dol_buildpath('/abricot/',1));
			}

			if(!defined('COREROOT')) {
				define('COREROOT',ROOT);	
				define('COREHTTP',HTTP);
			}
			
			define('CORECLASS',COREROOT.'includes/class/');
			define('COREFCT',COREROOT.'includes/lib/');
			
		/*
		 * Inclusion des classes Core
		 * Le require_once permet de surcharger préalablement ces classes
		 */
		 	if(!defined('NO_CORE_DB')){
			   	require_once(CORECLASS.'class.pdo.db.php');
		 	}
		 	require_once(CORECLASS.'class.objet_std.php');
	 		require_once(CORECLASS.'class.objet_std_dolibarr.php');
		 	require_once(CORECLASS.'class.trigger.php');
		 	require_once(CORECLASS.'class.observer.php');
		 	require_once(CORECLASS.'class.reponse.mail.php');
		 	require_once(CORECLASS.'class.cache_file.php');
		 	require_once(CORECLASS.'class.requete.core.php');
		 	require_once(CORECLASS.'class.tools.php');
		
			/* Construction page */
		 	require_once(CORECLASS.'class.form.core.php');
		 	require_once(CORECLASS.'class.tbl.php');
		
			/* templating TBS */
		 	require_once(CORECLASS.'tbs_class.php');
		 	require_once(CORECLASS.'tbs_plugin_opentbs.php');
			require_once(CORECLASS.'plugins/tbs_plugin_bypage.php');
			require_once(CORECLASS.'plugins/tbs_plugin_navbar.php');
			require_once(CORECLASS.'class.template.tbs.php');
			require_once(CORECLASS.'class.list.tbs.php');
			
			/* Début de la fin pour PDOdb */
			require_once(CORECLASS.'class.listview.php'); // remplace TListviewTBS pour un objet Dolibarr
			require_once(CORECLASS.'class.seedobject.php'); // remplace l'extend TObjetStd pour nos class
			
			/* wkhtmltopdf */
			require_once(CORECLASS.'Wkhtmltopdf.php');
			
			
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
		 		require_once(COREFCT.'abricot.lib.php');		
		 	}
		 
			define('ATM_CORE_INCLUDED', true);
			define('ABRICOT_VERSION',3.0);
		}
	
 
