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

require_once('widget/html.class.php');
require_once('historique.class.php');
require_once('circonscription.class.php');

class liste_electeurs
{
    public $db              =NULL;
    private $rawQueryStm;
    function __construct()
    {
        $this->db = DB::getDatabase('pointage');
    }


    static function getElectorsPDOStatement($additionalWhere=null,
    $selectListOverride=null,
    $groupByHavingSql=null,
    $orderByOverride=null,
    $circWhere=null,
    $joinTelephones=false) {
        $debug=false;
        if($additionalWhere) {
            $additionalWhereSql = $additionalWhere;
        }
        else {
            $additionalWhereSql = '';
        }
        if($selectListOverride) {
            $selectListSql = $selectListOverride;
        }
        else {
            $selectListSql = 'historique_pointage.CONTACT, historique_pointage.RESULTAT, historique_pointage.ALLEGEANCE, historique_pointage.DATE, pointage.*,liste_telephones.no_telephone, liste_dge.*, (YEAR(CURRENT_DATE()) - YEAR(`DA_NAISN_ELECT`)) as AGE';
        }

        if($orderByOverride) {
            $orderBySql = "ORDER BY $orderByOverride";
        }
        else {
            $orderBySql = "ORDER BY liste_dge.NO_SECTN_VOTE, liste_dge.NO_ELECT, historique_pointage.DATE desc";
        }

        if($circWhere) {
            $circWhereSql = $circWhere;
        }
        else{
            $circWhereSql = "liste_dge.NO_CIRCN_PROVN= '{$_SESSION['user']->no_circn}'";
        }
        if($joinTelephones){
            $joinTelephonesSql = "LEFT JOIN liste_telephones ON (
                    liste_dge.NM_ELECT LIKE liste_telephones.NM_ELECT 
                    AND liste_dge.PR_ELECT LIKE concat(liste_telephones.PR_ELECT,'%')
                    AND liste_dge.CO_POSTL = liste_telephones.CO_POSTL
                    AND liste_dge.NO_CIVQ_ELECT = liste_telephones.NO_CIVQ_ELECT
                    )";
        }else {
            $joinTelephonesSql = null;
        }
        $dbQuery = "SELECT $selectListSql
            FROM liste_dge 
            $joinTelephonesSql
            LEFT JOIN pointage ON (
                    liste_dge.ID_DGE = pointage.P_ID 
                    )
            LEFT JOIN historique_pointage ON (
                    liste_dge.ID_DGE = historique_pointage.H_ID
            )
            WHERE $circWhereSql $additionalWhereSql $groupByHavingSql $orderBySql "; 


            //Uset to be a condition like the following:  AND (liste_dge.NO_CIVQ_ELECT = pointage.DERNIER_POINTAGE_NO_CIVQ_ELECT OR liste_telephones.no_telephone = pointage.DERNIER_POINTAGE_TELEPHONE)
            //It was way too slow
            $connection = $_SESSION['user']->getDB();

            //pretty_print_r($dbQuery);
            if($debug) {
                list($usec, $sec) = explode(' ', microtime());
                $script_start = (float) $sec + (float) $usec;
            }

            try {
                $retval = $connection->query($dbQuery);
            }
            catch (Exception $e) {
                pretty_print_r($dbQuery);
            }

            if($debug){
                list($usec, $sec) = explode(' ', microtime());
                $script_end = (float) $sec + (float) $usec;
                $elapsed_time = $script_end - $script_start;
                pretty_print_r($dbQuery);
                echo "Elapsed time = ". $elapsed_time."<br/>";
            }
            return $retval;
    }


    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function executeListQuery()
    {

        //SEARCH
        if( isset($_POST['lastname']) && $_POST['lastname'] != ''){
            $dbQueryAppend = $dbQueryAppend." AND liste_dge.NM_ELECT LIKE '%".$_POST['lastname']."%'";
        }

        if( isset($_POST['firstname']) && $_POST['firstname'] != '' ){
            $dbQueryAppend = $dbQueryAppend." AND liste_dge.PR_ELECT LIKE '%".$_POST['firstname']."%'";
        }
        if( isset($_POST['city']) && $_POST['city'] != '' ){
            $dbQueryAppend = $dbQueryAppend." AND liste_dge.NM_MUNCP LIKE '%".$_POST['city']."%'";
        }

        //FILTER-------------------

        $dernier_pointage_flag = FALSE;

        if(!isset($_SESSION['NO_SECTN_VOTE'])){
            $_SESSION['NO_SECTN_VOTE'] = '1';
        }
        if(!empty($_SESSION['NO_SECTN_VOTE'])&&!isset($_POST['search_submit'])){
            $dbQueryAppend = $dbQueryAppend." AND `NO_SECTN_VOTE`='".$_SESSION['NO_SECTN_VOTE']."'";
        }
        // RESULTAT
        if(!empty($_SESSION['resultat'])){
            $valeur = $_SESSION['resultat'];
            $dbQueryAppend = $dbQueryAppend." AND RESULTAT='{$valeur}'";
            $dbQueryAppend = $dbQueryAppend." AND IS_DERNIER_POINTAGE=TRUE";
            $dernier_pointage_flag = TRUE;
        }
        // ALLEGEANCE
        if(!empty($_SESSION['allegeance'])){
            $valeur = $_SESSION['allegeance'];
            $dbQueryAppend = $dbQueryAppend." AND `ALLEGEANCE`='{$valeur}'";
            if($dernier_pointage_flag==FALSE)
            {
                $dbQueryAppend = $dbQueryAppend." AND IS_DERNIER_POINTAGE=TRUE";
                $dernier_pointage_flag = TRUE;
            }

        }
        //CONTACT
        if(!empty($_SESSION['type_contact'])){
            $valeur = $_SESSION['type_contact'];
            $dbQueryAppend = $dbQueryAppend." AND `CONTACT`='{$valeur}'";
            if($dernier_pointage_flag==FALSE)
            {
                $dbQueryAppend = $dbQueryAppend." AND IS_DERNIER_POINTAGE=TRUE";
                $dernier_pointage_flag = TRUE;
            }
        }
        //VOTE
        if(!empty($_SESSION['vote'])){
            $valeur = $_SESSION['vote'];
            $dbQueryAppend = $dbQueryAppend." AND pointage.VOTE='{$valeur}'";
        }


        $this->rawQueryStm = self::getElectorsPDOStatement($dbQueryAppend,null, null, null, null, true);
    }



    /** Affiche une liste d'électeurs selon ... A déterminer 
     *
     * @author benoitg
     **/
    function displayListUI() {
        ?>
<form method="POST" id="batch_selection"
	action="<?=$_SERVER['REQUEST_URI']?>"><?php
	echo "<input type=hidden id='formInvalidItemsArray'/>";
	$no_pointage = NULL;
	$factory= new DropdownFactory();
	$electorRows = null;
	$nextRow = $this->rawQueryStm->fetch( PDO::FETCH_ASSOC);

	while ($nextRow)
	{
	    //var_dump($nextRow);
	    $currentRow = $nextRow;
	    $electorRows = array();
	    while($nextRow['ID_DGE']==$currentRow['ID_DGE']) {
	        //echo " Fetch a row for {$nextRow['ID_DGE']}<br>";
	        $electorRows[] = $nextRow;
	        $currentRow = $nextRow;
	        $nextRow = $this->rawQueryStm->fetch(PDO::FETCH_ASSOC);
	    }
	    $elector = $electorRows[0];

	    $last_pointage = null;
	    $historique = Historique::getHistoriqueFromAllRows($elector['ID_DGE'], $electorRows);
	    flush();
	    if(!is_null($no_pointage) && $elector['NO_SECTN_VOTE'] != $no_pointage)
	    {
	        echo '</table><hr class="breakhere">';
	    }

	    if($no_pointage == NULL || $elector['NO_SECTN_VOTE'] != $no_pointage)
	    {
	        echo "<h2>Liste des électeurs : Section de vote {$elector['NO_SECTN_VOTE']}</h2>\n";
	        echo "<h2 id='saveWarning' style='background-color: red;display: none;'>Attention, vous n'avez pas encore sauvegardé vos résultats de pointage!</h2>\n";
	        if($_SESSION['display']!="add_history" && $_SESSION['display']!="add"){
	            echo "<u>Note:</u> Pour trier, cliquez sur le titre d'une colonne.";
	            echo "<br><br>";
	        }
	        else {
	            echo "<p><u>Important:</u> Les champs <i>Type de contact</i> et <i>Contact Réussi ?</i> sont <b>obligatoires</b> pour créer un pointage valide.</p>";
	            ?>
<p><u>Légende:</u> <span
	style="padding: 2px; margin: 5px; BACKGROUND-COLOR: red">Champs
obligatoire manquant</span> <span
	style="padding: 2px; margin: 5px; BACKGROUND-COLOR: yellow">Édition
d'un pointage en cours</span> <span
	style="padding: 2px; margin: 5px; BACKGROUND-COLOR: green">Pointage
valide</span></p>
&nbsp;&nbsp;<img border="0" src="images/selection_down.png"> Pour les
entrées sélectionnées:&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; <input
	type="submit" name="apply" value="Appliquer"> <input type="hidden"
	name="displayed" value="<?php echo $displayedElectors; ?>"> <br>
<br>
	            <?php } ?>

<table id="table-1" class="sort-table" border="1" cellpadding="4"
	cellspacing="0">
	<thead>
		<tr>
			<td><input type="checkbox" name="select_all"
				onClick="markAllRows('batch_selection');" /></td>
			<td>No.</td>
			<?php if($_SESSION['show_age'] == 1){?>
			<td class="age">Age</td>
			<?php } ?>
			<?php if($_SESSION['show_co_sexe'] == 1){?>
			<td class="sex">Sexe</td>
			<?php } ?>
			<td class="prenom">Prénom</td>
			<td class="nom">Nom</td>
			<?php if($_SESSION['show_address'] == 1){?>
			<td class="adresse">Adresse</td>
			<td class="rue">Rue</td>
			<?php } ?>
			<?php if($_SESSION['show_city'] == 1){?>
			<td class="municipalite">Municipalité</td>
			<?php } ?>
			<?php if($_SESSION['show_telephone1'] == 1){?>
			<td class="telephone">Téléphone</td>
			<?php } ?>
			<?php if($_SESSION['show_telephone_m'] == 1){?>
			<td class="telephone_m">Téléphone Manuel</td>
			<?php } ?>

			<?php if($_SESSION['show_origin'] == 1){?>
			<td>Domicile</td>
			<?php } ?>
			<?php if($_SESSION['show_category'] == 1){?>
			<td class="membership">Membership</td>
			<?php } ?>
			<?php if($_SESSION['show_date'] == 1){?>
			<td class="date">Date pointage</td>
			<?php } ?>
			<?php if($_SESSION['show_contact'] == 1){?>
			<td class="contact">Type de contact</td>
			<?php } ?>
			<?php if($_SESSION['show_rslt'] == 1){?>
			<td class="resultat">Contact réussi ?</td>
			<?php } ?>
			<?php if($_SESSION['show_allg'] == 1){?>
			<td class="allegeance">Intention de vote</td>
			<?php } ?>
			<?php if($_SESSION['show_vote'] == 1){?>
			<td class="vote">Jour du scrutin</td>
			<?php } ?>
			<?php if($_SESSION['show_note'] == 1){?>
			<td class="commentaire">Commentaire</td>
			<?php } ?>
		</tr>
	</thead>

	<?php }
	$no_pointage = $elector['NO_SECTN_VOTE'];

	$individuals++;
	if(!empty($historique)){
	    $last_pointage = $historique->getDernierPointageRow();
	    //var_dump($historique);
	    //var_dump($last_pointage);
	    if(!empty($last_pointage['CONTACT'])){
	        $pointed++;
	    }
	    if($last_pointage['ALLEGEANCE'] == 'FQCIL'){
	        $FQCIL++;
	        if($elector['VOTE'] == 'bva'){
	            $FQCILBVA++;
	        }
	        else if($elector['VOTE'] == 'voted'){
	            $FQCILBVO++;
	        }
	    }
	}

	$address = $elector['NO_CIVQ_ELECT'].$elector['NO_APPRT_ELECT'];
	//Changement de couleur lorsque l'adresse change
	if($previousAddress != $address) {
	    $motif = !$motif;
	    $residences++;
	    if($last_pointage['RESULTAT'] == 'success'){
	        $coveredResidences++;
	    }
	}

	if ($motif){
	    $bgColor = $GLOBALS['first_color'];
	}
	else{
	    $bgColor = $GLOBALS['second_color'];
	}

	$previousAddress = $address;

	$displayedElectors = $displayedElectors.$elector['ID_DGE'].',';


	$historique->displayEditUI($bgColor);
	}
	echo "</table>\n";
	if($_SESSION['display']!="add_history" && $_SESSION['display']!="add"){
	    ?>

	<script type="text/javascript">
            <!--
            var st1 = new SortableTable(document.getElementById("table-1"),
                    [	
                    "None", 
                    "Number", 	
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString",
                    "CaseInsensitiveString", 
                    "CaseInsensitiveString", 
                    "CaseInsensitiveString", 
                    "CaseInsensitiveString", 		
                    "None"
                    ]);
        st1.sort(1);
        -->
            </script>
            <?php
	}
	?>
	<br>
	<img border="0" src="images/selection.png">
	Pour les entrées sélectionnées:&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;
	<input type="submit" name="apply" value="Appliquer"
		onClick='return submitCheck();'>
	<input type="hidden" name="displayed"
		value="<?php echo $displayedElectors; ?>">

	</form>
	<br>
	Individus pointés:
	<b><?php echo $pointed." / ".$individuals; ?></b>
	<br>
	Domiciles couverts:
	<b><?php echo $coveredResidences." / ".$residences; ?></b>
	<br>
	<?php
    }

    function processListUI()
    {
        if(!empty($_POST['apply']) && !empty($_POST['elector_id'])) {
            foreach($_POST['elector_id'] AS $key => $elector_id)
            {
                $electeur = Historique::getHistoriqueFromIdDge($elector_id);
                $electeur->createUpdate($key);
            }
        }
    }

    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function getDisplayColumnConfigUI() {
        ?>
	<fieldset class="hide"><legend>Choisir les colonnes à afficher:</legend>

	<form action="<?=$_SERVER['REQUEST_URI']?>" name="change_view"
		method="post" style="display: inline; margin: 0px; padding: 0px"><?php
		$data = array();
		$data[] = array('label'=>'Visualisation','value'=>'view');
		$data[] = array('label'=>'Pointage simple','value'=>'add');
		$data[] = array('label'=>'Pointage avec historique','value'=>'add_history');
		$data[] = array('label'=>'Impression: Liste des t&eacute;l&eacute;phones','value'=>'liste_telephone');
		$data[] = array('label'=>'Impression: Liste des domiciles','value'=>'liste_domicile');
		?> <input type="hidden"
		value='<?php if(!empty($_SESSION['change_view_requested_css'])) echo $_SESSION['requested_css']; ?>'
		name="change_view_requested_css" id="change_view_requested_css"> <input
		type="hidden" value='<?php if(!empty($_SESSION['show_age'])) echo '1'; ?>'
		name="show_age" id="change_view_show_age"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_co_sexe'])) echo '1'; ?>'
		name="show_co_sexe" id="change_view_show_co_sexe"> <input
		type="hidden"
		value='<?php if(!empty($_SESSION['show_address'])) echo '1'; ?>'
		name="show_address" id="change_view_show_address"> <input
		type="hidden" value='<?php if(!empty($_SESSION['show_city'])) echo '1'; ?>'
		name="show_city" id="change_view_show_city"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_telephone1'])) echo '1'; ?>'
		name="show_telephone1" id="change_view_show_telephone1"> <input
		type="hidden"
		value='<?php if(!empty($_SESSION['show_telephone_m'])) echo '1'; ?>'
		name="show_telephone_m" id="change_view_show_telephone_m"> <input
		type="hidden"
		value='<?php if(!empty($_SESSION['show_category'])) echo '1'; ?>'
		name="show_category" id="change_view_show_category"> <input
		type="hidden" value='<?php if(!empty($_SESSION['show_rslt'])) echo '1'; ?>'
		name="show_rslt" id="change_view_show_rslt"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_contact'])) echo '1'; ?>'
		name="show_contact" id="change_view_show_contact"> <input
		type="hidden" value='<?php if(!empty($_SESSION['show_allg'])) echo '1'; ?>'
		name="show_allg" id="change_view_show_allg"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_date'])) echo '1'; ?>'
		name="show_date" id="change_view_show_date"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_vote'])) echo '1'; ?>'
		name="show_vote" id="change_view_show_vote"> <input type="hidden"
		value='<?php if(!empty($_SESSION['show_note'])) echo '1'; ?>'
		name="show_note" id="change_view_show_note"> <?php
		echo html::genDropdown('display',$data,!empty($_SESSION['display'])?$_SESSION['display']:NULL,'onchange=changeStyle(this)');
		?></form>

	<form method="POST" name="frm_column_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>"><input type="checkbox"
		value="1" name="show_age"
		<?php if(!empty($_SESSION['show_age'])) echo 'checked'; ?>> Age&nbsp;&nbsp;
	<input type="checkbox" value="1" name="show_co_sexe"
	<?php if(!empty($_SESSION['show_co_sexe'])) echo 'checked'; ?>>
	Sexe&nbsp;&nbsp; <input type="checkbox" value="1" name="show_address"
	<?php if(!empty($_SESSION['show_address'])) echo 'checked'; ?>>
	Adresse&nbsp;&nbsp; <input type="checkbox" value="1" name="show_city"
	<?php if(!empty($_SESSION['show_city'])) echo 'checked'; ?>>
	Municipalité&nbsp;&nbsp; <input type="checkbox" value="1"
		name="show_telephone1"
		<?php if(!empty($_SESSION['show_telephone1'])) echo 'checked'; ?>> Téléphone
	bottin&nbsp;&nbsp; <input type="checkbox" value="1"
		name="show_telephone_m"
		<?php if(!empty($_SESSION['show_telephone_m'])) echo 'checked'; ?>>
	Téléphone manuel&nbsp;&nbsp; <input type="checkbox" value="1"
		name="show_category"
		<?php if(!empty($_SESSION['show_category'])) echo 'checked'; ?>>
	Membership&nbsp;&nbsp; <input type="checkbox" value="1"
		id="show_contact" name="show_contact"
		<?php if(!empty($_SESSION['show_contact'])) echo 'checked'; ?>> Type de
	contact&nbsp;&nbsp; <input type="checkbox" value="1" id="show_rslt"
		name="show_rslt" <?php if(!empty($_SESSION['show_rslt'])) echo 'checked'; ?>>
	Contact réussi ?&nbsp;&nbsp; <input type="checkbox" value="1"
		id="show_vote" name="show_allg"
		<?php if(!empty($_SESSION['show_allg'])) echo 'checked'; ?>> Intention de
	vote&nbsp;&nbsp; <input type="checkbox" value="1" id="show_date"
		name="show_vote" <?php if(!empty($_SESSION['show_vote'])) echo 'checked'; ?>>
	Jour du scrutin&nbsp;&nbsp; <input type="checkbox" value="1"
		id="show_allg" name="show_date"
		<?php if(!empty($_SESSION['show_date'])) echo 'checked'; ?>> Date
	pointage&nbsp;&nbsp; <input type="checkbox" value="1" name="show_note"
	<?php if(!empty($_SESSION['show_note'])) echo 'checked'; ?>>
	Commentaire&nbsp;&nbsp; <input type="submit" name='random'
		value="Rafraichir liste"></form>
	</fieldset>
	<?php
    }
    /** Traite l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function processDisplayColumnConfigUI() {
        //DISPLAY
        /*
        if(!empty($_POST['display']))
        {
        unset($_SESSION['display']);
        }*/

        if(isset($_POST['random']))
        {
            unset($_SESSION['show_age'],$_SESSION['show_co_sexe'],$_SESSION['show_address'],$_SESSION['show_city'],$_SESSION['show_telephone1'],$_SESSION['show_telephone_m'],$_SESSION['show_origin'],$_SESSION['show_category'],$_SESSION['show_rslt'],$_SESSION['show_contact'],$_SESSION['show_allg'],$_SESSION['show_vote'],$_SESSION['show_date'],$_SESSION['show_note']);
        }
        //Age
        if( isset($_POST['show_age']) ){
            $_SESSION['show_age'] = $_POST['show_age'];
        }

        //Sexe
        if( isset($_POST['show_co_sexe']) ){
            $_SESSION['show_co_sexe'] = $_POST['show_co_sexe'];
        }

        //Adresse
        if( isset($_POST['show_address']) ){
            $_SESSION['show_address'] = $_POST['show_address'];
        }

        //City
        if( isset($_POST['show_city']) ){
            $_SESSION['show_city'] = $_POST['show_city'];
        }

        //Téléphone 1
        if( isset($_POST['show_telephone1']) ){
            $_SESSION['show_telephone1'] = $_POST['show_telephone1'];
        }

        //Téléphone Manuel 
        if( isset($_POST['show_telephone_m']) ){
            $_SESSION['show_telephone_m'] = $_POST['show_telephone_m'];
        }

        //Catégorie
        if( isset($_POST['show_category']) ){
            $_SESSION['show_category'] = $_POST['show_category'];
        }

        //Résultat
        if( isset($_POST['show_rslt']) ){
            $_SESSION['show_rslt'] = $_POST['show_rslt'];
        }

        //Contact
        if( isset($_POST['show_contact']) ){
            $_SESSION['show_contact'] = $_POST['show_contact'];
        }

        //Allégeance
        if( isset($_POST['show_allg']) ){
            $_SESSION['show_allg'] = $_POST['show_allg'];
        }
        // This code snippet prevents the checkbox to be unchecked
        /*else if( !isset($_SESSION['show_allg']) ){
        $_SESSION['show_allg'] = 1;
        }*/

        //Vote
        if( isset($_POST['show_vote']) ){
            $_SESSION['show_vote'] = $_POST['show_vote'];
        }

        //Date
        if( isset($_POST['show_date']) ){
            $_SESSION['show_date'] = $_POST['show_date'];
        }
        /*else if( !isset($_SESSION['show_date']) ){
         $_SESSION['show_date'] = 1;
         }*/

        //Note
        if( isset($_POST['show_note']) ){
            $_SESSION['show_note'] = $_POST['show_note'];
        }
    }

    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function displayFilterConfigUI() {
        $factory= new DropdownFactory();

        ?>
	<p>
	
	
	<form method="POST" name="frm_filter_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<fieldset class="hide"><legend>Filtrer les résultats:</legend> <?php

	echo "Section de vote: ";
	$sql = "SELECT DISTINCT `NO_SECTN_VOTE` FROM liste_dge WHERE NO_CIRCN_PROVN={$_SESSION['user']->no_circn} ORDER BY `NO_SECTN_VOTE`";
	$connection = $_SESSION['user']->getDB();
	$result = $connection->query($sql);

	echo "<select name='NO_SECTN_VOTE' onchange='this.form.submit();'>\n";
	($_SESSION['NO_SECTN_VOTE']=='')?$selected='SELECTED':$selected='';
	echo "<option value='' $selected>Toutes</option>\n";
	while ($section = $result->fetch( PDO::FETCH_ASSOC))
	{
	    ?>
	<option value="<?php echo $section['NO_SECTN_VOTE']; ?>"
	<?php if($_SESSION['NO_SECTN_VOTE'] == $section['NO_SECTN_VOTE']) echo 'selected'; ?>><?php echo $section['NO_SECTN_VOTE']; ?></option>
	<?php
	}
	?> </select> &nbsp;&nbsp; <?php
	//$factory->build_filter_new( "Membership", "CATEGORY");
	?> &nbsp;&nbsp; <?php
	$factory->build_filter_new("Type de contact", "type_contact");
	?> &nbsp;&nbsp; <?php
	$factory->build_filter_new("Contact réussi ?", "resultat");
	?> &nbsp;&nbsp; <?php
	$factory->build_filter_new("Intention de vote", "allegeance");
	?> &nbsp;&nbsp; <?php
	$factory->build_filter_new("Jour du scrutin", "vote");
	?>
	
	</form>
	</fieldset>
	<?php
    }
    /** Traite l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function processFilterConfigUI() {

        //NO_SECTN_VOTE
        if(isset($_POST['NO_SECTN_VOTE'])){
            $_SESSION['NO_SECTN_VOTE'] = $_POST['NO_SECTN_VOTE'];
        }

        //CATEGORY
        if(isset($_POST['CATEGORY']) ){
            $_SESSION['CATEGORY'] = $_POST['CATEGORY'];
        }
        //RESULTAT
        if(isset($_POST['resultat'])){
            $_SESSION['resultat'] = $_POST['resultat'];
        }
        //CONTACT
        if(isset($_POST['type_contact'])){
            $_SESSION['type_contact'] = $_POST['type_contact'];
        }
        //ALLEGEANCE
        if(isset($_POST['allegeance'])){
            $_SESSION['allegeance'] = $_POST['allegeance'];
        }
        //VOTE
        if(isset($_POST['vote'])){
            $_SESSION['vote'] = $_POST['vote'];
        }

    }
    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function displaySearchUI() {
        ?>
	<fieldset class="hide"><legend>Chercher un électeur par mot clef, dans
	toutes les sections de vote:</legend>
	<form method="POST" name="frm_date_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">Nom:&nbsp;<input
		name="lastname" value="" type="text" maxlength="255" size="30">&nbsp;&nbsp;&nbsp;
	Prénom:&nbsp;<input name="firstname" type="text" value=""
		maxlength="255" size="30">&nbsp;&nbsp;&nbsp; Municipalité:&nbsp;<input
		name="city" type="text" value="" maxlength="255" size="30"> <input
		type="submit" name="search_submit" value="Chercher"></form>
	</fieldset>
	<?php

    }
    /** Traite l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function processSearchUI() {
        //SEARCH
        if( isset($_POST['lastname'])){
            $_SESSION['lastname'] = $_POST['lastname'];
        }

        if( isset($_POST['firstname'])){
            $_SESSION['firstname'] = $_POST['firstname'];
        }
        if( isset($_POST['city'])){

            $_SESSION['city'] = $_POST['city'];
        }
    }

    /** Construit le menu de navigation de haut de page
     *
     * @return unknown_type
     */
    function createTopMenu() {

        $circn = new Circonscription();

        switch($_SESSION['current_page']) {
            case 'admin.php':
                $title = 'Interface administrateur';
                break;
            default:
                $title = "ElectionsLibres ";
                $title .= $circn->genDropdown();
                break;
        }
        ?>
	<h1 id="header-title"><?php echo $title; ?></h1>
	</div> <!-- End of header tag, refer include/header.php -->
	<div id="main">
	<!--div id="menu"-->
	<div id="navbar">
	<div id="primary">
	<!--ul id="nav"-->
	<ul class="links">
	<?php
	$_SESSION['current_page'] == 'contacts.php'?$class='class="selected"':$class=null;
	echo "<li><a href='contacts.php' $class>Membres/Sympathisants</a></li>";

	$_SESSION['current_page'] == 'index.php'?$class='class="selected"':$class=null;
	echo "<li><a href='index.php' $class>Électeurs</a></li>";
	if($_SESSION['user']->access != 'writer') {
	    echo "<li><a href='report.php'>Rapport pointage</a></li>";
	}
	echo "<li><a href='saisie.php'>Saisie par numéro d'électeur</a></li>";
	if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
	    $_SESSION['current_page'] == 'admin.php'?$class='class="selected"':$class=null;
	    echo "<li><a href='admin.php' $class>Admin</a></li>";
	}
	echo "<li><a href='logout.php'>Quitter</a></li>";
	?>
	</ul>
	</div>
	</div>

	<br class="clear" />
	<div id="content">
	<?php
    }

    function processTopMenu(){
        if(!empty($_POST['display']))
        {
            $_SESSION['display'] = $_POST['display'];
        }
        $circn = new Circonscription();
        $circn->processDropdown();
    }

    function createTextArea()
    {
        $factory = new DropdownFactory();
        ?>
	<h2>Saisie par numéros d'électeurs</h2>
	<p>Veuillez entrer les électeurs, un par ligne, selon le format <em><strong>section
	de vote.numéro d'électeur</strong></em>. Ex:<strong>23.84</strong></p>
	<form method="POST" name="batch_selection" action="batch2.php">
	<table>
		<tr>
			<td colspan="2" align="center"><textarea name="batch_voters"
				rows="30" cols="20"></textarea></td>
		</tr>
		<tr>
			<td colspan="2" align="center">&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; <img border="0"
				src="images/selection.png">Pour les entrées saisies: <br>
			<br>
			</td>
		</tr>
		<tr>
			<td valign="top" align="center"><?php echo $factory->build_edit_new("Jour du scrutin", "vote", 'VOTE[]');?>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center">
			<h3><u>Important:</u> Les champs <i>Type de contact</i> et <i>Contact
			Réussi ?</i> sont <b>obligatoires</b> pour créer un pointage valide.</h3>
			</td>
		</tr>

		<tr>
			<td colspan="3" align="center"><b>Type de contact: </b> <?php echo $factory->build_edit_new(NULL, "type_contact", "CONTACT[]");?>&nbsp;&nbsp;
			<b>Contact Réussi ?:</b> <?php echo $factory->build_edit_new(NULL, "resultat", "RESULTAT[]");?>&nbsp;&nbsp;
			<?php echo $factory->build_edit_new("Intention de vote", "allegeance", "ALLEGEANCE[]");?>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center"><input type="submit" name="apply"
				value="Appliquer"> <input type="hidden" name="displayed" value="">
			</form>
			</td>
		</tr>
	</table>
	<?php
    }
}
