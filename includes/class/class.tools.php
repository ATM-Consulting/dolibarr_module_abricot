<?php

/*
 Copyright (C) 2006-2013 Alexis Algoud <azriel68@gmail.com>
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



class Tools{

    static function checkVersion(&$DoliDb, $moduleName) {
        global $conf;

        if(class_exists($moduleName)) {

            $conf_name = 'ATM_MODULE_VERSION_'.strtoupper($moduleName);

            $mod = new $moduleName($DoliDb);

            if(!empty($mod->version)) {
                $version = $mod->version;
                if($conf->global->$conf_name != $version) {

                    $message = "Your module wasn't updated (v".$conf->global->$conf_name." != ".$version."). Please reload it or launch the update of database script";

                    accessforbidden($message);
                }
            }
        }

    }

    static function setVersion(&$DoliDb, $moduleName) {

        if(class_exists($moduleName)) {
            dol_include_once('/core/lib/admin.lib.php');

            $mod = new $moduleName($DoliDb);

            if(!empty($mod->version)) {
                $version = $mod->version;
                dolibarr_set_const($DoliDb, 'ATM_MODULE_VERSION_'.strtoupper($moduleName), $version);

            }
        }


    }

	static function url_format($s){
		$r='';
		$nb=strlen($s);
		for($i = 0; $i < $nb; $i++){
			if(ctype_alnum($s[$i]) || $s[$i]=='-' || $s[$i]=='.'){
				$r.=$s[$i];
			}
		}
		return $r;
	}

	static function anagramme(&$TResult,$chaine,$Tab){
		$TResult[] = $chaine;
		foreach($Tab as $x => $car){
			$LTab = $Tab;
			unset($LTab[$x]);
			Tools::anagramme($TResult,$chaine.$car, $LTab);
		}
	}

	static function summarize($texte, $nb=5, $asString=true) {

		//print_r($TWord);
		$TSentence=array();
		Tools::_sum_get_sentence_with_date($TSentence, $texte);

		$TWord = Tools::_sum_get_words($texte, $nb);
		Tools::_sum_get_sentence($TSentence, $texte, $TWord);
		//print_r($TSentence);

		ksort($TSentence);
		$TSentence = array_slice($TSentence,0,$nb);


		if($asString) return implode(' ', $TSentence);
		else return $TSentence;
	}
	static function _sum_get_sentence(&$TSentence, $texte, $TWord) {



		foreach($TWord as $word) {

			$mot = $word['mot'];
			$s_texte=strtr($texte,array(
				"\n"=>"."
				,'?'=>"."
				,'!'=>"."
				,'"'=>"."

			));

			$pos = strpos($s_texte, $mot);
			if($pos!==false) {

				$sub_texte = substr($s_texte,0,$pos);
				$pos_start = strrpos($sub_texte, '.');
				if($pos_start===false)$pos_start=0;
				else $pos_start++;

				$pos_end =strpos($s_texte, '.', $pos);
				if($pos_end===false)$pos_end=strlen($texte);

				//print "($pos) $pos_start -> $pos_end. <br>";

				$sentence = trim(substr($s_texte, $pos_start, $pos_end-$pos_start));
				//print  "$mot ($sub_texte) ".$sentence.'<br>';
				if(Tools::_sum_add_sentence($TSentence, $sentence, $pos_start)) {
					$texte = substr($texte,0,$pos_start).' '.substr($texte,$pos_end)	;
				}

			}
			else{
				/*print "$mot non trouvé ?";*/
			}

		}


	}

	static function _sum_add_sentence(&$TSentence, $sentence,$pos_start) {

		if(empty($sentence)) return false;

		foreach($TSentence as &$s) {
			$meta_s1  = metaphone($s);
			$meta_s2  = metaphone($sentence);

			$score = strcasecmp($meta_s1,$meta_s2);
			//print "$score ($s ($meta_s1)=== $sentence($meta_s2))<br>";
			if(abs($score)<1) return false;
		}

		$TSentence[$pos_start] = $sentence;

		return true;
	}
	static function _sum_get_sentence_with_date(&$TSentence, $texte) {
		$KeyDate=explode(',','janvier,février,mars,avril,mai,jui,juillet,août,septembre,octobre,novembre,décembre');
		$s_texte=strtr($texte,array(
				"\n"=>"."
				,'?'=>"."
				,'!'=>"."
				,'"'=>"."

			));

		foreach($KeyDate as $motDate) {

			$pos = strpos($s_texte, $motDate);
			if($pos!==false) {

				$sub_texte = substr($s_texte,0,$pos);
				$pos_start = strrpos($sub_texte, '.');
				if($pos_start===false)$pos_start=0;
				else $pos_start++;

				$pos_end =strpos($s_texte, '.', $pos);
				if($pos_end===false)$pos_end=strlen($texte);

				$sentence = trim(substr($s_texte, $pos_start, $pos_end-$pos_start));
				Tools::_sum_add_sentence($TSentence, $sentence, $pos_start);

			}

		}



	}
	static function _sum_get_words($texte,$nbWord) {

		$texte = strtr($texte,array(
			"\n"=>" "
			,','=>" "
			,'.'=>" "
			,'?'=>" "
			,'!'=>" "
		));

		$Tab = explode(' ',$texte);

		$TMotCollect = array();
		foreach($Tab as $k=>$mot) {
			if(strlen($mot)>4) {
				@$TMotCollect[$mot]+= (1000 - $k);
			}
		}

		$TMot=array(); $i=0;
		foreach($TMotCollect as $mot=>$nb) {
			$TMot[$nb] = array('mot'=>$mot, 'position'=>$i);
			$i++;
		}

		krsort($TMot);
		usort($TMot, 'Tools::sortByPosition');
		//print_r($TMot);
		return array_slice($TMot,0,$nbWord);
	}

	function sortByPosition($a, $b) {
		if($a['position']>$b['position']) return 1;
		elseif($a['position']<$b['position']) return -1;
		else return 0;
	}

	/*
	 * donne la listes des constantes interne au projet
	 *
	 * c'est les variables globales commencant par : "GBL"
	 *
	 * @return 	array 	Le tableau des variables globales d�finies
	 */
	static function getConstant(){
		/* table des bglobales */
		$temp = &$GLOBALS;
		$Tout = array();
		foreach($temp as $key => $val){
			if(substr($key,0,4)=="GBL_"){
				$Tout[] = $key;
			}
		}
		return $Tout;
	}


	/*
	 * fonction file_get_contents maison
	 *
	 * cela permet de tracer tout les appels externes et �ventuellement d'utiliser curl
	 * pour d�tecter les entetes HTTP (encodage par exemple)
	 *
	 * @param 	string $filename
	 */
	static function file_get_contents($filename){
		$time_start = Tools::microtime_float();
		$out = file_get_contents($filename);
		$time_end =  Tools::microtime_float();
		$time = number_format($time_end - $time_start,3);
		file_put_contents(PATH_TMP."file_get_contents.txt",date("YmdHis").":".$time."ms:".$filename."\r\n",FILE_APPEND);
		return($out);
	}


	static function microtime_float(){
     list($usec, $sec) = explode(" ", microtime());
     return ((float)$usec + (float)$sec)*1000;
	}

	static function debug($tab = array()){
		if($tab!=array()){
			Tools::pre($tab);
		}

		$temp = get_defined_constants();
		$display = false;
		foreach($temp as $key => $val){
			if($key=='DB_DRIVER')$display = TRUE;
			if($display==FALSE)unset($temp[$key]);
		}
		Tools::pre($temp);
		//Tools::pre(get_defined_vars());

	}


	/*
	 * CryptMsgUrl($string) retourne la chaine url encodée et encryptée
	 *
	 * @param string chaine
	 *
	 * @return string chaine encodée
	 */
	static function CryptMsgUrl($string){
		$crypt = new RevCrypt(MESSAGE_KEY);

		return(urlencode($crypt->code($string)));

	}

	/*
	 * CryptMsgUrl($string) retourne la chaine url encodée et encryptée
	 *
	 * @param string chaine
	 *
	 * @return string chaine encodée
	 */
	static function DeCryptMsg($string){
		$crypt = new RevCrypt(MESSAGE_KEY);

		return($crypt->decode($string));

	}


	static function objectToArray($object) {

		$array=array();
		foreach($object as $member=>$data)
		{
			$array[$member]=$data;
		}
		return $array;
	 }

	static function pre($val){

		echo '<pre>';
		print_r($val);
		echo '</pre>';


	}

	static function parse_txt_file($file){
			$Tab=array();
			if(!file_exists($file)){
				echo '<li>le fichier '.$file.' n\'existe pas!';
				return($Tab);
			}

			$f1=fopen($file,"r");
			if($f1!=false){
			  	while(!feof($f1)){
			  		$ligne = trim(fgets($f1));
			  		if($ligne!=""){
			  			$TLigne =explode("\t", $ligne);

			  			@$key=$TLigne[0];
			  			@$value=$TLigne[1];

			  			$TSecteur=explode(",", $key);
			  			$nb=count($TSecteur);
			  			for($i=0;$i<$nb;$i++){
				  			$sect = $TSecteur[$i];
				          	$Tab[$sect]=$value;
				        } // for
			  		} // if
			  	} // while
		  		fclose($f1);
			}

			return $Tab;
		}

	static function decoupe_doc($doc, $tag_start = "<!--START_OF_DOC-->",$tag_end = "<!--END_OF_DOC-->"){
		$pos_start = strpos($doc, $tag_start) + strlen($tag_start);
		$pos_end = strpos($doc, $tag_end, $pos_start);

		return substr($doc,$pos_start,$pos_end-$pos_start);
	}

	static function writeIniFile($datas,$filename,$reinit_val=false,$dim=2){


		$data = "; Ceci est un fichier de configuration généré dynamiquement \r\n";
		$data .= "\r\n\r\n \r\n";
		$nb=1;
		foreach($datas as $key => $val){
			$data .= 'key_'.$nb.' = "'.$key.'"'." \r\n";
			switch($dim){
				case 2:
					if($reinit_val==false)$data .= 'val_'.$nb++.' = "'.$val.'"'." \r\n\r\n";
					else $data .= 'val_'.$nb++.' = ""'." \r\n\r\n";
					break;
				case 3:

					if(is_array($val)){
						$n=1;
						foreach($val as $sskey => $ssval){
							if($reinit_val==false)$data .= 'val'.$n.'_'.$nb.' = "'.$ssval.'"'." \r\n";
							else $data .= 'val'.$n.'_'.$nb.' = ""'." \r\n";
							$n++;
						}
						$data .= " \r\n";
					}else{
						if($reinit_val==false)$data .= 'val_'.$nb.' = "'.$val.'"'." \r\n\r\n";
						else $data .= 'val_'.$nb.' = ""'." \r\n\r\n";
					}
					$nb++;
					break;
			}

		}

		file_put_contents($filename, $data);
		chmod ( $filename , 0775 ) ;

	}

	static function parse_ini_file_array($filename,$dim=2){
		$temp = parse_ini_file($filename);
		$array = array();
		//if($dim==3)print_r($temp);
		foreach($temp as $key => $val){
			list($type,$number) = explode('_',$key);
			if($type=='key'){   //if($i++%$dim==1){
				//if($dim==3)echo '<li>val:'.$val.' key:'.$key."($i)";
				switch($dim){
					case 2:
						$array[$val] = $temp[str_replace('key_','val_',$key)];
						break;
					case 3:
						//@ car les valeurs ne sont peut etre pas définies
						$array[0][$val] = @$temp[str_replace('key_','val1_',$key)];
						$array[1][$val] = @$temp[str_replace('key_','val2_',$key)];
						break;
				}
			}
		}
		return $array;
	}

	//permet de mettre un espace devant une chaine et pas deux...
	//exemple : transformation des "xxxx(H/F)" et "xxxx (H/F)" en "xxxxx (H/F)"
	static function OnspaceBefore($string,$search){
		$string = str_replace($search,' '.$search,$string);
		$string = str_replace('  '.$search,' '.$search,$string);
		return($string);
	}

	static function string2num($s) {
		if(is_numeric($s)){
			// detect scientific notation before is_string : because 8.0E-6 is detected as numéric but also as string  AND it's a NUMERIC we need to detect first
			return (float)$s;
		}
		elseif(is_string($s))
		{
			$r = '';
			$l=strlen($s);

			for($i = 0;$i<$l;$i++) {
				$c = $s[$i];
				if($c == ',') $c = '.';
				if(ctype_digit($c) || $c == '.' || $c == '-') {
					$r.=$c;
				}
			}

			return (float)$r;
		}
		else if(empty($s)) {
			return 0;
		}
		else {
			return $s;
		}


	}

	static function get_time($date) {
		$std=new TObjetStd;
		$std->set_date('dt_date', $date);

		return $std->dt_date;
	}

	static function showTime($time) {

		if($time==0) {
			return '';
		}

		$time_ref = strtotime('2013-01-01 00:00:00');

		if($time<86400) {
			return date('H:i', $time + $time_ref);
		}
		else {

			return date('z\j H\h i\m', $time  + $time_ref);
		}



	}

}



class TInsertSQL {

	static function getFileContent($file, $gz=false) {

		$f1 = TInsertSQL::_fopen($file, $gz);

		print "Lecture du fichier..."; flush();

		if($f1===false) { exit("Erreur d'ouverture du fichier"); }

		$ligne="";

		while(!TInsertSQL::_feof($f1, $gz)){

			$ligne.= TInsertSQL::_fgets($f1, $gz)."\n";

	    }

		TInsertSQL::_fclose($f1, $gz);

		return $ligne;

	}

	static function insertSQL(&$db, $sql, $tag='INSERT INTO' ) {


		$buffer = explode("\n",$sql);
		$cpt=0;$ligne='';
		foreach ($buffer as $ligne_partiel) {

				if($ligne_partiel!='' && (strcmp(substr($ligne_partiel,0,2),'--'))){

				$ligne.=$ligne_partiel."\n";
				$var =TInsertSQL::_get_sql_expression($ligne, $tag);

					if(count($var)>1){

						$nb=count($var);
						for($i = 0; $i < $nb-1; $i++){


									$sql=$var[$i];

									$db->Execute($sql);// print '<strong>Echec SQL : </strong>'.$sql.'<br /><br />';
									if($cpt==1000){
										$cpt=0;
											print $sql."<br>";
									}

						} // for

						$ligne = $var[$nb-1];
					}


				}
				$cpt++;
		}

		$db->Execute($ligne);
		print "($ligne) Fin";

	}

	static function _get_sql_expression($ligne,$tag) {
	/* Recherche les expression SQL et les retourne dans un tableau */
		$end = false;
		$Tab=array();

		$pos_deb=0;
		while(!$end) {
				$pos_fin = strpos($ligne, $tag, $pos_deb+1);
				if($pos_fin===false)$pos_fin = strpos($ligne, 'ALTER TABLE', $pos_deb+1);
				if($pos_fin===false)$pos_fin = strpos($ligne, 'DROP TABLE', $pos_deb+1);
				if($pos_fin===false)$pos_fin = strpos($ligne, 'CREATE TABLE', $pos_deb+1);
				if($pos_fin===false)$pos_fin = strpos($ligne, 'REPLACE INTO', $pos_deb+1);

				if($pos_fin===false) {
						$end=true;
						$Tab[]=substr($ligne, $pos_deb);
				}
				else {

					$Tab[]=substr($ligne, $pos_deb,$pos_fin-$pos_deb);
					$pos_deb = $pos_fin;
				}


		}

		return $Tab;
	}
	static function _fclose(&$f1, $gz=false) {

		if($gz) {
			return gzclose($f1);
		}
		else {
			return fclose($f1);
		}

	}
	static function _fgets(&$f1, $gz=false) {

		if($gz) {
			return gzgets($f1);
		}
		else {
			return fgets($f1);
		}

	}
	static function _feof(&$f1, $gz=false) {

		if($gz) {
			return gzeof($f1);
		}
		else {
			return feof($f1);
		}

	}

	static function _fopen($file, $gz=false) {

		if($gz) {
			return gzopen($file ,'r');
		}
		else {
			return fopen($file, 'r');
		}
	}



}
