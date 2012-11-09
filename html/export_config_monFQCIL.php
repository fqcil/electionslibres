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

require_once('login.php');
require_once("./config/config.php");
require_once("common.php");
require_once("lib/circonscription.class.php");

header('Content-type: text/plain');
if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
    $conn = $_SESSION['user']->getDB();
    
    //List users for circonscriptions
    $sql = "SELECT users.id_region, circonscriptions.NO_CIRCN_PROVN AS value,circonscriptions.NM_CIRCN_PROVN AS label, monFQCIL_circonscription_og_group_id, username, pw FROM circonscriptions join users ON (users.NO_CIRCN_PROVN=circonscriptions.NO_CIRCN_PROVN AND access='report') WHERE users.id_region IS NULL ORDER BY circonscriptions.NM_CIRCN_PROVN";
    if (!($result = $conn->query($sql))) dislayErrorMessage($sql."<br><br>".$result->errorInfo(), "index.php");

    $retval = null;
    echo '$circonscriptionInfoArray=array();'."\n";
    while( $row = $result->fetch(PDO::FETCH_ASSOC)){
        //var_dump($row);
        if(!empty($row['monFQCIL_circonscription_og_group_id'])){
            $url = "http://pointage.FQCIL.qc.ca/direct_login.php?user=".urlencode($row['username'])."&password=".urlencode($row['pw']);
            echo '$circonscriptionInfoArray[\''.$row['monFQCIL_circonscription_og_group_id'].'\']=array(\'NO_CIRCN_PROVN\'=>\''.$row['value'].'\', \'direct_login_url\'=>\''.$url.'\');'."\n";
        }
    }
echo "\n";
        //List users for regions
    $sql = "SELECT users.id_region, regions.id_region AS value,regions.nom AS label, monFQCIL_region_og_group_id, username, pw FROM regions join users ON (users.id_region=regions.id_region AND access='report') ORDER BY regions.id_region";
    if (!($result = $conn->query($sql))) dislayErrorMessage($sql."<br><br>".$result->errorInfo(), "index.php");

    $retval = null;
    while( $row = $result->fetch(PDO::FETCH_ASSOC)){
        //var_dump($row);
        if(!empty($row['monFQCIL_region_og_group_id'])){
            $url = "http://pointage.FQCIL.qc.ca/direct_login.php?user=".urlencode($row['username'])."&password=".urlencode($row['pw']);
            echo '$circonscriptionInfoArray[\''.$row['monFQCIL_region_og_group_id'].'\']=array(\'id_region\'=>\''.$row['value'].'\', \'direct_login_url\'=>\''.$url.'\');'."\n";
        }
    }
    
    echo $retval;

}

