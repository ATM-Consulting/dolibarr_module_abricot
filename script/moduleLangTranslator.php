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

if(is_file('../main.inc.php'))$dir = '../';
else  if(is_file('../../../main.inc.php'))$dir = '../../../';
else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
else $dir = '../../';


include($dir."master.inc.php");


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
	2 => 'moduleName',
	3 => 'langTarget',
	4 => 'langFrom'
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
if ($param->securitykey !== $conf->global->CRON_KEY)
{
	print "Error: securitykey is wrong\n";
	exit(-1);
}

if (!ctype_alnum($param->moduleName) || !is_dir ( $customFolder . $param->moduleName ) ){
	print "Error: Invalid module name\n";
	exit(-1);
}


$dirFrom 	= $customFolder . $param->moduleName. '/langs/' . $param->langFrom;
$dirTarget = $customFolder . $param->moduleName. '/langs/' . $param->langTarget;

if(!is_dir($dirTarget) || !is_dir($dirTarget)){
	print "Error: lang folder does not exists\n";
	exit(-1);
}

if(!preg_match ( '/^[a-z]{2}_[A-Z]{2}$/' , $param->langTarget) || !is_dir($dirTarget)){
	print "Error: Invalid lang to check\n".$dirTarget."\n";
	exit(-1);
}

if(!preg_match ( '/^[a-z]{2}_[A-Z]{2}$/' , $param->langFrom) || !is_dir($dirFrom)){
	print "Error: Invalid lang from\n";
	exit(-1);
}


$scanDirFrom 	= scandir($dirFrom);
$scanDirTarget = scandir($dirTarget);

if(!empty($scanDirFrom) && is_array($scanDirFrom)){
	foreach ($scanDirFrom as $filename){

		// skip folders
		if($filename == "." || $filename == ".."  || is_dir($dirFrom.'/'.$filename) ){
			continue;
		}

		// check is a lang file
		if(preg_match ( '/^(.)+(\.lang)$/' , $filename)){

			$file_lang_osencoded=dol_osencode($dirFrom.'/'.$filename);

			// Load "from" translations
			$trads_from = _loadTranslation($dirFrom.'/'.$filename);
			if(!is_array($trads_from)){
				print "Error: lang file content\n".$dirFrom .'/'. $filename."\n";
				exit(-1);
			}

			// Load "target" translation if exists
			$trads_target = array();
			$targetFileExist = false;
			if(file_exists($dirTarget .'/'. $filename)){
				$targetFileExist = true;
				$trads_target = _loadTranslation($dirTarget .'/'. $filename);

				if(!is_array($trads_target)){
					print "Error: lang file content\n".$dirTarget .'/'. $filename."\n";
					exit(-1);
				}
			}

			// extract missing translation
			// $trads_missing = array_diff_key($trads_from, $trads_target);
			if(empty($trads_target)){
				$trads_missing = $trads_from;
			}else{
				$trads_missing = array();
				foreach($trads_from as $tfKey => $tfValue){
					if(!isset($trads_target[$tfKey])){
						$trads_missing[$tfKey]=$tfValue;
					}
				}
			}

			if(empty($trads_missing))
			{
				print $filename." is Ok\n";
			}
			else
			{

				$TNewLines = array();
				$TNewLines[] = '';
				$TNewLines[] = '#';
				$TNewLines[] = '# MISSING TRANSLATION FROM '.$param->langFrom;
				$TNewLines[] = '#';

				foreach($trads_missing as $tmKey => $tmValue){
					$TNewLines[] = $tmKey."=".$tmValue;
				}

				$TNewLines[] = '';

				// Ecrit le contenu dans le fichier à la suite du fichier et
				// LOCK_EX pour empêcher quiconque d'autre d'écrire dans le fichier en même temps
				$writeRes = file_put_contents($dirTarget .'/'. $filename, implode("\n", $TNewLines), FILE_APPEND | LOCK_EX);

				if($writeRes === false)
				{
					print "Error: writing file\n".$dirTarget .'/'. $filename."\n";
					exit(-1);
				}
				else
				{
					print $filename." Updated ".count($trads_missing)." missing translations\n";
				}
			}
		}
	}
}


/**
 * Use GOOGLE translate API
 * 		$text = 'My name is john';
 *		if(!empty($conf->global->GOOGLE_TRAD_API)){
 *		_googleTranslate($conf->global->GOOGLE_TRAD_API, $text, $param->langFrom, $param->langTarget);
 *		}
 * @param $api_key
 * @param $text
 * @param $langFrom
 * @param $langTarget
 */
function _googleTranslate($api_key, $text, $langFrom, $langTarget) {
	$url = 'https://translation.googleapis.com/language/translate/v2?key=' . $api_key;

	$form = [
		'q' => $text,
		'target' => $langTarget,
		'from' => $langFrom,
	];

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $form );

	$response = curl_exec( $ch );

	curl_close( $ch );

	$response = json_decode( $response );
	var_dump( $response );
}

/**
 * @param $filename
 * @return array|bool
 */
function _loadTranslation($filename){
	$tab_translate = array();

	if(!is_file($filename)){
		return false;
	}

	/**
	 * Read each lines until a '=' (with any combination of spaces around it)
	 * and split the rest until a line feed.
	 * This is more efficient than fgets + explode + trim by a factor of ~2.
	 */
	if ($fp = @fopen($filename,"rt")) {
		while ($line = fscanf($fp, "%[^= ]%*[ =]%[^\n]")) {
			if (isset($line[1])) {
				list($key, $value) = $line;
				//if ($domain == 'orders') print "Domain=$domain, found a string for $tab[0] with value $tab[1]. Currently in cache ".$this->tab_translate[$key]."<br>";
				//if ($key == 'Order') print "Domain=$domain, found a string for key=$key=$tab[0] with value $tab[1]. Currently in cache ".$this->tab_translate[$key]."<br>";
				if (empty($tab_translate[$key])) { // If translation was already found, we must not continue, even if MAIN_FORCELANGDIR is set (MAIN_FORCELANGDIR is to replace lang dir, not to overwrite entries)
					$value = preg_replace('/\\n/', "\n", $value); // Parse and render carriage returns
					if ($key == 'DIRECTION') { // This is to declare direction of language
						// TODO
						continue;
					} elseif ($key[0] == '#') {
						continue;
					} else {
						$tab_translate[$key] = $value;
					}
				}
			}
		}
		fclose($fp);
	}

	return $tab_translate;
}

/**
 * @param $path
 * @param $script_file
 */
function _helpUsage($path,$script_file)
{
	global $conf;

	print "Usage: ".$script_file." cronSecuritykey moduleFolderName langKeyTarget langKeyFrom(optional default fr_FR)  \n";
	print "Exemple: ./".$script_file." khce86zgj84fzefef8f48 moduleFolderName en_EN fr_FR\n";
	print "\n";

}
