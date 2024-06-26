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


$db->query("SET SQL_MODE='ALLOW_INVALID_DATES';");

$res1 = $db->query("SHOW TABLES");
while($objt = $db->fetch_object($res1)) {

	list($t) = array_values((array)$objt);

	$sql = "DESCRIBE ".$t;
	$res2 = $db->query($sql);
	if($res2){
		while($obj = $db->fetch_object($res2)) {

//			if($t=='llx_accounting_account') var_dump($obj);
			if(($obj->Type == 'datetime' || $obj->Type=='timestamp')
//				&& $obj->Null == 'NO'
				&& (
						$obj->Default=='0000-00-00 00:00:00'
						|| $obj->Default=='1000-01-01 00:00:00'
						|| $obj->Default == 'CURRENT_TIMESTAMP'
						|| is_null($obj->Default)
				)
			) {


				ast_log($t.':'.$obj->Field);

				if($obj->Default != 'CURRENT_TIMESTAMP') {
					$sql = "ALTER TABLE ".$t." CHANGE ".$obj->Field." ".$obj->Field." datetime NULL";
					ast_sqlQuerylog($db, $sql);
				}

				$sql = "UPDATE ".$t." SET ".$obj->Field."=NULL WHERE ".$obj->Field."='0000-00-00 00:00:00' OR ".$obj->Field."='1000-01-01 00:00:00' ";
				ast_sqlQuerylog($db, $sql);
			}


		}
		//ALTER TABLE `llx_planif_period` CHANGE `date_cre` `date_cre` datetime NULL

	}else{
		ast_log("KO : ".$sql, 'error');
		ast_log($db->error(), 'error-code');
	}
}

echo ast_log('FIN');
