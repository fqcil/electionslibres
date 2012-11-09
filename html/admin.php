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
require_once("./lib/user/user.class.php");
require_once("common.php");
require_once("lib/widget/DropdownFactory.class.php");
require_once("lib/widget/html.class.php");
require_once("lib/liste_electeurs.class.php");
require_once("lib/circonscription.class.php");
$factory= new DropdownFactory();
$connection = $_SESSION['user']->getDB();
$liste_electeurs = new liste_electeurs();
$circ = new Circonscription();

$_SESSION['current_page'] = 'admin.php';
require_once("include/header.php");
$liste_electeurs->processTopMenu();
if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
$liste_electeurs->createTopMenu($_SESSION['current_page']);


if($_SESSION['user']->access == 'superadmin'){
    require_once("include/schema_validate.php");
	validate_schema();
    ?>
<h2>Gestion des utilisateurs</h2>

<h3>Liste des utilisateurs</h3>
<form method="POST" id="users_list" action="admin_scripts.php">

<table id="table-1" class="sort-table" border="1" cellpadding="4"
	cellspacing="0">
	<thead>
		<tr>
			<td nowrap align="center"><input type="checkbox" name="select_all"
				onClick="markAllRows('users_list');" /></td>
			<td nowrap align="center"><strong>Username</strong></td>
			<td nowrap align="center"><strong>Password</strong></td>
			<td nowrap align="center"><strong>Permission</strong></td>
			<td nowrap align="center"><strong>Prénom</strong></td>
			<td nowrap align="center"><strong>Nom</strong></td>
			<td nowrap align="center"><strong>Circonscription</strong></td>
		</tr>
	</thead>

	<?php
	$sql = "SELECT * from users ORDER BY access, NO_CIRCN_PROVN";
	$conn = $_SESSION['user']->getDB();
	if (!($result = $conn->query($sql))) dislayErrorMessage($sql."<br><br>".$result->errorInfo(), "index.php");
	while( $users = $result->fetch(PDO::FETCH_ASSOC)){
	    $users_checked = $users_checked.$users['username'].',';
	    ?>
	<tr bgcolor="<? echo $bgColor; ?>">
		<td nowrap align="center"><input type="checkbox"
			name="user_<? echo $users['username']; ?>"
			value="<? echo $users['username']; ?>" /></td>
		<td nowrap align="right"><? echo $users['username']; ?></td>
		<td nowrap align="right"><? echo $users['pw']; ?></td>
		<td nowrap align="right"><? echo $users['access']; ?></td>
		<td nowrap align="right"><? echo $users['first_name']; ?></td>
		<td nowrap align="right"><? echo $users['last_name']; ?></td>
		<td nowrap align="right"><? echo $users['NO_CIRCN_PROVN']; ?></td>
	</tr>


	<?php
	}
	?>
</table>
<br>

	 <img border="0"
	src="images/selection.png">Pour les entrées
sélectionnées:&nbsp;&nbsp;&nbsp;&nbsp; <? 
?> &nbsp; <input type="submit" name="apply" value="Effacer"> <input
	type="hidden" name="displayed" value="<? echo $users_checked ?>"></form>
<? }
echo $circ->processAdminUI();
echo $circ->getAdminUI();
?>
<br>

<? if($_SESSION['user']->access == 'superadmin' ){?>

<h3>Ajouter un super-utilisateur</h3>
<form method="POST" name="batch_selection" action="admin_scripts.php">
<table border="0" bordercolor="#0000FF" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">Nom d'usager:</td>
		<td align="left"><input type="username" name="username"></td>
	</tr>

	<tr>
		<td align="right">Mot de passe:</td>
		<td align="left"><input type="password" name="password"></td>
	</tr>
	<tr>
		<td align="right">Permission:</td>
		<td align="center">superadmin<input type="radio" name="permission"
			value="superadmin" checked="checked" />
		
		
		<td align="left">permanence<input type="radio" name="permission"
			value="permanence" />
	
	</tr>
	<tr>
		<td align="right">Prenom:</td>
		<td align="left"><input type="first_name" name="first_name"></td>
		<td align="left">(Facultatif)</td>
	</tr>
	<td align="right">Nom:</td>
	<td align="left"><input type="last_name" name="last_name"></td>
	<td align="left">(Facultatif)</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="apply"
			value="Creer"></td>
		<input type="hidden" name="displayed"
			value="<? echo "Usagers créés avec succès" ?>">
	</tr>
</table>
</form>
<? }
?>
<? if($_SESSION['user']->access == 'superadmin'){?>
<h2><a href="import_dge.php">Importer une liste d'électeurs du Directeur Général des élections</a></h2>

<h2><a href="import_telephone.php">Importer une liste de numéros de téléphones</a></h2>

<h2><a href="import_sondage.php">Importer un nouveau sondage téléphonique</a></h2>

<h2><a href="export_config_monFQCIL.php">Exporter la correspondance circonscription/région</a></h2>
<? }
?>
<?php
}

require_once("include/footer.php");
