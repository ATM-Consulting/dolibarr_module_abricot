<?php

class TObjetStdDolibarr extends TObjetStd {
	
	/**
	 * 
	 * @param unknown $id
	 * @return boolean
	 */
	function fetch($id) {
		
		$sql = 'SELECT '.$this->_get_field_list().' rowid FROM '.$this->get_table().' WHERE '.OBJETSTD_MASTERKEY.'='.$id;
		$res = $this->db->query($sql);

		$this->{OBJETSTD_MASTERKEY}=$id;
			
		$obj = $this->db->fetch_object($res)	;	
		$this->_set_vars_by_db( $obj  );
				
		return true;		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see TObjetStd::save()
	 */
	function save(&$db) {
		
		$query = array();
		$this->_set_save_query($query);
		
		
		$key[0]=OBJETSTD_MASTERKEY;
		
		if($this->{OBJETSTD_MASTERKEY}==0){
			$this->get_newid($db);
			$query[OBJETSTD_MASTERKEY]=$this->{OBJETSTD_MASTERKEY};
			$this->dbinsert($this->get_table(),$query);
		}
		else {
			$query[OBJETSTD_MASTERKEY]=$this->{OBJETSTD_MASTERKEY};
			$this->dbupdate($this->get_table(),$query,$key);	
		}
	
		return $this->{OBJETSTD_MASTERKEY};		
		
	}
	function dbinsert($table,$value){
        $fmtsql = 'INSERT INTO `'.$table.'` ( %s ) values( %s ) ';
        foreach ($value as $k => $v) {
                
                $fields[] = $k;
                if(is_null($v)){
                	$values[] = 'NULL';
				}else{
					$v=stripslashes($v);
					$values[] =$this->quote( $v );
				}
        }
        $this->query = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

        if (!$this->db->query( $this->query )) {
                return false;
        }
		
        return true;
	}
	function quote($v) {
		return "'".addslashes($v)."'";
	}
	function dbupdate($table,$value,$key){
        $fmtsql = "UPDATE `$table` SET %s WHERE %s";
        foreach ($value as $k => $v) {
                $v=stripslashes($v);
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

                if ($v == '') {
                        $val = 'NULL';
                } else {
                        $val = $this->quote( $v );
                }
                $tmp[] = "`$k`=$val";
        }
        $this->query = sprintf( $fmtsql, implode( ",", $tmp ) , implode(" AND ",$where) );
		
		$res = $this->db->query( $this->query );
		
		if($res===false) print "Erreur ".$this->query;
		
        return $res;
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
	
	
	    return $this->db->query( $this->query );
	}
    function _set_vars_by_db(&$obj){
	
		foreach ($this->TChamps as $nom_champ=>$info) {
			if($this->_is_date($info)){
				$this->{$nom_champ} = strtotime($obj->{$nom_champ});
			
			}
			elseif($this->_is_tableau($info)){
				//echo '<li>TABLEAU '.$nom_champ." ".$db->Get_field($nom_champ)." ".unserialize($db->Get_field($nom_champ));
				$this->{$nom_champ} = @unserialize($obj->{$nom_champ});
				//HACK POUR LES DONNES NON UTF8
				if($this->{$nom_champ}===FALSE)@unserialize(utf8_decode($obj->{$nom_champ}));
			}
			elseif($this->_is_int($info)){
				$this->{$nom_champ} = (int)$obj->{$nom_champ};
			}
			elseif($this->_is_float($info)){
				$this->{$nom_champ} = floatval($obj->{$nom_champ});
			}
			elseif($this->_is_null($info)){
				$val = $obj->{$nom_champ};
				// le zéro n'est pas null!				
				$this->{$nom_champ} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0')?null:$val);				
			} 
			else{
				$this->{$nom_champ} = $obj->{$nom_champ};
			}
				
		}
	}
	function get_newid(&$db){
		$sql="SELECT max(".OBJETSTD_MASTERKEY.") as 'maxi' FROM ".$this->get_table();
		$res = $this->db->query($sql);
		$object = $this->db->fetch_object($res);
		$this->{OBJETSTD_MASTERKEY} = (double)$object->maxi + 1;
	}
}
