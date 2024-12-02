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

require_once '/home/client/monk/dolibarr/htdocs/master.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

/** @var DoliDB $db */

$ret = [];

$confName = 'MULTICOMPANY_EXTERNAL_MODULES_SHARING';

// en gros, cet algo va rajouter une clé-valeur 'type' => 'object' dans
// json_decode(MULTICOMPANY_EXTERNAL_MODULES_SHARING)[*]['sharingelements'][*]
// car le module multicompany utilise cette clé sans tester si elle existe et ça
// crée des warnings sur chaque page.
$resql = $db->query("SELECT rowid, entity, value, type, note FROM llx_const WHERE name = '{$confName}'");
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
    dolibarr_set_const($db, $confName, json_encode($full_conf_MEMS), $obj->type, $obj->note, $obj->entity);
}

echo '<pre>'. PHP_EOL;
echo json_encode($ret);
