<?php

/**
 * COMMON ABRICOT SCRIPT TOOL
 * ast_ prefix for abricot script tools
 */

/**
 * check if current output is bash
 * @return bool
 */
function ast_isBash()
{
	// Use only on command line
	$isBash = true;
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
		$isBash = false;
	}

	return $isBash;
}


/**
 *
 * @param string $msg
 * @param string $type '' | 'success' | 'error' | 'error-code'
 * @return void
 */
function ast_log($msg, $type = ''){
	global $isBash;

	if(!isset($isBash)){
		$isBash = ast_isBash();
	}

	if($isBash){
		$bashColor = '0;37';
		if($type == 'error' ){
			$bashColor = '1;37;41';
		}elseif($type == 'error-code'){
			$bashColor = '1;31;40';
		}elseif($type == 'success'){
			$bashColor = '0;32';
		}
		echo "\e[".$bashColor."m".$msg."\e[0m\n";
	}else{
		$style = '';
		if($type == 'error' || $type == 'error-code'){
			$style = ' style="background: #fbb"';
		}

		echo '<p' . $style . '>'.$msg.'</p>';
	}
}


/**
 * launch sql query and display log
 * @param string $sql
 * @param bool $res
 * @param DoliDB $db
 * @return void
 */
function ast_sqlQuerylog($db, $sql){
	if($db->query($sql)){
		ast_log($sql, 'success');
	}else{
		ast_log($sql, 'error');
		ast_log($db->error(), 'error-code');
	}
}
