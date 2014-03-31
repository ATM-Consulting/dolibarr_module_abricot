<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */
define('INC_FROM_CRON_SCRIPT', true);

require('../config.php');
require('../class/xxx.class.php');

$PDOdb=new TPDOdb;
$PDOdb->db->debug=true;

$o=new TXXX($db);
$o->init_db_by_vars($PDOdb);