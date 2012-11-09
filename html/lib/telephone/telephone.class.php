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
require_once('lib/import.class.php');
require_once('lib/liste_electeurs.class.php');
require_once('lib/historique.class.php');

class telephone extends import
{
    public $filename    = NULL;
    public $destination = NULL;
    public $data        = array();
    public $password    = NULL;
    public $circonscription    = NULL;
    public $date    = NULL;

	public $blacklist = array ();

    function parse($filename)
    {
        $this->db->beginTransaction();
        $sql = "INSERT INTO liste_telephones
            (id,NM_ELECT,PR_ELECT, AD_ELECT, NO_CIVQ_ELECT, NO_APPRT_ELECT, NM_MUNCP, CO_POSTL, TELEPHONE) 
            VALUES (:id,:NM_ELECT,:PR_ELECT,:AD_ELECT,:NO_CIVQ_ELECT,:NO_APPRT_ELECT,:NM_MUNCP,:CO_POSTL,:TELEPHONE)
            ON DUPLICATE KEY UPDATE id=:id, NM_ELECT=:NM_ELECT,PR_ELECT=:PR_ELECT,AD_ELECT=:AD_ELECT,NO_CIVQ_ELECT=:NO_CIVQ_ELECT,NO_APPRT_ELECT=:NO_APPRT_ELECT,NM_MUNCP=:NM_MUNCP,CO_POSTL=:CO_POSTL,TELEPHONE=:TELEPHONE";

        $stm = $this->db->prepare($sql);
        $fp = fopen($filename,'r');
        if(!$fp) {
            throw new exception ("Unable to open $filename");
        }
        $first_line = fgetcsv($fp,1024,",");
        pretty_print_r($first_line);
        $keys = $first_line;
        $count = 0;
        while(($line = fgetcsv($fp,1024,",")) !== FALSE)
        {
            if(!empty($line) && !empty($line[0])){


                $data = array_combine($keys, $line);

                $stm->bindValue(":id",$data['infoCANADA_ID_C']);
                $stm->bindValue(":NM_ELECT",$data['LastName']);
                $stm->bindValue(":PR_ELECT",$data['FirstName']);
                $stm->bindValue(":NO_APPRT_ELECT",$data['Address2']);
                $stm->bindValue(":NM_MUNCP",$data['City']);
                $stm->bindValue(":CO_POSTL",preg_replace  ("/\s*/", "", $data['PCode']));//Strip whitespace
                if(!empty($data['Address1'])){
                    preg_match  ( "/^([^\s]*?)\s+(.*)/" , $data['Address1'] , $matches);
                    //pretty_print_r($matches);
                    if(empty($matches[0])){
                        echo ("<br>unable to parse address {$data['Address1']}, Skipping<br>");
                        continue;
                    }
                    $stm->bindValue(":NO_CIVQ_ELECT", $matches[1]);
                    $stm->bindValue(":AD_ELECT", $matches[2]);
                }
                else {
                    $stm->bindValue(":NO_CIVQ_ELECT", '');
                    $stm->bindValue(":AD_ELECT", '');
                }
                $stm->bindValue(":TELEPHONE", $data['AreaCode']."-".$data['Phone']);

                //pretty_print_r($data);
                echo ".";
                try {
                    $stm->execute();
                }
                catch (Exception $e) {

                    echo "Error inserting row in database";
                    var_dump($line);
                    var_dump($data);
                    $this->db->rollBack();
                    throw $e;
                }
            }
            else
            {
                echo "Skipped empty line<br/>";
            }
            $count++;
        }
        echo "<br/>Importé $count no. de tél avec succès.<br/>";
        //$this->db->rollBack();
        $this->db->commit();
        fclose($fp);
    }


	function parse_alternate ($filename)
    {


        $this->db->beginTransaction();
        $sql = "INSERT INTO liste_telephones
            (NM_ELECT,PR_ELECT, AD_ELECT, NO_CIVQ_ELECT, NO_APPRT_ELECT, NM_MUNCP, CO_POSTL, no_telephone) 
            VALUES (:NM_ELECT,:PR_ELECT,:AD_ELECT,:NO_CIVQ_ELECT,:NO_APPRT_ELECT,:NM_MUNCP,:CO_POSTL,:TELEPHONE)";
            //ON DUPLICATE KEY UPDATE id=:id, NM_ELECT=:NM_ELECT,PR_ELECT=:PR_ELECT,AD_ELECT=:AD_ELECT,NO_CIVQ_ELECT=:NO_CIVQ_ELECT,NO_APPRT_ELECT=:NO_APPRT_ELECT,NM_MUNCP=:NM_MUNCP,CO_POSTL=:CO_POSTL,TELEPHONE=:TELEPHONE";

        $stm = $this->db->prepare($sql);
        $fp = fopen($filename,'r');
        if(!$fp) {
            throw new exception ("Unable to open $filename");
        }
        $first_line = fgetcsv($fp,1024,",");

        pretty_print_r($first_line);
        $keys = $first_line;
        $count = 0;
		$not_imported = 0;
		$this->blacklist = array ();

		/* Get the city from the filename */
		$explode = explode ("/", $filename);		// Get the basename
		$city = $explode [sizeof ($explode) - 1];

		$city = substr ($city, 0, -4);				// Strip the file extension
		$city = str_replace ('-', ' ', $city);		// Strip the dashes
		$city = str_replace ('%', '\'', $city);		// Restore quote

        while(($line = fgetcsv($fp,1024,",")) !== FALSE)
        {
            if(!empty($line) && !empty($line[0])){

                $data = array_combine($keys, $line);

				/* Split the complete name at the first space */
				$pos = strpos ($data ['Nom'], ' ');
				$last_name = substr ($data ['Nom'], 0, $pos);
				$first_name = substr ($data ['Nom'], $pos+1);

				/* Strip the dashes in the phone number and save it */
				$phone = str_replace ('-', '', $data['Phone']);
				//$phonenumbers[$phone] =  $phone;

				/* Remove the existing entries in liste_telephones table with this very same phone number */
				$this->remove_database_entries ($phone);

                //$stm->bindValue(":id", ' ');										// We haven't this information in those files
                $stm->bindValue(":NM_ELECT", $last_name);
                $stm->bindValue(":PR_ELECT", $first_name);
                $stm->bindValue(":NO_APPRT_ELECT",' ');
                $stm->bindValue(":NM_MUNCP",$city);
                $stm->bindValue(":CO_POSTL",preg_replace  ("/\s*/", "", $data['Pcode']));//Strip whitespace
                $stm->bindValue(":NO_CIVQ_ELECT", $data['No']);
                $stm->bindValue(":AD_ELECT", $data['Adress1']);
                $stm->bindValue(":TELEPHONE", $phone);

                //pretty_print_r($data);
                echo ".";
                try {
                    $stm->execute();
                }
                catch (Exception $e) {

                    echo "Error inserting row in database";
                    var_dump($line);
                    var_dump($data);
                    $this->db->rollBack();
					$not_imported ++;
                    throw $e;
                }
            }
            else
            {
                echo "Skipped empty line<br/>";
				$not_imported ++;
            }
            $count++;
        }
        echo "<br/>$count entrées valides trouvées dans le fichier d'importation.<br/>";
		$imported = $count - $not_imported;
		$imported_pc = ($imported/$count)*100;
        echo "<br/>Importé $imported no. de tél avec succès, soit $imported_pc %.<br/><br/>";
        //$this->db->rollBack();
        $this->db->commit();
        fclose($fp);
    }

    public function parse_sondage($filename){

        $electors = array();

        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;

        // Save the all data in an array
        $sql = "SELECT pointage.P_ID, liste_dge.ID_DGE, liste_dge.NO_CIRCN_PROVN, liste_dge.NM_ELECT, liste_dge.PR_ELECT, liste_dge.NO_CIVQ_ELECT, pointage.NOTE, liste_dge.DA_NAISN_ELECT, liste_telephones.no_telephone
            FROM liste_dge 
            LEFT JOIN liste_telephones ON (
                    liste_dge.NM_ELECT LIKE liste_telephones.NM_ELECT 
                    AND liste_dge.PR_ELECT LIKE concat(liste_telephones.PR_ELECT,'%') 
                    AND liste_dge.CO_POSTL = liste_telephones.CO_POSTL
                    AND liste_dge.NO_CIVQ_ELECT = liste_telephones.NO_CIVQ_ELECT
                    )
            LEFT JOIN pointage ON (
                    liste_dge.ID_DGE = pointage.P_ID 
            )
            WHERE liste_dge.NO_CIRCN_PROVN='{$this->circonscription}' AND liste_telephones.no_telephone IS NOT NULL;";        
        $stm = $this->db->query($sql);
		// $previousIdDge = '';
        while( $results = $stm->fetch(PDO::FETCH_ASSOC)){
            if($results['ID_DGE']==$previousIdDge) {
                continue;
            }
            else {
                $previousIdDge=$results['ID_DGE'];
            }

            $key = str_replace('-','',$results['no_telephone']);
            $electors[$key] = $results;
        }

        ////////////////////////////////////////////////
        // Prepare insert SQL request
        $sql_insert = "INSERT INTO pointage
            (`P_ID`,`NOTE`,`NM_ELECT`,`PR_ELECT`,`DERNIER_POINTAGE_DA_NAISN_ELECT`,`DERNIER_POINTAGE_NO_CIVQ_ELECT`,`DERNIER_POINTAGE_TELEPHONE`,`VOTE`) 
            VALUES(:id, :note, :NM_ELECT,:PR_ELECT,:DA_NAISN_ELECT,:NO_CIVQ_ELECT,:telephone,:vote);";
        $insert = $this->db->prepare($sql_insert);

        // Prepare update SQL request
        $sql_update = "UPDATE pointage SET `NOTE`=:note,NM_ELECT=:NM_ELECT,PR_ELECT=:PR_ELECT,DERNIER_POINTAGE_DA_NAISN_ELECT=:DA_NAISN_ELECT,DERNIER_POINTAGE_NO_CIVQ_ELECT=:NO_CIVQ_ELECT,DERNIER_POINTAGE_TELEPHONE=:telephone,VOTE=:vote
            WHERE `P_ID`=:id";
        $update = $this->db->prepare($sql_update);
        /////////////////////////////////////////////////


        $fp = fopen($filename,'r');
        if(!$fp) {
            throw new exception ("Unable to open $filename");
        }
        $first_line = fgetcsv($fp,1024,",");
        pretty_print_r($first_line);
        $keys = $first_line;
        $count = 0;
        $invalid = 0;
        while(($line = fgetcsv($fp,1024,",")) !== FALSE)
        {
			var_dump ($line);
            if(!empty($line) && !empty($line[0])){
                $data = array_combine($keys, $line);
                if( strcmp($data['Last Disposition'], 'Duplicate') != 0  && 
                        strcmp($data['Last Disposition'], 'DNC') != 0 &&
                        strcmp($data['Last Disposition'], 'Invalid') != 0 &&
                        !empty($data['Last Disposition']) ) {

                    if( array_key_exists($data['Telephone'], $electors ))
                    {
                        $elector = array();
                        $elector = $electors[$data['Telephone']];
                        $resultat = $this->resultat_sondage($data); 

                        $note = $elector['NOTE'];
                        $nm_elect = $elector['NM_ELECT'];
                        $pr_elect = $elector['PR_ELECT'];
                        $da_nais = $elector['DA_NAISN_ELECT'];
                        $no_civq = $elector['NO_CIVQ_ELECT'];
                        $tel = $elector['no_telephone'];
                        $pointage_id = $elector['P_ID'];
                        $vote = $resultat['vote'];

                        
                        // Update or create an entry in pointage table
                        if(empty($pointage_id))
                        {
							echo "Insert";
                            $pointage_id=$elector['ID_DGE'];
                            // Add in pointage
                            $insert->bindParam(':id',$pointage_id);
                            $insert->bindParam(':note', $note);
                            $insert->bindParam(':NM_ELECT',$nm_elect);
                            $insert->bindParam(':PR_ELECT', $pr_elect);
                            $insert->bindParam(':DA_NAISN_ELECT',$da_nais);
                            $insert->bindParam(':NO_CIVQ_ELECT',$no_civq);
                            $insert->bindParam(':telephone',$tel);
                            $insert->bindParam(':vote',$vote);

                            $result = $insert->execute();
                        }
                        else
                        {

							echo "Update";
                            // Update the existing pointage
                            $update->bindParam(':id',$pointage_id);
                            $update->bindParam(':note', $note);
                            $update->bindParam(':NM_ELECT',$nm_elect);
                            $update->bindParam(':PR_ELECT', $pr_elect);
                            $update->bindParam(':DA_NAISN_ELECT',$da_nais);
                            $update->bindParam(':NO_CIVQ_ELECT',$no_civq);
                            $update->bindParam(':telephone',$tel);
                            $update->bindParam(':vote',$vote);

                            $result = $update->execute();
                        }

                        // Historique::ajoute_pointage($this->db, $pointage_id, $resultat['contact'], $resultat['resultat'], $resultat['allegeance'], $this->date, $data['Last Disposition']);
                        $count++;
                    }
                    else{
                        $invalid++;
                    }
                } 
                else{
                    $invalid++;
                }
            }
            else
            {
                echo "Skipped empty line<br/>";
            }
        }
        echo "<br/>$count pointages téléphoniques mis à jour avec succès.<br/>";
        echo "<br/>$invalid pointages inutilisables.<br/><br/>";
        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = $script_end - $script_start;
        echo "(".round($count/$elapsed_time,1)." électeurs/sec)<br>";
        fclose($fp);

    }


	private function remove_database_entries ($phone)
	{
		// The blacklist contains the number we already during the current import.

		if (!array_key_exists ($phone, $this->blacklist))
		{
			$sql = "DELETE from liste_telephones WHERE no_telephone = '{$phone}';";
			$stm = $this->db->query($sql);
			$this->blacklist[$phone] = $phone;
		}
	}


/* Convertit une ligne de fichier d'autodialer en une rangée de table historique_pointage */
    private function resultat_sondage($data){

        $resultat = array();

        $resultat['contact'] = 'autodialed';
        $resultat['vote'] = NULL;
        $resultat['allegeance'] = NULL;

        switch($data['Last Disposition']){
            case 'Digit Pressed':
                $resultat['resultat'] = 'success';
                switch(substr($data['Tier1'], 0, 1)){
                    case '1':
                        $resultat['allegeance'] = 'pq';
                        break;
                    case '2':
                        $resultat['allegeance'] = 'plq';
                        break;
                    case '3':
                        $resultat['allegeance'] = 'FQCIL';
                        break;
                    case '4':
                        $resultat['allegeance'] = 'qs';
                        break;
                    case '5':
                        $resultat['allegeance'] = 'pv';
                        break;
                    case '6':
                        $resultat['allegeance'] = 'indecis';
                        break;
                    case '7':
                        $resultat['vote'] = 'nvp';
                        break;
                    default:
                        $resultat['allegeance'] = 'indecis';
                        break;
                }
                break;
            case 'No Digit Pressed':
            case 'No Digit pressed': //Attention, à la minuscule
                // no digit pressed but there was an answer
                $resultat['resultat'] = 'noanswer';
                break;
            case 'No Answer/No Connection':
                $resultat['resultat'] = 'noanswer';
                break;
            case 'Answering Machine/Disconnected':
                $resultat['resultat'] = 'noanswer';
                break;
            default:
                $resultat['resultat'] = 'noanswer';
                echo "Impossible d'interpréter le résultat: '{$data['Last Disposition']}'<br>";
                break;
        }
        return $resultat;
    }

    protected function import()
    {
        echo "Starting import process<br/>";
        $files = $this->findFiles($this->destination,array('.xls'));

        if(count($files) === 0)
        {
            throw new exception("No files found");
        }
        foreach($files AS $file)
        {
            $path_parts = pathinfo($file);

            $destinationFilename = $path_parts['dirname'].'/'.$path_parts['filename'].'.csv';
            echo $destinationFilename;
		
            $cmd = "xls2csv $file > {$destinationFilename}";
            self::shellExec($cmd);
            echo "Importing file $destinationFilename<br/>";
            if($this->source == 'sondage')
                $this->parse_sondage($destinationFilename);
            else
                $this->parse_alternate($destinationFilename);		// Call here the right parsing methd, depending on the type of files we received
        }
    }
}

?>
