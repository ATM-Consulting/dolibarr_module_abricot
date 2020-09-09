<?php

    if(is_file('../master.inc.php')) include '../master.inc.php';
    elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
    elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
    elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
    else include '../../master.inc.php';

    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

    $db->begin();

    /*
     * CONF
     */
    $resql = $db->query("SELECT *  FROM ".MAIN_DB_PREFIX."const WHERE name LIKE 'TICKETS\_%'");

    if ($db->num_rows($resql))
	{
		$error_conf = 0;

		while ($object = $db->fetch_object($resql))
		{
			$sql = "INSERT IGNORE INTO ".MAIN_DB_PREFIX."const (`name`, `entity`, `value`, `type`, `visible`, `note`, `tms`)";
			$sql.= " VALUES ('";
			$sql.= str_replace("TICKETS_", "TICKET_", $object->name)."','";
			$sql.= $object->entity."','";
			$sql.= $db->escape($object->value)."','";
			$sql.= $db->escape($object->type)."','";
			$sql.= $db->escape($object->visible)."','";
			$sql.= $db->escape($object->note)."','";
			$sql.= $object->tms."'";
			$sql.= ")";

			$result = $db->query($sql);
			if (!$result) {
				dol_print_error($db);
				$error_conf++;
			}

		}
	}

    /*
     * CATEGORY
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."c_ticketsup_category'");

    if ($db->num_rows($resql) > 0) {
        $sql = "TRUNCATE TABLE " . MAIN_DB_PREFIX . "c_ticket_category";
        $db->query($sql);
    }

    //on sélectionne les données de ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_category";
    $resql = $db->query($sql);

    if ($resql) {

        $i = 0;
        $num_rows = $db->num_rows($resql);

        //on insère les données de ticketsup dans la table de ticket standard
        while ($i < $num_rows) {

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "c_ticket_category (rowid, entity, code, pos, label, active, use_default, description)";

            $sql .= " VALUES (";
            $sql .= "'" . $object->rowid . "',";
            $sql .= "'1',";
            $sql .= "'" . $db->escape($object->code) . "',";
            $sql .= "'" . $db->escape($object->pos) . "',";
            $sql .= "'" . $db->escape($object->label) . "',";
            $sql .= "'" . $db->escape($object->active) . "',";
            $sql .= "'" . $db->escape($object->use_default) . "',";
            $sql .= "'" . $db->escape($object->description) . "'";
            $sql .= ")";

            $result = $db->query($sql);

            if (!$result) {
                dol_print_error($db);
                $error_category = 1;
            }

            $i++;
        }

        if (empty($error_category)) {
            echo MAIN_DB_PREFIX . 'c_ticket_category : OK' . '<br><br>';
        }
    }

    /*
     * EXTRAFIELDS
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."ticketsup_extrafields'");

    if ($db->num_rows($resql) > 0) {
        $sql = "TRUNCATE TABLE " . MAIN_DB_PREFIX . "ticket_extrafields";
        $db->query($sql);
    }

    //Infos sur la table d'extrafields de ticketsup
    $sql = "DESCRIBE ".MAIN_DB_PREFIX."ticketsup_extrafields";
    $resql = $db->query($sql);

    if ($resql)
    {
        //tableau des colonnes de la table
        $TColumnsExtrafields = array();

        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $column = $object->Field;
            $type = $object->Type;

            //si extrafield, on rajoute la colonne dans le tableau
            if($column != 'rowid' && $column != 'tms' && $column != 'fk_object' && $column != 'import_key'){
                $TColumnsExtrafields[$i]['name'] = $column;
                $TColumnsExtrafields[$i]['type'] = $type;
            }
            $i++;
        }

        //pour chaque extrafield, on rajoute la colonne dans la table de ticket standard
        foreach ($TColumnsExtrafields as $column){
            $sql = "ALTER TABLE ".MAIN_DB_PREFIX."ticket_extrafields ADD `".$column['name']."` ".$column['type'];
            $db->query($sql);
        }
    }

    //on sélectionne toutes les valeurs des extrafields de la table ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup_extrafields";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            //on insère les données de ticket sup dans la table standard
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."ticket_extrafields (rowid, tms, fk_object, import_key";

            //si des extrafields en plus dans ticket sup, on rajoute les données dans la table standard
            if(!empty($TColumnsExtrafields)){
                foreach ($TColumnsExtrafields as $column){
                    $sql.="," . $column['name'];
                }
            }

            $sql.= ")";

            $sql.= " VALUES (";
            $sql.="'".$object->rowid."',";
            $sql.="'".$object->tms."',";
            $sql.="'".$db->escape($object->fk_object)."',";
            $sql.="'".$object->import_key."'";

            //si des extrafields en plus dans ticket sup, on rajoute les données dans la table standard
            if(!empty($TColumnsExtrafields)){
                foreach ($TColumnsExtrafields as $column){
                    $extrafieldName = $column['name'];
                    $TDateFields = array('date_prev', 'deadline', 'date_detection');

     				if(in_array($extrafieldName, $TDateFields) && ($object->$extrafieldName === '0000-00-00' || empty($object->$extrafieldName))){
						$sql.= ", NULL ";
					}
     				elseif($extrafieldName == 'heure_est'){
						$sql.= ", ".doubleval($object->$extrafieldName);
					}
                    else{
                    	$sql.= ",'".$db->escape($object->$extrafieldName)."'";
					}
                }
            }

            $sql.=")";


            $result = $db->query($sql);

            if(!$result){
                dol_print_error($db);
                $error_extrafields = 1;
            }

            $i++;
        }

        if (empty($error_extrafields)) {
        	// euh la définition des extrafields ce serait bien aussi...
        	$sqlextDef = "SELECT * FROM ".MAIN_DB_PREFIX."extrafields WHERE elementtype='ticketsup'";
			$resqlExtDef = $db->query($sqlextDef);
			if ($resqlExtDef)
			{
				$fields = array();
				while ($obj = $db->fetch_array($resqlExtDef))
				{
					if (empty($fields))
					{
						$tmpfields = array_keys($obj);
						foreach ($tmpfields as $f) if (!is_numeric($f) && $f != 'rowid') $fields[] = $f;
					}

					$obj['elementtype'] = 'ticket';

					$sql = "INSERT INTO ".MAIN_DB_PREFIX."extrafields (";
					$sql.= '`'.implode('`,`', $fields).'`';
					$sql.= ") values (";
					foreach ($fields as $k => $fieldkey) {
						if ($k != 0) $sql.= ",";
						$sql.= "'".$obj[$fieldkey]."'";
					}
					$sql.= ")";

					$db->query($sql);
				}
			}

            echo MAIN_DB_PREFIX . 'ticket_extrafields : OK' . '<br><br>';
        }
    }

    /*
     * SEVERITY
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."c_ticketsup_severity'");

    if ($db->num_rows($resql) > 0) {
        $sql = "TRUNCATE TABLE " . MAIN_DB_PREFIX . "c_ticket_severity";
        $db->query($sql);
    }

    //on sélectionne les données de ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_severity";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        //on insère les données de ticketsup dans la table de ticket standard
        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_ticket_severity (rowid, entity, code, pos, label, color, active, use_default, description)";

            $sql.= " VALUES ('".$object->rowid."', '1', '".$db->escape($object->code)."', '".$object->pos."', '".$db->escape($object->label)."', '".$db->escape($object->color)."', '".$object->active."', '".$object->use_default."', '".$db->escape($object->description)."')";

            $result = $db->query($sql);

            if(!$result){
                dol_print_error($db);
                $error_severity = 1;
            }

            $i++;
        }

        if (empty($error_severity)) {
            echo MAIN_DB_PREFIX . 'c_ticket_severity : OK' . '<br><br>';
        }
    }


    /*
     * TYPE
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."c_ticketsup_type'");

    if ($db->num_rows($resql) > 0) {
        $sql = "TRUNCATE TABLE " . MAIN_DB_PREFIX . "c_ticket_type";
        $db->query($sql);
    }


    //on sélectionne les données de ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_type";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        //on insère les données de ticketsup dans la table de ticket standard
        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_ticket_type (rowid, entity, code, pos, label, active, use_default, description)";

            $sql.= " VALUES ('".$object->rowid."', '1', '".$db->escape($object->code)."', '".$object->pos."', '".$db->escape($object->label)."', '".$object->active."', '".$object->use_default."', '".$db->escape($object->description)."')";

            $result = $db->query($sql);

            if(!$result){
                dol_print_error($db);
                $error_type = 1;
            }

            $i++;
        }

        if (empty($error_type)) {
            echo MAIN_DB_PREFIX . 'c_ticket_type : OK' . '<br><br>';
        }
    }


    /*
     * TICKET
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."ticketsup'");

    if ($db->num_rows($resql) > 0) {
        $sql = "TRUNCATE TABLE " . MAIN_DB_PREFIX . "ticket";
        $db->query($sql);
    }

    //on sélectionne les données de ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup";
    $resql = $db->query($sql);

    if ($resql)
    {
    	$TStatusChange = array(
			0 => 0
			,1 => 1
			,2 => 2
			,3 => 3
			,4 => 2
			,5 => 3
			,6 => 7
			,8 => 8
			,9 => 9
    	);

        $i = 0;
        $num_rows=$db->num_rows($resql);
        $DistinctTarckIDs = array();

        //on insère les données de ticketsup dans la table de ticket standard
        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

			// fix bug connu de ticketsup : dupplicate track_id
			if (!in_array($object->track_id, $DistinctTarckIDs)) $DistinctTarckIDs[] = $object->track_id;
			else
			{
				while (in_array($object->track_id, $DistinctTarckIDs)) $object->track_id.='a';
			}

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."ticket (rowid, entity, ref, track_id, fk_soc, fk_project, origin_email, fk_user_create, fk_user_assign, subject, message, fk_statut, resolution, progress, timing, type_code, category_code, severity_code, datec, date_read, date_close, notify_tiers_at_create, tms)";

            $sql.= " VALUES (";
            $sql.= "'".$object->rowid."',";
            $sql.= "'".$object->entity."',";
            $sql.= "'".$object->ref."',";
            $sql.= "'".$object->track_id."',";
			$sql.= (!empty($object->fk_soc)?intval($object->fk_soc):'NULL').",";
			$sql.= (!empty($object->fk_project)?intval($object->fk_project):'NULL').",";
            $sql.= "'".$object->origin_email."',";
			$sql.= (!empty($object->fk_user_create)?intval($object->fk_user_create):'NULL').",";
            $sql.= (!empty($object->fk_user_assign)?intval($object->fk_user_assign):'NULL').",";
            $sql.= "'".$db->escape($object->subject)."',";
            $sql.= "'".$db->escape($object->message)."',";
			$sql.= (!empty($TStatusChange[$object->fk_statut])?intval($TStatusChange[$object->fk_statut]):'NULL').",";
            $sql.= intval($object->resolution).",";
            $sql.= "'".$object->progress."',";
            $sql.= "'".$object->timing."',";
            $sql.= "'".$db->escape($object->type_code)."',";
            $sql.= "'".$object->category_code."',";
            $sql.= "'".$object->severity_code."',";

			if(empty($object->datec) || $object->datec === '0000-00-00'){
				$sql.=" NULL,";
			}else{
				$sql.= "'".$object->datec."',";
			}

            if(empty($object->date_read) || $object->date_read === '0000-00-00'){
				$sql.=" NULL,";
			}else{
				$sql.= "'".$object->date_read."',";
			}

			if(empty($object->date_close) || $object->date_close === '0000-00-00'){
				$sql.=" NULL,";
			}else{
				$sql.= "'".$object->date_close."',";
			}

            $sql.= " NULL,";
            $sql.= "'".$object->tms."'";
            $sql.= ")";

            $result = $db->query($sql);

            if(!$result){
                dol_print_error($db);
                $error_ticket = 1;
            }

            $i++;
        }

        if (empty($error_ticket)) {
            echo MAIN_DB_PREFIX . 'ticket : OK' . '<br><br>';
        }

    }

    /*
     * MESSAGES
     */

    //on vide la table standard si on a la table de ticketsup
    $resql = $db->query("SHOW TABLES LIKE '".MAIN_DB_PREFIX."ticketsup_msg'");

    if ($db->num_rows($resql) > 0) {
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "actioncomm WHERE elementtype = 'ticket'";
        $db->query($sql);
    }

    //on sélectionne les données de ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup_msg";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $entity = $object->entity;
            $fk_user_action = $object->fk_user_action;
            $datec = $object->datec;
            $note = $object->message;
            $elementtype = 'ticket';

            //on récupère l'id du ticket auquel est associé la note en fonction dut track_id
            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."ticketsup WHERE track_id ='".$object->fk_track_id."'";
            $result = $db->query($sql);

            if($result){
                $ticketsup = $db->fetch_object($result);
                $fk_element = $ticketsup->rowid;
            }

            //on insère les données de ticketsup dans la table de ticket standard si la note est associée à un ticket
            if(!empty($fk_element)){

                $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (entity, fk_user_action, datec, note, fk_element, elementtype, label)";

                $sql.= " VALUES (";
                $sql.= "'".$entity."',";
                $sql.= "'".$fk_user_action."',";
                $sql.= "'".$datec."',";
                $sql.= "'".$db->escape($note)."',";
                $sql.= "'".$fk_element."',";
                $sql.= "'".$elementtype."',";
                $sql.= "''";
                $sql.= ")";

                $result = $db->query($sql);

                if(!$result){
                    dol_print_error($db);
                    $error_actioncomm = 1;
                }
            }
            $i++;
        }

        if (empty($error_actioncomm)) {
            echo MAIN_DB_PREFIX . 'actioncomm : OK' . '<br><br>';
        }
    }

    /*
     * PIECES JOINTES
     */

    $dir_ticket = $dolibarr_main_data_root . '/ticket';
    $dir_ticketsup = $dolibarr_main_data_root . '/ticketsup';

    if(dol_is_dir($dir_ticketsup)) {

        //si le répertoire "ticket" n'existe pas on le créée
        if (!dol_is_dir($dir_ticket)) {
            $res = mkdir($dir_ticket);
        }

        //on récupère la lise des dossiers dans le répertoire "ticketsup"
        $TDirs = dol_dir_list($dir_ticketsup, 'directories');

        //on copie-colle chaque dossier du répertoire "ticketsup" dans le répertoire "ticket"
        foreach ($TDirs as $dir) {

            $track_id = $dir['name'];

            //on récupère la référence du ticket suivant son track_id
            $sql = "SELECT ref FROM " . MAIN_DB_PREFIX . "ticket WHERE track_id ='" . $track_id . "'";
            $result = $db->query($sql);

            if ($result) {
                $object = $db->fetch_object($result);

                if (!empty($object->ref)) {
                    $dir_source = $dir_ticketsup . '/' . $track_id;
                    $dir_dest = $dir_ticket . '/' . $object->ref;

                    $res = dolCopyDir($dir_source, $dir_dest, 0, 1);

                    if ($res < 0) {
                        echo 'Dossier ' . $object->ref . ' : KO <br><br>';
                        $error_dir = 1;
                    }
                }
            }
        }

        //on supprime le dossier source "ticketsup"
        if (!$error_dir) {

            $res = dol_delete_dir_recursive($dir_ticketsup);

            if (!$res) {
                echo 'Suppression du dossier ' . $dir_source . ' : KO <br><br>';
                $error_dir = 1;
            }
        }

        if (!$error_dir) {
            echo 'Pièces jointes : OK' . '<br><br>';
        }
    }

    /*
     * RESULTAT SCRIPT
     */

    if(!empty($error_dir) || !empty($error_actioncomm) || !empty($error_category) || !empty($error_extrafields) || !empty($error_severity) || !empty($error_severity) || !empty($error_type) || !empty($error_ticket) || !empty($error_conf)){
        $db->rollback();
        echo 'EXECUTION DU SCRIPT KO';
    } else {
        $db->commit();
        echo 'EXECUTION DU SCRIPT OK';
    }




