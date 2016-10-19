<?php

	$s_name = $_POST['session_name'] or die('Pas de session trouvée');
	session_name($s_name);
	session_start();
	
	if(empty($_SESSION['token_list_'.$_POST['token']])) die('token de session liste invalide');

	$TData = unserialize( gzinflate( $_SESSION['token_list_'.$_POST['token']]) );
	$mode = $_POST['mode'];
	$title = $TData['title'];
	$sql = $TData['sql'];
	$TBind = $TData['TBind'];
	$TEntete = $TData['TEntete'];
	$TChamps = $TData['TChamps'];

	$utf8_with_bom = chr(239) . chr(187) . chr(191);

	if(empty($title)) $title = 'report';

	if($mode == 'CSV') {
		
		header('Content-Type: application/octet-stream');
	    header('Content-disposition: attachment; filename='. $title.'.csv');
	    header('Pragma: no-cache');
	    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	    header('Expires: 0');
	  
		$r='';
		foreach($TEntete as &$entete) {
			$r.='"'.addslashes($entete['libelle']).'";';
		}
		$r.=PHP_EOL;
		
		foreach($TChamps as $row) {
			foreach($row as $v) {
				$r.='"'.addslashes(strip_tags( $v )).'";';
			}
			$r.=PHP_EOL;
		}

		if( mb_detect_encoding($r, 'UTF-8', true) ) $r = $utf8_with_bom.$r;

		echo $r;
		
	    exit();
		
		
	}
	else if($mode == 'TXT') {
		
		header('Content-Type: application/octet-stream');
	    header('Content-disposition: attachment; filename='. $title.'.txt');
	    header('Pragma: no-cache');
	    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	    header('Expires: 0');
	  
		$r='';
		foreach($TEntete as &$entete) {
			$r.='"'.addslashes($entete['libelle']).'"'."\t";
		}
		$r.=PHP_EOL;
		
		foreach($TChamps as $row) {
			foreach($row as $v) {
				$r.='"'.addslashes(strip_tags($v)).'"'."\t";
			}
			$r.=PHP_EOL;
		}

		echo $r;
		
	    exit();
		
	}
	else if ($mode == 'PDF') {
		
	}
