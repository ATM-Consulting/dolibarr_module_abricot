<?php

/**
 * Ceci est plutôt une collection normalisée de fonction 
 */
class TRequeteCore{
	/**
	 * Constructor
	 * @access protected
	 */
	
	
	function get_tache(&$db, $type="ANNONCE", $etat="STANDBY", $limit=50){
		$date=date('Y-m-d H:i:s');
		
		$sql="SELECT id,hash FROM ".TACHE_TABLE." 
			WHERE etat='$etat' AND type='$type' AND dt_exec <='$date'
			ORDER BY priorite DESC,dt_cre 
			LIMIT $limit";
		
		return TRequeteCore::_get_id_by_sql($db, $sql);
	}
	
	static function _get_id_by_sql(&$db, $sql, $field = OBJETSTD_MASTERKEY, $key=false) {
		$TResultat=array();	
		
		$db->Execute($sql);
		while($db->Get_line()){
			if(!empty($key))$TResultat[$db->Get_field($key)] = $db->Get_field($field);
			else $TResultat[] = $db->Get_field($field);
		}
			
		return $TResultat;
	}
	
	static function get_id_from_what_you_want(&$db, $table, $keys=array(), $field=OBJETSTD_MASTERKEY,$orderby=OBJETSTD_MASTERKEY){
		
		$sql="SELECT ".$field." FROM $table WHERE 1 ";

		if(is_array( $keys )) {
			foreach($keys as $key=>$value) {
				$sql.= ' AND '.$key." = '".addslashes( $value )."'";
			}
		}
		else {
			$sql.=' AND '.$keys;
		}
		
		$sql .= " ORDER BY ".$orderby;
		
		return TRequeteCore::_get_id_by_sql($db, $sql,$field);
	}
	
	static function get_keyval_by_sql(&$db, $sql, $fieldkey, $fieldval) {
		return TRequeteCore::_get_id_by_sql($db,$sql,$fieldval,$fieldkey);
	}
}

