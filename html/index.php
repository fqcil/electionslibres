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
require_once("lib/widget/DropdownFactory.class.php");
require_once("lib/widget/html.class.php");
require_once("lib/liste_electeurs.class.php");

$factory= new DropdownFactory();
$connection = $_SESSION['user']->getDB();

$_SESSION['current_page'] = 'index.php';

//Filtering
$liste_electeurs = new liste_electeurs();
$liste_electeurs->processTopMenu();
$liste_electeurs->processListUI();
$liste_electeurs->processDisplayColumnConfigUI();
$liste_electeurs->processFilterConfigUI();
$liste_electeurs->processSearchUI();
include('include/header.php');
?>

<?php
$liste_electeurs->createTopMenu();
if(strstr($_SESSION['user']->no_circn, 'region_')) {
echo "<h1 class='error'>Vous devez d'abord choisir une circonscription spécifique (et non une région) pour le pointage électoral</h1>";
}
    else {
$liste_electeurs->displaySearchUI();
$liste_electeurs->getDisplayColumnConfigUI();
$liste_electeurs->displayFilterConfigUI();
$liste_electeurs->executeListQuery();
$liste_electeurs->displayListUI();
}

include('include/footer.php');
