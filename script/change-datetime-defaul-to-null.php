<?php
	
	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else $dir = '../../';


	include($dir."master.inc.php");
	
	$db->query("SET SQL_MODE='ALLOW_INVALID_DATES';");

	$res1 = $db->query("SHOW TABLES");
	while($objt = $db->fetch_object($res1)) {
		
		list($t) = array_values((array)$objt);
		
		$res2 = $db->query("DESCRIBE ".$t);
		while($obj = $db->fetch_object($res2)) {
			
			
			if($obj->Type == 'datetime' && $obj->Null == 'NO' 
					&& ($obj->Default=='0000-00-00 00:00:00' || $obj->Default=='1000-01-01 00:00:00') ) {
				
				echo $t.':'.$obj->Field.'<br />';
				
				$db->query("ALTER TABLE ".$t." CHANGE ".$obj->Field." ".$obj->Field." datetime NULL");
				
				$db->query("UPDATE ".$t." SET ".$obj->Field."=NULL WHERE 
						".$obj->Field."='0000-00-00 00:00:00'
						OR ".$obj->Field."='1000-01-01 00:00:00'
				");
				
			}
			
			
		}
		//ALTER TABLE `llx_planif_period` CHANGE `date_cre` `date_cre` datetime NULL
		
	}
	
	echo 'fin';