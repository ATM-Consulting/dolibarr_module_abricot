<?php

    if(is_file('../main.inc.php'))$dir = '../';
    else  if(is_file('../../../main.inc.php'))$dir = '../../../';
    else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
    else $dir = '../../';


    include($dir."master.inc.php");

    $error = 0;

    /*
     * CATEGORY
     */

    $sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."c_ticket_category";
    $db->query($sql);

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_category";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_ticket_category (rowid, entity, code, pos, label, active, use_default, description)";
            $sql.= " VALUES (";
            $sql.= "'".$object->rowid."',";
            $sql.= "'1',";
            $sql.= "'".$db->escape($object->code)."',";
            $sql.= "'".$db->escape($object->pos)."',";
            $sql.= "'".$db->escape($object->label)."',";
            $sql.= "'".$db->escape($object->active)."',";
            $sql.= "'".$db->escape($object->use_default)."',";
            $sql.= "'".$db->escape($object->description)."'";
            $sql.= ")";

            $result = $db->query($sql);

            if($result){
                echo $sql . ' : OK' . '<br><br>';
            } else {
                dol_print_error($db);
                $error++;
            }

            $i++;
        }
    }

    /*
     * EXTRAFIELDS
     */

    //Infos sur la table d'extrafields de ticketsup
    $sql = "DESCRIBE ".MAIN_DB_PREFIX."ticketsup_extrafields";
    $resql = $db->query($sql);

    if ($resql)
    {
        //tableau des colonnes et du type de données de la table
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

        //pour chaque extrafield, on rajoute la colonne dans la table de tickets standard
        foreach ($TColumnsExtrafields as $column){
            $sql = "ALTER TABLE ".MAIN_DB_PREFIX."ticket_extrafields ADD ".$column['name']." ".$column['type'];
            $db->query($sql);
        }
    }

    //on vide la table standard
    $sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."ticket_extrafields";
    $db->query($sql);

    //on sélection tous les extrafields de la table ticketsup
    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup_extrafields";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            //on insert les données de ticket sup dans la table standard
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
            $sql.="'".$object->fk_object."',";
            $sql.="'".$object->import_key."'";

            if(!empty($TColumnsExtrafields)){
                foreach ($TColumnsExtrafields as $column){
                    $extrafieldName = $column['name'];
                    $sql.= ",'".$object->$extrafieldName."'";
                }
            }

            $sql.=")";

            $result = $db->query($sql);

            if($result){
                echo $sql . ' : OK' . '<br><br>';
            } else {
                dol_print_error($db);
                $error++;
            }

            $i++;
        }
    }

    /*
     * SEVERITY
     */

    $sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."c_ticket_severity";
    $db->query($sql);

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_severity";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_ticket_severity (rowid, entity, code, pos, label, color, active, use_default, description)";
            $sql.= " VALUES ('".$object->rowid."', '1', '".$db->escape($object->code)."', '".$object->pos."', '".$db->escape($object->label)."', '".$db->escape($object->color)."', '".$object->active."', '".$object->use_default."', '".$db->escape($object->description)."')";

            $result = $db->query($sql);

            if($result){
                echo $sql . ' : OK' . '<br><br>';
            } else {
                dol_print_error($db);
                $error++;
            }

            $i++;
        }
    }


    /*
     * TYPE
     */

    $sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."c_ticket_type";
    $db->query($sql);

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_ticketsup_type";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_ticket_type (rowid, entity, code, pos, label, active, use_default, description)";
            $sql.= " VALUES ('".$object->rowid."', '1', '".$db->escape($object->code)."', '".$object->pos."', '".$db->escape($object->label)."', '".$object->active."', '".$object->use_default."', '".$db->escape($object->description)."')";

            $result = $db->query($sql);

            if($result){
                echo $sql . ' : OK' . '<br><br>';
            } else {
                dol_print_error($db);
                $error++;
            }

            $i++;
        }
    }


    /*
     * TICKET
     */

    $sql = "TRUNCATE TABLE ".MAIN_DB_PREFIX."ticket";
    $db->query($sql);

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."ticketsup";
    $resql = $db->query($sql);

    if ($resql)
    {
        $i = 0;
        $num_rows=$db->num_rows($resql);

        while ($i < $num_rows){

            $object = $db->fetch_object($resql);

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."ticket (rowid, entity, ref, track_id, fk_soc, fk_project, origin_email, fk_user_create, fk_user_assign, subject, message, fk_statut, resolution, progress, timing, type_code, category_code, severity_code, datec, date_read, date_close, notify_tiers_at_create, tms)";
            $sql.= " VALUES (";
            $sql.= "'".$object->rowid."',";
            $sql.= "'".$object->entity."',";
            $sql.= "'".$object->ref."',";
            $sql.= "'".$object->track_id."',";
            $sql.= "'".$object->fk_soc."',";
            $sql.= "'".$object->fk_project."',";
            $sql.= "'".$object->origin_email."',";
            $sql.= "'".$object->fk_user_create."',";
            $sql.= "'".$object->fk_user_assign."',";
            $sql.= "'".$db->escape($object->subject)."',";
            $sql.= "'".$db->escape($object->message)."',";
            $sql.= "'".$object->fk_statut."',";
            $sql.= "'".$object->resolution."',";
            $sql.= "'".$object->progress."',";
            $sql.= "'".$object->timing."',";
            $sql.= "'".$db->escape($object->type_code)."',";
            $sql.= "'".$object->category_code."',";
            $sql.= "'".$object->severity_code."',";
            $sql.= "'".$object->datec."',";
            $sql.= "'".$object->date_read."',";
            $sql.= "'".$object->date_close."',";
            $sql.= "'',";
            $sql.= "'".$object->tms."'";
            $sql.= ")";

            $result = $db->query($sql);

            if($result){
                echo ' Ticket n°'.$object->rowid.' dans llx_c_ticket : OK' . '<br><br>';
            } else {
                dol_print_error($db);
                $error++;
            }

            $i++;
        }
    }

    /*
     * Messages
     */

    $sql = "DELETE FROM ".MAIN_DB_PREFIX."actioncomm WHERE elementtype = 'ticket'";
    $db->query($sql);

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

            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."ticketsup WHERE track_id ='".$object->fk_track_id."'";
            $result = $db->query($sql);

            if($result){
                $ticketsup = $db->fetch_object($result);
                $fk_element = $ticketsup->rowid;
            }

            if(!empty($fk_element)){

                $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (entity, fk_user_action, datec, note, fk_element, elementtype)";
                $sql.= " VALUES (";
                $sql.= "'".$entity."',";
                $sql.= "'".$fk_user_action."',";
                $sql.= "'".$datec."',";
                $sql.= "'".$db->escape($note)."',";
                $sql.= "'".$fk_element."',";
                $sql.= "'".$elementtype."'";
                $sql.= ")";

                $result = $db->query($sql);

                if($result){
                    echo $sql . ' : OK' . '<br><br>';
                } else {
                    dol_print_error($db);
                    $error++;
                }
            }
            $i++;
        }
    }


