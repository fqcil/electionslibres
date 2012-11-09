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

error_reporting(E_ALL);
require_once('lib/import.class.php');

class dge extends import
{
    public $filename    = NULL;
    private $lastParsedCircn = NULL;
    public $destination = NULL;
    public $data        = array();
    public $password    = NULL;

    function assoce_array_to_csv($array){
        $retval = '';
        $first=true;
        foreach ($array as $element) {
            !$first?$retval .= ',':$first=false;
            $retval .= '"'.str_replace('"','""',$element).'"';
        }
        $retval .="\r";
        return $retval;
    }

    function updatePointagePrimaryKeys() {
        $sql[]= " ALTER TABLE pointage ADD NEW_P_ID VARCHAR( 255 ) NOT NULL AFTER id ;\n";
        $sql[]= " ALTER TABLE historique_pointage ADD NEW_H_ID VARCHAR( 255 ) NOT NULL AFTER ELECT_ID ;\n";

        $sql[]= "SET autocommit=0;";

        $sql[]= "LOCK TABLES pointage WRITE, liste_dge READ, historique_pointage WRITE";
        $sql[]= "SELECT FROM pointage, liste_dge WHERE
liste_dge.NM_ELECT = pointage.NM_ELECT 
AND liste_dge.PR_ELECT = pointage.PR_ELECT  
AND liste_dge.DA_NAISN_ELECT = pointage.DERNIER_POINTAGE_DA_NAISN_ELECT
AND liste_dge.NO_CIVQ_ELECT = pointage.DERNIER_POINTAGE_NO_CIVQ_ELECT AND liste_dge.ID_DGE IS NULL;
";; //Rows from historique pointage will e dropped as well.
        //$sql[]= "SET unique_checks=0;";
        $sql[]= "SET foreign_key_checks=0;";
        $sql[]= "UPDATE pointage, liste_dge
            SET pointage.NEW_P_ID=liste_dge.ID_DGE
            WHERE 
liste_dge.NM_ELECT = pointage.NM_ELECT 
AND liste_dge.PR_ELECT = pointage.PR_ELECT  
AND liste_dge.DA_NAISN_ELECT = pointage.DERNIER_POINTAGE_DA_NAISN_ELECT
AND liste_dge.NO_CIVQ_ELECT = pointage.DERNIER_POINTAGE_NO_CIVQ_ELECT;
";
        $sql[]= "UPDATE pointage, historique_pointage
            SET  historique_pointage.NEW_H_ID=pointage.NEW_P_ID
            WHERE historique_pointage.H_ID = pointage.P_ID;";
        $sql[]= "SET foreign_key_checks=1;";
        //$sql[]= "SET unique_checks=1;";
        $sql[]= "COMMIT;";
        $sql[]= "UNLOCK TABLES;";
        $sql[]= "SET autocommit=1;";
        // Drop the current primary key and add a new
        $sql[]= "SET foreign_key_checks=0;";
        $sql[]= "ALTER TABLE pointage DROP PRIMARY KEY;\n";
        // Drop the column
        $sql[]= "ALTER TABLE pointage DROP P_ID;\n";
        $sql[]= "ALTER TABLE pointage CHANGE COLUMN NEW_P_ID P_ID;\n";
        $sql[]= "ALTER IGNORE TABLE pointage ADD PRIMARY KEY ( `P_ID` );\n";
        $sql[]= "SET foreign_key_checks=1;";
    }
    /** Parses the .csv found inside a dge zip archive in a format suitable for mysql import */
    function parse($filename, $outputFileHandle)
    {
        var_dump($filename);
        preg_match  ( "/^.*(?:ppcir)([\d]*)\.csv/" , $filename, $matches);
        //pretty_print_r($matches);
        if(empty($matches[0])){
            throw new exception ("unable to parse filename '{$filename}' to get circonscription number ");
        }
        $noCircn = $matches[1];
        echo "Importation du fichier $filename pour la circonscription {$matches[1]}<br/>";
        $this->lastParsedCircn=$noCircn;

        $fp = fopen($filename,'r');
        if(!$fp) {
            $this->db->rollBack();
            throw new exception ("Unable to open $filename");
        }
        fgetcsv($fp,1024,";");//Real first line is the disclaimer, skip it.  Somethimes they forget it, depending on their mood.   In which case it's really the header.
        $header = fgetcsv($fp,1024,";");
        pretty_print_r($header);
        $keys = $header;
        $count = 0;
        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;

        while(($line = fgetcsv($fp,1024,";")) !== FALSE)
        {
            if(!empty($line)){
                if($line[0]=="Nombre total d'enregistrement") {
                    //$nb_total_verify = $line[1] - 2;//Soustraire header et cette ligne
                    break;
                }

                $data = array_combine($keys, $line);
                //Not used
                unset($data['FIN']);
                unset($data['NM_CIRCN_PROVN']);
                foreach($data as &$line) {
                    //So fgetcsv treats encoding properly
                    $line = iconv  ('CP850', 'UTF8', $line);
                }
                
                //Liste du DGE normale
                $prependData['ID_DGE'] = $noCircn."_".$data['NO_SECTR_ELECT']."_".$data['NO_SECTN_VOTE']."_".$data['NO_ELECT'];
                
                //Pour le compté de rousseau (liste partielle), on a NO_SECTR_ELECT_PROVN au lieu de NO_SECTR_ELECT
                // $prependData['ID_DGE'] = $noCircn."_".$data['NO_SECTR_ELECT_PROVN']."_".$data['NO_SECTN_VOTE']."_".$data['NO_ELECT'];

                
                $prependData['NO_CIRCN_PROVN'] = $noCircn;
                //$data['CO_POSTL'] = preg_replace  ("/\s*/", "", $data['CO_POSTL']);//Strip whitespace
                $data['CO_POSTL'] = str_replace  (' ', '', $data['CO_POSTL']);

                $data = array_merge($prependData, $data);
                // var_dump($data);
                //$output = $this->assoc_array_to_csv($data);

                if(!fputcsv($outputFileHandle, $data)){
                    throw new exception("Error writing CSV file");
                }

                //echo ".";

            }
            else {
                echo "Skipped empty line<br/>";
            }
            $count++;
        }
        $nb_total_verify = $count; //Les listes de septembre 2009 ne comporte pas de nombre de vérification dans le fichier
        if($nb_total_verify == $count) {
            echo "<br/>Converti $count électeurs avec succès.";
        }
        else {
            throw new exception("<br/>Le nombre d'électeurs convertis ($count) ne correspond pas aux chiffres du fichier ($nb_total_verify)");
        }
        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = $script_end - $script_start;
        echo "(".round($count/$elapsed_time,1)." électeurs/sec)<br>";
        fclose($fp);
        @ob_flush();
        flush();
        return $count;
    }

    function loadDataFromMysqlCSV($tmpfname, $tableName='liste_dge_new'){
        $sql = "LOAD DATA INFILE '$tmpfname'
        IGNORE
        INTO TABLE $tableName
        CHARACTER SET utf8
         FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
         (ID_DGE, NO_CIRCN_PROVN, NO_SECTR_ELECT, NO_SECTN_VOTE, NM_ELECT, PR_ELECT, DA_NAISN_ELECT, CO_SEXE, AD_ELECT, NO_CIVQ_ELECT, NO_APPRT_ELECT, NM_MUNCP, CO_POSTL, NO_ELECT, STATUT_ELECT)
         ";
        safeExecSql($sql, true);
    }

    function importFromMysqlCSVAllCircn($tmpfname){
        require_once('include/schema_validate.php');
        $sql = "DROP TABLE IF EXISTS liste_dge_new;";
        safeExecSql($sql);
        $sql = "CREATE TABLE liste_dge_new LIKE liste_dge;";
        safeExecSql($sql);
        $sql = "LOCK TABLES liste_dge_new WRITE;";
        safeExecSql($sql);
        $this->loadDataFromMysqlCSV($tmpfname, 'liste_dge_new');
        $sql = "UNLOCK TABLES;";
        safeExecSql($sql);
        $sql = "RENAME TABLE liste_dge TO liste_dge_old, liste_dge_new TO liste_dge;";
        safeExecSql($sql, true);
        $sql = "DROP TABLE liste_dge_old;";
        safeExecSql($sql, true);
    }

    function importFromMysqlCSVOneCircn($tmpfname, $circnNo){
        if(empty($tmpfname) || empty($circnNo)) {
            throw new Exception("Invalid parameters: tmpfname: $tmpfname, circnNo: $circnNo ");
        }
        require_once('include/schema_validate.php');
		
        $sql = "LOCK TABLES liste_dge WRITE;";
        safeExecSql($sql);
        $sql = "DELETE FROM liste_dge WHERE NO_CIRCN_PROVN=$circnNo;";
        safeExecSql($sql);
        $this->loadDataFromMysqlCSV($tmpfname, 'liste_dge');
        $sql = "UNLOCK TABLES;";
        safeExecSql($sql);
		
	}

    function import() {
        switch($_REQUEST['format']) {
            case 'zip_of_zip_all_circonscriptions':
                $this->importDGEzipOfZip();
                break;
            case 'mysql_csv_all_circonscriptions':
                $this->importFromMysqlCSVAllCircn($this->filename);
                break;
            case 'zip_one_circonscription':
                $this->importDGEzipOneCircn();
                break;
            default:
                throw new exception("Format inconnu: {$_REQUEST['format']}!");
        }
    }

    /** Import from DGE zip archive */
    function importDGEzipOneCircn()
    {
        $files = $this->findFiles($this->destination,array('csv','pp'));
        $tmpfname = tempnam("/tmp2", "FOO");

        $handle = fopen($tmpfname, "w");
        $numElecteurs = 0;
        foreach($files AS $file)
        {
            echo($file."<br/>");
            if(!is_dir($file))
            {
                $numElecteurs += $this->parse($file, $handle);
            }
        }
        fclose($handle);
        chmod($tmpfname, 0666);
        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;
        $this->importFromMysqlCSVOneCircn($tmpfname, $this->lastParsedCircn);
        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = $script_end - $script_start;
        echo "<br/>Importé $numElecteurs électeurs avec succès.";
        echo "(".round($numElecteurs/$elapsed_time,1)." électeurs/sec)<br>";
        if($_REQUEST['delete_mysql_csv']=='yes') {
            unlink($tmpfname);
        }
        $this->removeDir($this->destination);
    }

    /** Import from DGE zip archive */
    function importDGEzipOfZip()
    {
        $files = $this->findFiles($this->destination,array('csv','pp'));
        $tmpfname = tempnam("/tmp2", "FOO");

        $handle = fopen($tmpfname, "w");
        $numElecteurs = 0;
        foreach($files AS $file)
        {
            echo($file."<br/>");
            if(!is_dir($file))
            {
                $numElecteurs += $this->parse($file, $handle);
            }
        }
        fclose($handle);
        chmod($tmpfname, 0666);
        list($usec, $sec) = explode(' ', microtime());
        $script_start = (float) $sec + (float) $usec;
        $this->importFromMysqlCSVAllCircn($tmpfname);
        list($usec, $sec) = explode(' ', microtime());
        $script_end = (float) $sec + (float) $usec;
        $elapsed_time = $script_end - $script_start;
        echo "<br/>Importé $numElecteurs électeurs avec succès.";
        echo "(".round($numElecteurs/$elapsed_time,1)." électeurs/sec)<br>";
        if($_REQUEST['delete_mysql_csv']=='yes') {
            unlink($tmpfname);
        }
        $this->removeDir($this->destination);
    }

    /** Deziper deux fois pour la liste du DGE */
    function unzip($file,$destination,$password)
    {
        switch($_REQUEST['format']) {
            case 'zip_of_zip_all_circonscriptions':
                parent::unzip($file,$destination,NULL,false);
                echo "Deleting: $file<br/>";
                unlink($file);
                $files = $this->findFiles($destination,array('zip'), true);

                foreach($files AS $archive)
                {
                    if(!is_dir($archive))
                    {
                        parent::unzip($archive,$destination,$password);
                    }
                }
                break;
            case 'zip_one_circonscription':
                parent::unzip($file,$destination,$password);
                break;
            default:
                throw new exception("Format inconnu: {$_REQUEST['format']}!");
        }

    }
}

?>
