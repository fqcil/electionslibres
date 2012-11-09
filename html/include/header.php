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

header("Cache-control: private, no-cache");
header("Expires: Mon, 26 Jun 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
header('Content-Type: text/html;charset=utf-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="-1">
        
        <title>ElectionsLibres - Gestionnaire de pointage électoral</title>
  	<script type="text/javascript" src="calendar/calendar.js"></script>
	<script type="text/javascript" src="scripts/sortabletable.js"></script>
	<script type="text/javascript" src="scripts/sortabletable_addons.js"></script> 
	<script type="text/javascript" src="scripts/functions.js"></script> 
	
	<link href="css/sortabletable.css" rel="stylesheet" type="text/css">
	<link href="css/FQCIL.css" rel="stylesheet" type="text/css">
	<link href="css/default.css" rel="stylesheet" type="text/css"> <?php 
	if(!empty($_REQUEST['change_view_requested_css'])){
	echo "<link id='custom_style' href='{$_REQUEST['change_view_requested_css']}' rel='stylesheet' type='text/css'>";
	}
	?>
	
	    </head>
    <body> 
	<div id='page'>
		<div id="header">
			<div id="logo">
				<img id="logo-image" alt="Accueil" src="images/FQCIL_logo.jpg"/>
			</div>
