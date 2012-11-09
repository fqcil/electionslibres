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


class Historique {
    //Infos electeur

    public $pointage_id     =NULL;
    public $telephone1      =NULL;
    public $telephone2      =NULL;
    public $nm_elect        =NULL;
    public $pr_elect        =NULL;
    public $da_naisn_elect  =NULL;
    public $no_civq_elect   =NULL;
    public $comment         =NULL;
    public $vote            =NULL;


    public $db;
    public $electeur_id;
    public $contact;
    public $resultat;
    public $allegeance;

    private $loadStatus = null; //null, 'last', 'full'
    private $electeurRow = null;
    private $historiqueRows = array();
    /** Contient les valeurs des étiquettes
     Tableau à deux dimensions $labelCache[$table][$tag]=$etiquette
     */
    static private $labelCache = array();

    private function load($row)
    {
        $this->pointage_id    = $row['P_ID'];
        $this->telephone1     = $row['TELEPHONE1'];
        $this->telephone2     = $row['TELEPHONE_MANUEL'];
        $this->nm_elect       = $row['NM_ELECT'];
        $this->pr_elect       = $row['PR_ELECT'];
        $this->da_naisn_elect = $row['DA_NAISN_ELECT'];
        $this->ad_elect = $row['AD_ELECT'];
        $this->no_civq_elect  = $row['NO_CIVQ_ELECT'];
        $this->comment        = $row['NOTE'];
        $this->vote           = $row['VOTE'];
        return TRUE;
    }

    private function loadRow($row) {
        if(
        !array_key_exists('CONTACT', $row)
        || !array_key_exists('RESULTAT', $row)
        || !array_key_exists('ALLEGEANCE', $row)
        //|| !array_key_exists('ELECT_ID', $row)
        //|| !array_key_exists('H_ID', $row)
        || !array_key_exists('DATE', $row)
        ){
            var_dump($row);
            throw new Exception ("la rangée n'est pas une rangée d'historique valide");  
        }
        else if(!empty($row['DATE'])) {
            $this->historiqueRows[] = $row;
        }
        else {

            ; //Do nothing, the query is valid, but there is no history for that elector
        }
    }

    static function getHistoriqueFromLastRow($idDge, $row) {
        //var_dump($row);
        $object = new self($idDge);
        $object->load($row);
        $object->loadRow($row);
        $object->electeurRow = $row;
        $object->loadStatus = 'last';
        return $object;
    }

    static function getHistoriqueFromIdDge($idDge) {
        //var_dump($row);
        $object = new self($idDge);
        $stm = liste_electeurs::getElectorsPDOStatement("AND ID_DGE='{$idDge}'", null, null, null, null, true);
        $row = $stm->fetch( PDO::FETCH_ASSOC);
        $object->load($row);
        $object->loadRow($row);
        $object->electeurRow = $row;
        $object->loadStatus = 'last';
        return $object;
    }

    static function getHistoriqueFromAllRows($idDge, $rows) {
        //var_dump($row);
        $object = new self($idDge);
        $object->load($rows[0]);
        foreach($rows as $row) {
            $object->loadRow($row);
        }

        $object->electeurRow = $rows[0];
        $object->loadStatus = 'full';
        return $object;
    }

    private function __construct($idDge){
        $this->db = DB::getDatabase('pointage');
        if(empty($idDge)){
            throw new Exception ("l'id de l'électeur ne peut être null");  
        }
        $object->idDge = $idDge;
        $object->loadStatus = null;
    }

    /*private function load($contact, $resultat, $vote, $allg){

    // Load a new pointage
    $this->contact = $contact;
    $this->resultat = $resultat;
    $this->vote= $vote;
    $this->allegeance= $allg;
    }*/
    function createUpdate($id, $iddge = NULL)
    {
        $updatePointageOnly = FALSE;

        if(empty($this->pointage_id))
        {
            $sql = "INSERT INTO pointage
                (`P_ID`, `NOTE`,`NM_ELECT`,`PR_ELECT`,`DERNIER_POINTAGE_DA_NAISN_ELECT`,`DERNIER_POINTAGE_NO_CIVQ_ELECT`,`DERNIER_POINTAGE_AD_ELECT`,`DERNIER_POINTAGE_TELEPHONE`,`TELEPHONE_MANUEL`, `VOTE`) 
                VALUES(:id, :note,:NM_ELECT,:PR_ELECT,:DA_NAISN_ELECT,:NO_CIVQ_ELECT,:DERNIER_POINTAGE_AD_ELECT,:telephone,:telephone2, :vote);";
            $stm = $this->db->prepare($sql);
        }
        else
        {
            $sql = "UPDATE pointage SET `NOTE`=:note,NM_ELECT=:NM_ELECT,PR_ELECT=:PR_ELECT,DERNIER_POINTAGE_DA_NAISN_ELECT=:DA_NAISN_ELECT,DERNIER_POINTAGE_NO_CIVQ_ELECT=:NO_CIVQ_ELECT,DERNIER_POINTAGE_AD_ELECT=:DERNIER_POINTAGE_AD_ELECT,DERNIER_POINTAGE_TELEPHONE=:telephone, TELEPHONE_MANUEL=:telephone2, VOTE=:vote
                WHERE `P_ID`=:id";
            $stm = $this->db->prepare($sql);
            $stm->bindParam(':id',$this->pointage_id);
        }
        //var_dump($_REQUEST);
        //var_dump($sql);
        
        //$id_dge  = !empty($POST['ID_DGE'])?$_POST['ID_DGE']:$id;
        $id_dge  = !empty($iddge)?$iddge:$id;
        $nm_elect = !empty($_POST['NM_ELECT'])?$_POST['NM_ELECT']:$this->nm_elect;
        $pr_elect = !empty($_POST['PR_ELECT'])?$_POST['PR_ELECT']:$this->pr_elect;
        $da_naisn_elect = !empty($_POST['DA_NAISN_ELECT'])?$_POST['DA_NAISN_ELECT']:$this->da_naisn_elect;
        $no_civq_elect = !empty($_POST['NO_CIVQ_ELECT'])?$_POST['NO_CIVQ_ELECT']:$this->no_civq_elect;
        $ad_elect = !empty($_POST['AD_ELECT'])?$_POST['AD_ELECT']:$this->ad_elect;
        $telephone = !empty($_POST['telephone1'])?$_POST['telephone1']:'';
        $telephone2 = isset($_POST['telephone2'][$id])?$_POST['telephone2'][$id]:$this->telephone2;
        $comment = isset($_POST['comment'][$id])?$_POST['comment'][$id]:$this->comment;
        $vote = isset($_POST['VOTE'][$id])?$_POST['VOTE'][$id]:$this->vote;
        ($vote=='bof')?$vote=NULL:$vote=$vote;


        $contact =  ($_POST['CONTACT'][$id]=='bof')?'':$_POST['CONTACT'][$id];
        $allg =  ($_POST['ALLEGEANCE'][$id]=='bof')?NULL:$_POST['ALLEGEANCE'][$id];
        $rslt = ($_POST['RESULTAT'][$id]=='bof')?'':$_POST['RESULTAT'][$id];

        // Quick hack to not add the last pointage in the history if nothing has been modified
        $row = $this->getDernierPointageRow();
        // Check if the value are the same

        if( ( $contact == $row['CONTACT'] && $rslt == $row['RESULTAT'] && $allg == $row['ALLEGEANCE'] ) || 
            ( empty($contact) && empty($rslt) && empty($allg) ) ){

            $updatePointageOnly = TRUE;
        }

        $stm->bindParam(':id', $id_dge);
        $stm->bindParam(':note',$comment);
        $stm->bindParam(':NM_ELECT',$nm_elect);
        $stm->bindParam(':PR_ELECT',$pr_elect);
        $stm->bindParam(':DA_NAISN_ELECT',$da_naisn_elect);
        $stm->bindParam(':NO_CIVQ_ELECT',$no_civq_elect);
        $stm->bindParam(':DERNIER_POINTAGE_AD_ELECT',$ad_elect);
        $stm->bindParam(':telephone',$telephone);
        $stm->bindParam(':telephone2',$telephone2);
        $stm->bindParam(':vote',$vote);
        $result = $stm->execute();

        if(!$updatePointageOnly){

            //empty($this->pointage_id)?$p_id=$result['LAST_INSERT_ID()']:$p_id=$this->pointage_id;
            !empty($iddge)?$id=$iddge:$id=$id;
            empty($this->pointage_id)?$p_id=$id:$p_id=$this->pointage_id;

            // Ajoute le pointage courant dans l'historique
            Historique::ajoute_pointage($this->db, $p_id, $contact, $rslt, $allg);
        }

        return $stm->rowCount();
    }

    static function getLabelValeurArray($table){
        $db = DB::getDatabase('pointage');
        //var_dump(self::$labelCache);

        if(!isset(self::$labelCache[$table])) {
            //echo "Cache miss";
            $sql = "SELECT * from $table;";
            $result = $db->query($sql);
            if($result===false){
                throw new Exception("invalid table: $table");
            }
            while($row  = $result->fetch(PDO::FETCH_ASSOC)){

                self::$labelCache[$table][$row['valeur']]=$row['label'];
            }
            //var_dump(self::$labelCache[$table]);
        }
        else {
            //echo "Cache hit";
        }
        return self::$labelCache[$table];
    }

    static function getLabelFromValeur($valeur, $table){
        //var_dump(self::$labelCache);
        if(empty($valeur)) {
            $retval = null;
        }
        else {
            $array = self::getLabelValeurArray($table);
            if(empty($array[$valeur])){
                throw new Exception("invalid valeur ($valeur), table ($table) pair");
            }
            $retval = $array[$valeur];
        }
        return $retval;
    }

    /**
     * Ajoute un pointage dans l'historique pour un électeur donné
     * @param $id_dge      L'id unique de l'électeur concerné
     * @param $contact      The contact type. Must be non NULL
     * @param $rslt         The result. Must be non NULL
     * @param $contact      The allegeance
     * @param $autodialer   The autodialer result if exists
     */
    static function ajoute_pointage( $db, $id_dge, $contact, $rslt, $allg, $date_sondage=NULL, $autodialer=NULL ){

        // Reset le flag `dernier pointage` pour toutes les entrées précédentes dans l'historique   
        //var_dump($elect_id);
        // Insert dans la table historique_pointage
        $sql = "UPDATE historique_pointage SET `IS_DERNIER_POINTAGE`=FALSE WHERE `H_ID`='{$id_dge}' AND `IS_DERNIER_POINTAGE`=TRUE;\n";
        $db->exec($sql);

        if($date_sondage == NULL)
        {
            $sql = " INSERT into historique_pointage
                (`CONTACT`, `RESULTAT`, `ALLEGEANCE`, `H_ID`, `IS_DERNIER_POINTAGE`, `AUTODIALER_RESULT`)
                VALUES(:contact, :resultat, :allegeance, :elect_id, TRUE, :autodialer)";
            $stm = $db->prepare($sql);
        }
        else
        {
            $sql = " INSERT into historique_pointage
                (`CONTACT`, `RESULTAT`, `ALLEGEANCE`, `H_ID`, `IS_DERNIER_POINTAGE`, `DATE`, `AUTODIALER_RESULT`)
                VALUES(:contact, :resultat, :allegeance, :elect_id, TRUE, :date_sondage, :autodialer)";
            $stm = $db->prepare($sql);
            $stm->bindParam(':date_sondage',$date_sondage);
        }

        
        $stm->bindParam(':resultat',$rslt);
        $stm->bindParam(':contact',$contact);
        $stm->bindParam(':allegeance',$allg);
        $stm->bindParam(':elect_id',$id_dge);
        $stm->bindParam(':autodialer',$autodialer);
        $result = $stm->execute();
    }
    /**
     *
     * @return Rangée, ou null si l'électeur n'a pas été pointé
     */
    public function getDernierPointageRow() {
        if ($this->loadStatus == null) {
            $this->loadAllPointages();
        }
        //var_dump($this->historiqueRows[0]);
        return $this->historiqueRows[0];//La table est indexée par date, la dernière en premier
    }
    /**
     * Retourne le dernier pointage listé dans l'historique pour un électeur donné
     * @param $elect_id     L'id unique de l'électeur dont on veut l'information.
     */
    private function dernier_pointage( $elect_id ){

        $data = NULL;

        if( $elect_id == NULL)
        return NULL;

        $sql = "SELECT `CONTACT`, `RESULTAT`, `ALLEGEANCE`, `VOTE`, `DATE` from historique_pointage
            WHERE H_ID='{$elect_id}'
            ORDER BY `DATE` DESC;";
        $result = $this->db->query($sql);
        $data = $result->fetchAll(PDO::FETCH_ASSOC);

        // Le plus récent est l'indice 0 dans le  tableau, car nous classons par ordre décroissant
        return $data[0];
    }

    /**
     * Retourne tous les pointages listés dans l'historique pour un électeur donné
     * @param $elect_id     L'id unique de l'électeur dont on veut l'information.
     */
    private function loadAllPointages(){

        $data = NULL;

        $sql = "SELECT `CONTACT`, `RESULTAT`, `ALLEGEANCE`, `VOTE`, `DATE` from historique_pointage
            WHERE H_ID='{$this->electeur_id}'
            ORDER BY `DATE` DESC;";
        $result = $this->db->query($sql);
        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->loadRow($row);
        }
        $this->loadStatus='full';
        return TRUE;

    }

    public function displayEditUI($bgColor)
    {
        $last_pointage = $this->getDernierPointageRow();
        $elector = $this->electeurRow;
        unset($data);
        // Display the label rather than the key

        if ($_SESSION['display']=='add')
        {
            $data['DATE'] = $last_pointage['DATE'];
            $data['RESULTAT']   = DropdownFactory::build_edit_new(NULL,'resultat','RESULTAT['.$elector['ID_DGE'].']',$last_pointage['RESULTAT'], $elector['ID_DGE']);
            $data['CONTACT']  = DropdownFactory::build_edit_new(NULL,'type_contact','CONTACT['.$elector['ID_DGE'].']', $last_pointage['CONTACT'], $elector['ID_DGE']);
            $data['ALLEGEANCE']   = DropdownFactory::build_edit_new(NULL,'allegeance','ALLEGEANCE['.$elector['ID_DGE'].']',$last_pointage['ALLEGEANCE'], $elector['ID_DGE']);
            $data['VOTE']   = DropdownFactory::build_edit_new(NULL,'vote','VOTE['.$elector['ID_DGE'].']',$elector['VOTE'], $elector['ID_DGE']);
            $javascript = 'onchange="processSelectBoxChange(\''.$elector['ID_DGE'].'\', \''."comment[{$elector['ID_DGE']}]".'\');"';
            $data['NOTE'] = "<input type=\"text\" size=\"30\" maxlength=\"45\" value=\"{$elector['NOTE']}\" name=\"comment[{$elector['ID_DGE']}]\" id=\"comment[{$elector['ID_DGE']}]\" $javascript>";
            $javascript = 'onchange="processSelectBoxChange(\''.$elector['ID_DGE'].'\', \''."telephone2[{$elector['ID_DGE']}]".'\');"';
            $data['TELEPHONE_MANUEL'] = "<input type=\"text\" size=\"15\" maxlength=\"12\" value=\"{$elector['TELEPHONE_MANUEL']}\" name=\"telephone2[{$elector['ID_DGE']}]\" id=\"telephone2[{$elector['ID_DGE']}]\" $javascript>";
            
        }
        else if($_SESSION['display']=="add_history") {
            $data['DATE'] = "<select name='dummy'><option value=''>Nouveau pointage</option></select>\n";
            $data['RESULTAT']   = DropdownFactory::build_edit_new(NULL,'resultat','RESULTAT['.$elector['ID_DGE'].']',null, $elector['ID_DGE']);
            $data['CONTACT']  = DropdownFactory::build_edit_new(NULL,'type_contact','CONTACT['.$elector['ID_DGE'].']', null, $elector['ID_DGE']);
            $data['ALLEGEANCE']   = DropdownFactory::build_edit_new(NULL,'allegeance','ALLEGEANCE['.$elector['ID_DGE'].']',null, $elector['ID_DGE']);
            $data['VOTE'] = DropdownFactory::build_edit_new(NULL,'vote','VOTE['.$elector['ID_DGE'].']',$elector['VOTE'], $elector['ID_DGE']);
            $javascript = 'onchange="processSelectBoxChange(\''.$elector['ID_DGE'].'\', \''."comment[{$elector['ID_DGE']}]".'\');"';
            $data['NOTE'] = "<input type=\"text\" size=\"30\" maxlength=\"45\" value=\"{$elector['NOTE']}\" name=\"comment[{$elector['ID_DGE']}]\" id=\"comment[{$elector['ID_DGE']}]\" $javascript>";
            $javascript = 'onchange="processSelectBoxChange(\''.$elector['ID_DGE'].'\', \''."telephone2[{$elector['ID_DGE']}]".'\');"';
            $data['TELEPHONE_MANUEL'] = "<input type=\"text\" size=\"15\" maxlength=\"12\" value=\"{$elector['TELEPHONE_MANUEL']}\" name=\"telephone2[{$elector['ID_DGE']}]\" $javascript>";
        }
        else
        {
            //var_dump($last_pointage);
            $data['DATE'] = $last_pointage['DATE'];
            $data['CONTACT'] = Historique::getLabelFromValeur($last_pointage['CONTACT'], 'type_contact', $this->db);
            $data['RESULTAT'] = Historique::getLabelFromValeur($last_pointage['RESULTAT'], 'resultat', $this->db);
            $data['VOTE'] = Historique::getLabelFromValeur($elector['VOTE'], 'vote', $this->db);
            $data['ALLEGEANCE'] = Historique::getLabelFromValeur($last_pointage['ALLEGEANCE'], 'allegeance', $this->db);
            $data['NOTE'] = $elector['NOTE'];
            $data['TELEPHONE_MANUEL'] = $elector['TELEPHONE_MANUEL'];
            //var_dump($data);
        }

        echo "<tr bgcolor='$bgColor'>\n";
        echo "<td align='center'>\n";
        echo "<input type='checkbox' id='elector_id_{$elector['ID_DGE']}' name='elector_id[{$elector['ID_DGE']}]' value='{$elector['ID_DGE']}' />\n";
        echo "</td>\n";//Checkbox
        echo "<td align='right'><a href='edit.php?id={$elector['ID_DGE']}'>{$elector['NO_ELECT']}</a></td>\n";
        if($_SESSION['show_age'] == 1){
            echo "<td class='age'>{$elector['AGE']}</td>\n";
        }
        if($_SESSION['show_co_sexe'] == 1){
            echo "<td class='sex'>{$elector['CO_SEXE']}</td>\n";
        }

        echo "<td class='prenom'>{$elector['PR_ELECT']}</td>\n";


        echo "<td class='nom'>{$elector['NM_ELECT']}</td>\n";

        if($_SESSION['show_address'] == 1){
            $address = $elector['NO_CIVQ_ELECT'];
            if($elector['NO_APPRT_ELECT'] != '' && $elector['NO_APPRT_ELECT'] != 0) {
                $address = $address.' app '.$elector['NO_APPRT_ELECT'];
            }

            echo "<td class='adresse'>{$address}</td>\n";
            echo "<td class='rue'>{$elector['AD_ELECT']}</td>\n";
        }
        if($_SESSION['show_city'] == 1){
            echo "<td class='municipalite'>{$elector['NM_MUNCP']}</td>\n";
        }
        if($_SESSION['show_telephone1'] == 1){
            echo "<td class='telephone'>{$elector['no_telephone']}</td>\n";
        }
        if($_SESSION['show_telephone_m'] == 1){
            echo "<td class='telephone_m'>{$data['TELEPHONE_MANUEL']}</td>\n";
        }

        if($_SESSION['show_origin'] == 1){
            echo "<td>{$elector['ORIGIN']}</td>\n";
        }
        if($_SESSION['show_category'] == 1){
            echo "<td class='membership'>{$elector['CATEGORY']}</td>\n";
        }
        if($_SESSION['show_date'] == 1){
            echo "<td class='date'>{$data['DATE']}\n";
            if ($_SESSION['display']=="add_history"){
                foreach($this->historiqueRows as $row) {
                    echo "<br>{$row['DATE']}\n";
                }
            }
            echo "</td>\n";
        }
        if($_SESSION['show_contact'] == 1){
            echo "<td class='contact'>{$data['CONTACT']}\n";
            if ($_SESSION['display']=="add_history"){
                foreach($this->historiqueRows as $row) {
                    $contact = Historique::getLabelFromValeur($row['CONTACT'], 'type_contact', $this->db);
                    echo "<br>{$contact}\n";
                }
            }
            echo "</td>\n";
        }
        if($_SESSION['show_rslt'] == 1){
            echo "<td class='resultat'>{$data['RESULTAT']}\n";
            if ($_SESSION['display']=="add_history"){
                foreach($this->historiqueRows as $row) {
                    $contact = Historique::getLabelFromValeur($row['RESULTAT'], 'resultat', $this->db);
                    echo "<br>{$contact}\n";
                }
            }
            echo "</td>\n";
        }
        if($_SESSION['show_allg'] == 1){
            echo "<td class='allegeance'>{$data['ALLEGEANCE']}\n";
            if ($_SESSION['display']=="add_history"){
                foreach($this->historiqueRows as $row) {
                    $contact = Historique::getLabelFromValeur($row['ALLEGEANCE'], 'allegeance', $this->db);
                    echo "<br>{$contact}\n";
                }
            }
            echo "</td>\n";
        }
        if($_SESSION['show_vote'] == 1){
            echo "<td class='vote'>{$data['VOTE']}</td>\n";
        }
        
        if($_SESSION['show_note'] == 1){
            echo "<td class='commentaire'>{$data['NOTE']}</td>\n";
        }
        echo "</tr>\n";
         
         
    }

}

?>
