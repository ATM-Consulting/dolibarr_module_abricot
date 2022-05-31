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
if(! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disables token renewal
if(! defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');
if(! defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');
if(! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');
if(! defined('NOLOGIN')) define('NOLOGIN', '1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');

if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';


include_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__).'/';

// Test if batch mode
if(substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
    echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
    exit(-1);
}
// $argv[0] is filename

// Check parameters

$argsList = array(
    1 => 'securitykey',
    2 => 'moduleName'
);

// Récupération des arguments
$param = new stdClass();
foreach($argsList as $argKey => $paramName) {

    if(! isset($argv[$argKey]) || ! $argv[$argKey]) {
        if(! isset($optionalArgsList[$paramName])) {
            _helpUsage($path, $script_file);
            exit(-1);
        }
        else {
            $param->{$paramName} = $optionalArgsList[$paramName];
        }
    }
    else {
        $param->{$paramName} = $argv[$argKey];
    }
}

// Check security key
if($param->securitykey !== $conf->global->CRON_KEY) {
    print "Error: securitykey is wrong\n";
    exit(-1);
}
$sourceDir = DOL_DATA_ROOT.'/'.$param->moduleName;
if(! is_dir($sourceDir)) {
    print "Error: Invalid DIR ".$sourceDir."\n";
    exit(-1);
}
// Create dir to_delete
if(dol_mkdir('to_delete', DOL_DATA_ROOT) >= 0) {
    $scanDirSource = scandir($sourceDir);
    if(! empty($scanDirSource) && is_array($scanDirSource)) {
        // skip folders
        foreach($scanDirSource as $filename) {
            if($filename == "." || $filename == "..") {
                continue;
            }
            if(!empty(preg_match('/^\(PROV[0-9]*\)$/', $filename)) && is_dir($sourceDir.'/'.$filename)) {
                $scanDirProv = scandir($sourceDir.'/'.$filename);
                if(is_array($scanDirProv) && count($scanDirProv) === 3 && $scanDirProv[0] === $filename.'.pdf') { //. .. et le pdf
                    //Dans ce cas là on move tout les pdfs
                   if(dol_mkdir($param->moduleName, DOL_DATA_ROOT.'/to_delete/') >= 0
                       && dol_mkdir($filename, DOL_DATA_ROOT.'/to_delete/'.$param->moduleName) >= 0) {
                       rename ( $sourceDir.'/'.$filename , DOL_DATA_ROOT.'/to_delete/'.$param->moduleName.'/'.$filename);
                   }
                }
            }
        }
    }
}
else {
    print "Error: can't create to_delete dir\n";
    exit(-1);
}

/**
 * @param $path
 * @param $script_file
 */
function _helpUsage($path, $script_file) {
    global $conf;

    print "MAKE A BACKUP BEFORE DO THAT ";
    print "Usage: ".$script_file." cronSecuritykey  \n";
    print "Exemple: ./".$script_file." khce86zgj84fzefef8f48 propal\n";
    print "\n";
}
