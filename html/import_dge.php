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

require_once('common.php');
require_once('config/config.php');
error_reporting(E_ALL);
require_once('lib/dge.class.php');
include('include/header.php');


if(!empty($_POST) && !empty($_FILES))
{
    $dge = new dge();
    $dge->password = $_POST['password'];
    $dge->upload();
}
?>

<form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>"
	name="test" method="POST">
<table>
	<tr>
		<td>Format de fichier à importer</td>
		<td><input type="radio" name="format"
			value="zip_of_zip_all_circonscriptions" checked="checked"/>Archive zip des fichiers
		zip de TOUTES les circonscriptions (la base de données actuelle sera
		détruite)<br>
		<input type="radio" name="format"
			value="mysql_csv_all_circonscriptions" />Fichier CSV MySQL de TOUTES
		les circonscriptions (la base de données actuelle sera détruite)<br>
		<input type="radio" name="format" value="zip_one_circonscription" />Archive
		zip d'une seule circonscription (les autres circonscriptions seront
		conservées)</td>
	</tr>
	<tr>
		<td>Effacer fichier CSV MySql temporaire?</td>
		<td><input type="radio" name="delete_mysql_csv" value="yes" checked="checked"/>Oui<br>
		<input type="radio" name="delete_mysql_csv" value="no" />Non<br>
		</td>
	</tr>
	<tr>
		<td>Mot de passe pour le fichier ZIP</td>
		<td><input type="text" name="password" value="" /></td>
	</tr>
	<tr>
		<td>Fichier &agrave; importer</td>
		<td>Local: <input name="userfile" type="file" /> ou<br>
		Chemin sur le serveur: <input type="text" name="serverfile" value="" />
		</td>
	</tr>
	<td colspan="2" align="center"><input type="submit" value="Send File" />
	<input type="hidden" name="MAX_FILE_SIZE" value="104857600" /></td>
	</tr>
</table>
</form>
<?php
include('include/footer.php');
