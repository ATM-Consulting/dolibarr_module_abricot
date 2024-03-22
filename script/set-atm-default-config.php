<?php
/**
* SPDX-License-Identifier: GPL-3.0-or-later
* This file is part of Dolibarr module Abricot
*/

if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';

require_once __DIR__ . '/common_script.lib.php';

$BOOL_CONST_SQL_TEMPLATE = 'INSERT INTO ' . MAIN_DB_PREFIX . 'const (entity, name, value)'
	. ' VALUES (0, \'%s\', \'%s\')'
	. ' ON DUPLICATE KEY UPDATE value = VALUES(value);';

$TCONST_TO_SET_GLOBALLY = [
	'MAIN_USE_TOP_MENU_SEARCH_DROPDOWN' => '1',
	'MAIN_INFO_SOCIETE_LOGO_NO_BACKGROUND' => '1',
];

foreach ($TCONST_TO_SET_GLOBALLY as $constName => $constValue) {
	$sql = sprintf($BOOL_CONST_SQL_TEMPLATE, $constName, $constValue);
	ast_sqlQuerylog($db, $sql);
}

echo ast_log('FIN');
