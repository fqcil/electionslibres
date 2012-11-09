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

$connection = $_SESSION['user']->getDB();
$liste_electeurs = new liste_electeurs();

$_SESSION['current_page'] = 'report.php';

list($usec, $sec) = explode(' ', microtime());
$script_start = (float) $sec + (float) $usec;

$dbQuery = "SELECT COUNT(liste_dge.ID_DGE) as count_elect, COUNT(DISTINCT liste_dge.AD_ELECT, liste_dge.NO_CIVQ_ELECT, liste_dge.NO_APPRT_ELECT) as count_residences FROM liste_dge WHERE  liste_dge.NO_CIRCN_PROVN='{$_SESSION['user']->no_circn}';";
$result = $connection->query($dbQuery);
$row = $result->fetch( PDO::FETCH_ASSOC );
//var_dump($row);
$electors = $row['count_elect'];
$residences = $row['count_residences'];
list($usec, $sec) = explode(' ', microtime());
$script_end = (float) $sec + (float) $usec;
$elapsed_time = $script_end - $script_start;
//echo "$dbQuery Elapsed time = ". $elapsed_time."<br/>";

/* ATTENTION:  Mysql n'a pas de DISTINCT on, et GROUP BY id_dge est trop lent.  Donc, in faut mettre ID_DGE dans tous les distinct */

/*$sql = "SELECT COUNT(*) - COUNT(DISTINCT liste_dge.ID_DGE) as elect_avec_plus_de_un_tel_bottin, COUNT(DISTINCT liste_dge.ID_DGE) as `count_elect_real'
FROM liste_dge LEFT JOIN liste_telephones 
ON ( liste_dge.NM_ELECT LIKE liste_telephones.NM_ELECT AND liste_dge.PR_ELECT LIKE concat(liste_telephones.PR_ELECT,'%') AND liste_dge.CO_POSTL = liste_telephones.CO_POSTL AND liste_dge.NO_CIVQ_ELECT = liste_telephones.NO_CIVQ_ELECT ) 
WHERE liste_dge.NO_CIRCN_PROVN='{$_SESSION['user']->no_circn}';";
$result25 = $connection->query($sql);
$row = $result25->fetch(PDO::FETCH_ASSOC);
var_dump($row);*/

//$result25=liste_electeurs::getElectorsPDOStatement("AND no_telephone IS NOT NULL", "COUNT(*) as `count`");
$sql = "SELECT COUNT(*) - COUNT(DISTINCT liste_dge.ID_DGE) as elect_avec_plus_de_un_tel_bottin, COUNT(DISTINCT liste_dge.ID_DGE) as `count_elect_tel`, COUNT(no_telephone) as `count_tel`,  COUNT(DISTINCT liste_dge.AD_ELECT, liste_dge.NO_CIVQ_ELECT, liste_dge.NO_APPRT_ELECT) as count_residences_avec_tel
FROM liste_dge JOIN liste_telephones 
ON ( liste_dge.NM_ELECT LIKE liste_telephones.NM_ELECT AND liste_dge.PR_ELECT LIKE concat(liste_telephones.PR_ELECT,'%') AND liste_dge.CO_POSTL = liste_telephones.CO_POSTL AND liste_dge.NO_CIVQ_ELECT = liste_telephones.NO_CIVQ_ELECT ) 
WHERE liste_dge.NO_CIRCN_PROVN='{$_SESSION['user']->no_circn}';";
$result25 = $connection->query($sql);
$row = $result25->fetch(PDO::FETCH_ASSOC);
//var_dump($row);
$electorsWithTelephone = $row['count_elect_tel'];
$electorsWithFuzzyTelephone = $row['elect_avec_plus_de_un_tel_bottin'];
$residencesWithTelephone = $row['count_residences_avec_tel'];
list($usec, $sec) = explode(' ', microtime());
$script_end = (float) $sec + (float) $usec;
$elapsed_time = $script_end - $script_start;
//echo "$sql Elapsed time = ". $elapsed_time."<br/>";

// if IS_DERNIER_POINTAGE=TRUE, then pointage.id is not null
$tmp=liste_electeurs::getElectorsPDOStatement("AND IS_DERNIER_POINTAGE=TRUE", "historique_pointage.ALLEGEANCE, COUNT(*) as `count`", "GROUP BY historique_pointage.ALLEGEANCE","`count` DESC");
$allegeance_results = $tmp->fetchAll(PDO::FETCH_ASSOC);
list($usec, $sec) = explode(' ', microtime());
$script_end = (float) $sec + (float) $usec;
$elapsed_time = $script_end - $script_start;
//echo "Elapsed time = ". $elapsed_time."<br/>";

// Calcul of the total polled
foreach( $allegeance_results as $key=>$value )
{    
    $totalPolled = $totalPolled + $value['count'];
}

$liste_electeurs->processTopMenu();
$liste_electeurs->createTopMenu();
?>

<h2>Portrait de la circonscription</h2>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td align="right" nowrap>Nombre d'électeurs:</td>
		<td align="right" nowrap><strong><? echo $electors; ?></strong></td>
	</tr>
	<tr>
		<td align="right" nowrap>Nombre de domiciles:</td>
		<td align="right" nowrap><strong><? echo $residences; ?></strong></td>
	</tr>
	<tr>
		<td align="right" nowrap>Nombre moyen d'électeurs par domicile:</td>
		<td align="right" nowrap><strong><? echo number_format($electors/$residences, 2); ?></strong></td>
	</tr>
</table>
<br />
<table border="0" cellpadding="4" cellspacing="0">
	<tr>
		<td align="right" nowrap>Électeurs avec numéros de téléphone:</td>
		<td align="right" nowrap><strong><? echo $electorsWithTelephone; ?></strong></td>
		<td><strong><? echo number_format($electorsWithTelephone/$electors*100,0).'%'; ?></strong> (<? echo $electorsWithFuzzyTelephone; ?> électeurs avec no possiblement incorrect)</td>
	</tr>
	<tr>
		<td align="right" nowrap>Domiciles avec numéros de téléphone:</td>
		<td align="right" nowrap><strong><? echo $residencesWithTelephone; ?></strong></td>
		<td><strong><? echo number_format($residencesWithTelephone/$residences*100,0).'%'; ?></strong></td>
	</tr>
</table>
<h2>Résultats du pointage</h2>
<table border="0" cellpadding="4" cellspacing="0">
<?php

foreach( $allegeance_results as $key=>$value )
{
    $allg_label = historique::getLabelFromValeur($value['ALLEGEANCE'], 'allegeance',$connection);
    if($allg_label == NULL)
        $allg_label="Aucun";
    ?>
	<tr>
		<td align="right" nowrap><? echo $allg_label; ?>:</td>
		<td align="right" nowrap><? echo $value['count']; ?></td>
		<td align="right" nowrap><? echo round($value['count']/$totalPolled*100,2)?>
		%</td>
	</tr>
	<?php
}
?>
	<tr>
		<td align="right" nowrap>Nombre d'électeurs pointés:</td>
		<td align="right" nowrap><? echo $totalPolled; ?></td>
		<td align="right" nowrap><? echo round($totalPolled/$electors*100,2); ?>
		%</td>
	</tr>
</table>

<br />
<a href="report_bv.php?group_by=sv">Résultats par section de vote</a>
<br />
<a href="report_bv.php?group_by=bv">Résultats par secteur électoral (bureau de vote)</a>
<br/ >
<?php
include('include/footer.php');
