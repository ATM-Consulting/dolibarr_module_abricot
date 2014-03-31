<?php
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
function __out($data) {
	
	if(isset($_REQUEST['gz'])) {
		$s = serialize($data);
		print gzdeflate($s,9);
	}
	elseif(isset($_REQUEST['gz2'])) {
		$s = serialize($data);
		print gzencode($s,9);
	}
	elseif(isset($_REQUEST['json'])) {
		print json_encode($data);
	}
	elseif(isset($_REQUEST['jsonp'])) {
			print $_GET['callback'].'('.json_encode($data).');' ;
	}
	else{
		$s = serialize($data);
		print $s;
	}

}

function getStandartJS() {
	?><script language="JavaScript" src="<?=COREHTTP?>includes/js/jquery-1.9.1.min.js"></script><?
	?><script language="JavaScript" src="<?=COREHTTP?>includes/js/jquery-ui-1.8.6.custom.min.js"></script><?
		
	?><script language="JavaScript" src="<?=COREHTTP?>includes/js/dataTable/js/jquery.dataTables.min.js"></script><?
	?><link href="<?=COREHTTP?>includes/js/dataTable/css/jquery.dataTables.css" rel="stylesheet" type="text/css" /><?
	
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
 * Fonction de conversion des �l�ments d'un tableau en UTF-8
 * Encodage appliqu� sur les cl�s et les valeurs
 * Fonction r�cursive 
 * @param $array Le tableau à encoder
 * @return Array Tableau identiques avec donn�es en UTF-8
 */
function arrayConvertUTF8 ($array, $encode=true, $special_caracters=false) {
	$tmp = array();
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
	
	return $tmp;
}


/**
 * Convertit la donn�es en json
 * Utilise une fonction red�finie si json_encode n'existe pas (> PHP 5.2)
 * @param Mixed $data La donn�es � convertir en json (donn�e, tableau, objet)
 * @return Mixed La donn�e encod�e en json
 */
function get_json($data,$encoded=false) {
  $data = convertUTF8($data);
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
function _url_format($s, $cut=true, $its_file=false){
	$Tab_TransUrl= array(
		" - "=>"-"
		," "=>"-"
		,"/"=>"-"
		,"'"=>"-"
		,"’"=>"-"
		,","=>""
		,"\""=>"-"
		,"*"=>"-"
		,"?"=>""
		,"+"=>"-"
		,"("=>"-"
		,")"=>"-"
		,"%"=>"-"
		,"é"=>"e"
		,"è"=>"e"
		,"&"=>"-"
		,"à"=>"a"
		,"ç"=>"c"
		,"Ç"=>"c"
		,"ê"=>"e"
		,"ë"=>"e"
		,"â"=>"a"
		,"ä"=>"a"
		,"û"=>"u"
		,"ü"=>"u"
		,"î"=>"i"
		,"ï"=>"i"
		,"ù"=>"u"
		,"ô"=>"o"
		,"ö"=>"o"
		,"Ö"=>"o"
		,"Ô"=>"o"
		,"<"=>"-"
		,">"=>"-"
		,":"=>"-"
		,";"=>"-"
		,"\\"=>""
		,"\""=>"-"
		,"|"=>"-"
		,"»"=>"-"
		,"«"=>"-"
		//,"."=>""
		,"!"=>""
		,"’"=>"-"
		,"®"=>""
		,"“"=>""
		,"”"=>""
		,"°"=>""
		,"™"=>""
		,"²"=>""
		,"•"=>""
		,"œ"=>"oe"
		,"æ"=>"ae"
		,"#"=>""
		,"…"=>"-"
		,"—"=>"-"
		,"‘"=>""
		,"€"=>"e"
		,'’'=>"'"
     	,'–'=>'-'
	);
	
	if(!$its_file){
    $Tab_TransUrl['.']="";
  }
	//$s = strtolower($s);
	if(defined("USE_UTF8")){
	 $s = mb_strtolower($s, "utf8");
  }else{  
	 $s = mb_strtolower($s, "latin1");
	}
	
	$s = strtr($s,$Tab_TransUrl);
	if($cut)$s = substr($s,0,50);
	$s = trim($s);
	
	$s = _url_format_verif_format($s);

	return $s;

}	
function _url_format_verif_format($s){
	$r="";
	$nb=strlen($s);
	for($i = 0; $i < $nb; $i++){
		//print "$i : ".$s[$i]." ".ctype_alnum($s[$i])."<br>";
		if(ctype_alnum($s[$i]) || $s[$i]=='-' || $s[$i]=='.'){
			$r.=$s[$i];			
		}
	} // for
	return $r;
}
function erreur($s){
	$name="erreur_".md5(time().rand(100,2000)).rand(100,2000);
  ?>
	<table class="erreur" id="<?=$name?>"><tr><td>
	<img src="../images/s_error.png" ALIGN="absmiddle"><b>Erreur : <?=$s?></b>
	</td>
	<td>
	<a href="javascript:function wdn() {document.getElementById('<?=$name?>').style['display']='none';} wdn();" class="lien"> (Effacer) </a>
	</td></tr></table>
	<?
}
function info($s){
  $name="info_".md5(time().rand(100,2000)).rand(100,2000);

	?>
	<table class="info" id="<?=$name?>"><tr><td>
	<img src="../images/s_info.png" ALIGN="absmiddle"><b>Information : <?=$s?></b>
	</td>
	<td>
	<a href="javascript:function wdn() {document.getElementById('<?=$name?>').style['display']='none';} wdn();" class="lien"> (Effacer) </a>
	</td></tr></table>
	<?
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

function delete_part_of_doc($doc,$tag_start,$tag_end){
  /**
   * Supprime la partie du document entre 2 balises de fa�on s�curis�e
   * GM 23/03/2012 
   **/
	$pos_start = strpos($doc, $tag_start);
  if($pos_start===false){
    return $doc;
  }  
	$pos_end = strpos($doc, $tag_end)+strlen($tag_end);
  if($pos_end===false){
    return $doc;
  }
	return substr($doc,0,$pos_start).substr($doc,$pos_end);
	
}

function _liste_all_files_in_dir_R($dir, & $Tab){
	if ($handle = opendir($dir)) {
	   /* Ceci est la fa�on correcte de traverser un dossier. */
	   while (false !== ($file = readdir($handle))) {
			set_time_limit(30);  
		   	if($file!='.' && $file!='..'){
				if(is_file($dir.$file)){
					$row['file']=$file;
					$row['dir']=$dir;
					$Tab[]=$row;
				}
				else if(is_dir($dir.$file)){
					_liste_all_files_in_dir_R($dir.$file."/",$Tab);
				}			
			}
	   }
	   closedir($handle);
	}
}
function get_sess_name($start=""){

	return $start."-".substr(md5(time()),0,8);

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
/**
 * Fonctions nécessaires pour la class.listview_js.php
 * utilisent les expressions régulières pour remplacer et évaluer à la volée le code placé entre
 * %%PHP%%...%%PHPEND%%
 * notamment dans les requêtes sql du back-office    
 **/ 
function eval_match($matches) {
	   ob_start();
	   $stringy = 'echo '.stripslashes(addslashes($matches[1])).';';
	   eval($stringy);
	   $ret = ob_get_contents();
	   ob_end_clean();
	   return $ret;
}
function replace_php_in(&$req){
			$pattern = '/%%PHP%%(.*?)%%PHPEND%%/';
			$req = preg_replace_callback($pattern, 'eval_match', $req);
} 
