<?php
/* Copyright (C) 2022	ATM Consulting		<support@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       	abricot/script/migrate_ndf.php
 *	\ingroup    	CliATM
 *	\brief      	Script to migrate the "Note de frais +" module (ATM) to the standard "Expense report" module
 */

ini_set('display_errors', true);

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport.class.php';
require_once DOL_DOCUMENT_ROOT.'/expensereport/class/paymentexpensereport.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/expensereport/modules_expensereport.php';
dol_include_once('/ndfp/class/ndfp.class.php');
dol_include_once("/ndfp/class/ndfp.payment.class.php");

/* @var $db DoliDB */
/* @var $user User */
$error = 0;
$db->begin();

if(empty($user->admin)){
	accessForbidden();
	exit();
}

$limit = 0;
$forceRollback = false;

$aStatusTrans = array(
	Ndfp::STATUS_DRAFT => ExpenseReport::STATUS_DRAFT,
	Ndfp::STATUS_WAITING_VALIDATE => ExpenseReport::STATUS_VALIDATED,
	Ndfp::STATUS_VALIDATE => ExpenseReport::STATUS_APPROVED,
	Ndfp::STATUS_PAID => ExpenseReport::STATUS_CLOSED,
	Ndfp::STATUS_ABANDONED => ExpenseReport::STATUS_REFUSED
);

// 0. Activation du module note de frais

_logMsg('1. Activation du module note de frais', 'title');
if(empty($conf->expensereport->enabled)) {
	echo ' > Activation du module à faire via l\'interface administrateur<br>';
	echo ' > Désactivation des types de frais à faire dans le dictionnaire<hr>';
	$error++;
}

// 2. Gestion des types de dépenses
if(!$error) {
	_logMsg('2. Gestion des types de dépenses', 'title');
	$sql = 'INSERT IGNORE INTO ' . MAIN_DB_PREFIX . 'c_type_fees (code, label, accountancy_code, active)';
	$sql .= ' SELECT code, label, accountancy_code, active FROM ' . MAIN_DB_PREFIX . 'c_exp';
	$res = $db->query($sql);
	_logMsg($sql, 'code');
	if (!$res) {
		_logMsg('#'.__LINE__ . $db->error(), 'error');
	}
	$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'c_type_fees tf';
	$sql.= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_exp e ON e.code = tf.code';
	$sql .= ' SET tf.active = 1 WHERE e.active = 1';
	$res = $db->query($sql);
	_logMsg($sql, 'code');
	if (!$res) {
		$error++;
		_logMsg('#'.__LINE__ . $db->error(), 'error');
	}
}

// Ajout d'un extrafields si non existant


// Add soc for asset
$param = array (
	'options' =>
		array (
			'Societe:societe/class/societe.class.php::statut=1' => NULL,
		),
);
include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
$extrafields=new ExtraFields($db);
$extrafields->addExtraField('fk_societe', 'Company', 'link', 1, 11, 'expensereport', 0, 0, '', $param, 0, '', '1', 0, '', '', 'compagnies',0);


// 3. Transfert des ndf
// @TODO : Extrafield pour stocker le client concerné par la NDF afin de conserver l'info, on verra plus tard si utile ou pas

if(!$error) {
	_logMsg('3. Transfert des ndf', 'title');

	// 3.1 Récupération des types de dépenses
	_logMsg('3.1. Récupération des types de dépenses', 'title');
	$sql = 'SELECT id, code FROM ' . MAIN_DB_PREFIX . 'c_type_fees';
	$res = $db->query($sql);
	_logMsg('#'.__LINE__ . ' '.$sql, 'code');
	if (!$res) {
		_logMsg('#'.__LINE__ . ' '.$db->error(), 'error');
	} else {
		$aTypeExp = array();
		while($obj = $db->fetch_object($res)) {
			$aTypeExp[$obj->code] = $obj->id;
		}
	}

	if(!$error) {
		$sql = 'SELECT n.rowid FROM ' . MAIN_DB_PREFIX . 'ndfp n LEFT JOIN ' . MAIN_DB_PREFIX . 'expensereport e ON CONCAT(\'ndf\', n.rowid) = e.import_key WHERE e.rowid IS NULL ORDER BY n.rowid';

		if(!empty($limit)){
			$sql.= ' LIMIT '.$limit;
		}

		$resList = $db->query($sql);
		_logMsg('#'.__LINE__ . ' '.$sql, 'code');
		if ($resList) {

			_logMsg('#'.__LINE__ . ' NDFP found '.$db->num_rows($resList));
			while ($obj = $db->fetch_object($resList)) {
				$error = 0;
				$ndfp = new Ndfp($db);
				$ndfp->fetch($obj->rowid);

				_logMsg('#'.__LINE__ . ' Traitement  '.$ndfp->getNomUrl());

				//var_dump($ndfp);
				// Gestion du valideur dans certains cas
				if(empty($ndfp->fk_user_valid) && ($ndfp->statut == Ndfp::STATUS_PAID || $ndfp->statut == Ndfp::STATUS_VALIDATE)) {
					$ndfp->fk_user_valid = $ndfp->fk_user_author;
				} else if(empty($ndfp->fk_user_valid)) {
					$ndfp->fk_user_valid = 'NULL';
				}

				if(empty($ndfp->date_valid) && ($ndfp->statut == Ndfp::STATUS_PAID || $ndfp->statut == Ndfp::STATUS_VALIDATE)) $ndfp->date_valid = $ndfp->datec;

				$expensereport = new ExpenseReport($db);

				if($ndfp->fk_soc > 0){
					$societe = new Societe($db);
					if($societe->fetch($ndfp->fk_soc) > 0){
						$expensereport->array_options['options_fk_societe'] = $ndfp->fk_soc;
					}else{
						_logMsg('#'.__LINE__ . ' Societe #'.$ndfp->fk_soc.' not found', 'warning');
					}
				}

				$expensereport->date_debut = $ndfp->dates;
				$expensereport->date_fin = $ndfp->datee;

				$expensereport->fk_user_author = $ndfp->fk_user;

				//$expensereport->status = 1;
				$expensereport->fk_user_validator = $ndfp->fk_user_valid;
				$desc = strtr($ndfp->description, array($ndfp->comment_admin => ''));
				$desc = dol_concatdesc($desc, $ndfp->comment_user);
				$desc = dol_concatdesc($desc, $ndfp->comment_admin);
				$expensereport->note_public = $desc;
				$expensereport->note_private = 'Note de frais importée depuis NDFP+ le '.date('m/d/Y').' '.$ndfp->getNomUrl();

				$id = $expensereport->create($user, 1);
				if ($id <= 0) {
					_logMsg('#'.__LINE__ . ' '.$expensereport->errorsToString(), 'error');
				}

				if (!$error) {
					/** @var NdfpLine $line */
					foreach ($ndfp->lines as $line) {
						$fk_type_exp = isset($aTypeExp[$line->code]) ? $aTypeExp[$line->code] : 0;
						if(empty($line->qty)) $line->qty = 1;
						$up = $line->total_ttc / $line->qty;
						$newlineId = $expensereport->addline($line->qty, $up, $fk_type_exp, $line->taux, $line->dated, $line->comment, $ndfp->fk_project);
						if($newlineId>0){
							$sql = 'UPDATE '.MAIN_DB_PREFIX.'expensereport_det SET';
							$sql.= ' import_key = \'ndfdet'.$line->id.'\'';
							$sql.= ' WHERE rowid = '.$newlineId;
							$res2 = $db->query($sql);
							if (!$res2) {
								_logMsg('#'.__LINE__ . $db->error(), 'error');
							}
						}
					}
				}

				$sql = 'UPDATE '.MAIN_DB_PREFIX.'expensereport SET';
				$sql.= ' tms = \''.$db->idate($ndfp->tms).'\'';
				$sql.= ', date_create = \''.$db->idate($ndfp->datec).'\'';
				if(!empty($ndfp->date_valid)) $sql.= ', date_valid = \''.$db->idate($ndfp->date_valid).'\'';
				if($ndfp->statut != Ndfp::STATUS_DRAFT) $sql.= ', ref = \''.$ndfp->ref.'\'';
				$sql.= ', entity = '.$ndfp->entity;
				$sql.= ', fk_user_creat = '.$ndfp->fk_user;
				$sql.= ', fk_user_modif = '.$ndfp->fk_user;
				$sql.= ', fk_user_valid = '.$ndfp->fk_user;
				$sql.= ', fk_user_validator = '.$ndfp->fk_user_valid;
				$sql.= ', fk_user_approve = '.$ndfp->fk_user_valid;
				$sql.= ', fk_statut = '.$aStatusTrans[$ndfp->statut];
				$sql.= ', import_key = \'ndf'.$ndfp->id.'\'';
				$sql.= ' WHERE rowid = '.$expensereport->id;

				$res2 = $db->query($sql);
				_logMsg('#' . __LINE__ . ' ' . $sql, 'code');
				if (!$res2) {
					_logMsg('#'.__LINE__ . $db->error(), 'error');
				}

				if (!$error) {
					// Déplacement des fichiers du répertoire documents
					$ndfpDocumentPath = DOL_DATA_ROOT . '/ndfp/' . $ndfp->id;
					$expenseReportDocumentPath = DOL_DATA_ROOT . '/expensereport/' . $expensereport->id;
					if (is_dir($ndfpDocumentPath)) {

						$logAction = 'Déplacement dossier : '
							.str_replace(DOL_DATA_ROOT, "", $ndfpDocumentPath)
							.' => '
							.str_replace(DOL_DATA_ROOT, "", $expenseReportDocumentPath);

						if(!is_dir($expenseReportDocumentPath) && !file_exists($expenseReportDocumentPath)){
							_logMsg('#'.__LINE__  . $logAction);
							rename($ndfpDocumentPath, $expenseReportDocumentPath);
						}
						else{
							_logMsg('#'.__LINE__ . $logAction . ' destination already exists', 'error');
						}
					}
				}
			}
		} else {
			_logMsg('#'.__LINE__ . $db->error(), 'error');
		}


		// Création des règlement + lien banque
		if(!$error ){

			_logMsg('4. Création des paiements et les liaison avec les paiements bancaires existants', 'title');

			// TODO : trouver un moyen d'identifier les paiement deja importé car il n'y a pas import_key
			$sql = 'SELECT n.rowid, fk_ndfp_payment, fk_ndfp, amount, tms FROM ' . MAIN_DB_PREFIX . 'ndfp_pay_det n ORDER BY n.rowid ;';
			$resList = $db->query($sql);

			while($objNdfpPayDet = $db->fetch_object($resList)) {
				$error = 0;
				$ndfpPayment = new NdfpPayment($db);
				$resa = $ndfpPayment->fetch($objNdfpPayDet->fk_ndfp_payment);
				if ($resa) {

					_logMsg('#'.__LINE__ . 'Traitement NdfpPayment '.$ndfpPayment->getNomUrl(1));

					if ($ndfpPayment->fk_bank > 0) {

						// TODO cache
						$expensereport = new ExpenseReport($db);
						$sql = "SELECT d.rowid id, d.total_ttc"; // DEFAULT
						$sql .= " FROM ".MAIN_DB_PREFIX.$expensereport->table_element." as d";
						$sql .= " WHERE d.import_key = '".$db->escape('ndf'.$objNdfpPayDet->fk_ndfp)."' ";
						$expenseReportObj = $db->getRow($sql);

						if($expenseReportObj){

							// Create a line of payments
							$paymentExpenseReport = new PaymentExpenseReport($db);
							$paymentExpenseReport->fk_expensereport = $expenseReportObj->id;
							$paymentExpenseReport->datepaid = $ndfpPayment->datep;

							$paymentExpenseReport->amounts = array(// Tableau de montant
								$ndfpPayment->fk_user_author => $objNdfpPayDet->amount,
							);


							$paymentExpenseReport->total = $objNdfpPayDet->amount;
							$paymentExpenseReport->fk_typepayment = $ndfpPayment->fk_payment;
							$paymentExpenseReport->num_payment = $ndfpPayment->num_payment;
							$paymentExpenseReport->note_public = $ndfpPayment->note_public;

							$paymentExpenseReport->fk_bank = $ndfpPayment->fk_bank;

							if (!$error) {
								$paymentExpenseReportid = $paymentExpenseReport->create($user);
								if ($paymentExpenseReportid < 0) {
									_logMsg('#' . __LINE__ . ' create : ' . $paymentExpenseReport->errorsToString(), 'error');
								}
								else{
									_logMsg('created '.$paymentExpenseReport->getNomUrl(1));
									$result = $paymentExpenseReport->update_fk_bank($ndfpPayment->fk_bank);// oui c'est con dans Dolibarr le create le met à 0...
									if ($result<=0) {
										_logMsg('#'.__LINE__ . $db->error(), 'error');
									}
								}
							}

							if (!$error) {

								// Liaison avec le paiement effectué
								$bank_line = new AccountLine($db);
								$resFetch = $bank_line->fetch($ndfpPayment->fk_bank);
								if($resFetch<=0){
									_logMsg('#' . __LINE__ . ' fetch AccountLine '.$ndfpPayment->fk_bank.' : ' . $bank_line->errorsToString().' code error '.$resFetch, 'error');
								}

								$acc = new Account($db);
								$resFetch = $acc->fetch($bank_line->fk_account);
								if($resFetch<=0){
									_logMsg('#' . __LINE__ . ' fetch Account '.$bank_line->fk_account.' : ' . $bank_line->errorsToString().' code error '.$resFetch, 'error');
								}

								// Add link 'payment', 'payment_supplier', 'payment_expensereport' in bank_url between payment and bank transaction
								$url = DOL_URL_ROOT . '/expensereport/payment/card.php?rowid=';
								$result = $acc->add_url_line($ndfpPayment->fk_bank, $expenseReportObj->id, $url, '(paiement)', 'payment_expensereport');
								if ($result <= 0) {
									_logMsg('#' . __LINE__ . ' create : ' . $acc->errorsToString().' code error '.$result, 'error');
								}
							}
						}
						else{
							_logMsg('#' . __LINE__ . ' get ExpenseReport  : ' . $db->error(), 'error');
						}
					}
					else{
						_logMsg('#'.__LINE__ .' fk_bank missing', 'error');
					}
				} else {
					_logMsg('#' . __LINE__ . ' Fectching NdfpPayment #' . $objNdfpPayDet->rowid . ' fail', 'error');
				}

			}
		}

	} else {
		_logMsg('#'.__LINE__ . $db->error(), 'error');
	}
}

//$error++;

// le rollback ne fonctionne pas donc autan ne pas ci fier
$db->commit();

// FIN
_logMsg('Fin du script de migration', 'title');

/**
 * @param $msg
 * @param $type msg | error | title | warning
 * @return void
 */
function _logMsg($msg, $type = 'msg'){
	global $error;
	if(!empty($msg)){
		if( $type == 'title'){
			print '<h3>'.$msg.'</h3>';
		}
		elseif( $type == 'code'){
			print '<pre>'.$msg.'</pre>';
		}
		elseif( $type == 'error'){
			$error++;
			print '<div class="error" style="color: #a41010; background-color: #ffc3c3;padding: 5px;"> >> ERR : ' .$msg.'</div>';
		}elseif( $type == 'warning'){
			print '<div class="warning" style="color: #c45700; background-color: #ffbb88;padding: 5px;"> >> WARN : ' .$msg.'</div>';
		}else{
			print '<br/>';
			print $msg;
		}
	}
}

/**
 * @return void
 */
function _logSep(){
	print '<hr/>';
}
