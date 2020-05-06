<?php

    if(is_file('../master.inc.php')) include '../master.inc.php';
    elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
    elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
    elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
    else include '../../master.inc.php';

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

	    $resql3 = $db->query("SET FOREIGN_KEY_CHECKS = 0");

        for($i = 0; $i < $num; $i++)
        {
            $obj = $db->fetch_object($resql);

            $resql2 = $db->query($obj->queries);

            $status = $resql2 ? 'OK' : 'KO';
            $style = $resql2 ? '' : ' style="background: #fbb"';

            echo '<p' . $style . '><code>' . $obj->queries . '</code> : ' . $status;

            if(! $resql2)
            {
                echo '<br /><br />';

                dol_print_error($db);
            }

            echo '</p>';
        }
    }

    $db->close();
