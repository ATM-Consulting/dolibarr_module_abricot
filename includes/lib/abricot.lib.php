<?php
/*
 Copyright (C) 2003-2013 Alexis Algoud <azriel68@gmail.com>
 Copyright (C) 2013-2015 ATM Consulting <support@atm-consulting.fr>

 This program and all files within this directory and sub directory
 is free software: you can redistribute it and/or modify it under 
 the terms of the GNU General Public License as published by the 
 Free Software Foundation, either version 3 of the License, or any 
 later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



function __get($varName, $default=null, $type='', $maxlength=0) {
     $var = isset($_REQUEST[$varName]) ? $_REQUEST[$varName] : $default;
	 
	 if($maxlength>0) {
	 	$var = substr($var, 0, $maxlength);
	 }
	 
	 if(!empty($type)) {
	 /*
	  Les valeurs possibles pour le paramètre type sont :
        "boolean" (ou, depuis PHP 4.2.0, "bool")
        "integer" (ou, depuis PHP 4.2.0, "int")
        "float" (uniquement depuis PHP 4.2.0. Pour les anciennes versions, utilisez l'alternative "double")
        "string"
        "array"
        "object"
        "NULL" (depuis PHP 4.2.0)
	  */
		settype($var, $type);
	 }
	 
	 return $var;
} 
function __val(&$valueObject, $default='', $type='string',$noempty=false) {
     
	 if(!isset($valueObject))$value=$default;
	 elseif ($noempty && empty($valueObject))$value=$default;
	 else $value = $valueObject;
	 
	 if(!empty($type)) {
	 /*
	  Les valeurs possibles pour le paramètre type sont :
        "boolean" (ou, depuis PHP 4.2.0, "bool")
        "integer" (ou, depuis PHP 4.2.0, "int")
        "float" (uniquement depuis PHP 4.2.0. Pour les anciennes versions, utilisez l'alternative "double")
        "string"
        "array"
        "object"
        "NULL" (depuis PHP 4.2.0)
	  */
		settype($value, $type);
	 }
	 
	 return $value;
} 
function __out($data, $type='', $callback='') {
	
	if(empty($type)) {
		if(isset($_REQUEST['gz'])) $type = 'gz';
		else if(isset($_REQUEST['gz2']))$type = 'gz2';
		elseif(isset($_REQUEST['json'])) $type='json';
		elseif(isset($_REQUEST['jsonp'])) $type='jsonp';
	}
	
	
	
	if($type==='gz') {
		$s = serialize($data);
		print gzdeflate($s,9);
	}
	elseif($type==='gz2') {
		$s = serialize($data);
		print gzencode($s,9);
	}
	elseif($type==='json') {
		print json_encode($data);
	}
	elseif($type==='jsonp') {
		if(empty($callback) && isset($_GET['callback'])) $callback = $_GET['callback'];
		print $callback.'('.json_encode($data).');' ;
	}
	else{
		$s = serialize($data);
		print $s;
	}

}

/**
 * @deprecated Should not be used anymore: jQuery included by Dolibarr, include dataTables manually if needed
 */

function getStandartJS() {
	?><script language="JavaScript" src="<?php echo COREHTTP?>includes/js/jquery-1.9.1.min.js"></script><?php
	?><script language="JavaScript" src="<?php echo COREHTTP?>includes/js/jquery-ui-1.8.6.custom.min.js"></script><?php
		
	?><script language="JavaScript" src="<?php echo COREHTTP?>includes/js/dataTable/js/jquery.dataTables.min.js"></script><?php
	?><link href="<?php echo COREHTTP?>includes/js/dataTable/css/jquery.dataTables.css" rel="stylesheet" type="text/css" /><?php
	
}
function getStandartCSS() {
	
}

function _htmlentities($val){ 
	if(defined('USE_UTF8') && USE_UTF8){
		$val = htmlentities($val, ENT_COMPAT, "UTF-8");
	}else{
		$val = htmlentities($val);
	}
	return $val;
}
function _strtolower($str){
	$encoding = 'latin1';
	if(defined('USE_UTF8')&&(USE_UTF8==true)){
		$encoding = 'utf-8';
	}
  	return mb_strtolower($str,$encoding);
}

function pre($t, $all=false){
  if($all) {
  	print '<pre>'. print_r($t, true) .'</pre>';
  }	
  else {
  	var_dump($t);
  }
  
}
/** 
 * @param string $str unicode and ulrencoded string 
 * @return string decoded string 
 */ 
function utf8_urldecode($str,$quotes = null,$charset = null){
    $str = str_replace("\\","",$str);
    $str = preg_replace_callback('/%u([0-9a-f]{4})/i',create_function('$arr','return "&#".hexdec($arr[1]).";";'),$str);
    return html_entity_decode($str,$quotes,$charset);
}


function decode_special_caracters($element){
  if(is_array($element) || is_object($element)) {
    foreach ($element as $k => $v) {
			$tmp[utf8_urldecode($k)] = decode_special_caracters($v);
    }
  }else{
    $tmp = utf8_urldecode($element);
  }
  return $tmp;
}
/**
 * Fonction de conversion des éléments d'un tableau en UTF-8
 * Encodage appliqué sur les clés et les valeurs
 * Fonction récursive 
 * @param array $array Le tableau à encoder
 * @return array Tableau identiques avec données en UTF-8
 */
function arrayConvertUTF8 ($array, $encode=true, $special_caracters=false) {
	$tmp = array();
	
	if(is_array($array)) {
		
		foreach ($array as $k => $v) {
	
			if(is_array($v) || is_object($v)) {
				if($encode) {
					$tmp[utf8_encode($k)] = arrayConvertUTF8($v, $encode);
				} else {
					$tmp[utf8_urldecode($k)] = arrayConvertUTF8($v, $encode);
				}
			} else {
				if($encode) {
					$tmp[utf8_encode($k)] = utf8_encode($v);
				} else {
					$tmp[utf8_urldecode($k)] = utf8_decode($v);
				}
			}
			 
		}
		
	}
	else{
		if($encode) {
			$tmp = utf8_encode($array);
		} else {
			$tmp = utf8_decode($array);
		}
	}
	
	
	return $tmp;
}


function get_json($data,$encoded=false) {
  $data = arrayConvertUTF8($data, $encoded);
  return json_encode($data);
}

function IsJsonString($str){
   try{
       $jObject = json_decode($str);
   }catch(Exception $e){
       return false;
   }
   return (is_object($jObject)) ? true : false;
}
function in_http($url, $ttl=3600) {
	$f=new TCacheFile;
	$f->ttl = $ttl;
	//$f->to_log=true;
	return $f->file_get($url);
} 
function _debug() {
	if(isset($_REQUEST['DEBUG'])) {
		return true;
	}
	
	return false;
}
function _fnumber($i,$dec=0){
	return number_format($i, $dec, ',', ' ');
}

function _less30c($s){
	$pos = strrpos(substr($s,0,-20)," ");
	return substr($s,0,$pos)."...";
}
function _str_cut($s,$len = 120){
	if(strlen($s)>$len){
		$r = substr($s,0,$len);
		$pos = strrpos($r," ");
		$r = substr($r,0,$pos);
		
		$last_car = substr($r,-1);
		if($last_car=="."){
			$r = substr($r,0,-1)."...";
		}
		else{
			$r=$r."...";	
		}
	}
	else{
		$r=$s;
	}
	return $r;
}

function array_delete_value($array,$search) {
  $temp = array();
  foreach($array as $key => $value) {
    if($value!=$search) $temp[$key] = $value;
  }
  return $temp;
}


/*
 * Encode un HTML pour être exploiter dans une inclusion javascript
 */
function _in_js($s){
  $js="";
  
  $trans=array("'"=>"\'","\r"=>"");
  
  $var = explode("\n", $s);
  foreach ($var as $ligne) {
  	 $js.="document.writeln('".strtr($ligne, $trans)."');\r\n";
  }

  return $js;
}

