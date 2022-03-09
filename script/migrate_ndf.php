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

$limit = 1;
$forceRollback = false;

$aStatusTrans = array(
    Ndfp::STATUS_DRAFT => ExpenseReport::STATUS_DRAFT,
    Ndfp::STATUS_WAITING_VALIDATE => ExpenseReport::STATUS_VALIDATED,
    Ndfp::STATUS_VALIDATE => ExpenseReport::STATUS_APPROVED,
    Ndfp::STATUS_PAID => ExpenseReport::STATUS_CLOSED,
    Ndfp::STATUS_ABANDONED => ExpenseReport::STATUS_REFUSED
);

// 0. Activation du module note de frais
// @TODO
echo '1. Activation du module note de frais<hr>';
if(empty($conf->expensereport->enabled)) {
    echo ' > Activation du module à faire via l\'interface administrateur<br>';
    echo ' > Désactivation des types de frais à faire dans le dictionnaire<hr>';
    $error++;
}

// 2. Gestion des types de dépenses
if(!$error) {
	_logMsg('#'.__LINE__ . '2. Gestion des types de dépenses');
    $sql = 'INSERT IGNORE INTO ' . MAIN_DB_PREFIX . 'c_type_fees (code, label, accountancy_code, active)';
    $sql .= ' SELECT code, label, accountancy_code, active FROM ' . MAIN_DB_PREFIX . 'c_exp';
    $res = $db->query($sql);
    echo ' > ' . $sql . '<hr>';
    if (!$res) {
        $error++;
        echo ' >> ERR : ' . $db->error() . '<hr>';
    }
    $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'c_type_fees tf';
    $sql.= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_exp e ON e.code = tf.code';
    $sql .= ' SET tf.active = 1 WHERE e.active = 1';
    $res = $db->query($sql);
    echo ' > ' . $sql . '<hr>';
    if (!$res) {
        $error++;
        echo ' >> ERR : ' . $db->error() . '<hr>';
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
$extrafields=new ExtraFields($this->db);
$extrafields->addExtraField('fk_societe', 'Company', 'link', 1, 11, 'expensereport', 0, 0, '', $param, 0, '', '1', 0, '', '', 'compagnies',0);


// 3. Transfert des ndf
// @TODO : Extrafield pour stocker le client concerné par la NDF afin de conserver l'info, on verra plus tard si utile ou pas
if(!$error) {
	_logMsg('#'.__LINE__ . '3. Transfert des ndf', 'title');

    // 3.1 Récupération des types de dépenses
	_logMsg('#'.__LINE__ . '3.1. Récupération des types de dépenses', 'title');
    $sql = 'SELECT id, code FROM ' . MAIN_DB_PREFIX . 'c_type_fees';
    $res = $db->query($sql);
    echo ' > ' . $sql . '<hr>';
    if (!$res) {
        $error++;
        echo ' >> ERR : ' . $db->error() . '<hr>';
    } else {
        $aTypeExp = array();
        while($obj = $db->fetch_object($res)) {
            $aTypeExp[$obj->code] = $obj->id;
        }
    }

    if(!$error) {
        $sql = 'SELECT n.rowid FROM ' . MAIN_DB_PREFIX . 'ndfp n LEFT JOIN ' . MAIN_DB_PREFIX . 'expensereport e ON CONCAT(\'ndf\', n.rowid) = e.import_key WHERE e.rowid IS NULL ORDER BY n.rowid';
        $sql.= ' LIMIT '.$limit;

        $res = $db->query($sql);
		_logMsg('#'.__LINE__ . $sql, 'code');
        if ($res) {
            while ($obj = $db->fetch_object($res)) {
                $ndfp = new Ndfp($db);
                $ndfp->fetch($obj->rowid);
                //var_dump($ndfp);
                // Gestion du valideur dans certains cas
                if(empty($ndfp->fk_user_valid) && ($ndfp->statut == Ndfp::STATUS_PAID || $ndfp->statut == Ndfp::STATUS_VALIDATE)) {
                    $ndfp->fk_user_valid = $ndfp->fk_user_author;
                } else if(empty($ndfp->fk_user_valid)) {
                    $ndfp->fk_user_valid = 'NULL';
                }

                if(empty($ndfp->date_valid) && ($ndfp->statut == Ndfp::STATUS_PAID || $ndfp->statut == Ndfp::STATUS_VALIDATE)) $ndfp->date_valid = $ndfp->datec;

                $expensereport = new ExpenseReport($db);
				$expensereport->array_options['options_fk_societe'] = $ndfp->fk_soc;

                $expensereport->date_debut = $ndfp->dates;
                $expensereport->date_fin = $ndfp->datee;

                $expensereport->fk_user_author = $ndfp->fk_user;

                //$expensereport->status = 1;
                $expensereport->fk_user_validator = $ndfp->fk_user_valid;
                $desc = strtr($ndfp->description, array($ndfp->comment_admin => ''));
                $desc = dol_concatdesc($desc, $ndfp->comment_user);
                $desc = dol_concatdesc($desc, $ndfp->comment_admin);
                $expensereport->note_public = $desc;

                $id = $expensereport->create($user, 1);
                if ($id <= 0) {
                    $error++;
					_logMsg('#'.__LINE__ . $db->error(), 'error');
                }

                if (!$error) {
                    /** @var NdfpLine $line */
                    foreach ($ndfp->lines as $line) {
                        $fk_type_exp = isset($aTypeExp[$line->code]) ? $aTypeExp[$line->code] : 0;
                        if(empty($line->qty)) $line->qty = 1;
                        $up = $line->total_ttc / $line->qty;
                        $expensereport->addline($line->qty, $up, $fk_type_exp, $line->taux, $line->dated, $line->comment, $ndfp->fk_project);
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
                    $error++;
					_logMsg('#'.__LINE__ . $db->error(), 'error');
                }

                // Création du règlement + lien banque
				if(!$error && $ndfp->statut == Ndfp::STATUS_PAID){
					$payments = $ndfp->get_payments();
					if(is_array($payments)){

						if(!empty($payments)) {
							foreach ($payments as $payment) {
								$ndfpPayment = new NdfpPayment($db);
								$res = $ndfpPayment->fetch($payment->rowid);
								if ($res) {
									if ($payment->fk_bank) {
										// Create a line of payments
										$paymentExpenseReport = new PaymentExpenseReport($db);
										$paymentExpenseReport->fk_expensereport = $expensereport->id;
										$paymentExpenseReport->datepaid = $ndfpPayment->datepaye;
										$paymentExpenseReport->amounts = array(// Tableau de montant
											$ndfpPayment->fk_user_author => $ndfpPayment->amount,
										);
										$paymentExpenseReport->total = $ndfpPayment->amount;
										$paymentExpenseReport->fk_typepayment = $ndfpPayment->fk_payment;
										$paymentExpenseReport->num_payment = $ndfpPayment->num_payment;
										$paymentExpenseReport->note_public = $ndfpPayment->note_public;

										$paymentExpenseReport->fk_bank = $ndfpPayment->fk_bank;

										if (!$error) {
											$paymentExpenseReportid = $paymentExpenseReport->create($user);
											if ($paymentExpenseReportid < 0) {
												_logMsg('#' . __LINE__ . ' create : ' . $paymentExpenseReport->errorsToString(), 'error');
											}
										}

										if (!$error) {

											// IL ne faut pas créer un nouveau paiement car déja effectué par NDFP+ il faut plutot lié ce qui à été fait
//											$result = $paymentExpenseReport->addPaymentToBank($user, 'payment_expensereport', '(ExpenseReportPayment)', $ndfpPayment->fk_bank, '', '');
//											if (!$result > 0) {
//											_logMsg('#'.__LINE__ . ' addPaymentToBank : '. $paymentExpenseReport->errorsToString(), 'error');
//											}

											// Liaison avec le paiement effectué
											$bank_line = new AccountLine($db);
											$bank_line->fetch($ndfpPayment->fk_bank);

											$acc = new Account($this->db);
											$acc->fetch($bank_line->fk_account);

											// Add link 'payment', 'payment_supplier', 'payment_expensereport' in bank_url between payment and bank transaction
											$url = DOL_URL_ROOT . '/expensereport/payment/card.php?rowid=';
											$result = $acc->add_url_line($bank_line->id, $expensereport->id, $url, '(paiement)', 'payment_expensereport');
											if ($result <= 0) {
												_logMsg('#' . __LINE__ . ' create : ' . $paymentExpenseReport->errorsToString(), 'error');
											}
										}

//									// Logiquement pas besoin car deja fait avant
//									if (!$error) {
//										$paymentExpenseReport->fetch($paymentExpenseReportid);
//										if ($expensereport->total_ttc - $paymentExpenseReport->amount == 0) {
//											$result = $expensereport->setPaid($expensereport->id, $user);
//											if (!$result > 0) {
//												_logMsg('#'.__LINE__ . ' setPaid : '. $paymentExpenseReport->errorsToString(), 'error');
//											}
//										}
//									}
									}
								} else {
									_logMsg('#' . __LINE__ . ' Fectching NdfpPayment #' . $payment->rowid . ' fail', 'error');
								}
							}
						}
					}
					else{
						_logMsg('#'.__LINE__ . ' $ndfp->get_payments() #'.$ndfp->id.' fail', 'error');
					}
				}

				if (!$error) {
					// Déplacement des fichiers du répertoire documents
					$ndfpDocumentPath = DOl_DATA_ROOT . '/ndfp/' . $ndfp->id;
					$expenseReportDocumentPath = DOl_DATA_ROOT . '/expensereport/' . $expensereport->id;
					if (is_dir($ndfpDocumentPath)) {

						$logAction = 'Déplacement dossier : '
							.str_replace(DOl_DATA_ROOT, "", $ndfpDocumentPath)
							.' => '
							.str_replace(DOl_DATA_ROOT, "", $expenseReportDocumentPath);

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
            $error++;
			_logMsg('#'.__LINE__ . $db->error(), 'error');
        }
    } else {
        $error++;
		_logMsg('#'.__LINE__ . $db->error(), 'error');
    }
}

//$error++;
if($error > 0 || $forceRollback) {
	_logMsg('#'.__LINE__ . 'RollBack !', 'error');
    $db->rollBack();
} else {
	_logMsg('#'.__LINE__ . 'Commit !');
    $db->commit();
}

// FIN
_logMsg('#'.__LINE__ . 'Fin du script de migration', 'title');

/**
 * @param $msg
 * @param $type msg | error | title
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
			print '<br/>';
			print '<strong class="error"> >> ERR : '.$msg.'</strong>';
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
