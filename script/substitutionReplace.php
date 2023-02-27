#!/usr/bin/env php
<?php
/* Copyright (C) 2020     ATM consulting
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       scripts/substitutionReplace.php
 *  \ingroup    cron
 *  \brief      Ce fichier sert à mettre à jour les données au moment d'une mise à jour V13 vers V14
 */
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
if (! defined('NOLOGIN'))        define('NOLOGIN','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');

if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path=dirname(__FILE__).'/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
	echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
	exit(-1);
}

$optionalArgsList = array(
	'langFrom' => 'fr_FR' // set default value
);


$TReplace = array();

if(intval(DOL_VERSION) >= 13){
	$TReplace['__SIGNATURE__'] = '__USER_SIGNATURE__';
}

if(intval(DOL_VERSION) >= 14){
	$TReplace['__REFCLIENT__'] = '__REF_CLIENT__';
	$TReplace['__PROPREF__'] = '__REF__';
	$TReplace['__FACREF__'] = '__REF__';
}

$tables = array(
	'c_email_templates' => array( 'topic', 'content'),
	'mailing' => array( 'sujet', 'body')
);


if(intval(DOL_VERSION) >= 16) {
	$sql = "UPDATE " . MAIN_DB_PREFIX . "c_email_templates SET topic = REPLACE(topic, '__TICKET_REF__', '__REF__'), content = REPLACE(content, '__TICKET_REF__', '__REF__') WHERE type_template LIKE '%ticket%' ";
	$resCol = $db->query($sql);
	if (!$resCol) {
		print "c_email_templates remplacement of __TICKET_REF__ UPDATE ERROR " . $db->error() . " \n";
	} else {
		$num = $db->affected_rows($resCol);
		print "c_email_templates remplacement of __TICKET_REF__ " . $num . " lines affected \n";
	}
}

foreach ($tables as $tableName => $cols){
	$tableName = MAIN_DB_PREFIX.$tableName;
	$sqlShowTable = "SHOW TABLES LIKE '".$db->escape($tableName)."' ";
	$resST = $db->query($sqlShowTable);
	if($resST && $db->num_rows($resST) > 0) {

		foreach ($TReplace as $substitutionKey => $substitutionReplacement) {

			foreach ($cols as $col) {
				$sql = "UPDATE " . $db->escape($tableName) . " SET " . $db->escape($col) . " = REPLACE(" .$col . ",'".$db->escape($substitutionKey)."' ,'".$db->escape($substitutionReplacement)."');";
				$resCol = $db->query($sql);
				if (!$resCol) {
					print $tableName .' ' . $substitutionKey . '=>' . $substitutionReplacement . " :  " . $col . " UPDATE ERROR " . $db->error() . " \n";
				} else {
					$num = $db->affected_rows($resCol);
					print $tableName .' ' . $substitutionKey . '=>' . $substitutionReplacement . " :  " . $col . " => " . $num . " \n";
				}
			}
		}
	}
	else{
		print "Error : " .$sqlShowTable. " ". $db->error()." \n";
	}
}
