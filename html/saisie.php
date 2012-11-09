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
require_once("lib/liste_electeurs.class.php");

$liste_electeurs = new liste_electeurs();

$_SESSION['current_page'] = 'saisie.php';
include('include/header.php');
$liste_electeurs->processTopMenu();
$liste_electeurs->createTopMenu();

$liste_electeurs->createTextArea();

include('include/footer.php');
