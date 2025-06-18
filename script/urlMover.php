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
 *  \file       scripts/moduleLangTranslator.php
 *  \ingroup    cron
 *  \brief      copy missing lang trans from fr_FR
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
$customFolder = DOL_DOCUMENT_ROOT . '/custom/'; // pour l'instant ...

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
	echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
	exit(-1);
}
// $argv[0] is filename

// Check parameters

$argsList = array(
	1 => 'securitykey',
	2 => 'oldUrl',
	3 => 'newUrl',
);

$optionalArgsList = array(
	'langFrom' => 'fr_FR' // set default value
);

// Récupération des arguments
$param = new stdClass();
foreach ($argsList as $argKey => $paramName  ){

	if (! isset($argv[$argKey]) || ! $argv[$argKey]) {
		if(!isset($optionalArgsList[$paramName])){
			_helpUsage($path,$script_file);
			exit(-1);
		}
		else{
			$param->{$paramName} = $optionalArgsList[$paramName];
		}
	}
	else{
		$param->{$paramName} = $argv[$argKey];
	}
}

// Check security key
if ($param->securitykey !== getDolGlobalString('CRON_KEY'))
{
	print "Error: securitykey is wrong\n";
	exit(-1);
}

if (empty($param->oldUrl)){
	print "Error: Invalid oldUrl name\n";
	exit(-1);
}

if (empty($param->newUrl)){
	print "Error: Invalid oldUrl name\n";
	exit(-1);
}

$tables = array(
	'referenceletters_chapters' => array( 'content_text'),
	'referenceletters_elements' => array( 'header', 'footer'),
);

foreach ($tables as $tableName => $cols){
	$tableName = MAIN_DB_PREFIX.$tableName;
	$sqlShowTable = "SHOW TABLES LIKE '".$db->escape($tableName)."' ";
	$resST = $db->query($sqlShowTable);
	if($resST && $db->num_rows($resST) > 0) {
		foreach ($cols as $col){
			$sql = "UPDATE `".$db->escape($tableName)."` SET `".$db->escape($col)."` = REPLACE(`".$db->escape($col)."`,'".$db->escape($param->oldUrl)."' ,'".$db->escape($param->newUrl)."');";
			$resCol = $db->query($sql);
			if(!$sql){
				print $tableName. " :  ".$col." UPDATE ERROR ".$db->error()." \n";
			}else{
				$num = $db->affected_rows($resCol);
				print $tableName. " :  ".$col." => ".$num." \n";
			}
		}
	}
	else{
		print "Error : " .$sqlShowTable. " ". $db->error()." \n";
	}
}



/**
 * @param $path
 * @param $script_file
 */
function _helpUsage($path,$script_file)
{
	global $conf;

	print "MAKE A BACKUP BEFORE DO THAT ";
	print "Usage: ".$script_file." cronSecuritykey oldUrl newUrl \n";
	print "Exemple: ./".$script_file." khce86zgj84fzefef8f48 name.srv47.atm-consulting.fr name.srv88.atm-consulting.fr\n";
	print "\n";

}
