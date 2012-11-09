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
include('lib/liste_electeurs.class.php');
include('include/header.php');

$connection =  $_SESSION['user']->getDB();
$liste_electeurs = new liste_electeurs();

$_SESSION['current_page'] = 'report_bv.php';

$connection =  $_SESSION['user']->getDB();

if($_REQUEST['group_by']=='bv' || empty($_REQUEST['group_by'])) {
    $groupingCaption = "Secteur électoral";
    $grouping ="NO_SECTR_ELECT";
}
else if($_REQUEST['group_by']=='sv'){
    $groupingCaption = "Section de vote";
    $grouping ="NO_SECTN_VOTE";
}
else {
    throw new Exception("Paramètre invalide");
}

$liste_electeurs->processTopMenu();
$liste_electeurs->createTopMenu();
//$result_bureaux=liste_electeurs::getElectorsPDOStatement("AND hi.IS_DERNIER_POINTAGE=TRUE", "liste_dge.$grouping, count(*) as `count`, count(historique_pointage.IS_DERNIER_POINTAGE) as count_pointes", "GROUP BY liste_dge.$grouping", "liste_dge.$grouping");

$sql = "SELECT liste_dge.$grouping, count(*) as `count`, count(historique_pointage.IS_DERNIER_POINTAGE) as count_pointes
            FROM liste_dge 
            LEFT JOIN pointage ON (
                    liste_dge.ID_DGE = pointage.P_ID 
                    )
            LEFT JOIN historique_pointage ON (
                    pointage.P_ID = historique_pointage.H_ID
                    AND historique_pointage.IS_DERNIER_POINTAGE=TRUE
            )
            WHERE liste_dge.NO_CIRCN_PROVN='{$_SESSION['user']->no_circn}' GROUP BY liste_dge.$grouping ORDER BY liste_dge.$grouping";
//echo $sql;
$result_bureaux=$connection->query($sql);
?>

<h2>Résultats par <?php echo $groupingCaption?></h2>
<table border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td align="center"><strong><?php echo $groupingCaption?></strong></td>
		<td align="center"><strong>Résultats</strong></td>
	</tr>
	<?php while($bureau = $result_bureaux->fetch( PDO::FETCH_ASSOC )) {?>
	<tr>
		<td align="center"><strong><? echo $bureau[$grouping] ?></strong></td>
		<td>
		<table border="0" cellpadding="4" cellspacing="0">
		<?php
		$result=liste_electeurs::getElectorsPDOStatement("AND historique_pointage.IS_DERNIER_POINTAGE=TRUE AND liste_dge.$grouping={$bureau[$grouping]}", "historique_pointage.ALLEGEANCE, count(*) AS `count`", "GROUP BY historique_pointage.ALLEGEANCE", "`count` DESC");

		while ($row = $result->fetch( PDO::FETCH_ASSOC ))
		{
		    $allg_label = historique::getLabelFromValeur($row['ALLEGEANCE'], 'allegeance',$connection);
		    if($allg_label == NULL)
		    $allg_label="Aucun";
		    ?>
			<tr>
				<td align="right" nowrap><? echo $allg_label; ?>:</td>
				<td align="right" nowrap><? echo $row['count']; ?></td>
				<td align="right" nowrap><? echo round($row['count']/$bureau['count_pointes']*100,2)?>
				%</td>
			</tr>
			<?php
			@ ob_flush();
			flush();
		}
		?>
			<tr>
				<td align="right" nowrap>Nombre d'électeurs pointés:</td>
				<td align="left" nowrap colspan="2"><strong><? echo $bureau['count_pointes']." / ".$bureau['count'] ; ?></strong></td>
			</tr>
		</table>
		</td>
	</tr>
	<?php }
	?>
</table>

	<?php
	include('include/footer.php');
