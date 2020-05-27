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


class TPDOdb{
/**
* Construteur
**/
function __construct($db_type = '', $connexionString='', $DB_USER='', $DB_PASS='', $DB_OPTIONS=array()) {

	$this -> db = null;
	$this -> rs = null;            //RecordSet
	$this -> currentLine = null;   //ligne courante
	$this -> query = '';			//requete actuelle
	$this -> type = $db_type;
	$this -> debug = false;
	$this -> debugError = false;
	$this -> error = '';
    $this -> stopOnInsertOrUpdateError = true;

	$this -> insertMode = 'INSERT';

	global $conf;

	if(empty($conf->global->ABRICOT_USE_OLD_DATABASE_ENCODING_SETTING)) {
		$charset = $conf->db->character_set;
	}
	else {
		$charset = ini_get('default_charset');

	}

	if(empty($DB_OPTIONS[1002]) && ($charset  === 'iso-8859-1' || $charset  === 'latin1' || empty($charset))){
			$DB_OPTIONS[1002]= 'SET NAMES \'UTF8\'';
	}

	if(empty($connexionString)) {
		/* intégration configuration Dolibarr */
		$db_type = $conf->db->type;
		$db = (!empty($conf->db->name)) ? $conf->db->name : DB_NAME;
		$host = (!empty($conf->db->host)) ? $conf->db->host : DB_HOST;
		$usr = (!empty($conf->db->user)) ? $conf->db->user : DB_USER;
		$pass = (!empty($conf->db->pass)) ? $conf->db->pass : DB_PASS;
		$port = $conf->db->port;

		if (($db_type == '') && (defined('DB_DRIVER'))) {
			$db_type = DB_DRIVER;
		}
		else {
			if ($db_type == 'mysql')
				$db_type = 'mysql';
			else
				$db_type = 'mysqli';
		}


		if (defined('DB_NAME') && constant('DB_NAME')!='') {
			$db = DB_NAME;
			$usr = DB_USER;
			$pass = DB_PASS;
			$host = DB_HOST;
		}
		elseif(empty($db)) {
			$this->debug=true;
		    $this->Error('PDO DB ErrorConnexion : Paramètres de connexion impossible à utiliser (db:'.$db.'/user:'.$usr.')' );
		}

		$this->connexionString = 'mysql:dbname='.$db.';host='.$host;
		if(!empty($port))$this->connexionString .= ';port='.$port;
		if(!empty($charset) && empty($conf->global->ABRICOT_USE_OLD_DATABASE_ENCODING_SETTING) )$this->connexionString.=';charset='.$charset;

		if(defined('DB_SOCKET') && constant('DB_SOCKET')!='') $this->connexionString .= ';unix_socket='.DB_SOCKET;

		try {
			$this -> db = new PDO($this->connexionString, $usr, $pass, $DB_OPTIONS);
		} catch (PDOException $e) {
		    $this->Error('PDO DB ErrorConnexion : '.$e->getMessage().' ( '. $this->connexionString.' - '.$usr .' )' );
		}

	}
	else{
		if(empty($DB_USER))$DB_USER = DB_USER;
		if(empty($DB_PASS))$DB_PASS = DB_PASS;

		$this->connexionString = $connexionString;
		try {
		    $this -> db = new PDO($this->connexionString, $DB_USER, $DB_PASS, $DB_OPTIONS);
		} catch (PDOException $e) {
			$this->debug=true;
		    $this->Error('PDO DB ErrorConnexion : '.$e->getMessage().' ( '. $this->connexionString.' )' );
		}
	}




	$this -> currentLine = array();

	$this->debugError = defined('SHOW_LOG_DB_ERROR') || (ini_get('display_errors')=='On');

	if (isset($_REQUEST['DEBUG']) || defined('DB_SHOW_ALL_QUERY') ) {
		print "SQL DEBUG : 	<br>";
		$this -> debug = true;
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	}

	if (defined('USE_UTF8'))
		$this -> Execute("set names 'utf8'");

	$this->Execute("SET sql_mode = '';");
}

function beginTransaction() {
	return $this->db->beginTransaction();
}
function commit() {
/*
 * Valide une transaction débuté par beginTransaction()
 * Sinon en AutoCommit
 */
	return $this->db->commit();
}
function rollBack() {
/*
 * Annule une transaction débuté par beginTransaction()
 * Sinon en AutoCommitis
 */

	return $this->db->rollBack();
}

function Get_DbType() {
	return $this -> type;
}

function Get_Recordcount() {
	return $this -> rs -> rowCount();
}

function showTrace() {
        print '<pre>';
        $trace=debug_backtrace();

        $log='';
        foreach($trace as $row) {
                if((!empty($row['class']) && $row['class']=='TPDOdb')
                        || (!empty($row['function']) && $row['function']==__FUNCTION__)
                        || (!empty($row['function']) && $row['function']=='call_user_func')) continue;

                $log='<strong>L. '.$row['line'].'</strong>';
                if(!empty($row['class']))$log.=' '.$row['class'];
                $log.=' <span style="color:green">'.$row['function'].'()</span> dans <span style="color:blue">'.$row['file'].'</span>';

                print $log.'<br>';
        }

        //debug_print_backtrace();
        print '</pre><hr>';


}

private function Error($message, $showTrace=true) {
	$this -> error = $message;

	if($this->debug ||  $this->debugError) {
		//print $this->connexionString.'<br/>';
		print "<strong>".$message."</strong>";

		   if($showTrace) {
                $this->showTrace();
           }

	}
	else {
		$trace=debug_backtrace();

        $log='';
        foreach($trace as $row) {
                if((!empty($row['class']) && $row['class']=='TPDOdb')
                        || (!empty($row['function']) && $row['function']==__FUNCTION__)
                        || (!empty($row['function']) && $row['function']=='call_user_func')) continue;

                $log.=' < L. '.$row['line'];
                if(!empty($row['class']))$log.=' '.$row['class'];
                $log.=$row['function'].'() dans '.$row['file'];
				//print $log;
        }


		error_log($message.$log);
	}

}

function bind($k,$v) {

	if(is_array($v)) {
		foreach($v as $kk=>$vv)$this->bind($k, $vv);
	}
	else{
		$this->rs->bindValue($k, $v);
	}


}

function Execute ($sql, $TBind=array()){
        $mt_start = microtime(true)*1000;

        $this->query = $sql;

		if($this->debug) {
				$this->Error('Debug requête : '.$this->query);

		}

		if(!empty($TBind)) {
			$this->rs = $this->db->prepare( $this->query);
			foreach($TBind as $k=>$v) {
				$this->bind($k, $v);
			}

			$this->rs->execute();
		}
		else {
			$this->rs = $this->db->query( $this->query);
		}

        $mt_end = microtime(true)*1000;

		if (!empty($this->db->errorCode)) {
			if($this->debug) $this->Error("PDO DB ErrorExecute : " . print_r($this ->db->errorInfo(),true).' '.$this->query);
			//return(mysql_errno());
		}

		if(defined('LOG_DB_SLOW_QUERY')) {
                $diff = $mt_end - $mt_start;
                if($diff >= LOG_DB_SLOW_QUERY) {
                        $this->Error('PDO DB SlowQuery('.round($diff/1000,2).' secondes) : '.$this -> query)    ;

                }

        }

		return $this->rs;
}
function quote($s) {
	return $this->db->quote($s);
}
function close() {
	$this->db=null;
}
function dbupdate($table,$value,$key){

	   if($this -> insertMode =='REPLACE') {
			return $this->dbinsert($table,$value);
	   }

        $fmtsql = "UPDATE `$table` SET %s WHERE %s";
        foreach ($value as $k => $v) {
                if(is_string($v)) $v=stripslashes($v);

                if (is_array($key)){
                        $i=array_search($k , $key );
                        if ( $i !== FALSE) {
                                $where[] = "`".$key[$i]."`=" . $this->quote( $v ) ;
                            continue;
                        }
                } else {
                        if ( $k == $key) {
                                $where[] = "`$k`=" .$this->quote( $v ) ;
                                continue;
                        }
                }

		if(is_null($v)) $val = 'NULL';
		else if(is_int($v) || is_double($v)) $val=$v;
                else $val = $this->quote( $v );

                $tmp[] = "`$k`=$val";
        }
        $this->query = sprintf( $fmtsql, implode( ",", $tmp ) , implode(" AND ",$where) );

		$res = $this->db->exec( $this->query );

		if($res===false) {
		    $this->Error("PDO DB ErrorUpdate : " . print_r($this ->db-> errorInfo(),true)." ".$this->query);
            if($this->stopOnInsertOrUpdateError) {

                echo $this->error.'<hr />';
                $this->showTrace();
                exit('PDOdb stop execution for caution');

            }
        }

		if($this->debug)$this->Error("Mise à jour (".(int)$res." ligne(s)) ".$this->query);

        return $res;
}
function dbinsert($table,$value){

		if($this -> insertMode =='REPLACE') {
			$fmtsql = 'REPLACE INTO `'.$table.'` ( %s ) values( %s ) ';
		}
		else{
			$fmtsql = 'INSERT INTO `'.$table.'` ( %s ) values( %s ) ';
		}



        foreach ($value as $k => $v) {

                $fields[] = '`'.$k.'`'; // fix special col like status, rank etc...
                if(is_null($v)){
                	$values[] = 'NULL';
				}else{
					$v=stripslashes($v);
					$values[] =$this->quote( $v );
				}
        }
        $this->query = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

        if (!$this->db->exec( $this->query )) {
        		$this->Error("PDO DB ErrorInsert : ". print_r($this ->db-> errorInfo(),true).'<br />'.$this->query);

                if($this->stopOnInsertOrUpdateError) {

                    echo $this->error.'<hr />';
                    $this->showTrace();
                    exit('PDOdb stop execution for caution');

                }

                return false;
        }
		if($this->debug)$this->Error("Insertion ".$this->query);

        return true;
}

function dbdelete($table,$value,$key){
    if (is_array($value)){
          foreach ($value as $k => $v) {
           if (is_array($key)){
              $i=array_search($k , $key );
              if ( $i !== FALSE) {
                 $where[] = "$k=" . $this->quote( $v ) ;
                 continue;
                 }
           }
           else {
              $v=stripslashes($v);
              if( $k == $key ) {
                 $where[] = "$key=" . $this->quote( $v ) ;
                 continue;
                 }
              }
           }
    } else {
        $value=stripslashes($value);
                $where[] = "$key=" . $this->quote( $value );
    }

    $tmp=implode(" AND ",$where);

	$this->query = sprintf( 'DELETE FROM '.$table.' WHERE '.$tmp);


    return $this->db->exec( $this->query );
}

function ExecuteAsArray($sql, $TBind=array() ,$mode = PDO::FETCH_OBJ) {

	$this->Execute($sql, $TBind);
	return $this->Get_All($mode);


}

function Get_All($mode = PDO::FETCH_OBJ, $functionOrClassOrColumn=null) {

	if(!is_null($functionOrClassOrColumn))return $this->rs->fetchAll($mode,$functionOrClassOrColumn);
	else if ($this->rs === false) return array();
	else return $this->rs->fetchAll($mode);


}
function Get_line($mode = PDO::FETCH_OBJ){
	if(!is_object($this->rs)){
		//die('query : '.$this->query);
		//if (isset($_REQUEST['DEBUG'])) {
			$this->Error("PDO DB ErrorGetLine : " . print_r($this ->db-> errorInfo(),true).' '.$this->query);

		//}
		return FALSE;
	}

	$this->currentLine=$this->rs->fetch($mode);

	return $this->currentLine;
}

function Get_lineHeader(){
   $ret=array();

   if (!empty($this->currentLine)){
      foreach ($this->currentLine as $key=>$val){
         	$ret[]=$key;
      }
	}
   return $ret;
}


function Get_field($pField){

		if(isset($this->currentLine->{$pField})) return $this->currentLine->{$pField};
		else return false;

}

function Get_columns($table) {
	$sql = 'SHOW COLUMNS FROM ' . $table;
	if($this->type == 'sqlsrv') {
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = \''.$table.'\'';
	}
	return $this->ExecuteAsArray($sql);
}

function Get_column_list($table, $alias = '') {
	$colfield = 'Field';
	if($this->type == 'sqlsrv') $colfield = 'COLUMN_NAME';

	$TColumns = $this->Get_columns($table);

	$TFields = array();
	foreach ($TColumns as $col) {
		if(!empty($alias)) $TFields[] = $alias . '.' . $col->{$colfield} . ' AS "' . $alias . '.' . $col->{$colfield}.'"';
		else $TFields[] = $col->{$colfield};
	}

	return implode(', ', $TFields);
}

}
