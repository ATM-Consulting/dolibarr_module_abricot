<?php
	
	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else $dir = '../../';

	include($dir."master.inc.php");

    $sql = '
        SELECT DISTINCT
            CONCAT("ALTER TABLE ", C.TABLE_NAME, " CHANGE `", C.COLUMN_NAME, "` `", C.COLUMN_NAME, "` ", C.COLUMN_TYPE, " CHARACTER SET ' . $db->escape($dolibarr_main_db_character_set) . ' COLLATE ' . $db->escape($dolibarr_main_db_collation) . ';") as queries
        FROM INFORMATION_SCHEMA.COLUMNS as C
            LEFT JOIN INFORMATION_SCHEMA.TABLES as T
                ON C.TABLE_NAME = T.TABLE_NAME
        WHERE C.COLLATION_NAME is not null
            AND C.TABLE_SCHEMA="' . $db->escape($db->database_name) . '"
            AND T.TABLE_TYPE="BASE TABLE"

        UNION

        SELECT DISTINCT
            CONCAT("ALTER TABLE ", TABLE_NAME," CONVERT TO CHARACTER SET ' . $db->escape($dolibarr_main_db_character_set) . ' COLLATE ' . $db->escape($dolibarr_main_db_collation) . ';") as queries
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA="' . $db->escape($db->database_name) . '"
          AND TABLE_TYPE="BASE TABLE"

        UNION

        SELECT DISTINCT
            CONCAT("CREATE OR REPLACE VIEW ", V.TABLE_NAME, " AS ", V.VIEW_DEFINITION, ";") as queries
        FROM INFORMATION_SCHEMA.VIEWS as V
            LEFT JOIN INFORMATION_SCHEMA.TABLES as T
                ON V.TABLE_NAME = T.TABLE_NAME
        WHERE V.TABLE_SCHEMA="' . $db->escape($db->database_name) . '"
            AND T.TABLE_TYPE="VIEW"';

    $resql = $db->query($sql);
    if($resql)
    {
        $num = $db->num_rows($sql);

        for($i = 0; $i < $num; $i++)
        {
            $obj = $db->fetch_object($resql);

            echo '<p><code>' . $obj->queries . '</code> : ';

            $resql2 = $db->query($obj->queries);

            echo ($resql2 ? 'OK' : 'KO');

            if(! $resql2) dol_print_error($db);

            echo '</p>';
        }
    }

    $db->close();
