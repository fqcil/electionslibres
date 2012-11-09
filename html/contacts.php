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
require_once("lib/liste_contacts.class.php");

$factory= new DropdownFactory();
$connection = $_SESSION['user']->getDB();

$_SESSION['current_page'] = 'contacts.php';

//Filtering
$liste_electeurs = new liste_electeurs();
$liste_contacts = new liste_contacts();
$liste_electeurs->processTopMenu();
$liste_contacts->processListUI();
$liste_contacts->processDisplayColumnConfigUI();
$liste_contacts->processFilterConfigUI();
$format = $liste_contacts->processSelectFormatUI();

if($format=='csv') {
    global $OVERRIDE_DB_ENCODING;
    $OVERRIDE_DB_ENCODING='latin1';
    $liste_contacts->executeListQuery();
    $liste_contacts->exportCSV();
}
else {
    $liste_contacts->executeListQuery();
    require('include/header.php');

    $liste_electeurs->createTopMenu();
    $liste_contacts->getDisplayColumnConfigUI();
    $liste_contacts->displayFilterConfigUI();
    $liste_contacts->displaySelectFormatUI();
    $liste_contacts->displayListUI();



    require('include/footer.php');
}
