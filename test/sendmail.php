<?php

	require '../../../master.inc.php';
	require '../inc.core.php';

	$mailto = $argv[1] or die('?to=');

	$r=new TReponseMail('test-mail@atm-consulting.fr', $mailto, 'test envoi mail smtp', 'Ceci est un test, merci de ne pas tenir compte')  ;
//	$r->use_dolibarr_for_smtp = false;

	print (int)$r->send();
