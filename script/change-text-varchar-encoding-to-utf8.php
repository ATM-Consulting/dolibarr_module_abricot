<?php
	
	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else $dir = '../../';


	include($dir."master.inc.php");

	$res1 = $db->query("SHOW TABLES");

	while($objt = $db->fetch_object($res1)) {

		list($t) = array_values((array)$objt);

		echo $t.'<br />';

		$db->query("ALTER TABLE ".$t." CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
	}

	echo 'fin';
