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
require_once("lib/widget/DropdownFactory.class.php");
include('include/header.php');

$liste_electeurs = new liste_electeurs();
$factory = new DropdownFactory();

//SET ACTION
$electorId = $_GET['id'];

if(empty($_GET['id']))
{
    dislayErrorMessage("No id or New selected", "edit.php");
}

$electeur = Historique::getHistoriqueFromIdDge($electorId);
if(!empty($_POST))
{
    $electeur->createUpdate($electorId);
    $electeur = Historique::getHistoriqueFromIdDge($electorId);//Hack to avoid refresh problem
}
$row = $electeur->getDernierPointageRow();
?>

<h1>Edition d'un électeur</h1>
<a href="<?php echo $_SESSION['current_page']?>">Retour</a><br><br>

<form name="edit_newsletter" action="edit.php?id=<?php echo $electorId; ?>" method="post">
<table>
    <tr>
		<td valign="top" align="right"><strong>Jour du scrutin:</strong></td>
		<td valign="top" align="left"><?php echo $factory->build_edit_new("", "vote", "VOTE[{$electorId}]", $electeur->vote, $electorId);?><br><br></td>
	</tr>
	<tr>
		<td valign="top" align="right"><strong>Téléphone bottin:</strong></td>
		<td valign="top" align="left"><?=$electeur->telephone1?></td>
	</tr>
	<tr>
		<td valign="top" align="right"><strong>Téléphone manuel:</strong></td>
		<td valign="top" align="left"><input type="text" size="40" maxlength="12" value="<?=$electeur->telephone2?>" name="<?php echo "telephone2[$electorId]";?>"></td>
	</tr>
	<tr>
		<td valign="top" align="right"><strong>Commentaire:</strong></td>
		<td valign="top" align="left"><textArea rows="4" cols="40" name="<?php echo "comment[$electorId]"; ?>"><?=$electeur->comment?></textarea></td>
	</tr>
	<tr>
		<td colspan="2">
        <h3><u>Important:</u> Les champs <i>Type de contact</i> et <i>Contact Réussi ?</i> sont <b>obligatoires</b> pour créer un pointage valide.</h3>
		<p><u>Dernier contact</u> (<? echo $row['DATE'] ?>) : </p>
    <b>Type de contact: </b>
	<? echo $factory->build_edit_new(NULL, "type_contact", "CONTACT[{$electorId}]", $row['CONTACT'], $electorId);?>
    &nbsp;&nbsp;
    <b>Contact Réussi ?: </b>
	<? echo $factory->build_edit_new(NULL, "resultat", "RESULTAT[{$electorId}]", $row['RESULTAT'], $electorId);?>
    &nbsp;&nbsp;
	<? echo $factory->build_edit_new("Intention de vote", "allegeance", "ALLEGEANCE[{$electorId}]", $row['ALLEGEANCE'], $electorId);?>
    &nbsp;&nbsp;
	
	</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><br><input type="submit" name="go" value="Sauvegarder"></td>
	</tr>
</table>
<input type="hidden" value="<?=$electeur->pointage_id?>" name="pointage_id">
<input type="hidden" name="NM_ELECT" value="<?=$electeur->nm_elect?>"/>
<input type="hidden" name="PR_ELECT" value="<?=$electeur->pr_elect?>"/>
<input type="hidden" name="DA_NAISN_ELECT" value="<?=$electeur->da_naisn_elect?>"/>
<input type="hidden" name="NO_CIVQ_ELECT" value="<?=$electeur->no_civq_elect?>"/>
</form>
<?php 
include('include/footer.php');
