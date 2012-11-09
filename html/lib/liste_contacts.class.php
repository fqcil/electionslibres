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
require_once('circonscription.class.php');

class liste_contacts
{
    /* label (mandatory): String displayed as the column header
     *
     * display (mandatory): Allowed values:
     * 	yes: always show
     *  default_yes: optionnal, default to yes
     *  default_no: optionnal, default to no
     *  no: never show
     *
     *  formatterCallback (optional): Name of the formating function for display
     *
     *  sqlSelectList (optional): select list for the sql qery.  Must resolve to the array key
     */
    static $fieldInfoArray = array(
    'no_membre_FQCIL' => array('label'=>"#membre FQCIL", 
                             'display'=>'default_yes'),
    'last_name' => array('label'=>"Nom",
                         'display'=>'yes'), 
    'first_name' => array('label'=>"Prénom", 'display'=>'yes'),
    'age' => array('label'=>"Âge", 'display'=>'default_no',
                   'sqlSelectList'=>"FLOOR(DATEDIFF(NOW(),civicrm_individual.birth_date)/365.25) AS age"),
    'birth_date' => array('label'=>"Date de naissance", 'display'=>'restricted'),
    'gender_id' => array('label'=>"Sexe", 'display'=>'no'),
    'circonscription_id' => array('label'=>"Id circonscription", 'display'=>'no'),
    'circonscription_name' => array('label'=>"Nom circonscription", 'display'=>'default_no',
                                    'sqlSelectList'=>"circonscription_civicrm_custom_option.label AS circonscription_name"),
    'street_address' => array('label'=>"Addresse", 'display'=>'default_yes'),
    'city' => array('label'=>"Ville", 'display'=>'default_yes'),
    'postal_code' => array('label'=>"Code postal", 'display'=>'default_yes'),
    'phone_list' => array('label'=>"Téléphones", 'display'=>'default_yes'),
    'email_list' => array('label'=>"Courriels", 'display'=>'default_yes'),
    'name' => array('label'=>"Statut membership", 'display'=>'yes'),
    'join_date' => array('label'=>"Membre depuis", 'display'=>'default_no'),
    'start_date' => array('label'=>"Debut validité membership", 'display'=>'default_no'),
    'end_date' => array('label'=>"Fin validité membership", 'display'=>'default_no'),
    'last_contribution_date' => array('label'=>"Dernière contribution", 'display'=>'default_no'),
    'match_liste_dge' => array('label'=>"Correspondance(s) dans la liste élect. (date de naissance, code postal, no de porte)", /* Date de naissance, code postal et no. de porte */
                                   'display'=>'default_no', 
                                   'formatterCallback'=>array('liste_contacts', 'formatDGELink'),
                                   'sqlSelectList'=>"GROUP_CONCAT(DISTINCT liste_dge.ID_DGE SEPARATOR '+') as match_liste_dge",
                                   'sqlJoin'=>"LEFT JOIN pointage.liste_dge ON (
        									     liste_dge.DA_NAISN_ELECT = civicrm_individual.birth_date 
                                                 AND liste_dge.CO_POSTL = REPLACE(civicrm_address.postal_code, ' ', '')
                                                 AND civicrm_address.street_address LIKE CONCAT(liste_dge.NO_CIVQ_ELECT,'%')
                                                 )"),
     'match_liste_dge_dateNaiss_Nom_Prenom' => array('label'=>"Correspondance(s) dans la liste élect. (date de naissance, nom et prénom)", /* Date de naissance, nom et prénom */
                                   'display'=>'default_no', 
                                   'formatterCallback'=>array('liste_contacts', 'formatDGELink'),
                                   'sqlSelectList'=>"GROUP_CONCAT(DISTINCT liste_dge_dateNaiss_Nom_Prenom.ID_DGE SEPARATOR '+') as match_liste_dge_dateNaiss_Nom_Prenom",
                                   'sqlJoin'=>"LEFT JOIN pointage.liste_dge as liste_dge_dateNaiss_Nom_Prenom ON (
                                                liste_dge_dateNaiss_Nom_Prenom.DA_NAISN_ELECT = civicrm_individual.birth_date 
                                                AND liste_dge_dateNaiss_Nom_Prenom.NM_ELECT = civicrm_individual.last_name
                                                AND liste_dge_dateNaiss_Nom_Prenom.PR_ELECT = civicrm_individual.first_name
                                                )"),
    'match_liste_dge_codePostl_noPorte_Nom_Prenom' => array('label'=>"Correspondance(s) dans la liste élect. (code postal, no. de porte, nom et prénom)", /* code postal, no. de porte, nom et prénom */
                                   'display'=>'default_no', 
                                   'formatterCallback'=>array('liste_contacts', 'formatDGELink'),
                                   'sqlSelectList'=>"GROUP_CONCAT(DISTINCT liste_dge_codePostl_noPorte_Nom_Prenom.ID_DGE SEPARATOR '+') as match_liste_dge_codePostl_noPorte_Nom_Prenom",
                                   'sqlJoin'=>"LEFT JOIN pointage.liste_dge as liste_dge_codePostl_noPorte_Nom_Prenom ON (
                                                 liste_dge_codePostl_noPorte_Nom_Prenom.DA_NAISN_ELECT = civicrm_individual.birth_date 
                                                 AND liste_dge_codePostl_noPorte_Nom_Prenom.CO_POSTL = REPLACE(civicrm_address.postal_code, ' ', '')
                                                 AND liste_dge_codePostl_noPorte_Nom_Prenom.NM_ELECT = civicrm_individual.last_name
                                                 AND liste_dge_codePostl_noPorte_Nom_Prenom.PR_ELECT = civicrm_individual.first_name
                                                 )")

    );

    static $filterInfoArray = array(
    'all' => array('label'=>"Tous les membres valides, membres expirés et contributeurs", 'sql_where'=>''),
    'membres_valides' => array('label'=>"Membres valides de l'FQCIL", 'sql_where'=>"AND civicrm_membership.status_id != 4 -- Membre n'est pas expiré"),
    'membres_junior_valides' => array('label'=>"Membres junior valides", 'sql_where'=>"AND civicrm_membership.status_id != 4  AND (DATEDIFF(NOW(),civicrm_individual.birth_date)/365.25 < 26) -- Membre n'est pas expiré et a 25 ans (inclusivement) ou moins"),
    'membres_mineurs_valides' => array('label'=>"Membres mineurs valides", 'sql_where'=>"AND civicrm_membership.status_id != 4  AND (DATEDIFF(NOW(),civicrm_individual.birth_date)/365.25 < 18) -- Membre n'est pas expiré et a 17 ans (inclusivement) ou moins"),
    'membres_valides_et_expires' => array('label'=>"Membres valides et membres expirés", 'sql_where'=>"AND civicrm_membership.status_id IS NOT NULL -- A deja été membre"),
    'membres_junior_valides_et_expires' => array('label'=>"Membres junior valides et expirés", 'sql_where'=>"AND civicrm_membership.status_id IS NOT NULL AND (DATEDIFF(NOW(),civicrm_individual.birth_date)/365.25 < 26) -- A deja été membre et a 25 ans (inclusivement) ou moins"),
    'membres_expires' => array('label'=>"Membres expirés", 'sql_where'=>'AND civicrm_membership.status_id = 4'),
    'membres_expires_depuis_moins_24_mois' => array('label'=>"Membres expirés depuis moins de 24 mois", 'sql_where'=>"AND civicrm_membership.status_id = 4 AND end_date > TIMESTAMPADD(MONTH,-24,NOW())"),
    'membres_expirent_dans_moins_6_mois' => array('label'=>"Membres qui expireront dans moins de 6 mois", 'sql_where'=>"AND civicrm_membership.status_id != 4 AND end_date < TIMESTAMPADD(MONTH,6,NOW())"),
    'membres_expirent_dans_moins_2_mois' => array('label'=>"Membres qui expireront dans moins de 2 mois", 'sql_where'=>"AND civicrm_membership.status_id != 4 AND end_date < TIMESTAMPADD(MONTH,2,NOW())"),
    'contrib_dans_dernier_2_jours' => array('label'=>"Don ou renouvellement dans les derniers 2 jours", 'sql_where'=>"AND civicrm_contribution.receipt_date IS NOT NULL AND civicrm_contribution.receipt_date > TIMESTAMPADD(DAY,-2,NOW()) "), 
    'contrib_dans_dernier_2_semaines' => array('label'=>"Don ou renouvellement dans les dernières 2 semaines", 'sql_where'=>"AND civicrm_contribution.receipt_date IS NOT NULL AND civicrm_contribution.receipt_date > TIMESTAMPADD(WEEK,-2,NOW()) "), 
    'contrib_dans_dernier_2_mois' => array('label'=>"Don ou renouvellement dans les derniers 2 mois", 'sql_where'=>"AND civicrm_contribution.receipt_date IS NOT NULL AND civicrm_contribution.receipt_date > TIMESTAMPADD(MONTH,-2,NOW()) "), //'sql_having'=>"AND last_financial_transaction_date > TIMESTAMPADD(MONTH,-2,NOW())"),
    'contrib_recue_dans_dernier_2_jours' => array('label'=>"Don ou renouvellement reçu dans les derniers 2 jours", 'sql_where'=>"AND civicrm_contribution.receive_date IS NOT NULL AND civicrm_contribution.receive_date > TIMESTAMPADD(DAY,-2,NOW()) "), 
    'contrib_recue_dans_dernier_2_semaines' => array('label'=>"Don ou renouvellement reçu dans les dernières 2 semaines", 'sql_where'=>"AND civicrm_contribution.receive_date IS NOT NULL AND civicrm_contribution.receive_date > TIMESTAMPADD(WEEK,-2,NOW()) "), 
    'contrib_recue_dans_dernier_2_mois' => array('label'=>"Don ou renouvellement reçu dans les derniers 2 mois", 'sql_where'=>"AND civicrm_contribution.receive_date IS NOT NULL AND civicrm_contribution.receive_date > TIMESTAMPADD(MONTH,-2,NOW()) "), //'sql_having'=>"AND last_financial_transaction_date > TIMESTAMPADD(MONTH,-2,NOW())"),
    'membres_valides_sans_email' => array('label'=>"Membres valides sans emails", 'sql_where'=>"AND civicrm_membership.status_id != 4", 'sql_having'=>"AND email_list IS NULL"),
    'membres_valides_infos_manquantes' => array('label'=>"Membres valides avec infos manquantes", 'sql_where'=>"AND civicrm_membership.status_id != 4", 'sql_having'=>"AND (email_list IS NULL OR phone_list IS NULL OR civicrm_individual.birth_date IS NULL OR street_address IS NULL)"),
    'membres_valides_non_trouve_liste_elect' => array('label'=>"Membres valides non trouvés dans la liste électorale (date naiss, code postal, no. de porte).  N'oubliez pas d'activer les correspondances dans la liste électorale.", 'sql_where'=>"AND civicrm_membership.status_id != 4", 'sql_having'=>"AND match_liste_dge IS NULL"),
    'membres_valides_non_trouve_liste_elect_excl_mineurs' => array('label'=>"Membres valides non trouvés dans la liste électorale (date naiss, code postal, no. de porte), excluant les mineurs.  N'oubliez pas d'activer les correspondances dans la liste électorale.", 'sql_where'=>"AND civicrm_membership.status_id != 4 AND (civicrm_individual.birth_date IS NULL OR civicrm_individual.birth_date=0000-00-00 OR (DATEDIFF(NOW(),civicrm_individual.birth_date)/365.25 >= 18)) -- Membre n'est pas expiré et a 18 ans ou plus ou son âge est inconnu", 'sql_having'=>"AND match_liste_dge IS NULL"),
    'membres_valides_non_trouve_liste_elect_mais_autre_match' => array('label'=>"Membres valides non trouvés dans la liste électorale (date naiss, code postal, no. de porte) mais trouvés par une autre méthode.  N'oubliez pas d'activer les correspondances dans la liste électorale.", 'sql_where'=>"AND civicrm_membership.status_id != 4", 'sql_having'=>"AND match_liste_dge IS NULL AND (match_liste_dge_dateNaiss_Nom_Prenom IS NOT NULL OR match_liste_dge_codePostl_noPorte_Nom_Prenom IS NOT NULL) ")
    
    );

    static $resultsOrderingInfoArray = array(
    'circonscription' => array('label'=>"Circonscription, Nom, prénom", 'sql_order_by'=>'circonscription_id, '),
    'last_contrib_or_membership' => array('label'=>"Par ordre de dernière transaction financière", 'sql_order_by'=>"last_financial_transaction_date DESC, "),
    'satus' => array('label'=>"Circonscription, validité du membership, Nom, prénom", 'sql_order_by'=>"circonscription_id, name DESC, ")

    );

    static function formatDGELink($string) {
        $idArray = explode('+',$string);
        return "<a href='https://registre.FQCIL.qc.ca/registre/?q=node/5&iddgelist={$string}'>".implode(', ', $idArray)."</a>";
    }

    //
     
    function __construct()
    {

		$this->addExpiredMembersFilters ();
    }


	function addExpiredMembersFilters () {

		$currentMonth = date ('F Y');
		$nbDaysCurrentMonth = date ('j');

		$lastMonth =  date ('F Y', mktime(0, 0, 0, date('m')-1, date('d'),   date('Y')));
		$nbDaysLastMonth = intval (date ('t', mktime(0, 0, 0, date('m')-1, date('d'),   date('Y')))) + $nbDaysCurrentMonth; 

		$twoMonths =  date ('F Y', mktime(0, 0, 0, date('m')-2, date('d'),   date('Y')));
		$nbDaysTwoMonth = intval (date ('t', mktime(0, 0, 0, date('m')-2, date('d'),   date('Y')))) + $nbDaysLastMonth;

		$threeMonths =  date ('F Y', mktime(0, 0, 0, date('m')-3, date('d'),   date('Y')));
		$nbDaysThreeMonth = intval (date ('t', mktime(0, 0, 0, date('m')-3, date('d'),   date('Y')))) + $nbDaysTwoMonth;

		$fourMonths =  date ('F Y', mktime(0, 0, 0, date('m')-4, date('d'),   date('Y')));
		$nbDaysFourMonth = intval (date ('t', mktime(0, 0, 0, date('m')-4, date('d'),   date('Y')))) + $nbDaysThreeMonth;

		$fiveMonths =  date ('F Y', mktime(0, 0, 0, date('m')-5, date('d'),   date('Y')));
		$nbDaysFiveMonth = intval (date ('t', mktime(0, 0, 0, date('m')-5, date('d'),   date('Y')))) + $nbDaysFourMonth;

		self::$filterInfoArray['membres_expire_mois_courant'] = array('label'=>"Membres qui ont expiré en ".$currentMonth, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysCurrentMonth." AND DATEDIFF(NOW(), end_date) > 0");
		self::$filterInfoArray['membres_expire_il_y_a_2_mois'] =  array('label'=>"Membres qui ont expiré en ".$lastMonth, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysLastMonth." AND DATEDIFF(NOW(), end_date) >".$nbDaysCurrentMonth);
		self::$filterInfoArray['membres_expire_il_y_a_3_mois'] = array('label'=>"Membres qui ont expiré en ".$twoMonths, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysTwoMonth." AND DATEDIFF(NOW(), end_date) > ".$nbDaysLastMonth);
		self::$filterInfoArray['membres_expire_il_y_a_4_mois'] = array('label'=>"Membres qui ont expiré en ".$threeMonths, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysThreeMonth." AND DATEDIFF(NOW(), end_date) > ".$nbDaysTwoMonth);
		self::$filterInfoArray['membres_expire_il_y_a_5_mois'] = array('label'=>"Membres qui ont expiré en ".$fourMonths, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysFourMonth." AND DATEDIFF(NOW(), end_date) > ".$nbDaysThreeMonth);
		self::$filterInfoArray['membres_expire_il_y_a_6_mois'] = array('label'=>"Membres qui ont expiré en ".$fiveMonths, 'sql_where'=>"AND DATEDIFF(NOW(), end_date) < ".$nbDaysFiveMonth." AND DATEDIFF(NOW(), end_date) > ".$nbDaysFourMonth);
	}


    static function getMembersPDOStatement($additionalWhere=null,
    $selectListOverride=null,
    $groupByHavingSql=null,
    $additionalOrderBy=null,
    $circWhereOverride=null,
    $joinTelephones=false) {

        $debug=false;

        if($additionalWhere) {
            $additionalWhereSql = $additionalWhere;
        }
        else {
            $additionalWhereSql = '';
        }

        if($circWhere) {
            $memberCircWhereSql = $circWhereOverride;
        }
        else{
            if($_SESSION["contact_override_circonscription"]) {
                $memberCircWhereSql = "";
                $memberCircWhereJoin = '';
            }
            else {
                if(strstr($_SESSION['user']->no_circn, 'region_')) {
                    $regionId = substr($_SESSION['user']->no_circn, strlen('region_'));
                    //var_dump($regionId);
                    $memberCircWhereSql = "";
                    $memberCircWhereJoin = "JOIN circonscriptions ON (circonscription_civicrm_custom_value.int_data=circonscriptions.NO_CIRCN_PROVN AND circonscriptions.region=$regionId)";
                }
                else {
                    $memberCircWhereSql = "AND circonscription_civicrm_custom_value.int_data= {$_SESSION['user']->no_circn}";
                    $memberCircWhereJoin = '';
                }
            }

        }

        $fieldsSqlSelectListArray = null;
        $fieldsSqlSelectList = null;
        $fieldsSqlJoinArray = null;
        $fieldsSqlJoin = null;
        foreach (self::$fieldInfoArray as $field => $fieldInfo) {
            if( $_SESSION["show_contact_{$field}"]==1){
                if(!empty($fieldInfo['sqlSelectList'])) {
                    $fieldsSqlSelectListArray[] = $fieldInfo['sqlSelectList'];
                }
                if(!empty($fieldInfo['sqlJoin'])) {
                    $fieldsSqlJoinArray[] = $fieldInfo['sqlJoin'];
                }
            }
        }
        if(!empty($fieldsSqlSelectListArray)){
            $fieldsSqlSelectList .= "-- Begin fields Sql select list \n";
            $fieldsSqlSelectList .= implode(", \n", $fieldsSqlSelectListArray);
            $fieldsSqlSelectList .= ", \n";
            $fieldsSqlSelectList .= "-- End fields Sql select list \n";
        }
        if(!empty($fieldsSqlJoinArray)){
            $fieldsSqlJoin .= "-- Begin fields Sql Joins \n";
            $fieldsSqlJoin .= implode(" \n", $fieldsSqlJoinArray);
            $fieldsSqlJoin .= " \n";
            $fieldsSqlJoin .= "-- End fields Sql Joins \n";
        }

        $resultOrdering = self::$resultsOrderingInfoArray[$_SESSION["contact_selected_ordering"]];
        //var_dump($resultOrdering);
        $resultOrderingAppendSql = $resultOrdering['sql_order_by'];
         
        // circonscription_civicrm_custom_value.int_data, but can't be qualified
        $orderCond = "$resultOrderingAppendSql last_name, first_name, street_address";
        if($orderBy!=NULL)
        $orderCond = $orderBy;

        $filter = $_SESSION["contact_selected_filter"];
        $filterInfo = self::$filterInfoArray[$filter];
        $filterSqlWhere = $filterInfo['sql_where'];
        if(!empty($filterInfo['sql_having'])) {
            $filterSqlHaving=$filterInfo['sql_having'];
        }
        else {
            $filterSqlHaving=null;
        }
        $membershipSelectList = self::buildMembershipSelectList();
        $contributionSelectList = self::buildContributionSelectList();
        $membershipJoin = self::buildMembershipJoin();
        $contributionJoin = self::buildContributionJoin();
        $sqlTempTable = "CREATE TEMPORARY TABLE latest_membership (PRIMARY KEY(max_id)) SELECT MAX(civicrm_membership.id) as max_id, contact_id FROM registre_prod.civicrm_membership GROUP BY contact_id;";

        $sql_select_individual_info = <<<EOT
                SELECT 
                civicrm_contact.external_identifier AS no_membre_FQCIL,
                civicrm_contact.id AS contact_id,
                civicrm_individual.first_name,
                civicrm_individual.last_name,
                civicrm_individual.birth_date,
                civicrm_individual.gender_id,
                circonscription_civicrm_custom_value.int_data AS circonscription_id,
                civicrm_address.street_address, 
                civicrm_address.city,
                civicrm_address.postal_code,
                -- civicrm_phone.phone, doit rester pour filtrer
                GROUP_CONCAT(DISTINCT civicrm_phone.phone ORDER BY civicrm_phone.is_primary DESC SEPARATOR ', ') as phone_list, 
                -- civicrm_email.email, doit rester pour filtrer
                GROUP_CONCAT(DISTINCT civicrm_email.email ORDER BY civicrm_email.is_primary DESC SEPARATOR ', ') as email_list, 
                $fieldsSqlSelectList
                $contributionSelectList
                $membershipSelectList
                MAX(civicrm_contribution.receipt_date) AS last_financial_transaction_date
                FROM registre_prod.civicrm_contact
                LEFT JOIN $contributionJoin
                LEFT JOIN $membershipJoin
                JOIN registre_prod.civicrm_individual ON (civicrm_individual.contact_id=civicrm_contact.id)
                -- Begin Get contact circonscription
                JOIN registre_prod.civicrm_custom_value as circonscription_civicrm_custom_value ON (
                circonscription_civicrm_custom_value.entity_table='civicrm_contact' AND
                circonscription_civicrm_custom_value.custom_field_id='2' AND -- Circonscription
                civicrm_contact.id = circonscription_civicrm_custom_value.entity_id
                )
                JOIN registre_prod.civicrm_custom_option AS circonscription_civicrm_custom_option ON (
                    circonscription_civicrm_custom_option.entity_table = 'civicrm_custom_field' AND
                    circonscription_civicrm_custom_option.entity_id = '2' AND -- circonscription
                    circonscription_civicrm_custom_option.value = circonscription_civicrm_custom_value.int_data -- int_data=no. de la circonscription, label=nom de la circonscription
                    )
                -- End Get contact circonscription
                $memberCircWhereJoin
                -- Begin get radiation status
                -- custom_field_id = '3' correspond au booléen de la radiation. int_data 0 pour non radié, 1 pour radié.  
                -- Il y a toujours une valeur, c'est pourquoi on ne fait pas un left join, pour des raisons de performance
				JOIN registre_prod.civicrm_custom_value AS radiation_civi_crm_custom_value ON
         		(radiation_civi_crm_custom_value.entity_table = 'civicrm_contact' AND
         		radiation_civi_crm_custom_value.custom_field_id='3' AND
         		civicrm_contact.id = radiation_civi_crm_custom_value.entity_id 
         		AND radiation_civi_crm_custom_value.int_data=0 -- Completely exclude radiated members (very important!!!)
         		)
                -- End get radiation status
                -- Begin get contact information, all left join because any of it could not exist
                LEFT JOIN registre_prod.civicrm_location ON (civicrm_contact.id=civicrm_location.entity_id AND civicrm_location.entity_table = 'civicrm_contact')
                LEFT JOIN registre_prod.civicrm_phone ON (civicrm_location.id=civicrm_phone.location_id)
                LEFT JOIN registre_prod.civicrm_email ON (civicrm_location.id=civicrm_email.location_id)
                JOIN registre_prod.civicrm_address ON
                (civicrm_address.location_id = civicrm_location.id
                )
                -- End get contact information
                $fieldsSqlJoin
                -- For testing (none found when searching for members) WHERE circonscription_civicrm_custom_value.int_data IS NULL
                WHERE
                1=1 $memberCircWhereSql
                $filterSqlWhere
                GROUP BY civicrm_contact.id
                HAVING 
                ((last_contribution_date IS NOT NULL) /* Contact once had a contribution */
                OR (civicrm_membership.id IS NOT NULL)) /* Contact is or was once a member */
                $filterSqlHaving
                ORDER BY $orderCond
EOT;

                $dbQuery = $sql_select_individual_info;

                $connection = DB::getDatabase('pointage');

                // pretty_print_r($dbQuery);
                if($debug) {
                    list($usec, $sec) = explode(' ', microtime());
                    $script_start = (float) $sec + (float) $usec;
                    pretty_print_r($sqlTempTable);
                    pretty_print_r($dbQuery);
                    flush();
                }

                try {

                    $retval = $connection->query($sqlTempTable);
                    //$retval = $connection->query($sqlTempFunction);
                    $retval = $connection->query($dbQuery);
                }
                catch (Exception $e) {
                    pretty_print_r($e->getMessage());
                    pretty_print_r($dbQuery);
                }

                if($debug){
                    list($usec, $sec) = explode(' ', microtime());
                    $script_end = (float) $sec + (float) $usec;
                    $elapsed_time = $script_end - $script_start;
                    echo "Elapsed time = ". $elapsed_time."<br/>";
                }
                return $retval;
    }


    static function buildMembershipSelectList() {
        $selectList = <<<EOT
        civicrm_membership.id, -- membership id
        civicrm_membership.join_date,
        civicrm_membership.start_date,
        civicrm_membership.end_date,
        civicrm_membership_status.name,
        civicrm_membership.status_id,
EOT;
        return $selectList;
    }
    static function buildMembershipJoin() {

        $sql_select_contributions = <<<EOT
		-- SELECT OR JOIN
        (latest_membership JOIN registre_prod.civicrm_membership
        JOIN registre_prod.civicrm_membership_type JOIN registre_prod.civicrm_membership_status )
        ON (
        latest_membership.max_id = civicrm_membership.id AND
        civicrm_contact.id = civicrm_membership.contact_id
        AND civicrm_membership.membership_type_id = civicrm_membership_type.id
        AND civicrm_membership.status_id = civicrm_membership_status.id)
        -- Le statut de radiation du membre sera dans civicrm_membership.status_id: '4' = membre radié
EOT;

        //echo "<pre>$sql_select_contributions</pre>";
        return $sql_select_contributions;
    }

    static function buildContributionSelectList() {
        $selectList = <<<EOT
            MAX(civicrm_contribution.receipt_date) as last_contribution_date,
            civicrm_contribution.id,
EOT;

        return $selectList;
    }
    static function buildContributionJoin() {

        
        /* civicrm_contribution_type contient la liste des types
         * 2=Adhésions à la FQCIL
         */
        
        /*
         * 
         * SELECT OR JOIN
                (registre_prod.civicrm_contribution JOIN registre_prod.civicrm_contribution_type)
                ON
                (civicrm_contact.id = civicrm_contribution.contact_id
                AND contribution_status_id = 1 -- Seulement terminé, exclut "En attente" et "Annulées" 
                /* AND civicrm_contribution.contribution_type_id = civicrm_contribution_type.id
                AND civicrm_contribution_type.id != '2'  -- Toutes les contributions sauf les adhésions à la FQCIL
                )
         */
        $sql_select_contributions = <<<EOT
        
            -- SELECT OR JOIN
                registre_prod.civicrm_contribution 
                ON
                (civicrm_contact.id = civicrm_contribution.contact_id
                AND contribution_status_id = 1 /* Seulement terminé, exclut "En attente" et "Annulées" */
                )
EOT;

        //echo "<pre>$sql_select_contributions</pre>";
        return $sql_select_contributions;
    }

    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function executeListQuery()
    {
        $this->rawQueryStm = self::getMembersPDOStatement(null,null, null, null, null, true);
    }

    /** Exporte la liste des contacts en CSV
     *
     * @author benoitg
     **/
    function exportCSV() {
        $delimiter = ',';
        // Tell browser to expect a CSV file
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="contacts.csv"');
        $fiveMBs = 5 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'rw+');


        $electorRows = null;
        $nextRow = $this->rawQueryStm->fetch( PDO::FETCH_ASSOC);

        $headers = array();
        foreach (self::$fieldInfoArray as $field => $fieldInfo) {
            if( $_SESSION["show_contact_{$field}"]==1){
                $headers[$field]=utf8_decode($fieldInfo['label']);
            }
        }
        //$headers = array_keys($nextRow);
        fputcsv($fp, $headers, $delimiter);
        $grandTotalIndividualString=utf8_decode("Nombre total d'individus dans le rapport");
        $membership_status_totals[$grandTotalIndividualString]=0;
        while ($nextRow)
        {
            //var_dump($nextRow);
            $data = array();
            foreach (self::$fieldInfoArray as $field => $fieldInfo) {
                if( $_SESSION["show_contact_{$field}"]==1){
                    $data[$field]=$nextRow[$field];
                }
            }
            fputcsv($fp, $data, $delimiter);
            $nextRow['name']==''?$membership_status_totals[utf8_decode("Nombre de contributeurs n'ayant jamais été membres")]++:$membership_status_totals[$nextRow['name']]++;
            $membership_status_totals[$grandTotalIndividualString]++;
            $nextRow = $this->rawQueryStm->fetch(PDO::FETCH_ASSOC);
        }
        $reportTmpArray = array();
        foreach($membership_status_totals as $key=>$value) {
            $reportTmpArray[] = $key . ": ".$value;
        }
        $strReport[] = implode(', ', $reportTmpArray);
        fputcsv($fp, $strReport, $delimiter);
        // read what we have written
        rewind($fp);
        //var_dump($membership_status_totals);
        //exit();
        echo stream_get_contents($fp);
    }

    /** Affiche une liste d'électeurs selon ... A déterminer 
     *
     * @author benoitg
     **/
    function displayListUI() {
        ?>
<form method="POST" id="batch_selection"
	action="<?=$SERVER['REQUEST_URI']?>"><?php
	echo "<input type=hidden id='formInvalidItemsArray'/>";
	$no_pointage = NULL;
	$noCirconscription = NULL;
	$factory= new DropdownFactory();
	$electorRows = null;
	$nextRow = $this->rawQueryStm->fetch( PDO::FETCH_ASSOC);

	while ($nextRow)
	{
	    //var_dump($nextRow);
	    $currentRow = $nextRow;
	    $nextRow = $this->rawQueryStm->fetch(PDO::FETCH_ASSOC);
	    $electorRows = array();
	    /*
	     while($nextRow['ID_DGE']==$currentRow['ID_DGE']) {
	     //echo " Fetch a row for {$nextRow['ID_DGE']}<br>";
	     $electorRows[] = $nextRow;
	     $currentRow = $nextRow;
	     $nextRow = $this->rawQueryStm->fetch(PDO::FETCH_ASSOC);
	     }
	     */
	    $elector = $currentRow;

	    $last_pointage = null;
	    //$historique = Historique::getHistoriqueFromAllRows($elector['ID_DGE'], $electorRows);
	    flush();
	    if($elector['circonscription_id'] != $noCirconscription)
	    {
	        if(!is_null($noCirconscription) && (strpos($_SESSION["contact_selected_ordering"],'circonscription')!==false)) {
	            echo '</table><hr class="breakhere">';
	        }

	        if(is_null($noCirconscription) || (strpos($_SESSION["contact_selected_ordering"],'circonscription')!==false))
	        {
	            if((strpos($_SESSION["contact_selected_ordering"],'circonscription')!==false)){
	                echo "<h2>Liste des Membres/Sympathisants : Circonscription {$elector['circonscription_id']} (".circonscription::circn_name_from_no($elector['circonscription_id'], DB::getDatabase('pointage')).")</h2>\n";
	            }
	            $noCirconscription = $elector['circonscription_id'];
	            echo "<u>Note:</u> Pour trier, cliquez sur le titre d'une colonne.";
	            echo "<br><br>";
	            ?>
<table id="table-1" class="sort-table" border="1" cellpadding="4"
	cellspacing="0">
	<thead>
		<tr>

		<?php
		if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
		    echo "<td>SGM</td>\n";
		}
		foreach (self::$fieldInfoArray as $field => $fieldInfo) {
		    if( $_SESSION["show_contact_{$field}"]==1){
		        echo "<td class='$field'>{$fieldInfo['label']}</td>\n";
		    }
		}


		?>
		</tr>
	</thead>
	<? }
	    }



	    //$motif = !$motif;
	    $motif=true;

	    if ($motif){
	        $bgColor = $GLOBALS['first_color'];
	    }
	    else{
	        $bgColor = $GLOBALS['second_color'];
	    }

	    /* Display actual info */
	    echo "<tr bgcolor='$bgColor'>\n";
	    if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){

	        echo "<td align='right'><a href='http://registre.FQCIL.qc.ca/registre/index.php?q=civicrm/contact/view&reset=1&cid={$currentRow['contact_id']}'>Éditer</a></td>\n";
	    }
	    foreach (self::$fieldInfoArray as $field => $fieldInfo) {
	        if( $_SESSION["show_contact_{$field}"]==1){
	            if(!empty($fieldInfo['formatterCallback'])) {
	                $displayValue = call_user_func($fieldInfo['formatterCallback'],$currentRow[$field]);
	            }
	            else {
	                $displayValue = $currentRow[$field];
	            }
	            echo "<td class='$field'>{$displayValue}</td>\n";
	        }
	    }
	    echo "</tr>\n";
	    /* Décomptes */
	    $individuals++;
	    if(!empty($currentRow['status_id']) && $currentRow['status_id']!=4) {
	        $membresValides++;
	    }
	}
	echo "</table>\n";
	if($_SESSION['display']!="add_history" && $_SESSION['display']!="add"){
	    ?>

	<script type="text/javascript">
            <!--
            var st1 = new SortableTable(document.getElementById("table-1"),
                    [	
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
                    "CaseInsensitiveString", 		
                    "None"
                    ]);
        //st1.sort(1);
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
		value="<? echo $displayedElectors; ?>">

	</form>
	<br>
	Individus listés:
	<b><? echo $individuals; ?></b>
	<br>
	Membres en règle:
	<b><? echo $membresValides; ?></b>

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
	<form method="POST" name="frm_column_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>"><?php 
		/*
		 * if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
		 echo "<td>SGM</td>\n";
		 }
		 */
		foreach (self::$fieldInfoArray as $field => $fieldInfo) {
		    if($fieldInfo['display']=='default_yes'
		    || $fieldInfo['display']=='default_no'
		    || ($fieldInfo['display']=='restricted' && ($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'))
		    ){
		        echo "<input type='checkbox' value='1' name='show_contact_{$field}'";
		        if($_SESSION["show_contact_{$field}"] == 1 || (!isset($_SESSION["show_contact_{$field}"]) && $fieldInfo['display']=='default_yes')) {
		            echo 'checked';
		        }
		        echo "> {$fieldInfo['label']}&nbsp;&nbsp;";
		    }
		}


		?> <input type="submit" name='submit_contact_column_config'
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


        foreach (self::$fieldInfoArray as $field => $fieldInfo) {
            if(!empty($_POST['submit_contact_column_config']))
            {
                if(!empty($_POST["show_contact_{$field}"])){
                    $fieldInfo['display']=='no' || ($fieldInfo['display']=='restricted' && !($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence')
                    )?$_SESSION["show_contact_{$field}"]=0:$_SESSION["show_contact_{$field}"]=1;
                }
                else {
                    $fieldInfo['display']=='yes'?$_SESSION["show_contact_{$field}"]=1:$_SESSION["show_contact_{$field}"]=0;
                }
            }
            else {
                if(!isset($_SESSION["show_contact_{$field}"])) {
                    if($fieldInfo['display']=='default_yes' || $fieldInfo['display']=='yes' ){
                        $_SESSION["show_contact_{$field}"]=1;
                    }
                    else {
                        $_SESSION["show_contact_{$field}"]=0;
                    }
                }
            }
        }
    }

    /** Retourne l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return unknown_type
     */
    function displayFilterConfigUI() {
         
        ?>

	<form method="POST" name="frm_filter_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<fieldset class="hide"><legend>Filtrer les résultats:</legend> <?php

	// Print the label
	print("<select name='contact_selected_filter' onChange='this.form.submit();'>");
	foreach (self::$filterInfoArray as $filter => $filterInfo) {
	    ($_SESSION["contact_selected_filter"]==$filter)?$selected='selected':$selected='';
	    print("<option value='{$filter}' $selected>");
	    echo $filterInfo['label'];
	    print("</option>");
	    //}
	}
	print("</select>");

	print(" Tri initial: <select name='contact_selected_ordering' onChange='this.form.submit();'>");
	foreach (self::$resultsOrderingInfoArray as $filter => $filterInfo) {
	    ($_SESSION["contact_selected_ordering"]==$filter)?$selected='selected':$selected='';
	    print("<option value='{$filter}' $selected>");
	    echo $filterInfo['label'];
	    print("</option>");
	}
	print("</select>");

	if($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence'){
	    echo " Obtenir la liste pour l'ensemble des circonscriptions";
	    echo "<input type='checkbox' value='1' name='contact_override_circonscription' onChange='this.form.submit();' ";
	    if($_SESSION["contact_override_circonscription"] == 1 ) {
	        echo ' checked ';
	    }
	    echo ">";
	}

	echo "<input type='hidden' value='1' name='contact_filter_form_submit' /> ";
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
        // var_dump($_REQUEST);

        if(!empty($_REQUEST['contact_filter_form_submit']) && !empty($_REQUEST['contact_selected_filter']))
        {
            $_SESSION["contact_selected_filter"]=$_REQUEST['contact_selected_filter'];
        }
        else if(empty($_SESSION["contact_selected_filter"])){
            $_SESSION["contact_selected_filter"]='membres_valides';
        }

        if(!empty($_REQUEST['contact_filter_form_submit']) && !empty($_REQUEST['contact_selected_ordering']))
        {
            $_SESSION["contact_selected_ordering"]=$_REQUEST['contact_selected_ordering'];
        }
        else if(empty($_SESSION["contact_selected_ordering"])){
            $_SESSION["contact_selected_ordering"]='circonscription';
        }

        if(!empty($_REQUEST['contact_filter_form_submit']) && !empty($_REQUEST['contact_override_circonscription'])
        && ($_SESSION['user']->access == 'superadmin' || $_SESSION['user']->access == 'permanence')){
            $_SESSION["contact_override_circonscription"] = 1;
        }
        else if(!empty($_REQUEST['contact_filter_form_submit']) || !isset($_SESSION["contact_selected_ordering"])){
            $_SESSION["contact_override_circonscription"] = 0;
        }
    }
    /** Retourne l'interface pour sélectionner le format d'exportation
     *
     * @return unknown_type
     */
    function displaySelectFormatUI() {
        ?>
	<fieldset class="hide"><legend>Exportation vers un chiffrier:</legend>
	<form method="POST" name="frm_format_selection"
		action="<?php echo $_SERVER['PHP_SELF']; ?>"><input type="submit"
		name="export_csv" value="Exporter la liste en format CSV (Excel)"></form>
	</fieldset>
	<?php

    }
    /** Traite l'interface pour sélectionner les champs affichés dans la liste
     *
     * @return format:  'csv' or 'screen'
     */
    function processSelectFormatUI() {
        //SEARCH
        if( !empty($_POST['export_csv'])){
            return('csv');
        }
        else {
            return 'screen';
        }
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
			<td valign="top" align="center"><? echo $factory->build_edit_new("Jour du scrutin", "vote", 'VOTE[]');?>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center">
			<h3><u>Important:</u> Les champs <i>Type de contact</i> et <i>Contact
			Réussi ?</i> sont <b>obligatoires</b> pour créer un pointage valide.</h3>
			</td>
		</tr>

		<tr>
			<td colspan="3" align="center"><b>Type de contact: </b> <? echo $factory->build_edit_new(NULL, "type_contact", "CONTACT[]");?>&nbsp;&nbsp;
			<b>Contact Réussi ?:</b> <? echo $factory->build_edit_new(NULL, "resultat", "RESULTAT[]");?>&nbsp;&nbsp;
			<? echo $factory->build_edit_new("Intention de vote", "allegeance", "ALLEGEANCE[]");?>&nbsp;&nbsp;
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
