<?php
/*
 *  Copyright (C) 2012 Fédération Québécoise des Communautés et Industries du Libre.
 *
 *  Author: Emmanuel Milou <emmanuel.milou@fqcil.org>
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

require_once(dirname(__FILE__). '/../../config/config.php');
/** Database to get a connection to
 * @param $database pointage, registre*/
class DB {
    static function getDatabase($database) {
        global $DATABASES;
        $pdo = null;
        switch ($database){
            case 'pointage':
                $databaseCfgArray = $DATABASES['pointage'];
                break;
            case 'registre':
                $databaseCfgArray = $DATABASES['registre'];
                break;
            default:
                throw new Exception("Don't know how to connect to database: $database")  ;
        }

        try {
            if(!empty($databaseCfgArray['db_port'])){
                $dbPortStr = 'port='.$databaseCfgArray['db_port'];
            }
            else {
                $dbPortStr = null;
            }
            $pdo = new PDO('mysql:host='.$databaseCfgArray['db_location'].';dbname='.$databaseCfgArray['db_name'],$databaseCfgArray['db_user'],$databaseCfgArray['db_pw']);
            // Throw exception in case of error and log it
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
            global $OVERRIDE_DB_ENCODING;
            $OVERRIDE_DB_ENCODING?$dbEncoding=$OVERRIDE_DB_ENCODING:$dbEncoding='utf8';
            $pdo->exec("SET CHARACTER SET $dbEncoding");
            $pdo->exec("set session sql_mode=STRICT_ALL_TABLES;");
            //$pdo->exec("set session sql_mode=ONLY_FULL_GROUP_BY;");
        }
        catch( Exception $e ){
            echo 'Erreur : '.$e->getMessage().'<br />';
            die();
        }
        return $pdo;
    }

}
