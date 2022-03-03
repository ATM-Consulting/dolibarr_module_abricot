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
dol_include_once('/ndfp/class/ndfp.class.php');

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
    echo '2. Gestion des types de dépenses<hr>';
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

// 3. Transfert des ndf
// @TODO : Extrafield pour stocker le client concerné par la NDF afin de conserver l'info, on verra plus tard si utile ou pas
if(!$error) {
    echo '3. Transfert des ndf<hr>';

    // 3.1 Récupération des types de dépenses
    echo '3.1. Récupération des types de dépenses<hr>';
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
        echo ' > ' . $sql . '<hr>';
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

                $object = new ExpenseReport($db);

                $object->date_debut = $ndfp->dates;
                $object->date_fin = $ndfp->datee;

                $object->fk_user_author = $ndfp->fk_user;

                //$object->status = 1;
                $object->fk_user_validator = $ndfp->fk_user_valid;
                $desc = strtr($ndfp->description, array($ndfp->comment_admin => ''));
                $desc = dol_concatdesc($desc, $ndfp->comment_user);
                $desc = dol_concatdesc($desc, $ndfp->comment_admin);
                $object->note_public = $desc;

                $id = $object->create($user, 1);
                if ($id <= 0) {
                    $error++;
                    echo ' >> ERR : ' . $db->error() . '<hr>';
                }

                if (!$error) {
                    /** @var NdfpLine $line */
                    foreach ($ndfp->lines as $line) {
                        $fk_type_exp = isset($aTypeExp[$line->code]) ? $aTypeExp[$line->code] : 0;
                        if(empty($line->qty)) $line->qty = 1;
                        $up = $line->total_ttc / $line->qty;
                        $object->addline($line->qty, $up, $fk_type_exp, $line->taux, $line->dated, $line->comment, $ndfp->fk_project);
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
                $sql.= ' WHERE rowid = '.$object->id;

                $res2 = $db->query($sql);
                echo ' > ' . $sql . '<hr>';
                if (!$res2) {
                    $error++;
                    echo ' >> ERR : ' . $db->error() . '<hr>';
                }

                // @TODO : création du règlement + lien banque
                // @TODO : déplacement des fichiers du répertoire documents

            }
        } else {
            $error++;
            echo ' >> ERR : ' . $db->error() . '<hr>';
        }
    } else {
        $error++;
        echo ' >> ERR : '.$db->error().'<hr>';
    }
}

//$error++;
if($error > 0 || $forceRollback) {
    print '<br/><pre><strong><span style="background-color: red;">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;ROLLBACK !</strong></pre><br/>';
    $db->rollBack();
} else {
    print '<br/><pre><strong><span style="background-color: green;">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;COMMIT !</strong></pre><br/>';
    $db->commit();
}

// FIN
echo '<hr>Fin du script de migration<hr>';
