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



//DATABASE - CONNECTION


// Temporary hack to be able to connect to the database
//$pointageDatabase = 'local_root_no_pass';
//$pointageDatabase = 'remote_sfl';
$environment = 'dev';
switch ($environment){
    case 'prod':
        $pointageDatabaseArray['db_user'] = 'pointage';
        $pointageDatabaseArray['db_port'] = '3306';
        $pointageDatabaseArray['db_pw'] = 'pointage';
        $pointageDatabaseArray['db_location'] = 'localhost';
        $pointageDatabaseArray['db_name'] = 'pointage';
        break;
    case 'dev':
        $pointageDatabaseArray['db_user'] = 'pointage';
        $pointageDatabaseArray['db_pw'] = 'pointage';
        $pointageDatabaseArray['db_location'] = 'localhost';
        $pointageDatabaseArray['db_name'] = 'pointage';
        break;
    default:
        throw new Exception("No valid pointage database specified: $pointageDatabase")  ;
}
$DATABASES['pointage']=$pointageDatabaseArray;

$GLOBALS['first_color'] = "#FFFFFF";
$GLOBALS['second_color'] = "#E4E4E4";
