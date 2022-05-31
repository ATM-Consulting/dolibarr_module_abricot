<?php
/* Copyright (C) 2022     ATM consulting
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
 *  \file       scripts/password-encrypt-on-all-entities.php
 *  \ingroup    cron
 *  \brief      Ce script sert a passer à 1 les lignes de la configuration urlDeDolibarr/admin/security.php directement en base
 *                Arcoop a beaucoup trop d'entité pour s'amuser à le faire depuis l'interface entité par entité donc
 *                avec un script qui va modifie en base c'est plus efficace. cette action est a effectuer le plus souvent
 *                lors d'une montée de version et/ou MEP.
 *
 * Ce script fait passer à 1 la ligne suivantes pour toute les entités:
 *
 *            Chiffrer les mots de passe stockés dans la base de données (PAS en texte brut). Il est fortement recommandé d'activer cette option.
 *
 */
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disables token renewal
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');
if (!defined('NOLOGIN')) define('NOLOGIN', '1');

if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';


require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/security2.lib.php';

global $db, $conf;

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);

// Use only on command line
if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
	echo "Error: You are using PHP for CGI. To execute " . $script_file . " from command line, you must use PHP for CLI mode.\n";
	exit(-1);
}


$error = 0;

$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "entity";

$resql = $db->query($sql);

if (!$resql) {
	dol_print_error($db);
	$error++;
}

$status = array();

while ($entity = $db->fetch_object($resql)) {
	$result = dolibarr_set_const($db, "DATABASE_PWD_ENCRYPTED", "1", 'chaine', 0, '', $entity->rowid);
	if (!$result) {
		$status[] = "Erreur sur la fonction dolibarr_set_const concernant l'entité n° $entity->rowid \n";
	} else {
		$status[] = "Mise à jour de la ligne suivante: 'Chiffrer les mots de passe stockés dans la base de données (PAS en texte brut). Il est fortement recommandé d'activer cette option' pour l'entité $entity->rowid \n";
	}
}
for ($i = 0; $i < count($status); $i++) {
	print $status[$i];
}

$sql = "SELECT u.rowid, u.pass, u.pass_crypted";
$sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
$sql .= " WHERE u.pass IS NOT NULL AND LENGTH(u.pass) < 32"; // Not a MD5 value

$resql = $db->query($sql);
if ($resql) {
	$numrows = $db->num_rows($resql);
	$i = 0;
	while ($i < $numrows) {
		$obj = $db->fetch_object($resql);
		if (dol_hash($obj->pass)) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "user";
			$sql .= " SET pass_crypted = '" . dol_hash($obj->pass) . "', pass = NULL";
			$sql .= " WHERE rowid=" . $obj->rowid;

			$resql2 = $db->query($sql);
			if (!$resql2) {
				dol_print_error($db);
				$error++;
				break;
			}
			$i++;
		}
	}
} else dol_print_error($db);

if (!$error) {
	$db->commit();
	header("Location: security.php");
	exit;
} else {
	$db->rollback();
	dol_print_error($db, '');
}

