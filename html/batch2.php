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
require_once("lib/liste_electeurs.class.php");
require_once('lib/historique.class.php');
include('include/header.php');

if($_POST['apply']){

    // Chaque électeur est séparé par un retour chariot
    $electors_batch = split("\n", $_POST['batch_voters']);
    $updated = 0;
    $liste_electeurs = new liste_electeurs();

    foreach ($electors_batch as $key => $value){

        if($value != '' && ($_POST['RSLT'] != '' || $_POST['VISIT'] != '' || $_POST['CALL'] != '' || $_POST['VOTE'] != '')){

            $electors = explode(".", $value);

            $poll_id = $electors[0];
            $elector_id = $electors[1];
            
            // Get the elector from the dge list
            $sql = "SELECT * FROM liste_dge WHERE NO_SECTN_VOTE='{$poll_id}' AND NO_ELECT='{$elector_id}' AND NO_CIRCN_PROVN='{$_SESSION['user']->no_circn}';";
            $conn = $_SESSION['user']->getDB();
            $result = $conn->query($sql);
            $elect = $result->fetch(PDO::FETCH_ASSOC);
                              
            $electeur = Historique::getHistoriqueFromIdDge($elect['ID_DGE']);
            $electeur->createUpdate(0, $elect['ID_DGE']);
            
            $modified = $modified.'- '.$value.'<br>';
            $updated ++;
        }												
    }
}

?>
<h3>Nombre d'électeurs modifiés:</h3><? echo $updated; ?>
<h3>Liste des électeurs modifiés:</h3><? if($updated == 0) echo 'vide'; else echo $modified; ?>

<br><br>
[ <a href="saisie.php">Retour</a> ]
