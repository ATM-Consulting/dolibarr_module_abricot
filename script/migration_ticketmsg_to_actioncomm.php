<?php

if (is_file('../master.inc.php')) include '../master.inc.php';
elseif (is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif (is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif (is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';

require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

$db->begin();

//on sélectionne les données de ticket
$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "ticket_msg";
$resql = $db->query($sql);

if ($resql) {
	$i = 0;
	$num_rows = $db->num_rows($resql);

	require_once DOL_DOCUMENT_ROOT."/ticket/class/ticket.class.php";
	dol_include_once('/multicompany/class/actions_multicompany.class.php');

	$ticketStatic = new Ticket($db);
	$daoStatic = new ActionsMulticompany($db);
	$TTicketCache = array();

	while ($i < $num_rows) {

		$i++;
		$fk_element = 0;
		$object = $db->fetch_object($resql);

		$entity = $object->entity;
		$daoStatic->getInfo($entity);

		$fk_user_action = $object->fk_user_action;
		$datec = $object->datec;
		$note = $object->message;
		$elementtype = 'ticket';

		if (empty($TTicketCache[$object->fk_track_id])) {
			//on récupère l'id du ticket auquel est associé la note en fonction dut track_id
			$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "ticket WHERE track_id ='" . $object->fk_track_id . "'";
			$result = $db->query($sql);

			if ($result) {
				$ticket = $db->fetch_object($result);
				if ($ticket->rowid > 0) {
					$fk_element = $ticket->rowid;
					$res = $ticketStatic->fetch($fk_element);
					if ($res > 0) {
						$TTicketCache[$object->fk_track_id] = $ticketStatic;
						$ticketRef = $ticketStatic->ref;
						$ticketSoc = $ticketStatic->fk_soc;
					}
					else continue;
				}
				else continue;
			}
		}
		else {
			$fk_element = $TTicketCache[$object->fk_track_id]->id;
			$ticketRef = $TTicketCache[$object->fk_track_id]->ref;
			$ticketSoc = $TTicketCache[$object->fk_track_id]->fk_soc;
		}

		//on insère les données de ticket dans la table de ticket standard si la note est associée à un ticket
		if (! empty($fk_element)) {

			$sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm (entity, fk_user_author, fk_user_action, fk_soc, fk_action, transparency, priority, percent, datec, datep, note, fk_element, elementtype, label, code)";

			$sql .= " VALUES (";
			$sql .= "'" . $entity . "',";
			$sql .= "'" . $fk_user_action . "',";
			$sql .= "'" . $fk_user_action . "',";
			$sql .= "'" . intval($ticketSoc) . "',";
			$sql .= "'50',";
			$sql .= "'0',";
			$sql .= "'0',";
			$sql .= "'-1',";
			$sql .= "'" . $datec . "',";
			$sql .= "'" . $datec . "',";
			$sql .= "'" . $db->escape($note) . "',";
			$sql .= "'" . $fk_element . "',";
			$sql .= "'" . $elementtype . "',";
			$sql .= "'[" . $daoStatic->label . "] - Ticket " . $ticketRef . " - Nouveau message',";
			if ($object->private == 1) $sql .= "'TICKET_MSG_PRIVATE'";
			else $sql .= "'TICKET_MSG'";
			$sql .= ")";

			$result = $db->query($sql);

			if (! $result) {
				dol_print_error($db);
				$error_actioncomm = 1;
			}

			$actioncommId = $db->last_insert_id(MAIN_DB_PREFIX."actioncomm", "id");

			// On remplit la table llx_actioncomm_resources
			$sqlResources = 'INSERT INTO '.MAIN_DB_PREFIX.'actioncomm_resources (fk_actioncomm, element_type, fk_element, answer_status, mandatory, transparency)';
			$sqlResources.= " VALUES (";
			$sqlResources .= "'" . $actioncommId . "',";
			$sqlResources .= "'user',";
			$sqlResources .= "'" . $fk_user_action . "',";
			$sqlResources .= "'0',";
			$sqlResources .= "'0',";
			$sqlResources .= "'0'";
			$sqlResources .= ")";

			$result = $db->query($sqlResources);

			if (! $result) {
				dol_print_error($db);
				$error_actioncomm = 1;
			}
		}
	}

	$sql = 'UPDATE '.MAIN_DB_PREFIX.'actioncomm SET ref = id';
	$sql .= ' WHERE ref IS NULL';

	$result = $db->query($sql);

	if (! $result) {
		dol_print_error($db);
		$error_actioncomm = 1;
	}

	/*
	 * RESULTAT SCRIPT
	 */

	if (! empty($error_actioncomm)) {
		$db->rollback();
		echo 'EXECUTION DU SCRIPT KO';
	} else {
		$db->commit();
		echo 'EXECUTION DU SCRIPT OK';
	}
}