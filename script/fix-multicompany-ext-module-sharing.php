<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * This file is part of Dolibarr module Financement
 *
 *
 * Ce script est un one-shot qui sert à corriger des warnings dans MultiCompany liés à la conf
 * qui permet à des modules tiers d'ajouter des objets "partageables" avec MultiCompany.
 *
 * Cette conf est un json qui encode un tableau qui ressemble à ceci:
 *
 * [ PARTAGES_MODULE_A, PARTAGES_MODULE_B, PARTAGES_MODULE_C, … ].
 *
 * Chaque élément est un sous-tableau avec à son tour 2 sous-sous-tableaux nommés:
 * [
 *   "sharingelements" => [ "obj_1" => CONF_OBJ_1, "obj_2" => CONF_OBJ_2, … ],
 *   "sharingmodulename" => [ "obj_1" => "nom_module_A", "obj_2" => "nom_module_A", … ]
 * ]
 *
 * Et chaque élément (CONF_OBJ_*) ci-dessus est encore un sous-sous-sous-tableau. Dans les anciennes
 * version, il était laissé vide, mais les versions plus récentes attendent une clé 'type' obligatoire
 * (sous peine de warnings). La valeur de cette clé est toujours "objet", apparemment. Je ne sais pas
 * quelles sont les autres valeurs possibles. Ça semble conditionner des features obscures, qui ne nous
 * intéressent pas ici.
 *
 *
 * Exemple:
 *   on a un vieux module "Pizzeria" avec des objets "Pizza" qui peuvent être partagées entre entités.
 *
 * En gros, ce qu'on veut faire, c'est transformer ceci:
 *
 * [
 *   [
 *    'sharingelements' => [ 'pizza' => [] ],
 *    'sharingmodulename' => [ 'pizza' => 'pizzeria' ]
 *   ]
 * ]
 *
 * en ceci:
 *
 * [
 *   [
 *    'sharingelements' => [ 'pizza' => ['type' => 'object'] ],
 *    'sharingmodulename' => [ 'pizza' => 'pizzeria' ]
 *   ]
 * ]
 *
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
	$res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)).'/main.inc.php')) {
	$res = @include substr($tmp, 0, ($i + 1)).'/main.inc.php';
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))).'/main.inc.php')) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))).'/main.inc.php';
}

// Try main.inc.php using relative path
$main_inc = 'main.inc.php';
for ($i = 2 ; $i < 5 && ! $res ; $i++) {
	$res = @include str_repeat('../', $i).$main_inc;
}
if (! $res) {
	die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

/** @var DoliDB $db */

$ret = [];

$confName = 'MULTICOMPANY_EXTERNAL_MODULES_SHARING';

// en gros, cet algo va rajouter une clé-valeur 'type' => 'object' dans
// json_decode(MULTICOMPANY_EXTERNAL_MODULES_SHARING)[*]['sharingelements'][*]
// car le module multicompany utilise cette clé sans tester si elle existe et ça
// crée des warnings sur chaque page.
$n = 0;
$resql = $db->query("SELECT rowid, entity, value, type, note FROM {$db->prefix()}const WHERE name = '{$confName}'");
while($obj = $db->fetch_object($resql)) {
    $full_conf_MEMS = json_decode($obj->value, JSON_OBJECT_AS_ARRAY);

    // je sais pas pourquoi le JSON est structuré comme ça.
    foreach ($full_conf_MEMS as &$conf_MEMS_of_module) {
        if (!isset($conf_MEMS_of_module['sharingelements'])) continue;
        foreach ($conf_MEMS_of_module['sharingelements'] as &$se) {
            $se['type'] = $se['type'] ?? 'object';
        }
        $ret[] = $conf_MEMS_of_module;
    }

	# dolibarr_set_const ne fonctionne pas bien pour l'entité 0.
    # dolibarr_set_const($db, $confName, json_encode($full_conf_MEMS), $obj->type, $obj->note, $obj->entity);
	$newValue = json_encode($full_conf_MEMS);
	$sql = "UPDATE {$db->prefix()}const SET value = '{$db->escape($newValue)}' WHERE rowid = {$obj->rowid}";
	$resql2 = $db->query($sql);
	if (! $resql2) {
		echo 'SQL ERROR: '.$db->lasterror() . PHP_EOL;
		echo 'Query: '.$db->lastquery() . PHP_EOL;
		exit(1);
	}
	$n++;
}

echo 'OK: ' . $n . PHP_EOL;
