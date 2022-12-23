<?php

	if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

$db->begin();





//on sélectionne les données de ticketsup
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup WHERE rowid in (897,2914,3222,3599,3645,3840,5343,6343,8380,9432,19892)";
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
	$TrasksIds = array();

	//on insère les données de ticketsup dans la table de ticket standard
	require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
	$ticket = new ticket($db);
	while ($i < $num_rows){

		$object = $db->fetch_object($resql);

		// fix bug connu de ticketsup : dupplicate track_id
		if (!in_array($object->track_id, $DistinctTarckIDs)) $DistinctTarckIDs[] = $object->track_id;
		else
		{
			while (in_array($object->track_id, $DistinctTarckIDs)) $object->track_id.='a';
		}

		$r = $ticket->getDefaultRef();
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."ticket (entity, ref, track_id, fk_soc, fk_project, origin_email, fk_user_create, fk_user_assign, subject, message, fk_statut, resolution, progress, timing, type_code, category_code, severity_code, datec, date_read, date_close, notify_tiers_at_create, tms)";
		$sql.= " VALUES (";
		//$sql.= "'".$object->rowid."',";
		$sql.= "'".$object->entity."',";
		$sql.= "'".$ticket->getDefaultRef()."',";
		$sql.= "'".$object->track_id."',";
		$TrasksIds[] = $object->track_id; // on ajoutes lestrackid pour les msg (actionCom)
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
		echo '<pre>' . var_export($result, true) . '</pre>';
		if(!$result){
			echo '<pre>' . var_export($sql, true) . '</pre>';
			dol_print_error($db);
			$error_ticket = 1;
		}

		$i++;
	}

	if (empty($error_ticket)) {
		echo MAIN_DB_PREFIX . 'ticket doublon  : OK' . '<br><br>';
		$db->commit();
	}else{
		$db->rollback();
	}



	$db->begin();
	//on sélectionne les données de ticketsup
	$sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup_msg where fk_track_id in (". implode($TrasksIds).")";
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

				$field = (float) DOL_VERSION >= 14  ? ' ref, ' : '';
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm ( " . $field . " entity, fk_user_action, datec, note, fk_element, elementtype, label)";

				$sql.= " VALUES (";
				$sql .= ((float) DOL_VERSION >= 14 ) ? " '(PROV)'," : "";
				$sql.= "'".$entity."',";
				$sql.= "'".$fk_user_action."',";
				$sql.= "'".$datec."',";
				$sql.= "'".$db->escape($note)."',";
				$sql.= "'".$fk_element."',";
				$sql.= "'".$elementtype."',";
				$sql.= "''";
				$sql.= ")";

				$resql2 = $db->query($sql);
				if ($resql2) {
					if ((float) DOL_VERSION >= 14 ) {
						$ref = $id = $db->last_insert_id(MAIN_DB_PREFIX . "actioncomm", "id");
						$sql = "UPDATE " . MAIN_DB_PREFIX . "actioncomm SET ref='" . $db->escape($ref) . "' WHERE id=" . $id;
						$resql2 = $db->query($sql);
						if (!$resql2) {
							$error++;
							dol_syslog('Error to process ref: ' . $this->db->lasterror(), LOG_ERR);
							$this->errors[] = $db->lasterror();
							//dol_print_error($db);
							//$error_actioncomm = 1;
						}
					}
				}else{
					dol_print_error($db);
					$error_actioncomm = 1;
				}
			}
			$i++;
		}
			if (empty($error_actioncomm) ) {
				echo MAIN_DB_PREFIX . 'actioncomm : OK' . '<br><br>';
				$db->commit();
			}else{
				$db->rollback();
			}
		}
}
