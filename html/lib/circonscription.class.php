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

require_once('lib/liste_electeurs.class.php');

class Circonscription
{
    private static $SORT_ORDER = array('REGION_CIRCN' => array('sql_order_by'=>'nom, NM_CIRCN_PROVN', 'sql_label'=>"CONCAT(regions.nom, ' / ', NM_CIRCN_PROVN) as label"),
                                'CIRCN_REGION' => array('sql_order_by'=>'NM_CIRCN_PROVN', 'sql_label'=>"CONCAT(NM_CIRCN_PROVN, ' / ', regions.nom) as label")
    );
    public $db     = NULL;

    /*
     * Retrouve dans la base de données circonscriptions le nom de la circonscription 
     * d'après le numéro.
     */
    static function circn_name_from_no($circ_no, $db)
    {
        // Retrieve the circonscription name
        $sql = "SELECT `NM_CIRCN_PROVN` FROM circonscriptions WHERE NO_CIRCN_PROVN='{$circ_no}';";
        $circ_nm = $db->query($sql);
        $circ_nm = $circ_nm->fetch(PDO::FETCH_ASSOC);
        $circ_nm = $circ_nm['NM_CIRCN_PROVN'];

        return $circ_nm;
    }

    /*
     * Retrouve dans la base de données circonscriptions le numéro de la circonscription 
     * d'après le nom.
     */
    static function circn_no_from_name($circ_nm, $db)
    {
        // Retrieve the circonscription number
        $sql = "SELECT `NO_CIRCN_PROVN` FROM circonscriptions WHERE NM_CIRCN_PROVN='{$circ_nm}';";
        $circ_no = $db->query($sql);
        $circ_no = $circ_no->fetch(PDO::FETCH_ASSOC);
        $circ_no = $circ_no['NO_CIRCN_PROVN'];

        return $circ_no;
    }

    function __construct()
    {
        $this->db = DB::getDatabase('pointage');
    }

    static function dataCmp($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }
    
    function selectData($onlyAccessible=true, $includeRegions=true)
    {
        $user = $_SESSION['user'];
        if($onlyAccessible) {
            $sqlJoinCond = "";
            if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
                $sqlJoinCond .= "";
            }
            else {
                //var_dump($_SESSION['user']);
                $sqlJoinCond .= " JOIN users ON (users.username=".$this->db->quote($_SESSION['user']->username)." AND (users.NO_CIRCN_PROVN=circonscriptions.NO_CIRCN_PROVN OR users.id_region=circonscriptions.region))";
            }

        }
        //var_dump($_SESSION);
        //var_dump(self::$SORT_ORDER);
        $sqlOrderBy = self::$SORT_ORDER[$_SESSION['user']->circn_sort_order]['sql_order_by'];
        $sqlLabel = self::$SORT_ORDER[$_SESSION['user']->circn_sort_order]['sql_label'];
        //var_dump($sqlOrderBy);
        $sql = "SELECT circonscriptions.NO_CIRCN_PROVN as value, regions.id_region, regions.nom FROM circonscriptions JOIN regions ON (circonscriptions.region=regions.id_region) $sqlJoinCond ";
        // var_dump($sql);
        $stm = $this->db->prepare($sql);
        $stm->execute();

        $retval = array();
        $previousRegion = null;
        $fullRegionArray = array();
        while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            //var_dump($row);
            if($includeRegions && $previousRegion!=$row['id_region']){
                $value =  'region_'.$row['id_region'];
                $fullRegion=array('value'=>$value, 'label'=>'-- Toutes les circonscriptions de '.$row['nom']);
                if($_SESSION['user']->circn_sort_order == 'REGION_CIRCN') {
                    $retval[$value]=$fullRegion;
                }
                else {
                    $fullRegionArray[$value]=$fullRegion;
                }
                $previousRegion=$value;

            }
            $retval[$row['value']]=array('value'=>$row['value'], 'label'=>$row['label']);
        }
        //var_dump($retval);
        
        uasort($fullRegionArray,array("Circonscription", "dataCmp"));
        //var_dump($fullRegionArray);
        $retval = $retval + $fullRegionArray;
        return $retval;
    }

    function genDropdown()
    {
        $accessibleCirconscriptionsData = $this->selectData();
        $html = null;

        $html .= "<form method=\"post\" id=\"change_circ\" action='' style='display: inline; margin: 0px; padding: 0px'>";

        if(count($accessibleCirconscriptionsData) == 1){
            $html .= $_SESSION['user']->nm_circn;
            $html .= "<input type=\"hidden\" name=\"circonscription\" value=\"".$_SESSION['user']->no_circn."\">";
        }
        else {
            $html .= html::genDropdown('circonscription',$accessibleCirconscriptionsData, $_SESSION['user']->no_circn,'onChange=submit();',TRUE);
        }
        $html .= "<input type=\"hidden\" name=\"displayed\" value=\"Changement_effectué\">";
        $html .= "</form>";
        return $html;
    }

    function processDropdown() {
        if(!empty($_REQUEST['circonscription']) ){
            // 'Changer de circonscription':
            $circ_no = $_REQUEST['circonscription'];
            $accessibleCirconscriptionsData = $this->selectData();
            //var_dump($accessibleCirconscriptionsData);
            if (empty($accessibleCirconscriptionsData[$circ_no])) {
                throw new exception("Access denied to $circ_no");
            }

            if($_SESSION['user']->no_circn != $circ_no ) {
                $circ_nm = Circonscription::circn_name_from_no($circ_no, $_SESSION['user']->getDB());
                $_SESSION['user']->nm_circn = $accessibleCirconscriptionsData[$circ_no]['label'];
                $_SESSION['user']->no_circn = $circ_no;
            }
        }
    }

    function drawAllCheckboxList($formname)
    {
        $data = $this->selectData(true, true);

        $rows = array();
        $headers = array("<input type=\"checkbox\" name=\"select_all\" onClick=\"markAllRows('$formname');\"/>",'ID','Circonscription');
        //var_dump($data);
        $i = 0;
        foreach($data as $key => $value)
        {
            $rows[$i]['checkbox'] = html::genCheckbox($formname."[]",$value['value']);
            $rows[$i] += $value;
            $i++;
        }
        //var_dump($rows);
        return html::genList('Circonscription',$rows,$headers);
    }

    function exportPostalCode($circonscription_id=array())
    {
        $sql = "SELECT DISTINCT D.CO_POSTL,D.NO_CIRCN_PROVN, C.NM_CIRCN_PROVN
        FROM liste_dge AS D
        LEFT JOIN circonscriptions AS C ON C.NO_CIRCN_PROVN=D.NO_CIRCN_PROVN ";

        if(!is_null($circonscription_id))
        {
            $id = "'".implode("','",$circonscription_id)."'";

            $sql .= "WHERE D.NO_CIRCN_PROVN IN($id)";
        }
        //pretty_print_r($sql);
        $stm = $this->db->prepare($sql);

        $stm->execute();

        $data = array();
        $previous_circn = null;
        $retval=null;
        $headers = array('Code Postal');
        while($row = $stm->fetch(PDO::FETCH_ASSOC))
        {
            if($row['NO_CIRCN_PROVN']!=$previous_circn) {
                if(!empty($previous_circn)) {
                    $retval .= html::genList('Liste de Code Postaux',$data,$headers);
                    $data = null;
                }
                $retval .= "<h2>{$row['NO_CIRCN_PROVN']} - {$row['NM_CIRCN_PROVN']}</h2><hr>";
                $previous_circn = $row['NO_CIRCN_PROVN'];
            }
            $data[] = array($row['CO_POSTL']);
        }
        $retval .= html::genList('Liste de Code Postaux',$data,$headers);
        return  $retval;
    }

    /** Exporte la liste de # de tél pour une circonscription */
    function exportTelephoneNumbers($circonscription_id=array(), $optionalWhere=NULL)
    {

        //$additionalWhere = " AND ";
        if(!is_null($circonscription_id))
        {
            $id = "'".implode("','",$circonscription_id)."'";

            //$additionalWhere .= "NO_CIRCN_PROVN IN($id)";
        }

        $additionalWhere = $optionalWhere;
        $additionalWhere .= " AND telephone IS NOT NULL";
        $selectListOverride ="liste_dge.NO_CIRCN_PROVN, COALESCE(TELEPHONE_MANUEL, TELEPHONE) as telephone";

        if(!empty($circonscription_id))
        {
            $id = "'".implode("','",$circonscription_id)."'";

            $circonscriptions .= " liste_dge.NO_CIRCN_PROVN IN($id)";
            $stm=liste_electeurs::getElectorsPDOStatement($additionalWhere, $selectListOverride,"","",$circonscriptions, true);
        }
        else{
            $stm=liste_electeurs::getElectorsPDOStatement($additionalWhere, $selectListOverride,"","","", true);
        }


        $data = array();
        $previous_circn = null;
        $retval=null;
        $headers = array('Numéros de téléphone');
        while($row = $stm->fetch(PDO::FETCH_ASSOC))
        {
            if($row['NO_CIRCN_PROVN']!=$previous_circn) {
                if(!empty($previous_circn)) {
                    $retval .= html::genList('Liste de Numéros de Téléphone',$data,$headers);
                    $data = null;
                }
                $retval .= "<h2>{$row['NO_CIRCN_PROVN']} - {$row['NM_CIRCN_PROVN']}</h2><hr>";
                $previous_circn = $row['NO_CIRCN_PROVN'];
            }
            $data[] = array(str_replace('-','',$row['telephone']));
        }
        $retval .= html::genList('Liste de Téléphones',$data,$headers);
        return  $retval;
    }

    function getAdminUI()
    {
        $actionList = array();
        $actionList[] = array('label'=>'Exporter les Code Postaux','value'=>'export_postal_code');
        $actionList[] = array('label'=>'Exporter les numéros de téléphone','value'=>'export_telephone_numbers');
        if($_SESSION['user']->access == 'superadmin'){
            $actionList[] = array('label'=>'Générer de nouveaux usagers','value'=>'generate_users');
        }
        $retval = '';
        $retval .= "<h2>Gestion des circonscriptions</h2>\n";
        $retval .= "<form action='{$_SERVER['REQUEST_URI']}' method='post' id='circonscriptions'>\n";
        $retval .= "<table>\n";
        $retval .= "<tr style='vertical-align:top'>\n";
        $retval .= "<td>{$this->drawAllCheckboxList('circonscriptions')}</td>\n";
        $retval .= "<td>Pour les circonscriptions sélectionnées:<br>\n";
        $retval .= html::genDropdown('action',$actionList);
        $retval .= "<input type='submit' name='circonscriptionSubmit' value='Exécuter'/></td>\n";
        $retval .= "</tr>\n";
        $retval .= "</table>\n";
        $retval .= "</form>\n";
        return $retval;
    }
    function processAdminUI()
    {
        $list = NULL;
        if(!empty($_REQUEST['circonscriptionSubmit']))
        {
            $idArray = !empty($_POST['circonscriptions'])?$_POST['circonscriptions']:array();
            if($_POST['action'] == 'export_postal_code')
            {
                $retval = $this->exportPostalCode($idArray);
            }
            else if ($_POST['action'] == 'export_telephone_numbers') {
                $retval = $this->exportTelephoneNumbers($idArray);
            }
            else if ($_POST['action'] == 'export_telephone_numbers_FQCIL') {
                $retval = $this->exportTelephoneNumbers($idArray, " AND historique_pointage.ALLEGEANCE IN ('FQCIL','indecis')");
            }
            else if ($_POST['action'] == 'generate_users') {
                if($_SESSION['user']->access == 'superadmin'){
                    foreach($idArray as $key => $value){
                        user::create_users($value, $this->db);
                    }
                }
                else {
                    throw new Exception("Access denied");
                }
                 
            }
            else {
                throw new Exception("Unknown action");
            }
        }
        return $retval;
    }
}

?>
